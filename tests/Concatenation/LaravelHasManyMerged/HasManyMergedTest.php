<?php

namespace Tests\Concatenation\LaravelHasManyMerged;

use Tests\Concatenation\LaravelHasManyMerged\Models\Country;
use Tests\Concatenation\LaravelHasManyMerged\Models\User;

class HasManyMergedTest extends TestCase
{
    public function testLazyLoadingWithLeadingKey(): void
    {
        $messages = User::find(11)->attachments;

        $this->assertEquals([31, 33], $messages->pluck('id')->all());
    }

    public function testLazyLoadingWithIntermediateKey(): void
    {
        $attachments = Country::find(1)->attachments;

        $this->assertEquals([31, 31, 32, 33], $attachments->pluck('id')->all());
    }

    public function testLazyLoadingWithTrailingKey(): void
    {
        $messages = Country::find(1)->messages;

        $this->assertEquals([21, 21, 22, 23], $messages->pluck('id')->all());
    }

    public function testEagerLoadingWithLeadingKey(): void
    {
        $users = User::with('attachments')->get();

        $this->assertEquals([31, 33], $users[0]->attachments->pluck('id')->all());
        $this->assertEquals([31, 32], $users[1]->attachments->pluck('id')->all());
        $this->assertEquals([], $users[4]->attachments->pluck('id')->all());
    }

    public function testEagerLoadingWithIntermediateKey(): void
    {
        $countries = Country::with('attachments')->get();

        $this->assertEquals([31, 31, 32, 33], $countries[0]->attachments->pluck('id')->all());
        $this->assertEquals([32, 33, 34], $countries[1]->attachments->pluck('id')->all());
        $this->assertEquals([], $countries[3]->attachments->pluck('id')->all());
    }

    public function testEagerLoadingWithTrailingKey(): void
    {
        $countries = Country::with('messages')->get();

        $this->assertEquals([21, 21, 22, 23], $countries[0]->messages->pluck('id')->all());
        $this->assertEquals([22, 23, 24], $countries[1]->messages->pluck('id')->all());
        $this->assertEquals([], $countries[3]->messages->pluck('id')->all());
    }

    public function testLazyEagerLoadingWithLeadingKey(): void
    {
        $users = User::all()->load('attachments');

        $this->assertEquals([31, 33], $users[0]->attachments->pluck('id')->all());
        $this->assertEquals([31, 32], $users[1]->attachments->pluck('id')->all());
        $this->assertEquals([], $users[4]->attachments->pluck('id')->all());
    }

    public function testLazyEagerLoadingWithIntermediateKey(): void
    {
        $countries = Country::all()->load('attachments');

        $this->assertEquals([31, 31, 32, 33], $countries[0]->attachments->pluck('id')->all());
        $this->assertEquals([32, 33, 34], $countries[1]->attachments->pluck('id')->all());
        $this->assertEquals([], $countries[3]->attachments->pluck('id')->all());
    }

    public function testLazyEagerLoadingWithTrailingKey(): void
    {
        $countries = Country::all()->load('messages');

        $this->assertEquals([21, 21, 22, 23], $countries[0]->messages->pluck('id')->all());
        $this->assertEquals([22, 23, 24], $countries[1]->messages->pluck('id')->all());
        $this->assertEquals([], $countries[3]->messages->pluck('id')->all());
    }

    public function testExistenceQueryWithLeadingKey(): void
    {
        $users = User::has('attachments')->get();

        $this->assertEquals([11, 12, 13], $users->pluck('id')->all());
    }

    public function testExistenceQueryWithIntermediateKey(): void
    {
        $countries = Country::has('attachments')->get();

        $this->assertEquals([1, 2], $countries->pluck('id')->all());
    }

    public function testExistenceQueryWithTrailingKey(): void
    {
        $messages = Country::has('messages')->get();

        $this->assertEquals([1, 2, 3], $messages->pluck('id')->all());
    }

    public function testPaginateWithLeadingKey(): void
    {
        $users = User::find(11)->attachments()->paginate();

        $this->assertArrayNotHasKey('laravel_through_key', $users[0]);
        $this->assertArrayNotHasKey('laravel_through_key_1', $users[0]);
    }

    public function testSimplePaginateWithLeadingKey(): void
    {
        $attachments = User::find(11)->attachments()->simplePaginate();

        $this->assertArrayNotHasKey('laravel_through_key', $attachments[0]);
        $this->assertArrayNotHasKey('laravel_through_key_1', $attachments[0]);
    }

    public function testCursorPaginateWithLeadingKey(): void
    {
        $attachments = User::find(11)->attachments()->cursorPaginate();

        $this->assertArrayNotHasKey('laravel_through_key', $attachments[0]);
        $this->assertArrayNotHasKey('laravel_through_key_1', $attachments[0]);
    }
}
