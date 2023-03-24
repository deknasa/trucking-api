<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ProsesGajiSupirDetailController as ApiProsesGajiSupirDetailController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ProsesGajiSupirDetailController;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePenerimaanHeaderRequest;
use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\StoreProsesGajiSupirDetailRequest;
use App\Models\ProsesGajiSupirHeader;
use App\Http\Requests\StoreProsesGajiSupirHeaderRequest;
use App\Http\Requests\UpdatePenerimaanHeaderRequest;
use App\Http\Requests\UpdatePenerimaanTruckingHeaderRequest;
use App\Http\Requests\UpdatePengeluaranHeaderRequest;
use App\Http\Requests\UpdatePengeluaranTruckingHeaderRequest;
use App\Http\Requests\UpdateProsesGajiSupirHeaderRequest;
use App\Models\Bank;
use App\Models\Error;
use App\Models\GajiSupirBBM;
use App\Models\GajiSupirDeposito;
use App\Models\GajiSupirHeader;
use App\Models\GajiSupirPelunasanPinjaman;
use App\Models\GajiSupirPinjaman;
use App\Models\LogTrail;
use App\Models\Parameter;
use App\Models\PenerimaanHeader;
use App\Models\PenerimaanTrucking;
use App\Models\PenerimaanTruckingDetail;
use App\Models\PenerimaanTruckingHeader;
use App\Models\PengeluaranHeader;
use App\Models\PengeluaranTruckingDetail;
use App\Models\PengeluaranTruckingHeader;
use App\Models\ProsesGajiSupirDetail;
use App\Models\Supir;
use App\Models\SuratPengantar;
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

    public function default()
    {
        $prosesGaji = new ProsesGajiSupirHeader();
        return response([
            'status' => true,
            'data' => $prosesGaji->default(),
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreProsesGajiSupirHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            if ($request->rincianId != '') {

                if ($request->nomPP > 0) {
                    $request->validate(
                        [
                            'bankPP' => 'required',
                        ],
                        [
                            'bankPP.required' => 'bank pot. pinjaman pribadi ' . app(ErrorController::class)->geterror('WI')->keterangan,
                        ]
                    );
                }
                if ($request->nomPS > 0) {
                    $request->validate(
                        [
                            'bankPS' => 'required',
                        ],
                        [
                            'bankPS.required' => 'bank pot. pinjaman (Semua) ' . app(ErrorController::class)->geterror('WI')->keterangan,
                        ]
                    );
                }
                if ($request->nomDeposito > 0) {
                    $request->validate(
                        [
                            'bankDeposito' => 'required',
                        ],
                        [
                            'bankDeposito.required' => 'bank posting deposito ' . app(ErrorController::class)->geterror('WI')->keterangan,
                        ]
                    );
                }
                if ($request->nomBBM > 0) {
                    $request->validate(
                        [
                            'bankBBM' => 'required',
                        ],
                        [
                            'bankBBM.required' => 'bank Posting BBM ' . app(ErrorController::class)->geterror('WI')->keterangan,
                        ]
                    );
                }

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
                $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
                $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

                $prosesgajisupirheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
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

                for ($i = 0; $i < count($request->rincianId); $i++) {

                    $ric = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))
                        ->where('id', $request->rincianId[$i])->first();
                    $sp = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
                        ->where('supir_id', $ric->supir_id)->first();
                    $datadetail = [
                        'prosesgajisupir_id' => $prosesgajisupirheader->id,
                        'nobukti' => $prosesgajisupirheader->nobukti,
                        'gajisupir_nobukti' => $ric->nobukti,
                        'supir_id' => $ric->supir_id,
                        'trado_id' => $sp->trado_id,
                        'nominal' => $ric->total,
                        'keterangan' => $ric->keterangan ?? '',
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


                // POSTING POT. SEMUA

                if ($request->nomPS != 0) {
                    // SAVE TO PENERIMAAN
                    $querysubgrppenerimaan = Bank::from(DB::raw("bank with (readuncommitted)"))
                        ->select(
                            'parameter.grp',
                            'parameter.subgrp',
                            'bank.formatpenerimaan',
                            'bank.coa',
                            'bank.tipe'
                        )
                        ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')
                        ->whereRaw("bank.id = $request->bank_idPS")
                        ->first();

                    $group = $querysubgrppenerimaan->grp;
                    $subgroup = $querysubgrppenerimaan->subgrp;
                    $format = DB::table('parameter')
                        ->where('grp', $group)
                        ->where('subgrp', $subgroup)
                        ->first();
                    $penerimaanRequest = new Request();
                    $penerimaanRequest['group'] = $querysubgrppenerimaan->grp;
                    $penerimaanRequest['subgroup'] = $querysubgrppenerimaan->subgrp;
                    $penerimaanRequest['table'] = 'penerimaanheader';
                    $penerimaanRequest['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                    $nobuktiPenerimaanPS = app(Controller::class)->getRunningNumber($penerimaanRequest)->original['data'];
                    $penerimaanDetail = [];
                    $gajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->whereRaw("tglbukti >= '$prosesgajisupirheader->tgldari'")
                        ->whereRaw("tglbukti <= '$prosesgajisupirheader->tglsampai'")
                        ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupir_id='$prosesgajisupirheader->id')")
                        ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from gajisupirpelunasanpinjaman where supir_id=0)")
                        ->get();

                    foreach ($gajiSupir as $key => $value) {
                        $fetchPS = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $value->nobukti)->where('supir_id', '0')->first();

                        $penerimaanPS = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                            ->where('nobukti', $fetchPS->penerimaantrucking_nobukti)->first();
                        $getNominal = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
                            ->where('nobukti', $fetchPS->penerimaantrucking_nobukti)->get();

                        $penerimaanTruckingHeaderPS = [
                            'isUpdate' => 2,
                            'postingdari' => 'PROSES GAJI SUPIR',
                            'from' => 'ebs',
                            'bank_id' => $request->bank_idPS,
                            'penerimaan_nobukti' => $nobuktiPenerimaanPS,
                        ];

                        $penerimaanDetail[] = [
                            'nobukti' => $nobuktiPenerimaanPS,
                            'nowarkat' => '',
                            'tgljatuhtempo' => date('Y-m-d', strtotime($request->tglbukti)),
                            "nominal" => $getNominal->sum('nominal'),
                            'coadebet' => $querysubgrppenerimaan->coa,
                            'coakredit' => $penerimaanPS->coa,
                            'keterangan' => $penerimaanPS->nobukti,
                            'invoice_nobukti' => '',
                            'pelunasanpiutang_nobukti' => '',
                            'bulanbeban' => date('Y-m-d', strtotime($request->tglbukti)),
                            'modifiedby' => auth('api')->user()->name,
                        ];


                        $newPenerimaanTruckingPS = new PenerimaanTruckingHeader();
                        $newPenerimaanTruckingPS = $newPenerimaanTruckingPS->findAll($penerimaanPS->id);
                        $penerimaanTruckingPS = new UpdatePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderPS);
                        app(PenerimaanTruckingHeaderController::class)->update($penerimaanTruckingPS, $newPenerimaanTruckingPS);
                    }

                    $penerimaanHeader = [
                        'tanpaprosesnobukti' => 1,
                        'nobukti' => $nobuktiPenerimaanPS,
                        'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                        'pelanggan_id' => '',
                        'bank_id' => $request->bank_idPS,
                        'postingdari' => 'PROSES GAJI SUPIR',
                        'diterimadari' => "PROSES GAJI SUPIR PERIODE $request->tgldari S/D $request->tglsampai",
                        'tgllunas' => date('Y-m-d', strtotime($request->tglbukti)),
                        'statusformat' => $format->id,
                        'modifiedby' => auth('api')->user()->name,
                        'datadetail' => $penerimaanDetail
                    ];

                    $penerimaan = new StorePenerimaanHeaderRequest($penerimaanHeader);
                    app(PenerimaanHeaderController::class)->store($penerimaan);
                }

                // POSTING POT. PRIBADI

                if ($request->nomPP != 0) {
                    // SAVE TO PENERIMAAN

                    $queryPenerimaanPP = Bank::from(DB::raw("bank with (readuncommitted)"))
                        ->select(
                            'parameter.grp',
                            'parameter.subgrp',
                            'bank.formatpenerimaan',
                            'bank.coa',
                            'bank.tipe'
                        )
                        ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')
                        ->whereRaw("bank.id = $request->bank_idPP")
                        ->first();

                    $group = $queryPenerimaanPP->grp;
                    $subgroup = $queryPenerimaanPP->subgrp;
                    $formatPP = DB::table('parameter')
                        ->where('grp', $group)
                        ->where('subgrp', $subgroup)
                        ->first();
                    $penerimaanRequestPP = new Request();
                    $penerimaanRequestPP['group'] = $queryPenerimaanPP->grp;
                    $penerimaanRequestPP['subgroup'] = $queryPenerimaanPP->subgrp;
                    $penerimaanRequestPP['table'] = 'penerimaanheader';
                    $penerimaanRequestPP['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                    $nobuktiPenerimaanPP = app(Controller::class)->getRunningNumber($penerimaanRequestPP)->original['data'];
                    $penerimaanDetailPP = [];
                    $gajiSupirPP = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->whereRaw("tglbukti >= '$prosesgajisupirheader->tgldari'")
                        ->whereRaw("tglbukti <= '$prosesgajisupirheader->tglsampai'")
                        ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupir_id='$prosesgajisupirheader->id')")
                        ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from gajisupirpelunasanpinjaman where supir_id != 0)")
                        ->get();
                    foreach ($gajiSupirPP as $key => $value) {
                        $fetchPP = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $value->nobukti)->where('supir_id', '!=', '0')->first();

                        $penerimaanPP = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                            ->where('nobukti', $fetchPP->penerimaantrucking_nobukti)->first();
                        $getNominalPP = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
                            ->where('nobukti', $fetchPP->penerimaantrucking_nobukti)->get();

                        $penerimaanTruckingHeaderPP = [
                            'isUpdate' => 2,
                            'postingdari' => 'PROSES GAJI SUPIR',
                            'from' => 'ebs',
                            'bank_id' => $request->bank_idPP,
                            'penerimaan_nobukti' => $nobuktiPenerimaanPP,
                        ];

                        $penerimaanDetailPP[] = [
                            'nobukti' => $nobuktiPenerimaanPP,
                            'nowarkat' => '',
                            'tgljatuhtempo' => date('Y-m-d', strtotime($request->tglbukti)),
                            "nominal" => $getNominalPP->sum('nominal'),
                            'coadebet' => $queryPenerimaanPP->coa,
                            'coakredit' => $penerimaanPP->coa,
                            'keterangan' => $penerimaanPP->nobukti,
                            'invoice_nobukti' => '',
                            'pelunasanpiutang_nobukti' => '',
                            'bulanbeban' => date('Y-m-d', strtotime($request->tglbukti)),
                            'modifiedby' => auth('api')->user()->name,
                        ];


                        $newPenerimaanTruckingPP = new PenerimaanTruckingHeader();
                        $newPenerimaanTruckingPP = $newPenerimaanTruckingPP->findAll($penerimaanPP->id);
                        $penerimaanTruckingPP = new UpdatePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderPP);
                        app(PenerimaanTruckingHeaderController::class)->update($penerimaanTruckingPP, $newPenerimaanTruckingPP);
                    }

                    $penerimaanHeaderPP = [
                        'tanpaprosesnobukti' => 1,
                        'nobukti' => $nobuktiPenerimaanPP,
                        'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                        'pelanggan_id' => '',
                        'bank_id' => $request->bank_idPP,
                        'postingdari' => 'PROSES GAJI SUPIR',
                        'diterimadari' => "PROSES GAJI SUPIR PERIODE $request->tgldari S/D $request->tglsampai",
                        'tgllunas' => date('Y-m-d', strtotime($request->tglbukti)),
                        'statusformat' => $formatPP->id,
                        'modifiedby' => auth('api')->user()->name,
                        'datadetail' => $penerimaanDetailPP
                    ];
                    $penerimaanPP = new StorePenerimaanHeaderRequest($penerimaanHeaderPP);
                    app(PenerimaanHeaderController::class)->store($penerimaanPP);
                }

                // POSTING DEPOSITO
                if ($request->nomDeposito != 0) {
                    // SAVE TO PENERIMAAN
                    $queryPenerimaanDeposito = Bank::from(DB::raw("bank with (readuncommitted)"))
                        ->select(
                            'parameter.grp',
                            'parameter.subgrp',
                            'bank.formatpenerimaan',
                            'bank.coa',
                            'bank.tipe'
                        )
                        ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')
                        ->whereRaw("bank.id = $request->bank_idDeposito")
                        ->first();

                    $group = $queryPenerimaanDeposito->grp;
                    $subgroup = $queryPenerimaanDeposito->subgrp;
                    $formatDeposito = DB::table('parameter')
                        ->where('grp', $group)
                        ->where('subgrp', $subgroup)
                        ->first();
                    $penerimaanRequestDeposito = new Request();
                    $penerimaanRequestDeposito['group'] = $queryPenerimaanDeposito->grp;
                    $penerimaanRequestDeposito['subgroup'] = $queryPenerimaanDeposito->subgrp;
                    $penerimaanRequestDeposito['table'] = 'penerimaanheader';
                    $penerimaanRequestDeposito['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                    $nobuktiPenerimaanDeposito = app(Controller::class)->getRunningNumber($penerimaanRequestDeposito)->original['data'];
                    $penerimaanDetailDeposito = [];
                    $gajiSupirDeposito = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->whereRaw("tglbukti >= '$prosesgajisupirheader->tgldari'")
                        ->whereRaw("tglbukti <= '$prosesgajisupirheader->tglsampai'")
                        ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupir_id='$prosesgajisupirheader->id')")
                        ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from gajisupirdeposito)")
                        ->get();
                    foreach ($gajiSupirDeposito as $key => $value) {
                        $fetchDeposito = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))->where('gajisupir_nobukti', $value->nobukti)->first();

                        $penerimaanDeposito = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                            ->where('nobukti', $fetchDeposito->penerimaantrucking_nobukti)->first();
                        $getNominalDeposito = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
                            ->where('nobukti', $fetchDeposito->penerimaantrucking_nobukti)->get();

                        $penerimaanTruckingHeaderDeposito = [
                            'isUpdate' => 2,
                            'postingdari' => 'PROSES GAJI SUPIR',
                            'from' => 'ebs',
                            'bank_id' => $request->bank_idDeposito,
                            'penerimaan_nobukti' => $nobuktiPenerimaanDeposito,
                        ];

                        $penerimaanDetailDeposito[] = [
                            'nobukti' => $nobuktiPenerimaanDeposito,
                            'nowarkat' => '',
                            'tgljatuhtempo' => date('Y-m-d', strtotime($request->tglbukti)),
                            "nominal" => $getNominalDeposito->sum('nominal'),
                            'coadebet' => $queryPenerimaanDeposito->coa,
                            'coakredit' => $penerimaanDeposito->coa,
                            'keterangan' => $penerimaanDeposito->nobukti,
                            'invoice_nobukti' => '',
                            'pelunasanpiutang_nobukti' => '',
                            'bulanbeban' => date('Y-m-d', strtotime($request->tglbukti)),
                            'modifiedby' => auth('api')->user()->name,
                        ];


                        $newPenerimaanTruckingDeposito = new PenerimaanTruckingHeader();
                        $newPenerimaanTruckingDeposito = $newPenerimaanTruckingDeposito->findAll($penerimaanDeposito->id);
                        $penerimaanTruckingDeposito = new UpdatePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderDeposito);
                        app(PenerimaanTruckingHeaderController::class)->update($penerimaanTruckingDeposito, $newPenerimaanTruckingDeposito);
                    }

                    $penerimaanHeaderDeposito = [
                        'tanpaprosesnobukti' => 1,
                        'nobukti' => $nobuktiPenerimaanDeposito,
                        'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                        'pelanggan_id' => '',
                        'bank_id' => $request->bank_idDeposito,
                        'postingdari' => 'PROSES GAJI SUPIR',
                        'diterimadari' => "PROSES GAJI SUPIR PERIODE $request->tgldari S/D $request->tglsampai",
                        'tgllunas' => date('Y-m-d', strtotime($request->tglbukti)),
                        'statusformat' => $formatDeposito->id,
                        'modifiedby' => auth('api')->user()->name,
                        'datadetail' => $penerimaanDetailDeposito
                    ];

                    $penerimaanDeposito = new StorePenerimaanHeaderRequest($penerimaanHeaderDeposito);
                    app(PenerimaanHeaderController::class)->store($penerimaanDeposito);
                }

                // POSTING BBM

                if ($request->nomBBM != 0) {
                    // SAVE TO PENERIMAAN
                    $queryPenerimaanBBM = Bank::from(DB::raw("bank with (readuncommitted)"))
                        ->select(
                            'parameter.grp',
                            'parameter.subgrp',
                            'bank.formatpenerimaan',
                            'bank.coa',
                            'bank.tipe'
                        )
                        ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')
                        ->whereRaw("bank.id = $request->bank_idBBM")
                        ->first();

                    $group = $queryPenerimaanBBM->grp;
                    $subgroup = $queryPenerimaanBBM->subgrp;
                    $formatBBM = DB::table('parameter')
                        ->where('grp', $group)
                        ->where('subgrp', $subgroup)
                        ->first();
                    $penerimaanRequestBBM = new Request();
                    $penerimaanRequestBBM['group'] = $queryPenerimaanBBM->grp;
                    $penerimaanRequestBBM['subgroup'] = $queryPenerimaanBBM->subgrp;
                    $penerimaanRequestBBM['table'] = 'penerimaanheader';
                    $penerimaanRequestBBM['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                    $nobuktiPenerimaanBBM = app(Controller::class)->getRunningNumber($penerimaanRequestBBM)->original['data'];
                    $penerimaanDetailBBM = [];
                    $gajiSupirBBM = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->whereRaw("tglbukti >= '$prosesgajisupirheader->tgldari'")
                        ->whereRaw("tglbukti <= '$prosesgajisupirheader->tglsampai'")
                        ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupir_id='$prosesgajisupirheader->id')")
                        ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from gajisupirbbm)")
                        ->get();
                    foreach ($gajiSupirBBM as $key => $value) {
                        $fetchBBM = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))->where('gajisupir_nobukti', $value->nobukti)->first();

                        $penerimaanBBM = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                            ->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->first();
                        $coaBBM = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))->where('kodepenerimaan', 'BBM')->first();
                        $penerimaanTruckingHeaderBBM = [
                            'isUpdate' => 2,
                            'postingdari' => 'PROSES GAJI SUPIR',
                            'from' => 'ebs',
                            'bank_id' => $request->bank_idBBM,
                            'penerimaan_nobukti' => $nobuktiPenerimaanBBM,
                        ];

                        $penerimaanDetailBBM[] = [
                            'nobukti' => $nobuktiPenerimaanBBM,
                            'nowarkat' => '',
                            'tgljatuhtempo' => date('Y-m-d', strtotime($request->tglbukti)),
                            "nominal" => $fetchBBM->nominal,
                            'coadebet' => $queryPenerimaanBBM->coa,
                            'coakredit' => $coaBBM->coapostingdebet,
                            'keterangan' => $penerimaanBBM->nobukti,
                            'invoice_nobukti' => '',
                            'pelunasanpiutang_nobukti' => '',
                            'bulanbeban' => date('Y-m-d', strtotime($request->tglbukti)),
                            'modifiedby' => auth('api')->user()->name,
                        ];


                        $newPenerimaanTruckingBBM = new PenerimaanTruckingHeader();
                        $newPenerimaanTruckingBBM = $newPenerimaanTruckingBBM->findAll($penerimaanBBM->id);
                        $penerimaanTruckingBBM = new UpdatePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderBBM);
                        app(PenerimaanTruckingHeaderController::class)->update($penerimaanTruckingBBM, $newPenerimaanTruckingBBM);
                    }

                    $penerimaanHeaderBBM = [
                        'tanpaprosesnobukti' => 1,
                        'nobukti' => $nobuktiPenerimaanBBM,
                        'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                        'pelanggan_id' => '',
                        'bank_id' => $request->bank_idBBM,
                        'postingdari' => 'PROSES GAJI SUPIR',
                        'diterimadari' => "PROSES GAJI SUPIR PERIODE $request->tgldari S/D $request->tglsampai",
                        'tgllunas' => date('Y-m-d', strtotime($request->tglbukti)),
                        'statusformat' => $formatBBM->id,
                        'modifiedby' => auth('api')->user()->name,
                        'datadetail' => $penerimaanDetailBBM
                    ];

                    $penerimaanBBM = new StorePenerimaanHeaderRequest($penerimaanHeaderBBM);
                    app(PenerimaanHeaderController::class)->store($penerimaanBBM);
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
                ], 201);
            } else {
                $query = Error::from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'WP')
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
        $proses = new ProsesGajiSupirHeader();
        $prosesGajiSupirHeader = ProsesGajiSupirHeader::from(DB::raw("prosesgajisupirheader with (readuncommitted)"))->where('id', $id)->first();
        $semua = $proses->showPotSemua($id);
        $pribadi = $proses->showPotPribadi($id);
        $deposito = $proses->showDeposito($id);
        $bbm = $proses->showBBM($id);
        return response([
            'status' => true,
            'data' => $prosesGajiSupirHeader,
            'potsemua' => $semua,
            'potpribadi' => $pribadi,
            'deposito' => $deposito,
            'bbm' => $bbm
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
            $prosesgajisupirheader->tgldari = date('Y-m-d', strtotime($request->tgldari));
            $prosesgajisupirheader->tglsampai = date('Y-m-d', strtotime($request->tglsampai));
            $prosesgajisupirheader->periode = date('Y-m-d', strtotime($request->periode));
            $prosesgajisupirheader->modifiedby = auth('api')->user()->name;


            $prosesgajisupirheader->save();

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


            $penerimaan_nobuktiPS = '';
            $gajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->whereRaw("tglbukti >= '$prosesgajisupirheader->tgldari'")
                ->whereRaw("tglbukti <= '$prosesgajisupirheader->tglsampai'")
                ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupir_id='$prosesgajisupirheader->id')")
                ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from gajisupirpelunasanpinjaman where supir_id=0)")
                ->get();

            foreach ($gajiSupir as $key => $value) {
                $fetchPS = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $value->nobukti)->where('supir_id', '0')->first();
                if ($fetchPS != null) {
                    $penerimaanPS = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                        ->where('nobukti', $fetchPS->penerimaantrucking_nobukti)->first();
                    $penerimaan_nobuktiPS = $penerimaanPS->penerimaan_nobukti;

                    $penerimaanTruckingHeaderPS = [
                        'isUpdate' => 2,
                        'postingdari' => 'PROSES GAJI SUPIR',
                        'from' => 'ebs',
                        'bank_id' => 0,
                        'penerimaan_nobukti' => ''
                    ];

                    $newPenerimaanTruckingPS = new PenerimaanTruckingHeader();
                    $newPenerimaanTruckingPS = $newPenerimaanTruckingPS->findAll($penerimaanPS->id);
                    $penerimaanTruckingPS = new UpdatePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderPS);
                    app(PenerimaanTruckingHeaderController::class)->update($penerimaanTruckingPS, $newPenerimaanTruckingPS);
                }
            }

            $penerimaan_nobuktiPP = '';
            $gajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->whereRaw("tglbukti >= '$prosesgajisupirheader->tgldari'")
                ->whereRaw("tglbukti <= '$prosesgajisupirheader->tglsampai'")
                ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupir_id='$prosesgajisupirheader->id')")
                ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from gajisupirpelunasanpinjaman where supir_id != 0)")
                ->get();

            foreach ($gajiSupir as $key => $value) {
                $fetchPP = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $value->nobukti)->where('supir_id', '!=', '0')->first();
                if ($fetchPP != null) {

                    $penerimaanPP = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                        ->where('nobukti', $fetchPP->penerimaantrucking_nobukti)->first();
                    $penerimaan_nobuktiPP = $penerimaanPP->penerimaan_nobukti;

                    $penerimaanTruckingHeaderPP = [
                        'isUpdate' => 2,
                        'postingdari' => 'PROSES GAJI SUPIR',
                        'from' => 'ebs',
                        'bank_id' => 0,
                        'penerimaan_nobukti' => ''
                    ];

                    $newPenerimaanTruckingPP = new PenerimaanTruckingHeader();
                    $newPenerimaanTruckingPP = $newPenerimaanTruckingPP->findAll($penerimaanPP->id);
                    $penerimaanTruckingPP = new UpdatePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderPP);
                    app(PenerimaanTruckingHeaderController::class)->update($penerimaanTruckingPP, $newPenerimaanTruckingPP);
                }
            }

            $penerimaan_nobuktiDeposito = '';
            $penerimaan_nobuktiBBM = '';
            $gajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->whereRaw("tglbukti >= '$prosesgajisupirheader->tgldari'")
                ->whereRaw("tglbukti <= '$prosesgajisupirheader->tglsampai'")
                ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupir_id='$prosesgajisupirheader->id')")
                ->get();

            foreach ($gajiSupir as $key => $value) {
                $fetchDeposito = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))->where('gajisupir_nobukti', $value->nobukti)->first();
                if ($fetchDeposito != null) {
                    $penerimaanDeposito = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                        ->where('nobukti', $fetchDeposito->penerimaantrucking_nobukti)->first();
                    $penerimaan_nobuktiDeposito = $penerimaanDeposito->penerimaan_nobukti;

                    $penerimaanTruckingHeaderDeposito = [
                        'isUpdate' => 2,
                        'postingdari' => 'PROSES GAJI SUPIR',
                        'from' => 'ebs',
                        'bank_id' => 0,
                        'penerimaan_nobukti' => ''
                    ];

                    $newPenerimaanTruckingDeposito = new PenerimaanTruckingHeader();
                    $newPenerimaanTruckingDeposito = $newPenerimaanTruckingDeposito->findAll($penerimaanDeposito->id);
                    $penerimaanTruckingDeposito = new UpdatePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderDeposito);
                    app(PenerimaanTruckingHeaderController::class)->update($penerimaanTruckingDeposito, $newPenerimaanTruckingDeposito);
                }

                $fetchBBM = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))->where('gajisupir_nobukti', $value->nobukti)->first();
                if ($fetchBBM != null) {
                    $penerimaanBBM = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                        ->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->first();
                    $penerimaan_nobuktiBBM = $penerimaanBBM->penerimaan_nobukti;

                    $penerimaanTruckingHeaderBBM = [
                        'isUpdate' => 2,
                        'postingdari' => 'PROSES GAJI SUPIR',
                        'from' => 'ebs',
                        'bank_id' => 0,
                        'penerimaan_nobukti' => ''
                    ];

                    $newPenerimaanTruckingBBM = new PenerimaanTruckingHeader();
                    $newPenerimaanTruckingBBM = $newPenerimaanTruckingBBM->findAll($penerimaanBBM->id);
                    $penerimaanTruckingBBM = new UpdatePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderBBM);
                    app(PenerimaanTruckingHeaderController::class)->update($penerimaanTruckingBBM, $newPenerimaanTruckingBBM);
                }
             
            }

            ProsesGajiSupirDetail::where('prosesgajisupir_id', $prosesgajisupirheader->id)->delete();
            /* Store detail */
            $detaillog = [];
            $urut = 1;
            $penerimaanDetailPS = [];
            $penerimaanDetailPP = [];
            $penerimaanDetailDeposito = [];
            $penerimaanDetailBBM = [];
            for ($i = 0; $i < count($request->rincianId); $i++) {
                $ric = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))
                    ->where('id', $request->rincianId[$i])->first();
                $sp = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
                    ->where('supir_id', $ric->supir_id)->first();
                $datadetail = [
                    'prosesgajisupir_id' => $prosesgajisupirheader->id,
                    'nobukti' => $prosesgajisupirheader->nobukti,
                    'gajisupir_nobukti' => $ric->nobukti,
                    'supir_id' => $ric->supir_id,
                    'trado_id' => $sp->trado_id,
                    'nominal' => $ric->total,
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

                if ($request->nomPS != 0) {

                    $bank = Bank::from(DB::raw("bank with (readuncommitted)"))
                        ->select('coa')
                        ->where('id', $request->bank_idPS)
                        ->first();
                    $fetchPS = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $request->nobuktiRIC[$i])->where('supir_id', '0')->first();

                    if ($fetchPS != null) {
                        $penerimaanPS = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                            ->where('nobukti', $fetchPS->penerimaantrucking_nobukti)->first();
                        $getNominal = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
                            ->where('nobukti', $fetchPS->penerimaantrucking_nobukti)->get();

                        $penerimaanTruckingHeaderPS = [
                            'isUpdate' => 2,
                            'postingdari' => 'PROSES GAJI SUPIR',
                            'from' => 'ebs',
                            'bank_id' => $request->bank_idPS,
                            'penerimaan_nobukti' => $penerimaan_nobuktiPS,
                        ];

                        $penerimaanDetailPS[] = [
                            'nobukti' => $penerimaan_nobuktiPS,
                            'nowarkat' => '',
                            'tgljatuhtempo' => date('Y-m-d', strtotime($request->tglbukti)),
                            "nominal" => $getNominal->sum('nominal'),
                            'coadebet' => $bank->coa,
                            'coakredit' => $penerimaanPS->coa,
                            'keterangan' => $penerimaanPS->nobukti,
                            'invoice_nobukti' => '',
                            'pelunasanpiutang_nobukti' => '',
                            'bulanbeban' => date('Y-m-d', strtotime($request->tglbukti)),
                            'modifiedby' => auth('api')->user()->name,
                        ];

                        $newPenerimaanTruckingPS = new PenerimaanTruckingHeader();
                        $newPenerimaanTruckingPS = $newPenerimaanTruckingPS->findAll($penerimaanPS->id);
                        $penerimaanTruckingPS = new UpdatePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderPS);
                        app(PenerimaanTruckingHeaderController::class)->update($penerimaanTruckingPS, $newPenerimaanTruckingPS);
                    }
                }

                if ($request->nomPP != 0) {

                    $bank = Bank::from(DB::raw("bank with (readuncommitted)"))
                        ->select('coa')
                        ->where('id', $request->bank_idPP)
                        ->first();
                    $fetchPP = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $request->nobuktiRIC[$i])->where('supir_id', '!=', '0')->first();

                    if ($fetchPP != null) {
                        $penerimaanPP = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                            ->where('nobukti', $fetchPP->penerimaantrucking_nobukti)->first();
                        $getNominal = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
                            ->where('nobukti', $fetchPP->penerimaantrucking_nobukti)->get();

                        $penerimaanTruckingHeaderPP = [
                            'isUpdate' => 2,
                            'postingdari' => 'PROSES GAJI SUPIR',
                            'from' => 'ebs',
                            'bank_id' => $request->bank_idPP,
                            'penerimaan_nobukti' => $penerimaan_nobuktiPP,
                        ];

                        $penerimaanDetailPP[] = [
                            'nobukti' => $penerimaan_nobuktiPP,
                            'nowarkat' => '',
                            'tgljatuhtempo' => date('Y-m-d', strtotime($request->tglbukti)),
                            "nominal" => $getNominal->sum('nominal'),
                            'coadebet' => $bank->coa,
                            'coakredit' => $penerimaanPP->coa,
                            'keterangan' => $penerimaanPP->nobukti,
                            'invoice_nobukti' => '',
                            'pelunasanpiutang_nobukti' => '',
                            'bulanbeban' => date('Y-m-d', strtotime($request->tglbukti)),
                            'modifiedby' => auth('api')->user()->name,
                        ];

                        $newPenerimaanTruckingPP = new PenerimaanTruckingHeader();
                        $newPenerimaanTruckingPP = $newPenerimaanTruckingPP->findAll($penerimaanPP->id);
                        $penerimaanTruckingPP = new UpdatePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderPP);
                        app(PenerimaanTruckingHeaderController::class)->update($penerimaanTruckingPP, $newPenerimaanTruckingPP);
                    }
                }

                if ($request->nomDeposito != 0) {

                    $bank = Bank::from(DB::raw("bank with (readuncommitted)"))
                        ->select('coa')
                        ->where('id', $request->bank_idDeposito)
                        ->first();
                    $fetchDeposito = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))->where('gajisupir_nobukti', $request->nobuktiRIC[$i])->first();

                    if ($fetchDeposito != null) {
                        $penerimaanDeposito = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                            ->where('nobukti', $fetchDeposito->penerimaantrucking_nobukti)->first();
                        $getNominal = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
                            ->where('nobukti', $fetchDeposito->penerimaantrucking_nobukti)->get();

                        $penerimaanTruckingHeaderDeposito = [
                            'isUpdate' => 2,
                            'postingdari' => 'PROSES GAJI SUPIR',
                            'from' => 'ebs',
                            'bank_id' => $request->bank_idDeposito,
                            'penerimaan_nobukti' => $penerimaan_nobuktiDeposito,
                        ];

                        $penerimaanDetailDeposito[] = [
                            'nobukti' => $penerimaan_nobuktiDeposito,
                            'nowarkat' => '',
                            'tgljatuhtempo' => date('Y-m-d', strtotime($request->tglbukti)),
                            "nominal" => $getNominal->sum('nominal'),
                            'coadebet' => $bank->coa,
                            'coakredit' => $penerimaanDeposito->coa,
                            'keterangan' => $penerimaanDeposito->nobukti,
                            'invoice_nobukti' => '',
                            'pelunasanpiutang_nobukti' => '',
                            'bulanbeban' => date('Y-m-d', strtotime($request->tglbukti)),
                            'modifiedby' => auth('api')->user()->name,
                        ];

                        $newPenerimaanTruckingDeposito = new PenerimaanTruckingHeader();
                        $newPenerimaanTruckingDeposito = $newPenerimaanTruckingDeposito->findAll($penerimaanDeposito->id);
                        $penerimaanTruckingDeposito = new UpdatePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderDeposito);
                        app(PenerimaanTruckingHeaderController::class)->update($penerimaanTruckingDeposito, $newPenerimaanTruckingDeposito);
                    }
                }

                if ($request->nomBBM != 0) {

                    $bank = Bank::from(DB::raw("bank with (readuncommitted)"))
                        ->select('coa')
                        ->where('id', $request->bank_idBBM)
                        ->first();
                    $fetchBBM = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))->where('gajisupir_nobukti', $request->nobuktiRIC[$i])->first();

                    if ($fetchBBM != null) {
                        $penerimaanBBM = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                            ->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->first();
                        $getNominal = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
                            ->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->get();

                        $penerimaanTruckingHeaderBBM = [
                            'isUpdate' => 2,
                            'postingdari' => 'PROSES GAJI SUPIR',
                            'from' => 'ebs',
                            'bank_id' => $request->bank_idBBM,
                            'penerimaan_nobukti' => $penerimaan_nobuktiBBM,
                        ];

                        $penerimaanDetailBBM[] = [
                            'nobukti' => $penerimaan_nobuktiBBM,
                            'nowarkat' => '',
                            'tgljatuhtempo' => date('Y-m-d', strtotime($request->tglbukti)),
                            "nominal" => $getNominal->sum('nominal'),
                            'coadebet' => $bank->coa,
                            'coakredit' => $penerimaanBBM->coa,
                            'keterangan' => $penerimaanBBM->nobukti,
                            'invoice_nobukti' => '',
                            'pelunasanpiutang_nobukti' => '',
                            'bulanbeban' => date('Y-m-d', strtotime($request->tglbukti)),
                            'modifiedby' => auth('api')->user()->name,
                        ];

                        $newPenerimaanTruckingBBM = new PenerimaanTruckingHeader();
                        $newPenerimaanTruckingBBM = $newPenerimaanTruckingBBM->findAll($penerimaanBBM->id);
                        $penerimaanTruckingBBM = new UpdatePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderBBM);
                        app(PenerimaanTruckingHeaderController::class)->update($penerimaanTruckingBBM, $newPenerimaanTruckingBBM);
                    }
                }

            }


            $getPS = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))
                ->select('id')
                ->where('nobukti', $penerimaan_nobuktiPS)->first();

            if ($getPS != null) {
                $penerimaanHeaderPS = [
                    'isUpdate' => 1,
                    'postingdari' => 'PROSES GAJI SUPIR',
                    'datadetail' => $penerimaanDetailPS,
                    'nowarkat' => '',
                    'bank_id' => $request->bank_idPS

                ];
                $newPenerimaanPS = new PenerimaanHeader();
                $newPenerimaanPS = $newPenerimaanPS->findAll($getPS->id);

                $penerimaanUpdatePS = new UpdatePenerimaanHeaderRequest($penerimaanHeaderPS);
                app(PenerimaanHeaderController::class)->update($penerimaanUpdatePS, $newPenerimaanPS);
            }



            $getPP = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))
                ->select('id')
                ->where('nobukti', $penerimaan_nobuktiPP)->first();

            if ($getPP != null) {
                $penerimaanHeaderPP = [
                    'isUpdate' => 1,
                    'postingdari' => 'PROSES GAJI SUPIR',
                    'datadetail' => $penerimaanDetailPP,
                    'nowarkat' => '',
                    'bank_id' => $request->bank_idPP

                ];
                $newPenerimaanPP = new PenerimaanHeader();
                $newPenerimaanPP = $newPenerimaanPP->findAll($getPP->id);

                $penerimaanUpdatePP = new UpdatePenerimaanHeaderRequest($penerimaanHeaderPP);
                app(PenerimaanHeaderController::class)->update($penerimaanUpdatePP, $newPenerimaanPP);
            }

            $getDeposito = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))
                ->select('id')
                ->where('nobukti', $penerimaan_nobuktiDeposito)->first();

            if ($getDeposito != null) {
                $penerimaanHeaderDeposito = [
                    'isUpdate' => 1,
                    'postingdari' => 'PROSES GAJI SUPIR',
                    'datadetail' => $penerimaanDetailDeposito,
                    'nowarkat' => '',
                    'bank_id' => $request->bank_idDeposito

                ];

                $newPenerimaanDeposito = new PenerimaanHeader();
                $newPenerimaanDeposito = $newPenerimaanDeposito->findAll($getDeposito->id);

                $penerimaanUpdateDeposito = new UpdatePenerimaanHeaderRequest($penerimaanHeaderDeposito);
                app(PenerimaanHeaderController::class)->update($penerimaanUpdateDeposito, $newPenerimaanDeposito);
            }

            $getBBM = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))
                ->select('id')
                ->where('nobukti', $penerimaan_nobuktiBBM)->first();

            if ($getBBM != null) {
                $penerimaanHeaderBBM = [
                    'isUpdate' => 1,
                    'postingdari' => 'PROSES GAJI SUPIR',
                    'datadetail' => $penerimaanDetailBBM,
                    'nowarkat' => '',
                    'bank_id' => $request->bank_idBBM

                ];
                $newPenerimaanBBM = new PenerimaanHeader();
                $newPenerimaanBBM = $newPenerimaanBBM->findAll($getBBM->id);

                $penerimaanUpdateBBM = new UpdatePenerimaanHeaderRequest($penerimaanHeaderBBM);
                app(PenerimaanHeaderController::class)->update($penerimaanUpdateBBM, $newPenerimaanBBM);
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
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $getDetail = ProsesGajiSupirDetail::lockForUpdate()->where('prosesgajisupir_id', $id)->get();
        $request['postingdari'] =  'PROSES GAJI SUPIR';

        $prosesGajiSupirHeader = new ProsesGajiSupirHeader();
        $prosesGajiSupirHeader = $prosesGajiSupirHeader->lockAndDestroy($id);
        if ($prosesGajiSupirHeader) {
            $logTrail = [
                'namatabel' => strtoupper($prosesGajiSupirHeader->getTable()),
                'postingdari' => 'DELETE PROSES GAJI SUPIR HEADER',
                'idtrans' => $prosesGajiSupirHeader->id,
                'nobuktitrans' => $prosesGajiSupirHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $prosesGajiSupirHeader->toArray(),
                'modifiedby' => $prosesGajiSupirHeader->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE PROSES GAJI SUPIR DETAIL
            $logTrailProsesGajiSupirDetail = [
                'namatabel' => 'PROSESGAJISUPIRDETAIL',
                'postingdari' => 'DELETE PROSES GAJI SUPIR DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $prosesGajiSupirHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailProsesGajiSupirDetail = new StoreLogTrailRequest($logTrailProsesGajiSupirDetail);
            app(LogTrailController::class)->store($validatedLogTrailProsesGajiSupirDetail);

            foreach ($getDetail as $key => $value) {
                $fetchPS = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $value->gajisupir_nobukti)->where('supir_id', '0')->first();
                if ($fetchPS != null) {

                    $penerimaanPS = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                        ->where('nobukti', $fetchPS->penerimaantrucking_nobukti)->first();

                    $penerimaanTruckingHeaderPS = [
                        'isUpdate' => 2,
                        'postingdari' => 'PROSES GAJI SUPIR',
                        'from' => 'ebs',
                        'bank_id' => 0,
                        'penerimaan_nobukti' => '',
                    ];
                    $newPenerimaanTruckingPS = new PenerimaanTruckingHeader();
                    $newPenerimaanTruckingPS = $newPenerimaanTruckingPS->findAll($penerimaanPS->id);
                    $penerimaanTruckingPS = new UpdatePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderPS);
                    app(PenerimaanTruckingHeaderController::class)->update($penerimaanTruckingPS, $newPenerimaanTruckingPS);

                    $getPenerimaanPS = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))->where('nobukti', $penerimaanPS->penerimaan_nobukti)->first();
                    if ($getPenerimaanPS != null) {
                        app(PenerimaanHeaderController::class)->destroy($request, $getPenerimaanPS->id);
                    }
                }

                $fetchPP = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $value->gajisupir_nobukti)->where('supir_id', '!=', '0')->first();

                if ($fetchPP != null) {

                    $penerimaanPP = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                        ->where('nobukti', $fetchPP->penerimaantrucking_nobukti)->first();

                    $penerimaanTruckingHeaderPS = [
                        'isUpdate' => 2,
                        'postingdari' => 'PROSES GAJI SUPIR',
                        'from' => 'ebs',
                        'bank_id' => 0,
                        'penerimaan_nobukti' => '',
                    ];
                    $newPenerimaanTruckingPS = new PenerimaanTruckingHeader();
                    $newPenerimaanTruckingPS = $newPenerimaanTruckingPS->findAll($penerimaanPP->id);
                    $penerimaanTruckingPS = new UpdatePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderPS);
                    app(PenerimaanTruckingHeaderController::class)->update($penerimaanTruckingPS, $newPenerimaanTruckingPS);

                    $getPenerimaanPP = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))->where('nobukti', $penerimaanPP->penerimaan_nobukti)->first();
                    if ($getPenerimaanPP != null) {
                        app(PenerimaanHeaderController::class)->destroy($request, $getPenerimaanPP->id);
                    }
                }

                $fetchDeposito = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))->where('gajisupir_nobukti', $value->gajisupir_nobukti)->first();

                if ($fetchDeposito != null) {

                    $penerimaanDeposito = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                        ->where('nobukti', $fetchDeposito->penerimaantrucking_nobukti)->first();

                    $penerimaanTruckingHeaderDeposito = [
                        'isUpdate' => 2,
                        'postingdari' => 'PROSES GAJI SUPIR',
                        'from' => 'ebs',
                        'bank_id' => 0,
                        'penerimaan_nobukti' => '',
                    ];
                    $newPenerimaanTruckingDeposito = new PenerimaanTruckingHeader();
                    $newPenerimaanTruckingDeposito = $newPenerimaanTruckingDeposito->findAll($penerimaanDeposito->id);
                    $penerimaanTruckingDeposito = new UpdatePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderDeposito);
                    app(PenerimaanTruckingHeaderController::class)->update($penerimaanTruckingDeposito, $newPenerimaanTruckingDeposito);

                    $getPenerimaanDeposito = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))->where('nobukti', $penerimaanDeposito->penerimaan_nobukti)->first();
                    if ($getPenerimaanDeposito != null) {
                        app(PenerimaanHeaderController::class)->destroy($request, $getPenerimaanDeposito->id);
                    }
                }

                $fetchBBM = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))->where('gajisupir_nobukti', $value->gajisupir_nobukti)->first();

                if ($fetchBBM != null) {

                    $penerimaanBBM = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                        ->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->first();

                    $penerimaanTruckingHeaderBBM = [
                        'isUpdate' => 2,
                        'postingdari' => 'PROSES GAJI SUPIR',
                        'from' => 'ebs',
                        'bank_id' => 0,
                        'penerimaan_nobukti' => '',
                    ];
                    $newPenerimaanTruckingBBM = new PenerimaanTruckingHeader();
                    $newPenerimaanTruckingBBM = $newPenerimaanTruckingBBM->findAll($penerimaanBBM->id);
                    $penerimaanTruckingBBM = new UpdatePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderBBM);
                    app(PenerimaanTruckingHeaderController::class)->update($penerimaanTruckingBBM, $newPenerimaanTruckingBBM);

                    $getPenerimaanBBM = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))->where('nobukti', $penerimaanBBM->penerimaan_nobukti)->first();
                    if ($getPenerimaanBBM != null) {
                        app(PenerimaanHeaderController::class)->destroy($request, $getPenerimaanBBM->id);
                    }
                }

            }

            DB::commit();

            $selected = $this->getPosition($prosesGajiSupirHeader, $prosesGajiSupirHeader->getTable(), true);
            $prosesGajiSupirHeader->position = $selected->position;
            $prosesGajiSupirHeader->id = $selected->id;
            $prosesGajiSupirHeader->page = ceil($prosesGajiSupirHeader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $prosesGajiSupirHeader
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function getRic()
    {
        $gajiSupir = new ProsesGajiSupirHeader();
        $dari = date('Y-m-d', strtotime(request()->dari));
        $sampai = date('Y-m-d', strtotime(request()->sampai));

        $cekRic = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))
            ->whereRaw("tglbukti >= '$dari'")
            ->whereRaw("tglbukti <= '$sampai'")
            ->first();

        //CEK APAKAH ADA RIC
        if ($cekRic) {
            $nobukti = $cekRic->nobukti;
            $cekEBS = ProsesGajiSupirDetail::from(DB::raw("prosesgajisupirdetail with (readuncommitted)"))
                ->whereRaw("gajisupir_nobukti = '$nobukti'")->first();

            return response([
                'errors' => false,
                'data' => $gajiSupir->getRic($dari, $sampai),
                'attributes' => [
                    'totalRows' => $gajiSupir->totalRows,
                    'totalPages' => $gajiSupir->totalPages,
                    'totalBorongan' => $gajiSupir->totalBorongan,
                    'totalUangJalan' => $gajiSupir->totalUangJalan,
                    'totalUangBBM' => $gajiSupir->totalUangBBM,
                    'totalUangMakan' => $gajiSupir->totalUangMakan,
                    'totalPotPinjaman' => $gajiSupir->totalPotPinjaman,
                    'totalPotPinjSemua' => $gajiSupir->totalPotPinjSemua,
                    'totalDeposito' => $gajiSupir->totalDeposito,
                    'totalKomisi' => $gajiSupir->totalKomisi,
                    'totalTol' => $gajiSupir->totalTol
                ]
            ]);
        } else {

            return response([
                'data' => [],
                'attributes' => [
                    'totalRows' => 0,
                    'totalPages' => 0,
                ]
            ]);
        }
    }
    public function getEdit($gajiId)
    {
        $prosesgajisupir = new ProsesGajiSupirHeader();
        $aksi = request()->aksi;
        if ($aksi == 'edit') {
            $dari = date('Y-m-d', strtotime(request()->dari));
            $sampai = date('Y-m-d', strtotime(request()->sampai));
            $data = $prosesgajisupir->getAllEdit($gajiId, $dari, $sampai, $aksi);
        } else {
            $data = $prosesgajisupir->getEdit($gajiId, $aksi);
        }

        return response([
            'data' => $data,
            'attributes' => [
                'totalRows' => $prosesgajisupir->totalRows,
                'totalPages' => $prosesgajisupir->totalPages,
                'totalBorongan' => $prosesgajisupir->totalBorongan,
                'totalUangJalan' => $prosesgajisupir->totalUangJalan,
                'totalUangBBM' => $prosesgajisupir->totalUangBBM,
                'totalUangMakan' => $prosesgajisupir->totalUangMakan,
                'totalPotPinjaman' => $prosesgajisupir->totalPotPinjaman,
                'totalPotPinjSemua' => $prosesgajisupir->totalPotPinjSemua,
                'totalDeposito' => $prosesgajisupir->totalDeposito,
                'totalKomisi' => $prosesgajisupir->totalKomisi,
                'totalTol' => $prosesgajisupir->totalTol
            ]
        ]);
    }

    public function hitungNominal()
    {
        $ric = request()->rincianId;
    }
    public function noEdit()
    {
        $query = Error::from(DB::raw("error with (readuncommitted)"))
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
            $statusSudahCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($prosesgaji->statuscetak != $statusSudahCetak->id) {
                $prosesgaji->statuscetak = $statusSudahCetak->id;
                $prosesgaji->tglbukacetak = date('Y-m-d H:i:s');
                $prosesgaji->userbukacetak = auth('api')->user()->name;
                $prosesgaji->jumlahcetak = $prosesgaji->jumlahcetak + 1;

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
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $prosesgaji->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($status == $statusApproval->id) {
            $query = Error::from(DB::raw("error with (readuncommitted)"))
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
            $query = Error::from(DB::raw("error with (readuncommitted)"))
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

    public function getAllData($dari, $sampai)
    {
        $tglDari = date('Y-m-d', strtotime($dari));
        $tglSampai = date('Y-m-d', strtotime($sampai));

        $gajiSupir = new ProsesGajiSupirHeader();
        return response([
            'potsemua' => $gajiSupir->getPotSemua($tglDari, $tglSampai),
            'potpribadi' => $gajiSupir->getPotPribadi($tglDari, $tglSampai),
            'deposito' => $gajiSupir->getDeposito($tglDari, $tglSampai),
            'bbm' => $gajiSupir->getBBM($tglDari, $tglSampai),
            'pinjaman' => $gajiSupir->getPinjaman($tglDari, $tglSampai)
        ]);
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
