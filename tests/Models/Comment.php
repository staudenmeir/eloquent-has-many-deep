<?php

namespace Tests\Models;

use Staudenmeir\EloquentEagerLimit\HasEagerLimit;
use Staudenmeir\EloquentHasManyDeep\HasTableAlias;

class Comment extends Model
{
    use HasEagerLimit, HasTableAlias;

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
