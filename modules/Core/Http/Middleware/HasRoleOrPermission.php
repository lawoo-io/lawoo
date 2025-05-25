<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class HasRoleOrPermission
{
    /**
     * Handle an incoming request.
     *
     * Usage: role:admin,manager|permission:user.create,user.edit
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $requirements): BaseResponse
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

        // Parse requirements (role:admin,manager|permission:user.create)
        $parts = explode('|', $requirements);
        $hasAccess = false;

        foreach ($parts as $part) {
            $hasAccess = $this->checkRequirement($user, trim($part));
            if ($hasAccess) {
                break; // If any requirement is met, grant access
            }
        }

        if (!$hasAccess) {
            return $this->unauthorized($request, "Access denied. Requirements: {$requirements}");
        }

        return $next($request);
    }

    /**
     * Check a single requirement (role:admin or permission:user.create)
     */
    protected function checkRequirement($user, string $requirement): bool
    {
        if (str_starts_with($requirement, 'role:')) {
            $roles = explode(',', substr($requirement, 5));
            return $this->hasAnyRole($user, $roles);
        }

        if (str_starts_with($requirement, 'permission:')) {
            $permissions = explode(',', substr($requirement, 11));
            return $this->hasAnyPermission($user, $permissions);
        }

        return false;
    }

    /**
     * Check if user has any of the specified roles
     */
    protected function hasAnyRole($user, array $roles): bool
    {
        if (!method_exists($user, 'hasRole')) {
            return false;
        }

        foreach ($roles as $role) {
            if ($user->hasRole(trim($role))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has any of the specified permissions
     */
    protected function hasAnyPermission($user, array $permissions): bool
    {
        if (!method_exists($user, 'can')) {
            return false;
        }

        foreach ($permissions as $permission) {
            if ($user->can(trim($permission))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle unauthorized access
     */
    protected function unauthorized(Request $request, string $message = 'Unauthorized'): BaseResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'error' => 'insufficient_access'
            ], Response::HTTP_FORBIDDEN);
        }

        abort(Response::HTTP_FORBIDDEN, $message);
    }
}
