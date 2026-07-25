<?php
namespace App\Filament\Resources\QuotationRequests\Pages;
use App\Filament\Resources\QuotationRequests\QuotationRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
final class ListQuotationRequests extends ListRecords { protected static string $resource = QuotationRequestResource::class; protected function getHeaderActions(): array { return [CreateAction::make()->label('Nova cotação')]; } }
