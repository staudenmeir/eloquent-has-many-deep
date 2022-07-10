<?php

namespace Tests;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Tests\Models\Country;
use Tests\Models\Post;
use Tests\Models\RoleUser;
use Tests\Models\User;

class IntermediateTableTest extends TestCase
{
    public function testWithIntermediate()
    {
        $comments = Country::find(1)->comments()
            ->withIntermediate(User::class, ['id', 'deleted_at'], 'post.user')
            ->withIntermediate(Post::class)
            ->get();

        $this->assertInstanceOf(Post::class, $post = $comments[0]->post);
        $this->assertEquals(['id' => 21, 'user_id' => 11, 'published' => true], $post->getAttributes());
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

    public function testPaginate()
    {
        $comments = Country::find(1)->comments()
            ->withIntermediate(Post::class)
            ->paginate();

        $this->assertTrue($comments[0]->relationLoaded('post'));
        $this->assertArrayNotHasKey('laravel_through_key', $comments[0]);
    }

    public function testSimplePaginate()
    {
        $comments = Country::find(1)->comments()
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

        $comments = Country::find(1)->comments()
            ->withIntermediate(Post::class)
            ->cursorPaginate();

        $this->assertTrue($comments[0]->relationLoaded('post'));
        $this->assertArrayNotHasKey('laravel_through_key', $comments[0]);
    }

    public function testChunk()
    {
        Country::find(1)->comments()
            ->withIntermediate(Post::class)
            ->chunk(1, function ($results) {
                $this->assertTrue($results[0]->relationLoaded('post'));
            });
    }
}
