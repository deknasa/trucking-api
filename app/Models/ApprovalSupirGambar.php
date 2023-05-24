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
}
