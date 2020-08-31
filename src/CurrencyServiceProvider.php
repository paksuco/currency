<?php

namespace Paksuco\Currency;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Paksuco\Currency\Facades\Currency;
use Paksuco\Settings\Facades\Settings;

class CurrencyServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->handleConfigs();
        $this->handleMigrations();
        $this->handleViews();
        $this->handleTranslations();
        $this->handleRoutes();

        Event::listen("paksuco.menu.beforeRender", function ($key, $container) {
            if ($key == "admin") {
                if ($container->hasItem("Currencies") == false) {
                    $container->addItem("Currencies", route("paksuco.currencies.admin"), "fas fa-coins");
                }
            }
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Paksuco\Currency\Commands\CurrencyUpdate::class,
            ]);
        }

        $this->app->booted(function () {
            if (Settings::get("fixer_api_key", "") != "") {
                $schedule = app(Schedule::class);
                $schedule->command('currency:update')->everyThreeHours();
            }
        });

        $this->app['router']
            ->pushMiddlewareToGroup(
                'web',
                \Paksuco\Currency\Middleware\SetUserCurrency::class
            );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind("currency", function () {
            return new \Paksuco\Currency\Services\Currency();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            \Paksuco\Currency\Services\Currency::class,
        ];
    }

    private function handleConfigs()
    {
        $configPath = __DIR__ . '/../config/currencies.php';

        $this->publishes([$configPath => base_path('config/currencies.php')], "config");

        $this->mergeConfigFrom($configPath, 'currencies');
    }

    private function handleTranslations()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'currency-ui');
    }

    private function handleViews()
    {
        $this->loadViewsFrom(__DIR__ . '/../views', 'paksuco-currency');

        $this->publishes([__DIR__ . '/../views' => base_path('resources/views/vendor/paksuco-currency')], "views");

        Livewire::component("paksuco-currency::currencies", Components\Currencies::class);
        Livewire::component("paksuco-currency::currency", Components\Currency::class);
    }

    private function handleMigrations()
    {
        $this->publishes([__DIR__ . '/../migrations' => base_path('database/migrations')], "migrations");
    }

    private function handleRoutes()
    {
        include __DIR__ . '/../routes/routes.php';
    }
}

if (!function_exists("base_path")) {
    function base_path($path)
    {
        return \Illuminate\Support\Facades\App::basePath($path);
    }
}
