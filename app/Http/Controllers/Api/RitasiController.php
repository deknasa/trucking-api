<?php

namespace App\Http\Controllers\Api;

use App\Models\Ritasi;
use App\Http\Requests\StoreRitasiRequest;
use App\Http\Requests\UpdateRitasiRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;
use App\Models\Supir;
use App\Models\Trado;
use App\Models\Kota;
use App\Models\SuratPengantar;
use App\Models\UpahRitasi;
use App\Models\UpahRitasiRincian;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyRitasiRequest;
use App\Http\Requests\GetIndexRangeRequest;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RitasiController extends Controller
{
    /**
     * @ClassName 
     */
    public function index(GetIndexRangeRequest $request)
    {
        $ritasi = new Ritasi();
        return response([
            'data' => $ritasi->get(),
            'attributes' => [
                'totalRows' => $ritasi->totalRows,
                'totalPages' => $ritasi->totalPages
            ]
        ]);
    }

    public function default()
    {
        $ritasi = new Ritasi();
        return response([
            'status' => true,
            'data' => $ritasi->default()
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreRitasiRequest $request)
    {
        DB::beginTransaction();

        try {

            $group = 'RITASI';
            $subgroup = 'RITASI';
            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'ritasi';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $ritasi = new Ritasi();
            $ritasi->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $ritasi->statusritasi = $request->statusritasi;
            $ritasi->suratpengantar_nobukti = $request->suratpengantar_nobukti;
            $ritasi->supir_id = $request->supir_id;
            $ritasi->trado_id = $request->trado_id;
            $ritasi->dari_id = $request->dari_id;
            $ritasi->sampai_id = $request->sampai_id;
            // $upahRitasi = UpahDB::table((new Ritasi())->getTable())->where('kotadari_id',$request->dari_id)->where('kotasampai_id',$request->sampai_id)->first();

            $upahRitasi = DB::table('upahritasi')->where('kotadari_id', $request->dari_id)->where('kotasampai_id', $request->sampai_id)->first();

            if ($upahRitasi == '') {
                return response([
                    'status' => false,
                    'errors' => [
                        'dari' => ['Kota Dari belum terdaftar di master Upah Ritasi'],
                        'sampai' => ['Kota Sampai belum terdaftar di master Upah Ritasi']
                    ],
                    'message' => 'Kota Dari dan Sampai Belum terdaftar di master Upah Ritasi'
                ], 422);
            } else {
                $upahRitasiId = $upahRitasi->id;
                $upahRitasiRincian = DB::table('upahritasirincian')->where('upahritasi_id', $upahRitasiId)->first();
                // $ritasi->jarak = $upahRitasi->upahritasiRincian()->first()->liter;
                // $ritasi->gaji = $upahRitasi->upahritasiRincian()->first()->nominalsupir;

                $ritasi->jarak = $upahRitasiRincian->liter;
                $ritasi->gaji = $upahRitasiRincian->nominalsupir;
                $ritasi->modifiedby = auth('api')->user()->name;
                $ritasi->statusformat = $format->id;

                $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
                $ritasi->nobukti = $nobukti;

                if ($ritasi->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($ritasi->getTable()),
                        'postingdari' => 'ENTRY RITASI',
                        'idtrans' => $ritasi->id,
                        'nobuktitrans' => $ritasi->id,
                        'aksi' => 'ENTRY',
                        'datajson' => $ritasi->toArray(),
                        'modifiedby' => $ritasi->modifiedby
                    ];

                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                    DB::commit();
                }

                /* Set position and page */
                $selected = $this->getPosition($ritasi, $ritasi->getTable());
                $ritasi->position = $selected->position;
                $ritasi->page = ceil($ritasi->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil disimpan',
                    'data' => $ritasi
                ], 201);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $data = Ritasi::find($id);
        return response([
            'status' => true,
            'data' => $data
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateRitasiRequest $request, Ritasi $ritasi)
    {
        DB::beginTransaction();
        try {
            $ritasi->tglbukti = $request->tglbukti;
            $ritasi->statusritasi = $request->statusritasi;
            $ritasi->suratpengantar_nobukti = $request->suratpengantar_nobukti;
            $ritasi->supir_id = $request->supir_id;
            $ritasi->trado_id = $request->trado_id;
            // $upahRitasi = UpahDB::table((new Ritasi())->getTable())->where('kotadari_id',$request->dari_id)->where('kotasampai_id',$request->sampai_id)->first();

            $upahRitasi = DB::table('upahritasi')->where('kotadari_id', $request->dari_id)->where('kotasampai_id', $request->sampai_id)->first();

            if ($upahRitasi == '') {
                header("HTTP/1.1 400 Bad Request");
                return response([
                    'status' => false,
                    'errors' => [
                        'dari' => ['Kota Dari belum terdaftar di master Upah Ritasi'],
                        'sampai' => ['Kota Sampai belum terdaftar di master Upah Ritasi']
                    ],
                    
                    'message' => 'Kota Dari dan Sampai Belum terdaftar di master Upah Ritasi',
                ], 422);
            }
            $upahRitasiId = $upahRitasi->id;
            $upahRitasiRincian = DB::table('upahritasirincian')->where('upahritasi_id', $upahRitasiId)->first();
            // $ritasi->jarak = $upahRitasi->upahritasiRincian()->first()->liter;
            // $ritasi->gaji = $upahRitasi->upahritasiRincian()->first()->nominalsupir;

            $ritasi->jarak = $upahRitasiRincian->liter;
            $ritasi->gaji = $upahRitasiRincian->nominalsupir;
            $ritasi->dari_id = $request->dari_id;
            $ritasi->sampai_id = $request->sampai_id;
            $ritasi->modifiedby = auth('api')->user()->name;

            if ($ritasi->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($ritasi->getTable()),
                    'postingdari' => 'EDIT RITASI',
                    'idtrans' => $ritasi->id,
                    'nobuktitrans' => $ritasi->id,
                    'aksi' => 'EDIT',
                    'datajson' => $ritasi->toArray(),
                    'modifiedby' => $ritasi->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);
                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($ritasi, $ritasi->getTable());
            $ritasi->position = $selected->position;
            $ritasi->page = ceil($ritasi->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $ritasi
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function destroy(DestroyRitasiRequest $request, $id)
    {
        DB::beginTransaction();

        $ritasi = new Ritasi();
        $ritasi = $ritasi->lockAndDestroy($id);
        if ($ritasi) {
            $logTrail = [
                'namatabel' => strtoupper($ritasi->getTable()),
                'postingdari' => 'DELETE RITASI',
                'idtrans' => $ritasi->id,
                'nobuktitrans' => $ritasi->id,
                'aksi' => 'DELETE',
                'datajson' => $ritasi->toArray(),
                'modifiedby' => $ritasi->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();
            $selected = $this->getPosition($ritasi, $ritasi->getTable(), true);
            $ritasi->position = $selected->position;
            $ritasi->id = $selected->id;
            $ritasi->page = ceil($ritasi->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $ritasi
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('ritasi')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function combo(Request $request)
    {
        $data = [
            'statusritasi' => Parameter::where(['grp' => 'status ritasi'])->get(),
            'suratpengantar' => SuratPengantar::all(),
            'supir' => Supir::all(),
            'trado' => Trado::all(),
            'kota' => Kota::all(),
        ];

        return response([
            'data' => $data
        ]);
    }
}
