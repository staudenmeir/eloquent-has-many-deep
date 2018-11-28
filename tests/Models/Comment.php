<?php

namespace Tests\Models;

class Comment extends Model
{
    protected $primaryKey = 'comment_pk';

    public function user()
    {
        return $this->hasOneDeep(
            User::class,
            [Post::class],
            ['post_pk', 'user_pk'],
            ['post_post_pk', 'user_user_pk']
        );
    }
}
