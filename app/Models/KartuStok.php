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

        $filter = Parameter::where('grp', 'STOK PERSEDIAAN')->where('subgrp', 'STOK PERSEDIAAN')->where('text', 'GUDANG')->first();

        if (request()->filter == $filter->id) {
            $query = $this->getlaporan($tgldari, $tglsampai, request()->stokdari_id, request()->stoksampai_id, request()->datafilter, 0, 0, $filter->text);
        }


        if (request()->filter && request()->datafilter && request()->stokdari_id && request()->stoksampai_id) {

            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
            $this->filter($query);
            $this->paginate($query);

            $data = $query->get();
        } else {
            $data = [];
        }

        return $data;
    }

    private function getlaporan($tgldari, $tglsampai, $stokdari, $stoksampai, $gudang_id, $trado_id, $gandengan_id, $filter)
    {

        $templaporan = '##templaporan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($templaporan, function ($table) {
            $table->id();
            $table->unsignedBigInteger('kodebarang')->default(0);
            $table->string('namabarang', 1000)->default('');
            $table->dateTime('tglbukti')->default('1900/1/1');
            $table->string('nobukti', 100)->default('');
            $table->unsignedBigInteger('kategori_id')->default(0);
            $table->double('qtymasuk', 15, 2)->default(0);
            $table->double('nilaimasuk', 15, 2)->default(0);
            $table->double('qtykeluar', 15, 2)->default(0);
            $table->double('nilaikeluar', 15, 2)->default(0);
            $table->double('qtysaldo', 15, 2)->default(0);
            $table->double('nilaisaldo', 15, 2)->default(0);
            $table->string('modifiedby', 100)->default('');
        });

        $temprekap = '##temprekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekap, function ($table) {
            $table->id();
            $table->integer('statusmasuk')->length(11)->default(0);;
            $table->unsignedBigInteger('kodebarang')->default(0);
            $table->string('namabarang', 1000)->default('');
            $table->dateTime('tglbukti')->default('1900/1/1');
            $table->string('nobukti', 100)->default('');
            $table->unsignedBigInteger('kategori_id')->default(0);
            $table->double('qtymasuk', 15, 2)->default(0);
            $table->double('nilaimasuk', 15, 2)->default(0);
            $table->double('qtykeluar', 15, 2)->default(0);
            $table->double('nilaikeluar', 15, 2)->default(0);
            $table->double('qtysaldo', 15, 2)->default(0);
            $table->double('nilaisaldo', 15, 2)->default(0);
            $table->string('modifiedby', 100)->default('');
        });


        $tempsaldoawalmasuk = '##tempsaldoawalmasuk' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldoawalmasuk, function ($table) {
            $table->id();
            $table->unsignedBigInteger('kodebarang')->default(0);
            $table->double('qtymasuk', 15, 2)->default(0);
            $table->double('nilaimasuk', 15, 2)->default(0);
        });

        $tempsaldoawalkeluar = '##tempsaldoawalkeluar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldoawalkeluar, function ($table) {
            $table->id();
            $table->unsignedBigInteger('kodebarang')->default(0);
            $table->double('qtykeluar', 15, 2)->default(0);
            $table->double('nilaikeluar', 15, 2)->default(0);
        });

        $tempsaldoawal = '##tempsaldoawal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldoawal, function ($table) {
            $table->id();
            $table->unsignedBigInteger('kodebarang')->default(0);
            $table->double('qtysaldo', 15, 2)->default(0);
            $table->double('nilaisaldo', 15, 2)->default(0);
        });

        $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();

        if ($filter == 'GUDANG' and $gudang_id = $gudangkantor->text) {
            $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();

            $querysaldomasuk = PenerimaanstokHeader::from(
                "penerimaanstokheader as a"
            )
                ->select(
                    'c.id as kodebarang',
                    DB::raw("sum(b.qty) as qtymasuk"),
                    DB::raw("sum(b.qty*b.harga) as nilaimasuk"),
                )
                ->join('penerimaanstokdetail as b', 'a.id', 'b.penerimaanstokheader_id')
                ->join('stok as c', 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti <'" . $tgldari . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $spb->text .",7)")
                ->groupBy('c.id');

            DB::table($tempsaldoawalmasuk)->insertUsing([
                'kodebarang',
                'qtymasuk',
                'nilaimasuk',
            ], $querysaldomasuk);


         

            $queryrekap = PenerimaanStokHeader::from(
                "penerimaanstokheader as a"
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
                ->join('penerimaanstokdetail as b', 'a.id', 'b.penerimaanstokheader_id')
                ->join('stok as c', 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $spb->text .")")
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

            $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();

            $querysaldokeluar = PengeluaranstokHeader::from(
                "pengeluaranstokheader as a"
            )
                ->select(
                    'c.id as kodebarang',
                    DB::raw("sum(b.penerimaanstok_qty) as qtykeluar"),
                    DB::raw("sum(b.penerimaanstok_qty*b.penerimaanstok_harga) as nilaikeluar"),
                )
                ->join('pengeluaranstokdetailfifo as b', 'a.id', 'b.pengeluaranstokheader_id')
                ->join('stok as c', 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti <'" . $tgldari . "')")
                ->whereRaw("a.pengeluaranstok_id=" . $spk->text)
                ->groupBy('c.id');

            DB::table($tempsaldoawalkeluar)->insertUsing([
                'kodebarang',
                'qtykeluar',
                'nilaikeluar',
            ], $querysaldokeluar);

            $querysaldo=Stok::from(
                "stok as a"
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
                        DB::raw("'Saldo Awal '+ltrim(rtrim(c.namastok)) as namabarang"),
                        DB::raw("'".$tgldari."' as tglbukti"),
                        DB::raw("'' as nobukti"),
                        'c.kategori_id',
                        DB::raw("0 as qtymasuk"),
                        DB::raw("0 as nilaimasuk"),
                        DB::raw("0 as qtykeluar"),
                        DB::raw("0 as nilaikeluar"),
                        DB::raw("a.qtysaldo as qtysaldo"),
                        DB::raw("a.nilaisaldo as nilaisaldo"),
                        DB::raw("'' as modifiedby"),
                    )
                    ->join('stok as c', 'a.kodebarang', 'c.id');
    
    
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
                "pengeluaranstokheader as a"
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
                ->join('pengeluaranstokdetailfifo as b', 'a.id', 'b.pengeluaranstokheader_id')
                ->join('stok as c', 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("a.pengeluaranstok_id=" . $spk->text)
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

        $datalist = DB::table($templaporan);
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
                            $query = $query->where('stok.namastok', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'kodebarang') {
                            $query = $query->where('stok.namaterpusat', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'kategori_id') {
                            $query = $query->where('kategori.keterangan', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {

                            if ($filters['field'] == 'namabarang') {
                                $query = $query->orWhere('stok.namastok', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'kodebarang') {
                                $query = $query->orWhere('stok.namaterpusat', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'kategori') {
                                $query = $query->orWhere('kategori.keterangan', 'LIKE', "%$filters[data]%");
                            } else {
                                $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
