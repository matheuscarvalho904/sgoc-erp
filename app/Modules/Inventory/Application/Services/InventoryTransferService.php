<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Services;

use App\Modules\Inventory\Domain\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class InventoryTransferService
{
    public function __construct(private readonly InventoryTransactionService $transactions) {}

    /** @return array{out: StockMovement, in: StockMovement} */
    public function transfer(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $group = (string) Str::uuid();
            $common = [
                'tenant_id' => $data['tenant_id'] ?? null,
                'product_id' => $data['product_id'],
                'unit_id' => $data['unit_id'] ?? null,
                'quantity' => $data['quantity'],
                'unit_cost' => $data['unit_cost'] ?? 0,
                'lot_number' => $data['lot_number'] ?? null,
                'expires_at' => $data['expires_at'] ?? null,
                'occurred_at' => $data['occurred_at'] ?? now(),
                'reason' => 'warehouse_transfer',
                'transfer_group_id' => $group,
                'notes' => $data['notes'] ?? null,
            ];

            $out = $this->transactions->createAndPost($common + [
                'warehouse_id' => $data['source_warehouse_id'],
                'location_id' => $data['source_location_id'] ?? null,
                'movement_type' => 'transfer_out',
            ]);

            $in = $this->transactions->createAndPost($common + [
                'warehouse_id' => $data['destination_warehouse_id'],
                'location_id' => $data['destination_location_id'] ?? null,
                'movement_type' => 'transfer_in',
                'unit_cost' => $out->unit_cost,
                'source_type' => StockMovement::class,
                'source_id' => $out->id,
            ]);

            $out->forceFill(['paired_movement_id' => $in->id])->saveQuietly();
            $in->forceFill(['paired_movement_id' => $out->id])->saveQuietly();
            return ['out' => $out->refresh(), 'in' => $in->refresh()];
        }, 5);
    }
}
