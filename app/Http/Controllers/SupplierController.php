<?php

namespace App\Http\Controllers;

use App\Http\Resources\Supplier as SupplierResource;
use App\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{

    public function __construct()
    {

        $this->middleware('auth:api');
    }

    public function index()
    {
        $this->authorize('hasPermission', 'view_suppliers');

        return SupplierResource::collection(Supplier::paginate(8));

    }

    public function store(Request $request)
    {

        $this->authorize('hasPermission', 'add_supplier');
        

        $this->validate($request, [

            'name'    => 'required|regex:/^[\pL\s\-]+$/u',

            'address' => 'required|string|max:200',

            'phone'   => 'required|unique:customers,phone|digits:10',

            'details' => 'required|string|max:400',
        ]);

        $supplier = new Supplier();

        $supplier->name = $request->input('name');

        $supplier->address = $request->input('address');

        $supplier->phone = $request->input('phone');

        $supplier->details = $request->input('details');

        if ($supplier->save()) {

            return response()->json([

                'msg'    => 'Supplier added successfully',

                'status' => 'success',
            ]);

        } else {
            return response()->json([

                'msg'    => 'Error while adding supplier',

                'status' => 'error',
            ]);
        }
    }

    public function update(Request $request)
    {
        $this->authorize('hasPermission', 'edit_supplier');


        $this->validate($request, [

            'name'    => 'required|regex:/^[\pL\s\-]+$/u',

            'address' => 'required|string|max:200',

            'phone'   => 'required|digits:10',

            'details' => 'required|string|max:400',

        ]);

        $id = $request->input('id'); //get id from edit modal

        $supplier = Supplier::findOrFail($id);

        $supplier->name = $request->input('name');

        $supplier->address = $request->input('address');

        $supplier->phone = $request->input('phone');

        if ($supplier->save()) {

            return response()->json([

                'msg'    => 'Supplier update successfully',

                'status' => 'success',
            ]);
        } else {

            return response()->json([

                'msg'    => 'Error while updating supplier',
                'status' => 'error',
            ]);
        }

    }

    public function destroy($id)
    {
        $this->authorize('hasPermission', 'delete_supplier');


        $supplier = Supplier::findOrFail($id);
        if ($supplier->delete()) {
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

    public function show($id)
    {
        $this->authorize('hasPermission', 'show_supplier');


        $supplier = Supplier::findOrFail($id);
        if ($supplier->save()) {
            return response()->json([
                'supplier' => $supplier,
                'status'   => 'success',
            ]);
        } else {
            return response()->json([
                'msg'    => 'Error while retriving Supplier',
                'status' => 'error',
            ]);
        }
    }

    public function searchSuppliers(Request $request)
    {
        $this->authorize('hasPermission', 'search_suppliers');

        $searchKey = $request->input('searchQuery');
        if ($searchKey != '') {
            return SupplierResource::collection(Supplier::where('name', 'like', '%' . $searchKey . '%')->paginate(8));
        } else {
            return response()->json([
                'msg'    => 'Error while retriving Suppliers. No Data Supplied as key.',
                'status' => 'error',
            ]);
        }
    }

}
