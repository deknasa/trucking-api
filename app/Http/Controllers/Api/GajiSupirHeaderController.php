<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGajiSupirBBMRequest;
use App\Http\Requests\StoreGajiSupirDepositoRequest;
use App\Http\Requests\StoreGajiSupirDetailRequest;
use App\Models\GajiSupirHeader;
use App\Http\Requests\StoreGajiSupirHeaderRequest;
use App\Http\Requests\StoreGajiSupirPelunasanPinjamanRequest;
use App\Http\Requests\StoreGajiSupirPinjamanRequest;
use App\Http\Requests\StoreGajisUpirUangJalanRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePenerimaanTruckingHeaderRequest;
use App\Http\Requests\StorePengeluaranTruckingHeaderRequest;
use App\Http\Requests\UpdateGajiSupirBBMRequest;
use App\Http\Requests\UpdateGajiSupirDepositoRequest;
use App\Http\Requests\UpdateGajiSupirHeaderRequest;
use App\Http\Requests\UpdateGajiSupirPelunasanPinjamanRequest;
use App\Http\Requests\UpdateGajiSupirPinjamanRequest;
use App\Http\Requests\UpdateJurnalUmumHeaderRequest;
use App\Http\Requests\UpdatePenerimaanTruckingHeaderRequest;
use App\Http\Requests\UpdatePengeluaranTruckingHeaderRequest;
use App\Models\Error;
use App\Models\GajiSupirBBM;
use App\Models\GajiSupirDeposito;
use App\Models\GajiSupirDetail;
use App\Models\GajiSupirPelunasanPinjaman;
use App\Models\GajiSupirPinjaman;
use App\Models\GajisUpirUangJalan;
use App\Models\JurnalUmumHeader;
use App\Models\LogTrail;
use App\Models\Parameter;
use App\Models\PenerimaanTrucking;
use App\Models\PenerimaanTruckingHeader;
use App\Models\PengeluaranTruckingDetail;
use App\Models\PengeluaranTruckingHeader;
use App\Models\Ritasi;
use App\Models\Supir;
use App\Models\SuratPengantar;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GajiSupirHeaderController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $gajiSupirHeader = new GajiSupirHeader();
        return response([
            'data' => $gajiSupirHeader->get(),
            'attributes' => [
                'totalRows' => $gajiSupirHeader->totalRows,
                'totalPages' => $gajiSupirHeader->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreGajiSupirHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            if ($request->rincianId != '') {

                if ($request->pinjSemua) {
                    $request->validate(
                        [
                            'nominalPS' => 'required|array',
                            'nominalPS.*' => 'required|numeric|gt:0',
                        ],
                        [
                            'nominalPS.*.numeric' => 'nominal pinjaman yang dipilih pot. pinjaman (semua) harus berupa angka',
                            'nominalPS.*.gt' => 'nominal pinjaman yang dipilih pot. pinjaman (semua) tidak boleh 0 (pot. pinjaman (semua))'
                        ]
                    );

                    for ($i = 0; $i < count($request->pinjSemua); $i++) {
                        if ($request->pinjSemua_sisa[$i] < 0) {

                            $query =  Error::from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'STM')
                                ->first();
                            return response([
                                'errors' => [
                                    "nominalPS.$i" => ["$query->keterangan"]
                                ],
                                'message' => "pinjSemua_sisa",
                            ], 422);
                        }
                    }
                }
                if ($request->pinjPribadi) {
                    $request->validate(
                        [
                            'nominalPP' => 'required|array',
                            'nominalPP.*' => 'required|numeric|gt:0',
                        ],
                        [
                            'nominalPP.*.numeric' => 'nominal pinjaman yang dipilih pot. pinjaman (pribadi) harus berupa angka',
                            'nominalPP.*.gt' => 'nominal pinjaman yang dipilih tidak boleh 0 (pot. pinjaman (pribadi))'
                        ]
                    );

                    for ($i = 0; $i < count($request->pinjPribadi); $i++) {
                        if ($request->pinjPribadi_sisa[$i] < 0) {

                            $query =  Error::from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'STM')
                                ->first();
                            return response([
                                'errors' => [
                                    "nominalPP.$i" => ["$query->keterangan"]
                                ],
                                'message' => "pinjPribadi_sisa",
                            ], 422);
                        }
                    }
                }
                if ($request->nomDeposito > 0) {
                    $request->validate(
                        [
                            'nomDeposito' => 'required|gt:0',
                            'ketDeposito' => 'required'
                        ],
                        [
                            'nomDeposito.gt' => 'nilai deposito tidak boleh 0',
                            'ketDeposito.required' => 'keterangan deposito' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
                        ]
                    );
                }
                if ($request->nomBBM > 0) {
                    $request->validate(
                        [
                            'nomBBM' => 'required|gt:0',
                            'ketBBM' => 'required'
                        ],
                        [
                            'nomBBM.gt' => 'nilai BBM tidak boleh 0',
                            'ketBBM.required' => 'keterangan BBM' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
                        ]
                    );
                }

                $group = 'RINCIAN GAJI SUPIR BUKTI';
                $subgroup = 'RINCIAN GAJI SUPIR BUKTI';


                $format = DB::table('parameter')
                    ->where('grp', $group)
                    ->where('subgrp', $subgroup)
                    ->first();

                $content = new Request();
                $content['group'] = $group;
                $content['subgroup'] = $subgroup;
                $content['table'] = 'gajisupirheader';
                $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

                $gajisupirheader = new GajiSupirHeader();
                $gajisupirheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
                $gajisupirheader->supir_id = $request->supir_id;
                $gajisupirheader->nominal = '';
                $gajisupirheader->tgldari = date('Y-m-d', strtotime($request->tgldari));
                $gajisupirheader->tglsampai = date('Y-m-d', strtotime($request->tglsampai));
                $gajisupirheader->total = '';
                $gajisupirheader->uangjalan = $request->uangjalan ?? 0;
                $gajisupirheader->bbm = $request->nomBBM ?? 0;
                $gajisupirheader->potonganpinjaman = ($request->nominalPP) ? array_sum($request->nominalPP) : 0;
                $gajisupirheader->deposito = $request->nomDeposito ?? 0;
                $gajisupirheader->potonganpinjamansemua = ($request->nominalPS) ? array_sum($request->nominalPS) : 0;
                $gajisupirheader->komisisupir = ($request->rincian_komisisupir) ? array_sum($request->rincian_komisisupir) : 0;
                $gajisupirheader->tolsupir = ($request->rincian_tolsupir) ? array_sum($request->rincian_tolsupir) : 0;
                $gajisupirheader->voucher = $request->voucher ?? 0;
                $gajisupirheader->uangmakanharian = $request->uangmakanharian ?? 0;
                $gajisupirheader->pinjamanpribadi = 0;
                $gajisupirheader->gajiminus = 0;
                $gajisupirheader->uangJalantidakterhitung = $request->uangjalantidakterhitung ?? 0;
                $gajisupirheader->statusformat = $format->id;
                $gajisupirheader->statuscetak = $statusCetak->id;
                $gajisupirheader->modifiedby = auth('api')->user()->name;

                $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
                $gajisupirheader->nobukti = $nobukti;

                $gajisupirheader->save();
                /* Store detail */

                $detaillog = [];

                $total = 0;
                $urut = 1;
                for ($i = 0; $i < count($request->rincianId); $i++) {

                    $total = $total + $request->rincian_gajisupir[$i] + $request->rincian_gajikenek[$i] + $request->rincian_upahritasi[$i] + $request->rincian_biayaextra[$i];
                    $datadetail = [
                        'gajisupir_id' => $gajisupirheader->id,
                        'nobukti' => $gajisupirheader->nobukti,
                        'nominaldeposito' => 0,
                        'nourut' => $urut,
                        'suratpengantar_nobukti' => $request->rincian_nobukti[$i],
                        'ritasi_nobukti' => $request->rincian_ritasi[$i],
                        'komisisupir' => $request->rincian_komisisupir[$i],
                        'tolsupir' => $request->rincian_tolsupir[$i],
                        'voucher' => $request->voucher[$i] ?? 0,
                        'novoucher' => $request->novoucher[$i] ?? 0,
                        'gajisupir' => $request->rincian_gajisupir[$i],
                        'gajikenek' => $request->rincian_gajikenek[$i],
                        'gajiritasi' => $request->rincian_upahritasi[$i],
                        'biayatambahan' => $request->rincian_biayaextra[$i],
                        'keteranganbiayatambahan' => $request->rincian_keteranganbiaya[$i],
                        'nominalpengembalianpinjaman' => 0,
                        'modifiedby' => $gajisupirheader->modifiedby,
                    ];

                    //STORE 
                    $data = new StoreGajiSupirDetailRequest($datadetail);

                    $datadetails = app(GajiSupirDetailController::class)->store($data);

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }


                    $detaillog[] = $datadetails['detail']->toArray();

                    $urut++;
                }

                $nominal = ($total - $gajisupirheader->uangjalan - $gajisupirheader->bbm - $gajisupirheader->potonganpinjaman - $gajisupirheader->potonganpinjamansemua - $gajisupirheader->deposito) + $gajisupirheader->uangmakanharian;

                $gajisupirheader->nominal = $nominal;
                $gajisupirheader->total = $total;
                $gajisupirheader->save();

                // Store Header
                $logTrail = [
                    'namatabel' => strtoupper($gajisupirheader->getTable()),
                    'postingdari' => 'ENTRY GAJI SUPIR HEADER',
                    'idtrans' => $gajisupirheader->id,
                    'nobuktitrans' => $gajisupirheader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $gajisupirheader->toArray(),
                    'modifiedby' => $gajisupirheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                // store detail
                $datalogtrail = [
                    'namatabel' => strtoupper($tabeldetail),
                    'postingdari' => 'ENTRY GAJI SUPIR DETAIL',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $gajisupirheader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => $gajisupirheader->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);

                if ($request->pinjSemua) {

                    $fetchFormatPS = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))
                        ->where('kodepenerimaan', 'PJP')
                        ->first();
                    $statusformatPS = $fetchFormatPS->format;
                    $fetchGrpPS = Parameter::where('id', $statusformatPS)->first();
                    $formatPS = DB::table('parameter')
                        ->where('grp', $fetchGrpPS->grp)
                        ->where('subgrp', $fetchGrpPS->subgrp)
                        ->first();

                    $contentPS = new Request();
                    $contentPS['group'] = $fetchGrpPS->grp;
                    $contentPS['subgroup'] = $fetchGrpPS->subgrp;
                    $contentPS['table'] = 'penerimaantruckingheader';
                    $contentPS['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                    $nobuktiPenerimaanPS = app(Controller::class)->getRunningNumber($contentPS)->original['data'];

                    $penerimaanTruckingDetailPS = [];
                    for ($i = 0; $i < count($request->pinjSemua); $i++) {


                        $penerimaanTruckingDetailPS[] = [
                            'supir_id' => 0,
                            'pengeluarantruckingheader_nobukti' => $request->pinjSemua_nobukti[$i],
                            'nominal' => $request->nominalPS[$i],
                            'keterangan' => $request->pinjSemua_keterangan[$i]
                        ];
                        $gajiSupirPelunasanPS = [
                            'gajisupir_id' => $gajisupirheader->id,
                            'gajisupir_nobukti' => $gajisupirheader->nobukti,
                            'penerimaantrucking_nobukti' => $nobuktiPenerimaanPS,
                            'pengeluarantrucking_nobukti' => $request->pinjSemua_nobukti[$i],
                            'supir_id' => 0,
                            'nominal' => $request->nominalPS[$i]
                        ];

                        $gajiSupirPelunasan = new StoreGajiSupirPelunasanPinjamanRequest($gajiSupirPelunasanPS);
                        app(GajiSupirPelunasanPinjamanController::class)->store($gajiSupirPelunasan);
                    }

                    $penerimaanTruckingHeaderPS = [
                        'tanpaprosesnobukti' => '2',
                        'nobukti' => $nobuktiPenerimaanPS,
                        'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                        'penerimaantrucking_id' => $fetchFormatPS->id,
                        'bank_id' => 0,
                        'coa' => $fetchFormatPS->coapostingkredit,
                        'penerimaan_nobukti' => '',
                        'statusformat' => $formatPS->id,
                        'postingdari' => 'ENTRY GAJI SUPIR',
                        'datadetail' => $penerimaanTruckingDetailPS
                    ];

                    $penerimaanTruckingPS = new StorePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderPS);
                    app(PenerimaanTruckingHeaderController::class)->store($penerimaanTruckingPS);
                }

                if ($request->pinjPribadi) {

                    $fetchFormatPP = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))
                        ->where('kodepenerimaan', 'PJP')
                        ->first();
                    $statusformatPP = $fetchFormatPP->format;
                    $fetchGrpPP = Parameter::where('id', $statusformatPP)->first();
                    $formatPP = DB::table('parameter')
                        ->where('grp', $fetchGrpPP->grp)
                        ->where('subgrp', $fetchGrpPP->subgrp)
                        ->first();

                    $contentPP = new Request();
                    $contentPP['group'] = $fetchGrpPP->grp;
                    $contentPP['subgroup'] = $fetchGrpPP->subgrp;
                    $contentPP['table'] = 'penerimaantruckingheader';
                    $contentPP['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                    $nobuktiPenerimaanPP = app(Controller::class)->getRunningNumber($contentPP)->original['data'];

                    $penerimaanTruckingDetailPP = [];
                    for ($i = 0; $i < count($request->pinjPribadi); $i++) {

                        $penerimaanTruckingDetailPP[] = [
                            'supir_id' => $request->supir_id,
                            'pengeluarantruckingheader_nobukti' => $request->pinjPribadi_nobukti[$i],
                            'nominal' => $request->nominalPP[$i],
                            'keterangan' => $request->pinjPribadi_keterangan[$i]
                        ];
                        $gajiSupirPelunasanPP = [
                            'gajisupir_id' => $gajisupirheader->id,
                            'gajisupir_nobukti' => $gajisupirheader->nobukti,
                            'penerimaantrucking_nobukti' => $nobuktiPenerimaanPP,
                            'pengeluarantrucking_nobukti' => $request->pinjPribadi_nobukti[$i],
                            'supir_id' => $gajisupirheader->supir_id,
                            'nominal' => $request->nominalPP[$i]
                        ];

                        $gajiSupirPelunasan = new StoreGajiSupirPelunasanPinjamanRequest($gajiSupirPelunasanPP);
                        $tes = app(GajiSupirPelunasanPinjamanController::class)->store($gajiSupirPelunasan);
                    }

                    $penerimaanTruckingHeaderPP = [
                        'tanpaprosesnobukti' => '2',
                        'nobukti' => $nobuktiPenerimaanPP,
                        'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                        'penerimaantrucking_id' => $fetchFormatPP->id,
                        'bank_id' => 0,
                        'coa' => $fetchFormatPP->coapostingkredit,
                        'penerimaan_nobukti' => '',
                        'statusformat' => $formatPP->id,
                        'postingdari' => 'ENTRY GAJI SUPIR',
                        'datadetail' => $penerimaanTruckingDetailPP
                    ];

                    $penerimaanTruckingPP = new StorePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderPP);
                    app(PenerimaanTruckingHeaderController::class)->store($penerimaanTruckingPP);
                }


                if ($request->nomDeposito != 0) {
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
                        'nominal' => $request->nomDeposito,
                        'keterangan' => $request->ketDeposito
                    ];
                    $penerimaanTruckingHeaderDPO = [
                        'tanpaprosesnobukti' => '2',
                        'nobukti' => $nobuktiPenerimaanTruckingDPO,
                        'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                        'penerimaantrucking_id' => $fetchFormatDPO->id,
                        'bank_id' => 0,
                        'coa' => $fetchFormatDPO->coapostingkredit,
                        'penerimaan_nobukti' => '',
                        'statusformat' => $formatDPO->id,
                        'postingdari' => 'ENTRY GAJI SUPIR',
                        'datadetail' => $penerimaanTruckingDetailDPO
                    ];
                    $penerimaanTruckingDeposit = new StorePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderDPO);
                    app(PenerimaanTruckingHeaderController::class)->store($penerimaanTruckingDeposit);

                    $gajiSupirDeposito = [
                        'gajisupir_id' => $gajisupirheader->id,
                        'gajisupir_nobukti' => $gajisupirheader->nobukti,
                        'penerimaantrucking_nobukti' => $nobuktiPenerimaanTruckingDPO,
                        'pengeluarantrucking_nobukti' => '',
                        'supir_id' => $request->supir_id,
                        'nominal' => $request->nomDeposito
                    ];

                    $gajiSupirDepo = new StoreGajiSupirDepositoRequest($gajiSupirDeposito);
                    app(GajiSupirDepositoController::class)->store($gajiSupirDepo);
                }

                if ($request->nomBBM != 0) {
                    $fetchFormatBBM = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))
                        ->where('kodepenerimaan', 'BBM')
                        ->first();
                    $statusformatBBM = $fetchFormatBBM->format;
                    $fetchGrpBBM = Parameter::where('id', $statusformatBBM)->first();
                    $formatBBM = DB::table('parameter')
                        ->where('grp', $fetchGrpBBM->grp)
                        ->where('subgrp', $fetchGrpBBM->subgrp)
                        ->first();

                    $contentBBM = new Request();
                    $contentBBM['group'] = $fetchGrpBBM->grp;
                    $contentBBM['subgroup'] = $fetchGrpBBM->subgrp;
                    $contentBBM['table'] = 'penerimaantruckingheader';
                    $contentBBM['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                    $nobuktiPenerimaanTruckingBBM = app(Controller::class)->getRunningNumber($contentBBM)->original['data'];

                    $penerimaanTruckingDetailBBM[] = [
                        'supir_id' => $request->supir_id,
                        'pengeluarantruckingheader_nobukti' => '',
                        'nominal' => $request->nomBBM,
                        'keterangan' => $request->ketBBM
                    ];
                    $penerimaanTruckingHeaderBBM = [
                        'tanpaprosesnobukti' => '2',
                        'nobukti' => $nobuktiPenerimaanTruckingBBM,
                        'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                        'penerimaantrucking_id' => $fetchFormatBBM->id,
                        'bank_id' => 0,
                        'coa' => $fetchFormatBBM->coadebet,
                        'penerimaan_nobukti' => '',
                        'statusformat' => $formatBBM->id,
                        'postingdari' => 'ENTRY GAJI SUPIR',
                        'datadetail' => $penerimaanTruckingDetailBBM
                    ];
                    $penerimaanTruckingDeposit = new StorePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderBBM);
                    app(PenerimaanTruckingHeaderController::class)->store($penerimaanTruckingDeposit);

                    $gajiSupirBBM = [
                        'gajisupir_id' => $gajisupirheader->id,
                        'gajisupir_nobukti' => $gajisupirheader->nobukti,
                        'penerimaantrucking_nobukti' => $nobuktiPenerimaanTruckingBBM,
                        'pengeluarantrucking_nobukti' => '',
                        'supir_id' => $request->supir_id,
                        'nominal' => $request->nomBBM
                    ];

                    $gajiSupirBbm = new StoreGajiSupirBBMRequest($gajiSupirBBM);
                    app(GajiSupirBBMController::class)->store($gajiSupirBbm);

                    $jurnalHeader = [
                        'tanpaprosesnobukti' => 1,
                        'nobukti' => $nobuktiPenerimaanTruckingBBM,
                        'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                        'postingdari' => "ENTRY GAJI SUPIR",
                        'modifiedby' => auth('api')->user()->name,
                        'statusformat' => "0",
                    ];
                    $jurnalDetail = [
                        [
                            'nobukti' => $nobuktiPenerimaanTruckingBBM,
                            'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                            'coa' => $fetchFormatBBM->coadebet,
                            'nominal' => $request->nomBBM,
                            'keterangan' => $request->ketBBM,
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => 0,
                        ],
                        [
                            'nobukti' => $nobuktiPenerimaanTruckingBBM,
                            'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                            'coa' => $fetchFormatBBM->coakredit,
                            'nominal' => -$request->nomBBM,
                            'keterangan' => $request->ketBBM,
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => 0,
                        ]
                    ];
                    $jurnal = $this->storeJurnal($jurnalHeader, $jurnalDetail);
                }

                if ($request->absensi_nobukti) {
                    for ($i = 0; $i < count($request->absensi_nobukti); $i++) {
                        $gajiSupirUangJalan = [
                            'gajisupir_id' => $gajisupirheader->id,
                            'gajisupir_nobukti' => $gajisupirheader->nobukti,
                            'absensisupir_nobukti' => $request->absensi_nobukti[$i],
                            'supir_id' => $request->supir_id,
                            'nominal' => $request->absensi_uangjalan[$i]
                        ];

                        $gajiSupirUang = new StoreGajisUpirUangJalanRequest($gajiSupirUangJalan);
                        app(GajisUpirUangJalanController::class)->store($gajiSupirUang);
                    }
                }

                $request->sortname = $request->sortname ?? 'id';
                $request->sortorder = $request->sortorder ?? 'asc';
                DB::commit();

                /* Set position and page */


                $selected = $this->getPosition($gajisupirheader, $gajisupirheader->getTable());
                $gajisupirheader->position = $selected->position;
                $gajisupirheader->page = ceil($gajisupirheader->position / ($request->limit ?? 10));


                return response([
                    'status' => true,
                    'message' => 'Berhasil disimpan',
                    'data' => $gajisupirheader
                ], 201);
            } else {
                $query = Error::from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'WP')
                    ->first();
                return response([
                    'errors' => [
                        'rincian' => "REKAP RINCIAN $query->keterangan"
                    ],
                    'message' => "REKAP RINCIAN $query->keterangan",
                ], 422);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show($id)
    {
        $gajisupirpinjaman = new GajiSupirPelunasanPinjaman();
        $data = GajiSupirHeader::findAll($id);
        $deposito = GajiSupirDeposito::findAll($data->nobukti);
        $BBM = GajiSupirBBM::findAll($data->nobukti);

        return response([
            'status' => true,
            'data' => $data,
            'deposito' => $deposito,
            'bbm' => $BBM
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateGajiSupirHeaderRequest $request, GajiSupirHeader $gajisupirheader)
    {
        DB::beginTransaction();

        try {

            if ($request->rincianId != '') {

                if ($request->pinjSemua) {
                    $request->validate(
                        [
                            'nominalPS' => 'required|array',
                            'nominalPS.*' => 'required|numeric|gt:0',
                        ],
                        [
                            'nominalPS.*.numeric' => 'nominal pinjaman yang dipilih pot. pinjaman (semua) harus berupa angka',
                            'nominalPS.*.gt' => 'nominal pinjaman yang dipilih pot. pinjaman (semua) tidak boleh 0 (pot. pinjaman (semua))'
                        ]
                    );

                    for ($i = 0; $i < count($request->pinjSemua); $i++) {
                        if ($request->pinjSemua_sisa[$i] < 0) {

                            $query =  Error::from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'STM')
                                ->first();
                            return response([
                                'errors' => [
                                    "nominalPS.$i" => ["$query->keterangan"]
                                ],
                                'message' => "pinjSemua_sisa",
                            ], 422);
                        }
                    }
                }
                if ($request->pinjPribadi) {
                    $request->validate(
                        [
                            'nominalPP' => 'required|array',
                            'nominalPP.*' => 'required|numeric|gt:0',
                        ],
                        [
                            'nominalPP.*.numeric' => 'nominal pinjaman yang dipilih pot. pinjaman (pribadi) harus berupa angka',
                            'nominalPP.*.gt' => 'nominal pinjaman yang dipilih tidak boleh 0 (pot. pinjaman (pribadi))'
                        ]
                    );

                    for ($i = 0; $i < count($request->pinjPribadi); $i++) {
                        if ($request->pinjPribadi_sisa[$i] < 0) {

                            $query =  Error::from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'STM')
                                ->first();
                            return response([
                                'errors' => [
                                    "nominalPP.$i" => ["$query->keterangan"]
                                ],
                                'message' => "pinjPribadi_sisa",
                            ], 422);
                        }
                    }
                }
                if ($request->nomDeposito > 0) {
                    $request->validate(
                        [
                            'nomDeposito' => 'required|gt:0',
                            'ketDeposito' => 'required'
                        ],
                        [
                            'nomDeposito.gt' => 'nilai deposito tidak boleh 0',
                            'ketDeposito.required' => 'keterangan deposito' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
                        ]
                    );
                }
                if ($request->nomBBM > 0) {
                    $request->validate(
                        [
                            'nomBBM' => 'required|gt:0',
                            'ketBBM' => 'required'
                        ],
                        [
                            'nomBBM.gt' => 'nilai BBM tidak boleh 0',
                            'ketBBM.required' => 'keterangan BBM' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
                        ]
                    );
                }

                $gajisupirheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
                $gajisupirheader->supir_id = $request->supir_id;
                $gajisupirheader->nominal = '';
                $gajisupirheader->tgldari = date('Y-m-d', strtotime($request->tgldari));
                $gajisupirheader->tglsampai = date('Y-m-d', strtotime($request->tglsampai));
                $gajisupirheader->total = '';
                $gajisupirheader->uangjalan = $request->uangjalan ?? 0;
                $gajisupirheader->bbm = $request->nomBBM ?? 0;
                $gajisupirheader->potonganpinjaman = ($request->nominalPP) ? array_sum($request->nominalPP) : 0;
                $gajisupirheader->deposito = $request->nomDeposito ?? 0;
                $gajisupirheader->potonganpinjamansemua = ($request->nominalPS) ? array_sum($request->nominalPS) : 0;
                $gajisupirheader->komisisupir = ($request->rincian_komisisupir) ? array_sum($request->rincian_komisisupir) : 0;
                $gajisupirheader->tolsupir = ($request->rincian_tolsupir) ? array_sum($request->rincian_tolsupir) : 0;
                $gajisupirheader->voucher = $request->voucher ?? 0;
                $gajisupirheader->uangmakanharian = $request->uangmakanharian ?? 0;
                $gajisupirheader->pinjamanpribadi = 0;
                $gajisupirheader->gajiminus = 0;
                $gajisupirheader->uangJalantidakterhitung = $request->uangjalantidakterhitung ?? 0;
                $gajisupirheader->modifiedby = auth('api')->user()->name;


                if ($gajisupirheader->save()) {

                    GajiSupirDetail::where('gajisupir_id', $gajisupirheader->id)->delete();

                    /* Store detail */
                    $detaillog = [];
                    $total = 0;
                    $urut = 1;

                    for ($i = 0; $i < count($request->rincianId); $i++) {
                        $total = $total + $request->rincian_gajisupir[$i] + $request->rincian_gajikenek[$i] + $request->rincian_upahritasi[$i] + $request->rincian_biayaextra[$i];
                        $datadetail = [
                            'gajisupir_id' => $gajisupirheader->id,
                            'nobukti' => $gajisupirheader->nobukti,
                            'nominaldeposito' => 0,
                            'nourut' => $urut,
                            'suratpengantar_nobukti' => $request->rincian_nobukti[$i],
                            'ritasi_nobukti' => $request->rincian_ritasi[$i],
                            'komisisupir' => $request->rincian_komisisupir[$i],
                            'tolsupir' =>  $request->rincian_tolsupir[$i],
                            'voucher' => $request->voucher[$i] ?? 0,
                            'novoucher' => $request->novoucher[$i] ?? 0,
                            'gajisupir' => $request->rincian_gajisupir[$i],
                            'gajikenek' => $request->rincian_gajikenek[$i],
                            'gajiritasi' => $request->rincian_upahritasi[$i],
                            'biayatambahan' => $request->rincian_biayaextra[$i],
                            'keteranganbiayatambahan' => $request->rincian_keteranganbiaya[$i] ?? '',
                            'nominalpengembalianpinjaman' => 0,
                            'modifiedby' => $gajisupirheader->modifiedby,
                        ];

                        //STORE

                        $data = new StoreGajiSupirDetailRequest($datadetail);
                        $datadetails = app(GajiSupirDetailController::class)->store($data);

                        if ($datadetails['error']) {
                            return response($datadetails, 422);
                        } else {
                            $iddetail = $datadetails['id'];
                            $tabeldetail = $datadetails['tabel'];
                        }

                        $detaillog[] = $datadetails['detail']->toArray();

                        $urut++;
                    }


                    $nominal = ($total - $gajisupirheader->uangjalan - $gajisupirheader->bbm - $gajisupirheader->potonganpinjaman - $gajisupirheader->potonganpinjamansemua - $gajisupirheader->deposito) + $gajisupirheader->uangmakanharian;

                    $gajisupirheader->nominal = $nominal;
                    $gajisupirheader->total = $total;
                    $gajisupirheader->save();

                    // store log header
                    $logTrail = [
                        'namatabel' => strtoupper($gajisupirheader->getTable()),
                        'postingdari' => 'EDIT GAJI SUPIR HEADER',
                        'idtrans' => $gajisupirheader->id,
                        'nobuktitrans' => $gajisupirheader->nobukti,
                        'aksi' => 'EDIT',
                        'datajson' => $gajisupirheader->toArray(),
                        'modifiedby' => $gajisupirheader->modifiedby
                    ];
                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                    // store log detail
                    $datalogtrail = [
                        'namatabel' => strtoupper($tabeldetail),
                        'postingdari' => 'EDIT GAJI SUPIR DETAIL',
                        'idtrans' =>  $storedLogTrail['id'],
                        'nobuktitrans' => $gajisupirheader->nobukti,
                        'aksi' => 'EDIT',
                        'datajson' => $detaillog,
                        'modifiedby' => $gajisupirheader->modifiedby,
                    ];

                    $data = new StoreLogTrailRequest($datalogtrail);

                    app(LogTrailController::class)->store($data);
                }


                if ($request->pinjSemua != 0) {

                    $fetchPS = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
                        ->where('gajisupir_nobukti', $gajisupirheader->nobukti)->where('supir_id', '0')->first();

                    // jika ada maka update
                    if ($fetchPS != null) {

                        $pengeluaranPS = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                            ->where('nobukti', $fetchPS->penerimaantrucking_nobukti)->first();
                        GajiSupirPelunasanPinjaman::where('gajisupir_nobukti', $gajisupirheader->nobukti)->where('supir_id', '0')->delete();
                        $penerimaanTruckingDetailPS = [];
                        for ($i = 0; $i < count($request->pinjSemua); $i++) {

                            $penerimaanTruckingDetailPS[] = [
                                'supir_id' => 0,
                                'pengeluarantruckingheader_nobukti' => $request->pinjSemua_nobukti[$i],
                                'nominal' => $request->nominalPS[$i],
                                'keterangan' => $request->pinjSemua_keterangan[$i]
                            ];

                            $gajiSupirPelunasanPS = [
                                'gajisupir_id' => $gajisupirheader->id,
                                'gajisupir_nobukti' => $gajisupirheader->nobukti,
                                'penerimaantrucking_nobukti' => $fetchPS->penerimaantrucking_nobukti,
                                'pengeluarantrucking_nobukti' => $request->pinjSemua_nobukti[$i],
                                'supir_id' => 0,
                                'nominal' => $request->nominalPS[$i]
                            ];


                            $gajiSupirPelunasan = new StoreGajiSupirPelunasanPinjamanRequest($gajiSupirPelunasanPS);
                            $tes = app(GajiSupirPelunasanPinjamanController::class)->store($gajiSupirPelunasan);
                        }
                        $penerimaanTruckingHeaderPS = [
                            'isUpdate' => 2,
                            'postingdari' => 'EDIT GAJI SUPIR',
                            'datadetail' => $penerimaanTruckingDetailPS
                        ];
                        $newPenerimaanTruckingPS = new PenerimaanTruckingHeader();
                        $newPenerimaanTruckingPS = $newPenerimaanTruckingPS->findAll($pengeluaranPS->id);
                        $penerimaanTruckingPS = new UpdatePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderPS);
                        app(PenerimaanTruckingHeaderController::class)->update($penerimaanTruckingPS, $newPenerimaanTruckingPS);
                    } else {
                        // jika tidak ada, maka insert

                        $fetchFormatPS = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))
                            ->where('kodepenerimaan', 'PJP')
                            ->first();
                        $statusformatPS = $fetchFormatPS->format;
                        $fetchGrpPS = Parameter::where('id', $statusformatPS)->first();
                        $formatPS = DB::table('parameter')
                            ->where('grp', $fetchGrpPS->grp)
                            ->where('subgrp', $fetchGrpPS->subgrp)
                            ->first();

                        $contentPS = new Request();
                        $contentPS['group'] = $fetchGrpPS->grp;
                        $contentPS['subgroup'] = $fetchGrpPS->subgrp;
                        $contentPS['table'] = 'penerimaantruckingheader';
                        $contentPS['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                        $nobuktiPenerimaanPS = app(Controller::class)->getRunningNumber($contentPS)->original['data'];

                        $penerimaanTruckingDetailPS = [];
                        for ($i = 0; $i < count($request->pinjSemua); $i++) {

                            $penerimaanTruckingDetailPS[] = [
                                'supir_id' => 0,
                                'pengeluarantruckingheader_nobukti' => $request->pinjSemua_nobukti[$i],
                                'nominal' => $request->nominalPS[$i],
                                'keterangan' => $request->pinjSemua_keterangan[$i]
                            ];
                            $gajiSupirPelunasanPS = [
                                'gajisupir_id' => $gajisupirheader->id,
                                'gajisupir_nobukti' => $gajisupirheader->nobukti,
                                'penerimaantrucking_nobukti' => $nobuktiPenerimaanPS,
                                'pengeluarantrucking_nobukti' => $request->pinjSemua_nobukti[$i],
                                'supir_id' => 0,
                                'nominal' => $request->nominalPS[$i]
                            ];

                            $gajiSupirPelunasan = new StoreGajiSupirPelunasanPinjamanRequest($gajiSupirPelunasanPS);
                            app(GajiSupirPelunasanPinjamanController::class)->store($gajiSupirPelunasan);
                        }

                        $penerimaanTruckingHeaderPS = [
                            'tanpaprosesnobukti' => '2',
                            'nobukti' => $nobuktiPenerimaanPS,
                            'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                            'penerimaantrucking_id' => $fetchFormatPS->id,
                            'bank_id' => 0,
                            'coa' => $fetchFormatPS->coapostingkredit,
                            'penerimaan_nobukti' => '',
                            'statusformat' => $formatPS->id,
                            'postingdari' => 'ENTRY GAJI SUPIR',
                            'datadetail' => $penerimaanTruckingDetailPS
                        ];

                        $penerimaanTruckingPS = new StorePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderPS);
                        app(PenerimaanTruckingHeaderController::class)->store($penerimaanTruckingPS);
                    }
                } else {
                    $fetchPS = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
                        ->where('gajisupir_nobukti', $gajisupirheader->nobukti)->where('supir_id', '0')->first();
                    if ($fetchPS != null) {
                        $getPenerimaanTrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where('nobukti', $fetchPS->penerimaantrucking_nobukti)->first();
                        $request['postingdari'] = 'GAJI SUPIR';
                        $request['gajisupir'] = 1;

                        app(PenerimaanTruckingHeaderController::class)->destroy($request, $getPenerimaanTrucking->id);
                        $getDetailGSPS = GajiSupirPelunasanPinjaman::lockForUpdate()->where('gajisupir_nobukti', $gajisupirheader->nobukti)->where('supir_id', '0')->get();
                        foreach ($getDetailGSPS as $key => $value) {
                            app(GajiSupirPelunasanPinjamanController::class)->destroy($request, $value->id);
                        }
                    }
                }

                if ($request->pinjPribadi != 0) {

                    $fetchPP = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
                        ->where('gajisupir_nobukti', $gajisupirheader->nobukti)->where('supir_id', $request->supir_id)->first();

                    // jika ada maka edit
                    if ($fetchPP != null) {

                        $pengeluaranPP = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                            ->where('nobukti', $fetchPP->penerimaantrucking_nobukti)->first();
                        GajiSupirPelunasanPinjaman::where('gajisupir_nobukti', $gajisupirheader->nobukti)->where('supir_id', $request->supir_id)->delete();
                        $penerimaanTruckingDetailPP = [];
                        for ($i = 0; $i < count($request->pinjPribadi); $i++) {

                            $penerimaanTruckingDetailPP[] = [
                                'supir_id' => $request->supir_id,
                                'pengeluarantruckingheader_nobukti' => $request->pinjPribadi_nobukti[$i],
                                'nominal' => $request->nominalPP[$i],
                                'keterangan' => $request->pinjPribadi_keterangan[$i]
                            ];

                            $gajiSupirPelunasanPP = [
                                'gajisupir_id' => $gajisupirheader->id,
                                'gajisupir_nobukti' => $gajisupirheader->nobukti,
                                'penerimaantrucking_nobukti' => $fetchPP->penerimaantrucking_nobukti,
                                'pengeluarantrucking_nobukti' => $request->pinjPribadi_nobukti[$i],
                                'supir_id' => $gajisupirheader->supir_id,
                                'nominal' => $request->nominalPP[$i]
                            ];


                            $gajiSupirPelunasan = new StoreGajiSupirPelunasanPinjamanRequest($gajiSupirPelunasanPP);
                            $tes = app(GajiSupirPelunasanPinjamanController::class)->store($gajiSupirPelunasan);
                        }
                        $penerimaanTruckingHeaderPP = [
                            'isUpdate' => 2,
                            'postingdari' => 'EDIT GAJI SUPIR',
                            'datadetail' => $penerimaanTruckingDetailPP
                        ];
                        $newPenerimaanTruckingPP = new PenerimaanTruckingHeader();
                        $newPenerimaanTruckingPP = $newPenerimaanTruckingPP->findAll($pengeluaranPP->id);
                        $penerimaanTruckingPP = new UpdatePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderPP);
                        app(PenerimaanTruckingHeaderController::class)->update($penerimaanTruckingPP, $newPenerimaanTruckingPP);
                    } else {
                        // jika tidak ada, maka insert

                        $fetchFormatPP = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))
                            ->where('kodepenerimaan', 'PJP')
                            ->first();
                        $statusformatPP = $fetchFormatPP->format;
                        $fetchGrpPP = Parameter::where('id', $statusformatPP)->first();
                        $formatPP = DB::table('parameter')
                            ->where('grp', $fetchGrpPP->grp)
                            ->where('subgrp', $fetchGrpPP->subgrp)
                            ->first();

                        $contentPP = new Request();
                        $contentPP['group'] = $fetchGrpPP->grp;
                        $contentPP['subgroup'] = $fetchGrpPP->subgrp;
                        $contentPP['table'] = 'penerimaantruckingheader';
                        $contentPP['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                        $nobuktiPenerimaanPP = app(Controller::class)->getRunningNumber($contentPP)->original['data'];

                        $penerimaanTruckingDetailPP = [];
                        for ($i = 0; $i < count($request->pinjPribadi); $i++) {

                            $penerimaanTruckingDetailPP[] = [
                                'supir_id' => $request->supir_id,
                                'pengeluarantruckingheader_nobukti' => $request->pinjPribadi_nobukti[$i],
                                'nominal' => $request->nominalPP[$i],
                                'keterangan' => $request->pinjPribadi_keterangan[$i]
                            ];
                            $gajiSupirPelunasanPP = [
                                'gajisupir_id' => $gajisupirheader->id,
                                'gajisupir_nobukti' => $gajisupirheader->nobukti,
                                'penerimaantrucking_nobukti' => $nobuktiPenerimaanPP,
                                'pengeluarantrucking_nobukti' => $request->pinjPribadi_nobukti[$i],
                                'supir_id' => $gajisupirheader->supir_id,
                                'nominal' => $request->nominalPP[$i]
                            ];

                            $gajiSupirPelunasan = new StoreGajiSupirPelunasanPinjamanRequest($gajiSupirPelunasanPP);
                            $tes = app(GajiSupirPelunasanPinjamanController::class)->store($gajiSupirPelunasan);
                        }

                        $penerimaanTruckingHeaderPP = [
                            'tanpaprosesnobukti' => '2',
                            'nobukti' => $nobuktiPenerimaanPP,
                            'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                            'penerimaantrucking_id' => $fetchFormatPP->id,
                            'bank_id' => 0,
                            'coa' => $fetchFormatPP->coapostingkredit,
                            'penerimaan_nobukti' => '',
                            'statusformat' => $formatPP->id,
                            'postingdari' => 'ENTRY GAJI SUPIR',
                            'datadetail' => $penerimaanTruckingDetailPP
                        ];

                        $penerimaanTruckingPP = new StorePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderPP);
                        app(PenerimaanTruckingHeaderController::class)->store($penerimaanTruckingPP);
                    }
                } else {
                    $fetchPP = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
                        ->where('gajisupir_nobukti', $gajisupirheader->nobukti)->where('supir_id', $request->supir_id)->first();
                    if ($fetchPP != null) {
                        $getPenerimaanTrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where('nobukti', $fetchPP->penerimaantrucking_nobukti)->first();
                        $request['postingdari'] = 'GAJI SUPIR';
                        $request['gajisupir'] = 1;

                        app(PenerimaanTruckingHeaderController::class)->destroy($request, $getPenerimaanTrucking->id);
                        $getDetailGSPP = GajiSupirPelunasanPinjaman::lockForUpdate()->where('gajisupir_nobukti', $gajisupirheader->nobukti)->where('supir_id', $gajisupirheader->supir_id)->get();
                        foreach ($getDetailGSPP as $key => $value) {
                            app(GajiSupirPelunasanPinjamanController::class)->destroy($request, $value->id);
                        }
                    }
                }


                if ($request->nomDeposito != 0) {

                    $fetchDPO = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))
                        ->where('gajisupir_nobukti', $gajisupirheader->nobukti)->first();

                    // jika ada maka update
                    if ($fetchDPO != null) {
                        $penerimaanDepo = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                            ->where('nobukti', $fetchDPO->penerimaantrucking_nobukti)->first();

                        $penerimaanTruckingDetailDPO[] = [
                            'supir_id' => $request->supir_id,
                            'pengeluarantruckingheader_nobukti' => '',
                            'nominal' => $request->nomDeposito,
                            'keterangan' => $request->ketDeposito
                        ];
                        $penerimaanTruckingHeaderDPO = [
                            'isUpdate' => 2,
                            'postingdari' => 'EDIT GAJI SUPIR',
                            'datadetail' => $penerimaanTruckingDetailDPO
                        ];
                        $newPenerimaanTruckingDPO = new PenerimaanTruckingHeader();
                        $newPenerimaanTruckingDPO = $newPenerimaanTruckingDPO->findAll($penerimaanDepo->id);
                        $penerimaanTrucking = new UpdatePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderDPO);
                        app(PenerimaanTruckingHeaderController::class)->update($penerimaanTrucking, $newPenerimaanTruckingDPO);

                        $gajiSupirDeposito = [
                            'supir_id' => $request->supir_id,
                            'nominal' => $request->nomDeposito
                        ];

                        $newGajisSupirDPO = GajiSupirDeposito::find($fetchDPO->id);
                        $gajiSupirDepo = new UpdateGajiSupirDepositoRequest($gajiSupirDeposito);
                        app(GajiSupirDepositoController::class)->update($gajiSupirDepo, $newGajisSupirDPO);
                    } else {
                        // jika tidak ada, maka insert
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
                            'nominal' => $request->nomDeposito,
                            'keterangan' => $request->ketDeposito
                        ];
                        $penerimaanTruckingHeaderDPO = [
                            'tanpaprosesnobukti' => '2',
                            'nobukti' => $nobuktiPenerimaanTruckingDPO,
                            'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                            'penerimaantrucking_id' => $fetchFormatDPO->id,
                            'bank_id' => 0,
                            'coa' => $fetchFormatDPO->coapostingkredit,
                            'penerimaan_nobukti' => '',
                            'statusformat' => $formatDPO->id,
                            'postingdari' => 'ENTRY GAJI SUPIR',
                            'datadetail' => $penerimaanTruckingDetailDPO
                        ];
                        $penerimaanTruckingDeposit = new StorePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderDPO);
                        app(PenerimaanTruckingHeaderController::class)->store($penerimaanTruckingDeposit);

                        $gajiSupirDeposito = [
                            'gajisupir_id' => $gajisupirheader->id,
                            'gajisupir_nobukti' => $gajisupirheader->nobukti,
                            'penerimaantrucking_nobukti' => $nobuktiPenerimaanTruckingDPO,
                            'pengeluarantrucking_nobukti' => '',
                            'supir_id' => $request->supir_id,
                            'nominal' => $request->nomDeposito
                        ];

                        $gajiSupirDepo = new StoreGajiSupirDepositoRequest($gajiSupirDeposito);
                        app(GajiSupirDepositoController::class)->store($gajiSupirDepo);
                    }
                } else {
                    $fetchDPO = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))
                        ->where('gajisupir_nobukti', $gajisupirheader->nobukti)->first();
                    if ($fetchDPO != null) {
                        $getPenerimaanTrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where('nobukti', $fetchDPO->penerimaantrucking_nobukti)->first();
                        $request['postingdari'] = 'GAJI SUPIR';
                        $request['gajisupir'] = 1;

                        app(PenerimaanTruckingHeaderController::class)->destroy($request, $getPenerimaanTrucking->id);
                        app(GajiSupirDepositoController::class)->destroy($request, $fetchDPO->id);
                    }
                }

                if ($request->nomBBM != 0) {

                    $fetchBBM = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))
                        ->where('gajisupir_nobukti', $gajisupirheader->nobukti)->first();

                    // jika ada maka update
                    if ($fetchBBM != null) {
                        $pengeluaranbbm = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                            ->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->first();

                        $fetchFormatBBM = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))
                            ->where('kodepenerimaan', 'BBM')
                            ->first();
                        $nobuktiPenerimaanTruckingBBM = $fetchBBM->penerimaantrucking_nobukti;

                        $penerimaanTruckingDetailBBM[] = [
                            'supir_id' => $request->supir_id,
                            'pengeluarantruckingheader_nobukti' => '',
                            'nominal' => $request->nomBBM,
                            'keterangan' => $request->ketBBM
                        ];
                        $penerimaanTruckingHeaderBBM = [
                            'isUpdate' => 2,
                            'postingdari' => 'EDIT GAJI SUPIR',
                            'datadetail' => $penerimaanTruckingDetailBBM
                        ];
                        $newPenerimaanTruckingBBM = new PenerimaanTruckingHeader();
                        $newPenerimaanTruckingBBM = $newPenerimaanTruckingBBM->findAll($pengeluaranbbm->id);
                        $penerimaanTruckingBBM = new UpdatePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderBBM);
                        app(PenerimaanTruckingHeaderController::class)->update($penerimaanTruckingBBM, $newPenerimaanTruckingBBM);

                        $gajiSupirBBM = [
                            'supir_id' => $request->supir_id,
                            'nominal' => $request->nomBBM
                        ];

                        $newGajisSupirBBM = GajiSupirBBM::find($fetchBBM->id);
                        $gajiSupirbbm = new UpdateGajiSupirBBMRequest($gajiSupirBBM);
                        app(GajiSupirBBMController::class)->update($gajiSupirbbm, $newGajisSupirBBM);
                    } else {
                        // jika tidak ada, maka insert
                        $fetchFormatBBM = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))
                            ->where('kodepenerimaan', 'BBM')
                            ->first();
                        $statusformatBBM = $fetchFormatBBM->format;
                        $fetchGrpBBM = Parameter::where('id', $statusformatBBM)->first();
                        $formatBBM = DB::table('parameter')
                            ->where('grp', $fetchGrpBBM->grp)
                            ->where('subgrp', $fetchGrpBBM->subgrp)
                            ->first();

                        $contentBBM = new Request();
                        $contentBBM['group'] = $fetchGrpBBM->grp;
                        $contentBBM['subgroup'] = $fetchGrpBBM->subgrp;
                        $contentBBM['table'] = 'penerimaantruckingheader';
                        $contentBBM['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                        $nobuktiPenerimaanTruckingBBM = app(Controller::class)->getRunningNumber($contentBBM)->original['data'];

                        $penerimaanTruckingDetailBBM[] = [
                            'supir_id' => $request->supir_id,
                            'pengeluarantruckingheader_nobukti' => '',
                            'nominal' => $request->nomBBM,
                            'keterangan' => $request->ketBBM
                        ];
                        $penerimaanTruckingHeaderBBM = [
                            'tanpaprosesnobukti' => '2',
                            'nobukti' => $nobuktiPenerimaanTruckingBBM,
                            'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                            'penerimaantrucking_id' => $fetchFormatBBM->id,
                            'bank_id' => 0,
                            'coa' => $fetchFormatBBM->coadebet,
                            'penerimaan_nobukti' => '',
                            'statusformat' => $formatBBM->id,
                            'postingdari' => 'ENTRY GAJI SUPIR',
                            'datadetail' => $penerimaanTruckingDetailBBM
                        ];
                        $penerimaanTruckingBBM = new StorePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderBBM);
                        app(PenerimaanTruckingHeaderController::class)->store($penerimaanTruckingBBM);

                        $gajiSupirBBM = [
                            'gajisupir_id' => $gajisupirheader->id,
                            'gajisupir_nobukti' => $gajisupirheader->nobukti,
                            'penerimaantrucking_nobukti' => $nobuktiPenerimaanTruckingBBM,
                            'pengeluarantrucking_nobukti' => '',
                            'supir_id' => $request->supir_id,
                            'nominal' => $request->nomBBM
                        ];

                        $newGajisSupirBBM = new StoreGajiSupirBBMRequest($gajiSupirBBM);
                        app(GajiSupirBBMController::class)->store($newGajisSupirBBM);
                    }

                    $jurnalDetail = [
                        [
                            'nobukti' => $nobuktiPenerimaanTruckingBBM,
                            'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                            'coa' => $fetchFormatBBM->coadebet,
                            'nominal' => $request->nomBBM,
                            'keterangan' => $request->ketBBM,
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => 0,
                        ],
                        [
                            'nobukti' => $nobuktiPenerimaanTruckingBBM,
                            'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                            'coa' => $fetchFormatBBM->coakredit,
                            'nominal' => -$request->nomBBM,
                            'keterangan' => $request->ketBBM,
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => 0,
                        ]
                    ];
                    $jurnalHeader = [
                        'isUpdate' => 1,
                        'postingdari' => $request->postingdari ?? "EDIT GAJI SUPIR",
                        'modifiedby' => auth('api')->user()->name,
                        'datadetail' => $jurnalDetail
                    ];

                    $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $nobuktiPenerimaanTruckingBBM)->first();
                    $newJurnal = new JurnalUmumHeader();
                    $newJurnal = $newJurnal->find($getJurnal->id);
                    $jurnal = new UpdateJurnalUmumHeaderRequest($jurnalHeader);
                    app(JurnalUmumHeaderController::class)->update($jurnal, $newJurnal);
                } else {
                    $fetchBBM = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))
                        ->where('gajisupir_nobukti', $gajisupirheader->nobukti)->first();
                    if ($fetchBBM != null) {
                        $getPenerimaanTrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->first();
                        $request['postingdari'] = 'GAJI SUPIR';
                        $request['gajisupir'] = 1;

                        $getJurnalHeader = JurnalUmumHeader::lockForUpdate()->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->first();

                        app(PenerimaanTruckingHeaderController::class)->destroy($request, $getPenerimaanTrucking->id);
                        app(GajiSupirBBMController::class)->destroy($request, $fetchBBM->id);
                        app(JurnalUmumHeaderController::class)->destroy($request, $getJurnalHeader->id);
                    }
                }

                if ($request->absensi_nobukti) {
                    $cekUangjalan = GajisUpirUangJalan::from(DB::raw("gajisupiruangjalan with (readuncommitted)"))
                        ->where('gajisupir_nobukti', $gajisupirheader->nobukti)->where('supir_id', $request->supir_id)->first();

                    if ($cekUangjalan != null) {
                        GajisUpirUangJalan::where('gajisupir_nobukti', $gajisupirheader->nobukti)->where('supir_id', $request->supir_id)->delete();
                    }

                    for ($i = 0; $i < count($request->absensi_nobukti); $i++) {
                        $gajiSupirUangJalan = [
                            'gajisupir_id' => $gajisupirheader->id,
                            'gajisupir_nobukti' => $gajisupirheader->nobukti,
                            'absensisupir_nobukti' => $request->absensi_nobukti[$i],
                            'supir_id' => $request->supir_id,
                            'nominal' => $request->absensi_uangjalan[$i]
                        ];

                        $gajiSupirUang = new StoreGajisUpirUangJalanRequest($gajiSupirUangJalan);
                        app(GajisUpirUangJalanController::class)->store($gajiSupirUang);
                    }
                }
                $request->sortname = $request->sortname ?? 'id';
                $request->sortorder = $request->sortorder ?? 'asc';


                DB::commit();

                /* Set position and page */
                $selected = $this->getPosition($gajisupirheader, $gajisupirheader->getTable());
                $gajisupirheader->position = $selected->position;
                $gajisupirheader->page = ceil($gajisupirheader->position / ($request->limit ?? 10));


                return response([
                    'status' => true,
                    'message' => 'Berhasil disimpan',
                    'data' => $gajisupirheader
                ]);
            } else {
                $query = Error::from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'WP')
                    ->first();
                return response([
                    'errors' => [
                        'rincian' => "REKAP RINCIAN $query->keterangan"
                    ],
                    'message' => "REKAP RINCIAN $query->keterangan",
                ], 422);
            }
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

        $getDetail = GajiSupirDetail::lockForUpdate()->where('gajisupir_id', $id)->get();
        $request['postingdari'] = "DELETE GAJI SUPIR";
        $request['gajisupir'] = 1;

        $gajiSupir = new GajiSupirHeader();
        $gajiSupir = $gajiSupir->lockAndDestroy($id);

        if ($gajiSupir) {
            $logTrail = [
                'namatabel' => strtoupper($gajiSupir->getTable()),
                'postingdari' => 'DELETE GAJI SUPIR HEADER',
                'idtrans' => $gajiSupir->id,
                'nobuktitrans' => $gajiSupir->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $gajiSupir->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE GAJI SUPIR DETAIL
            $logTrailGajiSupirDetail = [
                'namatabel' => 'GAJISUPIRDETAIL',
                'postingdari' => 'DELETE GAJI SUPIR DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $gajiSupir->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailGajiSupirDetail = new StoreLogTrailRequest($logTrailGajiSupirDetail);
            app(LogTrailController::class)->store($validatedLogTrailGajiSupirDetail);

            $fetchDPO = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))->whereRaw("gajisupir_nobukti = '$gajiSupir->nobukti'")->first();
            if ($fetchDPO != null) {
                $getPenerimaanTrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where('nobukti', $fetchDPO->penerimaantrucking_nobukti)->first();
                app(PenerimaanTruckingHeaderController::class)->destroy($request, $getPenerimaanTrucking->id);
                app(GajiSupirDepositoController::class)->destroy($request, $fetchDPO->id);
            }

            $fetchBBM = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))->whereRaw("gajisupir_nobukti = '$gajiSupir->nobukti'")->first();
            if ($fetchBBM != null) {
                $getPenerimaanTrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->first();
                $getJurnalHeader = JurnalUmumHeader::lockForUpdate()->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->first();
                app(PenerimaanTruckingHeaderController::class)->destroy($request, $getPenerimaanTrucking->id);
                app(GajiSupirBBMController::class)->destroy($request, $fetchBBM->id);
                app(JurnalUmumHeaderController::class)->destroy($request, $getJurnalHeader->id);
            }

            $fetchPP = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti',  $gajiSupir->nobukti)->where('supir_id', $gajiSupir->supir_id)->first();

            if ($fetchPP != null) {
                $getPenerimaanTrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where('nobukti', $fetchPP->penerimaantrucking_nobukti)->first();
                app(PenerimaanTruckingHeaderController::class)->destroy($request, $getPenerimaanTrucking->id);
                $getDetailGSPP = GajiSupirPelunasanPinjaman::lockForUpdate()->where('gajisupir_nobukti',  $gajiSupir->nobukti)->where('supir_id', $gajiSupir->supir_id)->get();
                foreach ($getDetailGSPP as $key => $value) {
                    app(GajiSupirPelunasanPinjamanController::class)->destroy($request, $value->id);
                }
            }

            $fetchPS = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
                ->where('gajisupir_nobukti', $gajiSupir->nobukti)->where('supir_id', '0')->first();
            if ($fetchPS != null) {
                $getPenerimaanTrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where('nobukti', $fetchPS->penerimaantrucking_nobukti)->first();
                app(PenerimaanTruckingHeaderController::class)->destroy($request, $getPenerimaanTrucking->id);
                $getDetailGSPS = GajiSupirPelunasanPinjaman::lockForUpdate()->where('gajisupir_nobukti', $gajiSupir->nobukti)->where('supir_id', '0')->get();
                foreach ($getDetailGSPS as $key => $value) {
                    app(GajiSupirPelunasanPinjamanController::class)->destroy($request, $value->id);
                }
            }

            $fetchUangJalan = GajisUpirUangJalan::from(DB::raw("gajisupiruangjalan with (readuncommitted)"))->whereRaw("gajisupir_nobukti = '$gajiSupir->nobukti'")->first();
            if ($fetchUangJalan != null) {
                $getDetailUangJalan = GajisUpirUangJalan::lockForUpdate()->where('gajisupir_nobukti', $gajiSupir->nobukti)->get();
                foreach ($getDetailUangJalan as $key => $value) {
                    app(GajisUpirUangJalanController::class)->destroy($request, $value->id);
                }
            }

            DB::commit();

            $selected = $this->getPosition($gajiSupir, $gajiSupir->getTable(), true);
            $gajiSupir->position = $selected->position;
            $gajiSupir->id = $selected->id;
            $gajiSupir->page = ceil($gajiSupir->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $gajiSupir
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function getTrip()
    {
        $gajiSupir = new GajiSupirHeader();

        $dari = request()->dari;
        $sampai = request()->sampai;
        $supir_id = request()->supirId;
        $tglDari = date('Y-m-d', strtotime($dari));
        $tglSampai = date('Y-m-d', strtotime($sampai));

        if ($dari && $sampai) {
            $cekSP = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
                ->where('tglbukti', '>=', $tglDari)
                ->where('tglbukti', '<=', $tglSampai)
                ->where('supir_id', $supir_id)->first();

            // CEK APAKAH ADA SP UNTUK DATA TERSEBUT
            if ($cekSP) {
                $nobukti = $cekSP->nobukti;
                $cekTrip = GajiSupirDetail::from(DB::raw("gajisupirdetail with (readuncommitted)"))->where('suratpengantar_nobukti', $nobukti)->first();


                return response([
                    'errors' => false,
                    'data' => $gajiSupir->getTrip($supir_id, $tglDari, $tglSampai),
                    'attributes' => [
                        'totalRows' => $gajiSupir->totalRows,
                        'totalPages' => $gajiSupir->totalPages,
                        'totalGajiSupir' => $gajiSupir->totalGajiSupir,
                        'totalGajiKenek' => $gajiSupir->totalGajiKenek,
                        'totalKomisiSupir' => $gajiSupir->totalKomisiSupir,
                        'totalUpahRitasi' => $gajiSupir->totalUpahRitasi,
                        'totalBiayaExtra' => $gajiSupir->totalBiayaExtra,
                        'totalTolSupir' => $gajiSupir->totalTolSupir,
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

    public function getPinjSemua()
    {
        $gajiSupir = new GajiSupirHeader();
        return response([
            'data' => $gajiSupir->getPinjSemua()
        ]);
    }

    public function getPinjPribadi($supir_id)
    {
        $gajiSupir = new GajiSupirHeader();
        return response([
            'data' => $gajiSupir->getPinjPribadi($supir_id)
        ]);
    }
    public function getEditTrip($gajiId)
    {
        $gajisupir = new GajiSupirHeader();
        $aksi = request()->aksi;
        if ($aksi == 'edit') {
            $supir_id = request()->supirId;
            $dari = date('Y-m-d', strtotime(request()->dari));
            $sampai = date('Y-m-d', strtotime(request()->sampai));
            $data = $gajisupir->getAllEditTrip($gajiId, $supir_id, $dari, $sampai);
        } else {
            $data = $gajisupir->getEditTrip($gajiId);
        }

        return response([
            'data' => $data,
            'attributes' => [
                'totalRows' => $gajisupir->totalRows,
                'totalPages' => $gajisupir->totalPages,
                'totalGajiSupir' => $gajisupir->totalGajiSupir,
                'totalGajiKenek' => $gajisupir->totalGajiKenek,
                'totalKomisiSupir' => $gajisupir->totalKomisiSupir,
                'totalUpahRitasi' => $gajisupir->totalUpahRitasi,
                'totalBiayaExtra' => $gajisupir->totalBiayaExtra,
                'totalTolSupir' => $gajisupir->totalTolSupir,
            ]
        ]);
    }

    public function getUangJalan()
    {
        $tglbukti = date('Y-m-d', strtotime(request()->tglbukti));
        $supir_id = request()->supir_id;
        $dari = date('Y-m-d', strtotime(request()->dari));
        $sampai = date('Y-m-d', strtotime(request()->sampai));

        $cekRic = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->where('tglbukti', $tglbukti)->where('supir_id', $supir_id)->first();

        if ($cekRic == null) {
            $gajisupir = new GajiSupirHeader();
            $uangjalan = $gajisupir->getUangJalan($supir_id, $dari, $sampai);
            return response([
                'data' => $uangjalan
            ]);
        }
    }

    public function noEdit()
    {
        $query = Error::from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'RICX')
            ->first();
        return response([
            'message' => "$query->keterangan",
        ]);
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $gajisupir = GajiSupirHeader::lockForUpdate()->findOrFail($id);
            $statusSudahCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($gajisupir->statuscetak != $statusSudahCetak->id) {
                $gajisupir->statuscetak = $statusSudahCetak->id;
                $gajisupir->tglbukacetak = date('Y-m-d H:i:s');
                $gajisupir->userbukacetak = auth('api')->user()->name;
                $gajisupir->jumlahcetak = $gajisupir->jumlahcetak + 1;

                if ($gajisupir->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($gajisupir->getTable()),
                        'postingdari' => 'PRINT GAJI SUPIR HEADER',
                        'idtrans' => $gajisupir->id,
                        'nobuktitrans' => $gajisupir->nobukti,
                        'aksi' => 'PRINT',
                        'datajson' => $gajisupir->toArray(),
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
        $gajisupir = GajiSupirHeader::find($id);
        $statusdatacetak = $gajisupir->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($statusdatacetak == $statusCetak->id) {
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
                'errors' => 'belum cetak',
                'kodestatus' => '0',
                'kodenobukti' => '1'
            ];

            return response($data);
        }
    }
    public function cekValidasiAksi($id)
    {
        $gajisupir = new GajiSupirHeader();
        $nobukti = GajiSupirHeader::from(DB::raw("gajisupirheader"))->where('id', $id)->first();
        $cekdata = $gajisupir->cekvalidasiaksi($nobukti->nobukti);
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

    public function getEditPinjSemua($id, $aksi)
    {
        $data = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->where('id', $id)->first();
        $pinjamanSemua = new GajiSupirPelunasanPinjaman();

        if ($aksi == 'edit') {
            $data = $pinjamanSemua->getPinjamanSemua($data->nobukti);
        } else {
            $data = $pinjamanSemua->getDeletePinjSemua($data->nobukti);
        }
        return response([
            'data' => $data
        ]);
    }

    public function getEditPinjPribadi($id, $supirId, $aksi)
    {
        $data = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->where('id', $id)->first();
        $pinjamanPribadi = new GajiSupirPelunasanPinjaman();

        if ($aksi == 'edit') {
            $data = $pinjamanPribadi->getPinjamanPribadi($data->nobukti, $supirId);
        } else {
            $data = $pinjamanPribadi->getDeletePinjPribadi($data->nobukti, $supirId);
        }
        return response([
            'data' => $data
        ]);
    }

    public function getAbsensi()
    {
        $gajiSupir = new GajiSupirHeader();

        $supir_id = request()->supirId;
        $tglDari = date('Y-m-d', strtotime(request()->dari));
        $tglSampai = date('Y-m-d', strtotime(request()->sampai));

        if (request()->dari && request()->sampai && request()->supir_id) {

            $data = $gajiSupir->getAbsensi($supir_id, $tglDari, $tglSampai);
            if ($data != null) {
                return response([
                    'errors' => false,
                    'data' => $data,
                    'attributes' => [
                        'totalRows' => $gajiSupir->totalRows,
                        'totalPages' => $gajiSupir->totalPages,
                        'uangjalan' => $gajiSupir->totalUangJalan,
                    ]
                ]);
            } else {
                return response([
                    'errors' => false,
                    'data' => [],
                    'attributes' => [
                        'totalRows' => 0,
                        'totalPages' => 0,
                        'uangjalan' => 0,
                    ]
                ]);
            }
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

    public function getEditAbsensi($gajiId)
    {
        $gajisupir = new GajiSupirHeader();
        $aksi = request()->aksi;
        if ($aksi == 'edit') {
            $supir_id = request()->supirId;
            $dari = date('Y-m-d', strtotime(request()->dari));
            $sampai = date('Y-m-d', strtotime(request()->sampai));
            $data = $gajisupir->getAllEditAbsensi($gajiId, $supir_id, $dari, $sampai);
        } else {
            $data = $gajisupir->getEditAbsensi($gajiId);
        }

        return response([
            'data' => $data,
            'attributes' => [
                'totalRows' => $gajisupir->totalRows,
                'totalPages' => $gajisupir->totalPages,
                'uangjalan' => $gajisupir->totalUangJalan,
            ]
        ]);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('gajisupirheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
