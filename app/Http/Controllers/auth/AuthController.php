<?php

namespace App\Http\Controllers\auth;

use App\Helpers\BaseResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\auth\AuthRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!auth()->attempt($credentials)) {
            return back()->withErrors(['email' => 'Email atau password tidak valid.'])
            ->onlyInput('email');
        }

        $request->session()->regenerate();
        return BaseResponse::Success('Login berhasil', null);
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

        Auth::login($user);
        $request->session()->regenerate();
        return BaseResponse::Success('Register berhasil', $user);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return BaseResponse::Success('Logout berhasil', null);
    }
}
