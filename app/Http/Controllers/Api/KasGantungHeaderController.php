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

            $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

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
            $kasgantungHeader->statuscetak = $statusCetak->id ?? 0;
            $kasgantungHeader->userbukacetak = '';
            $kasgantungHeader->tglbukacetak = '';

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
                'postingdari' => $request->postingdari ?? 'ENTRY KAS GANTUNG HEADER',
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

                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'ENTRY KAS GANTUNG DETAIL',
                    'idtrans' =>  $kasgantungHeader->id,
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

                        if ($bank->tipe == 'KAS') {
                            $jenisTransaksi = Parameter::where('grp', 'JENIS TRANSAKSI')->where('text', 'KAS')->first();
                        }
                        if ($bank->tipe == 'BANK') {
                            $jenisTransaksi = Parameter::where('grp', 'JENIS TRANSAKSI')->where('text', 'BANK')->first();
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
    }

    public function show($id)
    {
        $data = KasGantungHeader::findUpdate($id);
        $detail = KasGantungDetail::findUpdate($id);
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

            $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

            /* Store header */
            $kasgantungheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $kasgantungheader->penerima_id = $request->penerima_id;
            $kasgantungheader->keterangan = $request->keterangan ?? '';
            $kasgantungheader->bank_id = $request->bank_id ?? 0;
            $kasgantungheader->pengeluaran_nobukti = $request->pengeluaran_nobukti ?? '';
            $kasgantungheader->coakaskeluar = $bank->coa ?? '';
            $kasgantungheader->statuscetak = $statusCetak->id ?? 0;
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

            $datalogtrail = [
                'namatabel' => $tabeldetail,
                'postingdari' => 'EDIT KAS GANTUNG DETAIL',
                'idtrans' =>  $kasgantungheader->id,
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


                    if ($bank->tipe == 'KAS') {
                        $jenisTransaksi = Parameter::where('grp', 'JENIS TRANSAKSI')->where('text', 'KAS')->first();
                    }
                    if ($bank->tipe == 'BANK') {
                        $jenisTransaksi = Parameter::where('grp', 'JENIS TRANSAKSI')->where('text', 'BANK')->first();
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

            $getDetail = KasGantungDetail::where('kasgantung_id', $kasgantungheader->id)->get();
            $getPengeluaranHeader = PengeluaranHeader::where('nobukti', $kasgantungheader->pengeluaran_nobukti)->first();
            $getPengeluaranDetail = PengeluaranDetail::where('nobukti', $kasgantungheader->pengeluaran_nobukti)->get();
            $getJurnalHeader = JurnalUmumHeader::where('nobukti', $kasgantungheader->pengeluaran_nobukti)->first();
            $getJurnalDetail = JurnalUmumDetail::where('nobukti', $kasgantungheader->pengeluaran_nobukti)->get();

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
                    'modifiedby' => auth('api')->user()->name
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);

                // DELETE KAS GANTUNG DETAIL
                $logTrailKasgantungDetail = [
                    'namatabel' => 'KASGANTUNGDETAIL',
                    'postingdari' => 'DELETE KAS GANTUNG DETAIL',
                    'idtrans' => $kasgantungheader->id,
                    'nobuktitrans' => $kasgantungheader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $getDetail->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrailKasgantungDetail = new StoreLogTrailRequest($logTrailKasgantungDetail);
                app(LogTrailController::class)->store($validatedLogTrailKasgantungDetail);

                // DELETE PENGELUARAN HEADER
                $logTrailPengeluaranHeader = [
                    'namatabel' => 'PENGELUARANHEADER',
                    'postingdari' => 'DELETE PENGELUARAN HEADER DARI KAS GANTUNG',
                    'idtrans' => $getPengeluaranHeader->id,
                    'nobuktitrans' => $getPengeluaranHeader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $getPengeluaranHeader->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrailPengeluaranHeader = new StoreLogTrailRequest($logTrailPengeluaranHeader);
                app(LogTrailController::class)->store($validatedLogTrailPengeluaranHeader);

                // DELETE PENGELUARAN DETAIL
                $logTrailPengeluaranDetail = [
                    'namatabel' => 'PENGELUARANDETAIL',
                    'postingdari' => 'DELETE PENGELUARAN DETAIL DARI KAS GANTUNG',
                    'idtrans' => $getPengeluaranHeader->id,
                    'nobuktitrans' => $getPengeluaranHeader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $getPengeluaranDetail->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrailPengeluaranDetail = new StoreLogTrailRequest($logTrailPengeluaranDetail);
                app(LogTrailController::class)->store($validatedLogTrailPengeluaranDetail);

                // DELETE JURNAL HEADER
                $logTrailJurnalHeader = [
                    'namatabel' => 'JURNALUMUMHEADER',
                    'postingdari' => 'DELETE JURNAL UMUM HEADER DARI KAS GANTUNG',
                    'idtrans' => $getJurnalHeader->id,
                    'nobuktitrans' => $getJurnalHeader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $getJurnalHeader->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrailJurnalHeader = new StoreLogTrailRequest($logTrailJurnalHeader);
                app(LogTrailController::class)->store($validatedLogTrailJurnalHeader);

                // DELETE JURNAL DETAIL
                $logTrailJurnalDetail = [
                    'namatabel' => 'JURNALUMUMDETAIL',
                    'postingdari' => 'DELETE JURNAL UMUM DETAIL DARI KAS GANTUNG',
                    'idtrans' => $getJurnalHeader->id,
                    'nobuktitrans' => $getJurnalHeader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $getJurnalDetail->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrailJurnalDetail = new StoreLogTrailRequest($logTrailJurnalDetail);
                app(LogTrailController::class)->store($validatedLogTrailJurnalDetail);

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

    public function storePengeluaran($pengeluaranHeader, $pengeluaranDetail)
    {
        try {

            $pengeluaran = new StorePengeluaranHeaderRequest($pengeluaranHeader);
            $header = app(PengeluaranHeaderController::class)->store($pengeluaran);

            $nobukti = $pengeluaranHeader['nobukti'];
            $fetchPengeluaran = PengeluaranHeader::whereRaw("nobukti = '$nobukti'")->first();

            $parameterController = new ParameterController;
            $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');
            $jurnalHeader = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $fetchPengeluaran->nobukti,
                'tglbukti' => $fetchPengeluaran->tglbukti,
                'keterangan' => $fetchPengeluaran->keterangan,
                'postingdari' => "ENTRY PENGELUARAN DARI KAS GANTUNG",
                'statusapproval' => $statusApp->id,
                'userapproval' => "",
                'tglapproval' => "",
                'statusformat' => 0,
                'modifiedby' => auth('api')->user()->name,
            ];

            $storeJurnal = new StoreJurnalUmumHeaderRequest($jurnalHeader);
            app(JurnalUmumHeaderController::class)->store($storeJurnal);
            
            $fetchJurnal = JurnalUmumHeader::whereRaw("nobukti = '$nobukti'")->first();
         
            $idPengeluaran = $fetchPengeluaran->id;

            $detailLogPengeluaran = [];
            $detailLogJurnal = [];
            foreach ($pengeluaranDetail as $value) {

                $value['pengeluaran_id'] = $idPengeluaran;
                $pengeluaranDetail = new StorePengeluaranDetailRequest($value);
                $detailPengeluaran = app(PengeluaranDetailController::class)->store($pengeluaranDetail);

                $pengeluarans = $detailPengeluaran['detail'];
                $dataDetailLogPengeluaran = [
                    'id' => $pengeluarans->id,
                    'pengeluaran_id' => $pengeluarans->pengeluaran_id,
                    'nobukti' => $pengeluarans->nobukti,
                    'alatbayar_id' => $pengeluarans->alatbayar_id,
                    'nowarkat' => $pengeluarans->nowarkat,
                    'tgljatuhtempo' =>  date('Y-m-d', strtotime($pengeluarans->tgljatuhtempo)),
                    'nominal' => $pengeluarans->nominal,
                    'coadebet' =>  $pengeluarans->coadebet,
                    'coakredit' => $pengeluarans->coakredit,
                    'keterangan' => $pengeluarans->keterangan,
                    'bulanbeban' =>  date('Y-m-d', strtotime($pengeluarans->bulanbeban)) ?? '',
                    'modifiedby' => $pengeluarans->modifiedby,
                    'created_at' => date('d-m-Y H:i:s', strtotime($pengeluarans->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($pengeluarans->updated_at)),
                ];
                $detailLogPengeluaran[] = $dataDetailLogPengeluaran;

                // JURNAL
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
                    $detailJurnal = app(JurnalUmumDetailController::class)->store($detail);
                    
                    $jurnals = $detailJurnal['detail'];
                    $dataDetailLogJurnal = [
                        'id' => $jurnals->id,
                        'jurnalumum_id' =>  $jurnals->jurnalumum_id,
                        'nobukti' => $jurnals->nobukti,
                        'tglbukti' => $jurnals->tglbukti,
                        'coa' => $jurnals->coa,
                        'nominal' => $jurnals->nominal,
                        'keterangan' => $jurnals->keterangan,
                        'modifiedby' => $jurnals->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($jurnals->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($jurnals->updated_at)),
                        'baris' => $jurnals->baris,
                    ];
                    $detailLogJurnal[] = $dataDetailLogJurnal;
                }

            }

            $datalogtrail = [
                'namatabel' => $detailPengeluaran['tabel'],
                'postingdari' => 'ENTRY KAS GANTUNG',
                'idtrans' =>  $idPengeluaran,
                'nobuktitrans' => $nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detailLogPengeluaran,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);


            $datalogtrail = [
                'namatabel' => $detailJurnal['tabel'],
                'postingdari' => 'ENTRY PENGELUARAN DARI KAS GANTUNG',
                'idtrans' =>  $fetchJurnal->id,
                'nobuktitrans' => $fetchJurnal->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detailLogJurnal,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);


            return [
                'status' => true
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $kasgantung = KasGantungHeader::lockForUpdate()->findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($kasgantung->statuscetak != $statusSudahCetak->id) {
                $kasgantung->statuscetak = $statusSudahCetak->id;
                $kasgantung->tglbukacetak = date('Y-m-d H:i:s');
                $kasgantung->userbukacetak = auth('api')->user()->name;
                $kasgantung->jumlahcetak = $kasgantung->jumlahcetak + 1;

                if ($kasgantung->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($kasgantung->getTable()),
                        'postingdari' => 'PRINT KAS GANTUNG HEADER',
                        'idtrans' => $kasgantung->id,
                        'nobuktitrans' => $kasgantung->nobukti,
                        'aksi' => 'PRINT',
                        'datajson' => $kasgantung->toArray(),
                        'modifiedby' => $kasgantung->modifiedby
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
        $kasgantung = KasGantungHeader::find($id);
        $status = $kasgantung->statusapproval;
        $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $kasgantung->statuscetak;
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('kasgantungheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
