<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Validator;
use Auth;
use App\Http\Resources\AuthResource;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:55|exists:users,email',
            'password' => 'required|string|min:8'
        ]);

        if($validator->fails()) {
            throw new HttpException(400, $validator->errors()->messages());
        }

        $input = $validator->validated();

        if(! Auth::attempt($input)) {
            throw new HttpException(400, 'Password Salah!');
        }

        $user = User::where('email', '=', $input['email'])->firstOrFail();

        return new AuthResource($user);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:10',
            'email' => 'required|email|max:55|unique:users',
            'password' => 'required|string'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $input = $validator->validated();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password'])
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return new AuthResource($user);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json(['message' => 'Kamu telah logout.'], 200);
    }
}
