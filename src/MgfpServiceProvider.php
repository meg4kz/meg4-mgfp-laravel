<?php

namespace Meg4\Mgfp;

use Illuminate\Support\ServiceProvider;
use Meg4\Mgfp\Console\DemoCommand;
use Meg4\Mgfp\Console\DoctorCommand;
use Meg4\Mgfp\Console\InstallCommand;
use Meg4\Mgfp\Contracts\StoreContext;
use Meg4\Mgfp\Contracts\StoreRepository;
use Meg4\Mgfp\Support\EloquentAutoStore;
use Meg4\Mgfp\Support\FakeStore;
use Meg4\Mgfp\Support\TokenStoreContext;

/**
 * Service provider del SDK MGFP — plug-and-play.
 *
 * - Mergea `config/mgfp.php` y bindea el {@see StoreRepository} desde
 *   `config('mgfp.store')` (default: {@see EloquentAutoStore}; `'demo'` =
 *   {@see FakeStore}).
 * - Registra los comandos `mgfp:install` / `mgfp:doctor` / `mgfp:demo`.
 * - Publica config y rutas de ejemplo.
 */
class MgfpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/mgfp.php', 'mgfp');

        $this->app->bind(StoreContext::class, TokenStoreContext::class);
        $this->app->bind(StoreRepository::class, function ($app) {
            $store = (string) config('mgfp.store', EloquentAutoStore::class);
            if ($store === 'demo' || $store === FakeStore::class) {
                return new FakeStore();
            }
            return $app->make($store);
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/mgfp.php' => config_path('mgfp.php'),
        ], 'mgfp-config');

        $this->publishes([
            __DIR__ . '/../routes/ai.php' => base_path('routes/ai.php'),
        ], 'mgfp-routes');

        if ($this->app->runningInConsole()) {
            $this->commands([InstallCommand::class, DoctorCommand::class, DemoCommand::class]);
        }
    }
}
