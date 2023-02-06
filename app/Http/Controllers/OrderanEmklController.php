<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrderanEmklController extends Controller
{
    public function index(Request $request)
    {
        $response = Http::accept('application/json')
            ->withToken(session('access_token'))
            ->get(config('emkl.api.url') . '/orderanemkl', [
                'container_id' => $request->container_id,
                'jenisorder_id' => $request->jenisorder_id,
                'bulanjob' => $request->bulanjob
            ]);

        return response()->json($response->json(), $response->status());
    }
}
