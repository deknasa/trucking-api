<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SuratPengantarBiayaTambahan;
use App\Http\Requests\StoreSuratPengantarBiayaTambahanRequest;
use App\Http\Requests\UpdateSuratPengantarBiayaTambahanRequest;
use Illuminate\Http\JsonResponse;

class SuratPengantarBiayaTambahanController extends Controller
{
    /**
     * @ClassName 
     */
    public function index(): JsonResponse
    {
        $suratPengantarBiayaTambahan = new SuratPengantarBiayaTambahan();

        return response()->json([
            'data' => $suratPengantarBiayaTambahan->get(),
            'attributes' => [
                'totalRows' => $suratPengantarBiayaTambahan->totalRows,
                'totalPages' => $suratPengantarBiayaTambahan->totalPages,
                'totalNominal' => $suratPengantarBiayaTambahan->totalNominal,
                'totalNominalTagih' => $suratPengantarBiayaTambahan->totalNominalTagih
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSuratPengantarBiayaTambahanRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSuratPengantarBiayaTambahanRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SuratPengantarBiayaTambahan  $suratPengantarBiayaTambahan
     * @return \Illuminate\Http\Response
     */
    public function show(SuratPengantarBiayaTambahan $suratPengantarBiayaTambahan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SuratPengantarBiayaTambahan  $suratPengantarBiayaTambahan
     * @return \Illuminate\Http\Response
     */
    public function edit(SuratPengantarBiayaTambahan $suratPengantarBiayaTambahan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSuratPengantarBiayaTambahanRequest  $request
     * @param  \App\Models\SuratPengantarBiayaTambahan  $suratPengantarBiayaTambahan
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSuratPengantarBiayaTambahanRequest $request, SuratPengantarBiayaTambahan $suratPengantarBiayaTambahan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SuratPengantarBiayaTambahan  $suratPengantarBiayaTambahan
     * @return \Illuminate\Http\Response
     */
    public function destroy(SuratPengantarBiayaTambahan $suratPengantarBiayaTambahan)
    {
        //
    }
}
