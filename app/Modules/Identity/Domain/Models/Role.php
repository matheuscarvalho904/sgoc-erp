<?php

namespace App\Modules\Identity\Domain\Models;

use App\Modules\Foundation\Domain\Models\Tenant;
use App\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Role extends BaseModel
{
    protected $table = 'access_control.roles';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'is_system',
        'is_super_admin',
        'status',
    ];

    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'is_system' => 'boolean',
            'is_super_admin' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'access_control.role_permissions',
            'role_id',
            'permission_id'
        )->withPivot('granted')->withTimestamps();
    }

    public function userAssignments(): HasMany
    {
        return $this->hasMany(UserRole::class, 'role_id');
    }
}