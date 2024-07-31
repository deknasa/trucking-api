<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\RunningNumberService;

class PendapatanSupirHeader extends MyModel
{
    use HasFactory;

    protected $table = 'pendapatansupirheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {
        $this->setRequestParameters();
        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';
        $query = DB::table($this->table)->from(DB::raw("pendapatansupirheader with (readuncommitted)"))
            ->select(
                'pendapatansupirheader.id',
                'pendapatansupirheader.nobukti',
                'pendapatansupirheader.tglbukti',
                'bank.namabank as bank_id',
                'pendapatansupirheader.tgldari',
                'pendapatansupirheader.tglsampai',
                'akunpusat.keterangancoa as coa',
                'statusapproval.memo as statusapproval',
                'pendapatansupirheader.userapproval',
                DB::raw('(case when (year(pendapatansupirheader.tglapproval) <= 2000) then null else pendapatansupirheader.tglapproval end ) as tglapproval'),
                DB::raw('(case when (year(pendapatansupirheader.tglbukacetak) <= 2000) then null else pendapatansupirheader.tglbukacetak end ) as tglbukacetak'),
                'statuscetak.memo as statuscetak',
                'pendapatansupirheader.userbukacetak',
                'pendapatansupirheader.jumlahcetak',
                'pendapatansupirheader.pengeluaran_nobukti',
                'supir.namasupir as supir_id',
                'pendapatansupirheader.modifiedby',
                'pendapatansupirheader.created_at',
                'pendapatansupirheader.updated_at',
                db::raw("cast((format(pengeluaranheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpengeluaranheader"),
                db::raw("cast(cast(format((cast((format(pengeluaranheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpengeluaranheader"),
                'pengeluaranheader.bank_id as pengeluaranbank_id',

            )
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pendapatansupirheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("pengeluaranheader with (readuncommitted)"), 'pendapatansupirheader.pengeluaran_nobukti', '=', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pendapatansupirheader.supir_id', 'supir.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pendapatansupirheader.coa', 'akunpusat.coa')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'pendapatansupirheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'pendapatansupirheader.statuscetak', 'statuscetak.id');
        if (request()->tgldari && request()->tglsampai) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(pendapatansupirheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(pendapatansupirheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("pendapatansupirheader.statuscetak", $statusCetak);
        }

        // dd($query->ToSql());
        $this->totalRows = $query->count();

        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function cekvalidasiaksi($nobukti)
    {
        $error = new Error();
        $keteranganerror = $error->cekKeteranganError('SAPP') ?? '';
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';


        $jurnal = DB::table('pendapatansupirheader')
            ->from(
                DB::raw("pendapatansupirheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.pengeluaran_nobukti'
            )
            ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.pengeluaran_nobukti', 'b.nobukti')
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($jurnal)) {
            $data = [
                'kondisi' => true,
                'keterangan' =>  'No Bukti <b>'. $nobukti . '</b><br>' .$keteranganerror.'<br> No Bukti Approval Jurnal <b>'. $jurnal->pengeluaran_nobukti .'</b> <br> '.$keterangantambahanerror,
                'kodeerror' => 'SAP'
            ];
            goto selesai;
        }


        $data = [
            'kondisi' => false,
            'keterangan' => '',
        ];
        selesai:
        return $data;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('bank', 255)->nullable();
        });


        $bank = DB::table('bank')->from(
            DB::raw('bank with (readuncommitted)')
        )
            ->select(
                'id as bank_id',
                'namabank as bank',

            )
            ->where('tipe', '=', 'KAS')
            ->first();


        DB::table($tempdefault)->insert(
            ["bank_id" => $bank->bank_id, "bank" => $bank->bank]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'bank_id',
                'bank'
            );

        $data = $query->first();
        return $data;
    }
    public function gettrip($tgldari, $tglsampai, $supir_id, $id, $aksi = null)
    {
        $tempsaldopendapatan = '##tempsaldopendapatan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldopendapatan, function ($table) {
            $table->integer('pendapatansupir_id')->nullable();
            $table->integer('supir_id')->nullable();
            $table->string('gajisupir_nobukti', 1000)->nullable();
            $table->string('suratpengantar_nobukti', 1000)->nullable();
            $table->date('tgl_trip')->nullable();
            $table->string('tgl_ric', 1000)->nullable();
            $table->integer('dari_id')->nullable();
            $table->integer('sampai_id')->nullable();
            $table->double('nominal')->nullable();
            $table->double('gajikenek')->nullable();
            $table->longText('keterangan')->nullable();
        });

        $querysaldopendapatan = DB::table('saldopendapatansupir')->from(
            db::raw("saldopendapatansupir a with (readuncommitted)")
        )
            ->select(
                DB::raw("0 as pendapatansupir_id"),
                'a.supir_id',
                'a.gajisupir_nobukti',
                'a.suratpengantar_nobukti',
                'a.suratpengantar_tglbukti as tgl_trip',
                DB::raw("null as tgl_ric"),
                'a.dari_id',
                'a.sampai_id',
                'a.nominal',
                DB::raw("isnull(a.gajikenek,0) as gajikenek"),
                DB::raw("'' as keterangan")
            )
            ->leftjoin(DB::raw("pendapatansupirdetail as b with(readuncommitted)"), function ($join) {
                $join->on('a.supir_id', '=', 'b.supir_id');
                $join->on('a.gajisupir_nobukti', '=', 'b.nobuktirincian');
                $join->on('a.suratpengantar_nobukti', '=', 'b.nobuktitrip');
            })
            ->whereRaw("isnull(b.nobukti,'')=''")
            ->whereRaw("a.suratpengantar_tglbukti>='" . date('Y-m-d', strtotime($tgldari)) . "' and  a.suratpengantar_tglbukti<='" . date('Y-m-d', strtotime($tglsampai)) . "'")
            ->whereRaw("(a.supir_id=" . $supir_id . " or " . $supir_id . "=0)")
            ->Orderby('a.suratpengantar_tglbukti', 'asc')
            ->Orderby('a.suratpengantar_nobukti', 'asc');


        DB::table($tempsaldopendapatan)->insertUsing([
            'pendapatansupir_id',
            'supir_id',
            'gajisupir_nobukti',
            'suratpengantar_nobukti',
            'tgl_trip',
            'tgl_ric',
            'dari_id',
            'sampai_id',
            'nominal',
            'gajikenek',
            'keterangan',

        ], $querysaldopendapatan);


        $querysaldopendapatan = DB::table('prosesgajisupirdetail')->from(
            db::raw("prosesgajisupirdetail a with (readuncommitted)")
        )
            ->select(
                DB::raw("0 as pendapatansupir_id"),
                'd.supir_id',
                'c.nobukti as gajisupir_nobukti',
                'c.suratpengantar_nobukti',
                'd.tglbukti as tgl_trip',
                DB::raw("null as tgl_ric"),
                'd.dari_id',
                'd.sampai_id',
                'c.komisisupir as nominal',
                'c.gajikenek',
                DB::raw("'' as keterangan")
            )
            ->join(DB::raw("gajisupirdetail c with (readuncommitted)"), 'a.gajisupir_nobukti', 'c.nobukti')
            ->join(DB::raw("suratpengantar d with (readuncommitted)"), 'c.suratpengantar_nobukti', 'd.nobukti')
            ->leftjoin(DB::raw("pendapatansupirdetail as b with(readuncommitted)"), function ($join) {
                $join->on('d.supir_id', '=', 'b.supir_id');
                $join->on('a.gajisupir_nobukti', '=', 'b.nobuktirincian');
                $join->on('d.nobukti', '=', 'b.nobuktitrip');
            })
            ->whereRaw("isnull(b.nobukti,'')=''")
            ->whereRaw("d.tglbukti>='" . date('Y-m-d', strtotime($tgldari)) . "' and  d.tglbukti<='" . date('Y-m-d', strtotime($tglsampai)) . "'")
            ->whereRaw("(d.supir_id=" . $supir_id . " or " . $supir_id . "=0)")

            ->Orderby('d.tglbukti', 'asc')
            ->Orderby('d.nobukti', 'asc');

        DB::table($tempsaldopendapatan)->insertUsing([
            'pendapatansupir_id',
            'supir_id',
            'gajisupir_nobukti',
            'suratpengantar_nobukti',
            'tgl_trip',
            'tgl_ric',
            'dari_id',
            'sampai_id',
            'nominal',
            'gajikenek',
            'keterangan',

        ], $querysaldopendapatan);

        $querysaldopendapatan = DB::table('pendapatansupirdetail')->from(
            db::raw("pendapatansupirdetail a with (readuncommitted)")
        )
            ->select(
                DB::raw("a.pendapatansupir_id as pendapatansupir_id"),
                'a.supir_id',
                'a.nobuktirincian as gajisupir_nobukti',
                'a.nobuktitrip as suratpengantar_nobukti',
                'b.tglbukti as tgl_trip',
                DB::raw("null as tgl_ric"),
                'b.dari_id',
                'b.sampai_id',
                'a.nominal',
                DB::raw("(case when a.gajikenek IS NULL then 0 else a.gajikenek end) as gajikenek"),
                'a.keterangan'
            )
            ->join(DB::raw("suratpengantar b with (readuncommitted)"), 'a.nobuktitrip', 'b.nobukti')

            ->where('a.pendapatansupir_id', $id)
            ->Orderby('b.tglbukti', 'asc')
            ->Orderby('b.nobukti', 'asc');

        DB::table($tempsaldopendapatan)->insertUsing([
            'pendapatansupir_id',
            'supir_id',
            'gajisupir_nobukti',
            'suratpengantar_nobukti',
            'tgl_trip',
            'tgl_ric',
            'dari_id',
            'sampai_id',
            'nominal',
            'gajikenek',
            'keterangan',
        ], $querysaldopendapatan);

        $querysaldopendapatan = DB::table('pendapatansupirdetail')->from(
            db::raw("pendapatansupirdetail a with (readuncommitted)")
        )
            ->select(
                DB::raw("a.pendapatansupir_id as pendapatansupir_id"),
                'a.supir_id',
                'a.nobuktirincian as gajisupir_nobukti',
                'a.nobuktitrip as suratpengantar_nobukti',
                'b.suratpengantar_tglbukti as tgl_trip',
                DB::raw("null as tgl_ric"),
                'b.dari_id',
                'b.sampai_id',
                'a.nominal',
                DB::raw("(case when a.gajikenek IS NULL then 0 else a.gajikenek end) as gajikenek"),
                'a.keterangan'
            )
            ->join(DB::raw("saldopendapatansupir b with (readuncommitted)"), 'a.nobuktitrip', 'b.suratpengantar_nobukti')

            ->where('a.pendapatansupir_id', $id)
            ->Orderby('b.suratpengantar_tglbukti', 'asc')
            ->Orderby('b.suratpengantar_nobukti', 'asc');

        DB::table($tempsaldopendapatan)->insertUsing([
            'pendapatansupir_id',
            'supir_id',
            'gajisupir_nobukti',
            'suratpengantar_nobukti',
            'tgl_trip',
            'tgl_ric',
            'dari_id',
            'sampai_id',
            'nominal',
            'gajikenek',
            'keterangan',
        ], $querysaldopendapatan);

        $this->setRequestParameters();

        $query = DB::table($tempsaldopendapatan)->from(
            db::raw($tempsaldopendapatan . " a ")
        )
            ->select(
                DB::raw("row_number() Over(Order By a.suratpengantar_nobukti) as id"),
                'a.pendapatansupir_id',
                'a.supir_id',
                'd.namasupir',
                'a.gajisupir_nobukti as nobukti_ric',
                'a.suratpengantar_nobukti  as nobukti_trip',
                'a.tgl_trip',
                'a.tgl_ric',
                'a.dari_id',
                DB::raw("isnull(b.kodekota,'') as dari"),
                'a.sampai_id',
                DB::raw("isnull(c.kodekota,'') as sampai"),
                'a.nominal as nominal_detail',
                'a.gajikenek',
                'a.keterangan',
            )
            ->leftJoin(DB::raw("kota b with (readuncommitted)"), 'a.dari_id', 'b.id')
            ->leftJoin(DB::raw("kota c with (readuncommitted)"), 'a.sampai_id', 'c.id')
            ->leftJoin(DB::raw("supir d with (readuncommitted)"), 'a.supir_id', 'd.id')
            ->where('a.nominal', '!=', '0');

        // ->Orderby('a.suratpengantar_tglbukti', 'asc')
        // ->Orderby('a.suratpengantar_nobukti', 'asc');

        if ($aksi != 'show') {
            $this->filterTrip($query, 'format 1');
            $this->totalNominal = $query->sum('nominal');
            $this->totalGajiKenek = $query->sum('gajikenek');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
            $this->sortTrip($query);
            $query->skip($this->params['offset'])->take($this->params['limit']);
        }
        // else {
        //     $query->Orderby('a.suratpengantar_tglbukti', 'asc')
        //         ->Orderby('a.suratpengantar_nobukti', 'asc');
        // }
        $data = $query->get();

        // dd($data);
        return $data;
    }

    public function gettrip2($tgldari, $tglsampai, $supir_id, $id, $aksi = null)
    {
        $tempsaldopendapatan = '##tempsaldopendapatan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldopendapatan, function ($table) {
            $table->integer('pendapatansupir_id')->nullable();
            $table->string('gajisupir_nobukti', 1000)->nullable();
            $table->date('tgl_ric')->nullable();
            $table->date('tgl_trip')->nullable();
            $table->integer('supir_id')->nullable();
            $table->double('gajikenek')->nullable();
            $table->double('nominal')->nullable();
            $table->longText('keterangan')->nullable();
        });


        if ($id != '') {

            $querysaldopendapatan = DB::table('pendapatansupirdetail')->from(
                db::raw("pendapatansupirdetail a with (readuncommitted)")
            )
                ->select(
                    DB::raw("a.pendapatansupir_id as pendapatansupir_id"),
                    'a.nobuktirincian as gajisupir_nobukti',
                    'b.tglbukti as tgl_ric',
                    DB::raw("null as tgl_trip"),
                    'a.supir_id',
                    DB::raw("(case when a.gajikenek IS NULL then 0 else a.gajikenek end) as gajikenek"),
                    DB::raw("0 as nominal"),
                    'a.keterangan'
                )
                ->join(DB::raw("gajisupirheader b with (readuncommitted)"), 'a.nobuktirincian', 'b.nobukti')

                ->where('a.pendapatansupir_id', $id)
                ->Orderby('b.tglbukti', 'asc')
                ->Orderby('b.nobukti', 'asc');

            DB::table($tempsaldopendapatan)->insertUsing([
                'pendapatansupir_id',
                'gajisupir_nobukti',
                'tgl_ric',
                'tgl_trip',
                'supir_id',
                'gajikenek',
                'nominal',
                'keterangan',
            ], $querysaldopendapatan);
        }

        $tempAwal = '##tempAwal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempAwal, function ($table) {
            $table->integer('pendapatansupir_id')->nullable();
            $table->string('gajisupir_nobukti', 1000)->nullable();
            $table->date('tgl_ric')->nullable();
            $table->date('tgl_trip')->nullable();
            $table->integer('supir_id')->nullable();
            $table->double('gajikenek')->nullable();
            $table->double('nominal')->nullable();
            $table->longText('keterangan')->nullable();
        });


        $queryGajisupir = DB::table('gajisupirheader')->from(
            db::raw("gajisupirheader a with (readuncommitted)")
        )
            ->select(
                DB::raw("0 as pendapatansupir_id"),
                'a.nobukti as gajisupir_nobukti',
                'a.tglbukti as tgl_ric',
                DB::raw("null as tgl_trip"),
                'a.supir_id',
                DB::raw("SUM(b.gajikenek) AS gajikenek"),
                DB::raw("0 as nominal"),
                DB::raw("'' as keterangan")
            )
            ->join(DB::raw("gajisupirdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("a.tglbukti>='" . date('Y-m-d', strtotime($tgldari)) . "' and  a.tglbukti<='" . date('Y-m-d', strtotime($tglsampai)) . "'")
            ->whereRaw("(a.supir_id=" . $supir_id . " or " . $supir_id . "=0)")
            ->where('b.gajikenek', '!=', 0)
            ->groupBy('a.nobukti', 'a.tglbukti', 'a.supir_id')
            ->Orderby('a.tglbukti', 'asc')
            ->Orderby('a.nobukti', 'asc');

        DB::table($tempAwal)->insertUsing([
            'pendapatansupir_id',
            'gajisupir_nobukti',
            'tgl_ric',
            'tgl_trip',
            'supir_id',
            'gajikenek',
            'nominal',
            'keterangan',
        ], $queryGajisupir);

        $queryPendapatan = DB::table($tempAwal)->from(
            db::raw("$tempAwal a with (readuncommitted)")
        )
            ->select(
                "a.pendapatansupir_id",
                'a.gajisupir_nobukti',
                'a.tgl_ric',
                'a.tgl_trip',
                'a.supir_id',
                "a.gajikenek",
                DB::raw("0 as nominal"),
                "a.keterangan"
            )
            ->leftJoin(DB::raw("pendapatansupirdetail b with (readuncommitted)"), 'a.gajisupir_nobukti', 'b.nobuktirincian')
            ->whereRaw("isnull(b.nobukti,'')=''");

        DB::table($tempsaldopendapatan)->insertUsing([
            'pendapatansupir_id',
            'gajisupir_nobukti',
            'tgl_ric',
            'tgl_trip',
            'supir_id',
            'gajikenek',
            'nominal',
            'keterangan',
        ], $queryPendapatan);

        $this->setRequestParameters();

        $query = DB::table($tempsaldopendapatan)->from(
            db::raw($tempsaldopendapatan . " a ")
        )
            ->select(
                DB::raw("row_number() Over(Order By a.gajisupir_nobukti) as id"),
                'a.pendapatansupir_id',
                'a.supir_id',
                'd.namasupir',
                'a.gajisupir_nobukti as nobukti_ric',
                'a.tgl_ric',
                'a.tgl_trip',
                'a.gajikenek',
                'a.keterangan',
                DB::raw("'0' as dari_id"),
                DB::raw("'0' as sampai_id")
            )
            ->leftJoin(DB::raw("supir d with (readuncommitted)"), 'a.supir_id', 'd.id');

        // ->Orderby('a.suratpengantar_tglbukti', 'asc')
        // ->Orderby('a.suratpengantar_nobukti', 'asc');
        if ($aksi != 'show') {
            $this->filterTrip($query, 'format 2');
            // $this->totalNominal = $query->sum('nominal');
            $this->totalGajiKenek = $query->sum('gajikenek');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            if ($this->params['sortIndex'] == 'nobukti_trip') {
                $query->orderBy('a.gajisupir_nobukti', 'asc');
            } else {
                $this->sortTrip($query);
            }
            $query->skip($this->params['offset'])->take($this->params['limit']);
        }
        // else {
        //     $query->Orderby('a.suratpengantar_tglbukti', 'asc')
        //         ->Orderby('a.suratpengantar_nobukti', 'asc');
        // }
        $data = $query->get();

        // dd($data);
        return $data;
    }

    public function gettrip3($tgldari, $tglsampai, $supir_id, $id, $aksi = null)
    {
        $tempsaldopendapatan = '##tempsaldopendapatan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldopendapatan, function ($table) {
            $table->integer('pendapatansupir_id')->nullable();
            $table->string('gajisupir_nobukti', 1000)->nullable();
            $table->string('nobukti_trip', 1000)->nullable();
            $table->date('tgl_ric')->nullable();
            $table->date('tgl_trip')->nullable();
            $table->integer('dari_id')->nullable();
            $table->integer('sampai_id')->nullable();
            $table->integer('supir_id')->nullable();
            $table->double('nominal')->nullable();
            $table->double('gajikenek')->nullable();
            $table->longText('keterangan')->nullable();
        });


        if ($id != '') {
            $querysaldopendapatan = DB::table('pendapatansupirdetail')->from(
                db::raw("pendapatansupirdetail a with (readuncommitted)")
            )
                ->select(
                    DB::raw("a.pendapatansupir_id as pendapatansupir_id"),
                    'a.nobuktirincian as gajisupir_nobukti',
                    'a.nobuktitrip as nobukti_trip',
                    'b.tglbukti as tgl_ric',
                    'c.tglbukti as tgl_trip',
                    'c.dari_id',
                    'c.sampai_id',
                    'd.supir_id',
                    DB::raw("(case when a.nominal IS NULL then 0 else a.nominal end) as nominal"),
                    DB::raw("0 as gajikenek"),
                    'a.keterangan',
                )
                
                ->join(DB::raw("pendapatansupirheader d with (readuncommitted)"), 'a.pendapatansupir_id', 'd.id')
                ->join(DB::raw("gajisupirheader b with (readuncommitted)"), 'a.nobuktirincian', 'b.nobukti')
                ->join(DB::raw("suratpengantar c with (readuncommitted)"), 'a.nobuktitrip', 'c.nobukti')

                ->where('a.pendapatansupir_id', $id)
                ->Orderby('b.tglbukti', 'asc')
                ->Orderby('b.nobukti', 'asc');
                    
            DB::table($tempsaldopendapatan)->insertUsing([
                'pendapatansupir_id',
                'gajisupir_nobukti',
                'nobukti_trip',
                'tgl_ric',
                'tgl_trip',
                'dari_id',
                'sampai_id',
                'supir_id',
                'nominal',
                'gajikenek',
                'keterangan',
            ], $querysaldopendapatan);
        }

        $tempAwal = '##tempAwal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempAwal, function ($table) {
            $table->integer('pendapatansupir_id')->nullable();
            $table->string('gajisupir_nobukti', 1000)->nullable();
            $table->string('nobukti_trip', 1000)->nullable();
            $table->date('tgl_ric')->nullable();
            $table->date('tgl_trip')->nullable();
            $table->integer('dari_id')->nullable();
            $table->integer('sampai_id')->nullable();
            $table->integer('supir_id')->nullable();
            $table->double('nominal')->nullable();
            $table->double('gajikenek')->nullable();
            $table->longText('keterangan')->nullable();
        });


        $queryGajisupir = DB::table('gajisupirheader')->from(
            db::raw("gajisupirheader a with (readuncommitted)")
        )
            ->select(
                DB::raw("0 as pendapatansupir_id"),
                'a.nobukti as gajisupir_nobukti',
                'c.nobukti as nobukti_trip',
                'a.tglbukti as tgl_ric',
                'c.tglbukti as tgl_trip',
                'c.dari_id',
                'c.sampai_id',
                'a.supir_id',
                'b.komisisupir as nominal',
                DB::raw("0 as gajikenek"),
                DB::raw("'' as keterangan")
            )
            ->join(DB::raw("gajisupirdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw("suratpengantar c with (readuncommitted)"), 'b.suratpengantar_nobukti', 'c.nobukti')
            
            ->whereRaw("a.tglbukti>='" . date('Y-m-d', strtotime($tgldari)) . "' and  a.tglbukti<='" . date('Y-m-d', strtotime($tglsampai)) . "'")
            ->whereRaw("(a.supir_id=" . $supir_id . " or " . $supir_id . "=0)")
            ->where('b.komisisupir', '!=', 0)
            ->Orderby('a.tglbukti', 'asc')
            ->Orderby('a.nobukti', 'asc');

        DB::table($tempAwal)->insertUsing([
            'pendapatansupir_id',
            'gajisupir_nobukti',
            'nobukti_trip',
            'tgl_ric',
            'tgl_trip',
            'dari_id',
            'sampai_id',
            'supir_id',
            'nominal',
            'gajikenek',
            'keterangan',
        ], $queryGajisupir);

        $queryPendapatan = DB::table($tempAwal)->from(
            db::raw("$tempAwal a with (readuncommitted)")
        )
            ->select(
                "a.pendapatansupir_id",
                'a.gajisupir_nobukti',
                'a.nobukti_trip',
                'a.tgl_ric',
                'a.tgl_trip',
                'a.dari_id',
                'a.sampai_id',
                'a.supir_id',
                'a.nominal',
                DB::raw("0 as gajikenek"),
                'a.keterangan',
            )
            ->leftJoin(DB::raw("pendapatansupirdetail b with (readuncommitted)"), 'a.gajisupir_nobukti', 'b.nobuktirincian')
            ->whereRaw("isnull(b.nobukti,'')=''");

        DB::table($tempsaldopendapatan)->insertUsing([
            'pendapatansupir_id',
            'gajisupir_nobukti',
            'nobukti_trip',
            'tgl_ric',
            'tgl_trip',
            'dari_id',
            'sampai_id',
            'supir_id',
            'nominal',
            'gajikenek',
            'keterangan',
        ], $queryPendapatan);

        $this->setRequestParameters();

        $query = DB::table($tempsaldopendapatan)->from(
            db::raw($tempsaldopendapatan . " a ")
        )
            ->select(
                DB::raw("row_number() Over(Order By a.gajisupir_nobukti) as id"),
                'a.pendapatansupir_id',
                'a.nobukti_trip',
                'a.supir_id',
                'd.namasupir',
                'a.gajisupir_nobukti as nobukti_ric',
                'a.tgl_ric',
                'a.tgl_trip',
                'a.dari_id',
                'b.kodekota as dari',
                'a.sampai_id',
                'c.kodekota as sampai',
                'a.nominal as nominal_detail',
                DB::raw("0 as gajikenek"),
                'a.keterangan',
                
            )
            ->leftJoin(DB::raw("kota b with (readuncommitted)"), 'a.dari_id', 'b.id')
            ->leftJoin(DB::raw("kota c with (readuncommitted)"), 'a.sampai_id', 'c.id')
            ->leftJoin(DB::raw("supir d with (readuncommitted)"), 'a.supir_id', 'd.id');

        // ->Orderby('a.suratpengantar_tglbukti', 'asc')
        // ->Orderby('a.suratpengantar_nobukti', 'asc');
        if ($aksi != 'show') {
            $this->filterTrip($query, 'FORMAT 3');
            $this->totalNominal = $query->sum('nominal');
            // $this->totalGajiKenek = $query->sum('komisisupir');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            if ($this->params['sortIndex'] == 'nobukti_trip') {
                $query->orderBy('a.gajisupir_nobukti', 'asc');
            } else {
                $this->sortTrip($query);
            }
            $query->skip($this->params['offset'])->take($this->params['limit']);
        }
        // else {
        //     $query->Orderby('a.suratpengantar_tglbukti', 'asc')
        //         ->Orderby('a.suratpengantar_nobukti', 'asc');
        // }
        $data = $query->get();

        // dd($data);
        return $data;
    }
    public function sortTrip($query)
    {
        if ($this->params['sortIndex'] == 'nobukti_ric') {
            return $query->orderBy('a.gajisupir_nobukti', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'nobukti_trip') {
            return $query->orderBy('a.suratpengantar_nobukti', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'nominal_detail') {
            return $query->orderBy(DB::raw("a.nominal"), $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'dari') {
            return $query->orderBy(DB::raw("isnull(b.kodekota,'')"), $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'sampai') {
            return $query->orderBy(DB::raw("isnull(c.kodekota,'')"), $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'id') {
            return $query->orderBy('a.suratpengantar_nobukti', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'namasupir') {
            return $query->orderBy('d.namasupir', $this->params['sortOrder']);
        } else {
            return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }
    public function filterTrip($query, $format)
    {
        $this->format = $format;
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'nobukti_ric') {
                                $query = $query->where('a.gajisupir_nobukti', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nobukti_trip') {
                                if ($this->format == 'format 1') {
                                    $query = $query->where('a.suratpengantar_nobukti', 'LIKE', "%$filters[data]%");
                                }
                            } else if ($filters['field'] == 'namasupir') {
                                $query = $query->where('d.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'dari') {
                                if ($this->format == 'format 1') {
                                    $query = $query->where(DB::raw("isnull(b.kodekota,'')"), 'LIKE', "%$filters[data]%");
                                }
                            } else if ($filters['field'] == 'sampai') {
                                if ($this->format == 'format 1') {
                                    $query = $query->where(DB::raw("c.kodekota"), 'LIKE', "%$filters[data]%");
                                }
                            } else if ($filters['field'] == 'tgl_trip') {
                                if ($this->format == 'format 1') {
                                    $query = $query->whereRaw("format(a.tgl_trip,'dd-MM-yyyy') like '%$filters[data]%'");
                                }
                            } else if ($filters['field'] == 'tgl_ric') {
                                if ($this->format == 'format 2') {
                                    $query = $query->orWhereRaw("format(a.tgl_ric,'dd-MM-yyyy') like '%$filters[data]%'");
                                }
                            } else if ($filters['field'] == 'nominal_detail') {
                                $query = $query->whereRaw("format(a.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'gajikenek') {
                                $query = $query->whereRaw("format(a.gajikenek, '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'nobukti_ric') {
                                    $query = $query->orWhere('a.gajisupir_nobukti', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'nobukti_trip') {
                                    if ($this->format == 'format 1') {
                                        $query = $query->orWhere('a.suratpengantar_nobukti', 'LIKE', "%$filters[data]%");
                                    }
                                } else if ($filters['field'] == 'namasupir') {
                                    $query = $query->orWhere('d.namasupir', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'dari') {
                                    if ($this->format == 'format 1') {
                                        $query = $query->orWhere(DB::raw("isnull(b.kodekota,'')"), 'LIKE', "%$filters[data]%");
                                    }
                                } else if ($filters['field'] == 'sampai') {
                                    if ($this->format == 'format 1') {
                                        $query = $query->orWhere(DB::raw("isnull(c.kodekota,'')"), 'LIKE', "%$filters[data]%");
                                    }
                                } else if ($filters['field'] == 'tgl_trip') {
                                    if ($this->format == 'format 1') {
                                        $query = $query->whereRaw("format(a.tgl_trip,'dd-MM-yyyy') like '%$filters[data]%'");
                                    }
                                } else if ($filters['field'] == 'tgl_ric') {
                                    if ($this->format == 'format 2') {
                                        $query = $query->orWhereRaw("format(a.tgl_ric,'dd-MM-yyyy') like '%$filters[data]%'");
                                    }
                                } else if ($filters['field'] == 'nominal_detail') {
                                    $query = $query->orWhereRaw("format(a.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'gajikenek') {
                                    $query = $query->orWhereRaw("format(a.gajikenek, '#,#0.00') LIKE '%$filters[data]%'");
                                } else {
                                    $query = $query->orWhere('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                }
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
    }
    public function findUpdate($id)
    {
        $data = DB::table('pendapatansupirheader')->from(DB::raw("pendapatansupirheader with (readuncommitted)"))
            ->select(
                'pendapatansupirheader.id',
                'pendapatansupirheader.nobukti',
                'pendapatansupirheader.tglbukti',
                'pendapatansupirheader.bank_id',
                'bank.namabank as bank',
                'pendapatansupirheader.supir_id',
                'supir.namasupir as supir',
                'pendapatansupirheader.pengeluaran_nobukti',
                'pendapatansupirheader.tgldari',
                'pendapatansupirheader.tglsampai',
                'pendapatansupirheader.statuscetak',
            )
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pendapatansupirheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pendapatansupirheader.supir_id', 'supir.id')
            ->where('pendapatansupirheader.id', $id)
            ->first();

        return $data;
    }
    public function getNobuktiDPO($nobukti)
    {
        $query = DB::table("penerimaantruckingheader")->from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
            ->select('penerimaan_nobukti')
            ->where('pendapatansupir_bukti', $nobukti)
            ->where('nobukti', 'like', "%DPO%")
            ->first();

        return $query;
    }
    public function getNobuktiPJP($nobukti)
    {
        $query = DB::table("penerimaantruckingheader")->from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
            ->select('penerimaan_nobukti')
            ->where('pendapatansupir_bukti', $nobukti)
            ->where('nobukti', 'like', "%PJP%")
            ->first();

        return $query;
    }


    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->table.id,
                 $this->table.nobukti,
                 $this->table.tglbukti,
                 'bank.namabank as bank_id', 
                 'supir.namasupir as supir_id', 
                 $this->table.tgldari,
                 $this->table.tglsampai,
                'akunpusat.keterangancoa as coa',
                'parameter.text as statusapproval',
                 $this->table.userapproval,
                 $this->table.tglapproval,
                 'statuscetak.text as statuscetak',
                 $this->table.userbukacetak,
                 $this->table.tglbukacetak,
                 $this->table.jumlahcetak,
                 $this->table.periode,
                 $this->table.modifiedby,
                 $this->table.created_at,
                 $this->table.updated_at"
                )
            )
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pendapatansupirheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pendapatansupirheader.supir_id', 'supir.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pendapatansupirheader.coa', 'akunpusat.coa')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'pendapatansupirheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'pendapatansupirheader.statuscetak', 'statuscetak.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('bank_id', 1000)->nullable();
            $table->string('supir_id', 1000)->nullable();
            $table->date('tgldari')->nullable();
            $table->date('tglsampai')->nullable();
            $table->string('coa')->nullable();
            $table->string('statusapproval')->nullable();
            $table->string('userapproval')->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->date('periode')->nullable();
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });
        if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
            request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
        }

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        $models =  $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'bank_id', 'supir_id', 'tgldari', 'tglsampai', 'coa', 'statusapproval', 'userapproval', 'tglapproval', 'statuscetak', 'userbukacetak', 'tglbukacetak', 'jumlahcetak', 'periode', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'bank_id') {
            return $query->orderBy('bank.namabank', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'supir_id') {
            return $query->orderBy('supir.namasupir', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'coa') {
            return $query->orderBy('akunpusat.keterangancoa', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'bank_id') {
                                $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'supir_id') {
                                $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coa') {
                                $query = $query->where('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'statusapproval') {
                                $query = $query->where('statusapproval.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statuscetak') {
                                $query = $query->where('statuscetak.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgldari' || $filters['field'] == 'tglsampai'  || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
                                $query = $query->whereRaw("format(pendapatansupirheader." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(pendapatansupirheader." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'bank_id') {
                                    $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'supir_id') {
                                    $query = $query->orwhere('supir.namasupir', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'coa') {
                                    $query = $query->orwhere('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'statusapproval') {
                                    $query = $query->orWhere('statusapproval.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statuscetak') {
                                    $query = $query->orWhere('statuscetak.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgldari' || $filters['field'] == 'tglsampai'  || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
                                    $query = $query->OrwhereRaw("format(pendapatansupirheader." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->OrwhereRaw("format(pendapatansupirheader." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else {
                                    // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                                }
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
        if (request()->cetak && request()->periode) {
            $query->where('pendapatansupirheader.statuscetak', '<>', request()->cetak)
                ->whereYear('pendapatansupirheader.tglbukti', '=', request()->year)
                ->whereMonth('pendapatansupirheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($this->table)->from(DB::raw("pendapatansupirheader with (readuncommitted)"))
            ->select(
                'pendapatansupirheader.id',
                'pendapatansupirheader.nobukti',
                'pendapatansupirheader.tglbukti',
                'bank.namabank as bank_id',
                'supir.namasupir as supir_id',
                'pendapatansupirheader.tgldari',
                'pendapatansupirheader.tglsampai',
                'pendapatansupirheader.periode',
                'statuscetak.memo as statuscetak',
                'statuscetak.id as  statuscetak_id',
                'pendapatansupirheader.jumlahcetak',
                DB::raw("'Bukti Komisi Supir' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'pendapatansupirheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pendapatansupirheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pendapatansupirheader.supir_id', 'supir.id');

        $data = $query->first();
        return $data;
    }

    public function getExportsupir($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($this->table)->from(DB::raw("pendapatansupirheader with (readuncommitted)"))
            ->select(
                'pendapatansupirheader.id',
                'pendapatansupirheader.nobukti',
                'pendapatansupirheader.tglbukti',
                'bank.namabank as bank_id',
                'supir.namasupir as supir_id',
                'pendapatansupirheader.tgldari',
                'pendapatansupirheader.tglsampai',
                'pendapatansupirheader.periode',
                'statuscetak.memo as statuscetak',
                'statuscetak.id as  statuscetak_id',
                DB::raw("'Bukti Pendapatan Supir' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'pendapatansupirheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pendapatansupirheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pendapatansupirheader.supir_id', 'supir.id');

        $data = $query->first();
        return $data;
    }


    public function processStore(array $data): PendapatanSupirHeader
    {
        /* Store header */
        $group = 'PENDAPATAN SUPIR BUKTI';
        $subGroup = 'PENDAPATAN SUPIR BUKTI';
        $format = DB::table('parameter')->where('grp', $group)->where('subgrp', $subGroup)->first();

        $statusApp = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $coaDebet = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PENDAPATAN SUPIR')->where('kelompok', 'DEBET')
            ->first();
        $memoDebet = json_decode($coaDebet->memo, true);

        $getListTampilan = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'UBAH TAMPILAN')->where('text', 'PENDAPATANSUPIR')->first();
        $getListTampilan = json_decode($getListTampilan->memo);
        if ($getListTampilan->INPUT != '') {
            $getListTampilan = (explode(",", $getListTampilan->INPUT));
            foreach ($getListTampilan as $value) {
                if (array_key_exists(trim(strtolower($value)), $data) == true) {
                    unset($data[trim(strtolower($value))]);
                }
            }
        }
        $pendapatanSupirHeader = new PendapatanSupirHeader();

        $pendapatanSupirHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $pendapatanSupirHeader->bank_id  = $data['bank_id'] ?? 0;
        $pendapatanSupirHeader->supir_id  = $data['supir_id'];
        $pendapatanSupirHeader->tgldari  = date('Y-m-d', strtotime($data['tgldari']));
        $pendapatanSupirHeader->tglsampai  = date('Y-m-d', strtotime($data['tglsampai']));
        $pendapatanSupirHeader->coa = $memoDebet['JURNAL'];
        $pendapatanSupirHeader->statusapproval  = $statusApp->id;
        $pendapatanSupirHeader->statusformat = $format->id;
        $pendapatanSupirHeader->statuscetak = $statusCetak->id;
        $pendapatanSupirHeader->modifiedby = auth('api')->user()->name;
        $pendapatanSupirHeader->info = html_entity_decode(request()->info);
        $pendapatanSupirHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $pendapatanSupirHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$pendapatanSupirHeader->save()) {
            throw new \Exception("Error storing pendapatan Supir header.");
        }
        $parameterKeterangan = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'KETERANGAN DEFAULT PENDAPATAN SUPIR')->where('subgrp', 'KETERANGAN DEFAULT PENDAPATAN SUPIR')
            ->first();
        $totalPengeluaran = 0;

        for ($i = 0; $i < count($data['id_detail']); $i++) {
            $gajiKenek =  $data['gajikenek'][$i] ?? 0;
            $nominal = $data['nominal_detail'][$i] ?? 0;
            $pendapatanSupirDetail = (new PendapatanSupirDetail)->processStore($pendapatanSupirHeader, [
                'supir_id' => $data['supirtrip'][$i],
                'dari_id' => $data['dari_id'][$i] ?? 0,
                'sampai_id' => $data['sampai_id'][$i] ?? 0,
                'nobuktitrip' => $data['nobukti_trip'][$i] ?? '',
                'nobuktirincian' => $data['nobukti_ric'][$i],
                'nominal' => $nominal,
                'gajikenek' => $gajiKenek,
                'modifiedby' => $pendapatanSupirHeader->modifiedby,
            ]);
            $totalPengeluaran = $totalPengeluaran + $nominal + $gajiKenek;
            $pendapatanSupirs[] = $pendapatanSupirHeader->toArray();
        }

        $noWarkat[] = '';
        $tglJatuhTempo[] = $data['tglbukti'];
        $nominalDetailPengeluaran[] = $totalPengeluaran;
        $coaDebetPengeluaran[] = $memoDebet['JURNAL'];
        $keteranganDetailPengeluaran[] = "$parameterKeterangan->text " . $data['tgldari'] . " s/d " . $data['tglsampai'];
        if ($data['bank_id'] != '') {

            if ($data['bank_id'] == 1) {
                $alatBayar = DB::table("alatbayar")->from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'TUNAI')->first();
            } else {
                $alatBayar = DB::table("alatbayar")->from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'TRANSFER')->first();
            }

            // POSTING KE PENGELUARAN
            $pengeluaranHeaderRequest = [
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'pelanggan_id' => 0,
                'postingdari' => 'ENTRY PENDAPATANSUPIR',
                'dibayarke' => 'PENDAPATAN SUPIR',
                'bank_id' => $data['bank_id'],
                'alatbayar_id' => $alatBayar->id,
                'nowarkat' => $noWarkat,
                'tgljatuhtempo' => $tglJatuhTempo,
                'nominal_detail' => $nominalDetailPengeluaran,
                'coadebet' => $coaDebetPengeluaran,
                'keterangan_detail' => $keteranganDetailPengeluaran,
            ];

            $pengeluaranHeader = (new PengeluaranHeader())->processStore($pengeluaranHeaderRequest);
            $pendapatanSupirHeader->pengeluaran_nobukti = $pengeluaranHeader->nobukti;

            $pendapatanSupirHeader->save();
        }

        $pendapatanSupirLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($pendapatanSupirHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? 'ENTRY pendapatan Supir HEADER',
            'idtrans' => $pendapatanSupirHeader->id,
            'nobuktitrans' => $pendapatanSupirHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pendapatanSupirHeader->toArray(),
            'modifiedby' => $pendapatanSupirHeader->modifiedby
        ]);


        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pendapatanSupirDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? 'ENTRY pendapatan Supir HEADER',
            'idtrans' => $pendapatanSupirLogTrail->id,
            'nobuktitrans' => $pendapatanSupirHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pendapatanSupirs,
            'modifiedby' => $pendapatanSupirHeader->modifiedby
        ]);

        $params = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PENDAPATAN SUPIR')->where('subgrp', 'DEPOSITO')->first();
        $deposito = $params->text;

        if ($deposito == 'YA') {
            $fetchFormat =  DB::table('penerimaantrucking')->where('kodepenerimaan', 'DPO')->first();
            if ($data['nominal_depo'] != '') {

                $dataDeposito = [
                    'tanpaprosesnobukti' => 3,
                    'penerimaantrucking_id' => $fetchFormat->id,
                    'bank_id' => $data['bank_id'],
                    'tglbukti' => $data['tglbukti'],
                    'pendapatansupir_bukti' => $pendapatanSupirHeader->nobukti,
                    'supirheader_id' => 0,
                    'karyawanheader_id' => 0,
                    'jenisorder_id' => '',
                    'supir_id' => $data['supir_depo'],
                    'nominal' => $data['nominal_depo'],
                    'keterangan' => $data['keterangan_depo'],
                ];
                if ($data['bank_id'] == '') {
                    $dataDeposito['tanpaprosesnobukti'] = 2;
                }
                $penerimaanPS = (new PenerimaanTruckingHeader())->processStore($dataDeposito);
            }
            $fetchFormat =  DB::table('penerimaantrucking')->where('kodepenerimaan', 'PJP')->first();
            if ($data['pinj_nominal'] != '') {

                $dataPinjaman = [
                    'tanpaprosesnobukti' => 3,
                    'penerimaantrucking_id' => $fetchFormat->id,
                    'bank_id' => $data['bank_id'],
                    'tglbukti' => $data['tglbukti'],
                    'pendapatansupir_bukti' => $pendapatanSupirHeader->nobukti,
                    'supirheader_id' => 0,
                    'karyawanheader_id' => 0,
                    'jenisorder_id' => '',
                    'supir_id' => $data['pinj_supir'],
                    'nominal' => $data['pinj_nominal'],
                    'keterangan' => $data['pinj_keterangan'],
                    'pengeluarantruckingheader_nobukti' => $data['pinj_nobukti'],
                ];

                if ($data['bank_id'] == '') {
                    $dataPinjaman['tanpaprosesnobukti'] = 2;
                }
                $penerimaanPS = (new PenerimaanTruckingHeader())->processStore($dataPinjaman);
            }
        }


        return $pendapatanSupirHeader;
    }


    public function processUpdate(PendapatanSupirHeader $pendapatanSupirHeader, array $data): PendapatanSupirHeader
    {
        $prevBank = $pendapatanSupirHeader->bank_id;
        $getTgl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'EDIT TANGGAL BUKTI')->where('subgrp', 'PENDAPATAN SUPIR')->first();
        if (trim($getTgl->text) == 'YA') {
            $group = 'PENDAPATAN SUPIR BUKTI';
            $subGroup = 'PENDAPATAN SUPIR BUKTI';
            $querycek = DB::table('pendapatansupirheader')->from(
                DB::raw("pendapatansupirheader a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.id', $pendapatanSupirHeader->id)
                ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
                ->first();

            if (isset($querycek)) {
                $nobukti = $querycek->nobukti;
            } else {
                $nobukti = (new RunningNumberService)->get($group, $subGroup, $pendapatanSupirHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
            }

            $pendapatanSupirHeader->nobukti = $nobukti;
            $pendapatanSupirHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        }
        $getListTampilan = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'UBAH TAMPILAN')->where('text', 'PENDAPATANSUPIR')->first();
        $getListTampilan = json_decode($getListTampilan->memo);
        if ($getListTampilan->INPUT != '') {
            $getListTampilan = (explode(",", $getListTampilan->INPUT));
            foreach ($getListTampilan as $value) {
                if (array_key_exists(trim(strtolower($value)), $data) == true) {
                    unset($data[trim(strtolower($value))]);
                }
            }
        }
        $pendapatanSupirHeader->bank_id = $data['bank_id'] ?? '';
        $pendapatanSupirHeader->tgldari = date('Y-m-d', strtotime($data['tgldari']));
        $pendapatanSupirHeader->tglsampai = date('Y-m-d', strtotime($data['tglsampai']));
        $pendapatanSupirHeader->modifiedby = auth('api')->user()->name;
        $pendapatanSupirHeader->info = html_entity_decode(request()->info);

        if (!$pendapatanSupirHeader->save()) {
            throw new \Exception("Error storing pendapatan Supir header.");
        }

        PendapatanSupirDetail::where('pendapatansupir_id', $pendapatanSupirHeader->id)->lockForUpdate()->delete();

        $parameterKeterangan = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'KETERANGAN DEFAULT PENDAPATAN SUPIR')->where('subgrp', 'KETERANGAN DEFAULT PENDAPATAN SUPIR')
            ->first();
        $totalPengeluaran = 0;
        for ($i = 0; $i < count($data['id_detail']); $i++) {
            $gajiKenek =  $data['gajikenek'][$i] ?? 0;
            $nominal = $data['nominal_detail'][$i] ?? 0;
            $pendapatanSupirDetail = (new PendapatanSupirDetail)->processStore($pendapatanSupirHeader, [
                'supir_id' => $data['supirtrip'][$i],
                'dari_id' => $data['dari_id'][$i] ?? 0,
                'sampai_id' => $data['sampai_id'][$i] ?? 0,
                'nobuktitrip' => $data['nobukti_trip'][$i] ?? '',
                'nobuktirincian' => $data['nobukti_ric'][$i],
                'nominal' => $nominal,
                'gajikenek' => $gajiKenek,
                'modifiedby' => $pendapatanSupirHeader->modifiedby,
            ]);
            $totalPengeluaran = $totalPengeluaran + $nominal + $gajiKenek;
            $pendapatanSupirs[] = $pendapatanSupirHeader->toArray();
        }
        $coaDebet = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PENDAPATAN SUPIR')->where('kelompok', 'DEBET')
            ->first();
        $memoDebet = json_decode($coaDebet->memo, true);

        $noWarkat[] = '';
        $tglJatuhTempo[] = $pendapatanSupirHeader->tglbukti;
        $nominalDetailPengeluaran[] = $totalPengeluaran;
        $coaDebetPengeluaran[] = $memoDebet['JURNAL'];
        $keteranganDetailPengeluaran[] = "$parameterKeterangan->text " . $data['tgldari'] . " s/d " . $data['tglsampai'];

        $cekBank = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PENDAPATAN SUPIR')->where('subgrp', 'BANK')
            ->first();

        if ($cekBank->text == 'YA') {
            if ($pendapatanSupirHeader->bank_id == 1) {
                $alatBayar = DB::table("alatbayar")->from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'TUNAI')->first();
            } else {
                $alatBayar = DB::table("alatbayar")->from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'TRANSFER')->first();
            }

            // POSTING KE PENGELUARAN
            $pengeluaranHeaderRequest = [
                'tglbukti' => $pendapatanSupirHeader->tglbukti,
                'pelanggan_id' => 0,
                'postingdari' => 'EDIT PENDAPATAN SUPIR',
                'dibayarke' => 'PENDAPATAN SUPIR',
                'bank_id' => $pendapatanSupirHeader->bank_id,
                'alatbayar_id' => $alatBayar->id,
                'nowarkat' => $noWarkat,
                'tgljatuhtempo' => $tglJatuhTempo,
                'nominal_detail' => $nominalDetailPengeluaran,
                'coadebet' => $coaDebetPengeluaran,
                'keterangan_detail' => $keteranganDetailPengeluaran,
            ];

            $get = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))
                ->where('pengeluaranheader.nobukti', $pendapatanSupirHeader->pengeluaran_nobukti)->first();
            $newPengeluaran = new PengeluaranHeader();
            $newPengeluaran = $newPengeluaran->findAll($get->id);
            $pengeluaranheader = (new PengeluaranHeader())->processUpdate($newPengeluaran, $pengeluaranHeaderRequest);
            $pendapatanSupirHeader->pengeluaran_nobukti = $pengeluaranheader->nobukti;
            $pendapatanSupirHeader->save();
        }

        if ($cekBank->text == 'TIDAK') {
            if ($pendapatanSupirHeader->pengeluaran_nobukti == '') {
                if ($data['bank_id'] != 0 && $data['bank_id'] != '') {
                    if ($data['bank_id'] == 1) {
                        $alatBayar = DB::table("alatbayar")->from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'TUNAI')->first();
                    } else {
                        $alatBayar = DB::table("alatbayar")->from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'TRANSFER')->first();
                    }

                    // POSTING KE PENGELUARAN
                    $pengeluaranHeaderRequest = [
                        'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                        'pelanggan_id' => 0,
                        'postingdari' => 'EDIT PENDAPATANSUPIR',
                        'dibayarke' => 'PENDAPATAN SUPIR',
                        'bank_id' => $data['bank_id'],
                        'alatbayar_id' => $alatBayar->id,
                        'nowarkat' => $noWarkat,
                        'tgljatuhtempo' => $tglJatuhTempo,
                        'nominal_detail' => $nominalDetailPengeluaran,
                        'coadebet' => $coaDebetPengeluaran,
                        'keterangan_detail' => $keteranganDetailPengeluaran,
                    ];

                    $pengeluaranHeader = (new PengeluaranHeader())->processStore($pengeluaranHeaderRequest);
                    $pendapatanSupirHeader->pengeluaran_nobukti = $pengeluaranHeader->nobukti;

                    $pendapatanSupirHeader->save();
                }
            } else {

                if ($data['bank_id'] == 0 && $data['bank_id'] == '') {
                    $getPengeluaran = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))->where('nobukti', $pendapatanSupirHeader->pengeluaran_nobukti)->first();
                    (new PengeluaranHeader())->processDestroy($getPengeluaran->id, 'EDIT PENDAPATAN SUPIR');
                    $pendapatanSupirHeader->pengeluaran_nobukti = '';

                    $pendapatanSupirHeader->save();
                } else {
                    if ($pendapatanSupirHeader->bank_id == 1) {
                        $alatBayar = DB::table("alatbayar")->from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'TUNAI')->first();
                    } else {
                        $alatBayar = DB::table("alatbayar")->from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'TRANSFER')->first();
                    }
                    if ($data['bank_id'] != $prevBank) {
                        $getPengeluaran = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))->where('nobukti', $pendapatanSupirHeader->pengeluaran_nobukti)->first();
                        (new PengeluaranHeader())->processDestroy($getPengeluaran->id, 'EDIT PENDAPATAN SUPIR');

                        // POSTING KE PENGELUARAN
                        $pengeluaranHeaderRequest = [
                            'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                            'pelanggan_id' => 0,
                            'postingdari' => 'EDIT PENDAPATANSUPIR',
                            'dibayarke' => 'PENDAPATAN SUPIR',
                            'bank_id' => $data['bank_id'],
                            'alatbayar_id' => $alatBayar->id,
                            'nowarkat' => $noWarkat,
                            'tgljatuhtempo' => $tglJatuhTempo,
                            'nominal_detail' => $nominalDetailPengeluaran,
                            'coadebet' => $coaDebetPengeluaran,
                            'keterangan_detail' => $keteranganDetailPengeluaran,
                        ];

                        $pengeluaranHeader = (new PengeluaranHeader())->processStore($pengeluaranHeaderRequest);
                        $pendapatanSupirHeader->pengeluaran_nobukti = $pengeluaranHeader->nobukti;

                        $pendapatanSupirHeader->save();
                    } else {
                        // POSTING KE PENGELUARAN
                        $pengeluaranHeaderRequest = [
                            'tglbukti' => $pendapatanSupirHeader->tglbukti,
                            'pelanggan_id' => 0,
                            'postingdari' => 'EDIT PENDAPATAN SUPIR',
                            'dibayarke' => 'PENDAPATAN SUPIR',
                            'bank_id' => $pendapatanSupirHeader->bank_id,
                            'alatbayar_id' => $alatBayar->id,
                            'nowarkat' => $noWarkat,
                            'tgljatuhtempo' => $tglJatuhTempo,
                            'nominal_detail' => $nominalDetailPengeluaran,
                            'coadebet' => $coaDebetPengeluaran,
                            'keterangan_detail' => $keteranganDetailPengeluaran,
                        ];

                        $get = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))
                            ->where('pengeluaranheader.nobukti', $pendapatanSupirHeader->pengeluaran_nobukti)->first();
                        $newPengeluaran = new PengeluaranHeader();
                        $newPengeluaran = $newPengeluaran->findAll($get->id);
                        $pengeluaranheader = (new PengeluaranHeader())->processUpdate($newPengeluaran, $pengeluaranHeaderRequest);
                        $pendapatanSupirHeader->pengeluaran_nobukti = $pengeluaranheader->nobukti;
                        $pendapatanSupirHeader->save();
                    }
                }
            }
        }
        $pendapatanSupirLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($pendapatanSupirHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? 'EDIT pendapatan Supir HEADER',
            'idtrans' => $pendapatanSupirHeader->id,
            'nobuktitrans' => $pendapatanSupirHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $pendapatanSupirHeader->toArray(),
            'modifiedby' => $pendapatanSupirHeader->modifiedby
        ]);
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pendapatanSupirDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? 'EDIT pendapatan Supir HEADER',
            'idtrans' => $pendapatanSupirLogTrail->id,
            'nobuktitrans' => $pendapatanSupirHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $pendapatanSupirs,
            'modifiedby' => $pendapatanSupirHeader->modifiedby
        ]);

        $params = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PENDAPATAN SUPIR')->where('subgrp', 'DEPOSITO')->first();
        $deposito = $params->text;
        if ($deposito == 'YA') {
            $fetchFormat =  DB::table('penerimaantrucking')->where('kodepenerimaan', 'DPO')->first();
            $cekDeposito = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                ->where('pendapatansupir_bukti', $pendapatanSupirHeader->nobukti)
                ->where('penerimaantrucking_id', 3)
                ->first();

            if ($data['nominal_depo'] != '') {
                $dataDeposito = [
                    'tanpaprosesnobukti' => 2,
                    'penerimaantrucking_id' => $fetchFormat->id,
                    'bank_id' => $data['bank_id'],
                    'prevBank' => $prevBank,
                    'tglbukti' => $pendapatanSupirHeader->tglbukti,
                    'pendapatansupir_bukti' => $pendapatanSupirHeader->nobukti,
                    'supirheader_id' => 0,
                    'karyawanheader_id' => 0,
                    'jenisorder_id' => '',
                    'komisi' => true,
                    'supir_id' => $data['supir_depo'],
                    'nominal' => $data['nominal_depo'],
                    'keterangan' => $data['keterangan_depo'],
                ];
                if ($cekDeposito != null) {
                    if (isset($cekDeposito)) {
                        $newPenerimaanTruckingPS = new PenerimaanTruckingHeader();
                        $newPenerimaanTruckingPS = $newPenerimaanTruckingPS->findAll($cekDeposito->id);
                        $penerimaanPS = (new PenerimaanTruckingHeader())->processUpdate($newPenerimaanTruckingPS, $dataDeposito);
                    }
                } else {

                    if ($data['bank_id'] != '' && $data['bank_id'] != 0) {
                        $dataDeposito['tanpaprosesnobukti'] = 3;
                    }
                    if($data['bank_id'] == 0){
                        
                        $dataDeposito['tanpaprosesnobukti'] = 2;
                    }
                    $penerimaanPS = (new PenerimaanTruckingHeader())->processStore($dataDeposito);
                }
            } else {

                if ($cekDeposito != null) {
                    if (isset($cekDeposito)) {
                        (new PenerimaanTruckingHeader())->processDestroy($cekDeposito->id, 'EDIT PENDAPATAN SUPIR');
                    }
                }
            }
            $fetchFormat =  DB::table('penerimaantrucking')->where('kodepenerimaan', 'PJP')->first();
            $cekPinjaman = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                ->where('pendapatansupir_bukti', $pendapatanSupirHeader->nobukti)
                ->where('penerimaantrucking_id', 2)
                ->first();
            if ($data['pinj_nominal'] != '') {
                $dataPinjaman = [
                    'tanpaprosesnobukti' => 2,
                    'penerimaantrucking_id' => $fetchFormat->id,
                    'bank_id' => $data['bank_id'],
                    'tglbukti' => $pendapatanSupirHeader->tglbukti,
                    'pendapatansupir_bukti' => $pendapatanSupirHeader->nobukti,
                    'supirheader_id' => 0,
                    'karyawanheader_id' => 0,
                    'prevBank' => $prevBank,
                    'jenisorder_id' => '',
                    'komisi' => true,
                    'supir_id' => $data['pinj_supir'],
                    'nominal' => $data['pinj_nominal'],
                    'keterangan' => $data['pinj_keterangan'],
                    'pengeluarantruckingheader_nobukti' => $data['pinj_nobukti'],
                ];
                if ($cekPinjaman != null) {
                    if (isset($cekPinjaman)) {
                        $newPenerimaanTruckingPS = new PenerimaanTruckingHeader();
                        $newPenerimaanTruckingPS = $newPenerimaanTruckingPS->findAll($cekPinjaman->id);
                        $penerimaanPS = (new PenerimaanTruckingHeader())->processUpdate($newPenerimaanTruckingPS, $dataPinjaman);
                    }
                } else {
                    if ($data['bank_id'] != '' && $data['bank_id'] != 0) {
                        $dataPinjaman['tanpaprosesnobukti'] = 3;
                    }
                    if($data['bank_id'] == 0){
                        
                        $dataDeposito['tanpaprosesnobukti'] = 2;
                    }
                    $penerimaanPS = (new PenerimaanTruckingHeader())->processStore($dataPinjaman);
                }
            } else {

                if ($cekPinjaman != null) {

                    if (isset($cekPinjaman)) {
                        (new PenerimaanTruckingHeader())->processDestroy($cekPinjaman->id, 'EDIT PENDAPATAN SUPIR');
                    }
                }
            }
        }

        return $pendapatanSupirHeader;
    }

    public function processDestroy($id, $postingDari = ''): PendapatanSupirHeader
    {
        $pendapatanSupirDetail = PendapatanSupirDetail::lockForUpdate()->where('pendapatansupir_id', $id)->get();

        $pendapatanSupirHeader = new PendapatanSupirHeader();
        $pendapatanSupirHeader = $pendapatanSupirHeader->lockAndDestroy($id);

        $hutangLogTrail = (new LogTrail())->processStore([
            'namatabel' => $pendapatanSupirHeader->getTable(),
            'postingdari' => strtoupper('DELETE PENDAPATAN SUPIR HEADAER'),
            'idtrans' => $pendapatanSupirHeader->id,
            'nobuktitrans' => $pendapatanSupirHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $pendapatanSupirHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'PENDAPATANSUPIRDETAIL',
            'postingdari' => strtoupper('DELETE PENDAPATAN SUPIR DETAIL'),
            'idtrans' => $hutangLogTrail['id'],
            'nobuktitrans' => $pendapatanSupirHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $pendapatanSupirDetail->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        $getPengeluaran = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))->where('nobukti', $pendapatanSupirHeader->pengeluaran_nobukti)->first();
        if (isset($getPengeluaran)) {
            (new PengeluaranHeader())->processDestroy($getPengeluaran->id, $postingDari);
        }
        $cekPinjaman = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
            ->where('pendapatansupir_bukti', $pendapatanSupirHeader->nobukti)
            ->where('penerimaantrucking_id', 2)
            ->first();
        if ($cekPinjaman != null) {
            if (isset($cekPinjaman)) {
                (new PenerimaanTruckingHeader())->processDestroy($cekPinjaman->id, 'DELETE PENDAPATAN SUPIR');
            }
        }

        $cekDeposito = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
            ->where('pendapatansupir_bukti', $pendapatanSupirHeader->nobukti)
            ->where('penerimaantrucking_id', 3)
            ->first();
        if ($cekDeposito != null) {
            if (isset($cekDeposito)) {
                (new PenerimaanTruckingHeader())->processDestroy($cekDeposito->id, 'DELETE PENDAPATAN SUPIR');
            }
        }
        return $pendapatanSupirHeader;
    }

    public function getDataDeposito()
    {
        $nobukti = request()->nobukti ?? '';

        $statusaktif = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('text', '=', 'AKTIF')
            ->first();

        if ($nobukti != '') {
            $temp = '##tempDepo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temp, function ($table) {
                $table->bigInteger('id');
                $table->string('supirdeposito');
                $table->bigInteger('nominal')->nullable();
            });
            $fetch = DB::table("supir")->from(DB::raw("supir with (readuncommitted)"))
                ->select('id', 'namasupir as supirdeposito')
                ->where('statusaktif', $statusaktif->id)
                ->whereRaw("id not in (select a.supir_id from penerimaantruckingdetail as a left join penerimaantruckingheader as b on b.id = a.penerimaantruckingheader_id
            where b.pendapatansupir_bukti='$nobukti' and b.nobukti like '%DPO%')");

            DB::table($temp)->insertUsing(['id', 'supirdeposito'], $fetch);

            $fetch = DB::table("supir")->from(DB::raw("supir with (readuncommitted)"))
                ->select('supir.id', 'supir.namasupir as supirdeposito', 'a.nominal')
                ->leftJoin(DB::raw("penerimaantruckingdetail as a with (readuncommitted)"), 'a.supir_id', 'supir.id')
                ->leftJoin(DB::raw("penerimaantruckingheader as b with (readuncommitted)"), 'b.id', 'a.penerimaantruckingheader_id')
                ->where('supir.statusaktif', '=', $statusaktif->id)
                ->where('b.pendapatansupir_bukti', $nobukti)
                ->where('b.nobukti', 'LIKE', "%DPO%");

            DB::table($temp)->insertUsing(['id', 'supirdeposito', 'nominal'], $fetch);

            $query = DB::table($temp)->from(DB::raw("$temp with (readuncommitted)"))
                ->select(DB::raw("row_number() Over(Order By supirdeposito) as id, id as supir_id, supirdeposito, nominal"))
                ->orderBy('supirdeposito')->get();
        } else {

            $query = DB::table("supir")->from(DB::raw("supir with (readuncommitted)"))
                ->select(DB::raw("row_number() Over(Order By namasupir) as id, id as supir_id, namasupir as supirdeposito"))
                ->where('supir.statusaktif', '=', $statusaktif->id)
                ->orderBy('namasupir')
                ->get();
        }

        return $query;
    }

    public function getPinjaman($supir_id)
    {
        $nobukti = request()->nobukti;
        $supir_id = $supir_id;

        $tglBukti = date('Y-m-d', strtotime(request()->tglbukti));

        if ($nobukti != '') {

            $tempPribadi = $this->createTempPengembalianPinjaman($nobukti);
            $tempAll = $this->createTempPinjaman($nobukti, $tglBukti, $supir_id);
            $temp = '##tempPinjaman' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temp, function ($table) {
                $table->bigInteger('penerimaantruckingheader_id')->nullable();
                $table->string('nobukti');
                $table->date('tglbukti');
                $table->bigInteger('supir_id')->nullable();
                $table->float('nominal')->nullable();
                $table->longText('keterangan')->nullable();
                $table->float('jlhpinjaman')->nullable();
                $table->float('totalbayar')->nullable();
                $table->float('sisa')->nullable();
            });
            $pengembalian = DB::table($tempPribadi)->from(DB::raw("$tempPribadi with (readuncommitted)"))
                ->select(DB::raw("penerimaantruckingheader_id,nobukti,tglbukti,supir_id,nominal,keterangan,jlhpinjaman,totalbayar,sisa"));

            DB::table($temp)->insertUsing(['penerimaantruckingheader_id', 'nobukti', 'tglbukti', 'supir_id', 'nominal', 'keterangan', 'jlhpinjaman', 'totalbayar', 'sisa'], $pengembalian);

            $pinjaman = DB::table($tempAll)->from(DB::raw("$tempAll with (readuncommitted)"))

                ->select(DB::raw("null as penerimaantruckingheader_id,nobukti,tglbukti,supir_id,nominal,keterangan,jlhpinjaman,totalbayar,sisa"))
                ->where('sisa', '!=', '0');

            DB::table($temp)->insertUsing(['penerimaantruckingheader_id', 'nobukti', 'tglbukti', 'supir_id', 'nominal', 'keterangan', 'jlhpinjaman', 'totalbayar', 'sisa'], $pinjaman);

            $query = DB::table($temp)->from(DB::raw("$temp as a with (readuncommitted)"))
                ->select(DB::raw("row_number() Over(Order By a.tglbukti asc,a.nobukti) as id, a.penerimaantruckingheader_id,a.tglbukti as pinj_tglbukti,a.nobukti as pinj_nobukti,a.keterangan as pinj_keterangan,a.supir_id as pinj_supirid, supir.namasupir as pinj_supir,
                a.jlhpinjaman,a.totalbayar,
            (case when a.sisa IS NULL then 0 else a.sisa end) as pinj_sisa,
            (case when a.nominal IS NULL then 0 else a.nominal end) as pinj_nominal"))
                ->leftJoin(DB::raw("supir with (readuncommitted)"), 'a.supir_id', "supir.id")
                ->orderBy('a.tglbukti', 'asc')
                ->orderBy('a.nobukti', 'asc');
        } else {

            $tempPribadi = $this->createTempPinjPribadi($supir_id);
            $query = PengeluaranTruckingDetail::from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
                ->select(DB::raw("row_number() Over(Order By pengeluarantruckingheader.tglbukti asc,pengeluarantruckingdetail.nobukti) as id,pengeluarantruckingheader.tglbukti as pinj_tglbukti,pengeluarantruckingdetail.nobukti as pinj_nobukti,pengeluarantruckingdetail.keterangan as pinj_keterangan,pengeluarantruckingdetail.supir_id as pinj_supirid, supir.namasupir as pinj_supir," . $tempPribadi . ".sisa as pinj_sisa,$tempPribadi.jlhpinjaman,$tempPribadi.totalbayar"))
                ->leftJoin(DB::raw("$tempPribadi with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', $tempPribadi . ".nobukti")
                ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', "pengeluarantruckingheader.nobukti")
                ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingdetail.supir_id', "supir.id")
                ->whereRaw("(pengeluarantruckingdetail.supir_id=" . $supir_id . " or " . $supir_id . "=0)")
                ->whereRaw("pengeluarantruckingdetail.nobukti = $tempPribadi.nobukti")
                ->where("pengeluarantruckingheader.pengeluarantrucking_id", 1)
                ->where("pengeluarantruckingheader.tglbukti", "<=", $tglBukti)
                ->where(function ($query) use ($tempPribadi) {
                    $query->whereRaw("$tempPribadi.sisa != 0")
                        ->orWhereRaw("$tempPribadi.sisa is null");
                })
                ->orderBy('pengeluarantruckingheader.tglbukti', 'asc')
                ->orderBy('pengeluarantruckingdetail.nobukti', 'asc');
        }
        return $query->get();
    }
    public function createTempPinjPribadi($supir_id)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("pengeluarantruckingdetail.nobukti,SUM(pengeluarantruckingdetail.nominal) AS jlhpinjaman,
            (SELECT isnull(SUM(penerimaantruckingdetail.nominal),0) FROM penerimaantruckingdetail
            WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS totalbayar, (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"))
            ->whereRaw("(pengeluarantruckingdetail.supir_id=" . $supir_id . " or " . $supir_id . "=0)")
            ->where("pengeluarantruckingdetail.nobukti",  'LIKE', "%PJT%")
            ->groupBy('pengeluarantruckingdetail.nobukti', 'pengeluarantruckingdetail.nominal');

        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->bigInteger('jlhpinjaman')->nullable();
            $table->bigInteger('totalbayar')->nullable();
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'jlhpinjaman', 'totalbayar', 'sisa'], $fetch);


        return $temp;
    }
    public function createTempPengembalianPinjaman($nobukti)
    {
        $temp = '##tempPengembalian' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = DB::table("penerimaantruckingdetail")->from(DB::raw("penerimaantruckingdetail as a with (readuncommitted)"))
            ->select(DB::raw("a.id as penerimaantruckingheader_id, c.nobukti,c.tglbukti,a.supir_id, a.nominal, a.keterangan,d.nominal AS jlhpinjaman, 
             (SELECT isnull(SUM(penerimaantruckingdetail.nominal),0) FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= d.nobukti and penerimaantruckingdetail.penerimaantruckingheader_id != b.id) AS totalbayar,
             (select d.nominal - sum(isnull(penerimaantruckingdetail.nominal,0)) from penerimaantruckingdetail where d.nobukti=penerimaantruckingdetail.pengeluarantruckingheader_nobukti) as sisa"))
            ->leftJoin(DB::raw("penerimaantruckingheader as b"), 'b.id', 'a.penerimaantruckingheader_id')
            ->leftJoin(DB::raw("pengeluarantruckingheader as c"), 'c.nobukti', 'a.pengeluarantruckingheader_nobukti')
            ->leftJoin(DB::raw("pengeluarantruckingdetail as d"), 'c.nobukti', 'd.nobukti')
            ->where('b.penerimaantrucking_id', "2")
            ->where('b.pendapatansupir_bukti', $nobukti);
        Schema::create($temp, function ($table) {
            $table->bigInteger('penerimaantruckingheader_id')->nullable();
            $table->string('nobukti');
            $table->date('tglbukti');
            $table->bigInteger('supir_id')->nullable();
            $table->float('nominal')->nullable();
            $table->longText('keterangan')->nullable();
            $table->float('jlhpinjaman')->nullable();
            $table->float('totalbayar')->nullable();
            $table->float('sisa')->nullable();
        });
        DB::table($temp)->insertUsing(['penerimaantruckingheader_id', 'nobukti', 'tglbukti', 'supir_id', 'nominal', 'keterangan', 'jlhpinjaman', 'totalbayar', 'sisa'], $fetch);

        return $temp;
    }

    public function createTempPinjaman($nobukti, $tglbukti, $supir_id)
    {
        $temp = '##tempPengembalian' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch2 = DB::table("pengeluarantruckingdetail")->from(DB::raw("pengeluarantruckingdetail as a with (readuncommitted)"))
            ->select(DB::raw("b.nobukti,b.tglbukti, a.supir_id,a.keterangan,a.nominal AS jlhpinjaman, 

        (SELECT isnull(SUM(penerimaantruckingdetail.nominal),0) FROM penerimaantruckingdetail
             WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= a.nobukti) AS totalbayar,
        (SELECT (a.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= a.nobukti) AS sisa"))
            ->leftJoin(DB::raw("pengeluarantruckingheader as b"), 'b.nobukti', 'a.nobukti')
            ->where("b.pengeluarantrucking_id", 1);
        if ($supir_id != 0) {
            $fetch2->whereRaw("a.supir_id = $supir_id");
        }
        $fetch2->where("b.tglbukti", "<=", $tglbukti)
            ->whereRaw("b.nobukti not in (select a.pengeluarantruckingheader_nobukti from penerimaantruckingdetail as a 
                left join penerimaantruckingheader as b on b.id = a.penerimaantruckingheader_id
                where b.pendapatansupir_bukti='$nobukti' and b.penerimaantrucking_id=2)");

        Schema::create($temp, function ($table) {
            $table->bigInteger('penerimaantruckingheader_id')->nullable();
            $table->string('nobukti');
            $table->date('tglbukti');
            $table->bigInteger('supir_id')->nullable();
            $table->float('nominal')->nullable();
            $table->longText('keterangan')->nullable();
            $table->float('jlhpinjaman')->nullable();
            $table->float('totalbayar')->nullable();
            $table->float('sisa')->nullable();
        });
        DB::table($temp)->insertUsing(['nobukti', 'tglbukti', 'supir_id', 'keterangan', 'jlhpinjaman', 'totalbayar', 'sisa'], $fetch2);

        return $temp;
    }
}
