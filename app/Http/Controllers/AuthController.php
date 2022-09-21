<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Validator;
use Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:55|exists:users,email',
            'password' => 'required|string'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors());
        }

        $input = $validator->validated();

        if(! Auth::attempt($input)) {
            return response()->json(['message' => 'password salah!']);
        }

        $user = User::where('email', '=', $input['email'])->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'data' => [
                'name' => $user->name,
                'email' => $user->email
            ],
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:10',
            'email' => 'required|email|max:55|unique:users',
            'password' => 'required|string'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors());
        }

        $input = $validator->validated();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password'])
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'data' => [
                'name' => $user->name,
                'email' => $user->email
            ],
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json(['message' => 'Kamu telah logout.']);
    }
}
