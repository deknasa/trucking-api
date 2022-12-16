<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class ApprovalNotaHeader extends MyModel
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

    public function get()
    {
        $this->setRequestParameters();
        $periode = request()->periode ?? date('m-Y');
        $approve = request()->approve ?? 0;
        
        
        $tabel = (request()->tabel == 'NOTA DEBET') ? 'notadebetheader' : 'notakreditheader';
        $approval = Parameter::where('grp','STATUS APPROVAL')->where('text','APPROVAL')->first();
        $nonApproval = Parameter::where('grp','STATUS APPROVAL')->where('text','NON APPROVAL')->first();
        if($approve == $approval->id) {
            $approval = $nonApproval->id;
        }else if($approve == $nonApproval->id) {
            $approval = $approval->id;
        }else{
            $approval = 0;
        }
        
        $month = substr($periode,0,2);
        $year = substr($periode,3);

        $query = DB::table($tabel)
            ->select(
                DB::raw(" 
                    $tabel.id,$tabel.nobukti, $tabel.pelunasanpiutang_nobukti, $tabel.tglbukti, $tabel.keterangan, $tabel.postingdari, parameter.memo as statusapproval, $tabel.tglapproval, $tabel.userapproval, $tabel.tgllunas, $tabel.modifiedby, $tabel.created_at, $tabel.updated_at
                ")
            )
            ->leftJoin("parameter", "$tabel.statusapproval", "parameter.id")
            ->whereRaw("$tabel.statusapproval = $approval")
            ->whereRaw("MONTH($tabel.tglbukti) = $month")
            ->whereRaw("YEAR($tabel.tglbukti) = $year");
        

        
        $this->totalRows = $query->count();
        
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query, $tabel);
        $this->filter($query, $tabel);
        $this->paginate($query);


        $data = $query->get();

        return $data;
    }

    
    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
            "$this->anothertable.id,
            $this->anothertable.nobukti,
            $this->anothertable.tglbukti,
            $this->anothertable.keterangan,
            $this->anothertable.postingdari,
            'statusapproval.text as statusapproval',
            $this->anothertable.userapproval,
            $this->anothertable.tglapproval,
            $this->anothertable.modifiedby,
            $this->anothertable.created_at,
            $this->anothertable.updated_at"
            )
        )
        ->leftJoin('parameter as statusapproval', 'jurnalumumpusatheader.statusapproval', 'statusapproval.id');

    }

    public function sort($query, $tabel)
    {
        return $query->orderBy($tabel . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $tabel, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('parameter.text', '=', $filters['data']);
                        } else{
                            $query = $query->where($tabel . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->orWhere('parameter.text', '=', $filters['data']);
                        } else {
                            $query = $query->orWhere($tabel . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
