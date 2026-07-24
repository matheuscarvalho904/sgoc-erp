<?php
 declare(strict_types=1);
 namespace App\Filament\Resources\Workshops;
 use App\Filament\Resources\Workshops\Pages\CreateWorkshop; use App\Filament\Resources\Workshops\Pages\EditWorkshop; use App\Filament\Resources\Workshops\Pages\ListWorkshops;
 use App\Modules\Maintenance\Domain\Models\Workshop; use BackedEnum; use UnitEnum; use Filament\Resources\Resource; use Filament\Schemas\Schema; use Filament\Schemas\Components\Section; use Filament\Schemas\Components\Grid; use Filament\Forms\Components\TextInput; use Filament\Forms\Components\Select; use Filament\Forms\Components\Toggle; use Filament\Forms\Components\DatePicker; use Filament\Tables\Table; use Filament\Tables\Columns\TextColumn; use Filament\Tables\Columns\IconColumn; use Filament\Actions\EditAction; use Filament\Actions\DeleteBulkAction; use Filament\Actions\BulkActionGroup;
 final class WorkshopResource extends Resource { protected static ?string $model=Workshop::class; protected static ?string $modelLabel='Oficina'; protected static ?string $pluralModelLabel='Oficinas'; protected static ?string $navigationLabel='Oficinas'; protected static string|UnitEnum|null $navigationGroup='Oficina e Manutenção'; protected static string|BackedEnum|null $navigationIcon='heroicon-o-wrench-screwdriver';
 public static function form(Schema $schema): Schema { return $schema->columns(1)->components([Section::make('Oficina')->schema([Grid::make(['default'=>1,'md'=>2,'xl'=>4])->schema([TextInput::make('code')->label('Code')->required(),
                    TextInput::make('name')->label('Name')->required(),
                    Select::make('type')->label('Tipo')->options(['internal'=>'Interna','external'=>'Terceirizada'])->default('internal')->required(),
                    TextInput::make('phone')->label('Phone'),
                    TextInput::make('email')->label('Email'),
                    TextInput::make('contact_name')->label('Contact Name'),
                    Select::make('status')->label('Status')->options(['active'=>'Ativo','inactive'=>'Inativo'])->default('active')->required()])])]); }
 public static function table(Table $table): Table { return $table->columns([TextColumn::make('code')->label('Code')->searchable()->sortable(),
            TextColumn::make('name')->label('Name')->searchable()->sortable(),
            TextColumn::make('type')->label('Tipo')->formatStateUsing(fn (?string $state): string => $state === 'internal' ? 'Interna' : 'Terceirizada')->badge(),
            TextColumn::make('phone')->label('Phone')->searchable()->sortable(),
            TextColumn::make('email')->label('Email')->searchable()->sortable(),
            TextColumn::make('contact_name')->label('Contact Name')->searchable()->sortable(),
            TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn (?string $state): string => $state === 'active' ? 'Ativo' : 'Inativo')->color(fn (?string $state): string => $state === 'active' ? 'success' : 'gray')])->recordActions([EditAction::make()->label('Editar')])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->label('Excluir selecionados')])]); }
 public static function getPages(): array { return ['index'=>ListWorkshops::route('/'),'create'=>CreateWorkshop::route('/criar'),'edit'=>EditWorkshop::route('/{record}/editar')]; } }
