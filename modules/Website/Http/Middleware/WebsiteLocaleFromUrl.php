<?php

namespace Modules\Website\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class WebsiteLocaleFromUrl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get default language from core config file
        $defaultLocale = config('app.locale');

        // Get all available languages
        $supportedLocales = array_keys(config('app.locales'));

        // 1. Check the language from URL (Example: /en/dashboard)
        $localeFromUrl = $request->segment(1);

        // 2. Check the URL-Segment by available languages
        if (in_array($localeFromUrl, $supportedLocales)) {
            // When true, set the current language as default
            App::setLocale($localeFromUrl);
            Session::put('locale', $localeFromUrl); // Set the current language in session
        } else {
            // 3. When the language ist not available in the URL-Segment or the language not exists (Example: /dashboard)
            // Check the language in the Session
            $sessionLocale = Session::get('locale');

            if (in_array($sessionLocale, $supportedLocales)) {
                App::setLocale($sessionLocale);
            } else {
                // Or set the default language from config and save it in Session
                App::setLocale($defaultLocale);
                Session::put('locale', $defaultLocale);
            }
        }

        return $next($request);
    }
}
