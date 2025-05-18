<?php
namespace App\Http\Controllers\Api;


use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'gender' => 'nullable|string|in:male,female,other',
        ]);

        $validated['password'] = Hash::make($request->password);

        $user = User::create($validated);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }
}
