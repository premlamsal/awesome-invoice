<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{

    public function user()
    {

        return $this->belongsTo('\App\User');
    }

    public function permission()
    {

        return $this->belongsTo('App\Permission', 'permission_id', 'id');
    }

    public function hasPermission($permission)
    {

        $permission = $this->role()->first();

        $permission = $permission->permission()->value('name');

        $permission = explode(',', $permission); //seperate name string by ',' and push them to array

        if (in_array($permission, $permission)) {

            return true;
        }
        return false;
    }
}
