<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class User
 *
 * @package App\Models
 *
 * @property int $id_user
 * @property string $name
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read \App\Models\Guru|null $guru
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Role[] $roles
 * @property-read int|null $roles_count
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'status', // Added status to fillable, assuming it's managed via the application
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // 'id_user' => 'integer',
            'name' => 'string',
            'username' => 'string',
            'email' => 'string',
            'email_verified_at' => 'datetime',
            'password' => 'string',
            'status' => 'string',
            'remember_token' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * The roles that belong to the User.
     *
     * Defines a many-to-many relationship with the Role model through the 'user_role' pivot table.
     * 'user_id' is the foreign key on the pivot table referring to the User model's primary key.
     * 'role_id' is the foreign key on the pivot table referring to the Role model's primary key.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role', 'user_id', 'role_id');
    }

    /**
     * Check if the user has a specific role.
     *
     * @param string $roleName The name of the role to check.
     * @return bool
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('role_name', $roleName)->exists();
    }

    /**
     * Check if the user has a specific permission through their roles.
     *
     * @param string $permissionName The name of the permission to check.
     * @return bool
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->roles()->whereHas('permissions', function ($query) use ($permissionName) {
            $query->where('permission_name', $permissionName);
        })->exists();
    }

    // Tambahkan method ini di User.php
    public function hasPermissionTo($permissionName): bool
    {
        return $this->roles()->whereHas('permissions', function ($query) use ($permissionName) {
            $query->where('permission_name', $permissionName);
        })->exists();
    }

    public function hasAnyPermission(array $permissionNames): bool
    {
        return $this->roles()->whereHas('permissions', function ($query) use ($permissionNames) {
            $query->whereIn('permission_name', $permissionNames);
        })->exists();
    }

    /**
     * Check if the user is a developer based on their roles.
     *
     * @return bool
     */
    public function isDeveloper(): bool
    {
        return $this->hasRole('developer');
    }

    public function petugas(): HasOne
    {
        return $this->hasOne(Petugas::class, 'user_id', 'id_user');
    }
}
