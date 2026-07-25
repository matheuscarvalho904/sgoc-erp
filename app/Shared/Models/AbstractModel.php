<?php

declare(strict_types=1);

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Base técnico de todos os models SGOC.
 *
 * Mantém apenas comportamentos universais. Soft delete não é aplicado aqui,
 * pois o ciclo de vida depende da natureza da entidade.
 */
abstract class AbstractModel extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
