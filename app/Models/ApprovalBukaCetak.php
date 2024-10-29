<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApprovalBukaCetak extends MyModel
{
    use HasFactory;

    public function get() {
        $this->setRequestParameters();

        $table = Parameter::where('text',request()->table)->first();
        $statusCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();

        $backSlash = " \ ";
        $model = 'App\Models'.trim($backSlash).$table->text;
        $data = app($model);
        $tabledb = $data->getTable();
        $periode = explode("-", request()->periode);

        $query = DB::table($tabledb)->from(DB::raw("$tabledb with (readuncommitted)"));

        $query
        ->select(
            "$tabledb.id",
            "$tabledb.nobukti",
            "$tabledb.tglbukti",
            "$tabledb.keterangan",
            "$tabledb.userbukacetak",
            "$tabledb.modifiedby",
            "$tabledb.statuscetak",
            "statuscetak.memo as  statuscetak",
            "statuscetak.id as  statuscetak_id",
            "$tabledb.modifiedby",
            "$tabledb.created_at",
            "$tabledb.updated_at",
        )
        ->leftJoin('parameter as statuscetak', "$tabledb.statuscetak", 'statuscetak.id')
        ->whereRaw("MONTH($tabledb.tglbukti) ='" . $periode[0] . "'")
        ->whereRaw("year($tabledb.tglbukti) ='" . $periode[1] . "'")
        ->where("$tabledb.statuscetak", $statusCetak->id);
        
        $this->filter($query,$tabledb);
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query,$tabledb);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }


    public function sort($query,$tabledb = "")
    {
        return $query->orderBy($tabledb . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function filter($query, $tabledb = "")
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == "") {
                        } else if ($filters['field'] == 'tglbukti') {
                            $query = $query->whereRaw("format($tabledb." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format($tabledb." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");   
                        }else{
                            $query = $query->whereRaw($tabledb. ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == "") {
                            } else if ($filters['field'] == 'tglbukti') {
                                $query = $query->whereRaw("format($tabledb." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format($tabledb." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'"); 
                            }else{
                                $query = $query->orWhereRaw($tabledb . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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


    public function processStore(array $data)
    {
        $table = Parameter::where('text', $data['table'])->first();
        foreach ($data['tableId'] as $tableId) {
            $resultData[] = $this->bukaCetak($tableId, $table);
        }
        return $resultData;
    }

    public function bukaCetak($id, $table)
    {
        $backSlash = " \ ";

        $model = 'App\Models' . trim($backSlash) . $table->text;
        $data = app($model)->findOrFail($id);
        $statusCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
        $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

        if ($data->statuscetak == $statusCetak->id) {
            $data->statuscetak = $statusBelumCetak->id;
        // } else {
        //     $data->statuscetak = $statusCetak->id;
        }

        $data->tglbukacetak = date('Y-m-d H:i:s');
        $data->userbukacetak = auth('api')->user()->name;
        $data->info = html_entity_decode(request()->info);
        if (!$data->save()) {
            throw new \Exception('Error Buka Cetak.');
        }
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($data->getTable()),
            'postingdari' => "BUKA/BELUM CETAK $table->text",
            'idtrans' => $data->id,
            'nobuktitrans' => $data->nobukti,
            'aksi' => 'BUKA/BELUM CETAK',
            'datajson' => $data->toArray(),
            'modifiedby' => auth('api')->user()->name,
        ]);
        return $data;
    }
}
