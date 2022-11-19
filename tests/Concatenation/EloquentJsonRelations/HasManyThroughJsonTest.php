<?php

namespace Tests\Concatenation\EloquentJsonRelations;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Tests\Concatenation\EloquentJsonRelations\Models\Project;
use Tests\Concatenation\EloquentJsonRelations\Models\Role;

class HasManyThroughJsonTest extends TestCase
{
    public function testLazyLoading()
    {
        $projects = Role::find(2)->projects;

        $this->assertEquals([71, 73], $projects->pluck('id')->all());
    }

    public function testLazyLoadingWithObjects()
    {
        $projects = Role::find(2)->projects2;

        $this->assertEquals([71, 73], $projects->pluck('id')->all());
        $pivot = $projects[0]->pivot;
        $this->assertInstanceOf(Pivot::class, $pivot);
        $this->assertTrue($pivot->exists);
        $this->assertEquals(['role' => ['active' => false]], $pivot->getAttributes());
    }

    public function testLazyLoadingWithReverseRelationship()
    {
        $roles = Project::find(71)->roles;

        $this->assertEquals([1, 2], $roles->pluck('id')->all());
    }

    public function testLazyLoadingWithReverseRelationshipAndObjects()
    {
        $roles = Project::find(71)->roles2;

        $this->assertEquals([1, 2], $roles->pluck('id')->all());
        $pivot = $roles[0]->pivot;
        $this->assertInstanceOf(Pivot::class, $pivot);
        $this->assertTrue($pivot->exists);
        $this->assertEquals(['role' => ['active' => true]], $pivot->getAttributes());
    }

    public function testEmptyLazyLoading()
    {
        $projects = (new Role())->projects()->get();

        $this->assertEmpty($projects);
    }

    public function testEmptyLazyLoadingWithReverseRelationship()
    {
        DB::enableQueryLog();

        $roles = (new Project())->roles;

        $this->assertInstanceOf(Collection::class, $roles);
        $this->assertEmpty(DB::getQueryLog());
    }

    public function testEagerLoading()
    {
        $roles = Role::with('projects')->get();

        $this->assertEquals([71], $roles[0]->projects->pluck('id')->all());
        $this->assertEquals([71, 73], $roles[1]->projects->pluck('id')->all());
        $this->assertEquals([73], $roles[2]->projects->pluck('id')->all());
        $this->assertEquals([], $roles[3]->projects->pluck('id')->all());
    }

    public function testEagerLoadingWithObjects()
    {
        $roles = Role::with('projects2')->get();

        $this->assertEquals([71], $roles[0]->projects2->pluck('id')->all());
        $this->assertEquals([71, 73], $roles[1]->projects2->pluck('id')->all());
        $this->assertEquals([73], $roles[2]->projects2->pluck('id')->all());
        $this->assertEquals([], $roles[3]->projects2->pluck('id')->all());
        $pivot = $roles[1]->projects2[0]->pivot;
        $this->assertInstanceOf(Pivot::class, $pivot);
        $this->assertTrue($pivot->exists);
        $this->assertEquals(['role' => ['active' => false]], $pivot->getAttributes());
    }

    public function testEagerLoadingWithReverseRelationship()
    {
        $projects = Project::with('roles')->get();

        $this->assertEquals([1, 2], $projects[0]->roles->pluck('id')->all());
        $this->assertEquals([], $projects[1]->roles->pluck('id')->all());
        $this->assertEquals([2, 3], $projects[2]->roles->pluck('id')->all());
    }

    public function testEagerLoadingWithReverseRelationshipAndObjects()
    {
        $projects = Project::with('roles2')->get();

        $this->assertEquals([1, 2], $projects[0]->roles2->pluck('id')->all());
        $this->assertEquals([], $projects[1]->roles2->pluck('id')->all());
        $this->assertEquals([2, 3], $projects[2]->roles2->pluck('id')->all());
        $pivot = $projects[0]->roles2[0]->pivot;
        $this->assertInstanceOf(Pivot::class, $pivot);
        $this->assertTrue($pivot->exists);
        $this->assertEquals(['role' => ['active' => true]], $pivot->getAttributes());
    }

    public function testExistenceQuery()
    {
        $roles = Role::has('projects')->get();

        $this->assertEquals([1, 2, 3], $roles->pluck('id')->all());
    }

    public function testExistenceQueryWithObjects()
    {
        $roles = Role::has('projects2')->get();

        $this->assertEquals([1, 2, 3], $roles->pluck('id')->all());
    }

    public function testExistenceQueryWithReverseRelationship()
    {
        $projects = Project::has('roles')->get();

        $this->assertEquals([71, 73], $projects->pluck('id')->all());
    }

    public function testExistenceQueryWithReverseRelationshipAndObjects()
    {
        $projects = Project::has('roles2')->get();

        $this->assertEquals([71, 73], $projects->pluck('id')->all());
    }
}
