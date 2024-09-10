<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Tests\Models\Country;
use Tests\Models\Post;
use Tests\Models\RoleUser;
use Tests\Models\User;

class IntermediateTableTest extends TestCase
{
    public function testWithIntermediate(): void
    {
        $comments = Country::find(1)->comments()
            ->withIntermediate(User::class, ['id', 'deleted_at'], 'post.user')
            ->withIntermediate(Post::class)
            ->get();

        $post = $comments[0]->post;
        $this->assertInstanceOf(Post::class, $post);
        $this->assertEquals(['id' => 21, 'user_id' => 11, 'published' => true], $post->getAttributes());
        $this->assertEquals(['id' => 11, 'deleted_at' => null], $post->user->getAttributes());
    }

    public function testWithPivot(): void
    {
        $permissions = User::first()->permissions()
            ->withPivot('role_user', ['role_id'])
            ->withPivot('role_user', ['user_id'])
            ->get();

        $pivot = $permissions[0]->role_user;
        $this->assertInstanceOf(Pivot::class, $pivot);
        $this->assertEquals(['role_id' => 61, 'user_id' => 11], $pivot->getAttributes());
    }

    public function testWithPivotWithClass(): void
    {
        $permissions = User::first()->permissions()
            ->withPivot('role_user', ['role_id'], RoleUser::class, 'pivot')
            ->get();

        $pivot = $permissions[0]->pivot;
        $this->assertInstanceOf(RoleUser::class, $pivot);
        $this->assertEquals(['role_id' => 61], $pivot->getAttributes());
    }

    public function testWithPivotWithPostProcessor(): void
    {
        $permissions = User::first()->permissions()
            ->withPivot('role_user', ['role_id'], postProcessor: function (Model $model, array $attributes) {
                return $attributes += ['foo' => 'bar'];
            })->get();

        $pivot = $permissions[0]->role_user;
        $this->assertEquals(['role_id' => 61, 'foo' => 'bar'], $pivot->getAttributes());
    }

    public function testPaginate(): void
    {
        $comments = Country::find(1)->comments()
            ->withIntermediate(Post::class)
            ->paginate();

        $this->assertTrue($comments[0]->relationLoaded('post'));
        $this->assertArrayNotHasKey('laravel_through_key', $comments[0]);
    }

    public function testSimplePaginate(): void
    {
        $comments = Country::find(1)->comments()
            ->withIntermediate(Post::class)
            ->simplePaginate();

        $this->assertTrue($comments[0]->relationLoaded('post'));
        $this->assertArrayNotHasKey('laravel_through_key', $comments[0]);
    }

    public function testCursorPaginator(): void
    {
        $comments = Country::find(1)->comments()
            ->withIntermediate(Post::class)
            ->cursorPaginate();

        $this->assertTrue($comments[0]->relationLoaded('post'));
        $this->assertArrayNotHasKey('laravel_through_key', $comments[0]);
    }

    public function testChunk(): void
    {
        Country::find(1)->comments()
            ->withIntermediate(Post::class)
            ->chunk(1, function ($results) {
                $this->assertTrue($results[0]->relationLoaded('post'));
            });
    }

    public function testGetIntermediateTables(): void
    {
        $comments = Country::find(1)->comments();

        $this->assertEquals([], $comments->getIntermediateTables());
    }
}
