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

    public function create(StoreOrderanTruckingRequest $request)
    {
        
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
            // $request->sortname = $request->sortname ?? 'id';
            // $request->sortorder = $request->sortorder ?? 'asc';

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

    public function show(OrderanTrucking $orderanTrucking,$id)
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
    public function update(StoreOrderanTruckingRequest $request, OrderanTrucking $orderanTrucking, $id)
    {
        try {
            $orderanTrucking = OrderanTrucking::findOrFail($id);
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

            $tarif = Tarif::find($request->tarif_id);
            $orderanTrucking->nominal = $tarif->nominal;

            if ($orderanTrucking->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($orderanTrucking->getTable()),
                    'postingdari' => 'EDIT ORDERAN TRUCKING',
                    'idtrans' => $orderanTrucking->id,
                    'nobuktitrans' => $orderanTrucking->id,
                    'aksi' => 'EDIT',
                    'datajson' => $orderanTrucking->toArray(),
                    'modifiedby' => $orderanTrucking->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $selected = $this->getPosition($orderanTrucking, $orderanTrucking->getTable());
                $orderanTrucking->position = $selected->position;
                $orderanTrucking->page = ceil($orderanTrucking->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $orderanTrucking
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
        $del = 1;
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

    public function getid($id, $request, $del)
    {
        $params = [
            'indexRow' => $request->indexRow ?? 1,
            'limit' => $request->limit ?? 100,
            'page' => $request->page ?? 1,
            'sortname' => $request->sortname ?? 'id',
            'sortorder' => $request->sortorder ?? 'asc',
        ];
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->id();
            $table->bigInteger('id_')->default('0');
            $table->string('nobukti', 50)->default('');
            $table->string('tglbukti', 50)->default('');
            $table->string('container_id', 50)->default('');
            $table->string('agen_id', 50)->default('');
            $table->string('jenisorder_id', 50)->default('');
            $table->string('pelanggan_id', 50)->default('');
            $table->string('tarif_id', 50)->default('');
            $table->integer('nominal')->default(0);
            $table->string('nojobemkl', 50)->default('');
            $table->string('nocont', 50)->default('');
            $table->string('noseal', 50)->default('');
            $table->string('nojobemkl2', 50)->default('');
            $table->string('nocont2', 50)->default('');
            $table->string('noseal2', 50)->default('');
            $table->string('statuslangsir', 50)->nullable()->default('');
            $table->string('statusperalihan', 50)->nullable()->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = DB::table((new OrderanTrucking)->getTable())->select(
                'orderantrucking.id as id_',
                'orderantrucking.nobukti',
                'orderantrucking.tglbukti',
                'container.keterangan as container_id',
                'agen.namaagen as agen_id',
                'jenisorder.keterangan as jenisorder_id',
                'pelanggan.namapelanggan as pelanggan_id',
                'orderantrucking.nominal',
                'orderantrucking.nojobemkl',
                'orderantrucking.nocont',
                'orderantrucking.noseal',
                'orderantrucking.nojobemkl2',
                'orderantrucking.nocont2',
                'orderantrucking.noseal2',
                'parameter.text as statuslangsir',
                'param2.text as statusperalihan',
                'orderantrucking.modifiedby',
                'orderantrucking.created_at',
                'orderantrucking.updated_at'
            )
            ->leftJoin('container', 'orderantrucking.container_id', '=', 'container.id')
            ->leftJoin('agen', 'orderantrucking.agen_id', '=', 'agen.id')
            ->leftJoin('jenisorder', 'orderantrucking.jenisorder_id', '=', 'jenisorder.id')
            ->leftJoin('pelanggan', 'orderantrucking.pelanggan_id', '=', 'pelanggan.id')
            ->leftJoin('parameter', 'orderantrucking.statuslangsir', '=', 'parameter.id')
            ->leftJoin('parameter AS param2', 'orderantrucking.statusperalihan', '=', 'parameter.id')
                ->orderBy('orderantrucking.id', $params['sortorder']);
        } else if ($params['sortname'] == 'nobukti' or $params['sortname'] == 'nojobemkl') {
            $query = DB::table((new OrderanTrucking)->getTable())->select(
                'orderantrucking.id as id_',
                'orderantrucking.nobukti',
                'orderantrucking.tglbukti',
                'container.keterangan as container_id',
                'agen.namaagen as agen_id',
                'jenisorder.keterangan as jenisorder_id',
                'pelanggan.namapelanggan as pelanggan_id',
                'orderantrucking.nominal',
                'orderantrucking.nojobemkl',
                'orderantrucking.nocont',
                'orderantrucking.noseal',
                'orderantrucking.nojobemkl2',
                'orderantrucking.nocont2',
                'orderantrucking.noseal2',
                'parameter.text as statuslangsir',
                'param2.text as statusperalihan',
                'orderantrucking.modifiedby',
                'orderantrucking.created_at',
                'orderantrucking.updated_at'
            )
            ->leftJoin('container', 'orderantrucking.container_id', '=', 'container.id')
            ->leftJoin('agen', 'orderantrucking.agen_id', '=', 'agen.id')
            ->leftJoin('jenisorder', 'orderantrucking.jenisorder_id', '=', 'jenisorder.id')
            ->leftJoin('pelanggan', 'orderantrucking.pelanggan_id', '=', 'pelanggan.id')
            ->leftJoin('parameter', 'orderantrucking.statuslangsir', '=', 'parameter.id')
            ->leftJoin('parameter AS param2', 'orderantrucking.statusperalihan', '=', 'parameter.id')
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('orderantrucking.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = DB::table((new OrderanTrucking)->getTable())->select(
                    'orderantrucking.id as id_',
                    'orderantrucking.nobukti',
                'orderantrucking.tglbukti',
                'container.keterangan as container_id',
                'agen.namaagen as agen_id',
                'jenisorder.keterangan as jenisorder_id',
                'pelanggan.namapelanggan as pelanggan_id',
                'orderantrucking.nominal',
                'orderantrucking.nojobemkl',
                'orderantrucking.nocont',
                'orderantrucking.noseal',
                'orderantrucking.nojobemkl2',
                'orderantrucking.nocont2',
                'orderantrucking.noseal2',
                'parameter.text as statuslangsir',
                'param2.text as statusperalihan',
                'orderantrucking.modifiedby',
                'orderantrucking.created_at',
                'orderantrucking.updated_at'
                )
                ->leftJoin('container', 'orderantrucking.container_id', '=', 'container.id')
            ->leftJoin('agen', 'orderantrucking.agen_id', '=', 'agen.id')
            ->leftJoin('jenisorder', 'orderantrucking.jenisorder_id', '=', 'jenisorder.id')
            ->leftJoin('pelanggan', 'orderantrucking.pelanggan_id', '=', 'pelanggan.id')
            ->leftJoin('parameter', 'orderantrucking.statuslangsir', '=', 'parameter.id')
            ->leftJoin('parameter AS param2', 'orderantrucking.statusperalihan', '=', 'parameter.id')
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('orderantrucking.id', $params['sortorder']);
            } else {
                $query = DB::table((new OrderanTrucking)->getTable())->select(
                    'orderantrucking.id as id_',
                    'orderantrucking.nobukti',
                'orderantrucking.tglbukti',
                'container.keterangan as container_id',
                'agen.namaagen as agen_id',
                'jenisorder.keterangan as jenisorder_id',
                'pelanggan.namapelanggan as pelanggan_id',
                'orderantrucking.nominal',
                'orderantrucking.nojobemkl',
                'orderantrucking.nocont',
                'orderantrucking.noseal',
                'orderantrucking.nojobemkl2',
                'orderantrucking.nocont2',
                'orderantrucking.noseal2',
                'parameter.text as statuslangsir',
                'param2.text as statusperalihan',
                'orderantrucking.modifiedby',
                'orderantrucking.created_at',
                'orderantrucking.updated_at'
                )
                ->leftJoin('container', 'orderantrucking.container_id', '=', 'container.id')
            ->leftJoin('agen', 'orderantrucking.agen_id', '=', 'agen.id')
            ->leftJoin('jenisorder', 'orderantrucking.jenisorder_id', '=', 'jenisorder.id')
            ->leftJoin('pelanggan', 'orderantrucking.pelanggan_id', '=', 'pelanggan.id')
            ->leftJoin('parameter', 'orderantrucking.statuslangsir', '=', 'parameter.id')
            ->leftJoin('parameter AS param2', 'orderantrucking.statusperalihan', '=', 'parameter.id')
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('orderantrucking.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'nobukti', 'tglbukti', 'container_id', 'agen_id', 'jenisorder_id', 'pelanggan_id','nominal','nojobemkl','nocont','noseal','nojobemkl2','nocont2','noseal2','statuslangsir','statusperalihan', 'modifiedby', 'created_at', 'updated_at'], $query);


        if ($del == 1) {
            if ($params['page'] == 1) {
                $baris = $params['indexRow'] + 1;
            } else {
                $hal = $params['page'] - 1;
                $bar = $hal * $params['limit'];
                $baris = $params['indexRow'] + $bar + 1;
            }


            if (DB::table($temp)
                ->where('id', '=', $baris)->exists()
            ) {
                $querydata = DB::table($temp)
                    ->select('id as row', 'id_ as id')
                    ->where('id', '=', $baris)
                    ->orderBy('id');
            } else {
                $querydata = DB::table($temp)
                    ->select('id as row', 'id_ as id')
                    ->where('id', '=', ($baris - 1))
                    ->orderBy('id');
            }
        } else {
            $querydata = DB::table($temp)
                ->select('id as row')
                ->where('id_', '=',  $id)
                ->orderBy('id');
        }


        $data = $querydata->first();
        return $data;
    }
}
