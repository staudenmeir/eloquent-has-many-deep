<?php

namespace Tests;

use Illuminate\Support\Facades\Schema;
use Tests\Models\Comment;
use Tests\Models\Permission;

class ReversingTest extends TestCase
{
    public function testHasManyDeep(): void
    {
        $tags = Comment::find(31)->tags;

        $this->assertEquals([91], $tags->pluck('id')->all());
    }

    public function testHasOneDeep(): void
    {
        $country = Comment::find(31)->country;

        $this->assertEquals(1, $country->id);
    }

    public function testAlias(): void
    {
        $post = Comment::find(36)->rootPost;

        $this->assertEquals(24, $post->id);
    }

    public function testPivotAlias(): void
    {
        $countries = Permission::find(71)->countries;

        $this->assertEquals([1], $countries->pluck('id')->all());
    }

    public function testCustomThroughTable(): void
    {
        Schema::rename('users', 'my_users');

        $country = Comment::find(31)->countryWithCustomThroughTable;

        $this->assertEquals(1, $country->id);
    }
}
