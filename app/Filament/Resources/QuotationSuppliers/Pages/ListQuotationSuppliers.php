<?php
namespace App\Filament\Resources\QuotationSuppliers\Pages;
use App\Filament\Resources\QuotationSuppliers\QuotationSupplierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
final class ListQuotationSuppliers extends ListRecords { protected static string $resource = QuotationSupplierResource::class; protected function getHeaderActions(): array { return [CreateAction::make()->label('Registrar proposta')]; } }
