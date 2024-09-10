<?php

namespace Tests;

use Tests\Models\Employee;
use Tests\Models\Project;

class CompositeKeyTest extends TestCase
{
    public function testLazyLoadingWithLeadingKey(): void
    {
        $projects = Employee::find(131)->projects;

        $this->assertEquals([101, 102], $projects->pluck('id')->all());
    }

    public function testLazyLoadingWithIntermediateKey(): void
    {
        $employees = Project::find(101)->employees;

        $this->assertEquals([131, 132], $employees->pluck('id')->all());
    }

    public function testEagerLoadingWithLeadingKey(): void
    {
        $employees = Employee::with('projects')->get();

        $this->assertEquals([101, 102], $employees[0]->projects->pluck('id')->all());
        $this->assertEquals([103], $employees[2]->projects->pluck('id')->all());
    }

    public function testEagerLoadingWithIntermediateKey(): void
    {
        $projects = Project::with('employees')->get();

        $this->assertEquals([131, 132], $projects[0]->employees->pluck('id')->all());
        $this->assertEquals([133], $projects[2]->employees->pluck('id')->all());
    }

    public function testLazyEagerLoadingWithLeadingKey(): void
    {
        $employees = Employee::all()->load('projects');

        $this->assertEquals([101, 102], $employees[0]->projects->pluck('id')->all());
        $this->assertEquals([103], $employees[2]->projects->pluck('id')->all());
    }

    public function testLazyEagerLoadingWithIntermediateKey(): void
    {
        $projects = Project::all()->load('employees');

        $this->assertEquals([131, 132], $projects[0]->employees->pluck('id')->all());
        $this->assertEquals([133], $projects[2]->employees->pluck('id')->all());
    }

    public function testExistenceQueryWithLeadingKey(): void
    {
        $employees = Employee::has('projects')->get();

        $this->assertEquals([131, 132, 133], $employees->pluck('id')->all());
    }

    public function testExistenceQueryWithIntermediateKey(): void
    {
        $projects = Project::has('employees')->get();

        $this->assertEquals([101, 102, 103], $projects->pluck('id')->all());
    }

    public function testPaginateWithLeadingKey(): void
    {
        $projects = Employee::find(131)->projects()->paginate();

        $this->assertArrayNotHasKey('laravel_through_key', $projects[0]);
        $this->assertArrayNotHasKey('laravel_through_key_1', $projects[0]);
    }

    public function testSimplePaginateWithLeadingKey(): void
    {
        $projects = Employee::find(131)->projects()->simplePaginate();

        $this->assertArrayNotHasKey('laravel_through_key', $projects[0]);
        $this->assertArrayNotHasKey('laravel_through_key_1', $projects[0]);
    }

    public function testCursorPaginateWithLeadingKey(): void
    {
        $projects = Employee::find(131)->projects()->cursorPaginate();

        $this->assertArrayNotHasKey('laravel_through_key', $projects[0]);
        $this->assertArrayNotHasKey('laravel_through_key_1', $projects[0]);
    }
}
