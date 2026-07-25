<?php

declare(strict_types=1);

namespace App\Modules\Maintenance\Domain\Models;

use App\Modules\Assets\Domain\Models\Asset;
use App\Shared\Models\TransactionModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class WorkOrder extends TransactionModel
{
    protected $table = 'maintenance.work_orders';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'opened_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'entry_hourmeter' => 'decimal:2',
            'exit_hourmeter' => 'decimal:2',
            'entry_odometer' => 'decimal:2',
            'exit_odometer' => 'decimal:2',
            'estimated_cost' => 'decimal:4',
            'actual_cost' => 'decimal:4',
            'metadata' => 'array',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function maintenanceType(): BelongsTo
    {
        return $this->belongsTo(MaintenanceType::class);
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(MaintenancePriority::class, 'priority_id');
    }

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(WorkOrderService::class, 'work_order_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(WorkOrderMaterial::class, 'work_order_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(WorkOrderEvent::class, 'work_order_id')->orderByDesc('occurred_at');
    }

    public function recalculateActualCost(): void
    {
        $services = (float) $this->services()->sum('total_cost');
        $materials = (float) $this->materials()->sum('total_cost');

        $this->forceFill(['actual_cost' => $services + $materials])->saveQuietly();
    }

    public function registerEvent(
        string $type,
        ?string $description = null,
        ?string $fromStatus = null,
        ?string $toStatus = null,
        array $data = [],
    ): WorkOrderEvent {
        return $this->events()->create([
            'user_id' => auth()->id(),
            'event_type' => $type,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'description' => $description,
            'data' => $data === [] ? null : $data,
            'occurred_at' => now(),
        ]);
    }
}
