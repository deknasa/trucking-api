<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Controllers\Controller;

use App\Models\PengeluaranStokDetailFifo;
use App\Models\PengeluaranStok;
use App\Models\PenerimaanStokDetail;
use App\Models\PengeluaranStokDetail;
use App\Models\PengeluaranStokHeader;
use App\Models\Parameter;
use App\Models\StokPersediaan;
use App\Models\Stok;

use App\Http\Requests\StorePengeluaranStokDetailFifoRequest;
use App\Http\Requests\UpdatePengeluaranStokDetailFifoRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\UpdateJurnalUmumHeaderRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PengeluaranStokDetailFifoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePengeluaranStokDetailFifoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePengeluaranStokDetailFifoRequest $request)
    {

        DB::beginTransaction();
        try {




            $tempmasuk = '##tempmasuk' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempmasuk, function ($table) {
                $table->string('fntrans', 100)->default('');
                $table->dateTime('ftgl')->default('1900/1/1');
                $table->string('fkstck', 100)->default('');
                $table->string('fkgdg', 100)->default('');
                $table->double('fqty', 15, 2)->default(0);
                $table->double('fhargasat', 15, 2)->default(0);
                $table->bigInteger('furut')->default(0);
            });

            $tempalur = '##tempalur' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempalur, function ($table) {
                $table->string('fntranskeluar', 100)->default('');
                $table->double('fqtyout', 15, 2)->default(0);
                $table->double('fqtyoutberjalan', 15, 2)->default(0);
                $table->string('fntransmasuk', 100)->default('');
                $table->double('fqtyinberjalan', 15, 2)->default(0);
                $table->double('fselisih', 15, 2)->default(0);
                $table->bigInteger('furut')->default(0);
            });


            $querytempmasuk = Penerimaanstokdetail::select(
                'B.nobukti as nobukti',
                'B.tglbukti as tglbukti',
                'D.namastok as fkstck',
                'C.gudang as fkgdg',
                db::raw("(penerimaanstokdetail.qty-isnull(penerimaanstokdetail.qtykeluar,0)) as qty"),
                'penerimaanstokdetail.harga as harga',
                db::raw("row_number() Over(Order By B.tglbukti ,penerimaanstokdetail.id ) as urut")
            )
                ->join('penerimaanstokheader as B', 'B.id', 'penerimaanstokdetail.penerimaanstokheader_id')
                ->join('gudang as C', 'C.id', 'B.gudang_id')
                ->join('stok as D', 'D.id', 'penerimaanstokdetail.stok_id')
                ->where('B.gudang_id', '=',  $request->gudang_id)
                ->where('penerimaanstokdetail.stok_id', '=',  $request->stok_id)
                // ->where('penerimaanstokdetail.qtykeluar', '<',  'penerimaanstokdetail.qty')
                ->whereRaw("penerimaanstokdetail.qtykeluar<penerimaanstokdetail.qty")
                ->orderBy('B.tglbukti', 'Asc')
                ->orderBy('penerimaanstokdetail.id', 'Asc');


            DB::table($tempmasuk)->insertUsing([
                'fntrans',
                'ftgl',
                'fkstck',
                'fkgdg',
                'fqty',
                'fhargasat',
                'furut'
            ], $querytempmasuk);

            $querymsk = DB::table($tempmasuk)
                ->select(
                    DB::raw("sum(fqty) as qty")
                )
                ->first();

            $qtyin = $querymsk->qty ?? 0;



            $validator = Validator::make(
                $request->all(),
                [
                    'qty' => [
                        "required",
                        "numeric",
                        "gt:0",
                        "max:" . $qtyin
                    ]
                ],
                [
                    'qty.max' => ':attribute' . ' ' . app(ErrorController::class)->geterror('SMIN')->keterangan,

                ],
                [
                    'qty' => 'qty',
                ],
            );

            if (!$validator->passes()) {
                // dump($qtyin);
                // dump($validator->messages());
                // dd($validator->passes());

                // return $validator->messages();

                return [
                    'error' => true,
                    'errors' => $validator->messages()
                ];
            }



            $tempkeluar = '##tempkeluar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempkeluar, function ($table) {
                $table->string('fntrans', 100)->default('');
                $table->dateTime('ftgl')->default('1900/1/1');
                $table->string('fkstck', 100)->default('');
                $table->string('fkgdg', 100)->default('');
                $table->double('fqty', 15, 2)->default(0);
                $table->double('furut', 15, 2)->default(0);
                $table->bigInteger('fid')->default(0);
            });



            $querytempkeluar = PengeluaranStokDetail::select(
                'b.nobukti as FNtrans',
                'b.tglbukti as Ftgl',
                DB::raw("rtrim(ltrim(str(" . $request->stok_id . "))) as FKstck"),
                'b.gudang_id as  FKgdg',
                'PengeluaranStokDetail.qty as FQty',
                DB::raw(" row_number() Over(Order By B.tglbukti ,PengeluaranStokDetail.id)  as urut"),
                'PengeluaranStokDetail.id'
            )
                ->join('pengeluaranstokheader as B', 'B.id', 'pengeluaranstokdetail.pengeluaranstokheader_id')
                ->join('gudang as C', 'C.id', 'B.gudang_id')
                ->join('stok as D', 'D.id', 'pengeluaranstokdetail.stok_id')
                ->where('B.gudang_id', '=',  $request->gudang_id)
                ->where('pengeluaranstokdetail.stok_id', '=',  $request->stok_id)
                ->where('pengeluaranstokdetail.nobukti', '=',  $request->nobukti)
                ->orderBy('B.tglbukti', 'Asc')
                ->orderBy('pengeluaranstokdetail.id', 'Asc');

            DB::table($tempkeluar)->insertUsing([
                'fntrans',
                'ftgl',
                'fkstck',
                'fkgdg',
                'fqty',
                'furut',
                'fid'
            ], $querytempkeluar);

            // $test=DB::table($tempmasuk)->get();

            // $datakeluar = DB::table($tempkeluar)->select(
            //     'furut',
            //     'fid'
            // )
            //     ->orderBy('furut', 'desc')
            //     ->first();

            // DB::table($tempkeluar)->insert([
            //     'fntrans' => $request->nobukti,
            //     'ftgl' => date('Y-m-d', strtotime($request->tglbukti)),
            //     'fkstck' => $request->stok_id,
            //     'fkgdg' => $request->gudang_id,
            //     'fqty' => $request->qty,
            //     'furut' => $datakeluar->furut + 1,
            //     'fid' => $datakeluar->fid + 1,
            // ]);

            $tempkeluarrekap = '##Tempkeluarrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempkeluarrekap, function ($table) {
                $table->string('fntrans', 100)->default('');
                $table->dateTime('ftgl')->default('1900/1/1');
                $table->string('fkstck', 100)->default('');
                $table->string('fkgdg', 100)->default('');
                $table->double('fqty', 15, 2)->default(0);
                $table->double('furut', 15, 2)->default(0);
                $table->double('fqty2', 15, 2)->default(0);
                $table->string('fntransmasuk', 100)->default('');
                $table->bigInteger('fid')->default(0);
            });

            $tempmasukrekap = '##Tempmasukrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempmasukrekap, function ($table) {
                $table->string('fntrans', 100)->default('');
                $table->dateTime('ftgl')->default('1900/1/1');
                $table->string('fkstck', 100)->default('');
                $table->string('fkgdg', 100)->default('');
                $table->double('fqty', 15, 2)->default(0);
                $table->double('furut', 15, 2)->default(0);
                $table->double('fqty2', 15, 2)->default(0);
            });


            $querytempkeluarrekap = DB::table($tempkeluar)->from(
                DB::raw($tempkeluar . " as i")
            )
                ->select(
                    'i.fntrans',
                    'i.ftgl',
                    'i.fkstck',
                    'i.fkgdg',
                    'i.fqty',
                    'i.furut',
                    DB::raw(
                        "isnull(sum(i.fqty) over (
                partition by i.fkstck
                order by i.ftgl, i.fntrans
                rows between unbounded preceding and 0 preceding
             ),0) as fqty2"
                    ),
                    'i.fid'
                );

            DB::table($tempkeluarrekap)->insertUsing([
                'fntrans',
                'ftgl',
                'fkstck',
                'fkgdg',
                'fqty',
                'furut',
                'fqty2',
                'fid'
            ], $querytempkeluarrekap);


            $querytempmasukrekap = DB::table($tempmasuk)->from(
                DB::raw($tempmasuk . " as i")
            )
                ->select(
                    'i.fntrans',
                    'i.ftgl',
                    'i.fkstck',
                    'i.fkgdg',
                    'i.fqty',
                    'i.furut',
                    DB::raw(
                        "isnull(sum(i.fqty) over (
                        partition by i.fkstck
                        order by i.ftgl, i.fntrans
                        rows between unbounded preceding and 0 preceding
                     ),0) as fqty2"
                    )
                );


            DB::table($tempmasukrekap)->insertUsing([
                'fntrans',
                'ftgl',
                'fkstck',
                'fkgdg',
                'fqty',
                'furut',
                'fqty2'
            ], $querytempmasukrekap);


            $tempkeluarupdate = '##tempkeluarupdate' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempkeluarupdate, function ($table) {
                $table->string('fntransmasuk', 100)->default('');
                $table->double('fqty', 15, 2)->default(0);
            });


            $queryloopkeluarrekap = DB::table($tempkeluarrekap)->select(
                'fntrans',
                'ftgl',
                'fkstck',
                'fkgdg',
                'fqty',
                'furut',
                'fqty2'
            )->get();

            // dd($queryloopkeluarrekap);
            $aqty = 1;

            $curut = 0;
            $datadetail = json_decode($queryloopkeluarrekap, true);
            foreach ($datadetail as $item) {
                // dump('-');
                // dump($aqty);
                // dump($item['fqty2']);
                // dump('AA');
                while ($aqty <= $item['fqty2']) {
                    // dump($curut);
                    $curut += 1;
                    $datamasuk = DB::table($tempmasukrekap)->select(
                        'fntrans',
                        'fqty2'
                    )
                        ->whereRaw($aqty . "<=fqty2")
                        ->orderBy('fqty2', 'asc')
                        ->first();

                    $selqty = $datamasuk->fqty2 - $item['fqty2'];


                    DB::table($tempalur)->insert([
                        'fntranskeluar' => $item['fntrans'],
                        'fqtyout' => $item['fqty'],
                        'fqtyoutberjalan' => $item['fqty2'],
                        'fntransmasuk' => $datamasuk->fntrans,
                        'fqtyinberjalan' => $datamasuk->fqty2,
                        'fselisih' => $selqty,
                        'furut' => $curut,
                    ]);

                    $aqty += 1;
                }
            }


            $tempalurrekap = '##Tempalurrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempalurrekap, function ($table) {
                $table->string('fntrans', 100)->default('');
                $table->string('fntransmasuk', 100)->default('');
                $table->double('fjumlah', 15, 2)->default(0);
                $table->double('furut', 15, 2)->default(0);
            });


            $querytempalurrekap = DB::table($tempalur)->from(
                DB::raw($tempalur . " as i")
            )
                ->select(
                    'i.fntranskeluar',
                    'i.fntransmasuk',
                    DB::raw("count(i.fntransmasuk) as fjumlah"),
                    DB::raw("max(i.furut) as furut")
                )
                ->groupBy('i.fntranskeluar', 'i.fntransmasuk');


            DB::table($tempalurrekap)->insertUsing([
                'fntrans',
                'fntransmasuk',
                'fjumlah',
                'furut'
            ], $querytempalurrekap);



            $temphasil = '##Temphasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temphasil, function ($table) {
                $table->string('nobukti', 100)->default('');
                $table->dateTime('tglbukti')->default('1900/1/1');
                $table->bigInteger('stok_id')->default(0);
                $table->bigInteger('gudang_id')->default(0);
                $table->double('qty', 15, 2)->default(0);
                $table->double('urut', 15, 2)->default(0);
                $table->double('qty2', 15, 2)->default(0);
                $table->longText('penerimaan_nobukti')->default('');
                $table->double('penerimaan_qty', 15, 2)->default(0);
                $table->double('penerimaan_terpakai', 15, 2)->default(0);
                $table->double('penerimaan_harga', 15, 2)->default(0);
                $table->bigInteger('id')->default(0);
            });

            $temphasil2 = '##Temphasil2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temphasil2, function ($table) {
                $table->string('nobukti', 100)->default('');
                $table->dateTime('tglbukti')->default('1900/1/1');
                $table->bigInteger('stok_id')->default(0);
                $table->bigInteger('gudang_id')->default(0);
                $table->float('qty', 15, 2)->default(0);
                $table->bigInteger('urut')->default(0);
                $table->float('qty2', 15, 2)->default(0);
                $table->longText('penerimaan_nobukti')->default('');
                $table->float('penerimaan_qty', 15, 2)->default(0);
                $table->float('penerimaan_harga', 15, 2)->default(0);
            });


            $querytemphasil2 = DB::table($tempkeluarrekap)->from(
                DB::raw($tempkeluarrekap . " as A")
            )
                ->select(
                    'A.fntrans',
                    'A.ftgl',
                    DB::raw($request->stok_id . " as stok_id"),
                    DB::raw($request->gudang_id . " as gudang_id"),
                    'A.fqty',
                    DB::raw("row_number() Over(Order By A.FUrut,B.FUrut) As FUrut"),
                    'A.fqty2',
                    'B.fntransmasuk',
                    'B.fjumlah as fqty',
                    'C.fhargasat as fhargasat',
                )
                ->leftjoin(DB::raw($tempalurrekap . " as B"), 'A.fntrans', 'b.fntrans')
                ->leftjoin(DB::raw($tempmasuk . " as C"), 'B.fntransmasuk', 'c.fntrans')

                ->orderBy('A.furut', 'Asc');

            DB::table($temphasil2)->insertUsing([
                'nobukti',
                'tglbukti',
                'stok_id',
                'gudang_id',
                'qty',
                'urut',
                'qty2',
                'penerimaan_nobukti',
                'penerimaan_qty',
                'penerimaan_harga',
            ], $querytemphasil2);


            $querytemphasil = DB::table($temphasil2)->from(
                DB::raw($temphasil2 . " as A")
            )
                ->select(
                    'A.nobukti',
                    'A.tglbukti',
                    DB::raw($request->stok_id . " as stok_id"),
                    DB::raw($request->gudang_id . " as gudang_id"),
                    'A.qty',
                    'A.urut',
                    'A.qty2',
                    'A.penerimaan_nobukti',
                    DB::raw("isnull(b.fqty,0) as fqty"),
                    DB::raw("isnull(sum(A.penerimaan_qty) over (
                            partition by A.stok_id,A.gudang_id,A.nobukti
                            order by a.urut
                            rows between unbounded preceding and 0 preceding
                         ),0) as fsaldoqty"),
                    DB::raw("isnull(b.fhargasat,0) as fhargasat"),
                    DB::raw("isnull(c.fid,0) as fid"),
                )
                ->leftjoin(DB::raw($tempmasuk . " as B"), 'A.penerimaan_nobukti', 'B.fntrans')
                ->leftjoin(DB::raw($tempkeluar . " as C"), 'A.nobukti', 'C.fntrans')
                ->orderBy('A.urut', 'Asc');

            DB::table($temphasil)->insertUsing([
                'nobukti',
                'tglbukti',
                'stok_id',
                'gudang_id',
                'qty',
                'urut',
                'qty2',
                'penerimaan_nobukti',
                'penerimaan_qty',
                'penerimaan_terpakai',
                'penerimaan_harga',
                'id',
            ], $querytemphasil);


            // $test = DB::table($temphasil)->orderBy('urut', 'Asc')->get();

            $datalist = DB::table($temphasil2);

            // dd($datalist->get());

            $datadetail = json_decode($datalist->get(), true);
            $totalharga = 0;
            $spk = Parameter::from(
                db::Raw("parameter with (readuncommitted)")
            )
                ->where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();

            $statusApp = Parameter::from(
                db::Raw("parameter with (readuncommitted)")
            )
                ->where('grp', 'STATUS APPROVAL')->where('subgrp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();




            $jurnalHeader = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $request->nobukti,
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'keterangan' => $request->keterangan,
                'postingdari' => "ENTRY HUTANG",
                'statusapproval' => $statusApp->id,
                'userapproval' => "",
                'tglapproval' => "",
                'modifiedby' => auth('api')->user()->name,
                'statusformat' => "0",
            ];
            $jurnaldetail=[];
            foreach ($datadetail as $item) {

                $pengeluaranstokdetailfifo = new PengeluaranStokDetailFifo();
                $pengeluaranstokdetailfifo->pengeluaranstokheader_id = $request->pengeluaranstokheader_id ?? 0;
                $pengeluaranstokdetailfifo->nobukti = $request->nobukti ?? '';
                $pengeluaranstokdetailfifo->stok_id = $request->stok_id ?? 0;
                $pengeluaranstokdetailfifo->gudang_id = $request->gudang_id ?? 0;
                $pengeluaranstokdetailfifo->urut = $item['urut'] ?? 0;
                $pengeluaranstokdetailfifo->qty = $item['qty'] ?? 0;
                $pengeluaranstokdetailfifo->penerimaanstokheader_nobukti = $item['penerimaan_nobukti'] ?? '';
                $pengeluaranstokdetailfifo->penerimaanstok_qty = $item['penerimaan_qty'] ?? 0;
                $pengeluaranstokdetailfifo->penerimaanstok_harga = $item['penerimaan_harga'] ?? 0;
                $pengeluaranstokdetailfifo->modifiedby = $request->modifiedby ?? '';
                $total = $item['penerimaan_qty'] * $item['penerimaan_harga'];
                if ($pengeluaranstokdetailfifo->save()) {
                    if ($request->pengeluaranstok_id == $spk->text) {
                        $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                            ->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'DEBET')->first();
                        $memo = json_decode($getCoaDebet->memo, true);
                        $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                            ->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'KREDIT')->first();
                        $memokredit = json_decode($getCoaKredit->memo, true);

                        $jurnaldetail[] = [
                                'nobukti' => $request->nobukti,
                                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                                'coa' =>  $memo['JURNAL'],
                                'nominal' => $total,
                                'keterangan' => $request->detail_keterangan,
                                'modifiedby' => auth('api')->user()->name,
                                'baris' => 0,
                        ];

                        $jurnaldetail []= 
                            [
                                'nobukti' => $request->nobukti,
                                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                                'coa' =>  $memokredit['JURNAL'],
                                'nominal' => ($total * -1),
                                'keterangan' => $request->detail_keterangan,
                                'modifiedby' => auth('api')->user()->name,
                                'baris' => 0,
                            ]
                        ;

                        // $jurnaldetail = array_merge($jurnaldetail, $jurnalDetail);
                    }
                }

                $totalharga += ($item['penerimaan_harga'] * $item['penerimaan_qty']);


                $penerimaanstokdetail  = PenerimaanStokDetail::lockForUpdate()->where("stok_id", $item['stok_id'])
                    ->where("nobukti", $item['penerimaan_nobukti'])
                    ->firstorFail();
                $penerimaanstokdetail->qtykeluar += $item['penerimaan_qty'] ?? 0;
                $penerimaanstokdetail->save();
            }
            
            if ($request->pengeluaranstok_id == $spk->text) {
                // dump($jurnalHeader);
// dd($jurnaldetail);
                $jurnal = $this->storeJurnal($jurnalHeader, $jurnaldetail);
                // dd($jurnal);
                if (!$jurnal['status']) {
                    throw new \Throwable($jurnal['message']);
                }
            }




            $pengeluaranstokdetail  = PengeluaranStokDetail::lockForUpdate()->where("stok_id", $item['stok_id'])
                ->where("nobukti", $request->nobukti)
                ->firstorFail();

            $hrgsat = $totalharga / $request->qty;
            $pengeluaranstokdetail->harga =   $hrgsat;
            $pengeluaranstokdetail->total =  $totalharga;
            $pengeluaranstokdetail->save();




            DB::commit();
            return [
                'error' => false,
            ];
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PengeluaranStokDetailFifo  $pengeluaranStokDetailFifo
     * @return \Illuminate\Http\Response
     */
    public function show(PengeluaranStokDetailFifo $pengeluaranStokDetailFifo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PengeluaranStokDetailFifo  $pengeluaranStokDetailFifo
     * @return \Illuminate\Http\Response
     */
    public function edit(PengeluaranStokDetailFifo $pengeluaranStokDetailFifo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePengeluaranStokDetailFifoRequest  $request
     * @param  \App\Models\PengeluaranStokDetailFifo  $pengeluaranStokDetailFifo
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePengeluaranStokDetailFifoRequest $request, PengeluaranStokDetailFifo $pengeluaranStokDetailFifo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PengeluaranStokDetailFifo  $pengeluaranStokDetailFifo
     * @return \Illuminate\Http\Response
     */
    public function destroy(PengeluaranStokDetailFifo $pengeluaranStokDetailFifo)
    {
        //
    }

    private function storeJurnal($header, $detail)
    {
        DB::beginTransaction();

        try {

            $jurnal = new StoreJurnalUmumHeaderRequest($header);
            $jurnals = app(JurnalUmumHeaderController::class)->store($jurnal);

            $detailLog = [];

            foreach ($detail as $value) {
                $value['jurnalumum_id'] = $jurnals->original['data']['id'];
                $detail = new StoreJurnalUmumDetailRequest($value);
                $datadetails = app(JurnalUmumDetailController::class)->store($detail);

                $detailLog[] = $datadetails['detail']->toArray();
            }

            $datalogtrail = [
                'namatabel' => strtoupper($datadetails['tabel']),
                'postingdari' => 'ENTRY HUTANG',
                'idtrans' => $jurnals->original['idlogtrail'],
                'nobuktitrans' => $header['nobukti'],
                'aksi' => 'ENTRY',
                'datajson' => $detailLog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            DB::commit();
            return [
                'status' => true,
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
