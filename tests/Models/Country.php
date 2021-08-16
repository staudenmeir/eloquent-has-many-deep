<?php

namespace Tests\Models;

class Country extends Model
{
    public function comment()
    {
        return $this->hasOneDeep(Comment::class, [User::class, Post::class])->withDefault();
    }

    public function commentFromRelations()
    {
        return $this->hasOneDeepFromRelations($this->posts(), (new Post())->comments());
    }

    public function comments()
    {
        return $this->hasManyDeep(Comment::class, [User::class, Post::class]);
    }

    public function commentsFromRelations()
    {
        return $this->hasManyDeepFromRelations([$this->posts(), (new Post())->comments()]);
    }

    public function permissions()
    {
        return $this->hasManyDeep(Permission::class, [User::class, 'role_user', Role::class]);
    }

    public function permissionsFromRelations()
    {
        return $this->hasManyDeepFromRelations($this->permissions());
    }

    public function permissionsWithPivotAlias()
    {
        return $this->hasManyDeep(Permission::class, [User::class, RoleUser::class.' as alias', Role::class]);
    }

    public function permissionsWithPivotAliasFromRelations()
    {
        return $this->hasManyDeepFromRelations($this->permissionsWithPivotAlias());
    }

    public function posts()
    {
        return $this->hasManyThrough(Post::class, User::class);
    }

    public function roles()
    {
        return $this->hasManyDeep(Role::class, [User::class, 'role_user']);
    }
}
