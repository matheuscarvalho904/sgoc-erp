<?php
declare(strict_types=1);
namespace App\Modules\Inventory\Domain\Models;
use App\Shared\Models\TransactionModel;
final class StockRequestItem extends TransactionModel { protected $table='inventory.stock_request_items'; protected $guarded=[]; }
