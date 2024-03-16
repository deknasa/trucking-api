<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApprovalSupirGambar extends MyModel
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
    protected $table = 'approvalsupirgambar';


    public function get()
    {
        $this->setRequestParameters();
        
        $query = ApprovalSupirGambar::from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
                "approvalsupirgambar.id",
                "approvalsupirgambar.namasupir",
                "approvalsupirgambar.noktp",
                "approvalsupirgambar.statusapproval",
                "statusapproval.memo as statusapproval_memo",

                "approvalsupirgambar.tglbatas",
                // "approvalsupirgambar.modifiedby",
                "approvalsupirgambar.created_at",
                "approvalsupirgambar.updated_at",
            )->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'approvalsupirgambar.statusapproval', 'statusapproval.id');            
            
            
            
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
            'approvalsupirgambar.id',
            'approvalsupirgambar.namasupir',
            'approvalsupirgambar.noktp',
            'approvalsupirgambar.statusapproval',
            'approvalsupirgambar.tglbatas',
            'approvalsupirgambar.created_at',
            'approvalsupirgambar.updated_at',
            'approvalsupirgambar.modifiedby'
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
        $query = ApprovalSupirGambar::from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
                "approvalsupirgambar.id",
                "approvalsupirgambar.namasupir",
                "approvalsupirgambar.noktp",
                "approvalsupirgambar.statusapproval",
                "approvalsupirgambar.tglbatas",
                // "approvalsupirgambar.modifiedby",
                "approvalsupirgambar.created_at",
                "approvalsupirgambar.updated_at",
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

    public function processStore(array $data): ApprovalSupirGambar
    {
        $approvalSupirGambar = new ApprovalSupirGambar();
        $approvalSupirGambar->namasupir = $data['namasupir'];
        $approvalSupirGambar->noktp = $data['noktp'];
        $approvalSupirGambar->statusapproval = $data['statusapproval'];
        $approvalSupirGambar->tglbatas = date('Y-m-d', strtotime($data['tglbatas']));
        
        $statusNonApproval = Parameter::from(DB::Raw("parameter with (readuncommitted)"))->select('id')->where('grp', '=', 'STATUS APPROVAL')->where('subgrp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();
        //nonaktif supir
        if ($data['statusapproval'] == $statusNonApproval->id) {
            $supirModel = Supir::processStatusNonAktifGambar($approvalSupirGambar->noktp);
        }else{
            $statusAktif = Parameter::from(DB::Raw("parameter with (readuncommitted)"))->select('id')->where('grp', '=', 'STATUS AKTIF')->where('subgrp', '=', 'STATUS AKTIF')->where('text', '=', 'AKTIF')->first();
            $supir = Supir::where('noktp',$approvalSupirGambar->noktp)->first();
            // if ($supir) {
            //     $supir->statusaktif = $statusAktif->id;
            //     $supir->save();
            // }
        }

        if (!$approvalSupirGambar->save()) {
            throw new \Exception("Error store Approval Supir Gambar.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($approvalSupirGambar->getTable()),
            'postingdari' => $data['postingdari'] ??strtoupper('ENTRY Approval Supir Gambar '),
            'idtrans' => $approvalSupirGambar->id,
            'nobuktitrans' =>  $approvalSupirGambar->id,
            'aksi' => 'ENTRY',
            'datajson' => $approvalSupirGambar->toArray(),
            'modifiedby' => $approvalSupirGambar->modifiedby
        ]);
        
        return $approvalSupirGambar;
    }

    public function processUpdate(ApprovalSupirGambar $approvalSupirGambar ,array $data): ApprovalSupirGambar
    {
        $approvalSupirGambar->namasupir = $data['namasupir'];
        $approvalSupirGambar->noktp = $data['noktp'];
        $approvalSupirGambar->statusapproval = $data['statusapproval'];
        $approvalSupirGambar->tglbatas = date('Y-m-d', strtotime($data['tglbatas']));
        
        $statusNonApproval = Parameter::from(DB::Raw("parameter with (readuncommitted)"))->select('id')->where('grp', '=', 'STATUS APPROVAL')->where('subgrp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();
        //nonaktif supir
        if ($data['statusapproval'] == $statusNonApproval->id) {
            $supirModel = Supir::processStatusNonAktifGambar($approvalSupirGambar->noktp);
        }else{
            $statusAktif = Parameter::from(DB::Raw("parameter with (readuncommitted)"))->select('id')->where('grp', '=', 'STATUS AKTIF')->where('subgrp', '=', 'STATUS AKTIF')->where('text', '=', 'AKTIF')->first();
            $supir = Supir::where('noktp',$approvalSupirGambar->noktp)->first();
            // if ($supir) {
            //     $supir->statusaktif = $statusAktif->id;
            //     $supir->save();
            // }
        }

        if (!$approvalSupirGambar->save()) {
            throw new \Exception("Error store Approval Supir Gambar.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($approvalSupirGambar->getTable()),
            'postingdari' => $data['postingdari'] ??strtoupper('EDIT Approval Supir Gambar '),
            'idtrans' => $approvalSupirGambar->id,
            'nobuktitrans' =>  $approvalSupirGambar->id,
            'aksi' => 'EDIT',
            'datajson' => $approvalSupirGambar->toArray(),
            'modifiedby' => $approvalSupirGambar->modifiedby
        ]);
        
        return $approvalSupirGambar;
    }

    public function processDestroy($id,$postingdari =""): ApprovalSupirGambar
    {
        $approvalSupirGambar = ApprovalSupirGambar::findOrFail($id);
        $dataHeader =  $approvalSupirGambar->toArray();
        $approvalSupirGambar = $approvalSupirGambar->lockAndDestroy($id);
        
        //nonaktif supir
        $supirModel = Supir::processStatusNonAktifGambar($approvalSupirGambar->noktp);
        
        if ($approvalSupirGambar) {

            $supir = DB::table('supir')->from(DB::raw("supir with (readuncommitted)"))
                ->where('noktp', $approvalSupirGambar->noktp)
                ->first();
            
            if ($supir) {
                $statusNonAktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'NON AKTIF')->first();
                $statusAktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'AKTIF')->first();

                $statusAktifSupir = $supir->statusaktif;
                $aktif =$statusNonAktif->id;
                if ( ($supir->photosupir != "") || ($supir->photoktp != "") || ($supir->photosim != "") || ($supir->photokk != "") || ($supir->photoskck != "") || ($supir->photodomisili != "") || ($supir->photovaksin != "") || ($supir->pdfsuratperjanjian != "") ) {
                    // dd('ada supir ada poto');
                    foreach (json_decode($supir->photosupir) as $value) {
                        if (!Storage::exists("supir/supir/$value")) {
                            $aktif = $statusNonAktif->id;
                            goto selesai;
                        }
                    }                           
                    foreach (json_decode($supir->photoktp) as $value) {
                        if (!Storage::exists("supir/ktp/$value")) {
                            $aktif = $statusNonAktif->id;
                            goto selesai;
                        }
                    }                           
                    foreach (json_decode($supir->photosim) as $value) {
                        if (!Storage::exists("supir/sim/$value")) {
                            $aktif = $statusNonAktif->id;
                            goto selesai;
                        }
                    }                           
                    foreach (json_decode($supir->photokk) as $value) {
                        if (!Storage::exists("supir/kk/$value")) {
                            $aktif = $statusNonAktif->id;
                            goto selesai;
                        }
                    }                           
                    foreach (json_decode($supir->photoskck) as $value) {
                        if (!Storage::exists("supir/skck/$value")) {
                            $aktif = $statusNonAktif->id;
                            goto selesai;
                        }
                    }                           
                    foreach (json_decode($supir->photodomisili) as $value) {
                        if (!Storage::exists("supir/domisili/$value")) {
                            $aktif = $statusNonAktif->id;
                            goto selesai;
                        }
                    }
                    foreach (json_decode($supir->photovaksin) as $value) {
                        if (!Storage::exists("supir/vaksin/$value")) {
                            $aktif = $statusNonAktif->id;
                            goto selesai;
                        }
                    }                           
                    foreach (json_decode($supir->pdfsuratperjanjian) as $value) {
                        if (!Storage::exists("supir/suratperjanjian/$value")) {
                            $aktif = $statusNonAktif->id;
                            goto selesai;
                        }
                    }
                   
                }
                selesai:
                DB::table('supir')->where('noktp', $supir->noktp)->update([
                    'statusaktif' => $aktif,
                ]);
                $sup = DB::table('supir')->where('noktp', $supir->noktp)->first();
                // dd($sup->statusaktif,$statusAktif->id);
            }
        }        
        (new LogTrail())->processStore([
            'namatabel' => $this->table,
            'postingdari' => ($postingdari =="") ? $postingdari :strtoupper('DELETE Approval Supir Gambar'),
            'idtrans' => $approvalSupirGambar->id,
            'nobuktitrans' =>  $approvalSupirGambar->id,
            'aksi' => 'DELETE',
            'datajson' => $approvalSupirGambar->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);
        return $approvalSupirGambar;
    }


}
