<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class StatusOliTradoDetail extends MyModel
{
    use HasFactory;

    public function get($trado_id)
    {

        $trado_id = $trado_id ?? 0;
        // dd($trado_id);
        $this->setRequestParameters();

        $datafilter = request()->filter ?? 0;
        $forExport = request()->forExport ?? false;
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'StatusOliDetailController';

        $temtabel = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

        if ($proses == 'reload') {

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
                $table->integer('trado_id')->nullable();
                $table->string('kodetrado')->nullable();
                $table->dateTime('tglbukti')->nullable();
                $table->integer('stok_id')->nullable();
                $table->string('nobukti', 100)->nullable();
                $table->double('qty', 15, 2)->nullable();
                $table->longText('keterangan')->nullable();
                $table->integer('urut')->nullable();
                $table->double('jarak', 15, 2)->nullable();
                $table->double('selisih', 15, 2)->nullable();
                $table->longText('keterangantambahan')->nullable();
                $table->string('namastok', 1000)->nullable();
            });

            DB::table($temtabel)->insertUsing([
                'trado_id',
                'kodetrado',
                'tglbukti',
                'stok_id',
                'nobukti',
                'qty',
                'keterangan',
                'urut',
                'jarak',
                'selisih',
                'keterangantambahan',
                'namastok',

            ], $this->getdata($trado_id));

            //  dd('test');
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
        $query = DB::table($temtabel)->from(DB::raw($temtabel . " a "))
            ->select(
                'a.trado_id',
                'a.kodetrado',
                'a.tglbukti',
                'a.stok_id',
                'a.nobukti',
                'a.qty',
                'a.keterangan',
                'a.urut',
                'a.jarak',
                'a.selisih',
                'a.keterangantambahan',
                'a.namastok',

            );

        //    dd($query->get());

        if (!$forExport) {

            $this->filter($query);
            // dd('test');
            $this->totalRows = $query->count();

            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
            $query->orderBy('a.id', 'asc');
            $this->paginate($query);
        } else {
            $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp', 'JUDULAN LAPORAN')
                ->where('subgrp', 'JUDULAN LAPORAN')
                ->first();
            $query->addSelect(DB::raw("'" . $getJudul->text . "' as judul"), DB::raw("'LAPORAN STATUS OLI' as judulLaporan"));
        }

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
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else {
                            $query = $query->whereRaw('a' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'tanggal') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->OrwhereRaw('a' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
    public function getdata($trado_id)
    {

        // 

        // dd($trado_id);
        $statuspergantianoli = 346;

        $tempstokoli = '##tempstokoli' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempstokoli, function ($table) {
            $table->unsignedBigInteger('stok_id')->nullable();
        });

        $querystokoli = db::table("stok")->from(db::raw("stok a with (readuncommitted)"))
            ->select(
                'a.id as stok_id'
            )
            ->whereRaw("isnull(statusservicerutin,0)=" . $statuspergantianoli);

        DB::table($tempstokoli)->insertUsing([
            'stok_id',
        ], $querystokoli);

        $statusolitambah = 387;
        $statusoliganti = 388;

        $tempsaldojarak = '##tempsaldojarak' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldojarak, function ($table) {
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->double('jarak', 15, 2)->nullable();
        });

        $querysaldojarak = db::table("saldoreminderpergantian")->from(db::raw("saldoreminderpergantian a with (readuncommitted)"))
            ->select(
                'a.trado_id',
                'a.jarak',

            )
            ->whereRaw("a.statusreminder='Penggantian Oli Mesin'");

        DB::table($tempsaldojarak)->insertUsing([
            'trado_id',
            'jarak',
        ], $querysaldojarak);

        $temppergantian = '##temppergantian' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppergantian, function ($table) {
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->datetime('tglbukti')->nullable();
        });

        $pengeluaranstok_id = 1;

      
        $querypergantian = db::table("pengeluaranstokdetail")->from(db::raw("pengeluaranstokdetail a with (readuncommitted)"))
            ->select(
                'b.trado_id',
                'b.tglbukti',
            )
            ->join(db::raw("pengeluaranstokheader b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(db::raw($tempstokoli . " c "), 'a.stok_id', 'c.stok_id')
            ->where('b.trado_id', $trado_id)
            ->where('b.pengeluaranstok_id', $pengeluaranstok_id)
            ->where('a.statusoli', $statusoliganti);

        DB::table($temppergantian)->insertUsing([
            'trado_id',
            'tglbukti',
        ], $querypergantian);
        // dd('test');
        $parameter = new Parameter();
        $tglsaldo = $parameter->cekText('SALDO', 'SALDO') ?? '1900-01-01';
        $tglsaldo = date('Y-m-d', strtotime($tglsaldo));

        $tempjarak = '##tempjarak' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempjarak, function ($table) {
            $table->dateTime('tglbukti')->nullable();
            $table->double('jarak', 15, 2)->nullable();
        });
        // dd('test');
        $querycekpergantian = db::table($temppergantian)->from(db::raw($temppergantian . " a "))
            ->select(
                'a.tglbukti',
            )
            ->where('a.trado_id', $trado_id)
            ->first();

        if (isset($querycekpergantian)) {
            $tglawal = $querycekpergantian->tglbukti ?? '1900-01-01';
        } else {
            $tglawal = $tglsaldo;

            $queryjarak = db::table($tempsaldojarak)->from(db::raw($tempsaldojarak . " a "))
                ->select(
                    db::raw("'" . $tglawal . "'  as tglbukti"),
                    'a.jarak',
                );

            DB::table($tempjarak)->insertUsing([
                'tglbukti',
                'jarak',
            ], $queryjarak);
        }

        $queryjarak = db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
            ->select(
                'a.tglbukti',
                db::raw("sum(a.jarak) as jarak"),
            )
            ->whereraw("a.tglbukti>='" . $tglawal . "'")
            ->where('a.trado_id', $trado_id)
            ->groupBy('a.tglbukti');

        DB::table($tempjarak)->insertUsing([
            'tglbukti',
            'jarak',

        ], $queryjarak);

        // dd(db::table($tempjarak)->get());

        $tempjarakrekap = '##tempjarakrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempjarakrekap, function ($table) {
            $table->date('tglbukti')->nullable();
            $table->double('jarak', 15, 2)->nullable();
            $table->double('jaraksaldo', 15, 2)->nullable();
        });


        $queryjarakrekap = db::table($tempjarak)->from(db::raw($tempjarak . " a with (readuncommitted)"))
            ->select(
                'a.tglbukti',
                'a.jarak',
                db::raw("isnull(sum(a.jarak) over (
                            order by a.tglbukti
                         ),0) as jaraksaldo"),
            )
            ->orderBy('a.tglbukti', 'asc');

        DB::table($tempjarakrekap)->insertUsing([
            'tglbukti',
            'jarak',
            'jaraksaldo',
        ], $queryjarakrekap);

        $tgl1 = db::table($tempjarakrekap)->from(db::raw($tempjarakrekap . " a "))
            ->select(
                'a.tglbukti',
            )
            ->orderBy('a.tglbukti', 'asc')
            ->first()->tglbukti ?? '1900/1/1';

        $tgl2 = date('Y-m-d');

        // dd(db::table($tempjarakrekap)->get());
        // dd($tgl1,$tgl2);
        while ($tgl1 <= $tgl2) {

            $querycek = db::table($tempjarakrekap)->from(db::raw($tempjarakrekap . " a "))
                ->select(
                    'a.tglbukti'
                )
                ->where('a.tglbukti', $tgl1)
                ->first();
            if (!isset($querycek)) {
                $tglkurang=date('Y-m-d', strtotime($tgl1 . ' -1 day'));
                $jarakold = db::table($tempjarakrekap)->from(db::raw($tempjarakrekap . " a "))
                    ->select(
                        'a.jaraksaldo',
                    )
                    ->where('a.tglbukti',$tglkurang )
                    ->first()->jaraksaldo ?? 0;

                    // dump($tglkurang,$tgl1, $jarakold );

                DB::table($tempjarakrekap)->insert(
                    [
                        'tglbukti' => $tgl1,
                        'jarak' => 0,
                        'jaraksaldo' => $jarakold,
                    ]
                );
            }

            $tgl1 = date('Y-m-d', strtotime($tgl1 . ' +1 day'));
        }
        // dd('test');
        $temptambah = '##temptambah' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temptambah, function ($table) {
            $table->id();
            $table->integer('trado_id')->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->integer('stok_id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->double('qty', 15, 2)->nullable();
            $table->longtext('keterangan')->nullable();
            $table->double('jarak', 15, 2)->nullable();
            $table->double('selisih', 15, 2)->nullable();
        });

        // dd(db::table($tempjarakrekap)->get());
       
        $querytambah = db::table("pengeluaranstokdetail")->from(db::raw("pengeluaranstokdetail a "))
            ->select(
                'b.trado_id',
                'b.tglbukti',
                'a.stok_id',
                'b.nobukti',
                'a.qty',
                'a.keterangan',
                'd.jaraksaldo'
            )
            ->join(db::raw("pengeluaranstokheader b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(db::raw($tempstokoli . " c "), 'a.stok_id', 'c.stok_id')
            ->leftjoin(db::raw($tempjarakrekap . " d "), 'b.tglbukti', 'd.tglbukti')
            ->where('b.trado_id', $trado_id)
            ->where('b.pengeluaranstok_id', $pengeluaranstok_id)
            ->where('a.statusoli', $statusolitambah)
            ->whereraw("b.tglbukti>='" . $tglawal . "'")
            ->orderby('b.tglbukti', 'asc');

            
            //  dd($querytambah->get());
        DB::table($temptambah)->insertUsing([
            'trado_id',
            'tglbukti',
            'stok_id',
            'nobukti',
            'qty',
            'keterangan',
            'jarak'
        ], $querytambah);


        $temptambahrekap = '##temptambahrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temptambahrekap, function ($table) {
            $table->id();
            $table->integer('trado_id')->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->integer('stok_id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->double('qty', 15, 2)->nullable();
            $table->longtext('keterangan')->nullable();
            $table->integer('urut')->nullable();
            $table->double('jarak', 15, 2)->nullable();
            $table->double('selisih', 15, 2)->nullable();
        });

        $querytambahrekap = db::table($temptambah)->from(db::raw($temptambah . " a "))
            ->select(
                'a.trado_id',
                'a.tglbukti',
                'a.stok_id',
                'a.nobukti',
                'a.qty',
                'a.keterangan',
                db::raw("row_number() Over(Order By a.id ) as urut"),
                'a.jarak',
                db::raw("SUM(ISNULL(a.jarak, 0) - isnull(a.selisih,0)) OVER (ORDER BY a.id ASC) AS selisih")
            )
            ->orderby('a.id', 'asc');

            // dd('test');
        DB::table($temptambahrekap)->insertUsing([
            'trado_id',
            'tglbukti',
            'stok_id',
            'nobukti',
            'qty',
            'keterangan',
            'urut',
            'jarak',
            'selisih',

        ], $querytambahrekap);

        $query = db::table($temptambahrekap)->from(db::raw($temptambahrekap . " a "))
            ->select(
                'a.trado_id',
                'd.kodetrado',
                'a.tglbukti',
                'a.stok_id',
                'a.nobukti',
                'a.qty',
                'a.keterangan',
                'a.urut',
                'a.jarak',
                'a.selisih',
                db::raw("a.nobukti+' '+format(a.tglbukti,'dd-MM-yyyy')+' Penambahan oli ke-'+trim(str(a.urut))+', Selisih KM : '+format(a.selisih,'#,#0.00')+', Km Ke : '+format(a.jarak,'#,#0.00')+' ( '+format(a.qty,'#,#0.00')+' '+c.satuan+' ) '+
	                ' Keterangan : '+a.keterangan as keterangantambahan"),
                'b.namastok',
            )
            ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id')
            ->join(db::raw("satuan c with (readuncommitted)"), 'b.satuan_id', 'c.id')
            ->leftJoin(db::raw("trado d with (readuncommitted)"), 'a.trado_id', 'd.id')
            ->orderBy('a.id', 'asc');


        // 
        // dd($query->get());
        return $query;
    }
}
