<?php

declare(strict_types=1);

namespace App\Filament\Resources\PurchaseCategories\Pages;

use App\Filament\Resources\PurchaseCategories\PurchaseCategoryResource;
use Filament\Resources\Pages\EditRecord;

final class EditPurchaseCategory extends EditRecord
{
    protected static string $resource = PurchaseCategoryResource::class;
}
