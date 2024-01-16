<?php

namespace App\Http\Controllers\Api;

use App\Models\Trado;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ApprovalTradoGambar;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreApprovalTradoGambarRequest;
use App\Http\Requests\UpdateApprovalTradoGambarRequest;

class ApprovalTradoGambarController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $approvalTradoGambar = new ApprovalTradoGambar();
        $data = $approvalTradoGambar->get();
        if (isset(request()->trado_id)) {
            $data = $approvalTradoGambar->firstOrFind(request()->trado_id);
        }
        return response([
            'data' => $data,
            'attributes' => [
                'totalRows' => $approvalTradoGambar->totalRows,
                'totalPages' => $approvalTradoGambar->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreApprovalTradoGambarRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodetrado' => $request->kodetrado,
                'tglbatas' => $request->tglbatas,
                'statusapproval' => $request->statusapproval
            ];
            $approvalTradoGambar = (new ApprovalTradoGambar())->processStore($data);
            // $selected = $this->getPosition($approvalTradoGambar, $approvalTradoGambar->getTable());
            // $approvalTradoGambar->position = $selected->position;
            // if ($request->limit == 0) {
            //     $approvalTradoGambar->page = ceil($approvalTradoGambar->position / (10));
            // } else {
            //     $approvalTradoGambar->page = ceil($approvalTradoGambar->position / ($request->limit ?? 10));
            // }
            DB::commit();
            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $approvalTradoGambar
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(ApprovalTradoGambar $approvaltradogambar)
    {
        return response([
            'status' => true,
            'data' => $approvaltradogambar
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateApprovalTradoGambarRequest $request, ApprovalTradoGambar $approvaltradogambar)
    {
        DB::beginTransaction();
        try {
            
            $data = [
                'kodetrado' => $request->kodetrado,
                'tglbatas' => $request->tglbatas,
                'statusapproval' => $request->statusapproval
            ];
            $approvaltradogambar = (new ApprovalTradoGambar())->processUpdate($approvaltradogambar, $data);
            // $selected = $this->getPosition($approvaltradogambar, $approvaltradogambar->getTable());
            // $approvaltradogambar->position = $selected->position;
            // if ($request->limit == 0) {
            //     $approvaltradogambar->page = ceil($approvaltradogambar->position / (10));
            // } else {
            //     $approvaltradogambar->page = ceil($approvaltradogambar->position / ($request->limit ?? 10));
            // }

            DB::commit();
            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $approvaltradogambar
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        $approvalTradoGambar = new ApprovalTradoGambar();
        $approvalTradoGambar = $approvalTradoGambar->lockAndDestroy($id);
        if ($approvalTradoGambar) {
            $trado = DB::table('trado')->from(DB::raw("trado with (readuncommitted)"))
                ->where('kodetrado', $approvalTradoGambar->kodetrado)
                ->first();
            if ($trado != '') {
                $statusNonAktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'NON AKTIF')->first();

                if ($trado->photostnk == '' || $trado->phototrado == '' || $trado->photobpkb == '') {
                    DB::table('trado')->where('kodetrado', $approvalTradoGambar->kodetrado)->update([
                        'statusaktif' => $statusNonAktif->id,
                    ]);
                    goto selesai;
                } else {
                    foreach (json_decode($trado->photobpkb) as $value) {
                        if (!Storage::exists("trado/bpkb/$value")) {
                            DB::table('trado')->where('kodetrado', $approvalTradoGambar->kodetrado)->update([
                                'statusaktif' => $statusNonAktif->id,
                            ]);
                            goto selesai;
                        }
                    }
                    foreach (json_decode($trado->photostnk) as $value) {
                        if (!Storage::exists("trado/stnk/$value")) {
                            DB::table('trado')->where('kodetrado', $approvalTradoGambar->kodetrado)->update([
                                'statusaktif' => $statusNonAktif->id,
                            ]);
                            goto selesai;
                        }
                    }
                    foreach (json_decode($trado->phototrado) as $value) {
                        if (!Storage::exists("trado/trado/$value")) {
                            DB::table('trado')->where('kodetrado', $approvalTradoGambar->kodetrado)->update([
                                'statusaktif' => $statusNonAktif->id,
                            ]);
                            goto selesai;
                        }
                    }
                }
                selesai:
            }
            $logTrail = [
                'namatabel' => strtoupper($approvalTradoGambar->getTable()),
                'postingdari' => 'DELETE APPROVAL TRADO GAMBAR',
                'idtrans' => $approvalTradoGambar->id,
                'nobuktitrans' => $approvalTradoGambar->id,
                'aksi' => 'DELETE',
                'datajson' => $approvalTradoGambar->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();


            /* Set position and page */
            $selected = $this->getPosition($approvalTradoGambar, $approvalTradoGambar->getTable(), true);
            $approvalTradoGambar->position = $selected->position;
            $approvalTradoGambar->id = $selected->id;
            if ($request->limit == 0) {
                $approvalTradoGambar->page = ceil($approvalTradoGambar->position / (10));
            } else {
                $approvalTradoGambar->page = ceil($approvalTradoGambar->position / ($request->limit ?? 10));
            }

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $approvalTradoGambar
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('approvaltradogambar')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
