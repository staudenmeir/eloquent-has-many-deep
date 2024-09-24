<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasOneDeep;

class Country extends Model
{
    public function comment(): HasOneDeep
    {
        return $this->hasOneDeep(Comment::class, [User::class, Post::class])->withDefault();
    }

    public function commentFromRelations(): HasOneDeep
    {
        return $this->hasOneDeepFromRelations($this->posts(), (new Post())->comments());
    }

    public function commentFromRelationsWithConstraints(): HasOneDeep
    {
        return $this->hasOneDeepFromRelationsWithConstraints(
            [$this, 'postsWithConstraints'],
            [new Post(), 'comments']
        )->orderByDesc('comments.id');
    }

    public function comments(): HasManyDeep
    {
        return $this->hasManyDeep(Comment::class, [User::class, Post::class]);
    }

    public function commentsFromRelations(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations([$this->posts(), (new Post())->comments()]);
    }

    public function commentsFromRelationsWithConstraints(): HasManyDeep
    {
        return $this->hasManyDeepFromRelationsWithConstraints(
            [[$this, 'postsWithConstraints'], [new Post(), 'comments']]
        );
    }

    public function commentsFromRelationsWithTrashedFinalRelatedModel(): HasManyDeep
    {
        return $this->hasManyDeepFromRelationsWithConstraints(
            [[$this, 'posts'], [new Post(), 'commentsWithTrashed']]
        );
    }

    public function commentsFromRelationsWithTrashedIntermediateDeepModel(): HasManyDeep
    {
        return $this->hasManyDeepFromRelationsWithConstraints([$this, 'commentsWithTrashedUsers']);
    }

    public function commentsFromRelationsWithTrashedIntermediateRelatedModel(): HasManyDeep
    {
        return $this->hasManyDeepFromRelationsWithConstraints(
            [[$this, 'usersWithTrashed'], [new User(), 'comment']]
        );
    }

    public function commentsFromRelationsWithTrashedParents(): HasManyDeep
    {
        return $this->hasManyDeepFromRelationsWithConstraints(
            [[$this, 'postsWithTrashedParents'], [new Post(), 'comments']]
        );
    }

    public function commentsFromRelationsWithCustomRelatedTable(): HasManyDeep
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

    public function commentsFromRelationsWithCustomThroughTable(): HasManyDeep
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

    public function commentsWithTrashedUsers(): HasManyDeep
    {
        return $this->comments()->withTrashed('users.deleted_at');
    }

    public function permissions(): HasManyDeep
    {
        return $this->hasManyDeep(Permission::class, [User::class, 'role_user', Role::class]);
    }

    public function permissionsFromRelations(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->permissions());
    }

    public function permissionsWithPivotAlias(): HasManyDeep
    {
        return $this->hasManyDeep(Permission::class, [User::class, RoleUser::class.' as alias', Role::class]);
    }

    public function permissionsWithPivotAliasFromRelations(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->permissionsWithPivotAlias());
    }

    public function posts(): HasManyThrough
    {
        return $this->hasManyThrough(Post::class, User::class);
    }

    public function postsWithConstraints(): HasManyThrough
    {
        return $this->posts()->where('posts.published', true);
    }

    public function postsWithTrashedParents(): HasManyThrough
    {
        return $this->posts()->withTrashedParents();
    }

    public function roles(): HasManyDeep
    {
        return $this->hasManyDeep(Role::class, [User::class, 'role_user']);
    }

    public function usersWithTrashed(): HasMany
    {
        return $this->hasMany(User::class)->withTrashed();
    }
}
