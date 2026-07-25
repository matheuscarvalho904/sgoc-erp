<?php
declare(strict_types=1);
namespace App\Modules\Workflow\Domain\Models;
use App\Shared\Models\BaseModel;
final class WorkflowDecision extends BaseModel { protected $table='workflow.decisions'; protected $guarded=[]; protected function casts(): array { return [...parent::casts(),'settings'=>'array','conditions'=>'array','metadata'=>'array','started_at'=>'datetime','completed_at'=>'datetime','decided_at'=>'datetime']; } }
