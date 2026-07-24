<?php
declare(strict_types=1);
namespace App\Filament\Resources\PurchaseOrderItems\Pages;
use App\Filament\Resources\PurchaseOrderItems\PurchaseOrderItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
final class ListPurchaseOrderItems extends ListRecords { protected static string $resource = PurchaseOrderItemResource::class; protected function getHeaderActions(): array { return [CreateAction::make()->label('Novo item')]; } }
