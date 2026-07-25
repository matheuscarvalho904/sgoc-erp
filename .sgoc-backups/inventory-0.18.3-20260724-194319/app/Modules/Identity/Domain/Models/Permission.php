<?php

namespace App\Modules\Identity\Domain\Models;

use App\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Permission extends BaseModel
{
    protected $table = 'access_control.permissions';

    protected $fillable = [
        'code',
        'name',
        'module',
        'action',
        'description',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'is_system' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'access_control.role_permissions',
            'permission_id',
            'role_id'
        )->withPivot('granted')->withTimestamps();
    }
}