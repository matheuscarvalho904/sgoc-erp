<?php

namespace App\Modules\Foundation\Domain\Models;

use App\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Tenant extends BaseModel
{
    protected $table = 'core.tenants';

    protected $fillable = [
        'name',
        'slug',
        'legal_name',
        'document',
        'email',
        'phone',
        'timezone',
        'currency',
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

    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'tenant_id');
    }
}