<?php

namespace Tests\Concatenation\EloquentJsonRelations\BelongsToJson;

use Tests\Concatenation\EloquentJsonRelations\Models\User;
use Tests\Concatenation\EloquentJsonRelations\TestCase;

class FirstPositionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if ($this->connection === 'sqlite') {
            $this->markTestSkipped();
        }
    }

    public function testLazyLoading(): void
    {
        $permissions = User::find(21)->permissions;

        $this->assertEquals([81, 82, 83], $permissions->pluck('id')->all());
    }

    public function testLazyLoadingWithObjects(): void
    {
        $permissions = User::find(21)->permissions2;

        $this->assertEquals([81, 82, 83], $permissions->pluck('id')->all());
    }
}
