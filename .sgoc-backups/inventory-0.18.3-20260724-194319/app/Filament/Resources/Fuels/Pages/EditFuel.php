<?php

declare(strict_types=1);

namespace App\Filament\Resources\Fuels\Pages;

use App\Filament\Resources\Fuels\FuelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\EditRecord;

final class EditFuel extends EditRecord
{
    protected static string $resource = FuelResource::class;
}
