<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Auth;
use App\Http\Resources\AuthResource;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Requests\AuthRequest;
use App\Http\Requests\AuthRegisterRequest;

class AuthController extends Controller
{
    public function login(AuthRequest $request)
    {
        $input = $validator->validated();

        if(! Auth::attempt($input)) {
            throw new HttpException(400, 'Password Salah!');
        }

        $user = User::where('email', '=', $input['email'])->firstOrFail();

        return new AuthResource($user);
    }

    public function register(Request $request)
    {
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
