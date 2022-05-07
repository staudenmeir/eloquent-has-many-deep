<?php

namespace Tests;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Tests\Models\Comment;
use Tests\Models\Country;
use Tests\Models\Post;
use Tests\Models\RoleUser;
use Tests\Models\Tag;
use Tests\Models\User;
use Tests\Models\UserWithAliasTrait;

class HasManyDeepTest extends TestCase
{
    public function testLazyLoading(): void
    {
        $comments = Country::first()->comments;

        $this->assertEquals([31, 32], $comments->pluck('id')->all());
    }

    public function testLazyLoadingWithLeadingBelongsToMany(): void
    {
        $permissions = User::first()->permissions;

        $this->assertEquals([71], $permissions->pluck('id')->all());
    }

    public function testLazyLoadingWithIntermediateBelongsToMany(): void
    {
        $permissions = Country::first()->permissions;

        $this->assertEquals([71], $permissions->pluck('id')->all());
    }

    public function testLazyLoadingWithTrailingBelongsToMany(): void
    {
        $roles = Country::first()->roles;

        $this->assertEquals([61], $roles->pluck('id')->all());
    }

    public function testLazyLoadingWithLeadingMorphMany(): void
    {
        $likes = Post::first()->users;

        $this->assertEquals([11], $likes->pluck('id')->all());
    }

    public function testLazyLoadingWithTrailingMorphMany(): void
    {
        $likes = User::first()->likes;

        $this->assertEquals([81], $likes->pluck('id')->all());
    }

    public function testLazyLoadingWithMorphedByMany(): void
    {
        $comments = Tag::first()->comments;

        $this->assertEquals([31], $comments->pluck('id')->all());
    }

    public function testLazyLoadingWithAlias(): void
    {
        $comments = Post::find(24)->commentReplies;

        $this->assertEquals([35], $comments->pluck('id')->all());
    }

    public function testLazyLoadingWithLimit(): void
    {
        $comments = Country::first()->comments()->limit(1)->offset(1)->get();

        $this->assertEquals([32], $comments->pluck('id')->all());
    }

    public function testEagerLoading(): void
    {
        $countries = Country::with('comments')->get();

        $this->assertEquals([31, 32], $countries[0]->comments->pluck('id')->all());
        $this->assertEquals([34, 35], $countries[1]->comments->pluck('id')->all());
    }

    public function testEagerLoadingWithLeadingMorphMany(): void
    {
        $posts = Post::with('users')->get();

        $this->assertEquals([11], $posts[0]->users->pluck('id')->all());
    }

    public function testEagerLoadingWithTrailingMorphMany(): void
    {
        $users = User::with('likes')->get();

        $this->assertEquals([81], $users[0]->likes->pluck('id')->all());
    }

    public function testEagerLoadingWithMorphedByMany(): void
    {
        $tags = Tag::with('comments')->get();

        $this->assertEquals([31], $tags[0]->comments->pluck('id')->all());
    }

    public function testEagerLoadingWithLimit(): void
    {
        $countries = Country::with(['comments' => function (HasManyDeep $query) {
            $query->orderByDesc('comments.id')->limit(1);
        }])->get();

        $this->assertEquals([32], $countries[0]->comments->pluck('id')->all());
        $this->assertEquals([35], $countries[1]->comments->pluck('id')->all());
    }

    public function testLazyEagerLoading(): void
    {
        $countries = Country::all()->load('comments');

        $this->assertEquals([31, 32], $countries[0]->comments->pluck('id')->all());
    }

    public function testLazyEagerLoadingWithLimit(): void
    {
        $countries = Country::all()->load(['comments' => function (HasManyDeep $query) {
            $query->orderByDesc('comments.id')->take(1);
        }]);

        $this->assertEquals([32], $countries[0]->comments->pluck('id')->all());
        $this->assertEquals([35], $countries[1]->comments->pluck('id')->all());
    }

    public function testExistenceQuery(): void
    {
        $countries = Country::has('comments')->get();

        $this->assertEquals([1, 2], $countries->pluck('id')->all());
    }

    public function testExistenceQueryWithLeadingMorphMany(): void
    {
        $posts = Post::has('users')->get();

        $this->assertEquals([21, 23], $posts->pluck('id')->all());
    }

    public function testExistenceQueryWithTrailingMorphMany(): void
    {
        $users = User::has('likes')->get();

        $this->assertEquals([11], $users->pluck('id')->all());
    }

    public function testExistenceQueryWithMorphedByMany(): void
    {
        $tags = Tag::has('comments')->get();

        $this->assertEquals([91], $tags->pluck('id')->all());
    }

    public function testExistenceQueryForSelfRelation(): void
    {
        $users = User::has('players')->get();

        $this->assertEquals([11], $users->pluck('id')->all());
    }

    public function testExistenceQueryForSelfRelationWithLeadingMorphMany(): void
    {
        $posts = Post::has('posts')->get();

        $this->assertEquals([21, 23], $posts->pluck('id')->all());
    }

    public function testExistenceQueryForThroughSelfRelation(): void
    {
        $users = UserWithAliasTrait::withCount('teamPosts')->get();

        $this->assertEquals([2, 2, 1], $users->pluck('team_posts_count')->all());
    }

    public function testExistenceQueryForThroughSelfRelationWithoutAliasTrait(): void
    {
        $this->expectExceptionMessageMatches('/' . preg_quote(User::class) . '/');

        User::withCount('teamPosts')->get();
    }

