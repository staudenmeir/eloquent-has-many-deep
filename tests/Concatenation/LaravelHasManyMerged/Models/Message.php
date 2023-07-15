<?php

namespace Tests\Concatenation\LaravelHasManyMerged\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
}
