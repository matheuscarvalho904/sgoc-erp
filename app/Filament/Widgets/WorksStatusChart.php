<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Modules\Foundation\Domain\Models\Work;
use Filament\Widgets\ChartWidget;

final class WorksStatusChart extends ChartWidget
{
    protected ?string $heading = 'Obras por Status';
    protected static ?int $sort = 20;
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $statuses = [
            'planning' => 'Planejamento',
            'mobilizing' => 'Mobilização',
            'in_progress' => 'Em andamento',
            'paused' => 'Paralisadas',
            'completed' => 'Concluídas',
            'cancelled' => 'Canceladas',
        ];

        $counts = Work::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'datasets' => [
                [
                    'label' => 'Obras',
                    'data' => collect(array_keys($statuses))
                        ->map(fn (string $status): int => (int) ($counts[$status] ?? 0))
                        ->all(),
                ],
            ],
            'labels' => array_values($statuses),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
