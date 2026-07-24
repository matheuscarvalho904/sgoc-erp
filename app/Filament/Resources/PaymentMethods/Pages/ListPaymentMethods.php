<?php
  declare(strict_types=1); namespace App\Filament\Resources\PaymentMethods\Pages; use App\Filament\Resources\PaymentMethods\PaymentMethodResource; use Filament\Resources\Pages\ListRecords; use Filament\Actions\CreateAction;
  final class ListPaymentMethods extends ListRecords { protected static string $resource=PaymentMethodResource::class; protected function getHeaderActions(): array { return [CreateAction::make()->label('Novo registro')->icon('heroicon-o-plus')]; } }
