<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Domain\Models;

use App\Models\User;
use App\Modules\Foundation\Domain\Models\Tenant;
use App\Shared\Models\TransactionModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PurchaseApproval extends TransactionModel
{
    protected $table = 'purchasing.purchase_approvals';
    protected $fillable = ['tenant_id','purchase_request_id','approval_rule_id','approval_order','approver_user_id','status','requested_at','decided_at','decision_by','comments'];
    protected function casts(): array { return [...parent::casts(),'requested_at'=>'immutable_datetime','decided_at'=>'immutable_datetime']; }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function purchaseRequest(): BelongsTo { return $this->belongsTo(PurchaseRequest::class); }
    public function rule(): BelongsTo { return $this->belongsTo(ApprovalRule::class, 'approval_rule_id'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approver_user_id'); }
}
