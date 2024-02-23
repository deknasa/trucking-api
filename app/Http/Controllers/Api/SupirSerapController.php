<?php

namespace App\Http\Controllers\Api;

use Throwable;
use App\Models\Error;
use App\Models\Parameter;
use App\Models\SupirSerap;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupirSerapRequest;
use App\Http\Requests\UpdateSupirSerapRequest;
use App\Http\Requests\ApprovalSupirSerapRequest;

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
            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            for ($i = 0; $i < count($data['serapId']); $i++) {
                $supirSerap = (new SupirSerap())->processApproval(["serapId"=>$data['serapId'][$i]]);
                if ($supirSerap) {
                    if ($supirSerap->statusapproval == $statusApproval->id) {
                        (new SupirSerap())->processStoreToAbsensi($supirSerap);
                    }else{
                        (new SupirSerap())->processDestroyToAbsensi($supirSerap);
                    }
                }
            }

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

            $query = DB::table('absensisupirdetail')->from(DB::raw("absensisupirdetail as detail with (readuncommitted)"))
                ->select('header.nobukti')
                ->whereRaw("detail.trado_id = $supirSerap->trado_id and header.tglbukti = '$supirSerap->tglabsensi' and (detail.supir_id = $supirSerap->supirserap_id or detail.supirold_id = $supirSerap->supirserap_id)")
                ->leftJoin(DB::raw("absensisupirheader as header with (readuncommitted)"), 'header.id', 'detail.absensi_id')
                ->first();

            if ($query != '') {

                $data = DB::table("supirserap")->from(DB::raw("supirserap with (readuncommitted)"))
                    ->select(DB::raw("supirserap.trado_id, supirserap.tglabsensi, supirserap.supirserap_id, trado.kodetrado, supir.namasupir"))
                    ->leftJoin(DB::raw("trado with (readuncommitted)"), 'supirserap.trado_id', 'trado.id')
                    ->leftJoin(DB::raw("supir with (readuncommitted)"), 'supirserap.supirserap_id', 'supir.id')
                    ->where('supirserap.id', $id)
                    ->first();
                $keterangan = 'supir serap ' . $data->namasupir . ' di trado ' . $data->kodetrado . ' tgl ' . date('d-m-Y', strtotime($data->tglabsensi)) . ' SUDAH DIINPUT DI ABSENSI ' . $query->nobukti;
               
                $data = [
                    'error' => true,
                    'message' => $keterangan,
                    'statuspesan' => 'warning',
                ];
            } else {

                $data = [
                    'error' => false,
                    'message' => '',
                    'statuspesan' => 'success',
                ];
            }
            return response($data);
        }
    }
}
