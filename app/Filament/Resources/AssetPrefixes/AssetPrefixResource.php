<?php

declare(strict_types=1);

namespace App\Filament\Resources\AssetPrefixes;

use App\Filament\Resources\AssetPrefixes\Pages\{CreateAssetPrefix, EditAssetPrefix, ListAssetPrefixes};
use App\Modules\Assets\Domain\Models\AssetPrefix;
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

final class AssetPrefixResource extends Resource
{
    protected static ?string $model = AssetPrefix::class;
    protected static ?string $modelLabel = 'prefixo de ativo';
    protected static ?string $pluralModelLabel = 'prefixos de ativos';
    protected static ?string $navigationLabel = 'Prefixos de Ativos';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-hashtag';
    protected static string|UnitEnum|null $navigationGroup = 'Gestão de Ativos';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Hidden::make('tenant_id')->default(fn()=>Tenant::query()->value('id'))->required(),
            Section::make('Cadastro')->columns(['default'=>1,'md'=>2,'xl'=>4])->schema([
                Select::make('asset_type_id')->label('Tipo de ativo')->options(fn()=>AssetType::query()->where('status','active')->orderBy('name')->pluck('name','id'))->searchable()->preload(), TextInput::make('code')->label('Prefixo')->required(), TextInput::make('name')->label('Descrição')->required(), TextInput::make('next_number')->label('Próximo número')->numeric()->default(1)->required(), TextInput::make('digits')->label('Dígitos')->numeric()->default(3)->required(), Select::make('status')->label('Status')->options(['active'=>'Ativo','inactive'=>'Inativo'])->default('active')->required()
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('code')->label('Código')->searchable()->sortable(), TextColumn::make('name')->label('Nome')->searchable()->sortable()->weight('bold'), TextColumn::make('next_number')->label('Próximo número'), TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn(string $state)=>$state==='active'?'Ativo':'Inativo')->color(fn(string $state)=>$state==='active'?'success':'gray')])->recordActions([EditAction::make()->label('Editar')]);
    }

    public static function getPages(): array { return ['index'=>ListAssetPrefixes::route('/'),'create'=>CreateAssetPrefix::route('/criar'),'edit'=>EditAssetPrefix::route('/{record}/editar')]; }
}
