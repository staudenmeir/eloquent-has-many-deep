<?php

namespace Tests\Concatenation\LaravelHasManyMerged;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Base;
use Tests\Concatenation\LaravelAdjacencyList\Models\User;
use Tests\Concatenation\LaravelHasManyMerged\Models\Attachment;
use Tests\Concatenation\LaravelHasManyMerged\Models\Country;
use Tests\Concatenation\LaravelHasManyMerged\Models\Message;

abstract class TestCase extends Base
{
    protected string $connection;

    protected function setUp(): void
    {
        $this->connection = getenv('DB_CONNECTION') ?: 'sqlite';

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

        Schema::create(
            'countries',
            function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            }
        );

        Schema::create(
            'users',
            function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('country_id');
                $table->timestamps();
            }
        );

        Schema::create(
            'messages',
            function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sender_id');
                $table->unsignedBigInteger('recipient_id');
                $table->timestamps();
            }
        );

        Schema::create(
            'attachments',
            function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('message_id');
                $table->timestamps();
            }
        );
    }

    protected function seedDatabase(): void
    {
        Model::unguard();

        Country::create();
        Country::create();
        Country::create();
        Country::create();

        User::create(['id' => 11, 'country_id' => 1]);
        User::create(['id' => 12, 'country_id' => 1]);
        User::create(['id' => 13, 'country_id' => 2]);
        User::create(['id' => 14, 'country_id' => 3]);
        User::create(['id' => 15, 'country_id' => 3]);

        Message::create(['id' => 21, 'sender_id' => 11, 'recipient_id' => 12]);
        Message::create(['id' => 22, 'sender_id' => 12, 'recipient_id' => 13]);
        Message::create(['id' => 23, 'sender_id' => 13, 'recipient_id' => 11]);
        Message::create(['id' => 24, 'sender_id' => 13, 'recipient_id' => 13]);
        Message::create(['id' => 25, 'sender_id' => 14, 'recipient_id' => 14]);

        Attachment::create(['id' => 31, 'message_id' => 21]);
        Attachment::create(['id' => 32, 'message_id' => 22]);
        Attachment::create(['id' => 33, 'message_id' => 23]);
        Attachment::create(['id' => 34, 'message_id' => 24]);

        Model::reguard();
    }

    protected function getEnvironmentSetUp($app)
    {
        $config = require __DIR__.'/../../config/database.php';

        $app['config']->set('database.default', 'testing');

        $app['config']->set('database.connections.testing', $config[$this->connection]);
    }
}
