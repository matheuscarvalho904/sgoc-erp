<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Modules\Foundation\Domain\Models\Work;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

final class RecentWorks extends TableWidget
{
    protected static ?string $heading = 'Obras Recentes';
    protected static ?int $sort = 30;
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Work::query()->latest('created_at')
            )
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Obra')
                    ->searchable()
                    ->weight('bold')
                    ->limit(35),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'planning' => 'Planejamento',
                        'mobilizing' => 'Mobilização',
                        'in_progress' => 'Em andamento',
                        'paused' => 'Paralisada',
                        'completed' => 'Concluída',
                        'cancelled' => 'Cancelada',
                        default => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('Cadastro')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->defaultPaginationPageOption(5);
    }
}
