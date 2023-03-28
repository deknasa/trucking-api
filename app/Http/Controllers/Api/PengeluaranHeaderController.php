<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSupirApprovalHeader;
use App\Models\KasGantungHeader;
use App\Models\PengeluaranHeader;
use App\Models\PengeluaranDetail;
use App\Models\Parameter;
use App\Models\Pelanggan;
use App\Models\Bank;
use App\Models\AlatBayar;
use App\Models\AkunPusat;
use App\Models\LogTrail;

use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\StorePengeluaranDetailRequest;

use App\Http\Requests\UpdatePengeluaranHeaderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Models\Error;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use Exception;
use Illuminate\Database\QueryException;

class PengeluaranHeaderController extends Controller
{

    /**
     * @ClassName
     */
    public function index()
    {
        $pengeluaran = new PengeluaranHeader();

        return response([
            'data' => $pengeluaran->get(),
            'attributes' => [
                'totalRows' => $pengeluaran->totalRows,
                'totalPages' => $pengeluaran->totalPages
            ]
        ]);
    }


    public function default()
    {


        $pengeluaranheader = new PengeluaranHeader();
        return response([
            'status' => true,
            'data' => $pengeluaranheader->default(),
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StorePengeluaranHeaderRequest $request)
    {
        //    dd($request->all());
        DB::beginTransaction();

        try {
            /* Store header */

            $tanpaprosesnobukti = $request->tanpaprosesnobukti ?? 0;

            // dd($request->bank_id);
            if ($tanpaprosesnobukti == 0) {
                $content = new Request();
                $bankid = $request->bank_id;
                $querysubgrppengeluaran = Bank::from(DB::raw("bank with (readuncommitted)"))
                    ->select(
                        'parameter.grp',
                        'parameter.subgrp',
                        'bank.formatpengeluaran',
                        'bank.coa',
                        'bank.tipe'
                    )
                    ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpengeluaran', 'parameter.id')
                    ->whereRaw("bank.id = $bankid")
                    ->first();

                $content['group'] = $querysubgrppengeluaran->grp;
                $content['subgroup'] = $querysubgrppengeluaran->subgrp;
                $content['table'] = 'pengeluaranheader';
                $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
                $alatBayar = AlatBayar::from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'TRANSFER')->first();
                if ($querysubgrppengeluaran->tipe == 'BANK' && $alatBayar->id == $request->alatbayar_id) {
                    $request->validate([
                        'transferkeac' => 'required',
                        'transferkean' => 'required',
                        'transferkebank' => 'required',
                    ]);
                }
            }
            $pengeluaranHeader = new PengeluaranHeader();

            if ($tanpaprosesnobukti == 1) {
                $pengeluaranHeader->nobukti = $request->nobukti;
            }


            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();


            $pengeluaranHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pengeluaranHeader->pelanggan_id = $request->pelanggan_id ?? 0;
            $pengeluaranHeader->postingdari = $request->postingdari ?? 'ENTRY PENGELUARAN KAS/BANK';
            $pengeluaranHeader->statusapproval = $statusApproval->id ?? $request->statusapproval;
            $pengeluaranHeader->dibayarke = $request->dibayarke ?? '';
            $pengeluaranHeader->alatbayar_id = $request->alatbayar_id ?? 0;
            $pengeluaranHeader->bank_id = $request->bank_id ?? 0;
            $pengeluaranHeader->userapproval = $request->userapproval ?? '';
            $pengeluaranHeader->tglapproval = $request->tglapproval ?? '';
            $pengeluaranHeader->transferkeac = $request->transferkeac ?? '';
            $pengeluaranHeader->transferkean = $request->transferkean ?? '';
            $pengeluaranHeader->transferkebank = $request->transferkebank ?? '';
            $pengeluaranHeader->statusformat = $request->statusformat ?? $querysubgrppengeluaran->formatpengeluaran;
            $pengeluaranHeader->statuscetak = $statusCetak->id;
            $pengeluaranHeader->userbukacetak = '';
            $pengeluaranHeader->tglbukacetak = '';
            $pengeluaranHeader->modifiedby = auth('api')->user()->name;

            if ($tanpaprosesnobukti == 0) {
                $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
                $pengeluaranHeader->nobukti = $nobukti;
            }


            $pengeluaranHeader->save();

            if ($tanpaprosesnobukti == 1) {
                DB::commit();
            }
            $logTrail = [
                'namatabel' => strtoupper($pengeluaranHeader->getTable()),
                'postingdari' => $request->postingdari ?? 'ENTRY PENGELUARAN HEADER',
                'idtrans' => $pengeluaranHeader->id,
                'nobuktitrans' => $pengeluaranHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $pengeluaranHeader->toArray(),
                'modifiedby' => $pengeluaranHeader->modifiedby
            ];


            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            $idLogTrail = $storedLogTrail['id'];
            /* Store detail */

            $parameterController = new ParameterController;
            $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');

            $detaillog = [];
            if ($request->datadetail != '') {
                $counter = $request->datadetail;
            } else {
                $counter = $request->nominal_detail;
            }
            for ($i = 0; $i < count($counter); $i++) {

                $datadetail = [
                    'pengeluaran_id' => $pengeluaranHeader->id,
                    'nobukti' => $pengeluaranHeader->nobukti,
                    'nowarkat' => ($request->datadetail != '') ? $request->datadetail[$i]['nowarkat']  : $request->nowarkat[$i],
                    'tgljatuhtempo' => ($request->datadetail != '') ? $request->datadetail[$i]['tgljatuhtempo'] : date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                    'nominal' => ($request->datadetail != '') ? $request->datadetail[$i]['nominal'] : $request->nominal_detail[$i],
                    'coadebet' => ($request->datadetail != '') ? $request->datadetail[$i]['coadebet'] : $request->coadebet[$i],
                    'coakredit' => ($request->datadetail != '') ? $request->datadetail[$i]['coakredit'] : $querysubgrppengeluaran->coa,
                    'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan'] : $request->keterangan_detail[$i],
                    'bulanbeban' =>  date('Y-m-d', strtotime($request->bulanbeban[$i] ?? '1900/1/1')),
                    'modifiedby' => auth('api')->user()->name,
                ];

                $data = new StorePengeluaranDetailRequest($datadetail);
                $datadetails = app(PengeluaranDetailController::class)->store($data);

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
                'postingdari' => $request->postingdari ?? 'ENTRY PENGELUARAN DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $pengeluaranHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            $jurnalHeader = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $pengeluaranHeader->nobukti,
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'postingdari' => $request->postingdari ?? "ENTRY PENGELUARAN KAS/BANK",
                'statusapproval' => $statusApp->id,
                'userapproval' => "",
                'tglapproval' => "",
                'modifiedby' => auth('api')->user()->name,
                'statusformat' => "0",
            ];

            $jurnaldetail = [];

            for ($i = 0; $i < count($counter); $i++) {
                $detail = [];

                $jurnalDetail = [
                    [
                        'nobukti' => $pengeluaranHeader->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                        'coa' => ($request->datadetail != '') ? $request->datadetail[$i]['coadebet'] ?? '-' : $request->coadebet[$i],
                        'nominal' => ($request->datadetail != '') ? $request->datadetail[$i]['nominal'] : $request->nominal_detail[$i],
                        'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan'] : $request->keterangan_detail[$i],
                        'modifiedby' => auth('api')->user()->name,
                        'baris' => $i,
                    ],
                    [
                        'nobukti' => $pengeluaranHeader->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                        'coa' => ($request->datadetail != '') ? $request->datadetail[$i]['coakredit'] : $querysubgrppengeluaran->coa,
                        'nominal' => ($request->datadetail != '') ? -$request->datadetail[$i]['nominal'] : -$request->nominal_detail[$i],
                        'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan'] : $request->keterangan_detail[$i],
                        'modifiedby' => auth('api')->user()->name,
                        'baris' => $i,
                    ]
                ];


                $jurnaldetail = array_merge($jurnaldetail, $jurnalDetail);
            }

