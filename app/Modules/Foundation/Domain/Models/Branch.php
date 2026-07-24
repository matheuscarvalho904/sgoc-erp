<?php

namespace App\Modules\Foundation\Domain\Models;

use App\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Branch extends BaseModel
{
    protected $table = 'core.branches';

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'company_id',
        'code',
        'name',
        'document',
        'email',
        'phone',
        'zip_code',
        'street',
        'number',
        'complement',
        'district',
        'city',
        'state',
        'is_headquarters',
        'status',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'is_headquarters' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function works(): HasMany
    {
        return $this->hasMany(Work::class, 'branch_id');
    }
}