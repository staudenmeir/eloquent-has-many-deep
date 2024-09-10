<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasOneDeep;
use Staudenmeir\EloquentHasManyDeep\HasTableAlias;

/**
 * @property-read \Tests\Models\Country|null $country
 * @property-read \Tests\Models\Country|null $countryWithCustomThroughTable
 * @property-read \Tests\Models\Post|null $post
 * @property-read \Tests\Models\Post|null $rootPost
 */
class Comment extends Model
{
    use HasTableAlias;
    use SoftDeletes;

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasOneDeep<\Tests\Models\Country, $this>
     */
    public function country(): HasOneDeep
    {
        return $this->hasOneDeepFromReverse(
            (new Country())->commentsFromRelations()
        );
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasOneDeep<\Tests\Models\Country, $this>
     */
    public function countryWithCustomThroughTable(): HasOneDeep
    {
        return $this->hasOneDeepFromReverse(
            (new Country())->commentsFromRelationsWithCustomThroughTable()
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Tests\Models\Comment>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasOneDeep<\Tests\Models\Post, $this>
     */
    public function rootPost(): HasOneDeep
    {
        return $this->hasOneDeepFromReverse(
            (new Post())->nestedCommentReplies()
        );
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Tag, $this>
     */
    public function tags(): HasManyDeep
    {
        return $this->hasManyDeepFromReverse(
            (new Tag())->comments()
        );
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasOneDeep<\Tests\Models\User, $this>
     */
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
