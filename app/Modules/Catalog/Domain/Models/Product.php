<?php
 declare(strict_types=1);
 namespace App\Modules\Catalog\Domain\Models;
 use App\Shared\Models\BaseModel;
 final class Product extends BaseModel { protected $table = 'catalog.products'; protected $fillable = ['tenant_id','product_class_id','category_id','unit_id','brand_id','code','name','description','product_type','barcode','ncm','cest','sku','track_stock','minimum_stock','maximum_stock','average_cost','last_purchase_price','status','settings']; }
