<?php

declare(strict_types=1);
namespace App\Filament\Resources\Fuelings\Pages;
use App\Filament\Resources\Fuelings\FuelingResource;
use Filament\Resources\Pages\CreateRecord;
use App\Modules\Fuel\Application\Services\FuelTransactionService;
final class CreateFueling extends CreateRecord { protected static string $resource=FuelingResource::class; protected function afterCreate(): void { app(FuelTransactionService::class)->postFueling($this->record); } }
