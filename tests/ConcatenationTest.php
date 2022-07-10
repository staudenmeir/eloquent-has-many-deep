<?php

namespace Tests;

use Illuminate\Database\Capsule\Manager as DB;
use Tests\Models\Country;
use Tests\Models\Post;
use Tests\Models\Tag;
use Tests\Models\User;

class ConcatenationTest extends TestCase
{
    public function testHasManyDeep()
    {
        $comments = Country::find(1)->commentsFromRelations;

        $this->assertEquals([31, 32], $comments->pluck('id')->all());
    }

    public function testWithBelongsToMany()
    {
        $permissions = User::first()->permissionsFromRelations;

        $this->assertEquals([71], $permissions->pluck('id')->all());
    }

    public function testWithMorphManyAndBelongsTo()
    {
        $users = Post::first()->usersFromRelations;

        $this->assertEquals([11], $users->pluck('id')->all());
    }

    public function testWithMorphToMany()
    {
        $tags = User::first()->tagsFromRelations;

        $this->assertEquals([91], $tags->pluck('id')->all());
    }

    public function testWithMorphedByMany()
    {
        $comments = Tag::first()->commentsFromRelations;

        $this->assertEquals([31], $comments->pluck('id')->all());
    }

    public function testWithHasManyDeepWithPivot()
    {
        $permissions = Country::find(1)->permissionsFromRelations;

        $this->assertEquals([71], $permissions->pluck('id')->all());
    }

    public function testWithHasManyDeepWithPivotAlias()
    {
        $permissions = Country::find(1)->permissionsWithPivotAliasFromRelations;

        $this->assertEquals([71], $permissions->pluck('id')->all());
    }

    public function testWithAlias()
    {
        $comments = Post::find(24)->commentRepliesFromRelations;

        $this->assertEquals([35, 36], $comments->pluck('id')->all());
    }

    public function testWithCustomRelatedTable()
    {
        DB::schema()->rename('comments', 'my_comments');

        $comments = Country::find(1)->commentsFromRelationsWithCustomRelatedTable;

        $this->assertEquals([31, 32], $comments->pluck('id')->all());
    }

    public function testWithCustomThroughTable()
    {
        DB::schema()->rename('users', 'my_users');

        $comments = Country::find(1)->commentsFromRelationsWithCustomThroughTable;

        $this->assertEquals([31, 32], $comments->pluck('id')->all());
    }

    public function testWithConstraints()
    {
        $comments = Country::find(1)->commentsFromRelationsWithConstraints;

        $this->assertEquals([31], $comments->pluck('id')->all());
    }

    public function testWithTrashedFinalRelatedModel()
    {
        $comments = Country::find(1)->commentsFromRelationsWithTrashedFinalRelatedModel;

        $this->assertEquals([31, 32, 37], $comments->pluck('id')->all());
    }

    public function testWithTrashedIntermediateRelatedModel()
    {
        $comments = Country::find(1)->commentsFromRelationsWithTrashedIntermediateRelatedModel;

        $this->assertEquals([31, 32, 33], $comments->pluck('id')->all());
    }

    public function testWithTrashedParents()
    {
        $comments = Country::find(1)->commentsFromRelationsWithTrashedParents;

        $this->assertEquals([31, 32, 33], $comments->pluck('id')->all());
    }

    public function testWithTrashedIntermediateDeepModel()
    {
        $comments = Country::find(1)->commentsFromRelationsWithTrashedIntermediateDeepModel;

        $this->assertEquals([31, 32, 33], $comments->pluck('id')->all());
    }

    public function testHasOneDeep()
    {
        $comment = Country::find(1)->commentFromRelations;

        $this->assertEquals(31, $comment->id);
    }

    public function testHasOneDeepWithConstraints()
    {
        $comment = Country::find(1)->commentFromRelationsWithConstraints;

        $this->assertEquals(31, $comment->id);
    }
}
