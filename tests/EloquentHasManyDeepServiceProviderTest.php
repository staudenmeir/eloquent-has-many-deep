<?php

declare(strict_types=1);

namespace Tests;

use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Orchestra\Testbench\TestCase;
use Staudenmeir\EloquentHasManyDeep\IdeHelper\DeepRelationsHook;
use Staudenmeir\EloquentHasManyDeep\EloquentHasManyDeepServiceProvider;

class EloquentHasManyDeepServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            IdeHelperServiceProvider::class,
            EloquentHasManyDeepServiceProvider::class,
        ];
    }

    public function testAutoRegistrationOfModelHook(): void
    {
        $this->app->loadDeferredProvider(IdeHelperServiceProvider::class);
        $this->app->loadDeferredProvider(EloquentHasManyDeepServiceProvider::class);

        /** @var \Illuminate\Contracts\Config\Repository $config */
        $config = $this->app->get('config');

        $this->assertContains(
            DeepRelationsHook::class,
            $config->get('ide-helper.model_hooks'),
        );
    }

    /**
     * @test
     * @define-env usesIdeHelperDisabledInConfig
     */
    public function testDisabledRegistrationOfModelHookFromConfig(): void
    {
        $this->app->loadDeferredProvider(IdeHelperServiceProvider::class);
        $this->app->loadDeferredProvider(EloquentHasManyDeepServiceProvider::class);

        /** @var \Illuminate\Contracts\Config\Repository $config */
        $config = $this->app->get('config');

        $this->assertNotContains(
            DeepRelationsHook::class,
            $config->get('ide-helper.model_hooks'),
        );
    }

    protected function usesIdeHelperDisabledInConfig($app): void
    {
        $app['config']->set('eloquent-has-many-deep.ide_helper_enabled', false);
    }
}
