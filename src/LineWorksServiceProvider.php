<?php

namespace Sumihiro\LineWorksClient;

use Illuminate\Support\ServiceProvider;
use Sumihiro\LineWorksClient\Contracts\Bot\BotClientFactoryInterface;
use Sumihiro\LineWorksClient\Facades\LineWorks;
use Sumihiro\LineWorksClient\Factories\BotClientFactory;

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

        $this->app->bind(BotClientFactoryInterface::class, BotClientFactory::class);

        $this->app->singleton('lineworks', function ($app) {
            $config = $app['config']['lineworks'];
            $factory = $app->make(BotClientFactoryInterface::class);
            return new LineWorksManager($app, $config, $factory);
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