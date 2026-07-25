<?php

declare(strict_types=1);

namespace App\Filament\Resources\PurchaseCategories;

use App\Filament\Resources\PurchaseCategories\Pages\CreatePurchaseCategory;
use App\Filament\Resources\PurchaseCategories\Pages\EditPurchaseCategory;
use App\Filament\Resources\PurchaseCategories\Pages\ListPurchaseCategories;
use App\Modules\Foundation\Domain\Models\Tenant;
use App\Modules\Purchasing\Domain\Models\PurchaseCategory;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class PurchaseCategoryResource extends Resource
{
    protected static ?string $model = PurchaseCategory::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'categoria de compra';
    protected static ?string $pluralModelLabel = 'categorias de compra';
    protected static ?string $navigationLabel = 'Categorias de compra';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    protected static string|UnitEnum|null $navigationGroup = 'Compras';
    protected static ?int $navigationSort = 10;
    protected static ?string $slug = 'compras/categorias';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Hidden::make('tenant_id')->default(fn () => Tenant::query()->value('id'))->required(),
            Section::make('Categoria')->columns(2)->schema([
                TextInput::make('code')->label('Código')->required()->maxLength(30),
                TextInput::make('name')->label('Nome')->required()->maxLength(120),
                Select::make('status')->label('Status')->default('active')->required()->options(['active' => 'Ativa', 'inactive' => 'Inativa']),
                Textarea::make('description')->label('Descrição')->rows(3)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Código')->searchable()->sortable(),
            TextColumn::make('name')->label('Categoria')->searchable()->sortable()->weight('bold'),
            TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn (string $state) => $state === 'active' ? 'Ativa' : 'Inativa')->color(fn (string $state) => $state === 'active' ? 'success' : 'gray'),
        ])->filters([SelectFilter::make('status')->options(['active' => 'Ativa', 'inactive' => 'Inativa'])])
          ->recordActions([EditAction::make()->label('Editar')])
          ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->label('Excluir selecionadas')])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListPurchaseCategories::route('/'), 'create' => CreatePurchaseCategory::route('/criar'), 'edit' => EditPurchaseCategory::route('/{record}/editar')];
    }
}
