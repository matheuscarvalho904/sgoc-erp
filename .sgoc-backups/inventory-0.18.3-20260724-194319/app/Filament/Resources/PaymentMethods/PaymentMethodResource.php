<?php
 declare(strict_types=1);
 namespace App\Filament\Resources\PaymentMethods;
 use App\Modules\Commercial\Domain\Models\PaymentMethod;
 use App\Modules\Foundation\Domain\Models\Tenant;
 use App\Filament\Resources\PaymentMethods\Pages\CreatePaymentMethod; use App\Filament\Resources\PaymentMethods\Pages\EditPaymentMethod; use App\Filament\Resources\PaymentMethods\Pages\ListPaymentMethods;
 use BackedEnum; use UnitEnum; use Filament\Resources\Resource; use Filament\Schemas\Schema; use Filament\Schemas\Components\Section;
 use Filament\Forms\Components\Hidden; use Filament\Forms\Components\TextInput; use Filament\Forms\Components\Textarea; use Filament\Forms\Components\Select; use Filament\Forms\Components\Toggle;
 use Filament\Tables\Table; use Filament\Tables\Columns\TextColumn; use Filament\Tables\Columns\IconColumn; use Filament\Actions\EditAction;
 final class PaymentMethodResource extends Resource {
  protected static ?string $model=PaymentMethod::class; protected static ?string $recordTitleAttribute='name';
  protected static ?string $modelLabel='forma de pagamento'; protected static ?string $pluralModelLabel='formas de pagamento'; protected static ?string $navigationLabel='Formas de pagamento';
  protected static string|BackedEnum|null $navigationIcon='heroicon-o-credit-card'; protected static string|UnitEnum|null $navigationGroup='Cadastros Gerais'; protected static ?int $navigationSort=30;
  public static function form(Schema $schema): Schema { return $schema->columns(1)->components([Hidden::make('tenant_id')->default(fn()=>Tenant::query()->value('id'))->required(),Section::make('Forma de pagamento')->columnSpanFull()->columns(['default'=>1,'md'=>2,'xl'=>4])->schema([
                    TextInput::make('code')->label('Código')->required()->maxLength(180),
                    TextInput::make('name')->label('Nome')->required()->maxLength(180),
                    Select::make('type')->label('Tipo')->options(['cash'=>'Dinheiro','pix'=>'PIX','bank_transfer'=>'Transferência','boleto'=>'Boleto','credit_card'=>'Cartão de crédito','debit_card'=>'Cartão de débito','check'=>'Cheque','other'=>'Outro'])->required(),
                    Toggle::make('requires_bank_data')->label('Exige dados bancários'),
                    Select::make('status')->label('Status')->options(['active'=>'Ativo','inactive'=>'Inativo'])->default('active')->required(),
  ])]); }
  public static function table(Table $table): Table { return $table->columns([
            TextColumn::make('code')->label('Código')->searchable()->sortable(),
            TextColumn::make('name')->label('Nome')->searchable()->sortable(),
            TextColumn::make('type')->label('Tipo')->searchable()->sortable(),
            IconColumn::make('requires_bank_data')->label('Exige dados bancários')->boolean(),
  ])->recordActions([EditAction::make()->label('Editar')]); }
  public static function getPages(): array { return ['index'=>ListPaymentMethods::route('/'),'create'=>CreatePaymentMethod::route('/criar'),'edit'=>EditPaymentMethod::route('/{record}/editar')]; }
 }
