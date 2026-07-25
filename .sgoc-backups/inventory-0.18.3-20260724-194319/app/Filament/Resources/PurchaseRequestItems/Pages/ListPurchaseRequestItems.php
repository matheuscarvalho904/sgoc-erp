<?php

declare(strict_types=1);

namespace App\Filament\Resources\PurchaseRequestItems\Pages;

use App\Filament\Resources\PurchaseRequestItems\PurchaseRequestItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListPurchaseRequestItems extends ListRecords
{
    protected static string $resource = PurchaseRequestItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo item')
                ->icon('heroicon-o-plus'),
        ];
    }
}
