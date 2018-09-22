<?php

namespace Tests;

use Illuminate\Database\Capsule\Manager as Capsule;
use Tests\Models\Country;
use Tests\Models\User;

class HasManyDeepTest extends TestCase
{
    public function testResults()
    {
        $comments = Country::first()->comments;

        $this->assertEquals([1, 2], $comments->pluck('comment_pk')->all());
        $sql = 'select "comments".*, "users"."country_country_pk" from "comments"'
            .' inner join "posts" on "posts"."post_pk" = "comments"."post_post_pk"'
            .' inner join "users" on "users"."user_pk" = "posts"."user_user_pk"'
            .' where "users"."deleted_at" is null and "users"."country_country_pk" = ?';
        $this->assertEquals($sql, Capsule::getQueryLog()[1]['query']);
    }

    public function testPaginate()
    {
        $comments = Country::first()->comments()
            ->paginate();

        $this->assertArrayNotHasKey('country_country_pk', $comments[0]->getAttributes());
    }

    public function testSimplePaginate()
    {
        $comments = Country::first()->comments()
            ->simplePaginate();

        $this->assertArrayNotHasKey('country_country_pk', $comments[0]->getAttributes());
    }

    public function testEagerLoading()
    {
        $countries = Country::with('comments')->get();

        $this->assertEquals([1, 2], $countries[0]->comments->pluck('comment_pk')->all());
        $sql = 'select "comments".*, "users"."country_country_pk" from "comments"'
            .' inner join "posts" on "posts"."post_pk" = "comments"."post_post_pk"'
            .' inner join "users" on "users"."user_pk" = "posts"."user_user_pk"'
            .' where "users"."deleted_at" is null and "users"."country_country_pk" in (?, ?)';
        $this->assertEquals($sql, Capsule::getQueryLog()[1]['query']);
    }

    public function testExistenceQuery()
    {
        $countries = Country::has('comments')->get();

        $this->assertEquals([1], $countries->pluck('country_pk')->all());
        $sql = 'select * from "countries"'
            .' where exists (select * from "comments"'
            .' inner join "posts" on "posts"."post_pk" = "comments"."post_post_pk"'
            .' inner join "users" on "users"."user_pk" = "posts"."user_user_pk"'
            .' where "users"."deleted_at" is null and "countries"."country_pk" = "users"."country_country_pk"'
            .' and "users"."deleted_at" is null)';
        $this->assertEquals($sql, Capsule::getQueryLog()[0]['query']);
    }

    public function testExistenceQueryForSelfRelation()
    {
        $users = User::has('players')->get();

        $this->assertEquals([1], $users->pluck('user_pk')->all());
        $sql = 'select * from "users"'
            .' where exists (select * from "users" as "laravel_reserved_0"'
            .' inner join "teams" on "teams"."team_pk" = "laravel_reserved_0"."team_team_pk"'
            .' inner join "clubs" on "clubs"."club_pk" = "teams"."club_club_pk"'
            .' where "users"."user_pk" = "clubs"."user_user_pk" and "laravel_reserved_0"."deleted_at" is null)'
            .' and "users"."deleted_at" is null';
        $this->assertEquals($sql, Capsule::getQueryLog()[0]['query']);
    }
}
