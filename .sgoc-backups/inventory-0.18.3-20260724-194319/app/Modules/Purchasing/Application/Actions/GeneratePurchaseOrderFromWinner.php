<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Application\Actions;

use App\Modules\Purchasing\Domain\Models\PurchaseOrder;
use App\Modules\Purchasing\Domain\Models\QuotationSupplier;
use DomainException;
use Illuminate\Support\Facades\DB;

final class GeneratePurchaseOrderFromWinner
{
    public function execute(QuotationSupplier $proposal, ?int $userId = null): PurchaseOrder
    {
        $proposal->loadMissing(['quotationRequest.purchaseRequest', 'supplier', 'items.purchaseRequestItem']);

        if (! $proposal->is_winner || $proposal->status !== 'winner') {
            throw new DomainException('Somente a proposta vencedora pode gerar um pedido de compra.');
        }

        if (! $proposal->items()->exists()) {
            throw new DomainException('A proposta vencedora não possui itens cotados.');
        }

        $existing = PurchaseOrder::query()->where('quotation_supplier_id', $proposal->getKey())->first();
        if ($existing) {
            throw new DomainException("Já existe o pedido {$existing->number} para esta proposta.");
        }

        return DB::transaction(function () use ($proposal, $userId): PurchaseOrder {
            $request = $proposal->quotationRequest->purchaseRequest;
            $number = $this->nextNumber((string) $proposal->tenant_id);
            $expectedAt = $proposal->delivery_days !== null
                ? now()->startOfDay()->addDays((int) $proposal->delivery_days)->toDateString()
                : null;

            $order = PurchaseOrder::query()->create([
                'tenant_id' => $proposal->tenant_id,
                'organization_id' => $request->organization_id,
                'company_id' => $request->company_id,
                'branch_id' => $request->branch_id,
                'work_id' => $request->work_id,
                'cost_center_id' => $request->cost_center_id,
                'purchase_request_id' => $request->getKey(),
                'quotation_request_id' => $proposal->quotation_request_id,
                'quotation_supplier_id' => $proposal->getKey(),
                'supplier_id' => $proposal->supplier_id,
                'number' => $number,
                'ordered_at' => now()->toDateString(),
                'expected_at' => $expectedAt,
                'status' => 'draft',
                'payment_terms' => $proposal->payment_terms,
                'delivery_location' => $request->delivery_location,
                'notes' => $proposal->notes,
                'subtotal' => $proposal->subtotal,
                'freight_amount' => $proposal->freight_amount,
                'discount_amount' => $proposal->discount_amount,
                'other_amount' => $proposal->other_amount,
                'total_amount' => $proposal->total_amount,
                'created_by' => $userId,
            ]);

            foreach ($proposal->items()->with('purchaseRequestItem')->orderBy('created_at')->get() as $index => $quotedItem) {
                $source = $quotedItem->purchaseRequestItem;
                $order->items()->create([
                    'tenant_id' => $proposal->tenant_id,
                    'purchase_request_item_id' => $quotedItem->purchase_request_item_id,
                    'quotation_item_id' => $quotedItem->getKey(),
                    'sequence' => $source?->sequence ?? ($index + 1),
                    'description' => $source?->description ?? 'Item cotado',
                    'specification' => $source?->specification,
                    'unit' => $source?->unit ?? 'UN',
                    'quantity' => $quotedItem->quantity,
                    'unit_price' => $quotedItem->unit_price,
                    'discount_amount' => $quotedItem->discount_amount,
                    'tax_amount' => $quotedItem->tax_amount,
                    'quantity_received' => 0,
                    'status' => 'pending',
                    'notes' => $quotedItem->notes,
                ]);
            }

            $request->forceFill(['status' => 'ordered'])->save();

            return $order->load(['supplier', 'items']);
        });
    }

    private function nextNumber(string $tenantId): string
    {
        $year = now()->format('Y');
        $prefix = "PC-{$year}-";
        $last = PurchaseOrder::query()
            ->where('tenant_id', $tenantId)
            ->where('number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('number')
            ->value('number');

        $sequence = $last ? ((int) substr((string) $last, -6)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }
}
