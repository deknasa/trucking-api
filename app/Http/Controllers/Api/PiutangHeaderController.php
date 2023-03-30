<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PiutangHeader;
use App\Http\Requests\StorePiutangHeaderRequest;
use App\Http\Requests\UpdatePiutangHeaderRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;


use App\Http\Requests\StoreLogTrailRequest;
use App\Models\LogTrail;
use App\Http\Requests\StorePiutangDetailRequest;
use App\Models\InvoiceHeader;
use App\Models\PiutangDetail;

use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\UpdateJurnalUmumHeaderRequest;
use App\Models\Agen;
use App\Models\Parameter;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class PiutangHeaderController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $piutang = new PiutangHeader();

        return response([
            'data' => $piutang->get(),
            'attributes' => [
                'totalRows' => $piutang->totalRows,
                'totalPages' => $piutang->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StorePiutangHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $tanpaprosesnobukti = $request->tanpaprosesnobukti ?? 0;

            if ($tanpaprosesnobukti == 0) {
                $group = 'PIUTANG BUKTI';
                $subgroup = 'PIUTANG BUKTI';

                $format = DB::table('parameter')
                    ->where('grp', $group)
                    ->where('subgrp', $subgroup)
                    ->first();

                $content = new Request();
                $content['group'] = $group;
                $content['subgroup'] = $subgroup;
                $content['table'] = 'piutangheader';
                $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
            }

            $piutang = new PiutangHeader();

            if ($tanpaprosesnobukti == 1) {
                $piutang->nobukti = $request->nobukti;
            }
            $getCoa = Agen::from(DB::raw("agen with (readuncommitted)"))->where('id', $request->agen_id)->first();

            $statusCetak = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

            $piutang->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $piutang->postingdari = $request->postingdari ?? 'ENTRY PIUTANG';
            $piutang->invoice_nobukti = $request->invoice_nobukti ?? '';
            $piutang->modifiedby = auth('api')->user()->name;
            $piutang->statusformat = $request->statusformat ?? $format->id;
            $piutang->agen_id = $request->agen_id;
            $piutang->coadebet = $getCoa->coa;
            $piutang->coakredit = $getCoa->coapendapatan;
            $piutang->statuscetak = $statusCetak->id;
            $piutang->userbukacetak = '';
            $piutang->tglbukacetak = '';
            $piutang->nominal = ($tanpaprosesnobukti == 0) ? array_sum($request->nominal_detail) : $request->nominal;

            if ($tanpaprosesnobukti == 0) {
                $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
                $piutang->nobukti = $nobukti;
            }
            $piutang->save();


            $logTrail = [
                'namatabel' => strtoupper($piutang->getTable()),
                'postingdari' => $request->postingdari ?? 'ENTRY PIUTANG HEADER',
                'idtrans' => $piutang->id,
                'nobuktitrans' => $piutang->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $piutang->toArray(),
                'modifiedby' => $piutang->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            $idLogTrail = $storedLogTrail['id'];


            /* Store detail */
            $detaillog = [];
            if ($request->datadetail != '') {
                $counter = $request->datadetail;
            } else {
                $counter = $request->nominal_detail;
            }
            for ($i = 0; $i < count($counter); $i++) {
                $datadetail = [
                    'piutang_id' => $piutang->id,
                    'nobukti' => $piutang->nobukti,
                    'nominal' => ($request->datadetail != '') ? $request->datadetail[$i]['nominal'] : $request->nominal_detail[$i],
                    'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan'] : $request->keterangan_detail[$i],
                    'invoice_nobukti' => ($request->datadetail != '') ? $request->datadetail[$i]['invoice_nobukti'] : '',
                    'modifiedby' => $piutang->modifiedby,
                ];

                // STORE 
                $data = new StorePiutangDetailRequest($datadetail);

                $datadetails = app(PiutangDetailController::class)->store($data);

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
                'postingdari' => $request->postingdari ?? 'ENTRY PIUTANG DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $piutang->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => $piutang->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            $parameterController = new ParameterController;
            $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');

            $jenisinvoice = $request->jenisinvoice ?? '';

            // if ($jenisinvoice == 'UTAMA') {
            //     $coapiutang = DB::table('parameter')->from(
            //         DB::raw("parameter with (readuncommitted)")
            //     )->where('grp', 'JURNAL PIUTANG INVOICE UTAMA')->get();
            // } else if ($jenisinvoice == 'TAMBAHAN') {
            //     $coapiutang = DB::table('parameter')->from(
            //         DB::raw("parameter with (readuncommitted)")
            //     )->where('grp', 'JURNAL PIUTANG INVOICE TAMBAHAN')->get();
            // } else {
            //     $coapiutang = DB::table('parameter')->from(
            //         DB::raw("parameter with (readuncommitted)")
            //     )->where('grp', 'JURNAL PIUTANG MANUAL')->get();
            // }

            $jurnalHeader = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $piutang->nobukti,
                'tglbukti' => date('Y-m-d', strtotime($piutang->tglbukti)),
                'postingdari' => $request->postingdari ?? "ENTRY PIUTANG",
                'statusapproval' => $statusApp->id,
                'userapproval' => "",
                'tglapproval' => "",
                'modifiedby' => auth('api')->user()->name,
                'statusformat' => "0",
            ];

            $jurnaldetail = [];

            for ($i = 0; $i < count($counter); $i++) {

                $jurnalDetail = [
                    [
                        'nobukti' => $piutang->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($piutang->tglbukti)),
                        'coa' =>  $getCoa->coa,
                        'nominal' => ($request->datadetail != '') ? $request->datadetail[$i]['nominal'] : $request->nominal_detail[$i],
                        'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan'] : $request->keterangan_detail[$i],
                        'modifiedby' => auth('api')->user()->name,
                        'baris' => $i,
                    ],
                    [
                        'nobukti' => $piutang->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($piutang->tglbukti)),
                        'coa' => $getCoa->coapendapatan,
                        'nominal' => ($request->datadetail != '') ? '-' . $request->datadetail[$i]['nominal'] : '-' . $request->nominal_detail[$i],
                        'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan'] : $request->keterangan_detail[$i],
                        'modifiedby' => auth('api')->user()->name,
                        'baris' => $i,
                    ]
                ];
                $jurnaldetail = array_merge($jurnaldetail, $jurnalDetail);
            }

            $jurnal = $this->storeJurnal($jurnalHeader, $jurnaldetail);

            if (!$jurnal['status']) {
                throw new \Throwable($jurnal['message']);
            }


            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($piutang, $piutang->getTable());
            $piutang->position = $selected->position;
            $piutang->page = ceil($piutang->position / ($request->limit ?? 10));



            return response([
                'status' => true,
                'idlogtrail' => $idLogTrail,
                'message' => 'Berhasil disimpan',
                'data' => $piutang
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function show(PiutangHeader $piutangHeader)
    {
        return response([
            'data' => $piutangHeader->load('piutangDetails', 'agen'),
        ]);
    }

    /**
     * @ClassName
     */
    public function update(UpdatePiutangHeaderRequest $request, PiutangHeader $piutangHeader)
    {
        DB::beginTransaction();

        try {

            $proseslain = $request->proseslain ?? 0;
            $getCoa = Agen::from(DB::raw("agen with (readuncommitted)"))->where('id', $request->agen_id)->first();

            $piutangHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $piutangHeader->modifiedby = auth('api')->user()->name;
            $piutangHeader->agen_id = $request->agen_id;
            $piutangHeader->coadebet = $getCoa->coa;
            $piutangHeader->coakredit = $getCoa->coapendapatan;
            $piutangHeader->nominal = ($proseslain != 0) ? $request->nominal : array_sum($request->nominal_detail);

            if ($piutangHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($piutangHeader->getTable()),
                    'postingdari' => $request->postingdari ?? 'EDIT PIUTANG HEADER',
                    'idtrans' => $piutangHeader->id,
                    'nobuktitrans' => $piutangHeader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $piutangHeader->toArray(),
                    'modifiedby' => $piutangHeader->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                PiutangDetail::where('piutang_id', $piutangHeader->id)->delete();

                /* Store detail */

                $detaillog = [];
                if ($request->datadetail != '') {
                    $counter = $request->datadetail;
                } else {
                    $counter = $request->nominal_detail;
                }
                for ($i = 0; $i < count($counter); $i++) {
                    $datadetail = [
                        'piutang_id' => $piutangHeader->id,
                        'nobukti' => $piutangHeader->nobukti,
                        'nominal' => ($request->datadetail != '') ? $request->datadetail[$i]['nominal'] : $request->nominal_detail[$i],
                        'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan'] : $request->keterangan_detail[$i],
                        'invoice_nobukti' => ($request->datadetail != '') ? $request->datadetail[$i]['invoice_nobukti'] : '',
                        'modifiedby' => $piutangHeader->modifiedby,
                    ];


                    //STORE

                    $data = new StorePiutangDetailRequest($datadetail);
                    $datadetails = app(PiutangDetailController::class)->store($data);

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
                    'postingdari' => $request->postingdari ?? 'EDIT PIUTANG DETAIL',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $piutangHeader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $detaillog,
                    'modifiedby' => $request->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);

                app(LogTrailController::class)->store($data);
            }

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';


            $jurnalDetail = [];

            for ($i = 0; $i < count($counter); $i++) {
                $detail = [];

                $jurnaldetail = [
                    [
                        'nobukti' => $piutangHeader->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($piutangHeader->tglbukti)),
                        'coa' => $getCoa->coa,
                        'nominal' => ($request->datadetail != '') ? $request->datadetail[$i]['nominal'] : $request->nominal_detail[$i],
                        'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan'] : $request->keterangan_detail[$i],
                        'modifiedby' => auth('api')->user()->name,
                        'baris' => $i,
                    ],
                    [
                        'nobukti' => $piutangHeader->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($piutangHeader->tglbukti)),
                        'coa' => $getCoa->coapendapatan,
                        'nominal' => ($request->datadetail != '') ? -$request->datadetail[$i]['nominal'] : -$request->nominal_detail[$i],
                        'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan'] : $request->keterangan_detail[$i],
                        'modifiedby' => auth('api')->user()->name,
                        'baris' => $i,
                    ]
                ];
                $jurnalDetail = array_merge($jurnalDetail, $jurnaldetail);
            }

            $jurnalHeader = [
                'isUpdate' => 1,
                'postingdari' => $request->postingdari ?? "EDIT PENGELUARAN KAS/BANK",
                'modifiedby' => auth('api')->user()->name,
                'datadetail' => $jurnalDetail
            ];

            $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $piutangHeader->nobukti)->first();
            $newJurnal = new JurnalUmumHeader();
            $newJurnal = $newJurnal->find($getJurnal->id);
            $jurnal = new UpdateJurnalUmumHeaderRequest($jurnalHeader);
            app(JurnalUmumHeaderController::class)->update($jurnal, $newJurnal);

            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($piutangHeader, $piutangHeader->getTable());
            $piutangHeader->position = $selected->position;
            $piutangHeader->page = ceil($piutangHeader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $piutangHeader
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

        $getDetail = PiutangDetail::lockForUpdate()->where('piutang_id', $id)->get();

        $piutangHeader = new PiutangHeader();
        $piutangHeader = $piutangHeader->lockAndDestroy($id);
        $getJurnalHeader = JurnalUmumHeader::lockForUpdate()->where('nobukti', $piutangHeader->nobukti)->first();

        $getJurnalDetail = JurnalUmumDetail::lockForUpdate()->where('nobukti', $piutangHeader->nobukti)->get();
        JurnalUmumHeader::where('nobukti', $piutangHeader->nobukti)->delete();

        if ($piutangHeader) {
            // DELETE PIUTANG HEADER
            $logTrail = [
                'namatabel' => strtoupper($piutangHeader->getTable()),
                'postingdari' => $request->postingdari ?? 'DELETE PIUTANG HEADER',
                'idtrans' => $piutangHeader->id,
                'nobuktitrans' => $piutangHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $piutangHeader->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE PIUTANG DETAIL
            $logTrailPiutangDetail = [
                'namatabel' => 'PIUTANGDETAIL',
                'postingdari' => $request->postingdari ?? 'DELETE PIUTANG DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $piutangHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailPiutangDetail = new StoreLogTrailRequest($logTrailPiutangDetail);
            app(LogTrailController::class)->store($validatedLogTrailPiutangDetail);

            // DELETE JURNAL HEADER
            $logTrailJurnalHeader = [
                'namatabel' => 'JURNALUMUMHEADER',
                'postingdari' => $request->postingdari ?? 'DELETE JURNAL UMUM HEADER DARI PIUTANG',
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
                'postingdari' => $request->postingdari ?? 'DELETE JURNAL UMUM DETAIL DARI PIUTANG',
                'idtrans' => $storedLogTrailJurnal['id'],
                'nobuktitrans' => $getJurnalHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getJurnalDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailJurnalDetail = new StoreLogTrailRequest($logTrailJurnalDetail);
            app(LogTrailController::class)->store($validatedLogTrailJurnalDetail);

            DB::commit();

            $selected = $this->getPosition($piutangHeader, $piutangHeader->getTable(), true);
            $piutangHeader->position = $selected->position;
            $piutangHeader->id = $selected->id;
            $piutangHeader->page = ceil($piutangHeader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $piutangHeader
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
                'postingdari' => $header['postingdari'],
                'idtrans' =>  $jurnals->original['idlogtrail'],
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

            throw $th;
        }
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $piutang = PiutangHeader::lockForUpdate()->findOrFail($id);
            $statusSudahCetak = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($piutang->statuscetak != $statusSudahCetak->id) {
                $piutang->statuscetak = $statusSudahCetak->id;
                $piutang->tglbukacetak = date('Y-m-d H:i:s');
                $piutang->userbukacetak = auth('api')->user()->name;

                if ($piutang->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($piutang->getTable()),
                        'postingdari' => 'PRINT PIUTANG HEADER',
                        'idtrans' => $piutang->id,
                        'nobuktitrans' => $piutang->nobukti,
                        'aksi' => 'PRINT',
                        'datajson' => $piutang->toArray(),
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


    public function cekValidasiAksi($id)
    {
        $piutangHeader = new PiutangHeader();
        $nobukti = PiutangHeader::from(DB::raw("piutangheader"))->where('id', $id)->first();
        $cekdata = $piutangHeader->cekvalidasiaksi($nobukti->nobukti);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', 'SATL')
                ->get();
            $keterangan = $query['0'];

            $data = [
                'status' => false,
                'message' => $keterangan,
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        } else {
            $invoice = $nobukti->invoice_nobukti ?? false;
            if ($invoice) {
                $query = DB::table('error')
                    ->select(
                        DB::raw("ltrim(rtrim(keterangan))+' (" . $nobukti['postingdari'] . ")' as keterangan")
                    )
                    ->where('kodeerror', '=', 'TDT')
                    ->get();
                $keterangan = $query['0'];

                $data = [
                    'status' => false,
                    'message' => $keterangan,
                    'errors' => '',
                    'kondisi' => true,
                ];
            } else {

                $data = [
                    'status' => false,
                    'message' => '',
                    'errors' => '',
                    'kondisi' => $cekdata['kondisi'],
                ];
            }

            return response($data);
        }
    }
    public function cekvalidasi($id)
    {
        $pengeluaran = PiutangHeader::find($id);
        $status = $pengeluaran->statusapproval;
        $statusApproval = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('piutangheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
