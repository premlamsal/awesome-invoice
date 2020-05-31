<?php

namespace App\Http\Controllers;

use App\Http\Resources\Role as RoleResource;
use App\Permission;
use App\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct()
    {

        $this->middleware('auth:api');

    }

    public function index()
    {

        $this->authorize('hasPermission', 'view_roles');

        return RoleResource::collection(Role::where('name', '!=', 'owner')->with('permission')->paginate(8));

    }

    public function store(Request $request)
    {

        $this->authorize('hasPermission', 'add_role');

        $this->validate($request, [

            'name' => 'required|string|max:10',

        ]);

        $role = new Role();

        $role->name = $request->input('name');

        $role->permission_id = $request->input('permission_id');

        if ($role->save()) {

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

    public function show($id)
    {

        $this->authorize('hasPermission', 'show_role');

        $role = Role::where('id', $id)->with('permission')->first();

        return response()->json([
            'data'   => $role,
            'status' => 'success',
        ]);
    }

    public function update(Request $request)
    {

        $this->authorize('hasPermission', 'edit_role');

        $this->validate($request, [

            'name' => 'required|string|max:10',

        ]);

        $role = Role::findOrFail($request->input('id'));

        $role->name = $request->input('name');

        $role->permission_id = $request->input('permission_id');

        if ($role->save()) {

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

    public function destroy($id)
    {

        $this->authorize('hasPermission', 'delete_role');

        $role = Role::where('id', $id)->first();

        if ($role->delete()) {

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
    }

    public function searchPermissions(Request $request)
    {

        $this->authorize('hasPermission', 'search_role');

        $searchKey = $request->input('searchQuery');
        if ($searchKey != '') {
            return PermissionResource::collection(Permission::where('short_name', 'like', '%' . $searchKey . '%')->paginate(8));
        } else {
            return response()->json([
                'msg'    => 'Error while retriving Permissions. No Data Supplied as key.',
                'status' => 'error',
            ]);
        }
    }
}
