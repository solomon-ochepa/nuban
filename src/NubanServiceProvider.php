<?php

namespace SolomonOchepa\Nuban;

use Illuminate\Support\ServiceProvider;

class NubanServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->bindNubanSingleton();

        $this->publishConfig();
    }

    public function bindNubanSingleton()
    {
        $this->app->singleton(Nuban::class, function () {
            return new Nuban;
        });
    }

    public function publishConfig()
    {
        $this->publishes([
            __DIR__.'/../config/nuban.php' => config_path('nuban.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/nuban.php', 'nuban');
    }
}
