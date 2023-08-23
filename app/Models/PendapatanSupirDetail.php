<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PendapatanSupirDetail extends MyModel
{
    use HasFactory;

    protected $table = 'pendapatansupirdetail';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function findUpdate($id)
    {
        $query = DB::table('pendapatansupirdetail')->from(DB::raw("pendapatansupirdetail with (readuncommitted)"))
            ->select(
                'pendapatansupirdetail.supir_id',
                'supir.namasupir as supir',
                'pendapatansupirdetail.nominal',
                'pendapatansupirdetail.keterangan'
            )
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pendapatansupirdetail.supir_id', 'supir.id')
            ->where('pendapatansupirdetail.pendapatansupir_id', $id)
            ->get();

        return $query;
    }
    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));
        $tempsaldopendapatan = '##tempsaldopendapatan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldopendapatan, function ($table) {
            $table->string('nobukti', 1000)->nullable();
            $table->string('nobuktitrip', 1000)->nullable();
            $table->string('tgltrip', 1000)->nullable();
            $table->string('nobuktirincian', 1000)->nullable();
            $table->integer('dari_id')->nullable();
            $table->integer('sampai_id')->nullable();
            $table->double('nominal')->nullable();
            $table->double('gajikenek')->nullable();
        });
        $querysaldopendapatan = $query->select(
            $this->table . '.nobukti',
            $this->table . '.nobuktitrip',
            DB::raw("(case when ISNULL(suratpengantar.tglbukti, '') = '' then ISNULL(saldopendapatansupir.suratpengantar_tglbukti, '') else ISNULL(suratpengantar.tglbukti, '')  end) as tgltrip"),
            $this->table . '.nobuktirincian',
            DB::raw("(case when ISNULL(suratpengantar.dari_id, '') = '' then ISNULL(saldopendapatansupir.dari_id, '') else ISNULL(suratpengantar.dari_id, '')  end) as dari_id"),
            DB::raw("(case when ISNULL(suratpengantar.sampai_id, '') = '' then ISNULL(saldopendapatansupir.sampai_id, '') else ISNULL(suratpengantar.sampai_id, '')  end) as sampai_id"),
            $this->table . '.nominal',            
            DB::raw("(case when pendapatansupirdetail.gajikenek IS NULL then 0 else pendapatansupirdetail.gajikenek end) as gajikenek"),
        )
            ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), $this->table . '.nobuktitrip', 'suratpengantar.nobukti')
            ->leftJoin(DB::raw("saldopendapatansupir with (readuncommitted)"), $this->table . '.nobuktitrip', 'saldopendapatansupir.suratpengantar_nobukti')
            ->where($this->table . '.pendapatansupir_id', '=', request()->pendapatansupir_id);

        DB::table($tempsaldopendapatan)->insertUsing([
            'nobukti',
            'nobuktitrip',
            'tgltrip',
            'nobuktirincian',
            'dari_id',
            'sampai_id',
            'nominal',
            'gajikenek'

        ], $querysaldopendapatan);
        if (isset(request()->forReport) && request()->forReport) {
            $query = DB::table($tempsaldopendapatan)->from(
                db::raw($tempsaldopendapatan . " a ")
            )->select(
                'header.nobukti',
                'header.tglbukti',
                'bank.namabank as bank',
                'header.tgldari',
                'header.tglsampai',
                'header.periode',
                'supir.namasupir as supir_id',
                'a.nobuktitrip',
                'a.tgltrip',
                'a.nobuktirincian',
                DB::raw("isnull(b.kodekota,'') as dari"),
                DB::raw("isnull(c.kodekota,'') as sampai"),
                'a.nominal',
                'a.gajikenek'
            )
                ->leftJoin(DB::raw("pendapatansupirheader as header with (readuncommitted)"), 'header.nobukti', 'a.nobukti')
                ->leftJoin(DB::raw("bank with (readuncommitted)"), 'header.bank_id', 'bank.id')
                ->leftJoin(DB::raw("supir with (readuncommitted)"), 'header.supir_id', 'supir.id')
                ->leftJoin(DB::raw("kota b with (readuncommitted)"), 'a.dari_id', 'b.id')
                ->leftJoin(DB::raw("kota c with (readuncommitted)"), 'a.sampai_id', 'c.id');

        } else {

            $query = DB::table($tempsaldopendapatan)->from(
                db::raw($tempsaldopendapatan . " a ")
            )
                ->select(
                    'a.nobukti',
                    'a.nobuktitrip',
                    'a.tgltrip',
                    'a.nobuktirincian',
                    DB::raw("isnull(b.kodekota,'') as dari"),
                    DB::raw("isnull(c.kodekota,'') as sampai"),
                    'a.nominal',
                    'a.gajikenek'
                )
                ->leftJoin(DB::raw("kota b with (readuncommitted)"), 'a.dari_id', 'b.id')
                ->leftJoin(DB::raw("kota c with (readuncommitted)"), 'a.sampai_id', 'c.id');

            $this->sort($query);
            $this->filter($query);
            $this->totalNominal = $query->sum('nominal');
            $this->totalGajiKenek = $query->sum('gajikenek');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->paginate($query);
        }

        return $query->get();
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'dari') {
            return $query->orderBy(DB::raw("isnull(b.kodekota,'')"), $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'sampai') {
            return $query->orderBy(DB::raw("isnull(c.kodekota,'')"), $this->params['sortOrder']);
        } else {
            return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'dari') {
                                $query = $query->where(DB::raw("isnull(b.kodekota,'')"), 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'sampai') {
                                $query = $query->where(DB::raw("isnull(c.kodekota,'')"), 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->whereRaw("format(a.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'gajikenek') {
                                $query = $query->whereRaw("format(a.gajikenek, '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'dari') {
                                $query = $query->orWhere(DB::raw("isnull(b.kodekota,'')"), 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'sampai') {
                                $query = $query->orWhere(DB::raw("isnull(c.kodekota,'')"), 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->orWhereRaw("format(a.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'gajikenek') {
                                $query = $query->orWhereRaw("format(a.gajikenek, '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
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

    public function processStore(PendapatanSupirHeader $pendapatanSupirHeader, array $data): PendapatanSupirDetail
    {
        $pendapatanSupirDetail = new PendapatanSupirDetail();
        $pendapatanSupirDetail->pendapatansupir_id = $pendapatanSupirHeader->id;
        $pendapatanSupirDetail->nobukti = $pendapatanSupirHeader->nobukti;
        $pendapatanSupirDetail->supir_id = $data['supir_id'];
        $pendapatanSupirDetail->nominal = $data['nominal'];
        $pendapatanSupirDetail->gajikenek = $data['gajikenek'];
        $pendapatanSupirDetail->nobuktirincian = $data['nobuktirincian'];
        $pendapatanSupirDetail->nobuktitrip = $data['nobuktitrip'];
        $pendapatanSupirDetail->modifiedby = $data['modifiedby'];

        if (!$pendapatanSupirDetail->save()) {
            throw new \Exception("Error storing pendapatan Supir detail.");
        }

        return $pendapatanSupirDetail;
    }
}
