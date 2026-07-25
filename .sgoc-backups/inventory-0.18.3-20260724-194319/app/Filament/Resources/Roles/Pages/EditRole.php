<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    /** @var array<int, string> */
    private array $permissionIds = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->permissionIds = array_values(array_filter(
            $this->form->getRawState()['permission_ids'] ?? []
        ));

        unset($data['permission_ids']);

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data): Model {
            $record->update($data);

            DB::table('access_control.role_permissions')
                ->where('role_id', $record->getKey())
                ->delete();

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

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Excluir')
                ->visible(fn (): bool => ! $this->record->is_system),
        ];
    }
}
