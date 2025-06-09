<?php

namespace App\Http\Controllers\API;

use App\Helpers\APIResponse;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use function Laravel\Prompts\password;

class ProfileController extends Controller
{
    public function updateProfile(Request $request){
        DB::beginTransaction();
        try {
            $user = $request->user();
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'phone_number' => 'required|numeric|unique:users,phone_number,' . $user->id,
            ]);


            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
            ]);
            DB::commit();

            return APIResponse::success('Updated data success', $user);
        } catch (ValidationException $e){
            return APIResponse::error('Validations', $e->errors(), 422);
        }
        catch (Exception $e) {
            DB::rollBack();
            return APIResponse::error('Error', $e->getMessage());
        }
    }

    public function updatePassword(Request $request){
        try {
            DB::beginTransaction();
            $user = $request->user();

            $request->validate([
                'current_password' => 'required',
                'password' => 'required|min:8|confirmed',
                'password_confirmation' => 'required'
            ]);

            if (!Hash::check($request->current_password, $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['Current password is incorrect']
                ]);
            }

            $user->update([
                'password' => Hash::make($request->password)
            ]);

            DB::commit();
            return APIResponse::success('Password updated successfully');

        } catch (ValidationException $e){
            return APIResponse::error('Validations', $e->errors(), 422);
        }
        catch (Exception $e) {
            DB::rollBack();
            return APIResponse::error('Error', $e->getMessage());
        }
    }
}