            $jurnal = $this->storeJurnal($jurnalHeader, $jurnaldetail);


            if (!$jurnal['status']) {
                throw new Exception($jurnal['message']);
            }

            $approvalabsensisupir = $request->approvalabsensisupir ?? false;
            if ($approvalabsensisupir == true) {
                $kasgantungheader  = KasGantungHeader::lockForUpdate()->where("id", $request->kasgantungheader_id)
                    ->firstorFail();
                $kasgantungheader->pengeluaran_nobukti = $pengeluaranHeader->nobukti;
                $kasgantungheader->coakaskeluar = $request->coakaskeluar;
                $kasgantungheader->tglkaskeluar = $request->tglbukti;
                $kasgantungheader->save();
            }


            DB::commit();
            if ($tanpaprosesnobukti == 0) {

                /* Set position and page */
                $selected = $this->getPosition($pengeluaranHeader, $pengeluaranHeader->getTable());
                $pengeluaranHeader->position = $selected->position;
                $pengeluaranHeader->page = ceil($pengeluaranHeader->position / ($request->limit ?? 10));
            }
            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'idlogtrail' => $idLogTrail,
                'data' => $pengeluaranHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $data = PengeluaranHeader::findAll($id);
        $detail = PengeluaranDetail::findAll($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }

    /**
     * @ClassName
     */
    public function update(UpdatePengeluaranHeaderRequest $request, PengeluaranHeader $pengeluaranheader)
    {
        DB::beginTransaction();

        try {

            $isUpdate = $request->isUpdate ?? 0;
            /* Store header */
            if ($isUpdate == 0) {

                $bankid = $request->bank_id;
                $querysubgrppengeluaran = Bank::from(DB::raw("bank with (readuncommitted)"))
                    ->select(
                        'parameter.grp',
                        'parameter.subgrp',
                        'bank.formatpengeluaran',
                        'bank.coa',
                        'bank.tipe'
                    )
                    ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpengeluaran', 'parameter.id')
                    ->whereRaw("bank.id = $bankid")
                    ->first();

                $alatBayar = AlatBayar::from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'TRANSFER')->first();
                if ($querysubgrppengeluaran->tipe == 'BANK' && $alatBayar->id == $request->alatbayar_id) {
                    $request->validate([
                        'transferkeac' => 'required',
                        'transferkean' => 'required',
                        'transferkebank' => 'required',
                    ]);
                }
                $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
                $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();


                $pengeluaranheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
                $pengeluaranheader->pelanggan_id = $request->pelanggan_id ?? 0;
                $pengeluaranheader->statusapproval = $statusApproval->id ?? 0;
                $pengeluaranheader->statuscetak = $statusCetak->id ?? 0;
                $pengeluaranheader->dibayarke = $request->dibayarke ?? '';
                $pengeluaranheader->alatbayar_id = $request->alatbayar_id ?? 0;
                $pengeluaranheader->bank_id = $request->bank_id ?? 0;
                $pengeluaranheader->transferkeac = $request->transferkeac ?? '';
                $pengeluaranheader->transferkean = $request->transferkean ?? '';
                $pengeluaranheader->transferkebank = $request->transferkebank ?? '';
                $pengeluaranheader->modifiedby = auth('api')->user()->name;
                $pengeluaranheader->save();
            } else {
                $from = $request->from ?? '';
                if ($from != 'prosesuangjalansupir') {
                    $pengeluaranheader->dibayarke = $request->dibayarke ?? '';
                    $pengeluaranheader->save();
                }
            }


            $logTrail = [
                'namatabel' => strtoupper($pengeluaranheader->getTable()),
                'postingdari' => $request->postingdari ?? 'EDIT PENGELUARAN HEADER',
                'idtrans' => $pengeluaranheader->id,
                'nobuktitrans' => $pengeluaranheader->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $pengeluaranheader->toArray(),
                'modifiedby' => $pengeluaranheader->modifiedby
            ];


            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            /* Delete existing detail */
            PengeluaranDetail::where('nobukti', $pengeluaranheader->nobukti)->delete();
            JurnalUmumHeader::where('nobukti', $pengeluaranheader->nobukti)->delete();


            /* Store detail */
            $detaillog = [];
            if ($request->datadetail != '') {
                $counter = $request->datadetail;
            } else {
                $counter = $request->nominal_detail;
            }
            for ($i = 0; $i < count($counter); $i++) {


                $datadetail = [
                    'pengeluaran_id' => $pengeluaranheader->id,
                    'nobukti' => $pengeluaranheader->nobukti,
                    'nowarkat' => ($isUpdate != 0) ? $request->datadetail[$i]['nowarkat'] : $request->nowarkat[$i],
                    'tgljatuhtempo' => ($isUpdate != 0) ? $request->datadetail[$i]['tgljatuhtempo'] : date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                    'nominal' => ($isUpdate != 0) ? $request->datadetail[$i]['nominal'] : $request->nominal_detail[$i],
                    'coadebet' => ($isUpdate != 0) ? $request->datadetail[$i]['coadebet'] : $request->coadebet[$i],
                    'coakredit' => ($isUpdate != 0) ? $request->datadetail[$i]['coakredit'] : $querysubgrppengeluaran->coa,
                    'keterangan' => ($isUpdate != 0) ? $request->datadetail[$i]['keterangan'] : $request->keterangan_detail[$i],
                    'bulanbeban' =>  date('Y-m-d', strtotime($request->bulanbeban[$i] ?? '1900/1/1')),
                    'modifiedby' => auth('api')->user()->name,
                ];

                $data = new StorePengeluaranDetailRequest($datadetail);
                $datadetails = app(PengeluaranDetailController::class)->store($data);

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
                'postingdari' => $request->postingdari ?? 'EDIT PENGELUARAN DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $pengeluaranheader->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $detaillog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($pengeluaranheader->pengeluarandetail()) {

                $parameterController = new ParameterController;
                $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');

                $jurnalHeader = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $pengeluaranheader->nobukti,
                    'tglbukti' => date('Y-m-d', strtotime($pengeluaranheader->tglbukti)),
                    'postingdari' => $request->postingdari ?? "ENTRY PENGELUARAN KAS/BANK",
                    'statusapproval' => $statusApp->id,
                    'userapproval' => "",
                    'tglapproval' => "",
                    'modifiedby' => auth('api')->user()->name,
                    'statusformat' => "0",
                ];

                $jurnaldetail = [];

                for ($i = 0; $i < count($counter); $i++) {
                    $detail = [];

                    $jurnalDetail = [
                        [
                            'nobukti' => $pengeluaranheader->nobukti,
                            'tglbukti' => date('Y-m-d', strtotime($pengeluaranheader->tglbukti)),
                            'coa' => ($request->datadetail != '') ? $request->datadetail[$i]['coadebet'] : $request->coadebet[$i],
                            'nominal' => ($request->datadetail != '') ? $request->datadetail[$i]['nominal'] : $request->nominal_detail[$i],
                            'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan'] : $request->keterangan_detail[$i],
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $i,
                        ],
                        [
                            'nobukti' => $pengeluaranheader->nobukti,
                            'tglbukti' => date('Y-m-d', strtotime($pengeluaranheader->tglbukti)),
                            'coa' => ($request->datadetail != '') ? $request->datadetail[$i]['coakredit'] : $querysubgrppengeluaran->coa,
                            'nominal' => ($request->datadetail != '') ? -$request->datadetail[$i]['nominal'] : -$request->nominal_detail[$i],
                            'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan'] : $request->keterangan_detail[$i],
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $i,
                        ]
                    ];


