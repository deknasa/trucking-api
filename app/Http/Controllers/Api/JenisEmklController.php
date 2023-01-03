<?php

namespace App\Http\Controllers\Api;

use App\Models\JenisEmkl;
use App\Http\Requests\StoreJenisEmklRequest;
use App\Http\Requests\UpdateJenisEmklRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class JenisEmklController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $jenisemkl = new JenisEmkl();

        return response([
            'data' => $jenisemkl->get(),
            'attributes' => [
                'totalRows' => $jenisemkl->totalRows,
                'totalPages' => $jenisemkl->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreJenisEmklRequest $request)
    {
        DB::beginTransaction();

        try {
            $jenisemkl = new JenisEmkl();
            $jenisemkl->kodejenisemkl = $request->kodejenisemkl;
            $jenisemkl->keterangan = $request->keterangan;
            $jenisemkl->statusaktif = $request->statusaktif;
            $jenisemkl->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($jenisemkl->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($jenisemkl->getTable()),
                    'postingdari' => 'ENTRY JENISEMKL',
                    'idtrans' => $jenisemkl->id,
                    'nobuktitrans' => $jenisemkl->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $jenisemkl->toArray(),
                    'modifiedby' => $jenisemkl->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($jenisemkl, $jenisemkl->getTable());
            $jenisemkl->position = $selected->position;
            $jenisemkl->page = ceil($jenisemkl->position / ($request->limit ?? 10));
            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $jenisemkl
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(JenisEmkl $jenisemkl)
    {
        return response([
            'status' => true,
            'data' => $jenisemkl
        ]);
    }


    /**
     * @ClassName 
     */
    public function update(StoreJenisEmklRequest $request, JenisEmkl $jenisemkl)
    {
        DB::beginTransaction();
        try {
            $jenisemkl->kodejenisemkl = $request->kodejenisemkl;
            $jenisemkl->keterangan = $request->keterangan;
            $jenisemkl->modifiedby = auth('api')->user()->name;
            $jenisemkl->statusaktif = $request->statusaktif;

            if ($jenisemkl->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($jenisemkl->getTable()),
                    'postingdari' => 'EDIT JENISEMKL',
                    'idtrans' => $jenisemkl->id,
                    'nobuktitrans' => $jenisemkl->id,
                    'aksi' => 'EDIT',
                    'datajson' => $jenisemkl->toArray(),
                    'modifiedby' => $jenisemkl->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);
                DB::commit();
            }
            /* Set position and page */
            $selected = $this->getPosition($jenisemkl, $jenisemkl->getTable());
            $jenisemkl->position = $selected->position;
            $jenisemkl->page = ceil($jenisemkl->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $jenisemkl
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function destroy(JenisEmkl $jenisemkl, Request $request)
    {
        DB::beginTransaction();
        try {
            $isDelete = JenisEmkl::where('id', $jenisemkl->id)->delete();
            if ($isDelete) {
                $logTrail = [
                    'namatabel' => strtoupper($jenisemkl->getTable()),
                    'postingdari' => 'DELETE JENISEMKL',
                    'idtrans' => $jenisemkl->id,
                    'nobuktitrans' => $jenisemkl->id,
                    'aksi' => 'DELETE',
                    'datajson' => $jenisemkl->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                $selected = $this->getPosition($jenisemkl, $jenisemkl->getTable(), true);
                $jenisemkl->position = $selected->position;
                $jenisemkl->id = $selected->id;
                $jenisemkl->page = ceil($jenisemkl->position / ($request->limit ?? 10));
    
                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $jenisemkl
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('jenisemkl')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function combo()
    {
        $jenisemkls = JenisEmkl::where('statusaktif', '=', 1)
            ->get();

        dd($jenisemkls);
        return response([
            'data' => $jenisemkls
        ]);
    }
}
