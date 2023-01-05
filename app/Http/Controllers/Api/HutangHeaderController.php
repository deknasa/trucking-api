<?php

namespace App\Http\Controllers\Api;

use App\Models\HutangHeader;
use App\Models\HutangDetail;
use App\Http\Requests\StoreHutangHeaderRequest;
use App\Http\Requests\StoreHutangDetailRequest;
use App\Http\Requests\UpdateHutangDetailRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\LogTrail;
use App\Models\AkunPusat;
use App\Models\Supplier;
use App\Models\Bank;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\Parameter;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\UpdateHutangHeaderRequest;
use App\Models\Pelanggan;
use PhpParser\Builder\Param;
use Illuminate\Database\QueryException;

class HutangHeaderController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $hutang = new HutangHeader();

        return response([
            'data' => $hutang->get(),
            'attributes' => [
                'totalRows' => $hutang->totalRows,
                'totalPages' => $hutang->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StoreHutangHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            /* Store header */

            $group = 'HUTANG BUKTI';
            $subgroup = 'HUTANG BUKTI';


            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'hutangheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

            $hutangHeader = new HutangHeader();
            $hutangHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $hutangHeader->keterangan = $request->keterangan ?? '';
            $hutangHeader->coa = $request->akunpusat;
            $hutangHeader->pelanggan_id = $request->pelanggan_id;
            $hutangHeader->postingdari = $request->postingdari ?? 'ENTRY HUTANG';
            $hutangHeader->statusformat = $format->id;
            $hutangHeader->statuscetak = $statusCetak->id;
            $hutangHeader->total = array_sum($request->total_detail);
            $hutangHeader->modifiedby = auth('api')->user()->name;

            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];

            $hutangHeader->nobukti = $nobukti;
            $hutangHeader->save();

            $logTrail = [
                'namatabel' => strtoupper($hutangHeader->getTable()),
                'postingdari' => 'ENTRY HUTANG HEADER',
                'idtrans' => $hutangHeader->id,
                'nobuktitrans' => $hutangHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $hutangHeader->toArray(),
                'modifiedby' => $hutangHeader->modifiedby
            ];
            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            /* Store detail */
            $detaillog = [];
            for ($i = 0; $i < count($request->total_detail); $i++) {

                $datadetail = [
                    'hutang_id' => $hutangHeader->id,
                    'nobukti' => $hutangHeader->nobukti,
                    'supplier_id' => $request->supplier_id[$i],
                    'tgljatuhtempo' => date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                    'total' => $request->total_detail[$i],
                    'cicilan' => '',
                    'totalbayar' => '',
                    'keterangan' => $request->keterangan_detail[$i],
                    'modifiedby' => $hutangHeader->modifiedby,
                ];
                $data = new StoreHutangDetailRequest($datadetail);
                $datadetails = app(HutangDetailController::class)->store($data);

                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }

                $detaillog[] = $datadetails['detail']->toArray();
            }

            $datalogtrail = [
                'namatabel' => strtoupper($tabeldetail),
                'postingdari' => 'ENTRY HUTANG DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $hutangHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => $hutangHeader->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';


            $parameterController = new ParameterController;
            $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');

            $coahutang = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'COA HUTANG MANUAL')->get();

            $jurnalHeader = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $hutangHeader->nobukti,
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'keterangan' => $request->keterangan,
                'postingdari' => "ENTRY HUTANG",
                'statusapproval' => $statusApp->id,
                'userapproval' => "",
                'tglapproval' => "",
                'modifiedby' => auth('api')->user()->name,
                'statusformat' => "0",
            ];

            $jurnaldetail = [];

            for ($i = 0; $i < count($request->total_detail); $i++) {
                $detail = [];

                foreach ($coahutang as $key => $coa) {
                    $a = 0;
                    $getcoa = DB::table('akunpusat')
                        ->where('id', $coa->text)->first();

                    $jurnalDetail = [
                        [
                            'nobukti' => $hutangHeader->nobukti,
                            'tglbukti' => date('Y-m-d', strtotime($hutangHeader->tglbukti)),
                            'coa' =>  $getcoa->coa,
                            'keterangan' => $request->keterangan_detail[$i],
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $i,
                        ]
                    ];
                    if ($coa->subgrp == 'DEBET') {
                        $jurnalDetail[$a]['nominal'] = $request->total_detail[$i];
                    } else {
                        $jurnalDetail[$a]['nominal'] = '-' . $request->total_detail[$i];
                    }

                    $detail = array_merge($detail, $jurnalDetail);
                    $a++;
                }
                $jurnaldetail = array_merge($jurnaldetail, $detail);
            }

            $jurnal = $this->storeJurnal($jurnalHeader, $jurnaldetail);

            if (!$jurnal['status']) {
                throw new \Throwable($jurnal['message']);
            }

            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($hutangHeader, $hutangHeader->getTable());
            $hutangHeader->position = $selected->position;
            $hutangHeader->page = ceil($hutangHeader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $hutangHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
    }

    public function show($id)
    {

        $data = HutangHeader::findAll($id);
        $detail = HutangDetail::getAll($id);

        // dd($details);
        // $datas = array_merge($data, $detail);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }

    public function combo(Request $request)
    {
        $data = [
            'coa'           => AkunPusat::all(),
            'parameter'     => Parameter::all(),
            'pelanggan'     => Pelanggan::all(),
            'supplier'      => Supplier::all(),

            'statuskas'     => Parameter::where('grp', 'STATUS KAS')->get(),
            'statusapproval' => Parameter::where('grp', 'STATUS APPROVAL')->get(),
            'statusberkas'  => Parameter::where('grp', 'STATUS BERKAS')->get(),

        ];

        return response([
            'data' => $data
        ]);
    }


    /**
     * @ClassName
     */
    public function update(UpdateHutangHeaderRequest $request, HutangHeader $hutangheader)
    {
        DB::beginTransaction();

        try {
            $hutangheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $hutangheader->keterangan = $request->keterangan ?? '';
            $hutangheader->coa = $request->akunpusat;
            $hutangheader->pelanggan_id = $request->pelanggan_id;
            $hutangheader->total = array_sum($request->total_detail);
            $hutangheader->modifiedby = auth('api')->user()->name;

            if ($hutangheader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($hutangheader->getTable()),
                    'postingdari' => 'EDIT HUTANG HEADER',
                    'idtrans' => $hutangheader->id,
                    'nobuktitrans' => $hutangheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $hutangheader->toArray(),
                    'modifiedby' => $hutangheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                HutangDetail::where('hutang_id', $hutangheader->id)->delete();
                /* Store detail */
                $detaillog = [];
                for ($i = 0; $i < count($request->total_detail); $i++) {
                    $datadetail = [
                        'hutang_id' => $hutangheader->id,
                        'nobukti' => $hutangheader->nobukti,
                        'supplier_id' => $request->supplier_id[$i],
                        'tgljatuhtempo' => date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                        'total' => $request->total_detail[$i],
                        'cicilan' => '',
                        'totalbayar' => '',
                        'keterangan' => $request->keterangan_detail[$i],
                        'modifiedby' => $hutangheader->modifiedby,
                    ];

                    $data = new StoreHutangDetailRequest($datadetail);
                    $datadetails = app(HutangDetailController::class)->store($data);

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }
                    $detaillog[] = $datadetails['detail']->toArray();
                }

                $datalogtrail = [
                    'namatabel' => strtoupper($tabeldetail),
                    'postingdari' => 'EDIT HUTANG DETAIL',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $hutangheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $detaillog,
                    'modifiedby' => $hutangheader->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);
            }
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            JurnalUmumHeader::where('nobukti', $hutangheader->nobukti)->delete();
            JurnalUmumDetail::where('nobukti', $hutangheader->nobukti)->delete();


            $parameterController = new ParameterController;
            $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');

            $coahutang = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'COA HUTANG MANUAL')->get();

            $jurnalHeader = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $hutangheader->nobukti,
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'keterangan' => $request->keterangan,
                'postingdari' => "ENTRY HUTANG",
                'statusapproval' => $statusApp->id,
                'userapproval' => "",
                'tglapproval' => "",
                'modifiedby' => auth('api')->user()->name,
                'statusformat' => "0",
            ];

            $jurnaldetail = [];

            for ($i = 0; $i < count($request->total_detail); $i++) {
                $detail = [];

                foreach ($coahutang as $key => $coa) {
                    $a = 0;
                    $getcoa = DB::table('akunpusat')
                        ->where('id', $coa->text)->first();

                    $jurnalDetail = [
                        [
                            'nobukti' => $hutangheader->nobukti,
                            'tglbukti' => date('Y-m-d', strtotime($hutangheader->tglbukti)),
                            'coa' =>  $getcoa->coa,
                            'keterangan' => $request->keterangan_detail[$i],
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $i,
                        ]
                    ];
                    if ($coa->subgrp == 'DEBET') {
                        $jurnalDetail[$a]['nominal'] = $request->total_detail[$i];
                    } else {
                        $jurnalDetail[$a]['nominal'] = '-' . $request->total_detail[$i];
                    }

                    $detail = array_merge($detail, $jurnalDetail);
                    $a++;
                }
                $jurnaldetail = array_merge($jurnaldetail, $detail);
            }

            $jurnal = $this->storeJurnal($jurnalHeader, $jurnaldetail);

            if (!$jurnal['status']) {
                throw new \Throwable($jurnal['message']);
            }

            DB::commit();


            /* Set position and page */
            $selected = $this->getPosition($hutangheader, $hutangheader->getTable());
            $hutangheader->position = $selected->position;
            $hutangheader->page = ceil($hutangheader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $hutangheader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function destroy(Request $request, $id)
    {

        DB::beginTransaction();

        $getDetail = HutangDetail::lockForUpdate()->where('hutang_id', $id)->get();
        $hutangheader = new HutangHeader();
        $hutangheader = $hutangheader->lockAndDestroy($id);

        $getJurnalHeader = JurnalUmumHeader::lockForUpdate()->where('nobukti', $hutangheader->nobukti)->first();
        $getJurnalDetail = JurnalUmumDetail::lockForUpdate()->where('nobukti', $hutangheader->nobukti)->get();

        JurnalUmumHeader::where('nobukti', $hutangheader->nobukti)->delete();

        if ($hutangheader) {
            $logTrail = [
                'namatabel' => strtoupper($hutangheader->getTable()),
                'postingdari' => 'DELETE HUTANG HEADER',
                'idtrans' => $hutangheader->id,
                'nobuktitrans' => $hutangheader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $hutangheader->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE HUTANG DETAIL

            $logTrailHutangDetail = [
                'namatabel' => 'HUTANGDETAIL',
                'postingdari' => 'DELETE HUTANG DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $hutangheader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailHutangDetail = new StoreLogTrailRequest($logTrailHutangDetail);
            app(LogTrailController::class)->store($validatedLogTrailHutangDetail);

            // DELETE JURNAL HEADER
            $logTrailJurnalHeader = [
                'namatabel' => 'JURNALUMUMHEADER',
                'postingdari' => 'DELETE JURNAL UMUM HEADER DARI HUTANG',
                'idtrans' => $getJurnalHeader->id,
                'nobuktitrans' => $getJurnalHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getJurnalHeader->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailJurnalHeader = new StoreLogTrailRequest($logTrailJurnalHeader);
            $storedLogTrailJurnal = app(LogTrailController::class)->store($validatedLogTrailJurnalHeader);


            // DELETE JURNAL DETAIL

            $logTrailJurnalDetail = [
                'namatabel' => 'JURNALUMUMDETAIL',
                'postingdari' => 'DELETE JURNAL UMUM DETAIL DARI HUTANG',
                'idtrans' => $storedLogTrailJurnal['id'],
                'nobuktitrans' => $getJurnalHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getJurnalDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailJurnalDetail = new StoreLogTrailRequest($logTrailJurnalDetail);
            app(LogTrailController::class)->store($validatedLogTrailJurnalDetail);

            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($hutangheader, $hutangheader->getTable(), true);
            $hutangheader->position = $selected->position;
            $hutangheader->id = $selected->id;
            $hutangheader->page = ceil($hutangheader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $hutangheader
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }


    private function storeJurnal($header, $detail)
    {
        DB::beginTransaction();

        try {

            $jurnal = new StoreJurnalUmumHeaderRequest($header);
            $jurnals = app(JurnalUmumHeaderController::class)->store($jurnal);

            $detailLog = [];

            foreach ($detail as $value) {
                $value['jurnalumum_id'] = $jurnals->original['data']['id'];
                $detail = new StoreJurnalUmumDetailRequest($value);
                $datadetails = app(JurnalUmumDetailController::class)->store($detail);

                $detailLog[] = $datadetails['detail']->toArray();
            }

            $datalogtrail = [
                'namatabel' => strtoupper($datadetails['tabel']),
                'postingdari' => 'ENTRY HUTANG',
                'idtrans' => $jurnals->original['idlogtrail'],
                'nobuktitrans' => $header['nobukti'],
                'aksi' => 'ENTRY',
                'datajson' => $detailLog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            DB::commit();
            return [
                'status' => true,
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $hutang = HutangHeader::lockForUpdate()->findOrFail($id);
            $statusSudahCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($hutang->statuscetak != $statusSudahCetak->id) {
                $hutang->statuscetak = $statusSudahCetak->id;
                $hutang->tglbukacetak = date('Y-m-d H:i:s');
                $hutang->userbukacetak = auth('api')->user()->name;
                $hutang->jumlahcetak = $hutang->jumlahcetak + 1;

                if ($hutang->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($hutang->getTable()),
                        'postingdari' => 'PRINT HUTANG HEADER',
                        'idtrans' => $hutang->id,
                        'nobuktitrans' => $hutang->nobukti,
                        'aksi' => 'PRINT',
                        'datajson' => $hutang->toArray(),
                        'modifiedby' => Auth('api')->user()->name
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
        $hutang = HutangHeader::find($id);
        $status = $hutang->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $hutang->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('hutangheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
