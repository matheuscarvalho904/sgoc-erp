<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuotationItems\Pages;

use App\Filament\Resources\QuotationItems\QuotationItemResource;
use App\Modules\Purchasing\Domain\Models\PurchaseRequestItem;
use Filament\Resources\Pages\CreateRecord;

final class CreateQuotationItem extends CreateRecord
{
    protected static string $resource = QuotationItemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['quantity'] ?? null) && filled($data['purchase_request_item_id'] ?? null)) {
            $data['quantity'] = PurchaseRequestItem::query()->find($data['purchase_request_item_id'])?->quantity;
        }

        return $data;
    }
}
