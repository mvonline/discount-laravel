<?php

namespace Coupone\DiscountManager\Providers;

use Illuminate\Support\ServiceProvider;

class SwaggerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/l5-swagger.php', 'l5-swagger'
        );
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/l5-swagger.php' => config_path('l5-swagger.php'),
            ], 'discount-manager-config');

            $this->publishes([
                __DIR__ . '/../../resources/views/vendor/l5-swagger' => resource_path('views/vendor/l5-swagger'),
            ], 'discount-manager-views');
        }
    }
} 