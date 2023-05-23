<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Http\Requests\StoreKaryawanRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateKaryawanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KaryawanController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $karyawan = new Karyawan();

        return response([
            'data' => $karyawan->get(),
            'attributes' => [
                'totalRows' => $karyawan->totalRows,
                'totalPages' => $karyawan->totalPages,
            ]
        ]);
    }

    public function cekValidasi($id) {
        $karyawan= new Karyawan();
        $cekdata=$karyawan->cekvalidasihapus($id);
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
        $karyawan = new Karyawan();
        return response([
            'status' => true,
            'data' => $karyawan->default(),
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreKaryawanRequest $request)
    {
        DB::beginTransaction();
        try {

            $karyawan = new Karyawan();
            $karyawan->namakaryawan = $request->namakaryawan;
            $karyawan->keterangan = $request->keterangan ?? '';
            $karyawan->statusaktif = $request->statusaktif;
            $karyawan->statusstaff = $request->statusstaff;
            $karyawan->modifiedby = auth('api')->user()->name;

            if ($karyawan->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($karyawan->getTable()),
                    'postingdari' => 'ENTRY KARYAWAN',
                    'idtrans' => $karyawan->id,
                    'nobuktitrans' => $karyawan->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $karyawan->toArray(),
                    'modifiedby' => $karyawan->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);
            }

            DB::commit();
            /* Set position and page */
            $selected = $this->getPosition($karyawan, $karyawan->getTable());
            $karyawan->position = $selected->position;
            $karyawan->page = ceil($karyawan->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $karyawan
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show(Karyawan $karyawan)
    {
        return response([
            'status' => true,
            'data' => $karyawan
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateKaryawanRequest $request, Karyawan $karyawan)
    {
        DB::beginTransaction();
        try {
            $karyawan->namakaryawan = $request->namakaryawan;
            $karyawan->keterangan = $request->keterangan ?? '';
            $karyawan->statusaktif = $request->statusaktif;
            $karyawan->statusstaff = $request->statusstaff;
            $karyawan->modifiedby = auth('api')->user()->name;

            if ($karyawan->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($karyawan->getTable()),
                    'postingdari' => 'EDIT KARYAWAN',
                    'idtrans' => $karyawan->id,
                    'nobuktitrans' => $karyawan->id,
                    'aksi' => 'EDIT',
                    'datajson' => $karyawan->toArray(),
                    'modifiedby' => $karyawan->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);
            }

            DB::commit();
            /* Set position and page */
            $selected = $this->getPosition($karyawan, $karyawan->getTable());
            $karyawan->position = $selected->position;
            $karyawan->page = ceil($karyawan->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $karyawan
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

        $karyawan = new Karyawan();
        $karyawan = $karyawan->lockAndDestroy($id);

        if ($karyawan) {
            $logTrail = [
                'namatabel' => strtoupper($karyawan->getTable()),
                'postingdari' => 'DELETE KARYAWAN',
                'idtrans' => $karyawan->id,
                'nobuktitrans' => $karyawan->id,
                'aksi' => 'DELETE',
                'datajson' => $karyawan->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);


            DB::commit();

            $selected = $this->getPosition($karyawan, $karyawan->getTable(), true);
            $karyawan->position = $selected->position;
            $karyawan->id = $selected->id;
            $karyawan->page = ceil($karyawan->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $karyawan
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('karyawan')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
