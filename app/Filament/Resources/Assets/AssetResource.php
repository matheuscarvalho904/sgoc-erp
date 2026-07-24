<?php

declare(strict_types=1);

namespace App\Filament\Resources\Assets;

use App\Filament\Resources\Assets\Pages\{CreateAsset, EditAsset, ListAssets};
use App\Modules\Assets\Domain\Models\{Asset, AssetCategory, AssetPrefix, AssetType, Fuel};
use App\Modules\Catalog\Domain\Models\Brand;
use App\Modules\Foundation\Domain\Models\{Branch, Company, CostCenter, Department, Organization, Tenant, Work};
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\{DatePicker, Hidden, Select, Textarea, TextInput};
use Filament\Resources\Resource;
use Filament\Schemas\Components\{Grid, Section, Tabs, Tabs\Tab};
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;
    protected static ?string $recordTitleAttribute = 'code';
    protected static ?string $modelLabel = 'ativo';
    protected static ?string $pluralModelLabel = 'ativos';
    protected static ?string $navigationLabel = 'Ativos e Equipamentos';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static string|UnitEnum|null $navigationGroup = 'Gestão de Ativos';
    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Hidden::make('tenant_id')->default(fn()=>Tenant::query()->value('id'))->required(),
            Hidden::make('organization_id')->default(fn()=>Organization::query()->value('id')),
            Tabs::make('Ativo')->columnSpanFull()->tabs([
                Tab::make('Identificação')->schema([
                    Grid::make(['default'=>1,'md'=>2,'xl'=>4])->schema([
                        Select::make('asset_type_id')->label('Tipo')->options(fn()=>AssetType::query()->where('status','active')->orderBy('name')->pluck('name','id'))->searchable()->preload()->required(),
                        Select::make('asset_category_id')->label('Categoria')->options(fn()=>AssetCategory::query()->where('status','active')->orderBy('name')->pluck('name','id'))->searchable()->preload(),
                        Select::make('asset_prefix_id')->label('Prefixo')->options(fn()=>AssetPrefix::query()->where('status','active')->orderBy('code')->get()->mapWithKeys(fn(AssetPrefix $prefix)=>[$prefix->id=>$prefix->code.' - '.$prefix->name]))->searchable()->preload()->live(),
                        TextInput::make('code')->label('Código')->helperText('Deixe vazio para gerar pelo prefixo.')->maxLength(40),
                        TextInput::make('name')->label('Descrição do ativo')->required()->columnSpan(2),
                        Select::make('brand_id')->label('Marca')->options(fn()=>Brand::query()->where('status','active')->orderBy('name')->pluck('name','id'))->searchable()->preload(),
                        TextInput::make('model')->label('Modelo'),
                        TextInput::make('manufacture_year')->label('Ano fabricação')->numeric(), TextInput::make('model_year')->label('Ano modelo')->numeric(),
                        TextInput::make('plate')->label('Placa'), TextInput::make('patrimony_number')->label('Patrimônio'),
                    ]),
                ]),
                Tab::make('Documentação')->schema([Grid::make(['default'=>1,'md'=>2,'xl'=>4])->schema([
                    TextInput::make('renavam')->label('RENAVAM'), TextInput::make('chassis')->label('Chassi')->columnSpan(2), TextInput::make('serial_number')->label('Número de série'),
                    DatePicker::make('warranty_until')->label('Garantia até'),
                ])]),
                Tab::make('Alocação')->schema([Grid::make(['default'=>1,'md'=>2,'xl'=>3])->schema([
                    Select::make('company_id')->label('Empresa')->options(fn()=>Company::query()->orderBy('name')->pluck('name','id'))->searchable()->preload(),
                    Select::make('branch_id')->label('Filial')->options(fn()=>Branch::query()->orderBy('name')->pluck('name','id'))->searchable()->preload(),
                    Select::make('work_id')->label('Obra')->options(fn()=>Work::query()->orderBy('name')->pluck('name','id'))->searchable()->preload(),
                    Select::make('cost_center_id')->label('Centro de custo')->options(fn()=>CostCenter::query()->orderBy('name')->pluck('name','id'))->searchable()->preload(),
                    Select::make('department_id')->label('Departamento')->options(fn()=>Department::query()->orderBy('name')->pluck('name','id'))->searchable()->preload(),
                    TextInput::make('responsible_name')->label('Responsável'), TextInput::make('location')->label('Localização')->columnSpan(2),
                ])]),
                Tab::make('Operação')->schema([Grid::make(['default'=>1,'md'=>2,'xl'=>4])->schema([
                    Select::make('ownership_type')->label('Propriedade')->options(['owned'=>'Próprio','rented'=>'Locado','leased'=>'Arrendado','third_party'=>'Terceiro'])->default('owned')->required(),
                    Select::make('operational_status')->label('Situação operacional')->options(['operating'=>'Operando','maintenance'=>'Em manutenção','stopped'=>'Parado','rented'=>'Locado','available'=>'Disponível','disposed'=>'Baixado','sold'=>'Vendido'])->default('available')->required(),
                    Select::make('meter_type')->label('Medidor')->options(['none'=>'Nenhum','odometer'=>'Hodômetro','hourmeter'=>'Horímetro','both'=>'Ambos'])->default('none')->required(),
                    Select::make('fuel_id')->label('Combustível')->options(fn()=>Fuel::query()->where('status','active')->orderBy('name')->pluck('name','id'))->searchable()->preload(),
                    TextInput::make('current_odometer')->label('Hodômetro atual')->numeric()->default(0), TextInput::make('current_hourmeter')->label('Horímetro atual')->numeric()->default(0),
                    TextInput::make('tank_capacity')->label('Capacidade do tanque')->numeric()->suffix('L'), TextInput::make('expected_consumption')->label('Consumo previsto')->numeric(),
                ])]),
                Tab::make('Financeiro')->schema([Grid::make(['default'=>1,'md'=>2,'xl'=>4])->schema([
                    DatePicker::make('acquisition_date')->label('Data de aquisição'), TextInput::make('acquisition_value')->label('Valor de aquisição')->numeric()->prefix('R$')->default(0),
                    TextInput::make('residual_value')->label('Valor residual')->numeric()->prefix('R$')->default(0), TextInput::make('useful_life_months')->label('Vida útil')->numeric()->suffix('meses'),
                ])]),
                Tab::make('Observações')->schema([Textarea::make('notes')->label('Observações')->rows(7), Select::make('status')->label('Status do cadastro')->options(['active'=>'Ativo','inactive'=>'Inativo'])->default('active')->required()]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Código')->searchable()->sortable()->weight('bold'), TextColumn::make('name')->label('Ativo / Equipamento')->searchable()->sortable(),
            TextColumn::make('plate')->label('Placa')->searchable(), TextColumn::make('model')->label('Modelo')->toggleable(),
            TextColumn::make('operational_status')->label('Situação')->badge()->formatStateUsing(fn(string $state)=>match($state){'operating'=>'Operando','maintenance'=>'Em manutenção','stopped'=>'Parado','rented'=>'Locado','available'=>'Disponível','disposed'=>'Baixado','sold'=>'Vendido',default=>$state})->color(fn(string $state)=>match($state){'operating'=>'success','maintenance'=>'warning','stopped'=>'danger','available'=>'info',default=>'gray'}),
            TextColumn::make('status')->label('Cadastro')->badge()->formatStateUsing(fn(string $state)=>$state==='active'?'Ativo':'Inativo'),
        ])->filters([SelectFilter::make('operational_status')->label('Situação')->options(['operating'=>'Operando','maintenance'=>'Em manutenção','stopped'=>'Parado','rented'=>'Locado','available'=>'Disponível','disposed'=>'Baixado','sold'=>'Vendido'])])->recordActions([EditAction::make()->label('Editar')]);
    }

    public static function getPages(): array { return ['index'=>ListAssets::route('/'),'create'=>CreateAsset::route('/criar'),'edit'=>EditAsset::route('/{record}/editar')]; }
}
