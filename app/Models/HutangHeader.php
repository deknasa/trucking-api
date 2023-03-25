<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HutangHeader extends MyModel
{
    use HasFactory;

    protected $table = 'hutangheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function cekvalidasiaksi($nobukti)
    {
        $hutangBayar = DB::table('hutangbayardetail')
            ->from(
                DB::raw("hutangbayardetail as a with (readuncommitted)")
            )
            ->select(
                'a.hutang_nobukti'
            )
            ->where('a.hutang_nobukti', '=', $nobukti)
            ->first();
        if (isset($hutangBayar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pembayaran Hutang',
                'kodeerror' => 'SATL'
            ];
            goto selesai;
        }

        $penerimaanStok = DB::table('penerimaanstokheader')
            ->from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
            ->select(
                'a.hutang_nobukti'
            )
            ->where('a.hutang_nobukti', '=', $nobukti)
            ->first();
        if (isset($penerimaanStok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan Stok',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }


        $data = [
            'kondisi' => false,
            'keterangan' => '',
        ];
        selesai:
        return $data;
    }

    public function get()
    {

        $tempbayar = '##tempbayar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempbayar, function ($table) {
            $table->string('hutang_nobukti', 100)->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $query = DB::table('hutangbayardetail')->from(
            DB::raw("hutangbayardetail as a with (readuncommitted)")
        )
            ->select(
                'a.hutang_nobukti',
                DB::raw("sum(a.nominal+a.potongan) as nominal")
            )
            ->groupby('hutang_nobukti');

        DB::table($tempbayar)->insertUsing([
            'hutang_nobukti',
            'nominal',
        ], $query);

        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("hutangheader with (readuncommitted)"))
            ->select(
                'hutangheader.id',
                'hutangheader.nobukti',
                'hutangheader.tglbukti',

                'akunpusat.keterangancoa as coa',
                'supplier.namasupplier as supplier_id',
                'hutangheader.total',
                DB::raw("isnull(c.nominal,0) as nominalbayar"),
                DB::raw("hutangheader.total-isnull(c.nominal,0) as sisahutang"),

                'parameter.memo as statuscetak',
                'hutangheader.userbukacetak',
                'hutangheader.jumlahcetak',
                DB::raw('(case when (year(hutangheader.tglbukacetak) <= 2000) then null else hutangheader.tglbukacetak end ) as tglbukacetak'),

                'hutangheader.modifiedby',
                'hutangheader.created_at',
                'hutangheader.updated_at'
            )
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'hutangheader.statuscetak', 'parameter.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'hutangheader.coa', 'akunpusat.coa')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'hutangheader.supplier_id', 'supplier.id')
            ->leftJoin(DB::raw($tempbayar . " as c"), 'hutangheader.nobukti', 'c.hutang_nobukti');


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
        $query = DB::table('hutangheader')->from(
            DB::raw("hutangheader with (readuncommitted)")
        )
            ->select(
                'hutangheader.id',
                'hutangheader.nobukti',
                'hutangheader.tglbukti',
                'supplier.namasupplier as supplier',
                'supplier.id as supplier_id',
                'hutangheader.statuscetak',
                'hutangheader.total',

                'hutangheader.modifiedby',
                'hutangheader.updated_at'
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'hutangheader.statuscetak', 'parameter.id')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'hutangheader.supplier_id', 'supplier.id')

            ->where('hutangheader.id', $id);

        $data = $query->first();
        return $data;
    }

    public function getHutang($id, $field)
    {
        $this->setRequestParameters();

        $temp = $this->createTempHutang($id, $field);



        $query = DB::table('hutangheader')->from(DB::raw("hutangheader with (readuncommitted)"))
            ->select(DB::raw("hutangheader.id,hutangheader.nobukti as nobukti,hutangheader.tglbukti, hutangheader.total," . $temp . ".sisa"))
            ->join(DB::raw("$temp with (readuncommitted)"), 'hutangheader.nobukti', $temp . ".nobukti")
            ->whereRaw("hutangheader.nobukti = $temp.nobukti")
            ->where(function ($query) use ($temp) {
                $query->whereRaw("$temp.sisa != 0")
                    ->orWhereRaw("$temp.sisa is null");
            });
        $data = $query->get();

        return $data;
    }

    public function createTempHutang($id, $field)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = DB::table('hutangheader')->from(
            DB::raw("hutangheader with (readuncommitted)")
        )
            ->select(DB::raw("hutangheader.nobukti,sum(hutangbayardetail.nominal) as terbayar, (SELECT (hutangheader.total - coalesce(SUM(hutangbayardetail.nominal),0)) FROM hutangbayardetail WHERE hutangbayardetail.hutang_nobukti= hutangheader.nobukti) AS sisa"))
            ->leftJoin(DB::raw("hutangbayardetail with (readuncommitted)"), 'hutangbayardetail.hutang_nobukti', 'hutangheader.nobukti')
            ->whereRaw("hutangheader.$field = $id")
            ->groupBy('hutangheader.nobukti', 'hutangheader.total');
        // ->get();
        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->bigInteger('terbayar')->nullable();
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'terbayar', 'sisa'], $fetch);

        return $temp;
    }
    public function hutangdetail()
    {
        return $this->hasMany(HutangDetail::class, 'hutang_id');
    }

    public function selectColumns($query)
    {
        $tempbayar = '##tempbayar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempbayar, function ($table) {
            $table->string('hutang_nobukti', 100)->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $tes = DB::table('hutangbayardetail')->from(
            DB::raw("hutangbayardetail as a with (readuncommitted)")
        )
            ->select(
                'a.hutang_nobukti',
                DB::raw("sum(a.nominal+a.potongan) as nominal")
            )
            ->groupby('hutang_nobukti');

        DB::table($tempbayar)->insertUsing([
            'hutang_nobukti',
            'nominal',
        ], $tes);

        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->table.id,
                 $this->table.nobukti,
                 $this->table.tglbukti,
                 $this->table.coa,
                 'supplier.namasupplier as supplier_id',
                 $this->table.total,
                 isnull(c.nominal,0) as nominalbayar,
                hutangheader.total-isnull(c.nominal,0) as sisahutang,
                 'parameter.text as statuscetak',
                 $this->table.userbukacetak,
                 $this->table.tglbukacetak,
                 $this->table.jumlahcetak,
                 $this->table.modifiedby,
                 $this->table.created_at,
                 $this->table.updated_at,
                 $this->table.statusformat"
                )

            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'hutangheader.statuscetak', 'parameter.id')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'hutangheader.supplier_id', 'supplier.id')
            ->leftJoin(DB::raw($tempbayar . " as c"), 'hutangheader.nobukti', 'c.hutang_nobukti');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->string('coa', 50)->nullable();
            $table->string('supplier_id', 50)->nullable();
            $table->double('total', 15, 2)->nullable();
            $table->double('nominalbayar', 15, 2)->nullable();
            $table->double('sisahutang', 15, 2)->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->bigInteger('statusformat')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        $models = $query
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);

        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'coa', 'supplier_id', 'total','nominalbayar','sisahutang', 'statuscetak', 'userbukacetak', 'tglbukacetak', 'jumlahcetak', 'modifiedby', 'created_at', 'updated_at', 'statusformat'], $models);

        return $temp;
    }

    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statuscetak') {
                            $query->where('parameter.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'supplier_id') {
                            $query->where('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'coa') {
                            $query->where('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'total') {
                            $query->Where(DB::raw("hutangheader.total"), 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'tglbukti') {
                            $query->where('hutangheader.tglbukti', '=', date('Y-m-d', strtotime($filters['data'])));
                        } else if ($filters['field'] == 'nominalbayar') {
                            $query->where('c.nominal', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'sisahutang') {
                            $query->where(DB::raw("(hutangheader.total - isnull(c.nominal,0))"), 'LIKE', "%$filters[data]%");
                        } else {
                            $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statuscetak') {
                                $query->orWhere('parameter.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'supplier_id') {
                                $query->orWhere('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coa') {
                                $query->orWhere('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglbukti') {
                                $query->orWhere('hutangheader.tglbukti', '=', date('Y-m-d', strtotime($filters['data'])));
                            } else if ($filters['field'] == 'total') {
                                $query->orWhere(DB::raw("hutangheader.total"), 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominalbayar') {
                                $query->orWhere('c.nominal', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'sisahutang') {
                                $query->orWhere(DB::raw("hutangheader.total - isnull(c.nominal,0)"), 'LIKE', "%$filters[data]%");
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
        if (request()->cetak && request()->periode) {
            $query->where('hutangheader.statuscetak', '<>', request()->cetak)
                ->whereYear('hutangheader.tglbukti', '=', request()->year)
                ->whereMonth('hutangheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
