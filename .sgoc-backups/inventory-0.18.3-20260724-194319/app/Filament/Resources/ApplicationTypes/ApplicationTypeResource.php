<?php

declare(strict_types=1);

namespace App\Filament\Resources\ApplicationTypes;

use App\Filament\Resources\ApplicationTypes\Pages\{CreateApplicationType, EditApplicationType, ListApplicationTypes};
use App\Modules\Foundation\Domain\Models\Tenant;
use App\Modules\Purchasing\Domain\Models\ApplicationType;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\{Hidden, Select, Textarea, TextInput, Toggle};
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\{IconColumn, TextColumn};
use Filament\Tables\Table;
use UnitEnum;

final class ApplicationTypeResource extends Resource
{
    protected static ?string $model = ApplicationType::class;
    protected static ?string $modelLabel = 'tipo de aplicação';
    protected static ?string $pluralModelLabel = 'tipos de aplicação';
    protected static ?string $navigationLabel = 'Tipos de aplicação';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrows-pointing-out';
    protected static string|UnitEnum|null $navigationGroup = 'Cadastros Gerais';
    protected static ?int $navigationSort = 70;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Hidden::make('tenant_id')->default(fn () => Tenant::query()->value('id'))->required(),
            Section::make('Configuração da aplicação')->columnSpanFull()->columns(['default' => 1, 'md' => 2, 'xl' => 4])->schema([
                TextInput::make('code')->label('Código')->required()->maxLength(20),
                TextInput::make('name')->label('Nome')->required()->maxLength(120)->columnSpan(['default' => 1, 'xl' => 2]),
                TextInput::make('sort_order')->label('Ordem')->numeric()->default(0),
                Select::make('target_kind')->label('Destino associado')->options([
                    'cost_application' => 'Centro de aplicação', 'asset' => 'Equipamento/Ativo',
                    'subcontractor' => 'Subempreiteiro', 'asset_subcontractor' => 'Equipamento/Subempreiteiro',
                    'work' => 'Obra', 'cost_center' => 'Centro de custo', 'department' => 'Departamento',
                    'warehouse' => 'Almoxarifado', 'service_order' => 'Ordem de serviço', 'contract' => 'Contrato',
                    'measurement' => 'Medição', 'production' => 'Produção', 'budget_service' => 'Serviço/Composição',
                    'work_front' => 'Frente de serviço', 'laboratory' => 'Laboratório', 'patrimony' => 'Patrimônio',
                    'manual' => 'Manual/Outro',
                ])->required()->searchable(),
                Select::make('measurement_effect')->label('Efeito na medição')->options([
                    'none' => 'Sem efeito', 'deduct' => 'Deduzir', 'add' => 'Adicionar', 'deduct_add' => 'Deduzir e adicionar',
                ])->default('none')->required(),
                Toggle::make('requires_target')->label('Exige destino associado')->default(true),
                Toggle::make('allows_allocation')->label('Permite rateio')->default(true),
                Select::make('status')->label('Status')->options(['active' => 'Ativo', 'inactive' => 'Inativo'])->default('active')->required(),
                Textarea::make('description')->label('Descrição e regras')->rows(3)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Código')->searchable()->sortable(),
            TextColumn::make('name')->label('Aplicação')->searchable()->sortable(),
            TextColumn::make('target_kind')->label('Destino')->formatStateUsing(fn (string $state) => str_replace('_', ' ', ucfirst($state))),
            TextColumn::make('measurement_effect')->label('Medição')->badge()->formatStateUsing(fn (string $state) => match ($state) {
                'deduct' => 'Deduzir', 'add' => 'Adicionar', 'deduct_add' => 'Deduzir e adicionar', default => 'Sem efeito',
            }),
            IconColumn::make('allows_allocation')->label('Rateio')->boolean(),
            TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn (string $state) => $state === 'active' ? 'Ativo' : 'Inativo')->color(fn (string $state) => $state === 'active' ? 'success' : 'gray'),
        ])->recordActions([EditAction::make()->label('Editar')]);
    }

    public static function getPages(): array
    {
        return ['index' => ListApplicationTypes::route('/'), 'create' => CreateApplicationType::route('/criar'), 'edit' => EditApplicationType::route('/{record}/editar')];
    }
}
