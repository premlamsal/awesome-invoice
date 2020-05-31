<?php

namespace App\Http\Controllers;

use App\Http\Resources\Category as CategoryResource;
use App\ProductCategory as Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct()
    {

        $this->middleware('auth:api');

    }

    public function index()
    {
        $this->authorize('hasPermission', 'view_categories');

        return CategoryResource::collection(Category::paginate(8));
    }

    public function store(Request $request)
    {
        $this->authorize('hasPermission', 'add_category');

        $this->validate($request, [
            'name'        => 'required|string|max:10',
            'description' => 'required|string|max:100',
        ]);

        $category              = new Category();
        $category->name        = $request->input('name');
        $category->description = $request->input('description');

        if ($category->save()) {
            return response()->json(['msg' => 'You have successfully added the information.', 'status' => 'success']);
        } else {
            return response()->json(['msg' => 'Opps! My Back got cracked while working in Database', 'status' => 'error']);
        }

    }

    public function show($id)
    {

        $this->authorize('hasPermission', 'show_category');


        $category = Category::findOrFail($id);

        if ($category) {
            return response()->json([
                'category' => $category,
                'status'   => 'success',
            ]);
        } else {
            return response()->json(['msg' => 'Opps! My Back got cracked while working in Database', 'status' => 'error']);
        }

    }

    public function update(Request $request)
    {
        $this->authorize('hasPermission', 'update_category');


        $this->validate($request, [
            'name'        => 'required|string|max:10',
            'description' => 'required|string|max:100',
        ]);

        $id                    = $request->input('id');
        $category              = Category::findOrFail($id);
        $category->name        = $request->input('name');
        $category->description = $request->input('description');
        if ($category->save()) {
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
        $this->authorize('hasPermission', 'delete_category');


        $category = Category::findOrFail($id);
        if ($category->delete()) {
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

    public function searchCategories(Request $request)
    {
        $this->authorize('hasPermission', 'search_categories');

        $searchKey = $request->input('searchQuery');
        if ($searchKey != '') {
            return CategoryResource::collection(Category::where('name', 'like', '%' . $searchKey . '%')->paginate(8));
        } else {
            return response()->json([
                'msg'    => 'Error while retriving Categories. No Data Supplied as key.',
                'status' => 'error',
            ]);
        }
    }
}
