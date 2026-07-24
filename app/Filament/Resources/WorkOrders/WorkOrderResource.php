<?php

declare(strict_types=1);

namespace App\Filament\Resources\WorkOrders;

use App\Filament\Resources\WorkOrders\Pages\CreateWorkOrder;
use App\Filament\Resources\WorkOrders\Pages\EditWorkOrder;
use App\Filament\Resources\WorkOrders\Pages\ListWorkOrders;
use App\Models\User;
use App\Modules\Assets\Domain\Models\Asset;
use App\Modules\Catalog\Domain\Models\Product;
use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\Foundation\Domain\Models\Branch;
use App\Modules\Foundation\Domain\Models\Company;
use App\Modules\Foundation\Domain\Models\CostCenter;
use App\Modules\Foundation\Domain\Models\Organization;
use App\Modules\Foundation\Domain\Models\Tenant;
use App\Modules\Foundation\Domain\Models\Work;
use App\Modules\Maintenance\Domain\Models\MaintenancePriority;
use App\Modules\Maintenance\Domain\Models\MaintenanceType;
use App\Modules\Maintenance\Domain\Models\WorkOrder;
use App\Modules\Maintenance\Domain\Models\Workshop;
use App\Shared\Filament\SgocInput;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class WorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;
    protected static ?string $recordTitleAttribute = 'number';
    protected static ?string $modelLabel = 'ordem de serviço';
    protected static ?string $pluralModelLabel = 'ordens de serviço';
    protected static ?string $navigationLabel = 'Ordens de serviço';
    protected static string|UnitEnum|null $navigationGroup = 'Oficina e Manutenção';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Hidden::make('tenant_id')->default(fn () => Tenant::query()->value('id')),
            Hidden::make('organization_id')->default(fn () => Organization::query()->value('id')),
            Hidden::make('requester_id')->default(fn () => auth()->id()),

            Tabs::make('OS')->tabs([
                Tab::make('Identificação')->schema([
                    Grid::make(['default' => 1, 'md' => 2, 'xl' => 4])->schema([
                        TextInput::make('number')->label('Número')->disabled()->dehydrated(),
                        DateTimePicker::make('opened_at')->label('Abertura')->default(now())->required(),
                        Select::make('status')->label('Status')->options(self::statuses())->default('open')->required(),
                        Select::make('asset_id')
                            ->label('Ativo/Equipamento')
                            ->options(fn (): array => Asset::query()
                                ->orderBy('code')
                                ->get(['id', 'code', 'name', 'plate'])
                                ->mapWithKeys(fn (Asset $asset): array => [
                                    $asset->id => trim($asset->code.' - '.$asset->name.($asset->plate ? ' | '.$asset->plate : '')),
                                ])->all())
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('maintenance_type_id')->label('Tipo')->options(fn () => MaintenanceType::query()->where('status', 'active')->pluck('name', 'id')->all())->searchable()->preload(),
                        Select::make('priority_id')->label('Prioridade')->options(fn () => MaintenancePriority::query()->where('status', 'active')->orderByDesc('level')->pluck('name', 'id')->all())->searchable()->preload(),
                        Select::make('workshop_id')->label('Oficina')->options(fn () => Workshop::query()->where('status', 'active')->pluck('name', 'id')->all())->searchable()->preload(),
                        Select::make('responsible_id')->label('Responsável')->options(fn () => User::query()->orderBy('name')->pluck('name', 'id')->all())->searchable()->preload(),
                    ]),
                ]),

                Tab::make('Alocação')->schema([
                    Grid::make(['default' => 1, 'md' => 2, 'xl' => 4])->schema([
                        Select::make('company_id')->label('Empresa')->options(fn () => Company::query()->pluck('name', 'id')->all())->searchable()->preload(),
                        Select::make('branch_id')->label('Filial')->options(fn () => Branch::query()->pluck('name', 'id')->all())->searchable()->preload(),
                        Select::make('work_id')->label('Obra')->options(fn () => Work::query()->pluck('name', 'id')->all())->searchable()->preload(),
                        Select::make('cost_center_id')->label('Centro de custo')->options(fn () => CostCenter::query()->pluck('name', 'id')->all())->searchable()->preload(),
                    ]),
                ]),

                Tab::make('Defeito e diagnóstico')->schema([
                    Textarea::make('symptom')->label('Sintoma/Defeito informado')->required()->rows(4),
                    Textarea::make('diagnosis')->label('Diagnóstico')->rows(4),
                    Textarea::make('cause')->label('Causa')->rows(3),
                    Textarea::make('solution')->label('Solução executada')->rows(4),
                ]),

                Tab::make('Serviços')->schema([
                    Repeater::make('services')
                        ->relationship()
                        ->label('Serviços e mão de obra')
                        ->schema([
                            Grid::make(['default' => 1, 'md' => 2, 'xl' => 4])->schema([
                                TextInput::make('description')->label('Descrição do serviço')->required()->columnSpan(['md' => 2, 'xl' => 2]),
                                Select::make('technician_id')->label('Técnico/Responsável')->options(fn () => User::query()->orderBy('name')->pluck('name', 'id')->all())->searchable()->preload(),
                                Select::make('status')->label('Situação')->options(self::serviceStatuses())->default('pending')->required(),
                                SgocInput::quantity('estimated_hours', 2)->label('Horas previstas')->default(0),
                                SgocInput::quantity('actual_hours', 2)->label('Horas realizadas')->default(0),
                                SgocInput::money('hourly_rate')->label('Valor por hora')->default(0),
                                SgocInput::money('total_cost')->label('Custo calculado')->disabled()->dehydrated(false),
                            ]),
                            Textarea::make('notes')->label('Observações')->rows(2),
                        ])
                        ->addActionLabel('Adicionar serviço')
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['description'] ?? 'Novo serviço')
                        ->columnSpanFull(),
                ]),

                Tab::make('Peças e materiais')->schema([
                    Repeater::make('materials')
                        ->relationship()
                        ->label('Peças, materiais e insumos')
                        ->schema([
                            Grid::make(['default' => 1, 'md' => 2, 'xl' => 4])->schema([
                                Select::make('product_id')
                                    ->label('Produto')
                                    ->options(fn () => Product::query()->where('status', 'active')->orderBy('name')->pluck('name', 'id')->all())
                                    ->searchable()
                                    ->preload(),
                                TextInput::make('description')->label('Descrição')->required()->columnSpan(['md' => 1, 'xl' => 2]),
                                Select::make('unit_id')->label('Unidade')->options(fn () => Unit::query()->where('status', 'active')->orderBy('name')->pluck('name', 'id')->all())->searchable()->preload(),
                                SgocInput::quantity('quantity_requested')->label('Quantidade solicitada')->default(0),
                                SgocInput::quantity('quantity_applied')->label('Quantidade aplicada')->default(0),
                                SgocInput::money('unit_cost')->label('Custo unitário')->default(0),
                                SgocInput::money('total_cost')->label('Custo calculado')->disabled()->dehydrated(false),
                                Select::make('status')->label('Situação')->options(self::materialStatuses())->default('requested')->required(),
                            ]),
                        ])
                        ->addActionLabel('Adicionar peça/material')
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['description'] ?? 'Novo material')
                        ->columnSpanFull(),
                ]),

                Tab::make('Medidores')->schema([
                    Grid::make(4)->schema([
                        SgocInput::quantity('entry_hourmeter', 2)->label('Horímetro de entrada'),
                        SgocInput::quantity('exit_hourmeter', 2)->label('Horímetro de saída'),
                        SgocInput::quantity('entry_odometer', 2)->label('Hodômetro de entrada'),
                        SgocInput::quantity('exit_odometer', 2)->label('Hodômetro de saída'),
                    ]),
                ]),

                Tab::make('Programação e custos')->schema([
                    Grid::make(4)->schema([
                        DateTimePicker::make('scheduled_at')->label('Programada para'),
                        DateTimePicker::make('started_at')->label('Iniciada em'),
                        DateTimePicker::make('completed_at')->label('Concluída em'),
                        SgocInput::money('estimated_cost')->label('Custo previsto')->default(0),
                        SgocInput::money('actual_cost')->label('Custo realizado')->disabled()->dehydrated(),
                    ]),
                    Textarea::make('notes')->label('Observações')->rows(4),
                ]),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->label('Número')->weight('bold')->searchable()->sortable(),
                TextColumn::make('asset.code')->label('Ativo')->searchable(),
                TextColumn::make('asset.name')->label('Descrição')->searchable()->toggleable(),
                TextColumn::make('maintenanceType.name')->label('Tipo'),
                TextColumn::make('priority.name')->label('Prioridade')->badge(),
                TextColumn::make('opened_at')->label('Aberta em')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('actual_cost')->label('Custo')->money('BRL')->sortable(),
                TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn (?string $state): string => self::statuses()[$state] ?? (string) $state)->color(fn (?string $state): string => match ($state) {
                    'completed' => 'success',
                    'cancelled' => 'danger',
                    'waiting_approval', 'waiting_parts', 'waiting_supplier' => 'warning',
                    'in_progress' => 'info',
                    default => 'gray',
                }),
            ])
            ->filters([SelectFilter::make('status')->options(self::statuses())])
            ->recordActions([
                Action::make('start')->label('Iniciar')->icon('heroicon-o-play')->color('info')->visible(fn (WorkOrder $record) => in_array($record->status, ['open', 'approved', 'paused'], true))->action(fn (WorkOrder $record) => self::changeStatus($record, 'in_progress', 'OS iniciada')),
                Action::make('complete')->label('Concluir')->icon('heroicon-o-check-circle')->color('success')->requiresConfirmation()->visible(fn (WorkOrder $record) => $record->status === 'in_progress')->action(fn (WorkOrder $record) => self::changeStatus($record, 'completed', 'OS concluída')),
                EditAction::make()->label('Editar'),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->label('Excluir selecionadas')])])
            ->defaultSort('opened_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkOrders::route('/'),
            'create' => CreateWorkOrder::route('/criar'),
            'edit' => EditWorkOrder::route('/{record}/editar'),
        ];
    }

    public static function statuses(): array
    {
        return [
            'open' => 'Aberta',
            'diagnosis' => 'Em diagnóstico',
            'waiting_approval' => 'Aguardando aprovação',
            'approved' => 'Aprovada',
            'waiting_parts' => 'Aguardando peças',
            'waiting_supplier' => 'Aguardando fornecedor',
            'in_progress' => 'Em execução',
            'paused' => 'Pausada',
            'completed' => 'Concluída',
            'cancelled' => 'Cancelada',
        ];
    }

    public static function serviceStatuses(): array
    {
        return [
            'pending' => 'Pendente',
            'in_progress' => 'Em execução',
            'completed' => 'Concluído',
            'cancelled' => 'Cancelado',
        ];
    }

    public static function materialStatuses(): array
    {
        return [
            'requested' => 'Solicitado',
            'reserved' => 'Reservado',
            'purchased' => 'Comprado',
            'applied' => 'Aplicado',
            'cancelled' => 'Cancelado',
        ];
    }

    private static function changeStatus(WorkOrder $record, string $status, string $message): void
    {
        $fromStatus = $record->status;

        $record->update([
            'status' => $status,
            'started_at' => $status === 'in_progress' ? now() : $record->started_at,
            'completed_at' => $status === 'completed' ? now() : $record->completed_at,
        ]);

        $record->registerEvent('status_changed', $message, $fromStatus, $status);

        Notification::make()->title($message)->success()->send();
    }
}
