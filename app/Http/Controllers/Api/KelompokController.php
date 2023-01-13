<?php

namespace App\Http\Controllers\Api;

use App\Models\Kelompok;
use App\Http\Requests\StoreKelompokRequest;
use App\Http\Requests\UpdateKelompokRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class KelompokController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $kelompok = new Kelompok();

        return response([
            'data' => $kelompok->get(),
            'attributes' => [
                'totalRows' => $kelompok->totalRows,
                'totalPages' => $kelompok->totalPages
            ]
        ]);
    }

    public function default()
    {
        $kelompok = new Kelompok();
        return response([
            'status' => true,
            'data' => $kelompok->default()
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreKelompokRequest $request)
    {
        DB::beginTransaction();

        try {
            $kelompok = new Kelompok();
            $kelompok->kodekelompok = $request->kodekelompok;
            $kelompok->keterangan = $request->keterangan;
            $kelompok->statusaktif = $request->statusaktif;
            $kelompok->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($kelompok->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($kelompok->getTable()),
                    'postingdari' => 'ENTRY KELOMPOK',
                    'idtrans' => $kelompok->id,
                    'nobuktitrans' => $kelompok->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $kelompok->toArray(),
                    'modifiedby' => $kelompok->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            $selected = $this->getPosition($kelompok, $kelompok->getTable());

            $kelompok->position = $selected->position;
            $kelompok->page = ceil($kelompok->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $kelompok
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Kelompok $kelompok)
    {
        return response([
            'status' => true,
            'data' => $kelompok
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateKelompokRequest $request, Kelompok $kelompok)
    {
        DB::beginTransaction();
        try {
            $kelompok->kodekelompok = $request->kodekelompok;
            $kelompok->keterangan = $request->keterangan;
            $kelompok->statusaktif = $request->statusaktif;
            $kelompok->modifiedby = auth('api')->user()->name;

            if ($kelompok->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($kelompok->getTable()),
                    'postingdari' => 'EDIT KELOMPOK',
                    'idtrans' => $kelompok->id,
                    'nobuktitrans' => $kelompok->id,
                    'aksi' => 'EDIT',
                    'datajson' => $kelompok->toArray(),
                    'modifiedby' => $kelompok->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);
                DB::commit();
            }
            /* Set position and page */
            $selected = $this->getPosition($kelompok, $kelompok->getTable());
            $kelompok->position = $selected->position;
            $kelompok->page = ceil($kelompok->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $kelompok
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

        $kelompok = new Kelompok();
        $kelompok = $kelompok->lockAndDestroy($id);
        if ($kelompok) {
            $logTrail = [
                'namatabel' => strtoupper($kelompok->getTable()),
                'postingdari' => 'DELETE KELOMPOK',
                'idtrans' => $kelompok->id,
                'nobuktitrans' => $kelompok->id,
                'aksi' => 'DELETE',
                'datajson' => $kelompok->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);


            DB::commit();

            $selected = $this->getPosition($kelompok, $kelompok->getTable(), true);
            $kelompok->position = $selected->position;
            $kelompok->id = $selected->id;
            $kelompok->page = ceil($kelompok->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $kelompok
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('kelompok')->getColumns();

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
