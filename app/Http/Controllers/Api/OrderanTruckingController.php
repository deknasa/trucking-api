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
use App\Http\Requests\UpdateSuratPengantarRequest;
use App\Models\Container;
use App\Models\Agen;
use App\Models\JenisOrder;
use App\Models\Pelanggan;
use App\Models\SuratPengantar;
use App\Models\Tarif;
use App\Models\TarifRincian;
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

    public function cekValidasi($id)
    {
        $orderanTrucking = new OrderanTrucking();
        $nobukti = OrderanTrucking::from(DB::raw("orderantrucking"))->where('id', $id)->first();
        $cekdata = $orderanTrucking->cekvalidasihapus($nobukti->nobukti);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
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
        $orderanTrucking = new OrderanTrucking();
        return response([
            'status' => true,
            'data' => $orderanTrucking->default()
        ]);
    }



    /**
     * @ClassName 
     */
    public function store(StoreOrderanTruckingRequest $request)
    {
        DB::beginTransaction();
        // dd($request->all());
        $inputtripmandor = $request->inputtripmandor ?? '';
        try {
            $orderantrucking = new OrderanTrucking();
            // $statusTas = $orderantrucking->getagentas($request->agen_id);
            // if ($inputtripmandor == '') {
            //     if ($statusTas->statustas == 1) {
            //         $request->validate([
            //             'nojobemkl' => 'required'
            //         ]);
            //     } else {
            //         $request->validate([
            //             'nocont' => 'required',
            //             'noseal' => 'required'
            //         ]);
            //     }

            //     $container = Container::find($request->container_id);
            //     if ($container->kodecontainer == '2X20`') {
            //         $request->validate([
            //             'nocont2' => 'required',
            //             'noseal2' => 'required'
            //         ]);
            //     }
            // }


            $group = 'ORDERANTRUCKING';
            $subgroup = 'ORDERANTRUCKING';
            $format = DB::table('parameter')
                ->where('grp', $group)
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
            $orderanTrucking->tarif_id = $request->tarifrincian_id;
            $orderanTrucking->nojobemkl = $request->nojobemkl ?? '';
            $orderanTrucking->nocont = $request->nocont;
            $orderanTrucking->noseal = $request->noseal;
            $orderanTrucking->nojobemkl2 = $request->nojobemkl2 ?? '';
            $orderanTrucking->nocont2 = $request->nocont2 ?? '';
            $orderanTrucking->noseal2 = $request->noseal2 ?? '';
            $orderanTrucking->statuslangsir = $request->statuslangsir;
            $orderanTrucking->statusperalihan = $request->statusperalihan;
            $orderanTrucking->modifiedby = auth('api')->user()->name;
            $orderanTrucking->statusformat = $format->id;

            $tarifrincian = TarifRincian::find($request->tarifrincian_id);
            $orderanTrucking->nominal = $tarifrincian->nominal;

            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $orderanTrucking->nobukti = $nobukti;

            $orderanTrucking->save();


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
        $data = OrderanTrucking::findAll($id);

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
        DB::beginTransaction();
        try {
            $orderanTrucking = new OrderanTrucking();
            $statusTas = $orderanTrucking->getagentas($request->agen_id);
            if ($statusTas->statustas == 1) {
                $request->validate([
                    'nojobemkl' => 'required'
                ]);
            } else {
                $request->validate([
                    'nocont' => 'required',
                    'noseal' => 'required'
                ]);
            }
            $orderantrucking->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $orderantrucking->container_id = $request->container_id;
            $orderantrucking->agen_id = $request->agen_id;
            $orderantrucking->jenisorder_id = $request->jenisorder_id;
            $orderantrucking->pelanggan_id = $request->pelanggan_id;
            $orderantrucking->tarif_id = $request->tarifrincian_id;
            $orderantrucking->nojobemkl = $request->nojobemkl ?? '';
            $orderantrucking->nocont = $request->nocont;
            $orderantrucking->noseal = $request->noseal;
            $orderantrucking->nojobemkl2 = $request->nojobemkl2 ?? '';
            $orderantrucking->nocont2 = $request->nocont2 ?? '';
            $orderantrucking->noseal2 = $request->noseal2 ?? '';
            $orderantrucking->statuslangsir = $request->statuslangsir;
            $orderantrucking->statusperalihan = $request->statusperalihan;
            $orderantrucking->modifiedby = auth('api')->user()->name;

            $tarifrincian = TarifRincian::from(DB::raw("tarifrincian"))->where('tarif_id', $request->tarifrincian_id)->where('container_id', $request->container_id)->first();
            $orderantrucking->nominal = $tarifrincian->nominal;

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

                $get = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
                    ->select('id', 'nominalperalihan', 'qtyton')
                    ->where('jobtrucking', $orderantrucking->nobukti)->get();

                $datadetail = json_decode($get, true);
                foreach ($datadetail as $item) {
                    $suratPengantar = [
                        'proseslain' => '1',
                        'jobtrucking' => $orderantrucking->nobukti,
                        'nojob' =>  $request->nojobemkl ?? '',
                        'nocont' =>  $request->nocont ?? '',
                        'noseal' =>  $request->noseal ?? '',
                        'nojob2' =>  $request->nojobemkl2 ?? '',
                        'nocont2' =>  $request->nocont2 ?? '',
                        'noseal2' =>  $request->noseal2 ?? '',
                        'nominalperalihan' => $item['nominalperalihan'],
                        'qtyton' => $item['qtyton'],
                        'postingdari' => 'EDIT ORDERAN TRUCKING'
                    ];
                    $newSuratPengantar = new SuratPengantar();
                    $newSuratPengantar = $newSuratPengantar->findAll($item['id']);
                    $sp = new UpdateSuratPengantarRequest($suratPengantar);
                    app(SuratPengantarController::class)->update($sp, $newSuratPengantar);
                }


                DB::commit();
            }
            /* Set position and page */
            $selected = $this->getPosition($orderantrucking, $orderantrucking->getTable());
            $orderantrucking->position = $selected->position;
            $orderantrucking->page = ceil($orderantrucking->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $orderantrucking
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

        $orderanTrucking = new OrderanTrucking();
        $orderanTrucking = $orderanTrucking->lockAndDestroy($id);

        if ($orderanTrucking) {
            $logTrail = [
                'namatabel' => strtoupper($orderanTrucking->getTable()),
                'postingdari' => 'DELETE ORDERAN TRUCKING',
                'idtrans' => $orderanTrucking->id,
                'nobuktitrans' => $orderanTrucking->id,
                'aksi' => 'DELETE',
                'datajson' => $orderanTrucking->toArray(),
                'modifiedby' => $orderanTrucking->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();
            $selected = $this->getPosition($orderanTrucking, $orderanTrucking->getTable(), true);
            $orderanTrucking->position = $selected->position;
            $orderanTrucking->id = $selected->id;
            $orderanTrucking->page = ceil($orderanTrucking->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $orderanTrucking
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
            'statuslangsir' => Parameter::where(['grp' => 'status langsir'])->get(),
            'statusperalihan' => Parameter::where(['grp' => 'status peralihan'])->get(),
            'jobemkl' => $response['data']['jobemkl'],
        ];

        return response([
            'data' => $data
        ]);
    }

    public function getOrderanTrip(Request $request)
    {
        $orderanTrucking = new OrderanTrucking();
        $agen = $request->agen;
        $tglbukti = date('Y-m-d',strtotime($request->tglbukti));
        return response([
            'data' => $orderanTrucking->getOrderanTrip($tglbukti,$agen),
            'attributes' => [
                'totalRows' => $orderanTrucking->totalRows,
                'totalPages' => $orderanTrucking->totalPages
            ]
        ]);
    }

    public function getagentas($id)
    {

        $orderantrucking = new OrderanTrucking();
        return response([
            "data" => $orderantrucking->getagentas($id)
        ]);
    }
    public function getcont($id)
    {

        $orderantrucking = new OrderanTrucking();
        return response([
            "data" => $orderantrucking->getcont($id)
        ]);
    }
}
