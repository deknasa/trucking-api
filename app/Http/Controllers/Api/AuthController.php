<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function token(Request $request)
    {
        $request->validate([
            'user' => 'required',
            'password' => 'required'
        ]);

        $credentials = [
            'user' => $request->user,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            return response([
                'user' => $user,
                'access_token' => $user->createToken('Access Token')->accessToken,
            ]);
        } else {
            return response([
                'message' => 'User not found'
            ], 404);
        }
    }
}
