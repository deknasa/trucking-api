<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePenerimaanHeaderRequest;
use App\Http\Requests\StorePenerimaanTruckingHeaderRequest;
use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\StorePengeluaranTruckingHeaderRequest;
use App\Http\Requests\StorePengembalianKasGantungHeaderRequest;
use App\Http\Requests\StoreProsesUangJalanSupirDetailRequest;
use App\Models\ProsesUangJalanSupirHeader;
use App\Http\Requests\StoreProsesUangJalanSupirHeaderRequest;
use App\Http\Requests\UpdatePenerimaanHeaderRequest;
use App\Http\Requests\UpdatePenerimaanTruckingHeaderRequest;
use App\Http\Requests\UpdatePengeluaranHeaderRequest;
use App\Http\Requests\UpdatePengeluaranTruckingHeaderRequest;
use App\Http\Requests\UpdatePengembalianKasGantungHeaderRequest;
use App\Http\Requests\UpdateProsesUangJalanSupirHeaderRequest;
use App\Models\AbsensiSupirHeader;
use App\Models\AlatBayar;
use App\Models\Bank;
use App\Models\Error;
use App\Models\Parameter;
use App\Models\PenerimaanHeader;
use App\Models\PenerimaanTrucking;
use App\Models\PenerimaanTruckingHeader;
use App\Models\PengeluaranHeader;
use App\Models\PengeluaranTrucking;
use App\Models\PengeluaranTruckingDetail;
use App\Models\PengeluaranTruckingHeader;
use App\Models\PengembalianKasGantungHeader;
use App\Models\ProsesUangJalanSupirDetail;
use App\Models\Supir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProsesUangJalanSupirHeaderController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $prosesUangJalanSupir = new ProsesUangJalanSupirHeader();
        return response([
            'data' => $prosesUangJalanSupir->get(),
            'attributes' => [
                'totalRows' => $prosesUangJalanSupir->totalRows,
                'totalPages' => $prosesUangJalanSupir->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StoreProsesUangJalanSupirHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            if ($request->nilaideposit > 0 || $request->keterangandeposit != '') {
                $request->validate(
                    [
                        'nilaideposit' => 'required|gt:0',
                        'keterangandeposit' => 'required',
                        'bankdeposit' => 'required'
                    ],
                    [
                        'nilaideposit.gt' => 'nilai deposit harus lebih besar dari 0',
                        'keterangandeposit.required' => 'keterangan deposit ' . app(ErrorController::class)->geterror('WI')->keterangan,
                        'bankdeposit.required' => 'bank deposit ' . app(ErrorController::class)->geterror('WI')->keterangan,
                    ]
                );
            }
            if ($request->pjt_id) {
                $request->validate(
                    [
                        'nombayar' => 'required|array',
                        'nombayar.*' => 'required|gt:0',
                        'keteranganpinjaman' => 'required|array',
                        'keteranganpinjaman.*' => 'required',
                        'bankpengembalian' => 'required'
                    ],
                    [
                        'nombayar.*.gt' => 'nominal bayar harus lebih besar dari 0',
                        'keteranganpinjaman.*.required' => 'keterangan pinjaman ' . app(ErrorController::class)->geterror('WI')->keterangan,
                        'bankpengembalian.required' => 'bank pengembalian ' . app(ErrorController::class)->geterror('WI')->keterangan,
                    ]
                );
                for ($i = 0; $i < count($request->pjt_id); $i++) {
                   
                    if ($request->sisa[$i] < 0) {

                        $query =  Error::from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'STM')
                            ->first();
                        return response([
                            'errors' => [
                                "nombayar.$i" => ["$query->keterangan"]
                            ],
                            'message' => "sisa",
                        ], 422);
                    }
                }
            }

            $nilaiTransfer = array_sum($request->nilaitransfer);
            $nilaiDeposit = $request->nilaideposit ?? 0;
            $nilaiPinjaman = ($request->pjt_id) ? array_sum($request->nombayar) : 0;

            $total = $nilaiTransfer - $nilaiDeposit - $nilaiPinjaman;
            $dataAbsensiSupir = AbsensiSupirHeader::from(DB::raw("absensisupirheader with (readuncommitted)"))->where('nobukti', $request->absensisupir)->first();

            if ($dataAbsensiSupir->nominal != $total) {
                $query =  Error::from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'NTC')
                    ->first();
                return response([
                    'errors' => true,
                    'message' => "$query->keterangan",
                    'total' => "$nilaiTransfer, $nilaiDeposit, $nilaiPinjaman",
                ], 500);
            }

            $group = 'PROSES UANG JALAN BUKTI';
            $subgroup = 'PROSES UANG JALAN BUKTI';

            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'prosesuangjalansupirheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];

            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();


            $prosesUangJalan = new ProsesUangJalanSupirHeader();
            $prosesUangJalan->nobukti = $nobukti;
            $prosesUangJalan->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $prosesUangJalan->absensisupir_nobukti = $request->absensisupir;
            $prosesUangJalan->trado_id = $request->trado_id;
            $prosesUangJalan->supir_id = $request->supir_id;
            $prosesUangJalan->nominaluangjalan = $dataAbsensiSupir->nominal;
            $prosesUangJalan->statusapproval = $statusApproval->id;
            $prosesUangJalan->statusformat = $format->id;
            $prosesUangJalan->modifiedby = auth('api')->user()->name;

            $prosesUangJalan->save();

            $namaSupir = Supir::from(DB::raw("supir with (readuncommitted)"))->select('namasupir')->where('id', $request->supir_id)->first();

            $logTrail = [
                'namatabel' => strtoupper($prosesUangJalan->getTable()),
                'postingdari' => 'ENTRY PROSES UANG JALAN SUPIR HEADER',
                'idtrans' => $prosesUangJalan->id,
                'nobuktitrans' => $prosesUangJalan->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $prosesUangJalan->toArray(),
                'modifiedby' => $prosesUangJalan->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

            $statusBerkas = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS BERKAS')->where('text', 'TIDAK ADA BERKAS')->first();

            $statusTransfer = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'TRANSFER')->first();
            $statusAdjust = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'ADJUST TRANSFER')->first();
            $statusPengembalian = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'PENGEMBALIAN PINJAMAN')->first();
            $statusDeposit = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'DEPOSITO SUPIR')->first();

            $detailLog = [];
            //INSERT PENGELUARAN DARI LIST TRANSFER            
            $detaillogTransfer = [];
            for ($i = 0; $i < count($request->nilaitransfer); $i++) {
                $content = new Request();
                $bankid = $request->bank_idtransfer[$i];
                $coatransfer = Bank::from(DB::raw("bank with (readuncommitted)"))->where('id', $bankid)->first();
                // PENGELUARAN TRUCKING HEADER
                $fetchFormatBLS =  DB::table('pengeluarantrucking')
                    ->where('kodepengeluaran', 'BLS')
                    ->first();
                $statusformatBLS = $fetchFormatBLS->format;

                $fetchGrpBLS = Parameter::where('id', $statusformatBLS)->first();

                $formatBLS = DB::table('parameter')
                    ->where('grp', $fetchGrpBLS->grp)
                    ->where('subgrp', $fetchGrpBLS->subgrp)
                    ->first();

                $contentBLS = new Request();
                $contentBLS['group'] = $fetchGrpBLS->grp;
                $contentBLS['subgroup'] = $fetchGrpBLS->subgrp;
                $contentBLS['table'] = 'pengeluarantruckingheader';
                $contentBLS['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
                $nobuktiPengeluaranTrucking = app(Controller::class)->getRunningNumber($contentBLS)->original['data'];

                $pengeluaranTruckingDetail = [];
                $pengeluaranTruckingDetail[] = [
                    'supir_id' => $request->supir_id,
                    'penerimaantruckingheader_nobukti' => '',
                    'nominal' => $request->nilaitransfer[$i],
                    'keterangan' => $request->keterangantransfer[$i]
                ];
                $pengeluaranTruckingHeader = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $nobuktiPengeluaranTrucking,
                    'tglbukti' => date('Y-m-d', strtotime($request->tgltransfer[$i])),
                    'pengeluarantrucking_id' => $fetchFormatBLS->id,
                    'bank_id' => $bankid,
                    'coa' => $coatransfer->coa,
                    'pengeluaran_nobukti' => '',
                    'statusformat' => $formatBLS->id,
                    'postingdari' => 'ENTRY PROSES UANG JALAN',
                    'datadetail' => $pengeluaranTruckingDetail
                ];

                $pengeluaranTrucking = new StorePengeluaranTruckingHeaderRequest($pengeluaranTruckingHeader);
                $dataPengeluaran = app(PengeluaranTruckingHeaderController::class)->store($pengeluaranTrucking);
                
                $datadetail = [
                    'prosesuangjalansupir_id' => $prosesUangJalan->id,
                    'nobukti' => $prosesUangJalan->nobukti,
                    'penerimaantrucking_bank_id' => '',
                    'penerimaantrucking_tglbukti' => '',
                    'penerimaantrucking_nobukti' => '',
                    'pengeluarantrucking_bank_id' => $bankid,
                    'pengeluarantrucking_tglbukti' => date('Y-m-d', strtotime($request->tgltransfer[$i])),
                    'pengeluarantrucking_nobukti' => $dataPengeluaran->original['data']['pengeluaran_nobukti'],
                    'pengembaliankasgantung_bank_id' => '',
                    'pengembaliankasgantung_tglbukti' => '',
                    'pengembaliankasgantung_nobukti' => '',
                    'statusprosesuangjalan' => $statusTransfer->id,
                    'nominal' => $request->nilaitransfer[$i],
                    'keterangan' => $request->keterangantransfer[$i],
                    'modifiedby' => $prosesUangJalan->modifiedby,

                ];

                //STORE PROSES UANG JALAN DETAIL
                $data = new StoreProsesUangJalanSupirDetailRequest($datadetail);
                $datadetails = app(ProsesUangJalanSupirDetailController::class)->store($data);

                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }
                $detailLog[] = $datadetails['detail']->toArray();
            }
            // END PENGELUARAN DARI LIST TRANSFER 

            // INSERT PENGEMBALIAN KAS GANTUNG
            $detailPengembalianKasgantung[] = [
                'nominal' => $request->nilaiadjust,
                'keterangandetail' => $request->keteranganadjust,
                'kasgantung_nobukti' => $dataAbsensiSupir->kasgantung_nobukti
            ];

            $pengembalianKasgantung = [
                'tglbukti' => date('Y-m-d', strtotime($request->tgladjust)),
                'pelanggan_id' => '',
                'bank_id' => $request->bank_idadjust,
                'postingdari' => 'ENTRY PROSES UANG JALAN SUPIR',
                'tglkasmasuk' => date('Y-m-d', strtotime($request->tgladjust)),
                'modifiedby' => auth('api')->user()->name,
                'datadetail' => $detailPengembalianKasgantung,
                'diterimadari' => $namaSupir->namasupir
            ];

            $pengembalianKasgantung = new StorePengembalianKasGantungHeaderRequest($pengembalianKasgantung);
            $dataKasgantung = app(PengembalianKasGantungHeaderController::class)->store($pengembalianKasgantung);

            $datadetail = [
                'prosesuangjalansupir_id' => $prosesUangJalan->id,
                'nobukti' => $prosesUangJalan->nobukti,
                'penerimaantrucking_bank_id' => $request->bank_idadjust,
                'penerimaantrucking_tglbukti' => date('Y-m-d', strtotime($request->tgladjust)),
                'penerimaantrucking_nobukti' => $dataKasgantung->original['data']['penerimaan_nobukti'],
                'pengeluarantrucking_bank_id' => '',
                'pengeluarantrucking_tglbukti' => '',
                'pengeluarantrucking_nobukti' => '',
                'pengembaliankasgantung_bank_id' => $request->bank_idadjust,
                'pengembaliankasgantung_tglbukti' => date('Y-m-d', strtotime($request->tgladjust)),
                'pengembaliankasgantung_nobukti' => $dataKasgantung->original['data']['nobukti'],
                'statusprosesuangjalan' => $statusAdjust->id,
                'nominal' => $request->nilaiadjust,
                'keterangan' => $request->keteranganadjust,
                'modifiedby' => $prosesUangJalan->modifiedby,

            ];

            //STORE PROSES UANG JALAN DETAIL
            $data = new StoreProsesUangJalanSupirDetailRequest($datadetail);
            $datadetailsAdjust = app(ProsesUangJalanSupirDetailController::class)->store($data);

            if ($datadetailsAdjust['error']) {
                return response($datadetailsAdjust, 422);
            } else {
                $iddetail = $datadetailsAdjust['id'];
                $tabeldetail = $datadetailsAdjust['tabel'];
            }
            $detailLog[] = $datadetailsAdjust['detail']->toArray();

            // END PENERIMAAN DARI ADJUST TRANSFER / PENGEMBALIAN KAS GANTUNG

            // INSERT PENERIMAAN DARI DEPOSITO
            $content = new Request();
            $bankidDeposit = $request->bank_iddeposit;
            if ($bankidDeposit != '') {


                // INSERT PENERIMAAN TRUCKING DEPOSITO
                $fetchFormatDPO = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))
                    ->where('kodepenerimaan', 'DPO')
                    ->first();
                $statusformatDPO = $fetchFormatDPO->format;
                $fetchGrpDPO = Parameter::where('id', $statusformatDPO)->first();
                $formatDPO = DB::table('parameter')
                    ->where('grp', $fetchGrpDPO->grp)
                    ->where('subgrp', $fetchGrpDPO->subgrp)
                    ->first();

                $contentDPO = new Request();
                $contentDPO['group'] = $fetchGrpDPO->grp;
                $contentDPO['subgroup'] = $fetchGrpDPO->subgrp;
                $contentDPO['table'] = 'penerimaantruckingheader';
                $contentDPO['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                $nobuktiPenerimaanTruckingDPO = app(Controller::class)->getRunningNumber($contentDPO)->original['data'];

                $penerimaanTruckingDetailDPO[] = [
                    'supir_id' => $request->supir_id,
                    'pengeluarantruckingheader_nobukti' => '',
                    'nominal' => $request->nilaideposit,
                    'keterangan' => $request->keterangandeposit
                ];
                $penerimaanTruckingHeaderDPO = [
                    'tanpaprosesnobukti' => '1',
                    'nobukti' => $nobuktiPenerimaanTruckingDPO,
                    'tglbukti' => date('Y-m-d', strtotime($request->tgldeposit)),
                    'penerimaantrucking_id' => $fetchFormatDPO->id,
                    'bank_id' => $bankidDeposit,
                    'coa' => $fetchFormatDPO->coapostingkredit,
                    'penerimaan_nobukti' => '',
                    'statusformat' => $formatDPO->id,
                    'postingdari' => 'ENTRY PROSES UANG JALAN',
                    'diterimadari' => $namaSupir->namasupir,
                    'datadetail' => $penerimaanTruckingDetailDPO
                ];
                $penerimaanTruckingDeposit = new StorePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderDPO);
                $dataPenerimaanDepo = app(PenerimaanTruckingHeaderController::class)->store($penerimaanTruckingDeposit);
                
                $datadetail = [
                    'prosesuangjalansupir_id' => $prosesUangJalan->id,
                    'nobukti' => $prosesUangJalan->nobukti,
                    'penerimaantrucking_bank_id' => $bankidDeposit,
                    'penerimaantrucking_tglbukti' => date('Y-m-d', strtotime($request->tgldeposit)),
                    'penerimaantrucking_nobukti' => $dataPenerimaanDepo->original['data']['penerimaan_nobukti'],
                    'pengeluarantrucking_bank_id' => '',
                    'pengeluarantrucking_tglbukti' => '',
                    'pengeluarantrucking_nobukti' => '',
                    'pengembaliankasgantung_bank_id' => '',
                    'pengembaliankasgantung_tglbukti' => '',
                    'pengembaliankasgantung_nobukti' => '',
                    'statusprosesuangjalan' => $statusDeposit->id,
                    'nominal' => $request->nilaideposit,
                    'keterangan' => $request->keterangandeposit,
                    'modifiedby' => $prosesUangJalan->modifiedby,

                ];

                //STORE PROSES UANG JALAN DETAIL
                $data = new StoreProsesUangJalanSupirDetailRequest($datadetail);
                $datadetailsDeposit = app(ProsesUangJalanSupirDetailController::class)->store($data);

                if ($datadetailsDeposit['error']) {
                    return response($datadetailsDeposit, 422);
                } else {
                    $iddetail = $datadetailsDeposit['id'];
                    $tabeldetail = $datadetailsDeposit['tabel'];
                }
                $detailLog[] = $datadetailsDeposit['detail']->toArray();
            }

            // INSERT PENERIMAAN DARI PENGEMBALIAN PINJAMAN
            $detaillogPinjaman = [];

            if ($request->pjt_id) {

                for ($i = 0; $i < count($request->pjt_id); $i++) {
                    $contentPinjaman = new Request();
                    $bankidPengembalian = $request->bank_idpengembalian;

                    // PENERIMAAN TRUCKING HEADER
                    $fetchFormatPJP =  DB::table('penerimaantrucking')
                        ->where('kodepenerimaan', 'PJP')
                        ->first();
                    $statusformaPJP = $fetchFormatPJP->format;

                    $fetchGrpPJP = Parameter::where('id', $statusformaPJP)->first();

                    $formatPJP = DB::table('parameter')
                        ->where('grp', $fetchGrpPJP->grp)
                        ->where('subgrp', $fetchGrpPJP->subgrp)
                        ->first();

                    $contentPJP = new Request();
                    $contentPJP['group'] = $fetchGrpPJP->grp;
                    $contentPJP['subgroup'] = $fetchGrpPJP->subgrp;
                    $contentPJP['table'] = 'penerimaantruckingheader';
                    $contentPJP['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
                    $nobuktiPenerimaanTruckingPJP = app(Controller::class)->getRunningNumber($contentPJP)->original['data'];

                    $penerimaanTruckingDetailPJP = [];
                    $penerimaanTruckingDetailPJP[] = [
                        'supir_id' => $request->supir_id,
                        'pengeluarantruckingheader_nobukti' => $request->pengeluarantruckingheader_nobukti[$i],
                        'nominal' => $request->nombayar[$i],
                        'keterangan' => $request->keteranganpinjaman[$i]
                    ];
                    $penerimaanTruckingHeaderPJP = [
                        'tanpaprosesnobukti' => 1,
                        'nobukti' => $nobuktiPenerimaanTruckingPJP,
                        'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                        'penerimaantrucking_id' => $fetchFormatPJP->id,
                        'bank_id' => $bankidPengembalian,
                        'coa' => $fetchFormatPJP->coapostingkredit,
                        'penerimaan_nobukti' => '',
                        'statusformat' => $formatPJP->id,
                        'postingdari' => 'ENTRY PROSES UANG JALAN',
                        'diterimadari' => $namaSupir->namasupir,
                        'datadetail' => $penerimaanTruckingDetailPJP
                    ];

                    $penerimaanTruckingPJP = new StorePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderPJP);
                    $dataPenerimaanPinjaman = app(PenerimaanTruckingHeaderController::class)->store($penerimaanTruckingPJP);

                    
                    $datadetail = [
                        'prosesuangjalansupir_id' => $prosesUangJalan->id,
                        'nobukti' => $prosesUangJalan->nobukti,
                        'penerimaantrucking_bank_id' => $bankidPengembalian,
                        'penerimaantrucking_tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                        'penerimaantrucking_nobukti' => $dataPenerimaanPinjaman->original['data']['penerimaan_nobukti'],
                        'pengeluarantrucking_bank_id' => '',
                        'pengeluarantrucking_tglbukti' => '',
                        'pengeluarantrucking_nobukti' => '',
                        'pengembaliankasgantung_bank_id' => '',
                        'pengembaliankasgantung_tglbukti' => '',
                        'pengembaliankasgantung_nobukti' => '',
                        'statusprosesuangjalan' => $statusPengembalian->id,
                        'nominal' => $request->nombayar[$i],
                        'keterangan' => $request->keteranganpinjaman[$i],
                        'modifiedby' => $prosesUangJalan->modifiedby,

                    ];

                    //STORE PROSES UANG JALAN DETAIL
                    $data = new StoreProsesUangJalanSupirDetailRequest($datadetail);
                    $datadetailsPinjaman = app(ProsesUangJalanSupirDetailController::class)->store($data);
                    $detailLog[] = $datadetailsPinjaman['detail']->toArray();
                }
            }
            // END PENERIMAAN PENGEMBALIAN PINJAMAN

            $datalogtrail = [
                'namatabel' => strtoupper($tabeldetail),
                'postingdari' => 'ENTRY PROSES UANG JALAN SUPIR DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $prosesUangJalan->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detailLog,
                'modifiedby' => $request->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();

            /* Set position and page */


            $selected = $this->getPosition($prosesUangJalan, $prosesUangJalan->getTable());
            $prosesUangJalan->position = $selected->position;
            $prosesUangJalan->page = ceil($prosesUangJalan->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $prosesUangJalan
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function show($id)
    {
        $data = ProsesUangJalanSupirHeader::findAll($id);
        $detail = new ProsesUangJalanSupirDetail();

        return response([
            'status' => true,
            'data' => $data,
            'detail' => [
                'transfer' => $detail->findTransfer($id),
                'adjust' => $detail->adjustTransfer($id),
                'deposito' => $detail->deposito($id),
                'pengembalian' => $detail->pengembalian($id),
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function update(UpdateProsesUangJalanSupirHeaderRequest $request, ProsesUangJalanSupirHeader $prosesuangjalansupirheader)
    {
        DB::beginTransaction();

        try {
            $prosesuangjalansupirheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $prosesuangjalansupirheader->modifiedby = auth('api')->user()->name;
            $prosesuangjalansupirheader->save();

            $logTrail = [
                'namatabel' => strtoupper($prosesuangjalansupirheader->getTable()),
                'postingdari' => 'EDIT PROSES UANG JALAN SUPIR HEADER',
                'idtrans' => $prosesuangjalansupirheader->id,
                'nobuktitrans' => $prosesuangjalansupirheader->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $prosesuangjalansupirheader->toArray(),
                'modifiedby' => $prosesuangjalansupirheader->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            $id = $prosesuangjalansupirheader->id;
            $detail = new ProsesUangJalanSupirDetail();
            $detailTransfer = $detail->findTransfer($id);

            $detailLog = [];
            foreach ($detailTransfer as $key => $value) {
                $pengeluarantrucking_nobukti = $value['pengeluarantrucking_nobukti'];
                $fetchFormatBLS =  DB::table('pengeluarantrucking')
                    ->where('kodepengeluaran', 'BLS')
                    ->first();

                $getPengeluaranTrucking = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))->where("pengeluaran_nobukti", $pengeluarantrucking_nobukti)->first();
                $pengeluaranTruckingDetailTransfer = [];
                $pengeluaranTruckingDetailTransfer[] = [
                    'supir_id' => $prosesuangjalansupirheader->supir_id,
                    'penerimaantruckingheader_nobukti' => '',
                    'nominal' => $value['nominal'],
                    'keterangan' => $request->keterangantransfer[$key]
                ];
                $pengeluaranTruckingTransfer = [
                    'isUpdate' => 1,
                    'postingdari' => 'EDIT PROSES UANG JALAN SUPIR',
                    'datadetail' => $pengeluaranTruckingDetailTransfer,
                    'coa' => $fetchFormatBLS->coapostingdebet,
                ];

                $newPengeluaranTrucking = new PengeluaranTruckingHeader();
                $newPengeluaranTrucking = $newPengeluaranTrucking->findAll($getPengeluaranTrucking->id);
                $pengeluaranTrucking = new UpdatePengeluaranTruckingHeaderRequest($pengeluaranTruckingTransfer);
                app(PengeluaranTruckingHeaderController::class)->update($pengeluaranTrucking, $newPengeluaranTrucking);

                $editProsesDetailTransfer = ProsesUangJalanSupirDetail::find($value['idtransfer']);
                $editProsesDetailTransfer->keterangan = $request->keterangantransfer[$key];
                $editProsesDetailTransfer->update();

                $detailLog[] = $editProsesDetailTransfer->toArray();
            }
            // UPDATE ADJUST 
            $dataAbsensiSupir = AbsensiSupirHeader::from(DB::raw("absensisupirheader with (readuncommitted)"))->where('nobukti', $prosesuangjalansupirheader->absensisupir_nobukti)->first();

            $detailAdjust = $detail->adjustTransfer($id);
            $bankAdjust = Bank::from(DB::raw("bank with (readuncommitted)"))
                ->select('bank.coa')->whereRaw("bank.id = $detailAdjust->bank_idadjust")
                ->first();
            $coaKasMasuk = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('memo')->where('grp', 'JURNAL PENGEMBALIAN KAS GANTUNG')->where('subgrp', 'KREDIT')->first();
            $memoAdjust = json_decode($coaKasMasuk->memo, true);
            $penerimaanAdjust = $detailAdjust->penerimaan_nobukti;
            $detailPengembalianKasgantung[] = [
                'nominal' => $detailAdjust->nilaiadjust,
                'keterangandetail' => $request->keteranganadjust,
                'kasgantung_nobukti' => $dataAbsensiSupir->kasgantung_nobukti,
                'coadebet' => $bankAdjust->coa,
                'coakredit' => $memoAdjust['JURNAL']
            ];

            $pengembalianKasgantung = [
                'isUpdate' => 1,
                'from' => 'prosesuangjalansupir',
                'bank_id' => $detailAdjust->bank_idadjust,
                'postingdari' => 'EDIT PROSES UANG JALAN SUPIR',
                'penerimaanheader_nobukti' => $penerimaanAdjust,
                'datadetail' => $detailPengembalianKasgantung
            ];
            $getPengembalianKasgantung = PengembalianKasGantungHeader::from(DB::raw("pengembaliankasgantungheader with (readuncommitted)"))->where("penerimaan_nobukti", $penerimaanAdjust)->first();
            $newPengembalianKasgantung = new PengembalianKasGantungHeader();
            $newPengembalianKasgantung = $newPengembalianKasgantung->findAll($getPengembalianKasgantung->id);
            $pengembalianKasgantung = new UpdatePengembalianKasGantungHeaderRequest($pengembalianKasgantung);
            app(PengembalianKasGantungHeaderController::class)->update($pengembalianKasgantung, $newPengembalianKasgantung, $getPengembalianKasgantung->id);

            $editProsesDetailAdjust = ProsesUangJalanSupirDetail::find($detailAdjust->idadjust);
            $editProsesDetailAdjust->keterangan = $request->keteranganadjust;
            $editProsesDetailAdjust->update();

            $detailLog[] = $editProsesDetailAdjust->toArray();

            // UPDATE DEPOSITO
            $detailDeposito = $detail->deposito($id);
            if ($detailDeposito != null) {
                $fetchFormatDPO = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))
                    ->where('kodepenerimaan', 'DPO')
                    ->first();
                $getPenerimaanTruckingDPO = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where("penerimaan_nobukti", $detailDeposito->penerimaandeposit_nobukti)->first();
                $penerimaanTruckingDetailDPO = [];
                $penerimaanTruckingDetailDPO[] = [
                    'supir_id' => $prosesuangjalansupirheader->supir_id,
                    'pengeluarantruckingheader_nobukti' => '',
                    'nominal' => $detailDeposito->nilaideposit,
                    'keterangan' => $request->keterangandeposit
                ];
                $penerimaanTruckingDPO = [
                    'isUpdate' => 1,
                    'postingdari' => 'EDIT PROSES UANG JALAN SUPIR',
                    'coa' => $fetchFormatDPO->coapostingkredit,
                    'datadetail' => $penerimaanTruckingDetailDPO
                ];

                $newPenerimaanTruckingDPO = new PenerimaanTruckingHeader();
                $newPenerimaanTruckingDPO = $newPenerimaanTruckingDPO->findAll($getPenerimaanTruckingDPO->id);
                $penerimaanTrucking = new UpdatePenerimaanTruckingHeaderRequest($penerimaanTruckingDPO);
                app(PenerimaanTruckingHeaderController::class)->update($penerimaanTrucking, $newPenerimaanTruckingDPO);

                $editProsesDetailDeposit = ProsesUangJalanSupirDetail::find($detailDeposito->iddeposit);
                $editProsesDetailDeposit->keterangan = $request->keterangandeposit;
                $editProsesDetailDeposit->update();

                $detailLog[] = $editProsesDetailDeposit->toArray();
            }

            $datalogtrail = [
                'namatabel' => strtoupper('prosesuangjalansupirdetail'),
                'postingdari' => 'EDIT PROSES UANG JALAN SUPIR DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $prosesuangjalansupirheader->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $detailLog,
                'modifiedby' => $request->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($prosesuangjalansupirheader, $prosesuangjalansupirheader->getTable());
            $prosesuangjalansupirheader->position = $selected->position;
            $prosesuangjalansupirheader->page = ceil($prosesuangjalansupirheader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $prosesuangjalansupirheader
            ], 201);
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

        $getDetail = ProsesUangJalanSupirDetail::lockForUpdate()->where('prosesuangjalansupir_id', $id)->get();

        $request['postingdari'] = "DELETE PROSES UANG JALAN SUPIR";
        $prosesuangjalansupir = new ProsesUangJalanSupirHeader();
        $prosesuangjalansupir = $prosesuangjalansupir->lockAndDestroy($id);

        if ($prosesuangjalansupir) {
            $datalogtrail = [
                'namatabel' => strtoupper($prosesuangjalansupir->getTable()),
                'postingdari' => 'DELETE PROSES UANG JALAN SUPIR',
                'idtrans' => $prosesuangjalansupir->id,
                'nobuktitrans' => $prosesuangjalansupir->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $prosesuangjalansupir->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            $storedLogTrail = app(LogTrailController::class)->store($data);

            // DELETE PROSESUANGJALANSUPIR DETAIL
            $logTrailProsesUangJalanDetail = [
                'namatabel' => 'PROSESUANGJALANSUPIRDETAIL',
                'postingdari' => 'DELETE PROSES UANG JALAN SUPIR',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $prosesuangjalansupir->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailProsesUangJalanDetail = new StoreLogTrailRequest($logTrailProsesUangJalanDetail);
            app(LogTrailController::class)->store($validatedLogTrailProsesUangJalanDetail);

            $transfer = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'TRANSFER')->first();
            $adjust = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'ADJUST TRANSFER')->first();
            $pengembalian = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'PENGEMBALIAN PINJAMAN')->first();
            $deposito = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'DEPOSITO SUPIR')->first();
            foreach ($getDetail as $key) {

                if ($key->statusprosesuangjalan == $transfer->id) {

                    $getPengeluaranTrucking = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))->where('pengeluaran_nobukti', $key->pengeluarantrucking_nobukti)->first();
                    if ($getPengeluaranTrucking != null) {
                        app(PengeluaranTruckingHeaderController::class)->destroy($request, $getPengeluaranTrucking->id);
                    }
                } else if ($key->statusprosesuangjalan == $adjust->id) {
                    if ($key->pengembaliankasgantung_nobukti != '') {

                        $getPengembalianKasgantung = PengembalianKasGantungHeader::from(DB::raw("pengembaliankasgantungheader with (readuncommitted)"))->where('nobukti', $key->pengembaliankasgantung_nobukti)->first();
                        if ($getPengembalianKasgantung != null) {
                            app(PengembalianKasGantungHeaderController::class)->destroy($request, $getPengembalianKasgantung->id);
                        }
                    }
                } else if ($key->statusprosesuangjalan == $pengembalian->id) {

                    if ($key->penerimaantrucking_nobukti != '') {
                        $getPenerimaanTrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where('penerimaan_nobukti', $key->penerimaantrucking_nobukti)->first();
                        if ($getPenerimaanTrucking != null) {
                            app(PenerimaanTruckingHeaderController::class)->destroy($request, $getPenerimaanTrucking->id);
                        }
                    }
                } else if ($key->statusprosesuangjalan == $deposito->id) {
                    if ($key->penerimaantrucking_nobukti != '') {
                        $getPenerimaanTrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where('penerimaan_nobukti', $key->penerimaantrucking_nobukti)->first();
                        if ($getPenerimaanTrucking != null) {
                            app(PenerimaanTruckingHeaderController::class)->destroy($request, $getPenerimaanTrucking->id);
                        }
                    }
                }
            }


            DB::commit();
            $selected = $this->getPosition($prosesuangjalansupir, $prosesuangjalansupir->getTable(), true);
            $prosesuangjalansupir->position = $selected->position;
            $prosesuangjalansupir->id = $selected->id;
            $prosesuangjalansupir->page = ceil($prosesuangjalansupir->position / ($request->limit ?? 10));
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $prosesuangjalansupir
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function getPinjaman($supirId)
    {
        $prosesUangJalan = new ProsesUangJalanSupirHeader();
        return response([
            'status' => true,
            'data' => $prosesUangJalan->getPinjaman($supirId)
        ]);
    }
    public function getPengembalian($id)
    {
        $prosesUangJalan = new ProsesUangJalanSupirHeader();
        return response([
            'status' => true,
            'data' => $prosesUangJalan->getPengembalian($id)
        ]);
    }

    public function cekvalidasi($id)
    {
        $pengeluaran = ProsesUangJalanSupirHeader::find($id);
        $status = $pengeluaran->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();


        if ($status == $statusApproval->id) {
            $query = Error::from(DB::raw("error as (readuncommitted)"))
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
}
