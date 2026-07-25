<?php
namespace App\Filament\Resources\QuotationRequests\Pages;
use App\Filament\Resources\QuotationRequests\QuotationRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
final class EditQuotationRequest extends EditRecord { protected static string $resource = QuotationRequestResource::class; protected function getHeaderActions(): array { return [DeleteAction::make()->label('Excluir')]; } }
