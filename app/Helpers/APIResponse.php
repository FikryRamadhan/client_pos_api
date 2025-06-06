<?php

namespace App\Helpers;

class APIResponse
{
    public static function success($message = '', $data = [], $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public static function error($message = '', $errors = [], $code = 400, $data = [])
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
            'data' => $data,
        ], $code);
    }
}
