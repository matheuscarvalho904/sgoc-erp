<?php

declare(strict_types=1);

namespace App\Filament\Resources\AssetTypes\Pages;

use App\Filament\Resources\AssetTypes\AssetTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\CreateRecord;

final class CreateAssetType extends CreateRecord
{
    protected static string $resource = AssetTypeResource::class;
}
