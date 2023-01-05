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
            if (!request()->action){
                $this->paginate($query);

            }

            $data = $query->get();
        } else {
            $data = [];
        }

        return $data;
    }

    private function getlaporan($tgldari, $tglsampai, $stokdari, $stoksampai, $filter )
    {

        $templaporan = '##templaporan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($templaporan, function ($table) {
            $table->id();
            $table->unsignedBigInteger('kodebarang')->default(0);
            $table->string('namabarang', 1000)->default('');
            $table->dateTime('tglbukti')->default('1900/1/1');
            $table->string('nobukti', 100)->default('');
            $table->unsignedBigInteger('kategori_id')->default(0);
            $table->double('qtykeluar', 15, 2)->default(0);
            $table->double('nilaikeluar', 15, 2)->default(0);
            $table->string('modifiedby', 100)->default('');
        });

        $temprekap = '##temprekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekap, function ($table) {
            $table->id();
            $table->integer('statuskeluar')->length(11)->default(0);;
            $table->unsignedBigInteger('kodebarang')->default(0);
            $table->string('namabarang', 1000)->default('');
            $table->dateTime('tglbukti')->default('1900/1/1');
            $table->string('nobukti', 100)->default('');
            $table->unsignedBigInteger('kategori_id')->default(0);
            $table->double('qtykeluar', 15, 2)->default(0);
            $table->double('nilaikeluar', 15, 2)->default(0);
            $table->string('modifiedby', 100)->default('');
        });
        $tempsaldoawalkeluar = '##tempsaldoawalkeluar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldoawalkeluar, function ($table) {
            $table->id();
            $table->unsignedBigInteger('kodebarang')->default(0);
            $table->double('qtykeluar', 15, 2)->default(0);
            $table->double('nilaikeluar', 15, 2)->default(0);
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
            )
            ->leftjoin(DB::raw("kategori as B with (readuncommitted)"),'a.kategori_id','B.id')
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
