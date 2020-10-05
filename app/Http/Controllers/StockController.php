<?php
namespace App\Http\Controllers;

use App\Http\Resources\Stock as StockResource;
use App\Stock;
use App\StockHistory;
use Illuminate\Http\Request;

class StockController extends Controller
{

    public function __construct()
    {

        $this->middleware('auth:api');
    }

    public function index()
    {
        $this->authorize('hasPermission', 'view_stocks');

        // return StockResource::collection(Stock::with('product')->with('unit')->paginate(8));
        return StockResource::collection(Stock::with('product.unit')
                ->paginate(8));

        // $data= DB::table('stocks as s')
        //        ->select('s.unit_id')
        //        ->leftJoin('stocks as s1', function ($join) {
        //              $join->on('s.product_id','=','s1.product_id')
        //                   ->whereRaw(DB::raw('s.created_at < s1.created_at'));
        //         })
        //         ->join('products','s.product_id','=','products.id')
        //         ->join('units','s.unit_id','=','units.id')
        //        ->whereNull('s1.id')

        //        ->get();
        // -----------------------------------------------------------------------------
        // $data= DB::table('stocks as s')
        //     ->select('s.*','products.name','units.short_name')
        //     ->leftJoin('stocks as s1', function ($join) {
        //           $join->on('s.product_id','=','s1.product_id')
        //           ->whereRaw(DB::raw('s.id < s1.id'));
        //      })
        //     ->whereNull('s1.id')

        //      ->join('products','s.product_id','=','products.id')
        //      ->join('units','s.unit_id','=','units.id')
        //      ->paginate(8);
        // ---------------------------------------------------------------------------------

        // $data = DB::table('stocks')
        // ->select('stocks.*',)

        // ->join('units','stocks.unit_id','=','units.id')
        // ->get();

        // return $data;
        return response()
            ->json(['data' => $data]);
    }

    public function store(Request $request)
    {

        // $this->validate($request, [
        //  'name' => 'required|regex:/^[\pL\s\-]+$/u',
        //  'address' => 'required|string|max:200',
        //  'phone' => 'required|unique:customers,phone|digits:10',
        //  'details' => 'required|string|max:400'
        // ]);
        //    $customer=new Customer();
        //    $customer->name=$request->input('name');
        //    $customer->address=$request->input('address');
        //    $customer->phone=$request->input('phone');
        //    $customer->details=$request->input('details');
        //    if($customer->save()){
        //        return response()->json([
        //            'msg'=>'Customer added successfully',
        //            'status'=>'success'
        //        ]);
        //    }
        //    else{
        //        return response()->json([
        //            'msg'=>'Error while adding customer',
        //            'status'=>'error'
        //        ]);
        //    }

    }

    public function update(Request $request)
    {

        // $this->validate($request, [
        //   'name' => 'required|regex:/^[\pL\s\-]+$/u',
        //   'address' => 'required|string|max:200',
        //   'phone' => 'required|digits:10',
        //   'details' => 'required|string|max:400'
        // ]);
        // $id=$request->input('id');//get id from edit modal
        // $customer=Customer::findOrFail($id);
        // $customer->name=$request->input('name');
        // $customer->address=$request->input('address');
        // $customer->phone=$request->input('phone');
        // $customer->details=$request->input('details');
        // if($customer->save()){
        //     return response()->json([
        //         'msg'=>'Customer update successfully',
        //         'status'=>'success'
        //     ]);
        // }
        // else{
        //     return response()->json([
        //         'msg'=>'Error while updating customer',
        //         'status'=>'error'
        //     ]);
        // }

    }

    public function destroy($id)
    {

        $stock = Stock::findOrFail($id);
        if ($stock->delete()) {
            return response()
                ->json(['msg' => 'successfully Deleted', 'status' => 'success']);
        } else {
            return response()
                ->json(['msg' => 'Error while deleting data', 'status' => 'error']);
        }

    }

    public function show($id)
    {

        $stock = Stock::findOrFail($id);
        if ($stock->stock) {
            return response()
                ->json(['customer' => $customer, 'status' => 'success']);
        } else {
            return response()->json(['msg' => 'Error while retriving Customer', 'status' => 'error']);
        }
    }
    public function checkQuantityInStock(Request $request)
    {

        $product_id = $request->input('product_id');

        $quantity = $request->input('quantity');

        $stock_quantity = Stock::where('product_id', $product_id)->value('quantity');

        if ($stock_quantity >= $quantity && $quantity > 0) {

            return response()->json(['status' => 1, 'title' => 'Info', 'msg' => 'Quantity changed.', 'quantity' => $stock_quantity]);
        } else {

            return response()->json(['status' => 0, 'title' => 'Opps!!', 'msg' => 'You have only ' . $stock_quantity . ' in stock.', 'quantity' => $stock_quantity]);
        }

    }

    public function stockHistory(Request $request)
    {
        $this->authorize('hasPermission', 'view_stock_history');

        $dateFrom = $request->input('dateFrom');

        $dateTo = $request->input('dateTo');

        $data = StockHistory::whereBetween('date', [$dateFrom, $dateTo])->with('product')->get();

        return $data;
    }

    public function searchStock(Request $request)
    {
        $searchKey = $request->input('searchQuery');

        $stock = Stock::whereHas('product', function ($query) use ($searchKey) {
            $query->where('custom_product_id', 'like', '%' . $searchKey . '%');
        })->with('product.unit')->get();

        return response()->json(['msg' => 'success', 'queryResults' => $stock]);
    }
}
