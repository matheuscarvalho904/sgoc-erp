<?php
  declare(strict_types=1); namespace App\Filament\Resources\Units\Pages; use App\Filament\Resources\Units\UnitResource; use Filament\Resources\Pages\ListRecords; use Filament\Actions\CreateAction;
  final class ListUnits extends ListRecords { protected static string $resource=UnitResource::class; protected function getHeaderActions(): array { return [CreateAction::make()->label('Novo registro')->icon('heroicon-o-plus')]; } }
