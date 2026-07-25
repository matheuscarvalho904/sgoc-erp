<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Application\Actions;

use App\Modules\Purchasing\Domain\Models\QuotationSupplier;
use Illuminate\Support\Facades\DB;

final class RecalculateQuotationSupplier
{
    public function execute(QuotationSupplier $proposal): QuotationSupplier
    {
        return DB::transaction(function () use ($proposal): QuotationSupplier {
            $subtotal = (float) $proposal->items()->sum('total_amount');
            $total = max(
                0,
                $subtotal
                + (float) $proposal->freight_amount
                + (float) $proposal->other_amount
                - (float) $proposal->discount_amount,
            );

            $proposal->forceFill([
                'subtotal' => $subtotal,
                'total_amount' => $total,
                'status' => $proposal->status === 'invited' ? 'answered' : $proposal->status,
                'responded_at' => $proposal->responded_at ?? now(),
            ])->saveQuietly();

            return $proposal->refresh();
        });
    }
}
