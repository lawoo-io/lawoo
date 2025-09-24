<?php

use Modules\Website\Models\Page;
use Modules\Website\Models\Website;

$availableLocales = array_keys(config('app.locales'));

Route::get('/{slug?}', function (string $slug = '/') use ($availableLocales) {
    $domain = Request::getHost();

    $website = Website::where('url', $domain)->firstOrFail();

    // Basis-Parameter (immer verfügbar)
    $baseParams = [
        'website_id' => $website->id,
        'company_id' => $website->company_id,
    ];

    if ($slug === '/') {
        $prefix = '';
    } else {
        $prefix = '/';
    }

    $page = Page::where('website_id', $website->id)
        ->where('url', $prefix . $slug)
        ->first();

    if (! $page) {
        $pages = Page::where('website_id', $website->id)->get();
        foreach ($pages as $page) {
            $url = trim($page->url, '/'); // z. B. "products/{slug}"

            // Platzhalter in Regex umwandeln (ähnlich OctoberCMS)
            $pattern = preg_replace([
                '#\{([a-zA-Z0-9_]+)\}#',     // {slug}
                '#\{([a-zA-Z0-9_]+)\?\}#',   // {id?}
                '#\{([a-zA-Z0-9_]+)\*\}#',   // {rest*}
            ], [
                '([^/]+)',
                '([^/]+)?',
                '(.*)',
            ], $url);

            if (preg_match('#^' . $pattern . '$#', $slug, $matches)) {
                // Param-Namen extrahieren
                preg_match_all('#\{([a-zA-Z0-9_]+)\??\*?\}#', $url, $keys);

                $dynamicParams = array_combine($keys[1], array_slice($matches, 1));

                $viewPath = 'websites.website_' . $website->slug . '.pages.' . $page->path;
                return view($viewPath, array_merge($baseParams, $dynamicParams));
            }
        }

        abort(404);
    }

    $viewPath = 'websites.website_' . $website->slug . '.pages.' . $page->path;
    return view($viewPath, array_merge($baseParams, compact('slug')));
})->where('slug', '.*')->name('website');;


