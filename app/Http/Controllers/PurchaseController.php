<?php

namespace App\Http\Controllers;

use App\Http\Resources\Purchase as PurchaseResource;
use App\Purchase;
use App\PurchaseDetail;
use App\Stock;
use App\StockHistory;
use App\Store;
use Auth;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct()
    {

        $this->middleware('auth:api');

    }

    public function index()
    {
        $this->authorize('hasPermission', 'view_purchases');

        return PurchaseResource::collection(Purchase::with('purchaseDetail')->orderBy('updated_at', 'desc')->paginate(8));
    }

    public function store(Request $request)
    {
        $this->authorize('hasPermission', 'add_purchase');

        //validation
        $this->validate($request, [

            'info.note'            => 'required | string |max:200',
            'info.supplier_name'   => 'required | string| max:200',
            'info.due_date'        => 'required | date',
            'info.purchase_date'   => 'required | date',

            'info.discount'        => 'required | numeric| max:200',

            'items.*.product_id'   => 'required | numeric|max: 100',
            'items.*.product_name' => 'required | string |max:200',
            'items.*.price'        => 'required | numeric',
            'items.*.quantity'     => 'required | numeric',

        ], [
            //custom validation message for each module
            'required' => 'This field can\'t be blank',
            'numeric'  => 'This field only accepts numeric',
            'string'   => 'This field only accepts string',
            'max'      => 'This field should not exceed :max characters',
            'min'      => 'This field should contain minimum :min characters',
            'date'     => 'This field should contain valid date',
        ]);

        $store = Store::findOrFail(Auth::user()->store_id);

        $store_tax_percentage = $store->tax_percentage;

        $store_tax = $store_tax_percentage / 100;

        //old purchase id
        $purchase_id_count = $store->purchase_id_count;

        //explode invoice id from database

        $custom_purchase_id = explode('-', $purchase_id_count);

        $custom_purchase_id[1] = $custom_purchase_id[1] + 1; //increase purchase

        //new custom_purchase_id
        $new_count_purchase_id = implode('-', $custom_purchase_id);

        //collecting data
        $items = collect($request->items)->transform(function ($item) {
            $item['line_total'] = $item['quantity'] * $item['price'];
            return new PurchaseDetail($item);
        });

        if ($items->isEmpty()) {
            return response()
                ->json([
                    'items_empty' => 'One or more Item is required.',
                ], 422);
        }

        $data = $request->info;

        $data['sub_total'] = $items->sum('line_total');

        $data['tax_amount'] = $data['sub_total'] * $store_tax;

        $data['grand_total'] = $data['sub_total'] + $data['tax_amount'] - $data['discount'];

        $data['custom_purchase_id'] = $new_count_purchase_id;

        $purchase = Purchase::create($data);

        $purchase->purchaseDetail()->saveMany($items);

        //for inserting in stock and altering if already has one initialized stock and previous stock
        $items = collect($request->items);

        $countItems = count($items);

        $timeStamp = now();

        $jsonResponse = array();

        for ($i = 0; $i < $countItems; $i++) {

            $p_id = $items[$i]['product_id'];

            $stock = Stock::where('product_id', $p_id);

            //retirving current product-> stock quantity
            $in_stock_quantity = $stock->value('quantity');

            //get stock id
            $stock_id = $stock->value('id');

            //adding current stock with new purchased product quantity
            $new_stock_quantity = $in_stock_quantity + $items[$i]['quantity'];

            $stock = Stock::findOrFail($stock_id);

            $stock->quantity = $new_stock_quantity;

            $stock->unit_id = $items[$i]['unit_id'];

            $stock->created_at = $timeStamp;

            $stock->updated_at = $timeStamp;

            if ($stock->save()) {

                $today = date('Y-m-d');

                $StockHistoryID = StockHistory::where('date', '=', $today)->where('product_id', $p_id)->value('id');

                if ($StockHistoryID != null) {

                    $StockHistory = StockHistory::findOrFail($StockHistoryID);

                    $StockHistory->quantity = $new_stock_quantity;

                    if ($StockHistory->save()) {

                        //set current purchase_id_count to store table
                        $store->purchase_id_count = $new_count_purchase_id;
                        if ($store->save()) {

                            $jsonResponse = ['msg' => 'Saved successfully', 'status' => 'success'];

                        }
                    }

                } else {
                    $StockHistory = new StockHistory();

                    $StockHistory->product_id = $p_id;

                    $StockHistory->quantity = $new_stock_quantity;

                    $StockHistory->date = $today;

                    if ($StockHistory->save()) {

                        //set current purchase_id_count to store table
                        $store->purchase_id_count = $new_count_purchase_id;
                        if ($store->save()) {

                            $jsonResponse = ['msg' => 'Saved successfully', 'status' => 'success'];

                        }
                    }
                }
            } else {

                $jsonResponse = ['msg' => 'Failed Saving the Data to the Stock.', 'status' => 'error'];

            }

        }

        return response()->json($jsonResponse);
    }

    public function update(Request $request)
    {
        $this->authorize('hasPermission', 'edit_purchase');

        // //validation
        $this->validate($request, [

            'info.note'          => 'required | string |max:200',
            'info.supplier_name' => 'required | string| max:200',
            'info.due_date'      => 'required | date',
            'info.purchase_date' => 'required | date',

            'info.discount'      => 'required | numeric| max:200',

        ], [
            //custom validation message for each module
            'required' => 'This field can\'t be blank',
            'numeric'  => 'This field only accepts numeric',
            'string'   => 'This field only accepts string',
            'max'      => 'This field should not exceed :max characters',
            'min'      => 'This field should contain minimum :min characters',
            'date'     => 'This field should contain valid date',
        ]);

        $id = $request->id;

        $store = Store::findOrFail(Auth::user()->store_id);

        $store_tax_percentage = $store->tax_percentage;

        $store_tax = $store_tax_percentage / 100;

        $purchase = Purchase::findOrFail($id);

        $data = $request->info;

        $purchase->update($data);

        return response()->json(['msg' => 'You have successfully updated the Purchase.', 'status' => 'success']);

    }

    public function returnPurchase(Request $request)
    {
        $this->authorize('hasPermission', 'return_purchase');

        // //validation
        $this->validate($request, [

            'info.note'            => 'required | string |max:200',
            'info.supplier_name'   => 'required | string| max:200',
            'info.due_date'        => 'required | date',
            'info.purchase_date'   => 'required | date',

            'info.discount'        => 'required | numeric| max:200',

            'items.*.product_name' => 'required | string |max:200',
            'items.*.price'        => 'required | numeric',
            'items.*.quantity'     => 'required | numeric',

        ], [
            //custom validation message for each module
            'required' => 'This field can\'t be blank',
            'numeric'  => 'This field only accepts numeric',
            'string'   => 'This field only accepts string',
            'max'      => 'This field should not exceed :max characters',
            'min'      => 'This field should contain minimum :min characters',
            'date'     => 'This field should contain valid date',
        ]);

        $id = $request->id; //purchase id

        $purchase = Purchase::findOrFail($id);

        $items = collect($request->items)->transform(function ($item) {
            $item['line_total'] = $item['quantity'] * $item['price'];
            return new PurchaseDetail($item);
        });

        if ($items->isEmpty()) {
            return response()
                ->json([
                    'items_empty' => ['One or more Item is required.'],
                ], 422);
        }

        $data = $request->info;

        $data['sub_total']   = $items->sum('line_total');
        $data['tax_amount']  = $data['sub_total'] * $store_tax;
        $data['grand_total'] = $data['sub_total'] + $data['tax_amount'] - $data['discount'];

        //for inserting in stock and altering if already has one initialized stock and previous stock
        $items_raw = collect($request->items); //collecting new items from the submit form

        $countItemsNew = count($items_raw); //get new items length of elements

        $timeStamp = now();

        //retriving old purchase records for the references
        $purchaseDetail_old = PurchaseDetail::where('purchase_id', $id)->get(); //get old data from the database

        $countItemsOld = count($purchaseDetail_old); //get old items length of elements

        for ($i = 0; $i < $countItemsOld; $i++) {

            $p_id = $items[$i]['product_id'];

            $stock = Stock::where('product_id', $p_id);

            //retirving current product-> stock quantity
            $in_stock_quantity = $stock->value('quantity');

            //get stock id
            $stock_id = $stock->value('id');

            //adding current stock with new purchased product quantity
            if ($in_stock_quantity >= $items[$i]['quantity']) {

                $new_stock_quantity = $in_stock_quantity - $items[$i]['quantity'];

                $stock = Stock::findOrFail($stock_id);

                $stock->quantity = $new_stock_quantity;

                $stock->unit_id = $items[$i]['unit_id'];

                $stock->created_at = $timeStamp;

                $stock->updated_at = $timeStamp;

                if ($stock->save()) {

                    $purchase->update($data);

                    PurchaseDetail::where('purchase_id', $purchase->id)->delete();

                    $purchase->purchaseDetail()->saveMany($items);

                    return response()->json(['msg' => 'You have successfully return the Purchase.', 'status' => 'success']);

                }
            }
        }
        return response()->json(['msg' => 'Failed while returning purchase. Check your stock quanity.', 'status' => 'error']);
    }

    public function show($id)
    {

        $this->authorize('hasPermission', 'show_purchase');

        // Get Purchase

        $Purchase = Purchase::with('purchaseDetail.product.unit')->with('supplier')->findOrFail($id);

        return response()
            ->json([
                'purchase' => $Purchase,
                'message'  => "OK",
            ]);

    }

    public function destroy($id)
    {
        $this->authorize('hasPermission', 'delete_purchase');

        // Get Purchase
        $Purchase = Purchase::findOrFail($id);

        //get purchase details
        $purchaseDetail = PurchaseDetail::where('purchase_id', $id)->get();

        $countItems = count($purchaseDetail);

        // $timeStamp=now();

        for ($i = 0; $i < $countItems; $i++) {
            //get product id from each purchase details
            $p_id = $purchaseDetail[$i]['product_id'];

            $p_qty = $purchaseDetail[$i]['quantity'];

            //finding stock to decrease the quantity of this purchase
            $stock = Stock::where('product_id', $p_id);

            $stock_id = $stock->value('id');

            $stock_qty = $stock->value('quantity');

            $stock = Stock::findOrFail($stock_id);

            if ($stock_qty >= $p_qty) {

                $stock->quantity = $stock_qty - $p_qty;

            }
            if ($stock->save()) {

                if ($Purchase->delete()) {

                    return response()->json([
                        'msg'    => 'successfully Deleted',
                        'status' => 'success',
                    ]);

                } else {
                    return response()->json([
                        'msg'    => 'Delete Failed',
                        'status' => 'error',
                    ]);
                }
            }

        }

    }
    public function searchPurchases(Request $request)
    {
        $this->authorize('hasPermission', 'search_purchases');

        $searchKey = $request->input('searchQuery');
        if ($searchKey != '') {
            return PurchaseResource::collection(Purchase::where('customer_name', 'like', '%' . $searchKey . '%')->get());
        } else {
            return response()->json([
                'msg'    => 'Error while retriving Purchases. No Data Supplied as key.',
                'status' => 'error',
            ]);
        }
    }

}
