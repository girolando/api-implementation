<?php

namespace Andersonef\ApiImplementation\Providers;

use Andersonef\ApiImplementation\Services\ApiAuthService;
use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Route::controller('/api-layer', \Andersonef\ApiImplementation\Http\Controllers\ApiLayer::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /*$this->app->singleton('\Andersonef\ApiImplementation\Services\ApiAuthService', function(){
            return app('\Andersonef\ApiImplementation\Services\ApiAuthService');
        });*/

    }
}
