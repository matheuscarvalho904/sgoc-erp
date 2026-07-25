<?php

declare(strict_types=1);

namespace App\Filament\Resources\Fuels;

use App\Filament\Resources\Fuels\Pages\{CreateFuel, EditFuel, ListFuels};
use App\Modules\Assets\Domain\Models\Fuel;
use App\Modules\Foundation\Domain\Models\Tenant;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\{Hidden, Select, Textarea, TextInput};
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

final class FuelResource extends Resource
{
    protected static ?string $model = Fuel::class;
    protected static ?string $modelLabel = 'combustível';
    protected static ?string $pluralModelLabel = 'combustíveis';
    protected static ?string $navigationLabel = 'Combustíveis';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-fire';
    protected static string|UnitEnum|null $navigationGroup = 'Gestão de Ativos';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Hidden::make('tenant_id')->default(fn()=>Tenant::query()->value('id'))->required(),
            Section::make('Cadastro')->columns(['default'=>1,'md'=>2,'xl'=>4])->schema([
                TextInput::make('code')->label('Código')->required(), TextInput::make('name')->label('Nome')->required(), TextInput::make('unit')->label('Unidade')->default('L')->required(), Select::make('status')->label('Status')->options(['active'=>'Ativo','inactive'=>'Inativo'])->default('active')->required()
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('code')->label('Código')->searchable()->sortable(), TextColumn::make('name')->label('Nome')->searchable()->sortable()->weight('bold'), TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn(string $state)=>$state==='active'?'Ativo':'Inativo')->color(fn(string $state)=>$state==='active'?'success':'gray')])->recordActions([EditAction::make()->label('Editar')]);
    }

    public static function getPages(): array { return ['index'=>ListFuels::route('/'),'create'=>CreateFuel::route('/criar'),'edit'=>EditFuel::route('/{record}/editar')]; }
}
