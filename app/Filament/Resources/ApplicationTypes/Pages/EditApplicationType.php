<?php
namespace App\Filament\Resources\ApplicationTypes\Pages;
use App\Filament\Resources\ApplicationTypes\ApplicationTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
final class EditApplicationType extends EditRecord { protected static string $resource = ApplicationTypeResource::class; protected function getHeaderActions(): array { return [DeleteAction::make()]; } }
