<?php
declare(strict_types=1);
namespace App\Modules\Inventory\Domain\Models;
use App\Shared\Models\BaseModel;
final class StockRequestItem extends BaseModel { protected $table='inventory.stock_request_items'; protected $guarded=[]; }
