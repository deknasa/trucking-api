<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request) {
        $credentials = $this->validate($request, [
            'userid' => 'required',
            'password' => 'required'
        ]);

        // return ;
        return Auth::attempt($credentials);

        if (Auth::attempt($credentials)) {
            return redirect()->intended('dashboard.index');
        }
    }
}
