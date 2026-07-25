<?php

declare(strict_types=1);

namespace App\Modules\Documents\Domain\Models;

use App\Shared\Models\TransactionModel;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class Document extends TransactionModel
{
    protected $table = 'documents.documents';
    protected $guarded = [];

    protected function casts(): array
    {
        return [...parent::casts(), 'issued_at' => 'datetime', 'metadata' => 'array'];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
