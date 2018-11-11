<?php

namespace Tests;

use Illuminate\Database\Capsule\Manager as DB;
use Tests\Models\Comment;
use Tests\Models\Country;

class HasOneDeepTest extends TestCase
{
    public function testLazyLoading()
    {
        $comment = Country::first()->comment;

        $this->assertEquals(1, $comment->comment_pk);
        $sql = 'select "comments".*, "users"."country_country_pk" from "comments"'
            .' inner join "posts" on "posts"."post_pk" = "comments"."post_post_pk"'
            .' inner join "users" on "users"."user_pk" = "posts"."user_user_pk"'
            .' where "users"."deleted_at" is null and "users"."country_country_pk" = ? limit 1';
        $this->assertEquals($sql, DB::getQueryLog()[1]['query']);
        $this->assertEquals([1], DB::getQueryLog()[1]['bindings']);
    }

    public function testDefault()
    {
        $comment = Country::find(2)->comment;

        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertFalse($comment->exists);
    }

    public function testEagerLoading()
    {
        $countries = Country::with('comment')->get();

        $this->assertEquals(1, $countries[0]->comment->comment_pk);
        $this->assertInstanceOf(Comment::class, $countries[1]->comment);
        $this->assertFalse($countries[1]->comment->exists);
        $sql = 'select "comments".*, "users"."country_country_pk" from "comments"'
            .' inner join "posts" on "posts"."post_pk" = "comments"."post_post_pk"'
            .' inner join "users" on "users"."user_pk" = "posts"."user_user_pk"'
            .' where "users"."deleted_at" is null and "users"."country_country_pk" in (?, ?)';
        $this->assertEquals($sql, DB::getQueryLog()[1]['query']);
        $this->assertEquals([1, 2], DB::getQueryLog()[1]['bindings']);
    }
}
