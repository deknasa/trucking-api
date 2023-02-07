<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrderanEmklController extends Controller
{
    public function index(Request $request)
    {
        $response = Http::accept('application/json')
            ->withToken(session('access_token'))
            ->get(config('emkl.api.url') . '/orderanemkl', $request->all());

        return response()->json($response->json(), $response->status());
    }
}
