<?php

declare(strict_types=1);

namespace Tests\Providers;

use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Contracts\Config\Repository as Config;
use Orchestra\Testbench\TestCase;
use Staudenmeir\EloquentHasManyDeep\IdeHelper\DeepRelationsHook;
use Staudenmeir\EloquentHasManyDeep\Providers\EloquentHasManyDeepServiceProvider;

class EloquentHasManyDeepServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            IdeHelperServiceProvider::class,
            EloquentHasManyDeepServiceProvider::class,
        ];
    }

    public function testRegistrationOfModelHook(): void
    {
        /** @var Config $config */
        $config = $this->app->get('config');

        static::assertContains(
            DeepRelationsHook::class,
            $config->get('ide-helper.model_hooks'),
        );
    }
}
