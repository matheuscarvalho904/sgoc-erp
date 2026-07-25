<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Services;

use App\Modules\Inventory\Domain\Exceptions\InsufficientStockException;
use App\Modules\Inventory\Domain\Models\StockBalance;
use App\Modules\Inventory\Domain\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class InventoryTransactionService
{
    private const IN_TYPES = ['in', 'transfer_in', 'adjustment_in', 'return_in'];
    private const OUT_TYPES = ['out', 'transfer_out', 'adjustment_out', 'consumption_out'];

    public function post(StockMovement $movement): StockMovement
    {
        return DB::transaction(function () use ($movement): StockMovement {
            /** @var StockMovement $lockedMovement */
            $lockedMovement = StockMovement::query()->lockForUpdate()->findOrFail($movement->getKey());

            if ($lockedMovement->status === 'posted') {
                return $lockedMovement;
            }

            if ($lockedMovement->status === 'reversed') {
                throw new InvalidArgumentException('Uma movimentação estornada não pode ser processada novamente.');
            }

            $quantity = $this->positiveDecimal((string) $lockedMovement->quantity, 'A quantidade deve ser maior que zero.');
            $unitCost = $this->nonNegativeDecimal((string) ($lockedMovement->unit_cost ?? '0'));
            $direction = $this->direction((string) $lockedMovement->movement_type);

            $balance = $this->lockOrCreateBalance($lockedMovement);
            $before = (string) $balance->quantity_on_hand;

            if ($direction === 'in') {
                $newQuantity = $this->add($before, $quantity);
                $balance->average_cost = $this->weightedAverage(
                    currentQuantity: $before,
                    currentAverageCost: (string) $balance->average_cost,
                    incomingQuantity: $quantity,
                    incomingUnitCost: $unitCost,
                );
                $balance->quantity_on_hand = $newQuantity;
            } else {
                $available = $this->sub((string) $balance->quantity_on_hand, (string) $balance->quantity_reserved);
                $allowsNegative = (bool) optional($lockedMovement->warehouse)->allows_negative_stock;

                if (! $allowsNegative && $this->compare($quantity, $available) === 1) {
                    throw new InsufficientStockException(
                        sprintf('Saldo insuficiente. Disponível: %s; solicitado: %s.', $available, $quantity),
                    );
                }

                $balance->quantity_on_hand = $this->sub($before, $quantity);

                if ($this->compare($unitCost, '0') === 0) {
                    $unitCost = (string) $balance->average_cost;
                }
            }

            $balance->save();

            $lockedMovement->forceFill([
                'unit_cost' => $unitCost,
                'total_cost' => $this->multiply($quantity, $unitCost),
                'balance_before' => $before,
                'balance_after' => (string) $balance->quantity_on_hand,
                'status' => 'posted',
                'posted_at' => now(),
                'performed_by' => $lockedMovement->performed_by ?: auth()->id(),
            ])->saveQuietly();

            return $lockedMovement->refresh();
        }, 5);
    }

    public function reverse(StockMovement $movement, ?string $reason = null): StockMovement
    {
        return DB::transaction(function () use ($movement, $reason): StockMovement {
            /** @var StockMovement $original */
            $original = StockMovement::query()->lockForUpdate()->findOrFail($movement->getKey());

            if ($original->status !== 'posted') {
                throw new InvalidArgumentException('Somente movimentações processadas podem ser estornadas.');
            }

            if ($original->reversed_at !== null) {
                throw new InvalidArgumentException('Esta movimentação já foi estornada.');
            }

            $reversal = StockMovement::query()->create([
                'tenant_id' => $original->tenant_id,
                'warehouse_id' => $original->warehouse_id,
                'location_id' => $original->location_id,
                'product_id' => $original->product_id,
                'unit_id' => $original->unit_id,
                'stock_request_id' => $original->stock_request_id,
                'number' => 'EST-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4)),
                'movement_type' => $this->oppositeType((string) $original->movement_type),
                'reason' => 'reversal',
                'quantity' => $original->quantity,
                'unit_cost' => $original->unit_cost,
                'lot_number' => $original->lot_number,
                'expires_at' => $original->expires_at,
                'source_type' => StockMovement::class,
                'source_id' => $original->id,
                'performed_by' => auth()->id(),
                'occurred_at' => now(),
                'notes' => $reason ?: 'Estorno da movimentação ' . $original->number,
                'status' => 'draft',
                'reversal_of_id' => $original->id,
                'metadata' => ['reversal_reason' => $reason],
            ]);

            $this->post($reversal);

            $original->forceFill([
                'status' => 'reversed',
                'reversed_at' => now(),
            ])->saveQuietly();

            return $reversal->refresh();
        }, 5);
    }

    private function lockOrCreateBalance(StockMovement $movement): StockBalance
    {
        $query = StockBalance::query()
            ->where('warehouse_id', $movement->warehouse_id)
            ->where('product_id', $movement->product_id);

        $movement->location_id ? $query->where('location_id', $movement->location_id) : $query->whereNull('location_id');
        $movement->lot_number ? $query->where('lot_number', $movement->lot_number) : $query->whereNull('lot_number');

        $balance = $query->lockForUpdate()->first();

        if ($balance instanceof StockBalance) {
            return $balance;
        }

        try {
            StockBalance::query()->create([
                'tenant_id' => $movement->tenant_id,
                'warehouse_id' => $movement->warehouse_id,
                'location_id' => $movement->location_id,
                'product_id' => $movement->product_id,
                'unit_id' => $movement->unit_id,
                'lot_number' => $movement->lot_number,
                'expires_at' => $movement->expires_at,
                'quantity_on_hand' => 0,
                'quantity_reserved' => 0,
                'average_cost' => 0,
                'minimum_stock' => 0,
            ]);
        } catch (\Throwable) {
            // Outra transação pode ter criado o saldo entre a consulta e a inclusão.
        }

        return $query->lockForUpdate()->firstOrFail();
    }

    private function direction(string $type): string
    {
        if (in_array($type, self::IN_TYPES, true)) return 'in';
        if (in_array($type, self::OUT_TYPES, true)) return 'out';
        throw new InvalidArgumentException("Tipo de movimentação inválido: {$type}.");
    }

    private function oppositeType(string $type): string
    {
        return match ($this->direction($type)) {
            'in' => 'adjustment_out',
            'out' => 'adjustment_in',
        };
    }

    private function weightedAverage(string $currentQuantity, string $currentAverageCost, string $incomingQuantity, string $incomingUnitCost): string
    {
        $newQuantity = $this->add($currentQuantity, $incomingQuantity);
        if ($this->compare($newQuantity, '0') === 0) return '0.0000';

        $currentValue = $this->multiply($currentQuantity, $currentAverageCost);
        $incomingValue = $this->multiply($incomingQuantity, $incomingUnitCost);

        return $this->divide($this->add($currentValue, $incomingValue), $newQuantity);
    }

    private function positiveDecimal(string $value, string $message): string
    {
        $normalized = $this->normalize($value);
        if ($this->compare($normalized, '0') !== 1) throw new InvalidArgumentException($message);
        return $normalized;
    }

    private function nonNegativeDecimal(string $value): string
    {
        $normalized = $this->normalize($value);
        if ($this->compare($normalized, '0') === -1) throw new InvalidArgumentException('O custo unitário não pode ser negativo.');
        return $normalized;
    }

    private function normalize(string $value): string
    {
        $value = trim(str_replace(',', '.', $value));
        if (! is_numeric($value)) throw new InvalidArgumentException('Valor numérico inválido.');
        return number_format((float) $value, 4, '.', '');
    }

    private function add(string $a, string $b): string { return function_exists('bcadd') ? bcadd($a, $b, 4) : number_format((float)$a + (float)$b, 4, '.', ''); }
    private function sub(string $a, string $b): string { return function_exists('bcsub') ? bcsub($a, $b, 4) : number_format((float)$a - (float)$b, 4, '.', ''); }
    private function multiply(string $a, string $b): string { return function_exists('bcmul') ? bcmul($a, $b, 4) : number_format((float)$a * (float)$b, 4, '.', ''); }
    private function divide(string $a, string $b): string { return function_exists('bcdiv') ? bcdiv($a, $b, 4) : number_format((float)$a / (float)$b, 4, '.', ''); }
    private function compare(string $a, string $b): int { return function_exists('bccomp') ? bccomp($a, $b, 4) : ((float)$a <=> (float)$b); }
}
