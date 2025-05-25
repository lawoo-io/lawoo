<?php

use Illuminate\Support\Facades\Route;

// Get default and available languages
$defaultLocale = config('app.locale');
$supportedLocales = array_keys(config('app.locales'));

// Remove the default language prefix from the url
$localesWithoutDefault = array_diff($supportedLocales, [$defaultLocale]);
$localeRegex = implode('|', $localesWithoutDefault);

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
