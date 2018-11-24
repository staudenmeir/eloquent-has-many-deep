<?php

namespace Tests\Models;

class Like extends Model
{
    protected $primaryKey = 'like_pk';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
