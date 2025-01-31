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

        // Laravel only allows a single deferred service provider to claim
        // responsibility for a given class, interface, or service in the
        // provides() method. To ensure this provider is properly loaded
        // when running the ModelsCommand we bind an alias and use that instead.
        $this->app->alias(ModelsCommand::class, DeepRelationsHook::class);
    }

    public function register(): void
    {
        $this->registerConfig();

        $this->registerIdeHelperHook();
    }

    /**
     * @return list<class-string<\Barryvdh\LaravelIdeHelper\Contracts\ModelHookInterface>>
     */
    public function provides(): array
    {
        return [
            DeepRelationsHook::class,
        ];
    }

    protected function registerIdeHelperHook(): void
    {
        /** @var \Illuminate\Config\Repository $config */
        $config = $this->app->get('config');

        if (!$config->get('eloquent-has-many-deep.ide_helper_enabled')) {
            return;
        }

        $config->set(
            'ide-helper.model_hooks',
            array_merge(
                [DeepRelationsHook::class],
                $config->array('ide-helper.model_hooks', [])
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
