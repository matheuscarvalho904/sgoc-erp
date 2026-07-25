<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    /** @var array<int, string> */
    private array $permissionIds = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->permissionIds = array_values(array_filter(
            $this->form->getRawState()['permission_ids'] ?? []
        ));

        unset($data['permission_ids']);

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data): Model {
            $record = static::getModel()::create($data);

            foreach ($this->permissionIds as $permissionId) {
                DB::table('access_control.role_permissions')->insert([
                    'role_id' => $record->getKey(),
                    'permission_id' => $permissionId,
                    'granted' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $record;
        });
    }
}
