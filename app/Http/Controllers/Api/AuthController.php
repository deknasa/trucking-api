<?php

namespace App\Http\Controllers\Api;

use Config;
use Illuminate\Http\Request;
use App\Models\AbsensiSupirHeader;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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
// dd($info);
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            // dd($user);
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
      
        $ippublic = $this->get_server_ip();
        $infoLoc['ipserverpublic'] = $ippublic;
        // $infoLoc['ipserverpublic'] = $_SERVER['SERVER_ADDR'];

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
        if (env('APP_HOSTNAME') != request()->getHost()) {
            return response([
                'data' => [
                    'status' => true,
                    'message' => 'test',
                    'errors' => '',
                    'ipclient' => $ipclient,
                    'ipserver' =>  $ipserver,
                ]
            ]);
        }
        if ($this->ipToCheck($request->ipclient)) {
            $data = [
                'status' => true,
                'message' => 'test',
                'errors' => '',
                'ipclient' => $ipclient,
                'ipserver' =>  $ipserver,
            ];
        } else {
            $data = [
                'status' => false,
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

    public function remainderFinalAbsensi(){
        $user_id =  auth()->user()->id;
        $isUserPusat =auth()->user()->isUserPusat();

        $show = false;
        $data = [];
        if ($isUserPusat) {
            $show = true;
            $data = (new AbsensiSupirHeader())->notifApprovalFinal()->tglbukti;
            if ($data=='') {
                $show=false;
            }
        }

        return response([
            'data' => $data,
            'show' => $show,
        ]);
    }
}