                    $jurnaldetail = array_merge($jurnaldetail, $jurnalDetail);
                }

                $jurnal = $this->storeJurnal($jurnalHeader, $jurnaldetail);

                // if (!$jurnal['status'] AND @$jurnal['errorCode'] == 2601) {
                //     goto ATAS;
                // }

                if (!$jurnal['status']) {
                    throw new Exception($jurnal['message']);
                }
            }

            DB::commit();

            if ($isUpdate == 0) {
                /* Set position and page */
                $selected = $this->getPosition($pengeluaranheader, $pengeluaranheader->getTable());
                $pengeluaranheader->position = $selected->position;
                $pengeluaranheader->page = ceil($pengeluaranheader->position / ($request->limit ?? 10));
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranheader
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

        $getDetail = PengeluaranDetail::lockForUpdate()->where('pengeluaran_id', $id)->get();
        $pengeluaranHeader = new PengeluaranHeader();
        $pengeluaranHeader = $pengeluaranHeader->lockAndDestroy($id);

        $getJurnalHeader = JurnalUmumHeader::lockForUpdate()->where('nobukti', $pengeluaranHeader->nobukti)->first();
        $getJurnalDetail = JurnalUmumDetail::lockForUpdate()->where('nobukti', $pengeluaranHeader->nobukti)->get();

        JurnalUmumHeader::where('nobukti', $pengeluaranHeader->nobukti)->delete();

        if ($pengeluaranHeader) {
            $datalogtrail = [
                'namatabel' => strtoupper($pengeluaranHeader->getTable()),
                'postingdari' => $request->postingdari ?? 'DELETE PENGELUARAN HEADER',
                'idtrans' => $pengeluaranHeader->id,
                'nobuktitrans' => $pengeluaranHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $pengeluaranHeader->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            $storedLogTrail = app(LogTrailController::class)->store($data);

            // DELETE PENGELUARAN DETAIL
            $logTrailPengeluaranDetail = [
                'namatabel' => 'PENGELUARANDETAIL',
                'postingdari' => $request->postingdari ?? 'DELETE PENGELUARAN DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $pengeluaranHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailPengeluaranDetail = new StoreLogTrailRequest($logTrailPengeluaranDetail);
            app(LogTrailController::class)->store($validatedLogTrailPengeluaranDetail);

            // DELETE JURNAL HEADER
            $logTrailJurnalHeader = [
                'namatabel' => 'JURNALUMUMHEADER',
                'postingdari' => $request->postingdari ?? 'DELETE JURNAL UMUM HEADER DARI PENGELUARAN KAS/BANK',
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
                'postingdari' => $request->postingdari ?? 'DELETE JURNAL UMUM DETAIL DARI PENGELUARAN KAS/BANK',
                'idtrans' => $storedLogTrailJurnal['id'],
                'nobuktitrans' => $getJurnalHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getJurnalDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailJurnalDetail = new StoreLogTrailRequest($logTrailJurnalDetail);
            app(LogTrailController::class)->store($validatedLogTrailJurnalDetail);

            DB::commit();
            if ($request->postingdari === null) {

                $selected = $this->getPosition($pengeluaranHeader, $pengeluaranHeader->getTable(), true);
                $pengeluaranHeader->position = $selected->position;
                $pengeluaranHeader->id = $selected->id;
                $pengeluaranHeader->page = ceil($pengeluaranHeader->position / ($request->limit ?? 10));
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $pengeluaranHeader
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

        try {
            $jurnal = new StoreJurnalUmumHeaderRequest($header);
            $jurnals = app(JurnalUmumHeaderController::class)->store($jurnal);

            $detailLog = [];
            foreach ($detail as $key => $value) {
                $value['jurnalumum_id'] = $jurnals->original['data']['id'];
                $jurnal = new StoreJurnalUmumDetailRequest($value);
                $datadetails = app(JurnalUmumDetailController::class)->store($jurnal);

                $detailLog[] = $datadetails['detail']->toArray();
            }
            $datalogtrail = [
                'namatabel' => strtoupper($datadetails['tabel']),
                'postingdari' => $header['postingdari'],
                'idtrans' => $jurnals->original['idlogtrail'],
                'nobuktitrans' => $header['nobukti'],
                'aksi' => 'ENTRY',
                'datajson' => $detailLog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            return [
                'status' => true,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function approval($id)
    {
        DB::beginTransaction();

        try {
            $pengeluaranHeader = PengeluaranHeader::find($id);
            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            if ($pengeluaranHeader->statusapproval == $statusApproval->id) {
                $pengeluaranHeader->statusapproval = $statusNonApproval->id;
                $aksi = $statusNonApproval->text;
            } else {
                $pengeluaranHeader->statusapproval = $statusApproval->id;
                $aksi = $statusApproval->text;
            }

            $pengeluaranHeader->tglapproval = date('Y-m-d', time());
            $pengeluaranHeader->userapproval = auth('api')->user()->name;

            if ($pengeluaranHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pengeluaranHeader->getTable()),
                    'postingdari' => 'APPROVED KAS/BANK',
                    'idtrans' => $pengeluaranHeader->id,
                    'nobuktitrans' => $pengeluaranHeader->nobukti,
                    'aksi' => $aksi,
                    'datajson' => $pengeluaranHeader->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $pengeluaran = PengeluaranHeader::lockForUpdate()->findOrFail($id);
            $statusSudahCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($pengeluaran->statuscetak != $statusSudahCetak->id) {
                $pengeluaran->statuscetak = $statusSudahCetak->id;
                $pengeluaran->tglbukacetak = date('Y-m-d H:i:s');
                $pengeluaran->userbukacetak = auth('api')->user()->name;
                $pengeluaran->jumlahcetak = $pengeluaran->jumlahcetak + 1;

                if ($pengeluaran->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($pengeluaran->getTable()),
                        'postingdari' => 'PRINT PENGELUARAN HEADER',
                        'idtrans' => $pengeluaran->id,
                        'nobuktitrans' => $pengeluaran->nobukti,
                        'aksi' => 'PRINT',
                        'datajson' => $pengeluaran->toArray(),
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

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pengeluaranheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }


    public function cekvalidasi($id)
    {
        $pengeluaran = PengeluaranHeader::find($id);
        $status = $pengeluaran->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($status == $statusApproval->id) {
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->whereRaw("kodeerror = 'SAP'")
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
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->whereRaw("kodeerror = 'SDC'")
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

    public function cekValidasiAksi($id)
    {
        $pengeluaranHeader = new PengeluaranHeader();
        $nobukti = PengeluaranHeader::from(DB::raw("pengeluaranheader"))->where('id', $id)->first();
        $cekdata = $pengeluaranHeader->cekvalidasiaksi($nobukti->nobukti);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', $cekdata['kodeerror'])
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

            $data = [
                'status' => false,
                'message' => '',
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        }
    }
}
