<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExpSim extends MyModel
{
    use HasFactory;

    public function get()
    {
        $this->setRequestParameters();
        $statusaktif = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('text', '=', 'AKTIF')
            ->first();

        $batasMax = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', '=', 'BATAS MAX EXPIRED')
            ->first();
        $rentang = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', '=', 'RENTANG EXPIRED')
            ->first();
        $sudahExp =  Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', '=', 'STATUS EXPIRED')
            ->where('text', '=', 'SUDAH EXPIRED')
            ->first();
        $hampirExp =  Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', '=', 'STATUS EXPIRED')
            ->where('text', '=', 'HAMPIR EXPIRED')
            ->first();
        $belumExp =  Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', '=', 'STATUS EXPIRED')
            ->where('text', '=', '90 HARI SEBELUM EXPIRED')
            ->first();

        $class = 'ExpSimController';
        $user = auth('api')->user()->name;
        $proses = request()->proses ?? 'reload';

        if ($proses == 'reload') {
            $temtabel = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel a with (readuncommitted)")
            )
                ->select(
                    'id',
                    'class',
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            if (isset($querydata)) {
                Schema::dropIfExists($querydata->namatabel);
                DB::table('listtemporarytabel')->where('id', $querydata->id)->delete();
            }

            DB::table('listtemporarytabel')->insert(
                [
                    'class' => $class,
                    'namatabel' => $temtabel,
                    'modifiedby' => $user,
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s'),
                ]
            );

            Schema::create($temtabel, function (Blueprint $table) {
                $table->integer('id')->nullable();
                $table->string('namasupir', 1000)->nullable();
                $table->date('tglexpsim', 1000)->nullable();
                $table->integer('status')->nullable();
            });
            $getQuery = DB::table('supir')->from(DB::raw("supir with (readuncommitted)"))
                ->select(
                    'supir.id',
                    'supir.namasupir',
                    DB::raw('(case when (year(supir.tglexpsim) <= 2000) then null else supir.tglexpsim end ) as tglexpsim'),
                
                    DB::raw("(case 
                    when DATEDIFF(dd,getdate(),tglexpsim)>$batasMax->text then $belumExp->id 
                    when tglexpsim <= getdate() then $sudahExp->id
                    else $hampirExp->id end) 
                    
                    as status")
                )
                ->where('statusaktif', $statusaktif->id)
                ->where('tglexpsim', '<=', date('Y/m/d', strtotime("+$rentang->text days")));

            DB::table($temtabel)->insertUsing(['id', 'namasupir', 'tglexpsim', 'status'], $getQuery);
        } else {
            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel with (readuncommitted)")
            )
                ->select(
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            // dd($querydata);
            $temtabel = $querydata->namatabel;
        }

        $query = DB::table(DB::raw($temtabel))->from(
            DB::raw(DB::raw($temtabel) . " supir with (readuncommitted)")
        )
            ->select(
                'supir.id',
                'supir.namasupir',
                'supir.tglexpsim',
                'parameter.memo as status',
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'supir.status', 'parameter.id');


        $this->filter($query);

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->paginate($query);
        $data = $query->get();

        return $data;
    }


    public function sort($query)
    {

        return $query->orderBy('supir.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'status') {
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'tglexpsim') {
                            $query = $query->whereRaw("format(supir." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw("supir.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'status') {
                                $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'tglexpsim') {
                                $query = $query->orWhereRaw("format(supir." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw("supir.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
