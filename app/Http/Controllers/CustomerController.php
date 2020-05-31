<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Http\Resources\Customer as CustomerResource;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct()
    {

        $this->middleware('auth:api');

    }

    public function index()
    {
        $this->authorize('hasPermission', 'view_customers');

        return CustomerResource::collection(Customer::paginate(8));
    }

    public function store(Request $request)
    {
        $this->authorize('hasPermission', 'add_customer');


        $this->validate($request, [
            'name'    => 'required|regex:/^[\pL\s\-]+$/u',
            'address' => 'required|string|max:200',
            'phone'   => 'required|unique:customers,phone|digits:10',
            'details' => 'required|string|max:400',
        ]);

        $customer          = new Customer();
        $customer->name    = $request->input('name');
        $customer->address = $request->input('address');
        $customer->phone   = $request->input('phone');
        $customer->details = $request->input('details');

        if ($customer->save()) {
            return response()->json([
                'msg'    => 'Customer added successfully',
                'status' => 'success',
            ]);
        } else {
            return response()->json([
                'msg'    => 'Error while adding customer',
                'status' => 'error',
            ]);
        }

    }

    public function update(Request $request)
    {

        $this->authorize('hasPermission', 'edit_customer');

        $this->validate($request, [
            'name'    => 'required|regex:/^[\pL\s\-]+$/u',
            'address' => 'required|string|max:200',
            'phone'   => 'required|digits:10',
            'details' => 'required|string|max:400',
        ]);

        $id                = $request->input('id'); //get id from edit modal
        $customer          = Customer::findOrFail($id);
        $customer->name    = $request->input('name');
        $customer->address = $request->input('address');
        $customer->phone   = $request->input('phone');
        $customer->details = $request->input('details');

        if ($customer->save()) {
            return response()->json([
                'msg'    => 'Customer update successfully',
                'status' => 'success',
            ]);
        } else {
            return response()->json([
                'msg'    => 'Error while updating customer',
                'status' => 'error',
            ]);
        }

    }

    // public function search(Request $request){

    //    $searchQuery= $request->searchQuery;

    //    $queryResults=Customer::where('name','like','%'.$searchQuery.'%')->get();

    //       return response()
    //         ->json([
    //             'search' => 'ok',
    //             'queryResults' => $queryResults
    //         ]);
    // }

    public function destroy($id)
    {
        $this->authorize('hasPermission', 'delete_customer');


        $customer = Customer::findOrFail($id);
        if ($customer->delete()) {
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
        $this->authorize('hasPermission', 'show_customer');

       
        $customer = Customer::findOrFail($id);
        if ($customer->save()) {
            return response()->json([
                'customer' => $customer,
                'status'   => 'success',
            ]);
        } else {
            return response()->json([
                'msg'    => 'Error while retriving Customer',
                'status' => 'error',
            ]);
        }
    }

    public function searchCustomers(Request $request)
    {

        $this->authorize('hasPermission', 'search_customers');

        $searchKey = $request->input('searchQuery');
        if ($searchKey != '') {
            return CustomerResource::collection(Customer::where('name', 'like', '%' . $searchKey . '%')->paginate(8));
        } else {
            return response()->json([
                'msg'    => 'Error while retriving Customer. No Data Supplied as key.',
                'status' => 'error',
            ]);
        }
    }
}
