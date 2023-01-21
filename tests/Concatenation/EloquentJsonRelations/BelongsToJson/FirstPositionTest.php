<?php

namespace Tests\Concatenation\EloquentJsonRelations\BelongsToJson;

use Illuminate\Database\Capsule\Manager as DB;
use Tests\Concatenation\EloquentJsonRelations\Models\User;
use Tests\Concatenation\EloquentJsonRelations\TestCase;

class FirstPositionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDriverName() === 'sqlite') {
            $this->markTestSkipped();
        }
    }

    public function testLazyLoading()
    {
        $permissions = User::find(21)->permissions;

        $this->assertEquals([81, 82, 83], $permissions->pluck('id')->all());
    }

    public function testLazyLoadingWithObjects()
    {
        $permissions = User::find(21)->permissions2;

        $this->assertEquals([81, 82, 83], $permissions->pluck('id')->all());
    }
}
