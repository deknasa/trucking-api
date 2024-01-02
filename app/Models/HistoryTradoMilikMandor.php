<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class HistoryTradoMilikMandor extends MyModel
{
    use HasFactory;
    protected $table = 'historytradomilikmandor';

    public function get($id)
    {
        $this->setRequestParameters();
        $query = DB::table("historytradomilikmandor")->from(DB::raw("historytradomilikmandor as a with (readuncommitted)"))
            ->select('a.id as idgrid', 'trado.kodetrado as tradogrid', 'mandorbaru.namamandor as mandorbarugrid', 'mandorlama.namamandor as mandorlamagrid', 'a.tglberlaku as tanggalberlakugrid')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'a.trado_id', 'trado.id')
            ->leftJoin(DB::raw("mandor as mandorbaru with (readuncommitted)"), 'a.mandor_id', 'mandorbaru.id')
            ->leftJoin(DB::raw("mandor as mandorlama with (readuncommitted)"), 'a.mandorlama_id', 'mandorlama.id')
            ->where('a.trado_id', $id);

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);
        return $query->get();
    }
    public function processStore(array $data)
    {
        $history = new HistoryTradoMilikMandor();
        $history->trado_id = $data['id'];
        $history->mandor_id = $data['mandorbaru_id'];
        $history->mandorlama_id = $data['mandorlama_id'];
        $history->tglberlaku = $data['tglberlakumilikmandor'];
        $history->modifiedby = auth('api')->user()->name;
        if (!$history->save()) {
            throw new \Exception("Error updating trado milik mandor.");
        }


        (new LogTrail())->processStore([
            'namatabel' => strtoupper($history->getTable()),
            'postingdari' => 'ENTRY HISTORY TRADO MILIK MANDOR',
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
        } else if ($this->params['sortIndex'] == 'mandorbarugrid') {
            return $query->orderBy('mandorbaru.namamandor', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'mandorlamagrid') {
            return $query->orderBy('mandorlama.namamandor', $this->params['sortOrder']);
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
                        } else if ($filters['field'] == 'mandorlamagrid') {
                            $query = $query->where('mandorlama.namamandor', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'mandorbarugrid') {
                            $query = $query->where('mandorbaru.namamandor', 'LIKE', "%$filters[data]%");
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
                            if ($filters['field'] == 'mandorlamagrid') {
                                $query = $query->orwhere('mandorlama.namamandor', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'mandorbarugrid') {
                                $query = $query->orwhere('mandorbaru.namamandor', 'LIKE', "%$filters[data]%");
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
