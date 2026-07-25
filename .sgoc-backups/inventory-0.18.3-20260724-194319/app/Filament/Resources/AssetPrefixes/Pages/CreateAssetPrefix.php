<?php

declare(strict_types=1);

namespace App\Filament\Resources\AssetPrefixes\Pages;

use App\Filament\Resources\AssetPrefixes\AssetPrefixResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\CreateRecord;

final class CreateAssetPrefix extends CreateRecord
{
    protected static string $resource = AssetPrefixResource::class;
}
