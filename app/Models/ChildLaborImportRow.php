<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildLaborImportRow extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['normalized_data' => 'array', 'warnings' => 'array', 'errors' => 'array'];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ChildLaborImportBatch::class, 'batch_id');
    }
}
