<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAbsensiSupirHeaderRequest;
use App\Models\AbsensiSupirHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateAbsensiSupirHeaderRequest;
use App\Models\AbsensiSupirDetail;

class AbsensiSupirHeaderController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $absensiSupirHeader = new AbsensiSupirHeader();

        return response([
            'data' => $absensiSupirHeader->get(),
            'attributes' => [
                'totalRows' => $absensiSupirHeader->totalRows,
                'totalPages' => $absensiSupirHeader->totalPages
            ]
        ]);
    }

    public function show(AbsensiSupirHeader $absensiSupirHeader)
    {
        $data = $absensiSupirHeader->load(
            'absensiSupirDetail',
            'absensiSupirDetail.trado',
            'absensiSupirDetail.supir',
            'absensiSupirDetail.absenTrado',
        );

        return response([
            'status' => true,
            'data' => $data
        ]);
    }

    public function detail($id)
    {
        return response([
            'data' => AbsensiSupirDetail::with('trado', 'supir', 'absenTrado')->where('absensi_id', $id)->get()
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreAbsensiSupirHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            $noBuktiRequest = new Request();
            $noBuktiRequest['group'] = 'ABSENSI';
            $noBuktiRequest['subgroup'] = 'ABSENSI';
            $noBuktiRequest['table'] = 'absensisupirheader';
            $noBuktiRequest['tgl'] = $request->tglbukti;

            $noBuktiKasgantungRequest = new Request();
            $noBuktiKasgantungRequest['group'] = 'KAS GANTUNG';
            $noBuktiKasgantungRequest['subgroup'] = 'NOMOR KAS GANTUNG';
            $noBuktiKasgantungRequest['table'] = 'absensisupirheader';
            $noBuktiKasgantungRequest['tgl'] = $request->tglbukti;

            /* Store header */
            $absensiSupirHeader = new AbsensiSupirHeader();
            
            $absensiSupirHeader->nobukti = app(Controller::class)->getRunningNumber($noBuktiRequest)->original['data'];
            $absensiSupirHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $absensiSupirHeader->keterangan = $request->keterangan ?? '';
            $absensiSupirHeader->kasgantung_nobukti = app(Controller::class)->getRunningNumber($noBuktiKasgantungRequest)->original['data'];
            $absensiSupirHeader->nominal = array_sum($request->uangjalan);
            $absensiSupirHeader->modifiedby = auth('api')->user()->name;

            if ($absensiSupirHeader->save()) {
                /* Store Header LogTrail */
                $logTrail = [
                    'namatabel' => strtoupper($absensiSupirHeader->getTable()),
                    'postingdari' => 'ENTRY ABSENSI SUPIR HEADER',
                    'idtrans' => $absensiSupirHeader->id,
                    'nobuktitrans' => $absensiSupirHeader->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $absensiSupirHeader->toArray(),
                    'modifiedby' => $absensiSupirHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                for ($i = 0; $i < count($request->trado_id); $i++) {
                    /* Store Detail */
                    $absensiSupirDetail = $absensiSupirHeader->absensiSupirDetail()->create([
                        'absensi_id' => $absensiSupirHeader->id,
                        'nobukti' => $absensiSupirHeader->nobukti,
                        'trado_id' => $request->trado_id[$i],
                        'supir_id' => $request->supir_id[$i],
                        'keterangan' => $request->keterangan_detail[$i],
                        'uangjalan' => $request->uangjalan[$i],
                        'absen_id' => $request->absen_id[$i],
                        'jam' => $request->jam[$i],
                        'modifiedby' => $absensiSupirHeader->modifiedby,
                    ]);

                    if ($absensiSupirDetail) {
                        /* Store Detail LogTrail */
                        $detailLogTrail = [
                            'namatabel' => strtoupper($absensiSupirDetail->getTable()),
                            'postingdari' => 'ENTRY ABSENSI SUPIR DETAIL',
                            'idtrans' => $absensiSupirDetail->id,
                            'nobuktitrans' => $absensiSupirDetail->id,
                            'aksi' => 'ENTRY',
                            'datajson' => $absensiSupirDetail->toArray(),
                            'modifiedby' => $absensiSupirDetail->modifiedby
                        ];

                        $validatedLogTrail = new StoreLogTrailRequest($detailLogTrail);
                        $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                    }
                }
            }

            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($absensiSupirHeader, $absensiSupirHeader->getTable());
            $absensiSupirHeader->position = $selected->position;
            $absensiSupirHeader->page = ceil($absensiSupirHeader->position / ($request->limit ?? 10));

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $absensiSupirHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }

        return response($absensiSupirHeader->absensiSupirDetail);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateAbsensiSupirHeaderRequest $request, AbsensiSupirHeader $absensiSupirHeader)
    {
        DB::beginTransaction();

        try {
            $absensiSupirHeader->absensiSupirDetail()->delete();

            /* Store header */
            $absensiSupirHeader->nobukti = $request->nobukti;
            $absensiSupirHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $absensiSupirHeader->keterangan = $request->keterangan ?? '';
            $absensiSupirHeader->kasgantung_nobukti = $request->kasgantung_nobukti ?? '1';
            $absensiSupirHeader->nominal = array_sum($request->uangjalan);
            $absensiSupirHeader->modifiedby = auth('api')->user()->name;

            if ($absensiSupirHeader->save()) {
                /* Store Header LogTrail */
                $logTrail = [
                    'namatabel' => strtoupper($absensiSupirHeader->getTable()),
                    'postingdari' => 'EDIT ABSENSI SUPIR HEADER',
                    'idtrans' => $absensiSupirHeader->id,
                    'nobuktitrans' => $absensiSupirHeader->id,
                    'aksi' => 'EDIT',
                    'datajson' => $absensiSupirHeader->toArray(),
                    'modifiedby' => $absensiSupirHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                for ($i = 0; $i < count($request->trado_id); $i++) {
                    /* Store Detail */
                    $absensiSupirDetail = $absensiSupirHeader->absensiSupirDetail()->create([
                        'absensi_id' => $absensiSupirHeader->id,
                        'nobukti' => $absensiSupirHeader->nobukti,
                        'trado_id' => $request->trado_id[$i],
                        'supir_id' => $request->supir_id[$i],
                        'keterangan' => $request->keterangan_detail[$i],
                        'uangjalan' => $request->uangjalan[$i],
                        'absen_id' => $request->absen_id[$i],
                        'jam' => $request->jam[$i],
                        'modifiedby' => $absensiSupirHeader->modifiedby,
                    ]);

                    if ($absensiSupirDetail) {
                        /* Store Detail LogTrail */
                        $detailLogTrail = [
                            'namatabel' => strtoupper($absensiSupirDetail->getTable()),
                            'postingdari' => 'EDIT ABSENSI SUPIR DETAIL',
                            'idtrans' => $absensiSupirDetail->id,
                            'nobuktitrans' => $absensiSupirDetail->id,
                            'aksi' => 'EDIT',
                            'datajson' => $absensiSupirDetail->toArray(),
                            'modifiedby' => $absensiSupirDetail->modifiedby
                        ];

                        $validatedLogTrail = new StoreLogTrailRequest($detailLogTrail);
                        $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                    }
                }
            }

            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($absensiSupirHeader, $absensiSupirHeader->getTable());
            $absensiSupirHeader->position = $selected->position;
            $absensiSupirHeader->page = ceil($absensiSupirHeader->position / ($request->limit ?? 10));

            return response([
                'message' => 'Berhasil diubah',
                'data' => $absensiSupirHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    /**
     * @ClassName 
     */
    public function destroy(AbsensiSupirHeader $absensiSupirHeader, Request $request)
    {
        DB::beginTransaction();

        if ($absensiSupirHeader->delete()) {
            $logTrail = [
                'namatabel' => strtoupper($absensiSupirHeader->getTable()),
                'postingdari' => 'DELETE ABSENSI SUPIR',
                'idtrans' => $absensiSupirHeader->id,
                'nobuktitrans' => $absensiSupirHeader->id,
                'aksi' => 'DELETE',
                'datajson' => $absensiSupirHeader->toArray(),
                'modifiedby' => $absensiSupirHeader->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();
            
            $selected = $this->getPosition($absensiSupirHeader, $absensiSupirHeader->getTable(), true);
            $absensiSupirHeader->position = $selected->position;
            $absensiSupirHeader->id = $selected->id;
            $absensiSupirHeader->page = ceil($absensiSupirHeader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $absensiSupirHeader
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }
}
