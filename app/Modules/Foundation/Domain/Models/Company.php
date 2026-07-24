<?php

namespace App\Modules\Foundation\Domain\Models;

use App\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Company extends BaseModel
{
    protected $table = 'core.companies';

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'code',
        'name',
        'legal_name',
        'document',
        'state_registration',
        'municipal_registration',
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

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class, 'company_id');
    }
}