<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class Role
 *
 * This model represents the 'role' table in the database.
 * It manages roles within the application, typically used for defining
 * permissions and associating them with users.
 *
 * @package App\Models
 *
 * @property int $id_role The primary key for the role.
 * @property string $role_name The unique name of the role (e.g., 'Admin', 'Editor').
 * @property string|null $role_description A brief description of the role's purpose.
 * @property \Illuminate\Support\Carbon|null $created_at Timestamp when the role was created.
 * @property \Illuminate\Support\Carbon|null $updated_at Timestamp when the role was last updated.
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users The users assigned to this role.
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Permission[] $permissions The permissions associated with this role.
 * @property-read int|null $users_count
 * @property-read int|null $permissions_count
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Role extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'role';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_role';

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
    protected $fillable = ['role_scope', 'role_name', 'role_description'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id_role' => 'integer',
        'role_scope' => 'string',
        'role_name' => 'string',
        'role_description' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The users that belong to the Role.
     *
     * Defines a many-to-many relationship with the User model through the 'user_role' pivot table.
     * 'role_id' is the foreign key on the pivot table referring to the Role model's primary key.
     * 'user_id' is the foreign key on the pivot table referring to the User model's primary key.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_role', 'role_id', 'user_id');
    }

    /**
     * The permissions that belong to the Role.
     *
     * Defines a many-to-many relationship with the Permission model through the 'role_permission' pivot table.
     * 'role_id' is the foreign key on the pivot table referring to the Role model's primary key.
     * 'permission_id' is the foreign key on the pivot table referring to the Permission model's primary key.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission', 'role_id', 'permission_id');
    }

    /**
     * Get permissions associated with a specific role ID.
     *
     * This static method retrieves all permissions (id and name) linked to a given role ID
     * by joining the 'role_permission' and 'permission' tables.
     *
     * @param int $id The ID of the role to retrieve permissions for.
     * @return \Illuminate\Support\Collection A collection of permission objects,
     * each with 'permission_id' and 'permission_name'.
     */
    public static function getRolePermissions(int $id): Collection
    {
        $query = DB::table('role_permission')
            ->join('permission', 'role_permission.permission_id', '=', 'permission.id_permission')
            ->where('role_permission.role_id', $id)
            ->select('role_permission.permission_id', 'permission.permission_name')
            ->get();

        return $query;
    }

    public static function getByScope(string $scope): \Illuminate\Support\Collection
    {
        return DB::table('role')
            ->select('role_name')
            ->where('role_scope', $scope)
            ->get();
    }
}
