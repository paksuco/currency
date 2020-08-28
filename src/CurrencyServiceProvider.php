<?php

namespace Paksuco\Currency;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Bind any implementations.
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function handleConfigs()
    {
        $configPath = __DIR__ . '/../config/currencies.php';

        $this->publishes([$configPath => base_path('config/currencies.php')]);

        $this->mergeConfigFrom($configPath, 'currencies');
    }

    private function handleTranslations()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'currency-ui');
    }

    private function handleViews()
    {
        $this->loadViewsFrom(__DIR__ . '/../views', 'paksuco-currency');

        $this->publishes([__DIR__ . '/../views' => base_path('resources/views/vendor/paksuco-currency')]);

        Livewire::component("paksuco-currency::currencies", Components\Currencies::class);
        Livewire::component("paksuco-currency::currency", Components\Currency::class);
    }

    private function handleMigrations()
    {
        $this->publishes([__DIR__ . '/../migrations' => base_path('database/migrations')]);
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
