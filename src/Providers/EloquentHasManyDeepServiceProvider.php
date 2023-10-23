<?php

namespace Staudenmeir\EloquentHasManyDeep\Providers;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\ServiceProvider;
use Staudenmeir\EloquentHasManyDeep\IdeHelper\DeepRelationsHook;

class EloquentHasManyDeepServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishConfig();
    }

    public function register(): void
    {
        $this->registerConfig();
        $this->registerIDEHelperHook();
    }

    protected function registerIDEHelperHook(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        if ($this->app->isProduction()) {
            return;
        }

        /** @var Config $config */
        $config = $this->app->get('config');

        if (! $config->get('eloquent-has-many-deep.ide_helper_enabled')) {
            return;
        }

        if (! $config->has('ide-helper.model_hooks')) {
            return;
        }

        $config->set('ide-helper.model_hooks', array_merge([
            DeepRelationsHook::class,
        ], $config->get('ide-helper.model_hooks', [])));
    }

    protected function publishConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/eloquent-has-many-deep.php' => config_path('eloquent-has-many-deep.php'),
        ], 'eloquent-has-many-deep');
    }

    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/eloquent-has-many-deep.php',
            'eloquent-has-many-deep',
        );
    }
}
