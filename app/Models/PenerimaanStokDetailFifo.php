<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\Api\ErrorController;
use Illuminate\Validation\ValidationException;

class PenerimaanStokDetailFifo extends MyModel
{
    use HasFactory;

    protected $table = 'penerimaanstokdetailfifo';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function processStore(PenerimaanStokHeader $penerimaanStokHeader, array $data): PenerimaanStokDetailFifo
    {
        $qty = $data['qty'] ?? 0;

        $penerimaanstok_id = $data['penerimaanstok_id'] ?? 0;
        $pengeluaranstok_nobukti = $data['pengeluaranstok_nobukti'] ?? '';

        if ($penerimaanstok_id == 5) {
            $kondisipg = true;
        } else {
            $kondisipg = false;
        }
        $totalharga = 0;
        $spk = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();


        if ($penerimaanstok_id = 8 || $penerimaanstok_id = 9) {
            $tempfifo = '##tempfifo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempfifo, function ($table) {
                $table->bigInteger('stok_id')->nullable();
                $table->double('qty', 15, 2)->nullable();
                $table->bigInteger('id')->nullable();
                $table->string('pengeluaranstok_nobukti', 100)->nullable();
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
                $table->string('pengeluaranstokheader_nobukti', 100)->nullable();
                $table->double('penerimaanstok_qty', 15, 2)->nullable();
                $table->double('penerimaanstok_harga', 15, 2)->nullable();
                $table->double('penerimaanstokheader_total', 15, 2)->nullable();
                $table->double('penerimaanstokheader_totalterpakai', 15, 2)->nullable();
                $table->integer('pengeluaranstokfifo_id')->nullable();
            });

            $queryfifo = db::table('penerimaanstokdetailfifo')->from(db::raw("penerimaanstokdetailfifo a with (readuncommitted)"))
                ->select(
                    'a.penerimaanstokheader_id as stokheader_id',
                    'a.nobukti as nobukti',
                    'a.stok_id as stok_id',
                    'a.gudang_id as gudang_id',
                    'a.urut as urut',
                    'a.qty as qty',
                    'a.pengeluaranstokheader_nobukti as pengeluaranstokheader_nobukti',
                    'a.penerimaanstok_qty as penerimaanstokheader_qty',
                    'a.penerimaanstok_harga as penerimaanstokheader_harga',
                    'a.penerimaanstokheader_total as penerimaanstokheader_total',
                    'a.penerimaanstokheader_totalterpakai as penerimaanstokheader_totalterpakai',
                    'a.pengeluaranstokfifo_id as pengeluaranstokfifo_id',
                )
                ->where('a.stok_id', '=',   $data['stok_id'])
                ->where('a.gudang_id', '=',   $data['gudang_id'])
                ->where('a.pengeluaranstokheader_nobukti', '=',   $pengeluaranstok_nobukti)
                ->orderby('a.id');


            DB::table($temprekappengeluaranfifo)->insertUsing([
                "stokheader_id",
                "nobukti",
                "stok_id",
                "gudang_id",
                "urut",
                "qty",
                "pengeluaranstokheader_nobukti",
                "penerimaanstok_qty",
                "penerimaanstok_harga",
                "penerimaanstokheader_total",
                "penerimaanstokheader_totalterpakai",
                "pengeluaranstokfifo_id",
            ], $queryfifo);


