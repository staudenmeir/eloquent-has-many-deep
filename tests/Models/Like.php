<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Like extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
