<?php

declare(strict_types=1);

namespace App\Filament\Resources\WorkOrders\Pages;

use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Modules\Maintenance\Domain\Models\WorkOrder;
use Filament\Resources\Pages\EditRecord;

final class EditWorkOrder extends EditRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected function afterSave(): void
    {
        /** @var WorkOrder $record */
        $record = $this->record;
        $record->recalculateActualCost();
        $record->registerEvent('updated', 'Ordem de serviço atualizada.');
    }
}
