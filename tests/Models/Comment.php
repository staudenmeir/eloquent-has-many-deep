<?php

namespace Tests\Models;

use Staudenmeir\EloquentEagerLimit\HasEagerLimit;

class Comment extends Model
{
    use HasEagerLimit;

    public function user()
    {
        return $this->hasOneDeep(
            User::class,
            [Post::class],
            ['id', 'id'],
            ['post_id', 'user_id']
        );
    }
}
