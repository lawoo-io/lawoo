<?php

// Check if the function 'lroute' does not already exist to prevent conflicts.
use Modules\Core\Helpers\RouteHelper;
use Modules\Core\Models\ModuleUiTranslation;

/**
 * Retrieves a module-specific translation or automatically adds it to source JSON files if not found.
 *
 * @param string $keyString The original string to be translated, serving as the key.
 * @param string $moduleName The name of the module (e.g., 'CRM', 'Accounting', 'Global').
 * @param array $replace Placeholders to replace in the string.
 * @return string The translated string or the original key if no translation is found.
 */
if (!function_exists('__t')) {
    /**
     * Retrieves a module-specific translation from the database (via cache).
     * If the translation is not found, it logs a warning and returns the original key string with a visual marker.
     * This function does NOT automatically create entries in files or the database.
     *
     * @param string $keyString The original string to be translated, serving as the key.
     * @param string $moduleName The name of the module (e.g., 'CRM', 'Accounting', 'Global'). Defaults to 'global'.
     * @param array $replace Placeholders to replace in the string (e.g., ['name' => 'John Doe']).
     * @return string The translated string or the original key with a missing marker if not found in the DB.
     */
    function __t(string $keyString, string $moduleName = 'Parameter not exists', array $replace = []): string
    {
        // Ensure $moduleName is always a non-empty string.
        // If it's passed as empty, default it to 'global'.
        if (empty($moduleName)) {
            $moduleName = 'Parameter not exists';
        }

        $locale = App::getLocale();
        // Construct a unique cache key for this translation string.
        // Uses '__t.' prefix for consistency and md5 for shorter, fixed-length keys.
        $cacheKey = "__t.{$locale}.{$moduleName}." . md5($keyString);

        // 1. Attempt to retrieve the translation from the cache first for performance.
        if (Cache::has($cacheKey)) {
            $translatedValue = Cache::get($cacheKey);
            return str_replace(array_keys($replace), array_values($replace), $translatedValue);
        }

        // 2. If not in cache, try to retrieve the translation from the database.
        // The search includes 'module_name' to find the specific translation for this module.
        $translation = ModuleUiTranslation::where('key_string', $keyString)
            ->where('locale', $locale)
            ->where('module', $moduleName)
            ->first();

        $valueToReturn = $keyString; // Default to the original key string as a fallback.

        if ($translation) {
            // If the translation is found in the database, use its translated value.
            if (empty($translation->translated_value)) {
                $logMessage = "Missing UI translation: '{$keyString}' for module '{$moduleName}' in locale '{$locale}'.";
                $valueToReturn = "<span class='inline-block bg-red-100 text-red-800 border border-red-500 rounded px-2 py-1 text-xs font-semibold'>" . $logMessage . "</span>";
            } else {
                $valueToReturn = $translation->translated_value;
            }
        } else {
            // If the translation is NOT found in the database:
            // This indicates a missing translation.
            // Log a warning for developers. The string might be missing in JSON source files
            // or 'php artisan translations:sync-ui-strings' might not have been run.
            $logMessage = "Missing UI translation: '{$keyString}' for module '{$moduleName}' in locale '{$locale}'.";

            // In local or staging environments, display a highly visible marker in the view.
            if (App::environment('local', 'staging')) {
                Log::warning($logMessage . " Please ensure 'php artisan lawoo:sync-ui-strings ModuleName' has been run.");
                // Return a visually distinct HTML span with Tailwind classes for dev/staging.
                // This HTML will be rendered if `{!! __t(...) !!}` is used in Blade.
                $valueToReturn = "<span class='inline-block bg-red-100 text-red-800 border border-red-500 rounded px-2 py-1 text-xs font-semibold'>MISSING: " .
                    e($keyString) . " ({$moduleName}/{$locale})</span>";
            } else {
                // In production, just log the warning without specific commands and return the original key.
                Log::warning($logMessage);
                $valueToReturn = $keyString;
            }
        }

        // 3. Store the determined value (either translated or original key/marker) in cache for future requests.
        Cache::forever($cacheKey, $valueToReturn);

        // 4. Replace any placeholders in the final string and return it.
        return str_replace(array_keys($replace), array_values($replace), $valueToReturn);
    }
}

// The clear_t_cache function (remains unchanged as previously corrected)
if (!function_exists('clear_t_cache')) {
    /**
     * Clears the cache for module-specific translations.
     * Call this after any changes to source files or DB.
     *
     * @param string|null $moduleName If provided, attempts to clear cache specific to this module.
     * @param string|null $keyString If provided, attempts to clear cache for this specific original string.
     */
    function clear_t_cache(?string $moduleName = null, ?string $keyString = null): void
    {
        $supportedLocales = array_keys(config('app.locales', ['en' => 'English']));

        if ($keyString && $moduleName) {
            foreach ($supportedLocales as $locale) {
                $cacheKey = "__t.{$locale}.{$moduleName}." . md5($keyString);
                Cache::forget($cacheKey);
            }
        } elseif ($moduleName) {
            Cache::flush(); // Consider using cache tags for more granular module-specific flushing
        } else {
            Cache::flush(); // Flush all relevant cache
        }
    }
}

if (!function_exists('lroute')) {
    /**
     * Wrapper function for RouteHelper::localizedRoute().
     * Generates a URL with the correct language prefix or without, if it's the default language.
     *
     * @param string $routeName The name of the route (e.g., 'locale.dashboard').
     * @param array $parameters Optional parameters for the route.
     * @param string|null $locale The target locale. If null, the current application locale will be used.
     * @return string The generated URL.
     */
    function lroute(string $routeName, array $parameters = [], ?string $locale = null): string
    {
        // Access your Facade, which resolves your RouteHelpers class.
        return RouteHelper::localizedRoute($routeName, $parameters, $locale);
    }
}

// Check if the function 'lurl' does not already exist to prevent conflicts.
if (!function_exists('lurl')) {
    /**
     * Wrapper function for RouteHelper::currentLocalizedUrl().
     * Generates the URL for the current request in a new language.
     *
     * @param string $targetLocale The locale to switch to.
     * @return string The URL for the current page in the target locale.
     */
    function lurl(string $targetLocale): string
    {
        // Access your Facade, which resolves your RouteHelpers class.
        return RouteHelper::currentLocalizedUrl($targetLocale);
    }
}
