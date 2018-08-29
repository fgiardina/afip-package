<?php

namespace fernandogiardina\afip;

use Illuminate\Support\ServiceProvider;

class AfipServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/routes.php');
        $this->mergeConfigFrom(
            __DIR__.'/config/afip.php', 'afip'
        );
        $this->publishes([
            __DIR__.'/config/afip.php' => config_path('afip.php'),
        ]);
    }

    public function register()
    {
        
    }
}