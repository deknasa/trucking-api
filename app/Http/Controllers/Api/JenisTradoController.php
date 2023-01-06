<?php

namespace App\Http\Controllers\Api;

use App\Models\JenisTrado;
use App\Http\Requests\StoreJenisTradoRequest;
use App\Http\Requests\UpdateJenisTradoRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class JenisTradoController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $jenistrado = new JenisTrado();

        return response([
            'data' => $jenistrado->get(),
            'attributes' => [
                'totalRows' => $jenistrado->totalRows,
                'totalPages' => $jenistrado->totalPages
            ]
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreJenisTradoRequest $request)
    {
        DB::beginTransaction();

        try {
            $jenistrado = new jenistrado();
            $jenistrado->kodejenistrado = $request->kodejenistrado;
            $jenistrado->statusaktif = $request->statusaktif;
            $jenistrado->keterangan = $request->keterangan;
            $jenistrado->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            TOP:
            if ($jenistrado->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($jenistrado->getTable()),
                    'postingdari' => 'ENTRY JENIS TRADO',
                    'idtrans' => $jenistrado->id,
                    'nobuktitrans' => $jenistrado->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $jenistrado->toArray(),
                    'modifiedby' => $jenistrado->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($jenistrado, $jenistrado->getTable());
            $jenistrado->position = $selected->position;
            $jenistrado->page = ceil($jenistrado->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $jenistrado
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(jenistrado $jenistrado)
    {
        return response([
            'status' => true,
            'data' => $jenistrado
        ]);
    }


    /**
     * @ClassName 
     */
    public function update(StoreJenisTradoRequest $request, JenisTrado $jenistrado)
    {
        DB::beginTransaction();
        try {
            $jenistrado->kodejenistrado = $request->kodejenistrado;
            $jenistrado->keterangan = $request->keterangan;
            $jenistrado->statusaktif = $request->statusaktif;
            $jenistrado->modifiedby = auth('api')->user()->name;

            if ($jenistrado->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($jenistrado->getTable()),
                    'postingdari' => 'EDIT JENIS TRADO',
                    'idtrans' => $jenistrado->id,
                    'nobuktitrans' => $jenistrado->id,
                    'aksi' => 'EDIT',
                    'datajson' => $jenistrado->toArray(),
                    'modifiedby' => $jenistrado->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                $selected = $this->getPosition($jenistrado, $jenistrado->getTable(), true);
                $jenistrado->position = $selected->position;
                $jenistrado->page = ceil($jenistrado->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $jenistrado
                ]);
            }
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

        $jenisTrado = new JenisTrado();
        $jenisTrado = $jenisTrado->lockAndDestroy($id);
        if ($jenisTrado) {
            $logTrail = [
                'namatabel' => strtoupper($jenisTrado->getTable()),
                'postingdari' => 'DELETE JENIS TRADO',
                'idtrans' => $jenisTrado->id,
                'nobuktitrans' => $jenisTrado->id,
                'aksi' => 'DELETE',
                'datajson' => $jenisTrado->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $selected = $this->getPosition($jenisTrado, $jenisTrado->getTable(), true);
            $jenisTrado->position = $selected->position;
            $jenisTrado->id = $selected->id;
            $jenisTrado->page = ceil($jenisTrado->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $jenisTrado
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('jenistrado')->getColumns();

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
