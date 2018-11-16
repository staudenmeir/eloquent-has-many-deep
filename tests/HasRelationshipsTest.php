<?php

namespace Tests;

use Tests\Models\Comment;
use Tests\Models\Country;
use Tests\Models\Post;
use Tests\Models\User;

class HasRelationshipsTest extends TestCase
{
    public function testHasManyDeepWithDefaultKeys()
    {
        $relation = (new Country)->forceFill(['country_pk' => 1])->comments();

        $sql = 'select * from "comments"'
            .' inner join "posts" on "posts"."post_pk" = "comments"."post_post_pk"'
            .' inner join "users" on "users"."user_pk" = "posts"."user_user_pk"'
            .' where "users"."deleted_at" is null and "users"."country_country_pk" = ?';
        $this->assertEquals($sql, $relation->toSql());
        $this->assertEquals([1], $relation->getBindings());
    }

    public function testHasManyDeepWithCustomKeys()
    {
        $relation = (new Country)->forceFill(['country_pk' => 1])
            ->hasManyDeep(Comment::class, [User::class, Post::class], [null, 'user_id'], [null, 'id']);

        $sql = 'select * from "comments" inner join "posts" on "posts"."post_pk" = "comments"."post_post_pk"'
            .' inner join "users" on "users"."id" = "posts"."user_id"'
            .' where "users"."deleted_at" is null and "users"."country_country_pk" = ?';
        $this->assertEquals($sql, $relation->toSql());
        $this->assertEquals([1], $relation->getBindings());
    }

    public function testHasManyDeepWithLeadingBelongsToMany()
    {
        $relation = (new User)->forceFill(['user_pk' => 1])->permissions();

        $sql = 'select * from "permissions"'
            .' inner join "roles" on "roles"."role_pk" = "permissions"."role_role_pk"'
            .' inner join "role_user" on "role_user"."role_role_pk" = "roles"."role_pk"'
            .' where "role_user"."user_user_pk" = ?';
        $this->assertEquals($sql, $relation->toSql());
        $this->assertEquals([1], $relation->getBindings());
    }

    public function testHasManyDeepWithIntermediateBelongsToMany()
    {
        $relation = (new Country)->forceFill(['country_pk' => 1])->permissions();

        $sql = 'select * from "permissions"'
            .' inner join "roles" on "roles"."role_pk" = "permissions"."role_role_pk"'
            .' inner join "role_user" on "role_user"."role_role_pk" = "roles"."role_pk"'
            .' inner join "users" on "users"."user_pk" = "role_user"."user_user_pk"'
            .' where "users"."deleted_at" is null and "users"."country_country_pk" = ?';
        $this->assertEquals($sql, $relation->toSql());
        $this->assertEquals([1], $relation->getBindings());
    }

    public function testHasManyDeepWithTrailingBelongsToMany()
    {
        $relation = (new Country)->forceFill(['country_pk' => 1])->roles();

        $sql = 'select * from "roles"'
            .' inner join "role_user" on "role_user"."role_role_pk" = "roles"."role_pk"'
            .' inner join "users" on "users"."user_pk" = "role_user"."user_user_pk"'
            .' where "users"."deleted_at" is null and "users"."country_country_pk" = ?';
        $this->assertEquals($sql, $relation->toSql());
        $this->assertEquals([1], $relation->getBindings());
    }

    public function testHasManyDeepWithAlias()
    {
        $relation = (new Country)->forceFill(['country_pk' => 1])->commentsWithAlias();

        $sql = 'select * from "comments"'
            .' inner join "posts" on "posts"."post_pk" = "comments"."post_post_pk"'
            .' inner join "users" as "alias" on "alias"."user_pk" = "posts"."user_user_pk"'
            .' where "alias"."deleted_at" is null and "alias"."country_country_pk" = ?';
        $this->assertEquals($sql, $relation->toSql());
        $this->assertEquals([1], $relation->getBindings());
    }
}
