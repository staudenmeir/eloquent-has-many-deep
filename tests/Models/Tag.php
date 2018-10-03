<?php

namespace Tests\Models;

class Tag extends Model
{
    protected $primaryKey = 'tag_pk';

    public function comments()
    {
        return $this->hasManyDeep(
            Comment::class,
            ['taggables', Post::class],
            [null, 'post_pk'],
            [null, ['taggable_type', 'taggable_id']]
        );
    }
}
