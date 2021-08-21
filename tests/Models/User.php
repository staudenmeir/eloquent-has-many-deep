<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;

    public function likes()
    {
        return $this->hasManyDeep(Like::class, [Post::class], [null, ['likeable_type', 'likeable_id']]);
    }

    public function permissions()
    {
        return $this->hasManyDeep(Permission::class, ['role_user', Role::class]);
    }

    public function permissionsFromRelations()
    {
        return $this->hasManyDeepFromRelations($this->roles(), (new Role())->permissions());
    }

    public function players()
    {
        return $this->hasManyDeep(self::class, [Club::class, Team::class]);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function tagsFromRelations()
    {
        return $this->hasManyDeepFromRelations($this->posts(), (new Post())->tags());
    }

    public function teamPosts()
    {
        return $this->hasManyDeep(Post::class, [Team::class, static::class], ['id', null, 'user_id'], ['team_id']);
    }
}
