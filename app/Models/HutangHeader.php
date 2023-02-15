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
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("hutangheader with (readuncommitted)"))
            ->select(
                'hutangheader.id',
                'hutangheader.nobukti',
                'hutangheader.tglbukti',

                'akunpusat.keterangancoa as coa',
                'supplier.namasupplier as supplier_id',
                'hutangheader.total',
                'parameter.memo as statuscetak',
                'hutangheader.userbukacetak',
                'hutangheader.jumlahcetak',
                DB::raw('(case when (year(hutangheader.tglbukacetak) <= 2000) then null else hutangheader.tglbukacetak end ) as tglbukacetak'),

                'hutangheader.modifiedby',
                'hutangheader.created_at',
                'hutangheader.updated_at'
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'hutangheader.statuscetak', 'parameter.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'hutangheader.coa', 'akunpusat.coa')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'hutangheader.supplier_id', 'supplier.id');

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
            ->select(DB::raw("hutangheader.id as id,hutangheader.nobukti as nobukti,hutangheader.tglbukti, hutangheader.total," . $temp . ".sisa"))
            ->join(DB::raw("$temp with (readuncommitted)"), 'hutangheader.id', $temp . ".id")
            ->whereRaw("hutangheader.nobukti = $temp.nobukti")
            ->where(function ($query) use ($temp) {
                $query->whereRaw("$temp.sisa != 0")
                    ->orWhereRaw("$temp.sisa is null");
            });

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function createTempHutang($id, $field)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = DB::table('hutangheader')->from(
            DB::raw("hutangheader with (readuncommitted)")
        )
            ->select(DB::raw("hutangheader.id,hutangheader.nobukti,sum(hutangbayardetail.nominal) as terbayar, (SELECT (hutangheader.total - coalesce(SUM(hutangbayardetail.nominal),0)) FROM hutangbayardetail WHERE hutangbayardetail.hutang_nobukti= hutangheader.nobukti) AS sisa"))
            ->leftJoin(DB::raw("hutangbayardetail with (readuncommitted)"), 'hutangbayardetail.hutang_nobukti', 'hutangheader.nobukti')
            ->whereRaw("hutangheader.$field = $id")
            ->groupBy('hutangheader.id', 'hutangheader.nobukti', 'hutangheader.total');
        // ->get();
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti');
            $table->bigInteger('terbayar')->nullable();
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['id', 'nobukti', 'terbayar', 'sisa'], $fetch);

        // $data = DB::table($temp)->get();
        return $temp;
    }
    public function hutangdetail()
    {
        return $this->hasMany(HutangDetail::class, 'hutang_id');
    }

    public function selectColumns($query)
    {
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
                 'parameter.text as statuscetak',
                 $this->table.userbukacetak,
                 $this->table.tglbukacetak,
                 $this->table.jumlahcetak,
                 $this->table.modifiedby,
                 $this->table.created_at,
                 $this->table.updated_at,
                 $this->table.statusformat"
                )

            )->leftJoin(DB::raw("parameter with (readuncommitted)"), 'hutangheader.statuscetak', 'parameter.id')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'hutangheader.supplier_id', 'supplier.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->string('coa', 50)->default('');
            $table->string('supplier_id', 50)->default('');
            $table->double('total', 15, 2)->default(0);
            $table->string('statuscetak', 1000)->default('');
            $table->string('userbukacetak', 50)->default('');
            $table->date('tglbukacetak')->default('1900/1/1');
            $table->integer('jumlahcetak')->Length(11)->default('0');
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->bigInteger('statusformat')->default('');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'coa', 'supplier_id', 'total', 'statuscetak', 'userbukacetak', 'tglbukacetak', 'jumlahcetak', 'modifiedby', 'created_at', 'updated_at', 'statusformat'], $models);

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
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'supplier_id') {
                            $query = $query->where('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'coa') {
                            $query = $query->where('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statuscetak') {
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'supplier_id') {
                            $query = $query->where('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'coa') {
                            $query = $query->where('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

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
