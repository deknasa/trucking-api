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
use App\Models\NotaKreditHeader;
use App\Models\Parameter;
use App\Models\Pelanggan;
use App\Models\PenerimaanDetail;
use App\Models\PenerimaanGiroHeader;
use App\Models\PenerimaanHeader;
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

    /**
     * @ClassName
     */
    public function store(StorePelunasanPiutangHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            if ($request->piutang_id != '') {

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
                foreach ($request->potonganppd as $value) {
                    if ($value != '0') {
                        $notakredit = true;
                        break;
                    }
                }
                
                $notadebet = false;
                foreach ($request->nominallebihbayarppd as $value) {
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
                    $idpiutang = $request->piutang_id[$i];
                    $piutang = PiutangHeader::where('id', $idpiutang)->first();

                    if ($request->bayarppd[$i] > $piutang->nominal) {

                        $query =  Error::from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'NBP')
                            ->first();
                        return response([
                            'errors' => [
                                "bayarppd.$i" => "$query->keterangan"
                            ],
                            'message' => "$query->keterangan",
                        ], 422);
                    }


                    //get coa nominal lebih bayar                
                    if ($request->nominallebihbayarppd[$i] > 0) {
                        $getNominalLebih = AkunPusat::where('id', '138')->first();
                    }

                    $datadetail = [
                        'pelunasanpiutang_id' => $pelunasanpiutangheader->id,
                        'nobukti' => $pelunasanpiutangheader->nobukti,
                        'nominal' => $request->bayarppd[$i],
                        'piutang_nobukti' => $piutang->nobukti,
                        'keterangan' => $request->keterangandetailppd[$i] ?? '',
                        'potongan' => $request->potonganppd[$i] ?? '',
                        'coapotongan' => $request->coapotonganppd[$i] ?? '',
                        'invoice_nobukti' => $piutang->invoice_nobukti ?? '',
                        'keteranganpotongan' => $request->keteranganpotonganppd[$i] ?? '',
                        'nominallebihbayar' => $request->nominallebihbayarppd[$i] ?? '',
                        'coalebihbayar' => $getNominalLebih->coa ?? '',
                        'modifiedby' => $pelunasanpiutangheader->modifiedby,
                        'nominalpiutang' => $piutang->nominal,
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

                    if($request->potonganppd[$i] > 0){
                        $detailNotaKredit[] = $datadetail;
                    }
                    if($request->nominallebihbayarppd[$i] > 0){
                        $detailNotaDebet[] = $datadetail;
                    }
                    
                    $detaillog[] = $datadetails['detail']->toArray();
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
                        'nobukti' => $nobuktiPenerimaan,
                        'tglbukti' => $request->tglbukti,
                        'pelanggan_id' => 0,
                        'agen_id' => $request->agen_id,
                        'postingdari' => 'PELUNASAN PIUTANG',
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
                        'coadebet' => $querysubgrppenerimaan->coa

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
                        'postingdari' => 'PELUNASAN PIUTANG',
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
                        'postingdari' => 'PELUNASAN PIUTANG',
                        'tgllunas' => $request->tglbukti,
                        'agen_id' => $request->agen_id,
                        'statusformat' => $formatNota->id,
                        'modifiedby' => auth('api')->user()->name,
                        'datadetail' => $detailNotaKredit

                    ];

                    $notaKredit = new StoreNotaKreditHeaderRequest($notaKreditHeader);
                    app(NotaKreditHeaderController::class)->store($notaKredit);
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
                        'postingdari' => 'PELUNASAN PIUTANG',
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

            $pelunasanpiutangheader->agen_id = $request->agen_id;

            if ($pelunasanpiutangheader->save()) {
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

                PelunasanPiutangDetail::where('pelunasanpiutang_id', $pelunasanpiutangheader->id)->delete();

                /* Store detail */


                $detaillog = [];
                for ($i = 0; $i < count($request->piutang_id); $i++) {
                    $idpiutang = $request->piutang_id[$i];
                    $piutang = PiutangHeader::where('id', $idpiutang)->first();

                    if ($request->bayarppd[$i] > $piutang->nominal) {
                        $query = Error::from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'NBP')
                            ->first();
                        return response([
                            'errors' => [
                                "bayarppd.$i" => "$query->keterangan"
                            ],
                            'message' => "$query->keterangan",
                        ], 422);
                    }

                    //get coa nominal lebih bayar
                    if ($request->nominallebihbayarppd[$i] > 0) {
                        $getNominalLebih = AkunPusat::where('id', '138')->first();
                    }

                    $datadetail = [
                        'pelunasanpiutang_id' => $pelunasanpiutangheader->id,
                        'nobukti' => $pelunasanpiutangheader->nobukti,
                        'agen_id' => $request->agen_id,
                        'nominal' => $request->bayarppd[$i],
                        'piutang_nobukti' => $piutang->nobukti,
                        'cicilan' => '',
                        'tglcair' => $piutang->tglbukti,
                        'keterangan' => $request->keterangandetailppd[$i] ?? '',
                        'tgljt' => $piutang->tglbukti,
                        'potongan' => $request->potonganppd[$i] ?? '',
                        'coapotongan' => $request->coapotonganppd[$i]  ?? '',
                        'invoice_nobukti' => $piutang->invoice_nobukti,
                        'keteranganpotongan' => $request->keteranganpotonganppd[$i] ?? '',
                        'nominallebihbayar' => $request->nominallebihbayarppd[$i] ?? '',
                        'coalebihbayar' => $getNominalLebih->coa ?? '',
                        'modifiedby' => $pelunasanpiutangheader->modifiedby,
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
                    $detaillog[] = $datadetails['detail']->toArray();
                }

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

                if ($request->penerimaan_nobukti != '-') {
                    $get = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))
                        ->select('penerimaanheader.id','penerimaanheader.tglbukti', 'penerimaanheader.bank_id', 'penerimaandetail.coadebet')
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
                        'postingdari' => 'EDIT PELUNASAN PIUTANG'

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
            }

            
            if ($request->notakredit_nobukti != '-') {
                    
                $get = NotaKreditHeader::from(DB::raw("notakreditheader with (readuncommitted)"))
                ->select('id')
                ->where('nobukti', $request->notakredit_nobukti)->first();
                $notaKreditHeader = [
                    'isUpdate' => 1,
                    'agen_id' => $request->agen_id,
                    'postingdari' => 'EDIT PELUNASAN PIUTANG',
                    'datadetail' => $detaillog,
                    'nowarkat' => $pelunasanpiutangheader->nowarkat,
                    'bank_id' => $pelunasanpiutangheader->bank_id

                ];

                $newNotaKredit = new NotaKreditHeader();
                $newNotaKredit = $newNotaKredit->findAll($get->id);

                $notakredit = new UpdateNotaKreditHeaderRequest($notaKreditHeader);
                app(NotaKreditHeaderController::class)->update($notakredit, $newNotaKredit);
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
