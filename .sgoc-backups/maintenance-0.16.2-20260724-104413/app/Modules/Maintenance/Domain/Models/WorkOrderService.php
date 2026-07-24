<?php
declare(strict_types=1);
namespace App\Modules\Maintenance\Domain\Models;
use App\Shared\Models\BaseModel;
final class WorkOrderService extends BaseModel { protected $table='maintenance.work_order_services'; protected $guarded=[]; protected function casts(): array { return [...parent::casts(),'estimated_hours'=>'decimal:2','actual_hours'=>'decimal:2','hourly_rate'=>'decimal:4','total_cost'=>'decimal:4']; } }
