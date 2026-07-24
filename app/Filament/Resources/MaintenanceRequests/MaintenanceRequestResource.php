<?php

declare(strict_types=1);

namespace App\Filament\Resources\MaintenanceRequests;

use App\Filament\Resources\MaintenanceRequests\Pages\CreateMaintenanceRequest;
use App\Filament\Resources\MaintenanceRequests\Pages\EditMaintenanceRequest;
use App\Filament\Resources\MaintenanceRequests\Pages\ListMaintenanceRequests;
use App\Modules\Assets\Domain\Models\Asset;
use App\Modules\Foundation\Domain\Models\Branch;
use App\Modules\Foundation\Domain\Models\Company;
use App\Modules\Foundation\Domain\Models\CostCenter;
use App\Modules\Foundation\Domain\Models\Organization;
use App\Modules\Foundation\Domain\Models\Tenant;
use App\Modules\Foundation\Domain\Models\Work;
use App\Modules\Maintenance\Domain\Models\MaintenancePriority;
use App\Modules\Maintenance\Domain\Models\MaintenanceRequest;
use App\Modules\Maintenance\Domain\Models\WorkOrder;
use App\Shared\Filament\SgocInput;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use UnitEnum;

final class MaintenanceRequestResource extends Resource
{
    protected static ?string $model = MaintenanceRequest::class;
    protected static ?string $recordTitleAttribute = 'number';
    protected static ?string $modelLabel = 'solicitação de manutenção';
    protected static ?string $pluralModelLabel = 'solicitações de manutenção';
    protected static ?string $navigationLabel = 'Solicitações de manutenção';
    protected static string|UnitEnum|null $navigationGroup = 'Oficina e Manutenção';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Hidden::make('tenant_id')->default(fn () => Tenant::query()->value('id')),
            Hidden::make('organization_id')->default(fn () => Organization::query()->value('id')),
            Hidden::make('requester_id')->default(fn () => auth()->id()),
            Section::make('Solicitação')->schema([
                Grid::make(['default' => 1, 'md' => 2, 'xl' => 4])->schema([
                    DateTimePicker::make('requested_at')->label('Data e hora')->default(now())->required(),
                    Select::make('status')->label('Status')->options(self::statuses())->default('new')->required(),
                    Select::make('asset_id')->label('Ativo/Equipamento')
                        ->options(fn (): array => Asset::query()->orderBy('code')->get(['id', 'code', 'name', 'plate'])->mapWithKeys(fn (Asset $asset): array => [$asset->id => trim($asset->code.' - '.$asset->name.($asset->plate ? ' | '.$asset->plate : ''))])->all())
                        ->searchable()->preload()->required(),
                    Select::make('priority_id')->label('Prioridade')->options(fn () => MaintenancePriority::query()->where('status', 'active')->orderByDesc('level')->pluck('name', 'id')->all())->searchable()->preload(),
                    Select::make('company_id')->label('Empresa')->options(fn () => Company::query()->pluck('name', 'id')->all())->searchable()->preload(),
                    Select::make('branch_id')->label('Filial')->options(fn () => Branch::query()->pluck('name', 'id')->all())->searchable()->preload(),
                    Select::make('work_id')->label('Obra')->options(fn () => Work::query()->pluck('name', 'id')->all())->searchable()->preload(),
                    Select::make('cost_center_id')->label('Centro de custo')->options(fn () => CostCenter::query()->pluck('name', 'id')->all())->searchable()->preload(),
                    SgocInput::quantity('hourmeter', 2)->label('Horímetro informado'),
                    SgocInput::quantity('odometer', 2)->label('Hodômetro informado'),
                ]),
                Textarea::make('symptom')->label('Sintoma/Problema encontrado')->rows(5)->required(),
                Textarea::make('location_details')->label('Localização e detalhes adicionais')->rows(3),
                Textarea::make('review_notes')->label('Parecer da análise')->rows(3)->visible(fn (?MaintenanceRequest $record) => filled($record)),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('number')->label('Número')->weight('bold')->searchable()->sortable(),
            TextColumn::make('asset.code')->label('Ativo')->searchable(),
            TextColumn::make('asset.name')->label('Descrição')->searchable(),
            TextColumn::make('priority.name')->label('Prioridade')->badge(),
            TextColumn::make('requested_at')->label('Solicitada em')->dateTime('d/m/Y H:i')->sortable(),
            TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn (?string $state): string => self::statuses()[$state] ?? (string) $state)->color(fn (?string $state): string => match ($state) {
                'approved', 'work_order_created' => 'success', 'rejected' => 'danger', 'under_review' => 'warning', default => 'gray',
            }),
        ])->filters([SelectFilter::make('status')->options(self::statuses())])
          ->recordActions([
              Action::make('review')->label('Analisar')->icon('heroicon-o-magnifying-glass')->color('warning')->visible(fn (MaintenanceRequest $record): bool => $record->status === 'new')->action(fn (MaintenanceRequest $record) => $record->update(['status' => 'under_review', 'reviewer_id' => auth()->id(), 'reviewed_at' => now()])),
              Action::make('approve')->label('Aprovar')->icon('heroicon-o-check')->color('success')->requiresConfirmation()->visible(fn (MaintenanceRequest $record): bool => in_array($record->status, ['new', 'under_review'], true))->action(function (MaintenanceRequest $record): void { $record->update(['status' => 'approved', 'reviewer_id' => auth()->id(), 'reviewed_at' => now()]); Notification::make()->title('Solicitação aprovada')->success()->send(); }),
              Action::make('reject')->label('Reprovar')->icon('heroicon-o-x-mark')->color('danger')->requiresConfirmation()->visible(fn (MaintenanceRequest $record): bool => in_array($record->status, ['new', 'under_review'], true))->action(fn (MaintenanceRequest $record) => $record->update(['status' => 'rejected', 'reviewer_id' => auth()->id(), 'reviewed_at' => now()])),
              Action::make('generate_os')->label('Gerar OS')->icon('heroicon-o-document-plus')->color('info')->requiresConfirmation()->visible(fn (MaintenanceRequest $record): bool => $record->status === 'approved' && blank($record->work_order_id))->action(fn (MaintenanceRequest $record) => self::generateWorkOrder($record)),
              EditAction::make()->label('Editar'),
          ])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->label('Excluir selecionadas')])])->defaultSort('requested_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => ListMaintenanceRequests::route('/'), 'create' => CreateMaintenanceRequest::route('/criar'), 'edit' => EditMaintenanceRequest::route('/{record}/editar')];
    }

    public static function statuses(): array
    {
        return ['new' => 'Nova', 'under_review' => 'Em análise', 'approved' => 'Aprovada', 'rejected' => 'Reprovada', 'work_order_created' => 'Gerou OS', 'cancelled' => 'Cancelada'];
    }

    private static function generateWorkOrder(MaintenanceRequest $request): void
    {
        DB::transaction(function () use ($request): void {
            $sequence = WorkOrder::query()->lockForUpdate()->count() + 1;
            $workOrder = WorkOrder::query()->create([
                'tenant_id' => $request->tenant_id, 'organization_id' => $request->organization_id, 'company_id' => $request->company_id,
                'branch_id' => $request->branch_id, 'work_id' => $request->work_id, 'cost_center_id' => $request->cost_center_id,
                'asset_id' => $request->asset_id, 'priority_id' => $request->priority_id, 'requester_id' => $request->requester_id,
                'number' => 'OS-'.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT), 'status' => 'open', 'source' => 'maintenance_request',
                'opened_at' => now(), 'entry_hourmeter' => $request->hourmeter, 'entry_odometer' => $request->odometer,
                'symptom' => $request->symptom, 'notes' => 'Gerada pela solicitação '.$request->number,
            ]);
            $request->update(['work_order_id' => $workOrder->id, 'status' => 'work_order_created']);
        });
        Notification::make()->title('Ordem de serviço gerada com sucesso')->success()->send();
    }
}
