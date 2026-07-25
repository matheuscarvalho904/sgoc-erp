<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductClasses\Pages;

use App\Filament\Resources\ProductClasses\ProductClassResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\CreateRecord;

final class CreateProductClass extends CreateRecord
{
    protected static string $resource = ProductClassResource::class;
}
