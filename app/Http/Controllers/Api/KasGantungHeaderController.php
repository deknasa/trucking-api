<?php

namespace App\Http\Controllers\Api;

use App\Helpers\App;
use App\Http\Controllers\Controller;
use App\Models\KasGantungDetail;
use App\Models\KasGantungHeader;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\PengeluaranDetail;
use App\Models\PengeluaranHeader;
use App\Models\Bank;
use App\Models\Penerima;
use App\Http\Requests\StoreKasGantungHeaderRequest;
use App\Http\Requests\UpdateKasGantungHeaderRequest;
use App\Http\Requests\StoreKasGantungDetailRequest;
use App\Http\Requests\JurnalUmumHeaderRequest;
use App\Models\LogTrail;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\StorePengeluaranDetailRequest;
use Illuminate\Database\QueryException;

class KasGantungHeaderController extends Controller
{
    /**
     * @ClassName 
     */
    public function index(Request $request)
    {
        $kasgantungHeader = new KasGantungHeader();

        return response([
            'data' => $kasgantungHeader->get(),
            'attributes' => [
                'totalRows' => $kasgantungHeader->totalRows,
                'totalPages' => $kasgantungHeader->totalPages
            ]
        ]);
    }

    public function create()
    {
    }
    /**
     * @ClassName 
     */
    public function store(StoreKasGantungHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            $tanpaprosesnobukti = $request->tanpaprosesnobukti ?? 0;

            if ($tanpaprosesnobukti == 0) {
                /* Store header */
                $bank = Bank::find($request->bank_id);

                $group = 'KAS GANTUNG';
                $subgroup = 'NOMOR KAS GANTUNG';
                $format = DB::table('parameter')
                    ->where('grp', $group)
                    ->where('subgrp', $subgroup)
                    ->first();

                $content = new Request();
                $content['group'] = $group;
                $content['subgroup'] = $subgroup;
                $content['table'] = 'kasgantungheader';
                $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
            }
            $kasgantungHeader = new KasGantungHeader();

            if ($tanpaprosesnobukti == 1) {
                $kasgantungHeader->nobukti = $request->nobukti;
            }

            $kasgantungHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti)) ?? '1900/1/1';
            $kasgantungHeader->penerima_id = $request->penerima_id ?? '';
            $kasgantungHeader->keterangan = $request->keterangan ?? '';
            $kasgantungHeader->bank_id = $request->bank_id ?? 0;
            $kasgantungHeader->pengeluaran_nobukti = $request->pengeluaran_nobukti ?? '';
            $kasgantungHeader->coakaskeluar = $bank->coa ?? '';
            $kasgantungHeader->postingdari = $request->postingdari ?? 'ENTRY KAS GANTUNG';
            $kasgantungHeader->tglkaskeluar = date('Y-m-d', strtotime($request->tglkaskeluar)) ?? '1900/1/1';
            $kasgantungHeader->modifiedby = auth('api')->user()->name;
            $kasgantungHeader->statusformat = $format->id ?? $request->statusformat;
            TOP:
            if ($tanpaprosesnobukti == 0) {
                $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
                $kasgantungHeader->nobukti = $nobukti;
            }
            $kasgantungHeader->save();
            if ($tanpaprosesnobukti == 1) {
                DB::commit();
            }

            $logTrail = [
                'namatabel' => strtoupper($kasgantungHeader->getTable()),
                'postingdari' => 'ENTRY KAS GANTUNG',
                'idtrans' => $kasgantungHeader->id,
                'nobuktitrans' => $kasgantungHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $kasgantungHeader->toArray(),
                'modifiedby' => $kasgantungHeader->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);

            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            //UNTUK INSERT KE PENGELUARAN
            // if($tanpaprosesnobukti == 1) {
            //     $group = 'PENGELUARAN KAS';
            //     $subgroup = 'NOMOR  PENGELUARAN KAS';
            //     $format = DB::table('parameter')
            //     ->where('grp', $group )
            //     ->where('subgrp', $subgroup)
            //     ->first();

            //     $parameterController = new ParameterController;
            //     $statusApp = $parameterController->getparameterid('STATUS APPROVAL','STATUS APPROVAL','NON APPROVAL');

            //     $content = new Request();
            //     $content['group'] = $group;
            //     $content['subgroup'] = $subgroup;
            //     $content['table'] = 'pengeluaranheader';
            //     $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            //     $nobuktikaskeluar = app(Controller::class)->getRunningNumber($content)->original['data'];

            //     $kasgantungHeader->pengeluaran_nobukti = $nobuktikaskeluar;


            //     $kasgantungHeader->save();

            //     $pengeluaranHeader = [
            //         'tanpaprosesnobukti' => 1,
            //         'nobukti' => $nobuktikaskeluar,
            //         'tglbukti' => $kasgantungHeader->tglbukti,
            //         'pelanggan_id' => 0,
            //         'keterangan' => $kasgantungHeader->keterangan,
            //         'statusjenistransaksi' => 0,
            //         'postingdari' => 'ENTRY KAS GANTUNG DARI ABSEN SUPIR',
            //         'statusapproval' => $statusApp->id,
            //         'dibayarke' => '',
            //         'cabang_id' => 1, // masih manual karena belum di catat di session
            //         'bank_id' => '',
            //         'userapproval' => "",
            //         'tglapproval' => "",
            //         'transferkeac' => '',
            //         'transferkean' => '',
            //         'trasnferkebank' => '',
            //         'statusformat' => $format->id,
            //         'modifiedby' =>  auth('api')->user()->name
            //     ];
            //     $pengeluaran = new StorePengeluaranHeaderRequest($pengeluaranHeader);
            //     app(PengeluaranHeaderController::class)->store($pengeluaran);
            //     DB::commit(); 
            // }

            if ($tanpaprosesnobukti == 0) {
                /* Store detail */
                $detaillog = [];

                $total = 0;
                for ($i = 0; $i < count($request->nominal); $i++) {


                    $datadetail = [
                        'kasgantung_id' => $kasgantungHeader->id,
                        'nobukti' => $kasgantungHeader->nobukti,
                        'nominal' => $request->nominal[$i],
                        'coa' => $bank->coa ?? '',
                        'keterangan' => $request->keterangan_detail[$i],
                        'modifiedby' => auth('api')->user()->name,
                    ];
                    $data = new StoreKasGantungDetailRequest($datadetail);

                    $datadetails = app(KasGantungDetailController::class)->store($data);

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }

                    $datadetaillog = [
                        'id' => $iddetail,
                        'kasgantung_id' => $kasgantungHeader->id,
                        'nobukti' => $kasgantungHeader->nobukti,
                        'nominal' => $request->nominal[$i],
                        'coa' => $bank->coa ?? '',
                        'keterangan' => $request->keterangan_detail[$i],
                        'modifiedby' => auth('api')->user()->name,
                        'created_at' => date('d-m-Y H:i:s', strtotime($kasgantungHeader->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($kasgantungHeader->updated_at)),
                    ];
                    $detaillog[] = $datadetaillog;

                    $total += $request->nominal[$i];
                }

                $dataid = LogTrail::select('id')
                    ->where('nobuktitrans', '=', $kasgantungHeader->nobukti)
                    ->where('namatabel', '=', $kasgantungHeader->getTable())
                    ->orderBy('id', 'DESC')
                    ->first();
                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'ENTRY KAS GANTUNG',
                    'idtrans' =>  $dataid->id,
                    'nobuktitrans' => $kasgantungHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => auth('api')->user()->name,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);

                app(LogTrailController::class)->store($data);

                $request->sortname = $request->sortname ?? 'id';
                $request->sortorder = $request->sortorder ?? 'asc';


                if ($kasgantungHeader->save() && $kasgantungHeader->kasgantungDetail) {
                    if ($request->bank_id != '') {

                        $bankid = $request->bank_id;
                        $querysubgrppengeluaran = DB::table('bank')
                            ->select(
                                'parameter.grp',
                                'parameter.subgrp',
                                'bank.statusformatpengeluaran',
                                'bank.coa'
                            )
                            ->join('parameter', 'bank.statusformatpengeluaran', 'parameter.id')
                            ->whereRaw("bank.id = $bankid")
                            ->first();

                        $parameterController = new ParameterController;
                        $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');

                        $coaKasKeluar = DB::table('parameter')->select('text')->where('grp', 'COA KAS GANTUNG')->first();

                        $content = new Request();
                        $content['group'] = $querysubgrppengeluaran->grp;
                        $content['subgroup'] = $querysubgrppengeluaran->subgrp;
                        $content['table'] = 'pengeluaranheader';
                        $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));


                        $nobuktikaskeluar = app(Controller::class)->getRunningNumber($content)->original['data'];


                        $kasgantungHeader->pengeluaran_nobukti = $nobuktikaskeluar;
                        $kasgantungHeader->save();

                        if($bank->tipe == 'KAS'){
                            $jenisTransaksi = Parameter::where('grp','JENIS TRANSAKSI')->where('text','KAS')->first();
                        }
                        if($bank->tipe == 'BANK'){
                            $jenisTransaksi = Parameter::where('grp','JENIS TRANSAKSI')->where('text','BANK')->first();
                        }

                        $pengeluaranHeader = [
                            'tanpaprosesnobukti' => 1,
                            'nobukti' => $nobuktikaskeluar,
                            'tglbukti' => date('Y-m-d', strtotime($request->tglkaskeluar)),
                            'pelanggan_id' => 0,
                            'keterangan' => $request->keterangan,
                            'statusjenistransaksi' => $jenisTransaksi->id,
                            'postingdari' => 'ENTRY KAS GANTUNG',
                            'statusapproval' => $statusApp->id,
                            'dibayarke' => '',
                            'cabang_id' => 1, // masih manual karena belum di catat di session
                            'bank_id' => $bank->id,
                            'userapproval' => "",
                            'tglapproval' => "",
                            'transferkeac' => '',
                            'transferkean' => '',
                            'trasnferkebank' => '',
                            'statusformat' => $querysubgrppengeluaran->statusformatpengeluaran,
                            'modifiedby' =>  auth('api')->user()->name
                        ];

                        $pengeluaranDetail = [];
                        for ($i = 0; $i < count($request->nominal); $i++) {
                            $detail = [];

                            $detail = [
                                'entriluar' => 1,
                                'nobukti' => $nobuktikaskeluar,
                                'tglbukti' => date('Y-m-d', strtotime($request->tglkaskeluar)),
                                'alatbayar_id' => 2,
                                'nowarkat' => '',
                                'tgljatuhtempo' => '',
                                'nominal' => $request->nominal[$i],
                                'coadebet' => $bank->coa,
                                'coakredit' => $coaKasKeluar->text,
                                'keterangan' => $request->keterangan_detail[$i],
                                'bulanbeban' => '',
                                'modifiedby' =>  auth('api')->user()->name
                            ];
                            // $total += $nominal;
                            $pengeluaranDetail[] = $detail;
                        }


                        $pengeluaran = $this->storePengeluaran($pengeluaranHeader, $pengeluaranDetail);

                        // if (!$pengeluaran['status'] AND @$pengeluaran['errorCode'] == 2601) {
                        //     goto ATAS;
                        // }

                        if (!$pengeluaran['status']) {
                            throw new \Throwable($pengeluaran['message']);
                        }
                    }

                    DB::commit();

                    /* Set position and page */

                    $selected = $this->getPosition($kasgantungHeader, $kasgantungHeader->getTable());
                    $kasgantungHeader->position = $selected->position;
                    $kasgantungHeader->page = ceil($kasgantungHeader->position / ($request->limit ?? 10));
                }
            }
            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $kasgantungHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }

        return response($kasgantungHeader->kasgantungDetail);
    }

    public function show($id)
    {
        $data = KasGantungHeader::findAll($id);
        $detail = KasGantungDetail::findAll($id);
        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }


    /**
     * @ClassName 
     */
    public function update(StoreKasGantungHeaderRequest $request, KasGantungHeader $kasgantungheader)
    {
        DB::beginTransaction();

        try {

            $bank = Bank::lockForUpdate()->findOrFail($request->bank_id);

            /* Store header */
            $kasgantungheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $kasgantungheader->penerima_id = $request->penerima_id;
            $kasgantungheader->keterangan = $request->keterangan ?? '';
            $kasgantungheader->bank_id = $request->bank_id ?? 0;
            $kasgantungheader->pengeluaran_nobukti = $request->pengeluaran_nobukti ?? '';
            $kasgantungheader->coakaskeluar = $bank->coa ?? '';
            $kasgantungheader->postingdari = 'ENTRY KAS GANTUNG';
            $kasgantungheader->tglkaskeluar = date('Y-m-d', strtotime($request->tglkaskeluar));
            $kasgantungheader->modifiedby = auth('api')->user()->name;

            if ($kasgantungheader->save()) {

                $logTrail = [
                    'namatabel' => strtoupper($kasgantungheader->getTable()),
                    'postingdari' => 'EDIT KAS GANTUNG HEADER',
                    'idtrans' => $kasgantungheader->id,
                    'nobuktitrans' => $kasgantungheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $kasgantungheader->toArray(),
                    'modifiedby' => $kasgantungheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            }


            /* Delete existing detail */
            $kasgantungheader->kasgantungDetail()->lockForUpdate()->delete();
            PengeluaranDetail::where('nobukti', $request->pengeluaran_nobukti)->lockForUpdate()->delete();
            PengeluaranHeader::where('nobukti', $request->pengeluaran_nobukti)->lockForUpdate()->delete();
            JurnalUmumDetail::where('nobukti', $request->pengeluaran_nobukti)->lockForUpdate()->delete();
            JurnalUmumHeader::where('nobukti', $request->pengeluaran_nobukti)->lockForUpdate()->delete();

            /* Store detail */
            $detaillog = [];
            $total = 0;
            for ($i = 0; $i < count($request->nominal); $i++) {
                $datadetail = [
                    'kasgantung_id' => $kasgantungheader->id,
                    'nobukti' => $kasgantungheader->nobukti,
                    'nominal' => $request->nominal[$i],
                    'coa' => $bank->coa ?? '',
                    'keterangan' => $request->keterangan_detail[$i],
                    'modifiedby' => auth('api')->user()->name,
                ];
                $data = new StoreKasGantungDetailRequest($datadetail);
                $datadetails = app(KasGantungDetailController::class)->store($data);

                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }

                $datadetaillog = [
                    'id' => $iddetail,
                    'kasgantung_id' => $kasgantungheader->id,
                    'nobukti' => $kasgantungheader->nobukti,
                    'nominal' => $request->nominal[$i],
                    'coa' => $bank->coa ?? '',
                    'keterangan' => $request->keterangan_detail[$i],
                    'modifiedby' => auth('api')->user()->name,
                    'created_at' => date('d-m-Y H:i:s', strtotime($kasgantungheader->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($kasgantungheader->updated_at)),
                ];
                $detaillog[] = $datadetaillog;

                $total += $request->nominal[$i];
            }

            $dataid = LogTrail::select('id')
                ->where('nobuktitrans', '=', $kasgantungheader->nobukti)
                ->where('namatabel', '=', $kasgantungheader->getTable())
                ->orderBy('id', 'DESC')
                ->first();

            $datalogtrail = [
                'namatabel' => $tabeldetail,
                'postingdari' => 'EDIT KAS GANTUNG DETAIL',
                'idtrans' =>  $dataid->id,
                'nobuktitrans' => $kasgantungheader->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $detaillog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($kasgantungheader && $kasgantungheader->kasgantungDetail) {
                $kasgantungheader->pengeluaran_nobukti = '-';
                $kasgantungheader->save();

                if ($request->bank_id != '') {

                    $bankid = $request->bank_id;
                    $querysubgrppengeluaran = DB::table('bank')
                        ->select(
                            'parameter.grp',
                            'parameter.subgrp',
                            'bank.statusformatpengeluaran',
                            'bank.coa'
                        )
                        ->join('parameter', 'bank.statusformatpengeluaran', 'parameter.id')
                        ->whereRaw("bank.id = $bankid")
                        ->first();

                    $parameterController = new ParameterController;
                    $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');

                    $coaKasKeluar = DB::table('parameter')->select('text')->where('id', '110')->first();

                    $content = new Request();
                    $content['group'] = $querysubgrppengeluaran->grp;
                    $content['subgroup'] = $querysubgrppengeluaran->subgrp;
                    $content['table'] = 'pengeluaranheader';
                    $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));


                    ATAS:
                    $nobuktikaskeluar = app(Controller::class)->getRunningNumber($content)->original['data'];


                    $kasgantungheader->pengeluaran_nobukti = $nobuktikaskeluar;
                    $kasgantungheader->save();


                    if($bank->tipe == 'KAS'){
                        $jenisTransaksi = Parameter::where('grp','JENIS TRANSAKSI')->where('text','KAS')->first();
                    }
                    if($bank->tipe == 'BANK'){
                        $jenisTransaksi = Parameter::where('grp','JENIS TRANSAKSI')->where('text','BANK')->first();
                    }
                    $pengeluaranHeader = [
                        'tanpaprosesnobukti' => 1,
                        'nobukti' => $nobuktikaskeluar,
                        'tglbukti' => date('Y-m-d', strtotime($request->tglkaskeluar)),
                        'pelanggan_id' => 0,
                        'keterangan' => $request->keterangan,
                        'statusjenistransaksi' => $jenisTransaksi->id,
                        'postingdari' => 'ENTRY KAS GANTUNG',
                        'statusapproval' => $statusApp->id,
                        'dibayarke' => '',
                        'cabang_id' => 1, // masih manual karena belum di catat di session
                        'bank_id' => $bank->id,
                        'userapproval' => "",
                        'tglapproval' => "",
                        'transferkeac' => '',
                        'transferkean' => '',
                        'trasnferkebank' => '',
                        'statusformat' => $querysubgrppengeluaran->statusformatpengeluaran,
                        'modifiedby' =>  auth('api')->user()->name
                    ];

                    $pengeluaranDetail = [];
                    for ($i = 0; $i < count($request->nominal); $i++) {
                        $detail = [];

                        $detail = [
                            'entriluar' => 1,
                            'nobukti' => $nobuktikaskeluar,
                            'tglbukti' => date('Y-m-d', strtotime($request->tglkaskeluar)),
                            'alatbayar_id' => 2,
                            'nowarkat' => '',
                            'tgljatuhtempo' => '',
                            'nominal' => $request->nominal[$i],
                            'coadebet' => $bank->coa,
                            'coakredit' => $coaKasKeluar->text,
                            'keterangan' => $request->keterangan_detail[$i],
                            'bulanbeban' => '',
                            'modifiedby' =>  auth('api')->user()->name
                        ];
                        // $total += $nominal;
                        $pengeluaranDetail[] = $detail;
                    }


                    $pengeluaran = $this->storePengeluaran($pengeluaranHeader, $pengeluaranDetail);

                    // if (!$pengeluaran['status'] AND @$pengeluaran['errorCode'] == 2601) {
                    //     goto ATAS;
                    // }
                    // dd($pengeluaran);

                    if (!$pengeluaran['status']) {
                        throw new \Throwable($pengeluaran['message']);
                    }
                }

                DB::commit();

                /* Set position and page */
                $selected = $this->getPosition($kasgantungheader, $kasgantungheader->getTable());
                $kasgantungheader->position = $selected->position;
                $kasgantungheader->page = ceil($kasgantungheader->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $kasgantungheader
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }
    /**
     * @ClassName 
     */
    public function destroy(KasGantungHeader $kasgantungheader, Request $request)
    {
        DB::beginTransaction();

        try {
            $delete = PengeluaranDetail::where('nobukti', $kasgantungheader->pengeluaran_nobukti)->lockForUpdate()->delete();
            $delete = PengeluaranHeader::where('nobukti', $kasgantungheader->pengeluaran_nobukti)->lockForUpdate()->delete();
            $delete = JurnalUmumDetail::where('nobukti', $kasgantungheader->pengeluaran_nobukti)->lockForUpdate()->delete();
            $delete = JurnalUmumHeader::where('nobukti', $kasgantungheader->pengeluaran_nobukti)->lockForUpdate()->delete();
            $delete = KasGantungDetail::where('kasgantung_id', $kasgantungheader->id)->lockForUpdate()->delete();
            $delete = KasGantungHeader::destroy($kasgantungheader->id);

            if ($delete) {
                $datalogtrail = [
                    'namatabel' => $kasgantungheader->getTable(),
                    'postingdari' => 'DELETE KAS GANTUNG HEADER',
                    'idtrans' => $kasgantungheader->id,
                    'nobuktitrans' => $kasgantungheader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $kasgantungheader->toArray(),
                    'modifiedby' => $kasgantungheader->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);

                DB::commit();
            }

            $selected = $this->getPosition($kasgantungheader, $kasgantungheader->getTable(), true);
            $kasgantungheader->position = $selected->position;
            $kasgantungheader->id = $selected->id;
            $kasgantungheader->page = ceil($kasgantungheader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $kasgantungheader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function combo(Request $request)
    {
        $data = [
            'penerima' => Penerima::all(),
            'bank' => Bank::all(),
        ];

        return response([
            'data' => $data
        ]);
    }

    private function storePengeluaran($pengeluaranHeader, $pengeluaranDetail)
    {

        DB::beginTransaction();

        try {


            $pengeluaran = new StorePengeluaranHeaderRequest($pengeluaranHeader);

            $pengeluarans = app(PengeluaranHeaderController::class)->store($pengeluaran);

            $nobukti = $pengeluaranHeader['nobukti'];
            $fetchPengeluaran = PengeluaranHeader::where('nobukti', '=', $nobukti)->first();

            $parameterController = new ParameterController;
            $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');
            $jurnalHeader = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $fetchPengeluaran->nobukti,
                'tglbukti' => $fetchPengeluaran->tglbukti,
                'keterangan' => $fetchPengeluaran->keterangan,
                'postingdari' => "ENTRY PENGELUARAN KAS DARI KAS GANTUNG",
                'statusapproval' => $statusApp->id,
                'userapproval' => "",
                'tglapproval' => "",
                'statusformat' => 0,
                'modifiedby' => auth('api')->user()->name,
            ];
            $jurnal = new StoreJurnalUmumHeaderRequest($jurnalHeader);
            app(JurnalUmumHeaderController::class)->store($jurnal);
            $id = $fetchPengeluaran->id;

            $details = [];


            foreach ($pengeluaranDetail as $value) {
                $value['pengeluaran_id'] = $id;
                $pengeluaranDetail = new StorePengeluaranDetailRequest($value);

                app(PengeluaranDetailController::class)->store($pengeluaranDetail);

                $fetchId = JurnalUmumHeader::select('id', 'tglbukti')
                    ->where('nobukti', '=', $nobukti)
                    ->first();


                $getBaris = DB::table('jurnalumumdetail')->select('baris')->where('nobukti', $nobukti)->orderByDesc('baris')->first();

                if (is_null($getBaris)) {
                    $baris = 0;
                } else {
                    $baris = $getBaris->baris + 1;
                }

                for ($x = 0; $x <= 1; $x++) {
                    if ($x == 1) {
                        $datadetail = [
                            'jurnalumum_id' => $fetchId->id,
                            'nobukti' => $nobukti,
                            'tglbukti' => $fetchId->tglbukti,
                            'coa' =>  $pengeluaranDetail['coakredit'],
                            'nominal' => -$pengeluaranDetail['nominal'],
                            'keterangan' => $pengeluaranDetail['keterangan'],
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $baris,
                        ];
                    } else {
                        $datadetail = [
                            'jurnalumum_id' => $fetchId->id,
                            'nobukti' => $nobukti,
                            'tglbukti' => $fetchId->tglbukti,
                            'coa' =>  $pengeluaranDetail['coadebet'],
                            'nominal' => $pengeluaranDetail['nominal'],
                            'keterangan' => $pengeluaranDetail['keterangan'],
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $baris,
                        ];
                    }
                    $detail = new StoreJurnalUmumDetailRequest($datadetail);
                    $tes = app(JurnalUmumDetailController::class)->store($detail);
                }

                $details = $pengeluaranDetail;
            }
            DB::commit();

            return [
                'status' => true,
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('kasgantungheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
