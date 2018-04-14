<?php

namespace MihaiBlebea\GenericApp\Traits;

use MihaiBlebea\GenericApp\Models\Role;
use MihaiBlebea\GenericApp\Models\Permission;
use Exception;
use DB;

trait HasRoleTrait
{
    // Define relationship methods
    public function role()
    {
        return $this->belongsToMany(Role::class, 'user_roles')->withTimestamps();
    }

    public function getPermissions()
    {
        $role = $this->role->first();
        if($role)
        {
            return $role->permissions;
        }
        return null;
    }

    // Roles custom methods
    public function hasRole(String $role_name)
    {
        $role = $this->role->first();
        if($role && $role->name == $role_name)
        {
            return true;
        }
        return false;
    }

    public function assignRole(String $role_name)
    {
        $role = Role::where('name', $role_name)->first();
        if($role)
        {
            $table = DB::table('user_roles');
            $entry = $table->where('user_id', $this->id)->first();
            if($entry)
            {
                $table->update(['role_id' => $role->id]);
            } else {
                $table->insert([
                    'user_id' => $this->id,
                    'role_id' => $role->id
                ]);
            }
        }
        return $this;
    }

    public function cancelRole()
    {
        DB::table('user_roles')->where('user_id', $this->id)->delete();
        return $this;
    }

    public function hasPermissionTo(...$permissions)
    {
        $role = $this->role->first();
        if($role)
        {
            $actions = $role->permissions->pluck('action');
            foreach($permissions as $permission_name)
            {
                if($actions->contains($permission_name) == false)
                {
                    return false;
                }
            }
            return true;
        }
        return false;
    }
}
