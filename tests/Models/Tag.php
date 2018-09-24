<?php

namespace Tests\Models;

use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Tag extends Model
{
    use HasRelationships;

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
