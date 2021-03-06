<?php
namespace Zjien\Quantum\Traits;

use Illuminate\Support\Facades\Config;

trait PermissionTrait
{
    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function($permission) {
            $permission->roles()->detach();
        });
    }

    /**
     * Many to Many relations with the role model
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Config::get('config.model.role'), Config::get('config.database.tables.user_role_relation'), 'permission_id', 'role_id');
    }
}