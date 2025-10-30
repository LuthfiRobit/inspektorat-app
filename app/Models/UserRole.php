<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class UserRole
 *
 * @package App\Models
 *
 * @property int $id_user_role
 * @property int $user_id
 * @property int $role_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Role $role
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class UserRole extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_role';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_user_role';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id', // Foreign key for the User model
        'role_id', // Foreign key for the Role model
    ];

    /**
     * Get the user that owns the UserRole.
     *
     * Defines a many-to-one relationship with the User model.
     * The 'user_id' foreign key on the 'user_role' table refers to 'id_user' on the 'users' table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    /**
     * Get the role that owns the UserRole.
     *
     * Defines a many-to-one relationship with the Role model.
     * The 'role_id' foreign key on the 'user_role' table refers to 'id_role' on the 'roles' table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id_role');
    }
}
