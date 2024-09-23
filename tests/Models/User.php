<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;

class User extends Model
{
    use SoftDeletes;

    public function comment(): HasOneThrough
    {
        return $this->hasOneThrough(Comment::class, Post::class);
    }

    public function comments(): HasManyThrough
    {
        return $this->hasManyThrough(Comment::class, Post::class);
    }

    public function likes(): HasManyDeep
    {
        return $this->hasManyDeep(Like::class, [Post::class], [null, ['likeable_type', 'likeable_id']]);
    }

    public function permissions(): HasManyDeep
    {
        return $this->hasManyDeep(Permission::class, ['role_user', Role::class]);
    }

    public function permissionsFromRelations(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->roles(), (new Role())->permissions());
    }

    public function players(): HasManyDeep
    {
        return $this->hasManyDeep(User::class, [Club::class, Team::class]);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function tagsFromRelations(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->posts(), (new Post())->tags());
    }

    public function teamPosts(): HasManyDeep
    {
        return $this->hasManyDeep(
            Post::class,
            [Team::class, static::class],
            ['id', null, 'user_id'],
            ['users.team_id']
        );
    }
}
