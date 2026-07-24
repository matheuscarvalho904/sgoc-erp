<?php

declare(strict_types=1);

namespace App\Filament\Resources\Assets\Pages;

use App\Filament\Resources\Assets\AssetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\EditRecord;

final class EditAsset extends EditRecord
{
    protected static string $resource = AssetResource::class;
}
