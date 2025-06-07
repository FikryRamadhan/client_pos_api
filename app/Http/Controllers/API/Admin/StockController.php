<?php

namespace App\Http\Controllers\API\Admin;

use App\Helpers\APIResponse;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StockController extends Controller
{
    // public function store(Request $request){
    //     try {
    //         $validated = $request->validate([

    //         ]);
    //     } catch (ValidationException $v) {
    //         return APIResponse::error('Validation', $v->errors(), 422);
    //     } catch (Exception $e) {
    //         return APIResponse::error($e->getMessage());
    //     }
    // }
}
