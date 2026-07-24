<?php
namespace App\Filament\Resources\QuotationRequests\Pages;
use App\Filament\Resources\QuotationRequests\QuotationRequestResource;
use Filament\Resources\Pages\CreateRecord;
final class CreateQuotationRequest extends CreateRecord { protected static string $resource = QuotationRequestResource::class; protected function mutateFormDataBeforeCreate(array $data): array { $data['number'] ??= 'COT-'.now()->format('Y').'-'.str_pad((string)(\App\Modules\Purchasing\Domain\Models\QuotationRequest::query()->count()+1),6,'0',STR_PAD_LEFT); return $data; } }
