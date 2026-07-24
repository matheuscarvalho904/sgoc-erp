<?php
namespace App\Filament\Resources\ApprovalRules\Pages;
use App\Filament\Resources\ApprovalRules\ApprovalRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
final class EditApprovalRule extends EditRecord { protected static string $resource = ApprovalRuleResource::class; protected function getHeaderActions(): array { return [DeleteAction::make()->label('Excluir')]; } }
