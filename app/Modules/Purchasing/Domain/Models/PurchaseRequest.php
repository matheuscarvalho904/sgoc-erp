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

final class PurchaseRequest extends TransactionModel
{
    protected $table = 'purchasing.purchase_requests';

    protected $fillable = [
        'tenant_id', 'organization_id', 'company_id', 'branch_id', 'work_id', 'cost_center_id',
        'requester_id', 'category_id', 'number', 'requested_at', 'needed_at', 'priority',
        'justification', 'delivery_location', 'notes', 'status', 'total_estimated',
        'submitted_at', 'approved_at', 'approved_by', 'rejected_at', 'rejected_by', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'requested_at' => 'date', 'needed_at' => 'date',
            'total_estimated' => 'decimal:4',
            'submitted_at' => 'immutable_datetime', 'approved_at' => 'immutable_datetime',
            'rejected_at' => 'immutable_datetime',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function work(): BelongsTo { return $this->belongsTo(Work::class); }
    public function costCenter(): BelongsTo { return $this->belongsTo(CostCenter::class); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requester_id'); }
    public function category(): BelongsTo { return $this->belongsTo(PurchaseCategory::class, 'category_id'); }
    public function items(): HasMany { return $this->hasMany(PurchaseRequestItem::class, 'purchase_request_id'); }
    public function approvals(): HasMany { return $this->hasMany(PurchaseApproval::class, 'purchase_request_id'); }
    public function quotations(): HasMany { return $this->hasMany(QuotationRequest::class, 'purchase_request_id'); }
}
