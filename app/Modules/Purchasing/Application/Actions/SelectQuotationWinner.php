<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Application\Actions;

use App\Modules\Purchasing\Domain\Models\QuotationSupplier;
use DomainException;
use Illuminate\Support\Facades\DB;

final class SelectQuotationWinner
{
    public function execute(QuotationSupplier $proposal): QuotationSupplier
    {
        if (! in_array($proposal->status, ['answered', 'winner'], true)) {
            throw new DomainException('Somente propostas respondidas podem ser definidas como vencedoras.');
        }

        if (! $proposal->items()->exists()) {
            throw new DomainException('Cadastre os preços dos itens antes de escolher a proposta vencedora.');
        }

        return DB::transaction(function () use ($proposal): QuotationSupplier {
            QuotationSupplier::query()
                ->where('quotation_request_id', $proposal->quotation_request_id)
                ->whereKeyNot($proposal->getKey())
                ->update(['is_winner' => false]);

            QuotationSupplier::query()
                ->where('quotation_request_id', $proposal->quotation_request_id)
                ->whereKeyNot($proposal->getKey())
                ->where('status', 'winner')
                ->update(['status' => 'answered']);

            $proposal->forceFill([
                'is_winner' => true,
                'status' => 'winner',
            ])->save();

            $proposal->quotationRequest()->update([
                'status' => 'closed',
                'closed_at' => now(),
            ]);

            return $proposal->refresh();
        });
    }
}
