<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHariLiburRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\HariLibur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HariLiburController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $hariLibur = new HariLibur();

        return response([
            'data' => $hariLibur->get(),
            'attributes' => [
                'totalRows' => $hariLibur->totalRows,
                'totalPages' => $hariLibur->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreHariLiburRequest $request)
    {
        DB::beginTransaction();

        try{

            $hariLibur = new HariLibur();
            $hariLibur->tgl = date('Y-m-d', strtotime($request->tgl));
            $hariLibur->keterangan = $request->keterangan;
            $hariLibur->statusaktif = $request->statusaktif;
            $hariLibur->modifiedby = auth('api')->user()->name;

            
            if ($hariLibur->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($hariLibur->getTable()),
                    'postingdari' => 'ENTRY HARI LIBUR',
                    'idtrans' => $hariLibur->id,
                    'nobuktitrans' => $hariLibur->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $hariLibur->toArray(),
                    'modifiedby' => $hariLibur->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            }
            DB::commit();
            $selected = $this->getPosition($hariLibur, $hariLibur->getTable());
            $hariLibur->position = $selected->position;
            $hariLibur->page = ceil($hariLibur->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $hariLibur
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        } 
    }
    
    public function show(HariLibur $hariLibur)
    {
        return response([
            'status' => true,
            'data' => $hariLibur
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(StoreHariLiburRequest $request, HariLibur $hariLibur)
    {
        DB::beginTransaction();

        try {
            $hariLibur->tgl = date('Y-m-d', strtotime($request->tgl));
            $hariLibur->keterangan = $request->keterangan;
            $hariLibur->statusaktif = $request->statusaktif;
            $hariLibur->modifiedby = auth('api')->user()->name;

            if ($hariLibur->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($hariLibur->getTable()),
                    'postingdari' => 'EDIT HARI LIBUR',
                    'idtrans' => $hariLibur->id,
                    'nobuktitrans' => $hariLibur->id,
                    'aksi' => 'EDIT',
                    'datajson' => $hariLibur->toArray(),
                    'modifiedby' => $hariLibur->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);
            }
            DB::commit();
            $selected = $this->getPosition($hariLibur, $hariLibur->getTable());
            $hariLibur->position = $selected->position;
            $hariLibur->page = ceil($hariLibur->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $hariLibur
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        } 
    }

     /**
     * @ClassName 
     */
    public function destroy(HariLibur $hariLibur, Request $request)
    {
        DB::beginTransaction();
        
        try {
            $delete = HariLibur::destroy($hariLibur->id);
            $del = 1;
            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($hariLibur->getTable()),
                    'postingdari' => 'DELETE HARI LIBUR',
                    'idtrans' => $hariLibur->id,
                    'nobuktitrans' => $hariLibur->id,
                    'aksi' => 'DELETE',
                    'datajson' => $hariLibur->toArray(),
                    'modifiedby' => $hariLibur->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
                /* Set position and page */

                $selected = $this->getPosition($hariLibur, $hariLibur->getTable(), true);
                
                $hariLibur->position = $selected->position;
                $hariLibur->id = $selected->id;
                $hariLibur->page = ceil($hariLibur->position / ($request->limit ?? 10));
                
                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $hariLibur
                ]);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Gagal dihapus'
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('harilibur')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
