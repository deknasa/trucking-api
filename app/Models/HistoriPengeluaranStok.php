<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Models\PengeluaranStokDetailFifo;
use App\Models\PengeluaranStokDetail;
use App\Models\PengeluaranStok;

class HistoriPengeluaranStok extends MyModel
{
    use HasFactory;
    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function default()
    {

        $tempStokDari = '##tempStokDari' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($tempStokDari, function ($table) {
            $table->unsignedBigInteger('stokdari_id')->nullable();
            $table->string('stokdari', 255)->nullable();
            $table->unsignedBigInteger('stoksampai_id')->nullable();
            $table->string('stoksampai', 255)->nullable();
            $table->unsignedBigInteger('filter')->nullable();
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

        $stokSampai = Stok::from(
            DB::raw('stok with (readuncommitted)')
        )
            ->select(
                'id as stoksampai_id',
                'namastok as stoksampai',

            )
            ->orderBy('id', 'desc')
            ->limit(1)
            ->first();

        $pengeluaranStok = PengeluaranStok::from(
            DB::raw('pengeluaranstok with (readuncommitted)')
        )
            ->select(
                'id',

            )
            ->orderBy('id', 'asc')
            ->first();

        DB::table($tempStokDari)->insert(
            [
                "stokdari_id" => $stokDari->stokdari_id,
                "stokdari" => $stokDari->stokdari,
                "stoksampai_id" => $stokSampai->stoksampai_id,
                "stoksampai" => $stokSampai->stoksampai,
                "filter" => $pengeluaranStok->id
            ]
        );
        $query = DB::table($tempStokDari)->from(
            DB::raw($tempStokDari)
        )
            ->select(
                'stokdari_id',
                'stokdari',
                'stoksampai_id',
                'stoksampai',
                'filter'
            );

        $data = $query->first();
        return $data;
    }

    public function get()
    {
        $this->setRequestParameters();

        $tgldari = date('Y-m-d', strtotime(request()->dari));
        $tglsampai = date('Y-m-d', strtotime(request()->sampai));

        $query = $this->getlaporan($tgldari, $tglsampai, request()->stokdari_id, request()->stoksampai_id, request()->filter);


        if (request()->filter && request()->stokdari_id && request()->stoksampai_id) {

            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
            $this->filter($query);
            if (!request()->action) {
                $this->paginate($query);
            }

            $data = $query->get();
        } else {
            $data = [];
        }

        return $data;
    }

    private function getlaporan($tgldari, $tglsampai, $stokdari, $stoksampai, $filter)
    {

        $templaporan = '##templaporan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($templaporan, function ($table) {
            $table->id();
            $table->unsignedBigInteger('kodebarang')->nullable();
            $table->string('namabarang', 1000)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->unsignedBigInteger('kategori_id')->nullable();
            $table->double('qtykeluar', 15, 2)->nullable();
            $table->double('nilaikeluar', 15, 2)->nullable();
            $table->string('modifiedby', 100)->nullable();
        });

        $temprekap = '##temprekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekap, function ($table) {
            $table->id();
            $table->integer('statuskeluar')->length(11)->nullable();;
            $table->unsignedBigInteger('kodebarang')->nullable();
            $table->string('namabarang', 1000)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->unsignedBigInteger('kategori_id')->nullable();
            $table->double('qtykeluar', 15, 2)->nullable();
            $table->double('nilaikeluar', 15, 2)->nullable();
            $table->string('modifiedby', 100)->nullable();
        });
        $tempsaldoawalkeluar = '##tempsaldoawalkeluar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldoawalkeluar, function ($table) {
            $table->id();
            $table->unsignedBigInteger('kodebarang')->nullable();
            $table->double('qtykeluar', 15, 2)->nullable();
            $table->double('nilaikeluar', 15, 2)->nullable();
        });

        $querysaldokeluar = PengeluaranStokHeader::from(
            DB::raw("pengeluaranstokheader as a with (readuncommitted)")
        )
            ->select(
                'c.id as kodebarang',
                DB::raw("sum(b.qty) as qtykeluar"),
                DB::raw("sum(b.harga) as nilaikeluar"),
            )
            ->join(DB::raw("pengeluaranstokdetail as b with (readuncommitted)"), 'a.id', 'b.pengeluaranstokheader_id')
            ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
            ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti <'" . $tgldari . "')")
            ->whereRaw("a.pengeluaranstok_id in(" . $filter . ",2)")
            ->groupBy('c.id');

        DB::table($tempsaldoawalkeluar)->insertUsing([
            'kodebarang',
            'qtykeluar',
            'nilaikeluar',
        ], $querysaldokeluar);

        $queryrekap = PengeluaranStokHeader::from(
            DB::raw("pengeluaranstokheader as a with (readuncommitted)")
        )
            ->select(
                DB::raw("1 as statuskeluar"),
                'c.id as kodebarang',
                'c.namastok as namabarang',
                'a.tglbukti as tglbukti',
                'a.nobukti as nobukti',
                'c.kategori_id',
                'b.qty as qtykeluar',
                DB::raw("(b.harga) as nilaikeluar"),
                'a.modifiedby'
            )
            ->join(DB::raw("pengeluaranstokdetail as b with (readuncommitted)"), 'a.id', 'b.pengeluaranstokheader_id')
            ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
            ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
            ->whereRaw("a.pengeluaranstok_id in(" . $filter . ")")
            ->orderBy('a.tglbukti', 'Asc')
            ->orderBy('a.nobukti', 'Asc')
            ->orderBy('b.id', 'Asc');


        DB::table($temprekap)->insertUsing([
            'statuskeluar',
            'kodebarang',
            'namabarang',
            'tglbukti',
            'nobukti',
            'kategori_id',
            'qtykeluar',
            'nilaikeluar',
            'modifiedby',
        ], $queryrekap);


        $querylaporan = DB::table($temprekap)->from(
            DB::raw($temprekap . " as A")
        )
            ->select(
                'A.kodebarang',
                'A.namabarang',
                'A.tglbukti',
                'A.nobukti',
                'A.kategori_id',
                'A.qtykeluar',
                'A.nilaikeluar',
                'A.modifiedby',
            )
            ->orderBy('A.statuskeluar', 'Asc')
            ->orderBy('A.id', 'Asc');

        DB::table($templaporan)->insertUsing([
            'kodebarang',
            'namabarang',
            'tglbukti',
            'nobukti',
            'kategori_id',
            'qtykeluar',
            'nilaikeluar',
            'modifiedby',
        ], $querylaporan);

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $datalist = DB::table($templaporan)->from(
            DB::raw($templaporan . " as a")
        )
            ->select(
                'a.kodebarang',
                'a.namabarang',
                'a.tglbukti',
                'a.nobukti',
                'B.kodekategori as kategori_id',
                'a.qtykeluar',
                'a.nilaikeluar',
                DB::raw(" (a.qtykeluar * a.nilaikeluar) as total"),
                'a.modifiedby',
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Laporan Histori Pengeluaran Stok' as judulLaporan"),
            )
            ->leftjoin(DB::raw("kategori as B with (readuncommitted)"), 'a.kategori_id', 'B.id')
            ->orderBy('a.id', 'asc');
        // dd($datalist->get());
        return $datalist;
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
                        } else if ($filters['field'] == 'nilaikeluar') {
                            $query = $query->whereRaw("format(a.nilaikeluar, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'qtykeluar') {
                            $query = $query->whereRaw("format(a.qtykeluar, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'total') {
                            $query = $query->whereRaw("format((a.qtykeluar * a.nilaikeluar), '#,#0.00') LIKE '%$filters[data]%'");
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
                            } else if ($filters['field'] == 'nilaikeluar') {
                                $query = $query->orWhereRaw("format(a.nilaikeluar, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'qtykeluar') {
                                $query = $query->orWhereRaw("format(a.qtykeluar, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'total') {
                                $query = $query->orWhereRaw("format((a.qtykeluar * a.nilaikeluar), '#,#0.00') LIKE '%$filters[data]%'");
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
