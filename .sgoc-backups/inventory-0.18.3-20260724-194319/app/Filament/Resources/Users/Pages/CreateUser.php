<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Modules\Foundation\Domain\Models\Tenant;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /** @var array<int, string> */
    private array $roleIds = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->roleIds = array_values(array_filter(
            $this->form->getRawState()['role_ids'] ?? []
        ));

        unset($data['role_ids'], $data['password_confirmation']);

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data): Model {
            $record = static::getModel()::create($data);
            $tenantId = Tenant::query()->value('id');

            foreach ($this->roleIds as $roleId) {
                DB::table('access_control.user_roles')->insert([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'tenant_id' => $tenantId,
                    'user_id' => $record->getKey(),
                    'role_id' => $roleId,
                    'status' => 'active',
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $record;
        });
    }
}
