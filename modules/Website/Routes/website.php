<?php

use Modules\Website\Models\Page;
use Modules\Website\Models\Website;

$availableLocales = array_keys(config('app.locales'));

//Route::get('/{locale?}', function (string $locale = null) use ($availableLocales) {
//
//    $domain = Request::getHost();
//
//    $website = Website::where('url', $domain)->firstOrFail();
//
//    $locale = $locale ?? config('app.locale');
//    if (! in_array($locale, $availableLocales)) {
//        abort(404);
//    }
//
//    app()->setLocale($locale);
//
//    $page = Page::where('website_id', $website->id)
//        ->where('url', '/')
//        ->firstOrFail();
//
//    $viewPath = 'websites.website_' . $website->slug . '.pages.' . $page->path;
//    return view($viewPath);
//})->where('locale', implode('|', $availableLocales));

Route::get('/{slug?}', function (string $slug='/') use ($availableLocales) {
    $domain = Request::getHost();

    $website = Website::where('url', $domain)->firstOrFail();

    if ($slug === '/') $prefix = ''; else $prefix = '/';
    $page = Page::where('website_id', $website->id)
        ->where('url', $prefix . $slug)
        ->firstOrFail();

    $viewPath = 'websites.website_' . $website->slug . '.pages.' . $page->path;
    return view($viewPath, compact('slug'));
})->where([
    'locale' => implode('|', $availableLocales),
    'slug'   => '.*'
]);


