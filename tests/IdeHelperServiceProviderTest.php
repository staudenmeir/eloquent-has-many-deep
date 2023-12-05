<?php

declare(strict_types=1);

namespace Tests;

use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider as BarryvdhIdeHelperServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase;
use Staudenmeir\EloquentHasManyDeep\IdeHelperServiceProvider;
use Staudenmeir\EloquentHasManyDeep\IdeHelper\DeepRelationsHook;

class IdeHelperServiceProviderTest extends TestCase
{
    public function testAutoRegistrationOfModelHook(): void
    {
        $this->app->loadDeferredProvider(BarryvdhIdeHelperServiceProvider::class);
        $this->app->loadDeferredProvider(IdeHelperServiceProvider::class);

        /** @var \Illuminate\Contracts\Config\Repository $config */
        $config = $this->app->get('config');

        $this->assertContains(
            DeepRelationsHook::class,
            $config->get('ide-helper.model_hooks'),
        );
    }

    /**
     * @define-env usesIdeHelperDisabledInConfig
     */
    public function testDisabledRegistrationOfModelHookFromConfig(): void
    {
        $this->app->loadDeferredProvider(BarryvdhIdeHelperServiceProvider::class);
        $this->app->loadDeferredProvider(IdeHelperServiceProvider::class);

        /** @var \Illuminate\Contracts\Config\Repository $config */
        $config = $this->app->get('config');

        $this->assertNotContains(
            DeepRelationsHook::class,
            $config->get('ide-helper.model_hooks'),
        );
    }

    protected function usesIdeHelperDisabledInConfig(Application $app): void
    {
        $app['config']->set('eloquent-has-many-deep.ide_helper_enabled', false);
    }

    protected function getPackageProviders($app): array
    {
        return [
            BarryvdhIdeHelperServiceProvider::class,
            IdeHelperServiceProvider::class,
        ];
    }
}
