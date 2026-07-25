<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Application\Services;

use App\Modules\Assets\Domain\Models\Asset;
use App\Modules\Fuel\Domain\Models\{FuelEntry, Fueling, FuelPump, FuelStockBalance, FuelStockMovement, FuelStorage};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class FuelTransactionService
{
    public function postEntry(FuelEntry $entry): void
    {
        DB::transaction(function () use ($entry): void {
            if ((float) $entry->quantity_liters <= 0 || (float) $entry->unit_cost <= 0) {
                throw ValidationException::withMessages(['quantity_liters' => 'Quantidade e custo unitário devem ser maiores que zero.']);
            }
            $balance = FuelStockBalance::query()->lockForUpdate()->firstOrCreate(
                ['storage_id' => $entry->storage_id, 'fuel_id' => $entry->fuel_id],
                ['tenant_id' => $entry->tenant_id, 'quantity_liters' => 0, 'average_cost' => 0, 'total_value' => 0]
            );

            $oldQty = (float) $balance->quantity_liters;
            $oldValue = (float) $balance->total_value;
            $qty = (float) $entry->quantity_liters;
            $value = (float) $entry->total_cost;
            $newQty = $oldQty + $qty;
            $newValue = $oldValue + $value;

            $balance->update([
                'quantity_liters' => $newQty,
                'total_value' => $newValue,
                'average_cost' => $newQty > 0 ? $newValue / $newQty : 0,
                'last_movement_at' => $entry->received_at,
            ]);

            FuelStockMovement::query()->create([
                'tenant_id' => $entry->tenant_id,
                'storage_id' => $entry->storage_id,
                'fuel_id' => $entry->fuel_id,
                'number' => 'MC-' . now()->format('YmdHisv'),
                'movement_type' => 'entry',
                'quantity_liters' => $qty,
                'unit_cost' => $entry->unit_cost,
                'total_cost' => $value,
                'source_type' => FuelEntry::class,
                'source_id' => $entry->id,
                'performed_by' => $entry->received_by,
                'occurred_at' => $entry->received_at,
                'notes' => $entry->notes,
            ]);
        });
    }

    public function postFueling(Fueling $fueling): void
    {
        DB::transaction(function () use ($fueling): void {
            $storage = FuelStorage::query()->findOrFail($fueling->storage_id);
            if ($storage->status !== 'active') { throw ValidationException::withMessages(['storage_id' => 'O ponto de combustível está inativo.']); }
            if ($storage->default_fuel_id && $storage->default_fuel_id !== $fueling->fuel_id) { throw ValidationException::withMessages(['fuel_id' => 'Combustível incompatível com o ponto selecionado.']); }
            if ($fueling->pump_id) { $pump = FuelPump::query()->find($fueling->pump_id); if (! $pump || $pump->status !== 'active') { throw ValidationException::withMessages(['pump_id' => 'A bomba está inativa ou indisponível.']); } }
            if ((float) $fueling->quantity_liters <= 0) { throw ValidationException::withMessages(['quantity_liters' => 'A quantidade deve ser maior que zero.']); }
            if ($fueling->meter_reading !== null && $fueling->previous_meter_reading !== null && (float)$fueling->meter_reading < (float)$fueling->previous_meter_reading) { throw ValidationException::withMessages(['meter_reading' => 'A leitura atual não pode ser menor que a leitura anterior.']); }
            $duplicate = Fueling::query()->where('asset_id',$fueling->asset_id)->where('storage_id',$fueling->storage_id)->where('quantity_liters',$fueling->quantity_liters)->whereBetween('fueled_at',[$fueling->fueled_at->copy()->subMinutes(2),$fueling->fueled_at->copy()->addMinutes(2)])->whereKeyNot($fueling->id)->exists();
            if ($duplicate) { throw ValidationException::withMessages(['quantity_liters' => 'Possível abastecimento duplicado detectado.']); }
            $balance = FuelStockBalance::query()->lockForUpdate()
                ->where('storage_id', $fueling->storage_id)
                ->where('fuel_id', $fueling->fuel_id)
                ->first();

            $available = (float) ($balance?->quantity_liters ?? 0);
            $qty = (float) $fueling->quantity_liters;
            if ($available < $qty) {
                throw ValidationException::withMessages(['quantity_liters' => 'Saldo insuficiente no ponto de abastecimento.']);
            }

            $unitCost = (float) $balance->average_cost;
            $totalCost = $qty * $unitCost;
            $balance->update([
                'quantity_liters' => $available - $qty,
                'total_value' => max(0, (float) $balance->total_value - $totalCost),
                'last_movement_at' => $fueling->fueled_at,
            ]);

            $previous = $fueling->previous_meter_reading !== null ? (float) $fueling->previous_meter_reading : null;
            $current = $fueling->meter_reading !== null ? (float) $fueling->meter_reading : null;
            $delta = ($previous !== null && $current !== null && $current >= $previous) ? $current - $previous : null;
            $consumption = ($delta !== null && $delta > 0 && $qty > 0)
                ? ($fueling->meter_type === 'odometer' ? $delta / $qty : $qty / $delta)
                : null;

            $fueling->update([
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'distance_or_hours' => $delta,
                'calculated_consumption' => $consumption,
            ]);

            $asset = Asset::query()->find($fueling->asset_id);
            if ($asset && $current !== null) {
                $asset->update($fueling->meter_type === 'odometer'
                    ? ['current_odometer' => $current]
                    : ($fueling->meter_type === 'hourmeter' ? ['current_hourmeter' => $current] : []));
            }

            FuelStockMovement::query()->create([
                'tenant_id' => $fueling->tenant_id,
                'storage_id' => $fueling->storage_id,
                'fuel_id' => $fueling->fuel_id,
                'number' => 'MC-' . now()->format('YmdHisv'),
                'movement_type' => 'fueling',
                'quantity_liters' => -$qty,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'source_type' => Fueling::class,
                'source_id' => $fueling->id,
                'performed_by' => $fueling->performed_by,
                'occurred_at' => $fueling->fueled_at,
                'notes' => $fueling->notes,
            ]);

            app(FuelAlertService::class)->evaluate($fueling->fresh());
        });
    }
}
