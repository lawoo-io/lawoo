<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get default language from core config file (e.g., 'de')
        $defaultLocale = config('app.locale');

        // Get all available languages from config (e.g., ['de', 'en', 'fr'])
        $supportedLocales = array_keys(config('app.locales'));

        $appLocaleToSet = $defaultLocale; // Initialize with the default locale as a fallback

        // --- Core Logic for Locale Determination ---

        // Priority 1: Check if a supported locale is already stored in the session.
        $sessionLocale = Session::get('locale');
        if (in_array($sessionLocale, $supportedLocales)) {
            $appLocaleToSet = $sessionLocale; // Use the locale from the session
        }
        // Priority 2: If no supported locale in session, check the browser's preferred language.
        else {
            $browserLocale = $request->getPreferredLanguage($supportedLocales); // Get preferred language from browser
            if ($browserLocale && in_array($browserLocale, $supportedLocales)) {
                $appLocaleToSet = $browserLocale; // Use the locale from the browser
                Session::put('locale', $browserLocale); // Save browser locale to session for persistence
            }
            // Priority 3: If neither session nor browser provides a supported locale, fall back to the default.
            else {
                $appLocaleToSet = $defaultLocale;
                Session::put('locale', $defaultLocale); // Save default locale to session for persistence
            }
        }

        // Set the determined locale for the application.
        App::setLocale($appLocaleToSet);

        // --- End of Core Logic ---

        // IMPORTANT: No redirection logic in this middleware, as per your request.
        // URL segments (like /en/) will now be ignored for locale determination.
        // If you still want URL prefixes to trigger something (e.g., a language change),
        // that would need to be handled separately in routes or another middleware.

        return $next($request);
    }
}
