<?php

namespace App\Http\Controllers\Api;

use App\Models\Kerusakan;
use App\Http\Requests\StoreKerusakanRequest;
use App\Http\Requests\UpdateKerusakanRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class KerusakanController extends Controller
{

    /**
     * @ClassName 
     */
    public function index()
    {
        $kerusakan = new Kerusakan();

        return response([
            'data' => $kerusakan->get(),
            'attributes' => [
                'totalRows' => $kerusakan->totalRows,
                'totalPages' => $kerusakan->totalPages
            ]
        ]);
    }  

    public function cekValidasi($id) {
        $kerusakan= new Kerusakan();
        $cekdata=$kerusakan->cekvalidasihapus($id);
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

        $kerusakan = new Kerusakan();
        return response([
            'status' => true,
            'data' => $kerusakan->default(),
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreKerusakanRequest $request)
    {
        DB::beginTransaction();

        try {
            $kerusakan = new Kerusakan();
            $kerusakan->keterangan = $request->keterangan ?? '';
            $kerusakan->statusaktif = $request->statusaktif;
            $kerusakan->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($kerusakan->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($kerusakan->getTable()),
                    'postingdari' => 'ENTRY KERUSAKAN',
                    'idtrans' => $kerusakan->id,
                    'nobuktitrans' => $kerusakan->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $kerusakan->toArray(),
                    'modifiedby' => $kerusakan->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($kerusakan, $kerusakan->getTable());
            $kerusakan->position = $selected->position;
            $kerusakan->page = ceil($kerusakan->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $kerusakan
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Kerusakan $kerusakan)
    {
        return response([
            'status' => true,
            'data' => $kerusakan
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateKerusakanRequest $request, Kerusakan $kerusakan)
    {
        DB::beginTransaction();
        try {
            $kerusakan->keterangan = $request->keterangan ?? '';
            $kerusakan->statusaktif = $request->statusaktif;
            $kerusakan->modifiedby = auth('api')->user()->name;

            if ($kerusakan->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($kerusakan->getTable()),
                    'postingdari' => 'EDIT KERUSAKAN',
                    'idtrans' => $kerusakan->id,
                    'nobuktitrans' => $kerusakan->id,
                    'aksi' => 'EDIT',
                    'datajson' => $kerusakan->toArray(),
                    'modifiedby' => $kerusakan->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }
            /* Set position and page */
            $selected = $this->getPosition($kerusakan, $kerusakan->getTable());
            $kerusakan->position = $selected->position;
            $kerusakan->page = ceil($kerusakan->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $kerusakan
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

        $kerusakan = new Kerusakan();
        $kerusakan = $kerusakan->lockAndDestroy($id);
        if ($kerusakan) {
            $logTrail = [
                'namatabel' => strtoupper($kerusakan->getTable()),
                'postingdari' => 'DELETE KERUSAKAN',
                'idtrans' => $kerusakan->id,
                'nobuktitrans' => $kerusakan->id,
                'aksi' => 'DELETE',
                'datajson' => $kerusakan->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $selected = $this->getPosition($kerusakan, $kerusakan->getTable(), true);
            $kerusakan->position = $selected->position;
            $kerusakan->id = $selected->id;
            $kerusakan->page = ceil($kerusakan->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $kerusakan
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('kerusakan')->getColumns();

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
