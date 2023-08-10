<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class StatusGandenganTruck extends Model
{
    use HasFactory;

    public function get($periode)
    {

        $this->setRequestParameters();
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'StatusGandenganTruckController';

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
                $table->string('nogandengan',500)->nullable();
                $table->string('container', 500)->nullable();
                $table->string('gudang', 50)->nullable();
                $table->string('lokasiawal', 1000)->nullable();
                $table->string('orderan', 1000)->nullable();
                $table->string('sp', 100)->nullable();
                $table->string('jenis', 1000)->nullable();
            });

            DB::table($temtabel)->insertUsing([
                'id',
                'nogandengan',
                'container', 
                'gudang', 
                'lokasiawal', 
                'orderan', 
                'sp', 
                'jenis', 
            ], $this->getdata($periode));
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

     
        $query = db::table($temtabel)->from(
            db::raw($temtabel . " a")
        )
            ->select(
                'a.id',
                'a.nogandengan',
                'a.container', 
                'a.gudang', 
                'a.lokasiawal', 
                'a.orderan', 
                'a.sp', 
                'a.jenis', 
            );
        // dd('test');
        $this->totalRows = $query->count();

        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);

        $this->filter($query);

        $this->paginate($query);
        $data = $query->get();
        return $data;

        // $data = [
        //     0 => [
        //         'id' => 1,
        //         'nogandengan' => 'GANDENGAN T-03 PANJANG',
        //         'container' => '40"',
        //         'gudang' => 'TITI KUNING',
        //         'lokasiawal' => 'BELAWAN',
        //         'orderan' => 'BONGKARAN',
        //         'sp' => 'FULL',
        //         'jenis' => 'PANJANG'
        //     ],
        //     1 => [
        //         'id' => 2,
        //         'nogandengan' => 'GANDENGAN T-07 PANJANG',
        //         'container' => '',
        //         'gudang' => 'KANDANG',
        //         'lokasiawal' => 'BELAWAN RANGKA',
        //         'orderan' => '',
        //         'sp' => '',
        //         'jenis' => 'PANJANG'
        //     ]
        // ];

        // return $data;
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
                        // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        $query = $query->whereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->OrwhereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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


    public function getdata($periode)
    {

        $tempdata = '##tempdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdata, function ($table) {
            $table->integer('gandengan_id')->nullable();
            $table->integer('dari_id')->nullable();
            $table->integer('sampai_id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('noritasi', 50)->nullable();
            $table->string('kodegandengan', 1000)->nullable();
            $table->string('kotadari', 1000)->nullable();
            $table->string('kotasampai', 100)->nullable();
            $table->integer('urut')->nullable();
            $table->string('gandenganurut', 1000)->nullable();
        });

        $statusturunrangka = DB::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))->select('a.id')
            ->where('grp', 'STATUS RITASI')
            ->where('subgrp', 'STATUS RITASI')
            ->where('text', 'TURUN RANGKA')
            ->first()->id;

        $statuspulangrangka = DB::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))->select('a.id')
            ->where('grp', 'STATUS RITASI')
            ->where('subgrp', 'STATUS RITASI')
            ->where('text', 'PULANG RANGKA')
            ->first()->id;

        $statusaktif = DB::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))->select('a.id')
            ->where('grp', 'STATUS AKTIF')
            ->where('subgrp', 'STATUS AKTIF')
            ->where('text', 'AKTIF')
            ->first()->id;
        $querydata = DB::table("ritasi")->from(
            DB::raw("ritasi a with (readuncommitted)")
        )
            ->select(
                'a1.gandengan_id',
                'a.dari_id',
                'a.sampai_id',
                'a.suratpengantar_nobukti as nobukti',
                'a.tglbukti',
                'a.nobukti as noritasi',
                'b.kodegandengan',
                'c.kodekota',
                'd.kodekota',
                db::raw("1 as urut"),
                db::raw("replicate('0',3-len(ltrim(rtrim(str(a1.gandengan_id)))))+ltrim(rtrim(str(a1.gandengan_id)))+format(a.tglbukti,'yyyyMMdd')+a.suratpengantar_nobukti+'001' as gandenganurut")
            )
            ->join(DB::raw("suratpengantar as a1 with (readuncommitted)"), 'a.suratpengantar_nobukti', 'a1.nobukti')
            ->join(DB::raw("gandengan as b with (readuncommitted)"), 'a1.gandengan_id', 'b.id')
            ->join(DB::raw("kota as c with (readuncommitted)"), 'a.dari_id', 'c.id')
            ->join(DB::raw("kota as d with (readuncommitted)"), 'a.sampai_id', 'd.id')
            ->whereRaw("a.tglbukti<='" . $periode . "'")
            ->whereRaw("a.statusritasi=" . $statusturunrangka);

        DB::table($tempdata)->insertUsing([
            'gandengan_id',
            'dari_id',
            'sampai_id',
            'nobukti',
            'tglbukti',
            'noritasi',
            'kodegandengan',
            'kotadari',
            'kotasampai',
            'urut',
            'gandenganurut',
        ], $querydata);


        $querydata = DB::table("suratpengantar")->from(
            DB::raw("suratpengantar a with (readuncommitted)")
        )
            ->select(
                'a.gandengan_id',
                'a.dari_id',
                'a.sampai_id',
                'a.nobukti',
                'a.tglbukti',
                db::raw("'' as noritasi"),
                'b.kodegandengan',
                'c.kodekota',
                'd.kodekota',
                db::raw("(case when a.dari_id=1 then 2 
                         when a.dari_id=103 then 3
                         when a.sampai_id=1 then 4
                         when a.sampai_id=103 then 5
                         else 6 end)
                    
                    as urut"),
                db::raw("replicate('0',3-len(ltrim(rtrim(str(a.gandengan_id)))))+ltrim(rtrim(str(a.gandengan_id)))+format(a.tglbukti,'yyyyMMdd')+a.nobukti+
                         '00'+ltrim(rtrim((case when a.dari_id=1 then 2 
                         when a.dari_id=103 then 3
                         when a.sampai_id=1 then 4
                         when a.sampai_id=103 then 5
                         else 6 end))) as gandenganurut")
            )
            ->join(DB::raw("gandengan as b with (readuncommitted)"), 'a.gandengan_id', 'b.id')
            ->join(DB::raw("kota as c with (readuncommitted)"), 'a.dari_id', 'c.id')
            ->join(DB::raw("kota as d with (readuncommitted)"), 'a.sampai_id', 'd.id')
            ->whereRaw("a.tglbukti<='" . $periode . "'");

        DB::table($tempdata)->insertUsing([
            'gandengan_id',
            'dari_id',
            'sampai_id',
            'nobukti',
            'tglbukti',
            'noritasi',
            'kodegandengan',
            'kotadari',
            'kotasampai',
            'urut',
            'gandenganurut',
        ], $querydata);

        $querydata = DB::table("ritasi")->from(
            DB::raw("ritasi a with (readuncommitted)")
        )
            ->select(
                'a1.gandengan_id',
                'a.dari_id',
                'a.sampai_id',
                'a.suratpengantar_nobukti as nobukti',
                'a.tglbukti',
                'a.nobukti as noritasi',
                'b.kodegandengan',
                'c.kodekota',
                'd.kodekota',
                db::raw("10 as urut"),
                db::raw("replicate('0',3-len(ltrim(rtrim(str(a1.gandengan_id)))))+ltrim(rtrim(str(a1.gandengan_id)))+format(a.tglbukti,'yyyyMMdd')+a.suratpengantar_nobukti+'010' as gandenganurut")
            )

            ->join(DB::raw("suratpengantar as a1 with (readuncommitted)"), 'a.suratpengantar_nobukti', 'a1.nobukti')
            ->join(DB::raw("gandengan as b with (readuncommitted)"), 'a1.gandengan_id', 'b.id')
            ->join(DB::raw("kota as c with (readuncommitted)"), 'a.dari_id', 'c.id')
            ->join(DB::raw("kota as d with (readuncommitted)"), 'a.sampai_id', 'd.id')
            ->whereRaw("a.tglbukti<='" . $periode . "'")
            ->whereRaw("a.statusritasi=" . $statuspulangrangka);

        DB::table($tempdata)->insertUsing([
            'gandengan_id',
            'dari_id',
            'sampai_id',
            'nobukti',
            'tglbukti',
            'noritasi',
            'kodegandengan',
            'kotadari',
            'kotasampai',
            'urut',
            'gandenganurut',
        ], $querydata);

        $tempdatarekap = '##tempdatarekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdatarekap, function ($table) {
            $table->integer('gandengan_id')->nullable();
            $table->integer('dari_id')->nullable();
            $table->integer('sampai_id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('noritasi', 50)->nullable();
            $table->string('kodegandengan', 1000)->nullable();
            $table->string('kotadari', 1000)->nullable();
            $table->string('kotasampai', 100)->nullable();
            $table->integer('urut')->nullable();
            $table->string('gandenganurut', 1000)->nullable();
            $table->integer('s_index')->nullable();
        });

        $querydatarekap = DB::table($tempdata)->from(
            DB::raw($tempdata . " a ")
        )
            ->select(
                'a.gandengan_id',
                'a.dari_id',
                'a.sampai_id',
                'a.nobukti',
                'a.tglbukti',
                'a.noritasi',
                'a.kodegandengan',
                'a.kotadari',
                'a.kotasampai',
                'a.urut',
                'a.gandenganurut',
                db::raw("ROW_NUMBER() OVER(PARTITION BY isnull(a.gandengan_id,0) ORDER BY a.gandenganurut desc) as s_index")
            )

            ->orderBY('a.gandengan_id', 'asc');

        DB::table($tempdatarekap)->insertUsing([
            'gandengan_id',
            'dari_id',
            'sampai_id',
            'nobukti',
            'tglbukti',
            'noritasi',
            'kodegandengan',
            'kotadari',
            'kotasampai',
            'urut',
            'gandenganurut',
            's_index'
        ], $querydatarekap);

        DB::table($tempdatarekap, 'a')
            ->whereRaw("a.s_index<>1")
            ->delete();


        $query = DB::table('gandengan')->from(
            DB::raw("gandengan a with (readuncommitetd)")
        )
            ->select(
                'a.id',
                'a.kodegandengan as nogandengan',
                db::raw("isnull(d.kodecontainer,'') as container"),
                db::raw("isnull(b.kotasampai,'') as gudang"),
                db::raw("isnull(b.kotadari,'') as lokasiawal"),
                db::raw("isnull(e.keterangan,'')  as orderan"), 
                db::raw("isnull(f.kodestatuscontainer,'')  as sp"),
                'a.keterangan as jenis'
            )
            ->leftjoin(DB::raw($tempdatarekap . " as b "), 'a.id', 'b.gandengan_id')
            ->leftjoin(DB::raw("suratpengantar as c with (readuncommitted)"), 'b.nobukti', 'c.nobukti')
            ->leftjoin(DB::raw("container as d with (readuncommitted)"), 'c.container_id', 'd.id')
            ->leftjoin(DB::raw("jenisorder as e with (readuncommitted)"), 'c.jenisorder_id', 'e.id')
            ->leftjoin(DB::raw("statuscontainer as f with (readuncommitted)"), 'c.statuscontainer_id', 'f.id')
            ->where('a.statusaktif', '=', $statusaktif)
            ->orderBY('a.id', 'asc');

            return $query;
    }
}
