<?php

namespace Tests\Models;

class Like extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
