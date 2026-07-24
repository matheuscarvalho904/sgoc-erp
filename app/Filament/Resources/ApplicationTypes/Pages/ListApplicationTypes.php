<?php
namespace App\Filament\Resources\ApplicationTypes\Pages;
use App\Filament\Resources\ApplicationTypes\ApplicationTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
final class ListApplicationTypes extends ListRecords { protected static string $resource = ApplicationTypeResource::class; protected function getHeaderActions(): array { return [CreateAction::make()->label('Novo tipo de aplicação')]; } }
