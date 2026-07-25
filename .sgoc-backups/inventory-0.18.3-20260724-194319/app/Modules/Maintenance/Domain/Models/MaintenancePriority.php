<?php
declare(strict_types=1);
namespace App\Modules\Maintenance\Domain\Models;
use App\Shared\Models\BaseModel;
final class MaintenancePriority extends BaseModel { protected $table='maintenance.priorities'; protected $guarded=[]; protected function casts(): array { return [...parent::casts(),'level'=>'integer','sla_hours'=>'integer']; } }
