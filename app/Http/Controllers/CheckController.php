<?php

namespace App\Http\Controllers;

use App\Permission;
use App\Role;
use App\User;
use Auth;

class CheckController extends Controller
{

    public function __construct()
    {

        $this->middleware('auth:api');

    }

    public function checkPermissions()
    {

        $permissions = Auth::user()->role()->first();

        $permissions = $permissions->permission()->value('name');

        $permissions = explode(',', $permissions); //seperate name string by ',' and push them to array

        return response()->json([

            'permissions' => $permissions,

            'status'      => 'success',
        ]);

    }

}
