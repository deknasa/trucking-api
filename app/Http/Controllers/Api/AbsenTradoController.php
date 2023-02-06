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
    
    public function default()
    {
        $absenTrado = new AbsenTrado();
        return response([
            'status' => true,
            'data' => $absenTrado->default()
        ]);
    }

    public function cekValidasi($id) {
        $absenTrado= new AbsenTrado();
        $cekdata=$absenTrado->cekvalidasihapus($id);
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

    public function show(AbsenTrado $absentrado)
    {
        return response([
            'status' => true,
            'data' => $absentrado
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateAbsenTradoRequest $request, AbsenTrado $absentrado)
    {
        DB::beginTransaction();

        try {
            $absentrado->kodeabsen = $request->kodeabsen;
            $absentrado->keterangan = $request->keterangan;
            $absentrado->statusaktif = $request->statusaktif;
            $absentrado->modifiedby = auth('api')->user()->name;

            if ($absentrado->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($absentrado->getTable()),
                    'postingdari' => 'EDIT ABSEN TRADO',
                    'idtrans' => $absentrado->id,
                    'nobuktitrans' => $absentrado->id,
                    'aksi' => 'EDIT',
                    'datajson' => $absentrado->toArray(),
                    'modifiedby' => $absentrado->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                /* Set position and page */
                $selected = $this->getPosition($absentrado, $absentrado->getTable());
                $absentrado->position = $selected->position;
                $absentrado->page = ceil($absentrado->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $absentrado
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

        $absenTrado = new AbsenTrado();
        $absenTrado = $absenTrado->lockAndDestroy($id);

        if ($absenTrado) {
            $logTrail = [
                'namatabel' => strtoupper($absenTrado->getTable()),
                'postingdari' => 'DELETE ABSEN TRADO',
                'idtrans' => $absenTrado->id,
                'nobuktitrans' => $absenTrado->id,
                'aksi' => 'DELETE',
                'datajson' => $absenTrado->toArray(),
                'modifiedby' => auth('api')->user()->name
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('absentrado')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
