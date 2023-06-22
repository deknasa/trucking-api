<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TarifRincian;
use App\Http\Requests\StoreTarifRincianRequest;
use App\Http\Requests\UpdateTarifRincianRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TarifRincianController extends Controller
{
    /**
     * @ClassName 
     */
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'tarif_id' => $request->tarif_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];
        try {
            $query = TarifRincian::from(DB::raw("tarifrincian as detail with (readuncommitted)"));

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['tarif_id'])) {
                $query->where('detail.tarif_id', $params['tarif_id']);
            }

            if ($params['withHeader']) {
                $query->join('tarif', 'tarif.id', 'detail.tarif_id');
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('tarif_id', $params['whereIn']);
            }

            $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp', 'JUDULAN LAPORAN')
                ->where('subgrp', 'JUDULAN LAPORAN')
                ->first();

            if ($params['forReport']) {
                $query->select(
                    'header.tujuan',
                    'kota.keterangan as kota',
                    'zona.keterangan as zona',
                    'header.penyesuaian',
                    'header.tglmulaiberlaku',
                    'parameter.text',
                    'container.keterangan as container_id',
                    'detail.nominal',
                    'header.keterangan',
                    DB::raw("'Laporan Tarif' as judulLaporan"),
                    DB::raw("'" . $getJudul->text . "' as judul")
                )
                    ->leftJoin(DB::raw("tarif as header with (readuncommitted)"), 'header.id', 'detail.tarif_id')
                    ->leftJoin(DB::raw("container with (readuncommitted)"), 'container.id', 'detail.container_id')
                    ->leftJoin(DB::raw("kota as tujuan with (readuncommitted)"), 'tujuan.id', '=', 'header.kota_id')
                    ->leftJoin(DB::raw("zona with (readuncommitted)"), 'header.zona_id', 'zona.id')
                    ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'header.statussistemton', 'parameter.id')
                    ->leftJoin(DB::raw("kota with (readuncommitted)"), 'kota.id', '=', 'header.kota_id');


                $tarif = $query->get();
            } else {
                $query->select(
                    'container.keterangan as container_id',
                    'detail.nominal',
                    DB::raw("'Laporan Tarif' as judulLaporan"),
                    DB::raw("'" . $getJudul->text . "' as judul")
                )
                    ->leftJoin(DB::raw("container with (readuncommitted)"), 'container.id', 'detail.container_id');
                $tarif = $query->get();
            }

            $idUser = auth('api')->user()->id;
            $getuser = User::select('name', 'cabang.namacabang as cabang_id')
                ->where('user.id', $idUser)->join('cabang', 'user.cabang_id', 'cabang.id')->first();

            return response([
                'data' => $tarif,
                'user' => $getuser,

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

    public function get()
    {
        $tarifrincian = new TarifRincian();

        return response([
            'data' => $tarifrincian->get(),
            'attributes' => [
                'totalRows' => $tarifrincian->totalRows,
                'totalPages' => $tarifrincian->totalPages
            ]
        ]);
    }


    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreTarifRincianRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTarifRincianRequest $request)
    {
        $tarifRincian = new TarifRincian();

        $tarifRincian->tarif_id = $request->tarif_id;
        $tarifRincian->container_id = $request->container_id;
        $tarifRincian->nominal = $request->nominal;
        $tarifRincian->modifiedby = auth('api')->user()->name;


        if (!$tarifRincian->save()) {
            throw new \Exception("Gagal menyimpan tarif detail.");
        }

        return [
            'error' => false,
            'detail' => $tarifRincian,
            'id' => $tarifRincian->id,
            'tabel' => $tarifRincian->getTable(),
        ];
    }

    public function update(StoreTarifRincianRequest $request)
    {
        $tarifRincian = new TarifRincian();
        if ($request->detail_id !== "null") {
            $tarifRincian = TarifRincian::find($request->detail_id);
        }
        $tarifRincian->tarif_id = $request->tarif_id;
        $tarifRincian->container_id = $request->container_id;
        $tarifRincian->nominal = $request->nominal;
        $tarifRincian->modifiedby = auth('api')->user()->name;

        if (!$tarifRincian->save()) {
            throw new \Exception("Gagal mengedit tarif detail.", 1);
        }

        return [
            'error' => false,
            'detail' => $tarifRincian,
            'id' => $tarifRincian->id,
            'tabel' => $tarifRincian->getTable(),
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TarifRincian  $tarifRincian
     * @return \Illuminate\Http\Response
     */
    public function destroy(TarifRincian $tarifRincian)
    {
        //
    }

    public function setUpRow()
    {
        $tarifRincian = new tarifRincian();

        return response([
            'status' => true,
            'detail' => $tarifRincian->setUpRow()
        ]);
    }
    public function setUpRowExcept($id)
    {
        $tarifRincian = new tarifRincian();
        $rincian = $tarifRincian->where('tarif_id', $id)->get();
        foreach ($rincian as $e) {
            $data[] = [
                "container_id" => $e->container_id,
            ];
        }
        // return $data;
        return response([
            'status' => true,
            'detail' => $tarifRincian->setUpRowExcept($data)
        ]);
    }
}
