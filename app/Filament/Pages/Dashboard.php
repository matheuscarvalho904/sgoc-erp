<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\ExecutiveOverview;
use App\Filament\Widgets\FuelOverview;
use App\Filament\Widgets\FuelConsumptionChart;
use App\Filament\Widgets\RecentWorks;
use App\Filament\Widgets\WorksStatusChart;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;

final class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Painel de Controle';
    protected static ?string $navigationLabel = 'Painel de Controle';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = -100;

    public function getWidgets(): array
    {
        return [
            ExecutiveOverview::class,
            FuelOverview::class,
            FuelConsumptionChart::class,
            WorksStatusChart::class,
            RecentWorks::class,
        ];
    }

    public function getColumns(): int | array
    {
        return [
            'default' => 1,
            'xl' => 2,
        ];
    }
}
