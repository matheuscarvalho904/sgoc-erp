<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Application\Actions;

use App\Models\User;
use App\Modules\Purchasing\Domain\Models\PurchaseRequest;
use DomainException;
use Illuminate\Support\Facades\DB;

final class RejectPurchaseRequest
{
    public function execute(PurchaseRequest $request, User $user): PurchaseRequest
    {
        if ($request->status !== 'pending_approval') {
            throw new DomainException('Esta solicitação não está aguardando aprovação.');
        }

        return DB::transaction(function () use ($request, $user): PurchaseRequest {
            $approval = $request->approvals()->where('status', 'pending')->orderBy('approval_order')->first();

            if (! $approval) {
                throw new DomainException('Não existe aprovação pendente para esta solicitação.');
            }

            $isSuperAdmin = method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin($request->tenant_id);

            if ($approval->approver_user_id !== null && (int) $approval->approver_user_id !== (int) $user->getKey() && ! $isSuperAdmin) {
                throw new DomainException('Esta etapa está atribuída a outro aprovador.');
            }

            $approval->update([
                'status' => 'rejected',
                'decided_at' => now(),
                'decision_by' => $user->getKey(),
                'comments' => 'Solicitação reprovada pelo aprovador.',
            ]);

            $request->approvals()->where('status', 'pending')->update(['status' => 'cancelled']);

            $request->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by' => $user->getKey(),
                'rejection_reason' => 'Reprovada durante o fluxo de aprovação.',
            ]);

            return $request->refresh();
        });
    }
}
