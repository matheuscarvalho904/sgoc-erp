<?php
namespace App\Filament\Resources\MaintenanceRequests\Pages;
use App\Filament\Resources\MaintenanceRequests\MaintenanceRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
final class EditMaintenanceRequest extends EditRecord { protected static string $resource = MaintenanceRequestResource::class; protected function getHeaderActions(): array { return [DeleteAction::make()->label('Excluir')]; } }
