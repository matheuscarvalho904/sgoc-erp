<?php

declare(strict_types=1);
namespace App\Filament\Resources\FuelEntries\Pages;
use App\Filament\Resources\FuelEntries\FuelEntryResource;
use Filament\Resources\Pages\CreateRecord;
use App\Modules\Fuel\Application\Services\FuelTransactionService;
final class CreateFuelEntry extends CreateRecord { protected static string $resource=FuelEntryResource::class; protected function afterCreate(): void { app(FuelTransactionService::class)->postEntry($this->record); } }
