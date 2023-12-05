<?php

namespace Staudenmeir\EloquentHasManyDeep;

use Barryvdh\LaravelIdeHelper\Console\ModelsCommand;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Staudenmeir\EloquentHasManyDeep\IdeHelper\DeepRelationsHook;

class IdeHelperServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function boot(): void
    {
        $this->publishConfig();
    }

    public function register(): void
    {
        $this->registerConfig();

        $this->registerIdeHelperHook();
    }

    public function provides(): array
    {
        return [
            ModelsCommand::class,
        ];
    }

    protected function registerIdeHelperHook(): void
    {
        /** @var \Illuminate\Contracts\Config\Repository $config */
        $config = $this->app->get('config');

        if (!$config->get('eloquent-has-many-deep.ide_helper_enabled')) {
            return;
        }

        $config->set(
            'ide-helper.model_hooks',
            array_merge(
                [DeepRelationsHook::class],
                $config->get('ide-helper.model_hooks', [])
            )
        );
    }

    protected function publishConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../config/eloquent-has-many-deep.php' => config_path('eloquent-has-many-deep.php'),
        ], 'eloquent-has-many-deep');
    }

    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/eloquent-has-many-deep.php',
            'eloquent-has-many-deep',
        );
    }
}
