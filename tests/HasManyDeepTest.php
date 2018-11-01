<?php

namespace Tests;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Tests\Models\Country;
use Tests\Models\Post;
use Tests\Models\RoleUserPivot;
use Tests\Models\Tag;
use Tests\Models\User;

class HasManyDeepTest extends TestCase
{
    public function testLazyLoading()
    {
        $comments = Country::first()->comments;

        $this->assertEquals([1, 2], $comments->pluck('comment_pk')->all());
        $sql = 'select "comments".*, "users"."country_country_pk" from "comments"'
            .' inner join "posts" on "posts"."post_pk" = "comments"."post_post_pk"'
            .' inner join "users" on "users"."user_pk" = "posts"."user_user_pk"'
            .' where "users"."deleted_at" is null and "users"."country_country_pk" = ?';
        $this->assertEquals($sql, Capsule::getQueryLog()[1]['query']);
        $this->assertEquals([1], Capsule::getQueryLog()[1]['bindings']);
    }

    public function testLazyLoadingWithLeadingMorphMany()
    {
        $likes = Post::first()->users;

        $this->assertEquals([1], $likes->pluck('user_pk')->all());
        $sql = 'select "users".*, "likes"."likeable_id" from "users"'
            .' inner join "likes" on "likes"."user_user_pk" = "users"."user_pk"'
            .' where "likes"."likeable_id" = ? and "likes"."likeable_type" = ? and "users"."deleted_at" is null';
        $this->assertEquals($sql, Capsule::getQueryLog()[1]['query']);
        $this->assertEquals([1, Post::class], Capsule::getQueryLog()[1]['bindings']);
    }

    public function testLazyLoadingWithTrailingMorphMany()
    {
        $likes = User::first()->likes;

        $this->assertEquals([1], $likes->pluck('like_pk')->all());
        $sql = 'select "likes".*, "posts"."user_user_pk" from "likes"'
            .' inner join "posts" on "posts"."post_pk" = "likes"."likeable_id"'
            .' where "likes"."likeable_type" = ? and "posts"."user_user_pk" = ?';
        $this->assertEquals($sql, Capsule::getQueryLog()[1]['query']);
        $this->assertEquals([Post::class, 1], Capsule::getQueryLog()[1]['bindings']);
    }

    public function testLazyLoadingWithMorphedByMany()
    {
        $comments = Tag::first()->comments;

        $this->assertEquals([1], $comments->pluck('comment_pk')->all());
        $sql = 'select "comments".*, "taggables"."tag_tag_pk" from "comments"'
            .' inner join "posts" on "posts"."post_pk" = "comments"."post_post_pk"'
            .' inner join "taggables" on "taggables"."taggable_id" = "posts"."post_pk"'
            .' where "taggables"."taggable_type" = ? and "taggables"."tag_tag_pk" = ?';
        $this->assertEquals($sql, Capsule::getQueryLog()[1]['query']);
        $this->assertEquals([Post::class, 1], Capsule::getQueryLog()[1]['bindings']);
    }

    public function testPaginate()
    {
        $comments = Country::first()->comments()
            ->withIntermediate(Post::class)
            ->paginate();

        $this->assertTrue($comments[0]->relationLoaded('post'));
        $this->assertArrayNotHasKey('country_country_pk', $comments[0]->getAttributes());
    }

    public function testSimplePaginate()
    {
        $comments = Country::first()->comments()
            ->withIntermediate(Post::class)
            ->simplePaginate();

        $this->assertTrue($comments[0]->relationLoaded('post'));
        $this->assertArrayNotHasKey('country_country_pk', $comments[0]->getAttributes());
    }

    public function testChunk()
    {
        if (! method_exists(HasManyThrough::class, 'chunk')) {
            $this->markTestSkipped();
        }

        Country::first()->comments()
            ->withIntermediate(Post::class)
            ->chunk(1, function ($results) {
                $this->assertTrue($results[0]->relationLoaded('post'));
            });
    }

    public function testEagerLoading()
    {
        $countries = Country::with('comments')->get();

        $this->assertEquals([1, 2], $countries[0]->comments->pluck('comment_pk')->all());
        $sql = 'select "comments".*, "users"."country_country_pk" from "comments"'
            .' inner join "posts" on "posts"."post_pk" = "comments"."post_post_pk"'
            .' inner join "users" on "users"."user_pk" = "posts"."user_user_pk"'
            .' where "users"."deleted_at" is null and "users"."country_country_pk" in (?, ?)';
        $this->assertEquals($sql, Capsule::getQueryLog()[1]['query']);
        $this->assertEquals([1, 2], Capsule::getQueryLog()[1]['bindings']);
    }

