<?php

namespace Tests;

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
    public function testLazyLoading()
    {
        $comments = Country::first()->comments;

        $this->assertEquals([31, 32], $comments->pluck('id')->all());
    }

    public function testLazyLoadingWithLeadingBelongsToMany()
    {
        $permissions = User::first()->permissions;

        $this->assertEquals([71], $permissions->pluck('id')->all());
    }

    public function testLazyLoadingWithIntermediateBelongsToMany()
    {
        $permissions = Country::first()->permissions;

        $this->assertEquals([71], $permissions->pluck('id')->all());
    }

    public function testLazyLoadingWithTrailingBelongsToMany()
    {
        $roles = Country::first()->roles;

        $this->assertEquals([61], $roles->pluck('id')->all());
    }

    public function testLazyLoadingWithLeadingMorphMany()
    {
        $likes = Post::first()->users;

        $this->assertEquals([11], $likes->pluck('id')->all());
    }

    public function testLazyLoadingWithTrailingMorphMany()
    {
        $likes = User::first()->likes;

        $this->assertEquals([81], $likes->pluck('id')->all());
    }

    public function testLazyLoadingWithMorphedByMany()
    {
        $comments = Tag::first()->comments;

        $this->assertEquals([31], $comments->pluck('id')->all());
    }

    public function testLazyLoadingWithAlias()
    {
        $comments = Post::find(24)->commentReplies;

        $this->assertEquals([35], $comments->pluck('id')->all());
    }

    public function testLazyLoadingWithLimit()
    {
        $comments = Country::first()->comments()->limit(1)->offset(1)->get();

        $this->assertEquals([32], $comments->pluck('id')->all());
    }

    public function testEagerLoading()
    {
        $countries = Country::with('comments')->get();

        $this->assertEquals([31, 32], $countries[0]->comments->pluck('id')->all());
        $this->assertEquals([34, 35], $countries[1]->comments->pluck('id')->all());
    }

    public function testEagerLoadingWithLeadingMorphMany()
    {
        $posts = Post::with('users')->get();

        $this->assertEquals([11], $posts[0]->users->pluck('id')->all());
    }

    public function testEagerLoadingWithTrailingMorphMany()
    {
        $users = User::with('likes')->get();

        $this->assertEquals([81], $users[0]->likes->pluck('id')->all());
    }

    public function testEagerLoadingWithMorphedByMany()
    {
        $tags = Tag::with('comments')->get();

        $this->assertEquals([31], $tags[0]->comments->pluck('id')->all());
    }

    public function testEagerLoadingWithLimit()
    {
        $countries = Country::with(['comments' => function (HasManyDeep $query) {
            $query->orderByDesc('comments.id')->limit(1);
        }])->get();

        $this->assertEquals([32], $countries[0]->comments->pluck('id')->all());
        $this->assertEquals([35], $countries[1]->comments->pluck('id')->all());
    }

    public function testLazyEagerLoading()
    {
        $countries = Country::all()->load('comments');

        $this->assertEquals([31, 32], $countries[0]->comments->pluck('id')->all());
    }

    public function testLazyEagerLoadingWithLimit()
    {
        $countries = Country::all()->load(['comments' => function (HasManyDeep $query) {
            $query->orderByDesc('comments.id')->take(1);
        }]);

        $this->assertEquals([32], $countries[0]->comments->pluck('id')->all());
        $this->assertEquals([35], $countries[1]->comments->pluck('id')->all());
    }

    public function testExistenceQuery()
    {
        $countries = Country::has('comments')->get();

        $this->assertEquals([1, 2], $countries->pluck('id')->all());
    }

    public function testExistenceQueryWithLeadingMorphMany()
    {
        $posts = Post::has('users')->get();

        $this->assertEquals([21, 23], $posts->pluck('id')->all());
    }

    public function testExistenceQueryWithTrailingMorphMany()
    {
        $users = User::has('likes')->get();

        $this->assertEquals([11], $users->pluck('id')->all());
    }

    public function testExistenceQueryWithMorphedByMany()
    {
        $tags = Tag::has('comments')->get();

        $this->assertEquals([91], $tags->pluck('id')->all());
    }

    public function testExistenceQueryForSelfRelation()
    {
        $users = User::has('players')->get();

        $this->assertEquals([11], $users->pluck('id')->all());
    }

    public function testExistenceQueryForSelfRelationWithLeadingMorphMany()
    {
        $posts = Post::has('posts')->get();

        $this->assertEquals([21, 23], $posts->pluck('id')->all());
    }

    public function testExistenceQueryForThroughSelfRelation()
    {
        $users = UserWithAliasTrait::withCount('teamPosts')->get();

        $this->assertEquals([2, 2, 1], $users->pluck('team_posts_count')->all());
    }

    public function testExistenceQueryForThroughSelfRelationWithoutAliasTrait()
    {
        $this->expectExceptionMessageMatches('/' . preg_quote(User::class) . '/');

        User::withCount('teamPosts')->get();
    }

    public function testPaginate()
    {
        $comments = Country::first()->comments()
            ->withIntermediate(Post::class)
            ->paginate();

        $this->assertTrue($comments[0]->relationLoaded('post'));
        $this->assertArrayNotHasKey('laravel_through_key', $comments[0]);
    }

    public function testSimplePaginate()
    {
        $comments = Country::first()->comments()
            ->withIntermediate(Post::class)
            ->simplePaginate();

        $this->assertTrue($comments[0]->relationLoaded('post'));
        $this->assertArrayNotHasKey('laravel_through_key', $comments[0]);
    }

    public function testCursorPaginator()
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

    public function testChunk()
    {
        Country::first()->comments()
            ->withIntermediate(Post::class)
            ->chunk(1, function ($results) {
                $this->assertTrue($results[0]->relationLoaded('post'));
            });
    }

    public function testWithIntermediate()
    {
        $comments = Country::first()->comments()
            ->withIntermediate(User::class, ['id', 'deleted_at'], 'post.user')
            ->withIntermediate(Post::class)
            ->get();

        $this->assertInstanceOf(Post::class, $post = $comments[0]->post);
        $this->assertEquals(['id' => 21, 'user_id' => 11], $post->getAttributes());
        $this->assertEquals(['id' => 11, 'deleted_at' => null], $post->user->getAttributes());
    }

    public function testWithPivot()
    {
        $permissions = User::first()->permissions()
            ->withPivot('role_user', ['role_id'])
            ->withPivot('role_user', ['user_id'])
            ->get();

        $this->assertInstanceOf(Pivot::class, $pivot = $permissions[0]->role_user);
        $this->assertEquals(['role_id' => 61, 'user_id' => 11], $pivot->getAttributes());
    }

    public function testWithPivotClass()
    {
        $permissions = User::first()->permissions()
            ->withPivot('role_user', ['role_id'], RoleUser::class, 'pivot')
            ->get();

        $this->assertInstanceOf(RoleUser::class, $pivot = $permissions[0]->pivot);
        $this->assertEquals(['role_id' => 61], $pivot->getAttributes());
    }

    public function testWithTrashed()
    {
        $user = Comment::find(33)->user()
            ->withTrashed()
            ->first();

        $this->assertEquals(13, $user->id);
    }

    public function testWithTrashedIntermediate()
    {
        $comments = Country::first()->comments()
            ->withTrashed(['users.deleted_at'])
            ->get();

        $this->assertEquals([31, 32, 33], $comments->pluck('id')->all());
    }

    public function testWithTrashedIntermediateAndWithCount()
    {
        $country = Country::withCount('commentsWithTrashedUsers as count')->first();

        $this->assertEquals(3, $country->count);
    }

    public function testFromRelations()
    {
        $comments = Country::first()->commentsFromRelations;

        $this->assertEquals([31, 32], $comments->pluck('id')->all());
    }

    public function testFromRelationsWithBelongsToMany()
    {
        $permissions = User::first()->permissionsFromRelations;

        $this->assertEquals([71], $permissions->pluck('id')->all());
    }

    public function testFromRelationsWithMorphManyAndBelongsTo()
    {
        $users = Post::first()->usersFromRelations;

        $this->assertEquals([11], $users->pluck('id')->all());
    }

    public function testFromRelationsWithMorphToMany()
    {
        $tags = User::first()->tagsFromRelations;

        $this->assertEquals([91], $tags->pluck('id')->all());
    }

    public function testFromRelationsWithMorphedByMany()
    {
        $comments = Tag::first()->commentsFromRelations;

        $this->assertEquals([31], $comments->pluck('id')->all());
    }

    public function testFromRelationsWithHasManyDeepWithPivot()
    {
        $permissions = Country::first()->permissionsFromRelations;

        $this->assertEquals([71], $permissions->pluck('id')->all());
    }

    public function testFromRelationsWithHasManyDeepWithPivotAlias()
    {
        $permissions = Country::first()->permissionsWithPivotAliasFromRelations;

        $this->assertEquals([71], $permissions->pluck('id')->all());
    }

    public function testFromRelationsWithAlias()
    {
        $comments = Post::find(24)->commentRepliesFromRelations;

        $this->assertEquals([35], $comments->pluck('id')->all());
    }
}
