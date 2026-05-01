<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:teacher,cpe,parent,admin,direction'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        return $this->success(null, 'User registered successfully', 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if (!Auth::attempt($credentials)) {
            return $this->error('Invalid credentials', 401);
        }

        $request->session()->regenerate();

        return $this->success(auth()->user(), 'Logged in successfully');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return response()->json([
        'success' => true,
        'message' => 'Logged out successfully',
    ]);
    }

    public function me(Request $request)
    {
        return $this->success($request->user());
    }
}