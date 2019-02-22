<?php

namespace Tests;

use Tests\Models\Comment;
use Tests\Models\Country;
use Tests\Models\Post;
use Tests\Models\Tag;
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

    public function testHasManyDeepFromRelationsWithHasManyThroughAndHasMany()
    {
        $relation = (new Country)->forceFill(['country_pk' => 1])->commentsFromRelations();

        $sql = 'select * from "comments"'
            .' inner join "posts" on "posts"."post_pk" = "comments"."post_post_pk"'
            .' inner join "users" on "users"."user_pk" = "posts"."user_user_pk"'
            .' where "users"."deleted_at" is null and "users"."country_country_pk" = ?';
        $this->assertEquals($sql, $relation->toSql());
        $this->assertEquals([1], $relation->getBindings());
    }

    public function testHasManyDeepFromRelationsWithBelongsToMany()
    {
        $relation = (new User)->forceFill(['user_pk' => 1])->permissionsFromRelations();

        $sql = 'select * from "permissions"'
            .' inner join "roles" on "roles"."role_pk" = "permissions"."role_role_pk"'
            .' inner join "role_user" on "role_user"."role_role_pk" = "roles"."role_pk"'
            .' where "role_user"."user_user_pk" = ?';
        $this->assertEquals($sql, $relation->toSql());
        $this->assertEquals([1], $relation->getBindings());
    }

    public function testHasManyDeepFromRelationsWithMorphManyAndBelongsTo()
    {
        $relation = (new Post)->forceFill(['post_pk' => 1])->usersFromRelations();

        $sql = 'select * from "users"'
            .' inner join "likes" on "likes"."user_user_pk" = "users"."user_pk"'
            .' where "likes"."likeable_id" = ? and "likes"."likeable_type" = ? and "users"."deleted_at" is null';
        $this->assertEquals($sql, $relation->toSql());
        $this->assertEquals([1, Post::class], $relation->getBindings());
    }

    public function testHasManyDeepFromRelationsWithMorphToMany()
    {
        $relation = (new User)->forceFill(['user_pk' => 1])->tagsFromRelations();

        $sql = 'select * from "tags"'
            .' inner join "taggables" on "taggables"."tag_tag_pk" = "tags"."tag_pk"'
            .' inner join "posts" on "posts"."post_pk" = "taggables"."taggable_id"'
            .' where "taggables"."taggable_type" = ? and "posts"."user_user_pk" = ?';
        $this->assertEquals($sql, $relation->toSql());
        $this->assertEquals([Post::class, 1], $relation->getBindings());
    }

    public function testHasManyDeepFromRelationsWithMorphedByMany()
    {
        $relation = (new Tag)->forceFill(['tag_pk' => 1])->commentsFromRelations();

        $sql = 'select * from "comments"'
            .' inner join "posts" on "posts"."post_pk" = "comments"."post_post_pk"'
            .' inner join "taggables" on "taggables"."taggable_id" = "posts"."post_pk"'
            .' where "taggables"."taggable_type" = ? and "taggables"."tag_tag_pk" = ?';
        $this->assertEquals($sql, $relation->toSql());
        $this->assertEquals([Post::class, 1], $relation->getBindings());
    }

    public function testHasManyDeepFromRelationsWithHasManyDeepWithPivotAlias()
    {
        $relation = (new Country)->forceFill(['country_pk' => 1])
            ->hasManyDeepFromRelations((new Country)->permissionsWithPivotAlias());

        $sql = 'select * from "permissions"'
            .' inner join "roles" on "roles"."role_pk" = "permissions"."role_role_pk"'
            .' inner join "role_user" as "alias" on "alias"."role_role_pk" = "roles"."role_pk"'
            .' inner join "users" on "users"."user_pk" = "alias"."user_user_pk"'
            .' where "users"."deleted_at" is null and "users"."country_country_pk" = ?';
        $this->assertEquals($sql, $relation->toSql());
        $this->assertEquals([1], $relation->getBindings());
    }

    public function testHasManyDeepFromRelationsWithHasManyDeepWithPivot()
    {
        $relation = (new Country)->forceFill(['country_pk' => 1])
            ->hasManyDeepFromRelations((new Country)->permissions());

        $sql = 'select * from "permissions"'
            .' inner join "roles" on "roles"."role_pk" = "permissions"."role_role_pk"'
            .' inner join "role_user" on "role_user"."role_role_pk" = "roles"."role_pk"'
            .' inner join "users" on "users"."user_pk" = "role_user"."user_user_pk"'
            .' where "users"."deleted_at" is null and "users"."country_country_pk" = ?';
        $this->assertEquals($sql, $relation->toSql());
        $this->assertEquals([1], $relation->getBindings());
    }

    public function testHasOneDeepFromRelations()
    {
        $relation = (new Country)->forceFill(['country_pk' => 1])->commentFromRelations();

        $sql = 'select * from "comments"'
            .' inner join "posts" on "posts"."post_pk" = "comments"."post_post_pk"'
            .' inner join "users" on "users"."user_pk" = "posts"."user_user_pk"'
            .' where "users"."deleted_at" is null and "users"."country_country_pk" = ?';
        $this->assertEquals($sql, $relation->toSql());
        $this->assertEquals([1], $relation->getBindings());
    }
}
