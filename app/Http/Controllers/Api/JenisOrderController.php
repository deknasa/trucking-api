<?php

namespace App\Http\Controllers\Api;

use App\Models\JenisOrder;
use App\Http\Requests\StoreJenisOrderRequest;
use App\Http\Requests\UpdateJenisOrderRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class JenisOrderController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $jenisorder = new JenisOrder();

        return response([
            'data' => $jenisorder->get(),
            'attributes' => [
                'totalRows' => $jenisorder->totalRows,
                'totalPages' => $jenisorder->totalPages
            ]
        ]);
    }
    public function cekValidasi($id) {
        $jenisOrder= new JenisOrder();
        $cekdata=$jenisOrder->cekvalidasihapus($id);
        if ($cekdata['kondisi']==true) {
            $query = DB::table('error')
            ->select(
                DB::raw("ltrim(rtrim(keterangan))+' (".$cekdata['keterangan'].")' as keterangan")
                )
            ->where('kodeerror', '=', 'SATL')
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
    
    public function default()
    {
        $jenisOrder = new JenisOrder();
        return response([
            'status' => true,
            'data' => $jenisOrder->default()
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreJenisOrderRequest $request)
    {
        DB::beginTransaction();

        try {
            $jenisorder = new JenisOrder();
            $jenisorder->kodejenisorder = $request->kodejenisorder;
            $jenisorder->statusaktif = $request->statusaktif;
            $jenisorder->keterangan = $request->keterangan;
            $jenisorder->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($jenisorder->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($jenisorder->getTable()),
                    'postingdari' => 'ENTRY JENIS ORDER',
                    'idtrans' => $jenisorder->id,
                    'nobuktitrans' => $jenisorder->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $jenisorder->toArray(),
                    'modifiedby' => $jenisorder->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($jenisorder, $jenisorder->getTable());
            $jenisorder->position = $selected->position;
            $jenisorder->page = ceil($jenisorder->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $jenisorder
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(JenisOrder $jenisorder)
    {
        return response([
            'status' => true,
            'data' => $jenisorder
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(StoreJenisOrderRequest $request, JenisOrder $jenisorder)
    {
        DB::beginTransaction();
        try {
            $jenisorder->kodejenisorder = $request->kodejenisorder;
            $jenisorder->keterangan = $request->keterangan;
            $jenisorder->statusaktif = $request->statusaktif;
            $jenisorder->modifiedby = auth('api')->user()->name;

            if ($jenisorder->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($jenisorder->getTable()),
                    'postingdari' => 'EDIT JENIS ORDER',
                    'idtrans' => $jenisorder->id,
                    'nobuktitrans' => $jenisorder->id,
                    'aksi' => 'EDIT',
                    'datajson' => $jenisorder->toArray(),
                    'modifiedby' => $jenisorder->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);
                DB::commit();
            }
            /* Set position and page */
            $selected = $this->getPosition($jenisorder, $jenisorder->getTable());
            $jenisorder->position = $selected->position;
            $jenisorder->page = ceil($jenisorder->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $jenisorder
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    /**
     * @ClassName 
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $jenisOrder = new JenisOrder();
        $jenisOrder = $jenisOrder->lockAndDestroy($id);
        if ($jenisOrder) {
            $logTrail = [
                'namatabel' => strtoupper($jenisOrder->getTable()),
                'postingdari' => 'DELETE JENIS ORDER',
                'idtrans' => $jenisOrder->id,
                'nobuktitrans' => $jenisOrder->id,
                'aksi' => 'DELETE',
                'datajson' => $jenisOrder->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);


            DB::commit();
            /* Set position and page */
            $selected = $this->getPosition($jenisOrder, $jenisOrder->getTable(), true);
            $jenisOrder->position = $selected->position;
            $jenisOrder->id = $selected->id;
            $jenisOrder->page = ceil($jenisOrder->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $jenisOrder
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('jenisorder')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function combo(Request $request)
    {
        $data = [
            'statusaktif' => Parameter::where(['grp' => 'status aktif'])->get(),
        ];

        return response([
            'data' => $data
        ]);
    }
}
