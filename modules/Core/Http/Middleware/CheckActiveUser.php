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

        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (method_exists($user, 'is_active') && !$user->is_active) {
            auth()->logout();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your account has been deactivated',
                    'error' => 'account_deactivated'
                ], Response::HTTP_FORBIDDEN);
            }

            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been deactivated.']);
        }

        return $next($request);
    }
}
