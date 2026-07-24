<?php
 declare(strict_types=1);
 namespace App\Modules\Catalog\Domain\Models;
 use App\Shared\Models\BaseModel;
 final class Brand extends BaseModel { protected $table = 'catalog.brands'; protected $fillable = ['tenant_id','code','name','manufacturer_name','website','status']; }
