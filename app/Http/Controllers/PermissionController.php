<?php

namespace App\Http\Controllers;

use App\Http\Resources\Permission as PermissionResource;
use App\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{

    public function __construct()
    {

        $this->middleware('auth:api');

    }

    public function index()
    {

        $this->authorize('hasPermission', 'view_permissions');

        return PermissionResource::collection(Permission::where('name', '!=', 'all')->paginate(8));
    }

    public function store(Request $request)
    {

        $this->authorize('hasPermission', 'add_permission');

        $this->validate($request, [

            'name' => 'required',

        ]);

        $permission = new Permission();

        $permission_array = $request->input('name');

        $permission->name = implode(',', $permission_array); //joining array elements in string with ',' seperation

        if ($permission->save()) {

            return response()->json([
                'msg'    => 'You have successfully added the information.',
                'status' => 'success',
            ]);
        } else {
            return response()->json([
                'msg'    => 'Opps! My Back got cracked while working in Database',
                'status' => 'error',
            ]);
        }

    }

    public function show($id)
    {

        $this->authorize('hasPermission', 'show_permission');

        $permission = Permission::where('id', $id);

        $permission = $permission->value('name');

        $permission = explode(',', $permission);

        return response()->json([
            'permission' => $permission,
            'status'     => 'success',
        ]);
    }

    public function update(Request $request)
    {

        $this->authorize('hasPermission', 'edit_permission');

        $this->validate($request, [

            'name' => 'required',

        ]);

        $id = $request->input('id');

        $permission = Permission::where('id', $id)->first();

        $permission->name = $request->input('name');

        //joining array elements in string with ',' seperation

        // $permission->name=implode(',', $permission_array);

        if ($permission->save()) {

            return response()->json([

                'msg'    => "Record Updated successfully",

                'status' => 'success',
            ]);
        } else {

            return response()->json([

                'msg'    => 'Error Updating Data',

                'status' => 'error',
            ]);
        }
    }

    public function destroy($id)
    {

        $this->authorize('hasPermission', 'delete_permission');

        $permission = Permission::where('id', $id)->first();

        if ($permission->delete()) {

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

        $this->authorize('hasPermission', 'search_permission');

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
