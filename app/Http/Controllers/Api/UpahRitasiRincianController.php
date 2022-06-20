<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UpahRitasiRincian;
use App\Http\Requests\StoreUpahRitasiRincianRequest;
use App\Http\Requests\UpdateUpahRitasiRincianRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UpahRitasiRincianController extends Controller
{

    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'upahritasi_id' => $request->upahritasi_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        try {
            $query = UpahRitasiRincian::from('upahritasirincian as detail');

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['upahritasi_id'])) {
                $query->where('detail.upahritasi_id', $params['upahritasi_id']);
            }

            if ($params['withHeader']) {
                $query->join('upahritasi', 'upahritasi.id', 'detail.upahritasi_id');
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('upahritasi_id', $params['whereIn']);
            }

            if ($params['forReport']) {
                $query->select(
                    'container.keterangan as container_id',
                    'statuscontainer.keterangan as statuscontainer_id',
                    'detail.nominalsupir',
                    'detail.nominalkenek',
                    'detail.nominalkomisi',
                    'detail.nominaltol',
                    'detail.liter',
                )
                    ->join('upahritasi as header', 'header.id', 'detail.upahritasi_id')
                    ->leftJoin('container', 'container.id', 'detail.container_id')
                    ->leftJoin('statuscontainer', 'statuscontainer.id', 'detail.statuscontainer_id')
                    ->orderBy('header.id', 'asc');

                $upahritasi = $query->get();
            } else {
                $query->select(
                    'container.keterangan as container_id',
                    'statuscontainer.keterangan as statuscontainer_id',
                    'detail.nominalsupir',
                    'detail.nominalkenek',
                    'detail.nominalkomisi',
                    'detail.nominaltol',
                    'detail.liter',
                )
                    ->join('upahritasi as header', 'header.id', 'detail.upahritasi_id')
                    ->leftJoin('container', 'container.id', 'detail.container_id')
                    ->leftJoin('statuscontainer', 'statuscontainer.id', 'detail.statuscontainer_id')
                    ->orderBy('header.id', 'asc');

                $upahritasi = $query->get();
            }

            return response([
                'data' => $upahritasi
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
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
     * @param  \App\Http\Requests\StoreupahritasirincianRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUpahRitasiRincianRequest $request)
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'container_id' => 'required',
            'statuscontainer_id' => 'required',
            'nominalsupir' => 'required',
            'nominalkenek' => 'required',
            'nominalkomisi' => 'required',
            'nominaltol' => 'required',
            'liter' => 'required',
        ], [
            'container_id.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
        ], [
            'container_id' => 'Container',
        ]);
        if (!$validator->passes()) {
            return [
                'error' => true,
                'errors' => $validator->messages()
            ];
        }

        try {
            $upahritasirincian = new UpahRitasiRincian();

            $upahritasirincian->upahritasi_id = $request->upahritasi_id;
            $upahritasirincian->container_id = $request->container_id;
            $upahritasirincian->statuscontainer_id = $request->statuscontainer_id;
            $upahritasirincian->nominalsupir = $request->nominalsupir;
            $upahritasirincian->nominalkenek = $request->nominalkenek;
            $upahritasirincian->nominalkomisi = $request->nominalkomisi;
            $upahritasirincian->nominaltol = $request->nominaltol;
            $upahritasirincian->liter = $request->liter;
            $upahritasirincian->modifiedby = $request->modifiedby;
            
            $upahritasirincian->save();
            
            DB::commit();
            if ($validator->passes()) {
                return [
                    'error' => false,
                    'id' => $upahritasirincian->id,
                    'tabel' => $upahritasirincian->getTable(),
                ];
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\upahritasirincian  $upahritasirincian
     * @return \Illuminate\Http\Response
     */
    public function show(upahritasirincian $upahritasirincian)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\upahritasirincian  $upahritasirincian
     * @return \Illuminate\Http\Response
     */
    public function edit(upahritasirincian $upahritasirincian)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateupahritasirincianRequest  $request
     * @param  \App\Models\upahritasirincian  $upahritasirincian
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateupahritasirincianRequest $request, upahritasirincian $upahritasirincian)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\upahritasirincian  $upahritasirincian
     * @return \Illuminate\Http\Response
     */
    public function destroy(upahritasirincian $upahritasirincian)
    {
        //
    }
}
