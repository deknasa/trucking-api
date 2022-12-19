<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ProsesGajiSupirDetailController as ApiProsesGajiSupirDetailController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ProsesGajiSupirDetailController;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreProsesGajiSupirDetailRequest;
use App\Models\ProsesGajiSupirHeader;
use App\Http\Requests\StoreProsesGajiSupirHeaderRequest;
use App\Http\Requests\UpdateProsesGajiSupirHeaderRequest;

use App\Models\LogTrail;
use App\Models\Parameter;
use App\Models\ProsesGajiSupirDetail;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProsesGajiSupirHeaderController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $prosesGajiSupirHeader = new ProsesGajiSupirHeader();
        return response([
            'data' => $prosesGajiSupirHeader->get(),
            'attributes' => [
                'totalRows' => $prosesGajiSupirHeader->totalRows,
                'totalPages' => $prosesGajiSupirHeader->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreProsesGajiSupirHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            if ($request->ric_id != '') {
                $group = 'PROSES GAJI SUPIR BUKTI';
                $subgroup = 'PROSES GAJI SUPIR BUKTI';


                $format = DB::table('parameter')
                    ->where('grp', $group)
                    ->where('subgrp', $subgroup)
                    ->first();

                $content = new Request();
                $content['group'] = $group;
                $content['subgroup'] = $subgroup;
                $content['table'] = 'prosesgajisupirheader';
                $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                $prosesgajisupirheader = new ProsesGajiSupirHeader();
                $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
                $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

                $prosesgajisupirheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
                $prosesgajisupirheader->keterangan = $request->keterangan;
                $prosesgajisupirheader->tgldari = date('Y-m-d', strtotime($request->tgldari));
                $prosesgajisupirheader->tglsampai = date('Y-m-d', strtotime($request->tglsampai));
                $prosesgajisupirheader->statusapproval = $statusApproval->id ?? $request->statusapproval;;
                $prosesgajisupirheader->userapproval = '';
                $prosesgajisupirheader->tglapproval = '';
                $prosesgajisupirheader->periode = date('Y-m-d', strtotime($request->periode));
                $prosesgajisupirheader->statusformat = $format->id;
                $prosesgajisupirheader->statuscetak = $statusCetak->id;
                $prosesgajisupirheader->modifiedby = auth('api')->user()->name;

                $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
                $prosesgajisupirheader->nobukti = $nobukti;

                $prosesgajisupirheader->save();

                $logTrail = [
                    'namatabel' => strtoupper($prosesgajisupirheader->getTable()),
                    'postingdari' => 'ENTRY PROSES GAJI SUPIR HEADER',
                    'idtrans' => $prosesgajisupirheader->id,
                    'nobuktitrans' => $prosesgajisupirheader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $prosesgajisupirheader->toArray(),
                    'modifiedby' => $prosesgajisupirheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                /* Store detail */

                $detaillog = [];

                $urut = 1;

                for ($i = 0; $i < count($request->ric_id); $i++) {

                    $ric = DB::table('gajisupirheader')->where('id', $request->ric_id[$i])->first();
                    $sp = DB::table('suratpengantar')->where('supir_id', $ric->supir_id)->first();
                    $datadetail = [
                        'prosesgajisupir_id' => $prosesgajisupirheader->id,
                        'nobukti' => $prosesgajisupirheader->nobukti,
                        'gajisupir_nobukti' => $ric->nobukti,
                        'supir_id' => $ric->supir_id,
                        'trado_id' => $sp->trado_id,
                        'nominal' => $ric->nominal,
                        'keterangan' => $ric->keterangan,
                        'modifiedby' => $prosesgajisupirheader->modifiedby,
                    ];

                    //STORE 
                    $data = new StoreProsesGajiSupirDetailRequest($datadetail);

                    $datadetails = app(ApiProsesGajiSupirDetailController::class)->store($data);

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }
                    $detaillog[] = $datadetails['detail']->toArray();


                    $urut++;
                }
                $datalogtrail = [
                    'namatabel' => strtoupper($tabeldetail),
                    'postingdari' => 'ENTRY PROSES GAJI SUPIR DETAIL',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $prosesgajisupirheader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => $prosesgajisupirheader->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);

                $request->sortname = $request->sortname ?? 'id';
                $request->sortorder = $request->sortorder ?? 'asc';
                DB::commit();

                /* Set position and page */


                $selected = $this->getPosition($prosesgajisupirheader, $prosesgajisupirheader->getTable());
                $prosesgajisupirheader->position = $selected->position;
                $prosesgajisupirheader->page = ceil($prosesgajisupirheader->position / ($request->limit ?? 10));


                return response([
                    'status' => true,
                    'message' => 'Berhasil disimpan',
                    'data' => $prosesgajisupirheader
                ], 201);
            } else {
                $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'WP')
                    ->first();
                return response([
                    'errors' => [
                        'ric' => "RIC $query->keterangan"
                    ],
                    'message' => "RIC $query->keterangan",
                ], 422);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
    }

    public function show($id)
    {
        $prosesGajiSupirHeader = DB::table('prosesgajisupirheader')->where('id', $id)->first();
        return response([
            'status' => true,
            'data' => $prosesGajiSupirHeader
        ]);
    }


    /**
     * @ClassName 
     */
    public function update(UpdateProsesGajiSupirHeaderRequest $request, ProsesGajiSupirHeader $prosesgajisupirheader)
    {
        DB::beginTransaction();

        try {

            $prosesgajisupirheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $prosesgajisupirheader->keterangan = $request->keterangan;
            $prosesgajisupirheader->tgldari = date('Y-m-d', strtotime($request->tgldari));
            $prosesgajisupirheader->tglsampai = date('Y-m-d', strtotime($request->tglsampai));
            $prosesgajisupirheader->periode = date('Y-m-d', strtotime($request->periode));
            $prosesgajisupirheader->modifiedby = auth('api')->user()->name;


            if ($prosesgajisupirheader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($prosesgajisupirheader->getTable()),
                    'postingdari' => 'EDIT PROSES GAJI SUPIR HEADER',
                    'idtrans' => $prosesgajisupirheader->id,
                    'nobuktitrans' => $prosesgajisupirheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $prosesgajisupirheader->toArray(),
                    'modifiedby' => $prosesgajisupirheader->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                ProsesGajiSupirDetail::where('prosesgajisupir_id', $prosesgajisupirheader->id)->lockForUpdate()->delete();

                /* Store detail */

                $detaillog = [];
                $urut = 1;

                for ($i = 0; $i < count($request->ric_id); $i++) {
                    $ric = DB::table('gajisupirheader')->where('id', $request->ric_id[$i])->first();
                    $sp = DB::table('suratpengantar')->where('supir_id', $ric->supir_id)->first();
                    $datadetail = [
                        'prosesgajisupir_id' => $prosesgajisupirheader->id,
                        'nobukti' => $prosesgajisupirheader->nobukti,
                        'gajisupir_nobukti' => $ric->nobukti,
                        'supir_id' => $ric->supir_id,
                        'trado_id' => $sp->trado_id,
                        'nominal' => $ric->nominal,
                        'keterangan' => $ric->keterangan,
                        'modifiedby' => $prosesgajisupirheader->modifiedby,
                    ];

                    //STORE

                    $data = new StoreProsesGajiSupirDetailRequest($datadetail);
                    $datadetails = app(ProsesGajiSupirDetailController::class)->store($data);

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }

                    $detaillog[] = $datadetails['detail']->toArray();

                    $urut++;
                }
                $datalogtrail = [
                    'namatabel' => strtoupper($tabeldetail),
                    'postingdari' => 'EDIT PROSES GAJI SUPIR DETAIL',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $prosesgajisupirheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $detaillog,
                    'modifiedby' => $prosesgajisupirheader->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);

                app(LogTrailController::class)->store($data);
            }
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';


            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($prosesgajisupirheader, $prosesgajisupirheader->getTable());
            $prosesgajisupirheader->position = $selected->position;
            $prosesgajisupirheader->page = ceil($prosesgajisupirheader->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $prosesgajisupirheader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * @ClassName 
     */
    public function destroy(ProsesGajiSupirHeader $prosesgajisupirheader, Request $request)
    {
        DB::beginTransaction();
        try {

            $getDetail = ProsesGajiSupirDetail::where('prosesgajisupir_id', $prosesgajisupirheader->id)->get();
            $delete = ProsesGajiSupirDetail::where('prosesgajisupir_id', $prosesgajisupirheader->id)->lockForUpdate()->delete();
            $delete = ProsesGajiSupirHeader::destroy($prosesgajisupirheader->id);

            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($prosesgajisupirheader->getTable()),
                    'postingdari' => 'DELETE PROSES GAJI SUPIR HEADER',
                    'idtrans' => $prosesgajisupirheader->id,
                    'nobuktitrans' => $prosesgajisupirheader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $prosesgajisupirheader->toArray(),
                    'modifiedby' => $prosesgajisupirheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                // DELETE PROSES GAJI SUPIR DETAIL
                $logTrailProsesGajiSupirDetail = [
                    'namatabel' => 'PROSESGAJISUPIRDETAIL',
                    'postingdari' => 'DELETE PROSES GAJI SUPIR DETAIL',
                    'idtrans' => $storedLogTrail['id'],
                    'nobuktitrans' => $prosesgajisupirheader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $getDetail->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrailProsesGajiSupirDetail = new StoreLogTrailRequest($logTrailProsesGajiSupirDetail);
                app(LogTrailController::class)->store($validatedLogTrailProsesGajiSupirDetail);

            } 
            DB::commit();

            $selected = $this->getPosition($prosesgajisupirheader, $prosesgajisupirheader->getTable(), true);
            $prosesgajisupirheader->position = $selected->position;
            $prosesgajisupirheader->id = $selected->id;
            $prosesgajisupirheader->page = ceil($prosesgajisupirheader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $prosesgajisupirheader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function getRic($dari, $sampai)
    {
        $prosesgajisupir = new ProsesGajiSupirHeader();
        $dari = date('Y-m-d', strtotime($dari));
        $sampai = date('Y-m-d', strtotime($sampai));

        $cekRic = DB::table('gajisupirheader')
            ->where('tgldari', '>=', $dari)
            ->where('tglsampai', '<=', $sampai)
            ->first();

        //CEK APAKAH ADA RIC
        if ($cekRic) {
            $nobukti = $cekRic->nobukti;
            $cekEBS = DB::table('prosesgajisupirdetail')->where('gajisupir_nobukti', $nobukti)->first();
            if ($cekEBS) {
                
                $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'RICSD')
                ->first();
                return response([
                    'message' => "$query->keterangan",
                ], 422);
            } else {
                return response([
                    'errors' => false,
                    'data' => $prosesgajisupir->getRic($dari, $sampai)
                ]);
            }
        } else {
            
            $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'NRIC')
            ->first();
            return response([
                'message' => "$query->keterangan",
            ], 422);
        }
    }
    public function getEdit($gajiId)
    {
        $prosesgajisupir = new ProsesGajiSupirHeader();

        return response([
            'data' => $prosesgajisupir->getEdit($gajiId)
        ]);
    }

    public function noEdit()
    {
        $query = DB::table('error')
            ->select('keterangan')
            ->where('kodeerror', '=', 'EBSX')
            ->first();
        return response([
            'message' => "$query->keterangan",
        ]);
    }

    
    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $prosesgaji = ProsesGajiSupirHeader::lockForUpdate()->findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($prosesgaji->statuscetak != $statusSudahCetak->id) {
                $prosesgaji->statuscetak = $statusSudahCetak->id;
                $prosesgaji->tglbukacetak = date('Y-m-d H:i:s');
                $prosesgaji->userbukacetak = auth('api')->user()->name;
                $prosesgaji->jumlahcetak = $prosesgaji->jumlahcetak+1;

                if ($prosesgaji->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($prosesgaji->getTable()),
                        'postingdari' => 'PRINT PROSES GAJI SUPIR HEADER',
                        'idtrans' => $prosesgaji->id,
                        'nobuktitrans' => $prosesgaji->nobukti,
                        'aksi' => 'PRINT',
                        'datajson' => $prosesgaji->toArray(),
                        'modifiedby' => auth('api')->user()->name,
                    ];
    
                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
    
                    DB::commit();
                }
            }


            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
        
    }

    public function cekvalidasi($id)
    {
        $prosesgaji = ProsesGajiSupirHeader::find($id);
        $status = $prosesgaji->statusapproval;
        $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $prosesgaji->statuscetak;
        $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($status == $statusApproval->id) {
            $query = DB::table('error')
                ->select('keterangan')
                ->where('kodeerror', '=', 'SAP')
                ->get();
            $keterangan = $query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => 'sudah approve',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];

            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
            $query = DB::table('error')
                ->select('keterangan')
                ->where('kodeerror', '=', 'SDC')
                ->get();
            $keterangan = $query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => 'sudah cetak',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];

            return response($data);
        } else {

            $data = [
                'message' => '',
                'errors' => 'belum approve',
                'kodestatus' => '0',
                'kodenobukti' => '1'
            ];

            return response($data);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('prosesgajisupirheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
