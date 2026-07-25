<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Domain\Models;

use App\Models\User;
use App\Modules\Foundation\Domain\Models\Branch;
use App\Modules\Foundation\Domain\Models\Company;
use App\Modules\Foundation\Domain\Models\CostCenter;
use App\Modules\Foundation\Domain\Models\Organization;
use App\Modules\Foundation\Domain\Models\Tenant;
use App\Modules\Foundation\Domain\Models\Work;
use App\Shared\Models\TransactionModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PurchaseOrder extends TransactionModel
{
    protected $table = 'purchasing.purchase_orders';

    protected $fillable = [
        'tenant_id','organization_id','company_id','branch_id','work_id','cost_center_id',
        'purchase_request_id','quotation_request_id','quotation_supplier_id','supplier_id',
        'number','ordered_at','expected_at','status','payment_terms','delivery_location','notes',
        'subtotal','freight_amount','discount_amount','other_amount','total_amount','created_by',
        'approved_by','approved_at','issued_at','cancelled_at','cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'ordered_at' => 'date', 'expected_at' => 'date',
            'subtotal' => 'decimal:4', 'freight_amount' => 'decimal:4',
            'discount_amount' => 'decimal:4', 'other_amount' => 'decimal:4', 'total_amount' => 'decimal:4',
            'approved_at' => 'immutable_datetime', 'issued_at' => 'immutable_datetime', 'cancelled_at' => 'immutable_datetime',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function work(): BelongsTo { return $this->belongsTo(Work::class); }
    public function costCenter(): BelongsTo { return $this->belongsTo(CostCenter::class); }
    public function purchaseRequest(): BelongsTo { return $this->belongsTo(PurchaseRequest::class); }
    public function quotationRequest(): BelongsTo { return $this->belongsTo(QuotationRequest::class); }
    public function quotationSupplier(): BelongsTo { return $this->belongsTo(QuotationSupplier::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function items(): HasMany { return $this->hasMany(PurchaseOrderItem::class); }
}
