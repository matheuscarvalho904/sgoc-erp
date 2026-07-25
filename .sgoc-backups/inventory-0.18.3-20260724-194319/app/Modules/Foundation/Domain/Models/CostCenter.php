<?php

namespace App\Modules\Foundation\Domain\Models;

use App\Shared\Models\BaseModel;

final class CostCenter extends BaseModel
{
    protected $table = 'core.cost_centers';

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'company_id',
        'branch_id',
        'work_id',
        'parent_id',
        'code',
        'name',
        'description',
        'type',
        'status',
    ];
}