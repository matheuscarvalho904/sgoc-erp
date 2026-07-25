<?php

declare(strict_types=1);

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Base para cadastros mestres que admitem exclusão lógica.
 *
 * Exemplos: produtos, fornecedores, ativos, almoxarifados e localizações.
 */
abstract class BaseModel extends AbstractModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return parent::casts() + [
            'deleted_at' => 'immutable_datetime',
        ];
    }
}
