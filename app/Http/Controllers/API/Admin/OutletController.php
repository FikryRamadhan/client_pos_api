<?php

namespace App\Http\Controllers\API\Admin;

use App\Helpers\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\Outlet;
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
            $data = Outlet::with('cashier', 'stock.product')->get();
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
            $data = Outlet::with('cashier')->find($id);

            return APIResponse::success('Get data by id sucsess', $data);
        } catch (Exception $e) {
            return APIResponse::error('error', $e->getMessage(), 500);
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

    public function destroy(Outlet $outlet){
        DB::beginTransaction();
        try{
            $outlet->delete();
            DB::commit();

             return APIResponse::success('deleted data outlet success');
        } catch (Exception $e){
            DB::rollBack();
             return APIResponse::error('error', $e->getMessage(), 500);
        }
    }
}
