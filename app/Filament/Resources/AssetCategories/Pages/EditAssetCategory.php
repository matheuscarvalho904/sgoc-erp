<?php

declare(strict_types=1);

namespace App\Filament\Resources\AssetCategories\Pages;

use App\Filament\Resources\AssetCategories\AssetCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\EditRecord;

final class EditAssetCategory extends EditRecord
{
    protected static string $resource = AssetCategoryResource::class;
}
