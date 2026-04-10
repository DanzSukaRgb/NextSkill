<?php

namespace App\Http\Controllers\auth;

use App\Helpers\BaseResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\auth\AuthRequest;
use App\Models\User;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!auth()->attempt($credentials)) {
            return BaseResponse::Custom(false, 'Email atau password tidak valid.', null, 401);
        }

        $user = auth()->user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return BaseResponse::Success('Login berhasil', [
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function register(AuthRequest $request)
    {
        $data = $request->validated();
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'role' => $data['role'],
            'bio' => $data['bio'],
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return BaseResponse::Success('Register berhasil', [
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return BaseResponse::Success('Logout berhasil', null);
    }
}

