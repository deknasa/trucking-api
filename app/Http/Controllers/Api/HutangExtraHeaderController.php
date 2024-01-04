<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalHutangHeaderRequest;
use App\Http\Requests\DestroyHutangExtraHeaderRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Models\HutangExtraHeader;
use App\Http\Requests\StoreHutangExtraHeaderRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateHutangExtraHeaderRequest;
use App\Models\HutangExtraDetail;
use App\Models\Parameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HutangExtraHeaderController extends Controller
{
    /**
     * @ClassName 
     * HutangExtraHeader
     * @Detail1 HutangExtraDetailController
    */
    public function index(GetIndexRangeRequest $request)
    {
        $hutang = new HutangExtraHeader();

        return response([
            'data' => $hutang->get(),
            'attributes' => [
                'totalRows' => $hutang->totalRows,
                'totalPages' => $hutang->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreHutangExtraHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                "tglbukti" => $request->tglbukti,
                "supplier_id" => $request->supplier_id,
                "tgljatuhtempo" => $request->tgljatuhtempo,
                "keterangan_detail" => $request->keterangan_detail,
                "total_detail" => $request->total_detail,
            ];
            /* Store header */
            $hutangExtraHeader = (new HutangExtraHeader())->processStore($data);
            /* Set position and page */
            $hutangExtraHeader->position = $this->getPosition($hutangExtraHeader, $hutangExtraHeader->getTable())->position;
            if ($request->limit==0) {
                $hutangExtraHeader->page = ceil($hutangExtraHeader->position / (10));
            } else {
                $hutangExtraHeader->page = ceil($hutangExtraHeader->position / ($request->limit ?? 10));
            }
            $hutangExtraHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $hutangExtraHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            
            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $hutangExtraHeader
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show($id)
    {
        $data = (new HutangExtraHeader())->findAll($id);
        $detail = (new HutangExtraDetail())->findAll($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateHutangExtraHeaderRequest $request, HutangExtraHeader $hutangextraheader): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                "tglbukti" => $request->tglbukti,
                "supplier_id" => $request->supplier_id,
                "tgljatuhtempo" => $request->tgljatuhtempo,
                "keterangan_detail" => $request->keterangan_detail,
                "total_detail" => $request->total_detail,
            ];
            $hutangExtraHeader = (new HutangExtraHeader())->processUpdate($hutangextraheader, $data);
            $hutangExtraHeader->position = $this->getPosition($hutangExtraHeader, $hutangExtraHeader->getTable())->position;
            if ($request->limit==0) {
                $hutangExtraHeader->page = ceil($hutangExtraHeader->position / (10));
            } else {
                $hutangExtraHeader->page = ceil($hutangExtraHeader->position / ($request->limit ?? 10));
            }
            $hutangExtraHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $hutangExtraHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            
            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $hutangExtraHeader
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
    public function destroy(DestroyHutangExtraHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $hutangExtraHeader = (new HutangExtraHeader())->processDestroy($id, 'DELETE HUTANG EXTRA');
            $selected = $this->getPosition($hutangExtraHeader, $hutangExtraHeader->getTable(), true);
            $hutangExtraHeader->position = $selected->position;
            $hutangExtraHeader->id = $selected->id;
            if ($request->limit==0) {
                $hutangExtraHeader->page = ceil($hutangExtraHeader->position / (10));
            } else {
                $hutangExtraHeader->page = ceil($hutangExtraHeader->position / ($request->limit ?? 10));
            }
            $hutangExtraHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $hutangExtraHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $hutangExtraHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('hutangextraheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
    
     /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report()
    {}
    
    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id)
    {
        $hutangExtraHeader = new HutangExtraHeader();
        return response([
            'data' => $hutangExtraHeader->getExport($id)
        ]);
    }
    
    public function cekvalidasi($id)
    {
        $hutang = HutangExtraHeader::find($id);

        $statusdatacetak = $hutang->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
        if ($statusdatacetak == $statusCetak->id) {
            $query = DB::table('error')
                ->select('keterangan')
                ->where('kodeerror', '=', 'SDC')
                ->first();
           
            $data = [
                'error' => true,
                'message' => $query->keterangan,
                'kodeerror' => 'SDC',
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

    public function cekValidasiAksi($id)
    {
        $hutangExtra = new HutangExtraHeader();
        $nobukti = HutangExtraHeader::from(DB::raw("hutangextraheader"))->where('id', $id)->first();
        $cekdata = $hutangExtra->cekvalidasiaksi($nobukti->hutang_nobukti);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', $cekdata['kodeerror'])
                ->first();
            $data = [
                'error' => true,
                'message' => $query->keterangan,
                'kodeerror' => $cekdata['kodeerror'],
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

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $hutangHeader = HutangExtraHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($hutangHeader->statuscetak != $statusSudahCetak->id) {
                $hutangHeader->statuscetak = $statusSudahCetak->id;
                $hutangHeader->tglbukacetak = date('Y-m-d H:i:s');
                $hutangHeader->userbukacetak = auth('api')->user()->name;
                $hutangHeader->jumlahcetak = $hutangHeader->jumlahcetak + 1;
                if ($hutangHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($hutangHeader->getTable()),
                        'postingdari' => 'PRINT HUTANG EXTRA HEADER',
                        'idtrans' => $hutangHeader->id,
                        'nobuktitrans' => $hutangHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $hutangHeader->toArray(),
                        'modifiedby' => $hutangHeader->modifiedby
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

        /**
     * @ClassName 
     * @Keterangan APPROVAL DATA
     */
    public function approval(ApprovalHutangHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'hutangId' => $request->hutangId
            ];
            $hutangExtraHeader = (new HutangExtraHeader())->processApproval($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

}
