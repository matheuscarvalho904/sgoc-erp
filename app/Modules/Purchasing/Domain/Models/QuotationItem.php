<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Domain\Models;

use App\Modules\Foundation\Domain\Models\Tenant;
use App\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class QuotationItem extends BaseModel
{
    protected $table = 'purchasing.quotation_items';
    protected $fillable = ['tenant_id','quotation_supplier_id','purchase_request_item_id','quantity','unit_price','discount_amount','tax_amount','brand','notes','selected'];
    protected function casts(): array { return [...parent::casts(),'quantity'=>'decimal:4','unit_price'=>'decimal:4','discount_amount'=>'decimal:4','tax_amount'=>'decimal:4','total_amount'=>'decimal:4','selected'=>'boolean']; }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function quotationSupplier(): BelongsTo { return $this->belongsTo(QuotationSupplier::class); }
    public function purchaseRequestItem(): BelongsTo { return $this->belongsTo(PurchaseRequestItem::class); }
}
