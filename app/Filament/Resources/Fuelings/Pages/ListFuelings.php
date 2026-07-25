<?php

declare(strict_types=1);
namespace App\Filament\Resources\Fuelings\Pages;
use App\Filament\Resources\Fuelings\FuelingResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
final class ListFuelings extends ListRecords { protected static string $resource=FuelingResource::class; protected function getHeaderActions(): array { return [CreateAction::make()->label('Novo abastecimento')]; } }
