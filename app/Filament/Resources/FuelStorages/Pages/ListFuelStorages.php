<?php

declare(strict_types=1);
namespace App\Filament\Resources\FuelStorages\Pages;
use App\Filament\Resources\FuelStorages\FuelStorageResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
final class ListFuelStorages extends ListRecords { protected static string $resource=FuelStorageResource::class; protected function getHeaderActions(): array { return [CreateAction::make()->label('Novo ponto')]; } }
