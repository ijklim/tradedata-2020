# Stock data collection

An app to collect stock trade data from various api sources.

## Technologies used

* Laravel 8.20.1
* Vue.js
* SQLite

## Installation

* Clone repository
* Run ```yarn install```
* Run ```composer install```
* If using sqlite (setting in `.env`), manually create a blank file `database\database.sqlite`
* Run ```php artisan migrate```
* Run ```yarn run dev``` to compile js and css


## Troubleshoot

### Error "Class 'Doctrine\DBAL\Driver\AbstractSQLiteDriver' not found"

* Solution: Run `composer require doctrine/dbal`, renaming column requires this library

### Error "Target class [*Controller] does not exist."

* Reference: https://medium.com/@litvinjuan/how-to-fix-target-class-does-not-exist-in-laravel-8-f9e28b79f8b4

* Use full namespace in `routes\web.php` and `routes\api.php`, e.g. `Route::get('/', 'App\Http\Controllers\StockController@index');` instead of `Route::get('/', 'StockController@index');`

### Laravel 8 control structure, e.g. IF directive

* Reference: https://www.youtube.com/watch?v=wFvyjwRPaUE

### Form class is not found

* Run `composer require laravelcollective/html`

### Method bootstrapText does not exist

* Must define Form component in `app\Providers\AppServiceProvider.php`, e.g. `\Form::component('bootstrapText', 'components.form.text', ['name', 'value', 'attributes']);`

* Corresponding file should be in `resources\views\components\form` e.g. `text.blade.php`