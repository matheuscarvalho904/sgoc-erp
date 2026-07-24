<?php

declare(strict_types=1);

namespace App\Filament\Resources\WorkOrders\Pages;

use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Modules\Maintenance\Domain\Models\WorkOrder;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

final class CreateWorkOrder extends CreateRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenantId = $data['tenant_id'] ?? null;

        $data['number'] = DB::transaction(function () use ($tenantId): string {
            $lastNumber = WorkOrder::query()
                ->where('tenant_id', $tenantId)
                ->lockForUpdate()
                ->orderByDesc('created_at')
                ->value('number');

            $sequence = $lastNumber && preg_match('/(\d+)$/', $lastNumber, $matches)
                ? ((int) $matches[1] + 1)
                : 1;

            return 'OS-'.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
        });

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var WorkOrder $record */
        $record = $this->record;
        $record->recalculateActualCost();
        $record->registerEvent('created', 'Ordem de serviço criada.', null, $record->status);
    }
}
