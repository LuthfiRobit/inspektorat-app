<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class RolePermission
 *
 * This model represents the pivot table (many-to-many relationship)
 * between roles and permissions. It links specific roles to specific permissions.
 *
 * @package App\Models
 *
 * @property int $id_role_permission The primary key for the role_permission record.
 * @property int $role_id The foreign key linking to the Role model.
 * @property int $permission_id The foreign key linking to the Permission model.
 * @property \Illuminate\Support\Carbon|null $created_at Timestamp when the record was created.
 * @property \Illuminate\Support\Carbon|null $updated_at Timestamp when the record was last updated.
 *
 * @property-read \App\Models\Role $role The Role model this record belongs to.
 * @property-read \App\Models\Permission $permission The Permission model this record belongs to.
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class RolePermission extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'role_permission';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_role_permission';

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
        'role_id',
        'permission_id',
    ];

    /**
     * Get the role that owns the RolePermission.
     *
     * Defines a many-to-one relationship with the Role model.
     * The 'role_id' foreign key on the 'role_permission' table refers to 'id_role' on the 'roles' table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id_role');
    }

    /**
     * Get the permission that owns the RolePermission.
     *
     * Defines a many-to-one relationship with the Permission model.
     * The 'permission_id' foreign key on the 'role_permission' table refers to 'id_permission' on the 'permissions' table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'permission_id', 'id_permission');
    }
}
