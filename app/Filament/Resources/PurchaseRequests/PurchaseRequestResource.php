<?php

declare(strict_types=1);

namespace App\Filament\Resources\PurchaseRequests;

use App\Filament\Resources\PurchaseRequests\Pages\CreatePurchaseRequest;
use App\Filament\Resources\PurchaseRequests\Pages\EditPurchaseRequest;
use App\Filament\Resources\PurchaseRequests\Pages\ListPurchaseRequests;
use App\Modules\Purchasing\Application\Actions\ApprovePurchaseRequest;
use App\Modules\Purchasing\Application\Actions\CreateQuotationFromPurchaseRequest;
use App\Modules\Purchasing\Application\Actions\RejectPurchaseRequest;
use App\Modules\Purchasing\Application\Actions\SubmitPurchaseRequest;
use App\Modules\Foundation\Domain\Models\Branch;
use App\Modules\Foundation\Domain\Models\Company;
use App\Modules\Foundation\Domain\Models\CostCenter;
use App\Modules\Foundation\Domain\Models\Organization;
use App\Modules\Foundation\Domain\Models\Tenant;
use App\Modules\Foundation\Domain\Models\Work;
use App\Modules\Purchasing\Domain\Models\PurchaseCategory;
use App\Modules\Purchasing\Domain\Models\PurchaseRequest;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class PurchaseRequestResource extends Resource
{
    protected static ?string $model = PurchaseRequest::class;
    protected static ?string $recordTitleAttribute = 'number';
    protected static ?string $modelLabel = 'solicitação de compra';
    protected static ?string $pluralModelLabel = 'solicitações de compra';
    protected static ?string $navigationLabel = 'Solicitações de compra';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static string|UnitEnum|null $navigationGroup = 'Compras';
    protected static ?int $navigationSort = 30;
    protected static ?string $slug = 'compras/solicitacoes';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Hidden::make('tenant_id')->default(fn () => Tenant::query()->value('id'))->required(),
            Hidden::make('organization_id')->default(fn () => Organization::query()->value('id'))->required(),
            Hidden::make('requester_id')->default(fn () => auth()->id()),
            Section::make('Identificação')->schema([
                Grid::make(['default'=>1,'md'=>2,'xl'=>4])->schema([
                    TextInput::make('number')->label('Número')->disabled()->dehydrated()->maxLength(40),
                    DatePicker::make('requested_at')->label('Data da solicitação')->default(now())->required(),
                    DatePicker::make('needed_at')->label('Necessidade')->minDate(fn ($get) => $get('requested_at')),
                    Select::make('priority')->label('Prioridade')->default('normal')->required()->options(['low'=>'Baixa','normal'=>'Normal','high'=>'Alta','urgent'=>'Urgente']),
                    Select::make('company_id')->label('Empresa')->options(fn () => Company::query()->where('status','active')->orderBy('name')->pluck('name','id')->all())->searchable()->preload()->required()->live(),
                    Select::make('branch_id')->label('Filial')->options(fn ($get) => Branch::query()->when($get('company_id'), fn ($q,$id)=>$q->where('company_id',$id))->where('status','active')->orderBy('name')->pluck('name','id')->all())->searchable()->preload()->required(),
                    Select::make('work_id')->label('Obra')->options(fn ($get) => Work::query()->when($get('company_id'), fn ($q,$id)=>$q->where('company_id',$id))->whereNotIn('status',['completed','cancelled'])->orderBy('name')->pluck('name','id')->all())->searchable()->preload(),
                    Select::make('cost_center_id')->label('Centro de custo')->options(fn () => CostCenter::query()->where('status','active')->orderBy('name')->pluck('name','id')->all())->searchable()->preload(),
                    Select::make('category_id')->label('Categoria')->options(fn () => PurchaseCategory::query()->where('status','active')->orderBy('name')->pluck('name','id')->all())->searchable()->preload(),
                    Select::make('status')->label('Status')->default('draft')->required()->options(['draft'=>'Rascunho','pending_approval'=>'Aguardando aprovação','approved'=>'Aprovada','rejected'=>'Reprovada','quoting'=>'Em cotação','ordered'=>'Pedido emitido','partially_received'=>'Recebida parcialmente','received'=>'Recebida','cancelled'=>'Cancelada']),
                    TextInput::make('delivery_location')->label('Local de entrega')->maxLength(250)->columnSpan(2),
                    TextInput::make('total_estimated')->label('Total estimado')->numeric()->prefix('R$')->default(0),
                ]),
            ]),
            Section::make('Justificativa e observações')->schema([
                Textarea::make('justification')->label('Justificativa')->required()->rows(4),
                Textarea::make('notes')->label('Observações')->rows(3),
                Textarea::make('rejection_reason')->label('Motivo da reprovação')->rows(3)->visible(fn ($get) => $get('status') === 'rejected'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('number')->label('Número')->searchable()->sortable()->weight('bold'),
            TextColumn::make('requested_at')->label('Solicitada em')->date('d/m/Y')->sortable(),
            TextColumn::make('company.name')->label('Empresa')->searchable(),
            TextColumn::make('work.name')->label('Obra')->searchable()->placeholder('—'),
            TextColumn::make('priority')->label('Prioridade')->badge()->formatStateUsing(fn(string $s)=>match($s){'low'=>'Baixa','normal'=>'Normal','high'=>'Alta','urgent'=>'Urgente',default=>$s})->color(fn(string $s)=>match($s){'urgent'=>'danger','high'=>'warning','normal'=>'info',default=>'gray'}),
            TextColumn::make('total_estimated')->label('Estimado')->money('BRL')->sortable(),
            TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn(string $s)=>match($s){'draft'=>'Rascunho','pending_approval'=>'Aguardando aprovação','approved'=>'Aprovada','rejected'=>'Reprovada','quoting'=>'Em cotação','ordered'=>'Pedido emitido','partially_received'=>'Recebida parcialmente','received'=>'Recebida','cancelled'=>'Cancelada',default=>$s})->color(fn(string $s)=>match($s){'approved','received'=>'success','pending_approval','quoting'=>'warning','rejected','cancelled'=>'danger','ordered','partially_received'=>'info',default=>'gray'}),
        ])->filters([
            SelectFilter::make('status')->label('Status')->options(['draft'=>'Rascunho','pending_approval'=>'Aguardando aprovação','approved'=>'Aprovada','rejected'=>'Reprovada','quoting'=>'Em cotação','ordered'=>'Pedido emitido','partially_received'=>'Recebida parcialmente','received'=>'Recebida','cancelled'=>'Cancelada']),
            SelectFilter::make('priority')->label('Prioridade')->options(['low'=>'Baixa','normal'=>'Normal','high'=>'Alta','urgent'=>'Urgente']),
        ])->recordActions([
            Action::make('submit')
                ->label('Enviar para aprovação')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn (PurchaseRequest $record): bool => in_array($record->status, ['draft', 'rejected'], true))
                ->action(function (PurchaseRequest $record): void {
                    try {
                        app(SubmitPurchaseRequest::class)->execute($record);
                        Notification::make()->title('Solicitação enviada para aprovação')->success()->send();
                    } catch (\Throwable $exception) {
                        Notification::make()->title('Não foi possível enviar')->body($exception->getMessage())->danger()->send();
                    }
                }),
            Action::make('approve')
                ->label('Aprovar etapa')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (PurchaseRequest $record): bool => $record->status === 'pending_approval')
                ->action(function (PurchaseRequest $record): void {
                    try {
                        app(ApprovePurchaseRequest::class)->execute($record, auth()->user());
                        Notification::make()->title('Etapa aprovada')->success()->send();
                    } catch (\Throwable $exception) {
                        Notification::make()->title('Não foi possível aprovar')->body($exception->getMessage())->danger()->send();
                    }
                }),
            Action::make('reject')
                ->label('Reprovar')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (PurchaseRequest $record): bool => $record->status === 'pending_approval')
                ->action(function (PurchaseRequest $record): void {
                    try {
                        app(RejectPurchaseRequest::class)->execute($record, auth()->user());
                        Notification::make()->title('Solicitação reprovada')->warning()->send();
                    } catch (\Throwable $exception) {
                        Notification::make()->title('Não foi possível reprovar')->body($exception->getMessage())->danger()->send();
                    }
                }),
            Action::make('quotation')
                ->label('Gerar cotação')
                ->icon('heroicon-o-scale')
                ->color('info')
                ->requiresConfirmation()
                ->visible(fn (PurchaseRequest $record): bool => $record->status === 'approved')
                ->action(function (PurchaseRequest $record): void {
                    try {
                        app(CreateQuotationFromPurchaseRequest::class)->execute($record, auth()->user());
                        Notification::make()->title('Cotação gerada com sucesso')->success()->send();
                    } catch (\Throwable $exception) {
                        Notification::make()->title('Não foi possível gerar a cotação')->body($exception->getMessage())->danger()->send();
                    }
                }),
            EditAction::make()->label('Editar'),
        ])
          ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->label('Excluir selecionadas')])])
          ->defaultSort('requested_at','desc');
    }

    public static function getPages(): array
    {
        return ['index'=>ListPurchaseRequests::route('/'),'create'=>CreatePurchaseRequest::route('/criar'),'edit'=>EditPurchaseRequest::route('/{record}/editar')];
    }
}
