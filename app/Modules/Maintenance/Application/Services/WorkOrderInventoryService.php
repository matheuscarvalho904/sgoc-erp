<?php

declare(strict_types=1);

namespace App\Modules\Maintenance\Application\Services;

use App\Modules\Inventory\Application\Services\InventoryReservationService;
use App\Modules\Inventory\Application\Services\InventoryTransactionService;
use App\Modules\Inventory\Domain\Models\Reservation;
use App\Modules\Maintenance\Domain\Models\WorkOrderMaterial;
use Illuminate\Support\Facades\DB;

final class WorkOrderInventoryService
{
    public function __construct(
        private readonly InventoryReservationService $reservations,
        private readonly InventoryTransactionService $transactions,
    ) {}

    public function reserve(WorkOrderMaterial $material, string $warehouseId): Reservation
    {
        $reservation = $this->reservations->reserve(
            warehouseId: $warehouseId,
            productId: (string) $material->product_id,
            quantity: (string) $material->quantity_requested,
            reservable: $material,
            tenantId: $material->workOrder?->tenant_id,
        );

        $material->forceFill([
            'warehouse_id' => $warehouseId,
            'reservation_id' => $reservation->id,
            'quantity_reserved' => $reservation->quantity,
            'status' => 'reserved',
        ])->save();

        return $reservation;
    }

    public function consume(WorkOrderMaterial $material, string $quantity): WorkOrderMaterial
    {
        return DB::transaction(function () use ($material, $quantity): WorkOrderMaterial {
            $reservation = Reservation::query()->findOrFail($material->reservation_id);
            $reservation = $this->reservations->consume($reservation, $quantity, $this->transactions);
            $material->quantity_applied = ((float) $material->quantity_applied) + (float) $quantity;
            $material->unit_cost = $reservation->stockBalance?->average_cost ?? $material->unit_cost;
            $material->status = $reservation->status === 'consumed' ? 'applied' : 'partially_applied';
            $material->save();
            return $material->refresh();
        }, 5);
    }
}
