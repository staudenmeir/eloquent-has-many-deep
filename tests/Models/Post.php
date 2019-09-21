<?php

namespace Tests\Models;

class Post extends Model
{
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function commentReplies()
    {
        return $this->hasManyDeep(
            Comment::class,
            [Comment::class.' as alias'],
            [null, 'parent_id']
        );
    }

    public function commentRepliesFromRelations()
    {
        return $this->hasManyDeepFromRelations(
            $this->comments(),
            (new Comment)->setAlias('alias')->replies()
        );
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function posts()
    {
        return $this->hasManyDeep(
            self::class,
            [Like::class, User::class],
            [['likeable_type', 'likeable_id'], 'id'],
            [null, 'user_id']
        );
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function users()
    {
        return $this->hasManyDeep(
            User::class,
            [Like::class],
            [['likeable_type', 'likeable_id'], 'id'],
            [null, 'user_id']
        );
    }

    public function usersFromRelations()
    {
        return $this->hasManyDeepFromRelations($this->likes(), (new Like)->user());
    }
}
