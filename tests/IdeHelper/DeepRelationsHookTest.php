<?php

namespace Tests\IdeHelper;

use Barryvdh\LaravelIdeHelper\Console\ModelsCommand;
use Illuminate\Database\Capsule\Manager as DB;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Staudenmeir\EloquentHasManyDeep\IdeHelper\DeepRelationsHook;
use Tests\IdeHelper\Models\User;

class DeepRelationsHookTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    protected function setUp(): void
    {
        parent::setUp();

        $config = require __DIR__.'/../config/database.php';

        $db = new DB();
        $db->addConnection($config[getenv('DATABASE') ?: 'sqlite']);
        $db->setAsGlobal();
        $db->bootEloquent();
    }

    public function testRun()
    {
        $command = Mockery::mock(ModelsCommand::class);
        $command->shouldReceive('setProperty')->once()->with(
            'comment',
            '\Tests\IdeHelper\Models\Comment',
            true,
            false,
            '',
            true
        );
        $command->shouldReceive('setProperty')->once()->with(
            'comments',
            '\Illuminate\Database\Eloquent\Collection|\Tests\IdeHelper\Models\Comment[]',
            true,
            false,
            '',
            false
        );
        $command->shouldReceive('setProperty')->once()->with(
            'comments_count',
            'int',
            true,
            false,
            null,
            true
        );

        $hook = new DeepRelationsHook();
        $hook->run($command, new User());
    }
}
