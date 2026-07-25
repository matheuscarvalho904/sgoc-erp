<?php
 declare(strict_types=1);
 namespace App\Modules\Commercial\Domain\Models;
 use App\Shared\Models\BaseModel;
 final class PaymentMethod extends BaseModel { protected $table = 'commercial.payment_methods'; protected $fillable = ['tenant_id','code','name','type','requires_bank_data','status']; }
