<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChildLaborImportBatch extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['committed_at' => 'datetime', 'reversed_at' => 'datetime'];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(ChildLaborImportRow::class, 'batch_id');
    }
}
