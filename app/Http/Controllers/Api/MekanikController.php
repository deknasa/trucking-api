<?php

namespace App\Http\Controllers\Api;

use App\Models\Mekanik;
use App\Models\AkunPusat;
use App\Http\Requests\StoreMekanikRequest;
use App\Http\Requests\UpdateMekanikRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Controllers\Controller;
use App\Models\LogTrail;
use App\Models\Parameter;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class MekanikController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $mekanik = new Mekanik();

        return response([
            'data' => $mekanik->get(),
            'attributes' => [
                'totalRows' => $mekanik->totalRows,
                'totalPages' => $mekanik->totalPages
            ]
        ]);
    }

    public function default()
    {

        $mekanik = new Mekanik();
        return response([
            'status' => true,
            'data' => $mekanik->default(),
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreMekanikRequest $request)
    {
        DB::beginTransaction();
        try {
            $mekanik = new Mekanik();
            $mekanik->namamekanik = $request->namamekanik;
            $mekanik->keterangan = $request->keterangan;
            $mekanik->statusaktif = $request->statusaktif;
            $mekanik->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($mekanik->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($mekanik->getTable()),
                    'postingdari' => 'ENTRY MEKANIK',
                    'idtrans' => $mekanik->id,
                    'nobuktitrans' => $mekanik->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $mekanik->toArray(),
                    'modifiedby' => $mekanik->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($mekanik, $mekanik->getTable());
            $mekanik->position = $selected->position;
            $mekanik->page = ceil($mekanik->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $mekanik
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function show(Mekanik $mekanik)
    {
        return response([
            'status' => true,
            'data' => $mekanik
        ]);
    }


    /**
     * @ClassName 
     */
    public function update(UpdateMekanikRequest $request, Mekanik $mekanik)
    {
        DB::beginTransaction();
        try {
            $mekanik->namamekanik = $request->namamekanik;
            $mekanik->keterangan = $request->keterangan;
            $mekanik->statusaktif = $request->statusaktif;
            $mekanik->modifiedby = auth('api')->user()->name;

            if ($mekanik->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($mekanik->getTable()),
                    'postingdari' => 'EDIT MEKANIK',
                    'idtrans' => $mekanik->id,
                    'nobuktitrans' => $mekanik->id,
                    'aksi' => 'EDIT',
                    'datajson' => $mekanik->toArray(),
                    'modifiedby' => $mekanik->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }
            /* Set position and page */
            $selected = $this->getPosition($mekanik, $mekanik->getTable());
            $mekanik->position = $selected->position;
            $mekanik->page = ceil($mekanik->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $mekanik
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }
    /**
     * @ClassName 
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $mekanik = new Mekanik();
        $mekanik = $mekanik->lockAndDestroy($id);

        if ($mekanik) {
            $logTrail = [
                'namatabel' => strtoupper($mekanik->getTable()),
                'postingdari' => 'DELETE MEKANIK',
                'idtrans' => $mekanik->id,
                'nobuktitrans' => $mekanik->id,
                'aksi' => 'DELETE',
                'datajson' => $mekanik->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);


            DB::commit();

            $selected = $this->getPosition($mekanik, $mekanik->getTable(), true);
            $mekanik->position = $selected->position;
            $mekanik->id = $selected->id;
            $mekanik->page = ceil($mekanik->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $mekanik
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function combo(Request $request)
    {
        $data = [
            'status' => Parameter::where(['grp' => 'status aktif'])->get(),
        ];

        return response([
            'data' => $data
        ]);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('mekanik')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
