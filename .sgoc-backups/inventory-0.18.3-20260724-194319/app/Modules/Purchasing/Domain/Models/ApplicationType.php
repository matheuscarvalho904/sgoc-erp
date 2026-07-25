<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Domain\Models;

use App\Modules\Foundation\Domain\Models\Tenant;
use App\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ApplicationType extends BaseModel
{
    protected $table = 'purchasing.application_types';

    protected $fillable = [
        'tenant_id', 'code', 'name', 'description', 'target_kind',
        'requires_target', 'allows_allocation', 'measurement_effect', 'status', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'requires_target' => 'boolean',
            'allows_allocation' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
