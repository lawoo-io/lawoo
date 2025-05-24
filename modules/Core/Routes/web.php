<?php

use Illuminate\Support\Facades\Route;

// Get default and available languages
$defaultLocale = config('app.locale');
$supportedLocales = array_keys(config('app.locales'));

// Remove the default language prefix from the url
$localesWithoutDefault = array_diff($supportedLocales, [$defaultLocale]);
$localeRegex = implode('|', $localesWithoutDefault);

/*
 * |------------------------------------------------------------------
 * | Group for multilingual routes with an optional prefix
 * |------------------------------------------------------------------
 */

/*
 * Routes for default language
 */
Route::group([
    'as' => 'locale.',
], function() use($localeRegex) {
    App::setLocale($localeRegex);
    Route::get('core-test', function () {
        return Lang::get('core::messages.welcome');
    })->name('core.test');

//    Route::get('lawoo', function () {
//        return view('web.index');
//    })->name('lawoo');
});

/*
 * Route for change the current language
 */
Route::get('/change-language/{locale}', function ($locale) {
    $supportedLocales = array_keys(config('app.locales'));
    if (in_array($locale, $supportedLocales)) {
        App::setLocale($locale);
        Session::put('locale', $locale);
    }
    return redirect()->back();
})->name('change.language');
