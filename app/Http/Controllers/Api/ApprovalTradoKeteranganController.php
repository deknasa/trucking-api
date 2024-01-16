<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApprovalTradoKeterangan;
use App\Models\Trado;
use App\Http\Requests\StoreApprovalTradoKeteranganRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateApprovalTradoKeteranganRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ApprovalTradoKeteranganController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $approvalTradoKeterangan = new ApprovalTradoKeterangan();

        $data = $approvalTradoKeterangan->get();
        if (isset(request()->trado_id)) {
            $data = $approvalTradoKeterangan->firstOrFind(request()->trado_id);
        }
        return response([
            'data' =>  $data,
            'attributes' => [
                'totalRows' => $approvalTradoKeterangan->totalRows,
                'totalPages' => $approvalTradoKeterangan->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreApprovalTradoKeteranganRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodetrado' => $request->kodetrado,
                'tglbatas' => $request->tglbatas,
                'statusapproval' => $request->statusapproval
            ];
            $approvalTradoKeterangan = (new ApprovalTradoKeterangan())->processStore($data);
            $selected = $this->getPosition($approvalTradoKeterangan, $approvalTradoKeterangan->getTable());
            $approvalTradoKeterangan->position = $selected->position;
            if ($request->limit == 0) {
                $approvalTradoKeterangan->page = ceil($approvalTradoKeterangan->position / (10));
            } else {
                $approvalTradoKeterangan->page = ceil($approvalTradoKeterangan->position / ($request->limit ?? 10));
            }
            DB::commit();
            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $approvalTradoKeterangan
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(ApprovalTradoKeterangan $approvaltradoketerangan)
    {
        return response([
            'status' => true,
            'data' => $approvaltradoketerangan
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateApprovalTradoKeteranganRequest $request, ApprovalTradoKeterangan $approvaltradoketerangan)
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodetrado' => $request->kodetrado,
                'tglbatas' => $request->tglbatas,
                'statusapproval' => $request->statusapproval
            ];
            $approvaltradoketerangan = (new ApprovalTradoKeterangan())->processUpdate($approvaltradoketerangan, $data);
            $selected = $this->getPosition($approvaltradoketerangan, $approvaltradoketerangan->getTable());
            $approvaltradoketerangan->position = $selected->position;
            if ($request->limit == 0) {
                $approvaltradoketerangan->page = ceil($approvaltradoketerangan->position / (10));
            } else {
                $approvaltradoketerangan->page = ceil($approvaltradoketerangan->position / ($request->limit ?? 10));
            }
            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $approvaltradoketerangan
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
        $approvalTradoKeterangan = new ApprovalTradoKeterangan();
        $approvalTradoKeterangan = $approvalTradoKeterangan->lockAndDestroy($id);
        if ($approvalTradoKeterangan) {
            $trado = Trado::from(DB::raw("trado with (readuncommitted)"))
                ->where('kodetrado', $approvalTradoKeterangan->kodetrado)
                ->first();
            if ($trado != '') {
                $statusNonAktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'NON AKTIF')->first();
                $required = [
                    "kodetrado" => $trado->kodetrado,
                    "tahun" => $trado->tahun,
                    "merek" => $trado->merek,
                    "norangka" => $trado->norangka,
                    "nomesin" => $trado->nomesin,
                    "nama" => $trado->nama,
                    "nostnk" => $trado->nostnk,
                    "alamatstnk" => $trado->alamatstnk,
                    "tglpajakstnk" => $trado->tglpajakstnk,
                    "tipe" => $trado->tipe,
                    "jenis" => $trado->jenis,
                    "isisilinder" => $trado->isisilinder,
                    "warna" => $trado->warna,
                    "jenisbahanbakar" => $trado->jenisbahanbakar,
                    "jumlahsumbu" => $trado->jumlahsumbu,
                    "jumlahroda" => $trado->jumlahroda,
                    "model" => $trado->model,
                    "nobpkb" => $trado->nobpkb,
                    "jumlahbanserap" => $trado->jumlahbanserap,
                ];
                $key = array_keys($required, null);
                if (count($key)) {
                    $trado->statusaktif = $statusNonAktif->id;
                    $trado->save();
                }
            }
            $logTrail = [
                'namatabel' => strtoupper($approvalTradoKeterangan->getTable()),
                'postingdari' => 'DELETE APPROVAL TRADO KETERANGAN',
                'idtrans' => $approvalTradoKeterangan->id,
                'nobuktitrans' => $approvalTradoKeterangan->id,
                'aksi' => 'DELETE',
                'datajson' => $approvalTradoKeterangan->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();


            /* Set position and page */
            $selected = $this->getPosition($approvalTradoKeterangan, $approvalTradoKeterangan->getTable(), true);
            $approvalTradoKeterangan->position = $selected->position;
            $approvalTradoKeterangan->id = $selected->id;
            if ($request->limit == 0) {
                $approvalTradoKeterangan->page = ceil($approvalTradoKeterangan->position / (10));
            } else {
                $approvalTradoKeterangan->page = ceil($approvalTradoKeterangan->position / ($request->limit ?? 10));
            }

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $approvalTradoKeterangan
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('approvaltradoketerangan')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
