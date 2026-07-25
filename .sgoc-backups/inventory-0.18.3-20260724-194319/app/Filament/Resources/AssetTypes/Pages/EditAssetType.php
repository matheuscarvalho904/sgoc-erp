<?php

declare(strict_types=1);

namespace App\Filament\Resources\AssetTypes\Pages;

use App\Filament\Resources\AssetTypes\AssetTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\EditRecord;

final class EditAssetType extends EditRecord
{
    protected static string $resource = AssetTypeResource::class;
}
