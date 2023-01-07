<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AkunPusat;
use App\Http\Requests\StoreAkunPusatRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateAkunPusatRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class AkunPusatController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $akunPusat = new AkunPusat();

        return response([
            'data' => $akunPusat->get(),
            'attributes' => [
                'totalRows' => $akunPusat->totalRows,
                'totalPages' => $akunPusat->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreAkunPusatRequest $request)
    {
        DB::beginTransaction();

        try {
            $akunPusat = new AkunPusat();
            $akunPusat->coa = $request->coa;
            $akunPusat->keterangancoa = $request->keterangancoa;
            $akunPusat->type = $request->type;
            $akunPusat->level = $request->level;
            $akunPusat->parent = $request->parent;
            $akunPusat->statuscoa = $request->statuscoa;
            $akunPusat->statusaccountpayable = $request->statusaccountpayable;
            $akunPusat->statusneraca = $request->statusneraca;
            $akunPusat->statuslabarugi = $request->statuslabarugi;
            $akunPusat->coamain = $request->coamain;
            $akunPusat->statusaktif = $request->statusaktif;
            $akunPusat->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($akunPusat->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($akunPusat->getTable()),
                    'postingdari' => 'ENTRY AKUN PUSAT',
                    'idtrans' => $akunPusat->id,
                    'nobuktitrans' => $akunPusat->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $akunPusat->toArray(),
                    'modifiedby' => $akunPusat->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($akunPusat, $akunPusat->getTable());
            $akunPusat->position = $selected->position;
            $akunPusat->page = ceil($akunPusat->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $akunPusat
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show(AkunPusat $akunPusat)
    {
        return response([
            'status' => true,
            'data' => $akunPusat
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateAkunPusatRequest $request, AkunPusat $akunPusat)
    {
        DB::beginTransaction();

        try {
            $akunPusat->keterangancoa = $request->keterangancoa;
            $akunPusat->type = $request->type;
            $akunPusat->level = $request->level;
            $akunPusat->parent = $request->parent;
            $akunPusat->statuscoa = $request->statuscoa;
            $akunPusat->statusaccountpayable = $request->statusaccountpayable;
            $akunPusat->statusneraca = $request->statusneraca;
            $akunPusat->statuslabarugi = $request->statuslabarugi;
            $akunPusat->statusaktif = $request->statusaktif;
            $akunPusat->coamain = $request->coamain;
            $akunPusat->modifiedby = auth('api')->user()->name;

            if ($akunPusat->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($akunPusat->getTable()),
                    'postingdari' => 'EDIT AKUN PUSAT',
                    'idtrans' => $akunPusat->id,
                    'nobuktitrans' => $akunPusat->id,
                    'aksi' => 'EDIT',
                    'datajson' => $akunPusat->toArray(),
                    'modifiedby' => $akunPusat->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                /* Set position and page */
                $selected = $this->getPosition($akunPusat, $akunPusat->getTable());
                $akunPusat->position = $selected->position;
                $akunPusat->page = ceil($akunPusat->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $akunPusat
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
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $akunPusat = new AkunPusat();
        $akunPusat = $akunPusat->lockAndDestroy($id);
        if ($akunPusat) {
            $logTrail = [
                'namatabel' => strtoupper($akunPusat->getTable()),
                'postingdari' => 'DELETE AKUN PUSAT',
                'idtrans' => $akunPusat->id,
                'nobuktitrans' => $akunPusat->id,
                'aksi' => 'DELETE',
                'datajson' => $akunPusat->toArray(),
                'modifiedby' => $akunPusat->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);


            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($akunPusat, $akunPusat->getTable(), true);
            $akunPusat->position = $selected->position;
            $akunPusat->id = $selected->id;
            $akunPusat->page = ceil($akunPusat->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $akunPusat
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('akunPusat')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
