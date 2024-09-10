<?php

namespace Tests\Concatenation\LaravelAdjacencyList;

use Illuminate\Database\Eloquent\Relations\Relation;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Tests\Concatenation\LaravelAdjacencyList\Models\User;

class BloodlineTest extends TestCase
{
    public function testLazyLoading(): void
    {
        $posts = User::find(5)->bloodlinePosts()->orderBy('id')->get();

        $this->assertEquals([10, 20, 50, 80], $posts->pluck('id')->all());
    }

    public function testLazyLoadingWithoutParentKey(): void
    {
        $posts = (new User())->bloodlinePosts()->get();

        $this->assertEmpty($posts);
    }

    public function testEagerLoading(): void
    {
        $users = User::with([
            'bloodlinePosts' => fn (HasManyDeep $query) => $query->orderBy('id'),
        ])->get();

        $this->assertEquals([10, 20, 30, 40, 50, 60, 70, 80], $users[0]->bloodlinePosts->pluck('id')->all());
        $this->assertEquals([10, 20, 50, 80], $users[1]->bloodlinePosts->pluck('id')->all());
        $this->assertEquals([10, 20, 50, 80], $users[4]->bloodlinePosts->pluck('id')->all());
    }

    public function testLazyEagerLoading(): void
    {
        $users = User::all()->load([
            'bloodlinePosts' => fn (Relation $query) => $query->getQuery()->orderBy('id'),
        ]);

        $this->assertEquals([10, 20, 30, 40, 50, 60, 70, 80], $users[0]->bloodlinePosts->pluck('id')->all());
        $this->assertEquals([10, 20, 50, 80], $users[1]->bloodlinePosts->pluck('id')->all());
        $this->assertEquals([10, 20, 50, 80], $users[4]->bloodlinePosts->pluck('id')->all());
    }

    public function testExistenceQuery(): void
    {
        if (in_array($this->connection, ['mariadb', 'sqlsrv'])) {
            $this->markTestSkipped();
        }

        $users = User::find(8)->ancestors()->has('bloodlinePosts', '=', 4)->get();

        $this->assertEquals([5, 2], $users->pluck('id')->all());
    }

    public function testExistenceQueryForSelfRelation(): void
    {
        if (in_array($this->connection, ['mariadb', 'sqlsrv'])) {
            $this->markTestSkipped();
        }

        $users = User::has('bloodlinePosts', '=', 4)->get();

        $this->assertEquals([2, 5, 8], $users->pluck('id')->all());
    }
}
