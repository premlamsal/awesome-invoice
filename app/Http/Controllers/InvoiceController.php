<?php

namespace App\Http\Controllers;

use App\Http\Resources\Invoice as InvoiceResource;
use App\Invoice;
use App\InvoiceDetail;
use App\Stock;
use App\StockHistory;
use App\Store;
use Auth;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{

    public function __construct()
    {

        $this->middleware('auth:api');

    }
    public function index()
    {

        /*---------------------------------------------------------
        This block will only return non-realtionship model

        // Get Invoices
        // $Invoices= Invoice::orderBy('created_at', 'desc')->paginate(3);

        //Return collection of Invoices as a resource
        // return InvoiceResource::collection($Invoices);
        -----------------------------------------------------------*/
        $this->authorize('hasPermission', 'view_invoices');

        return InvoiceResource::collection(Invoice::with('invoiceDetail')->orderBy('updated_at', 'desc')->paginate(8));
    }

    public function store(Request $request)
    {
        $this->authorize('hasPermission', 'add_invoice');

        // //validation
        $this->validate($request, [

            'info.note'            => 'required | string |max:230',
            'info.customer_name'   => 'required | string| max:200',
            'info.due_date'        => 'required | date',
            'info.invoice_date'    => 'required | date',

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

        //old invoice id
        $invoice_id_count = $store->invoice_id_count;

        //explode invoice id from database

        $custom_invoice_id = explode('-', $invoice_id_count);

        $custom_invoice_id[1] = $custom_invoice_id[1] + 1; //increase invoice

        //new custom_invoice_id
        $new_count_invoice_id = implode('-', $custom_invoice_id);

        //collecting data
        $items = collect($request->items)->transform(function ($item) {
            $item['line_total'] = $item['quantity'] * $item['price'];
            return new InvoiceDetail($item);
        });

        if ($items->isEmpty()) {
            return response()
                ->json([
                    'items_empty' => 'One or more Item is required.',
                ], 422);
        }

        $data                      = $request->info;
        $data['sub_total']         = $items->sum('line_total');
        $data['tax_amount']        = $data['sub_total'] * $store_tax;
        $data['grand_total']       = $data['sub_total'] + $data['tax_amount'] - $data['discount'];
        $data['custom_invoice_id'] = $new_count_invoice_id;

        $invoice = Invoice::create($data);

        $invoice->invoiceDetail()->saveMany($items);

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

            if ($in_stock_quantity >= $items[$i]['quantity'] && $items[$i]['quantity'] > 0) {

                //adding current stock with new purchased product quantity
                $new_stock_quantity = $in_stock_quantity - $items[$i]['quantity'];

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

                            //set current invoice_id_count to store table
                            $store->invoice_id_count = $new_count_invoice_id;
                            if ($store->save()) {

                                $jsonResponse = ['msg' => 'You have successfully created the Invoice.', 'status' => 'success'];

                            }
                        }

                    } else {
                        $StockHistory = new StockHistory();

                        $StockHistory->product_id = $p_id;

                        $StockHistory->quantity = $new_stock_quantity;

                        $StockHistory->date = $today;

                        if ($StockHistory->save()) {

                            //set current invoice_id_count to store table
                            $store->invoice_id_count = $new_count_invoice_id;
                            if ($store->save()) {
                                $jsonResponse = ['msg' => 'You have successfully created the Invoice.', 'status' => 'success'];

                            }
                        }
                    }
                } else {

                    $jsonResponse = ['msg' => 'Failed Saving the Data to the Stock.', 'status' => 'error'];

                }

            } else {
                $jsonResponse = ['msg' => 'You dont have Stock.', 'status' => 'error'];
            }

        }

        return response()->json($jsonResponse);

    }
    public function update(Request $request)
    {
        $this->authorize('hasPermission', 'edit_invoice');

        // //validation
        $this->validate($request, [

            'info.note'          => 'required | string |max:200',
            'info.customer_name' => 'required | string| max:200',
            'info.due_date'      => 'required | date',
            'info.invoice_date'  => 'required | date',

            'info.discount'      => 'required | numeric| max:200',

            // 'items.*.product_name' => 'required | string |max:200',
            // 'items.*.price'        => 'required | numeric',
            // 'items.*.quantity'     => 'required | numeric',

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

        $invoice = Invoice::findOrFail($id);

        // $items = collect($request->items)->transform(function($item) {
        //     $item['line_total'] = $item['quantity'] *$item['price'];
        //     return new InvoiceDetail($item);
        // });

        // if ($items->isEmpty()) {
        //     return response()
        //         ->json([
        //             'items_empty' => ['One or more Item is required.'],
        //         ], 422);
        // }

        $data = $request->info;

        // $data['sub_total']   = $items->sum('line_total');
        // $data['tax_amount']  = $data['sub_total'] * $store_tax;
        // $data['grand_total'] = $data['sub_total'] + $data['tax_amount'] - $data['discount'];

        $invoice->update($data);

        // InvoiceDetail::where('invoice_id', $invoice->id)->delete();

        // $invoice->invoiceDetail()->saveMany($items);

        return response()->json(['msg' => 'You have successfully updated the Invoice.', 'status' => 'success']);

    }

    public function returnInvoice(Request $request)
    {
        $this->authorize('hasPermission', 'return_invoice');

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

        $id = $request->id; //invoice id

        $invoice = Invoice::findOrFail($id);

        $items = collect($request->items)->transform(function ($item) {
            $item['line_total'] = $item['quantity'] * $item['price'];
            return new InvoiceDetail($item);
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
        $invoiceDetail_old = InvoiceDetail::where('invoice_id', $id)->get(); //get old data from the database

        $countItemsOld = count($invoiceDetail_old); //get old items length of elements

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

                    $invoice->update($data);

                    InvoiceDetail::where('invoice_id', $invoice->id)->delete();

                    $invoice->InvoiceDetail()->saveMany($items);

                    return response()->json(['msg' => 'You have successfully return the invoice.', 'status' => 'success']);

                }
            }
        }
        return response()->json(['msg' => 'Failed while returning invoice. Check your stock quanity.', 'status' => 'error']);
    }

    public function show($id)
    {

        $this->authorize('hasPermission', 'show_invoice');

        // Get Invoice
        $Invoice = Invoice::with('invoiceDetail.product.unit')->with('customer')->findOrFail($id);

        return response()
            ->json([
                'invoice' => $Invoice,
                'message' => "OK",
            ]);

    }

    public function destroy($id)
    {
        $this->authorize('hasPermission', 'delete_invoice');

        // Get Purchase
        $Invoice = Invoice::findOrFail($id);

        //get purchase details
        $invoiceDetail = InvoiceDetail::where('invoice_id', $id)->get();

        $countItems = count($invoiceDetail);

        // $timeStamp=now();
        if ($countItems != 0) {

            for ($i = 0; $i < $countItems; $i++) {
                //get product id from each purchase details
                $p_id = $invoiceDetail[$i]['product_id'];

                $p_qty = $invoiceDetail[$i]['quantity'];

                //finding stock to decrease the quantity of this purchase
                $stock = Stock::where('product_id', $p_id);

                $stock_id = $stock->value('id');

                $stock_qty = $stock->value('quantity');

                $stock = Stock::findOrFail($stock_id);

                if ($stock_qty >= 0) {

                    $stock->quantity = $stock_qty + $p_qty;

                }
                if ($stock->save()) {

                    if ($Invoice->delete()) {

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

        } else {

            if ($Invoice->delete()) {

                return response()->json([
                    'msg'    => 'Successfully Deleted',
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
    public function searchInvoices(Request $request)
    {
        $this->authorize('hasPermission', 'search_invoices');

        $searchKey = $request->input('searchQuery');
        if ($searchKey != '') {

            // $queryResults=Estimate::where('customer_name','like','%'.$searchQuery.'%')->get();
            return InvoiceResource::collection(Invoice::where('customer_name', 'like', '%' . $searchKey . '%')->paginate(8));
        } else {
            return response()->json([
                'msg'    => 'Error while retriving Invoices. No Data Supplied as key.',
                'status' => 'error',
            ]);
        }
    }
    public function changeInvoiceStatus(Request $request)
    {

        $this->authorize('hasPermission', 'edit_invoice');
        
        $key = $request->input('key');

        $value = $request->input('value');

        $invoice             = Invoice::findOrFail($key);
        $invoice->status     = $value;
        $invoice->updated_at = time();

        if ($invoice->save()) {
            return response()->json(['status' => 'success', 'msg' => $invoice->custom_invoice_id.' changed to ' . $value . '']);
        } else {

            return response()->json(['status' => 'failed', 'msg' => 'Invoice status changed Failed']);

        }

    }

}
