<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Application\Actions;

use App\Models\User;
use App\Modules\Purchasing\Domain\Models\PurchaseRequest;
use App\Modules\Purchasing\Domain\Models\QuotationRequest;
use DomainException;
use Illuminate\Support\Facades\DB;

final class CreateQuotationFromPurchaseRequest
{
    public function execute(PurchaseRequest $request, User $user): QuotationRequest
    {
        if ($request->status !== 'approved') {
            throw new DomainException('A solicitação precisa estar aprovada antes da geração da cotação.');
        }

        return DB::transaction(function () use ($request, $user): QuotationRequest {
            $existing = $request->quotations()->whereNotIn('status', ['cancelled'])->first();

            if ($existing) {
                throw new DomainException('Esta solicitação já possui uma cotação ativa.');
            }

            $sequence = QuotationRequest::query()
                ->where('tenant_id', $request->tenant_id)
                ->whereYear('issued_at', now()->year)
                ->count() + 1;

            $quotation = QuotationRequest::query()->create([
                'tenant_id' => $request->tenant_id,
                'purchase_request_id' => $request->id,
                'number' => sprintf('COT-%s-%06d', now()->format('Y'), $sequence),
                'issued_at' => today(),
                'response_deadline' => today()->addDays(5),
                'status' => 'draft',
                'created_by' => $user->getKey(),
                'notes' => 'Cotação gerada automaticamente a partir da solicitação '.$request->number.'.',
            ]);

            $request->update(['status' => 'quoting']);

            return $quotation;
        });
    }
}
