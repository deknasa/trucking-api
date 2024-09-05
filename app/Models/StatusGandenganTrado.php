<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class StatusGandenganTrado extends MyModel
{
    use HasFactory;

    public function get()
    {

        $this->setRequestParameters();

        $tgl = date('Y-m-d', strtotime(request()->tgldari));

        $temptrip = '##temptrip' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temptrip, function ($table) {
            $table->string('nobukti', 50);
            $table->date('tglbukti');
            $table->integer('gandengan_id');
            $table->integer('dari_id');
            $table->integer('sampai_id');
            $table->longtext('pelanggan');
            $table->longtext('sampai');
            $table->longtext('gudang');
            $table->integer('urut');
        });

        $querytrip = db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
            ->select(
                'a.nobukti',
                'a.tglbukti',
                'a.gandengan_id',
                'a.dari_id',
                'a.sampai_id',
                db::raw("row_number() Over(partition BY A.gandengan_id Order By format(a.tglbukti,'yyyyMMdd')+trim(A.nobukti) desc ) as urut"),
                'b.namapelanggan as pelanggan',
                'c.kodekota as sampai',
                'a.gudang'
            )
            ->join(db::raw("pelanggan b with (readuncommitted)"), 'a.pelanggan_id', 'b.id')
            ->join(db::raw("kota c with (readuncommitted)"), 'a.sampai_id', 'c.id')
            ->whereraw("a.tglbukti<='" . $tgl . "' and A.tglbukti>=('" . $tgl . "'-60)")
            ->whereRaw("isnull(a.gandengan_id,0)<>0");

        DB::table($temptrip)->insertUsing([
            'nobukti',
            'tglbukti',
            'gandengan_id',
            'dari_id',
            'sampai_id',
            'urut',
            'pelanggan',
            'sampai',
            'gudang',
        ], $querytrip);


        DB::delete(DB::raw("delete " . $temptrip . " WHERE urut<>1"));

        $tempritasi = '##tempritasi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempritasi, function ($table) {
            $table->string('nobukti', 50);
            $table->string('nobuktitrip', 50);
            $table->integer('statusritasi');
            $table->integer('gandengan_id');
            $table->integer('dari_id');
            $table->integer('sampai_id');
            $table->longtext('pelanggan');
            $table->longtext('sampai');
            $table->longtext('gudang');
            $table->integer('urut');
        });

        $queryritasi = db::table("ritasi")->from(db::raw("ritasi a with (readuncommitted)"))
            ->select(
                'a.nobukti',
                'a.suratpengantar_nobukti as nobuktitrip',
                'a.statusritasi',
                'b.gandengan_id',
                'a.dari_id',
                'a.sampai_id',
                'row_number() Over(partition BY b.gandengan_id Order By a.nobukti desc ) as urut',
                'b.pelanggan',
                'c.kodekota as sampai',
                'b.gudang'

            )
            ->join(db::raw($temptrip . " b with (readuncommitted)"), 'a.suratpengantar_nobukti', 'b.nobukti')
            ->join(db::raw("kota c with (readuncommitted)"), 'a.sampai_id', 'c.id')
            ->whereRaw("a.statusritasi in(91,92)");


        DB::table($tempritasi)->insertUsing([
            'nobukti',
            'nobuktitrip',
            'statusritasi',
            'gandengan_id',
            'dari_id',
            'sampai_id',
            'urut',
            'pelanggan',
            'sampai',
            'gudang',
        ], $queryritasi);

        DB::delete(DB::raw("delete " . $tempritasi . " WHERE urut<>1"));

        $tempgandengan = '##tempgandengan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempgandengan, function ($table) {
            $table->integer('id');
            $table->longtext('kodegandengan');
            $table->longtext('keterangan');
        });

        $querygandengan = db::table("gandengan")->from(db::raw("gandengan a with (readuncommitted)"))
            ->select(
                'a.id',
                'a.kodegandengan',
                'a.keterangan',
            )
            ->whereRaw("statusaktif=1");

        DB::table($tempgandengan)->insertUsing([
            'id',
            'kodegandengan',
            'keterangan',
        ], $querygandengan);

        $tempdatagandengan = '##tempdatagandengan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdatagandengan, function ($table) {
            $table->string('nobukti', 50);
            $table->string('nobuktiritasi', 50);
            $table->date('tglbukti');
            $table->longtext('namagandengan');
            $table->longtext('keterangan');
            $table->longtext('lokasi');
            $table->integer('gandengan_id');
            $table->longtext('pelanggan');
            $table->longtext('gudang');
        });

        $querydatagandengan = db::table($tempgandengan)->from(db::raw($tempgandengan . " c "))
            ->select(
                'a.nobukti',
                'a.tglbukti',
                db::raw("isnull(c.kodegandengan,'') as namagandengan"),
                db::raw("(CASE WHEN  
            (case when isnull(b.sampai_id,0)=0 then 
                 (case when a.sampai_id=2 then 'GANTUNG' ELSE isnull(d.kodekota,'')  END)
                 else isnull(e.kodekota,'') end)='' THEN 'KANDANG'
            else  (case when isnull(b.sampai_id,0)=0 then 
            (case when a.sampai_id=2 then 'GANTUNG' ELSE isnull(d.kodekota,'')  END)
            else isnull(e.kodekota,'') end) end) as lokasi"),
                'c.keterangan',
                'c.id as gandengan_id',
                'a.pelanggan',
                db::raw("(case when isnull(b.sampai_id,0)=0 then ''  else a.gudang end) as gudang"),
                db::raw("isnull(b.nobukti,'') as nobuktiritasi")
            )

            ->leftjoin(db::raw($temptrip . " a "), 'c.id', 'a.gandengan_id')
            ->leftjoin(db::raw($tempritasi . " b "), 'a.nobukti', 'b.nobuktitrip')
            ->leftjoin(db::raw("kota d with (readuncommitted)"), 'a.sampai_id', 'd.id')
            ->leftjoin(db::raw("kota e with (readuncommitted)"), 'b.sampai_id', 'e.id')
            ->orderBY(db::raw("isnull(c.kodegandengan,'')"), 'asc');

        DB::table($tempdatagandengan)->insertUsing([
            'nobukti',
            'tglbukti',
            'namagandengan',
            'lokasi',
            'keterangan',
            'gandengan_id',
            'pelanggan',
            'gudang',
            'nobuktiritasi',
        ], $querydatagandengan);


        $tempdatagandenganlist = '##tempdatagandenganlist' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdatagandenganlist, function ($table) {
            $table->id();
            $table->string('nobukti', 50);
            $table->string('nobukti', 50);
            $table->date('tglbukti');
            $table->longtext('gandengan');
            $table->longtext('nopol');
            $table->longtext('namasupir');
            $table->longtext('container');
            $table->longtext('gudang');
            $table->longtext('kodeagen');
            $table->longtext('kodejeniscontainer');
            $table->longtext('kodestatuscontainer');
            $table->longtext('lokasi');
            $table->longtext('pelanggan');
        });


        $querylist = db::table($tempdatagandengan)->from(db::raw($tempdatagandengan . " a"))
            ->select(
                'a.nobukti',
                'a.tglbukti',
                db::raw("a.namagandengan+' ( '+trim(a.keterangan)+' ) ' as gandengan"),
                'c.kodetrado as nopol',
                'd.namasupir',
                db::raw("(case when isnull(i.nobukti,'')='' then e.kodecontainer  else '' end )as container"),
                'a.gudang',
                db::raw("(case when isnull(i.nobukti,'')='' then f.kodeagen  else '' end )as kodeagen"),
                db::raw("(case when isnull(i.nobukti,'')='' then g.kodejenisorder  else '' end )as kodejenisorder"),
                db::raw("(case when isnull(i.nobukti,'')='' then h.kodestatuscontainer  else '' end )as kodestatuscontainer"),
                'a.lokasi',
                db::raw("(case when isnull(i.nobukti,'')='' then a.pelanggan  else '' end )as pelanggan")
            )
            ->leftjoin(db::raw("suratpengantar b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->leftjoin(db::raw("trado c with (readuncommitted)"), 'b.trado_id', 'c.id')
            ->leftjoin(db::raw("supir d with (readuncommitted)"), 'b.supir_id', 'd.id')
            ->leftjoin(db::raw("container e with (readuncommitted)"), 'b.container_id', 'e.id')
            ->leftjoin(db::raw("agen f with (readuncommitted)"), 'b.agen_id', 'f.id')
            ->leftjoin(db::raw("jenisorder g with (readuncommitted)"), 'b.jenisorder_id', 'g.id')
            ->leftjoin(db::raw("statuscontainer h with (readuncommitted)"), 'b.statusorder_id', 'h.id')
            ->leftjoin(db::raw("ritasi i with (readuncommitted)"), 'b.statusorder_id', 'h.id')
            ->leftjoin(DB::raw("ritasi as i with (readuncommitted)"), function ($join) {
                $join->on('a.nobukti', '=', 'i.suratpengantar_nobukti');
                $join->on('a.nobuktiritasi', '=', 'i.nobukti');
            })
            ->orderBY('a.namagandengan', 'asc');

        DB::table($tempdatagandenganlist)->insertUsing([
            'nobukti',
            'tglbukti',
            'gandengan',
            'nopol',
            'namasupir',
            'container',
            'gudang',
            'kodeagen',
            'kodejeniscontainer',
            'kodestatuscontainer',
            'lokasi',
            'pelanggan',
        ], $querylist);


        $query = db::table($tempdatagandenganlist)->from(db::raw($tempdatagandenganlist . " a"))
            ->select(
                'a.nobukti',
                'a.tglbukti',
                'a.gandengan',
                'a.nopol',
                'a.namasupir',
                'a.container',
                'a.gudang',
                'a.kodeagen',
                'a.kodejeniscontainer',
                'a.kodestatuscontainer',
                'a.lokasi',
                'a.pelanggan',
            );
        // 

        $this->sort($query);
        $this->filter($query);
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $this->paginate($query);

        $data = $query->get();
        return $data;
    }

    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {

                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }
                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {

                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
