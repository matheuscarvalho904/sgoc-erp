<?php
namespace App\Filament\Resources\QuotationSuppliers\Pages;
use App\Filament\Resources\QuotationSuppliers\QuotationSupplierResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
final class EditQuotationSupplier extends EditRecord { protected static string $resource = QuotationSupplierResource::class; protected function getHeaderActions(): array { return [DeleteAction::make()->label('Excluir')]; } }
