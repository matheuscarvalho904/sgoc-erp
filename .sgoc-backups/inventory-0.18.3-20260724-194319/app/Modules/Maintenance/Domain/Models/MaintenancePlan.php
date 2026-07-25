<?php
declare(strict_types=1);
namespace App\Modules\Maintenance\Domain\Models;
use App\Shared\Models\BaseModel;
final class MaintenancePlan extends BaseModel { protected $table='maintenance.plans'; protected $guarded=[]; protected function casts(): array { return [...parent::casts(),'interval_value'=>'decimal:2','advance_value'=>'decimal:2','next_due_date'=>'date','next_due_meter'=>'decimal:2','auto_create_work_order'=>'boolean']; } }
