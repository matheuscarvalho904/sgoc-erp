<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Modules\Foundation\Domain\Models\Branch;
use App\Modules\Foundation\Domain\Models\Company;
use App\Modules\Foundation\Domain\Models\CostCenter;
use App\Modules\Foundation\Domain\Models\Department;
use App\Modules\Foundation\Domain\Models\Work;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class ExecutiveOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Visão Geral';

    protected function getStats(): array
    {
        $activeWorks = Work::query()
            ->whereIn('status', ['planning', 'mobilizing', 'in_progress'])
            ->count();

        return [
            Stat::make('Empresas', Company::query()->count())
                ->description('Cadastros no ambiente')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),

            Stat::make('Filiais', Branch::query()->count())
                ->description('Estrutura operacional')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('info'),

            Stat::make('Obras ativas', $activeWorks)
                ->description('Planejamento e execução')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('success'),

            Stat::make('Departamentos', Department::query()->count())
                ->description('Estrutura administrativa')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),

            Stat::make('Centros de custo', CostCenter::query()->count())
                ->description('Controle gerencial')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('gray'),
        ];
    }

    protected function getColumns(): int
    {
        return 5;
    }
}
