<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PenerimaanTrucking extends MyModel
{
    use HasFactory;

    protected $table = 'penerimaantrucking';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];   

    public function cekvalidasihapus($id)
    {     

        $penerimaanTrucking = DB::table('penerimaantruckingheader')
            ->from(
                DB::raw("penerimaantruckingheader as a with (readuncommitted)")
            )
            ->select(
                'a.penerimaantrucking_id'
            )
            ->where('a.penerimaantrucking_id', '=', $id)
            ->first();
        if (isset($penerimaanTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan Trucking',
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

        $query = PenerimaanTrucking::from(DB::raw("$this->table with (readuncommitted)"))
        ->select(
            'penerimaantrucking.id',
            'penerimaantrucking.kodepenerimaan',
            'penerimaantrucking.keterangan',
            'penerimaantrucking.coadebet',
            'penerimaantrucking.coakredit',
            'penerimaantrucking.coapostingdebet',
            'penerimaantrucking.coapostingkredit',
            'debet.keterangancoa as coadebet_keterangan',
            'kredit.keterangancoa as coakredit_keterangan',
            'postingdebet.keterangancoa as coapostingdebet_keterangan',
            'postingkredit.keterangancoa as coapostingkredit_keterangan',
            'parameter.memo as format',
            'penerimaantrucking.created_at',
            'penerimaantrucking.modifiedby',
            'penerimaantrucking.updated_at'
        )
            ->leftJoin(DB::raw("akunpusat as debet  with (readuncommitted)"), "penerimaantrucking.coadebet", "debet.coa")
            ->leftJoin(DB::raw("akunpusat as kredit  with (readuncommitted)"), "penerimaantrucking.coakredit", "kredit.coa")
            ->leftJoin(DB::raw("akunpusat as postingdebet  with (readuncommitted)"), "penerimaantrucking.coapostingdebet", "postingdebet.coa")
            ->leftJoin(DB::raw("akunpusat as postingkredit  with (readuncommitted)"), "penerimaantrucking.coapostingkredit", "postingkredit.coa")
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'penerimaantrucking.format', 'parameter.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
                "$this->table.id,
                 $this->table.kodepenerimaan,
                 $this->table.keterangan,
                 $this->table.coadebet,
                 $this->table.coakredit,
                 $this->table.coapostingdebet,
                 $this->table.coapostingkredit,
                 parameter.text as format,
                 $this->table.modifiedby,
                 $this->table.created_at,
                 $this->table.updated_at"
            )
        )->join('parameter','penerimaantrucking.format','parameter.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('kodepenerimaan', 1000)->default('');
            $table->string('keterangan', 1000)->default('');
            $table->string('coadebet', 1000)->default('');
            $table->string('coakredit', 1000)->default('');
            $table->string('coapostingdebet', 1000)->default('');
            $table->string('coapostingkredit', 1000)->default('');
            $table->string('format', 1000)->default('');
            $table->string('modifiedby', 1000)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'kodepenerimaan', 'keterangan','coadebet','coakredit','coapostingdebet','coapostingkredit', 'format', 'modifiedby','created_at', 'updated_at'], $models);

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
                        if ($filters['field'] == 'format') {
                            $query = $query->where('parameter.text', 'LIKE', "%$filters[data]%");
                        }else if ($filters['field'] == 'coadebet_keterangan') {
                            $query = $query->where('debet.keterangancoa', 'LIKE', "%$filters[data]%");
                        }else if ($filters['field'] == 'coakredit_keterangan') {
                            $query = $query->where('kredit.keterangancoa', 'LIKE', "%$filters[data]%");
                        }else if ($filters['field'] == 'coapostingdebet_keterangan') {
                            $query = $query->where('postingdebet.keterangancoa', 'LIKE', "%$filters[data]%");
                        }else if ($filters['field'] == 'coapostingkredit_keterangan') {
                            $query = $query->where('postingkredit.keterangancoa', 'LIKE', "%$filters[data]%");
                        }else{
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'format') {
                            $query = $query->orWhere('parameter.text', 'LIKE', "%$filters[data]%");
                        }else if ($filters['field'] == 'coadebet_keterangan') {
                            $query = $query->orWhere('debet.keterangancoa', 'LIKE', "%$filters[data]%");
                        }else if ($filters['field'] == 'coakredit_keterangan') {
                            $query = $query->orWhere('kredit.keterangancoa', 'LIKE', "%$filters[data]%");
                        }else if ($filters['field'] == 'coapostingdebet_keterangan') {
                            $query = $query->orWhere('postingdebet.keterangancoa', 'LIKE', "%$filters[data]%");
                        }else if ($filters['field'] == 'coapostingkredit_keterangan') {
                            $query = $query->orWhere('postingkredit.keterangancoa', 'LIKE', "%$filters[data]%");
                        }else{
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
