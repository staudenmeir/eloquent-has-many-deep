<?php

namespace Tests\Models;

use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Country extends Model
{
    use HasRelationships;

    protected $primaryKey = 'country_pk';

    public function comments()
    {
        return $this->hasManyDeep(Comment::class, [User::class, Post::class]);
    }

    public function permissions()
    {
        return $this->hasManyDeep(Permission::class, [User::class, 'role_user', Role::class]);
    }

    public function roles()
    {
        return $this->hasManyDeep(Role::class, [User::class, 'role_user']);
    }
}
