<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UpahRitasiRincian;
use App\Http\Requests\StoreUpahRitasiRincianRequest;
use App\Http\Requests\UpdateUpahRitasiRincianRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UpahRitasiRincianController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
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
            $query = DB::table("upahritasirincian")->from(DB::raw("upahritasirincian as detail with (readuncommitted)"));

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

            $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp', 'JUDULAN LAPORAN')
                ->where('subgrp', 'JUDULAN LAPORAN')
                ->first();


            if ($params['forReport']) {

                $query->select(
                    'kotadari.keterangan as kotadari',
                    'kotasampai.keterangan as kotasampai',
                    DB::raw("round(cast(header.jarak as money),2) as jarak"),
                    'zona.keterangan as zona',
                    'container.keterangan as container_id',
                    'header.tglmulaiberlaku',
                    'header.nominalsupir',
                    'detail.liter',
                    DB::raw("'Laporan Upah Ritasi' as judulLaporan"),
                    DB::raw("'" . $getJudul->text . "' as judul"),
                    DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                    DB::raw(" 'User :".auth('api')->user()->name."' as usercetak")
                )
                    ->leftJoin(DB::raw("upahritasi as header with (readuncommitted)"), 'header.id', 'detail.upahritasi_id')
                    ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'header.kotadari_id')
                    ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'header.kotasampai_id')
                    ->leftJoin(DB::raw("zona with (readuncommitted)"), 'header.zona_id', 'zona.id')
                    ->leftJoin(DB::raw("container with (readuncommitted)"), 'container.id', 'detail.container_id');
                
                $upahritasi = $query->get();
            } else {
                $query->select(
                    'container.keterangan as container_id',
                    // 'detail.nominalsupir',
                    // 'detail.nominalkenek',
                    // 'detail.nominalkomisi',
                    // 'detail.nominaltol',
                    'detail.liter',
                )
                    ->leftJoin(DB::raw("container with (readuncommitted)"), 'container.id', 'detail.container_id');

                $upahritasi = $query->get();
            }

            $idUser = auth('api')->user()->id;
            $getuser = User::select('name', 'cabang.namacabang as cabang_id')
                ->where('user.id', $idUser)->join('cabang', 'user.cabang_id', 'cabang.id')->first();

            return response([
                'data' => $upahritasi,
                'user' => $getuser,

            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }
    public function get()
    {
        $upahRitasiRincian = new UpahRitasiRincian();

        return response([
            'data' => $upahRitasiRincian->getLookup(),
            'attributes' => [
                'totalRows' => $upahRitasiRincian->totalRows,
                'totalPages' => $upahRitasiRincian->totalPages
            ]
        ]);
    }

    public function store(StoreUpahRitasiRincianRequest $request)
    {
        $upahritasirincian = new UpahRitasiRincian();

        $upahritasirincian->upahritasi_id = $request->upahritasi_id;
        $upahritasirincian->container_id = $request->container_id;
        $upahritasirincian->nominalsupir = $request->nominalsupir;
        $upahritasirincian->liter = $request->liter;
        $upahritasirincian->modifiedby = auth('api')->user()->name;

        if (!$upahritasirincian->save()) {
            throw new \Exception("Gagal menyimpan upah ritasi detail.");
        }

        return [
            'error' => false,
            'detail' => $upahritasirincian,
            'id' => $upahritasirincian->id,
            'tabel' => $upahritasirincian->getTable(),
        ];
    }

    public function setUpRow()
    {
        $upahRitasiRincian = new UpahRitasiRincian();

        return response([
            'status' => true,
            'detail' => $upahRitasiRincian->setUpRow()
        ]);
    }
    public function setUpRowExcept($id)
    {
        $upahRitasiRincian = new UpahRitasiRincian();
        $rincian = $upahRitasiRincian->where('upahritasi_id', $id)->get();
        foreach ($rincian as $e) {
            $data[] = [
                "container_id" => $e->container_id,
                "statuscontainer_id" => $e->statuscontainer_id
            ];
        }
        // return $data;
        return response([
            'status' => true,
            'detail' => $upahRitasiRincian->setUpRowExcept($data)
        ]);
    }
}
