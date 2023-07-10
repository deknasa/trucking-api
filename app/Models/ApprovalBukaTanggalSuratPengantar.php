<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApprovalBukaTanggalSuratPengantar extends MyModel
{
    use HasFactory;

    protected $table = 'approvalbukatanggalsuratpengantar';

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
                'approvalbukatanggalsuratpengantar.id',
                'approvalbukatanggalsuratpengantar.tglbukti',
                'approvalbukatanggalsuratpengantar.jumlah',
                'parameter.memo as statusapproval',
                'approvalbukatanggalsuratpengantar.modifiedby',
                'approvalbukatanggalsuratpengantar.created_at',
                'approvalbukatanggalsuratpengantar.updated_at',
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'approvalbukatanggalsuratpengantar.statusapproval', 'parameter.id');

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $data = $query->get();

        return $data;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusapproval')->nullable();
        });

        $statusapproval = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS APPROVAL')
            ->where('subgrp', '=', 'STATUS APPROVAL')
            ->where('text', '=', 'APPROVAL')
            ->first();

        DB::table($tempdefault)->insert(["statusapproval" => $statusapproval->id]);

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusapproval'
            );

        $data = $query->first();
        return $data;
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "$this->table.id",
            "$this->table.tglbukti",
            "$this->table.jumlah",
            "parameter.text as statusapproval",
            "$this->table.modifiedby",
            "$this->table.created_at",
            "$this->table.updated_at",
        )->leftJoin(DB::raw("parameter with (readuncommitted)"), 'approvalbukatanggalsuratpengantar.statusapproval', 'parameter.id');
    }

    
    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->date('tglbukti')->nullable();
            $table->integer('jumlah')->nullable();
            $table->string('statusapproval', 500)->nullable();
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
            'tglbukti',
            'jumlah',
            'statusapproval',
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
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%' escape '|'");
                        } else {
                            $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusapproval') {
                                $query = $query->orWhere('parameter.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function processStore(array $data): ApprovalBukaTanggalSuratPengantar
    {
        $approvalBukaTanggal = new ApprovalBukaTanggalSuratPengantar();
        $approvalBukaTanggal->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $approvalBukaTanggal->jumlah = $data['jumlah'];
        $approvalBukaTanggal->statusapproval = $data['statusapproval'];
        $approvalBukaTanggal->modifiedby = auth('api')->user()->user;

        if (!$approvalBukaTanggal->save()) {
            throw new \Exception('Error storing approval buka tanggal surat pengantar.');
        }

        (new LogTrail())->processStore([
            'namatabel' => $approvalBukaTanggal->getTable(),
            'postingdari' => 'ENTRY APPROVAL BUKA TANGGAL SP',
            'idtrans' => $approvalBukaTanggal->id,
            'nobuktitrans' => $approvalBukaTanggal->id,
            'aksi' => 'ENTRY',
            'datajson' => $approvalBukaTanggal->toArray(),
        ]);

        return $approvalBukaTanggal;
    }
    
    public function processUpdate(ApprovalBukaTanggalSuratPengantar $approvalBukaTanggal, array $data): ApprovalBukaTanggalSuratPengantar
    {
        $approvalBukaTanggal->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $approvalBukaTanggal->jumlah = $data['jumlah'];
        $approvalBukaTanggal->statusapproval = $data['statusapproval'];
        $approvalBukaTanggal->modifiedby = auth('api')->user()->user;

        if (!$approvalBukaTanggal->save()) {
            throw new \Exception('Error updating approval buka tanggal surat pengantar.');
        }

        (new LogTrail())->processStore([
            'namatabel' => $approvalBukaTanggal->getTable(),
            'postingdari' => 'EDIT APPROVAL BUKA TANGGAL SP',
            'idtrans' => $approvalBukaTanggal->id,
            'nobuktitrans' => $approvalBukaTanggal->id,
            'aksi' => 'EDIT',
            'datajson' => $approvalBukaTanggal->toArray(),
        ]);

        return $approvalBukaTanggal;
    }

    public function processDestroy($id): ApprovalBukaTanggalSuratPengantar
    {
        $approvalBukaTanggal = new ApprovalBukaTanggalSuratPengantar();
        $approvalBukaTanggal = $approvalBukaTanggal->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($approvalBukaTanggal->getTable()),
            'postingdari' => 'DELETE APPROVAL BUKA TANGGAL SP',
            'idtrans' => $approvalBukaTanggal->id,
            'nobuktitrans' => $approvalBukaTanggal->id,
            'aksi' => 'DELETE',
            'datajson' => $approvalBukaTanggal->toArray(),
        ]);

        return $approvalBukaTanggal;
    }

    public function storeTglValidation($tanggal){
        $query = DB::table("approvalbukatanggalsuratpengantar")->from(DB::raw("approvalbukatanggalsuratpengantar with (readuncommitted)"))
            ->where('tglbukti', date('Y-m-d', strtotime($tanggal)))
            ->first();

        return $query;
    }
    public function updateTglValidation($tanggal, $id){
        $query = DB::table("approvalbukatanggalsuratpengantar")->from(DB::raw("approvalbukatanggalsuratpengantar with (readuncommitted)"))
            ->where('tglbukti', date('Y-m-d', strtotime($tanggal)))
            ->where('id','<>',$id)
            ->first();

        return $query;
    }
}
