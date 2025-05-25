<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class HasPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): BaseResponse
    {
        if (!auth()->check()) {
            return $this->unauthorized($request, 'Authentication required');
        }

        $user = auth()->user();

        // Super Admin bypass
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return $next($request);
        }

        // Check if user is active
        if (method_exists($user, 'is_active') && !$user->is_active) {
            return $this->unauthorized($request, 'Account deactivated');
        }

        // Check if user has any of the required permissions
        if (empty($permissions)) {
            return $this->unauthorized($request, 'No permissions specified');
        }

        $hasPermission = false;
        foreach ($permissions as $permission) {
            if (method_exists($user, 'can') && $user->can($permission)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            $permissionList = implode(', ', $permissions);
            return $this->unauthorized($request, "Requires one of these permissions: {$permissionList}");
        }

        return $next($request);
    }

    /**
     * Handle unauthorized access
     */
    protected function unauthorized(Request $request, string $message = 'Unauthorized'): BaseResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'error' => 'insufficient_permission'
            ], Response::HTTP_FORBIDDEN);
        }

        abort(Response::HTTP_FORBIDDEN, $message);
    }
}
