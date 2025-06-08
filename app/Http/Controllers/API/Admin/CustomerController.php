<?php

namespace App\Http\Controllers\API\Admin;

use App\Helpers\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function index()
    {
        try {
            $data  = Customer::all();

            return APIResponse::success('Get data customer success', $data);
        } catch (Exception $e) {
            return APIResponse::error('Error', $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $data = Customer::where('id', $id)->first();
            if (!$data) {
                return APIResponse::error('Customer not found', null, 404);
            }
            return APIResponse::success('Get data customer success', $data);
        } catch (Exception $e) {
            return APIResponse::error('Error', $e->getMessage());
        }
    }
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'phone_number' => 'required|string|unique:customers,phone_number',
                'address' => 'required',
            ]);

            $customer = Customer::create($validated);
            DB::commit();

            return APIResponse::success('Customer created successfully', $customer);
        } catch (ValidationException $v) {
            DB::rollBack();
            return APIResponse::error('Validation', $v->errors(), 422);
        }
        catch (Exception $e) {
            DB::rollBack();
            return APIResponse::error('Error', $e->getMessage());
        }
    }
}
