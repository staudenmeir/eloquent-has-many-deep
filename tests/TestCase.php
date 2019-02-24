<?php

namespace Tests;

use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase as Base;
use Tests\Models\Club;
use Tests\Models\Comment;
use Tests\Models\Country;
use Tests\Models\Like;
use Tests\Models\Permission;
use Tests\Models\Post;
use Tests\Models\Role;
use Tests\Models\Tag;
use Tests\Models\Team;
use Tests\Models\User;

abstract class TestCase extends Base
{
    protected function setUp()
    {
        parent::setUp();

        $db = new DB;
        $db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $db->setAsGlobal();
        $db->bootEloquent();

        $this->migrate();

        $this->seed();

        DB::enableQueryLog();
    }

    /**
     * Migrate the database.
     *
     * @return void
     */
    protected function migrate()
    {
        DB::schema()->create('countries', function (Blueprint $table) {
            $table->increments('country_pk');
            $table->timestamps();
        });

        DB::schema()->create('users', function (Blueprint $table) {
            $table->increments('user_pk');
            $table->unsignedInteger('country_country_pk');
            $table->unsignedInteger('team_team_pk');
            $table->softDeletes();
        });

        DB::schema()->create('posts', function (Blueprint $table) {
            $table->increments('post_pk');
            $table->unsignedInteger('user_user_pk');
        });

        DB::schema()->create('comments', function (Blueprint $table) {
            $table->increments('comment_pk');
            $table->unsignedInteger('post_post_pk');
        });

        DB::schema()->create('clubs', function (Blueprint $table) {
            $table->increments('club_pk');
            $table->unsignedInteger('user_user_pk');
        });

        DB::schema()->create('teams', function (Blueprint $table) {
            $table->increments('team_pk');
            $table->unsignedInteger('club_club_pk');
        });

        DB::schema()->create('roles', function (Blueprint $table) {
            $table->increments('role_pk');
            $table->timestamps();
        });

        DB::schema()->create('permissions', function (Blueprint $table) {
            $table->increments('permission_pk');
            $table->unsignedInteger('role_role_pk');
        });

        DB::schema()->create('role_user', function (Blueprint $table) {
            $table->unsignedInteger('role_role_pk');
            $table->unsignedInteger('user_user_pk');
        });

        DB::schema()->create('likes', function (Blueprint $table) {
            $table->increments('like_pk');
            $table->morphs('likeable');
            $table->unsignedInteger('user_user_pk');
        });

        DB::schema()->create('tags', function (Blueprint $table) {
            $table->increments('tag_pk');
            $table->timestamps();
        });

        DB::schema()->create('taggables', function (Blueprint $table) {
            $table->unsignedInteger('tag_tag_pk');
            $table->morphs('taggable');
        });
    }

    /**
     * Seed the database.
     *
     * @return void
     */
    protected function seed()
    {
        Model::unguard();

        Country::create();
        Country::create();

        User::create(['country_country_pk' => 1, 'team_team_pk' => 1, 'deleted_at' => null]);
        User::create(['country_country_pk' => 1, 'team_team_pk' => 1, 'deleted_at' => null]);
        User::create(['country_country_pk' => 1, 'team_team_pk' => 2, 'deleted_at' => Carbon::yesterday()]);

        Post::create(['user_user_pk' => 1]);
        Post::create(['user_user_pk' => 2]);
        Post::create(['user_user_pk' => 3]);

        Comment::create(['post_post_pk' => 1]);
        Comment::create(['post_post_pk' => 2]);
        Comment::create(['post_post_pk' => 3]);

        Club::create(['user_user_pk' => 1]);
        Club::create(['user_user_pk' => 2]);
        Club::create(['user_user_pk' => 3]);

        Team::create(['club_club_pk' => 1]);
        Team::create(['club_club_pk' => 2]);
        Team::create(['club_club_pk' => 3]);

        Role::create();

        Permission::create(['role_role_pk' => 1]);

        DB::table('role_user')->insert([
            ['role_role_pk' => 1, 'user_user_pk' => 1]
        ]);

        Like::create(['likeable_type' => Post::class, 'likeable_id' => 1, 'user_user_pk' => 1]);
        Like::create(['likeable_type' => Post::class, 'likeable_id' => 3, 'user_user_pk' => 2]);
        Like::create(['likeable_type' => Comment::class, 'likeable_id' => 1, 'user_user_pk' => 1]);
        Like::create(['likeable_type' => Comment::class, 'likeable_id' => 2, 'user_user_pk' => 2]);

        Tag::create();
        Tag::create();

        DB::table('taggables')->insert([
            ['tag_tag_pk' => 1, 'taggable_type' => Post::class, 'taggable_id' => 1],
            ['tag_tag_pk' => 2, 'taggable_type' => Comment::class, 'taggable_id' => 2]
        ]);

        Model::reguard();
    }
}
