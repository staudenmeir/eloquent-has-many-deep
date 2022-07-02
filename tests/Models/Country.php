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

    public function commentFromRelationsWithConstraints()
    {
        return $this->hasOneDeepFromRelationsWithConstraints(
            [$this, 'postsWithConstraints'],
            [new Post(), 'comments']
        )->orderByDesc('comments.id');
    }

    public function comments()
    {
        return $this->hasManyDeep(Comment::class, [User::class, Post::class]);
    }

    public function commentsFromRelations()
    {
        return $this->hasManyDeepFromRelations([$this->posts(), (new Post())->comments()]);
    }

    public function commentsFromRelationsWithConstraints()
    {
        return $this->hasManyDeepFromRelationsWithConstraints(
            [[$this, 'postsWithConstraints'], [new Post(), 'comments']]
        );
    }

    public function commentsFromRelationsWithTrashedFinalRelatedModel()
    {
        return $this->hasManyDeepFromRelationsWithConstraints(
            [[$this, 'posts'], [new Post(), 'commentsWithTrashed']]
        );
    }

    public function commentsFromRelationsWithTrashedIntermediateDeepModel()
    {
        return $this->hasManyDeepFromRelationsWithConstraints([$this, 'commentsWithTrashedUsers']);
    }

    public function commentsFromRelationsWithTrashedIntermediateRelatedModel()
    {
        return $this->hasManyDeepFromRelationsWithConstraints(
            [[$this, 'usersWithTrashed'], [new User(), 'comments']]
        );
    }

    public function commentsFromRelationsWithTrashedParents()
    {
        return $this->hasManyDeepFromRelationsWithConstraints(
            [[$this, 'postsWithTrashedParents'], [new Post(), 'comments']]
        );
    }

    public function commentsFromRelationsWithCustomRelatedTable()
    {
        $comment = (new Comment())->setTable('my_comments');

        $comments = (new Post())->newHasMany(
            $comment->newQuery(),
            $this,
            $comment->getTable().'.post_id',
            'id'
        );

        return $this->hasManyDeepFromRelations([$this->posts(), $comments]);
    }

    public function commentsFromRelationsWithCustomThroughTable()
    {
        $user = (new User())->setTable('my_users');

        $users = (new Country())->newHasMany(
            $user->newQuery(),
            $this,
            $user->getTable().'.country_id',
            'id'
        );

        return $this->hasManyDeepFromRelations([$users, (new User())->comments()]);
    }

    public function commentsWithTrashedUsers()
    {
        return $this->comments()->withTrashed('users.deleted_at');
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

    public function postsWithConstraints()
    {
        return $this->posts()->where('posts.published', true);
    }

    public function postsWithTrashedParents()
    {
        return $this->posts()->withTrashedParents();
    }

    public function roles()
    {
        return $this->hasManyDeep(Role::class, [User::class, 'role_user']);
    }

    public function usersWithTrashed()
    {
        return $this->hasMany(User::class)->withTrashed();
    }
}
