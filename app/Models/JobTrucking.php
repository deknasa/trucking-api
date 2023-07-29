<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class JobTrucking extends MyModel
{
    use HasFactory;


    public function get()
    {

        $this->setRequestParameters();
        //  dd(request()->trado_id);
        // dump(request()->container_id);
        // dd(request()->jenisorder_id);


        $statusgerobak = DB::table('parameter')->from(
            DB::raw("parameter as a with (readuncommitted)")
        )
            ->select(
                'a.id'
            )
            ->where('a.grp', '=', 'STATUS GEROBAK')
            ->where('a.subgrp', '=', 'STATUS GEROBAK')
            ->where('a.text', '=', 'BUKAN GEROBAK')
            ->first();

        $statuslongtrip = DB::table('parameter')->from(
            DB::raw("parameter as a with (readuncommitted)")
        )
            ->select(
                'a.id'
            )
            ->where('a.grp', '=', 'STATUS LONGTRIP')
            ->where('a.subgrp', '=', 'STATUS LONGTRIP')
            ->where('a.text', '=', 'LONGTRIP')
            ->first();

        $pelabuhan = DB::table('parameter')->from(
            DB::raw("parameter as a with (readuncommitted)")
        )
            ->select(
                'a.text'
            )
            ->where('a.grp', '=', 'PELABUHAN CABANG')
            ->where('a.subgrp', '=', 'PELABUHAN CABANG')
            ->first();

            



        $query = DB::table('trado')->from(
            DB::raw("trado as a with (readuncommitted)")
        )
            ->select(
                'a.keterangan'
            )
            ->where('a.id', '=', request()->trado_id)
            ->where('a.statusgerobak', '=', $statusgerobak->id)
            ->first();

            // dd($query);

        $tempselesai = '##tempselesai' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempselesai, function ($table) {
            $table->string('jobtrucking', 1000)->nullable();
        });

        if (isset($query)) {

            $queryjob = DB::table('suratpengantar')->from(
                DB::raw("suratpengantar as a with(readuncommitted)")
            )
                ->select(
                    'a.jobtrucking'
                )
                ->join(DB::raw("orderantrucking as b with(readuncommitted)"), 'a.jobtrucking', 'b.jobtrucking')
                ->where('a.container_id', '=', request()->container_id)
                ->where('a.jenisorder_id', '=', request()->jenisorder_id)
                ->where('a.gandengan_id', '=', request()->gandengan_id)
                ->where('a.pelanggan_id', '=', request()->pelanggan_id)
                ->where('a.tarif_id', '=', request()->tarif_id)
                ->whereRaw("isnull(a.jobtrucking,'')<>''")
                ->whereRaw("a.sampai_id=". $pelabuhan->text." and isnull(B.statusapprovalbuka,4)=4") ;
                // ->where('a.sampai_id', '=', $pelabuhan->text);




            DB::table($tempselesai)->insertUsing([
                'jobtrucking',
            ], $queryjob);

            $querydata = DB::table('suratpengantar')->from(
                DB::raw("suratpengantar as a with(readuncommitted)")
            )
                ->select(
                    'a.jobtrucking',
                    'a.tglbukti',
                    'b.namasupir as supir',
                    'c.keterangan as trado',
                    'kotadr.keterangan as kotadari',
                    'kotasd.keterangan as kotasampai',
                    'a.nobukti',

                )
                ->leftjoin(DB::raw("supir as b with(readuncommitted)"), 'a.supir_id', 'b.id')
                ->leftjoin(DB::raw("trado as c with(readuncommitted)"), 'a.trado_id', 'c.id')
                ->leftjoin(DB::raw("kota as kotadr with(readuncommitted)"), 'a.dari_id', 'kotadr.id')
                ->leftjoin(DB::raw("kota as kotasd with(readuncommitted)"), 'a.sampai_id', 'kotasd.id')
                ->leftjoin(DB::raw($tempselesai . " as d"), 'a.jobtrucking', 'd.jobtrucking');
        } else {
            $queryjob = DB::table('suratpengantar')->from(
                DB::raw("suratpengantar as a with(readuncommitted)")
            )
                ->select(
                    'a.jobtrucking'
                )
                ->where('a.container_id', '=', request()->container_id)
                ->where('a.jenisorder_id', '=', request()->jenisorder_id)
                ->where('a.pelanggan_id', '=', request()->pelanggan_id)
                ->where('a.tarif_id', '=', request()->tarif_id)
                ->whereRaw("isnull(a.jobtrucking,'')<>''")
                ->where('a.sampai_id', '=', $pelabuhan->text);

            DB::table($tempselesai)->insertUsing([
                'jobtrucking',
            ], $queryjob);

            $querydata = DB::table('suratpengantar')->from(
                DB::raw("suratpengantar as a with(readuncommitted)")
            )
                ->select(
                    'a.jobtrucking',
                    'a.tglbukti',
                    'b.namasupir as supir',
                    'c.keterangan as trado',
                    'kotadr.keterangan as kotadari',
                    'kotasd.keterangan as kotasampai',
                    'a.nobukti',

                )
                ->leftjoin(DB::raw("supir as b with(readuncommitted)"), 'a.supir_id', 'b.id')
                ->leftjoin(DB::raw("trado as c with(readuncommitted)"), 'a.trado_id', 'c.id')
                ->leftjoin(DB::raw("kota as kotadr with(readuncommitted)"), 'a.dari_id', 'kotadr.id')
                ->leftjoin(DB::raw("kota as kotasd with(readuncommitted)"), 'a.sampai_id', 'kotasd.id')
                ->leftjoin(DB::raw($tempselesai . " as d"), 'a.jobtrucking', 'd.jobtrucking');
        }



       
        $this->filter($querydata);
       
     
  

        if (isset($query)) {
            $querydata->where('a.container_id', '=', request()->container_id );
            $querydata->where('a.jenisorder_id', '=', request()->jenisorder_id );
            $querydata->where('a.pelanggan_id', '=', request()->pelanggan_id );
            $querydata->where('a.gandengan_id', '=', request()->gandengan_id );
            $querydata->where('a.tarif_id', '=', request()->tarif_id );
            $querydata->whereRaw("isnull(a.jobtrucking,'')<>''");
            $querydata->whereRaw(DB::raw("(a.dari_id=" . $pelabuhan->text . " or a.statuslongtrip=" . $statuslongtrip->id.")"));
            $querydata->whereRaw("isnull(d.jobtrucking,'')=''");

        } else {
            $querydata->where('a.container_id', '=', request()->container_id );
            $querydata->where('a.jenisorder_id', '=', request()->jenisorder_id );
            $querydata->where('a.pelanggan_id', '=', request()->pelanggan_id );
            $querydata->where('a.tarif_id', '=', request()->tarif_id );
            $querydata->whereRaw("isnull(a.jobtrucking,'')<>''");
            $querydata->whereRaw(DB::raw("(a.dari_id=" . $pelabuhan->text . " or a.statuslongtrip=" . $statuslongtrip->id.")"));
            $querydata->whereRaw("isnull(d.jobtrucking,'')=''");
        }

        $this->totalRows = $querydata->count();

        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
       
        $this->sort($querydata);
     
        $this->paginate($querydata);


    
        $data = $querydata->get();

        return $data;
    }

    public function sort($query)
    {
        return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'jobtrucking') {
                            $query = $query->where('a.jobtrucking', 'like', "%$filters[data]%");
                        } elseif ($filters['field'] == 'tglbukti') {
                            $query = $query->whereRaw("format(a.tglbukti,'dd-MM-yyyy') like '%$filters[data]%'");
                        } elseif ($filters['field'] == 'supir') {
                            $query = $query->where('b.namasupir', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'trado') {
                            $query = $query->where('c.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'kotadari') {
                            $query = $query->where('kotadr.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'kotasampai') {
                            $query = $query->where('kotasd.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'nobukti') {
                            $query = $query->where('a.nobukti', 'LIKE', "%$filters[data]%");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'jobtrucking') {
                            $query = $query->OrwhereRaw("(a.jobtrucking like '%$filters[data]%'");
                        } elseif ($filters['field'] == 'tglbukti') {
                            $query = $query->OrwhereRaw("format(a.tglbukti,'dd-MM-yyyy') like '%$filters[data]%'");
                        } elseif ($filters['field'] == 'supir') {
                            $query = $query->Orwhere('b.namasupir', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'trado') {
                            $query = $query->Orwhere('c.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'kotadari') {
                            $query = $query->Orwhere('kotadr.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'kotasampai') {
                            $query = $query->Orwhere('kotasd.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'nobukti') {
                            $query = $query->OrwhereRaw("a.nobukti LIKE '%$filters[data]%')");
                        } else {
                            // $query = $query->Orwhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

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
