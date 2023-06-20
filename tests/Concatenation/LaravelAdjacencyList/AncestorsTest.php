<?php

namespace Tests\Concatenation\LaravelAdjacencyList;

use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Tests\Concatenation\LaravelAdjacencyList\Models\User;

class AncestorsTest extends TestCase
{
    public function testLazyLoading()
    {
        $posts = User::find(8)->ancestorPosts()->orderBy('id')->get();

        $this->assertEquals([10, 20, 50], $posts->pluck('id')->all());
    }

    public function testLazyLoadingAndSelf()
    {
        $posts = User::find(8)->ancestorAndSelfPosts()->orderBy('id')->get();

        $this->assertEquals([10, 20, 50, 80], $posts->pluck('id')->all());
    }

    public function testLazyLoadingWithoutParentKey()
    {
        $posts = (new User())->ancestorPosts()->get();

        $this->assertEmpty($posts);
    }

    public function testEagerLoading()
    {
        $users = User::with([
            'ancestorPosts' => fn (HasManyDeep $query) => $query->orderBy('id'),
        ])->get();

        $this->assertEquals([], $users[0]->ancestorPosts->pluck('id')->all());
        $this->assertEquals([10], $users[1]->ancestorPosts->pluck('id')->all());
        $this->assertEquals([10, 20, 50], $users[7]->ancestorPosts->pluck('id')->all());
        $this->assertEquals([], $users[10]->ancestorPosts->pluck('id')->all());
    }

    public function testEagerLoadingAndSelf()
    {
        $users = User::with('ancestorAndSelfPosts')->get();

        $this->assertEquals([10], $users[0]->ancestorAndSelfPosts->pluck('id')->all());
        $this->assertEquals([10, 20], $users[1]->ancestorAndSelfPosts->pluck('id')->all());
        $this->assertEquals([10, 20, 50, 80], $users[7]->ancestorAndSelfPosts->pluck('id')->all());
        $this->assertEquals([100, 110], $users[10]->ancestorAndSelfPosts->pluck('id')->all());
    }

    public function testEagerLoadingWithHasOneDeep()
    {
        $users = User::with([
            'ancestorPost' => fn (HasManyDeep $query) => $query->orderBy('id'),
        ])->get();

        $this->assertNull($users[0]->ancestorPost);
        $this->assertEquals(10, $users[1]->ancestorPost->id);
        $this->assertEquals(10, $users[7]->ancestorPost->id);
        $this->assertNull($users[10]->ancestorPost);
    }

    public function testLazyEagerLoading()
    {
        $users = User::all()->load([
            'ancestorPosts' => fn (HasManyDeep $query) => $query->orderBy('id'),
        ]);

        $this->assertEquals([], $users[0]->ancestorPosts->pluck('id')->all());
        $this->assertEquals([10], $users[1]->ancestorPosts->pluck('id')->all());
        $this->assertEquals([10, 20, 50], $users[7]->ancestorPosts->pluck('id')->all());
        $this->assertEquals([], $users[10]->ancestorPosts->pluck('id')->all());
    }

    public function testLazyEagerLoadingAndSelf()
    {
        $users = User::all()->load('ancestorAndSelfPosts');

        $this->assertEquals([10], $users[0]->ancestorAndSelfPosts->pluck('id')->all());
        $this->assertEquals([10, 20], $users[1]->ancestorAndSelfPosts->pluck('id')->all());
        $this->assertEquals([10, 20, 50, 80], $users[7]->ancestorAndSelfPosts->pluck('id')->all());
        $this->assertEquals([100, 110], $users[10]->ancestorAndSelfPosts->pluck('id')->all());
    }

    public function testExistenceQuery()
    {
        if (in_array($this->database, ['mariadb', 'sqlsrv'])) {
            $this->markTestSkipped();
        }

        $users = User::find(1)->descendants()->has('ancestorPosts', '>', 1)->get();

        $this->assertEquals([5, 6, 7, 8, 9], $users->pluck('id')->all());
    }

    public function testExistenceQueryAndSelf()
    {
        if (in_array($this->database, ['mariadb', 'sqlsrv'])) {
            $this->markTestSkipped();
        }

        $users = User::find(1)->descendants()->has('ancestorAndSelfPosts', '>', 2)->get();

        $this->assertEquals([5, 6, 7, 8, 9], $users->pluck('id')->all());
    }

    public function testExistenceQueryForSelfRelation()
    {
        if (in_array($this->database, ['mariadb', 'sqlsrv'])) {
            $this->markTestSkipped();
        }

        $users = User::has('ancestorPosts', '>', 1)->get();

        $this->assertEquals([5, 6, 7, 8, 9], $users->pluck('id')->all());
    }

    public function testExistenceQueryForSelfRelationAndSelf()
    {
        if (in_array($this->database, ['mariadb', 'sqlsrv'])) {
            $this->markTestSkipped();
        }

        $users = User::has('ancestorAndSelfPosts', '>', 2)->get();

        $this->assertEquals([5, 6, 7, 8, 9], $users->pluck('id')->all());
    }
}
