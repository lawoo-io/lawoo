<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class CheckActiveUser
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): BaseResponse
    {
        // Skip für Login/Register/Logout Routes
        if ($request->routeIs(['login', 'register', 'logout', 'password.*'])) {
            return $next($request);
        }

        // Skip für Livewire-Requests
        if ($request->is('livewire/*') || $request->header('X-Livewire')) {
            return $next($request);
        }

        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (!$user->is_active) {
            auth()->logout();
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('Your account has been deactivated.'),
                    'error' => 'account_deactivated'
                ], Response::HTTP_FORBIDDEN);
            }

            return redirect()->route('login')
                ->withErrors(['email' => __('Your account has been deactivated.')]);
        }

        return $next($request);
    }
}
