<?php
 declare(strict_types=1);
 namespace App\Modules\Catalog\Domain\Models;
 use App\Shared\Models\BaseModel;
 final class Unit extends BaseModel { protected $table = 'catalog.units'; protected $fillable = ['tenant_id','code','name','symbol','decimal_places','status']; }
