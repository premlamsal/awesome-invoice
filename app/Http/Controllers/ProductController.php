<?php

namespace App\Http\Controllers;

use App\Http\Resources\Product as ProductResource;
use App\Product;
use App\Stock;
use App\Store;
use Auth;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct()
    {

        $this->middleware('auth:api');

    }
    public function index()
    {

        return ProductResource::collection(Product::with('unit')->with('category')->paginate(8));
    }

    public function store(Request $request)
    {

        $this->validate($request, [
            'name'           => 'required|string|max:200',
            'description'    => 'required|string|max:1000',
            'cp'          => 'required|numeric ',
            'sp'          => 'required|numeric ',
            'product_cat_id' => 'required|numeric ',
            'unit_id'        => 'required|numeric ',
            'image'          => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:4048',

        ]);

        //getting current custom product id from respective store

        $store = Store::findOrFail(Auth::user()->store_id);

        //old product id
        $product_id_count = $store->product_id_count;

        //explode product id from database

        $custom_product_id = explode('-', $product_id_count);

        $custom_product_id[1] = $custom_product_id[1] + 1; //increase product

        //new custom_product_id
        $new_count_product_id = implode('-', $custom_product_id);

        $product                    = new Product();
        $product->name              = $request->input('name');
        $product->product_cat_id    = $request->input('product_cat_id');
        $product->unit_id           = $request->input('unit_id');
        $product->description       = $request->input('description');
        $product->cp             = $request->input('cp');
        $product->sp             = $request->input('sp');
        $product->custom_product_id = $new_count_product_id; //asign new increase product custom id

        if ($request->hasFile('image')) {
            $imageName = '/img/' . time() . '.' . $request->image->getClientOriginalExtension();
            $request->image->move(public_path('img'), $imageName);
            $product->image = $imageName;
        }
        if ($product->save()) {

            $stock = new Stock();

            $stock->quantity = 0; //initilizing stock with quantity 0

            $stock->product_id = $product->id;

            $stock->unit_id = $request->input('unit_id');

            if ($stock->save()) {

                //set current product_id_count to store table
                $store->product_id_count = $new_count_product_id;

                if ($store->save()) {

                    return response()->json([
                        'msg'    => 'You have successfully changed the information.',
                        'status' => 'success',
                    ]);
                }

            } else {
                return response()->json([
                    'msg'    => 'Opps! My Back got cracked while working in Database',
                    'status' => 'error',
                ]);
            }
        } else {
            return response()->json([
                'msg'    => 'Opps! My Back got cracked while working in Database',
                'status' => 'error',
            ]);
        }

    }
    public function update(Request $request)
    {

        $this->validate($request, [
            'name'           => 'required|string|max:200',
            'description'    => 'required|string|max:1000',
            'cp'          => 'required|numeric ',
            'sp'          => 'required|numeric ',
            'product_cat_id' => 'required|numeric ',
            'unit_id'        => 'required|numeric ',
            'id'             => 'required|numeric ',
            // 'image'=> 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'

        ]);

        $id                      = $request->input('id');
        $product                 = Product::findOrFail($id);
        $product->name           = $request->input('name');
        $product->product_cat_id = $request->input('product_cat_id');
        $product->unit_id        = $request->input('unit_id');
        $product->description    = $request->input('description');
        $product->cp          = $request->input('cp');
        $product->sp          = $request->input('sp');

        if ($request->hasFile('image')) {

            $img_ext = $request->image->getClientOriginalExtension();

            $checkExt = array("jpg", "png", "jpeg");

            if (in_array($img_ext, $checkExt)) {

                $imageName = '/img/' . time() . '.' . $request->image->getClientOriginalExtension();
                $request->image->move(public_path('img'), $imageName);
                $product->image = $imageName;

            } else {
                return response()->json([
                    'msg'    => 'Opps! My Back got cracked while working in Database',
                    'status' => 'error',
                ]);
            }

        }
        if ($product->update()) {
            return response()->json([
                'msg'    => 'You have successfully changed the information.',
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

        $product = Product::with('category')->with('unit')->findOrFail($id);

        if ($product) {
            return response()->json([
                'product' => $product,
                'status'  => 'success',
            ]);
        } else {
            return response()->json(['msg' => 'Opps! My Back got cracked while working in Database', 'status' => 'error']);
        }
    }

    public function search(Request $request)
    {

        $searchQuery = $request->searchQuery;

        // $queryResults=Stock::with()->where('name','like','%'.$searchQuery.'%')->with('unit')->get();

        $queryResults = Product::where('name', 'like', '%' . $searchQuery . '%')->with('unit')->get();

        return response()
            ->json([
                'search'       => 'ok',
                'queryResults' => $queryResults,
            ]);
    }

    public function destroy($id)
    {

        $product = Product::findOrFail($id);
        if ($product->delete()) {
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

    public function searchProducts(Request $request)
    {

        $searchKey = $request->input('searchQuery');
        if ($searchKey != '') {
            return ProductResource::collection(Product::where('name', 'like', '%' . $searchKey . '%')->with('unit')->paginate(8));
        } else {
            return response()->json([
                'msg'    => 'Error while retriving Products. No Data Supplied as key.',
                'status' => 'error',
            ]);
        }
    }

}
