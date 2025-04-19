<?php

namespace Coupone\DiscountManager;

use Illuminate\Support\ServiceProvider;
use Coupone\DiscountManager\Services\DiscountCalculator;
use Coupone\DiscountManager\Contracts\DiscountCalculatorInterface;
use Coupone\DiscountManager\Providers\SwaggerServiceProvider;
use Coupone\DiscountManager\Console\Commands\GenerateSwaggerDocs;

class DiscountManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/discount-manager.php', 'discount-manager'
        );

        $this->app->bind(DiscountCalculatorInterface::class, DiscountCalculator::class);
        $this->app->register(SwaggerServiceProvider::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateSwaggerDocs::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/discount-manager.php' => config_path('discount-manager.php'),
            ], 'discount-manager-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'discount-manager-migrations');

            $this->publishes([
                __DIR__ . '/../config/l5-swagger.php' => config_path('l5-swagger.php'),
            ], 'discount-manager-config');

            $this->publishes([
                __DIR__ . '/../resources/views/vendor/l5-swagger' => resource_path('views/vendor/l5-swagger'),
            ], 'discount-manager-views');
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
} 