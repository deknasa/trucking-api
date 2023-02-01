<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Http;

class OrderanEmklController extends Controller
{
    public function index()
    {
        $response = Http::accept('application/json')
            ->withToken(session('access_token'))
            ->get(config('emkl.api.url') . '/orderanemkl');

        return response()->json($response->json(), $response->status());
    }
}
