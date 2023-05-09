<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class HutangBayarHeader extends MyModel
{
    use HasFactory;
    protected $table = 'hutangbayarheader';

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

        $query = DB::table($this->table)->from(DB::raw("hutangbayarheader with (readuncommitted)"))
            ->select(
                'hutangbayarheader.id',
                'hutangbayarheader.nobukti',
                'hutangbayarheader.tglbukti',
                'hutangbayarheader.pengeluaran_nobukti',
                'akunpusat.keterangancoa as coa',
                'hutangbayarheader.userapproval',
                'statusapproval.memo as statusapproval',
                DB::raw('(case when (year(hutangbayarheader.tglapproval) <= 2000) then null else hutangbayarheader.tglapproval end ) as tglapproval'),
                DB::raw('(case when (year(hutangbayarheader.tglbukacetak) <= 2000) then null else hutangbayarheader.tglbukacetak end ) as tglbukacetak'),
                'statuscetak.memo as statuscetak',
                'hutangbayarheader.userbukacetak',
                'hutangbayarheader.jumlahcetak',
                'hutangbayarheader.modifiedby',
                'hutangbayarheader.created_at',
                'hutangbayarheader.updated_at',

                'bank.namabank as bank_id',
                'supplier.namasupplier as supplier_id',
                'alatbayar.keterangan as alatbayar_id',
                'hutangbayarheader.tglcair'

            )
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'hutangbayarheader.coa', 'akunpusat.coa')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'hutangbayarheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'hutangbayarheader.supplier_id', 'supplier.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'hutangbayarheader.alatbayar_id', 'alatbayar.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'hutangbayarheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'hutangbayarheader.statusapproval', 'statusapproval.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function findAll($id)
    {

        $query = DB::table('hutangbayarheader')->from(DB::raw("hutangbayarheader with (readuncommitted)"))
            ->select(
                'hutangbayarheader.id',
                'hutangbayarheader.nobukti',
                'hutangbayarheader.tglbukti',
                'hutangbayarheader.modifiedby',
                'hutangbayarheader.updated_at',
                'hutangbayarheader.bank_id',
                'bank.namabank as bank',
                'hutangbayarheader.statuscetak',
                'hutangbayarheader.supplier_id',
                'supplier.namasupplier as supplier',
                'hutangbayarheader.pengeluaran_nobukti',
                'hutangbayarheader.alatbayar_id',
                'alatbayar.keterangan as alatbayar',
                'hutangbayarheader.tglcair'
            )
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'hutangbayarheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'hutangbayarheader.supplier_id', 'supplier.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'hutangbayarheader.alatbayar_id', 'alatbayar.id')
            ->where('hutangbayarheader.id', $id);


        $data = $query->first();

        return $data;
    }

    public function hutangbayardetail()
    {
        return $this->hasMany(HutangBayarDetail::class, 'hutangbayar_id');
    }

    public function selectColumns($query)
    { //sesuaikan dengan createtemp
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->table.id,
                $this->table.nobukti,
                $this->table.tglbukti,
                $this->table.pengeluaran_nobukti,
                'bank.namabank as bank_id',
                'supplier.namasupplier as supplier_id',
                $this->table.coa,
                'statusapproval.text as statusapproval',
                $this->table.userapproval,
                $this->table.tglapproval,
                'statuscetak.memo as statuscetak',
                $this->table.userbukacetak,
                $this->table.tglbukacetak,
                $this->table.jumlahcetak,
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at"
                )

            )
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'hutangbayarheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'hutangbayarheader.supplier_id', 'supplier.id')
            ->join(DB::raw("parameter as statuscetak with (readuncommitted)"), 'hutangbayarheader.statuscetak', 'statuscetak.id')
            ->join(DB::raw("parameter as statusapproval with (readuncommitted)"), 'hutangbayarheader.statusapproval', 'statusapproval.id');
    }

    public function getPembayaran($id, $supplierId)
    {
        $this->setRequestParameters();

        $tempHutang = $this->createTempHutang($supplierId);
        $tempPembayaran = $this->createTempPembayaran($id, $supplierId);


        // $hutang = DB::table("$tempHutang as A")->from(DB::raw("$tempHutang as A with (readuncommitted)"))
        //     ->select(DB::raw("A.id as id,null as hutangbayar_id,A.nobukti as hutang_nobukti, A.tglbukti as tglbukti, null as bayar, null as keterangan, null as potongan, A.nominalhutang, A.sisa as sisa"))
        //     // ->distinct("A.nobukti")
        //     ->leftJoin(DB::raw("$tempPembayaran as B with (readuncommitted)"), "A.nobukti", "B.hutang_nobukti")
        //     ->whereRaw("isnull(b.hutang_nobukti,'') = ''")
        //     ->whereRaw("a.sisa > 0");


        // $pembayaran = DB::table($tempPembayaran)->from(DB::raw("$tempPembayaran with (readuncommitted)"))
        //     ->select(DB::raw("id,hutangbayar_id,hutang_nobukti,tglbukti,bayar,keterangan,potongan,nominalhutang,sisa"))
        //     ->unionAll($hutang);

        $temp = '##tempGet' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $pembayaran = DB::table($tempPembayaran)->from(DB::raw("$tempPembayaran with (readuncommitted)"))
            ->select(DB::raw("hutangbayar_id,hutang_nobukti,tglbukti,bayar,keterangan,potongan,nominalhutang,sisa"));

        Schema::create($temp, function ($table) {
            $table->bigInteger('hutangbayar_id')->nullable();
            $table->string('hutang_nobukti');
            $table->date('tglbukti')->nullable();
            $table->bigInteger('bayar')->nullable();
            $table->string('keterangan')->nullable();
            $table->bigInteger('potongan')->nullable();
            $table->bigInteger('nominalhutang');
            $table->bigInteger('sisa')->nullable();
        });

        DB::table($temp)->insertUsing(['hutangbayar_id', 'hutang_nobukti', 'tglbukti', 'bayar', 'keterangan',  'potongan', 'nominalhutang', 'sisa'], $pembayaran);

        $hutang = DB::table("$tempHutang as A")->from(DB::raw("$tempHutang as A with (readuncommitted)"))
            ->select(DB::raw("null as hutangbayar_id,A.nobukti as hutang_nobukti, A.tglbukti as tglbukti, null as bayar, null as keterangan, null as potongan, A.nominalhutang, A.sisa as sisa"))
            // ->distinct("A.nobukti")
            ->leftJoin(DB::raw("$tempPembayaran as B with (readuncommitted)"), "A.nobukti", "B.hutang_nobukti")
            ->whereRaw("isnull(b.hutang_nobukti,'') = ''")
            ->whereRaw("a.sisa > 0");
        DB::table($temp)->insertUsing(['hutangbayar_id', 'hutang_nobukti', 'tglbukti', 'bayar', 'keterangan',  'potongan', 'nominalhutang', 'sisa'], $hutang);

        $data = DB::table($temp)->from(DB::raw("$temp with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $temp.hutang_nobukti) as id,hutangbayar_id,hutang_nobukti as nobukti,tglbukti,bayar,keterangan,potongan,nominalhutang as nominal,sisa,
            (case when bayar IS NULL then 0 else (bayar + coalesce(potongan,0)) end) as total"))
            ->get();

        return $data;
    }

    public function createTempHutang($supplierId)
    {
        $temp = '##tempHutang' . rand(1, 10000);


        $fetch = DB::table('hutangheader')->from(DB::raw("hutangheader with (readuncommitted)"))
            ->select(DB::raw("hutangheader.nobukti,hutangheader.tglbukti,hutangheader.supplier_id,hutangheader.total as nominalhutang, (SELECT (hutangheader.total - COALESCE(SUM(hutangbayardetail.nominal),0) - COALESCE(SUM(hutangbayardetail.potongan),0)) FROM hutangbayardetail WHERE hutangbayardetail.hutang_nobukti= hutangheader.nobukti) AS sisa"))
            ->leftJoin(DB::raw("hutangbayardetail with (readuncommitted)"), 'hutangheader.nobukti', 'hutangbayardetail.hutang_nobukti')
            ->whereRaw("hutangheader.supplier_id = $supplierId")
            ->groupBy('hutangheader.id', 'hutangheader.nobukti', 'hutangheader.supplier_id', 'hutangheader.total', 'hutangheader.tglbukti');

        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->date('tglbukti')->nullable();
            $table->bigInteger('supplier_id')->nullable();
            $table->bigInteger('nominalhutang');
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'tglbukti', 'supplier_id', 'nominalhutang', 'sisa'], $fetch);

        return $temp;
    }

    public function createTempPembayaran($id, $supplierId)
    {
        $tempo = '##tempPembayaran' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = DB::table('hutangbayardetail as hbd')->from(DB::raw("hutangbayardetail as hbd with (readuncommitted)"))
            ->select(DB::raw("hbd.hutangbayar_id,hbd.hutang_nobukti,hutangheader.tglbukti,hbd.nominal as bayar, hbd.keterangan,hbd.potongan,hutangheader.total as nominalhutang, (SELECT (hutangheader.total - SUM(hutangbayardetail.nominal) - SUM(hutangbayardetail.potongan)) FROM hutangbayardetail WHERE hutangbayardetail.hutang_nobukti= hutangheader.nobukti) AS sisa"))
            ->join(DB::raw("hutangheader with (readuncommitted)"), 'hbd.hutang_nobukti', 'hutangheader.nobukti')
            ->whereRaw("hbd.hutangbayar_id = $id");

        Schema::create($tempo, function ($table) {
            $table->bigInteger('hutangbayar_id')->nullable();
            $table->string('hutang_nobukti');
            $table->date('tglbukti')->nullable();
            $table->bigInteger('bayar')->nullable();
            $table->string('keterangan')->nullable();
            $table->bigInteger('potongan')->nullable();
            $table->bigInteger('nominalhutang');
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($tempo)->insertUsing(['hutangbayar_id', 'hutang_nobukti', 'tglbukti', 'bayar', 'keterangan',  'potongan', 'nominalhutang', 'sisa'], $fetch);

        return $tempo;
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('pengeluaran_nobukti')->nullable();
            $table->string('bank_id')->nullable();
            $table->string('supplier_id')->nullable();
            $table->string('coa', 50)->nullable();
            $table->string('statusapproval', 1000)->nullable();
            $table->string('userapproval', 50)->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        $models =  $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'pengeluaran_nobukti', 'bank_id', 'supplier_id', 'coa', 'statusapproval', 'userapproval', 'tglapproval', 'statuscetak', 'userbukacetak', 'tglbukacetak', 'jumlahcetak', 'modifiedby', 'created_at', 'updated_at'], $models);


        return  $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'bank_id') {
            return $query->orderBy('bank.namabank', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'alatbayar_id') {
            return $query->orderBy('alatbayar.namaalatbayar', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'supplier_id') {
            return $query->orderBy('supplier.namasupplier', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'coa') {
            return $query->orderBy('akunpusat.keterangancoa', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statusapproval') {
                                $query = $query->where('statusapproval.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'statuscetak') {
                                $query = $query->where('statuscetak.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'supplier_id') {
                                $query = $query->where('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'bank_id') {
                                $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglcair' || $filters['field'] == 'tglbukacetak') {
                                $query = $query->whereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at' || $filters['field'] == 'tglapproval') {
                                $query = $query->whereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'statusapproval') {
                                    $query = $query->orWhere('statusapproval.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'statuscetak') {
                                    $query = $query->orWhere('statuscetak.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'supplier_id') {
                                    $query = $query->orWhere('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'bank_id') {
                                    $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgljatuhtempo' || $filters['field'] == 'tglbukacetak') {
                                    $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at' || $filters['field'] == 'tglapproval') {
                                    $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else {
                                    $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                }
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
        if (request()->cetak && request()->periode) {
            $query->where('hutangbayarheader.statuscetak', '<>', request()->cetak)
                ->whereYear('hutangbayarheader.tglbukti', '=', request()->year)
                ->whereMonth('hutangbayarheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
