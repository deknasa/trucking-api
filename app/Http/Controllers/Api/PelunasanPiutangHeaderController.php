<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PelunasanPiutangHeader;
use App\Models\PelunasanPiutangDetail;
use App\Models\PiutangHeader;


use App\Http\Requests\StorePelunasanPiutangHeaderRequest;
use App\Http\Requests\UpdatePelunasanPiutangHeaderRequest;
use App\Http\Requests\StorePelunasanPiutangDetailRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreNotaDebetHeaderRequest;
use App\Http\Requests\StoreNotaKreditHeaderRequest;
use App\Http\Requests\StorePenerimaanGiroHeaderRequest;
use App\Http\Requests\StorePenerimaanHeaderRequest;
use App\Http\Requests\UpdateNotaDebetDetailRequest;
use App\Http\Requests\UpdateNotaDebetHeaderRequest;
use App\Http\Requests\UpdateNotaKreditHeaderRequest;
use App\Http\Requests\UpdatePenerimaanGiroHeaderRequest;
use App\Http\Requests\UpdatePenerimaanHeaderRequest;
use App\Models\LogTrail;
use App\Models\Agen;
use App\Models\AkunPusat;
use App\Models\AlatBayar;
use App\Models\Cabang;
use App\Models\Bank;
use App\Models\Error;
use App\Models\JurnalUmumHeader;
use App\Models\NotaDebetHeader;
use App\Models\NotaKreditHeader;
use App\Models\Parameter;
use App\Models\Pelanggan;
use App\Models\PenerimaanDetail;
use App\Models\PenerimaanGiroHeader;
use App\Models\PenerimaanHeader;
use App\Models\SaldoPiutang;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class PelunasanPiutangHeaderController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $pengeluarantruckingheader = new PelunasanPiutangHeader();
        return response([
            'data' => $pengeluarantruckingheader->get(),
            'attributes' => [
                'totalRows' => $pengeluarantruckingheader->totalRows,
                'totalPages' => $pengeluarantruckingheader->totalPages
            ]
        ]);
    }

    public function default()
    {
        $pelunasan = new PelunasanPiutangHeader();
        return response([
            'status' => true,
            'data' => $pelunasan->default(),
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StorePelunasanPiutangHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            if ($request->piutang_id != '') {

                for ($i = 0; $i < count($request->piutang_id); $i++) {

                    $cekSisa = PiutangHeader::from(DB::raw("piutangheader with (readuncommitted)"))->select('nominal')->where('nobukti', $request->piutang_nobukti[$i])->first();

                    if ($request->bayar[$i] > $cekSisa->nominal) {
                        if ($request->nominallebihbayar[$i] == 0) {

                            $query =  Error::from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'WI')
                                ->first();
                            return response([
                                'errors' => [
                                    "nominallebihbayar" => "sisa bayar minus. nominal lebih bayar $query->keterangan"
                                ],
                                'message' => "The given data was invalid.",
                            ], 422);
                        } else {
                            $query =  Error::from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'STM')
                                ->first();
                            return response([
                                'errors' => [
                                    "bayar" => "$query->keterangan"
                                ],
                                'message' => "The given data was invalid.",
                            ], 422);
                        }
                    }

                    $byrPotongan = $request->bayar[$i] + $request->potongan[$i];
                    if ($byrPotongan > $cekSisa->nominal) {
                        $query =  Error::from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'STM')
                            ->first();
                        return response([
                            'errors' => [
                                "bayar" => "$query->keterangan"
                            ],
                            'message' => "The given data was invalid.",
                        ], 422);
                    }
                }


                $group = 'PELUNASAN PIUTANG BUKTI';
                $subgroup = 'PELUNASAN PIUTANG BUKTI';


                $format = DB::table('parameter')
                    ->where('grp', $group)
                    ->where('subgrp', $subgroup)
                    ->first();

                $content = new Request();
                $content['group'] = $group;
                $content['subgroup'] = $subgroup;
                $content['table'] = 'pelunasanpiutangheader';
                $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));


                $alatbayarGiro = AlatBayar::from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'GIRO')->first();

                if ($request->alatbayar_id != $alatbayarGiro->id) {
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
                        ->whereRaw("bank.id = $request->bank_id")
                        ->first();
                    $tipeKas = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS KAS')->where('text', 'KAS')->first();

                    if ($querysubgrppenerimaan->tipe == $tipeKas->text) {
                        $statusKas = $tipeKas->id;
                    } else {
                        $paramKas = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS KAS')->where('text', 'BUKAN KAS')->first();
                        $statusKas = $paramKas->id;
                    }

                    $penerimaanRequest = new Request();
                    $penerimaanRequest['group'] = $querysubgrppenerimaan->grp;
                    $penerimaanRequest['subgroup'] = $querysubgrppenerimaan->subgrp;
                    $penerimaanRequest['table'] = 'penerimaanheader';
                    $penerimaanRequest['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                    $nobuktiPenerimaan = app(Controller::class)->getRunningNumber($penerimaanRequest)->original['data'];
                } else {

                    if ($request->nowarkat == '') {
                        $request->validate([
                            'nowarkat' => 'required',
                        ]);
                    }

                    $group = 'PENERIMAAN GIRO BUKTI';
                    $subgroup = 'PENERIMAAN GIRO BUKTI';

                    $formatGiro = DB::table('parameter')
                        ->where('grp', $group)
                        ->where('subgrp', $subgroup)
                        ->first();

                    $penerimaanGiroRequest = new Request();
                    $penerimaanGiroRequest['group'] = $group;
                    $penerimaanGiroRequest['subgroup'] = $subgroup;
                    $penerimaanGiroRequest['table'] = 'penerimaangiroheader';
                    $penerimaanGiroRequest['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                    $nobuktiPenerimaanGiro = app(Controller::class)->getRunningNumber($penerimaanGiroRequest)->original['data'];
                }

                $notakredit = false;
                foreach ($request->potongan as $value) {
                    if ($value != '0') {
                        $notakredit = true;
                        break;
                    }
                }

                $notadebet = false;
                foreach ($request->nominallebihbayar as $value) {
                    if ($value != '0') {
                        $notadebet = true;
                        break;
                    }
                }

                $pelunasanpiutangheader = new PelunasanPiutangHeader();

                $pelunasanpiutangheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
                $pelunasanpiutangheader->bank_id = $request->bank_id;
                $pelunasanpiutangheader->alatbayar_id = $request->alatbayar_id;
                $pelunasanpiutangheader->penerimaan_nobukti = $nobuktiPenerimaan ?? '-';
                $pelunasanpiutangheader->penerimaangiro_nobukti = $nobuktiPenerimaanGiro ?? '-';
                $pelunasanpiutangheader->notakredit_nobukti = '-';
                $pelunasanpiutangheader->notadebet_nobukti = '-';
                $pelunasanpiutangheader->agen_id = $request->agen_id;
                $pelunasanpiutangheader->nowarkat = $request->nowarkat ?? '-';
                $pelunasanpiutangheader->statusformat = $format->id;
                $pelunasanpiutangheader->modifiedby = auth('api')->user()->name;

                $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
                $pelunasanpiutangheader->nobukti = $nobukti;


                $pelunasanpiutangheader->save();

                $logTrail = [
                    'namatabel' => strtoupper($pelunasanpiutangheader->getTable()),
                    'postingdari' => 'ENTRY PELUNASAN PIUTANG HEADER',
                    'idtrans' => $pelunasanpiutangheader->id,
                    'nobuktitrans' => $pelunasanpiutangheader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $pelunasanpiutangheader->toArray(),
                    'modifiedby' => $pelunasanpiutangheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                /* Store detail */

                $detaillog = [];
                $detailNotaKredit = [];
                $detailNotaDebet = [];
                for ($i = 0; $i < count($request->piutang_id); $i++) {
                    $piutang = PiutangHeader::where('nobukti', $request->piutang_nobukti[$i])->first();

                    if ($request->bayar[$i] > $piutang->nominal) {

                        $query =  Error::from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'NBP')
                            ->first();
                        return response([
                            'errors' => [
                                "bayar.$i" => "$query->keterangan"
                            ],
                            'message' => "$query->keterangan",
                        ], 422);
                    }


                    //get coa nominal lebih bayar                
                    if ($request->nominallebihbayar[$i] > 0) {
                        $getNominalLebih = AkunPusat::where('id', '138')->first();
                    }

                    $datadetail = [
                        'pelunasanpiutang_id' => $pelunasanpiutangheader->id,
                        'nobukti' => $pelunasanpiutangheader->nobukti,
                        'nominal' => $request->bayar[$i],
                        'piutang_nobukti' => $piutang->nobukti,
                        'keterangan' => $request->keterangan[$i] ?? '',
                        'potongan' => $request->potongan[$i] ?? '',
                        'coapotongan' => $request->coapotongan[$i] ?? '',
                        'invoice_nobukti' => $piutang->invoice_nobukti ?? '',
                        'keteranganpotongan' => $request->keteranganpotongan[$i] ?? '',
                        'nominallebihbayar' => $request->nominallebihbayar[$i] ?? '',
                        'coalebihbayar' => $getNominalLebih->coa ?? '',
                        'modifiedby' => $pelunasanpiutangheader->modifiedby,
                        'nominalpiutang' => $piutang->nominal,
                        'coakredit' => $piutang->coadebet
                    ];

                    //STORE 
                    $data = new StorePelunasanPiutangDetailRequest($datadetail);

                    $datadetails = app(PelunasanPiutangDetailController::class)->store($data);
                    // dd('tes');


                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }

                    if ($request->potongan[$i] > 0) {
                        $detailNotaKredit[] = $datadetail;
                    }
                    if ($request->nominallebihbayar[$i] > 0) {
                        $detailNotaDebet[] = $datadetail;
                    }

                    $detaillog[] = $datadetail;
                }

                $datalogtrail = [
                    'namatabel' => strtoupper($tabeldetail),
                    'postingdari' => 'ENTRY PELUNASAN PIUTANG DETAIL',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $pelunasanpiutangheader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => $request->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);

                if ($request->alatbayar_id != $alatbayarGiro->id) {

                    $penerimaanHeader = [
                        'tanpaprosesnobukti' => 1,
                        'tanpagetposition' => 1,
                        'nobukti' => $nobuktiPenerimaan,
                        'tglbukti' => $request->tglbukti,
                        'pelanggan_id' => 0,
                        'agen_id' => $request->agen_id,
                        'postingdari' => 'ENTRY PELUNASAN PIUTANG',
                        'diterimadari' => $request->agen,
                        'tgllunas' => $request->tglbukti,
                        'cabang_id' => 0,
                        'statuskas' => $statusKas,
                        'bank_id' => $request->bank_id,
                        'noresi' => '',
                        'statusformat' => $querysubgrppenerimaan->formatpenerimaan,
                        'modifiedby' => auth('api')->user()->name,
                        'datadetail' => $detaillog,
                        'nowarkat' => $pelunasanpiutangheader->nowarkat,
                        'coadebet' => $querysubgrppenerimaan->coa,
                        'pelunasanpiutang_nobukti' => $pelunasanpiutangheader->nobukti

                    ];
                    $penerimaan = new StorePenerimaanHeaderRequest($penerimaanHeader);
                    app(PenerimaanHeaderController::class)->store($penerimaan);
                } else {
                    $penerimaanGiroHeader = [
                        'tanpaprosesnobukti' => 1,
                        'nobukti' => $nobuktiPenerimaanGiro,
                        'tglbukti' => $request->tglbukti,
                        'pelanggan_id' => 0,
                        'agen_id' => $request->agen_id,
                        'postingdari' => 'ENTRY PELUNASAN PIUTANG',
                        'diterimadari' => $request->agen,
                        'tgllunas' => $request->tglbukti,
                        'cabang_id' => 0,
                        'statusformat' => $formatGiro->id,
                        'modifiedby' => auth('api')->user()->name,
                        'datadetail' => $detaillog,
                        'nowarkat' => $pelunasanpiutangheader->nowarkat,
                        'bank_id' => $request->bank_id

                    ];

                    $penerimaanGiro = new StorePenerimaanGiroHeaderRequest($penerimaanGiroHeader);
                    app(PenerimaanGiroHeaderController::class)->store($penerimaanGiro);
                }


                if ($notakredit) {
                    $group = 'NOTA KREDIT BUKTI';
                    $subgroup = 'NOTA KREDIT BUKTI';

                    $formatNota = DB::table('parameter')
                        ->where('grp', $group)
                        ->where('subgrp', $subgroup)
                        ->first();
                    $notaKreditRequest = new Request();
                    $notaKreditRequest['group'] = $group;
                    $notaKreditRequest['subgroup'] = $subgroup;
                    $notaKreditRequest['table'] = 'notakreditheader';
                    $notaKreditRequest['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
                    $nobuktiNotaKredit = app(Controller::class)->getRunningNumber($notaKreditRequest)->original['data'];

                    $pelunasanpiutangheader->notakredit_nobukti = $nobuktiNotaKredit;
                    $pelunasanpiutangheader->save();

                    $notaKreditHeader = [
                        'nobukti' => $nobuktiNotaKredit,
                        'tglbukti' => $request->tglbukti,
                        'pelunasanpiutang_nobukti' => $pelunasanpiutangheader->nobukti,
                        'postingdari' => 'ENTRY PELUNASAN PIUTANG',
                        'tgllunas' => $request->tglbukti,
                        'agen_id' => $request->agen_id,
                        'statusformat' => $formatNota->id,
                        'modifiedby' => auth('api')->user()->name,
                        'datadetail' => $detailNotaKredit

                    ];

                    $notaKredit = new StoreNotaKreditHeaderRequest($notaKreditHeader);
                    $tes = app(NotaKreditHeaderController::class)->store($notaKredit);
                }

                if ($notadebet) {
                    $group = 'NOTA DEBET BUKTI';
                    $subgroup = 'NOTA DEBET BUKTI';

                    $formatNota = DB::table('parameter')
                        ->where('grp', $group)
                        ->where('subgrp', $subgroup)
                        ->first();
                    $notaDebetRequest = new Request();
                    $notaDebetRequest['group'] = $group;
                    $notaDebetRequest['subgroup'] = $subgroup;
                    $notaDebetRequest['table'] = 'notadebetheader';
                    $notaDebetRequest['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
                    $nobuktiNotaDebet = app(Controller::class)->getRunningNumber($notaDebetRequest)->original['data'];

                    $pelunasanpiutangheader->notadebet_nobukti = $nobuktiNotaDebet;
                    $pelunasanpiutangheader->save();

                    $notaDebetHeader = [
                        'nobukti' => $nobuktiNotaDebet,
                        'tglbukti' => $request->tglbukti,
                        'pelunasanpiutang_nobukti' => $pelunasanpiutangheader->nobukti,
                        'postingdari' => 'ENTRY PELUNASAN PIUTANG',
                        'tgllunas' => $request->tglbukti,
                        'agen_id' => $request->agen_id,
                        'statusformat' => $formatNota->id,
                        'modifiedby' => auth('api')->user()->name,
                        'datadetail' => $detailNotaDebet

                    ];

                    $notaDebet = new StoreNotaDebetHeaderRequest($notaDebetHeader);
                    app(NotaDebetHeaderController::class)->store($notaDebet);
                }

                $request->sortname = $request->sortname ?? 'id';
                $request->sortorder = $request->sortorder ?? 'asc';
                DB::commit();

                /* Set position and page */


                $selected = $this->getPosition($pelunasanpiutangheader, $pelunasanpiutangheader->getTable());
                $pelunasanpiutangheader->position = $selected->position;
                $pelunasanpiutangheader->page = ceil($pelunasanpiutangheader->position / ($request->limit ?? 10));


                return response([
                    'status' => true,
                    'message' => 'Berhasil disimpan',
                    'data' => $pelunasanpiutangheader
                ], 201);
            } else {
                $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'WP')
                    ->first();
                return response([
                    'errors' => [
                        'piutang' => "PIUTANG $query->keterangan"
                    ],
                    'message' => "PIUTANG $query->keterangan",
                ], 422);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show($id)
    {
        // $data = PelunasanPiutangHeader::with(
        //     'pelunasanpiutangdetail',
        // )->find($id);

        $data = PelunasanPiutangHeader::findAll($id);

        return response([
            'status' => true,
            'data' => $data
        ]);
    }

    /**
     * @ClassName
     */
    public function update(UpdatePelunasanPiutangHeaderRequest $request, PelunasanPiutangHeader $pelunasanpiutangheader)
    {
        DB::beginTransaction();

        try {

            for ($i = 0; $i < count($request->piutang_id); $i++) {

                $cekSisa = PiutangHeader::from(DB::raw("piutangheader with (readuncommitted)"))->select('nominal')->where('nobukti', $request->piutang_nobukti[$i])->first();

                if ($request->bayar[$i] > $cekSisa->nominal) {
                    if ($request->nominallebihbayar[$i] == 0) {

                        $query =  Error::from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'WI')
                            ->first();
                        return response([
                            'errors' => [
                                "nominallebihbayar.$i" =>
                                [$i => "nominal lebih bayar $query->keterangan"]
                            ],
                            'message' => "The given data was invalid.",
                        ], 422);
                    } else {
                        $query =  Error::from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'STM')
                            ->first();
                        return response([
                            'errors' => [
                                "bayar.$i" =>
                                [$i => "$query->keterangan"]
                            ],
                            'message' => "The given data was invalid.",
                        ], 422);
                    }
                }
                $byrPotongan = $request->bayar[$i] + $request->potongan[$i];
                if ($byrPotongan > $cekSisa->nominal) {
                    $query =  Error::from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'STM')
                        ->first();
                    return response([
                        'errors' => [
                            "bayar.$i" =>
                            [$i => "$query->keterangan"]
                        ],
                        'message' => "The given data was invalid. ok",
                    ], 422);
                }

                if($request->potongan[$i] > 0){
                    $query =  Error::from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'WI')
                            ->first();
                    if($request->coapotongan[$i] == '') {
                        return response([
                            'errors' => [
                                "coapotongan.$i" =>
                                [$i => "coa potongan $query->keterangan"]
                            ],
                            'message' => "The given data was invalid.",
                        ], 422);
                    }
                    if($request->keteranganpotongan[$i] == '') {
                        return response([
                            'errors' => [
                                "keteranganpotongan.$i" =>
                                [$i => "keterangan potongan $query->keterangan"]
                            ],
                            'message' => "The given data was invalid.",
                        ], 422);
                    }
                }
            }
            $pelunasanpiutangheader->agen_id = $request->agen_id;

            $pelunasanpiutangheader->save();
            PelunasanPiutangDetail::where('pelunasanpiutang_id', $pelunasanpiutangheader->id)->delete();

            /* Store detail */


            $detaillog = [];
            $detailNotaKredit = [];
            $detailNotaDebet = [];
            for ($i = 0; $i < count($request->piutang_id); $i++) {
                $piutang = PiutangHeader::where('nobukti', $request->piutang_nobukti[$i])->first();


                if ($request->bayar[$i] > $piutang->nominal) {
                    $query = Error::from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'NBP')
                        ->first();
                    return response([
                        'errors' => [
                            "bayar.$i" =>
                            [$i => "$query->keterangan"]
                        ],
                        'message' => "$query->keterangan",
                    ], 422);
                }

                //get coa nominal lebih bayar
                if ($request->nominallebihbayar[$i] > 0) {
                    $getNominalLebih = AkunPusat::where('id', '138')->first();
                }

                $datadetail = [
                    'pelunasanpiutang_id' => $pelunasanpiutangheader->id,
                    'nobukti' => $pelunasanpiutangheader->nobukti,
                    'nominal' => $request->bayar[$i],
                    'piutang_nobukti' => $piutang->nobukti,
                    'keterangan' => $request->keterangan[$i] ?? '',
                    'potongan' => $request->potongan[$i] ?? '',
                    'coapotongan' => $request->coapotongan[$i]  ?? '',
                    'invoice_nobukti' => $piutang->invoice_nobukti,
                    'keteranganpotongan' => $request->keteranganpotongan[$i] ?? '',
                    'nominallebihbayar' => $request->nominallebihbayar[$i] ?? '',
                    'coalebihbayar' => $getNominalLebih->coa ?? '',
                    'modifiedby' => $pelunasanpiutangheader->modifiedby,
                    'nominalpiutang' => $piutang->nominal,
                    'coakredit' => $piutang->coadebet
                ];

                //STORE

                $data = new StorePelunasanPiutangDetailRequest($datadetail);
                $datadetails = app(PelunasanPiutangDetailController::class)->store($data);
                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }

                if ($request->potongan[$i] > 0) {
                    $detailNotaKredit[] = $datadetail;
                }
                if ($request->nominallebihbayar[$i] > 0) {
                    $detailNotaDebet[] = $datadetail;
                }

                $detaillog[] = $datadetail;
            }


            if ($request->penerimaan_nobukti != '-') {
                $get = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))
                    ->select('penerimaanheader.id', 'penerimaanheader.tglbukti', 'penerimaanheader.bank_id', 'penerimaandetail.coadebet')
                    ->leftJoin(DB::raw('penerimaandetail with (readuncommitted)'), 'penerimaanheader.nobukti', 'penerimaandetail.nobukti')
                    ->where('penerimaanheader.nobukti', $request->penerimaan_nobukti)->first();
                $penerimaanHeader = [
                    'isUpdate' => 1,
                    'datadetail' => $detaillog,
                    'nowarkat' => $pelunasanpiutangheader->nowarkat,
                    'tglbukti' => $get->tglbukti,
                    'nobukti' => $pelunasanpiutangheader->penerimaan_nobukti,
                    'coadebet' => $get->coadebet,
                    'bank_id' => $get->bank_id,
                    'agen_id' => $pelunasanpiutangheader->agen_id,
                    'id' => $get->id,
                    'postingdari' => 'EDIT PELUNASAN PIUTANG',
                    'pelunasanpiutang_nobukti' => $pelunasanpiutangheader->nobukti

                ];
                $newPenerimaan = new PenerimaanHeader();
                $newPenerimaan = $newPenerimaan->findAll($get->id);
                $penerimaan = new UpdatePenerimaanHeaderRequest($penerimaanHeader);
                app(PenerimaanHeaderController::class)->update($penerimaan, $newPenerimaan);
            }

            if ($request->penerimaangiro_nobukti != '-') {

                $get = PenerimaanGiroHeader::from(DB::raw("penerimaangiroheader with (readuncommitted)"))
                    ->select('id')
                    ->where('nobukti', $request->penerimaangiro_nobukti)->first();
                $penerimaanGiroHeader = [
                    'isUpdate' => 1,
                    'agen_id' => $request->agen_id,
                    'postingdari' => 'EDIT PELUNASAN PIUTANG',
                    'datadetail' => $detaillog,
                    'nowarkat' => $pelunasanpiutangheader->nowarkat,
                    'bank_id' => $pelunasanpiutangheader->bank_id

                ];

                $newPenerimaanGiro = new PenerimaanGiroHeader();
                $newPenerimaanGiro = $newPenerimaanGiro->findAll($get->id);

                $penerimaanGiro = new UpdatePenerimaanGiroHeaderRequest($penerimaanGiroHeader);
                app(PenerimaanGiroHeaderController::class)->update($penerimaanGiro, $newPenerimaanGiro);
            }

            $notakredit = false;
            foreach ($request->potongan as $value) {
                if ($value != '0') {
                    $notakredit = true;
                    break;
                }
            }

            $notadebet = false;
            foreach ($request->nominallebihbayar as $value) {
                if ($value != '0') {
                    $notadebet = true;
                    break;
                }
            }


            if ($request->notakredit_nobukti != '-') {

                if ($notakredit) {

                    $get = NotaKreditHeader::from(DB::raw("notakreditheader with (readuncommitted)"))
                        ->select('id')
                        ->where('nobukti', $request->notakredit_nobukti)->first();
                    $notaKreditHeader = [
                        'isUpdate' => 1,
                        'agen_id' => $request->agen_id,
                        'postingdari' => 'EDIT PELUNASAN PIUTANG',
                        'datadetail' => $detailNotaKredit

                    ];

                    $newNotaKredit = new NotaKreditHeader();
                    $newNotaKredit = $newNotaKredit->findAll($get->id);

                    $notakredit = new UpdateNotaKreditHeaderRequest($notaKreditHeader);
                    app(NotaKreditHeaderController::class)->update($notakredit, $newNotaKredit);
                } else {
                    $request['postingdari'] = "PELUNASAN PIUTANG";
                    $getNotaKredit = NotaKreditHeader::from(DB::raw("notakreditheader with (readuncommitted)"))->where('nobukti', $request->notakredit_nobukti)->first();
                    app(NotaKreditHeaderController::class)->destroy($request, $getNotaKredit->id);
                    $pelunasanpiutangheader->notakredit_nobukti = '-';
                    $pelunasanpiutangheader->save();
                }
            } else {
                if ($notakredit) {
                    $group = 'NOTA KREDIT BUKTI';
                    $subgroup = 'NOTA KREDIT BUKTI';

                    $formatNota = DB::table('parameter')
                        ->where('grp', $group)
                        ->where('subgrp', $subgroup)
                        ->first();
                    $notaKreditRequest = new Request();
                    $notaKreditRequest['group'] = $group;
                    $notaKreditRequest['subgroup'] = $subgroup;
                    $notaKreditRequest['table'] = 'notakreditheader';
                    $notaKreditRequest['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
                    $nobuktiNotaKredit = app(Controller::class)->getRunningNumber($notaKreditRequest)->original['data'];

                    $pelunasanpiutangheader->notakredit_nobukti = $nobuktiNotaKredit;
                    $pelunasanpiutangheader->save();

                    $notaKreditHeader = [
                        'nobukti' => $nobuktiNotaKredit,
                        'tglbukti' => $pelunasanpiutangheader->tglbukti,
                        'pelunasanpiutang_nobukti' => $pelunasanpiutangheader->nobukti,
                        'postingdari' => 'EDIT PELUNASAN PIUTANG',
                        'tgllunas' => $pelunasanpiutangheader->tglbukti,
                        'agen_id' => $request->agen_id,
                        'statusformat' => $formatNota->id,
                        'modifiedby' => auth('api')->user()->name,
                        'datadetail' => $detailNotaKredit

                    ];

                    $notaKredit = new StoreNotaKreditHeaderRequest($notaKreditHeader);
                    $tes = app(NotaKreditHeaderController::class)->store($notaKredit);
                }
            }
            if ($request->notadebet_nobukti != '-') {
                if ($notadebet) {
                    $get = NotaDebetHeader::from(DB::raw("notadebetheader with (readuncommitted)"))
                        ->select('id')
                        ->where('nobukti', $request->notadebet_nobukti)->first();
                    $notaDebetHeader = [
                        'isUpdate' => 1,
                        'agen_id' => $request->agen_id,
                        'postingdari' => 'EDIT PELUNASAN PIUTANG',
                        'datadetail' => $detailNotaDebet

                    ];

                    $newNotaDebet = new NotaDebetHeader();
                    $newNotaDebet = $newNotaDebet->findAll($get->id);

                    $notadebet = new UpdateNotaDebetHeaderRequest($notaDebetHeader);
                    app(NotaDebetHeaderController::class)->update($notadebet, $newNotaDebet);
                } else {
                    $request['postingdari'] = "PELUNASAN PIUTANG";
                    $getNotaDebet = NotaDebetHeader::from(DB::raw("notadebetheader with (readuncommitted)"))->where('nobukti', $request->notadebet_nobukti)->first();
                    app(NotaDebetHeaderController::class)->destroy($request, $getNotaDebet->id);
                    $pelunasanpiutangheader->notadebet_nobukti = '-';
                    $pelunasanpiutangheader->save();
                }
            } else {
                if ($notadebet) {
                    $group = 'NOTA DEBET BUKTI';
                    $subgroup = 'NOTA DEBET BUKTI';

                    $formatNota = DB::table('parameter')
                        ->where('grp', $group)
                        ->where('subgrp', $subgroup)
                        ->first();
                    $notaDebetRequest = new Request();
                    $notaDebetRequest['group'] = $group;
                    $notaDebetRequest['subgroup'] = $subgroup;
                    $notaDebetRequest['table'] = 'notadebetheader';
                    $notaDebetRequest['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
                    $nobuktiNotaDebet = app(Controller::class)->getRunningNumber($notaDebetRequest)->original['data'];

                    $pelunasanpiutangheader->notadebet_nobukti = $nobuktiNotaDebet;
                    $pelunasanpiutangheader->save();

                    $notaDebetHeader = [
                        'nobukti' => $nobuktiNotaDebet,
                        'tglbukti' => $pelunasanpiutangheader->tglbukti,
                        'pelunasanpiutang_nobukti' => $pelunasanpiutangheader->nobukti,
                        'postingdari' => 'EDIT PELUNASAN PIUTANG',
                        'tgllunas' => $pelunasanpiutangheader->tglbukti,
                        'agen_id' => $request->agen_id,
                        'statusformat' => $formatNota->id,
                        'modifiedby' => auth('api')->user()->name,
                        'datadetail' => $detailNotaDebet

                    ];

                    $notaDebet = new StoreNotaDebetHeaderRequest($notaDebetHeader);
                    app(NotaDebetHeaderController::class)->store($notaDebet);
                }
            }
            
            $logTrail = [
                'namatabel' => strtoupper($pelunasanpiutangheader->getTable()),
                'postingdari' => 'EDIT PELUNASAN PIUTANG HEADER',
                'idtrans' => $pelunasanpiutangheader->id,
                'nobuktitrans' => $pelunasanpiutangheader->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $pelunasanpiutangheader->toArray(),
                'modifiedby' => $pelunasanpiutangheader->modifiedby
            ];
            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            $datalogtrail = [
                'namatabel' => strtoupper($tabeldetail),
                'postingdari' => 'EDIT PELUNASAN PIUTANG DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $pelunasanpiutangheader->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $detaillog,
                'modifiedby' => $request->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);

            app(LogTrailController::class)->store($data);
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';


            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($pelunasanpiutangheader, $pelunasanpiutangheader->getTable());
            $pelunasanpiutangheader->position = $selected->position;
            $pelunasanpiutangheader->page = ceil($pelunasanpiutangheader->position / ($request->limit ?? 10));



            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pelunasanpiutangheader
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

        $getDetail = PelunasanPiutangDetail::where('pelunasanpiutang_id', $id)->get();

        $request['postingdari'] = "DELETE PELUNASAN PIUTANG";
        $pelunasanpiutangheader = new PelunasanPiutangHeader();
        $pelunasanpiutangheader = $pelunasanpiutangheader->lockAndDestroy($id);

        if ($pelunasanpiutangheader) {
            $logTrail = [
                'namatabel' => strtoupper($pelunasanpiutangheader->getTable()),
                'postingdari' => 'DELETE PELUNASAN PIUTANG HEADER',
                'idtrans' => $pelunasanpiutangheader->id,
                'nobuktitrans' => $pelunasanpiutangheader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $pelunasanpiutangheader->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE PELUNASAN PIUTANG DETAIL

            $logTrailPiutangDetail = [
                'namatabel' => 'PELUNASANPIUTANGDETAIL',
                'postingdari' => 'DELETE PELUNASAN PIUTANG DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $pelunasanpiutangheader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailPiutangDetail = new StoreLogTrailRequest($logTrailPiutangDetail);
            app(LogTrailController::class)->store($validatedLogTrailPiutangDetail);

            if ($pelunasanpiutangheader->penerimaan_nobukti != '-') {
                $getPenerimaan = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))->where('nobukti', $pelunasanpiutangheader->penerimaan_nobukti)->first();
                app(PenerimaanHeaderController::class)->destroy($request, $getPenerimaan->id);
            }
            if ($pelunasanpiutangheader->penerimaangiro_nobukti != '-') {
                $getGiro = PenerimaanGiroHeader::from(DB::raw("penerimaangiroheader with (readuncommitted)"))->where('nobukti', $pelunasanpiutangheader->penerimaangiro_nobukti)->first();
                app(PenerimaanGiroHeaderController::class)->destroy($request, $getGiro->id);
            }

            if ($pelunasanpiutangheader->notakredit_nobukti != '-') {
                $getNotaKredit = NotaKreditHeader::from(DB::raw("notakreditheader with (readuncommitted)"))->where('nobukti', $pelunasanpiutangheader->notakredit_nobukti)->first();
                app(NotaKreditHeaderController::class)->destroy($request, $getNotaKredit->id);
            }

            if ($pelunasanpiutangheader->notadebet_nobukti != '-') {
                $getNotaDebet = NotaDebetHeader::from(DB::raw("notadebetheader with (readuncommitted)"))->where('nobukti', $pelunasanpiutangheader->notadebet_nobukti)->first();
                app(NotaDebetHeaderController::class)->destroy($request, $getNotaDebet->id);
            }

            DB::commit();

            $selected = $this->getPosition($pelunasanpiutangheader, $pelunasanpiutangheader->getTable(), true);
            $pelunasanpiutangheader->position = $selected->position;
            $pelunasanpiutangheader->id = $selected->id;
            $pelunasanpiutangheader->page = ceil($pelunasanpiutangheader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $pelunasanpiutangheader
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function getpiutang($id)
    {
        $piutang = new PiutangHeader();
        return response([
            'data' => $piutang->getPiutang($id),
            'id' => $id,
            'attributes' => [
                'totalRows' => $piutang->totalRows,
                'totalPages' => $piutang->totalPages
            ]
        ]);
    }


    public function getPelunasanPiutang($id, $agenId)
    {
        $pelunasanpiutang = new PelunasanPiutangHeader();
        return response([
            'data' => $pelunasanpiutang->getPelunasanPiutang($id, $agenId),
            'attributes' => [
                'totalRows' => $pelunasanpiutang->totalRows,
                'totalPages' => $pelunasanpiutang->totalPages
            ]
        ]);
    }

    public function getDeletePelunasanPiutang($id, $agenId)
    {
        $pelunasanpiutang = new PelunasanPiutangHeader();
        return response([
            'data' => $pelunasanpiutang->getDeletePelunasanPiutang($id, $agenId),
            'attributes' => [
                'totalRows' => $pelunasanpiutang->totalRows,
                'totalPages' => $pelunasanpiutang->totalPages
            ]
        ]);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pelunasanpiutangheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
