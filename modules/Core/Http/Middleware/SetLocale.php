<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $defaultLocale = config('app.locale');
        $supportedLocales = array_keys(config('app.locales'));
        $appLocaleToSet = $defaultLocale;

        if ($request->hasSession()) {
            $sessionLocale = $request->session()->get('locale');

            if (in_array($sessionLocale, $supportedLocales)) {
                $appLocaleToSet = $sessionLocale;
            } else {
                $browserLocale = $request->getPreferredLanguage($supportedLocales);

                if ($browserLocale && in_array($browserLocale, $supportedLocales)) {
                    $appLocaleToSet = $browserLocale;
                    $request->session()->put('locale', $browserLocale);
                } else {
                    $appLocaleToSet = $defaultLocale;
                    $request->session()->put('locale', $defaultLocale);
                }
            }
        } else {
            $browserLocale = $request->getPreferredLanguage($supportedLocales);
            if ($browserLocale && in_array($browserLocale, $supportedLocales)) {
                $appLocaleToSet = $browserLocale;
            }
        }

        App::setLocale($appLocaleToSet);

        return $next($request);
    }
}
