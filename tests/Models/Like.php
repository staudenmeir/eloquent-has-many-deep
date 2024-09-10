<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Like extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Tests\Models\User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
