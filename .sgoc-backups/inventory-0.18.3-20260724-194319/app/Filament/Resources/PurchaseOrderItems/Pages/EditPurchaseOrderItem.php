<?php
declare(strict_types=1);
namespace App\Filament\Resources\PurchaseOrderItems\Pages;
use App\Filament\Resources\PurchaseOrderItems\PurchaseOrderItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
final class EditPurchaseOrderItem extends EditRecord { protected static string $resource = PurchaseOrderItemResource::class; protected function getHeaderActions(): array { return [DeleteAction::make()->label('Excluir')]; } }
