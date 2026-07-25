<?php
  declare(strict_types=1); namespace App\Filament\Resources\Brands\Pages; use App\Filament\Resources\Brands\BrandResource; use Filament\Resources\Pages\ListRecords; use Filament\Actions\CreateAction;
  final class ListBrands extends ListRecords { protected static string $resource=BrandResource::class; protected function getHeaderActions(): array { return [CreateAction::make()->label('Novo registro')->icon('heroicon-o-plus')]; } }
