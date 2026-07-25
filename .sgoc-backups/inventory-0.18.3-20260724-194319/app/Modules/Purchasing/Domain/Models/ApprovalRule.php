<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Domain\Models;

use App\Models\User;
use App\Modules\Foundation\Domain\Models\Company;
use App\Modules\Foundation\Domain\Models\Tenant;
use App\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ApprovalRule extends BaseModel
{
    protected $table = 'purchasing.approval_rules';
    protected $fillable = ['tenant_id','company_id','name','min_amount','max_amount','approver_role_id','approver_user_id','approval_order','status'];
    protected function casts(): array { return [...parent::casts(),'min_amount'=>'decimal:4','max_amount'=>'decimal:4']; }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approver_user_id'); }
}
