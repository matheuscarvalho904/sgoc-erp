<?php

declare(strict_types=1);
namespace App\Filament\Widgets;
use App\Modules\Fuel\Domain\Models\{FuelAlert,Fueling,FuelStockBalance};
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
final class FuelOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Gestão de Combustíveis';
    protected function getStats(): array
    {
        $today = Fueling::query()->whereDate('fueled_at', today())->sum('quantity_liters');
        $month = Fueling::query()->whereBetween('fueled_at',[now()->startOfMonth(),now()->endOfMonth()])->sum('quantity_liters');
        $stock = FuelStockBalance::query()->sum('quantity_liters');
        $value = FuelStockBalance::query()->sum('total_value');
        $alerts = FuelAlert::query()->where('status','open')->count();
        return [
            Stat::make('Estoque atual', number_format((float)$stock,2,',','.').' L')->description('Todos os pontos')->color('info'),
            Stat::make('Consumo hoje', number_format((float)$today,2,',','.').' L')->description('Abastecimentos do dia')->color('primary'),
            Stat::make('Consumo no mês', number_format((float)$month,2,',','.').' L')->description(now()->translatedFormat('F/Y'))->color('success'),
            Stat::make('Valor em estoque', 'R$ '.number_format((float)$value,2,',','.'))->description('Custo médio ponderado')->color('warning'),
            Stat::make('Alertas abertos', $alerts)->description('Anomalias e estoque mínimo')->color($alerts > 0 ? 'danger' : 'success'),
        ];
    }
    protected function getColumns(): int { return 5; }
}
