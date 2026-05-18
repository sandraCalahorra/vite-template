<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Notifications\CustomResetPasswordNotification;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, Notifiable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'level',

    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier() {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims() {
        return [];
    }
    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }

    /**
     * Check if the authenticated user has a specific permission.
     *
     * Usage examples:
     *
     * 1. Basic permission check:
     *    ```php
     *    if (auth()->user()->hasPermission('projects', 'create')) {
     *        // User can create projects
     *    }
     *    ```
     *
     * 2. Permission with level restriction (integer condition):
     *    ```php
     *    // User must have a level <= 20
     *    auth()->user()->hasPermission('projects', 'view', 20);
     *    ```
     *
     * 3. Permission with level comparison (array condition):
     *    ```php
     *    // User must have a level greater than or equal to 21
     *    auth()->user()->hasPermission('projects', 'view', ['gt' => 21]);
     *    ```
     *
     * Supported comparison operators in the condition array:
     * - `lt`  → less than
     * - `lte` → less than or equal to
     * - `gt`  → greater than
     * - `gte` → greater than or equal to
     * - `eq`  → equal to
     * - `neq`  → not equal to
     *
     * @param  string      $entity     The entity name (e.g., 'projects', 'news').
     * @param  string      $action     The action name (e.g., 'view', 'create', 'delete').
     * @param  int|array|null $condition Optional user level condition.
     * @return bool
     */
    public function hasPermission(string $entity, string $action, ?int $condition = null): bool
    {
        // Bypass if user level is 0
        if (auth()->user()->level == 0) {
            return true;
        }

        // $path = base_path('package.json');
        // $package = json_decode(file_get_contents($path), true);
        // if (auth()->user()->version != $package['version']) {
        //     return true; // Temporary permission bypass until version syncs
        // }

        $actionId = PermissionsActionsType::where('name', $action)->value('id');

        if (!$actionId) {
            return false; // action doesn't exist
        }

        // Check base permission
        $hasPermission = Permission::where('entity', $entity)
            ->where('action_id', $actionId)
            ->where('level', $this->level)
            ->exists();

        if (!$hasPermission) {
            return false;
        }

        // Optional level check
        if ($condition != null) {
            $level = $this->level;
            if (is_numeric($condition) && $level > $condition) return false;

            if (is_array($condition)) {
                if (isset($condition['lt']) && !($level < $condition['lt'])) return false;
                if (isset($condition['lte']) && !($level <= $condition['lte'])) return false;
                if (isset($condition['gt']) && !($level > $condition['gt'])) return false;
                if (isset($condition['gte']) && !($level >= $condition['gte'])) return false;
                if (isset($condition['eq']) && !($level == $condition['eq'])) return false;
                if (isset($condition['neq']) && !($level != $condition['neq'])) return false;
            }
        }

        return true;
    }
}
