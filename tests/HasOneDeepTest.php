<?php

namespace Tests;

use Illuminate\Database\Capsule\Manager as DB;
use Tests\Models\Comment;
use Tests\Models\Country;

class HasOneDeepTest extends TestCase
{
    public function testLazyLoading()
    {
        $comment = Country::find(1)->comment;

        $this->assertEquals(31, $comment->id);
    }

    public function testLazyLoadingWithDefault()
    {
        $comment = Country::find(3)->comment;

        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertFalse($comment->exists);
    }

    public function testEagerLoading()
    {
        $countries = Country::with('comment')->get();

        $this->assertEquals(31, $countries[0]->comment->id);
        $this->assertInstanceOf(Comment::class, $countries[2]->comment);
        $this->assertFalse($countries[2]->comment->exists);
    }

    public function testLazyEagerLoading()
    {
        $countries = Country::all()->load('comment');

        $this->assertEquals(31, $countries[0]->comment->id);
        $this->assertInstanceOf(Comment::class, $countries[2]->comment);
        $this->assertFalse($countries[2]->comment->exists);
    }

    public function testFromRelations()
    {
        $comment = Country::find(1)->commentFromRelations;

        $this->assertEquals(31, $comment->id);
    }

    public function testFromRelationsWithConstraints()
    {
        $comment = Country::find(1)->commentFromRelationsWithConstraints;

        $this->assertEquals(31, $comment->id);
    }

    public function testReverse()
    {
        $country = Comment::find(31)->country;

        $this->assertEquals(1, $country->id);
    }

    public function testReverseWithAlias()
    {
        $post = Comment::find(36)->rootPost;

        $this->assertEquals(24, $post->id);
    }

    public function testReverseWithCustomThroughTable()
    {
        DB::schema()->rename('users', 'my_users');

        $country = Comment::find(31)->countryWithCustomThroughTable;

        $this->assertEquals(1, $country->id);
    }
}
