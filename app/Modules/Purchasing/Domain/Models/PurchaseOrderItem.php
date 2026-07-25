<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Domain\Models;

use App\Modules\Foundation\Domain\Models\Tenant;
use App\Shared\Models\TransactionModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PurchaseOrderItem extends TransactionModel
{
    protected $table = 'purchasing.purchase_order_items';

    protected $fillable = [
        'tenant_id','purchase_order_id','purchase_request_item_id','quotation_item_id','sequence',
        'description','specification','unit','quantity','unit_price','discount_amount','tax_amount',
        'quantity_received','status','notes',
    ];

    protected function casts(): array
    {
        return [
            ...parent::casts(), 'quantity' => 'decimal:4', 'unit_price' => 'decimal:4',
            'discount_amount' => 'decimal:4', 'tax_amount' => 'decimal:4',
            'total_amount' => 'decimal:4', 'quantity_received' => 'decimal:4',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function purchaseRequestItem(): BelongsTo { return $this->belongsTo(PurchaseRequestItem::class); }
    public function quotationItem(): BelongsTo { return $this->belongsTo(QuotationItem::class); }
}