    public function testEagerLoadingWithLeadingMorphMany()
    {
        $posts = Post::with('users')->get();

        $this->assertEquals([1], $posts[0]->users->pluck('user_pk')->all());
        $sql = 'select "users".*, "likes"."likeable_id" from "users"'
            .' inner join "likes" on "likes"."user_user_pk" = "users"."user_pk"'
            .' where "likes"."likeable_id" in (?, ?, ?) and "likes"."likeable_type" = ? and "users"."deleted_at" is null';
        $this->assertEquals($sql, Capsule::getQueryLog()[1]['query']);
        $this->assertEquals([1, 2, 3, Post::class], Capsule::getQueryLog()[1]['bindings']);
    }

    public function testEagerLoadingWithTrailingMorphMany()
    {
        $users = User::with('likes')->get();

        $this->assertEquals([1], $users[0]->likes->pluck('like_pk')->all());
        $sql = 'select "likes".*, "posts"."user_user_pk" from "likes"'
            .' inner join "posts" on "posts"."post_pk" = "likes"."likeable_id"'
            .' where "likes"."likeable_type" = ? and "posts"."user_user_pk" in (?, ?)';
        $this->assertEquals($sql, Capsule::getQueryLog()[1]['query']);
        $this->assertEquals([Post::class, 1, 2], Capsule::getQueryLog()[1]['bindings']);
    }

    public function testEagerLoadingWithMorphedByMany()
    {
        $tags = Tag::with('comments')->get();

        $this->assertEquals([1], $tags[0]->comments->pluck('comment_pk')->all());
        $sql = 'select "comments".*, "taggables"."tag_tag_pk" from "comments"'
            .' inner join "posts" on "posts"."post_pk" = "comments"."post_post_pk"'
            .' inner join "taggables" on "taggables"."taggable_id" = "posts"."post_pk"'
            .' where "taggables"."taggable_type" = ? and "taggables"."tag_tag_pk" in (?, ?)';
        $this->assertEquals($sql, Capsule::getQueryLog()[1]['query']);
        $this->assertEquals([Post::class, 1, 2], Capsule::getQueryLog()[1]['bindings']);
    }

    public function testExistenceQuery()
    {
        $countries = Country::has('comments')->get();

        $this->assertEquals([1], $countries->pluck('country_pk')->all());
        $sql = 'select * from "countries"'
            .' where exists (select * from "comments"'
            .' inner join "posts" on "posts"."post_pk" = "comments"."post_post_pk"'
            .' inner join "users" on "users"."user_pk" = "posts"."user_user_pk"'
            .' where "users"."deleted_at" is null and "countries"."country_pk" = "users"."country_country_pk"'
            .' and "users"."deleted_at" is null)';
        $this->assertEquals($sql, Capsule::getQueryLog()[0]['query']);
    }

    public function testExistenceQueryWithLeadingMorphMany()
    {
        $posts = Post::has('users')->get();

        $this->assertEquals([1, 3], $posts->pluck('post_pk')->all());
        $sql = 'select * from "posts"'
            .' where exists (select * from "users"'
            .' inner join "likes" on "likes"."user_user_pk" = "users"."user_pk"'
            .' where "posts"."post_pk" = "likes"."likeable_id" and "likes"."likeable_type" = ?'
            .' and "users"."deleted_at" is null)';
        $this->assertEquals($sql, Capsule::getQueryLog()[0]['query']);
        $this->assertEquals([Post::class], Capsule::getQueryLog()[0]['bindings']);
    }

    public function testExistenceQueryWithTrailingMorphMany()
    {
        $users = User::has('likes')->get();

        $this->assertEquals([1], $users->pluck('user_pk')->all());
        $sql = 'select * from "users"'
            .' where exists (select * from "likes"'
            .' inner join "posts" on "posts"."post_pk" = "likes"."likeable_id"'
            .' where "likes"."likeable_type" = ? and "users"."user_pk" = "posts"."user_user_pk"'
            .' and "likes"."likeable_type" = ?) and "users"."deleted_at" is null';
        $this->assertEquals($sql, Capsule::getQueryLog()[0]['query']);
        $this->assertEquals([Post::class, Post::class], Capsule::getQueryLog()[0]['bindings']);
    }

    public function testExistenceQueryWithMorphedByMany()
    {
        $tags = Tag::has('comments')->get();

        $this->assertEquals([1], $tags->pluck('tag_pk')->all());
        $sql = 'select * from "tags"'
            .' where exists (select * from "comments"'
            .' inner join "posts" on "posts"."post_pk" = "comments"."post_post_pk"'
            .' inner join "taggables" on "taggables"."taggable_id" = "posts"."post_pk"'
            .' where "taggables"."taggable_type" = ? and "tags"."tag_pk" = "taggables"."tag_tag_pk"'
            .' and "taggables"."taggable_type" = ?)';
        $this->assertEquals($sql, Capsule::getQueryLog()[0]['query']);
        $this->assertEquals([Post::class, Post::class], Capsule::getQueryLog()[0]['bindings']);
    }

