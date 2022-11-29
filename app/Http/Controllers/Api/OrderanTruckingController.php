<?php

namespace App\Http\Controllers\Api;

use App\Models\OrderanTrucking;
use App\Http\Requests\StoreOrderanTruckingRequest;
use App\Http\Requests\UpdateOrderanTruckingRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\LogTrail;
use App\Models\Parameter;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Container;
use App\Models\Agen;
use App\Models\JenisOrder;
use App\Models\Pelanggan;
use App\Models\Tarif;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Http;

class OrderanTruckingController extends Controller
{
   /**
     * @ClassName 
     */
    public function index()
    {

        $orderanTrucking = new OrderanTrucking();
        return response([
            'data' => $orderanTrucking->get(),
            'attributes' => [
                'totalRows' => $orderanTrucking->totalRows,
                'totalPages' => $orderanTrucking->totalPages
            ]
        ]);
    }

    
   /**
     * @ClassName 
     */
    public function store(StoreOrderanTruckingRequest $request)
    {
        DB::beginTransaction();

        try {
            $group = 'ORDERANTRUCKING';
            $subgroup = 'ORDERANTRUCKING';
            $format = DB::table('parameter')
            ->where('grp', $group )
            ->where('subgrp', $subgroup)
            ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'orderantrucking';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $orderanTrucking = new OrderanTrucking();
            $orderanTrucking->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $orderanTrucking->container_id = $request->container_id;
            $orderanTrucking->agen_id = $request->agen_id;
            $orderanTrucking->jenisorder_id = $request->jenisorder_id;
            $orderanTrucking->pelanggan_id = $request->pelanggan_id;
            $orderanTrucking->tarif_id = $request->tarif_id;
            $orderanTrucking->nojobemkl = $request->nojobemkl;
            $orderanTrucking->nocont = $request->nocont;
            $orderanTrucking->noseal = $request->noseal;
            $orderanTrucking->nojobemkl2 = $request->nojobemkl2 ?? '';
            $orderanTrucking->nocont2 = $request->nocont2 ?? '';
            $orderanTrucking->noseal2 = $request->noseal2 ?? '';
            $orderanTrucking->statuslangsir = $request->statuslangsir;
            $orderanTrucking->statusperalihan = $request->statusperalihan;
            $orderanTrucking->modifiedby = auth('api')->user()->name;
            $orderanTrucking->statusformat = $format->id;

            $tarif = Tarif::find($request->tarif_id);
            $orderanTrucking->nominal = $tarif->nominal;

            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $orderanTrucking->nobukti = $nobukti;

            try {
                $orderanTrucking->save();
            } catch (\Exception $e) {
                $errorCode = @$e->errorInfo[1];
                if ($errorCode == 2601) {
                    goto TOP;
                }
            }

            $logTrail = [
                'namatabel' => strtoupper($orderanTrucking->getTable()),
                'postingdari' => 'ENTRY ORDERAN TRUCKING',
                'idtrans' => $orderanTrucking->id,
                'nobuktitrans' => $orderanTrucking->id,
                'aksi' => 'ENTRY',
                'datajson' => $orderanTrucking->toArray(),
                'modifiedby' => $orderanTrucking->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($orderanTrucking, $orderanTrucking->getTable());
            $orderanTrucking->position = $selected->position;
            $orderanTrucking->page = ceil($orderanTrucking->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $orderanTrucking
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $data = OrderanTrucking::find($id);

        return response([
            'status' => true,
            'data' => $data
        ]);
    }

   /**
     * @ClassName 
     */
    public function update(StoreOrderanTruckingRequest $request, OrderanTrucking $orderantrucking)
    {
        try {
            $orderantrucking->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $orderantrucking->container_id = $request->container_id;
            $orderantrucking->agen_id = $request->agen_id;
            $orderantrucking->jenisorder_id = $request->jenisorder_id;
            $orderantrucking->pelanggan_id = $request->pelanggan_id;
            $orderantrucking->tarif_id = $request->tarif_id;
            $orderantrucking->nojobemkl = $request->nojobemkl;
            $orderantrucking->nocont = $request->nocont;
            $orderantrucking->noseal = $request->noseal;
            $orderantrucking->nojobemkl2 = $request->nojobemkl2 ?? '';
            $orderantrucking->nocont2 = $request->nocont2 ?? '';
            $orderantrucking->noseal2 = $request->noseal2 ?? '';
            $orderantrucking->statuslangsir = $request->statuslangsir;
            $orderantrucking->statusperalihan = $request->statusperalihan;
            $orderantrucking->modifiedby = auth('api')->user()->name;

            $tarif = Tarif::find($request->tarif_id);
            $orderantrucking->nominal = $tarif->nominal;

            if ($orderantrucking->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($orderantrucking->getTable()),
                    'postingdari' => 'EDIT ORDERAN TRUCKING',
                    'idtrans' => $orderantrucking->id,
                    'nobuktitrans' => $orderantrucking->id,
                    'aksi' => 'EDIT',
                    'datajson' => $orderantrucking->toArray(),
                    'modifiedby' => $orderantrucking->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $selected = $this->getPosition($orderantrucking, $orderantrucking->getTable());
                $orderantrucking->position = $selected->position;
                $orderantrucking->page = ceil($orderantrucking->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $orderantrucking
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
    public function destroy(OrderanTrucking $orderantrucking, Request $request)
    {
        DB::beginTransaction();
        $delete = Orderantrucking::destroy($orderantrucking->id);
        
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($orderantrucking->getTable()),
                'postingdari' => 'DELETE ORDERAN TRUCKING',
                'idtrans' => $orderantrucking->id,
                'nobuktitrans' => $orderantrucking->id,
                'aksi' => 'DELETE',
                'datajson' => $orderantrucking->toArray(),
                'modifiedby' => $orderantrucking->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            
            DB::commit();
            $selected = $this->getPosition($orderantrucking, $orderantrucking->getTable(), true);
            $orderantrucking->position = $selected->position;
            $orderantrucking->id = $selected->id;
            $orderantrucking->page = ceil($orderantrucking->position / ($request->limit ?? 10));

            
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $orderantrucking
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('orderantrucking')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function combo(Request $request)
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])
        ->get(config('app.api_url') . "jobemkl/combo");
        
        $data = [
            'container' => Container::all(),
            'agen' => Agen::all(),
            'jenisorder' => JenisOrder::all(),
            'pelanggan' => Pelanggan::all(),
            'tarif' => Tarif::all(),
            'statuslangsir' => Parameter::where(['grp'=>'status langsir'])->get(),
            'statusperalihan' => Parameter::where(['grp'=>'status peralihan'])->get(),
            'jobemkl' => $response['data']['jobemkl'],
        ];

        return response([
            'data' => $data
        ]);
    }

}
