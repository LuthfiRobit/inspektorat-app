<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class LogActivity
 *
 * This model represents the 'log_activity' table in the database.
 * It is used to record various activities performed within the application,
 * typically including user actions, their IP addresses, and user agents.
 *
 * @package App\Models
 *
 * @property int $id_log_activity The primary key for the log activity record.
 * @property int $user_id The ID of the user who performed the action.
 * @property string $action The type of action performed (e.g., 'login', 'create_user', 'update_role').
 * @property string|null $description A detailed description of the activity.
 * @property string|null $ip_address The IP address from which the action was performed.
 * @property string|null $user_agent The user agent string of the client's browser.
 * @property \Illuminate\Support\Carbon|null $created_at Timestamp when the activity was logged.
 * @property \Illuminate\Support\Carbon|null $updated_at Timestamp when the log record was last updated.
 *
 * @property-read \App\Models\User $user The user associated with this log activity.
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class LogActivity extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'log_activity';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_log_activity';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true; // Assuming 'created_at' and 'updated_at' columns exist in the table

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
    ];

    /**
     * Get the user that owns the LogActivity.
     *
     * Defines a many-to-one relationship with the User model.
     * The 'user_id' foreign key on the 'log_activity' table refers to 'id_user' on the 'users' table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }
}
