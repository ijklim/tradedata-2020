<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Ref: https://scotch.io/tutorials/all-about-writing-custom-blade-directives
        // Note: Must run `php artisan view:clear` after creating a new directive
        \Illuminate\Support\Facades\Blade::if('debug', function () {
            return config('app.debug');
        });
    }
}
