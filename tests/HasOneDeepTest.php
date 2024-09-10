<?php

namespace Tests;

use Tests\Models\Comment;
use Tests\Models\Country;

class HasOneDeepTest extends TestCase
{
    public function testLazyLoading(): void
    {
        $comment = Country::find(1)->comment;

        $this->assertEquals(31, $comment->id);
    }

    public function testLazyLoadingWithDefault(): void
    {
        $comment = Country::find(3)->comment;

        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertFalse($comment->exists);
    }

    public function testEagerLoading(): void
    {
        $countries = Country::with('comment')->get();

        $this->assertEquals(31, $countries[0]->comment->id);
        $this->assertInstanceOf(Comment::class, $countries[2]->comment);
        $this->assertFalse($countries[2]->comment->exists);
    }

    public function testLazyEagerLoading(): void
    {
        $countries = Country::all()->load('comment');

        $this->assertEquals(31, $countries[0]->comment->id);
        $this->assertInstanceOf(Comment::class, $countries[2]->comment);
        $this->assertFalse($countries[2]->comment->exists);
    }
}
