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
    protected function setUp(): void
    {
        parent::setUp();

        $config = require __DIR__.'/config/database.php';

        $db = new DB();
        $db->addConnection($config[getenv('DATABASE') ?: 'sqlite']);
        $db->setAsGlobal();
        $db->bootEloquent();

        $this->migrate();

        $this->seed();
    }

    /**
     * Migrate the database.
     *
     * @return void
     */
    protected function migrate(): void
    {
        DB::schema()->dropAllTables();

        DB::schema()->create('countries', function (Blueprint $table) {
            $table->increments('id');
        });

        DB::schema()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('country_id');
            $table->unsignedInteger('team_id');
            $table->softDeletes();
        });

        DB::schema()->create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
        });

        DB::schema()->create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('post_id');
            $table->unsignedInteger('parent_id')->nullable();
        });

        DB::schema()->create('clubs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
        });

        DB::schema()->create('teams', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('club_id');
        });

        DB::schema()->create('roles', function (Blueprint $table) {
            $table->increments('id');
        });

        DB::schema()->create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('role_id');
        });

        DB::schema()->create('role_user', function (Blueprint $table) {
            $table->unsignedInteger('role_id');
            $table->unsignedInteger('user_id');
        });

        DB::schema()->create('likes', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('likeable');
            $table->unsignedInteger('user_id');
        });

        DB::schema()->create('tags', function (Blueprint $table) {
            $table->increments('id');
        });

        DB::schema()->create('taggables', function (Blueprint $table) {
            $table->unsignedInteger('tag_id');
            $table->morphs('taggable');
        });
    }

    /**
     * Seed the database.
     *
     * @return void
     */
    protected function seed(): void
    {
        Model::unguard();

        Country::create(['id' => 1]);
        Country::create(['id' => 2]);
        Country::create(['id' => 3]);

        User::create(['id' => 11, 'country_id' => 1, 'team_id' => 51, 'deleted_at' => null]);
        User::create(['id' => 12, 'country_id' => 1, 'team_id' => 51, 'deleted_at' => null]);
        User::create(['id' => 13, 'country_id' => 1, 'team_id' => 52, 'deleted_at' => Carbon::yesterday()]);
        User::create(['id' => 14, 'country_id' => 2, 'team_id' => 53, 'deleted_at' => null]);

        Post::create(['id' => 21, 'user_id' => 11]);
        Post::create(['id' => 22, 'user_id' => 12]);
        Post::create(['id' => 23, 'user_id' => 13]);
        Post::create(['id' => 24, 'user_id' => 14]);

        Comment::create(['id' => 31, 'post_id' => 21, 'parent_id' => null]);
        Comment::create(['id' => 32, 'post_id' => 22, 'parent_id' => null]);
        Comment::create(['id' => 33, 'post_id' => 23, 'parent_id' => null]);
        Comment::create(['id' => 34, 'post_id' => 24, 'parent_id' => null]);
        Comment::create(['id' => 35, 'post_id' => 24, 'parent_id' => 34]);

        Club::create(['id' => 41, 'user_id' => 11]);
        Club::create(['id' => 42, 'user_id' => 12]);
        Club::create(['id' => 43, 'user_id' => 13]);

        Team::create(['id' => 51, 'club_id' => 41]);
        Team::create(['id' => 52, 'club_id' => 42]);
        Team::create(['id' => 53, 'club_id' => 43]);

        Role::create(['id' => 61]);

        Permission::create(['id' => 71, 'role_id' => 61]);

        DB::table('role_user')->insert([
            ['role_id' => 61, 'user_id' => 11]
        ]);

        Like::create(['id' => 81, 'likeable_type' => Post::class, 'likeable_id' => 21, 'user_id' => 11]);
        Like::create(['id' => 82, 'likeable_type' => Post::class, 'likeable_id' => 23, 'user_id' => 12]);
        Like::create(['id' => 83, 'likeable_type' => Comment::class, 'likeable_id' => 31, 'user_id' => 11]);
        Like::create(['id' => 84, 'likeable_type' => Comment::class, 'likeable_id' => 32, 'user_id' => 12]);

        Tag::create(['id' => 91]);
        Tag::create(['id' => 92]);

        DB::table('taggables')->insert([
            ['tag_id' => 91, 'taggable_type' => Post::class, 'taggable_id' => 21],
            ['tag_id' => 92, 'taggable_type' => Comment::class, 'taggable_id' => 32]
        ]);

        Model::reguard();
    }
}
