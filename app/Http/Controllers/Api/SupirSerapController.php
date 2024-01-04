<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalSupirSerapRequest;
use App\Models\SupirSerap;
use App\Http\Requests\StoreSupirSerapRequest;
use App\Http\Requests\UpdateSupirSerapRequest;
use App\Models\Error;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class SupirSerapController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $supirSerap = new SupirSerap();
        return response([
            'data' => $supirSerap->get(),
            'attributes' => [
                'totalRows' => $supirSerap->totalRows,
                'totalPages' => $supirSerap->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreSupirSerapRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'tglabsensi' => $request->tglabsensi,
                'trado_id' => $request->trado_id,
                'supir_id' => $request->supir_id,
                'supirserap_id' => $request->supirserap_id,
            ];
            $supirSerap = (new SupirSerap())->processStore($data);
            $supirSerap->position = $this->getPosition($supirSerap, $supirSerap->getTable())->position;
            if ($request->limit == 0) {
                $supirSerap->page = ceil($supirSerap->position / (10));
            } else {
                $supirSerap->page = ceil($supirSerap->position / ($request->limit ?? 10));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $supirSerap
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $supirSerap = (new SupirSerap())->findAll($id);
        return response([
            'data' => $supirSerap
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateSupirSerapRequest $request, SupirSerap $supirserap)
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglabsensi' => $request->tglabsensi,
                'trado_id' => $request->trado_id,
                'supir_id' => $request->supir_id,
                'supirserap_id' => $request->supirserap_id,
            ];

            $supirSerap = (new SupirSerap())->processUpdate($supirserap, $data);
            $supirSerap->position = $this->getPosition($supirSerap, $supirSerap->getTable())->position;
            if ($request->limit == 0) {
                $supirSerap->page = ceil($supirSerap->position / (10));
            } else {
                $supirSerap->page = ceil($supirSerap->position / ($request->limit ?? 10));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $supirSerap
            ]);
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

        try {
            $supirSerap = (new SupirSerap())->processDestroy($id);
            $selected = $this->getPosition($supirSerap, $supirSerap->getTable(), true);
            $supirSerap->position = $selected->position;
            $supirSerap->id = $selected->id;
            if ($request->limit == 0) {
                $supirSerap->page = ceil($supirSerap->position / (10));
            } else {
                $supirSerap->page = ceil($supirSerap->position / ($request->limit ?? 10));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $supirSerap
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

     /**
     * @ClassName 
     * @Keterangan APRROVAL DATA
     */
    public function approval(ApprovalSupirSerapRequest $request)
    {
        DB::beginTransaction();

        try {

            $data = [
                'serapId' => $request->serapId
            ];
            $supirSerap = (new SupirSerap())->processApproval($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report()
    {
        //
    }
    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $supirSerap = (new SupirSerap())->get();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();
        return response([
            'data' => $supirSerap,
            'judul' => $getJudul->text
        ]);
    }
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('supirserap')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function cekvalidasi($id)
    {
        $supirSerap = SupirSerap::find($id);
        $status = $supirSerap->statusapproval;
        $statusApproval = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();

        if ($status == $statusApproval->id) {
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->where('kodeerror', '=', 'SAP')
                ->first();
            $data = [
                'error' => true,
                'message' => $query->keterangan,
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }
}
