<?php

declare(strict_types=1);

namespace App\Filament\Resources\AssetTypes;

use App\Filament\Resources\AssetTypes\Pages\{CreateAssetType, EditAssetType, ListAssetTypes};
use App\Modules\Assets\Domain\Models\AssetType;
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

final class AssetTypeResource extends Resource
{
    protected static ?string $model = AssetType::class;
    protected static ?string $modelLabel = 'tipo de ativo';
    protected static ?string $pluralModelLabel = 'tipos de ativos';
    protected static ?string $navigationLabel = 'Tipos de Ativos';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    protected static string|UnitEnum|null $navigationGroup = 'Gestão de Ativos';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Hidden::make('tenant_id')->default(fn()=>Tenant::query()->value('id'))->required(),
            Section::make('Cadastro')->columns(['default'=>1,'md'=>2,'xl'=>4])->schema([
                TextInput::make('code')->label('Código')->required(), TextInput::make('name')->label('Nome')->required(), Textarea::make('description')->label('Descrição')->columnSpanFull(), Select::make('status')->label('Status')->options(['active'=>'Ativo','inactive'=>'Inativo'])->default('active')->required()
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('code')->label('Código')->searchable()->sortable(), TextColumn::make('name')->label('Nome')->searchable()->sortable()->weight('bold'), TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn(string $state)=>$state==='active'?'Ativo':'Inativo')->color(fn(string $state)=>$state==='active'?'success':'gray')])->recordActions([EditAction::make()->label('Editar')]);
    }

    public static function getPages(): array { return ['index'=>ListAssetTypes::route('/'),'create'=>CreateAssetType::route('/criar'),'edit'=>EditAssetType::route('/{record}/editar')]; }
}
