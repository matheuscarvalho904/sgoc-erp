<?php

declare(strict_types=1);
namespace App\Filament\Widgets;
use App\Modules\Fuel\Domain\Models\Fueling;
use Filament\Widgets\ChartWidget;
final class FuelConsumptionChart extends ChartWidget
{
    protected ?string $heading = 'Consumo diário — últimos 14 dias';
    protected function getData(): array
    {
        $labels=[]; $values=[];
        for ($i=13;$i>=0;$i--) { $day=today()->subDays($i); $labels[]=$day->format('d/m'); $values[]=(float)Fueling::query()->whereDate('fueled_at',$day)->sum('quantity_liters'); }
        return ['datasets'=>[['label'=>'Litros','data'=>$values]],'labels'=>$labels];
    }
    protected function getType(): string { return 'line'; }
}
