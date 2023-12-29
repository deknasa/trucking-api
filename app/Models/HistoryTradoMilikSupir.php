<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class HistoryTradoMilikSupir extends MyModel
{
    use HasFactory;
    protected $table = 'historytradomiliksupir';
    public function get($id)
    {
        $this->setRequestParameters();
        $query = DB::table("historytradomiliksupir")->from(DB::raw("historytradomiliksupir as a with (readuncommitted)"))
            ->select('a.id as idgrid', 'trado.kodetrado as tradogrid', 'supirbaru.namasupir as supirbarugrid', 'supirlama.namasupir as supirlamagrid', 'a.tglberlaku as tanggalberlakugrid')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'a.trado_id', 'trado.id')
            ->leftJoin(DB::raw("supir as supirbaru with (readuncommitted)"), 'a.supir_id', 'supirbaru.id')
            ->leftJoin(DB::raw("supir as supirlama with (readuncommitted)"), 'a.supirlama_id', 'supirlama.id')
            ->where('a.trado_id', $id);

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);
        return $query->get();
    }
    public function processStore(array $data)
    {
        $history = new HistoryTradoMilikSupir();
        $history->trado_id = $data['id'];
        $history->supir_id = $data['supirbaru_id'];
        $history->supirlama_id = $data['supirlama_id'];
        $history->tglberlaku = $data['tglberlakumiliksupir'];
        $history->modifiedby = auth('api')->user()->name;
        if (!$history->save()) {
            throw new \Exception("Error entry trado milik supir.");
        }


        (new LogTrail())->processStore([
            'namatabel' => strtoupper($history->getTable()),
            'postingdari' => 'ENTRY HISTORY SUPIR MILIK SUPIR',
            'idtrans' => $history->id,
            'nobuktitrans' => $history->id,
            'aksi' => 'ENTRY',
            'datajson' => $history->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $history;
    }


    public function sort($query)
    {

        if ($this->params['sortIndex'] == 'tradogrid') {
            return $query->orderBy('trado.kodetrado', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'supirbarugrid') {
            return $query->orderBy('supirbaru.namasupir', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'supirlamagrid') {
            return $query->orderBy('supirlama.namasupir', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'tanggalberlakugrid') {
            return $query->orderBy('a.tglberlaku', $this->params['sortOrder']);
        } else {
            return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'tradogrid') {
                            $query = $query->where('trado.kodetrado', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'supirlamagrid') {
                            $query = $query->where('supirlama.namasupir', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'supirbarugrid') {
                            $query = $query->where('supirbaru.namasupir', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'tanggalberlakugrid') {
                            $query = $query->whereRaw("format((case when year(isnull(a.tglberlaku,'1900/1/1'))<2000 then null else a.tglberlaku end), 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->whereRaw($this->table . ".".  $filters['field'] ." LIKE '%".str_replace($filters['data'],'[','|[') ."%' escape '|'");
                            $query = $query->whereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'supirlamagrid') {
                                $query = $query->orwhere('supirlama.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'supirbarugrid') {
                                $query = $query->orwhere('supirbaru.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tradogrid') {
                                $query = $query->orWhere('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tanggalberlakugrid') {
                                $query = $query->orWhereRaw("format((case when year(isnull(a.tglberlaku,'1900/1/1'))<2000 then null else a.tglberlaku end), 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->OrwhereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                                // $query = $query->OrwhereRaw($this->table . ".".  $filters['field'] ." LIKE '%".str_replace($filters['data'],'[','|[') ."%' escape '|'");
                                // $query = $query->orWhereRaw($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
}
