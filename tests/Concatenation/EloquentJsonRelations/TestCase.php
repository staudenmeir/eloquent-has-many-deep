<?php

namespace Tests\Concatenation\EloquentJsonRelations;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase as Base;
use Tests\Concatenation\EloquentJsonRelations\Models\Permission;
use Tests\Concatenation\EloquentJsonRelations\Models\Project;
use Tests\Concatenation\EloquentJsonRelations\Models\Role;
use Tests\Concatenation\EloquentJsonRelations\Models\User;

abstract class TestCase extends Base
{
    protected string $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = getenv('DB_CONNECTION') ?: 'sqlite';

        if ($this->connection !== 'mysql') {
            $this->markTestSkipped();
        }

        $config = require __DIR__.'/../../config/database.php';

        $db = new DB();
        $db->addConnection($config[$this->connection]);
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

        DB::schema()->create('roles', function (Blueprint $table) {
            $table->id();
        });

        DB::schema()->create('users', function (Blueprint $table) {
            $table->unsignedInteger('id');
            $table->json('options');
        });

        DB::schema()->create('permissions', function (Blueprint $table) {
            $table->unsignedInteger('id');
            $table->unsignedInteger('role_id');
        });

        DB::schema()->create('projects', function (Blueprint $table) {
            $table->unsignedInteger('id');
            $table->unsignedInteger('user_id');
        });
    }

    protected function seed(): void
    {
        Model::unguard();

        Role::create();
        Role::create();
        Role::create();
        Role::create();

        User::create([
            'id' => 21,
            'options' => [
                'role_ids' => [1, 2],
                'roles' => [
                    ['role' => ['id' => 1, 'active' => true]],
                    ['role' => ['id' => 2, 'active' => false]],
                    ['foo' => 'bar'],
                ],
            ],
        ]);
        User::create(['id' => 22, 'options' => []]);
        User::create([
            'id' => 23,
            'options' => [
                'role_ids' => [2, 3],
                'roles' => [
                    ['role' => ['id' => 2, 'active' => true]],
                    ['role' => ['id' => 3, 'active' => false]],
                ],
            ],
        ]);

        Permission::create(['id' => 81, 'role_id' => 1]);
        Permission::create(['id' => 82, 'role_id' => 1]);
        Permission::create(['id' => 83, 'role_id' => 2]);
        Permission::create(['id' => 84, 'role_id' => 3]);
        Permission::create(['id' => 85, 'role_id' => 4]);

        Project::create([
             'id' => 71,
             'user_id' => 21,
         ]);
        Project::create([
             'id' => 72,
             'user_id' => 22,
         ]);
        Project::create([
             'id' => 73,
             'user_id' => 23,
         ]);

        Model::reguard();
    }
}
