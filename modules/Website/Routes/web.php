<?php

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

Route::group([
'prefix' => '{locale?}',
'as' => 'locale.',
'where' => ['locale' => $localeRegex],
], function() {
    Route::get('website', function () {
        return Lang::get('core::messages.welcome');
    })->name('website');
});

/*
* Routes for default language
*/
Route::group([
'as' => 'locale.',
], function() {
    Route::get('website', function () {
    return Lang::get('core::messages.welcome');
    })->name('website');
});
