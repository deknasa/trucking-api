<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Models\PengeluaranStokDetailFifo;
use App\Models\PenerimaanStokDetail;
use App\Models\PenerimaanStok;


class KartuStok extends MyModel
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

    public function get()
    {
        $this->setRequestParameters();

        $tgldari = date('Y-m-d', strtotime(request()->dari));
        $tglsampai = date('Y-m-d', strtotime(request()->sampai));

        $filtergudang = Parameter::where('grp', 'STOK PERSEDIAAN')->where('subgrp', 'STOK PERSEDIAAN')->where('text', 'GUDANG')->first();
        $filtertrado = Parameter::where('grp', 'STOK PERSEDIAAN')->where('subgrp', 'STOK PERSEDIAAN')->where('text', 'TRADO')->first();
        $filtergandengan = Parameter::where('grp', 'STOK PERSEDIAAN')->where('subgrp', 'STOK PERSEDIAAN')->where('text', 'GANDENGAN')->first();

        // dump(request()->filter);
        // dd($filter->id);

        // if (request()->filter == $filter->id) {
        // dd('test');
        // dd($filter->text);
        if (request()->filter == $filtergudang->id) {
            $query = $this->getlaporan($tgldari, $tglsampai, request()->stokdari_id, request()->stoksampai_id, request()->datafilter, 0, 0, $filtergudang->text);
        } else if (request()->filter == $filtertrado->id) {
            $query = $this->getlaporan($tgldari, $tglsampai, request()->stokdari_id, request()->stoksampai_id, 0, request()->datafilter, 0, $filtertrado->text);
        } else if (request()->filter == $filtergandengan->id) {
            $query = $this->getlaporan($tgldari, $tglsampai, request()->stokdari_id, request()->stoksampai_id, 0, 0, request()->datafilter, $filtergandengan->text);
        } else {
            $query = $this->getlaporan($tgldari, $tglsampai, request()->stokdari_id, request()->stoksampai_id, request()->datafilter, 0, 0, $filtergudang->text);
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();


        // } else {
        //     $data = [];
        // }

        return $data;
    }

    public function default()
    {

        $tempStokDari = '##tempStokDari' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($tempStokDari, function ($table) {
            $table->unsignedBigInteger('stokdari_id')->nullable();
            $table->string('stokdari', 255)->nullable();
        });
        $stokDari = Stok::from(
            DB::raw('stok with (readuncommitted)')
        )
            ->select(
                'id as stokdari_id',
                'namastok as stokdari',

            )
            ->orderBy('id', 'asc')
            ->limit(1)
            ->first();


        DB::table($tempStokDari)->insert(
            ["stokdari_id" => $stokDari->stokdari_id, "stokdari" => $stokDari->stokdari]
        );


        $tempStokSampai = '##tempStokSampai' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($tempStokSampai, function ($table) {
            $table->unsignedBigInteger('stoksampai_id')->nullable();
            $table->string('stoksampai', 255)->nullable();
        });
        $stokSampai = Stok::from(
            DB::raw('stok with (readuncommitted)')
        )
            ->select(
                'id as stoksampai_id',
                'namastok as stoksampai',

            )
            ->orderBy('id', 'asc')
            ->limit(1)
            ->first();
        DB::table($tempStokSampai)->insert(
            ["stoksampai_id" => $stokSampai->stoksampai_id, "stoksampai" => $stokSampai->stoksampai]
        );

        $tempGudang = '##tempGudang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($tempGudang, function ($table) {
            $table->unsignedBigInteger('gudang_id')->nullable();
            $table->string('gudang', 255)->nullable();
        });
        $gudang = Gudang::from(
            DB::raw('gudang with (readuncommitted)')
        )
            ->select(
                'id as gudang_id',
                'gudang as gudang',

            )
            ->orderBy('id', 'asc')
            ->limit(1)
            ->first();

        DB::table($tempGudang)->insert(
            ["gudang_id" => $gudang->gudang_id, "gudang" => $gudang->gudang]
        );

        $tempTrado = '##tempTrado' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($tempTrado, function ($table) {
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->string('trado', 255)->nullable();
        });
        $trado = Trado::from(
            DB::raw('trado with (readuncommitted)')
        )
            ->select(
                'id as trado_id',
                'kodetrado as trado',

            )
            ->orderBy('id', 'asc')
            ->limit(1)
            ->first();

        DB::table($tempTrado)->insert(
            ["trado_id" => $trado->trado_id, "trado" => $trado->trado]
        );

        $tempGandengan = '##tempGandengan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($tempGandengan, function ($table) {
            $table->unsignedBigInteger('gandengan_id')->nullable();
            $table->string('gandengan', 255)->nullable();
        });
        $gandengan = Gandengan::from(
            DB::raw('gandengan with (readuncommitted)')
        )
            ->select(
                'id as gandengan_id',
                'keterangan as gandengan',

            )
            ->orderBy('id', 'asc')
            ->limit(1)
            ->first();

        DB::table($tempGandengan)->insert(
            ["gandengan_id" => $gandengan->gandengan_id, "gandengan" => $gandengan->gandengan]
        );

        $tempFilter = '##tempFilter' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($tempFilter, function ($table) {
            $table->unsignedBigInteger('filter')->nullable();
        });
        $filter = Parameter::from(
            DB::raw('parameter with (readuncommitted)')
        )
            ->where('grp', 'STOK PERSEDIAAN')
            ->where('text', 'GUDANG')
            ->first();

        DB::table($tempFilter)->insert(
            ["filter" => $filter->id]
        );


        $data = [
            'stokdari' => DB::table($tempStokDari)->from(DB::raw($tempStokDari))->first(),
            'stoksampai' => DB::table($tempStokSampai)->from(DB::raw($tempStokSampai))->first(),
            'gudang' => DB::table($tempGudang)->from(DB::raw($tempGudang))->first(),
            'filter' => DB::table($tempFilter)->from(DB::raw($tempFilter))->first(),
            'trado' => DB::table($tempTrado)->from(DB::raw($tempTrado))->first(),
            'gandengan' => DB::table($tempGandengan)->from(DB::raw($tempGandengan))->first(),
        ];

        return $data;
    }

    private function getlaporan($tgldari, $tglsampai, $stokdari, $stoksampai, $gudang_id, $trado_id, $gandengan_id, $filter)
    {

        $gudang_id = $gudang_id ?? 0;
        $trado_id = $trado_id ?? 0;
        $gandengan_id = $gandengan_id ?? 0;



        $filtergudang = Parameter::where('grp', 'STOK PERSEDIAAN')->where('subgrp', 'STOK PERSEDIAAN')->where('text', 'GUDANG')->first();
        $filtertrado = Parameter::where('grp', 'STOK PERSEDIAAN')->where('subgrp', 'STOK PERSEDIAAN')->where('text', 'TRADO')->first();
        $filtergandengan = Parameter::where('grp', 'STOK PERSEDIAAN')->where('subgrp', 'STOK PERSEDIAAN')->where('text', 'GANDENGAN')->first();


        $templaporan = '##templaporan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($templaporan, function ($table) {
            $table->id();
            $table->unsignedBigInteger('kodebarang')->nullable();
            $table->string('namabarang', 1000)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->unsignedBigInteger('kategori_id')->nullable();
            $table->double('qtymasuk', 15, 2)->nullable();
            $table->double('nilaimasuk', 15, 2)->nullable();
            $table->double('qtykeluar', 15, 2)->nullable();
            $table->double('nilaikeluar', 15, 2)->nullable();
            $table->double('qtysaldo', 15, 2)->nullable();
            $table->double('nilaisaldo', 15, 2)->nullable();
            $table->string('modifiedby', 100)->nullable();
        });

        $temprekap = '##temprekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekap, function ($table) {
            $table->id();
            $table->integer('statusmasuk')->length(11)->nullable();;
            $table->unsignedBigInteger('kodebarang')->nullable();
            $table->string('namabarang', 1000)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->unsignedBigInteger('kategori_id')->nullable();
            $table->double('qtymasuk', 15, 2)->nullable();
            $table->double('nilaimasuk', 15, 2)->nullable();
            $table->double('qtykeluar', 15, 2)->nullable();
            $table->double('nilaikeluar', 15, 2)->nullable();
            $table->double('qtysaldo', 15, 2)->nullable();
            $table->double('nilaisaldo', 15, 2)->nullable();
            $table->string('modifiedby', 100)->nullable();
        });


        $tempsaldoawalmasuk = '##tempsaldoawalmasuk' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldoawalmasuk, function ($table) {
            $table->id();
            $table->unsignedBigInteger('kodebarang')->nullable();
            $table->double('qtymasuk', 15, 2)->nullable();
            $table->double('nilaimasuk', 15, 2)->nullable();
        });

        $tempsaldoawalkeluar = '##tempsaldoawalkeluar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldoawalkeluar, function ($table) {
            $table->id();
            $table->unsignedBigInteger('kodebarang')->nullable();
            $table->double('qtykeluar', 15, 2)->nullable();
            $table->double('nilaikeluar', 15, 2)->nullable();
        });

        $tempsaldoawal = '##tempsaldoawal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldoawal, function ($table) {
            $table->id();
            $table->unsignedBigInteger('kodebarang')->nullable();
            $table->double('qtysaldo', 15, 2)->nullable();
            $table->double('nilaisaldo', 15, 2)->nullable();
        });

        $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
        $gudangsementara = Parameter::where('grp', 'GUDANG SEMENTARA')->where('subgrp', 'GUDANG SEMENTARA')->first();
        $gudangpihak3 = Parameter::where('grp', 'GUDANG PIHAK3')->where('subgrp', 'GUDANG PIHAK3')->first();

        // and $gudang_id = $gudangkantor->text)

        $spb = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();

        $korplus = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', 'KOR STOK')->where('subgrp', 'KOR STOK')->first();


        $saldoawal = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', 'SALDO AWAL STOK')->where('subgrp', 'SALDO AWAL STOK')->first();

        $pg = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', 'PG STOK')->where('subgrp', 'PG STOK')->first();

        $pgdo = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', 'DO STOK')->where('subgrp', 'DO STOK')->first();

        $spbs = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', 'REUSE STOK')->where('subgrp', 'REUSE STOK')->first();


        $spk = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();

        $retur = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', 'RETUR STOK')->where('subgrp', 'RETUR STOK')->first();

        $korminus = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', 'KOR MINUS STOK')->where('subgrp', 'KOR MINUS STOK')->first();



        if ($filter == $filtergudang->text) {
            //=========================================saldo awal masuk=========================================
            if ($gudang_id == $gudangkantor->text) {
                $penerimaanstok_id = $spb->text . ',' . $saldoawal->text . ',' . $korplus->text .','.  $spbs->text;
                $pengeluaranstok_id = $spk->text . ',' . $korminus->text . ',' . $retur->text;
            } else if ($gudang_id == $gudangsementara->text) {
                $penerimaanstok_id = $pg->text . ',' . $pgdo->text . ',' . $spbs->text;
                $pengeluaranstok_id = $korminus->text;
            } else if ($gudang_id == $gudangpihak3->text) {
                $penerimaanstok_id = $pg->text . ',' . $pgdo->text . ',' . $spbs->text;
                $pengeluaranstok_id = $korminus->text;
            } else {
                $penerimaanstok_id = $spb->text . ',' . $saldoawal->text . ',' . $korplus->text;
                $pengeluaranstok_id = $spk->text . ',' . $korminus->text . ',' . $retur->text;
            }
        } else if ($filter == $filtertrado->text) {
            $penerimaanstok_id =  $pg->text . ',' . $pgdo->text . ',' . $spbs->text . ',' . $saldoawal->text . ',' . $korplus->text;
            $pengeluaranstok_id = $korminus->text;
        } else if ($filter == $filtergandengan->text) {
            $penerimaanstok_id =  $pg->text . ',' . $pgdo->text . ',' . $spbs->text . ',' . $saldoawal->text . ',' . $korplus->text;
            $pengeluaranstok_id = $korminus->text;
        } else {
            if ($gudang_id == $gudangkantor->text) {
                $penerimaanstok_id = $spb->text . ',' . $saldoawal->text . ',' . $korplus->text;
                $pengeluaranstok_id = $spk->text . ',' . $korminus->text . ',' . $retur->text;
            } else if ($gudang_id == $gudangsementara->text) {
                $penerimaanstok_id = $pg->text . ',' . $pgdo->text . ',' . $spbs->text;
                $pengeluaranstok_id = $korminus->text;
            } else if ($gudang_id == $gudangpihak3->text) {
                $penerimaanstok_id = $pg->text . ',' . $pgdo->text . ',' . $spbs->text;
                $pengeluaranstok_id = $korminus->text;
            }
        }


        if ($gudang_id != 0) {

            $querysaldomasuk = PenerimaanstokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    'c.id as kodebarang',
                    DB::raw("sum(b.qty) as qtymasuk"),
                    DB::raw("sum(b.qty*b.harga) as nilaimasuk"),
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti <'" . $tgldari . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id . ")")
                ->whereRaw("(a.gudang_id=" . $gudang_id)
                ->OrwhereRaw("a.gudangke_id=" . $gudang_id . ")")
                ->groupBy('c.id');
        } else if ($trado_id != 0) {
            $querysaldomasuk = PenerimaanstokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    'c.id as kodebarang',
                    DB::raw("sum(b.qty) as qtymasuk"),
                    DB::raw("sum(b.qty*b.harga) as nilaimasuk"),
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti <'" . $tgldari . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id . ")")
                ->whereRaw("(a.trado_id=" . $trado_id)
                ->OrwhereRaw("a.tradoke_id=" . $trado_id . ")")
                ->groupBy('c.id');
        } else if ($gandengan_id != 0) {
            $querysaldomasuk = PenerimaanstokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    'c.id as kodebarang',
                    DB::raw("sum(b.qty) as qtymasuk"),
                    DB::raw("sum(b.qty*b.harga) as nilaimasuk"),
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti <'" . $tgldari . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id . ")")
                ->whereRaw("(a.gandengan_id=" . $gandengan_id)
                ->OrwhereRaw("a.gandenganke_id=" . $gandengan_id . ")")
                ->groupBy('c.id');
        } else {

            $querysaldomasuk = PenerimaanstokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    'c.id as kodebarang',
                    DB::raw("sum(b.qty) as qtymasuk"),
                    DB::raw("sum(b.qty*b.harga) as nilaimasuk"),
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti <'" . $tgldari . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id . ")")
                ->whereRaw("(a.gudang_id=" . $gudang_id)
                ->OrwhereRaw("a.gudangke_id=" . $gudang_id . ")")
                ->groupBy('c.id');
        }



        // dd($stokdari);
        //  dd($querysaldomasuk->get());




        DB::table($tempsaldoawalmasuk)->insertUsing([
            'kodebarang',
            'qtymasuk',
            'nilaimasuk',
        ], $querysaldomasuk);



        $statusreuse = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', 'STATUS REUSE')->where('subgrp', 'STATUS REUSE')->where('text', 'REUSE')->first();



        $pengeluaranstok_id2 = $spk->text;
        if ($trado_id != 0) {
            $querysaldomasuk = PengeluaranstokHeader::from(
                DB::raw("pengeluaranstokheader as a with (readuncommitted)")
            )
                ->select(
                    'c.id as kodebarang',
                    DB::raw("sum(b.penerimaanstok_qty) as qtymasuk"),
                    // DB::raw("0 as qtymasuk"),
                    DB::raw("0 as nilaimasuk"),
                    // DB::raw("sum(b.penerimaanstok_qty*b.penerimaanstok_harga) as nilaimasuk"),
                )
                ->join(DB::raw("pengeluaranstokdetailfifo as b with (readuncommitted)"), 'a.id', 'b.pengeluaranstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti <'" . $tgldari . "')")
                ->whereRaw("a.pengeluaranstok_id in(" . $pengeluaranstok_id2 . ")")
                ->whereRaw("a.trado_id in(" . $trado_id . ")")
                ->whereRaw("c.statusreuse in(" . $statusreuse->id . ")")
                ->groupBy('c.id');

            DB::table($tempsaldoawalmasuk)->insertUsing([
                'kodebarang',
                'qtymasuk',
                'nilaimasuk',
            ], $querysaldomasuk);
        } else   if ($gandengan_id != 0) {
            $querysaldomasuk = PengeluaranstokHeader::from(
                DB::raw("pengeluaranstokheader as a with (readuncommitted)")
            )
                ->select(
                    'c.id as kodebarang',
                    DB::raw("sum(b.penerimaanstok_qty) as qtymasuk"),
                    // DB::raw("0 as qtymasuk"),
                    DB::raw("0 as nilaimasuk"),
                    // DB::raw("sum(b.penerimaanstok_qty) as qtymasuk"),
                    // DB::raw("sum(b.penerimaanstok_qty*b.penerimaanstok_harga) as nilaimasuk"),
                )
                ->join(DB::raw("pengeluaranstokdetailfifo as b with (readuncommitted)"), 'a.id', 'b.pengeluaranstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti <'" . $tgldari . "')")
                ->whereRaw("a.pengeluaranstok_id in(" . $pengeluaranstok_id2 . ")")
                ->whereRaw("a.trado_id in(" . $trado_id . ")")
                ->whereRaw("c.statusreuse in(" . $statusreuse->id . ")")
                ->groupBy('c.id');

            DB::table($tempsaldoawalmasuk)->insertUsing([
                'kodebarang',
                'qtymasuk',
                'nilaimasuk',
            ], $querysaldomasuk);
        }








        //=========================================query rekap data masuk=========================================
        if ($gudang_id != 0) {
            $queryrekap = PenerimaanStokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    DB::raw("1 as statusmasuk"),
                    'c.id as kodebarang',
                    'c.namastok as namabarang',
                    'a.tglbukti as tglbukti',
                    'a.nobukti as nobukti',
                    'c.kategori_id',
                    'b.qty as qtymasuk',
                    DB::raw("(b.qty*b.harga) as nilaimasuk"),
                    DB::raw("0 as qtykeluar"),
                    DB::raw("0 as nilaikeluar"),
                    DB::raw("0 as qtysaldo"),
                    DB::raw("0 as nilaisaldo"),
                    'a.modifiedby'
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id  . ")")
                ->whereRaw("(a.gudang_id=" . $gudang_id)
                ->OrwhereRaw("a.gudangke_id=" . $gudang_id . ")")
                ->orderBy('a.id', 'Asc')
                ->orderBy('a.tglbukti', 'Asc')
                ->orderBy('a.nobukti', 'Asc')
                ->orderBy('b.id', 'Asc');
        } else if ($gandengan_id != 0) {
            $queryrekap = PenerimaanStokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    DB::raw("1 as statusmasuk"),
                    'c.id as kodebarang',
                    'c.namastok as namabarang',
                    'a.tglbukti as tglbukti',
                    'a.nobukti as nobukti',
                    'c.kategori_id',
                    'b.qty as qtymasuk',
                    DB::raw("(b.qty*b.harga) as nilaimasuk"),
                    DB::raw("0 as qtykeluar"),
                    DB::raw("0 as nilaikeluar"),
                    DB::raw("0 as qtysaldo"),
                    DB::raw("0 as nilaisaldo"),
                    'a.modifiedby'
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id  . ")")
                ->whereRaw("(a.gandengan_id=" . $gandengan_id)
                ->OrwhereRaw("a.gandenganke_id=" . $gandengan_id . ")")
                ->orderBy('a.id', 'Asc')
                ->orderBy('a.tglbukti', 'Asc')
                ->orderBy('a.nobukti', 'Asc')
                ->orderBy('b.id', 'Asc');
        } else if ($trado_id != 0) {
            $queryrekap = PenerimaanStokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    DB::raw("1 as statusmasuk"),
                    'c.id as kodebarang',
                    'c.namastok as namabarang',
                    'a.tglbukti as tglbukti',
                    'a.nobukti as nobukti',
                    'c.kategori_id',
                    'b.qty as qtymasuk',
                    DB::raw("(b.qty*b.harga) as nilaimasuk"),
                    DB::raw("0 as qtykeluar"),
                    DB::raw("0 as nilaikeluar"),
                    DB::raw("0 as qtysaldo"),
                    DB::raw("0 as nilaisaldo"),
                    'a.modifiedby'
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id  . ")")
                ->whereRaw("(a.trado_id=" . $trado_id)
                ->OrwhereRaw("a.tradoke_id=" . $trado_id . ")")
                ->orderBy('a.id', 'Asc')
                ->orderBy('a.tglbukti', 'Asc')
                ->orderBy('a.nobukti', 'Asc')
                ->orderBy('b.id', 'Asc');
        } else {
            $queryrekap = PenerimaanStokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    DB::raw("1 as statusmasuk"),
                    'c.id as kodebarang',
                    'c.namastok as namabarang',
                    'a.tglbukti as tglbukti',
                    'a.nobukti as nobukti',
                    'c.kategori_id',
                    'b.qty as qtymasuk',
                    DB::raw("(b.qty*b.harga) as nilaimasuk"),
                    DB::raw("0 as qtykeluar"),
                    DB::raw("0 as nilaikeluar"),
                    DB::raw("0 as qtysaldo"),
                    DB::raw("0 as nilaisaldo"),
                    'a.modifiedby'
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id  . ")")
                ->whereRaw("(a.gudang_id=" . $gudang_id)
                ->OrwhereRaw("a.gudangke_id=" . $gudang_id . ")")
                ->orderBy('a.id', 'Asc')
                ->orderBy('a.tglbukti', 'Asc')
                ->orderBy('a.nobukti', 'Asc')
                ->orderBy('b.id', 'Asc');
        }


        DB::table($temprekap)->insertUsing([
            'statusmasuk',
            'kodebarang',
            'namabarang',
            'tglbukti',
            'nobukti',
            'kategori_id',
            'qtymasuk',
            'nilaimasuk',
            'qtykeluar',
            'nilaikeluar',
            'qtysaldo',
            'nilaisaldo',
            'modifiedby',
        ], $queryrekap);

        if ($trado_id != 0) {
            $queryrekap = PengeluaranStokHeader::from(
                DB::raw("pengeluaranstokheader as a with (readuncommitted)")
            )
                ->select(
                    DB::raw("2 as statusmasuk"),
                    'c.id as kodebarang',
                    'c.namastok as namabarang',
                    'a.tglbukti as tglbukti',
                    'a.nobukti as nobukti',
                    'c.kategori_id',
                    DB::raw("b.penerimaanstok_qty as qtymasuk"),
                    // DB::raw("(b.penerimaanstok_qty*b.penerimaanstok_harga) as nilaimasuk"),
                    DB::raw("0 as nilaimasuk"),
                    DB::raw("0 as qtykeluar"),
                    DB::raw("0 as nilaikeluar"),
                    DB::raw("0 as qtysaldo"),
                    DB::raw("0 as nilaisaldo"),
                    'a.modifiedby'
                )
                ->join(DB::raw("pengeluaranstokdetailfifo as b with (readuncommitted)"), 'a.id', 'b.pengeluaranstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("a.pengeluaranstok_id in(" . $pengeluaranstok_id2 . ")")
                ->whereRaw("c.statusreuse in(" . $statusreuse->id . ")")                
                ->whereRaw("a.trado_id in(" . $trado_id . ")")                
                ->orderBy('a.id', 'Asc')
                ->orderBy('a.tglbukti', 'Asc')
                ->orderBy('a.nobukti', 'Asc')
                ->orderBy('b.id', 'Asc');


            DB::table($temprekap)->insertUsing([
                'statusmasuk',
                'kodebarang',
                'namabarang',
                'tglbukti',
                'nobukti',
                'kategori_id',
                'qtymasuk',
                'nilaimasuk',
                'qtykeluar',
                'nilaikeluar',
                'qtysaldo',
                'nilaisaldo',
                'modifiedby',
            ], $queryrekap);
        } else  if ($gandengan_id != 0) {
            $queryrekap = PengeluaranStokHeader::from(
                DB::raw("pengeluaranstokheader as a with (readuncommitted)")
            )
                ->select(
                    DB::raw("2 as statusmasuk"),
                    'c.id as kodebarang',
                    'c.namastok as namabarang',
                    'a.tglbukti as tglbukti',
                    'a.nobukti as nobukti',
                    'c.kategori_id',
                    DB::raw("b.penerimaanstok_qty as qtymasuk"),
                    // DB::raw("(b.penerimaanstok_qty*b.penerimaanstok_harga) as nilaimasuk"),
                    DB::raw("0 as nilaimasuk"),
                    DB::raw("0 as qtykeluar"),
                    DB::raw("0 as nilaikeluar"),
                    DB::raw("0 as qtysaldo"),
                    DB::raw("0 as nilaisaldo"),
                    'a.modifiedby'
                )
                ->join(DB::raw("pengeluaranstokdetailfifo as b with (readuncommitted)"), 'a.id', 'b.pengeluaranstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("a.pengeluaranstok_id in(" . $pengeluaranstok_id2 . ")")
                ->whereRaw("c.statusreuse in(" . $statusreuse->id . ")")                
                ->whereRaw("a.gandengan_id in(" . $gandengan_id . ")")                
                ->orderBy('a.id', 'Asc')
                ->orderBy('a.tglbukti', 'Asc')
                ->orderBy('a.nobukti', 'Asc')
                ->orderBy('b.id', 'Asc');


            DB::table($temprekap)->insertUsing([
                'statusmasuk',
                'kodebarang',
                'namabarang',
                'tglbukti',
                'nobukti',
                'kategori_id',
                'qtymasuk',
                'nilaimasuk',
                'qtykeluar',
                'nilaikeluar',
                'qtysaldo',
                'nilaisaldo',
                'modifiedby',
            ], $queryrekap);
        }




        //=========================================saldo awal keluar=========================================

        $spk = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();

        $querysaldokeluar = PengeluaranstokHeader::from(
            DB::raw("pengeluaranstokheader as a with (readuncommitted)")
        )
            ->select(
                'c.id as kodebarang',
                DB::raw("sum(b.penerimaanstok_qty) as qtykeluar"),
                DB::raw("sum(b.penerimaanstok_qty*b.penerimaanstok_harga) as nilaikeluar"),
            )
            ->join(DB::raw("pengeluaranstokdetailfifo as b with (readuncommitted)"), 'a.id', 'b.pengeluaranstokheader_id')
            ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
            ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti <'" . $tgldari . "')")
            ->whereRaw("a.pengeluaranstok_id in(" . $pengeluaranstok_id . ")")
            ->groupBy('c.id');

        DB::table($tempsaldoawalkeluar)->insertUsing([
            'kodebarang',
            'qtykeluar',
            'nilaikeluar',
        ], $querysaldokeluar);

        if ($gudang_id != 0) {
            $querysaldokeluar = PenerimaanstokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    'c.id as kodebarang',
                    DB::raw("sum(b.qty) as qtykeluar"),
                    DB::raw("sum(b.qty*b.harga) as nilaikeluar"),
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti <'" . $tgldari . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id . ")")
                ->where('a.gudangdari_id', $gudang_id)
                ->groupBy('c.id');
        } else if ($trado_id != 0) {
            $querysaldokeluar = PenerimaanstokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    'c.id as kodebarang',
                    DB::raw("sum(b.qty) as qtykeluar"),
                    DB::raw("sum(b.qty*b.harga) as nilaikeluar"),
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti <'" . $tgldari . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id . ")")
                ->where('a.tradodari_id', $trado_id)
                ->groupBy('c.id');
        } else if ($gandengan_id != 0) {
            $querysaldokeluar = PenerimaanstokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    'c.id as kodebarang',
                    DB::raw("sum(b.qty) as qtykeluar"),
                    DB::raw("sum(b.qty*b.harga) as nilaikeluar"),
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti <'" . $tgldari . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id . ")")
                ->where('a.gandengandari_id', $gandengan_id)
                ->groupBy('c.id');
        } else {
            $querysaldokeluar = PenerimaanstokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    'c.id as kodebarang',
                    DB::raw("sum(b.qty) as qtykeluar"),
                    DB::raw("sum(b.qty*b.harga) as nilaikeluar"),
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti <'" . $tgldari . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id . ")")
                ->where('a.gudangdari_id', $gudang_id)
                ->groupBy('c.id');
        }

        // dd($querysaldokeluar->get());

        DB::table($tempsaldoawalkeluar)->insertUsing([
            'kodebarang',
            'qtykeluar',
            'nilaikeluar',
        ], $querysaldokeluar);


        //=========================================saldo awal=========================================
        //masuk - keluar
        $querysaldo = Stok::from(
            DB::raw("stok as a with (readuncommitted)")
        )
            ->select(
                'a.id as kodebarang',
                DB::raw("(isnull(b.qtymasuk,0)-isnull(c.qtykeluar,0)) as qtysaldo"),
                DB::raw("(isnull(b.nilaimasuk,0)-isnull(c.nilaikeluar,0)) as nilaisaldo"),
            )
            ->leftjoin(DB::raw($tempsaldoawalmasuk . " as b"), 'a.id', 'b.kodebarang')
            ->leftjoin(DB::raw($tempsaldoawalkeluar . " as c"), 'a.id', 'c.kodebarang')
            ->whereRaw("(a.id>=" . $stokdari . " and a.id<=" . $stoksampai . " ) ");


        DB::table($tempsaldoawal)->insertUsing([
            'kodebarang',
            'qtysaldo',
            'nilaisaldo',
        ], $querysaldo);

        $queryrekap = DB::table($tempsaldoawal)->from(
            DB::raw($tempsaldoawal . " as A")
        )
            ->select(
                DB::raw("0 as statusmasuk"),
                'c.id as kodebarang',
                DB::raw("c.namastok as namabarang"),
                DB::raw("'" . $tgldari . "' as tglbukti"),
                DB::raw("'Saldo Awal' as nobukti"),
                'c.kategori_id',
                DB::raw("a.qtysaldo as qtymasuk"),
                DB::raw("a.nilaisaldo as nilaimasuk"),
                DB::raw("0 as qtykeluar"),
                DB::raw("0 as nilaikeluar"),
                DB::raw("0 as qtysaldo"),
                DB::raw("0 as nilaisaldo"),
                DB::raw("'' as modifiedby"),
            )
            ->join(DB::raw("stok as c with (readuncommitted)"), 'a.kodebarang', 'c.id');
        //saldo awal

        DB::table($temprekap)->insertUsing([
            'statusmasuk',
            'kodebarang',
            'namabarang',
            'tglbukti',
            'nobukti',
            'kategori_id',
            'qtymasuk',
            'nilaimasuk',
            'qtykeluar',
            'nilaikeluar',
            'qtysaldo',
            'nilaisaldo',
            'modifiedby',
        ], $queryrekap);



        $queryrekap = PengeluaranStokHeader::from(
            DB::raw("pengeluaranstokheader as a with (readuncommitted)")
        )
            ->select(
                DB::raw("2 as statusmasuk"),
                'c.id as kodebarang',
                'c.namastok as namabarang',
                'a.tglbukti as tglbukti',
                'a.nobukti as nobukti',
                'c.kategori_id',
                DB::raw("0 as qtymasuk"),
                DB::raw("0 as nilaimasuk"),
                DB::raw("b.penerimaanstok_qty as qtykeluar"),
                DB::raw("(b.penerimaanstok_qty*b.penerimaanstok_harga) as nilaikeluar"),
                DB::raw("0 as qtysaldo"),
                DB::raw("0 as nilaisaldo"),
                'a.modifiedby'
            )
            ->join(DB::raw("pengeluaranstokdetailfifo as b with (readuncommitted)"), 'a.id', 'b.pengeluaranstokheader_id')
            ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
            ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
            ->whereRaw("a.pengeluaranstok_id in(" . $pengeluaranstok_id . ")")
            ->orderBy('a.id', 'Asc')
            ->orderBy('a.tglbukti', 'Asc')
            ->orderBy('a.nobukti', 'Asc')
            ->orderBy('b.id', 'Asc');


        DB::table($temprekap)->insertUsing([
            'statusmasuk',
            'kodebarang',
            'namabarang',
            'tglbukti',
            'nobukti',
            'kategori_id',
            'qtymasuk',
            'nilaimasuk',
            'qtykeluar',
            'nilaikeluar',
            'qtysaldo',
            'nilaisaldo',
            'modifiedby',
        ], $queryrekap);

        if ($gudang_id != 0) {
            $queryrekap = PenerimaanStokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    DB::raw("2 as statusmasuk"),
                    'c.id as kodebarang',
                    'c.namastok as namabarang',
                    'a.tglbukti as tglbukti',
                    'a.nobukti as nobukti',
                    'c.kategori_id',
                    DB::raw("0 as qtymasuk"),
                    DB::raw("0 as nilaimasuk"),
                    'b.qty as qtykeluar',
                    DB::raw("(b.qty*b.harga) as nilaikeluar"),
                    DB::raw("0 as qtysaldo"),
                    DB::raw("0 as nilaisaldo"),
                    'a.modifiedby'
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id  . ")")
                ->where('a.gudangdari_id', $gudang_id)
                ->orderBy('a.id', 'Asc')
                ->orderBy('a.tglbukti', 'Asc')
                ->orderBy('a.nobukti', 'Asc')
                ->orderBy('b.id', 'Asc');
        } else if ($gandengan_id != 0) {
            $queryrekap = PenerimaanStokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    DB::raw("2 as statusmasuk"),
                    'c.id as kodebarang',
                    'c.namastok as namabarang',
                    'a.tglbukti as tglbukti',
                    'a.nobukti as nobukti',
                    'c.kategori_id',
                    DB::raw("0 as qtymasuk"),
                    DB::raw("0 as nilaimasuk"),
                    'b.qty as qtykeluar',
                    DB::raw("(b.qty*b.harga) as nilaikeluar"),
                    DB::raw("0 as qtysaldo"),
                    DB::raw("0 as nilaisaldo"),
                    'a.modifiedby'
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id  . ")")
                ->where('a.gandengandari_id', $gandengan_id)
                ->orderBy('a.id', 'Asc')
                ->orderBy('a.tglbukti', 'Asc')
                ->orderBy('a.nobukti', 'Asc')
                ->orderBy('b.id', 'Asc');
        } else if ($trado_id != 0) {
            $queryrekap = PenerimaanStokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    DB::raw("2 as statusmasuk"),
                    'c.id as kodebarang',
                    'c.namastok as namabarang',
                    'a.tglbukti as tglbukti',
                    'a.nobukti as nobukti',
                    'c.kategori_id',
                    DB::raw("0 as qtymasuk"),
                    DB::raw("0 as nilaimasuk"),
                    'b.qty as qtykeluar',
                    DB::raw("(b.qty*b.harga) as nilaikeluar"),
                    DB::raw("0 as qtysaldo"),
                    DB::raw("0 as nilaisaldo"),
                    'a.modifiedby'
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id  . ")")
                ->where('a.tradodari_id', $trado_id)
                ->orderBy('a.id', 'Asc')
                ->orderBy('a.tglbukti', 'Asc')
                ->orderBy('a.nobukti', 'Asc')
                ->orderBy('b.id', 'Asc');
        } else {
            $queryrekap = PenerimaanStokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    DB::raw("2 as statusmasuk"),
                    'c.id as kodebarang',
                    'c.namastok as namabarang',
                    'a.tglbukti as tglbukti',
                    'a.nobukti as nobukti',
                    'c.kategori_id',
                    DB::raw("0 as qtymasuk"),
                    DB::raw("0 as nilaimasuk"),
                    'b.qty as qtykeluar',
                    DB::raw("(b.qty*b.harga) as nilaikeluar"),
                    DB::raw("0 as qtysaldo"),
                    DB::raw("0 as nilaisaldo"),
                    'a.modifiedby'
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id  . ")")
                ->orderBy('a.id', 'Asc')
                ->where('a.gudangdari_id', $gudang_id)
                ->orderBy('a.tglbukti', 'Asc')
                ->orderBy('a.nobukti', 'Asc')
                ->orderBy('b.id', 'Asc');
        }

        DB::table($temprekap)->insertUsing([
            'statusmasuk',
            'kodebarang',
            'namabarang',
            'tglbukti',
            'nobukti',
            'kategori_id',
            'qtymasuk',
            'nilaimasuk',
            'qtykeluar',
            'nilaikeluar',
            'qtysaldo',
            'nilaisaldo',
            'modifiedby',
        ], $queryrekap);
        //akhir if gudang sebelumnya 
        // }

        $querylaporan = DB::table($temprekap)->from(
            DB::raw($temprekap . " as A")
        )
            ->select(
                'A.kodebarang',
                'A.namabarang',
                'A.tglbukti',
                'A.nobukti',
                'A.kategori_id',
                'A.qtymasuk',
                'A.nilaimasuk',
                'A.qtykeluar',
                'A.nilaikeluar',
                'A.qtysaldo',
                'A.nilaisaldo',
                'A.modifiedby',
            )
            ->orderBy('A.statusmasuk', 'Asc')
            ->orderBy('A.id', 'Asc');

        DB::table($templaporan)->insertUsing([
            'kodebarang',
            'namabarang',
            'tglbukti',
            'nobukti',
            'kategori_id',
            'qtymasuk',
            'nilaimasuk',
            'qtykeluar',
            'nilaikeluar',
            'qtysaldo',
            'nilaisaldo',
            'modifiedby',
        ], $querylaporan);

        $datalist = DB::table($templaporan)->from(
            DB::raw($templaporan . " as a")
        )
            ->select(
                'a.kodebarang',
                'a.namabarang',
                'a.tglbukti',
                'a.nobukti',
                'B.kodekategori as kategori_id',
                'a.qtymasuk',
                'a.nilaimasuk',
                'a.qtykeluar',
                'a.nilaikeluar',
                DB::raw("sum ((isnull(a.qtysaldo,0)+a.qtymasuk)-a.qtykeluar) over (order by a.tglbukti,a.id ASC) as qtysaldo"),
                DB::raw("sum ((isnull(a.nilaisaldo,0)+a.nilaimasuk)-a.nilaikeluar) over (order by a.tglbukti,a.id ASC) as nilaisaldo"),
                'a.modifiedby',
            )
            ->leftjoin('kategori as B', 'a.kategori_id', 'B.id')
            ->orderBy('a.tglbukti', 'asc')
            ->orderBy('a.id', 'asc');
        // dd($datalist->get());
        return $datalist;
    }


    public function getReport($stokdari_id, $stoksampai_id, $dari, $sampai, $filter, $datafilter)
    {
        // data coba coba
        $query = DB::table('pengeluaranstokdetailfifo')->select(
            'pengeluaranstokdetailfifo.id',
            'stok.namastok as namabarang',
            'stok.namaterpusat as kodebarang',
            'kategori.keterangan as kategori_id',
            'pengeluaranstokdetailfifo.qty as qtykeluar',
            'pengeluaranstokdetailfifo.penerimaanstok_qty as qtymasuk',
            'pengeluaranstokdetailfifo.modifiedby'
        )
            ->leftJoin('stok', 'pengeluaranstokdetailfifo.stok_id', 'stok.id')
            ->leftJoin('kategori', 'stok.kategori_id', 'kategori.id');

        $parameter = Parameter::where('id', $filter)->first();
        if ($parameter->text == 'GUDANG') {
            $gudang_id = $datafilter;
            $query->where('pengeluaranstokdetailfifo.gudang_id', $gudang_id);
        }
        if ($parameter->text == 'TRADO') {
            $trado_id = $datafilter;
            $query->where('pengeluaranstokdetailfifo.trado_id', $trado_id);
        }
        if ($parameter->text == 'GANDENGAN') {
            $gandengan_id = $datafilter;
            $query->where('pengeluaranstokdetailfifo.gandengan_id', $gandengan_id);
        }
        $data = $query->get();
        return $data;
    }

    public function filter($query, $relationFields = [])
    {

        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'namabarang') {
                            $query = $query->where('a.namabarang', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'kodebarang') {
                            $query = $query->where('a.kodebarang', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'kategori_id') {
                            $query = $query->where('b.kodekategori', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'qtymasuk' || $filters['field'] == 'nilaimasuk' || $filters['field'] == 'qtykeluar' || $filters['field'] == 'nilaikeluar') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'qtysaldo') {
                            $query = $query->whereRaw("format((isnull(a.qtysaldo,0)+a.qtymasuk)-a.qtykeluar, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'nilaisaldo') {
                            $query = $query->whereRaw("format((isnull(a.nilaisaldo,0)+a.nilaimasuk)-a.nilaikeluar, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglbukti') {
                            $query = $query->whereRaw("format(a.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw('a' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {

                            if ($filters['field'] == 'namabarang') {
                                $query = $query->orWhere('a.namabarang', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'kodebarang') {
                                $query = $query->orWhere('a.kodebarang', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'kategori') {
                                $query = $query->orWhere('b.kodekategori', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'qtymasuk' || $filters['field'] == 'nilaimasuk' || $filters['field'] == 'qtykeluar' || $filters['field'] == 'nilaikeluar') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'qtysaldo') {
                                $query = $query->orWhereRaw("format((isnull(a.qtysaldo,0)+a.qtymasuk)-a.qtykeluar, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'nilaisaldo') {
                                $query = $query->orWhereRaw("format((isnull(a.nilaisaldo,0)+a.nilaimasuk)-a.nilaikeluar, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti') {
                                $query = $query->orWhereRaw("format(a.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else {
                                // $query->orWhere('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw('a' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    });

                    break;
                default:

                    break;
            }

            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