    public function testExistenceQueryForSelfRelation()
    {
        $users = User::has('players')->get();

        $this->assertEquals([1], $users->pluck('user_pk')->all());
        $sql = 'select * from "users"'
            .' where exists (select * from "users" as "laravel_reserved_0"'
            .' inner join "teams" on "teams"."team_pk" = "laravel_reserved_0"."team_team_pk"'
            .' inner join "clubs" on "clubs"."club_pk" = "teams"."club_club_pk"'
            .' where "users"."user_pk" = "clubs"."user_user_pk" and "laravel_reserved_0"."deleted_at" is null)'
            .' and "users"."deleted_at" is null';
        $this->assertEquals($sql, Capsule::getQueryLog()[0]['query']);
    }

    public function testExistenceQueryForSelfRelationWithLeadingMorphMany()
    {
        $posts = Post::has('posts')->get();

        $this->assertEquals([1, 3], $posts->pluck('post_pk')->all());
        $sql = 'select * from "posts"'
            .' where exists (select * from "posts" as "laravel_reserved_1"'
            .' inner join "users" on "users"."user_pk" = "laravel_reserved_1"."user_user_pk"'
            .' inner join "likes" on "likes"."user_user_pk" = "users"."user_pk"'
            .' where "users"."deleted_at" is null and "posts"."post_pk" = "likes"."likeable_id"'
            .' and "likes"."likeable_type" = ? and "users"."deleted_at" is null)';
        $this->assertEquals($sql, Capsule::getQueryLog()[0]['query']);
        $this->assertEquals([Post::class], Capsule::getQueryLog()[0]['bindings']);
    }

    public function testWithIntermediate()
    {
        $comments = Country::first()->comments()
            ->withIntermediate(User::class, ['user_pk', 'deleted_at'], 'post.user')
            ->withIntermediate(Post::class)
            ->get();

        $this->assertInstanceOf(Post::class, $post = $comments[0]->post);
        $this->assertEquals(['post_pk' => 1, 'user_user_pk' => 1], $post->getAttributes());
        $this->assertEquals(['user_pk' => 1, 'deleted_at' => null], $post->user->getAttributes());
        $sql = 'select "comments".*, "users"."country_country_pk",'
            .' "users"."user_pk" as "__post.user__user_pk", "users"."deleted_at" as "__post.user__deleted_at",'
            .' "posts"."post_pk" as "__post__post_pk", "posts"."user_user_pk" as "__post__user_user_pk"'
            .' from "comments"'
            .' inner join "posts" on "posts"."post_pk" = "comments"."post_post_pk"'
            .' inner join "users" on "users"."user_pk" = "posts"."user_user_pk"'
            .' where "users"."deleted_at" is null and "users"."country_country_pk" = ?';
        $this->assertEquals($sql, Capsule::getQueryLog()[2]['query']);
    }

    public function testWithPivot()
    {
        $permissions = User::first()->permissions()
            ->withPivot('role_user', ['role_role_pk'])
            ->withPivot('role_user', ['user_user_pk'])
            ->get();

        $this->assertInstanceOf(Pivot::class, $pivot = $permissions[0]->role_user);
        $this->assertEquals(['role_role_pk' => 1, 'user_user_pk' => 1], $pivot->getAttributes());
        $sql = 'select "permissions".*, "role_user"."user_user_pk",'
            .' "role_user"."user_user_pk" as "__role_user__user_user_pk",'
            .' "role_user"."role_role_pk" as "__role_user__role_role_pk"'
            .' from "permissions"'
            .' inner join "roles" on "roles"."role_pk" = "permissions"."role_role_pk"'
            .' inner join "role_user" on "role_user"."role_role_pk" = "roles"."role_pk"'
            .' where "role_user"."user_user_pk" = ?';
        $this->assertEquals($sql, Capsule::getQueryLog()[1]['query']);
    }

    public function testWithPivotClass()
    {
        $permissions = User::first()->permissions()
            ->withPivot('role_user', ['role_role_pk'], RoleUserPivot::class, 'pivot')
            ->get();

        $this->assertInstanceOf(RoleUserPivot::class, $pivot = $permissions[0]->pivot);
        $this->assertEquals(['role_role_pk' => 1], $pivot->getAttributes());
    }
}
