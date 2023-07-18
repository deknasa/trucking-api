<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
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
            $hutangExtraHeader->page = ceil($hutangExtraHeader->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $hutangExtraHeader->page = ceil($hutangExtraHeader->position / ($request->limit ?? 10));
            }

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
            $hutangExtraHeader->page = ceil($hutangExtraHeader->position / ($request->limit ?? 10));

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
     */
    public function destroy(DestroyHutangExtraHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $hutangExtraHeader = (new HutangExtraHeader())->processDestroy($id, 'DELETE HUTANG EXTRA');
            $selected = $this->getPosition($hutangExtraHeader, $hutangExtraHeader->getTable(), true);
            $hutangExtraHeader->position = $selected->position;
            $hutangExtraHeader->id = $selected->id;
            $hutangExtraHeader->page = ceil($hutangExtraHeader->position / ($request->limit ?? 10));

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
     */
    public function report()
    {}
    
    /**
     * @ClassName 
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
                'message' =>  'No Bukti ' . $hutang->nobukti . ' ' . $query->keterangan,
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

}