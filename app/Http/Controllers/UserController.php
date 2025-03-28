<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
    
        if (!auth()->attempt($request->only('email', 'password'))) {
            return response()->json(['error' => 'Informations invalides'], 401);
        }
    
        $user = User::where('email', $request->email)->first();
    
        // Générer le token correctement
        $token = $user->createToken('authToken')->plainTextToken;
    
        return response()->json([
            'user' => $user,
            'token' => $token
        ], 200);
    }
    
}
