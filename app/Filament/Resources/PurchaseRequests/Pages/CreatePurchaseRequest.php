<?php

declare(strict_types=1);

namespace App\Filament\Resources\PurchaseRequests\Pages;

use App\Filament\Resources\PurchaseRequests\PurchaseRequestResource;
use Filament\Resources\Pages\CreateRecord;

final class CreatePurchaseRequest extends CreateRecord
{
    protected static string $resource = PurchaseRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['number'] ?? null)) {
            $year = now()->format('Y');
            $lastNumber = \App\Modules\Purchasing\Domain\Models\PurchaseRequest::query()
                ->where('tenant_id', $data['tenant_id'])
                ->where('number', 'like', "SC-{$year}-%")
                ->orderByDesc('number')
                ->value('number');

            $sequence = $lastNumber ? ((int) substr($lastNumber, -6)) + 1 : 1;
            $data['number'] = sprintf('SC-%s-%06d', $year, $sequence);
        }

        return $data;
    }

}
