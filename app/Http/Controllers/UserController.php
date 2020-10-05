<?php

namespace App\Http\Controllers;

use App\Http\Resources\User as UserResource;
use App\Store;
use App\User;
use Auth;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {

        $this->middleware('auth:api');

    }

    public function index()
    {

        $this->authorize('hasPermission', 'all');

        return UserResource::collection(User::with('role')->paginate(8));
    }

    public function store(Request $request)
    {
        $this->authorize('hasPermission', 'all'); //all permission belongs to owner only

        $this->validate($request, [

            'name'  => 'required|string|max:20',

            'email' => 'required|email|max:100',
            // 'password'=>'required| min:6|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\X])(?=.*[!$#%]).*$/',
            //English uppercase characters (A – Z)

            // English lowercase characters (a – z)

            // Base 10 digits (0 – 9)

            // Non-alphanumeric (For example: !, $, #, or %)

            // Unicode characters
        ]);

        $user = new User();

        $user->name = $request->input('name');

        $user->email = $request->input('email');

        $user->password = bcrypt($request->input('password'));

        $user->role_id = $request->input('role_id');

        $user->store_id = Auth::user()->store_id;

        if ($user->save()) {

            return response()->json([
                'msg'    => 'Data Saved',
                'status' => 'success',
            ]);

        } else {

            return response()->json([
                'msg'    => 'Error Saving Data',
                'status' => 'eroor',
            ]);

        }

    }

    public function update(Request $request)
    {
        $this->authorize('hasPermission', 'all'); //all permission belongs to owner only

        $this->validate($request, [

            'name'  => 'required|string|max:20',

            'email' => 'required|email|max:100',
            // 'password'=>'required| min:6|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\X])(?=.*[!$#%]).*$/',
            //English uppercase characters (A – Z)

            // English lowercase characters (a – z)

            // Base 10 digits (0 – 9)

            // Non-alphanumeric (For example: !, $, #, or %)

            // Unicode characters
        ]);

        $user_id = $request->input('id');

        $user = User::findOrFail($user_id);

        $user->name = $request->input('name');

        $user->email = $request->input('email');

        $user->password = bcrypt($request->input('password'));

        $user->role_id = $request->input('role_id');

        $user->store_id = Auth::user()->store_id;

        if ($user->save()) {

            return response()->json([
                'msg'    => 'Data updated',
                'status' => 'success',
            ]);

        } else {

            return response()->json([
                'msg'    => 'Error updating Data',
                'status' => 'eroor',
            ]);

        }
    }

    public function show($id)
    {

        $this->authorize('hasPermission', 'all'); //all permission belongs to owner only

        $user = User::where('id', $id)->with('role')->first();

        return response()->json([
            'user'   => $user,
            'status' => 'success',
        ]);
    }

    public function destroy($id)
    {

        $this->authorize('hasPermission', 'all'); //all permission belongs to owner only

        $user = User::findOrFail($id); //finding passed user refrence

        $role_name_of_passed_user = $user->value('name');

        if ($role_name_of_passed_user != 'owner') {

            if ($user->delete()) {
                return response()->json([
                    'msg'    => 'successfully Deleted',
                    'status' => 'success',
                ]);
            } else {
                return response()->json([
                    'msg'    => 'Error while deleting data',
                    'status' => 'error',
                ]);
            }
        } else {
            return response()->json([
                'msg'    => 'You can\'t delete owner',
                'status' => 'error',
            ]);
        }

    }

    public function searchUsers(Request $request)
    {

        $this->authorize('hasPermission', 'all'); //all permission belongs to owner only

        $searchKey = $request->input('searchQuery');
        if ($searchKey != '') {
            return UserResource::collection(User::where('name', 'like', '%' . $searchKey . '%')->paginate(8));
        } else {
            return response()->json([
                'msg'    => 'Error while retriving Users. No Data Supplied as key.',
                'status' => 'error',
            ]);
        }
    }

}
