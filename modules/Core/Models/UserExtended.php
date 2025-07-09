<?php

namespace Modules\Core\Models;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Core\Database\Factories\UserFactory;
use Modules\Core\Models\Traits\ClearsCacheOnSave;
use Modules\Core\Models\Traits\HasMessages;
use Modules\Core\Models\Traits\HasRoles;
use Modules\Core\Models\Traits\TrackableModel;
use Modules\Web\Models\Company;

class UserExtended extends User implements MustVerifyEmail
{
    use HasRoles, SoftDeletes, ClearsCacheOnSave, HasMessages, TrackableModel;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super_admin',
        'is_active',
        'language_id'
    ];

    protected $trackable = [
        'name',
        'email',
        'language_id'
    ];

    protected $trackableFields = [
        'name' => 'Name',
        'email' => 'Email Address',
        'language_id' => [
            'type' => 'belongsTo',
            'relationship' => 'language',
            'display_field' => 'name',
            'label' => 'Language'
        ],
        'roles' => [
            'type' => 'belongsToMany',
            'relationship' => 'roles',
            'display_field' => 'name',
            'label' => 'Roles'
        ]
    ];

//    protected $trackCreation = true;
    protected $trackDeletion = true;

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'is_super_admin' => 'boolean',
            'is_active' => 'boolean',
            'last_permission_check' => 'datetime',
        ]);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')  // ← Explizite Keys
        ->withPivot(['assigned_by', 'expires_at'])
            ->withTimestamps();
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'user_companies', 'user_id', 'company_id');
    }

    /**
     * BelongsTo Language
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    // =====================================================================
    // RBAC Methods (Override Laravel's default can method)
    // =====================================================================

    /**
     * Main permission check method
     */
    // Permission Check (Main Method)
    public function can($permission, $arguments = []): bool
    {
        // Super Admin bypass
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Check if user is active
        if (!$this->is_active) {
            return false;
        }

        // Check through roles
        return $this->hasPermissionViaRole($permission);
    }

    /**
     * Check if user cannot perform ability
     */
    public function cannot($ability, $arguments = []): bool
    {
        return !$this->can($ability, $arguments);
    }

    // =====================================================================
    // User Management Methods
    // =====================================================================

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->is_active ?? false;
    }

    /**
     * Activate user account
     */
    public function activate(): self
    {
        $this->update(['is_active' => true]);
        return $this;
    }

    /**
     * Deactivate user account
     */
    public function deactivate(): self
    {
        $this->update(['is_active' => false]);
        return $this;
    }

    /**
     * Make user super admin
     */
    public function makeSuperAdmin(): self
    {
        $this->update(['is_super_admin' => true]);
        return $this;
    }

    /**
     * Remove super admin privileges
     */
    public function removeSuperAdmin(): self
    {
        $this->update(['is_super_admin' => false]);
        return $this;
    }

    /**
     * Update permission check timestamp for analytics
     */
    public function updatePermissionCheckTimestamp(): void
    {
        // Only update every 5 minutes to reduce DB load
        if (!$this->last_permission_check || $this->last_permission_check->diffInMinutes(now()) >= 5) {
            $this->update(['last_permission_check' => now()]);
        }
    }

    // =====================================================================
    // Convenience Methods
    // =====================================================================

    /**
     * Check if user has any administrative role
     */
    public function isAdmin(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->hasAnyRole(['admin', 'administrator', 'system-admin']);
    }

    /**
     * Check if user is a manager (has any manager role)
     */
    public function isManager(): bool
    {
        return $this->roles()
            ->where('slug', 'like', '%-manager')
            ->orWhere('slug', 'like', 'manager-%')
            ->exists();
    }

    /**
     * Get formatted role names for display
     */
    public function getRoleNames(): string
    {
        return $this->roles->pluck('name')->implode(', ');
    }

    /**
     * Get permission count for analytics
     */
    public function getPermissionCount(): int
    {
        return $this->getAllPermissions()->count();
    }

    /**
     * Get user's primary role (first role assigned)
     */
    public function getPrimaryRole(): ?\Modules\Core\Models\Role
    {
        return $this->roles()->orderBy('user_roles.created_at')->first();
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

}
