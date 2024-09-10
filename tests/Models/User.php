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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough<\Tests\Models\Comment>
     */
    public function comment(): HasOneThrough
    {
        return $this->hasOneThrough(Comment::class, Post::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough<\Tests\Models\Comment>
     */
    public function comments(): HasManyThrough
    {
        return $this->hasManyThrough(Comment::class, Post::class);
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Like, $this>
     */
    public function likes(): HasManyDeep
    {
        return $this->hasManyDeep(Like::class, [Post::class], [null, ['likeable_type', 'likeable_id']]);
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Permission, $this>
     */
    public function permissions(): HasManyDeep
    {
        return $this->hasManyDeep(Permission::class, ['role_user', Role::class]);
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Permission, $this>
     */
    public function permissionsFromRelations(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->roles(), (new Role())->permissions());
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\User, $this>
     */
    public function players(): HasManyDeep
    {
        return $this->hasManyDeep(User::class, [Club::class, Team::class]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Tests\Models\Post>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Tests\Models\Role>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Tag, $this>
     */
    public function tagsFromRelations(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->posts(), (new Post())->tags());
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Post, $this>
     */
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
