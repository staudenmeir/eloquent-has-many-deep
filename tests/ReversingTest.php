<?php

namespace Tests;

use Illuminate\Database\Capsule\Manager as DB;
use Tests\Models\Comment;
use Tests\Models\Permission;

class ReversingTest extends TestCase
{
    public function testHasManyDeep()
    {
        $tags = Comment::find(31)->tags;

        $this->assertEquals([91], $tags->pluck('id')->all());
    }

    public function testHasOneDeep()
    {
        $country = Comment::find(31)->country;

        $this->assertEquals(1, $country->id);
    }

    public function testAlias()
    {
        $post = Comment::find(36)->rootPost;

        $this->assertEquals(24, $post->id);
    }

    public function testPivotAlias()
    {
        $countries = Permission::find(71)->countries;

        $this->assertEquals([1], $countries->pluck('id')->all());
    }

    public function testCustomThroughTable()
    {
        DB::schema()->rename('users', 'my_users');

        $country = Comment::find(31)->countryWithCustomThroughTable;

        $this->assertEquals(1, $country->id);
    }
}
