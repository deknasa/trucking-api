<?php

namespace App\Http\Controllers\Api;

use App\Models\Mandor;
use App\Http\Requests\StoreMandorRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class MandorController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $mandor = new Mandor();

        return response([
            'data' => $mandor->get(),
            'attributes' => [
                'totalRows' => $mandor->totalRows,
                'totalPages' => $mandor->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreMandorRequest $request)
    {
        DB::beginTransaction();

        try {
            $mandor = new Mandor();
            $mandor->namamandor = $request->namamandor;
            $mandor->keterangan = $request->keterangan;
            $mandor->statusaktif = $request->statusaktif;
            $mandor->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($mandor->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($mandor->getTable()),
                    'postingdari' => 'ENTRY MANDOR',
                    'idtrans' => $mandor->id,
                    'nobuktitrans' => $mandor->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $mandor->toArray(),
                    'modifiedby' => $mandor->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($mandor, $mandor->getTable());
            $mandor->position = $selected->position;
            $mandor->page = ceil($mandor->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $mandor
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Mandor $mandor)
    {
        return response([
            'status' => true,
            'data' => $mandor
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(StoreMandorRequest $request, Mandor $mandor)
    {
        DB::beginTransaction();
        try {
            $mandor->namamandor = $request->namamandor;
            $mandor->keterangan = $request->keterangan;
            $mandor->statusaktif = $request->statusaktif;
            $mandor->modifiedby = auth('api')->user()->name;

            if ($mandor->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($mandor->getTable()),
                    'postingdari' => 'EDIT MANDOR',
                    'idtrans' => $mandor->id,
                    'nobuktitrans' => $mandor->id,
                    'aksi' => 'EDIT',
                    'datajson' => $mandor->toArray(),
                    'modifiedby' => $mandor->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);


                DB::commit();
            }
            /* Set position and page */
            $selected = $this->getPosition($mandor, $mandor->getTable());
            $mandor->position = $selected->position;
            $mandor->page = ceil($mandor->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $mandor
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

        $mandor = new Mandor();
        $mandor = $mandor->lockAndDestroy($id);

        if ($mandor) {
            $logTrail = [
                'namatabel' => strtoupper($mandor->getTable()),
                'postingdari' => 'DELETE MANDOR',
                'idtrans' => $mandor->id,
                'nobuktitrans' => $mandor->id,
                'aksi' => 'DELETE',
                'datajson' => $mandor->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);


            DB::commit();
            $selected = $this->getPosition($mandor, $mandor->getTable(), true);
            $mandor->position = $selected->position;
            $mandor->id = $selected->id;
            $mandor->page = ceil($mandor->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $mandor
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('mandor')->getColumns();

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
