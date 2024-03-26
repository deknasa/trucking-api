<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApprovalSupirKeterangan extends MyModel
{
    use HasFactory;
    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $table = 'approvalsupirketerangan';

    public function get()
    {
        $this->setRequestParameters();
        
        $query = ApprovalSupirKeterangan::from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
                "approvalsupirketerangan.id",
                "approvalsupirketerangan.namasupir",
                "approvalsupirketerangan.noktp",
                "approvalsupirketerangan.statusapproval",
                "statusapproval.memo as statusapproval_memo",

                "approvalsupirketerangan.tglbatas",
                // "approvalsupirketerangan.modifiedby",
                "approvalsupirketerangan.created_at",
                "approvalsupirketerangan.updated_at",
            )->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'approvalsupirketerangan.statusapproval', 'statusapproval.id');            
            
            
            
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function firstOrFind($supir_id){
        $supir = Supir::find($supir_id);
        $data = DB::table($this->table)
        ->select(
            'approvalsupirketerangan.id',
            'approvalsupirketerangan.namasupir',
            'approvalsupirketerangan.noktp',
            'approvalsupirketerangan.statusapproval',
            'approvalsupirketerangan.tglbatas',
            'approvalsupirketerangan.created_at',
            'approvalsupirketerangan.updated_at',
            'approvalsupirketerangan.modifiedby'
        )
        ->where('noktp',$supir->noktp)->first();
        

        if (!$data) {
            $data = [
                "id"=>null,
                "namasupir"=>$supir->namasupir,
                "noktp"=>$supir->noktp,
                "statusapproval"=>null,
                "tglbatas"=>null,
                "created_at"=>null,
                "updated_at"=>null,
                "modifiedby"=>null,
            ];
        }
        return $data;
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('namasupir')->nullable();
            $table->string('noktp')->nullable();
            $table->string('statusapproval')->nullable();
            $table->date('tglbatas')->nullable();
            // $table->string('modifiedby', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });


        $query = DB::table($modelTable);
        $query = ApprovalSupirKeterangan::from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
                "approvalsupirketerangan.id",
                "approvalsupirketerangan.namasupir",
                "approvalsupirketerangan.noktp",
                "approvalsupirketerangan.statusapproval",
                "approvalsupirketerangan.tglbatas",
                // "approvalsupirketerangan.modifiedby",
                "approvalsupirketerangan.created_at",
                "approvalsupirketerangan.updated_at",
            );

        $query = $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing([
            'id',
            'namasupir',
            'noktp',
            'statusapproval',
            'tglbatas',
            // 'modifiedby',
            'created_at', 'updated_at'
        ], $models);

        return $temp;
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
            ->where('default', '=', 'YA')
            ->first();
        DB::table($tempdefault)->insert(["statusapproval" => $statusapproval->id]);

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusapproval'
            );

        $data = $query->first();
        // dd($data);
        return $data;
    }

    public function processStore(array $data): ApprovalSupirKeterangan
    {
        $tglbatas = $data['tglbatas'];
        if ($data['tglbatas']) {
            $tglbatas = date('Y-m-d', strtotime($data['tglbatas']));
        }
        $approvalSupirKeterangan = new ApprovalSupirKeterangan();
        $approvalSupirKeterangan->namasupir = $data['namasupir'];
        $approvalSupirKeterangan->noktp = $data['noktp'];
        $approvalSupirKeterangan->statusapproval = $data['statusapproval'];
        $approvalSupirKeterangan->tglbatas = $tglbatas;

        $statusNonApproval = Parameter::from(DB::Raw("parameter with (readuncommitted)"))->select('id')->where('grp', '=', 'STATUS APPROVAL')->where('subgrp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();
        //nonaktif supir
        if ($data['statusapproval'] == $statusNonApproval->id) {
            $supirModel = Supir::processStatusNonAktifKeterangan($approvalSupirKeterangan->noktp);
        }else{
            $statusAktif = Parameter::from(DB::Raw("parameter with (readuncommitted)"))->select('id')->where('grp', '=', 'STATUS AKTIF')->where('subgrp', '=', 'STATUS AKTIF')->where('text', '=', 'AKTIF')->first();
            $supir = Supir::where('noktp',$approvalSupirKeterangan->noktp)->first();
            // if ($supir) {
            //     $supir->statusaktif = $statusAktif->id;
            //     $supir->save();
            // }
        }

        if (!$approvalSupirKeterangan->save()) {
            throw new \Exception("Error store Approval Supir Gambar.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($approvalSupirKeterangan->getTable()),
            'postingdari' => $data['postingdari'] ??strtoupper('ENTRY Approval Supir Gambar '),
            'idtrans' => $approvalSupirKeterangan->id,
            'nobuktitrans' =>  $approvalSupirKeterangan->id,
            'aksi' => 'ENTRY',
            'datajson' => $approvalSupirKeterangan->toArray(),
            'modifiedby' => $approvalSupirKeterangan->modifiedby
        ]);
        
        return $approvalSupirKeterangan;
    }

    public function processUpdate(ApprovalSupirKeterangan $approvalSupirKeterangan ,array $data): ApprovalSupirKeterangan
    {
        $tglbatas = $data['tglbatas'];
        if ($data['tglbatas']) {
            $tglbatas = date('Y-m-d', strtotime($data['tglbatas']));
        }
        $approvalSupirKeterangan->namasupir = $data['namasupir'];
        $approvalSupirKeterangan->noktp = $data['noktp'];
        $approvalSupirKeterangan->statusapproval = $data['statusapproval'];
        $approvalSupirKeterangan->tglbatas = $tglbatas;

        $statusNonApproval = Parameter::from(DB::Raw("parameter with (readuncommitted)"))->select('id')->where('grp', '=', 'STATUS APPROVAL')->where('subgrp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();
        //nonaktif supir
        if ($data['statusapproval'] == $statusNonApproval->id) {

            $supirModel = Supir::processStatusNonAktifKeterangan($approvalSupirKeterangan->noktp);
        }else{
            $statusAktif = Parameter::from(DB::Raw("parameter with (readuncommitted)"))->select('id')->where('grp', '=', 'STATUS AKTIF')->where('subgrp', '=', 'STATUS AKTIF')->where('text', '=', 'AKTIF')->first();
            $supir = Supir::where('noktp',$approvalSupirKeterangan->noktp)->first();
            // if ($supir) {
            //     $supir->statusaktif = $statusAktif->id;
            //     $supir->save();
            // }
        }

        if (!$approvalSupirKeterangan->save()) {
            throw new \Exception("Error store Approval Supir Gambar.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($approvalSupirKeterangan->getTable()),
            'postingdari' => $data['postingdari'] ??strtoupper('EDIT Approval Supir Gambar '),
            'idtrans' => $approvalSupirKeterangan->id,
            'nobuktitrans' =>  $approvalSupirKeterangan->id,
            'aksi' => 'EDIT',
            'datajson' => $approvalSupirKeterangan->toArray(),
            'modifiedby' => $approvalSupirKeterangan->modifiedby
        ]);
        
        return $approvalSupirKeterangan;
    }

    public function processDestroy($id,$postingdari =""): ApprovalSupirKeterangan
    {
        $approvalSupirKeterangan = ApprovalSupirKeterangan::findOrFail($id);
        $dataHeader =  $approvalSupirKeterangan->toArray();
      
        //nonaktif supir
        $supirModel = Supir::processStatusNonAktifKeterangan($approvalSupirKeterangan->noktp);

        $approvalSupirKeterangan = $approvalSupirKeterangan->lockAndDestroy($id);
        $hutangLogTrail = (new LogTrail())->processStore([
            'namatabel' => $this->table,
            'postingdari' => ($postingdari =="") ? $postingdari :strtoupper('DELETE Approval Supir Gambar'),
            'idtrans' => $approvalSupirKeterangan->id,
            'nobuktitrans' =>  $approvalSupirKeterangan->id,
            'aksi' => 'DELETE',
            'datajson' => $approvalSupirKeterangan->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);
        return $approvalSupirKeterangan;
    }
}
