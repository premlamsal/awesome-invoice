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

        return response()
            ->json(['data' => $data]);
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
}
