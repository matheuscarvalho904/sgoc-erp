<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductClasses\Pages;

use App\Filament\Resources\ProductClasses\ProductClassResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListProductClasses extends ListRecords
{
    protected static string $resource = ProductClassResource::class;

    protected function getHeaderActions(): array { return [CreateAction::make()->label('Novo')]; }
}
