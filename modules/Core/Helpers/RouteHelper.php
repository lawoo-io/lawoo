<?php

namespace Modules\Core\Helpers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

class RouteHelper
{
    /**
     * Generates a URL with the correct language prefix or without, if it's the default language.
     *
     * @param string $routeName The name of the route (e.g., 'locale.dashboard').
     * @param array $parameters Optional parameters for the route.
     * @param string|null $locale The target locale. If null, the current application locale will be used.
     * @return string The generated URL.
     */
    public static function localizedRoute(string $routeName, array $parameters = [], ?string $locale = null): string
    {
        $locale = $locale ?? App::getLocale();
        $defaultLocale = config('app.locale'); // e.g., 'de'
        $supportedLocales = array_keys(config('app.locales'));

        // Ensure the route name starts with 'locale.' prefix for consistency
        if (!str_starts_with($routeName, 'locale.')) {
            $routeName = 'locale.' . $routeName;
        }

        // Clone parameters to avoid modifying the original array
        $routeParameters = $parameters;

        // If the target locale is the default locale (e.g., 'de')
        if ($locale === $defaultLocale) {
            // Remove the 'locale' parameter if it was explicitly passed,
            // so the route without a prefix is matched.
            unset($routeParameters['locale']);
            return route($routeName, $routeParameters);
        }

        // If the target locale is another supported language
        if (in_array($locale, $supportedLocales)) {
            // Add the 'locale' parameter for the URL prefix
            $routeParameters['locale'] = $locale;
            return route($routeName, $routeParameters);
        }

        // Fallback: If the target locale is not supported
        // Use the current application locale as a fallback for URL generation.
        $fallbackLocale = App::getLocale();
        $routeParameters['locale'] = $fallbackLocale;

        // If the fallback locale happens to be the default, ensure no locale parameter is included in the URL
        if ($fallbackLocale === $defaultLocale) {
            unset($routeParameters['locale']);
        }

        return route($routeName, $routeParameters);
    }


    /**
     * Helper for the language switcher in the navigation bar.
     * Generates the URL for the current request but for a different locale.
     *
     * @param string $targetLocale The locale to switch to.
     * @return string The URL for the current page in the target locale.
     */
    public static function currentLocalizedUrl(string $targetLocale): string
    {
        $currentRouteName = Route::currentRouteName();
        $currentRouteParameters = Route::current()->parameters;

        // Remove the 'locale' segment from the current route parameters,
        // as localizedRoute will add it based on $targetLocale if necessary.
        if (isset($currentRouteParameters['locale'])) {
            unset($currentRouteParameters['locale']);
        }

        return self::localizedRoute($currentRouteName, $currentRouteParameters, $targetLocale);
    }
}
