<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OpnameDetail extends MyModel
{
    use HasFactory;

    protected $table = 'opnamedetail';

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

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));

        if (isset(request()->forReport) && request()->forReport) {
           
        } else {
            $query->select(
                $this->table . '.nobukti',
                'stok.namastok as stok',
                $this->table . '.qty',
                $this->table . '.tglbuktimasuk as tanggal',
                $this->table . '.qtyfisik',
            )
            ->leftJoin(DB::raw("stok with (readuncommitted)"), 'opnamedetail.stok_id', 'stok.id');

            $query->where($this->table . '.opname_id', '=', request()->opname_id);
            $this->sort($query);
            $this->filter($query);
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->paginate($query);
        }

        return $query->get();
    }

    public function findAll($id)
    {
        $query = DB::table("opnamedetail")->from(DB::raw("opnamedetail with (readuncommitted)"))
        ->select(
            'opnamedetail.stok_id as id',
            'stok.namastok as namabarang',
            'opnamedetail.tglbuktimasuk as tanggal',
            'opnamedetail.qty',
            'opnamedetail.qtyfisik',
        )
        ->leftJoin(DB::raw("opnameheader with (readuncommitted)"), 'opnamedetail.opname_id', 'opnameheader.id')
        ->leftJoin(DB::raw("stok with (readuncommitted)"), 'opnamedetail.stok_id', 'stok.id')
        ->where('opnamedetail.opname_id', $id)
        ->get();

        return $query;
    }
    
    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'stok') {
            return $query->orderBy('stok.namastok', $this->params['sortOrder']);
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
                        if ($filters['field'] == 'stok') {
                            $query = $query->where('stok.namastok', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'qty' || $filters['field'] == 'qtyfisik') {
                            $query = $query->whereRaw("format(opnamedetail.".$filters['field'].", '#,#0.00') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'stok') {
                                $query = $query->orWhere('stok.namastok', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'qty' || $filters['field'] == 'qtyfisik') {
                                $query = $query->orWhereRaw("format(opnamedetail.".$filters['field'].", '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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

    public function processStore(OpnameHeader $opnameHeader, array $data): OpnameDetail
    {
        $opnameDetail = new OpnameDetail();
        $opnameDetail->opname_id = $opnameHeader->id;
        $opnameDetail->nobukti = $opnameHeader->nobukti;
        $opnameDetail->tglbuktimasuk = $data['tglbuktimasuk'];
        $opnameDetail->stok_id = $data['stok_id'];
        $opnameDetail->qty = $data['qty'];
        $opnameDetail->qtyfisik = $data['qtyfisik'];
        $opnameDetail->info = html_entity_decode(request()->info);
        
        if (!$opnameDetail->save()) {
            throw new \Exception("Error storing opname detail.");
        }

        return $opnameDetail;
    }
}
