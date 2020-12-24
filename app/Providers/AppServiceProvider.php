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
        // Define new blade directive @debug, used in `resources\views\layouts\app.blade.php`
        // Ref: https://scotch.io/tutorials/all-about-writing-custom-blade-directives
        // Note: Must run `php artisan view:clear` after creating a new directive
        \Illuminate\Support\Facades\Blade::if('debug', function () {
            return config('app.debug');
        });

        // Register Bootstrap 4 Form elements
        // 'components.form.text' refers to resources/views/components/form/text.blade.php
        // e.g. Form::bootstrapText('symbol', null, ['placeholder' => 'symbol, e.g. QQQ'])
        \Form::component('bootstrapText', 'components.form.text', ['name', 'value', 'attributes']);
        \Form::component('bootstrapSelect', 'components.form.select', ['name', 'value' => null, 'label', 'options', 'attributes' => []]);
    }
}
