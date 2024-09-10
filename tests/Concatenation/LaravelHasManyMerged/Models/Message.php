<?php

namespace Tests\Concatenation\LaravelHasManyMerged\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Tests\Concatenation\LaravelHasManyMerged\Models\Attachment>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
}
