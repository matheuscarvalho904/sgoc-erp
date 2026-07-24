<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Application\Actions;

use App\Modules\Purchasing\Domain\Models\ApprovalRule;
use App\Modules\Purchasing\Domain\Models\PurchaseApproval;
use App\Modules\Purchasing\Domain\Models\PurchaseRequest;
use DomainException;
use Illuminate\Support\Facades\DB;

final class SubmitPurchaseRequest
{
    public function execute(PurchaseRequest $request): PurchaseRequest
    {
        if ($request->status !== 'draft' && $request->status !== 'rejected') {
            throw new DomainException('Somente solicitações em rascunho ou reprovadas podem ser enviadas.');
        }

        if (! $request->items()->exists()) {
            throw new DomainException('Inclua pelo menos um item antes de enviar a solicitação.');
        }

        return DB::transaction(function () use ($request): PurchaseRequest {
            $total = (float) $request->items()->sum('total_estimated');

            $rules = ApprovalRule::query()
                ->where('tenant_id', $request->tenant_id)
                ->where('status', 'active')
                ->where(function ($query) use ($request): void {
                    $query->whereNull('company_id')->orWhere('company_id', $request->company_id);
                })
                ->where('min_amount', '<=', $total)
                ->where(function ($query) use ($total): void {
                    $query->whereNull('max_amount')->orWhere('max_amount', '>=', $total);
                })
                ->orderBy('approval_order')
                ->get();

            if ($rules->isEmpty()) {
                throw new DomainException('Nenhuma alçada de aprovação ativa atende ao valor desta solicitação.');
            }

            $request->approvals()->delete();

            foreach ($rules as $rule) {
                PurchaseApproval::query()->create([
                    'tenant_id' => $request->tenant_id,
                    'purchase_request_id' => $request->id,
                    'approval_rule_id' => $rule->id,
                    'approval_order' => $rule->approval_order,
                    'approver_user_id' => $rule->approver_user_id,
                    'status' => 'pending',
                    'requested_at' => now(),
                ]);
            }

            $request->update([
                'status' => 'pending_approval',
                'total_estimated' => $total,
                'submitted_at' => now(),
                'approved_at' => null,
                'approved_by' => null,
                'rejected_at' => null,
                'rejected_by' => null,
                'rejection_reason' => null,
            ]);

            return $request->refresh();
        });
    }
}
