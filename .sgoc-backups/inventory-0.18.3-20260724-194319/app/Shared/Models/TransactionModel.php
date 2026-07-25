<?php

declare(strict_types=1);

namespace App\Shared\Models;

/**
 * Base para fatos e documentos operacionais.
 *
 * Não usa SoftDeletes: lançamentos devem ser cancelados ou estornados,
 * preservando a trilha de auditoria.
 */
abstract class TransactionModel extends AbstractModel
{
}
