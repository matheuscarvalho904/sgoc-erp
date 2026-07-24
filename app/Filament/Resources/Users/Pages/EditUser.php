<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Modules\Foundation\Domain\Models\Tenant;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /** @var array<int, string> */
    private array $roleIds = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->roleIds = array_values(array_filter(
            $this->form->getRawState()['role_ids'] ?? []
        ));

        unset($data['role_ids'], $data['password_confirmation']);

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data): Model {
            $record->update($data);

            $tenantId = Tenant::query()->value('id');

            DB::table('access_control.user_roles')
                ->where('tenant_id', $tenantId)
                ->where('user_id', $record->getKey())
                ->delete();

            foreach ($this->roleIds as $roleId) {
                DB::table('access_control.user_roles')->insert([
                    'id' => (string) Str::uuid(),
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

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Excluir'),
        ];
    }
}
