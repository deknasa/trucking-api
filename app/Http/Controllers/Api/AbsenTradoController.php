<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAbsenTradoRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateAbsenTradoRequest;
use App\Models\AbsenTrado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class AbsenTradoController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $absenTrado = new AbsenTrado();

        return response([
            'data' => $absenTrado->get(),
            'attributes' => [
                'totalRows' => $absenTrado->totalRows,
                'totalPages' => $absenTrado->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreAbsenTradoRequest $request)
    {
        DB::beginTransaction();

        try {
            $absenTrado = new AbsenTrado();
            $absenTrado->kodeabsen = $request->kodeabsen;
            $absenTrado->keterangan = $request->keterangan;
            $absenTrado->statusaktif = $request->statusaktif;
            $absenTrado->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($absenTrado->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($absenTrado->getTable()),
                    'postingdari' => 'ENTRY ABSEN TRADO',
                    'idtrans' => $absenTrado->id,
                    'nobuktitrans' => $absenTrado->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $absenTrado->toArray(),
                    'modifiedby' => $absenTrado->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($absenTrado, $absenTrado->getTable());
            $absenTrado->position = $selected->position;
            $absenTrado->page = ceil($absenTrado->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $absenTrado
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function show(AbsenTrado $absenTrado)
    {
        return response([
            'status' => true,
            'data' => $absenTrado
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateAbsenTradoRequest $request, AbsenTrado $absenTrado)
    {
        DB::beginTransaction();

        try {
            $absenTrado->kodeabsen = $request->kodeabsen;
            $absenTrado->keterangan = $request->keterangan;
            $absenTrado->statusaktif = $request->statusaktif;
            $absenTrado->modifiedby = auth('api')->user()->name;

            if ($absenTrado->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($absenTrado->getTable()),
                    'postingdari' => 'EDIT ABSEN TRADO',
                    'idtrans' => $absenTrado->id,
                    'nobuktitrans' => $absenTrado->id,
                    'aksi' => 'EDIT',
                    'datajson' => $absenTrado->toArray(),
                    'modifiedby' => $absenTrado->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                /* Set position and page */
                $selected = $this->getPosition($absenTrado, $absenTrado->getTable());
                $absenTrado->position = $selected->position;
                $absenTrado->page = ceil($absenTrado->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $absenTrado
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    /**
     * @ClassName 
     */
    public function destroy(AbsenTrado $absenTrado, Request $request)
    {
        DB::beginTransaction();
        try {
            $delete = AbsenTrado::destroy($absenTrado->id);

            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($absenTrado->getTable()),
                    'postingdari' => 'DELETE ABSEN TRADO',
                    'idtrans' => $absenTrado->id,
                    'nobuktitrans' => $absenTrado->id,
                    'aksi' => 'DELETE',
                    'datajson' => $absenTrado->toArray(),
                    'modifiedby' => $absenTrado->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                $selected = $this->getPosition($absenTrado, $absenTrado->getTable(), true);
                $absenTrado->position = $selected->position;
                $absenTrado->id = $selected->id;
                $absenTrado->page = ceil($absenTrado->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $absenTrado
                ]);
            } 
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('absentrado')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
