<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockMovements\Pages;

use App\Filament\Resources\StockMovements\StockMovementResource;
use App\Modules\Inventory\Application\Services\InventoryTransactionService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

final class CreateStockMovement extends CreateRecord
{
    protected static string $resource = StockMovementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = 'draft';
        $data['performed_by'] = auth()->id();
        return $data;
    }

    protected function afterCreate(): void
    {
        try {
            app(InventoryTransactionService::class)->post($this->record);
            Notification::make()->title('Movimentação processada')->body('O saldo foi atualizado com segurança.')->success()->send();
        } catch (\Throwable $exception) {
            $this->record->delete();
            throw ValidationException::withMessages(['quantity' => $exception->getMessage()]);
        }
    }
}
