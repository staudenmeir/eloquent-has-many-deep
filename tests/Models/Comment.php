<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasOneDeep;
use Staudenmeir\EloquentHasManyDeep\HasTableAlias;

class Comment extends Model
{
    use HasTableAlias;
    use SoftDeletes;

    public function country(): HasOneDeep
    {
        return $this->hasOneDeepFromReverse(
            (new Country())->commentsFromRelations()
        );
    }

    public function countryWithCustomThroughTable(): HasOneDeep
    {
        return $this->hasOneDeepFromReverse(
            (new Country())->commentsFromRelationsWithCustomThroughTable()
        );
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function rootPost(): HasOneDeep
    {
        return $this->hasOneDeepFromReverse(
            (new Post())->nestedCommentReplies()
        );
    }

    public function tags(): HasManyDeep
    {
        return $this->hasManyDeepFromReverse(
            (new Tag())->comments()
        );
    }

    public function user(): HasOneDeep
    {
        return $this->hasOneDeep(
            User::class,
            [Post::class],
            ['id', 'id'],
            ['post_id', 'user_id']
        );
    }
}
