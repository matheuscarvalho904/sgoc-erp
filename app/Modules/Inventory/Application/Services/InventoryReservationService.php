<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Services;

use App\Modules\Inventory\Domain\Exceptions\ReservationException;
use App\Modules\Inventory\Domain\Models\Reservation;
use App\Modules\Inventory\Domain\Models\StockBalance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class InventoryReservationService
{
    public function reserve(
        string $warehouseId,
        string $productId,
        string $quantity,
        Model $reservable,
        ?string $locationId = null,
        ?string $lotNumber = null,
        ?string $stockRequestItemId = null,
        ?string $tenantId = null,
    ): Reservation {
        return DB::transaction(function () use ($warehouseId, $productId, $quantity, $reservable, $locationId, $lotNumber, $stockRequestItemId, $tenantId): Reservation {
            $qty = $this->positive($quantity);
            $balance = $this->lockBalance($warehouseId, $productId, $locationId, $lotNumber);
            $available = $this->sub((string) $balance->quantity_on_hand, (string) $balance->quantity_reserved);

            if ($this->compare($available, $qty) < 0) {
                throw new ReservationException("Saldo disponível insuficiente. Disponível: {$available}; solicitado: {$qty}.");
            }

            $balance->quantity_reserved = $this->add((string) $balance->quantity_reserved, $qty);
            $balance->save();

            return Reservation::query()->create([
                'tenant_id' => $tenantId,
                'warehouse_id' => $warehouseId,
                'stock_balance_id' => $balance->id,
                'product_id' => $productId,
                'stock_request_item_id' => $stockRequestItemId,
                'reservable_type' => $reservable::class,
                'reservable_id' => $reservable->getKey(),
                'quantity' => $qty,
                'quantity_consumed' => 0,
                'status' => 'active',
                'reserved_at' => now(),
            ]);
        }, 5);
    }

    public function release(Reservation $reservation): Reservation
    {
        return DB::transaction(function () use ($reservation): Reservation {
            $reservation = Reservation::query()->lockForUpdate()->findOrFail($reservation->id);
            if ($reservation->status !== 'active') throw new ReservationException('Somente reservas ativas podem ser liberadas.');

            $balance = StockBalance::query()->lockForUpdate()->findOrFail($reservation->stock_balance_id);
            $remaining = $this->sub((string) $reservation->quantity, (string) ($reservation->quantity_consumed ?? 0));
            $balance->quantity_reserved = $this->sub((string) $balance->quantity_reserved, $remaining);
            $balance->save();

            $reservation->forceFill(['status' => 'released', 'released_at' => now()])->save();
            return $reservation->refresh();
        }, 5);
    }

    public function consume(Reservation $reservation, string $quantity, InventoryTransactionService $transactions): Reservation
    {
        return DB::transaction(function () use ($reservation, $quantity, $transactions): Reservation {
            $reservation = Reservation::query()->lockForUpdate()->findOrFail($reservation->id);
            if ($reservation->status !== 'active') throw new ReservationException('A reserva não está ativa.');

            $qty = $this->positive($quantity);
            $remaining = $this->sub((string) $reservation->quantity, (string) ($reservation->quantity_consumed ?? 0));
            if ($this->compare($remaining, $qty) < 0) throw new ReservationException('A quantidade supera o saldo reservado.');

            $balance = StockBalance::query()->lockForUpdate()->findOrFail($reservation->stock_balance_id);
            $movement = $transactions->createAndPost([
                'tenant_id' => $reservation->tenant_id,
                'warehouse_id' => $reservation->warehouse_id,
                'location_id' => $balance->location_id,
                'product_id' => $reservation->product_id,
                'unit_id' => $balance->unit_id,
                'movement_type' => 'consumption_out',
                'reason' => 'reservation_consumption',
                'quantity' => $qty,
                'unit_cost' => $balance->average_cost,
                'lot_number' => $balance->lot_number,
                'source_type' => Reservation::class,
                'source_id' => $reservation->id,
                'occurred_at' => now(),
                'notes' => 'Consumo de material reservado.',
            ], reservedQuantityToRelease: $qty);

            $consumed = $this->add((string) ($reservation->quantity_consumed ?? 0), $qty);
            $completed = $this->compare($consumed, (string) $reservation->quantity) >= 0;
            $reservation->forceFill([
                'quantity_consumed' => $consumed,
                'status' => $completed ? 'consumed' : 'active',
                'consumed_at' => $completed ? now() : null,
                'consumption_movement_id' => $movement->id,
            ])->save();

            return $reservation->refresh();
        }, 5);
    }

    private function lockBalance(string $warehouseId, string $productId, ?string $locationId, ?string $lotNumber): StockBalance
    {
        $query = StockBalance::query()->where('warehouse_id', $warehouseId)->where('product_id', $productId);
        $locationId ? $query->where('location_id', $locationId) : $query->whereNull('location_id');
        $lotNumber ? $query->where('lot_number', $lotNumber) : $query->whereNull('lot_number');
        return $query->lockForUpdate()->firstOrFail();
    }

    private function positive(string $value): string { $v = number_format((float) str_replace(',', '.', $value), 4, '.', ''); if ($this->compare($v, '0') <= 0) throw new ReservationException('A quantidade deve ser maior que zero.'); return $v; }
    private function add(string $a,string $b): string { return function_exists('bcadd') ? bcadd($a,$b,4) : number_format((float)$a+(float)$b,4,'.',''); }
    private function sub(string $a,string $b): string { return function_exists('bcsub') ? bcsub($a,$b,4) : number_format((float)$a-(float)$b,4,'.',''); }
    private function compare(string $a,string $b): int { return function_exists('bccomp') ? bccomp($a,$b,4) : ((float)$a <=> (float)$b); }
}
