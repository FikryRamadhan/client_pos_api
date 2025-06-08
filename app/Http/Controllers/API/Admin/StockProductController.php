<?php

namespace App\Http\Controllers\API\Admin;

use App\Helpers\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\Stock;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StockProductController extends Controller
{


    public function store(Request $request){
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'outlet_id' => 'required|exists:outlets,id',
                'quantity' => 'required|numeric',
                'note' => 'required|string',
            ]);

            $outlet = Outlet::where('id', $validated['outlet_id'])->first();
            $currentStock = Stock::where('outlet_id', $validated['outlet_id'])->sum('quantity');

            if (($currentStock + $validated['quantity']) > $outlet->capacity) {
                return APIResponse::error('Error', 'Stock exceeds outlet capacity');
            }

            $oldData = Stock::where('product_id', $validated['product_id'])
                           ->where('outlet_id', $validated['outlet_id'])
                           ->first();

            if ($oldData) {
                if (($currentStock + $validated['quantity']) > $outlet->capacity) {
                    return APIResponse::error('Error', 'Stock update would exceed outlet capacity');
                }
                $oldData->quantity += $validated['quantity'];
                $oldData->note = $validated['note'];
                $oldData->save();
                return APIResponse::success('Stock updated successfully', $oldData);
            }

            $stock = Stock::create([
                'product_id' => $validated['product_id'],
                'outlet_id' => $validated['outlet_id'],
                'quantity' => $validated['quantity'],
                'type' => 'in',
                'note' => $validated['note']
            ]);

            $finalStock = Stock::where('outlet_id', $validated['outlet_id'])->sum('quantity');
            if ($finalStock > $outlet->capacity) {
                $stock->delete();
                return APIResponse::error('Error', 'Final stock would exceed outlet capacity');
            }

            return APIResponse::success('Stock created successfully', $stock);
        } catch (Exception $e) {
            return APIResponse::error('Error', $e->getMessage());
        }
    }
}
