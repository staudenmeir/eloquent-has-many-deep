<?php

namespace Tests\Concatenation\EloquentJsonRelations\Models;

class User extends Model
{
    protected $casts = [
        'options' => 'json',
    ];
}
