<?php

namespace App\Http\Controllers\Api;

use App\Models\Tarif;
use App\Http\Requests\StoreTarifRequest;
use App\Http\Requests\UpdateTarifRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;
use App\Models\Container;
use App\Models\Kota;
use App\Models\Zona;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class TarifController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $tarif = new Tarif();

        return response([
            'data' => $tarif->get(),
            'attributes' => [
                'totalRows' => $tarif->totalRows,
                'totalPages' => $tarif->totalPages
            ]
        ]);
    }

    
    /**
     * @ClassName 
     */
    public function store(StoreTarifRequest $request)
    {
        DB::beginTransaction();

        try {
            $tarif = new Tarif();
            $tarif->tujuan = $request->tujuan;
            $tarif->container_id = $request->container_id;
            $tarif->nominal = $request->nominal;
            $tarif->statusaktif = $request->statusaktif;
            $tarif->tujuanasal = $request->tujuanasal;
            $tarif->statussistemton = $request->statussistemton;
            $tarif->kota_id = $request->kota_id;
            $tarif->zona_id = $request->zona_id;
            $tarif->nominalton = $request->nominalton;
            $tarif->tglmulaiberlaku = date('Y-m-d', strtotime($request->tglmulaiberlaku));
            $tarif->tglakhirberlaku = date('Y-m-d', strtotime($request->tglakhirberlaku));
            $tarif->statuspenyesuaianharga = $request->statuspenyesuaianharga;
            $tarif->modifiedby = auth('api')->user()->name;
           
            if ($tarif->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($tarif->getTable()),
                    'postingdari' => 'ENTRY TARIF',
                    'idtrans' => $tarif->id,
                    'nobuktitrans' => $tarif->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $tarif->toArray(),
                    'modifiedby' => $tarif->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($tarif, $tarif->getTable());
            $tarif->position = $selected->position;
            $tarif->page = ceil($tarif->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $tarif
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show($id)
    {

        $data = Tarif::findAll($id);
        return response([
            'status' => true,
            'data' => $data,
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateTarifRequest $request, Tarif $tarif)
    {
        try {
            $tarif->tujuan = $request->tujuan;
            $tarif->container_id = $request->container_id;
            $tarif->nominal = $request->nominal;
            $tarif->statusaktif = $request->statusaktif;
            $tarif->tujuanasal = $request->tujuanasal;
            $tarif->statussistemton = $request->statussistemton;
            $tarif->kota_id = $request->kota_id;
            $tarif->zona_id = $request->zona_id;
            $tarif->nominalton = $request->nominalton;
            $tarif->tglmulaiberlaku = date('Y-m-d', strtotime($request->tglmulaiberlaku));
            $tarif->tglakhirberlaku = date('Y-m-d', strtotime($request->tglakhirberlaku));
            $tarif->statuspenyesuaianharga = $request->statuspenyesuaianharga;
            $tarif->modifiedby = auth('api')->user()->name;

            if ($tarif->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($tarif->getTable()),
                    'postingdari' => 'EDIT TARIF',
                    'idtrans' => $tarif->id,
                    'nobuktitrans' => $tarif->id,
                    'aksi' => 'EDIT',
                    'datajson' => $tarif->toArray(),
                    'modifiedby' => $tarif->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);


                /* Set position and page */
                $selected = $this->getPosition($tarif, $tarif->getTable());
                $tarif->position = $selected->position;
                $tarif->page = ceil($tarif->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $tarif
                ]);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Gagal diubah'
                ]);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
   

    /**
     * @ClassName
     */
    public function destroy(Tarif $tarif, Request $request)
    {

        DB::beginTransaction();
        $tarif = new Tarif();

        try {
            // $delete = $servicein->delete();
            $delete = Tarif::destroy($tarif->id);

            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($tarif->getTable()),
                    'postingdari' => 'DELETE TARIF',
                    'idtrans' => $tarif->id,
                    'nobuktitrans' => $tarif->id,
                    'aksi' => 'DELETE',
                    'datajson' => $tarif->toArray(),
                    'modifiedby' => $tarif->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                /* Set position and page */
                $selected = $this->getPosition($tarif, $tarif->getTable(), true);
                $tarif->position = $selected->position;
                $tarif->id = $selected->id;
                $tarif->page = ceil($tarif->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $tarif
                ]);
            } else {
                DB::rollBack();

                return response([
                    'status' => false,
                    'message' => 'Gagal dihapus'
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('tarif')->getColumns();

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
            'container' => Container::all(),
            'kota' => Kota::all(),
            'zona' => Zona::all(),
            'statusaktif' => Parameter::where(['grp' => 'status aktif'])->get(),
            'statuspenyesuaianharga' => Parameter::where(['grp' => 'status penyesuaian harga'])->get(),
            'statussistemton' => Parameter::where(['grp' => 'sistem ton'])->get(),
        ];

        return response([
            'data' => $data
        ]);
    }

   
}
