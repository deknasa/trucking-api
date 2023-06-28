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

    public function cekIp(Request $request)
    {

        $controller = new Controller;
        $ipclient = $controller->get_client_ip();
        $ipserver = $controller->get_server_ip();
        if ($ipclient != $ipserver) {
            $data = [
                'status' => false,
                'message' => 'tests ',
                'errors' => '',
                'ipclient' => $ipclient,
                'ipserver' =>  $ipserver,
            ];
        } else {
            $data = [
                'status' => true,
                'message' => '',
                'errors' => '',
                'ipclient' => $ipclient,
                'ipserver' =>  $ipserver,
            ];

        }
        return response([
            'data' => $data,
        ]);
    }
}
