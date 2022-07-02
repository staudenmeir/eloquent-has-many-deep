<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;
use Staudenmeir\EloquentHasManyDeep\HasTableAlias;

class Comment extends Model
{
    use HasEagerLimit;
    use SoftDeletes;
    use HasTableAlias;

    public function country()
    {
        return $this->hasOneDeepFromReverse(
            (new Country())->commentsFromRelations()
        );
    }

    public function countryWithCustomThroughTable()
    {
        return $this->hasOneDeepFromReverse(
            (new Country())->commentsFromRelationsWithCustomThroughTable()
        );
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function rootPost()
    {
        return $this->hasOneDeepFromReverse(
            (new Post())->nestedCommentReplies()
        );
    }

    public function tags()
    {
        return $this->hasManyDeepFromReverse(
            (new Tag())->comments()
        );
    }

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
