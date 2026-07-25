<?php

declare(strict_types=1);
namespace App\Filament\Resources\FuelEntries\Pages;
use App\Filament\Resources\FuelEntries\FuelEntryResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
final class ListFuelEntries extends ListRecords { protected static string $resource=FuelEntryResource::class; protected function getHeaderActions(): array { return [CreateAction::make()->label('Nova entrada')]; } }
