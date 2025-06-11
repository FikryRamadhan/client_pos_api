<?php

namespace App\Http\Controllers\API\Admin;

use App\Helpers\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $data = Product::select('id', 'name', 'price', 'stock', 'description')->get();

            return APIResponse::success('Get data prosuct sucsess', $data);
        } catch (Exception $e) {
            return APIResponse::error('error', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'name' => 'required|unique:products,name',
                'price' => 'required',
                'description' => 'required',
            ]);

            $newData = Product::create($validated);
            DB::commit();


            return APIResponse::success('Created data success', $newData);
        } catch (ValidationException $e) {
            return APIResponse::error('Validation', $e->errors(), 422);
        } catch (Exception $e) {
            DB::rollBack();
            return APIResponse::error('error', $e->getMessage(), 500);
        }
    }

    public function show(Product $product)
    {
        try {
            return APIResponse::success('get data success', $product);
        } catch (Exception $e) {
            return APIResponse::error('error', $e->getMessage(), 500);
        }
    }

    public function update(Request $request, Product $product)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'name' => 'required|unique:products,name,'.$product->name,
                'price' => 'required',
                'description' => 'required',
            ]);

            $product->update($validated);
            DB::commit();


            return APIResponse::success('Updated data success', $product);
        } catch (ValidationException $e) {
            return APIResponse::error('Validation', $e->errors(), 422);
        } catch (Exception $e) {
            DB::rollBack();
            return APIResponse::error('Error', $e->getMessage(), 500);
        }
    }

    public function destroy(Product $product){
        DB::beginTransaction();
        try{
            $product->delete();
            DB::commit();

             return APIResponse::success('deleted data product success');
        } catch (Exception $e){
            DB::rollBack();
             return APIResponse::error('error', $e->getMessage(), 500);
        }
    }
}
