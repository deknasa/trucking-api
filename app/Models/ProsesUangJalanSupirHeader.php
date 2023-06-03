<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProsesUangJalanSupirHeader extends MyModel
{
    use HasFactory;
    protected $table = 'prosesuangjalansupirheader';

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


        $query = DB::table($this->table)->from(DB::raw("prosesuangjalansupirheader with (readuncommitted)"))
            ->select(
                'prosesuangjalansupirheader.id',
                'prosesuangjalansupirheader.nobukti',
                'prosesuangjalansupirheader.tglbukti',
                'prosesuangjalansupirheader.absensisupir_nobukti',
                'prosesuangjalansupirheader.nominaluangjalan',
                'prosesuangjalansupirheader.userapproval',
                DB::raw('(case when (year(prosesuangjalansupirheader.tglapproval) <= 2000) then null else prosesuangjalansupirheader.tglapproval end ) as tglapproval'),
                'statusapproval.memo as statusapproval',
                'trado.kodetrado as trado_id',
                'supir.namasupir as supir_id',
                'prosesuangjalansupirheader.modifiedby',
                'prosesuangjalansupirheader.created_at',
                'prosesuangjalansupirheader.updated_at'
            )
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'prosesuangjalansupirheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'prosesuangjalansupirheader.trado_id', 'trado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'prosesuangjalansupirheader.supir_id', 'supir.id');

        if (request()->tgldari) {
            $query->whereBetween('tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function getPinjaman($supirId)
    {
        $tempPribadi = $this->createTempPinjaman($supirId);

        $query = PengeluaranTruckingDetail::from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By pengeluarantruckingdetail.nobukti) as id,pengeluarantruckingheader.tglbukti,pengeluarantruckingdetail.nobukti as nobuktipengeluaran,pengeluarantruckingdetail.keterangan as keteranganpinjaman," . $tempPribadi . ".sisa"))
            ->leftJoin(DB::raw("$tempPribadi with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', $tempPribadi . ".nobukti")
            ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', "pengeluarantruckingheader.nobukti")
            ->whereRaw("pengeluarantruckingdetail.supir_id = $supirId")
            ->whereRaw("pengeluarantruckingdetail.nobukti = $tempPribadi.nobukti")
            ->where(function ($query) use ($tempPribadi) {
                $query->whereRaw("$tempPribadi.sisa != 0")
                    ->orWhereRaw("$tempPribadi.sisa is null");
            })
            ->orderBy('pengeluarantruckingheader.tglbukti', 'asc')
            ->orderBy('pengeluarantruckingdetail.nobukti', 'asc');

        return $query->get();
    }

    public function createTempPinjaman($supirId)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("pengeluarantruckingdetail.nobukti, (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"))
            // ->leftJoin(DB::raw("penerimaantruckingdetail with (readuncommitted)"), 'penerimaantruckingdetail.pengeluarantruckingheader_nobukti', 'pengeluarantruckingdetail.nobukti')
            ->whereRaw("pengeluarantruckingdetail.supir_id = $supirId")
            ->where("pengeluarantruckingdetail.nobukti",  'LIKE', "%PJT%")
            ->groupBy('pengeluarantruckingdetail.nobukti', 'pengeluarantruckingdetail.nominal');

        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'sisa'], $fetch);


        return $temp;
    }

    public function getPengembalian($id)
    {
        $status = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'PENGEMBALIAN PINJAMAN')->first();
        $getNobukti = ProsesUangJalanSupirDetail::from(DB::raw("prosesuangjalansupirdetail with (readuncommitted)"))
            ->select('penerimaantruckingheader.nobukti')
            ->join(DB::raw("penerimaantruckingheader with (readuncommitted)"), "prosesuangjalansupirdetail.penerimaantrucking_nobukti", 'penerimaantruckingheader.penerimaan_nobukti')
            ->where('prosesuangjalansupirdetail.prosesuangjalansupir_id', $id)
            ->where('prosesuangjalansupirdetail.statusprosesuangjalan', $status->id)
            ->first();

        $query = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("penerimaantruckingdetail.penerimaantruckingheader_id as id,pengeluarantruckingdetail.nobukti as nobuktipengeluaran,pengeluarantruckingdetail.keterangan as keteranganpinjaman, 
            penerimaantruckingdetail.nominal as nombayar,
            (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) 
            FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"))
            ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'pengeluarantruckingdetail.nobukti')
            ->leftJoin(DB::raw("penerimaantruckingdetail with (readuncommitted)"), 'penerimaantruckingdetail.pengeluarantruckingheader_nobukti', 'pengeluarantruckingdetail.nobukti')
            ->where('penerimaantruckingdetail.nobukti', $getNobukti->nobukti);
        
        return $query->get();
    }

    public function findAll($id)
    {
        $query = ProsesUangJalanSupirHeader::from(DB::raw("prosesuangjalansupirheader with (readuncommitted)"))
            ->select(
                'prosesuangjalansupirheader.id',
                'prosesuangjalansupirheader.nobukti',
                'prosesuangjalansupirheader.tglbukti',
                'prosesuangjalansupirheader.absensisupir_nobukti as absensisupir',
                'prosesuangjalansupirheader.supir_id',
                'supir.namasupir as supir',
                'prosesuangjalansupirheader.trado_id',
                'trado.kodetrado as trado'
            )
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'prosesuangjalansupirheader.supir_id', 'supir.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'prosesuangjalansupirheader.trado_id', 'trado.id')
            ->where('prosesuangjalansupirheader.id', $id);

        return $query->first();
    }
    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
                "$this->table.id,
                 $this->table.nobukti,
                 $this->table.tglbukti,
                 $this->table.absensisupir_nobukti,
                 $this->table.trado_id,
                 $this->table.supir_id,
                 $this->table.nominaluangjalan,
                 $this->table.statusapproval,
                 $this->table.modifiedby,
                 $this->table.updated_at"
            )
        );
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('absensisupir_nobukti', 1000)->nullable();
            $table->bigInteger('trado_id')->nullable();
            $table->bigInteger('supir_id')->nullable();
            $table->float('nominaluangjalan')->nullable();
            $table->bigInteger('statusapproval')->nullable();
            $table->string('modifiedby')->default();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);

        $this->sort($query);
        $models = $this->filter($query);
        $models =  $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'absensisupir_nobukti', 'trado_id', 'supir_id', 'nominaluangjalan', 'statusapproval', 'modifiedby', 'updated_at'], $models);

        return $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'trado_id') {
            return $query->orderBy('trado.kodetrado', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'supir_id') {
            return $query->orderBy('supir.namasupir', $this->params['sortOrder']);
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
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('statusapproval.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'trado_id') {
                            $query = $query->where('trado.kodetrado', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'supir_id') {
                            $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'nominaluangjalan') {
                            $query = $query->whereRaw("format($this->table.nominaluangjalan, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglapproval') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusapproval') {
                                $query = $query->orWhere('statusapproval.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'trado_id') {
                                $query = $query->orWhere('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'supir_id') {
                                $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominaluangjalan') {
                                $query = $query->orWhereRaw("format($this->table.nominaluangjalan, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglapproval') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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

    public function getNominalAbsensi($nobukti){
        $query = DB::table("absensisupirheader")->from(DB::raw("absensisupirheader with (readuncommitted)"))
        ->where('nobukti', $nobukti)
        ->first();
        return $query;
    }

    public function getSisaPinjamanForValidation($nobukti) {
        $fetch = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("pengeluarantruckingdetail.nobukti, (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"))
           
            ->where("pengeluarantruckingdetail.nobukti", $nobukti)
            ->groupBy('pengeluarantruckingdetail.nobukti', 'pengeluarantruckingdetail.nominal');

        return $fetch->first();
    }
}
