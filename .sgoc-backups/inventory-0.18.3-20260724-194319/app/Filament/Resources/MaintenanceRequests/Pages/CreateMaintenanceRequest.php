<?php
namespace App\Filament\Resources\MaintenanceRequests\Pages;
use App\Filament\Resources\MaintenanceRequests\MaintenanceRequestResource;
use Filament\Resources\Pages\CreateRecord;
final class CreateMaintenanceRequest extends CreateRecord { protected static string $resource = MaintenanceRequestResource::class; protected function mutateFormDataBeforeCreate(array $data): array { if (blank($data['number'] ?? null)) { $data['number'] = 'SM-'.str_pad((string) (\App\Modules\Maintenance\Domain\Models\MaintenanceRequest::query()->count() + 1), 6, '0', STR_PAD_LEFT); } return $data; } }
