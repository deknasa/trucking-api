<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSupirApprovalHeader;
use App\Models\AbsensiSupirHeader;
use App\Models\AbsensiSupirApprovalDetail;

use App\Models\KasGantungHeader;
use App\Models\PengeluaranDetail;
use App\Models\PengeluaranHeader;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\Parameter;

use App\Http\Requests\StoreAbsensiSupirApprovalHeaderRequest;
use App\Http\Requests\UpdateAbsensiSupirApprovalHeaderRequest;
use App\Http\Requests\StoreAbsensiSupirApprovalDetailRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreKasGantungHeaderRequest;
use App\Http\Requests\UpdateKasGantungHeaderRequest;
use App\Http\Requests\StoreLogTrailRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Exception;

class AbsensiSupirApprovalHeaderController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $absensiSupirApprovalHeader = new AbsensiSupirApprovalHeader();

        return response([
            'data' => $absensiSupirApprovalHeader->get(),
            'attributes' => [
                'totalRows' => $absensiSupirApprovalHeader->totalRows,
                'totalPages' => $absensiSupirApprovalHeader->totalPages
            ]
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreAbsensiSupirApprovalHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $group = 'ABSENSI SUPIR APPROVAL BUKTI';
            $subgroup = 'ABSENSI SUPIR APPROVAL BUKTI';
            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'absensisupirapprovalheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $group = 'JURNAL APPROVAL ABSENSI SUPIR';
            $subgroup = 'KREDIT';
            $coaaproval = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $memokredit = json_decode($coaaproval->memo, true);


            $group = 'JURNAL APPROVAL ABSENSI SUPIR';
            $subgroup = 'DEBET';
            $coadebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $memodebet = json_decode($coadebet->memo, true);




            $coakaskeluar = $memokredit['JURNAL'];
            $absensiSupirApprovalHeader = new AbsensiSupirApprovalHeader();
            $absensisupir = DB::table('absensisupirheader')->where('nobukti', $request->absensisupir)->first();
            $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
            /* Store header */
            $absensiSupirApprovalHeader->tglbukti =  date('Y-m-d', strtotime($request->tglbukti));
            $absensiSupirApprovalHeader->absensisupir_nobukti =  $request->absensisupir_nobukti;
            // $absensiSupirApprovalHeader->keterangan =  $request->keterangan;
            $absensiSupirApprovalHeader->statusapproval =  4;
            $absensiSupirApprovalHeader->statusformat =  $format->id;
            $absensiSupirApprovalHeader->pengeluaran_nobukti = $request->pengeluaran_nobukti ?? '0';
            $absensiSupirApprovalHeader->coakaskeluar = $coakaskeluar;
            $absensiSupirApprovalHeader->tglkaskeluar = $request->tglkaskeluar ?? '1900/1/1';
            $absensiSupirApprovalHeader->postingdari =  "ABSENSI SUPIR APPROVAL";
            $absensiSupirApprovalHeader->statuscetak = $statusCetak->id ?? 0;
            $absensiSupirApprovalHeader->modifiedby =  auth('api')->user()->name;
            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $absensiSupirApprovalHeader->nobukti = $nobukti;

            $absensiSupirApprovalHeader->save();

            $bank = DB::table('bank')->where('coa', $coakaskeluar)->first();
            // $kasgantung = DB::table('kasgantungheader')->where('nobukti', $request->kasgantung_nobukti)->first();
            $kasgantung = KasGantungHeader::where('nobukti', $request->kasgantung_nobukti)->first();
            $kasgantungdetail = DB::table('kasgantungdetail')->where('nobukti', $request->kasgantung_nobukti)->get();
            $details = [];
            $total = 0;
            foreach ($kasgantungdetail as $detail) {
                $details['keterangan'][] = $detail->keterangan;
                $details['nominal'][] = $detail->nominal;
                $total += $detail->nominal;
            }


            $dataKasgantung = [
                "tglbukti" => $kasgantung->tglbukti,
                // "keterangan" => $absensiSupirApprovalHeader->keterangan,
                "bank_id" => $bank->id,
                "penerima_id" => $kasgantung->penerima_id,
                "coakaskeluar" => $coakaskeluar,
                "postingdari" => 'ENTRY ABSENSI SUsPIR APPROVAL',
                "tglkaskeluar" => $request->tglbukti,
                'keterangan_detail' => $details['keterangan'],
                'nominal' => $details['nominal'],
                'approvalabsensisupir' => true,
                'absensisupirapprovalheader_id' => $absensiSupirApprovalHeader->id,
                'absensisupir_nobukti' => $absensiSupirApprovalHeader->absensisupir_nobukti,
                "from" => "AbsensiSupirApprovalHeader"
            ];


            $data = new UpdateKasGantungHeaderRequest($dataKasgantung);
            // dump($data);
            $kasgantungStore = app(KasGantungHeaderController::class)->update($data, $kasgantung);

            $kasgantung = $kasgantungStore->original['data'];


            $absensiSupirApprovalHeader->pengeluaran_nobukti = $kasgantung->pengeluaran_nobukti;
            $absensiSupirApprovalHeader->tglkaskeluar = $kasgantung->tglkaskeluar;
            $absensiSupirApprovalHeader->save();



            $logTrail = [
                'namatabel' => strtoupper($absensiSupirApprovalHeader->getTable()),
                'postingdari' => 'ENTRY ABSENSI SUPIR APPROVAL HEADER',
                'idtrans' => $absensiSupirApprovalHeader->id,
                'nobuktitrans' => $absensiSupirApprovalHeader->nobukti,
                'aksi' => 'ADD',
                'datajson' => $absensiSupirApprovalHeader->toArray(),
                'modifiedby' => $absensiSupirApprovalHeader->modifiedby
            ];
            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            if ($request->trado_id) {
                /* Store detail */
                $detaillog = [];
                $jurnalDetail = [];
                for ($i = 0; $i < count($request->trado_id); $i++) {
                    $supirId = ($request->supir_id[$i] != null) ? $request->supir_id[$i] : 0;
                    $datadetail = [
                        "absensisupirapproval_id" => $absensiSupirApprovalHeader->id,
                        "nobukti" => $absensiSupirApprovalHeader->nobukti,
                        "trado_id" => $request->trado_id[$i],
                        "supir_id" => $supirId,
                        "modifiedby" => auth('api')->user()->name
                    ];
                    $data = new StoreAbsensiSupirApprovalDetailRequest($datadetail);
                    $absensiSupirApprovalDetail = app(AbsensiSupirApprovalDetailController::class)->store($data);

                    if ($absensiSupirApprovalDetail['error']) {
                        return response($absensiSupirApprovalDetail, 422);
                    } else {
                        $iddetail = $absensiSupirApprovalDetail['id'];
                        $tabeldetail = $absensiSupirApprovalDetail['tabel'];
                    }
                    $datadetaillog = [
                        "id" => $iddetail,
                        "absensisupirapproval_id" => $absensiSupirApprovalHeader->id,
                        "nobukti" => $absensiSupirApprovalHeader->nobukti,
                        "trado_id" => $request->trado_id[$i],
                        "supir_id" => $supirId,
                        "modifiedby" => auth('api')->user()->name,
                        'created_at' => date('d-m-Y H:i:s', strtotime($absensiSupirApprovalHeader->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($absensiSupirApprovalHeader->updated_at)),
                    ];


                    $detaillog[] = $datadetaillog;
                }
                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'ENTRY ABSENSI SUPIR APPROVAL DETAIL',
                    'idtrans' =>  $iddetail,
                    'nobuktitrans' => $absensiSupirApprovalHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => auth('api')->user()->name,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);

                $jurnalHeader = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $absensiSupirApprovalHeader->nobukti,
                    'tglbukti' => date('Y-m-d', strtotime($absensiSupirApprovalHeader->tglbukti)),
                    // 'keterangan' => $absensiSupirApprovalHeader->keterangan,
                    'postingdari' => 'ENTRY ABSENSI SUPIR APPROVAL DETAIL',
                    'statusapproval' => $absensiSupirApprovalHeader->statusapproval,
                    'userapproval' => "",
                    'tglapproval' => "",
                    'statusformat' => 0,
                    'modifiedby' => auth('api')->user()->name,
                ];

                $jurnalDetail = [
                    [
                        'nobukti' => $absensiSupirApprovalHeader->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($absensiSupirApprovalHeader->tglbukti)),
                        'coa' =>  $memodebet['JURNAL'],
                        'nominal' => $total,
                        // 'keterangan' => $request->keterangan,
                        'modifiedby' => auth('api')->user()->name,
                        'baris' => $i,
                    ], [
                        'nobukti' => $absensiSupirApprovalHeader->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($absensiSupirApprovalHeader->tglbukti)),
                        'coa' =>  $kasgantung->coakaskeluar,
                        'nominal' => -$total,
                        // 'keterangan' => $request->keterangan,
                        'modifiedby' => auth('api')->user()->name,
                        'baris' => $i,
                    ]
                ];
            }

            $queryabsen = DB::table('absensisupirapproval')
                ->from(
                    DB::raw("absensisupirheader a with (readuncommitted)")
                )
                ->select(
                    'b.pengeluaran_nobukti',
                    'b.tglkaskeluar'
                )
                ->join(DB::raw("kasgantungheader b"), 'a.kasgantung_nobukti', 'b.nobukti')
                ->first();
            if (isset($queryabsen)) {
                $absensisupirapprovalheader  = AbsensiSupirApprovalHeader::lockForUpdate()->where("id", $absensiSupirApprovalHeader->id)
                    ->firstorFail();
                $absensisupirapprovalheader->pengeluaran_nobukti = $queryabsen->pengeluaran_nobukti;
                $absensisupirapprovalheader->tglkaskeluar = $queryabsen->tglkaskeluar;
                $absensisupirapprovalheader->save();
            }

            DB::commit();

            /* Set position and page */

            $selected = $this->getPosition($absensiSupirApprovalHeader, $absensiSupirApprovalHeader->getTable());
            $absensiSupirApprovalHeader->position = $selected->position;
            $absensiSupirApprovalHeader->page = ceil($absensiSupirApprovalHeader->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $absensiSupirApprovalHeader->page = ceil($absensiSupirApprovalHeader->position / $request->limit);
            }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $absensiSupirApprovalHeader
            ], 201);
        } catch (QueryException $queryException) {
            if (isset($queryException->errorInfo[1]) && is_array($queryException->errorInfo)) {
                // Check if deadlock
                if ($queryException->errorInfo[1] === 1205) {
                    goto TOP;
                }
            }

            throw $queryException;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
    }
    /**
     * @ClassName 
     */
    public function show(AbsensiSupirApprovalHeader $absensiSupirApprovalHeader, $id)
    {
        $data = $absensiSupirApprovalHeader->find($id);
        $detail = AbsensiSupirApprovalDetail::getAll($id);

        // dd($detail);
        //  $detail = NotaDebetHeaderDetail::findAll($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }
    /**
     * @ClassName 
     */
    public function update(UpdateAbsensiSupirApprovalHeaderRequest $request, AbsensiSupirApprovalHeader $absensiSupirApprovalHeader, $id)
    {
        DB::beginTransaction();

        try {

            $group = 'ABSENSI SUPIR APPROVAL BUKTI';
            $subgroup = 'ABSENSI SUPIR APPROVAL BUKTI';
            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $group = 'JURNAL APPROVAL ABSENSI SUPIR';
            $subgroup = 'KREDIT';
            $coaaproval = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $memokredit = json_decode($coaaproval->memo, true);

            $group = 'JURNAL APPROVAL ABSENSI SUPIR';
            $subgroup = 'DEBET';
            $coadebet = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();


            $coakaskeluar = $memokredit['JURNAL'];

            /* Store header */
            $absensiSupirApprovalHeader = AbsensiSupirApprovalHeader::lockForUpdate()->findOrFail($id);

            $kasgantung = DB::table('kasgantungheader')
                ->select('kasgantungheader.pengeluaran_nobukti', 'kasgantungheader.tglbukti', 'kasgantungdetail.coa')
                ->leftJoin('kasgantungdetail', 'kasgantungheader.id', 'kasgantungdetail.kasgantung_id')
                ->where('kasgantungheader.nobukti', $request->kasgantung_nobukti)
                ->first();

            /* Store header */
            $absensiSupirApprovalHeader->tglbukti =  date('Y-m-d', strtotime($request->tglbukti));
            $absensiSupirApprovalHeader->absensisupir_nobukti =  $request->absensisupir_nobukti;
            // $absensiSupirApprovalHeader->keterangan =  $request->keterangan;
            $absensiSupirApprovalHeader->statusapproval =  4;
            $absensiSupirApprovalHeader->statusformat =  $format->id;
            $absensiSupirApprovalHeader->pengeluaran_nobukti = $kasgantung->pengeluaran_nobukti ?? '0';
            $absensiSupirApprovalHeader->coakaskeluar = $coakaskeluar;
            $absensiSupirApprovalHeader->postingdari =  "ABSENSI SUPIR APPROVAL";
            $absensiSupirApprovalHeader->modifiedby =  auth('api')->user()->name;
            if ($absensiSupirApprovalHeader->save()) {
                $bank = DB::table('bank')->where('coa', $coakaskeluar)->first();

                // $kasgantung = DB::table('kasgantungheader')->where('nobukti', $request->kasgantung_nobukti)->first();
                $absensisupir = AbsensiSupirHeader::where('nobukti', $request->absensisupir_nobukti)->first();
                $kasgantung = KasGantungHeader::where('nobukti', $absensisupir->kasgantung_nobukti)->first();
                // return response($kasgantung, 442);

                $kasgantungdetail = DB::table('kasgantungdetail')->where('nobukti', $absensisupir->kasgantung_nobukti)->get();
                $details = [];
                $total = 0;
                foreach ($kasgantungdetail as $detail) {
                    $details['keterangan'][] = $detail->keterangan;
                    $details['nominal'][] = $detail->nominal;
                    $total += $detail->nominal;
                }

                $dataKasgantung = [
                    "tglbukti" => $kasgantung->tglbukti,
                    // "keterangan" => $absensiSupirApprovalHeader->keterangan,
                    "bank_id" => $bank->id,
                    "penerima_id" => $kasgantung->penerima_id,
                    "coakaskeluar" => $coakaskeluar,
                    "pengeluaran_nobukti" => $absensiSupirApprovalHeader->pengeluaran_nobukti,
                    "postingdari" => 'ENTRY ABSENSI SUPIR APPROVAL',
                    "tglkaskeluar" => $request->tglbukti,
                    'keterangan_detail' => $details['keterangan'],
                    'nominal' => $details['nominal'],
                    "from" => "AbsensiSupirApprovalHeader"
                ];

                $data = new UpdateKasGantungHeaderRequest($dataKasgantung);
                $kasgantungStore = app(KasGantungHeaderController::class)->update($data, $kasgantung);
                $kasgantung = $kasgantungStore->original['data'];


                $absensiSupirApprovalHeader->pengeluaran_nobukti = $kasgantung->pengeluaran_nobukti;
                $absensiSupirApprovalHeader->tglkaskeluar = $kasgantung->tglkaskeluar;

                $absensiSupirApprovalHeader->save();

                $logTrail = [
                    'namatabel' => strtoupper($absensiSupirApprovalHeader->getTable()),
                    'postingdari' => 'EDIT ABSENSI SUPIR APPROVAL HEADER',
                    'idtrans' => $absensiSupirApprovalHeader->id,
                    'nobuktitrans' => $absensiSupirApprovalHeader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $absensiSupirApprovalHeader->toArray(),
                    'modifiedby' => $absensiSupirApprovalHeader->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                if ($request->trado_id) {

                    /* Store detail */
                    $detaillog = [];
                    AbsensiSupirApprovalDetail::where('absensisupirapproval_id', $id)->lockForUpdate()->delete();
                    for ($i = 0; $i < count($request->trado_id); $i++) {
                        $datadetail = [
                            "absensisupirapproval_id" => $absensiSupirApprovalHeader->id,
                            "nobukti" => $absensiSupirApprovalHeader->nobukti,
                            "trado_id" => $request->trado_id[$i],
                            "supir_id" => $request->supir_id[$i],
                            "modifiedby" => auth('api')->user()->name
                        ];
                        $data = new StoreAbsensiSupirApprovalDetailRequest($datadetail);
                        $absensiSupirApprovalDetail = app(AbsensiSupirApprovalDetailController::class)->store($data);

                        if ($absensiSupirApprovalDetail['error']) {
                            return response($absensiSupirApprovalDetail, 422);
                        } else {
                            $iddetail = $absensiSupirApprovalDetail['id'];
                            $tabeldetail = $absensiSupirApprovalDetail['tabel'];
                        }
                        $datadetaillog = [
                            "id" => $iddetail,
                            "absensisupirapproval_id" => $absensiSupirApprovalHeader->id,
                            "nobukti" => $absensiSupirApprovalHeader->nobukti,
                            "trado_id" => $request->trado_id[$i],
                            "supir_id" => $request->supir_id[$i],
                            "modifiedby" => auth('api')->user()->name,
                            'created_at' => date('d-m-Y H:i:s', strtotime($absensiSupirApprovalHeader->created_at)),
                            'updated_at' => date('d-m-Y H:i:s', strtotime($absensiSupirApprovalHeader->updated_at)),
                        ];
                        $detaillog[] = $datadetaillog;
                    }
                    $datalogtrail = [
                        'namatabel' => $tabeldetail,
                        'postingdari' => 'ENTRY ABSENSI SUPIR APPROVAL DETAIL',
                        'idtrans' =>  $iddetail,
                        'nobuktitrans' => $absensiSupirApprovalHeader->nobukti,
                        'aksi' => 'ENTRY',
                        'datajson' => $detaillog,
                        'modifiedby' => auth('api')->user()->name,
                    ];
                    $data = new StoreLogTrailRequest($datalogtrail);
                    app(LogTrailController::class)->store($data);
                }
                DB::commit();
            }
            /* Set position and page */

            $selected = $this->getPosition($absensiSupirApprovalHeader, $absensiSupirApprovalHeader->getTable());
            $absensiSupirApprovalHeader->position = $selected->position;
            $absensiSupirApprovalHeader->page = ceil($absensiSupirApprovalHeader->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $absensiSupirApprovalHeader->page = ceil($absensiSupirApprovalHeader->position / $request->limit);
            }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $absensiSupirApprovalHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }

        return response($request->all(), 442);
    }
    /**
     * @ClassName 
     */
    public function destroy(Request $request, $id)
    {

        DB::beginTransaction();

        $getDetail = AbsensiSupirApprovalDetail::lockForUpdate()->where('absensisupirapproval_id', $id)->get();

        $absensiSupirApprovalHeader = new AbsensiSupirApprovalHeader();
        $absensiSupirApprovalHeader = $absensiSupirApprovalHeader->lockAndDestroy($id);

        $pengeluaran = $absensiSupirApprovalHeader->pengeluaran_nobukti;
        $kasGantung = KasGantungHeader::where('pengeluaran_nobukti', $pengeluaran)->first();
        // return response($kasGantung,422);
        $kasGantung->pengeluaran_nobukti = '';
        $kasGantung->coakaskeluar = '';
        $kasGantung->kasgantungDetail()->update(['coa' => '']);
        $kasGantung->save();

        $getPengeluaranHeader = PengeluaranHeader::lockForUpdate()->where('nobukti', $absensiSupirApprovalHeader->pengeluaran_nobukti)->first();
        $getPengeluaranDetail = PengeluaranHeader::lockForUpdate()->where('nobukti', $absensiSupirApprovalHeader->pengeluaran_nobukti)->get();
        $getJurnalHeader = JurnalUmumHeader::lockForUpdate()->where('nobukti', $absensiSupirApprovalHeader->pengeluaran_nobukti)->first();
        $getJurnalDetail = JurnalUmumDetail::lockForUpdate()->where('nobukti', $absensiSupirApprovalHeader->pengeluaran_nobukti)->get();

        PengeluaranHeader::where('nobukti', $absensiSupirApprovalHeader->pengeluaran_nobukti)->delete();
        JurnalUmumHeader::where('nobukti', $absensiSupirApprovalHeader->pengeluaran_nobukti)->delete();

        if ($absensiSupirApprovalHeader) {
            $logTrail = [
                'namatabel' => strtoupper($absensiSupirApprovalHeader->getTable()),
                'postingdari' => 'DELETE ABSENSI SUPIR APPROVAL',
                'idtrans' => $id,
                'nobuktitrans' => $absensiSupirApprovalHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $absensiSupirApprovalHeader->toArray(),
                'modifiedby' => $absensiSupirApprovalHeader->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE ABSENSI SUPIR APPROVAL DETAIL
            $logTrailAbsensiApprovalDetail = [
                'namatabel' => 'ABSENSISUPIRAPPROVALDETAIL',
                'postingdari' => 'DELETE ABSENSI SUPIR APPROVAL DETAIL',
                'idtrans' => $storedLogTrail['id'] ?? '',
                'nobuktitrans' => $absensiSupirApprovalHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailAbsensiApprovalDetail = new StoreLogTrailRequest($logTrailAbsensiApprovalDetail);
            app(LogTrailController::class)->store($validatedLogTrailAbsensiApprovalDetail);

            // DELETE PENGELUARAN HEADER
            if (isset($getPengeluaranHeader)) {
                $logTrailPengeluaranHeader = [
                    'namatabel' => 'PENGELUARAN',
                    'postingdari' => 'DELETE PENGELUARAN HEADER DARI ABSENSI SUPIR APPROVAL',
                    'idtrans' => $getPengeluaranHeader->id ?? '',
                    'nobuktitrans' => $getPengeluaranHeader->nobukti ?? '',
                    'aksi' => 'DELETE',
                    'datajson' => $getPengeluaranHeader->toArray() ?? [],
                    'modifiedby' => auth('api')->user()->name
                ];
                // dd($logTrailPengeluaranHeader['datajson']);
                $validatedLogTrailPengeluaranHeader = new StoreLogTrailRequest($logTrailPengeluaranHeader);
                $storedLogTrailPengeluaran = app(LogTrailController::class)->store($validatedLogTrailPengeluaranHeader);
            }

            // DELETE PENGELUARAN DETAIL
            if (isset($getPengeluaranDetail)) {

                $logTrailPengeluaranDetail = [
                    'namatabel' => 'PENGELUARANDETAIL',
                    'postingdari' => 'DELETE PENGELUARAN DETAIL DARI ABSENSI SUPIR APPROVAL',
                    'idtrans' => $storedLogTrailPengeluaran['id'] ?? '',
                    'nobuktitrans' => $getPengeluaranHeader->nobukti ?? '',
                    'aksi' => 'DELETE',
                    'datajson' => $getPengeluaranDetail->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrailPengeluaranDetail = new StoreLogTrailRequest($logTrailPengeluaranDetail);
                app(LogTrailController::class)->store($validatedLogTrailPengeluaranDetail);
            }
            // DELETE JURNAL HEADER
            if (isset($getJurnalHeader)) {
                $logTrailJurnalHeader = [
                    'namatabel' => 'JURNALUMUMHEADER',
                    'postingdari' => 'DELETE JURNAL UMUM HEADER DARI ABSENSI SUPIR APPROVAL',
                    'idtrans' => $getJurnalHeader->id ?? '',
                    'nobuktitrans' => $getJurnalHeader->nobukti ?? '',
                    'aksi' => 'DELETE',
                    'datajson' => $getJurnalHeader->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrailJurnalHeader = new StoreLogTrailRequest($logTrailJurnalHeader);
                $storedLogTrailJurnal = app(LogTrailController::class)->store($validatedLogTrailJurnalHeader);
            }
            // DELETE JURNAL DETAIL
            if (isset($getJurnalDetail)) {
                $logTrailJurnalDetail = [
                    'namatabel' => 'JURNALUMUMDETAIL',
                    'postingdari' => 'DELETE JURNAL UMUM DETAIL DARI ABSENSI SUPIR APPROVAL',
                    'idtrans' => $storedLogTrailJurnal['id'] ?? '',
                    'nobuktitrans' => $getJurnalHeader->nobukti ?? '',
                    'aksi' => 'DELETE',
                    'datajson' => $getJurnalDetail->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrailJurnalDetail = new StoreLogTrailRequest($logTrailJurnalDetail);
                app(LogTrailController::class)->store($validatedLogTrailJurnalDetail);
            }
            DB::commit();

            $selected = $this->getPosition($absensiSupirApprovalHeader, $absensiSupirApprovalHeader->getTable(), true);
            $absensiSupirApprovalHeader->position = $selected->position;
            $absensiSupirApprovalHeader->id = $selected->id;
            $absensiSupirApprovalHeader->page = ceil($absensiSupirApprovalHeader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $absensiSupirApprovalHeader
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }
    /**
     * @ClassName 
     */
    public function approval($id)
    {
        DB::beginTransaction();
        $absensiSupirApprovalHeader = AbsensiSupirApprovalHeader::lockForUpdate()->findOrFail($id);
        try {
            $statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            if ($absensiSupirApprovalHeader->statusapproval == $statusApproval->id) {
                $absensiSupirApprovalHeader->statusapproval = $statusNonApproval->id;
            } else {
                $absensiSupirApprovalHeader->statusapproval = $statusApproval->id;
            }

            $absensiSupirApprovalHeader->tglapproval = date('Y-m-d', time());
            $absensiSupirApprovalHeader->userapproval = auth('api')->user()->name;

            if ($absensiSupirApprovalHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($absensiSupirApprovalHeader->getTable()),
                    'postingdari' => 'UN/APPROVE ABSENSI SUPIR APPROVAL',
                    'idtrans' => $absensiSupirApprovalHeader->id,
                    'nobuktitrans' => $absensiSupirApprovalHeader->id,
                    'aksi' => 'UN/APPROVE',
                    'datajson' => $absensiSupirApprovalHeader->toArray(),
                    'modifiedby' => $absensiSupirApprovalHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            return response([
                'message' => 'Berhasil',
                'data' => $absensiSupirApprovalHeader
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function fieldLength(Type $var = null)
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('AbsensiSupirApprovalHeader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function getAbsensi($absensi)
    {
        $absensiSupir = new AbsensiSupirHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $absensiSupir->getAbsensi($absensi),
            // 'data' => $absensi,
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $absensiSupir->totalRows,
                'totalPages' => $absensiSupir->totalPages
            ]
        ]);
    }


    public function cekvalidasi($id)
    {

        $absensisupirapproval = AbsensiSupirApprovalHeader::find($id);
        $statusdatacetak = $absensisupirapproval->statuscetak;
        $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($statusdatacetak == $statusCetak->id) {
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

    public function getApproval($absensi)
    {
        $absensiSupirApprovalHeader = new AbsensiSupirApprovalHeader();
        $absensiSupir = $absensiSupirApprovalHeader->find($absensi);
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $absensiSupirApprovalHeader->getApproval($absensiSupir->absensisupir_nobukti),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $absensiSupirApprovalHeader->totalRows,
                'totalPages' => $absensiSupirApprovalHeader->totalPages
            ]
        ]);
    }
}
