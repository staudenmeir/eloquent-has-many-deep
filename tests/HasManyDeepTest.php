<?php

namespace Tests;

use Tests\Models\Comment;
use Tests\Models\Country;
use Tests\Models\Post;
use Tests\Models\Tag;
use Tests\Models\User;
use Tests\Models\UserWithAliasTrait;

class HasManyDeepTest extends TestCase
{
    public function testLazyLoading(): void
    {
        $comments = Country::find(1)->comments;

        $this->assertEquals([31, 32], $comments->pluck('id')->all());
    }

    public function testLazyLoadingWithLeadingBelongsToMany(): void
    {
        $permissions = User::first()->permissions;

        $this->assertEquals([71], $permissions->pluck('id')->all());
    }

    public function testLazyLoadingWithIntermediateBelongsToMany(): void
    {
        $permissions = Country::find(1)->permissions;

        $this->assertEquals([71], $permissions->pluck('id')->all());
    }

    public function testLazyLoadingWithTrailingBelongsToMany(): void
    {
        $roles = Country::find(1)->roles;

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

        $this->assertEquals([35, 36], $comments->pluck('id')->all());
    }

    public function testEagerLoading(): void
    {
        $countries = Country::with('comments')->get();

        $this->assertEquals([31, 32], $countries[0]->comments->pluck('id')->all());
        $this->assertEquals([34, 35, 36], $countries[1]->comments->pluck('id')->all());
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

    public function testLazyEagerLoading(): void
    {
        $countries = Country::all()->load('comments');

        $this->assertEquals([31, 32], $countries[0]->comments->pluck('id')->all());
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

    public function testWithTrashed(): void
    {
        $user = Comment::find(33)->user()
            ->withTrashed()
            ->first();

        $this->assertEquals(13, $user->id);
    }

    public function testWithTrashedIntermediate(): void
    {
        $comments = Country::find(1)->comments()
            ->withTrashed(['users.deleted_at'])
            ->get();

        $this->assertEquals([31, 32, 33], $comments->pluck('id')->all());
    }

    public function testWithTrashedIntermediateAndWithCount(): void
    {
        $country = Country::withCount('commentsWithTrashedUsers as count')->first();

        $this->assertEquals(3, $country->count);
    }
}
