<?php

namespace Tests\Concatenation\LaravelAdjacencyList;

use Tests\Concatenation\LaravelAdjacencyList\Models\User;

class DescendantsTest extends TestCase
{
    public function testLazyLoading(): void
    {
        $posts = User::find(2)->descendantPosts;

        $this->assertEquals([50, 80], $posts->pluck('id')->all());
    }

    public function testLazyLoadingAndSelf(): void
    {
        $posts = User::find(2)->descendantPostsAndSelf;

        $this->assertEquals([20, 50, 80], $posts->pluck('id')->all());
    }

    public function testLazyLoadingWithoutParentKey(): void
    {
        $posts = (new User())->descendantPosts()->get();

        $this->assertEmpty($posts);
    }

    public function testEagerLoading(): void
    {
        $users = User::with('descendantPosts')->get();

        $this->assertEquals([20, 30, 40, 50, 60, 70, 80], $users[0]->descendantPosts->pluck('id')->all());
        $this->assertEquals([50, 80], $users[1]->descendantPosts->pluck('id')->all());
        $this->assertEquals([], $users[8]->descendantPosts->pluck('id')->all());
        $this->assertEquals([100, 110], $users[9]->descendantPosts->pluck('id')->all());
    }

    public function testEagerLoadingAndSelf(): void
    {
        $users = User::with('descendantPostsAndSelf')->get();

        $this->assertEquals([10, 20, 30, 40, 50, 60, 70, 80], $users[0]->descendantPostsAndSelf->pluck('id')->all());
        $this->assertEquals([20, 50, 80], $users[1]->descendantPostsAndSelf->pluck('id')->all());
        $this->assertEquals([], $users[8]->descendantPostsAndSelf->pluck('id')->all());
        $this->assertEquals([100, 110], $users[9]->descendantPostsAndSelf->pluck('id')->all());
    }

    public function testEagerLoadingWithHasOneDeep(): void
    {
        $users = User::with('descendantPost')->get();

        $this->assertEquals(20, $users[0]->descendantPost->id);
        $this->assertEquals(50, $users[1]->descendantPost->id);
        $this->assertNull($users[8]->descendantPost);
        $this->assertEquals(100, $users[9]->descendantPost->id);
    }

    public function testLazyEagerLoading(): void
    {
        $users = User::all()->load('descendantPosts');

        $this->assertEquals([20, 30, 40, 50, 60, 70, 80], $users[0]->descendantPosts->pluck('id')->all());
        $this->assertEquals([50, 80], $users[1]->descendantPosts->pluck('id')->all());
        $this->assertEquals([], $users[8]->descendantPosts->pluck('id')->all());
        $this->assertEquals([100, 110], $users[9]->descendantPosts->pluck('id')->all());
    }

    public function testLazyEagerLoadingAndSelf(): void
    {
        $users = User::all()->load('descendantPostsAndSelf');

        $this->assertEquals([10, 20, 30, 40, 50, 60, 70, 80], $users[0]->descendantPostsAndSelf->pluck('id')->all());
        $this->assertEquals([20, 50, 80], $users[1]->descendantPostsAndSelf->pluck('id')->all());
        $this->assertEquals([], $users[8]->descendantPostsAndSelf->pluck('id')->all());
        $this->assertEquals([100, 110], $users[9]->descendantPostsAndSelf->pluck('id')->all());
    }

    public function testExistenceQuery(): void
    {
        if (in_array($this->connection, ['mariadb', 'sqlsrv'])) {
            $this->markTestSkipped();
        }

        $users = User::find(8)->ancestors()->has('descendantPosts', '>', 1)->get();

        $this->assertEquals([2, 1], $users->pluck('id')->all());
    }

    public function testExistenceQueryAndSelf(): void
    {
        if (in_array($this->connection, ['mariadb', 'sqlsrv'])) {
            $this->markTestSkipped();
        }

        $users = User::find(8)->ancestors()->has('descendantPostsAndSelf', '>', 2)->get();

        $this->assertEquals([2, 1], $users->pluck('id')->all());
    }

    public function testExistenceQueryForSelfRelation(): void
    {
        if (in_array($this->connection, ['mariadb', 'sqlsrv'])) {
            $this->markTestSkipped();
        }

        $users = User::has('descendantPosts', '>', 1)->get();

        $this->assertEquals([1, 2, 11], $users->pluck('id')->all());
    }

    public function testExistenceQueryForSelfRelationAndSelf(): void
    {
        if (in_array($this->connection, ['mariadb', 'sqlsrv'])) {
            $this->markTestSkipped();
        }

        $users = User::has('descendantPostsAndSelf', '>', 2)->get();

        $this->assertEquals([1, 2], $users->pluck('id')->all());
    }
}
