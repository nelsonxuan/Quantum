<?php
namespace Zjien\Quantum\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Zjien\Quantum\Contracts\UserContract;

class User extends Quantum implements UserContract
{
    /**
     * The User table.
     *
     * @var
     */
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('auth.table');
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        static::deleting(function($user) {
            $user->detachAllRole();
            return true;
        });
    }

    /**
     * Many to Many relations with the role model
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(config('quantum.model.role'), config('quantum.database.tables.user_role_relation'));
    }

    /**
     * Attach Role for User.
     *
     * @param array|Model|Collection $roles
     * @return void
     */
    public function attachRole($roles)
    {
        $this->roles()->attach(static::normalize($roles));
    }

    /**
     * Update Permission for Role.
     *
     * @param array|Model|Collection $roles
     * @return array
     */
    public function updateRole($roles)
    {
        return $this->roles()->sync(static::normalize($roles));
    }

    /**
     * Detach Role for User.
     *
     * @param array|Model|Collection $roles
     * @return int
     */
    public function detachRole($roles)
    {
       return $this->roles()->detach(static::normalize($roles));
    }

    /**
     * Detach All Role for User.
     *
     * @return int
     */
    public function detachAllRoles()
    {
        return $this->roles()->detach();
    }

    /**
     * Determine if the User has a given permission.
     *
     * @param array|object $permissions
     * @param bool $all
     * @return bool
     */
    public function can($permissions, $all = false)
    {
        $permissions = static::normalize($permissions);

        $userPermissions = [];

        foreach ($this->roles as $role) {
            $userPermissions[] = $role->permissions;
        }

        //waiting for testing
        $userPermissions = array_flatten($userPermissions);

        $userPermissions = array_map(function($userPermission) {
            return $userPermission->getKey();
        }, $userPermissions);

        $result = array_intersect($userPermissions, $permissions);

        if ($all && count($result) != count($permissions))
            return false;

        return !empty($result);
    }

    /**
     * Determine if the User has a given role.
     *
     * @param array|object $roles
     * @param bool $all
     * @return bool
     */
    public function is($roles, $all = false)
    {
        $roles = static::normalize($roles);

        $userRoles = $this->roles;

        $userRoles = array_map(function($userRole) {
            return $userRole->getkey();
        }, $userRoles);

        $result = array_intersect($userRoles, $roles);

        if ($all && count($result) != count($userRoles))
            return false;

        return !empty($result);
    }

    /**
     * @param array|object $permissions
     * @param array|object $roles
     * @param bool $all
     * @return bool
     */
    public function ability($permissions, $roles, $all = false)
    {
        $can = $this->can($permissions, $all);
        $is = $this->is($roles, $all);
        return $all? $can && $is : $can || $is;
    }

}