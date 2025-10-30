<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Permission
 *
 * This model represents the 'permission' table in the database.
 * It defines individual permissions that can be assigned to roles,
 * thereby controlling access to various parts or functionalities of the application.
 *
 * @package App\Models
 *
 * @property int $id_permission The primary key for the permission.
 * @property string $permission_name The unique name of the permission (e.g., 'view_users', 'create_posts').
 * @property string|null $permission_description A brief description of what the permission allows.
 * @property \Illuminate\Support\Carbon|null $created_at Timestamp when the permission was created.
 * @property \Illuminate\Support\Carbon|null $updated_at Timestamp when the permission was last updated.
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Role[] $roles The roles that have this permission.
 * @property-read int|null $roles_count
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Permission extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'permission';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_permission';

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
    protected $fillable = ['permission_name', 'permission_description'];

    /**
     * The roles that have this permission.
     *
     * Defines a many-to-many relationship with the Role model through the 'role_permission' pivot table.
     * 'permission_id' is the foreign key on the pivot table referring to the Permission model's primary key.
     * 'role_id' is the foreign key on the pivot table referring to the Role model's primary key.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission', 'permission_id', 'role_id');
    }
}