    public function testPaginate(): void
    {
        $comments = Country::first()->comments()
            ->withIntermediate(Post::class)
            ->paginate();

        $this->assertTrue($comments[0]->relationLoaded('post'));
        $this->assertArrayNotHasKey('laravel_through_key', $comments[0]);
    }

    public function testSimplePaginate(): void
    {
        $comments = Country::first()->comments()
            ->withIntermediate(Post::class)
            ->simplePaginate();

        $this->assertTrue($comments[0]->relationLoaded('post'));
        $this->assertArrayNotHasKey('laravel_through_key', $comments[0]);
    }

    public function testCursorPaginator(): void
    {
        if (!class_exists('Illuminate\Pagination\CursorPaginator')) {
            $this->markTestSkipped();
        }

        $comments = Country::first()->comments()
            ->withIntermediate(Post::class)
            ->cursorPaginate();

        $this->assertTrue($comments[0]->relationLoaded('post'));
        $this->assertArrayNotHasKey('laravel_through_key', $comments[0]);
    }

    public function testChunk(): void
    {
        Country::first()->comments()
            ->withIntermediate(Post::class)
            ->chunk(1, function ($results) {
                $this->assertTrue($results[0]->relationLoaded('post'));
            });
    }

    public function testWithIntermediate(): void
    {
        $comments = Country::first()->comments()
            ->withIntermediate(User::class, ['id', 'deleted_at'], 'post.user')
            ->withIntermediate(Post::class)
            ->get();

        $this->assertInstanceOf(Post::class, $post = $comments[0]->post);
        $this->assertEquals(['id' => 21, 'user_id' => 11], $post->getAttributes());
        $this->assertEquals(['id' => 11, 'deleted_at' => null], $post->user->getAttributes());
    }

    public function testWithPivot(): void
    {
        $permissions = User::first()->permissions()
            ->withPivot('role_user', ['role_id'])
            ->withPivot('role_user', ['user_id'])
            ->get();

        $this->assertInstanceOf(Pivot::class, $pivot = $permissions[0]->role_user);
        $this->assertEquals(['role_id' => 61, 'user_id' => 11], $pivot->getAttributes());
    }

    public function testWithPivotClass(): void
    {
        $permissions = User::first()->permissions()
            ->withPivot('role_user', ['role_id'], RoleUser::class, 'pivot')
            ->get();

        $this->assertInstanceOf(RoleUser::class, $pivot = $permissions[0]->pivot);
        $this->assertEquals(['role_id' => 61], $pivot->getAttributes());
    }

    public function testWithTrashed(): void
    {
        $user = Comment::find(33)->user()
            ->withTrashed()
            ->first();

        $this->assertEquals(13, $user->id);
    }

    public function testWithTrashedIntermediate(): void
    {
        $comments = Country::first()->comments()
            ->withTrashed(['users.deleted_at'])
            ->get();

        $this->assertEquals([31, 32, 33], $comments->pluck('id')->all());
    }

    public function testWithTrashedIntermediateAndWithCount(): void
    {
        $country = Country::withCount('commentsWithTrashedUsers as count')->first();

        $this->assertEquals(3, $country->count);
    }

    public function testFromRelations(): void
    {
        $comments = Country::first()->commentsFromRelations;

        $this->assertEquals([31, 32], $comments->pluck('id')->all());
    }

    public function testFromRelationsWithBelongsToMany(): void
    {
        $permissions = User::first()->permissionsFromRelations;

        $this->assertEquals([71], $permissions->pluck('id')->all());
    }

    public function testFromRelationsWithMorphManyAndBelongsTo(): void
    {
        $users = Post::first()->usersFromRelations;

        $this->assertEquals([11], $users->pluck('id')->all());
    }

    public function testFromRelationsWithMorphToMany(): void
    {
        $tags = User::first()->tagsFromRelations;

        $this->assertEquals([91], $tags->pluck('id')->all());
    }

    public function testFromRelationsWithMorphedByMany(): void
    {
        $comments = Tag::first()->commentsFromRelations;

        $this->assertEquals([31], $comments->pluck('id')->all());
    }

    public function testFromRelationsWithHasManyDeepWithPivot(): void
    {
        $permissions = Country::first()->permissionsFromRelations;

        $this->assertEquals([71], $permissions->pluck('id')->all());
    }

    public function testFromRelationsWithHasManyDeepWithPivotAlias(): void
    {
        $permissions = Country::first()->permissionsWithPivotAliasFromRelations;

        $this->assertEquals([71], $permissions->pluck('id')->all());
    }

    public function testFromRelationsWithAlias(): void
    {
        $comments = Post::find(24)->commentRepliesFromRelations;

        $this->assertEquals([35], $comments->pluck('id')->all());
    }

    public function testFromRelationsWithCustomRelatedTable(): void
    {
        DB::schema()->rename('comments', 'my_comments');

        $comments = Country::first()->commentsFromRelationsWithCustomRelatedTable;

        $this->assertEquals([31, 32], $comments->pluck('id')->all());
    }

    public function testFromRelationsWithCustomThroughTable(): void
    {
        DB::schema()->rename('users', 'my_users');

        $comments = Country::first()->commentsFromRelationsWithCustomThroughTable;

        $this->assertEquals([31, 32], $comments->pluck('id')->all());
    }
}
