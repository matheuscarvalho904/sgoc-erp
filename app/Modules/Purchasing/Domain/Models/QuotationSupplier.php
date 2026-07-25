<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Domain\Models;

use App\Modules\Foundation\Domain\Models\Tenant;
use App\Shared\Models\TransactionModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class QuotationSupplier extends TransactionModel
{
    protected $table = 'purchasing.quotation_suppliers';
    protected $fillable = ['tenant_id','quotation_request_id','supplier_id','status','proposal_number','proposal_date','validity_date','delivery_days','freight_amount','discount_amount','other_amount','payment_terms','notes','attachment_path','subtotal','total_amount','is_winner','responded_at'];
    protected function casts(): array { return [...parent::casts(),'proposal_date'=>'date','validity_date'=>'date','freight_amount'=>'decimal:4','discount_amount'=>'decimal:4','other_amount'=>'decimal:4','subtotal'=>'decimal:4','total_amount'=>'decimal:4','is_winner'=>'boolean','responded_at'=>'immutable_datetime']; }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function quotationRequest(): BelongsTo { return $this->belongsTo(QuotationRequest::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function items(): HasMany { return $this->hasMany(QuotationItem::class); }
}
