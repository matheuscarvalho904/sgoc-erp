<?php

declare(strict_types=1);

namespace App\Filament\Resources\PurchaseCategories\Pages;

use App\Filament\Resources\PurchaseCategories\PurchaseCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListPurchaseCategories extends ListRecords
{
    protected static string $resource = PurchaseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nova categoria')
                ->icon('heroicon-o-plus'),
        ];
    }
}
