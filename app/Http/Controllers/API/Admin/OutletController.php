<?php

namespace App\Http\Controllers\API\Admin;

use App\Helpers\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\Stock;
use Exception;
use Illuminate\Foundation\Exceptions\Renderer\Exception as RendererException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OutletController extends Controller
{
    public function index(Request $request)
    {
        try {
            $outlets = Outlet::with(['cashier:id,name,phone_number', 'stock' => function ($q) {
                $q->select('id', 'product_id', 'outlet_id', 'quantity'); // hanya ambil field ini
            }, 'stock.product:id,name'])->get(); // ambil hanya id dan name dari product

            // Mapping data agar output sesuai keinginan
            $data = $outlets->map(function ($outlet) {
                return [
                    'id' => $outlet->id,
                    'name' => $outlet->name,
                    'address' => $outlet->address,
                    'capacity' => $outlet->capacity,
                    'cashier' => [
                        'name' => $outlet->cashier->name ?? null,
                        'phone_number' => $outlet->cashier->phone_number ?? null,
                    ],
                    'stock' => $outlet->stock->map(function ($stock) {
                        return [
                            'quantity' => $stock->quantity,
                            'product_name' => $stock->product->name ?? null,
                        ];
                    }),
                ];
            });

            return APIResponse::success('Get data outlets success', $data, 200);
        } catch (Exception $e) {
            return APIResponse::error('error', $e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'name' => 'required|string',
                'address' => 'required',
                'capacity' => 'required|numeric'
            ]);

            $cashierInOutlet = Outlet::where('user_id', $request->user_id)->first();
            if ($cashierInOutlet) {
                throw ValidationException::withMessages([
                    'user_id' => ['the user is already in outlet']
                ]);
            }

            $newOutlet = Outlet::created($validated);
            DB::commit();

            return APIResponse::success('Created data success', $newOutlet);
        } catch (ValidationException $e) {
            return APIResponse::error('Validation', $e->errors(), 422);
        } catch (Exception $e) {
            DB::rollBack();
            return APIResponse::error('error', $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $outlet = Outlet::with(['cashier:id,name,phone_number'])->findOrFail($id);

            // Ambil semua stok (baik in maupun out) dari outlet
            $stocks = Stock::where('outlet_id', $id)
                ->with('product:id,name,price')
                ->get();

            // Group by product_id lalu hitung stok akhir
            $groupedStocks = $stocks->groupBy('product_id')->map(function ($items) {
                $firstItem = $items->first();
                $stockIn = $items->where('type', 'in')->sum('quantity');
                $stockOut = $items->where('type', 'out')->sum('quantity');
                $available = $stockIn - $stockOut;

                return [
                    'product_name' => $firstItem->product->name ?? null,
                    'product_price' => $firstItem->product->price ?? null,
                    'quantity' => $available,
                ];
            })->values(); // reset index

            $data = [
                'id' => $outlet->id,
                'name' => $outlet->name,
                'address' => $outlet->address,
                'capacity' => $outlet->capacity,
                'cashier' => [
                    'name' => $outlet->cashier->name ?? null,
                    'phone_number' => $outlet->cashier->phone_number ?? null,
                ],
                'stock' => $groupedStocks,
            ];

            return APIResponse::success('Get data by id success', $data);
        } catch (\Exception $e) {
            return APIResponse::error('Error', $e->getMessage(), 500);
        }
    }

    public function update(Request $request, Outlet $outlet)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'name' => 'required|string',
                'address' => 'required',
                'capacity' => 'required|numeric'
            ]);

            $cashierInOutlet = Outlet::where('user_id', $request->user_id)->first();
            if ($cashierInOutlet && $request->user_id != $outlet->user_id) {
                throw ValidationException::withMessages([
                    'user_id' => ['the user is already in outlet']
                ]);
            }

            $outlet->update($validated);
            DB::commit();

            return APIResponse::success('update data outlet success');
        } catch (ValidationException $e) {
            return APIResponse::error('Validation', $e->errors(), 422);
        } catch (Exception $e) {
            DB::rollBack();
            return APIResponse::error('error', $e->getMessage(), 500);
        }
    }

    public function destroy($outlet)
    {
        DB::beginTransaction();
        try {

            $data = Outlet::where('id', $outlet)->first();
            if (!$data) {
                return APIResponse::error('error', 'Outlet not found', 404);
            }
            $data->delete();
            DB::commit();

            return APIResponse::success('deleted data outlet success');
        } catch (Exception $e) {
            DB::rollBack();
            return APIResponse::error('error', $e->getMessage(), 500);
        }
    }
}
