<?php
  declare(strict_types=1); namespace App\Filament\Resources\PaymentTerms\Pages; use App\Filament\Resources\PaymentTerms\PaymentTermResource; use Filament\Resources\Pages\ListRecords; use Filament\Actions\CreateAction;
  final class ListPaymentTerms extends ListRecords { protected static string $resource=PaymentTermResource::class; protected function getHeaderActions(): array { return [CreateAction::make()->label('Novo registro')->icon('heroicon-o-plus')]; } }
