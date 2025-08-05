<?php

namespace RMS\Telegram;

use Illuminate\Support\ServiceProvider;

class TelegramServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/telegram.php' => config_path('telegram.php'),
        ], 'telegram-config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/telegram.php', 'telegram');

        $this->app->singleton('telegram', function ($app) {
            return new Telegram();
        });
    }
}
