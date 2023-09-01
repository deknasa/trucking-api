<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Config;
use Illuminate\Support\Facades\Artisan;

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

        $info = $this->infoLocation($request->all());

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $path = base_path('.env');

            if (file_exists($path)) {
                file_put_contents($path, str_replace(
                    'PASSWORD_TNL='. getenv('PASSWORD_TNL'),
                    'PASSWORD_TNL=' . $request->password,
                    file_get_contents($path)
                ));
                Artisan::call('cache:clear');
            }
            return response([
                'user' => $user,
                'access_token' => $user->createToken('Access Token')->accessToken,
                'info' =>$info
            ]);
        } else {
            return response([
                'message' => 'User not found'
            ], 404);
        }
    }

    public function infoLocation( $data)
    {

        $infoLoc['location'] = $data['latitude'].','.$data['longitude'];
        $infoLoc['ipclient'] = $data['ipclient'];
        if ($infoLoc['ipclient'] == '::1') {
            $infoLoc['ipclient'] = getHostByName(getHostName());
        }
        $infoLoc['ipserverlocal'] = $this->get_server_ip();
        // $infoLoc['ipserverpublic'] = $_SERVER['SERVER_ADDR'];
        $infoLoc['ipserverpublic'] = $data['ipserver']; //gethostbyname(env('APP_HOSTNAME'));
        $infoLoc['browser'] = $data['browser'];
        $infoLoc['os'] = $data['os'];

        $info = json_encode($infoLoc);
        return $info;
    }

    public function cekIp(Request $request)
    {

        $ipclient = $this->get_client_ip();
        if ($request->ipclient) {
            $ipclient = $request->ipclient;
            if ($ipclient == '::1') {
                $ipclient = getHostByName(getHostName());
                // $ipclient = gethostbyname('tasmdn.kozow.com');
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
