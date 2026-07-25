<?php

namespace App\Modules\Foundation\Domain\Models;

use App\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Organization extends BaseModel
{
    protected $table = 'core.organizations';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'legal_name',
        'document',
        'status',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'settings' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'organization_id');
    }
}