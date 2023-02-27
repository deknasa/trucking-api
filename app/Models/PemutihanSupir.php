<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PemutihanSupir extends MyModel
{
    use HasFactory;
    protected $table = 'pemutihansupir';

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

        $query = PemutihanSupir::from(DB::raw("pemutihansupir with (readuncommitted)"))
            ->select(
                'pemutihansupir.id',
                'pemutihansupir.nobukti',
                'pemutihansupir.tglbukti',
                'supir.namasupir as supir',
                'pemutihansupir.pengeluaransupir',
                'pemutihansupir.penerimaansupir',
                'pemutihansupir.modifiedby',
                'pemutihansupir.created_at',
                'pemutihansupir.updated_at'

            )
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pemutihansupir.supir_id', 'supir.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function getDataPemutihan($supirId)
    {
        $kodePJT = PengeluaranTrucking::where('kodepengeluaran', 'PJT')->first();
        $kodePJP = PenerimaanTrucking::where('kodepenerimaan', 'PJP')->first();
        $pjt = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
            ->select(DB::raw("SUM(pengeluarantruckingdetail.nominal) as nominal"))
            ->join(DB::raw("pengeluarantruckingdetail with (readuncommitted)"), "pengeluarantruckingheader.id", "pengeluarantruckingdetail.pengeluarantruckingheader_id")
            ->where("pengeluarantruckingheader.pengeluarantrucking_id", $kodePJT->id)
            ->where("pengeluarantruckingdetail.supir_id", $supirId)
            ->first();
        $pjp = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
            ->select(DB::raw("SUM(penerimaantruckingdetail.nominal) as nominal"))
            ->join(DB::raw("penerimaantruckingdetail with (readuncommitted)"), "penerimaantruckingheader.id", "penerimaantruckingdetail.penerimaantruckingheader_id")
            ->where("penerimaantruckingheader.penerimaantrucking_id", $kodePJP->id)
            ->where("penerimaantruckingdetail.supir_id", $supirId)
            ->first();
        $pengeluaran = $pjt->nominal - $pjp->nominal;

        $kodePDT = PengeluaranTrucking::where('kodepengeluaran', 'PDT')->first();
        $kodeDPO = PenerimaanTrucking::where('kodepenerimaan', 'DPO')->first();
        $pdt = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
            ->select(DB::raw("SUM(pengeluarantruckingdetail.nominal) as nominal"))
            ->join(DB::raw("pengeluarantruckingdetail with (readuncommitted)"), "pengeluarantruckingheader.id", "pengeluarantruckingdetail.pengeluarantruckingheader_id")
            ->where("pengeluarantruckingheader.pengeluarantrucking_id", $kodePDT->id)
            ->where("pengeluarantruckingdetail.supir_id", $supirId)
            ->first();
        $dpo = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
            ->select(DB::raw("SUM(penerimaantruckingdetail.nominal) as nominal"))
            ->join(DB::raw("penerimaantruckingdetail with (readuncommitted)"), "penerimaantruckingheader.id", "penerimaantruckingdetail.penerimaantruckingheader_id")
            ->where("penerimaantruckingheader.penerimaantrucking_id", $kodeDPO->id)
            ->where("penerimaantruckingdetail.supir_id", $supirId)
            ->first();

        $penerimaan = $dpo->nominal - $pdt->nominal;
        return [
            'pengeluaran' => $pengeluaran,
            'penerimaan' => $penerimaan
        ];
    }

    public function getPJT($supirId)
    {
        $temp = '##tempPJT' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $pjt = PengeluaranTrucking::where('kodepengeluaran', 'PJT')->first();

        $fetch = DB::table('pengeluarantruckingheader')->from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
            ->select(DB::raw("(SELECT (SUM(pengeluarantruckingdetail.nominal))
            FROM pengeluarantruckingdetail 
            WHERE pengeluarantruckingdetail.pengeluarantruckingheader_id= pengeluarantruckingheader.id and pengeluarantruckingdetail.supir_id=1) AS nominal"))
            ->join("pengeluarantruckingdetail", "pengeluarantruckingheader.id",  "pengeluarantruckingdetail.pengeluarantruckingheader_id")
            ->whereRaw("pengeluarantruckingheader.pengeluarantrucking_id = $pjt->id")
            ->whereRaw("pengeluarantruckingdetail.supir_id = $supirId");
        Schema::create($temp, function ($table) {
            $table->bigInteger('nominal');
        });

        $tes = DB::table($temp)->insertUsing(['nominal'], $fetch);

        return $temp;
    }
    
    public function selectColumns($query)
    {
        return $query->select(
            DB::raw("
                $this->table.id,
                $this->table.nobukti,
                $this->table.tglbukti,
                'supir.namasupir as supir',
                $this->table.pengeluaransupir,
                $this->table.penerimaansupir,
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at
            ")
        )
        ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pemutihansupir.supir_id', 'supir.id');
    }
    
    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti')->default('');
            $table->string('supir', 1000)->default('');
            $table->float('pengeluaransupir')->default('');
            $table->float('penerimaansupir')->default('');
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'supir', 'pengeluaransupir', 'penerimaansupir', 'modifiedby','created_at', 'updated_at'], $models);

        return $temp;
    }
    
    public function supir()
    {
        return $this->belongsTo(Supir::class);
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
                        if ($filters['field'] == 'supir') {
                            $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'supir') {
                            $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
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

        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
