<?php

namespace Tests\Concatenation\LaravelAdjacencyList;

use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase as Base;
use Tests\Concatenation\LaravelAdjacencyList\Models\Post;
use Tests\Concatenation\LaravelAdjacencyList\Models\User;

abstract class TestCase extends Base
{
    protected string $database;

    protected function setUp(): void
    {
        parent::setUp();

        $this->database = getenv('DB_CONNECTION') ?: 'sqlite';

        if ($this->database === 'mysql') {
            $this->markTestSkipped();
        }

        $config = require __DIR__.'/../../config/database.php';

        $db = new DB();
        $db->addConnection($config[$this->database]);
        $db->setAsGlobal();
        $db->bootEloquent();

        $this->migrate();

        $this->seed();
    }

    protected function tearDown(): void
    {
        DB::connection()->disconnect();

        parent::tearDown();
    }

    protected function migrate(): void
    {
        DB::schema()->dropAllTables();

        DB::schema()->create(
            'users',
            function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('parent_id')->nullable();
                $table->timestamps();
                $table->softDeletes();
            }
        );

        DB::schema()->create(
            'posts',
            function (Blueprint $table) {
                $table->unsignedInteger('id');
                $table->unsignedInteger('user_id');
                $table->timestamps();
                $table->softDeletes();
            }
        );
    }

    protected function seed(): void
    {
        Model::unguard();

        User::create(['parent_id' => null, 'deleted_at' => null]);
        User::create(['parent_id' => 1, 'deleted_at' => null]);
        User::create(['parent_id' => 1, 'deleted_at' => null]);
        User::create(['parent_id' => 1, 'deleted_at' => null]);
        User::create(['parent_id' => 2, 'deleted_at' => null]);
        User::create(['parent_id' => 3, 'deleted_at' => null]);
        User::create(['parent_id' => 4, 'deleted_at' => null]);
        User::create(['parent_id' => 5, 'deleted_at' => null]);
        User::create(['parent_id' => 6, 'deleted_at' => null]);
        User::create(['parent_id' => 7, 'deleted_at' => Carbon::now()]);
        User::create(['parent_id' => null, 'deleted_at' => null]);
        User::create(['parent_id' => 11, 'deleted_at' => null]);

        Post::create(['id' => 10, 'user_id' => 1, 'deleted_at' => null]);
        Post::create(['id' => 20, 'user_id' => 2, 'deleted_at' => null]);
        Post::create(['id' => 30, 'user_id' => 3, 'deleted_at' => null]);
        Post::create(['id' => 40, 'user_id' => 4, 'deleted_at' => null]);
        Post::create(['id' => 50, 'user_id' => 5, 'deleted_at' => null]);
        Post::create(['id' => 60, 'user_id' => 6, 'deleted_at' => null]);
        Post::create(['id' => 70, 'user_id' => 7, 'deleted_at' => null]);
        Post::create(['id' => 80, 'user_id' => 8, 'deleted_at' => null]);
        Post::create(['id' => 90, 'user_id' => 10, 'deleted_at' => null]);
        Post::create(['id' => 100, 'user_id' => 12, 'deleted_at' => null]);
        Post::create(['id' => 110, 'user_id' => 12, 'deleted_at' => null]);
        Post::create(['id' => 120, 'user_id' => 12, 'deleted_at' => Carbon::now()]);

        Model::reguard();
    }
}
