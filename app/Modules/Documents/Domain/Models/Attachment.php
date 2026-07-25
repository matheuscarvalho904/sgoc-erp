<?php

declare(strict_types=1);

namespace App\Modules\Documents\Domain\Models;

use App\Shared\Models\TransactionModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class Attachment extends TransactionModel
{
    protected $table = 'documents.attachments';
    protected $guarded = [];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
