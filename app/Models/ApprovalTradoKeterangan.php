<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApprovalTradoKeterangan extends MyModel
{
    use HasFactory;
    protected $table = 'approvaltradoketerangan';
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {
        $this->setRequestParameters();
        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
        ->select(
            'approvaltradoketerangan.id',
            'approvaltradoketerangan.kodetrado',
            'parameter.memo as statusapproval',
            'approvaltradoketerangan.tglbatas',
            'approvaltradoketerangan.created_at',
            'approvaltradoketerangan.updated_at',
            'approvaltradoketerangan.modifiedby'
        )
        ->join(DB::raw("parameter with (readuncommitted)"), 'approvaltradoketerangan.statusapproval', 'parameter.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function firstOrFind($trado_id){
        $trado = Trado::find(request()->trado_id);
        $data = DB::table($this->table)
        ->select(
            'approvaltradoketerangan.id',
            'approvaltradoketerangan.kodetrado',
            'approvaltradoketerangan.statusapproval',
            'approvaltradoketerangan.tglbatas',
            'approvaltradoketerangan.created_at',
            'approvaltradoketerangan.updated_at',
            'approvaltradoketerangan.modifiedby'
        )
        ->where('kodetrado',$trado->kodetrado)->first();
        

        if (!$data) {
            $data = [
                "id" => null,
                "info" => null,
                "kodetrado" => $trado->kodetrado,
                "modifiedby" => null,
                "statusapproval" => null,
                "tglbatas" => null,
                "updated_at" => null,
                "created_at" => null,
            ];
        }
        return $data;
    }

    
    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "$this->table.id",
            "$this->table.kodetrado",
            "parameter.text as statusapproval",
            "$this->table.tglbatas",
            "$this->table.modifiedby",
            "$this->table.created_at",
            "$this->table.updated_at",
        )->leftJoin(DB::raw("parameter with (readuncommitted)"), 'approvaltradoketerangan.statusapproval', '=', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodetrado', 500)->nullable();
            $table->string('statusapproval', 500)->nullable();
            $table->date('tglbatas')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $query = $this->sort($query);
        $models = $this->filter($query);

        DB::table($temp)->insertUsing([
            'id',
            'kodetrado',
            'statusapproval',
            'tglbatas',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $models);

        return  $temp;
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
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('parameter.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusapproval') {
                                $query = $query->orWhere('parameter.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
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

    
    public function processStore(array $data): ApprovalTradoKeterangan
    {
        $approvalTradoKeterangan = new ApprovalTradoKeterangan();
        $approvalTradoKeterangan->kodetrado = $data['kodetrado'];
        $approvalTradoKeterangan->tglbatas = date('Y-m-d', strtotime($data['tglbatas']));
        $approvalTradoKeterangan->statusapproval = $data['statusapproval'];
        $approvalTradoKeterangan->modifiedby = auth('api')->user()->name;

        $statusApproval = Parameter::from(DB::Raw("parameter with (readuncommitted)"))->select('id')->where('grp', '=', 'STATUS APPROVAL')->where('subgrp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        //nonaktif supir
        if ($approvalTradoKeterangan->statusapproval == $statusApproval->id) {
            $statusAktif = Parameter::from(DB::Raw("parameter with (readuncommitted)"))->select('id')->where('grp', '=', 'STATUS AKTIF')->where('subgrp', '=', 'STATUS AKTIF')->where('text', '=', 'AKTIF')->first();
            $trado = Trado::where('kodetrado', $approvalTradoKeterangan->kodetrado)->first();
            if ($trado) {
                $trado->statusaktif = $statusAktif->id;
                $trado->save();
            }
        }

        if (!$approvalTradoKeterangan->save()) {
            throw new \Exception('Error storing approvalTradoKeterangan.');
        }

        (new LogTrail())->processStore([
            'namatabel' => $approvalTradoKeterangan->getTable(),
            'postingdari' => 'ENTRY APPROVAL TRADO KETERANGAN',
            'idtrans' => $approvalTradoKeterangan->id,
            'nobuktitrans' => $approvalTradoKeterangan->id,
            'aksi' => 'ENTRY',
            'datajson' => $approvalTradoKeterangan->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $approvalTradoKeterangan;
    }
    public function processUpdate(ApprovalTradoKeterangan $approvaltradoketerangan, array $data): ApprovalTradoKeterangan
    {
        
        $approvaltradoketerangan->kodetrado = $data['kodetrado'];
        $approvaltradoketerangan->tglbatas = date('Y-m-d', strtotime($data['tglbatas']));
        $approvaltradoketerangan->statusapproval = $data['statusapproval'];
        $approvaltradoketerangan->modifiedby = auth('api')->user()->name;

        if (!$approvaltradoketerangan->save()) {
            throw new \Exception('Error updating approvaltradoketerangan.');
        }
            $statusApp = DB::table('parameter')->where('grp', 'STATUS APPROVAL')->where('subgrp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
            $trado = Trado::from(DB::raw("trado with (readuncommitted)"))
                ->where('kodetrado', $data['kodetrado'])
                ->first();
            if ($trado != '') {
                if ($data['statusapproval'] == $statusApp->id) {

                    $statusAktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'AKTIF')->first();
                    DB::table('trado')->where('kodetrado', $data['kodetrado'])->update([
                        'statusaktif' => $statusAktif->id,
                    ]);
                } else {

                    $statusNonAktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'NON AKTIF')->first();

                    $required = [
                        "kodetrado" => $trado->kodetrado,
                        "tahun" => $trado->tahun,
                        "merek" => $trado->merek,
                        "norangka" => $trado->norangka,
                        "nomesin" => $trado->nomesin,
                        "nama" => $trado->nama,
                        "nostnk" => $trado->nostnk,
                        "alamatstnk" => $trado->alamatstnk,
                        "tglpajakstnk" => $trado->tglpajakstnk,
                        "tipe" => $trado->tipe,
                        "jenis" => $trado->jenis,
                        "isisilinder" => $trado->isisilinder,
                        "warna" => $trado->warna,
                        "jenisbahanbakar" => $trado->jenisbahanbakar,
                        "jumlahsumbu" => $trado->jumlahsumbu,
                        "jumlahroda" => $trado->jumlahroda,
                        "model" => $trado->model,
                        "nobpkb" => $trado->nobpkb,
                        "jumlahbanserap" => $trado->jumlahbanserap,
                    ];
                    $key = array_keys($required, null);
                    if (count($key)) {
                        $trado->statusaktif = $statusNonAktif->id;
                        $trado->save();
                    }
                }
            }

        if (!$approvaltradoketerangan->save()) {
            throw new \Exception('Error updating approvaltradoketerangan.');
        }

        (new LogTrail())->processStore([
            'namatabel' => $approvaltradoketerangan->getTable(),
            'postingdari' => 'EDIT APPROVAL TRADO KETERANGAN',
            'idtrans' => $approvaltradoketerangan->id,
            'nobuktitrans' => $approvaltradoketerangan->id,
            'aksi' => 'EDIT',
            'datajson' => $approvaltradoketerangan->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $approvaltradoketerangan;
    }
}
