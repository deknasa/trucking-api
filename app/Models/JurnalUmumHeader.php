<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class JurnalUmumHeader extends MyModel
{
    use HasFactory;

    protected $table = 'jurnalumumheader';
    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    // public function resolveRouteBinding($value, $field = null)
    // {
    //     return $this->where('id', '=', $value)->lockForUpdate()->firstOrFail();
    // }

    public function get()
    {
        $this->setRequestParameters();

        $lennobukti = 3;

        $tempsummary = '##tempsummary' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsummary, function ($table) {
            $table->id();
            $table->string('nobukti', 1000)->nullable();
            $table->double('nominaldebet', 15, 2)->nullable();
            $table->double('nominalkredit', 15, 2)->nullable();
        });

        $querysummary = JurnalUmumHeader::from(
            DB::raw("jurnalumumheader as a with (readuncommitted)")
        )
            ->select(
                'a.nobukti as nobukti',
                DB::raw("sum((case when b.nominal<=0 then 0 else b.nominal end)) as nominaldebet"),
                DB::raw("sum((case when b.nominal>=0 then 0 else abs(b.nominal) end)) as nominalkredit"),
            )
            ->join(DB::raw("jurnalumumdetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->groupBy('a.nobukti');


        DB::table($tempsummary)->insertUsing([
            'nobukti',
            'nominaldebet',
            'nominalkredit',
        ], $querysummary);

        $query = DB::table($this->table)->from(
            DB::raw("jurnalumumheader with (readuncommitted)")
        )
            ->select(
                'jurnalumumheader.id',
                'jurnalumumheader.nobukti',
                'jurnalumumheader.tglbukti',
                'jurnalumumheader.postingdari',
                'jurnalumumheader.userapproval',
                DB::raw('(case when (year(jurnalumumheader.tglapproval) <= 2000) then null else jurnalumumheader.tglapproval end ) as tglapproval'),
                'jurnalumumheader.modifiedby',
                'jurnalumumheader.created_at',
                'jurnalumumheader.updated_at',
                'statusapproval.memo as statusapproval',
                'c.nominaldebet as nominaldebet',
                'c.nominalkredit as nominalkredit',
            )
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'jurnalumumheader.statusapproval', 'statusapproval.id')
            ->leftjoin(DB::raw($tempsummary . " as c"), 'jurnalumumheader.nobukti', 'c.nobukti');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);


        $data = $query->get();


        return $data;
    }
    public function jurnalumumdetail()
    {
        return $this->hasMany(JurnalUmumDetail::class, 'jurnalumum_id');
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
            $this->table.postingdari,
            'statusapproval.text as statusapproval',
            $this->table.userapproval,
            $this->table.tglapproval,
            $this->table.modifiedby,
            $this->table.updated_at"
                )
            )
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'jurnalumumheader.statusapproval', 'statusapproval.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('postingdari', 1000)->nullable();
            $table->string('statusapproval', 1000)->nullable();
            $table->string('userapproval', 1000)->nullable();
            $table->string('tglapproval', 1000)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        $models =  $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'postingdari', 'statusapproval', 'userapproval', 'tglapproval', 'modifiedby', 'updated_at'], $models);


        return  $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'nominaldebet') {
            return $query->orderBy('c.' . $this->params['sortIndex'], $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'nominalkredit') {

            return $query->orderBy('c.' . $this->params['sortIndex'], $this->params['sortOrder']);
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
                            } else if ($filters['field'] == 'nominaldebet') {
                                $query = $query->whereRaw("format(c.nominaldebet, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'nominalkredit') {
                                $query = $query->whereRaw("format(c.nominalkredit, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglapproval') {
                                $query = $query->whereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

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
                                } else if ($filters['field'] == 'nominaldebet') {
                                    $query = $query->orWhereRaw("format(c.nominaldebet, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'nominalkredit') {
                                    $query = $query->orWhereRaw("format(c.nominalkredit, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglapproval') {
                                    $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else {
                                    // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

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

        return $query;
    }
    public function cekvalidasiaksi($nobukti)
    {
        $pengeluaran = DB::table('pengeluaranheader')
            ->from(
                DB::raw("pengeluaranheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($pengeluaran)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pengeluaran',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }
        $penerimaan = DB::table('penerimaanheader')
            ->from(
                DB::raw("penerimaanheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($penerimaan)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }
        $hutang = DB::table('hutangheader')
            ->from(
                DB::raw("hutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($hutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Hutang',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }
        $piutang = DB::table('piutangheader')
            ->from(
                DB::raw("piutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($piutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Piutang',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }
        $penerimaanGiro = DB::table('penerimaangiroheader')
            ->from(
                DB::raw("penerimaangiroheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($penerimaanGiro)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan Giro',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }
        $prosesGajiSupir = DB::table('prosesgajisupirheader')
            ->from(
                DB::raw("prosesgajisupirheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($prosesGajiSupir)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Proses Gaji Supir',
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

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
