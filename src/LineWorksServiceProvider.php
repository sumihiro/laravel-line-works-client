<?php

namespace Sumihiro\LineWorksClient;

use Illuminate\Support\ServiceProvider;
use Sumihiro\LineWorksClient\Facades\LineWorks;

class LineWorksServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/lineworks.php', 'lineworks'
        );

        $this->app->singleton('lineworks', function ($app) {
            return new LineWorksManager($app, $app['config']['lineworks']);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/lineworks.php' => config_path('lineworks.php'),
            ], 'lineworks-config');
        }
    }
} 