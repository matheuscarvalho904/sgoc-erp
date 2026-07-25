<?php

declare(strict_types=1);

namespace App\Modules\Maintenance\Domain\Models;

use App\Shared\Models\TransactionModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WorkOrderService extends TransactionModel
{
    protected $table = 'maintenance.work_order_services';

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (self $service): void {
            $hours = (float) ($service->actual_hours ?: $service->estimated_hours ?: 0);
            $service->total_cost = $hours * (float) ($service->hourly_rate ?: 0);
        });

        static::saved(fn (self $service) => $service->workOrder?->recalculateActualCost());
        static::deleted(fn (self $service) => $service->workOrder?->recalculateActualCost());
    }

    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'estimated_hours' => 'decimal:2',
            'actual_hours' => 'decimal:2',
            'hourly_rate' => 'decimal:4',
            'total_cost' => 'decimal:4',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }
}
