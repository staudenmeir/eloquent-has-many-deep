<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;

/**
 * @property-read \Tests\Models\User|null $user
 */
class Post extends Model
{
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function commentReplies(): HasManyDeep
    {
        return $this->hasManyDeep(
            Comment::class,
            [Comment::class.' as alias'],
            [null, 'parent_id']
        );
    }

    public function commentRepliesFromRelations(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations(
            $this->comments(),
            (new Comment())->setAlias('alias')->replies()
        );
    }

    public function commentsWithTrashed(): HasMany
    {
        return $this->hasMany(Comment::class)->withTrashed();
    }

    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function nestedCommentReplies(): HasManyDeep
    {
        return $this->hasManyDeep(
            Comment::class,
            [Comment::class.' as alias', Comment::class.' as alias2'],
            [null, 'parent_id', 'parent_id']
        );
    }

    public function posts(): HasManyDeep
    {
        return $this->hasManyDeep(
            static::class,
            [Like::class, User::class],
            [['likeable_type', 'likeable_id'], 'id'],
            [null, 'user_id']
        );
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function users(): HasManyDeep
    {
        return $this->hasManyDeep(
            User::class,
            [Like::class],
            [['likeable_type', 'likeable_id'], 'id'],
            [null, 'user_id']
        );
    }

    public function usersFromRelations(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->likes(), (new Like())->user());
    }
}
