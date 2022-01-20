<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request) {
        $credentials = $request->validate([
            'user' => ['required'],
            'password' => ['required'],
        ]);

        return response([
            'status' => Auth::attempt($credentials),
            'data' => Auth::user(),
        ]);
    }
}
