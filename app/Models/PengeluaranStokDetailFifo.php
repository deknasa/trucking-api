<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\Api\ErrorController;
use Illuminate\Validation\ValidationException;

class PengeluaranStokDetailFifo extends MyModel
{
    use HasFactory;

    protected $table = 'pengeluaranstokdetailfifo';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];


    public function processStore(PengeluaranStokHeader $pengeluaranStokHeader, array $data): PengeluaranStokDetailFifo
    {
        $qty = $data['qty'] ?? 0;

        $totalharga = 0;
        $spk = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();

        // dd($data);

        $tempfifo = '##tempfifo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempfifo, function ($table) {
            $table->string('penerimaanstok_nobukti', 100)->nullable();
            $table->bigInteger('stok_id')->nullable();

            // $table->double('penerimaanstok_qty', 15, 2)->nullable();
            $table->double('qty', 15, 2)->nullable();
            $table->bigInteger('id')->nullable();
            $table->double('penerimaanstokheader_totalterpakai', 15, 2)->nullable();
        });

        $temprekappengeluaranfifo = '##temprekappengeluaranfifo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekappengeluaranfifo, function ($table) {
            $table->id();
            $table->bigInteger('stokheader_id')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->bigInteger('stok_id')->nullable();
            $table->bigInteger('gudang_id')->nullable();
            $table->bigInteger('urut')->nullable();
            $table->double('qty', 15, 2)->nullable();
            $table->string('penerimaanstokheader_nobukti', 100)->nullable();
            $table->double('penerimaanstok_qty', 15, 2)->nullable();
            $table->double('penerimaanstok_harga', 15, 2)->nullable();
            $table->double('penerimaanstokheader_total', 15, 2)->nullable();
            $table->double('penerimaanstokheader_totalterpakai', 15, 2)->nullable();
        });

        $pengeluaranstokheader_id=$data['pengeluaranstokheader_id'] ;
        $queryfifo = db::table('pengeluaranstokdetailfifo')->from(db::raw("pengeluaranstokdetailfifo a with (readuncommitted)"))
            ->select(
                'a.pengeluaranstokheader_id as stokheader_id',
                'a.nobukti as nobukti',
                'a.stok_id as stok_id',
                'a.gudang_id as gudang_id',
                'a.urut as urut',
                'a.qty as qty',
                'a.penerimaanstokheader_nobukti as penerimaanstokheader_nobukti',
                'a.penerimaanstok_qty as penerimaanstokheader_qty',
                'a.penerimaanstok_harga as penerimaanstokheader_harga',
                'a.penerimaanstokheader_total as penerimaanstokheader_total',
                'a.penerimaanstokheader_totalterpakai as penerimaanstokheader_totalterpakai',
            )
            ->where('a.stok_id', '=',   $data['stok_id'])
            ->where('a.gudang_id', '=',   $data['gudang_id'])
            ->where('a.pengeluaranstokheader_id', '<=',   $pengeluaranstokheader_id)
            ->orderby('a.id');


        DB::table($temprekappengeluaranfifo)->insertUsing([
            "stokheader_id",
            "nobukti",
            "stok_id",
            "gudang_id",
            "urut",
            "qty",
            "penerimaanstokheader_nobukti",
            "penerimaanstok_qty",
            "penerimaanstok_harga",
            "penerimaanstokheader_total",
            "penerimaanstokheader_totalterpakai",
        ], $queryfifo);

        $queryfifo = db::table('penerimaanstokdetailfifo')->from(db::raw("penerimaanstokdetailfifo a with (readuncommitted)"))
            ->select(
                'a.penerimaanstokheader_id as stokheader_id',
                'a.nobukti as nobukti',
                'a.stok_id as stok_id',
                'a.gudang_id as gudang_id',
                'a.urut as urut',
                'a.qty as qty',
                'a.penerimaanstokheader_nobukti as penerimaanstokheader_nobukti',
                'a.penerimaanstok_qty as penerimaanstokheader_qty',
                'a.penerimaanstok_harga as penerimaanstokheader_harga',
                'a.penerimaanstokheader_total as penerimaanstokheader_total',
                'a.penerimaanstokheader_totalterpakai as penerimaanstokheader_totalterpakai',
            )
            ->where('a.stok_id', '=',   $data['stok_id'])
            ->where('a.gudang_id', '=',   $data['gudang_id'])
            ->orderby('a.id');


        DB::table($temprekappengeluaranfifo)->insertUsing([
            "stokheader_id",
            "nobukti",
            "stok_id",
            "gudang_id",
            "urut",
            "qty",
            "penerimaanstokheader_nobukti",
            "penerimaanstok_qty",
            "penerimaanstok_harga",
            "penerimaanstokheader_total",
            "penerimaanstokheader_totalterpakai",
        ], $queryfifo);


        $a = 0;
        $atotalharga = 0;
        $totalterpakai2 = 0;
        $totalterpakai3 = 0;
        $kondisi = true;
        while ($kondisi == true) {

            DB::delete(DB::raw("delete " . $tempfifo));


            $queryfifo = db::table($temprekappengeluaranfifo)->from(db::raw($temprekappengeluaranfifo . " a with (readuncommitted)"))
                ->select(
                    'a.penerimaanstokheader_nobukti as penerimaanstok_nobukti',
                    'a.stok_id as stok_id',
                    // db::raw("sum(a.penerimaanstok_qty) as penerimaanstok_qty"),
                    db::raw("sum(a.qty) as qty"),
                    db::raw("max(b.id) as id"),
                    db::raw("round(sum(a.penerimaanstokheader_totalterpakai),2) as penerimaanstokheader_totalterpakai"),
                )
                ->join(db::raw("penerimaanstokheader b with (readuncommitted)"), 'a.penerimaanstokheader_nobukti', 'b.nobukti')
                // ->join(db::raw("penerimaanstokdetail c with (readuncommitted)"), 'b.nobukti', 'c.nobukti')
                ->join(db::raw("penerimaanstokdetail c with (readuncommitted)"), function ($join) {
                    $join->on('b.nobukti', '=', 'c.nobukti');
                    $join->on('a.stok_id', '=', 'c.stok_id');
                })
                ->where('c.stok_id', '=',   $data['stok_id'])
                ->whereRaw("(b.gudang_id=" .   $data['gudang_id'] . " or b.gudangke_id=" . $data['gudang_id'] . ")")
                ->groupBY('a.stok_id')
                ->groupBY('a.penerimaanstokheader_nobukti');


            DB::table($tempfifo)->insertUsing([
                'penerimaanstok_nobukti',
                'stok_id',
                'qty',
                'id',
                'penerimaanstokheader_totalterpakai',
            ], $queryfifo);

            $querysisa = db::table('penerimaanstokdetail')->from(db::raw("penerimaanstokdetail a with (readuncommitted)"))
                ->select(
                    db::raw("(a.qty-isnull(B.qty,0)) as qtysisa"),
                    'a.nobukti',
                    'a.qty',
                    'a.harga',
                    'a.total',
                    'c.id as penerimaanstok_id',
                    db::raw("(a.total-isnull(b.penerimaanstokheader_totalterpakai,0)) as totalsisa"),
                    db::raw("isnull(b.penerimaanstokheader_totalterpakai,0) as totalterpakai"),
                )
                // ->leftjoin(db::raw($tempfifo . " b "), 'a.nobukti', 'b.penerimaanstok_nobukti')
                ->leftjoin(db::raw($tempfifo . " b "), function ($join) {
                    $join->on('a.nobukti', '=', 'b.penerimaanstok_nobukti');
                    $join->on('a.stok_id', '=', 'b.stok_id');
                })
                ->join(db::raw("penerimaanstokheader c "), 'a.nobukti', 'c.nobukti')
                ->where('a.stok_id', '=',   $data['stok_id'])
                ->whereRaw("(c.gudang_id=" .   $data['gudang_id'] . " or c.gudangke_id=" . $data['gudang_id'] . ")")
                ->whereRaw("(a.qty-isnull(B.qty,0))<>0 ")
                ->orderBy('c.tglbukti', 'asc')
                ->orderBy('c.id', 'asc')
                ->orderBy('a.id', 'asc')

                ->first();

                // if ($data['stok_id'] == 4735) {
                //     $querysisa = db::table('penerimaanstokdetail')->from(db::raw("penerimaanstokdetail a with (readuncommitted)"))
                //     ->select(
                //         db::raw("(a.qty-isnull(B.qty,0)) as qtysisa"),
                //         'a.nobukti',
                //         'a.qty',
                //         'a.harga',
                //         'a.total',
                //         'c.id as penerimaanstok_id',
                //         db::raw("(a.total-isnull(b.penerimaanstokheader_totalterpakai,0)) as totalsisa"),
                //         db::raw("isnull(b.penerimaanstokheader_totalterpakai,0) as totalterpakai"),
                //     )
                //     // ->leftjoin(db::raw($tempfifo . " b "), 'a.nobukti', 'b.penerimaanstok_nobukti')
                //     ->leftjoin(db::raw($tempfifo . " b "), function ($join) {
                //         $join->on('a.nobukti', '=', 'b.penerimaanstok_nobukti');
                //         $join->on('a.stok_id', '=', 'b.stok_id');
                //     })
                //     ->join(db::raw("penerimaanstokheader c "), 'a.nobukti', 'c.nobukti')
                //     ->where('a.stok_id', '=',   $data['stok_id'])
                //     ->whereRaw("(c.gudang_id=" .   $data['gudang_id'] . " or c.gudangke_id=" . $data['gudang_id'] . ")")
                //     ->whereRaw("(a.qty-isnull(B.qty,0))<>0 ")
                //     ->orderBy('c.tglbukti', 'asc')
                //     ->orderBy('c.id', 'asc')
                //     ->orderBy('a.id', 'asc')
    
                //     ->get();

                //     dd( $querysisa);
        
                // }

            // $querytest = db::table('penerimaanstokdetail')->from(db::raw("penerimaanstokdetail a with (readuncommitted)"))
            // ->select(
            //     db::raw("(a.qty-isnull(B.qty,0)) as qtysisa"),
            //     'a.nobukti',
            //     'a.qty',
            //     'a.harga',
            //     'a.total',
            //     'c.id as penerimaanstok_id',
            //     db::raw("(a.total-isnull(b.penerimaanstokheader_totalterpakai,0)) as totalsisa"),
            //     'a.id'
            //     )
            // // ->leftjoin(db::raw($tempfifo . " b "), 'a.nobukti', 'b.penerimaanstok_nobukti')
            // ->leftjoin(db::raw($tempfifo . " b "), function ($join) {
            //     $join->on('a.nobukti', '=', 'b.penerimaanstok_nobukti');
            //     $join->on('a.stok_id', '=', 'b.stok_id');
            // })
            // ->join(db::raw("penerimaanstokheader c "), 'a.nobukti', 'c.nobukti')
            // ->where('a.stok_id', '=',   $data['stok_id'])
            // ->whereRaw("(c.gudang_id=" .   $data['gudang_id'] . " or c.gudangke_id=" . $data['gudang_id'] . ")")
            // ->whereRaw("(a.qty-isnull(B.qty,0))<>0 ")
            // ->orderBy('c.tglbukti', 'asc')
            // ->orderBy('c.id', 'asc')
            // ->orderBy('a.id', 'asc')
            // ->get();

            // if ($data['stok_id']==5200) {
            //     dump(db::table($tempfifo) ->get());    
            //     dump($querysisa);    
            //     dump($querytest);    
            // }

            $a = $a + 1;
            // if ($a == 3) {
            //     dd('test');
            // } else {
            //     dump(db::table($tempfifo)->get());
            //     // dump( $querysisa);
            // }


            if (isset($querysisa)) {
                $qtysisa = $querysisa->qtysisa ?? 0;
                if ($qty <= $qtysisa) {

                    // $hargatotalterpakai = ($querysisa->totalsisa / $querysisa->qtysisa);
                    $totalsisa = round(($querysisa->total - $querysisa->totalterpakai), 2);
                    $hargatotalterpakai = ($totalsisa / $querysisa->qtysisa);
                    // if ($data['stok_id'] == 4735) {
                    //     dump(1);
                    //     dump($querysisa->totalterpakai);
                    //     dump($querysisa->total);
                    //     dump($querysisa->totalsisa);
                    //     dump($querysisa->qtysisa);
                    // }


                    // dump($totalterpakai);
                    // $num = (($querysisa->total / $querysisa->qty) * $qty);
                    // $totalterpakai = floor($num * 100) / 100;

                    $penambahannilai_id=11;
                    $nobuktiambil=$querysisa->nobukti ?? '';
                    $tambahanilai=db::table("penerimaanstokdetail")->from(db::raw("penerimaanstokdetail a with (readuncommitted)")) 
                    ->select(
                        db::raw("(c.total/c.qty) as nominal")
                    )
                    ->join(db::raw("penerimaanstokheader b with (readuncommitted)"),'a.nobukti','b.nobukti')
                    ->join(DB::raw("penerimaanstokdetail as c with (readuncommitted)"), function ($join) {
                        $join->on("b.penerimaanstok_nobukti", "=", "c.nobukti");
                        $join->on("a.stok_id", "=", "c.stok_id");
                    })                    
                    ->where('a.penerimaanstok_nobukti',$nobuktiambil)
                    ->where('a.stok_id',$data['stok_id'])
                    ->where('b.penerimaanstok_id',$penambahannilai_id)
                    ->first()->nominal ?? 0;

                    // dd($tambahanilai);
                    // $totalterpakai = round(($hargatotalterpakai * $qty), 2);

                    $tharga=$querysisa->harga+$tambahanilai;
                    $ttotal=$querysisa->total+($tambahanilai*$querysisa->qty );
                    $ttambahannilai=$tambahanilai*$querysisa->qty;
                     $totalterpakai = round(($hargatotalterpakai * $qty), 2);


                     $totalterpakai2 += $totalterpakai;
                    //  dump($totalterpakai2,1);
                     $totalterpakai3 += ($totalterpakai+$ttambahannilai );
                     $pengeluaranStokDetailFifo = new pengeluaranStokDetailFifo();
                    $pengeluaranStokDetailFifo->pengeluaranstokheader_id = $data['pengeluaranstokheader_id'] ?? 0;
                    $pengeluaranStokDetailFifo->nobukti = $data['nobukti'] ?? '';
                    $pengeluaranStokDetailFifo->stok_id = $data['stok_id'] ?? 0;
                    $pengeluaranStokDetailFifo->gudang_id = $data['gudang_id'] ?? 0;
                    $pengeluaranStokDetailFifo->urut = $a;
                    $pengeluaranStokDetailFifo->qty = $qty ?? 0;
                    $pengeluaranStokDetailFifo->penerimaanstokheader_nobukti = $querysisa->nobukti ?? '';
                    $pengeluaranStokDetailFifo->penerimaanstok_qty = $querysisa->qty ?? 0;
                    $pengeluaranStokDetailFifo->penerimaanstok_harga =   $tharga ?? 0;//$querysisa->harga ?? 0;
                    $pengeluaranStokDetailFifo->penerimaanstokheader_total =  $ttotal ?? 0;//$querysisa->total ?? 0;
                    $pengeluaranStokDetailFifo->penerimaanstokheader_totalterpakai = $totalterpakai+$ttambahannilai ?? 0;
                    $pengeluaranStokDetailFifo->modifiedby = $data['modifiedby'] ?? '';

                    DB::table($temprekappengeluaranfifo)->insert([
                        'stokheader_id' => $data['pengeluaranstokheader_id'] ?? 0,
                        'nobukti' =>  $data['nobukti'] ?? '',
                        'stok_id' => $data['stok_id'] ?? 0,
                        'gudang_id' => $data['gudang_id'] ?? 0,
                        'urut' =>  $a,
                        'qty' =>  $qty ?? 0,
                        'penerimaanstokheader_nobukti' => $querysisa->nobukti ?? '',
                        'penerimaanstok_qty' => $querysisa->qty ?? 0,
                        'penerimaanstok_harga' =>   $tharga ?? 0,//$querysisa->harga ?? 0,
                        'penerimaanstokheader_total' =>   $ttotal ?? 0,//$querysisa->total ?? 0,
                        'penerimaanstokheader_totalterpakai' => $totalterpakai ?? 0,
                    ]);

                    $belitotalsisa = $querysisa->totalsisa ?? 0;
                    $beliqtysisa = $querysisa->qtysisa ?? 0;

                    $belitotal = $querysisa->total ?? 0;
                    $beliqty = $querysisa->qty ?? 0;

                    $zqty = $qty ?? 0;
                    // lama ryan 09-11-2023
                    // $zharga = $querysisa->harga ?? 0;
                    $zharga = round(($belitotalsisa / $beliqtysisa), 10) ?? 0;

                    $atotalharga = $atotalharga + round(($zqty * (($belitotalsisa / $beliqtysisa))), 2);
                    // lama ryan 09-11-2023
                    // $atotalharga = $atotalharga + ($zqty * ($belitotal / $beliqty));


                    // 
                    $ksqty = $qty ?? 0;
                    // lama ryan 09-11-2023
                    // $ksharga = $querysisa->harga ?? 0;
                    // $kstotal = $ksqty * ($belitotal / $beliqty);

                    $ksharga = round(($belitotalsisa / $beliqtysisa), 10) ?? 0;
                    $kstotal = round(($ksqty * ($belitotalsisa / $beliqtysisa)), 2);

                    $ksnobukti = $data['nobukti'] ?? '';

                    $pengeluaranstok_id = db::table("pengeluaranstokheader")->from(db::raw("pengeluaranstokheader as a with (readuncommitted)"))
                        ->select('a.pengeluaranstok_id', 'a.tglbukti')->where('a.nobukti', $ksnobukti)->first();

                    $kspengeluaranstok_id = $pengeluaranstok_id->pengeluaranstok_id ?? 0;
                    $kstglbukti = $pengeluaranstok_id->tglbukti ?? '1900/1/1';

                    $urutfifo = db::table("pengeluaranstok")->from(db::raw("pengeluaranstok as a with (readuncommitted)"))
                        ->select('a.urutfifo')->where('a.id', $kspengeluaranstok_id)->first()->urutfifo ?? 0;



                    if ($kspengeluaranstok_id != 6) {
                        $kartuStok = (new KartuStok())->processStore([
                            "gudang_id" => $data['gudang_id'] ?? 0,
                            "trado_id" => 0,
                            "gandengan_id" => 0,
                            "stok_id" => $data['stok_id'] ?? 0,
                            "nobukti" => $data['nobukti'] ?? '',
                            "tglbukti" => $kstglbukti,
                            "qtymasuk" => 0,
                            "nilaimasuk" =>  0,
                            "qtykeluar" => $qty ?? 0,
                            // "nilaikeluar" => $kstotal,
                            "nilaikeluar" => $totalterpakai+$ttambahannilai,
                            "urutfifo" => $urutfifo,
                        ]);
                    }

                    if ($data['pengeluaranstok_id'] == $spk->text) {
                        $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                            ->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'DEBET')->first();
                        $memo = json_decode($getCoaDebet->memo, true);
                        $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                            ->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'KREDIT')->first();
                        $memokredit = json_decode($getCoaKredit->memo, true);
                    }

                    $aksqty = $querysisa->qty ?? 0;

                    // lama ryan 09-11-2023
                    // $aksharga = $querysisa->harga ?? 0;
                    $aksharga = round(($belitotalsisa / $beliqtysisa), 10) ?? 0;
                    $aksnobukti = $querysisa->nobukti ?? '';
                    $aksstok_id = $data['stok_id'] ?? 0;

                    $totalharga += round(($aksharga  * $aksqty), 2);



                    // 

                    $kondisi = false;
                    if (!$pengeluaranStokDetailFifo->save()) {
                        throw new \Exception("Error Simpan Pengeluaran Detail fifo.");
                    }
                } else {
                    // dd('test');
                    $qty = $qty - $qtysisa;
                    // $hargatotalterpakai = ($querysisa->totalsisa / $querysisa->qtysisa);
                    $totalsisa = round(($querysisa->total - $querysisa->totalterpakai), 2);
                    $hargatotalterpakai = ($totalsisa / $querysisa->qtysisa);

                    // if ($data['stok_id'] == 4735) {
                    //     dump(2);
                    //     dump($querysisa->totalterpakai);
                    //     dump($querysisa->total);
                    //     dump($querysisa->totalsisa);
                    //     dump($querysisa->qtysisa);
                    // }

                    $totalterpakai = round(($hargatotalterpakai * $qtysisa), 2);
                    // $totalterpakai = ( $hargatotalterpakai* $qtysisa);
                    // dump($totalterpakai);
                    // $num = (($querysisa->total / $querysisa->qty) * $qtysisa);
                    // $totalterpakai = floor($num * 100) / 100;

                    // dd($hargatotalterpakai,$qtysisa);
                    $penambahannilai_id=11;
                    $nobuktiambil=$data['nobukti'] ?? '';
                    $tambahanilai=db::table("penerimaanstokdetail")->from(db::raw("penerimaanstokdetail a with (readuncommitted)")) 
                    ->select(
                        db::raw("(c.total/c.qty) as nominal")
                    )
                    ->join(db::raw("penerimaanstokheader b with (readuncommitted)"),'a.nobukti','b.nobukti')
                    ->join(DB::raw("penerimaanstokdetail as c with (readuncommitted)"), function ($join) {
                        $join->on("b.penerimaanstok_nobukti", "=", "c.nobukti");
                        $join->on("a.stok_id", "=", "c.stok_id");
                    })                    
                    ->where('a.penerimaanstok_nobukti',$nobuktiambil)
                    ->where('a.stok_id',$data['stok_id'])
                    ->where('b.penerimaanstok_id',$penambahannilai_id)
                    ->first()->nominal ?? 0;


                    $tharga=$querysisa->harga+$tambahanilai;
                    $ttotal=$querysisa->total+($tambahanilai*$querysisa->qty );
                    // $totalterpakai2 += $totalterpakai;
                    $ttambahannilai=$tambahanilai*$querysisa->qty;
                    // $totalterpakai3 += ($totalterpakai+$ttambahannilai );

                    // dump($totalterpakai2,1);
                    $totalterpakai2 += $totalterpakai;
                    $totalterpakai3 += ($totalterpakai+$ttambahannilai );
                    $pengeluaranStokDetailFifo = new pengeluaranStokDetailFifo();
                    $pengeluaranStokDetailFifo->pengeluaranstokheader_id = $data['pengeluaranstokheader_id'] ?? 0;
                    $pengeluaranStokDetailFifo->nobukti = $data['nobukti'] ?? '';
                    $pengeluaranStokDetailFifo->stok_id = $data['stok_id'] ?? 0;
                    $pengeluaranStokDetailFifo->gudang_id = $data['gudang_id'] ?? 0;
                    $pengeluaranStokDetailFifo->urut = $a;
                    $pengeluaranStokDetailFifo->qty = $qtysisa ?? 0;
                    $pengeluaranStokDetailFifo->penerimaanstokheader_nobukti = $querysisa->nobukti ?? '';
                    $pengeluaranStokDetailFifo->penerimaanstok_qty = $querysisa->qty ?? 0;
                    $pengeluaranStokDetailFifo->penerimaanstok_harga =  $tharga ?? 0;//$querysisa->harga ?? 0;
                    $pengeluaranStokDetailFifo->penerimaanstokheader_total = $ttotal ?? 0; //$querysisa->total ?? 0;
                    $pengeluaranStokDetailFifo->penerimaanstokheader_totalterpakai = $totalterpakai+$ttambahannilai ?? 0;
                    $pengeluaranStokDetailFifo->modifiedby = $data['modifiedby'] ?? '';

                    DB::table($temprekappengeluaranfifo)->insert([
                        'stokheader_id' => $data['pengeluaranstokheader_id'] ?? 0,
                        'nobukti' =>  $data['nobukti'] ?? '',
                        'stok_id' => $data['stok_id'] ?? 0,
                        'gudang_id' => $data['gudang_id'] ?? 0,
                        'urut' =>  $a,
                        'qty' => $qtysisa ?? 0,
                        'penerimaanstokheader_nobukti' => $querysisa->nobukti ?? '',
                        'penerimaanstok_qty' => $querysisa->qty ?? 0,
                        'penerimaanstok_harga' =>  $tharga  ?? 0,//$querysisa->harga ?? 0,
                        'penerimaanstokheader_total' => $ttotal ?? 0, //$querysisa->total ?? 0,
                        'penerimaanstokheader_totalterpakai' => $totalterpakai ?? 0,
                    ]);

                    // ryan 09-11-2023
                    $belitotal = $querysisa->total ?? 0;
                    $beliqty = $querysisa->qty ?? 0;

                    $belitotalsisa = $querysisa->totalsisa ?? 0;
                    $beliqtysisa = $querysisa->qtysisa ?? 0;

                    $zqty = $qtysisa ?? 0;
                    // ryan 09-11-2023
                    // $zharga = $querysisa->harga ?? 0;
                    $zharga = round(($belitotalsisa / $beliqtysisa), 10) ?? 0;

                    // ryan 09-11-2023

                    // $atotalharga = $atotalharga + ($zqty * ($belitotal / $beliqty));
                    $atotalharga = $atotalharga + round(($zqty * ($belitotalsisa / $beliqtysisa)), 2);

                    // 
                    $ksqty = $qtysisa ?? 0;

                    // ryan 09-11-2023
                    // $ksharga = $querysisa->harga ?? 0;
                    // $kstotal = $ksqty * ($belitotal / $beliqty);

                    $ksharga = round(($belitotalsisa / $beliqtysisa), 10) ?? 0;
                    $kstotal = round($ksqty * ($belitotalsisa / $beliqtysisa), 2);

                    $ksnobukti = $data['nobukti'] ?? '';

                    $pengeluaranstok_id = db::table("pengeluaranstokheader")->from(db::raw("pengeluaranstokheader as a with (readuncommitted)"))
                        ->select('a.pengeluaranstok_id', 'a.tglbukti')->where('a.nobukti', $ksnobukti)->first();

                    $kspengeluaranstok_id = $pengeluaranstok_id->pengeluaranstok_id ?? 0;
                    $kstglbukti = $pengeluaranstok_id->tglbukti ?? '1900/1/1';

                    $urutfifo = db::table("pengeluaranstok")->from(db::raw("pengeluaranstok as a with (readuncommitted)"))
                        ->select('a.urutfifo')->where('a.id', $kspengeluaranstok_id)->first()->urutfifo ?? 0;



                    if ($kspengeluaranstok_id != 6) {
                        $kartuStok = (new KartuStok())->processStore([
                            "gudang_id" => $data['gudang_id'] ?? 0,
                            "trado_id" => 0,
                            "gandengan_id" => 0,
                            "stok_id" => $data['stok_id'] ?? 0,
                            "nobukti" => $data['nobukti'] ?? '',
                            "tglbukti" => $kstglbukti,
                            "qtymasuk" => 0,
                            "nilaimasuk" =>  0,
                            "qtykeluar" => $qtysisa ?? 0,
                            // "nilaikeluar" => $kstotal,
                            "nilaikeluar" => $totalterpakai+$ttambahannilai,
                            "urutfifo" => $urutfifo,
                        ]);
                    }

                    if ($data['pengeluaranstok_id'] == $spk->text) {
                        $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                            ->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'DEBET')->first();
                        $memo = json_decode($getCoaDebet->memo, true);
                        $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                            ->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'KREDIT')->first();
                        $memokredit = json_decode($getCoaKredit->memo, true);
                    }

                    $aksqty = $querysisa->qty ?? 0;
                    // ryan 09-11-2023
                    // $aksharga = $querysisa->harga ?? 0;

                    $aksharga = round(($querysisa->totalsisa / $querysisa->qtysisa), 10) ?? 0;

                    $aksnobukti = $querysisa->nobukti ?? '';
                    $aksstok_id = $data['stok_id'] ?? 0;

                    $totalharga += round(($aksharga *  $aksqty), 2);






                    if (!$pengeluaranStokDetailFifo->save()) {
                        throw new \Exception("Error Simpan pengeluaran detail fifo Detail fifo.");
                    }
                }
            }
        }
        // 
        // if ($data['stok_id'] == 4735) {
        //     dd('test');
        // }
    //    dd($totalterpakai3);
        $nobuktipengeluaran = $data['nobukti'] ?? '';
        $stokidpengeluaran = $data['stok_id'] ?? 0;
        $pengeluaranstokdetail  = PengeluaranStokDetail::lockForUpdate()->where("stok_id", $stokidpengeluaran)
            ->where("nobukti", $nobuktipengeluaran)
            ->firstorFail();

        // $totalharga = $atotalharga;
        // $totalharga = $totalterpakai2;
        $totalharga = $totalterpakai3;

        // dump($totalharga);
        // dd($data['qty']);
        $hrgsat = round(($totalharga / $data['qty']), 10);

        if ($data['pengeluaranstok_id'] == 2) {
            $totdetailharga = $data['detail_harga'];
            // $selisih = $hrgsat - $totdetailharga;
            $selisih = $tharga - $totdetailharga;
            

            $hrgsat = $data['detail_harga'];
            // $totalharga = $hrgsat * $data['qty'];
            $totalharga = $tharga * $data['qty'];
        } else {
            $selisih = 0;
        }
        // dd($totalharga);
        $pengeluaranstokdetail->harga =   $tharga; //$hrgsat;
        $pengeluaranstokdetail->total =  $totalharga;

        $pengeluaranstokdetail->selisihhargafifo =  $selisih;
        // $pengeluaranstokdetail->save();
        if (!$pengeluaranstokdetail->save()) {
            throw new \Exception("Error storing pengeluaran Stok Detail  update fifo. ");
        }

        $qtyterimarekap = DB::table("pengeluaranstokdetailfifo")->from(db::raw("pengeluaranstokdetailfifo a with (readuncommitted)"))
            ->select(
                db::raw("sum(a.qty) as qty")
            )
            ->where("penerimaanstokheader_nobukti", $aksnobukti)
            ->where("stok_id",  $aksstok_id)
            ->first()->qty ?? 0;

        $qtyterimarekapklr = DB::table("penerimaanstokdetailfifo")->from(db::raw("penerimaanstokdetailfifo a with (readuncommitted)"))
            ->select(
                db::raw("sum(a.qty) as qty")
            )
            ->where("penerimaanstokheader_nobukti", $aksnobukti)
            ->where("stok_id",  $aksstok_id)
            ->first()->qty ?? 0;

        $totalqtysisa = $qtyterimarekap + $qtyterimarekapklr;


        $penerimaanstokdetail  = PenerimaanStokDetail::lockForUpdate()->where("stok_id", $aksstok_id)
            ->where("nobukti", $aksnobukti)
            ->firstorFail();
        $penerimaanstokdetail->qtykeluar = $totalqtysisa;
        $penerimaanstokdetail->save();
        //
        return $pengeluaranStokDetailFifo;
    }
    public function processStoreOld(PengeluaranStokHeader $pengeluaranStokHeader, array $data): PengeluaranStokDetailFifo
    {

        $tempmasuk = '##tempmasuk' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempmasuk, function ($table) {
            $table->string('fntrans', 100)->nullable();
            $table->dateTime('ftgl')->nullable();
            $table->string('fkstck', 100)->nullable();
            $table->string('fkgdg', 100)->nullable();
            $table->double('fqty', 15, 2)->nullable();
            $table->double('fhargasat', 15, 2)->nullable();
            $table->bigInteger('furut')->nullable();
        });

        $tempalur = '##tempalur' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempalur, function ($table) {
            $table->string('fntranskeluar', 100)->nullable();
            $table->double('fqtyout', 15, 2)->nullable();
            $table->double('fqtyoutberjalan', 15, 2)->nullable();
            $table->string('fntransmasuk', 100)->nullable();
            $table->double('fqtyinberjalan', 15, 2)->nullable();
            $table->double('fselisih', 15, 2)->nullable();
            $table->bigInteger('furut')->nullable();
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
            ->where('B.gudang_id', '=',  $data['gudang_id'])
            ->where('penerimaanstokdetail.stok_id', '=',  $data['stok_id'])
            // ->where('penerimaanstokdetail.qtykeluar', '<',  'penerimaanstokdetail.qty')
            ->whereRaw("isnull(penerimaanstokdetail.qtykeluar,0)<penerimaanstokdetail.qty")
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
            ->join('gudang as C', 'C.id', 'B.gudangke_id')
            ->join('stok as D', 'D.id', 'penerimaanstokdetail.stok_id')
            ->where('B.gudangke_id', '=',  $data['gudang_id'])
            ->where('penerimaanstokdetail.stok_id', '=',  $data['stok_id'])
            // ->where('penerimaanstokdetail.qtykeluar', '<',  'penerimaanstokdetail.qty')
            ->whereRaw("isnull(penerimaanstokdetail.qtykeluar,0)<penerimaanstokdetail.qty")
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


        if ($data['qty'] > $qtyin) {
            // throw new \Exception("QTY " .app(ErrorController::class)->geterror('SMIN')->keterangan);
            throw ValidationException::withMessages(['qty' => "QTY " . app(ErrorController::class)->geterror('SMIN')->keterangan]);
        }


        $tempkeluar = '##tempkeluar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempkeluar, function ($table) {
            $table->string('fntrans', 100)->nullable();
            $table->dateTime('ftgl')->nullable();
            $table->string('fkstck', 100)->nullable();
            $table->string('fkgdg', 100)->nullable();
            $table->double('fqty', 15, 2)->nullable();
            $table->double('furut', 15, 2)->nullable();
            $table->bigInteger('fid')->nullable();
        });



        $querytempkeluar = PengeluaranStokDetail::select(
            'b.nobukti as FNtrans',
            'b.tglbukti as Ftgl',
            DB::raw("rtrim(ltrim(str(" . $data['stok_id'] . "))) as FKstck"),
            'b.gudang_id as  FKgdg',
            'PengeluaranStokDetail.qty as FQty',
            DB::raw(" row_number() Over(Order By B.tglbukti ,PengeluaranStokDetail.id)  as urut"),
            'PengeluaranStokDetail.id'
        )
            ->join('pengeluaranstokheader as B', 'B.id', 'pengeluaranstokdetail.pengeluaranstokheader_id')
            ->join('stok as D', 'D.id', 'pengeluaranstokdetail.stok_id')

            ->where('pengeluaranstokdetail.stok_id', '=',  $data['stok_id'])
            ->where('pengeluaranstokdetail.nobukti', '=',  $data['nobukti'])
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


        $tempkeluarrekap = '##Tempkeluarrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempkeluarrekap, function ($table) {
            $table->string('fntrans', 100)->nullable();
            $table->dateTime('ftgl')->nullable();
            $table->string('fkstck', 100)->nullable();
            $table->string('fkgdg', 100)->nullable();
            $table->double('fqty', 15, 2)->nullable();
            $table->double('furut', 15, 2)->nullable();
            $table->double('fqty2', 15, 2)->nullable();
            $table->string('fntransmasuk', 100)->nullable();
            $table->bigInteger('fid')->nullable();
        });

        $tempmasukrekap = '##Tempmasukrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempmasukrekap, function ($table) {
            $table->string('fntrans', 100)->nullable();
            $table->dateTime('ftgl')->nullable();
            $table->string('fkstck', 100)->nullable();
            $table->string('fkgdg', 100)->nullable();
            $table->double('fqty', 15, 2)->nullable();
            $table->double('furut', 15, 2)->nullable();
            $table->double('fqty2', 15, 2)->nullable();
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
            $table->string('fntransmasuk', 100)->nullable();
            $table->double('fqty', 15, 2)->nullable();
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

        dd($queryloopkeluarrekap);
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





        // dd(db::table($tempalur)->get());


        $tempalurrekap = '##Tempalurrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempalurrekap, function ($table) {
            $table->string('fntrans', 100)->nullable();
            $table->string('fntransmasuk', 100)->nullable();
            $table->double('fjumlah', 15, 2)->nullable();
            $table->double('furut', 15, 2)->nullable();
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
            $table->string('nobukti', 100)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->bigInteger('stok_id')->nullable();
            $table->bigInteger('gudang_id')->nullable();
            $table->double('qty', 15, 2)->nullable();
            $table->double('urut', 15, 2)->nullable();
            $table->double('qty2', 15, 2)->nullable();
            $table->longText('penerimaan_nobukti')->nullable();
            $table->double('penerimaan_qty', 15, 2)->nullable();
            $table->double('penerimaan_terpakai', 15, 2)->nullable();
            $table->double('penerimaan_harga', 15, 2)->nullable();
            $table->bigInteger('id')->nullable();
        });

        $temphasil2 = '##Temphasil2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temphasil2, function ($table) {
            $table->string('nobukti', 100)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->bigInteger('stok_id')->nullable();
            $table->bigInteger('gudang_id')->nullable();
            $table->float('qty', 15, 2)->nullable();
            $table->bigInteger('urut')->nullable();
            $table->float('qty2', 15, 2)->nullable();
            $table->longText('penerimaan_nobukti')->nullable();
            $table->float('penerimaan_qty', 15, 2)->nullable();
            $table->float('penerimaan_harga', 15, 2)->nullable();
        });


        $querytemphasil2 = DB::table($tempkeluarrekap)->from(
            DB::raw($tempkeluarrekap . " as A")
        )
            ->select(
                'A.fntrans',
                'A.ftgl',
                DB::raw($data['stok_id'] . " as stok_id"),
                DB::raw($data['gudang_id'] . " as gudang_id"),
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
                DB::raw($data['stok_id'] . " as stok_id"),
                DB::raw($data['gudang_id'] . " as gudang_id"),
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

        foreach ($datadetail as $item) {

            $pengeluaranStokDetailFifo = new PengeluaranStokDetailFifo();
            $pengeluaranStokDetailFifo->pengeluaranstokheader_id = $data['pengeluaranstokheader_id'] ?? 0;
            $pengeluaranStokDetailFifo->nobukti = $data['nobukti'] ?? '';
            $pengeluaranStokDetailFifo->stok_id = $data['stok_id'] ?? 0;
            $pengeluaranStokDetailFifo->gudang_id = $data['gudang_id'] ?? 0;
            $pengeluaranStokDetailFifo->urut = $item['urut'] ?? 0;
            $pengeluaranStokDetailFifo->qty = $item['qty'] ?? 0;
            $pengeluaranStokDetailFifo->penerimaanstokheader_nobukti = $item['penerimaan_nobukti'] ?? '';
            $pengeluaranStokDetailFifo->penerimaanstok_qty = $item['penerimaan_qty'] ?? 0;
            $pengeluaranStokDetailFifo->penerimaanstok_harga = $item['penerimaan_harga'] ?? 0;
            $pengeluaranStokDetailFifo->modifiedby = $data['modifiedby'] ?? '';
            $total = $item['penerimaan_qty'] * $item['penerimaan_harga'];

            if (!$pengeluaranStokDetailFifo->save()) {
                throw new \Exception("Error storing pengeluaran Stok Detail fifo.");
            }
            $ksqty = $item['penerimaan_qty'] ?? 0;
            $ksharga = $item['penerimaan_harga'] ?? 0;
            $kstotal = $ksqty * $ksharga;
            $ksnobukti = $data['nobukti'] ?? '';

            $pengeluaranstok_id = db::table("pengeluaranstokheader")->from(db::raw("pengeluaranstokheader as a with (readuncommitted)"))
                ->select('a.pengeluaranstok_id', 'a.tglbukti')->where('a.nobukti', $ksnobukti)->first();

            $kspengeluaranstok_id = $pengeluaranstok_id->pengeluaranstok_id ?? 0;
            $kstglbukti = $pengeluaranstok_id->tglbukti ?? '1900/1/1';

            $urutfifo = db::table("pengeluaranstok")->from(db::raw("pengeluaranstok as a with (readuncommitted)"))
                ->select('a.urutfifo')->where('a.id', $kspengeluaranstok_id)->first()->urutfifo ?? 0;



            if ($kspengeluaranstok_id != 6) {
                $kartuStok = (new KartuStok())->processStore([
                    "gudang_id" => $data['gudang_id'] ?? 0,
                    "trado_id" => 0,
                    "gandengan_id" => 0,
                    "stok_id" => $data['stok_id'] ?? 0,
                    "nobukti" => $data['nobukti'] ?? '',
                    "tglbukti" => $kstglbukti,
                    "qtymasuk" => 0,
                    "nilaimasuk" =>  0,
                    "qtykeluar" => $item['penerimaan_qty'] ?? 0,
                    "nilaikeluar" => $kstotal,
                    "urutfifo" => $urutfifo,
                ]);
            }

            if ($data['pengeluaranstok_id'] == $spk->text) {
                $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'DEBET')->first();
                $memo = json_decode($getCoaDebet->memo, true);
                $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'KREDIT')->first();
                $memokredit = json_decode($getCoaKredit->memo, true);
            }


            $totalharga += ($item['penerimaan_harga'] * $item['penerimaan_qty']);


            $penerimaanstokdetail  = PenerimaanStokDetail::lockForUpdate()->where("stok_id", $item['stok_id'])
                ->where("nobukti", $item['penerimaan_nobukti'])
                ->firstorFail();
            $penerimaanstokdetail->qtykeluar += $item['penerimaan_qty'] ?? 0;
            $penerimaanstokdetail->save();
        }



        $pengeluaranstokdetail  = PengeluaranStokDetail::lockForUpdate()->where("stok_id", $item['stok_id'])
            ->where("nobukti", $data['nobukti'])
            ->firstorFail();

        $hrgsat = $totalharga / $data['qty'];

        if ($data['pengeluaranstok_id'] == 2) {
            $totdetailharga = $data['detail_harga'];
            $selisih = $hrgsat - $totdetailharga;

            $hrgsat = $data['detail_harga'];
            $totalharga = $hrgsat * $data['qty'];
        } else {
            $selisih = 0;
        }
        $pengeluaranstokdetail->harga =   $hrgsat;
        $pengeluaranstokdetail->total =  $totalharga;
        $pengeluaranstokdetail->selisihhargafifo =  $selisih;
        // $pengeluaranstokdetail->save();
        if (!$pengeluaranstokdetail->save()) {
            throw new \Exception("Error storing pengeluaran Stok Detail  update fifo. ");
        }

        return $pengeluaranStokDetailFifo;
    }
}
