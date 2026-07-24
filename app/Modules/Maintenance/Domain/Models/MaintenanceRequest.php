<?php

declare(strict_types=1);

namespace App\Modules\Maintenance\Domain\Models;

use App\Models\User;
use App\Modules\Assets\Domain\Models\Asset;
use App\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MaintenanceRequest extends BaseModel
{
    protected $table = 'maintenance.requests';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'requested_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'hourmeter' => 'decimal:2',
            'odometer' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
    public function priority(): BelongsTo { return $this->belongsTo(MaintenancePriority::class, 'priority_id'); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requester_id'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewer_id'); }
    public function workOrder(): BelongsTo { return $this->belongsTo(WorkOrder::class); }
}
