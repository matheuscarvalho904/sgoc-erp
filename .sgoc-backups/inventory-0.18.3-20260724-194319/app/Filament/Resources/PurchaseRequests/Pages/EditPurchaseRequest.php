<?php

declare(strict_types=1);

namespace App\Filament\Resources\PurchaseRequests\Pages;

use App\Filament\Resources\PurchaseRequests\PurchaseRequestResource;
use Filament\Resources\Pages\EditRecord;

final class EditPurchaseRequest extends EditRecord
{
    protected static string $resource = PurchaseRequestResource::class;
}
