<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SuratPengantarApprovalInputTrip extends MyModel
{
    use HasFactory;

    protected $table = 'suratpengantarapprovalinputtrip';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'tglbukti' => 'date:d-m-Y',
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];
    public function get()
    {
        $this->setRequestParameters();
        
        $query = SuratPengantarApprovalInputTrip::from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
                "suratpengantarapprovalinputtrip.id",
                "suratpengantarapprovalinputtrip.tglbukti",
                "suratpengantarapprovalinputtrip.jumlahtrip",
                "suratpengantarapprovalinputtrip.modifiedby",
                "suratpengantarapprovalinputtrip.created_at",
                "suratpengantarapprovalinputtrip.updated_at",
            );

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
                        if ($filters['field'] == 'tglbukti') {
                            $query = $query->where($this->table.'.tglbukti', '=', date("Y-m-d",strtotime($filters['data'])));
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'tglbukti') {
                            $query = $query->orWhere($this->table.'.tglbukti', '=', date("Y-m-d",strtotime($filters['data'])));
                        } else {
                            $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function isTanggalAvaillable(){

        $tutupbuku = DB::table('parameter')->from(
            DB::raw("parameter as a with (readuncommitted)")
        )
            ->where('a.grp', '=', 'TUTUP BUKU')
            ->where('a.subgrp', '=', 'TUTUP BUKU')
            ->first();
        // $bukaAbsensi = DB::table('suratpengantar')
        // ->select('suratpengantar.tglbukti', DB::raw('COUNT(suratpengantar.tglbukti) as data_tanggal'), 'subquery.jumlahtrip')
        // ->join(DB::raw('(SELECT tglbukti, SUM(jumlahtrip) as jumlahtrip FROM suratpengantarapprovalinputtrip GROUP BY tglbukti) AS subquery'), 'suratpengantar.tglbukti', '=', 'subquery.tglbukti')
        // ->groupBy('suratpengantar.tglbukti', 'subquery.jumlahtrip')
        // ->where('suratpengantar.tglbukti', '>', '2022-12-25')

        // ->havingRaw('COUNT(suratpengantar.tglbukti) < subquery.jumlahtrip')
        // ->get();
    
            
        $bukaAbsensi = DB::table('suratpengantar')
                ->join(DB::raw('(SELECT tglbukti, SUM(jumlahtrip) as jumlahtrip FROM suratpengantarapprovalinputtrip GROUP BY tglbukti) as subquery'), function ($join) {
                    $join->on('suratpengantar.tglbukti', '=', 'subquery.tglbukti');
                })
                ->select('suratpengantar.tglbukti', DB::raw('COUNT(suratpengantar.tglbukti) as data_tanggal'), 'subquery.jumlahtrip')
                ->where('suratpengantar.tglbukti', '>', $tutupbuku->text)
                ->groupBy('suratpengantar.tglbukti', 'subquery.jumlahtrip')
                ->havingRaw('COUNT(suratpengantar.tglbukti) < subquery.jumlahtrip')

                ->get();



           return $bukaAbsensi;
        }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->date('tglbukti')->nullable();
            $table->double('jumlahtrip')->nullable();
            $table->string('modifiedby', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });


        $query = DB::table($modelTable);
        $query = SuratPengantarApprovalInputTrip::from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
                "suratpengantarapprovalinputtrip.id",
                "suratpengantarapprovalinputtrip.tglbukti",
                "suratpengantarapprovalinputtrip.jumlahtrip",
                "suratpengantarapprovalinputtrip.modifiedby",
                "suratpengantarapprovalinputtrip.created_at",
                "suratpengantarapprovalinputtrip.updated_at",
            );

        $query = $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing([
            'id',
            'tglbukti',
            'jumlahtrip',
            'modifiedby',
            'created_at', 'updated_at'
        ], $models);

        return $temp;
    }
    
}
