<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHariLiburRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\HariLibur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HariLiburController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $hariLibur = new HariLibur();

        return response([
            'data' => $hariLibur->get(),
            'attributes' => [
                'totalRows' => $hariLibur->totalRows,
                'totalPages' => $hariLibur->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreHariLiburRequest $request)
    {
        DB::beginTransaction();

        try {

            $hariLibur = new HariLibur();
            $hariLibur->tgl = date('Y-m-d', strtotime($request->tgl));
            $hariLibur->keterangan = $request->keterangan;
            $hariLibur->statusaktif = $request->statusaktif;
            $hariLibur->modifiedby = auth('api')->user()->name;


            if ($hariLibur->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($hariLibur->getTable()),
                    'postingdari' => 'ENTRY HARI LIBUR',
                    'idtrans' => $hariLibur->id,
                    'nobuktitrans' => $hariLibur->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $hariLibur->toArray(),
                    'modifiedby' => $hariLibur->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            }
            DB::commit();
            $selected = $this->getPosition($hariLibur, $hariLibur->getTable());
            $hariLibur->position = $selected->position;
            $hariLibur->page = ceil($hariLibur->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $hariLibur
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $hariLibur = HariLibur::where('id', $id)->first();
        return response([
            'status' => true,
            'data' => $hariLibur
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(StoreHariLiburRequest $request, HariLibur $harilibur)
    {
        DB::beginTransaction();

        try {
            $harilibur->tgl = date('Y-m-d', strtotime($request->tgl));
            $harilibur->keterangan = $request->keterangan;
            $harilibur->statusaktif = $request->statusaktif;
            $harilibur->modifiedby = auth('api')->user()->name;

            if ($harilibur->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($harilibur->getTable()),
                    'postingdari' => 'EDIT HARI LIBUR',
                    'idtrans' => $harilibur->id,
                    'nobuktitrans' => $harilibur->id,
                    'aksi' => 'EDIT',
                    'datajson' => $harilibur->toArray(),
                    'modifiedby' => $harilibur->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);
            }
            DB::commit();
            $selected = $this->getPosition($harilibur, $harilibur->getTable());
            $harilibur->position = $selected->position;
            $harilibur->page = ceil($harilibur->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $harilibur
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function destroy(HariLibur $harilibur, Request $request)
    {
        DB::beginTransaction();

        try {

            $isDelete = HariLibur::where('id', $harilibur->id)->delete();
            if ($isDelete) {
                $logTrail = [
                    'namatabel' => strtoupper($harilibur->getTable()),
                    'postingdari' => 'DELETE HARI LIBUR',
                    'idtrans' => $harilibur->id,
                    'nobuktitrans' => $harilibur->id,
                    'aksi' => 'DELETE',
                    'datajson' => $harilibur->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
                /* Set position and page */

                $selected = $this->getPosition($harilibur, $harilibur->getTable(), true);

                $harilibur->position = $selected->position;
                $harilibur->id = $selected->id;
                $harilibur->page = ceil($harilibur->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $harilibur
                ]);
            }
            return response([
                'message' => 'Gagal dihapus'
            ], 500);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('harilibur')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
