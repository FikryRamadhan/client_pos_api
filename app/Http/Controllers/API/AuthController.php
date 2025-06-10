<?php

namespace App\Http\Controllers\API;

use App\Helpers\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'phone_number' => 'required|string',
                'role' => 'required|in:admin,cashier',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone_number' => $request->phone_number,
                'role' => $request->role,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return APIResponse::success('User registered successfully.', [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => array_merge(
                    $user->only(['id', 'name', 'email', 'role']),
                    $user->outlet ? ['outlet_id' => $user->outlet->id] : []
                )
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return APIResponse::error('Validation failed.', $e->errors(), 422);
        } catch (\Exception $e) {
            return APIResponse::error('Registration failed.', ['error' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required',
                'password' => 'required|string',
            ]);

            if (!Auth::attempt($request->only('email', 'password'))) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.']
                ]);
            }

            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return APIResponse::success('User login successfully.', [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => array_merge(
                    $user->only(['id', 'name', 'email', 'role']),
                    $user->outlet ? ['outlet_id' => $user->outlet->id] : []
                )
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return APIResponse::error('Validation failed.', $e->errors(), 422);
        } catch (\Exception $e) {
            return APIResponse::error('Login failed.', ['error' => $e->getMessage()], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return APIResponse::success('User logged out successfully.');
        } catch (\Exception $e) {
            return APIResponse::error('Logout failed.', ['error' => $e->getMessage()], 500);
        }
    }

    public function me(Request $request)
    {
        return APIResponse::success('User profile retrieved successfully.', [
            'user' => $request->user()->only(['id', 'name', 'email', 'role']),
        ]);
    }
}
