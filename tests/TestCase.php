<?php

namespace Tests;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Base;
use Tests\Models\Club;
use Tests\Models\Comment;
use Tests\Models\Country;
use Tests\Models\Employee;
use Tests\Models\Like;
use Tests\Models\Permission;
use Tests\Models\Post;
use Tests\Models\Project;
use Tests\Models\Role;
use Tests\Models\Tag;
use Tests\Models\Task;
use Tests\Models\Team;
use Tests\Models\User;
use Tests\Models\WorkStream;

abstract class TestCase extends Base
{
    protected string $database;

    protected function setUp(): void
    {
        $this->database = getenv('DATABASE') ?: 'sqlite';

        parent::setUp();

        $this->migrateDatabase();

        $this->seedDatabase();
    }

    protected function tearDown(): void
    {
        DB::connection()->disconnect();

        parent::tearDown();
    }

    protected function migrateDatabase(): void
    {
        Schema::dropAllTables();

        Schema::create('countries', function (Blueprint $table) {
            $table->id();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('country_id');
            $table->unsignedBigInteger('team_id');
            $table->softDeletes();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->boolean('published');
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->softDeletes();
        });

        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id');
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('user_id');
        });

        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->morphs('likeable');
            $table->unsignedBigInteger('user_id');
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
        });

        Schema::create('taggables', function (Blueprint $table) {
            $table->unsignedBigInteger('tag_id');
            $table->morphs('taggable');
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
        });

        Schema::create('work_streams', function (Blueprint $table) {
            $table->id();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('work_stream_id');
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('work_stream_id');
        });
    }

    protected function seedDatabase(): void
    {
        Model::unguard();

        Country::create(['id' => 1]);
        Country::create(['id' => 2]);
        Country::create(['id' => 3]);

        User::create(['id' => 11, 'country_id' => 1, 'team_id' => 51, 'deleted_at' => null]);
        User::create(['id' => 12, 'country_id' => 1, 'team_id' => 51, 'deleted_at' => null]);
        User::create(['id' => 13, 'country_id' => 1, 'team_id' => 52, 'deleted_at' => Carbon::yesterday()]);
        User::create(['id' => 14, 'country_id' => 2, 'team_id' => 53, 'deleted_at' => null]);

        Post::create(['id' => 21, 'user_id' => 11, 'published' => true]);
        Post::create(['id' => 22, 'user_id' => 12, 'published' => false]);
        Post::create(['id' => 23, 'user_id' => 13, 'published' => true]);
        Post::create(['id' => 24, 'user_id' => 14, 'published' => true]);

        Comment::create(['id' => 31, 'post_id' => 21, 'parent_id' => null, 'deleted_at' => null]);
        Comment::create(['id' => 32, 'post_id' => 22, 'parent_id' => null, 'deleted_at' => null]);
        Comment::create(['id' => 33, 'post_id' => 23, 'parent_id' => null, 'deleted_at' => null]);
        Comment::create(['id' => 34, 'post_id' => 24, 'parent_id' => null, 'deleted_at' => null]);
        Comment::create(['id' => 35, 'post_id' => 24, 'parent_id' => 34, 'deleted_at' => null]);
        Comment::create(['id' => 36, 'post_id' => 24, 'parent_id' => 35, 'deleted_at' => null]);
        Comment::create(['id' => 37, 'post_id' => 21, 'parent_id' => null, 'deleted_at' => Carbon::yesterday()]);

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

        Project::create(['id' => 101]);
        Project::create(['id' => 102]);
        Project::create(['id' => 103]);
        Project::create(['id' => 104]);

        WorkStream::create(['id' => 111]);
        WorkStream::create(['id' => 112]);
        WorkStream::create(['id' => 113]);

        Task::create(['id' => 121, 'project_id' => 101, 'team_id' => 51, 'work_stream_id' => 111]);
        Task::create(['id' => 122, 'project_id' => 102, 'team_id' => 51, 'work_stream_id' => 111]);
        Task::create(['id' => 123, 'project_id' => 103, 'team_id' => 52, 'work_stream_id' => 111]);
        Task::create(['id' => 124, 'project_id' => 104, 'team_id' => 51, 'work_stream_id' => 113]);

        Employee::create(['id' => 131, 'team_id' => 51, 'work_stream_id' => 111]);
        Employee::create(['id' => 132, 'team_id' => 51, 'work_stream_id' => 111]);
        Employee::create(['id' => 133, 'team_id' => 52, 'work_stream_id' => 111]);
        Employee::create(['id' => 134, 'team_id' => 51, 'work_stream_id' => 114]);

        Model::reguard();
    }

    protected function getEnvironmentSetUp($app)
    {
        $config = require __DIR__.'/config/database.php';

        $app['config']->set('database.default', 'testing');

        $app['config']->set('database.connections.testing', $config[$this->database]);
    }
}
