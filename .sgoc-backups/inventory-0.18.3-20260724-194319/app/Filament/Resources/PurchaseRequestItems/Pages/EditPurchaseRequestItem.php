<?php

declare(strict_types=1);

namespace App\Filament\Resources\PurchaseRequestItems\Pages;

use App\Filament\Resources\PurchaseRequestItems\PurchaseRequestItemResource;
use Filament\Resources\Pages\EditRecord;

final class EditPurchaseRequestItem extends EditRecord
{
    protected static string $resource = PurchaseRequestItemResource::class;
}
