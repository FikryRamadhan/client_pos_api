<?php

namespace App\Http\Controllers\API\Admin;

use App\Helpers\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DashboardController extends Controller
{
    public function laporan(Request $request)
    {
        try {
            $filter = $request->query("filter");

            switch ($filter) {
                case 'transaction':
                    $data = $this->getTrasaction($request);
                    break;

                case 'stock-product':
                    $data = $this->getStockProduct($request);
                    break;
                default:
                    throw ValidationException::withMessages([
                        'filter' => 'filter is not valid'
                    ]);
                    break;
            }

            return APIResponse::success('get data ' . $filter . ' sucsess', $data);
        } catch (ValidationException $v) {
            return APIResponse::error('Validation', $v->errors(), 422);
        } catch (Exception $e) {
            return APIResponse::error($e->getMessage());
        }
    }


    private function getTrasaction($request)
    {
        if ($request->user()->role == 'cashier') {
            $transactions = Transaction::where('cashier_id', $request->user()->id)->get();
        } else {
            $transactions = Transaction::all();
        }


        if ($transactions->isEmpty()) {
            return APIResponse::error('Data transaction in empty', [], 404);
        }

        return [
            'monthly' => $transactions->groupBy(fn($item) => $item->created_at->format('F'))->map->count(),
            'yearly' => $transactions->groupBy(fn($item) => $item->created_at->format('Y'))->map->count(),
            'summary' => [
                'this_month' => $transactions->where('created_at', '>=', now()->startOfMonth())->sum('total'),
                'this_year' => $transactions->where('created_at', '>=', now()->startOfYear())->sum('total'),
            ],
            'income_total' => $transactions->sum('total_price')
        ];
    }

    private function getStockProduct($request)
    {
        $result = [];
        if ($request->user()->role == 'cashier') {
            $outlets = Outlet::where('user_id', $request->user()->id)
                ->with(['stock.product', 'cashier'])
                ->get();
            foreach ($outlets as $outlet) {
                $stocks = [];

                foreach ($outlet->stock as $stock) {
                    if ($stock->product) {
                        $stocks[] = [
                            'product_name' => $stock->product->name,
                            'stock'        => $stock->quantity,
                        ];
                    }
                }

                $result[] = [
                    'id'        => $outlet->id,
                    'user_id'   => $outlet->cashier->name,
                    'name'      => $outlet->name,
                    'address'   => $outlet->address,
                    'capacity'  => $outlet->capacity,
                    'stock'     => $stocks
                ];
            }
        } else {
            $products = Product::get();
            foreach ($products as $product) {
                $result[] = [
                    'product_name' => $product->name,
                    'stock'       => $product->stock,
                    'price'        => $product->price,
                ];
            }
        }
        return $result;
    }
}
