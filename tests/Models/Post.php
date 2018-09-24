<?php

namespace Tests\Models;

use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Post extends Model
{
    use HasRelationships;

    protected $primaryKey = 'post_pk';

    public function posts()
    {
        return $this->hasManyDeep(
            self::class,
            [Like::class, User::class],
            [['likeable_type', 'likeable_id'], 'user_pk'],
            [null, 'user_user_pk']
        );
    }

    public function users()
    {
        return $this->hasManyDeep(
            User::class,
            [Like::class],
            [['likeable_type', 'likeable_id'], 'user_pk'],
            [null, 'user_user_pk']
        );
    }
}
