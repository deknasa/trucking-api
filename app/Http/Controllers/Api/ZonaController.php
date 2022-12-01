<?php

namespace App\Http\Controllers\Api;

use App\Models\Zona;
use App\Http\Requests\StoreZonaRequest;
use App\Http\Requests\UpdateZonaRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ZonaController extends Controller
{

    /**
     * @ClassName 
     */
    public function index()
    {
        $zona = new Zona();

        return response([
            'data' => $zona->get(),
            'attributes' => [
                'totalRows' => $zona->totalRows,
                'totalPages' => $zona->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreZonaRequest $request)
    {
        DB::beginTransaction();

        try {
            $zona = new Zona();
            $zona->zona = $request->zona;
            $zona->statusaktif = $request->statusaktif;
            $zona->keterangan = $request->keterangan;
            $zona->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($zona->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($zona->getTable()),
                    'postingdari' => 'ENTRY ZONA',
                    'idtrans' => $zona->id,
                    'nobuktitrans' => $zona->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $zona->toArray(),
                    'modifiedby' => $zona->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($zona, $zona->getTable());
            $zona->position = $selected->position;
            $zona->page = ceil($zona->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $zona
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Zona $zona)
    {
        return response([
            'status' => true,
            'data' => $zona
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(StoreZonaRequest $request, Zona $zona)
    {
        try {
            $zona->zona = $request->zona;
            $zona->keterangan = $request->keterangan;
            $zona->statusaktif = $request->statusaktif;
            $zona->modifiedby = auth('api')->user()->name;

            if ($zona->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($zona->getTable()),
                    'postingdari' => 'EDIT ZONA',
                    'idtrans' => $zona->id,
                    'nobuktitrans' => $zona->id,
                    'aksi' => 'EDIT',
                    'datajson' => $zona->toArray(),
                    'modifiedby' => $zona->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);


                /* Set position and page */
                $selected = $this->getPosition($zona, $zona->getTable());
                $zona->position = $selected->position;
                $zona->page = ceil($zona->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $zona
                ]);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Gagal diubah'
                ]);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function destroy(Zona $zona, Request $request)
    {
        $delete = Zona::destroy($zona->id);
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($zona->getTable()),
                'postingdari' => 'DELETE ZONA',
                'idtrans' => $zona->id,
                'nobuktitrans' => $zona->id,
                'aksi' => 'DELETE',
                'datajson' => $zona->toArray(),
                'modifiedby' => $zona->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $selected = $this->getPosition($zona, $zona->getTable(), true);
            $zona->position = $selected->position;
            $zona->id = $selected->id;
            $zona->page = ceil($zona->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $zona
            ]);
        } else {
            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('zona')->getColumns();

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
