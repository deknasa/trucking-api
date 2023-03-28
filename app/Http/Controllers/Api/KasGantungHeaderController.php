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
use App\Http\Requests\UpdatePengeluaranHeaderRequest;
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

    public function default()
    {
        $kasgantungHeader = new KasGantungHeader();
        return response([
            'status' => true,
            'data' => $kasgantungHeader->default(),
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

                // nobukti pengeluaran
                $bankid = $request->bank_id;
                $querysubgrppengeluaran = DB::table('bank')->from(DB::raw("bank with (readuncommitted)"))
                    ->select(
                        'parameter.grp',
                        'parameter.subgrp',
                        'bank.formatpengeluaran',
                        'bank.coa'
                    )
                    ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpengeluaran', 'parameter.id')
                    ->whereRaw("bank.id = $bankid")
                    ->first();


                $coaKasKeluar = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', 'COA KAS GANTUNG')->first();

                $contentKasgantung = new Request();
                $contentKasgantung['group'] = $querysubgrppengeluaran->grp;
                $contentKasgantung['subgroup'] = $querysubgrppengeluaran->subgrp;
                $contentKasgantung['table'] = 'pengeluaranheader';
                $contentKasgantung['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                $nobuktikaskeluar = app(Controller::class)->getRunningNumber($contentKasgantung)->original['data'];
            }

            $kasgantungHeader = new KasGantungHeader();

            if ($tanpaprosesnobukti == 1) {
                $kasgantungHeader->nobukti = $request->nobukti;
            }

            $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

            $kasgantungHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti)) ?? '1900/1/1';
            $kasgantungHeader->penerima_id = $request->penerima_id ?? '';
            $kasgantungHeader->bank_id = $request->bank_id ?? 0;
            $kasgantungHeader->pengeluaran_nobukti = $request->pengeluaran_nobukti ?? $nobuktikaskeluar;
            $kasgantungHeader->coakaskeluar = $bank->coa ?? '';
            $kasgantungHeader->postingdari = $request->postingdari ?? 'ENTRY KAS GANTUNG';
            $kasgantungHeader->tglkaskeluar = date('Y-m-d', strtotime($request->tglbukti));
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
                    'namatabel' => strtoupper($tabeldetail),
                    'postingdari' => 'ENTRY KAS GANTUNG DETAIL',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $kasgantungHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => auth('api')->user()->name,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);

                app(LogTrailController::class)->store($data);



                if ($kasgantungHeader->save() && $kasgantungHeader->kasgantungDetail) {
                    if ($request->bank_id != '') {

                        $parameterController = new ParameterController;
                        $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');

                        if ($bank->tipe == 'KAS') {
                            $jenisTransaksi = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                                ->where('grp', 'JENIS TRANSAKSI')->where('text', 'KAS')->first();
                        }
                        if ($bank->tipe == 'BANK') {
                            $jenisTransaksi = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                                ->where('grp', 'JENIS TRANSAKSI')->where('text', 'BANK')->first();
                        }


                        $coakredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                            ->where('grp', 'JURNAL KAS GANTUNG')->where('subgrp', 'DEBET')->first();
                        $memo = json_decode($coakredit->memo, true);
                        $penerima = Penerima::from(DB::raw("penerima with (readuncommitted)"))->where("id", $request->penerima_id)->first();
                        $namaPenerima = ($penerima != null) ? $penerima->namapenerima : '';
                        $pengeluaranDetail = [];
                        for ($i = 0; $i < count($request->nominal); $i++) {
                            $detail = [];

                            $detail = [
                                'entriluar' => 1,
                                'nobukti' => $nobuktikaskeluar,
                                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                                'alatbayar_id' => 2,
                                'nowarkat' => '',
                                'tgljatuhtempo' => '',
                                'nominal' => $request->nominal[$i],
                                'coadebet' => $memo['JURNAL'],
                                'coakredit' => $bank->coa,
                                'keterangan' => $request->keterangan_detail[$i],
                                'bulanbeban' => '',
                                'modifiedby' =>  auth('api')->user()->name
                            ];
                            // $total += $nominal;
                            $pengeluaranDetail[] = $detail;
                        }

                        $pengeluaranHeader = [
                            'tanpaprosesnobukti' => 1,
                            'nobukti' => $nobuktikaskeluar,
                            'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                            'pelanggan_id' => 0,
                            'statusjenistransaksi' => $jenisTransaksi->id,
                            'postingdari' => 'ENTRY KAS GANTUNG',
                            'statusapproval' => $statusApp->id,
                            'dibayarke' => $namaPenerima,
                            'cabang_id' => 1, // masih manual karena belum di catat di session
                            'bank_id' => $bank->id,
                            'userapproval' => "",
                            'tglapproval' => "",
                            'transferkeac' => '',
                            'transferkean' => '',
                            'trasnferkebank' => '',
                            'statusformat' => $querysubgrppengeluaran->formatpengeluaran,
                            'modifiedby' =>  auth('api')->user()->name,
                            'datadetail' => $pengeluaranDetail
                        ];

                        $pengeluaran = new StorePengeluaranHeaderRequest($pengeluaranHeader);
                        app(PengeluaranHeaderController::class)->store($pengeluaran);
                    }

                    $request->sortname = $request->sortname ?? 'id';
                    $request->sortorder = $request->sortorder ?? 'asc';

                    DB::commit();

                    /* Set position and page */

                    $selected = $this->getPosition($kasgantungHeader, $kasgantungHeader->getTable());
                    $kasgantungHeader->position = $selected->position;
                    $kasgantungHeader->page = ceil($kasgantungHeader->position / ($request->limit ?? 10));
                }
            }

            return response([
                'status' => true,
                'idlogtrail' => $storedLogTrail['id'],
                'message' => 'Berhasil disimpan',
                'data' => $kasgantungHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
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
    public function update(UpdateKasGantungHeaderRequest $request, KasGantungHeader $kasgantungheader)
    {
        //   dd($request->all());

        DB::beginTransaction();

        try {
            $bank_id = $kasgantungheader->bank_id;
            if ($request->from == "AbsensiSupirApprovalHeader") {
                $bank_id = $request->bank_id;
            }
            $bank = Bank::lockForUpdate()->findOrFail($bank_id);

            /* Edit header */

            $kasgantungheader->penerima_id = $request->penerima_id ?? '';
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
            $kasgantungheader->kasgantungDetail()->delete();

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

                $detaillog[] = $datadetails['detail']->toArray();

                $total += $request->nominal[$i];
            }

            $datalogtrail = [
                'namatabel' => strtoupper($tabeldetail),
                'postingdari' => 'EDIT KAS GANTUNG DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $kasgantungheader->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $detaillog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';



            // return DB::table('parameter')->where('grp', 'JURNAL KAS GANTUNG')->where('subgrp', 'DEBET')->first();
            $coakredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'JURNAL KAS GANTUNG')->where('subgrp', 'DEBET')->first();
            $memo =  json_decode($coakredit->memo, true);
            $penerima = Penerima::from(DB::raw("penerima with (readuncommitted)"))->where("id", $request->penerima_id)->first();
            $namaPenerima = ($penerima != null) ? $penerima->namapenerima : '';

            $pengeluaranDetail = [];
            for ($i = 0; $i < count($request->nominal); $i++) {
                $detail = [];

                $detail = [
                    'entriluar' => 1,
                    'nobukti' => $kasgantungheader->nobukti,
                    'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                    'alatbayar_id' => 2,
                    'nowarkat' => '',
                    'tgljatuhtempo' => '',
                    'nominal' => $request->nominal[$i],
                    'coadebet' => $memo['JURNAL'],
                    'coakredit' => $bank->coa,
                    'keterangan' => $request->keterangan_detail[$i],
                    'bulanbeban' => '',
                    'modifiedby' =>  auth('api')->user()->name
                ];
                // $total += $nominal;
                $pengeluaranDetail[] = $detail;
            }

            $pengeluaranHeader = [
                'dibayarke' => $namaPenerima,
                'bank_id' => $kasgantungheader->bank_id ?? 0,
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'isUpdate' => 1,
                'postingdari' => 'EDIT KAS GANTUNG',
                'modifiedby' => auth('api')->user()->name,
                'datadetail' => $pengeluaranDetail,
                'approvalabsensisupir' => $request->approvalabsensisupir ?? false,
                'kasgantungheader_id' => $kasgantungheader->id ?? 0,
                'absensisupirapprovalheader_id' => $request->absensisupirapprovalheader_id ?? 0,
                'coakaskeluar' => $request->coakaskeluar ?? 0,
            ];
            if ($bank->tipe == 'KAS') {
                $jenisTransaksi = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', 'JENIS TRANSAKSI')->where('text', 'KAS')->first();
            }
            if ($bank->tipe == 'BANK') {
                $jenisTransaksi = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', 'JENIS TRANSAKSI')->where('text', 'BANK')->first();
            }


            // dd($pengeluaranHeader);
            // return response(PengeluaranHeader::where('nobukti', $kasgantungheader->pengeluaran_nobukti)->get(),422);
            $get = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))->where('nobukti', $kasgantungheader->pengeluaran_nobukti)->first();
            $approvalabsensisupir = $request->approvalabsensisupir ?? false;
            $request->from = $request->from ?? false;
            $bank = Bank::lockForUpdate()->findOrFail($bank_id);
            if ($request->from == "AbsensiSupirApprovalHeader") {
                $querysubgrppengeluaran = DB::table('bank')->from(DB::raw("bank with (readuncommitted)"))
                    ->select(
                        'parameter.grp',
                        'parameter.subgrp',
                        'bank.formatpengeluaran',
                        'bank.coa'
                    )
                    ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpengeluaran', 'parameter.id')
                    ->whereRaw("bank.id = $request->bank_id")
                    ->first();
                $contentKasgantung = new Request();
                $contentKasgantung['group'] = $querysubgrppengeluaran->grp;
                $contentKasgantung['subgroup'] = $querysubgrppengeluaran->subgrp;
                $contentKasgantung['table'] = 'pengeluaranheader';
                $contentKasgantung['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
                $parameterController = new ParameterController;

                $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');

                $nobuktikaskeluar = app(Controller::class)->getRunningNumber($contentKasgantung)->original['data'];
                $pengeluaranHeader = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $nobuktikaskeluar,
                    'tglbukti' => date('Y-m-d', strtotime($request->tglkaskeluar)),
                    'pelanggan_id' => 0,
                    'statusjenistransaksi' => $jenisTransaksi->id,
                    'postingdari' => 'ENTRY KAS GANTUNG',
                    'statusapproval' => $statusApp->id,
                    'dibayarke' => $namaPenerima,
                    'cabang_id' => 1, // masih manual karena belum di catat di session
                    'bank_id' => $bank->id,
                    'userapproval' => "",
                    'tglapproval' => "",
                    'transferkeac' => '',
                    'transferkean' => '',
                    'trasnferkebank' => '',
                    'statusformat' => $querysubgrppengeluaran->formatpengeluaran,
                    'modifiedby' =>  auth('api')->user()->name,
                    'datadetail' => $pengeluaranDetail
                ];

                $pengeluaran = new StorePengeluaranHeaderRequest($pengeluaranHeader);
                app(PengeluaranHeaderController::class)->store($pengeluaran);
            } else {
                $newPengeluaran = new PengeluaranHeader();
                $newPengeluaran = $newPengeluaran->findAll($get->id);
                $pengeluaran = new UpdatePengeluaranHeaderRequest($pengeluaranHeader);
                app(PengeluaranHeaderController::class)->update($pengeluaran, $newPengeluaran);
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

        try {

            $getDetail = KasGantungDetail::lockForUpdate()->where('kasgantung_id', $id)->get();

            $request['postingdari'] = "DELETE KAS GANTUNG";
            $kasgantungheader = new KasGantungHeader();
            $kasgantungheader = $kasgantungheader->lockAndDestroy($id);

            if ($kasgantungheader) {
                $datalogtrail = [
                    'namatabel' => strtoupper($kasgantungheader->getTable()),
                    'postingdari' => 'DELETE KAS GANTUNG HEADER',
                    'idtrans' => $kasgantungheader->id,
                    'nobuktitrans' => $kasgantungheader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $kasgantungheader->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                $storedLogTrail = app(LogTrailController::class)->store($data);

                // DELETE KAS GANTUNG DETAIL
                $logTrailKasgantungDetail = [
                    'namatabel' => 'KASGANTUNGDETAIL',
                    'postingdari' => 'DELETE KAS GANTUNG DETAIL',
                    'idtrans' => $storedLogTrail['id'],
                    'nobuktitrans' => $kasgantungheader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $getDetail->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrailKasgantungDetail = new StoreLogTrailRequest($logTrailKasgantungDetail);
                app(LogTrailController::class)->store($validatedLogTrailKasgantungDetail);

                $getPengeluaran = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))->where('nobukti', $kasgantungheader->pengeluaran_nobukti)->first();
                app(PengeluaranHeaderController::class)->destroy($request, $getPengeluaran->id);

                DB::commit();

                $selected = $this->getPosition($kasgantungheader, $kasgantungheader->getTable(), true);
                $kasgantungheader->position = $selected->position;
                $kasgantungheader->id = $selected->id;
                $kasgantungheader->page = ceil($kasgantungheader->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $kasgantungheader
                ]);
            }

            return response([
                'message' => 'Gagal dihapus'
            ], 500);
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


    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $kasgantung = KasGantungHeader::lockForUpdate()->findOrFail($id);
            $statusSudahCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

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
        $kasgantungHeader = new KasGantungHeader();
        $nobukti = KasGantungHeader::from(DB::raw("kasgantungheader"))->where('id', $id)->first();
        $cekdata = $kasgantungHeader->cekvalidasiaksi($nobukti->nobukti);
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

    public function cekvalidasi($id)
    {
        $kasgantung = KasGantungHeader::find($id);
        $status = $kasgantung->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $kasgantung->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

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
