<?php

namespace App\Http\Controllers\Api;

use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use App\Models\RekapPengeluaranHeader;
use App\Http\Requests\StoreRekapPengeluaranHeaderRequest;
use App\Http\Requests\UpdateRekapPengeluaranHeaderRequest;

use App\Models\RekapPengeluaranDetail;
use App\Models\PengeluaranHeader;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\StoreRekapPengeluaranDetailRequest;
use App\Http\Requests\StoreLogTrailRequest;


class RekapPengeluaranHeaderController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $rekapPengeluaranHeader = new RekapPengeluaranHeader();
        return response([
            'data' => $rekapPengeluaranHeader->get(),
            'attributes' => [
                'totalRows' => $rekapPengeluaranHeader->totalRows,
                'totalPages' => $rekapPengeluaranHeader->totalPages
            ]
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreRekapPengeluaranHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            $group = 'REKAP PENGELUARAN BUKTI';
            $subgroup = 'REKAP PENGELUARAN BUKTI';

            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();
            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'rekappengeluaranheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
            $statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            $rekapPengeluaranHeader = new RekapPengeluaranHeader();

            $rekapPengeluaranHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $rekapPengeluaranHeader->tgltransaksi  = date('Y-m-d', strtotime($request->tgltransaksi));
            $rekapPengeluaranHeader->bank_id = $request->bank_id;
            $rekapPengeluaranHeader->statusapproval = $statusNonApproval->id;
            $rekapPengeluaranHeader->userapproval = auth('api')->user()->name;
            $rekapPengeluaranHeader->statusformat = $format->id;
            $rekapPengeluaranHeader->modifiedby = auth('api')->user()->name;
            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $rekapPengeluaranHeader->nobukti = $nobukti;

            if ($rekapPengeluaranHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($rekapPengeluaranHeader->getTable()),
                    'postingdari' => 'ENTRY REKAP PENGELUARAN HEADER',
                    'idtrans' => $rekapPengeluaranHeader->id,
                    'nobuktitrans' => $rekapPengeluaranHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $rekapPengeluaranHeader->toArray(),
                    'modifiedby' => $rekapPengeluaranHeader->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                /* Store detail */

                if ($request->pengeluaran_nobukti) {
                    $rekapPengeluaranDetail = RekapPengeluaranDetail::where('rekappengeluaran_id', $rekapPengeluaranHeader->id)->lockForUpdate()->delete();

                    $detaillog = [];
                    for ($i = 0; $i < count($request->pengeluaran_nobukti); $i++) {
                        $datadetail = [
                            "rekappengeluaran_id" => $rekapPengeluaranHeader->id,
                            "nobukti" =>  $rekapPengeluaranHeader->nobukti,
                            "tgltransaksi" => $request->tgltransaksi_detail[$i],
                            "pengeluaran_nobukti" => $request->pengeluaran_nobukti[$i],
                            "nominal" => $request->nominal[$i],
                            // "keterangandetail" => $request->keterangan_detail[$i],
                            "modifiedby" => $rekapPengeluaranHeader->modifiedby = auth('api')->user()->name
                        ];

                        $detaillog[] = $datadetail;
                        $data = new StoreRekapPengeluaranDetailRequest($datadetail);
                        $rekapPengeluaranDetail = app(RekapPengeluaranDetailController::class)->store($data);

                        if ($rekapPengeluaranDetail['error']) {
                            return response($rekapPengeluaranDetail, 422);
                        } else {
                            $iddetail = $rekapPengeluaranDetail['id'];
                            $tabeldetail = $rekapPengeluaranDetail['tabel'];
                        }
                    }
                    $datalogtrail = [
                        'namatabel' => $tabeldetail,
                        'postingdari' => 'ENTRY REKAP PENGELUARAN DETAIL',
                        'idtrans' =>  $iddetail,
                        'nobuktitrans' => $rekapPengeluaranHeader->nobukti,
                        'aksi' => 'ENTRY',
                        'datajson' => $detaillog,
                        'modifiedby' => auth('api')->user()->name,
                    ];
                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                    DB::commit();
                }
            }

            /* Set position and page */
            $selected = $this->getPosition($rekapPengeluaranHeader, $rekapPengeluaranHeader->getTable());
            $rekapPengeluaranHeader->position = $selected->position;
            $rekapPengeluaranHeader->page = ceil($rekapPengeluaranHeader->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $rekapPengeluaranHeader->page = ceil($rekapPengeluaranHeader->position / $request->limit);
            }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $rekapPengeluaranHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
    }
    public function show(RekapPengeluaranHeader $rekapPengeluaranHeader, $id)
    {
        $data = $rekapPengeluaranHeader->find($id);

        return response([
            'status' => true,
            'data' => $data,
        ]);
    }
    /**
     * @ClassName 
     */
    public function update(UpdateRekapPengeluaranHeaderRequest $request, RekapPengeluaranHeader $rekapPengeluaranHeader, $id)
    {
        DB::beginTransaction();

        try {
            $statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            $rekapPengeluaranHeader = RekapPengeluaranHeader::lockForUpdate()->findOrFail($id);

            $rekapPengeluaranHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $rekapPengeluaranHeader->tgltransaksi  = date('Y-m-d', strtotime($request->tgltransaksi));
            $rekapPengeluaranHeader->statusapproval = $statusNonApproval->id;
            $rekapPengeluaranHeader->userapproval = auth('api')->user()->name;
            $rekapPengeluaranHeader->bank_id = $request->bank_id;
            $rekapPengeluaranHeader->modifiedby = auth('api')->user()->name;

            if ($rekapPengeluaranHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($rekapPengeluaranHeader->getTable()),
                    'postingdari' => 'EDIT REKAP PENGELUARAN HEADER',
                    'idtrans' => $rekapPengeluaranHeader->id,
                    'nobuktitrans' => $rekapPengeluaranHeader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $rekapPengeluaranHeader->toArray(),
                    'modifiedby' => $rekapPengeluaranHeader->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                /* Store detail */

                if ($request->pengeluaran_nobukti) {
                    $rekapPengeluaranDetail = RekapPengeluaranDetail::where('rekappengeluaran_id', $rekapPengeluaranHeader->id)->lockForUpdate()->delete();

                    $detaillog = [];
                    for ($i = 0; $i < count($request->pengeluaran_nobukti); $i++) {
                        $datadetail = [
                            "rekappengeluaran_id" => $rekapPengeluaranHeader->id,
                            "nobukti" =>  $rekapPengeluaranHeader->nobukti,
                            "tgltransaksi" => $request->tgltransaksi_detail[$i],
                            "pengeluaran_nobukti" => $request->pengeluaran_nobukti[$i],
                            "nominal" => $request->nominal[$i],
                            // "keterangandetail" => $request->keterangan_detail[$i],
                            "modifiedby" => $rekapPengeluaranHeader->modifiedby = auth('api')->user()->name
                        ];

                        $detaillog[] = $datadetail;
                        $data = new StoreRekapPengeluaranDetailRequest($datadetail);
                        $rekapPengeluaranDetail = app(RekapPengeluaranDetailController::class)->store($data);

                        if ($rekapPengeluaranDetail['error']) {
                            return response($rekapPengeluaranDetail, 422);
                        } else {
                            $iddetail = $rekapPengeluaranDetail['id'];
                            $tabeldetail = $rekapPengeluaranDetail['tabel'];
                        }
                    }
                    $datalogtrail = [
                        'namatabel' => $tabeldetail,
                        'postingdari' => 'EDIT REKAP PENGELUARAN DETAIL',
                        'idtrans' =>  $iddetail,
                        'nobuktitrans' => $rekapPengeluaranHeader->nobukti,
                        'aksi' => 'EDIT',
                        'datajson' => $detaillog,
                        'modifiedby' => auth('api')->user()->name,
                    ];
                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                    DB::commit();
                }
            }

            /* Set position and page */
            $selected = $this->getPosition($rekapPengeluaranHeader, $rekapPengeluaranHeader->getTable());
            $rekapPengeluaranHeader->position = $selected->position;
            $rekapPengeluaranHeader->page = ceil($rekapPengeluaranHeader->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $rekapPengeluaranHeader->page = ceil($rekapPengeluaranHeader->position / $request->limit);
            }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $rekapPengeluaranHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
        return response([
            'message' => 'Berhasil gagal disimpan',
        ], 422);
    }
    /**
     * @ClassName 
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        $getDetail = RekapPengeluaranDetail::lockForUpdate()->where('rekappengeluaran_id', $id)->get();
        $rekapPengeluaranHeader = new RekapPengeluaranHeader();
        $rekapPengeluaranHeader = $rekapPengeluaranHeader->lockAndDestroy($id);
        if ($rekapPengeluaranHeader) {
            $logTrail = [
                'namatabel' => strtoupper($rekapPengeluaranHeader->getTable()),
                'postingdari' => 'DELETE Rekap Pengeluaran Header',
                'idtrans' => $id,
                'nobuktitrans' => $rekapPengeluaranHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $rekapPengeluaranHeader->toArray(),
                'modifiedby' => $rekapPengeluaranHeader->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE PENGELUARAN DETAIL
            $logTrailPengeluaranDetail = [
                'namatabel' => 'REKAPPENGELUARANDETAIL',
                'postingdari' => 'DELETE REKAP PENGELUARAN DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $rekapPengeluaranHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailPengeluaranDetail = new StoreLogTrailRequest($logTrailPengeluaranDetail);
            app(LogTrailController::class)->store($validatedLogTrailPengeluaranDetail);
            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($rekapPengeluaranHeader, $rekapPengeluaranHeader->getTable(), true);
            $rekapPengeluaranHeader->position = $selected->position;
            $rekapPengeluaranHeader->id = $selected->id;
            $rekapPengeluaranHeader->page = ceil($rekapPengeluaranHeader->position / ($selected->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $rekapPengeluaranHeader
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
        $rekapPengeluaranHeader = RekapPengeluaranHeader::lockForUpdate()->findOrFail($id);
        try {
            $statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            if ($rekapPengeluaranHeader->statusapproval == $statusApproval->id) {
                $rekapPengeluaranHeader->statusapproval = $statusNonApproval->id;
            } else {
                $rekapPengeluaranHeader->statusapproval = $statusApproval->id;
            }

            $rekapPengeluaranHeader->tglapproval = date('Y-m-d', time());
            $rekapPengeluaranHeader->userapproval = auth('api')->user()->name;

            if ($rekapPengeluaranHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($rekapPengeluaranHeader->getTable()),
                    'postingdari' => 'UN/APPROVE REKAP PENGELUARAN HEADER',
                    'idtrans' => $rekapPengeluaranHeader->id,
                    'nobuktitrans' => $rekapPengeluaranHeader->nobukti,
                    'aksi' => 'UN/APPROVE',
                    'datajson' => $rekapPengeluaranHeader->toArray(),
                    'modifiedby' => $rekapPengeluaranHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            return response([
                'message' => 'Berhasil',
                'data' => $rekapPengeluaranHeader
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getPengeluaran(Request $request)
    {
        $pengeluaran = new PengeluaranHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $pengeluaran->getRekapPengeluaranHeader($request->bank, date('Y-m-d', strtotime($request->tglbukti))),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $pengeluaran->totalRows,
                'totalPages' => $pengeluaran->totalPages
            ]
        ]);
    }

    public function getRekapPengeluaran($id)
    {
        $rekapPengeluaran = new RekapPengeluaranHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $rekapPengeluaran->getRekapPengeluaranHeader($id),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $rekapPengeluaran->totalRows,
                'totalPages' => $rekapPengeluaran->totalPages
            ]
        ]);
    }
}