            $a = 0;
            $atotalharga = 0;
            $kondisi = true;
            $totalterpakai2 = 0;
            while ($kondisi == true) {
                DB::delete(DB::raw("delete " . $tempfifo));

                $queryfifo = db::table($temprekappengeluaranfifo)->from(db::raw($temprekappengeluaranfifo . " a with (readuncommitted)"))
                    ->select(
                        'a.pengeluaranstokheader_nobukti as pengeluaranstok_nobukti',
                        'a.stok_id as stok_id',
                        db::raw("sum(a.qty) as qty"),
                        db::raw("c.id as id"),
                        db::raw("sum(a.penerimaanstokheader_totalterpakai) as penerimaanstokheader_totalterpakai"),

                    )
                    ->join(db::raw("pengeluaranstokdetailfifo c with (readuncommitted)"), 'a.pengeluaranstokfifo_id', 'c.id')
                    ->where('c.stok_id', '=',   $data['stok_id'])
                    ->groupBY('a.pengeluaranstokheader_nobukti')
                    ->groupBY('a.stok_id')
                    ->groupBY('c.id');

                // dd($queryfifo->get());


                DB::table($tempfifo)->insertUsing([
                    'pengeluaranstok_nobukti',
                    'stok_id',
                    'qty',
                    'id',
                    'penerimaanstokheader_totalterpakai',
                ], $queryfifo);
                // dd('test');
                $querysisa = db::table('pengeluaranstokdetailfifo')->from(db::raw("pengeluaranstokdetailfifo a with (readuncommitted)"))
                    ->select(
                        db::raw("(a.qty-isnull(B.qty,0)) as qtysisa"),
                        'a.nobukti',
                        'a.qty',
                        'a.penerimaanstok_harga as harga',
                        db::raw("(a.qty*a.penerimaanstok_harga) as total"),
                        'c.id as penerimaanstok_id',
                        db::raw("round((a.penerimaanstokheader_total-isnull(b.penerimaanstokheader_totalterpakai,0)),10) as totalsisa"),
                        db::raw("isnull(a.id,0) as pengeluaranstokfifo_id"),
                        'a.stok_id',
                    )
                    // ->leftjoin(db::raw($tempfifo . " b "), 'a.nobukti', 'b.penerimaanstok_nobukti')
                    ->leftjoin(db::raw($tempfifo . " b "), function ($join) {
                        $join->on('a.nobukti', '=', 'b.pengeluaranstok_nobukti');
                        $join->on('a.stok_id', '=', 'b.stok_id');
                        $join->on('a.id', '=', 'b.id');
                    })
                    ->join(db::raw("pengeluaranstokheader c "), 'a.nobukti', 'c.nobukti')
                    ->where('a.stok_id', '=',   $data['stok_id'])
                    ->where('a.nobukti', '=',   $pengeluaranstok_nobukti)
                    ->whereRaw("(a.qty-isnull(B.qty,0))<>0")
                    ->orderBy('a.id', 'asc')
                    ->first();

                // dd($querysisa);
                // 
                if (isset($querysisa)) {
                    $qtysisa = $querysisa->qtysisa ?? 0;
                    if ($qty <= $qtysisa) {

                        $totalterpakai = round((($querysisa->total / $querysisa->qty) * $qty), 2);
                        $totalterpakai2 += $totalterpakai;
                        $penerimaanStokDetailFifo = new penerimaanStokDetailFifo();
                        $penerimaanStokDetailFifo->penerimaanstokheader_id = $data['penerimaanstokheader_id'] ?? 0;
                        $penerimaanStokDetailFifo->nobukti = $data['nobukti'] ?? '';
                        $penerimaanStokDetailFifo->stok_id = $data['stok_id'] ?? 0;
                        $penerimaanStokDetailFifo->gudang_id = $data['gudang_id'] ?? 0;
                        $penerimaanStokDetailFifo->urut = $a;
                        $penerimaanStokDetailFifo->qty = $qty ?? 0;
                        $penerimaanStokDetailFifo->pengeluaranstokheader_nobukti = $querysisa->nobukti ?? '';
                        $penerimaanStokDetailFifo->penerimaanstok_qty = $querysisa->qty ?? 0;
                        $penerimaanStokDetailFifo->penerimaanstok_harga = $kondisipg ? 0 : $querysisa->harga;
                        $penerimaanStokDetailFifo->penerimaanstokheader_total = $kondisipg ? 0 : $querysisa->total;
                        $penerimaanStokDetailFifo->penerimaanstokheader_totalterpakai = $totalterpakai ?? 0;
                        $penerimaanStokDetailFifo->pengeluaranstokfifo_id = $querysisa->pengeluaranstokfifo_id ?? 0;
                        $penerimaanStokDetailFifo->modifiedby = $data['modifiedby'] ?? '';

                        DB::table($temprekappengeluaranfifo)->insert([
                            'stokheader_id' => $data['penerimaanstokheader_id'] ?? 0,
                            'nobukti' =>  $data['nobukti'] ?? '',
                            'stok_id' => $data['stok_id'] ?? 0,
                            'gudang_id' => $data['gudang_id'] ?? 0,
                            'urut' =>  $a,
                            'qty' =>  $qty ?? 0,
                            'pengeluaranstokheader_nobukti' => $querysisa->nobukti ?? '',
                            'penerimaanstok_qty' => $querysisa->qty ?? 0,
                            'penerimaanstok_harga' => $kondisipg ? 0 : $querysisa->harga,
                            'penerimaanstokheader_total' => $kondisipg ? 0 : $querysisa->total,
                            'penerimaanstokheader_totalterpakai' => $totalterpakai ?? 0,
                            'pengeluaranstokfifo_id' => $querysisa->pengeluaranstokfifo_id ?? 0,
                        ]);

                        $belitotalsisa = $querysisa->totalsisa ?? 0;
                        $beliqtysisa = $querysisa->qtysisa ?? 0;

                        $belitotal = $querysisa->total ?? 0;
                        $beliqty = $querysisa->qty ?? 0;

                        $zqty = $qty ?? 0;
                        // $zharga = $querysisa->harga ?? 0;
                        $zharga = round(($belitotalsisa / $beliqtysisa), 10) ?? 0;

                        $atotalharga = $atotalharga + round(($zqty * (($belitotalsisa / $beliqtysisa))), 2);

                        // $atotalharga = $atotalharga + ($zqty * ($belitotal / $beliqty));


                        // 
                        $ksqty = $qty ?? 0;
                        // $ksharga = $querysisa->harga ?? 0;
                        // $kstotal = $ksqty * ($belitotal / $beliqty);
                        $ksharga = round(($belitotalsisa / $beliqtysisa), 10) ?? 0;
                        $kstotal = round(($ksqty * ($belitotalsisa / $beliqtysisa)), 2);

                        $ksnobukti = $data['nobukti'] ?? '';


                        $penerimaanstok_id = db::table("penerimaanstokheader")->from(db::raw("penerimaanstokheader as a with (readuncommitted)"))
                            ->select('a.penerimaanstok_id', 'a.tglbukti')->where('a.nobukti', $ksnobukti)->first();

                        $kspenerimaanstok_id = $penerimaanstok_id->penerimaanstok_id ?? 0;
                        $kstglbukti = $penerimaanstok_id->tglbukti ?? '1900/1/1';

                        $urutfifo = db::table("penerimaanstok")->from(db::raw("penerimaanstok as a with (readuncommitted)"))
                            ->select('a.urutfifo')->where('a.id', $kspenerimaanstok_id)->first()->urutfifo ?? 0;



                        if ($kspenerimaanstok_id != 6) {
                            if ($kspenerimaanstok_id == 8 || $kspenerimaanstok_id == 9) {

                                $querybukti = db::table("pengeluaranstokheader")->from(db::raw("pengeluaranstokheader a with (readuncommitted)"))
                                    ->select(
                                        'a.gudang_id',
                                        'a.trado_id',
                                        'a.gandengan_id',
                                    )
                                    ->where('a.nobukti', $pengeluaranstok_nobukti)
                                    ->first();
                                $agudang_id = $querybukti->gudang_id ?? 0;
                                $atrado_id = $querybukti->trado_id ?? 0;
                                $agandengan_id = $querybukti->gandengan_id ?? 0;

                                $kartuStok = (new KartuStok())->processStore([
                                    "gudang_id" => $agudang_id ?? 0,
                                    "trado_id" => $atrado_id ?? 0,
                                    "gandengan_id" => $agandengan_id ?? 0,
                                    "stok_id" => $data['stok_id'] ?? 0,
                                    "nobukti" => $data['nobukti'] ?? '',
                                    "tglbukti" => $kstglbukti,
                                    "qtymasuk" => 0,
                                    "nilaimasuk" =>  0,
                                    "qtykeluar" => $qty ?? 0,
                                    "nilaikeluar" => $kondisipg ? 0 : $totalterpakai,
                                    "urutfifo" => $urutfifo,
                                ]);
                            } else {
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
                                    "nilaikeluar" => $kondisipg ? 0 : $totalterpakai,
                                    "urutfifo" => $urutfifo,
                                ]);
                            }

                            if ($kspenerimaanstok_id == 8 || $kspenerimaanstok_id == 9) {
                                $kartuStok = (new KartuStok())->processStore([
                                    "gudang_id" => $data['gudang_id'] ?? 0,
                                    "trado_id" => $data['trado_id'] ?? 0,
                                    "gandengan_id" => $data['gandengan_id'] ?? 0,
                                    "stok_id" => $data['stok_id'] ?? 0,
                                    "nobukti" => $data['nobukti'] ?? '',
                                    "tglbukti" => $kstglbukti,
                                    "qtymasuk" => $qty ?? 0,
                                    "nilaimasuk" =>  $kondisipg ? 0 : $totalterpakai,
                                    "qtykeluar" => 0,
                                    "nilaikeluar" => 0,
                                    "urutfifo" => $urutfifo,
                                ]);
                            }
                        }

                        // if ($data['pengeluaranstok_id'] == $spk->text) {
                        $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                            ->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'DEBET')->first();
                        $memo = json_decode($getCoaDebet->memo, true);
                        $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                            ->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'KREDIT')->first();
                        $memokredit = json_decode($getCoaKredit->memo, true);
                        // }

                        $aksqty = $querysisa->qty ?? 0;
                        // $aksharga = $querysisa->harga ?? 0;
                        $aksharga = round(($belitotalsisa / $beliqtysisa), 10) ?? 0;


                        $aksnobukti = $querysisa->nobukti ?? '';
                        $aksstok_id = $data['stok_id'] ?? 0;

                        $totalharga += round(($aksharga  * $aksqty), 2);



                        // 

                        $kondisi = false;
                        if (!$penerimaanStokDetailFifo->save()) {
                            throw new \Exception("Error Simpan Penerimaan Detail fifo.");
                        }
                    } else {
                        // dd('test');
                        $qty = $qty - $qtysisa;

                        $totalterpakai = round((($querysisa->total / $querysisa->qty) * $qtysisa), 2);
                        $totalterpakai2 += $totalterpakai;

                        $penerimaanStokDetailFifo = new penerimaanStokDetailFifo();
                        $penerimaanStokDetailFifo->penerimaanstokheader_id = $data['penerimaanstokheader_id'] ?? 0;
                        $penerimaanStokDetailFifo->nobukti = $data['nobukti'] ?? '';
                        $penerimaanStokDetailFifo->stok_id = $data['stok_id'] ?? 0;
                        $penerimaanStokDetailFifo->gudang_id = $data['gudang_id'] ?? 0;
                        $penerimaanStokDetailFifo->urut = $a;
                        $penerimaanStokDetailFifo->qty = $qtysisa ?? 0;
                        $penerimaanStokDetailFifo->pengeluaranstokheader_nobukti = $querysisa->nobukti ?? '';
                        $penerimaanStokDetailFifo->penerimaanstok_qty = $querysisa->qty ?? 0;
                        $penerimaanStokDetailFifo->penerimaanstok_harga = $kondisipg ? 0 : $querysisa->harga ?? 0;
                        $penerimaanStokDetailFifo->penerimaanstokheader_total = $kondisipg ? 0 : $querysisa->total ?? 0;
                        $penerimaanStokDetailFifo->penerimaanstokheader_totalterpakai = $totalterpakai ?? 0;
                        $penerimaanStokDetailFifo->pengeluaranstokfifo_id = $querysisa->pengeluaranstokfifo_id ?? 0;
                        $penerimaanStokDetailFifo->modifiedby = $data['modifiedby'] ?? '';

                        DB::table($temprekappengeluaranfifo)->insert([
                            'stokheader_id' => $data['penerimaanstokheader_id'] ?? 0,
                            'nobukti' =>  $data['nobukti'] ?? '',
                            'stok_id' => $data['stok_id'] ?? 0,
                            'gudang_id' => $data['gudang_id'] ?? 0,
                            'urut' =>  $a,
                            'qty' => $qtysisa ?? 0,
                            'pengeluaranstokheader_nobukti' => $querysisa->nobukti ?? '',
                            'penerimaanstok_qty' => $querysisa->qty ?? 0,
                            'penerimaanstok_harga' => $kondisipg ? 0 : $querysisa->harga ?? 0,
                            'penerimaanstokheader_total' => $kondisipg ? 0 : $querysisa->total ?? 0,
                            'penerimaanstokheader_totalterpakai' => $totalterpakai ?? 0,
                            'pengeluaranstokfifo_id' => $querysisa->pengeluaranstokfifo_id ?? 0,


                        ]);


                        $belitotal = $querysisa->total ?? 0;
                        $beliqty = $querysisa->qty ?? 0;

                        $belitotalsisa = $querysisa->totalsisa ?? 0;
                        $beliqtysisa = $querysisa->qtysisa ?? 0;


                        $zqty = $qtysisa ?? 0;
                        // $zharga = $querysisa->harga ?? 0;
                        // $atotalharga = $atotalharga + ($zqty * ($belitotal / $beliqty));

                        $zharga = round(($belitotalsisa / $beliqtysisa), 10) ?? 0;
                        $atotalharga = $atotalharga + round(($zqty * ($belitotalsisa / $beliqtysisa)), 2);




                        // 
                        $ksqty = $qtysisa ?? 0;
                        // $ksharga = $querysisa->harga ?? 0;
                        // $kstotal = $ksqty * ($belitotal / $beliqty);

                        $ksharga = round(($belitotalsisa / $beliqtysisa), 10) ?? 0;
                        $kstotal = round($ksqty * ($belitotalsisa / $beliqtysisa), 2);

                        $ksnobukti = $data['nobukti'] ?? '';

                        $penerimaanstok_id = db::table("penerimaanstokheader")->from(db::raw("penerimaanstokheader as a with (readuncommitted)"))
                            ->select('a.penerimaanstok_id', 'a.tglbukti')->where('a.nobukti', $ksnobukti)->first();

                        $kspenerimaanstok_id = $penerimaanstok_id->penerimaanstok_id ?? 0;
                        $kstglbukti = $penerimaanstok_id->tglbukti ?? '1900/1/1';

                        $urutfifo = db::table("penerimaanstok")->from(db::raw("penerimaanstok as a with (readuncommitted)"))
                            ->select('a.urutfifo')->where('a.id', $kspenerimaanstok_id)->first()->urutfifo ?? 0;



                        if ($kspenerimaanstok_id != 6) {
                            if ($kspenerimaanstok_id == 8 || $kspenerimaanstok_id == 9) {

                                $querybukti = db::table("pengeluaranstokheader")->from(db::raw("pengeluaranstokheader a with (readuncommitted)"))
                                    ->select(
                                        'a.gudang_id',
                                        'a.trado_id',
                                        'a.gandengan_id',
                                    )
                                    ->where('a.nobukti', $pengeluaranstok_nobukti)
                                    ->first();
                                $agudang_id = $querybukti->gudang_id ?? 0;
                                $atrado_id = $querybukti->trado_id ?? 0;
                                $agandengan_id = $querybukti->gandengan_id ?? 0;

                                $kartuStok = (new KartuStok())->processStore([
                                    "gudang_id" => $agudang_id ?? 0,
                                    "trado_id" => $atrado_id ?? 0,
                                    "gandengan_id" => $agandengan_id ?? 0,
                                    "stok_id" => $data['stok_id'] ?? 0,
                                    "nobukti" => $data['nobukti'] ?? '',
                                    "tglbukti" => $kstglbukti,
                                    "qtymasuk" => 0,
                                    "nilaimasuk" =>  0,
                                    "qtykeluar" => $qty ?? 0,
                                    "nilaikeluar" => $kondisipg ? 0 : $totalterpakai,
                                    "urutfifo" => $urutfifo,
                                ]);
                            } else {
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
                                    "nilaikeluar" => $kondisipg ? 0 : $totalterpakai,
                                    "urutfifo" => $urutfifo,
                                ]);
                            }
                            if ($kspenerimaanstok_id == 8 || $kspenerimaanstok_id == 9) {
                                $kartuStok = (new KartuStok())->processStore([
                                    "gudang_id" => $data['gudang_id'] ?? 0,
                                    "trado_id" => $data['trado_id'] ?? 0,
                                    "gandengan_id" => $data['gandengan_id'] ?? 0,
                                    "stok_id" => $data['stok_id'] ?? 0,
                                    "nobukti" => $data['nobukti'] ?? '',
                                    "tglbukti" => $kstglbukti,
                                    "qtymasuk" => $qty ?? 0,
                                    "nilaimasuk" =>  $kondisipg ? 0 : $totalterpakai,
                                    "qtykeluar" => 0,
                                    "nilaikeluar" => 0,
                                    "urutfifo" => $urutfifo,
                                ]);
                            }
                        }

                        $pengeluaranstok_id = $data['pengeluaranstok_id'] ?? 0;
                        if ($pengeluaranstok_id == $spk->text) {
                            $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                                ->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'DEBET')->first();
                            $memo = json_decode($getCoaDebet->memo, true);
                            $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                                ->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'KREDIT')->first();
                            $memokredit = json_decode($getCoaKredit->memo, true);
                        }

                        $aksqty = $querysisa->qty ?? 0;
                        // $aksharga = $querysisa->harga ?? 0;
                        $aksharga = round(($querysisa->totalsisa / $querysisa->qtysisa), 10) ?? 0;

                        $aksnobukti = $querysisa->nobukti ?? '';
                        $aksstok_id = $data['stok_id'] ?? 0;

                        $totalharga += round(($aksharga *  $aksqty), 2);






                        if (!$penerimaanStokDetailFifo->save()) {
                            throw new \Exception("Error Simpan pengeluaran detail fifo Detail fifo.");
                        }
                    }
                }


                // 
            }
            // dd('test11');
            $nobuktipenerimaan = $data['nobukti'] ?? '';
            $stokidpenerimaan = $data['stok_id'] ?? 0;
            $penerimaanstokdetail  = PenerimaanStokDetail::lockForUpdate()->where("stok_id", $stokidpenerimaan)
                ->where("nobukti", $nobuktipenerimaan)
                ->firstorFail();

            // $totalharga = $atotalharga;
            $totalharga = $totalterpakai2;

            // dump($totalharga);
            // dd($data['qty']);
            $hrgsat = $totalharga / $data['qty'];

            $selisih = 0;
            $penerimaanstokdetail->harga =  $kondisipg ? 0 : $hrgsat;
            $penerimaanstokdetail->total =  $kondisipg ? 0 : $totalharga;
            // $pengeluaranstokdetail->save();
            if (!$penerimaanstokdetail->save()) {
                throw new \Exception("Error storing pengeluaran Stok Detail  update fifo. ");
            }

            goto lanjut;
        } else {
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
        }


        // dd($data);



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
            ->whereRaw("isnull(a.penerimaanstokheader_nobukti,'')<>''")
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
        $kondisi = true;
        $totalterpakai2 = 0;
        while ($kondisi == true) {

            DB::delete(DB::raw("delete " . $tempfifo));


            $queryfifo = db::table($temprekappengeluaranfifo)->from(db::raw($temprekappengeluaranfifo . " a with (readuncommitted)"))
                ->select(
                    'a.penerimaanstokheader_nobukti as penerimaanstok_nobukti',
                    'a.stok_id as stok_id',
                    // db::raw("sum(a.penerimaanstok_qty) as penerimaanstok_qty"),
                    db::raw("sum(a.qty) as qty"),
                    db::raw("max(b.id) as id"),
                    db::raw("sum(a.penerimaanstokheader_totalterpakai) as penerimaanstokheader_totalterpakai"),

                )
                ->join(db::raw("penerimaanstokheader b with (readuncommitted)"), 'a.penerimaanstokheader_nobukti', 'b.nobukti')
                ->join(db::raw("penerimaanstokdetail c with (readuncommitted)"), 'b.nobukti', 'c.nobukti')
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
                    db::raw("round((a.total-isnull(b.penerimaanstokheader_totalterpakai,0)),10) as totalsisa"),
                )
                // ->leftjoin(db::raw($tempfifo . " b "), 'a.nobukti', 'b.penerimaanstok_nobukti')
                ->leftjoin(db::raw($tempfifo . " b "), function ($join) {
                    $join->on('a.nobukti', '=', 'b.penerimaanstok_nobukti');
                    $join->on('a.stok_id', '=', 'b.stok_id');
                })
                ->join(db::raw("penerimaanstokheader c "), 'a.nobukti', 'c.nobukti')
                ->where('a.stok_id', '=',   $data['stok_id'])
                ->whereRaw("(c.gudang_id=" .   $data['gudang_id'] . " or c.gudangke_id=" . $data['gudang_id'] . ")")
                ->whereRaw("(a.qty-isnull(B.qty,0))<>0")
                ->orderBy('a.id', 'asc')
                ->first();
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

                    $totalterpakai = round((($querysisa->total / $querysisa->qty) * $qty), 2);
                    $totalterpakai2 += $totalterpakai;
                    $penerimaanStokDetailFifo = new penerimaanStokDetailFifo();
                    $penerimaanStokDetailFifo->penerimaanstokheader_id = $data['penerimaanstokheader_id'] ?? 0;
                    $penerimaanStokDetailFifo->nobukti = $data['nobukti'] ?? '';
                    $penerimaanStokDetailFifo->stok_id = $data['stok_id'] ?? 0;
                    $penerimaanStokDetailFifo->gudang_id = $data['gudang_id'] ?? 0;
                    $penerimaanStokDetailFifo->urut = $a;
                    $penerimaanStokDetailFifo->qty = $qty ?? 0;
                    $penerimaanStokDetailFifo->penerimaanstokheader_nobukti = $querysisa->nobukti ?? '';
                    $penerimaanStokDetailFifo->penerimaanstok_qty = $querysisa->qty ?? 0;
                    $penerimaanStokDetailFifo->penerimaanstok_harga = $kondisipg ? 0 : $querysisa->harga;
                    $penerimaanStokDetailFifo->penerimaanstokheader_total = $kondisipg ? 0 : $querysisa->total;
                    $penerimaanStokDetailFifo->penerimaanstokheader_totalterpakai = $totalterpakai ?? 0;
                    $penerimaanStokDetailFifo->modifiedby = $data['modifiedby'] ?? '';

                    DB::table($temprekappengeluaranfifo)->insert([
                        'stokheader_id' => $data['penerimaanstokheader_id'] ?? 0,
                        'nobukti' =>  $data['nobukti'] ?? '',
                        'stok_id' => $data['stok_id'] ?? 0,
                        'gudang_id' => $data['gudang_id'] ?? 0,
                        'urut' =>  $a,
                        'qty' =>  $qty ?? 0,
                        'penerimaanstokheader_nobukti' => $querysisa->nobukti ?? '',
                        'penerimaanstok_qty' => $querysisa->qty ?? 0,
                        'penerimaanstok_harga' => $kondisipg ? 0 : $querysisa->harga,
                        'penerimaanstokheader_total' => $kondisipg ? 0 : $querysisa->total,
                        'penerimaanstokheader_totalterpakai' => $totalterpakai ?? 0,
                    ]);

                    $belitotalsisa = $querysisa->totalsisa ?? 0;
                    $beliqtysisa = $querysisa->qtysisa ?? 0;

                    $belitotal = $querysisa->total ?? 0;
                    $beliqty = $querysisa->qty ?? 0;

                    $zqty = $qty ?? 0;
                    // $zharga = $querysisa->harga ?? 0;
                    $zharga = round(($belitotalsisa / $beliqtysisa), 10) ?? 0;

                    $atotalharga = $atotalharga + round(($zqty * (($belitotalsisa / $beliqtysisa))), 2);

                    // $atotalharga = $atotalharga + ($zqty * ($belitotal / $beliqty));


                    // 
                    $ksqty = $qty ?? 0;
                    // $ksharga = $querysisa->harga ?? 0;
                    // $kstotal = $ksqty * ($belitotal / $beliqty);
                    $ksharga = round(($belitotalsisa / $beliqtysisa), 10) ?? 0;
                    $kstotal = round(($ksqty * ($belitotalsisa / $beliqtysisa)), 2);

                    $ksnobukti = $data['nobukti'] ?? '';


                    $penerimaanstok_id = db::table("penerimaanstokheader")->from(db::raw("penerimaanstokheader as a with (readuncommitted)"))
                        ->select('a.penerimaanstok_id', 'a.tglbukti')->where('a.nobukti', $ksnobukti)->first();

                    $kspenerimaanstok_id = $penerimaanstok_id->penerimaanstok_id ?? 0;
                    $kstglbukti = $penerimaanstok_id->tglbukti ?? '1900/1/1';

                    $urutfifo = db::table("penerimaanstok")->from(db::raw("penerimaanstok as a with (readuncommitted)"))
                        ->select('a.urutfifo')->where('a.id', $kspenerimaanstok_id)->first()->urutfifo ?? 0;



                    if ($kspenerimaanstok_id != 6) {
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
                            "nilaikeluar" => $kondisipg ? 0 : $totalterpakai,
                            "urutfifo" => $urutfifo,
                        ]);
                    }

                    // if ($data['pengeluaranstok_id'] == $spk->text) {
                    $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                        ->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'DEBET')->first();
                    $memo = json_decode($getCoaDebet->memo, true);
                    $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                        ->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'KREDIT')->first();
                    $memokredit = json_decode($getCoaKredit->memo, true);
                    // }

                    $aksqty = $querysisa->qty ?? 0;
                    // $aksharga = $querysisa->harga ?? 0;
                    $aksharga = round(($belitotalsisa / $beliqtysisa), 10) ?? 0;


                    $aksnobukti = $querysisa->nobukti ?? '';
                    $aksstok_id = $data['stok_id'] ?? 0;

                    $totalharga += round(($aksharga  * $aksqty), 2);



                    // 

                    $kondisi = false;
                    if (!$penerimaanStokDetailFifo->save()) {
                        throw new \Exception("Error Simpan Penerimaan Detail fifo.");
                    }
                } else {
                    // dd('test');
                    $qty = $qty - $qtysisa;

                    $totalterpakai = round((($querysisa->total / $querysisa->qty) * $qtysisa), 2);
                    $totalterpakai2 += $totalterpakai;

                    $penerimaanStokDetailFifo = new penerimaanStokDetailFifo();
                    $penerimaanStokDetailFifo->penerimaanstokheader_id = $data['penerimaanstokheader_id'] ?? 0;
                    $penerimaanStokDetailFifo->nobukti = $data['nobukti'] ?? '';
                    $penerimaanStokDetailFifo->stok_id = $data['stok_id'] ?? 0;
                    $penerimaanStokDetailFifo->gudang_id = $data['gudang_id'] ?? 0;
                    $penerimaanStokDetailFifo->urut = $a;
                    $penerimaanStokDetailFifo->qty = $qtysisa ?? 0;
                    $penerimaanStokDetailFifo->penerimaanstokheader_nobukti = $querysisa->nobukti ?? '';
                    $penerimaanStokDetailFifo->penerimaanstok_qty = $querysisa->qty ?? 0;
                    $penerimaanStokDetailFifo->penerimaanstok_harga = $kondisipg ? 0 : $querysisa->harga ?? 0;
                    $penerimaanStokDetailFifo->penerimaanstokheader_total = $kondisipg ? 0 : $querysisa->total ?? 0;
                    $penerimaanStokDetailFifo->penerimaanstokheader_totalterpakai = $totalterpakai ?? 0;
                    $penerimaanStokDetailFifo->modifiedby = $data['modifiedby'] ?? '';

                    DB::table($temprekappengeluaranfifo)->insert([
                        'stokheader_id' => $data['penerimaanstokheader_id'] ?? 0,
                        'nobukti' =>  $data['nobukti'] ?? '',
                        'stok_id' => $data['stok_id'] ?? 0,
                        'gudang_id' => $data['gudang_id'] ?? 0,
                        'urut' =>  $a,
                        'qty' => $qtysisa ?? 0,
                        'penerimaanstokheader_nobukti' => $querysisa->nobukti ?? '',
                        'penerimaanstok_qty' => $querysisa->qty ?? 0,
                        'penerimaanstok_harga' => $kondisipg ? 0 : $querysisa->harga ?? 0,
                        'penerimaanstokheader_total' => $kondisipg ? 0 : $querysisa->total ?? 0,
                        'penerimaanstokheader_totalterpakai' => $totalterpakai ?? 0,

                    ]);


                    $belitotal = $querysisa->total ?? 0;
                    $beliqty = $querysisa->qty ?? 0;

                    $belitotalsisa = $querysisa->totalsisa ?? 0;
                    $beliqtysisa = $querysisa->qtysisa ?? 0;


                    $zqty = $qtysisa ?? 0;
                    // $zharga = $querysisa->harga ?? 0;
                    // $atotalharga = $atotalharga + ($zqty * ($belitotal / $beliqty));

                    $zharga = round(($belitotalsisa / $beliqtysisa), 10) ?? 0;
                    $atotalharga = $atotalharga + round(($zqty * ($belitotalsisa / $beliqtysisa)), 2);




                    // 
                    $ksqty = $qtysisa ?? 0;
                    // $ksharga = $querysisa->harga ?? 0;
                    // $kstotal = $ksqty * ($belitotal / $beliqty);

                    $ksharga = round(($belitotalsisa / $beliqtysisa), 10) ?? 0;
                    $kstotal = round($ksqty * ($belitotalsisa / $beliqtysisa), 2);

                    $ksnobukti = $data['nobukti'] ?? '';

                    $penerimaanstok_id = db::table("penerimaanstokheader")->from(db::raw("penerimaanstokheader as a with (readuncommitted)"))
                        ->select('a.penerimaanstok_id', 'a.tglbukti')->where('a.nobukti', $ksnobukti)->first();

                    $kspenerimaanstok_id = $penerimaanstok_id->penerimaanstok_id ?? 0;
                    $kstglbukti = $penerimaanstok_id->tglbukti ?? '1900/1/1';

                    $urutfifo = db::table("penerimaanstok")->from(db::raw("penerimaanstok as a with (readuncommitted)"))
                        ->select('a.urutfifo')->where('a.id', $kspenerimaanstok_id)->first()->urutfifo ?? 0;



                    if ($kspenerimaanstok_id != 6) {
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
                            "nilaikeluar" => $kondisipg ? 0 : $totalterpakai,
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
                    // $aksharga = $querysisa->harga ?? 0;
                    $aksharga = round(($querysisa->totalsisa / $querysisa->qtysisa), 10) ?? 0;

                    $aksnobukti = $querysisa->nobukti ?? '';
                    $aksstok_id = $data['stok_id'] ?? 0;

                    $totalharga += round(($aksharga *  $aksqty), 2);






                    if (!$penerimaanStokDetailFifo->save()) {
                        throw new \Exception("Error Simpan pengeluaran detail fifo Detail fifo.");
                    }
                }
            }
        }
        // 

        // dd('test');
        $nobuktipenerimaan = $data['nobukti'] ?? '';
        $stokidpenerimaan = $data['stok_id'] ?? 0;
        $penerimaanstokdetail  = PenerimaanStokDetail::lockForUpdate()->where("stok_id", $stokidpenerimaan)
            ->where("nobukti", $nobuktipenerimaan)
            ->firstorFail();

        // $totalharga = $atotalharga;
        $totalharga = $totalterpakai2;

        // dump($totalharga);
        // dd($data['qty']);
        $hrgsat = $totalharga / $data['qty'];

        $selisih = 0;
        $penerimaanstokdetail->harga =  $kondisipg ? 0 : $hrgsat;
        $penerimaanstokdetail->total =  $kondisipg ? 0 : $totalharga;
        // $pengeluaranstokdetail->save();
        if (!$penerimaanstokdetail->save()) {
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

        lanjut:

        // dd('abcd');
        //
        return $penerimaanStokDetailFifo;
    }

    public function processStoreOld(PenerimaanStokHeader $penerimaanStokHeader, array $data): PenerimaanStokDetailFifo
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

        // dd(db::table($tempmasuk)->get());

        $querymsk = DB::table($tempmasuk)
            ->select(
                DB::raw("sum(fqty) as qty")
            )
            ->first();

        $qtyin = $querymsk->qty ?? 0;

        // dd($qtyin);


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
            $table->dateTime('tglbukti')->nullable();
            $table->bigInteger('id')->nullable();
        });

        $tempkeluarlist = '##tempkeluarlist' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempkeluarlist, function ($table) {
            $table->string('fntrans', 100)->nullable();
            $table->dateTime('ftgl')->nullable();
            $table->string('fkstck', 100)->nullable();
            $table->string('fkgdg', 100)->nullable();
            $table->double('fqty', 15, 2)->nullable();
            $table->double('furut', 15, 2)->nullable();
            $table->bigInteger('fid')->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->bigInteger('id')->nullable();
        });


        $querytempkeluarlist = PengeluaranStokDetail::select(
            'b.nobukti as FNtrans',
            'b.tglbukti as Ftgl',
            DB::raw("rtrim(ltrim(str(" . $data['stok_id'] . "))) as FKstck"),
            'b.gudang_id as  FKgdg',
            'PengeluaranStokDetail.qty as FQty',
            DB::raw(" row_number() Over(Order By B.tglbukti ,PengeluaranStokDetail.id)  as urut"),
            'PengeluaranStokDetail.id',
            'B.tglbukti',
            'pengeluaranstokdetail.id'
        )
            ->join('pengeluaranstokheader as B', 'B.id', 'pengeluaranstokdetail.pengeluaranstokheader_id')
            ->join('stok as D', 'D.id', 'pengeluaranstokdetail.stok_id')

            ->where('pengeluaranstokdetail.stok_id', '=',  $data['stok_id'])
            ->where('pengeluaranstokdetail.nobukti', '=',  $data['nobukti'])
            ->orderBy('B.tglbukti', 'Asc')
            ->orderBy('pengeluaranstokdetail.id', 'Asc');



        DB::table($tempkeluarlist)->insertUsing([
            'fntrans',
            'ftgl',
            'fkstck',
            'fkgdg',
            'fqty',
            'furut',
            'fid',
            'tglbukti',
            'id'
        ], $querytempkeluarlist);


        $querytempkeluarlist = PenerimaanStokDetail::select(
            'b.nobukti as FNtrans',
            'b.tglbukti as Ftgl',
            DB::raw("rtrim(ltrim(str(" . $data['stok_id'] . "))) as FKstck"),
            'b.gudangdari_id as  FKgdg',
            'PenerimaanStokDetail.qty as FQty',
            DB::raw(" row_number() Over(Order By B.tglbukti ,PenerimaanStokDetail.id)  as urut"),
            'PenerimaanStokDetail.id',
            'B.tglbukti',
            'penerimaanstokdetail.id'
        )
            ->join('penerimaanstokheader as B', 'B.id', 'penerimaanstokdetail.penerimaanstokheader_id')
            // ->join('gudang as C', 'C.id', 'B.gudang_id')
            ->join('stok as D', 'D.id', 'penerimaanstokdetail.stok_id')
            ->where('B.gudangdari_id', '=',  $data['gudang_id'])
            ->where('penerimaanstokdetail.stok_id', '=',  $data['stok_id'])
            ->where('penerimaanstokdetail.nobukti', '=',  $data['nobukti'])
            ->orderBy('B.tglbukti', 'Asc')
            ->orderBy('penerimaanstokdetail.id', 'Asc');



        DB::table($tempkeluarlist)->insertUsing([
            'fntrans',
            'ftgl',
            'fkstck',
            'fkgdg',
            'fqty',
            'furut',
            'fid',
            'tglbukti',
            'id'
        ], $querytempkeluarlist);

        $querytempkeluar = DB::table($tempkeluarlist)->from(
            db::raw($tempkeluarlist . " as a")
        )->select(
            'a.FNtrans',
            'a.Ftgl',
            'a.FKstck',
            'a.FKgdg',
            'a.FQty',
            DB::raw(" row_number() Over(Order By a.tglbukti ,a.id)  as urut"),
            'a.fid',
            'a.tglbukti',
            'a.id'
        )
            ->orderBy('a.tglbukti', 'Asc')
            ->orderBy('a.id', 'Asc');


        // dd($querytempkeluar->get());
        DB::table($tempkeluar)->insertUsing([
            'fntrans',
            'ftgl',
            'fkstck',
            'fkgdg',
            'fqty',
            'furut',
            'fid',
            'tglbukti',
            'id',
        ], $querytempkeluar);

        // dd('test');

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

            $penerimaanStokDetailFifo = new PenerimaanStokDetailFifo();
            $penerimaanStokDetailFifo->penerimaanstokheader_id = $data['penerimaanstokheader_id'] ?? 0;
            $penerimaanStokDetailFifo->nobukti = $data['nobukti'] ?? '';
            $penerimaanStokDetailFifo->stok_id = $data['stok_id'] ?? 0;
            $penerimaanStokDetailFifo->gudang_id = $data['gudang_id'] ?? 0;
            $penerimaanStokDetailFifo->urut = $item['urut'] ?? 0;
            $penerimaanStokDetailFifo->qty = $item['qty'] ?? 0;
            $penerimaanStokDetailFifo->penerimaanstokheader_nobukti = $item['penerimaan_nobukti'] ?? '';
            $penerimaanStokDetailFifo->penerimaanstok_qty = $item['penerimaan_qty'] ?? 0;
            $penerimaanStokDetailFifo->penerimaanstok_harga = $item['penerimaan_harga'] ?? 0;
            $penerimaanStokDetailFifo->modifiedby = $data['modifiedby'] ?? '';
            $total = $item['penerimaan_qty'] * $item['penerimaan_harga'];

            if (!$penerimaanStokDetailFifo->save()) {
                throw new \Exception("Error storing penerimaan Stok Detail fifo.");
            }
            if ($data['penerimaanstok_id'] == $spk->text) {
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



        $penerimaanstokdetail  = PenerimaanStokDetail::lockForUpdate()->where("stok_id", $item['stok_id'])
            ->where("nobukti", $data['nobukti'])
            ->firstorFail();



        $hrgsat = $totalharga / $data['qty'];
        $penerimaanstokdetail->harga =   $hrgsat;
        $penerimaanstokdetail->total =  $totalharga;
        // $penerimaanstokdetail->save();
        if (!$penerimaanstokdetail->save()) {


            throw new \Exception("Error storing pengeluaran Stok Detail  update fifo. ");
        }
        // dd('test');
        return $penerimaanStokDetailFifo;
    }
}
