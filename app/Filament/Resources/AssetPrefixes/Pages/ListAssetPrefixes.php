<?php

declare(strict_types=1);

namespace App\Filament\Resources\AssetPrefixes\Pages;

use App\Filament\Resources\AssetPrefixes\AssetPrefixResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListAssetPrefixes extends ListRecords
{
    protected static string $resource = AssetPrefixResource::class;

    protected function getHeaderActions(): array { return [CreateAction::make()->label('Novo')]; }
}
