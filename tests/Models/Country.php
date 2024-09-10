<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasOneDeep;

/**
 * @property-read \Tests\Models\Comment|null $comment
 * @property-read \Tests\Models\Comment|null $commentFromRelations
 * @property-read \Tests\Models\Comment|null $commentFromRelationsWithConstraints
 */
class Country extends Model
{
    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasOneDeep<\Tests\Models\Comment, $this>
     */
    public function comment(): HasOneDeep
    {
        return $this->hasOneDeep(Comment::class, [User::class, Post::class])->withDefault();
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasOneDeep<\Tests\Models\Comment, $this>
     */
    public function commentFromRelations(): HasOneDeep
    {
        return $this->hasOneDeepFromRelations($this->posts(), (new Post())->comments());
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasOneDeep<\Tests\Models\Comment, $this>
     */
    public function commentFromRelationsWithConstraints(): HasOneDeep
    {
        return $this->hasOneDeepFromRelationsWithConstraints(
            [$this, 'postsWithConstraints'],
            [new Post(), 'comments']
        )->orderByDesc('comments.id'); // TODO
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Comment, $this>
     */
    public function comments(): HasManyDeep
    {
        return $this->hasManyDeep(Comment::class, [User::class, Post::class]);
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Comment, $this>
     */
    public function commentsFromRelations(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations([$this->posts(), (new Post())->comments()]);
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Comment, $this>
     */
    public function commentsFromRelationsWithConstraints(): HasManyDeep
    {
        return $this->hasManyDeepFromRelationsWithConstraints(
            [[$this, 'postsWithConstraints'], [new Post(), 'comments']]
        );
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Comment, $this>
     */
    public function commentsFromRelationsWithTrashedFinalRelatedModel(): HasManyDeep
    {
        return $this->hasManyDeepFromRelationsWithConstraints(
            [[$this, 'posts'], [new Post(), 'commentsWithTrashed']]
        );
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Comment, $this>
     */
    public function commentsFromRelationsWithTrashedIntermediateDeepModel(): HasManyDeep
    {
        return $this->hasManyDeepFromRelationsWithConstraints([$this, 'commentsWithTrashedUsers']);
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Comment, $this>
     */
    public function commentsFromRelationsWithTrashedIntermediateRelatedModel(): HasManyDeep
    {
        return $this->hasManyDeepFromRelationsWithConstraints(
            [[$this, 'usersWithTrashed'], [new User(), 'comment']]
        );
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Comment, $this>
     */
    public function commentsFromRelationsWithTrashedParents(): HasManyDeep
    {
        return $this->hasManyDeepFromRelationsWithConstraints(
            [[$this, 'postsWithTrashedParents'], [new Post(), 'comments']]
        );
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Comment, $this>
     */
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

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Comment, $this>
     */
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

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Comment, $this>
     */
    public function commentsWithTrashedUsers(): HasManyDeep
    {
        return $this->comments()->withTrashed('users.deleted_at');
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Permission, $this>
     */
    public function permissions(): HasManyDeep
    {
        return $this->hasManyDeep(Permission::class, [User::class, 'role_user', Role::class]);
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Permission, $this>
     */
    public function permissionsFromRelations(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->permissions());
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Permission, $this>
     */
    public function permissionsWithPivotAlias(): HasManyDeep
    {
        return $this->hasManyDeep(Permission::class, [User::class, RoleUser::class.' as alias', Role::class]);
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Permission, $this>
     */
    public function permissionsWithPivotAliasFromRelations(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->permissionsWithPivotAlias());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough<\Tests\Models\Post>
     */
    public function posts(): HasManyThrough
    {
        return $this->hasManyThrough(Post::class, User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough<\Tests\Models\Post>
     */
    public function postsWithConstraints(): HasManyThrough
    {
        return $this->posts()->where('posts.published', true);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough<\Tests\Models\Post>
     */
    public function postsWithTrashedParents(): HasManyThrough
    {
        return $this->posts()->withTrashedParents();
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Role, $this>
     */
    public function roles(): HasManyDeep
    {
        return $this->hasManyDeep(Role::class, [User::class, 'role_user']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Tests\Models\User>
     */
    public function usersWithTrashed(): HasMany
    {
        return $this->hasMany(User::class)->withTrashed();
    }
}
