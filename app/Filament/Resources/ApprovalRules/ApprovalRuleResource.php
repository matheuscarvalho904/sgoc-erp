<?php

declare(strict_types=1);

namespace App\Filament\Resources\ApprovalRules;

use App\Filament\Resources\ApprovalRules\Pages\CreateApprovalRule;
use App\Filament\Resources\ApprovalRules\Pages\EditApprovalRule;
use App\Filament\Resources\ApprovalRules\Pages\ListApprovalRules;
use App\Models\User;
use App\Modules\Foundation\Domain\Models\Company;
use App\Modules\Foundation\Domain\Models\Tenant;
use App\Modules\Purchasing\Domain\Models\ApprovalRule;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class ApprovalRuleResource extends Resource
{
    protected static ?string $model = ApprovalRule::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'regra de aprovação';
    protected static ?string $pluralModelLabel = 'regras de aprovação';
    protected static ?string $navigationLabel = 'Alçadas de aprovação';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';
    protected static string|UnitEnum|null $navigationGroup = 'Compras';
    protected static ?int $navigationSort = 40;
    protected static ?string $slug = 'compras/alcadas';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Hidden::make('tenant_id')->default(fn () => Tenant::query()->value('id'))->required(),
            Section::make('Regra de aprovação')->schema([
                Grid::make(['default'=>1,'md'=>2,'xl'=>4])->schema([
                    TextInput::make('name')->label('Nome da regra')->required()->maxLength(120)->columnSpan(2),
                    Select::make('company_id')->label('Empresa')->options(fn()=>Company::query()->orderBy('name')->pluck('name','id')->all())->searchable()->preload(),
                    TextInput::make('approval_order')->label('Ordem')->numeric()->default(1)->minValue(1)->required(),
                    TextInput::make('min_amount')->label('Valor mínimo')->numeric()->prefix('R$')->default(0)->required(),
                    TextInput::make('max_amount')->label('Valor máximo')->numeric()->prefix('R$')->helperText('Deixe vazio para não limitar.'),
                    Select::make('approver_user_id')->label('Aprovador')->options(fn()=>User::query()->orderBy('name')->pluck('name','id')->all())->searchable()->preload()->required(),
                    Select::make('status')->label('Status')->default('active')->options(['active'=>'Ativa','inactive'=>'Inativa'])->required(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Regra')->searchable()->sortable()->weight('bold'),
            TextColumn::make('company.name')->label('Empresa')->placeholder('Todas'),
            TextColumn::make('min_amount')->label('Mínimo')->money('BRL')->sortable(),
            TextColumn::make('max_amount')->label('Máximo')->money('BRL')->placeholder('Sem limite')->sortable(),
            TextColumn::make('approver.name')->label('Aprovador')->searchable(),
            TextColumn::make('approval_order')->label('Ordem')->sortable(),
            TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn(string $s)=>$s==='active'?'Ativa':'Inativa')->color(fn(string $s)=>$s==='active'?'success':'gray'),
        ])->filters([
            SelectFilter::make('status')->label('Status')->options(['active'=>'Ativa','inactive'=>'Inativa']),
        ])->recordActions([EditAction::make()->label('Editar')])
          ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->label('Excluir selecionadas')])]);
    }

    public static function getPages(): array
    {
        return ['index'=>ListApprovalRules::route('/'),'create'=>CreateApprovalRule::route('/criar'),'edit'=>EditApprovalRule::route('/{record}/editar')];
    }
}
