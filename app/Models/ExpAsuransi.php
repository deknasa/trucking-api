<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExpAsuransi extends MyModel
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

        $statusabsensisupir = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', '=', 'STATUS ABSENSI SUPIR')
            ->where('subgrp', '=', 'STATUS ABSENSI SUPIR')
            ->where('text', '=', 'ABSENSI SUPIR')
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
            ->where('text', '=', 'RENTANG HARI SEBELUM EXP')
            ->first();

        $class = 'ExpStnkController';
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
                $table->string('kodetrado', 1000)->nullable();
                $table->date('tglasuransimati', 1000)->nullable();
                $table->integer('status')->nullable();
                $table->integer('rentang')->nullable();
            });
            $getQuery = DB::table('trado')->from(DB::raw("trado with (readuncommitted)"))
                ->select(
                    'trado.id',
                    'trado.kodetrado',
                    DB::raw('(case when (year(trado.tglasuransimati) <= 2000) then null else trado.tglasuransimati end ) as tglasuransimati'),

                    DB::raw("(case 
                    when DATEDIFF(dd,getdate(),tglasuransimati)>$batasMax->text then $belumExp->id 
                    when tglasuransimati <= getdate() then $sudahExp->id
                    else $hampirExp->id end) 
                    
                    as status"),
                    DB::raw("DATEDIFF(dd,getdate(),tglasuransimati)  as rentang")
                )
                ->where('statusaktif', $statusaktif->id)
                ->where('statusabsensisupir', $statusabsensisupir->id)
                ->where('tglasuransimati', '<=', date('Y/m/d', strtotime("+$rentang->text days")));

            DB::table($temtabel)->insertUsing(['id', 'kodetrado', 'tglasuransimati', 'status', 'rentang'], $getQuery);
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
            DB::raw(DB::raw($temtabel) . " trado with (readuncommitted)")
        )
            ->select(
                'trado.id',
                'trado.kodetrado',
                'trado.tglasuransimati',
                DB::raw(
                    '
                    CASE 
                        WHEN parameter.id = 391 THEN 
                            CONCAT(\'{"MEMO":"\', trado.rentang, \' HARI SEBELUM EXPIRED","SINGKATAN":"\', trado.rentang, \'","WARNA":"#E16000","WARNATULISAN":"#FFF"}\')
                        ELSE parameter.memo 
                    END
                 AS status'
                )
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'trado.status', 'parameter.id');


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

        return $query->orderBy('trado.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'status') {
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'tglasuransimati') {
                            $query = $query->whereRaw("format(trado." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw("trado.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'status') {
                                $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'tglasuransimati') {
                                $query = $query->orWhereRaw("format(trado." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw("trado.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
