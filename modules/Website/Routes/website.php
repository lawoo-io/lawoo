<?php

use Modules\Website\Models\Page;
use Modules\Website\Models\Website;

$availableLocales = array_keys(config('app.locales'));

Route::get('/{locale?}', function (string $locale = null) use ($availableLocales) {

    $domain = Request::getHost();

    $website = Website::where('url', $domain)->firstOrFail();

    $locale = $locale ?? config('app.locale');
    if (! in_array($locale, $availableLocales)) {
        abort(404);
    }

    app()->setLocale($locale);

    $page = Page::where('website_id', $website->id)
        ->where('url', '/')
        ->firstOrFail();

    $viewPath = 'websites.website_' . $website->slug . '.pages.' . $page->path;
    return view($viewPath);
})->where('locale', implode('|', $availableLocales));

Route::get('/{locale}/{slug}', function (string $locale, string $slug) use ($availableLocales) {
    $domain = Request::getHost();

    $website = Website::where('url', $domain)->firstOrFail();

    if (! in_array($locale, $availableLocales)) {
        abort(404);
    }
    app()->setLocale($locale);

    $page = Page::where('website_id', $website->id)
        ->where('url', '/' . $slug)
        ->firstOrFail();

    $viewPath = 'websites.website_' . $website->slug . '.pages.' . $page->path;
    return view($viewPath, compact('locale'));
})->where([
    'locale' => implode('|', $availableLocales),
    'slug'   => '.*'
]);


