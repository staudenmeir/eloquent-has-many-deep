<?php

namespace Tests\Models;

class Post extends Model
{
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

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
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
            [['likeable_type', 'likeable_id'], 'user_pk'],
            [null, 'user_user_pk']
        );
    }

    public function usersFromRelations()
    {
        return $this->hasManyDeepFromRelations($this->likes(), (new Like)->user());
    }
}
