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

        $ipclient = $this->get_client_ip();
        if ($request->ipclient) {
            $ipclient = $request->ipclient;
            if ($ipclient=='::1' ) {
                $ipclient= gethostbyname('tasmdn.kozow.com');
            }
        }

        $ipserver = $this->get_server_ip();
        if ($ipclient != $ipserver) {
            $data = [
                'status' => false,
                'message' => 'test',
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

        // $data = [
        //     'status' => false,
        //     'message' => 'tests ',
        //     'APP_HOSTNAME' => env('APP_HOSTNAME'),
        //     'tasmdn' => $ipclient,
        //     'request' => request()->ip(),
        //     'REMOTE_ADDR' => getenv('REMOTE_ADDR'),
        //     'SERVER_ADDR' => getenv('SERVER_ADDR'),
        //     'ipserver' =>  $ipserver,
        // ];
        return response([
            'data' => $data,
        ]);
    }
}
