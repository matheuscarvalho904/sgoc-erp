<?php
 declare(strict_types=1);
 namespace App\Modules\Catalog\Domain\Models;
 use App\Shared\Models\BaseModel;
 final class ProductCategory extends BaseModel { protected $table = 'catalog.product_categories'; protected $fillable = ['tenant_id','parent_id','code','name','description','status']; }
