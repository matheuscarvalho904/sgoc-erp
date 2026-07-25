<?php

namespace App\Modules\Foundation\Domain\Models;

use App\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Work extends BaseModel
{
    protected $table = 'core.works';

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'company_id',
        'branch_id',
        'code',
        'name',
        'description',
        'client_name',
        'contract_number',
        'start_date',
        'expected_end_date',
        'actual_end_date',
        'budget_amount',
        'zip_code',
        'street',
        'number',
        'complement',
        'district',
        'city',
        'state',
        'latitude',
        'longitude',
        'status',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'start_date' => 'date',
            'expected_end_date' => 'date',
            'actual_end_date' => 'date',
            'budget_amount' => 'decimal:4',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'settings' => 'array',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}