<?php
 declare(strict_types=1);
 namespace App\Filament\Resources\PaymentTerms;
 use App\Modules\Commercial\Domain\Models\PaymentTerm;
 use App\Modules\Foundation\Domain\Models\Tenant;
 use App\Filament\Resources\PaymentTerms\Pages\CreatePaymentTerm; use App\Filament\Resources\PaymentTerms\Pages\EditPaymentTerm; use App\Filament\Resources\PaymentTerms\Pages\ListPaymentTerms;
 use BackedEnum; use UnitEnum; use Filament\Resources\Resource; use Filament\Schemas\Schema; use Filament\Schemas\Components\Section;
 use Filament\Forms\Components\Hidden; use Filament\Forms\Components\TextInput; use Filament\Forms\Components\Textarea; use Filament\Forms\Components\Select; use Filament\Forms\Components\Toggle;
 use Filament\Tables\Table; use Filament\Tables\Columns\TextColumn; use Filament\Tables\Columns\IconColumn; use Filament\Actions\EditAction;
 final class PaymentTermResource extends Resource {
  protected static ?string $model=PaymentTerm::class; protected static ?string $recordTitleAttribute='name';
  protected static ?string $modelLabel='condição de pagamento'; protected static ?string $pluralModelLabel='condições de pagamento'; protected static ?string $navigationLabel='Condições de pagamento';
  protected static string|BackedEnum|null $navigationIcon='heroicon-o-calendar-days'; protected static string|UnitEnum|null $navigationGroup='Cadastros Gerais'; protected static ?int $navigationSort=40;
  public static function form(Schema $schema): Schema { return $schema->columns(1)->components([Hidden::make('tenant_id')->default(fn()=>Tenant::query()->value('id'))->required(),Section::make('Condição de pagamento')->columnSpanFull()->columns(['default'=>1,'md'=>2,'xl'=>4])->schema([
                    TextInput::make('code')->label('Código')->required()->maxLength(180),
                    TextInput::make('name')->label('Nome')->required()->maxLength(180),
                    TextInput::make('installments')->label('Parcelas')->numeric()->required(),
                    TextInput::make('first_due_days')->label('1º vencimento (dias)')->numeric()->required(),
                    TextInput::make('interval_days')->label('Intervalo (dias)')->numeric()->required(),
                    Toggle::make('is_cash')->label('À vista'),
                    Select::make('status')->label('Status')->options(['active'=>'Ativo','inactive'=>'Inativo'])->default('active')->required(),
  ])]); }
  public static function table(Table $table): Table { return $table->columns([
            TextColumn::make('code')->label('Código')->searchable()->sortable(),
            TextColumn::make('name')->label('Nome')->searchable()->sortable(),
            TextColumn::make('installments')->label('Parcelas')->searchable()->sortable(),
            TextColumn::make('first_due_days')->label('1º vencimento (dias)')->searchable()->sortable(),
  ])->recordActions([EditAction::make()->label('Editar')]); }
  public static function getPages(): array { return ['index'=>ListPaymentTerms::route('/'),'create'=>CreatePaymentTerm::route('/criar'),'edit'=>EditPaymentTerm::route('/{record}/editar')]; }
 }
