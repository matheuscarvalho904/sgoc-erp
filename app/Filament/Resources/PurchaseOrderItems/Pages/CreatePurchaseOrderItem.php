<?php
declare(strict_types=1);
namespace App\Filament\Resources\PurchaseOrderItems\Pages;
use App\Filament\Resources\PurchaseOrderItems\PurchaseOrderItemResource;
use Filament\Resources\Pages\CreateRecord;
final class CreatePurchaseOrderItem extends CreateRecord { protected static string $resource = PurchaseOrderItemResource::class; }
