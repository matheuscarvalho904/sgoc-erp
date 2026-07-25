<?php
declare(strict_types=1);
namespace App\Modules\Maintenance\Domain\Models;
use App\Shared\Models\TransactionModel;
final class WorkOrderEvent extends TransactionModel { protected $table='maintenance.work_order_events'; protected $guarded=[]; protected function casts(): array { return [...parent::casts(),'data'=>'array','occurred_at'=>'datetime']; } }
