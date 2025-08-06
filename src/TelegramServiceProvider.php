<?php

namespace RMS\Telegram;

use Illuminate\Support\ServiceProvider;
use Telegram\Bot\BotsManager;

class TelegramServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/telegram.php' => config_path('telegram.php'),
        ], 'telegram-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \RMS\Telegram\Console\Commands\TestTelegram::class,
                \RMS\Telegram\Console\Commands\CreateTestFiles::class,
            ]);
        }
    }

    public function register()
    {

        $this->mergeConfigFrom(__DIR__.'/../config/telegram.php', 'telegram');


        $this->app->singleton('rms.telegram', function ($app) {
            $telegram = new \RMS\Telegram\Telegram();
            return $telegram;
        });


        $this->app->singleton(BotsManager::class, function ($app) {
            return new BotsManager(config('telegram'));
        });

        $this->app->alias('rms.telegram', \RMS\Telegram\Telegram::class);
    }
}
