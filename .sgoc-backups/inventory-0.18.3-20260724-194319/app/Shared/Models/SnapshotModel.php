<?php

declare(strict_types=1);

namespace App\Shared\Models;

/**
 * Base para saldos, consolidações e snapshots derivados.
 *
 * Não usa SoftDeletes porque representa o estado corrente calculado.
 */
abstract class SnapshotModel extends AbstractModel
{
}
