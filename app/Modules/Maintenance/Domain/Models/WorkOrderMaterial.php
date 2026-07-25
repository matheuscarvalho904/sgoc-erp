<?php

declare(strict_types=1);

namespace App\Modules\Maintenance\Domain\Models;

use App\Modules\Catalog\Domain\Models\Product;
use App\Modules\Catalog\Domain\Models\Unit;
use App\Shared\Models\TransactionModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WorkOrderMaterial extends TransactionModel
{
    protected $table = 'maintenance.work_order_materials';

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (self $material): void {
            $quantity = (float) ($material->quantity_applied ?: $material->quantity_requested ?: 0);
            $material->total_cost = $quantity * (float) ($material->unit_cost ?: 0);
        });

        static::saved(fn (self $material) => $material->workOrder?->recalculateActualCost());
        static::deleted(fn (self $material) => $material->workOrder?->recalculateActualCost());
    }

    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'quantity_requested' => 'decimal:4',
            'quantity_applied' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'total_cost' => 'decimal:4',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
