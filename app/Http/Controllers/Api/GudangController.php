<?php

namespace App\Http\Controllers\Api;

use App\Models\Gudang;
use App\Http\Requests\StoreGudangRequest;
use App\Http\Requests\UpdateGudangRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;
use App\Models\Stok;
use App\Models\StokPersediaan;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class GudangController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $gudang = new Gudang();
        return response([
            'data' => $gudang->get(),
            'attributes' => [
                'totalRows' => $gudang->totalRows,
                'totalPages' => $gudang->totalPages
            ]
        ]);
    }

    public function cekValidasi($id) {
        $gudang= new Gudang();
        $cekdata=$gudang->cekvalidasihapus($id);
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
        $gudang = new Gudang();
        return response([
            'status' => true,
            'data' => $gudang->default()
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreGudangRequest $request)
    {
        DB::beginTransaction();

        try {
            $gudang = new Gudang();
            $gudang->gudang = $request->gudang;
            $gudang->statusaktif = $request->statusaktif;
            $gudang->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($gudang->save()) {

                $logTrail = [
                    'namatabel' => strtoupper($gudang->getTable()),
                    'postingdari' => 'ENTRY GUDANG',
                    'idtrans' => $gudang->id,
                    'nobuktitrans' => $gudang->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $gudang->toArray(),
                    'modifiedby' => $gudang->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                $param1 = $gudang->id;
                $param2 = $gudang->modifiedby;

                $statushitungstok=DB::table('parameter')->from(
                    DB::raw("parameter with (readuncommitted)")
                )
                ->select(
                    'id'
                )
                ->where('grp','=','STATUS HITUNG STOK')
                ->where('subgrp','=','STATUS HITUNG STOK')
                ->where('text','=','HITUNG STOK')
                ->first();

                $tempmasuk = '##tempmasuk' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempmasuk, function ($table) {
                    $table->unsignedBigInteger('stok_id')->nullable();
                    $table->unsignedBigInteger('gudang_id')->nullable();
                    $table->double('qty', 15,2)->nullable();
                });


                $querymasuk=DB::table('penerimaanstokdetail')->from(
                    DB::raw("penerimaanstokdetail as a with (readuncommitted)")
                )
                ->select (
                    'a.stok_id',
                    'b.gudang_id',
                    DB::raw("sum(a.qty) as qty"),
                )
                ->join(DB::raw("penerimaanstokheader as b"),'a.penerimaanstokheader_id','b.id')
                ->join(DB::raw("penerimaanstok as c"),'b.penerimaanstok_id','c.id')
                ->where('c.statushitungstok','=',$statushitungstok->id)
                ->whereRaw("isnull(b.gudang_id,0)<>0")
                ->groupby('a.stok_id','b.gudang_id');

                DB::table($tempmasuk)->insertUsing([
                    'stok_id',
                    'gudang_id',
                    'qty',
                ], $querymasuk);       
                
                $tempkeluar = '##tempkeluar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempkeluar, function ($table) {
                    $table->unsignedBigInteger('stok_id')->nullable();
                    $table->unsignedBigInteger('gudang_id')->nullable();
                    $table->double('qty', 15,2)->nullable();
                });


                $querykeluar=DB::table('pengeluaranstokdetail')->from(
                    DB::raw("pengeluaranstokdetail as a with (readuncommitted)")
                )
                ->select (
                    'a.stok_id',
                    'b.gudang_id',
                    DB::raw("sum(a.qty) as qty"),
                )
                ->join(DB::raw("pengeluaranstokheader as b"),'a.pengeluaranstokheader_id','b.id')
                ->join(DB::raw("pengeluaranstok as c"),'b.pengeluaranstok_id','c.id')
                ->where('c.statushitungstok','=',$statushitungstok->id)
                ->whereRaw("isnull(b.gudang_id,0)<>0")
                ->groupby('a.stok_id','b.gudang_id');

                DB::table($tempkeluar)->insertUsing([
                    'stok_id',
                    'gudang_id',
                    'qty',
                ], $querykeluar);                     

                $stokgudang = Stok::from(DB::raw("stok with (readuncommitted)"))
                    ->select(DB::raw(
                        "stok.id as stok_id,"
                            . $param1 . "  as gudang_id,
                    0 as trado_id,
                    0 as gandengan_id,
                    (isnull(b.qty,0)-isnull(C.Qty,0)) as qty,'"
                            . $param2 . "' as modifiedby"
                    ))
                    ->leftjoin('stokpersediaan', function ($join) use ($param1) {
                        $join->on('stokpersediaan.stok_id', '=', 'stok.id');
                        $join->on('stokpersediaan.gudang_id', '=', DB::raw("'" . $param1 . "'"));
                    })
                    ->leftjoin(DB::raw($tempmasuk. " as b"),'stok.id','b.stok_id')
                    ->leftjoin(DB::raw($tempkeluar. " as c"),'stok.id','c.stok_id')

                    ->where(DB::raw("isnull(stokpersediaan.id,0)"), '=', 0);



                    // dd($stokgudang->get());
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
                        'idtrans' => $gudang->id,
                        'nobuktitrans' => $gudang->id,
                        'aksi' => 'EDIT',
                        'datajson' => json_encode($detaillogtrail),
                        'modifiedby' => $gudang->modifiedby
                    ];

                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    app(LogTrailController::class)->store($validatedLogTrail);
                }


                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($gudang, $gudang->getTable());
            $gudang->position = $selected->position;
            $gudang->page = ceil($gudang->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $gudang
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Gudang $gudang)
    {
        return response([
            'status' => true,
            'data' => $gudang
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(StoreGudangRequest $request, Gudang $gudang)
    {
        DB::beginTransaction();
        try {
            $gudang->gudang = $request->gudang;
            $gudang->statusaktif = $request->statusaktif;
            $gudang->modifiedby = auth('api')->user()->name;

            if ($gudang->save()) {


                $logTrail = [
                    'namatabel' => strtoupper($gudang->getTable()),
                    'postingdari' => 'EDIT GUDANG',
                    'idtrans' => $gudang->id,
                    'nobuktitrans' => $gudang->id,
                    'aksi' => 'EDIT',
                    'datajson' => $gudang->toArray(),
                    'modifiedby' => $gudang->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);


                $param1 = $gudang->id;
                $param2 = $gudang->modifiedby;
                
                $statushitungstok=DB::table('parameter')->from(
                    DB::raw("parameter with (readuncommitted)")
                )
                ->select(
                    'id'
                )
                ->where('grp','=','STATUS HITUNG STOK')
                ->where('subgrp','=','STATUS HITUNG STOK')
                ->where('text','=','HITUNG STOK')
                ->first();

                $tempmasuk = '##tempmasuk' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempmasuk, function ($table) {
                    $table->unsignedBigInteger('stok_id')->nullable();
                    $table->unsignedBigInteger('gudang_id')->nullable();
                    $table->double('qty', 15,2)->nullable();
                });


                $querymasuk=DB::table('penerimaanstokdetail')->from(
                    DB::raw("penerimaanstokdetail as a with (readuncommitted)")
                )
                ->select (
                    'a.stok_id',
                    'b.gudang_id',
                    DB::raw("sum(a.qty) as qty"),
                )
                ->join(DB::raw("penerimaanstokheader as b"),'a.penerimaanstokheader_id','b.id')
                ->join(DB::raw("penerimaanstok as c"),'b.penerimaanstok_id','c.id')
                ->where('c.statushitungstok','=',$statushitungstok->id)
                ->whereRaw("isnull(b.gudang_id,0)<>0")
                ->groupby('a.stok_id','b.gudang_id');

                DB::table($tempmasuk)->insertUsing([
                    'stok_id',
                    'gudang_id',
                    'qty',
                ], $querymasuk);       
                
                $tempkeluar = '##tempkeluar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempkeluar, function ($table) {
                    $table->unsignedBigInteger('stok_id')->nullable();
                    $table->unsignedBigInteger('gudang_id')->nullable();
                    $table->double('qty', 15,2)->nullable();
                });


                $querykeluar=DB::table('pengeluaranstokdetail')->from(
                    DB::raw("pengeluaranstokdetail as a with (readuncommitted)")
                )
                ->select (
                    'a.stok_id',
                    'b.gudang_id',
                    DB::raw("sum(a.qty) as qty"),
                )
                ->join(DB::raw("pengeluaranstokheader as b"),'a.pengeluaranstokheader_id','b.id')
                ->join(DB::raw("pengeluaranstok as c"),'b.pengeluaranstok_id','c.id')
                ->where('c.statushitungstok','=',$statushitungstok->id)
                ->whereRaw("isnull(b.gudang_id,0)<>0")
                ->groupby('a.stok_id','b.gudang_id');

                DB::table($tempkeluar)->insertUsing([
                    'stok_id',
                    'gudang_id',
                    'qty',
                ], $querykeluar);                     

                $stokgudang = Stok::from(DB::raw("stok with (readuncommitted)"))
                    ->select(DB::raw(
                        "stok.id as stok_id,"
                            . $param1 . "  as gudang_id,
                    0 as trado_id,
                    0 as gandengan_id,
                    (isnull(b.qty,0)-isnull(C.Qty,0)) as qty,'"
                            . $param2 . "' as modifiedby"
                    ))
                    ->leftjoin('stokpersediaan', function ($join) use ($param1) {
                        $join->on('stokpersediaan.stok_id', '=', 'stok.id');
                        $join->on('stokpersediaan.gudang_id', '=', DB::raw("'" . $param1 . "'"));
                    })
                    ->leftjoin(DB::raw($tempmasuk. " as b"),'stok.id','b.stok_id')
                    ->leftjoin(DB::raw($tempkeluar. " as c"),'stok.id','c.stok_id')

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
                        'idtrans' => $gudang->id,
                        'nobuktitrans' => $gudang->id,
                        'aksi' => 'EDIT',
                        'datajson' => json_encode($detaillogtrail),
                        'modifiedby' => $gudang->modifiedby
                    ];

                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    app(LogTrailController::class)->store($validatedLogTrail);
                }



                DB::commit();
            }
            /* Set position and page */
            $selected = $this->getPosition($gudang, $gudang->getTable());
            $gudang->position = $selected->position;
            $gudang->page = ceil($gudang->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $gudang
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

        $gudang = new Gudang();
        $gudang = $gudang->lockAndDestroy($id);
        if ($gudang) {
            $logTrail = [
                'namatabel' => strtoupper($gudang->getTable()),
                'postingdari' => 'DELETE GUDANG',
                'idtrans' => $gudang->id,
                'nobuktitrans' => $gudang->id,
                'aksi' => 'DELETE',
                'datajson' => $gudang->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();
            /* Set position and page */
            $selected = $this->getPosition($gudang, $gudang->getTable());
            $gudang->position = $selected->position;
            $gudang->id = $selected->id;
            $gudang->page = ceil($gudang->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $gudang
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('gudang')->getColumns();

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
            'statusgudang' => Parameter::where(['grp' => 'status gudang'])->get(),
        ];

        return response([
            'data' => $data
        ]);
    }

    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $gudangs = $decodedResponse['data'];

        $i = 0;
        foreach ($gudangs as $index => $params) {

            $statusaktif = $params['statusaktif'];

            $result = json_decode($statusaktif, true);

            $statusaktif = $result['MEMO'];


            $gudangs[$i]['statusaktif'] = $statusaktif;

        
            $i++;


        }
        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'Gudang',
                'index' => 'gudang',
            ],
            [
                'label' => 'Status Aktif',
                'index' => 'statusaktif',
            ],
        ];

        $this->toExcel('Gudang', $gudangs, $columns);
    }
}
