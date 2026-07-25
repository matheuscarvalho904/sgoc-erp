<?php

namespace App\Modules\Foundation\Domain\Models;

use App\Shared\Models\BaseModel;

final class Department extends BaseModel
{
    protected $table = 'core.departments';

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'company_id',
        'branch_id',
        'parent_id',
        'code',
        'name',
        'description',
        'status',
    ];
}