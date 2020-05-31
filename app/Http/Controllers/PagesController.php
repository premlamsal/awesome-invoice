<?php

namespace App\Http\Controllers;

use App\Permission;
use App\Role;
use App\Store;
use App\User;
use Auth;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function __construct()
    {

        $this->middleware('auth');

    }

    public function saveStore(Request $request)
    {

        $user = User::findOrFail(Auth::user()->id);

        $store = new Store();

        $store->name = $request->input('name');

        $store->address = $request->input('address');

        $store->phone = $request->input('phone');

        $store->detail = $request->input('detail');

        $store->mobile = $request->input('mobile');

        $store->email = $request->input('email');

        $store->url = $request->input('url');

        $store->tax_number = $request->input('tax_number');

        $store->tax_percentage = $request->input('tax_percentage');

        $store->profit_percentage = $request->input('profit_percentage');

        $store->store_logo = null;

        if ($store->save()) {

            $permission = new Permission();

            $permission->name = 'all';

            if ($permission->save()) {

                $role = new Role();

                $role->name = 'owner'; //owner has all privilledge to do.

                $role->permission_id = $permission->id;

                if ($role->save()) {

                    $user->role_id = $role->id; //assign role id to user

                    $user->store_id = $store->id; //assign store id to user

                    if ($user->save()) {

                        return redirect('/');
                    }
                }
            }
        } else {
            // print_r("Store Creation Failed.");
            return back()->withInput();
        }
    }
}
