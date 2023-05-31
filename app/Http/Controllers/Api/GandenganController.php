<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreGandenganRequest;
use App\Http\Requests\UpdateGandenganRequest;
use App\Http\Requests\DestroyGandenganRequest;

use App\Http\Requests\StoreLogTrailRequest;

use App\Models\Gandengan;
use App\Models\LogTrail;
use App\Models\Parameter;
use App\Models\Stok;
use App\Models\StokPersediaan;


use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;

class GandenganController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $gandengan = new gandengan();
        return response([
            'data' => $gandengan->get(),
            'attributes' => [
                'totalRows' => $gandengan->totalRows,
                'totalPages' => $gandengan->totalPages
            ]
        ]);
    }

       /**
     * @ClassName 
     */
    public function report()
    {
    }
    public function cekValidasi($id) {
        $gandengan= new Gandengan();
        $cekdata=$gandengan->cekvalidasihapus($id);
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

        $gandengan = new Gandengan();
        return response([
            'status' => true,
            'data' => $gandengan->default(),
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreGandenganRequest $request)
    {
        DB::beginTransaction();
        try {
            $gandengan = new Gandengan();
            $gandengan->kodegandengan = $request->kodegandengan;
            $gandengan->keterangan = $request->keterangan ?? '';
            $gandengan->statusaktif = $request->statusaktif;
            $gandengan->modifiedby = auth('api')->user()->name;

            if ($gandengan->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($gandengan->getTable()),
                    'postingdari' => 'ENTRY GANDENGAN',
                    'idtrans' => $gandengan->id,
                    'nobuktitrans' => $gandengan->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $gandengan->toArray(),
                    'modifiedby' => $gandengan->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                $param1 = $gandengan->id;
                $param2 = $gandengan->modifiedby;
                $stokgudang = Stok::from(DB::raw("stok with (readuncommitted)"))
                    ->select(DB::raw(
                        "stok.id as stok_id,
                        0  as gudang_id,
                    0 as trado_id,"
                            . $param1 . " as gandengan_id,
                    0 as qty,'"
                            . $param2 . "' as modifiedby"
                    ))
                    ->leftjoin('stokpersediaan', function ($join) use ($param1) {
                        $join->on('stokpersediaan.stok_id', '=', 'stok.id');
                        $join->on('stokpersediaan.gandengan_id', '=', DB::raw("'" . $param1 . "'"));
                    })
                    ->where(DB::raw("isnull(stokpersediaan.id,0)"), '=', 0);



                $datadetail = json_decode($stokgudang->get(), true);

                $dataexist = $stokgudang->exists();
                $detaillogtrail = [];
                foreach ($datadetail as $item) {


                    $stokpersediaan = new StokPersediaan();
                    $stokpersediaan->stok_id = $item['stok_id'];
                    $stokpersediaan->gudang_id = $item['gudang_id'];
                    $stokpersediaan->trado_id = $item['trado_id'];
                    $stokpersediaan->gandengan_id = $item['gandengan_id'];
                    $stokpersediaan->qty = $item['qty'];
                    $stokpersediaan->modifiedby = $item['modifiedby'];
                    $stokpersediaan->save();
                    $detaillogtrail[] = $stokpersediaan->toArray();
                }

                if ($dataexist == true) {

                    $logTrail = [
                        'namatabel' => strtoupper($stokpersediaan->getTable()),
                        'postingdari' => 'STOK PERSEDIAAN',
                        'idtrans' => $gandengan->id,
                        'nobuktitrans' => $gandengan->id,
                        'aksi' => 'EDIT',
                        'datajson' => json_encode($detaillogtrail),
                        'modifiedby' => $gandengan->modifiedby
                    ];

                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    app(LogTrailController::class)->store($validatedLogTrail);
                }


                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($gandengan, $gandengan->getTable());
            $gandengan->position = $selected->position;
            $gandengan->page = ceil($gandengan->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $gandengan
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Gandengan  $gandengan
     * @return \Illuminate\Http\Response
     */
    public function show(Gandengan $gandengan)
    {
        return response([
            'status' => true,
            'data' => $gandengan
        ]);
    }



    /**
     * @ClassName 
     */
    public function update(UpdateGandenganRequest $request, Gandengan $gandengan)
    {
        DB::beginTransaction();
        try {
            $gandengan->kodegandengan = $request->kodegandengan;
            $gandengan->keterangan = $request->keterangan ?? '';
            $gandengan->statusaktif = $request->statusaktif;
            $gandengan->modifiedby = auth('api')->user()->name;

            if ($gandengan->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($gandengan->getTable()),
                    'postingdari' => 'EDIT GANDENGAN',
                    'idtrans' => $gandengan->id,
                    'nobuktitrans' => $gandengan->id,
                    'aksi' => 'EDIT',
                    'datajson' => $gandengan->toArray(),
                    'modifiedby' => $gandengan->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                $param1 = $gandengan->id;
                $param2 = $gandengan->modifiedby;
                $stokgudang = Stok::from(DB::raw("stok with (readuncommitted)"))
                    ->select(DB::raw(
                        "stok.id as stok_id,
                        0  as gudang_id,
                    0 as trado_id,"
                            . $param1 . " as gandengan_id,
                    0 as qty,'"
                            . $param2 . "' as modifiedby"
                    ))
                    ->leftjoin('stokpersediaan', function ($join) use ($param1) {
                        $join->on('stokpersediaan.stok_id', '=', 'stok.id');
                        $join->on('stokpersediaan.gandengan_id', '=', DB::raw("'" . $param1 . "'"));
                    })
                    ->where(DB::raw("isnull(stokpersediaan.id,0)"), '=', 0);



                $datadetail = json_decode($stokgudang->get(), true);

                $dataexist = $stokgudang->exists();
                $detaillogtrail = [];
                foreach ($datadetail as $item) {


                    $stokpersediaan = new StokPersediaan();
                    $stokpersediaan->stok_id = $item['stok_id'];
                    $stokpersediaan->gudang_id = $item['gudang_id'];
                    $stokpersediaan->trado_id = $item['trado_id'];
                    $stokpersediaan->gandengan_id = $item['gandengan_id'];
                    $stokpersediaan->qty = $item['qty'];
                    $stokpersediaan->modifiedby = $item['modifiedby'];
                    $stokpersediaan->save();
                    $detaillogtrail[] = $stokpersediaan->toArray();
                }

                if ($dataexist == true) {

                    $logTrail = [
                        'namatabel' => strtoupper($stokpersediaan->getTable()),
                        'postingdari' => 'STOK PERSEDIAAN',
                        'idtrans' => $gandengan->id,
                        'nobuktitrans' => $gandengan->id,
                        'aksi' => 'EDIT',
                        'datajson' => json_encode($detaillogtrail),
                        'modifiedby' => $gandengan->modifiedby
                    ];

                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    app(LogTrailController::class)->store($validatedLogTrail);
                }


                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($gandengan, $gandengan->getTable());
            $gandengan->position = $selected->position;
            $gandengan->page = ceil($gandengan->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $gandengan
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function destroy(DestroyGandenganRequest $request, $id)
    {
        DB::beginTransaction();

        $gandengan = new Gandengan();
        $gandengan = $gandengan->lockAndDestroy($id);
        if ($gandengan) {
            $logTrail = [
                'namatabel' => strtoupper($gandengan->getTable()),
                'postingdari' => 'DELETE GANDENGAN',
                'idtrans' => $gandengan->id,
                'nobuktitrans' => $gandengan->id,
                'aksi' => 'DELETE',
                'datajson' => $gandengan->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();


            /* Set position and page */
            $selected = $this->getPosition($gandengan, $gandengan->getTable(), true);
            $gandengan->position = $selected->position;
            $gandengan->id = $selected->id;
            $gandengan->page = ceil($gandengan->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $gandengan
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

   /**
     * @ClassName 
     */
    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $gandengans = $decodedResponse['data'];

        $i = 0;
        foreach ($gandengans as $index => $params) {

            $statusaktif = $params['statusaktif'];

            $result = json_decode($statusaktif, true);

            $statusaktif = $result['MEMO'];


            $gandengans[$i]['statusaktif'] = $statusaktif;

        
            $i++;


        }

        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'ID',
                'index' => 'id',
            ],
            [
                'label' => 'Kode Gandengan',
                'index' => 'kodegandengan',
            ],
            [
                'label' => 'Keterangan',
                'index' => 'keterangan',
            ],
            [
                'label' => 'Status Aktif',
                'index' => 'statusaktif',
            ],
        ];

        $this->toExcel('Gandengan', $gandengans, $columns);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('gandengan')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function combostatus(Request $request)
    {

        $params = [
            'status' => $request->status ?? '',
            'grp' => $request->grp ?? '',
            'subgrp' => $request->subgrp ?? '',
        ];
        $temp = '##temp' . rand(1, 10000);
        if ($params['status'] == 'entry') {
            $query = Parameter::select('id', 'text as keterangan')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);
        } else {
            Schema::create($temp, function ($table) {
                $table->integer('id')->length(11)->nullable();
                $table->string('parameter', 50)->nullable();
                $table->string('param', 50)->nullable();
            });

            DB::table($temp)->insert(
                [
                    'id' => '0',
                    'parameter' => 'ALL',
                    'param' => '',
                ]
            );

            $queryall = Parameter::select('id', 'text as parameter', 'text as param')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);

            $query = DB::table($temp)
                ->unionAll($queryall);
        }

        $data = $query->get();

        return response([
            'data' => $data
        ]);
    }
}
