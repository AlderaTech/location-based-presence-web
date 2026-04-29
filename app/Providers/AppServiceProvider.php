<?php

namespace App\Providers;

use App\Support\SesiDatabaseHandler;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Session::extend('database', function ($app) {
            $config = $app->make('config');

            return new SesiDatabaseHandler(
                $app->make('db')->connection($config->get('session.connection')),
                $config->get('session.table'),
                $config->get('session.lifetime'),
                $app,
            );
        });
    }
}
