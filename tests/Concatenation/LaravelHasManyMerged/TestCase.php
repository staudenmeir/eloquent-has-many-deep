<?php

namespace Tests\Concatenation\LaravelHasManyMerged;

use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase as Base;
use Tests\Concatenation\LaravelAdjacencyList\Models\Post;
use Tests\Concatenation\LaravelAdjacencyList\Models\User;
use Tests\Concatenation\LaravelHasManyMerged\Models\Attachment;
use Tests\Concatenation\LaravelHasManyMerged\Models\Country;
use Tests\Concatenation\LaravelHasManyMerged\Models\Message;

abstract class TestCase extends Base
{
    protected string $database;

    protected function setUp(): void
    {
        parent::setUp();

        $this->database = getenv('DATABASE') ?: 'sqlite';

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
            'countries',
            function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            }
        );

        DB::schema()->create(
            'users',
            function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('country_id');
                $table->timestamps();
            }
        );

        DB::schema()->create(
            'messages',
            function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sender_id');
                $table->unsignedBigInteger('recipient_id');
                $table->timestamps();
            }
        );

        DB::schema()->create(
            'attachments',
            function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('message_id');
                $table->timestamps();
            }
        );
    }

    protected function seed(): void
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
}
