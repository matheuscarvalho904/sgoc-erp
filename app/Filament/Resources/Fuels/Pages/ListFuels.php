<?php

declare(strict_types=1);

namespace App\Filament\Resources\Fuels\Pages;

use App\Filament\Resources\Fuels\FuelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListFuels extends ListRecords
{
    protected static string $resource = FuelResource::class;

    protected function getHeaderActions(): array { return [CreateAction::make()->label('Novo')]; }
}
