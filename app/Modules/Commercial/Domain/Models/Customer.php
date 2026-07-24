<?php
 declare(strict_types=1);
 namespace App\Modules\Commercial\Domain\Models;
 use App\Shared\Models\BaseModel;
 final class Customer extends BaseModel { protected $table = 'commercial.customers'; protected $fillable = ['tenant_id','organization_id','code','person_type','document','legal_name','trade_name','state_registration','municipal_registration','email','phone','zip_code','street','number','complement','district','city','state','payment_term_id','credit_limit','notes','status','settings','external_data','external_data_synced_at']; protected function casts(): array { return [...parent::casts(), 'settings'=>'array', 'external_data'=>'array', 'external_data_synced_at'=>'immutable_datetime']; } }
