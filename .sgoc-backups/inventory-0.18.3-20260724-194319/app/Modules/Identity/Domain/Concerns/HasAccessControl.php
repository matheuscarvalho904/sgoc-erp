<?php

namespace App\Modules\Identity\Domain\Concerns;

use App\Modules\Identity\Domain\Models\Permission;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\Identity\Domain\Models\UserRole;
use App\Modules\Identity\Domain\Models\UserScope;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

trait HasAccessControl
{
    public function roleAssignments(): HasMany
    {
        return $this->hasMany(UserRole::class, 'user_id');
    }

    public function scopes(): HasMany
    {
        return $this->hasMany(UserScope::class, 'user_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'access_control.user_roles',
            'user_id',
            'role_id'
        )
            ->withPivot([
                'tenant_id',
                'starts_at',
                'ends_at',
                'status',
            ])
            ->withTimestamps();
    }

    public function isSuperAdmin(?string $tenantId = null): bool
    {
        $query = DB::table('access_control.user_roles as ur')
            ->join('access_control.roles as r', 'r.id', '=', 'ur.role_id')
            ->where('ur.user_id', $this->getKey())
            ->where('ur.status', 'active')
            ->where('r.status', 'active')
            ->where('r.is_super_admin', true)
            ->where(function ($query): void {
                $query
                    ->whereNull('ur.starts_at')
                    ->orWhere('ur.starts_at', '<=', now());
            })
            ->where(function ($query): void {
                $query
                    ->whereNull('ur.ends_at')
                    ->orWhere('ur.ends_at', '>=', now());
            });

        if ($tenantId !== null) {
            $query->where('ur.tenant_id', $tenantId);
        }

        return $query->exists();
    }

    public function hasPermission(string $permissionCode, string $tenantId): bool
    {
        if ($this->isSuperAdmin($tenantId)) {
            return true;
        }

        $denied = DB::table('access_control.access_exceptions as ae')
            ->join('access_control.permissions as p', 'p.id', '=', 'ae.permission_id')
            ->where('ae.user_id', $this->getKey())
            ->where('ae.tenant_id', $tenantId)
            ->where('p.code', $permissionCode)
            ->where('ae.effect', 'deny')
            ->where('ae.status', 'active')
            ->where(function ($query): void {
                $query
                    ->whereNull('ae.starts_at')
                    ->orWhere('ae.starts_at', '<=', now());
            })
            ->where(function ($query): void {
                $query
                    ->whereNull('ae.ends_at')
                    ->orWhere('ae.ends_at', '>=', now());
            })
            ->exists();

        if ($denied) {
            return false;
        }

        $allowedByException = DB::table('access_control.access_exceptions as ae')
            ->join('access_control.permissions as p', 'p.id', '=', 'ae.permission_id')
            ->where('ae.user_id', $this->getKey())
            ->where('ae.tenant_id', $tenantId)
            ->where('p.code', $permissionCode)
            ->where('ae.effect', 'allow')
            ->where('ae.status', 'active')
            ->where(function ($query): void {
                $query
                    ->whereNull('ae.starts_at')
                    ->orWhere('ae.starts_at', '<=', now());
            })
            ->where(function ($query): void {
                $query
                    ->whereNull('ae.ends_at')
                    ->orWhere('ae.ends_at', '>=', now());
            })
            ->exists();

        if ($allowedByException) {
            return true;
        }

        return DB::table('access_control.user_roles as ur')
            ->join('access_control.roles as r', 'r.id', '=', 'ur.role_id')
            ->join('access_control.role_permissions as rp', 'rp.role_id', '=', 'r.id')
            ->join('access_control.permissions as p', 'p.id', '=', 'rp.permission_id')
            ->where('ur.user_id', $this->getKey())
            ->where('ur.tenant_id', $tenantId)
            ->where('ur.status', 'active')
            ->where('r.status', 'active')
            ->where('rp.granted', true)
            ->where('p.code', $permissionCode)
            ->where(function ($query): void {
                $query
                    ->whereNull('ur.starts_at')
                    ->orWhere('ur.starts_at', '<=', now());
            })
            ->where(function ($query): void {
                $query
                    ->whereNull('ur.ends_at')
                    ->orWhere('ur.ends_at', '>=', now());
            })
            ->exists();
    }
}