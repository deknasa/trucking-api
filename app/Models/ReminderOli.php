<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ReminderOli extends MyModel
{
    use HasFactory;

    public function get($status)
    {
        $this->setRequestParameters();


        // dump(request()->filter);
        // dd($filter->id);

        // if (request()->filter == $filter->id) {
        // dd('test');
        // dd($filter->text);
        $datafilter = request()->filter ?? 0;
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'ReminderOliController';





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
                $table->id();
                $table->longText('nopol')->nullable();
                $table->date('tanggal')->nullable();
                $table->string('status', 100)->nullable();
                $table->double('km', 15, 2)->nullable();
                $table->double('kmperjalanan', 15, 2)->nullable();

            });

                DB::table($temtabel)->insertUsing([
                    'nopol',
                    'tanggal',
                    'status',
                    'km',
                    'kmperjalanan',
                ], $this->getdata($status));

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
            DB::raw(DB::raw($temtabel) . " a with (readuncommitted)")
        )
            ->select(
                'a.nopol',
                'a.tanggal',
                'a.status',
                'a.km',
                'a.kmperjalanan',
            );


       

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
        // dd($query->toSql());
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();


        // } else {
        //     $data = [];
        // }

        return $data;
    }

    public function getdata($status) {
        
        $batasgardan = Parameter::where('grp', 'BATAS PERGANTIAN OLI GARDAN')->where('subgrp', 'BATAS PERGANTIAN OLI GARDAN')->first()->text;
        $bataspersneling = Parameter::where('grp', 'BATAS PERGANTIAN OLI PERSNELING')->where('subgrp', 'BATAS PERGANTIAN OLI PERSNELING')->first()->text;
        $batasmesin = Parameter::where('grp', 'BATAS PERGANTIAN OLI MESIN')->where('subgrp', 'BATAS PERGANTIAN OLI MESIN')->first()->text;
        $batassaringanhawa = Parameter::where('grp', 'BATAS PERGANTIAN SARINGAN HAWA')->where('subgrp', 'BATAS PERGANTIAN SARINGAN HAWA')->first()->text;

        $tempstatus = '##tempstatus' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempstatus, function ($table) {
            $table->longText('status')->nullable();
            $table->double('batas')->nullable();
        });        

        DB::table($tempstatus)->insert([
            'status' => 'PENGGANTIAN OLI GARDAN',
            'batas' => $batasgardan,
        ]);

        DB::table($tempstatus)->insert([
            'status' => 'PENGGANTIAN OLI PERSNELING',
            'batas' => $bataspersneling,
        ]);

        DB::table($tempstatus)->insert([
            'status' => 'PENGGANTIAN OLI MESIN',
            'batas' => $batasmesin,
        ]);        

        DB::table($tempstatus)->insert([
            'status' => 'PENGGANTIAN SARINGAN HAWA',
            'batas' => $batassaringanhawa,
        ]);          

        $query
    }

    public function filter($query, $relationFields = [])
    {

        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                     
                            // $query = $query->where('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw('a' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {

                                // $query->orWhere('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw('a' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
