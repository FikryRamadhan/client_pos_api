<?php

namespace App\Http\Controllers\API\Cashier;

use App\Helpers\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with(['cashier', 'customer', 'transactionDetails.product'])->get();
        return APIResponse::success('List of transactions retrieved successfully.', $transactions);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'payment_method' => 'required|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return APIResponse::error('Validation failed.', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $outletId = $request->user()->outlet->id;
            $total = 0;
            $productsCache = [];

            foreach ($request->products as $item) {
                $productId = $item['product_id'];
                $quantity = $item['quantity'];

                $stockIn = Stock::where('product_id', $productId)->where('outlet_id', $outletId)->where('type', 'in')->sum('quantity');
                $stockOut = Stock::where('product_id', $productId)->where('outlet_id', $outletId)->where('type', 'out')->sum('quantity');

                $currentStock = $stockIn - $stockOut;

                $productsCache[$productId] = Product::find($productId);
                $product = $productsCache[$productId];

                if ($currentStock < $quantity) {
                    return APIResponse::error("Stock is insufficient for product: \"$product->name\" at outlet ID $outletId.", [], 422);
                }
            }

            $transaction = Transaction::create([
                'cashier_id' => Auth::id(),
                'customer_id' => $request->customer_id,
                'total_price' => 0, // sementara
                'payment_method' => $request->payment_method,
            ]);

            foreach ($request->products as $item) {
                $product = $productsCache[$item['product_id']];
                $subtotal = $product->price * $item['quantity'];
                $total += $subtotal;

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'subtotal' => $subtotal,
                ]);
            }

            $transaction->update(['total_price' => $total]);

            foreach ($request->products as $item) {
                Stock::create([
                    'product_id' => $item['product_id'],
                    'outlet_id' => $request->user()->outlet->id,
                    'type' => 'out',
                    'quantity' => $item['quantity'],
                    'note' => 'Sale transaction ID: ' . $transaction->id,
                ]);
            }

            DB::commit();

            return APIResponse::success('Transaction created with multiple products.', $transaction, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return APIResponse::error('Transaction failed.', ['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $transaction = Transaction::with(['cashier', 'customer', 'transactionDetails.product'])->find($id);

        if (!$transaction) {
            return APIResponse::error('Transaction not found.', [], 404);
        }

        return APIResponse::success('Transaction detail retrieved.', $transaction);
    }

    // public function destroy($id)
    // {
    //     $transaction = Transaction::find($id);

    //     if (!$transaction) {
    //         return APIResponse::error('Transaction not found.', [], 404);
    //     }

    //     $transaction->delete();

    //     return APIResponse::success('Transaction deleted successfully.');
    // }
}
