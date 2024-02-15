<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ProsesGajiSupirHeader extends MyModel
{
    use HasFactory;

    protected $table = 'prosesgajisupirheader';
    protected $tableTotal = '';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

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

    public function cekvalidasiaksi($id)
    {

        $prosesGaji = ProsesGajiSupirHeader::from(DB::raw("prosesgajisupirheader"))->where('id', $id)->first();

        $hutangBayar = DB::table('jurnalumumpusatheader')
            ->from(
                DB::raw("jurnalumumpusatheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->where('a.nobukti', '=', $prosesGaji->nobukti)
            ->first();
        if (isset($hutangBayar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Approval Jurnal ' . $hutangBayar->nobukti,
                'kodeerror' => 'SAP'
            ];
            goto selesai;
        }

        $hutangBayar = DB::table('jurnalumumpusatheader')
            ->from(
                DB::raw("jurnalumumpusatheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
            )
            ->where('a.nobukti', '=', $prosesGaji->pengeluaran_nobukti)
            ->first();
        if (isset($hutangBayar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Approval Jurnal ' . $hutangBayar->nobukti,
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

    public function get()
    {
        $this->setRequestParameters();
        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';

        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'ProsesGajiSupirHeaderController';


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
                $table->string('nobukti', 1000)->nullable();
                $table->dateTime('tglbukti')->nullable();
                $table->date('tgldari')->nullable();
                $table->date('tglsampai')->nullable();
                $table->longText('keterangan')->nullable();
                $table->longText('periode')->nullable();
                $table->longText('userapproval')->nullable();
                $table->longText('statusapproval')->nullable();
                $table->longText('statuscetak')->nullable();
                $table->longText('statuscetaktext')->nullable();
                $table->longText('userbukacetak')->nullable();
                $table->integer('jumlahcetak')->nullable();
                $table->string('pengeluaran_nobukti', 1000)->nullable();
                $table->string('modifiedby', 1000)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->dateTime('tglapproval')->nullable();
                $table->dateTime('tglbukacetak')->nullable();
                $table->double('total', 15, 2)->nullable();
                $table->double('totalposting', 15, 2)->nullable();
                $table->double('uangjalan', 15, 2)->nullable();
                $table->double('bbm', 15, 2)->nullable();
                $table->double('uangmakanharian', 15, 2)->nullable();
                $table->double('uangmakanberjenjang', 15, 2)->nullable();
                $table->double('potonganpinjaman', 15, 2)->nullable();
                $table->double('potonganpinjamansemua', 15, 2)->nullable();
                $table->double('deposito', 15, 2)->nullable();
                $table->double('komisisupir', 15, 2)->nullable();
                $table->double('gajisupir', 15, 2)->nullable();
                $table->double('gajikenek', 15, 2)->nullable();
                $table->double('biayaextra', 15, 2)->nullable();
                $table->dateTime('tgldariheaderpengeluaranheader')->nullable();
                $table->dateTime('tglsampaiheaderpengeluaranheader')->nullable();
            });

            $tempgajidetail = '##tempgajidetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempgajidetail, function ($table) {
                $table->string('nobukti', 1000)->nullable();
                $table->double('komisisupir', 15, 2)->nullable();
                $table->double('gajisupir', 15, 2)->nullable();
                $table->double('gajikenek', 15, 2)->nullable();
                $table->double('biayaextra', 15, 2)->nullable();
            });

            $querytempdetail = DB::table("prosesgajisupirheader")->from(DB::raw("prosesgajisupirheader with (readuncommitted)"))
                ->select(
                    'prosesgajisupirheader.nobukti',
                    db::raw("sum(gajisupirdetail.komisisupir) as komisisupir"),
                    db::raw("sum(gajisupirdetail.gajisupir) as gajisupir"),
                    db::raw("sum(gajisupirdetail.gajikenek) as gajikenek"),
                    db::raw("sum(gajisupirdetail.biayatambahan) as biayaextra"),
                )
                ->join(DB::raw("prosesgajisupirdetail with (readuncommitted)"), 'prosesgajisupirheader.nobukti', 'prosesgajisupirdetail.nobukti')
                ->join(DB::raw("gajisupirheader with (readuncommitted)"), 'prosesgajisupirdetail.gajisupir_nobukti', 'gajisupirheader.nobukti')
                ->join(DB::raw("gajisupirdetail with (readuncommitted)"), 'gajisupirheader.nobukti', 'gajisupirdetail.nobukti');


            if (request()->tgldari && request()->tglsampai) {
                $querytempdetail->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
            }
            if ($periode != '') {
                $periode = explode("-", $periode);
                $querytempdetail->whereRaw("MONTH(prosesgajisupirheader.tglbukti) ='" . $periode[0] . "'")
                    ->whereRaw("year(prosesgajisupirheader.tglbukti) ='" . $periode[1] . "'");
            }
            if ($statusCetak != '') {
                $querytempdetail->where("prosesgajisupirheader.statuscetak", $statusCetak);
            }

            $querytempdetail->groupBy('prosesgajisupirheader.nobukti');

            DB::table($tempgajidetail)->insertUsing([
                'nobukti',
                'komisisupir',
                'gajisupir',
                'gajikenek',
                'biayaextra',
            ], $querytempdetail);

            $this->tableTotal = $this->createTempTotal();
            $totalTable = $this->tableTotal;

            $querytemp = DB::table($this->table)->from(DB::raw("prosesgajisupirheader with (readuncommitted)"))

                ->select(

                    'prosesgajisupirheader.id',
                    'prosesgajisupirheader.nobukti',
                    'prosesgajisupirheader.tglbukti',
                    'prosesgajisupirheader.tgldari',
                    'prosesgajisupirheader.tglsampai',
                    'prosesgajisupirheader.keterangan',
                    'prosesgajisupirheader.periode',
                    'prosesgajisupirheader.userapproval',
                    'statusapproval.memo as statusapproval',
                    'statuscetak.memo as statuscetak',
                    'statuscetak.text as statuscetaktext',
                    'prosesgajisupirheader.userbukacetak',
                    'prosesgajisupirheader.jumlahcetak',
                    'prosesgajisupirheader.pengeluaran_nobukti',
                    'prosesgajisupirheader.modifiedby',
                    'prosesgajisupirheader.created_at',
                    'prosesgajisupirheader.updated_at',
                    DB::raw("(case when (year(prosesgajisupirheader.tglapproval) <= 2000) then null else prosesgajisupirheader.tglapproval end ) as tglapproval"),
                    DB::raw("(case when (year(prosesgajisupirheader.tglbukacetak) <= 2000) then null else prosesgajisupirheader.tglbukacetak end ) as tglbukacetak"),
                    // db::raw("(d.total+isnull(c.komisisupir,0)+isnull(c.gajikenek,0) ) as total"),
                    DB::raw("(case when (select text from parameter where grp='PROSES GAJI SUPIR' and subgrp='PISAH GAJI KENEK')= 'YA' then d.totalposting else (d.total+isnull(c.komisisupir,0)+isnull(c.gajikenek,0) ) end) as total"),
                    DB::raw("(case when (select text from parameter where grp='PROSES GAJI SUPIR' and subgrp='PISAH GAJI KENEK')= 'YA' then (d.totalposting-isnull(c.gajikenek,0)) else d.totalposting end) as totalposting"),
                    // 'd.totalposting',
                    'd.uangjalan',
                    'd.bbm',
                    'd.uangmakanharian',
                    'd.uangmakanberjenjang',
                    'd.potonganpinjaman',
                    'd.potonganpinjamansemua',
                    'd.deposito',
                    db::raw("isnull(c.komisisupir,0) as komisisupir"),
                    db::raw("isnull(c.gajisupir,0) as gajisupir"),
                    db::raw("isnull(c.gajikenek,0) as gajikenek"),
                    db::raw("isnull(c.biayaextra,0) as biayaextra"),
                    db::raw("cast((format(pengeluaran.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpengeluaranheader"),
                    db::raw("cast(cast(format((cast((format(pengeluaran.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpengeluaranheader"),

                )

                ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'prosesgajisupirheader.statuscetak', 'statuscetak.id')
                ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'prosesgajisupirheader.statusapproval', 'statusapproval.id')
                ->leftJoin(DB::raw("pengeluaranheader as pengeluaran with (readuncommitted)"), 'prosesgajisupirheader.pengeluaran_nobukti', '=', 'pengeluaran.nobukti')
                ->leftJoin(DB::raw("$totalTable as d"), 'd.nobukti', 'prosesgajisupirheader.nobukti')
                ->leftJoin(DB::raw($tempgajidetail . " c"), 'prosesgajisupirheader.nobukti', 'c.nobukti');

            if (request()->tgldari && request()->tglsampai) {
                $querytemp->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
            }
            if ($periode != '') {
                $periode = explode("-", $periode);
                $querytemp->whereRaw("MONTH(gajisupirheader.tglbukti) ='" . $periode[0] . "'")
                    ->whereRaw("year(gajisupirheader.tglbukti) ='" . $periode[1] . "'");
            }
            if ($statusCetak != '') {
                $querytemp->where("gajisupirheader.statuscetak", $statusCetak);
            }

            DB::table($temtabel)->insertUsing([
                'id',
                'nobukti',
                'tglbukti',
                'tgldari',
                'tglsampai',
                'keterangan',
                'periode',
                'userapproval',
                'statusapproval',
                'statuscetak',
                'statuscetaktext',
                'userbukacetak',
                'jumlahcetak',
                'pengeluaran_nobukti',
                'modifiedby',
                'created_at',
                'updated_at',
                'tglapproval',
                'tglbukacetak',
                'total',
                'totalposting',
                'uangjalan',
                'bbm',
                'uangmakanharian',
                'uangmakanberjenjang',
                'potonganpinjaman',
                'potonganpinjamansemua',
                'deposito',
                'komisisupir',
                'gajisupir',
                'gajikenek',
                'biayaextra',
                'tgldariheaderpengeluaranheader',
                'tglsampaiheaderpengeluaranheader',
            ], $querytemp);
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
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                'a.tgldari',
                'a.tglsampai',
                'a.keterangan',
                'a.periode',
                'a.userapproval',
                'a.statusapproval',
                'a.statuscetak',
                'a.statuscetaktext',
                'a.userbukacetak',
                'a.jumlahcetak',
                'a.pengeluaran_nobukti',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
                'a.tglapproval',
                'a.tglbukacetak',
                'a.total',
                'a.totalposting',
                'a.uangjalan',
                'a.bbm',
                'a.uangmakanharian',
                'a.uangmakanberjenjang',
                'a.potonganpinjaman',
                'a.potonganpinjamansemua',
                'a.deposito',
                'a.komisisupir',
                'a.gajisupir',
                'a.gajikenek',
                'a.biayaextra',
                'a.tgldariheaderpengeluaranheader',
                'a.tglsampaiheaderpengeluaranheader',
            );
        // dd($query->get());


        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);
        $data = $query->get();

        $tempbuktisum = '##tempbuktisum' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempbuktisum, function ($table) {
            $table->string('nobukti', 100)->nullable();
        });
        $databukti = json_decode($data, true);
        foreach ($databukti as $item) {

            DB::table($tempbuktisum)->insert([
                'nobukti' => $item['nobukti'],
            ]);
        }
        $querytotal = DB::table($temtabel)->from(DB::raw($temtabel . " a "))
            ->select(
                db::raw("sum(a.komisisupir) as komisisupir"),
                db::raw("sum(a.gajisupir) as gajisupir"),
                db::raw("sum(a.gajikenek) as gajikenek"),
                db::raw("sum(a.biayaextra) as biayaextra"),
                db::raw("sum(a.total) as total"),
                db::raw("sum(a.totalposting) as totalposting"),
                db::raw("sum(a.uangjalan) as uangjalan"),
                db::raw("sum(a.bbm) as bbm"),
                db::raw("sum(a.uangmakanharian) as uangmakanharian"),
                db::raw("sum(a.uangmakanberjenjang) as uangmakanberjenjang"),
                db::raw("sum(a.potonganpinjaman) as potonganpinjaman"),
                db::raw("sum(a.potonganpinjamansemua) as potonganpinjamansemua"),
                db::raw("sum(a.deposito) as deposito"),
            )
            ->join(db::raw($tempbuktisum . " b "), 'a.nobukti', 'b.nobukti')
            ->first();

        $this->totalAll = $querytotal->total ?? 0;
        $this->totalPosting = $querytotal->totalposting ?? 0;
        $this->totalJalan = $querytotal->uangjalan ?? 0;
        $this->totalGajiSupir = $querytotal->gajisupir ?? 0;
        $this->totalGajiKenek = $querytotal->gajikenek ?? 0;
        $this->totalKomisiSupir = $querytotal->komisisupir ?? 0;
        $this->totalBiayaExtra = $querytotal->biayaextra ?? 0;
        $this->totalBbm = $querytotal->bbm ?? 0;
        $this->totalDeposito = $querytotal->deposito ?? 0;
        $this->totalPotPinj = $querytotal->potonganpinjaman ?? 0;
        $this->totalPotSemua = $querytotal->potonganpinjamansemua ?? 0;
        $this->totalMakanBerjenjang = $querytotal->uangmakanberjenjang ?? 0;
        $this->totalMakan = $querytotal->uangmakanharian ?? 0;


        return $data;
    }

    public function createTempTotal()
    {
        $temp = '##tempRIC' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetch = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))
            ->select(
                DB::raw("distinct(prosesgajisupirheader.nobukti),
                (SELECT SUM(isnull(gajisupirheader.total, 0)+isnull(gajisupirheader.uangmakanharian, 0)+isnull(gajisupirheader.uangmakanberjenjang, 0))
                FROM gajisupirheader 
                WHERE gajisupirheader.nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupirheader.id = prosesgajisupirdetail.prosesgajisupir_id)) AS total,
                (SELECT SUM(gajisupirheader.total)
                FROM gajisupirheader 
                WHERE gajisupirheader.nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupirheader.id = prosesgajisupirdetail.prosesgajisupir_id)) AS totalposting,
                (SELECT SUM(gajisupirheader.uangjalan)
                FROM gajisupirheader 
                WHERE gajisupirheader.nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupirheader.id = prosesgajisupirdetail.prosesgajisupir_id)) AS uangjalan,
                (SELECT SUM(gajisupirheader.bbm)
                FROM gajisupirheader 
                WHERE gajisupirheader.nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupirheader.id = prosesgajisupirdetail.prosesgajisupir_id)) AS bbm,  
                (SELECT SUM(gajisupirheader.uangmakanharian)
                FROM gajisupirheader 
                WHERE gajisupirheader.nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupirheader.id = prosesgajisupirdetail.prosesgajisupir_id)) AS uangmakanharian,  
                (SELECT SUM(isnull(gajisupirheader.uangmakanberjenjang,0))
                FROM gajisupirheader 
                WHERE gajisupirheader.nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupirheader.id = prosesgajisupirdetail.prosesgajisupir_id)) AS uangmakanberjenjang,
                (SELECT SUM(gajisupirheader.potonganpinjaman)
                FROM gajisupirheader 
                WHERE gajisupirheader.nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupirheader.id = prosesgajisupirdetail.prosesgajisupir_id)) AS potonganpinjaman,  
                (SELECT SUM(gajisupirheader.potonganpinjamansemua)
                FROM gajisupirheader 
                WHERE gajisupirheader.nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupirheader.id = prosesgajisupirdetail.prosesgajisupir_id)) AS potonganpinjamansemua,  
                 
                (SELECT SUM(gajisupirheader.deposito)
                FROM gajisupirheader 
                WHERE gajisupirheader.nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupirheader.id = prosesgajisupirdetail.prosesgajisupir_id)) AS deposito
            ")
            )
            ->join(DB::raw("prosesgajisupirdetail with (readuncommitted)"), 'prosesgajisupirdetail.gajisupir_nobukti', 'gajisupirheader.nobukti')
            ->join(DB::raw("prosesgajisupirheader with (readuncommitted)"), 'prosesgajisupirheader.id', 'prosesgajisupirdetail.prosesgajisupir_id')
            ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupirdetail.prosesgajisupir_id = prosesgajisupirheader.id)");


        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->bigInteger('total')->nullable();
            $table->bigInteger('totalposting')->nullable();
            $table->bigInteger('uangjalan')->nullable();
            $table->bigInteger('bbm')->nullable();
            $table->bigInteger('uangmakanharian')->nullable();
            $table->bigInteger('uangmakanberjenjang')->nullable();
            $table->bigInteger('potonganpinjaman')->nullable();
            $table->bigInteger('potonganpinjamansemua')->nullable();
            $table->bigInteger('deposito')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'total', 'totalposting', 'uangjalan', 'bbm', 'uangmakanharian', 'uangmakanberjenjang', 'potonganpinjaman', 'potonganpinjamansemua', 'deposito'], $fetch);

        return $temp;
    }


    public function getEdit($gajiId, $aksi)
    {
        $this->setRequestParameters();

        $tempRIC = $this->createTempRIC($gajiId, null, null, $aksi);
        $query = DB::table($tempRIC)
            ->select(
                $tempRIC . '.idric',
                $tempRIC . '.nobuktiric',
                $tempRIC . '.tglbuktiric',
                $tempRIC . '.supir_id',
                $tempRIC . '.supir',
                $tempRIC . '.tgldariric',
                $tempRIC . '.tglsampairic',
                $tempRIC . '.borongan',
                $tempRIC . '.uangjalan',
                $tempRIC . '.bbm',
                $tempRIC . '.uangmakanharian',
                $tempRIC . '.uangmakanberjenjang',
                $tempRIC . '.potonganpinjaman',
                $tempRIC . '.potonganpinjamansemua',
                $tempRIC . '.deposito',
                $tempRIC . '.komisisupir',
                $tempRIC . '.tolsupir',
                $tempRIC . '.gajisupir',
                $tempRIC . '.gajikenek',
                $tempRIC . '.extra',
            );
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        if ($this->params['sortIndex'] == 'id') {
            $query->orderBy($tempRIC . '.nobuktiric', $this->params['sortOrder']);
        } else {
            $query->orderBy($tempRIC . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
        $this->filterTrip($query, $tempRIC);
        if ($aksi != '') {
            $this->paginate($query);
        }
        // dd($query->toSql());
        $data = $query->get();

        $this->totalBorongan = $query->sum('borongan');
        $this->totalUangJalan = $query->sum('uangjalan');
        $this->totalUangBBM = $query->sum('bbm');
        $this->totalUangMakan = $query->sum('uangmakanharian');
        $this->totalUangMakanBerjenjang = $query->sum('uangmakanberjenjang');
        $this->totalPotPinjaman = $query->sum('potonganpinjaman');
        $this->totalPotPinjSemua = $query->sum('potonganpinjamansemua');
        $this->totalDeposito = $query->sum('deposito');
        $this->totalKomisi = $query->sum('komisisupir');
        $this->totalTol = $query->sum('tolsupir');
        $this->totalGajiSupir = $query->sum('gajisupir');
        $this->totalGajikenek = $query->sum('gajikenek');
        return $data;
    }

    public function getAllEdit($gajiId, $dari, $sampai, $aksi)
    {
        $this->setRequestParameters();
        $tempRIC = $this->createTempRIC($gajiId, $dari, $sampai, $aksi);
        $query = DB::table($tempRIC)
            ->select(
                $tempRIC . '.idric',
                $tempRIC . '.nobuktiric',
                $tempRIC . '.tglbuktiric',
                $tempRIC . '.supir_id',
                $tempRIC . '.supir',
                $tempRIC . '.tgldariric',
                $tempRIC . '.tglsampairic',
                $tempRIC . '.borongan',
                $tempRIC . '.uangjalan',
                $tempRIC . '.bbm',
                $tempRIC . '.uangmakanharian',
                $tempRIC . '.uangmakanberjenjang',
                $tempRIC . '.potonganpinjaman',
                $tempRIC . '.potonganpinjamansemua',
                $tempRIC . '.deposito',
                $tempRIC . '.komisisupir',
                $tempRIC . '.tolsupir',
                $tempRIC . '.gajisupir',
                $tempRIC . '.gajikenek',
                $tempRIC . '.extra',
            );
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $query->orderBy($tempRIC . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        $this->filterTrip($query, $tempRIC);
        $this->paginate($query);
        $data = $query->get();

        $this->totalBorongan = $query->sum('borongan');
        $this->totalUangJalan = $query->sum('uangjalan');
        $this->totalUangBBM = $query->sum('bbm');
        $this->totalUangMakan = $query->sum('uangmakanharian');
        $this->totalUangMakanBerjenjang = $query->sum('uangmakanberjenjang');
        $this->totalPotPinjaman = $query->sum('potonganpinjaman');
        $this->totalPotPinjSemua = $query->sum('potonganpinjamansemua');
        $this->totalDeposito = $query->sum('deposito');
        $this->totalKomisi = $query->sum('komisisupir');
        $this->totalTol = $query->sum('tolsupir');
        return $data;
    }

    public function createTempRIC($gajiId, $dari = null, $sampai = null, $aksi)
    {
        $temp = '##tempRIC' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $tempDetail = '##tempDetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetchDetail = DB::table("gajisupirdetail")->from(DB::raw("gajisupirdetail with (readuncommitted)"))
            ->select(
                'gajisupirdetail.nobukti',
                DB::raw("SUM(gajisupirdetail.gajisupir) AS gajisupir"),
                DB::raw("SUM(gajisupirdetail.gajikenek) AS gajikenek"),
                DB::raw("SUM(gajisupirdetail.biayatambahan) AS extra"),
            )
            ->leftJoin(DB::raw("prosesgajisupirdetail with (readuncommitted)"), 'prosesgajisupirdetail.gajisupir_nobukti', 'gajisupirdetail.nobukti')
            ->where('prosesgajisupirdetail.prosesgajisupir_id', $gajiId)
            ->groupBy('gajisupirdetail.nobukti');

        Schema::create($tempDetail, function ($table) {
            $table->string('nobukti');
            $table->float('gajisupir')->nullable();
            $table->float('gajikenek')->nullable();
            $table->float('extra')->nullable();
        });

        DB::table($tempDetail)->insertUsing(['nobukti', 'gajisupir', 'gajikenek', 'extra'], $fetchDetail);

        $fetch = ProsesGajiSupirDetail::from(DB::raw("prosesgajisupirdetail with (readuncommitted)"))
            ->select(
                'gajisupirheader.id as idric',
                'prosesgajisupirdetail.gajisupir_nobukti as nobuktiric',
                'gajisupirheader.tglbukti as tglbuktiric',
                'gajisupirheader.supir_id',
                'supir.namasupir as supir',
                'gajisupirheader.tgldari as tgldariric',
                'gajisupirheader.tglsampai as tglsampairic',
                'gajisupirheader.total as borongan',
                'gajisupirheader.uangjalan',
                'gajisupirheader.bbm',
                'gajisupirheader.uangmakanharian',
                DB::raw("(case when gajisupirheader.uangmakanberjenjang IS NULL then 0 else gajisupirheader.uangmakanberjenjang end) as uangmakanberjenjang"),
                'gajisupirheader.potonganpinjaman',
                'gajisupirheader.potonganpinjamansemua',
                'gajisupirheader.deposito',
                'gajisupirheader.komisisupir',
                'gajisupirheader.tolsupir',
                'detail.gajisupir',
                'detail.gajikenek',
                'detail.extra'
            )
            ->leftJoin(DB::raw("gajisupirheader with (readuncommitted)"), 'prosesgajisupirdetail.gajisupir_nobukti', 'gajisupirheader.nobukti')
            ->leftJoin(DB::raw("$tempDetail as detail with (readuncommitted)"), 'prosesgajisupirdetail.gajisupir_nobukti', 'detail.nobukti')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'gajisupirheader.supir_id', 'supir.id')
            ->where('prosesgajisupirdetail.prosesgajisupir_id', $gajiId);

        Schema::create($temp, function ($table) {
            $table->bigInteger('idric');
            $table->string('nobuktiric');
            $table->date('tglbuktiric')->nullable();
            $table->bigInteger('supir_id');
            $table->string('supir');
            $table->date('tgldariric')->nullable();
            $table->date('tglsampairic')->nullable();
            $table->bigInteger('borongan')->nullable();
            $table->bigInteger('uangjalan')->nullable();
            $table->bigInteger('bbm')->nullable();
            $table->bigInteger('uangmakanharian')->nullable();
            $table->bigInteger('uangmakanberjenjang')->nullable();
            $table->bigInteger('potonganpinjaman')->nullable();
            $table->bigInteger('potonganpinjamansemua')->nullable();
            $table->bigInteger('deposito')->nullable();
            $table->bigInteger('komisisupir')->nullable();
            $table->bigInteger('tolsupir')->nullable();
            $table->float('gajisupir')->nullable();
            $table->float('gajikenek')->nullable();
            $table->float('extra')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['idric', 'nobuktiric', 'tglbuktiric', 'supir_id', 'supir', 'tgldariric', 'tglsampairic', 'borongan', 'uangjalan', 'bbm', 'uangmakanharian', 'uangmakanberjenjang', 'potonganpinjaman', 'potonganpinjamansemua', 'deposito', 'komisisupir', 'tolsupir', 'gajisupir', 'gajikenek', 'extra'], $fetch);

        if ($aksi != '') {
            $tempDetail = '##tempDet' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            $fetchDetail = DB::table("gajisupirdetail")->from(DB::raw("gajisupirdetail with (readuncommitted)"))
                ->select(
                    'gajisupirdetail.nobukti',
                    DB::raw("SUM(gajisupirdetail.gajisupir) AS gajisupir"),
                    DB::raw("SUM(gajisupirdetail.gajikenek) AS gajikenek"),
                    DB::raw("SUM(gajisupirdetail.biayatambahan) AS extra"),
                )
                ->leftJoin(DB::raw("gajisupirheader with (readuncommitted)"), 'gajisupirheader.nobukti', 'gajisupirdetail.nobukti')
                ->where('gajisupirheader.tglbukti', '>=', $dari)
                ->where('gajisupirheader.tglbukti', '<=', $sampai)
                ->whereRaw("gajisupirheader.nobukti not in(select gajisupir_nobukti from prosesgajisupirdetail)")
                ->groupBy('gajisupirdetail.nobukti');

            Schema::create($tempDetail, function ($table) {
                $table->string('nobukti');
                $table->float('gajisupir')->nullable();
                $table->float('gajikenek')->nullable();
                $table->float('extra')->nullable();
            });

            DB::table($tempDetail)->insertUsing(['nobukti', 'gajisupir', 'gajikenek', 'extra'], $fetchDetail);

            $fetch = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))
                ->select(
                    'gajisupirheader.id as idric',
                    'gajisupirheader.nobukti as nobuktiric',
                    'gajisupirheader.tglbukti as tglbuktiric',
                    'gajisupirheader.supir_id',
                    'supir.namasupir as supir',
                    'gajisupirheader.tgldari as tgldariric',
                    'gajisupirheader.tglsampai as tglsampairic',
                    'gajisupirheader.total as borongan',
                    'gajisupirheader.uangjalan',
                    'gajisupirheader.bbm',
                    'gajisupirheader.uangmakanharian',
                    DB::raw("(case when gajisupirheader.uangmakanberjenjang IS NULL then 0 else gajisupirheader.uangmakanberjenjang end) as uangmakanberjenjang"),
                    'gajisupirheader.potonganpinjaman',
                    'gajisupirheader.potonganpinjamansemua',
                    'gajisupirheader.deposito',
                    'gajisupirheader.komisisupir',
                    'gajisupirheader.tolsupir',
                    'detail.gajisupir',
                    'detail.gajikenek',
                    'detail.extra'
                )
                ->leftJoin(DB::raw("supir with (readuncommitted)"), 'gajisupirheader.supir_id', 'supir.id')
                ->leftJoin(DB::raw("$tempDetail as detail with (readuncommitted)"), 'gajisupirheader.nobukti', 'detail.nobukti')
                ->where('gajisupirheader.tglbukti', '>=', $dari)
                ->where('gajisupirheader.tglbukti', '<=', $sampai)
                ->whereRaw("gajisupirheader.nobukti not in(select gajisupir_nobukti from prosesgajisupirdetail)");

            $tes = DB::table($temp)->insertUsing(['idric', 'nobuktiric', 'tglbuktiric', 'supir_id', 'supir', 'tgldariric', 'tglsampairic', 'borongan', 'uangjalan', 'bbm', 'uangmakanharian', 'uangmakanberjenjang', 'potonganpinjaman', 'potonganpinjamansemua', 'deposito', 'komisisupir', 'tolsupir', 'gajisupir', 'gajikenek', 'extra'], $fetch);
        }

        return $temp;
    }
    public function selectColumns()
    {
        $temtabel = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;


        Schema::create($temtabel, function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->date('tgldari')->nullable();
            $table->date('tglsampai')->nullable();
            $table->longText('keterangan')->nullable();
            $table->longText('periode')->nullable();
            $table->longText('userapproval')->nullable();
            $table->longText('statusapproval')->nullable();
            $table->longText('statuscetak')->nullable();
            $table->longText('statuscetaktext')->nullable();
            $table->longText('userbukacetak')->nullable();
            $table->integer('jumlahcetak')->nullable();
            $table->string('pengeluaran_nobukti', 1000)->nullable();
            $table->string('modifiedby', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('tglapproval')->nullable();
            $table->dateTime('tglbukacetak')->nullable();
            $table->double('total', 15, 2)->nullable();
            $table->double('totalposting', 15, 2)->nullable();
            $table->double('uangjalan', 15, 2)->nullable();
            $table->double('bbm', 15, 2)->nullable();
            $table->double('uangmakanharian', 15, 2)->nullable();
            $table->double('uangmakanberjenjang', 15, 2)->nullable();
            $table->double('potonganpinjaman', 15, 2)->nullable();
            $table->double('potonganpinjamansemua', 15, 2)->nullable();
            $table->double('deposito', 15, 2)->nullable();
            $table->double('komisisupir', 15, 2)->nullable();
            $table->double('gajisupir', 15, 2)->nullable();
            $table->double('gajikenek', 15, 2)->nullable();
            $table->double('biayaextra', 15, 2)->nullable();
            $table->dateTime('tgldariheaderpengeluaranheader')->nullable();
            $table->dateTime('tglsampaiheaderpengeluaranheader')->nullable();
        });

        $tempgajidetail = '##tempgajidetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempgajidetail, function ($table) {
            $table->string('nobukti', 1000)->nullable();
            $table->double('komisisupir', 15, 2)->nullable();
            $table->double('gajisupir', 15, 2)->nullable();
            $table->double('gajikenek', 15, 2)->nullable();
            $table->double('biayaextra', 15, 2)->nullable();
        });

        $querytempdetail = DB::table("prosesgajisupirheader")->from(DB::raw("prosesgajisupirheader with (readuncommitted)"))
            ->select(
                'prosesgajisupirheader.nobukti',
                db::raw("sum(gajisupirdetail.komisisupir) as komisisupir"),
                db::raw("sum(gajisupirdetail.gajisupir) as gajisupir"),
                db::raw("sum(gajisupirdetail.gajikenek) as gajikenek"),
                db::raw("sum(gajisupirdetail.biayatambahan) as biayaextra"),
            )
            ->join(DB::raw("prosesgajisupirdetail with (readuncommitted)"), 'prosesgajisupirheader.nobukti', 'prosesgajisupirdetail.nobukti')
            ->join(DB::raw("gajisupirheader with (readuncommitted)"), 'prosesgajisupirdetail.gajisupir_nobukti', 'gajisupirheader.nobukti')
            ->join(DB::raw("gajisupirdetail with (readuncommitted)"), 'gajisupirheader.nobukti', 'gajisupirdetail.nobukti');


        if (request()->tgldari && request()->tglsampai) {
            $querytempdetail->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        $querytempdetail->groupBy('prosesgajisupirheader.nobukti');

        DB::table($tempgajidetail)->insertUsing([
            'nobukti',
            'komisisupir',
            'gajisupir',
            'gajikenek',
            'biayaextra',
        ], $querytempdetail);

        $this->tableTotal = $this->createTempTotal();
        $querytemp = DB::table($this->table)->from(DB::raw("prosesgajisupirheader with (readuncommitted)"))

            ->select(

                'prosesgajisupirheader.id',
                'prosesgajisupirheader.nobukti',
                'prosesgajisupirheader.tglbukti',
                'prosesgajisupirheader.tgldari',
                'prosesgajisupirheader.tglsampai',
                'prosesgajisupirheader.keterangan',
                'prosesgajisupirheader.periode',
                'prosesgajisupirheader.userapproval',
                'statusapproval.memo as statusapproval',
                'statuscetak.memo as statuscetak',
                'statuscetak.text as statuscetaktext',
                'prosesgajisupirheader.userbukacetak',
                'prosesgajisupirheader.jumlahcetak',
                'prosesgajisupirheader.pengeluaran_nobukti',
                'prosesgajisupirheader.modifiedby',
                'prosesgajisupirheader.created_at',
                'prosesgajisupirheader.updated_at',
                DB::raw("(case when (year(prosesgajisupirheader.tglapproval) <= 2000) then null else prosesgajisupirheader.tglapproval end ) as tglapproval"),
                DB::raw("(case when (year(prosesgajisupirheader.tglbukacetak) <= 2000) then null else prosesgajisupirheader.tglbukacetak end ) as tglbukacetak"),
                db::raw("(" . $this->tableTotal . ".total+isnull(c.komisisupir,0)+isnull(c.gajikenek,0) ) as total"),
                $this->tableTotal . '.totalposting',
                $this->tableTotal . '.uangjalan',
                $this->tableTotal . '.bbm',
                $this->tableTotal . '.uangmakanharian',
                $this->tableTotal . '.uangmakanberjenjang',
                $this->tableTotal . '.potonganpinjaman',
                $this->tableTotal . '.potonganpinjamansemua',
                $this->tableTotal . '.deposito',
                db::raw("isnull(c.komisisupir,0) as komisisupir"),
                db::raw("isnull(c.gajisupir,0) as gajisupir"),
                db::raw("isnull(c.gajikenek,0) as gajikenek"),
                db::raw("isnull(c.biayaextra,0) as biayaextra"),
                db::raw("cast((format(pengeluaran.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpengeluaranheader"),
                db::raw("cast(cast(format((cast((format(pengeluaran.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpengeluaranheader"),

            )

            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'prosesgajisupirheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'prosesgajisupirheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("pengeluaranheader as pengeluaran with (readuncommitted)"), 'prosesgajisupirheader.pengeluaran_nobukti', '=', 'pengeluaran.nobukti')
            ->leftJoin($this->tableTotal, $this->tableTotal . '.nobukti', 'prosesgajisupirheader.nobukti')
            ->leftJoin(DB::raw($tempgajidetail . " c"), 'prosesgajisupirheader.nobukti', 'c.nobukti');

        DB::table($temtabel)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'tgldari',
            'tglsampai',
            'keterangan',
            'periode',
            'userapproval',
            'statusapproval',
            'statuscetak',
            'statuscetaktext',
            'userbukacetak',
            'jumlahcetak',
            'pengeluaran_nobukti',
            'modifiedby',
            'created_at',
            'updated_at',
            'tglapproval',
            'tglbukacetak',
            'total',
            'totalposting',
            'uangjalan',
            'bbm',
            'uangmakanharian',
            'uangmakanberjenjang',
            'potonganpinjaman',
            'potonganpinjamansemua',
            'deposito',
            'komisisupir',
            'gajisupir',
            'gajikenek',
            'biayaextra',
            'tgldariheaderpengeluaranheader',
            'tglsampaiheaderpengeluaranheader',
        ], $querytemp);


        $query = DB::table($temtabel)->from(DB::raw($temtabel . " a "))
            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                'a.tgldari',
                'a.tglsampai',
                'a.keterangan',
                'a.periode',
                'a.userapproval',
                'a.statusapproval',
                'a.statuscetak',
                'a.statuscetaktext',
                'a.userbukacetak',
                'a.jumlahcetak',
                'a.pengeluaran_nobukti',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
                'a.tglapproval',
                'a.tglbukacetak',
                'a.total',
                'a.totalposting',
                'a.uangjalan',
                'a.bbm',
                'a.uangmakanharian',
                'a.uangmakanberjenjang',
                'a.potonganpinjaman',
                'a.potonganpinjamansemua',
                'a.deposito',
                'a.komisisupir',
                'a.gajisupir',
                'a.gajikenek',
                'a.biayaextra',
                'a.tgldariheaderpengeluaranheader',
                'a.tglsampaiheaderpengeluaranheader',
            );

        return $query;
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->integer('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->date('tgldari')->nullable();
            $table->date('tglsampai')->nullable();
            $table->longText('keterangan')->nullable();
            $table->longText('periode')->nullable();
            $table->longText('userapproval')->nullable();
            $table->longText('statusapproval')->nullable();
            $table->longText('statuscetak')->nullable();
            $table->longText('statuscetaktext')->nullable();
            $table->longText('userbukacetak')->nullable();
            $table->integer('jumlahcetak')->nullable();
            $table->string('pengeluaran_nobukti', 1000)->nullable();
            $table->string('modifiedby', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('tglapproval')->nullable();
            $table->dateTime('tglbukacetak')->nullable();
            $table->double('total', 15, 2)->nullable();
            $table->double('totalposting', 15, 2)->nullable();
            $table->double('uangjalan', 15, 2)->nullable();
            $table->double('bbm', 15, 2)->nullable();
            $table->double('uangmakanharian', 15, 2)->nullable();
            $table->double('uangmakanberjenjang', 15, 2)->nullable();
            $table->double('potonganpinjaman', 15, 2)->nullable();
            $table->double('potonganpinjamansemua', 15, 2)->nullable();
            $table->double('deposito', 15, 2)->nullable();
            $table->double('komisisupir', 15, 2)->nullable();
            $table->double('gajisupir', 15, 2)->nullable();
            $table->double('gajikenek', 15, 2)->nullable();
            $table->double('biayaextra', 15, 2)->nullable();
            $table->dateTime('tgldariheaderpengeluaranheader')->nullable();
            $table->dateTime('tglsampaiheaderpengeluaranheader')->nullable();
            $table->increments('position');
        });
        if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
            request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
        }

        $this->setRequestParameters();
        $query = $this->selectColumns();
        $this->sort($query);
        $models = $this->filter($query);
        $models = $query
            ->whereBetween('a.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);

        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'tgldari',
            'tglsampai',
            'keterangan',
            'periode',
            'userapproval',
            'statusapproval',
            'statuscetak',
            'statuscetaktext',
            'userbukacetak',
            'jumlahcetak',
            'pengeluaran_nobukti',
            'modifiedby',
            'created_at',
            'updated_at',
            'tglapproval',
            'tglbukacetak',
            'total',
            'totalposting',
            'uangjalan',
            'bbm',
            'uangmakanharian',
            'uangmakanberjenjang',
            'potonganpinjaman',
            'potonganpinjamansemua',
            'deposito',
            'komisisupir',
            'gajisupir',
            'gajikenek',
            'biayaextra',
            'tgldariheaderpengeluaranheader',
            'tglsampaiheaderpengeluaranheader',
        ], $models);

        return $temp;
    }


    public function getRic($dari, $sampai)
    {
        $this->setRequestParameters();
        $getRIC = $this->createTempGetRIC($dari, $sampai);

        $query = DB::table($getRIC)
            ->select(
                $getRIC . '.idric',
                $getRIC . '.nobuktiric',
                $getRIC . '.tglbuktiric',
                $getRIC . '.supir_id',
                $getRIC . '.supir',
                $getRIC . '.tgldariric',
                $getRIC . '.tglsampairic',
                $getRIC . '.borongan',
                $getRIC . '.uangjalan',
                $getRIC . '.bbm',
                $getRIC . '.uangmakanharian',
                $getRIC . '.uangmakanberjenjang',
                $getRIC . '.potonganpinjaman',
                $getRIC . '.potonganpinjamansemua',
                $getRIC . '.deposito',
                $getRIC . '.komisisupir',
                $getRIC . '.tolsupir',
                $getRIC . '.gajisupir',
                $getRIC . '.gajikenek',
                $getRIC . '.extra',
            );
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $query->orderBy($getRIC . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        $this->filterTrip($query, $getRIC);
        $this->paginate($query);
        $data = $query->get();

        $this->totalBorongan = $query->sum('borongan');
        $this->totalUangJalan = $query->sum('uangjalan');
        $this->totalUangBBM = $query->sum('bbm');
        $this->totalUangMakan = $query->sum('uangmakanharian');
        $this->totalUangMakanBerjenjang = $query->sum('uangmakanberjenjang');
        $this->totalPotPinjaman = $query->sum('potonganpinjaman');
        $this->totalPotPinjSemua = $query->sum('potonganpinjamansemua');
        $this->totalDeposito = $query->sum('deposito');
        $this->totalKomisi = $query->sum('komisisupir');
        $this->totalTol = $query->sum('tolsupir');
        $this->totalGajiSupir = $query->sum('gajisupir');
        $this->totalGajikenek = $query->sum('gajikenek');
        return $data;
    }

    public function createTempGetRIC($dari, $sampai)
    {

        $tempDetail = '##tempDetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetchDetail = DB::table("gajisupirdetail")->from(DB::raw("gajisupirdetail with (readuncommitted)"))
            ->select(
                'gajisupirdetail.nobukti',
                DB::raw("SUM(gajisupirdetail.gajisupir) AS gajisupir"),
                DB::raw("SUM(gajisupirdetail.gajikenek) AS gajikenek"),
                DB::raw("SUM(gajisupirdetail.biayatambahan) AS extra"),
            )
            ->leftJoin(DB::raw("gajisupirheader with (readuncommitted)"), 'gajisupirheader.nobukti', 'gajisupirdetail.nobukti')
            ->where('gajisupirheader.tglbukti', '>=', $dari)
            ->where('gajisupirheader.tglbukti', '<=', $sampai)
            ->whereRaw("gajisupirheader.nobukti not in(select gajisupir_nobukti from prosesgajisupirdetail)")
            ->groupBy('gajisupirdetail.nobukti');

        Schema::create($tempDetail, function ($table) {
            $table->string('nobukti');
            $table->float('gajisupir')->nullable();
            $table->float('gajikenek')->nullable();
            $table->float('extra')->nullable();
        });

        DB::table($tempDetail)->insertUsing(['nobukti', 'gajisupir', 'gajikenek', 'extra'], $fetchDetail);


        $temp = '##tempRIC' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetch = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))
            ->select(
                'gajisupirheader.id as idric',
                'gajisupirheader.nobukti as nobuktiric',
                'gajisupirheader.tglbukti as tglbuktiric',
                'gajisupirheader.supir_id',
                'supir.namasupir as supir',
                'gajisupirheader.tgldari as tgldariric',
                'gajisupirheader.tglsampai as tglsampairic',
                'gajisupirheader.total as borongan',
                'gajisupirheader.uangjalan',
                'gajisupirheader.bbm',
                'gajisupirheader.uangmakanharian',
                DB::raw("(case when gajisupirheader.uangmakanberjenjang IS NULL then 0 else gajisupirheader.uangmakanberjenjang end) as uangmakanberjenjang"),
                'gajisupirheader.potonganpinjaman',
                'gajisupirheader.potonganpinjamansemua',
                'gajisupirheader.deposito',
                'gajisupirheader.komisisupir',
                'gajisupirheader.tolsupir',
                'detail.gajisupir',
                'detail.gajikenek',
                'detail.extra'
            )
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'gajisupirheader.supir_id', 'supir.id')
            ->leftJoin(DB::raw("$tempDetail as detail with (readuncommitted)"), 'gajisupirheader.nobukti', 'detail.nobukti')
            ->where('gajisupirheader.tglbukti', '>=', $dari)
            ->where('gajisupirheader.tglbukti', '<=', $sampai)
            ->whereRaw("gajisupirheader.nobukti not in(select gajisupir_nobukti from prosesgajisupirdetail)");

        Schema::create($temp, function ($table) {
            $table->bigInteger('idric');
            $table->string('nobuktiric');
            $table->date('tglbuktiric')->nullable();
            $table->bigInteger('supir_id');
            $table->string('supir');
            $table->date('tgldariric')->nullable();
            $table->date('tglsampairic')->nullable();
            $table->bigInteger('borongan')->nullable();
            $table->bigInteger('uangjalan')->nullable();
            $table->bigInteger('bbm')->nullable();
            $table->bigInteger('uangmakanharian')->nullable();
            $table->bigInteger('uangmakanberjenjang')->nullable();
            $table->bigInteger('potonganpinjaman')->nullable();
            $table->bigInteger('potonganpinjamansemua')->nullable();
            $table->bigInteger('deposito')->nullable();
            $table->bigInteger('komisisupir')->nullable();
            $table->bigInteger('tolsupir')->nullable();
            $table->float('gajisupir')->nullable();
            $table->float('gajikenek')->nullable();
            $table->float('extra')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['idric', 'nobuktiric', 'tglbuktiric', 'supir_id', 'supir', 'tgldariric', 'tglsampairic', 'borongan', 'uangjalan', 'bbm', 'uangmakanharian', 'uangmakanberjenjang', 'potonganpinjaman', 'potonganpinjamansemua', 'deposito', 'komisisupir', 'tolsupir', 'gajisupir', 'gajikenek', 'extra'], $fetch);

        return $temp;
    }

    public function filterTrip($query, $table, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            // $query = $query->where($table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            // $query = $query->orWhere($table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->OrwhereRaw($table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function getPotSemua($dari, $sampai)
    {
        $gajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->whereRaw("tglbukti >= '$dari'")->whereRaw("tglbukti <= '$sampai'")
            ->whereRaw("gajisupirheader.nobukti not in(select gajisupir_nobukti from prosesgajisupirdetail)")->get();

        $total = 0;
        foreach ($gajiSupir as $key => $value) {
            $ricId = $value->id;
            $potongan = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
                ->select('nominal')
                ->where('gajisupir_id', $ricId)
                ->where('supir_id', 0)
                ->get();

            $nominal = $potongan->sum('nominal');
            if ($nominal != 0) {
                $total = $total + $nominal;
            }
        }
        return $total;
    }

    public function getPotPribadi($dari, $sampai)
    {
        $gajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->whereRaw("tglbukti >= '$dari'")->whereRaw("tglbukti <= '$sampai'")
            ->whereRaw("gajisupirheader.nobukti not in(select gajisupir_nobukti from prosesgajisupirdetail)")->get();
        $total = 0;
        foreach ($gajiSupir as $key => $value) {
            $ricId = $value->id;
            $potongan = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
                ->select('nominal')
                ->where('gajisupir_id', $ricId)
                ->where('supir_id', '!=', 0)
                ->get();

            $nominal = $potongan->sum('nominal');
            if ($nominal != 0) {
                $total = $total + $nominal;
            }
        }
        return $total;
    }

    public function getDeposito($dari, $sampai)
    {
        $gajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->whereRaw("tglbukti >= '$dari'")->whereRaw("tglbukti <= '$sampai'")
            ->whereRaw("gajisupirheader.nobukti not in(select gajisupir_nobukti from prosesgajisupirdetail)")->get();
        $total = 0;
        foreach ($gajiSupir as $key => $value) {
            $ricId = $value->id;
            $potongan = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))
                ->where('gajisupir_id', $ricId)
                ->first();

            if ($potongan != null) {
                $total = $total + $potongan->nominal;
            }
        }
        return $total;
    }

    public function getBBM($dari, $sampai)
    {
        $gajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->whereRaw("tglbukti >= '$dari'")->whereRaw("tglbukti <= '$sampai'")
            ->whereRaw("gajisupirheader.nobukti not in(select gajisupir_nobukti from prosesgajisupirdetail)")->get();
        $total = 0;
        foreach ($gajiSupir as $key => $value) {
            $ricId = $value->id;
            $potongan = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))
                ->where('gajisupir_id', $ricId)
                ->first();

            if ($potongan != null) {
                $total = $total + $potongan->nominal;
            }
        }
        return $total;
    }

    public function getPinjaman($dari, $sampai)
    {
        $gajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->whereRaw("tglbukti >= '$dari'")->whereRaw("tglbukti <= '$sampai'")
            ->whereRaw("gajisupirheader.nobukti not in(select gajisupir_nobukti from prosesgajisupirdetail)")->get();
        $total = 0;
        foreach ($gajiSupir as $key => $value) {
            $ricId = $value->id;
            $potongan = GajiSupirPinjaman::from(DB::raw("gajisupirpinjaman with (readuncommitted)"))
                ->where('gajisupir_id', $ricId)
                ->first();

            if ($potongan != null) {
                $total = $total + $potongan->nominal;
            }
        }
        return $total;
    }

    public function findAll($id)
    {
        $parameter = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JUDULAN LAPORAN')->where('subgrp', 'JUDULAN LAPORAN')->first();
        $query = ProsesGajiSupirHeader::from(DB::raw("prosesgajisupirheader with (readuncommitted)"))
            ->select(
                'prosesgajisupirheader.id',
                'prosesgajisupirheader.nobukti',
                'prosesgajisupirheader.tglbukti',
                'prosesgajisupirheader.bank_id',
                'prosesgajisupirheader.tgldari',
                'prosesgajisupirheader.tglsampai',
                'prosesgajisupirheader.statuscetak',
                'bank.namabank as bank',
                'prosesgajisupirheader.pengeluaran_nobukti as nobuktiPR',
                DB::raw("'$parameter->text' as judul"),
                DB::raw("'Laporan Proses Gaji Supir' as judulLaporan"),
            )->leftJoin(DB::raw("bank with (readuncommitted)"), 'prosesgajisupirheader.bank_id', 'bank.id')
            ->where('prosesgajisupirheader.id', $id)
            ->first();

        return $query;
    }

    public function showPotSemua($id)
    {
        $gajidetail = ProsesGajiSupirDetail::from(DB::raw("prosesgajisupirdetail with (readuncommitted)"))->where('prosesgajisupir_id', $id)->get();
        $total = 0;
        $tes = '';
        foreach ($gajidetail as $key => $value) {
            $potongan = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
                ->where('gajisupir_nobukti', $value->gajisupir_nobukti)
                ->where('supir_id', '0')
                ->get();

            $nominal = $potongan->sum('nominal');
            if ($nominal != 0) {
                $total = $total + $nominal;
            }

            $fetchPS = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $value->gajisupir_nobukti)->where('supir_id', '0')->first();
            if (isset($fetchPS)) {
                $tes = $fetchPS->penerimaantrucking_nobukti;
            }
        }


        if ($tes != '') {

            $penerimaan = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                ->select('penerimaantruckingheader.bank_id', 'bank.namabank as bank', 'penerimaantruckingheader.penerimaan_nobukti')
                ->join(DB::raw("bank with (readuncommitted)"), 'penerimaantruckingheader.bank_id', 'bank.id')->where('nobukti', $tes)->first();
            $data = [
                'bank_id' => $penerimaan->bank_id,
                'bankPS' => $penerimaan->bank,
                'nobuktiPS' => $penerimaan->penerimaan_nobukti,
                'nomPS' => $total
            ];
            return $data;
        }
    }
    public function showPotPribadi($id)
    {
        $gajidetail = ProsesGajiSupirDetail::from(DB::raw("prosesgajisupirdetail with (readuncommitted)"))->where('prosesgajisupir_id', $id)->get();
        $total = 0;
        $tes = '';
        foreach ($gajidetail as $key => $value) {
            $potongan = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
                ->where('gajisupir_nobukti', $value->gajisupir_nobukti)
                ->where('supir_id', '!=', '0')
                ->get();

            $nominal = $potongan->sum('nominal');
            if ($nominal != 0) {
                $total = $total + $nominal;
            }

            $fetchPP = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $value->gajisupir_nobukti)->where('supir_id', '!=', '0')->first();
            if (isset($fetchPP)) {
                $tes = $fetchPP->penerimaantrucking_nobukti;
            }
        }


        if ($tes != '') {

            $penerimaan = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                ->select('penerimaantruckingheader.bank_id', 'bank.namabank as bank', 'penerimaantruckingheader.penerimaan_nobukti')
                ->join(DB::raw("bank with (readuncommitted)"), 'penerimaantruckingheader.bank_id', 'bank.id')->where('nobukti', $tes)->first();
            $data = [
                'bank_id' => $penerimaan->bank_id,
                'bankPP' => $penerimaan->bank,
                'nobuktiPP' => $penerimaan->penerimaan_nobukti,
                'nomPP' => $total
            ];
            return $data;
        }
    }
    public function showDeposito($id)
    {
        $gajidetail = ProsesGajiSupirDetail::from(DB::raw("prosesgajisupirdetail with (readuncommitted)"))->where('prosesgajisupir_id', $id)->get();
        $total = 0;
        $tes = '';
        foreach ($gajidetail as $key => $value) {
            $potongan = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))
                ->where('gajisupir_nobukti', $value->gajisupir_nobukti)
                ->get();

            $nominal = $potongan->sum('nominal');
            if ($nominal != 0) {
                $total = $total + $nominal;
            }

            $fetchDeposito = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))->where('gajisupir_nobukti', $value->gajisupir_nobukti)->first();
            if (isset($fetchDeposito)) {
                $tes = $fetchDeposito->penerimaantrucking_nobukti;
            }
        }


        if ($tes != '') {

            $penerimaan = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                ->select('penerimaantruckingheader.bank_id', 'bank.namabank as bank', 'penerimaantruckingheader.penerimaan_nobukti')
                ->join(DB::raw("bank with (readuncommitted)"), 'penerimaantruckingheader.bank_id', 'bank.id')->where('nobukti', $tes)->first();
            $data = [
                'bank_id' => $penerimaan->bank_id,
                'bankDeposito' => $penerimaan->bank,
                'nobuktiDeposito' => $penerimaan->penerimaan_nobukti,
                'nomDeposito' => $total
            ];
            return $data;
        }
    }
    public function showBBM($id)
    {
        $gajidetail = ProsesGajiSupirDetail::from(DB::raw("prosesgajisupirdetail with (readuncommitted)"))->where('prosesgajisupir_id', $id)->get();
        $total = 0;
        $tes = '';
        foreach ($gajidetail as $key => $value) {
            $potongan = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))
                ->where('gajisupir_nobukti', $value->gajisupir_nobukti)
                ->get();

            $nominal = $potongan->sum('nominal');
            if ($nominal != 0) {
                $total = $total + $nominal;
            }

            $fetchBBM = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))->where('gajisupir_nobukti', $value->gajisupir_nobukti)->first();
            if (isset($fetchBBM)) {
                $tes = $fetchBBM->penerimaantrucking_nobukti;
            }
        }

        // dd($tes)
        if ($tes != '') {

            $penerimaan = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                ->select('penerimaantruckingheader.bank_id', 'bank.namabank as bank', 'penerimaantruckingheader.penerimaan_nobukti')
                ->join(DB::raw("bank with (readuncommitted)"), 'penerimaantruckingheader.bank_id', 'bank.id')->where('nobukti', $tes)->first();
            $data = [
                'bank_id' => $penerimaan->bank_id,
                'bankBBM' => $penerimaan->bank,
                'nobuktiBBM' => $penerimaan->penerimaan_nobukti,
                'nomBBM' => $total
            ];
            return $data;
        }
    }
    public function showPinjaman($id)
    {
        $gajidetail = ProsesGajiSupirDetail::from(DB::raw("prosesgajisupirdetail with (readuncommitted)"))->where('prosesgajisupir_id', $id)->get();
        $total = 0;
        $tes = '';
        foreach ($gajidetail as $key => $value) {
            $potongan = GajiSupirPinjaman::from(DB::raw("gajisupirpinjaman with (readuncommitted)"))
                ->where('gajisupir_nobukti', $value->gajisupir_nobukti)
                ->get();

            $nominal = $potongan->sum('nominal');
            if ($nominal != 0) {
                $total = $total + $nominal;
            }

            $fetchBBM = GajiSupirPinjaman::from(DB::raw("gajisupirpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $value->gajisupir_nobukti)->first();
            if (isset($fetchBBM)) {
                $tes = $fetchBBM->pengeluarantrucking_nobukti;
            }
        }


        if ($tes != '') {

            $pengeluaran = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
                ->select('pengeluarantruckingheader.bank_id', 'bank.namabank as bank', 'pengeluarantruckingheader.pengeluaran_nobukti')
                ->join(DB::raw("bank with (readuncommitted)"), 'pengeluarantruckingheader.bank_id', 'bank.id')->where('nobukti', $tes)->first();
            $data = [
                'bank_idPinjaman' => $pengeluaran->bank_id,
                'bankPinjaman' => $pengeluaran->bank,
                'nobuktiPinjaman' => $pengeluaran->pengeluaran_nobukti,
                'nomPinjaman' => $total
            ];
            return $data;
        }
    }

    public function showUangjalan($id)
    {
        $gajidetail = ProsesGajiSupirDetail::from(DB::raw("prosesgajisupirdetail with (readuncommitted)"))->where('prosesgajisupir_id', $id)->get();
        $total = 0;
        $tes = '';
        $allSP = "";
        foreach ($gajidetail as $key => $value) {
            if ($key == 0) {
                $allSP = $allSP . "'$value->gajisupir_nobukti'";
            } else {
                $allSP = $allSP . ',' . "'$value->gajisupir_nobukti'";
            }
        }
        $getUangjalan = GajisUpirUangJalan::from(DB::raw("gajisupiruangjalan with (readuncommitted)"))
            ->select('absensisupirheader.kasgantung_nobukti')
            ->join(DB::raw("absensisupirheader with (readuncommitted)"), 'gajisupiruangjalan.absensisupir_nobukti', 'absensisupirheader.nobukti')
            ->join(DB::raw("pengembaliankasgantungdetail a with (readuncommitted)"), 'a.kasgantung_nobukti', 'absensisupirheader.kasgantung_nobukti')
            ->whereRaw("gajisupir_nobukti in ($allSP)")->get();


        $allSP = "";
        foreach ($getUangjalan as $key => $value) {
            if ($key == 0) {
                $allSP = $allSP . "'$value->kasgantung_nobukti'";
            } else {
                $allSP = $allSP . ',' . "'$value->kasgantung_nobukti'";
            }
        }
        if ($allSP != '') {
            $getKasgantung = PengembalianKasGantungHeader::from(DB::raw("pengembaliankasgantungheader with (readuncommitted)"))
                ->select("pengembaliankasgantungheader.penerimaan_nobukti", 'pengembaliankasgantungheader.bank_id', 'bank.namabank')
                ->join(DB::raw("pengembaliankasgantungdetail with (readuncommitted)"), 'pengembaliankasgantungheader.nobukti', 'pengembaliankasgantungdetail.nobukti')
                ->join(DB::raw("bank with (readuncommitted)"), 'pengembaliankasgantungheader.bank_id', 'bank.id')
                ->whereRaw("pengembaliankasgantungdetail.kasgantung_nobukti in ($allSP)")
                ->first();

            $data = [
                'bank_id' => $getKasgantung->bank_id,
                'bankUangjalan' => $getKasgantung->namabank,
                'nobuktiUangjalan' => $getKasgantung->penerimaan_nobukti
            ];
            return $data;
        }
    }

    public function getDataJurnal($nobukti)
    {

        $tempGaji = '##Tempgaji' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempGaji, function ($table) {
            $table->string('nobukti');
        });
        foreach ($nobukti as $value) {

            $fetchGajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))
                ->select('nobukti')
                ->where('nobukti', $value);

            DB::table($tempGaji)->insertUsing(['nobukti'], $fetchGajiSupir);
        }
        $tempsuratpengantartambahan = '##tempsuratpengantartambahan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsuratpengantartambahan, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $fetchsuratpengantartambahan = DB::table($tempGaji)->from(DB::raw("$tempGaji as A with (readuncommitted)"))
            ->select(
                DB::raw("C.nobukti, sum(isnull(d.nominal,0)) as nominal")
            )
            ->join(DB::raw("gajisupirdetail as B with (readuncommitted)"), 'A.nobukti', 'B.nobukti')
            ->join(DB::raw("suratpengantar as C with (readuncommitted)"), 'B.suratpengantar_nobukti', 'C.nobukti')
            ->leftjoin(DB::raw("suratpengantarbiayatambahan as D with (readuncommitted)"), 'c.id', 'd.suratpengantar_id')
            ->groupBy('C.nobukti');

        DB::table($tempsuratpengantartambahan)->insertUsing(['nobukti', 'nominal'], $fetchsuratpengantartambahan);



        $tempRincian = '##Temprincian' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetchTempRincian = DB::table($tempGaji)->from(DB::raw("$tempGaji as A with (readuncommitted)"))
            ->select(
                DB::raw("C.tglbukti, sum(isnull(B.gajisupir,0)+isnull(B.gajiritasi,0)+isnull(d.nominal,0)) as gajisupir")
            )
            ->join(DB::raw("gajisupirdetail as B with (readuncommitted)"), 'A.nobukti', 'B.nobukti')
            ->join(DB::raw("suratpengantar as C with (readuncommitted)"), 'B.suratpengantar_nobukti', 'C.nobukti')
            ->leftjoin(DB::raw($tempsuratpengantartambahan . " as d "), 'c.nobukti', 'd.nobukti')
            ->groupBy('C.tglbukti');

        Schema::create($tempRincian, function ($table) {

            $table->date('tglbukti');
            $table->bigInteger('gajisupir')->nullable();
        });

        DB::table($tempRincian)->insertUsing(['tglbukti', 'gajisupir'], $fetchTempRincian);

        $fetchTempRincian2 = DB::table($tempGaji)->from(DB::raw("$tempGaji as A with (readuncommitted)"))
            ->select(
                DB::raw("C.tglbukti, isnull(sum(isnull(B.gajisupir,0)+isnull(B.gajiritasi,0)),0) as gajisupir")
            )
            ->join(DB::raw("gajisupirdetail as B with (readuncommitted)"), 'A.nobukti', 'B.nobukti')
            ->join(DB::raw("ritasi as C with (readuncommitted)"), 'B.ritasi_nobukti', 'C.nobukti')
            ->whereRaw("isnull(B.suratpengantar_nobukti,'-')='-'")
            ->groupBy('C.tglbukti');


        DB::table($tempRincian)->insertUsing(['tglbukti', 'gajisupir'], $fetchTempRincian2);

        $tempRincianJurnal = '##Temprincianjurnal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetchTempRincianJurnal = DB::table($tempRincian)->from(DB::raw("$tempRincian with (readuncommitted)"))
            ->select(DB::raw("tglbukti, isnull(sum(gajisupir),0) as nominal, 'Borongan Supir' as keterangan"))
            ->groupBy('tglbukti');
        Schema::create($tempRincianJurnal, function ($table) {

            $table->date('tglbukti');
            $table->bigInteger('nominal')->nullable();
            $table->string('keterangan')->nullable();
        });

        DB::table($tempRincianJurnal)->insertUsing(['tglbukti', 'nominal', 'keterangan'], $fetchTempRincianJurnal);

        $fetchTempRincianJurnal2 = DB::table($tempGaji)->from(DB::raw("$tempGaji as A with (readuncommitted)"))
            ->select(
                DB::raw("C.tglbukti, isnull(sum(B.komisisupir),0) as nominal, 'Komisi Supir' as keterangan")
            )
            ->join(DB::raw("gajisupirdetail as B with (readuncommitted)"), 'A.nobukti', 'B.nobukti')
            ->join(DB::raw("suratpengantar as C with (readuncommitted)"), 'B.suratpengantar_nobukti', 'C.nobukti')
            ->whereRaw("isnull(B.komisisupir ,0)<>0")
            ->groupBy('C.tglbukti');

        DB::table($tempRincianJurnal)->insertUsing(['tglbukti', 'nominal', 'keterangan'], $fetchTempRincianJurnal2);

        // gaji kenek

        $queryebskenek = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->where('a.grp', 'JURNAL EBS GAJI KENEK')
            ->where('a.subgrp', 'JURNAL EBS GAJI KENEK')
            ->where('a.text', 'YA')
            ->first();
        if (isset($queryebskenek)) {
            $fetchTempRincianJurnal2 = DB::table($tempGaji)->from(DB::raw("$tempGaji as A with (readuncommitted)"))
                ->select(
                    DB::raw("C.tglbukti, isnull(sum(B.gajikenek),0) as nominal, 'Gaji Kenek' as keterangan")
                )
                ->join(DB::raw("gajisupirdetail as B with (readuncommitted)"), 'A.nobukti', 'B.nobukti')
                ->join(DB::raw("suratpengantar as C with (readuncommitted)"), 'B.suratpengantar_nobukti', 'C.nobukti')
                ->whereRaw("isnull(B.gajikenek ,0)<>0")
                ->groupBy('C.tglbukti');

            DB::table($tempRincianJurnal)->insertUsing(['tglbukti', 'nominal', 'keterangan'], $fetchTempRincianJurnal2);
        }


        // 

        $tgl = DB::table($tempRincianJurnal)->select(DB::raw("min(tglbukti) as tglbukti"))->first();

        $fetchTempRincianJurnal3 = DB::table($tempGaji)->from(DB::raw("$tempGaji as A with (readuncommitted)"))
            ->select(
                DB::raw("'$tgl->tglbukti', isnull(sum(B.uangmakanharian),0) as nominal, 'Uang Makan' as keterangan")
            )
            ->join(DB::raw("gajisupirheader as B with (readuncommitted)"), 'A.nobukti', 'B.nobukti')
            ->whereRaw("isnull(B.uangmakanharian ,0)<>0");
        DB::table($tempRincianJurnal)->insertUsing(['tglbukti', 'nominal', 'keterangan'], $fetchTempRincianJurnal3);

        $fetchTempRincianJurnal4 = DB::table($tempGaji)->from(DB::raw("$tempGaji as A with (readuncommitted)"))
            ->select(
                DB::raw("'$tgl->tglbukti', isnull(sum(B.uangmakanberjenjang),0) as nominal, 'Uang Makan Berjenjang' as keterangan")
            )
            ->join(DB::raw("gajisupirheader as B with (readuncommitted)"), 'A.nobukti', 'B.nobukti')
            ->whereRaw("isnull(B.uangmakanberjenjang ,0)<>0");
        $cekUangMakanBerjenjang = $fetchTempRincianJurnal4->first();
        if ($cekUangMakanBerjenjang->nominal > 0) {

            DB::table($tempRincianJurnal)->insertUsing(['tglbukti', 'nominal', 'keterangan'], $fetchTempRincianJurnal4);
        }
        $data = DB::table($tempRincianJurnal)
            ->whereRaw("nominal<>0")
            ->orderBy('tglbukti')->orderBy('keterangan')->get();
        return $data;
    }

    public function sort($query)
    {
        return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function sortposition($query)
    {
        if ($this->params['sortIndex'] == 'total' || $this->params['sortIndex'] == 'uangjalan' || $this->params['sortIndex'] == 'bbm' || $this->params['sortIndex'] == 'potonganpinjaman' || $this->params['sortIndex'] == 'potonganpinjamansemua' || $this->params['sortIndex'] == 'uangmakanharian' || $this->params['sortIndex'] == 'deposito') {

            return $query->orderBy($this->tableTotal . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
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
                        if ($filters['field'] == 'statuscetak') {
                            $query = $query->where('a.statuscetaktext', '=', "$filters[data]");
                        } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgldari' || $filters['field'] == 'tglsampai' || $filters['field'] == 'tglbukacetak') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'total' || $filters['field'] == 'totalposting' || $filters['field'] == 'uangjalan' || $filters['field'] == 'bbm' || $filters['field'] == 'uangmakanharian' || $filters['field'] == 'uangmakanberjenjang' || $filters['field'] == 'potonganpinjaman' || $filters['field'] == 'potonganpinjamansemua' || $filters['field'] == 'deposito' || $filters['field'] == 'komisisupir' || $filters['field'] == 'gajisupir' || $filters['field'] == 'gajikenek' || $filters['field'] == 'biayaextra') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'check') {
                            $query = $query->whereRaw('1 = 1');
                        } else {
                            $query = $query->where('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statuscetak') {
                                $query = $query->orWhere('a.statuscetaktext', '=', "$filters[data]");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgldari' || $filters['field'] == 'tglsampai' || $filters['field'] == 'tglbukacetak') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'total' || $filters['field'] == 'totalposting' || $filters['field'] == 'uangjalan' || $filters['field'] == 'bbm' || $filters['field'] == 'uangmakanharian' || $filters['field'] == 'uangmakanberjenjang' || $filters['field'] == 'potonganpinjaman' || $filters['field'] == 'potonganpinjamansemua' || $filters['field'] == 'deposito' || $filters['field'] == 'komisisupir' || $filters['field'] == 'gajisupir' || $filters['field'] == 'gajikenek' || $filters['field'] == 'biayaextra') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'check') {
                                $query = $query->whereRaw('1 = 1');
                            } else {
                                $query = $query->orWhere('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
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

    public function filterposition($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('statusapproval.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statuscetak') {
                            $query = $query->where('statuscetak.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'total') {
                            $query = $query->whereRaw("format($this->tableTotal.total, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'totalposting') {
                            $query = $query->whereRaw("format($this->tableTotal.totalposting, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'uangjalan') {
                            $query = $query->whereRaw("format($this->tableTotal.uangjalan, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'bbm') {
                            $query = $query->whereRaw("format($this->tableTotal.bbm, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'potonganpinjaman') {
                            $query = $query->whereRaw("format($this->tableTotal.potonganpinjaman, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'potonganpinjamansemua') {
                            $query = $query->whereRaw("format($this->tableTotal.potonganpinjamansemua, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'uangmakanharian') {
                            $query = $query->whereRaw("format($this->tableTotal.uangmakanharian, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'uangmakanberjenjang') {
                            $query = $query->whereRaw("format($this->tableTotal.uangmakanberjenjang, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'deposito') {
                            $query = $query->whereRaw("format($this->tableTotal.deposito, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgldari' || $filters['field'] == 'tglsampai' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusapproval') {
                                $query = $query->orWhere('statusapproval.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statuscetak') {
                                $query = $query->orWhere('statuscetak.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'total') {
                                $query = $query->orWhereRaw("format($this->tableTotal.total, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'totalposting') {
                                $query = $query->orWhereRaw("format($this->tableTotal.totalposting, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'uangjalan') {
                                $query = $query->orWhereRaw("format($this->tableTotal.uangjalan, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'bbm') {
                                $query = $query->orWhereRaw("format($this->tableTotal.bbm, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'potonganpinjaman') {
                                $query = $query->orWhereRaw("format($this->tableTotal.potonganpinjaman, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'potonganpinjamansemua') {
                                $query = $query->orWhereRaw("format($this->tableTotal.potonganpinjamansemua, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'uangmakanharian') {
                                $query = $query->orWhereRaw("format($this->tableTotal.uangmakanharian, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'uangmakanberjenjang') {
                                $query = $query->orWhereRaw("format($this->tableTotal.uangmakanberjenjang, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'deposito') {
                                $query = $query->orWhereRaw("format($this->tableTotal.deposito, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgldari' || $filters['field'] == 'tglsampai' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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

    public function getSumBoronganForValidation($nobukti)
    {
        $bukti = "";
        foreach ($nobukti as $key => $value) {
            if ($key == 0) {
                $bukti = $bukti . "'$value'";
            } else {
                $bukti = $bukti . ',' . "'$value'";
            }
        }
        $fetch = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))
            ->select(DB::raw("SUM(total) as borongan, SUM(potonganpinjaman) as pinjamanpribadi, SUM(potonganpinjamansemua) as pinjamansemua, SUM(deposito) as deposito, SUM(bbm) as bbm, SUM(uangjalan) as uangjalan"))
            ->whereRaw("gajisupirheader.nobukti in($bukti)");
        return $fetch->first();
    }

    public function getExport($id)
    {
        $this->setRequestParameters();
        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $this->tableTotal = $this->createTempTotal();
        $query = DB::table($this->table)->from(DB::raw("prosesgajisupirheader with (readuncommitted)"))
            ->select(
                'prosesgajisupirheader.id',
                'prosesgajisupirheader.nobukti',
                'prosesgajisupirheader.tglbukti',
                'prosesgajisupirheader.keterangan',
                'prosesgajisupirheader.jumlahcetak',
                DB::raw("'KAS BON' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("format(getdate(),'dd-MM-yyyy')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'prosesgajisupirheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'prosesgajisupirheader.statusapproval', 'statusapproval.id')
            ->leftJoin($this->tableTotal, $this->tableTotal . '.nobukti', 'prosesgajisupirheader.nobukti');

        if (request()->tgldari && request()->tglsampai) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(prosesgajisupirheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(prosesgajisupirheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("prosesgajisupirheader.statuscetak", $statusCetak);
        }

        $data = $query->first();

        return $data;
    }

    public function processStore(array $data): ProsesGajiSupirHeader
    {
        $group = 'PROSES GAJI SUPIR BUKTI';
        $subGroup = 'PROSES GAJI SUPIR BUKTI';
        $format = DB::table('parameter')->where('grp', $group)->where('subgrp', $subGroup)->first();

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $prosesGajiSupirHeader = new ProsesGajiSupirHeader();

        $prosesGajiSupirHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $prosesGajiSupirHeader->tgldari = date('Y-m-d', strtotime($data['tgldari']));
        $prosesGajiSupirHeader->tglsampai = date('Y-m-d', strtotime($data['tglsampai']));
        $prosesGajiSupirHeader->statusapproval = $statusApproval->id;
        $prosesGajiSupirHeader->userapproval = '';
        $prosesGajiSupirHeader->tglapproval = '';
        $prosesGajiSupirHeader->keterangan = 'PROSES GAJI SUPIR ' . $data['tgldari'] . ' s/d ' . $data['tglsampai'];
        $prosesGajiSupirHeader->bank_id = $data['bank_id'];
        $prosesGajiSupirHeader->statusformat = $format->id;
        $prosesGajiSupirHeader->statuscetak = $statusCetak->id;
        $prosesGajiSupirHeader->modifiedby = auth('api')->user()->name;
        $prosesGajiSupirHeader->info = html_entity_decode(request()->info);
        $prosesGajiSupirHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $prosesGajiSupirHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$prosesGajiSupirHeader->save()) {
            throw new \Exception("Error storing proses gaji supir header.");
        }

        $prosesGajiSupirDetails = [];

        $coaDebet = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PROSES GAJI SUPIR')->where('kelompok', 'PENGELUARAN')
            ->first();
        $pisahGajiKenek = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PROSES GAJI SUPIR')->where('subgrp', 'PISAH GAJI KENEK')
            ->first();
        $isPisahGajiKenek = $pisahGajiKenek->text;

        $memoDebet = json_decode($coaDebet->memo, true);
        $noWarkat = [];
        $tglJatuhTempo = [];
        $nominalDetailPengeluaran = [];
        $coaDebetPengeluaran = [];
        $keteranganDetailPengeluaran = [];
        $totalKBT = 0;
        for ($i = 0; $i < count($data['rincianId']); $i++) {
            $sp = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
                ->where('supir_id', $data['supir_id'][$i])->first();

            $prosesGajiSupirDetail = (new ProsesGajiSupirDetail())->processStore($prosesGajiSupirHeader, [
                'gajisupir_nobukti' => $data['nobuktiRIC'][$i],
                'supir_id' => $data['supir_id'][$i],
                'trado_id' => $sp->trado_id,
                'nominal' => $data['totalborongan'][$i],
                'keterangan' => '',
            ]);
            $prosesGajiSupirDetails[] = $prosesGajiSupirDetail->toArray();

            if ($isPisahGajiKenek == 'YA') {
                $totalKBT = $totalKBT + ($data['totalborongan'][$i] - $data['gajikenek'][$i]);
            } else {
                $totalKBT = $totalKBT + $data['totalborongan'][$i];
            }
        }
        $noWarkat[] = '';
        $tglJatuhTempo[] = $data['tglbukti'];
        $nominalDetailPengeluaran[] = $totalKBT;
        $coaDebetPengeluaran[] = $memoDebet['JURNAL'];
        $keteranganDetailPengeluaran[] = "Rincian Borongan Supir " . date('d-m-Y', strtotime($data['tgldari'])) . " s/d " . date('d-m-Y', strtotime($data['tglsampai']));
        // POSTING KE PENGELUARAN
        $pengeluaranHeaderRequest = [
            'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
            'pelanggan_id' => 0,
            'postingdari' => 'ENTRY PROSES GAJI SUPIR',
            'dibayarke' => 'PROSES GAJI SUPIR',
            'bank_id' => $data['bank_id'],
            'nowarkat' => $noWarkat,
            'tgljatuhtempo' => $tglJatuhTempo,
            'nominal_detail' => $nominalDetailPengeluaran,
            'coadebet' => $coaDebetPengeluaran,
            'keterangan_detail' => $keteranganDetailPengeluaran,
        ];

        $pengeluaranHeader = (new PengeluaranHeader())->processStore($pengeluaranHeaderRequest);
        $prosesGajiSupirHeader->pengeluaran_nobukti = $pengeluaranHeader->nobukti;

        $prosesGajiSupirHeader->save();

        $prosesGajiSupirHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($prosesGajiSupirHeader->getTable()),
            'postingdari' => 'PROSES GAJI SUPIR HEADER',
            'idtrans' => $prosesGajiSupirHeader->id,
            'nobuktitrans' => $prosesGajiSupirHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $prosesGajiSupirHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($prosesGajiSupirDetail->getTable()),
            'postingdari' => 'ENTRY PROSES GAJI SUPIR DETAIL',
            'idtrans' => $prosesGajiSupirHeaderLogTrail->id,
            'nobuktitrans' => $prosesGajiSupirHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $prosesGajiSupirDetails,
            'modifiedby' => auth('api')->user()->user
        ]);

        /*STORE JURNAL*/
        $getData = $prosesGajiSupirHeader->getDataJurnal($data['nobuktiRIC']);
        $nominalJurnal = [];
        $coaDebetJurnal = [];
        $coaKreditJurnal = [];
        $keteranganJurnal = [];
        $coadebet = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->select('memo')->where('grp', 'JURNAL PROSES GAJI SUPIR')->where('kelompok', 'PROSES GAJI SUPIR')->where('subgrp', 'DEBET')->first();
        $coakredit = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->select('memo')->where('grp', 'JURNAL PROSES GAJI SUPIR')->where('kelompok', 'PROSES GAJI SUPIR')->where('subgrp', 'KREDIT')->first();

        $memodebet = json_decode($coadebet->memo, true);
        $memokredit = json_decode($coakredit->memo, true);

        for ($i = 0; $i < count($getData); $i++) {
            $tglbuktiJurnal[] = $getData[$i]->tglbukti;
            $nominalJurnal[] = $getData[$i]->nominal;
            $coaDebetJurnal[] = $memodebet['JURNAL'];
            $coaKreditJurnal[] = $memokredit['JURNAL'];
            $keteranganJurnal[] = $getData[$i]->keterangan;
        }
        $jurnalRequest = [
            'tanpaprosesnobukti' => 1,
            'nobukti' => $prosesGajiSupirHeader->nobukti,
            'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
            'postingdari' => "ENTRY PROSES GAJI SUPIR",
            'statusapproval' => $statusApproval->id,
            'userapproval' => "",
            'tglapproval' => "",
            'modifiedby' => auth('api')->user()->name,
            'statusformat' => "0",
            'tglbukti_detail' => $tglbuktiJurnal,
            'coakredit_detail' => $coaKreditJurnal,
            'coadebet_detail' => $coaDebetJurnal,
            'nominal_detail' => $nominalJurnal,
            'keterangan_detail' => $keteranganJurnal
        ];
        (new JurnalUmumHeader())->processStore($jurnalRequest);

        // POSTING POT. SEMUA

        if ($data['nomPS'] != 0) {
            // INSERT KE PENERIMAAN
            $gajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->where('tglbukti', '>=', $prosesGajiSupirHeader['tgldari'])
                ->where('tglbukti', '<=', $prosesGajiSupirHeader['tglsampai'])
                ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupir_id=" . $prosesGajiSupirHeader['id'] . ")")
                ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from gajisupirpelunasanpinjaman where supir_id=0)")
                ->get();
            $totalPS = 0;
            $firstBuktiPostingPS = '';
            $firstBuktiNonPostingPS = '';
            $nominalPostingNonPS = ['nonposting' => 0, 'posting' => 0];
            $coakreditPostingNonPS = ['nonposting' => '', 'posting' => ''];
            $keteranganPostingNonPS = ['nonposting' => '', 'posting' => ''];
            $nobuktiPostingPS = '';
            $nobuktiNonPostingPS = '';
            $fetchFormatPJP =  DB::table('penerimaantrucking')->where('id', 2)->first();
            $uniqueValuesNonPosting = [];
            $uniqueValuesPosting = [];
            foreach ($gajiSupir as $key => $value) {
                $fetchPS = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $value->nobukti)->where('supir_id', '0')->get();

                $penerimaanPS = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $fetchPS[0]['penerimaantrucking_nobukti'])->first();

                $getNominal = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
                    ->where('nobukti', $fetchPS[0]['penerimaantrucking_nobukti'])->get();
                $totalPS = $totalPS + $getNominal->sum('nominal');


                foreach ($fetchPS as $dataPS) {
                    $queryposting = db::table('pengeluarantruckingheader')->from(db::raw("pengeluarantruckingheader a with (readuncommitted)"))
                        ->select('statusposting', 'coa')->where('nobukti', $dataPS->pengeluarantrucking_nobukti)->first();
                    if (isset($queryposting)) {
                        $bukanposting = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                            ->select('id')
                            ->where('grp', 'STATUS POSTING')
                            ->where('subgrp', 'STATUS POSTING')
                            ->where('id', '84')
                            ->first()->id ?? 0;
                        if ($bukanposting == $queryposting->statusposting) {
                            if ($firstBuktiNonPostingPS == '') {
                                $nobuktiNonPostingPS = $nobuktiNonPostingPS .  $dataPS->pengeluarantrucking_nobukti;
                                $uniqueValuesNonPosting[] = $dataPS->pengeluarantrucking_nobukti;
                                $coakreditPostingNonPS['nonposting'] =  $queryposting->coa ?? $fetchFormatPJP->coapostingkredit;
                                $tglJatuhTempoPS[] = date('Y-m-d', strtotime($data['tglbukti']));
                                $firstBuktiNonPostingPS =  $dataPS->pengeluarantrucking_nobukti;
                            } else {
                                if (!in_array($dataPS->pengeluarantrucking_nobukti, $uniqueValuesNonPosting)) {
                                    $uniqueValuesNonPosting[] = $dataPS->pengeluarantrucking_nobukti;

                                    $nobuktiNonPostingPS = $nobuktiNonPostingPS . ', ' .  $dataPS->pengeluarantrucking_nobukti;
                                }
                            }
                            $keteranganPostingNonPS['nonposting'] =  '(NON POSTING) PENGEMBALIAN PINJAMAN ' . $nobuktiNonPostingPS;

                            $nominalPostingNonPS['nonposting'] += $dataPS->nominal;
                        } else {
                            if ($firstBuktiPostingPS == '') {
                                $nobuktiPostingPS = $nobuktiPostingPS . $dataPS->pengeluarantrucking_nobukti;
                                $uniqueValuesPosting[] = $dataPS->pengeluarantrucking_nobukti;
                                $coakreditPostingNonPS['posting'] = $fetchFormatPJP->coapostingkredit;
                                $tglJatuhTempoPS[] = date('Y-m-d', strtotime($data['tglbukti']));
                                $firstBuktiPostingPS = $dataPS->pengeluarantrucking_nobukti;
                            } else {
                                if (!in_array($dataPS->pengeluarantrucking_nobukti, $uniqueValuesPosting)) {
                                    $uniqueValuesPosting[] = $dataPS->pengeluarantrucking_nobukti;

                                    $nobuktiPostingPS = $nobuktiPostingPS . ', ' . $dataPS->pengeluarantrucking_nobukti;
                                }
                            }
                            $keteranganPostingNonPS['posting'] =  '(POSTING) PENGEMBALIAN PINJAMAN ' . $nobuktiPostingPS;

                            $nominalPostingNonPS['posting'] += $dataPS->nominal;
                        }
                    }
                }
            }

            $nominalPostingNonPS = array_filter($nominalPostingNonPS, function ($value) {
                return $value !== 0;
            });
            $coakreditPostingNonPS = array_filter($coakreditPostingNonPS, function ($value) {
                return $value !== '';
            });
            $keteranganPostingNonPS = array_filter($keteranganPostingNonPS, function ($value) {
                return $value !== '';
            });
            $nominalPostingNonPS = array_values($nominalPostingNonPS);
            $coakreditPostingNonPS = array_values($coakreditPostingNonPS);
            $keteranganPostingNonPS = array_values($keteranganPostingNonPS);
            $nominalPS = $nominalPostingNonPS;
            $coaKreditPS = $coakreditPostingNonPS;
            $keteranganPS = $keteranganPostingNonPS;


            $penerimaanHeadeRequest = [
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'pelanggan_id' => '',
                'bank_id' => $data['bank_id'],
                'postingdari' => 'ENTRY PROSES GAJI SUPIR',
                'diterimadari' => "PROSES GAJI SUPIR PERIODE " . date('d-m-Y', strtotime($data['tgldari'])) . " S/D " . date('d-m-Y', strtotime($data['tglsampai'])),
                'tgllunas' => date('Y-m-d', strtotime($data['tglbukti'])),
                'coakredit' => $coaKreditPS,
                'nominal_detail' => $nominalPS,
                'keterangan_detail' => $keteranganPS,
                'tgljatuhtempo' => $tglJatuhTempoPS
            ];

            $penerimaanHeaderPS = (new PenerimaanHeader())->processStore($penerimaanHeadeRequest);

            // UPDATE PENERIMAAN TRUCKING
            foreach ($gajiSupir as $key => $value) {
                $fetchPS = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $value->nobukti)->where('supir_id', '0')->first();

                $penerimaanPS = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $fetchPS->penerimaantrucking_nobukti)->first();

                $penerimaanTruckingHeaderPS = [
                    'ebs' => true,
                    'postingdari' => 'PROSES GAJI SUPIR',
                    'bank_id' => $data['bank_id'],
                    'penerimaan_nobukti' => $penerimaanHeaderPS->nobukti,
                ];
                $newPenerimaanTruckingPS = new PenerimaanTruckingHeader();
                $newPenerimaanTruckingPS = $newPenerimaanTruckingPS->findAll($penerimaanPS->id);
                (new PenerimaanTruckingHeader())->processUpdate($newPenerimaanTruckingPS, $penerimaanTruckingHeaderPS);
            }
        }

        // POSTING POT. PRIBADI

        if ($data['nomPP'] != 0) {
            // SAVE TO PENERIMAAN
            $gajiSupirPP = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->where('tglbukti', '>=', $prosesGajiSupirHeader['tgldari'])
                ->where('tglbukti', '<=', $prosesGajiSupirHeader['tglsampai'])
                ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupir_id=" . $prosesGajiSupirHeader['id'] . ")")
                ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from gajisupirpelunasanpinjaman where supir_id != 0)")
                ->get();
            $totalPP = 0;
            $firstBuktiPosting = '';
            $firstBuktiNonPosting = '';
            $nominalPostingNon = ['nonposting' => 0, 'posting' => 0];
            $coakreditPostingNon = ['nonposting' => '', 'posting' => ''];
            $keteranganPostingNon = ['nonposting' => '', 'posting' => ''];
            $nobuktiPosting = '';
            $nobuktiNonPosting = '';
            $fetchFormatPJP =  DB::table('penerimaantrucking')->where('id', 2)->first();

            foreach ($gajiSupirPP as $key => $value) {
                $fetchPP = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $value->nobukti)->where('supir_id', '!=', '0')->get();

                $penerimaanPP = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $fetchPP[0]['penerimaantrucking_nobukti'])->first();
                $getNominalPP = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
                    ->where('nobukti', $fetchPP[0]['penerimaantrucking_nobukti'])->get();
                $totalPP = $totalPP + $getNominalPP->sum('nominal');

                foreach ($fetchPP as $dataPP) {
                    $queryposting = db::table('pengeluarantruckingheader')->from(db::raw("pengeluarantruckingheader a with (readuncommitted)"))
                        ->select('statusposting', 'coa')->where('nobukti', $dataPP->pengeluarantrucking_nobukti)->first();
                    if (isset($queryposting)) {
                        $bukanposting = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                            ->select('id')
                            ->where('grp', 'STATUS POSTING')
                            ->where('subgrp', 'STATUS POSTING')
                            ->where('id', '84')
                            ->first()->id ?? 0;
                        if ($bukanposting == $queryposting->statusposting) {
                            if ($firstBuktiNonPosting == '') {
                                $nobuktiNonPosting = $nobuktiNonPosting .  $dataPP->pengeluarantrucking_nobukti;
                                $coakreditPostingNon['nonposting'] =  $queryposting->coa ?? $fetchFormatPJP->coapostingkredit;
                                $tglJatuhTempoPP[] = date('Y-m-d', strtotime($data['tglbukti']));
                                $firstBuktiNonPosting =  $dataPP->pengeluarantrucking_nobukti;
                            } else {
                                $nobuktiNonPosting = $nobuktiNonPosting . ', ' .  $dataPP->pengeluarantrucking_nobukti;
                            }
                            $keteranganPostingNon['nonposting'] =  '(NON POSTING) PENGEMBALIAN PINJAMAN ' . $nobuktiNonPosting;

                            $nominalPostingNon['nonposting'] += $dataPP->nominal;
                        } else {
                            if ($firstBuktiPosting == '') {
                                $nobuktiPosting = $nobuktiPosting . $dataPP->pengeluarantrucking_nobukti;
                                $coakreditPostingNon['posting'] = $fetchFormatPJP->coapostingkredit;
                                $tglJatuhTempoPP[] = date('Y-m-d', strtotime($data['tglbukti']));
                                $firstBuktiPosting = $dataPP->pengeluarantrucking_nobukti;
                            } else {
                                $nobuktiPosting = $nobuktiPosting . ', ' . $dataPP->pengeluarantrucking_nobukti;
                            }
                            $keteranganPostingNon['posting'] =  '(POSTING) PENGEMBALIAN PINJAMAN ' . $nobuktiPosting;

                            $nominalPostingNon['posting'] += $dataPP->nominal;
                        }
                    }
                }
            }

            $nominalPostingNon = array_filter($nominalPostingNon, function ($value) {
                return $value !== 0;
            });
            $coakreditPostingNon = array_filter($coakreditPostingNon, function ($value) {
                return $value !== '';
            });
            $keteranganPostingNon = array_filter($keteranganPostingNon, function ($value) {
                return $value !== '';
            });
            $nominalPostingNon = array_values($nominalPostingNon);
            $coakreditPostingNon = array_values($coakreditPostingNon);
            $keteranganPostingNon = array_values($keteranganPostingNon);
            $nominalPP = $nominalPostingNon;
            $coaKreditPP = $coakreditPostingNon;
            $keteranganPP = $keteranganPostingNon;

            // $coaKreditPP = $penerimaanPP->coa;
            // $nominalPP = $totalPP;
            // $keteranganPP = 'POTONGAN PINJAMAN SUPIR (PRIBADI) ' . $prosesGajiSupirHeader->nobukti;

            $penerimaanHeaderPPRequest = [
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'pelanggan_id' => '',
                'bank_id' => $data['bank_id'],
                'postingdari' => 'ENTRY PROSES GAJI SUPIR',
                'diterimadari' => "PROSES GAJI SUPIR PERIODE " . date('d-m-Y', strtotime($data['tgldari'])) . " S/D " . date('d-m-Y', strtotime($data['tglsampai'])),
                'tgllunas' => date('Y-m-d', strtotime($data['tglbukti'])),
                'coakredit' => $coaKreditPP,
                'nominal_detail' => $nominalPP,
                'keterangan_detail' => $keteranganPP,
                'tgljatuhtempo' => $tglJatuhTempoPP
            ];
            $penerimaanHeaderPP = (new PenerimaanHeader())->processStore($penerimaanHeaderPPRequest);

            // UPDATE PENERIMAAN TRUCKING
            foreach ($gajiSupirPP as $key => $value) {
                $fetchPP = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $value->nobukti)->where('supir_id', '!=', '0')->first();

                $penerimaanPP = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $fetchPP->penerimaantrucking_nobukti)->first();

                $penerimaanTruckingHeaderPP = [
                    'ebs' => true,
                    'postingdari' => 'PROSES GAJI SUPIR',
                    'bank_id' => $data['bank_id'],
                    'penerimaan_nobukti' => $penerimaanHeaderPP->nobukti,
                ];
                $newPenerimaanTruckingPP = new PenerimaanTruckingHeader();
                $newPenerimaanTruckingPP = $newPenerimaanTruckingPP->findAll($penerimaanPP->id);
                (new PenerimaanTruckingHeader())->processUpdate($newPenerimaanTruckingPP, $penerimaanTruckingHeaderPP);
            }
        }

        // POSTING DEPOSITO
        if ($data['nomDeposito'] != 0) {
            // SAVE TO PENERIMAAN

            $gajiSupirDeposito = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->where('tglbukti', '>=', $prosesGajiSupirHeader['tgldari'])
                ->where('tglbukti', '<=', $prosesGajiSupirHeader['tglsampai'])
                ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupir_id=" . $prosesGajiSupirHeader['id'] . ")")
                ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from gajisupirdeposito)")
                ->get();
            $totalDepo = 0;
            foreach ($gajiSupirDeposito as $key => $value) {
                $fetchDeposito = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))->where('gajisupir_nobukti', $value->nobukti)->first();

                $penerimaanDeposito = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $fetchDeposito->penerimaantrucking_nobukti)->first();
                $getNominalDeposito = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
                    ->where('nobukti', $fetchDeposito->penerimaantrucking_nobukti)->get();

                $totalDepo = $totalDepo + $getNominalDeposito->sum('nominal');
            }

            $coaKreditDeposito[] = $penerimaanDeposito->coa;
            $nominalDeposito[] = $totalDepo;
            $keteranganDeposito[] = 'DEPOSITO SUPIR ' . $prosesGajiSupirHeader->nobukti;
            $tglJatuhTempoDeposito[] = $data['tglbukti'];

            $penerimaanHeaderDepositoRequest = [
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'pelanggan_id' => '',
                'bank_id' => $data['bank_id'],
                'postingdari' => 'ENTRY PROSES GAJI SUPIR',
                'diterimadari' => "PROSES GAJI SUPIR PERIODE " . date('d-m-Y', strtotime($data['tgldari'])) . " S/D " . date('d-m-Y', strtotime($data['tglsampai'])),
                'tgllunas' => date('Y-m-d', strtotime($data['tglbukti'])),
                'coakredit' => $coaKreditDeposito,
                'nominal_detail' => $nominalDeposito,
                'keterangan_detail' => $keteranganDeposito,
                'tgljatuhtempo' => $tglJatuhTempoDeposito
            ];

            $penerimaanHeaderDeposito = (new PenerimaanHeader())->processStore($penerimaanHeaderDepositoRequest);

            foreach ($gajiSupirDeposito as $key => $value) {
                $fetchDeposito = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))->where('gajisupir_nobukti', $value->nobukti)->first();

                $penerimaanDeposito = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $fetchDeposito->penerimaantrucking_nobukti)->first();

                $penerimaanTruckingHeaderDeposito = [
                    'ebs' => true,
                    'postingdari' => 'PROSES GAJI SUPIR',
                    'bank_id' => $data['bank_id'],
                    'penerimaan_nobukti' => $penerimaanHeaderDeposito->nobukti,
                ];
                $newPenerimaanTruckingDeposito = new PenerimaanTruckingHeader();
                $newPenerimaanTruckingDeposito = $newPenerimaanTruckingDeposito->findAll($penerimaanDeposito->id);
                (new PenerimaanTruckingHeader())->processUpdate($newPenerimaanTruckingDeposito, $penerimaanTruckingHeaderDeposito);
            }
        }
        // POSTING BBM

        if ($data['nomBBM'] != 0) {
            // SAVE TO PENERIMAAN

            $gajiSupirBBM = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->where('tglbukti', '>=', $prosesGajiSupirHeader['tgldari'])
                ->where('tglbukti', '<=', $prosesGajiSupirHeader['tglsampai'])
                ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupir_id=" . $prosesGajiSupirHeader['id'] . ")")
                ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from gajisupirbbm)")
                ->get();

            $totalBBM = 0;
            foreach ($gajiSupirBBM as $key => $value) {
                $fetchBBM = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))->where('gajisupir_nobukti', $value->nobukti)->first();

                $penerimaanBBM = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->first();
                $coaBBM = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))->where('kodepenerimaan', 'BBM')->first();
                $totalBBM = $totalBBM + $fetchBBM->nominal;
            }

            $coaKreditBBM[] = $coaBBM->coapostingkredit;
            $nominalBBM[] = $totalBBM;
            $tglJatuhTempoBBM[] = $data['tglbukti'];
            $keteranganBBM[] = 'BBM SUPIR ' . $prosesGajiSupirHeader->nobukti;

            $penerimaanHeadeRequest = [
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'pelanggan_id' => '',
                'bank_id' => $data['bank_id'],
                'postingdari' => 'ENTRY PROSES GAJI SUPIR',
                'diterimadari' => "PROSES GAJI SUPIR PERIODE " . date('d-m-Y', strtotime($data['tgldari'])) . " S/D " . date('d-m-Y', strtotime($data['tglsampai'])),
                'tgllunas' => date('Y-m-d', strtotime($data['tglbukti'])),
                'coakredit' => $coaKreditBBM,
                'nominal_detail' => $nominalBBM,
                'keterangan_detail' => $keteranganBBM,
                'tgljatuhtempo' => $tglJatuhTempoBBM
            ];

            $penerimaanHeaderBBM = (new PenerimaanHeader())->processStore($penerimaanHeadeRequest);

            foreach ($gajiSupirBBM as $key => $value) {
                $fetchBBM = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))->where('gajisupir_nobukti', $value->nobukti)->first();

                $penerimaanBBM = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->select(DB::raw("penerimaantruckingheader.id, penerimaantruckingheader.nobukti, penerimaantruckingdetail.nominal, penerimaantruckingdetail.keterangan"))
                    ->leftJoin(DB::raw("penerimaantruckingdetail with (readuncommitted)"), 'penerimaantruckingheader.id', 'penerimaantruckingdetail.penerimaantruckingheader_id')
                    ->where('penerimaantruckingheader.nobukti', $fetchBBM->penerimaantrucking_nobukti)
                    ->first();

                $penerimaanTruckingHeaderBBM = [
                    'ebs' => true,
                    'postingdari' => 'PROSES GAJI SUPIR',
                    'bank_id' => $data['bank_id'],
                    'penerimaan_nobukti' => $penerimaanHeaderBBM->nobukti,
                ];

                $newPenerimaanTruckingBBM = new PenerimaanTruckingHeader();
                $newPenerimaanTruckingBBM = $newPenerimaanTruckingBBM->findAll($penerimaanBBM->id);
                (new PenerimaanTruckingHeader())->processUpdate($newPenerimaanTruckingBBM, $penerimaanTruckingHeaderBBM);
                $coakreditBBM_detail = [];
                $coadebetBBM_detail = [];
                $nominalBBM_detail = [];
                $keteranganBBM_detail = [];

                $coakreditBBM_detail[] = $coaBBM->coakredit;
                $coadebetBBM_detail[] = $coaBBM->coadebet;
                $nominalBBM_detail[] = $penerimaanBBM->nominal;
                $keteranganBBM_detail[] = $penerimaanBBM->keterangan;

                $jurnalRequest = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $penerimaanBBM->nobukti,
                    'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'postingdari' => "ENTRY PROSES GAJI SUPIR",
                    'statusformat' => "0",
                    'coakredit_detail' => $coakreditBBM_detail,
                    'coadebet_detail' => $coadebetBBM_detail,
                    'nominal_detail' => $nominalBBM_detail,
                    'keterangan_detail' => $keteranganBBM_detail
                ];
                (new JurnalUmumHeader())->processStore($jurnalRequest);
            }
        }
        if ($data['nomUangjalan'] != 0) {

            // $request->nobuktiRIC[$i];
            $pengembalianKasGantungDetail = [];
            $allSP = "";
            foreach ($data['nobuktiRIC'] as $key => $value) {
                if ($key == 0) {
                    $allSP = $allSP . "'$value'";
                } else {
                    $allSP = $allSP . ',' . "'$value'";
                }
            }
            $gajiSupirUangjalan = GajisUpirUangJalan::from(DB::raw("gajisupiruangjalan with (readuncommitted)"))
                ->select(DB::raw("absensisupirheader.kasgantung_nobukti,kasgantungheader.coakaskeluar, sum(gajisupiruangjalan.nominal) as nominal"))
                ->join(DB::raw("absensisupirheader with (readuncommitted)"), 'gajisupiruangjalan.absensisupir_nobukti', 'absensisupirheader.nobukti')
                ->join(DB::raw("kasgantungheader with (readuncommitted)"), 'absensisupirheader.kasgantung_nobukti', 'kasgantungheader.nobukti')
                ->whereRaw("gajisupiruangjalan.gajisupir_nobukti in ($allSP)")
                ->groupBy('absensisupirheader.kasgantung_nobukti', 'kasgantungheader.coakaskeluar')
                ->get();

            foreach ($gajiSupirUangjalan as $key => $value) {

                $nominalUangJalan[] = $value->nominal;
                $keteranganUangJalan[] = 'POSTING UANG JALAN ' . $prosesGajiSupirHeader->nobukti;
                $kasgantung_nobukti[] = $value->kasgantung_nobukti;
                $coaDetailKasGantung[] = $value->coakaskeluar;
            }
            $pengembalianKasGantungHeader = [
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'bank_id' => $data['bank_id'],
                'tgldari' => date('Y-m-d', strtotime($data['tgldari'])),
                'tglsampai' => date('Y-m-d', strtotime($data['tglsampai'])),
                'postingdari' => 'ENTRY PROSES GAJI SUPIR',
                'tglkasmasuk' => date('Y-m-d', strtotime($data['tglbukti'])),
                'nominal' => $nominalUangJalan,
                'keterangandetail' => $keteranganUangJalan,
                'kasgantung_nobukti' => $kasgantung_nobukti,
                'kasgantungdetail_id' => $kasgantung_nobukti,
                'coadetail' => $coaDetailKasGantung
            ];


            (new PengembalianKasGantungHeader())->processStore($pengembalianKasGantungHeader);
        }

        return $prosesGajiSupirHeader;
    }

    public function processUpdate(ProsesGajiSupirHeader $prosesGajiSupirHeader, array $data): ProsesGajiSupirHeader
    {

        $nobuktiEbsOld = $prosesGajiSupirHeader->nobukti;

        $getTgl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'EDIT TANGGAL BUKTI')->where('subgrp', 'PROSES GAJI SUPIR')->first();
        if (trim($getTgl->text) == 'YA') {

            $group = 'PROSES GAJI SUPIR BUKTI';
            $subGroup = 'PROSES GAJI SUPIR BUKTI';

            $querycek = DB::table('prosesgajisupirheader')->from(
                DB::raw("prosesgajisupirheader a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.id', $prosesGajiSupirHeader->id)
                ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
                ->first();

            if (isset($querycek)) {
                $nobukti = $querycek->nobukti;
            } else {
                $nobukti = (new RunningNumberService)->get($group, $subGroup, $prosesGajiSupirHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
            }

            $prosesGajiSupirHeader->nobukti = $nobukti;
            $prosesGajiSupirHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        }

        $prosesGajiSupirHeader->tgldari = date('Y-m-d', strtotime($data['tgldari']));
        $prosesGajiSupirHeader->tglsampai = date('Y-m-d', strtotime($data['tglsampai']));
        $prosesGajiSupirHeader->keterangan = 'PROSES GAJI SUPIR ' . date('d-m-Y', strtotime($data['tgldari'])) . ' s/d ' .  date('d-m-Y', strtotime($data['tglsampai']));
        $prosesGajiSupirHeader->modifiedby = auth('api')->user()->name;
        $prosesGajiSupirHeader->info = html_entity_decode(request()->info);

        if (!$prosesGajiSupirHeader->save()) {
            throw new \Exception("Error Update proses gaji supir header.");
        }

        $prosesGajiSupirHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($prosesGajiSupirHeader->getTable()),
            'postingdari' => 'EDIT PROSES GAJI SUPIR HEADER',
            'idtrans' => $prosesGajiSupirHeader->id,
            'nobuktitrans' => $prosesGajiSupirHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $prosesGajiSupirHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        $pisahGajiKenek = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PROSES GAJI SUPIR')->where('subgrp', 'PISAH GAJI KENEK')
            ->first();
        $isPisahGajiKenek = $pisahGajiKenek->text;
        $penerimaan_nobuktiPS = '';
        $gajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))
            ->where('tglbukti', '>=', $prosesGajiSupirHeader['tgldari'])
            ->where('tglbukti', '<=', $prosesGajiSupirHeader['tglsampai'])
            ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupir_id=" . $prosesGajiSupirHeader['id'] . ")")
            ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from gajisupirpelunasanpinjaman where supir_id=0)")
            ->get();
        foreach ($gajiSupir as $key => $value) {
            $fetchPS = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $value->nobukti)->where('supir_id', '0')->first();
            if ($fetchPS != null) {
                $penerimaanPS = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $fetchPS->penerimaantrucking_nobukti)->first();


                $penerimaan_nobuktiPS = $penerimaanPS->penerimaan_nobukti;

                $penerimaanTruckingHeaderPS = [
                    'ebs' => true,
                    'postingdari' => 'EDIT PROSES GAJI SUPIR',
                    'bank_id' => 0,
                    'penerimaan_nobukti' => ''
                ];

                // $coaKreditPS[] = $penerimaanPS->coa;
                // $nominalPS[] =  $getNominal->sum('nominal');
                // $keteranganPS[] = $penerimaanPS->nobukti;
                // $tglJatuhTempoPS[] = $data['tglbukti'];

                $newPenerimaanTruckingPS = new PenerimaanTruckingHeader();
                $newPenerimaanTruckingPS = $newPenerimaanTruckingPS->findAll($penerimaanPS->id);
                (new PenerimaanTruckingHeader())->processUpdate($newPenerimaanTruckingPS, $penerimaanTruckingHeaderPS);
            }
        }

        $penerimaan_nobuktiPP = '';
        $gajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->where('tglbukti', '>=', $prosesGajiSupirHeader['tgldari'])
            ->where('tglbukti', '<=', $prosesGajiSupirHeader['tglsampai'])
            ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupir_id=" . $prosesGajiSupirHeader['id'] . ")")
            ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from gajisupirpelunasanpinjaman where supir_id != 0)")
            ->get();

        foreach ($gajiSupir as $key => $value) {
            $fetchPP = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $value->nobukti)->where('supir_id', '!=', '0')->first();
            if ($fetchPP != null) {

                $penerimaanPP = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $fetchPP->penerimaantrucking_nobukti)->first();


                $penerimaan_nobuktiPP = $penerimaanPP->penerimaan_nobukti;

                $penerimaanTruckingHeaderPP = [
                    'ebs' => true,
                    'postingdari' => 'EDIT PROSES GAJI SUPIR',
                    'bank_id' => 0,
                    'penerimaan_nobukti' => ''
                ];
                // $coaKreditPP[] = $penerimaanPP->coa;
                // $nominalPP[] =  $getNominalPP->sum('nominal');
                // $keteranganPP[] = $penerimaanPP->nobukti;
                // $tglJatuhTempoPP[] = $data['tglbukti'];

                $newPenerimaanTruckingPP = new PenerimaanTruckingHeader();
                $newPenerimaanTruckingPP = $newPenerimaanTruckingPP->findAll($penerimaanPP->id);
                (new PenerimaanTruckingHeader())->processUpdate($newPenerimaanTruckingPP, $penerimaanTruckingHeaderPP);
            }
        }

        $penerimaan_nobuktiDeposito = '';
        $penerimaan_nobuktiBBM = '';
        $gajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->where('tglbukti', '>=', $prosesGajiSupirHeader['tgldari'])
            ->where('tglbukti', '<=', $prosesGajiSupirHeader['tglsampai'])
            ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupir_id=" . $prosesGajiSupirHeader['id'] . ")")
            ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from gajisupirdeposito)")
            ->get();

        foreach ($gajiSupir as $key => $value) {
            $fetchDeposito = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))->where('gajisupir_nobukti', $value->nobukti)->first();
            if ($fetchDeposito != null) {

                $penerimaanDeposito = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $fetchDeposito->penerimaantrucking_nobukti)->first();
                $getNominalDeposito = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
                    ->where('nobukti', $fetchDeposito->penerimaantrucking_nobukti)->get();
                $penerimaan_nobuktiDeposito = $penerimaanDeposito->penerimaan_nobukti;

                $penerimaanTruckingHeaderDeposito = [
                    'ebs' => true,
                    'postingdari' => 'EDIT PROSES GAJI SUPIR',
                    'bank_id' => 0,
                    'penerimaan_nobukti' => ''
                ];



                $newPenerimaanTruckingDeposito = new PenerimaanTruckingHeader();
                $newPenerimaanTruckingDeposito = $newPenerimaanTruckingDeposito->findAll($penerimaanDeposito->id);
                (new PenerimaanTruckingHeader())->processUpdate($newPenerimaanTruckingDeposito, $penerimaanTruckingHeaderDeposito);
            }
        }

        $gajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->where('tglbukti', '>=', $prosesGajiSupirHeader['tgldari'])
            ->where('tglbukti', '<=', $prosesGajiSupirHeader['tglsampai'])
            ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupir_id=" . $prosesGajiSupirHeader['id'] . ")")
            ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from gajisupirbbm)")
            ->get();

        foreach ($gajiSupir as $key => $value) {
            $fetchBBM = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))->where('gajisupir_nobukti', $value->nobukti)->first();
            if ($fetchBBM != null) {
                $penerimaanBBM = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->first();
                $penerimaan_nobuktiBBM = $penerimaanBBM->penerimaan_nobukti;

                $coaBBM = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))->where('kodepenerimaan', 'BBM')->first();

                $penerimaanTruckingHeaderBBM = [
                    'ebs' => true,
                    'postingdari' => 'EDIT PROSES GAJI SUPIR',
                    'bank_id' => 0,
                    'penerimaan_nobukti' => ''
                ];
                // $coaKreditBBM[] = $coaBBM->coapostingkredit;
                // $nominalBBM[] =  $fetchBBM->nominal;
                // $keteranganBBM[] = $penerimaanBBM->nobukti;
                // $tglJatuhTempoBBM[] = $data['tglbukti'];

                $newPenerimaanTruckingBBM = new PenerimaanTruckingHeader();
                $newPenerimaanTruckingBBM = $newPenerimaanTruckingBBM->findAll($penerimaanBBM->id);
                (new PenerimaanTruckingHeader())->processUpdate($newPenerimaanTruckingBBM, $penerimaanTruckingHeaderBBM);

                $getJurnalHeader = JurnalUmumHeader::lockForUpdate()->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->first();
                (new JurnalUmumHeader())->processDestroy($getJurnalHeader->id, 'PROSES GAJI SUPIR');
            }
        }


        /*DELETE EXISTING Penerimaan*/
        ProsesGajiSupirDetail::where('prosesgajisupir_id', $prosesGajiSupirHeader->id)->lockForUpdate()->delete();
        $coaDebet = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PROSES GAJI SUPIR')->where('kelompok', 'PENGELUARAN')
            ->first();
        $memoDebet = json_decode($coaDebet->memo, true);
        $noWarkat = [];
        $tglJatuhTempo = [];
        $nominalDetailPengeluaran = [];
        $coaDebetPengeluaran = [];
        $keteranganDetailPengeluaran = [];

        $totalPotSemua = 0;
        $totalPotPribadi = 0;
        $totalDeposito = 0;
        $totalBBM = 0;
        $totalKBT = 0;
        $firstBuktiPostingPS = '';
        $firstBuktiNonPostingPS = '';
        $nominalPostingNonPS = ['nonposting' => 0, 'posting' => 0];
        $coakreditPostingNonPS = ['nonposting' => '', 'posting' => ''];
        $keteranganPostingNonPS = ['nonposting' => '', 'posting' => ''];
        $nobuktiPostingPS = '';
        $nobuktiNonPostingPS = '';
        $fetchFormatPJP =  DB::table('penerimaantrucking')->where('id', 2)->first();
        $firstBuktiPosting = '';
        $firstBuktiNonPosting = '';
        $nominalPostingNon = ['nonposting' => 0, 'posting' => 0];
        $coakreditPostingNon = ['nonposting' => '', 'posting' => ''];
        $keteranganPostingNon = ['nonposting' => '', 'posting' => ''];
        $nobuktiPosting = '';
        $nobuktiNonPosting = '';

        $uniqueValuesNonPosting = [];
        $uniqueValuesPosting = [];

        for ($i = 0; $i < count($data['rincianId']); $i++) {
            $sp = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
                ->where('supir_id', $data['supir_id'][$i])->first();

            $prosesGajiSupirDetail = (new ProsesGajiSupirDetail())->processStore($prosesGajiSupirHeader, [
                'gajisupir_nobukti' => $data['nobuktiRIC'][$i],
                'supir_id' => $data['supir_id'][$i],
                'trado_id' => $sp->trado_id,
                'nominal' => $data['totalborongan'][$i],
                'keterangan' => '',
            ]);

            $prosesGajiSupirDetails[] = $prosesGajiSupirDetail->toArray();
            // DATA DETAIL PENGELUARAN

            if ($isPisahGajiKenek == 'YA') {
                $totalKBT = $totalKBT + ($data['totalborongan'][$i] - $data['gajikenek'][$i]);
            } else {
                $totalKBT = $totalKBT + $data['totalborongan'][$i];
            }

            $fetchPS = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $data['nobuktiRIC'][$i])->where('supir_id', '0');
            $cekFetchPS = $fetchPS->first();

            if ($cekFetchPS != null) {
                $dataFetchPS = $fetchPS->get();
                $getNominal = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
                    ->where('nobukti', $cekFetchPS->penerimaantrucking_nobukti)->get();
                $totalPotSemua = $totalPotSemua + $getNominal->sum('nominal');
                foreach ($dataFetchPS as $dataPS) {
                    $queryposting = db::table('pengeluarantruckingheader')->from(db::raw("pengeluarantruckingheader a with (readuncommitted)"))
                        ->select('statusposting', 'coa')->where('nobukti', $dataPS->pengeluarantrucking_nobukti)->first();
                    if (isset($queryposting)) {
                        $bukanposting = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                            ->select('id')
                            ->where('grp', 'STATUS POSTING')
                            ->where('subgrp', 'STATUS POSTING')
                            ->where('id', '84')
                            ->first()->id ?? 0;
                        if ($bukanposting == $queryposting->statusposting) {
                            if ($firstBuktiNonPostingPS == '') {
                                $nobuktiNonPostingPS = $nobuktiNonPostingPS .  $dataPS->pengeluarantrucking_nobukti;
                                $uniqueValuesNonPosting[] = $dataPS->pengeluarantrucking_nobukti;
                                $coakreditPostingNonPS['nonposting'] =  $queryposting->coa ?? $fetchFormatPJP->coapostingkredit;
                                $tglJatuhTempoPS[] = date('Y-m-d', strtotime($data['tglbukti']));
                                $firstBuktiNonPostingPS =  $dataPS->pengeluarantrucking_nobukti;
                            } else {
                                if (!in_array($dataPS->pengeluarantrucking_nobukti, $uniqueValuesNonPosting)) {
                                    $uniqueValuesNonPosting[] = $dataPS->pengeluarantrucking_nobukti;

                                    $nobuktiNonPostingPS = $nobuktiNonPostingPS . ', ' .  $dataPS->pengeluarantrucking_nobukti;
                                }
                            }
                            $keteranganPostingNonPS['nonposting'] =  '(NON POSTING) PENGEMBALIAN PINJAMAN ' . $nobuktiNonPostingPS;

                            $nominalPostingNonPS['nonposting'] += $dataPS->nominal;
                        } else {
                            if ($firstBuktiPostingPS == '') {
                                $nobuktiPostingPS = $nobuktiPostingPS . $dataPS->pengeluarantrucking_nobukti;
                                $uniqueValuesPosting[] = $dataPS->pengeluarantrucking_nobukti;
                                $coakreditPostingNonPS['posting'] = $fetchFormatPJP->coapostingkredit;
                                $tglJatuhTempoPS[] = date('Y-m-d', strtotime($data['tglbukti']));
                                $firstBuktiPostingPS = $dataPS->pengeluarantrucking_nobukti;
                            } else {
                                if (!in_array($dataPS->pengeluarantrucking_nobukti, $uniqueValuesPosting)) {
                                    $uniqueValuesPosting[] = $dataPS->pengeluarantrucking_nobukti;

                                    $nobuktiPostingPS = $nobuktiPostingPS . ', ' . $dataPS->pengeluarantrucking_nobukti;
                                }
                            }
                            $keteranganPostingNonPS['posting'] =  '(POSTING) PENGEMBALIAN PINJAMAN ' . $nobuktiPostingPS;

                            $nominalPostingNonPS['posting'] += $dataPS->nominal;
                        }
                    }
                }
            }

            $fetchPP = DB::table("gajisupirpelunasanpinjaman")->from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $data['nobuktiRIC'][$i])->where('supir_id', '!=', '0')->first();
            if ($fetchPP != null) {
                $dataFetchPP = DB::table("gajisupirpelunasanpinjaman")->from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $data['nobuktiRIC'][$i])->where('supir_id', '!=', '0')->get();

                $getNominal = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
                    ->where('nobukti', $fetchPP->penerimaantrucking_nobukti)->get();
                $totalPotPribadi = $totalPotPribadi + $getNominal->sum('nominal');
                foreach ($dataFetchPP as $dataPP) {
                    $queryposting = db::table('pengeluarantruckingheader')->from(db::raw("pengeluarantruckingheader a with (readuncommitted)"))
                        ->select('statusposting', 'coa')->where('nobukti', $dataPP->pengeluarantrucking_nobukti)->first();
                    if (isset($queryposting)) {
                        $bukanposting = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                            ->select('id')
                            ->where('grp', 'STATUS POSTING')
                            ->where('subgrp', 'STATUS POSTING')
                            ->where('id', '84')
                            ->first()->id ?? 0;
                        if ($bukanposting == $queryposting->statusposting) {
                            if ($firstBuktiNonPosting == '') {
                                $nobuktiNonPosting = $nobuktiNonPosting .  $dataPP->pengeluarantrucking_nobukti;
                                $coakreditPostingNon['nonposting'] =  $queryposting->coa ?? $fetchFormatPJP->coapostingkredit;
                                $tglJatuhTempoPP[] = date('Y-m-d', strtotime($data['tglbukti']));
                                $firstBuktiNonPosting =  $dataPP->pengeluarantrucking_nobukti;
                            } else {
                                $nobuktiNonPosting = $nobuktiNonPosting . ', ' .  $dataPP->pengeluarantrucking_nobukti;
                            }
                            $keteranganPostingNon['nonposting'] =  '(NON POSTING) PENGEMBALIAN PINJAMAN ' . $nobuktiNonPosting;

                            $nominalPostingNon['nonposting'] += $dataPP->nominal;
                        } else {
                            if ($firstBuktiPosting == '') {
                                $nobuktiPosting = $nobuktiPosting . $dataPP->pengeluarantrucking_nobukti;
                                $coakreditPostingNon['posting'] = $fetchFormatPJP->coapostingkredit;
                                $tglJatuhTempoPP[] = date('Y-m-d', strtotime($data['tglbukti']));
                                $firstBuktiPosting = $dataPP->pengeluarantrucking_nobukti;
                            } else {
                                $nobuktiPosting = $nobuktiPosting . ', ' . $dataPP->pengeluarantrucking_nobukti;
                            }
                            $keteranganPostingNon['posting'] =  '(POSTING) PENGEMBALIAN PINJAMAN ' . $nobuktiPosting;

                            $nominalPostingNon['posting'] += $dataPP->nominal;
                        }
                    }
                }
            }

            $fetchDeposito = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))->where('gajisupir_nobukti', $data['nobuktiRIC'][$i])->first();
            if ($fetchDeposito != null) {
                $getNominal = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
                    ->where('nobukti', $fetchDeposito->penerimaantrucking_nobukti)->get();
                $totalDeposito = $totalDeposito + $getNominal->sum('nominal');
            }

            $fetchBBM = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))->where('gajisupir_nobukti', $data['nobuktiRIC'][$i])->first();
            if ($fetchBBM != null) {
                $getNominal = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
                    ->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->first();
                $totalBBM = $totalBBM + $fetchBBM->nominal;
            }
        }

        if ($data['nomPS'] != 0) {

            $getPS = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))
                ->select('id')
                ->where('nobukti', $penerimaan_nobuktiPS)->first();

            if ($getPS != null) {

                $nominalPostingNonPS = array_filter($nominalPostingNonPS, function ($value) {
                    return $value !== 0;
                });
                $coakreditPostingNonPS = array_filter($coakreditPostingNonPS, function ($value) {
                    return $value !== '';
                });
                $keteranganPostingNonPS = array_filter($keteranganPostingNonPS, function ($value) {
                    return $value !== '';
                });
                $nominalPostingNonPS = array_values($nominalPostingNonPS);
                $coakreditPostingNonPS = array_values($coakreditPostingNonPS);
                $keteranganPostingNonPS = array_values($keteranganPostingNonPS);
                $nominalPS = $nominalPostingNonPS;
                $coaKreditPS = $coakreditPostingNonPS;
                $keteranganPS = $keteranganPostingNonPS;

                $penerimaanHeaderPS = [

                    'tglbukti' => $prosesGajiSupirHeader->tglbukti,
                    'pelanggan_id' => '',
                    'bank_id' => $data['bank_id'],
                    'postingdari' => 'EDIT PROSES GAJI SUPIR',
                    'diterimadari' => "PROSES GAJI SUPIR PERIODE " . date('d-m-Y', strtotime($data['tgldari'])) . " S/D " . date('d-m-Y', strtotime($data['tglsampai'])),
                    'tgllunas' => $prosesGajiSupirHeader->tglbukti,
                    'coakredit' => $coaKreditPS,
                    'nominal_detail' => $nominalPS,
                    'keterangan_detail' => $keteranganPS,
                    'tgljatuhtempo' => $tglJatuhTempoPS

                ];
                $newPenerimaanPS = new PenerimaanHeader();
                $newPenerimaanPS = $newPenerimaanPS->findAll($getPS->id);
                $dataPenerimaanPS = (new PenerimaanHeader())->processUpdate($newPenerimaanPS, $penerimaanHeaderPS);
                $penerimaan_nobuktiPS = $dataPenerimaanPS->nobukti;
            }

            for ($i = 0; $i < count($data['rincianId']); $i++) {
                $fetchPS = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $data['nobuktiRIC'][$i])->where('supir_id', '0')->first();
                if ($fetchPS != null) {

                    $penerimaanPS = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                        ->where('nobukti', $fetchPS->penerimaantrucking_nobukti)->first();
                    $penerimaanTruckingHeaderPS = [
                        'ebs' => true,
                        'postingdari' => 'EDIT PROSES GAJI SUPIR',
                        'bank_id' => $data['bank_id'],
                        'penerimaan_nobukti' => $penerimaan_nobuktiPS,
                    ];
                    $newPenerimaanTruckingPS = new PenerimaanTruckingHeader();
                    $newPenerimaanTruckingPS = $newPenerimaanTruckingPS->findAll($penerimaanPS->id);
                    (new PenerimaanTruckingHeader())->processUpdate($newPenerimaanTruckingPS, $penerimaanTruckingHeaderPS);
                }
            }
        }

        if ($data['nomPP'] != 0) {

            $bank = Bank::from(DB::raw("bank with (readuncommitted)"))
                ->select('coa')
                ->where('id', $data['bank_id'])
                ->first();

            $getPP = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))
                ->select('id')
                ->where('nobukti', $penerimaan_nobuktiPP)->first();

            if ($getPP != null) {
                $nominalPostingNon = array_filter($nominalPostingNon, function ($value) {
                    return $value !== 0;
                });
                $coakreditPostingNon = array_filter($coakreditPostingNon, function ($value) {
                    return $value !== '';
                });
                $keteranganPostingNon = array_filter($keteranganPostingNon, function ($value) {
                    return $value !== '';
                });
                $nominalPostingNon = array_values($nominalPostingNon);
                $coakreditPostingNon = array_values($coakreditPostingNon);
                $keteranganPostingNon = array_values($keteranganPostingNon);
                $nominalPP = $nominalPostingNon;
                $coaKreditPP = $coakreditPostingNon;
                $keteranganPP = $keteranganPostingNon;

                $penerimaanHeaderPP = [
                    'tglbukti' => $prosesGajiSupirHeader->tglbukti,
                    'pelanggan_id' => '',
                    'bank_id' => $data['bank_id'],
                    'postingdari' => 'EDIT PROSES GAJI SUPIR',
                    'diterimadari' => "PROSES GAJI SUPIR PERIODE " . date('d-m-Y', strtotime($data['tgldari'])) . " S/D " . date('d-m-Y', strtotime($data['tglsampai'])),
                    'tgllunas' => $prosesGajiSupirHeader->tglbukti,
                    'coakredit' => $coaKreditPP,
                    'nominal_detail' => $nominalPP,
                    'keterangan_detail' => $keteranganPP,
                    'tgljatuhtempo' => $tglJatuhTempoPP

                ];
                $newPenerimaanPP = new PenerimaanHeader();
                $newPenerimaanPP = $newPenerimaanPP->findAll($getPP->id);
                $dataPenerimaanPP = (new PenerimaanHeader())->processUpdate($newPenerimaanPP, $penerimaanHeaderPP);

                $penerimaan_nobuktiPP = $dataPenerimaanPP->nobukti;
            }

            for ($i = 0; $i < count($data['rincianId']); $i++) {
                $fetchPP = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $data['nobuktiRIC'][$i])->where('supir_id', '!=', '0')->first();
                if ($fetchPP != null) {

                    $penerimaanPP = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                        ->where('nobukti', $fetchPP->penerimaantrucking_nobukti)->first();
                    $penerimaanTruckingHeaderPP = [
                        'ebs' => true,
                        'tglbukti' => $prosesGajiSupirHeader->tglbukti,
                        'postingdari' => 'EDIT PROSES GAJI SUPIR',
                        'bank_id' => $data['bank_id'],
                        'penerimaan_nobukti' => $penerimaan_nobuktiPP,
                    ];

                    $newPenerimaanTruckingPP = new PenerimaanTruckingHeader();
                    $newPenerimaanTruckingPP = $newPenerimaanTruckingPP->findAll($penerimaanPP->id);
                    (new PenerimaanTruckingHeader())->processUpdate($newPenerimaanTruckingPP, $penerimaanTruckingHeaderPP);
                }
            }
        }

        if ($data['nomDeposito'] != 0) {
            $bank = Bank::from(DB::raw("bank with (readuncommitted)"))
                ->select('coa')
                ->where('id', $data['bank_id'])
                ->first();

            $getDeposito = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))
                ->select('id')
                ->where('nobukti', $penerimaan_nobuktiDeposito)->first();
            if ($data['nomDeposito'] != 0) {
                if ($getDeposito != null) {

                    $coaKreditDeposito[] = $penerimaanDeposito->coa;
                    $nominalDeposito[] = $totalDeposito;
                    $keteranganDeposito[] = 'DEPOSITO SUPIR ' . $prosesGajiSupirHeader->nobukti;
                    $tglJatuhTempoDeposito[] = $prosesGajiSupirHeader->tglbukti;
                    $penerimaanHeaderDeposito = [
                        'tglbukti' => $prosesGajiSupirHeader->tglbukti,
                        'pelanggan_id' => '',
                        'bank_id' => $data['bank_id'],
                        'postingdari' => 'EDIT PROSES GAJI SUPIR',
                        'diterimadari' => "PROSES GAJI SUPIR PERIODE " . date('d-m-Y', strtotime($data['tgldari'])) . " S/D " . date('d-m-Y', strtotime($data['tglsampai'])),
                        'tgllunas' => $prosesGajiSupirHeader->tglbukti,
                        'coakredit' => $coaKreditDeposito,
                        'nominal_detail' => $nominalDeposito,
                        'keterangan_detail' => $keteranganDeposito,
                        'tgljatuhtempo' => $tglJatuhTempoDeposito


                    ];

                    $newPenerimaanDeposito = new PenerimaanHeader();
                    $newPenerimaanDeposito = $newPenerimaanDeposito->findAll($getDeposito->id);
                    $dataPenerimaanDeposito =  (new PenerimaanHeader())->processUpdate($newPenerimaanDeposito, $penerimaanHeaderDeposito);
                    $penerimaan_nobuktiDeposito = $dataPenerimaanDeposito->nobukti;
                } else {

                    $gajiSupirDeposito = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->where('tglbukti', '>=', $prosesGajiSupirHeader['tgldari'])
                        ->where('tglbukti', '<=', $prosesGajiSupirHeader['tglsampai'])
                        ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupir_id=" . $prosesGajiSupirHeader['id'] . ")")
                        ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from gajisupirdeposito)")
                        ->get();
                    $totalDepo = 0;
                    foreach ($gajiSupirDeposito as $key => $value) {
                        $fetchDeposito = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))->where('gajisupir_nobukti', $value->nobukti)->first();

                        $penerimaanDeposito = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                            ->where('nobukti', $fetchDeposito->penerimaantrucking_nobukti)->first();
                        $getNominalDeposito = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
                            ->where('nobukti', $fetchDeposito->penerimaantrucking_nobukti)->get();
                        $totalDepo = $totalDepo + $getNominalDeposito->sum('nominal');
                    }

                    $coaKreditDeposito[] = $penerimaanDeposito->coa;
                    $nominalDeposito[] = $totalDepo;
                    $keteranganDeposito[] = 'DEPOSITO SUPIR ' . $prosesGajiSupirHeader->nobukti;
                    $tglJatuhTempoDeposito[] = $prosesGajiSupirHeader->tglbukti;

                    $penerimaanHeaderDeposito = [
                        'tglbukti' => $prosesGajiSupirHeader->tglbukti,
                        'pelanggan_id' => '',
                        'bank_id' => $data['bank_id'],
                        'postingdari' => 'EDIT PROSES GAJI SUPIR',
                        'diterimadari' => "PROSES GAJI SUPIR PERIODE " . date('d-m-Y', strtotime($data['tgldari'])) . " S/D " . date('d-m-Y', strtotime($data['tglsampai'])),
                        'tgllunas' => $prosesGajiSupirHeader->tglbukti,
                        'coakredit' => $coaKreditDeposito,
                        'nominal_detail' => $nominalDeposito,
                        'keterangan_detail' => $keteranganDeposito,
                        'tgljatuhtempo' => $tglJatuhTempoDeposito
                    ];

                    $penerimaanHeaderDeposito = (new PenerimaanHeader())->processStore($penerimaanHeaderDeposito);

                    $penerimaan_nobuktiDeposito = $penerimaanHeaderDeposito->nobukti;
                }
            } else {
                if ($getDeposito != null) {
                    (new PenerimaanHeader())->processDestroy($getDeposito->id, 'PROSES GAJI SUPIR');
                }
            }

            for ($i = 0; $i < count($data['rincianId']); $i++) {
                $fetchDeposito = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))->where('gajisupir_nobukti', $data['nobuktiRIC'][$i])->first();

                if ($fetchDeposito != null) {

                    $penerimaanDeposito = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                        ->where('nobukti', $fetchDeposito->penerimaantrucking_nobukti)->first();
                    $penerimaanTruckingHeaderDeposito = [
                        'ebs' => true,
                        'tglbukti' => $prosesGajiSupirHeader->tglbukti,
                        'postingdari' => 'EDIT PROSES GAJI SUPIR',
                        'bank_id' => $data['bank_id'],
                        'penerimaan_nobukti' => $penerimaan_nobuktiDeposito,
                    ];
                    $newPenerimaanTruckingDeposito = new PenerimaanTruckingHeader();
                    $newPenerimaanTruckingDeposito = $newPenerimaanTruckingDeposito->findAll($penerimaanDeposito->id);
                    (new PenerimaanTruckingHeader())->processUpdate($newPenerimaanTruckingDeposito, $penerimaanTruckingHeaderDeposito);
                }
            }
        }

        if ($data['nomBBM'] != 0) {
            $bank = Bank::from(DB::raw("bank with (readuncommitted)"))
                ->select('coa')
                ->where('id', $data['bank_id'])
                ->first();
            $getBBM = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))
                ->select('id')
                ->where('nobukti', $penerimaan_nobuktiBBM)->first();
            if ($data['nomBBM'] != 0) {
                if ($getBBM != null) {
                    $coaKreditBBM[] = $coaBBM->coapostingkredit;
                    $nominalBBM[] =  $totalBBM;
                    $keteranganBBM[] = 'BBM SUPIR ' . $prosesGajiSupirHeader->nobukti;
                    $tglJatuhTempoBBM[] = $prosesGajiSupirHeader->tglbukti;

                    $penerimaanHeaderBBM = [
                        'tglbukti' => $prosesGajiSupirHeader->tglbukti,
                        'pelanggan_id' => '',
                        'bank_id' => $data['bank_id'],
                        'postingdari' => 'EDIT PROSES GAJI SUPIR',
                        'diterimadari' => "PROSES GAJI SUPIR PERIODE " . date('d-m-Y', strtotime($data['tgldari'])) . " S/D " . date('d-m-Y', strtotime($data['tglsampai'])),
                        'tgllunas' => $prosesGajiSupirHeader->tglbukti,
                        'coakredit' => $coaKreditBBM,
                        'nominal_detail' => $nominalBBM,
                        'keterangan_detail' => $keteranganBBM,
                        'tgljatuhtempo' => $tglJatuhTempoBBM

                    ];
                    $newPenerimaanBBM = new PenerimaanHeader();
                    $newPenerimaanBBM = $newPenerimaanBBM->findAll($getBBM->id);
                    $penerimaanHeaderBBM = (new PenerimaanHeader())->processUpdate($newPenerimaanBBM, $penerimaanHeaderBBM);
                    $penerimaan_nobuktiBBM = $penerimaanHeaderBBM->nobukti;
                } else {

                    $gajiSupirBBM = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->where('tglbukti', '>=', $prosesGajiSupirHeader['tgldari'])
                        ->where('tglbukti', '<=', $prosesGajiSupirHeader['tglsampai'])
                        ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupir_id=" . $prosesGajiSupirHeader['id'] . ")")
                        ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from gajisupirbbm)")
                        ->get();
                    $totalBBM = 0;
                    foreach ($gajiSupirBBM as $key => $value) {
                        $fetchBBM = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))->where('gajisupir_nobukti', $value->nobukti)->first();

                        $penerimaanBBM = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                            ->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->first();
                        $coaBBM = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))->where('kodepenerimaan', 'BBM')->first();
                        $totalBBM = $totalBBM + $fetchBBM->nominal;
                    }


                    $coaKreditBBM[] = $coaBBM->coapostingkredit;
                    $nominalBBM[] = $totalBBM;
                    $tglJatuhTempoBBM[] = $prosesGajiSupirHeader->tglbukti;
                    $keteranganBBM[] = 'BBM SUPIR ' . $prosesGajiSupirHeader->nobukti;

                    $penerimaanHeadeRequest = [
                        'tglbukti' => $prosesGajiSupirHeader->tglbukti,
                        'pelanggan_id' => '',
                        'bank_id' => $data['bank_id'],
                        'postingdari' => 'EDIT PROSES GAJI SUPIR',
                        'diterimadari' => "PROSES GAJI SUPIR PERIODE " . date('d-m-Y', strtotime($data['tgldari'])) . " S/D " . date('d-m-Y', strtotime($data['tglsampai'])),
                        'tgllunas' => $prosesGajiSupirHeader->tglbukti,
                        'coakredit' => $coaKreditBBM,
                        'nominal_detail' => $nominalBBM,
                        'keterangan_detail' => $keteranganBBM,
                        'tgljatuhtempo' => $tglJatuhTempoBBM
                    ];

                    $penerimaanHeaderBBM = (new PenerimaanHeader())->processStore($penerimaanHeadeRequest);

                    $penerimaan_nobuktiBBM = $penerimaanHeaderBBM->nobukti;
                }
            } else {
                if ($getBBM != null) {
                    (new PenerimaanHeader())->processDestroy($getBBM->id, 'PROSES GAJI SUPIR');

                    $gajiSupirBBM = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->where('tglbukti', '>=', $prosesGajiSupirHeader['tgldari'])
                        ->where('tglbukti', '<=', $prosesGajiSupirHeader['tglsampai'])
                        ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupir_id=" . $prosesGajiSupirHeader['id'] . ")")
                        ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from gajisupirbbm)")
                        ->get();
                    $totalBBM = 0;
                    foreach ($gajiSupirBBM as $key => $value) {
                        $fetchBBM = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))->where('gajisupir_nobukti', $value->nobukti)->first();

                        $getJurnalHeader = JurnalUmumHeader::lockForUpdate()->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->first();
                        (new JurnalUmumHeader())->processDestroy($getJurnalHeader->id, 'PROSES GAJI SUPIR');
                    }
                }
            }
            for ($i = 0; $i < count($data['rincianId']); $i++) {
                $fetchBBM = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))->where('gajisupir_nobukti', $data['nobuktiRIC'][$i])->first();

                if ($fetchBBM != null) {

                    $penerimaanBBM = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                        ->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->first();
                    $coaBBM = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))->where('kodepenerimaan', 'BBM')->first();
                    $penerimaanTruckingHeaderBBM = [
                        'ebs' => true,
                        'tglbukti' => $prosesGajiSupirHeader->tglbukti,
                        'postingdari' => 'EDIT PROSES GAJI SUPIR',
                        'bank_id' => $data['bank_id'],
                        'penerimaan_nobukti' => $penerimaan_nobuktiBBM,
                    ];
                    $getNominal = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
                        ->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->first();

                    $newPenerimaanTruckingBBM = new PenerimaanTruckingHeader();
                    $newPenerimaanTruckingBBM = $newPenerimaanTruckingBBM->findAll($penerimaanBBM->id);
                    (new PenerimaanTruckingHeader())->processUpdate($newPenerimaanTruckingBBM, $penerimaanTruckingHeaderBBM);
                    $coakreditBBM_detail = [];
                    $coadebetBBM_detail = [];
                    $nominalBBM_detail = [];
                    $keteranganBBM_detail = [];

                    $coakreditBBM_detail[] = $coaBBM->coakredit;
                    $coadebetBBM_detail[] = $coaBBM->coadebet;
                    $nominalBBM_detail[] = $fetchBBM->nominal;
                    $keteranganBBM_detail[] = $getNominal->keterangan;

                    $jurnalRequest = [
                        'tanpaprosesnobukti' => 1,
                        'nobukti' => $penerimaanBBM->nobukti,
                        'tglbukti' => $prosesGajiSupirHeader->tglbukti,
                        'postingdari' => "EDIT PROSES GAJI SUPIR",
                        'statusformat' => "0",
                        'coakredit_detail' => $coakreditBBM_detail,
                        'coadebet_detail' => $coadebetBBM_detail,
                        'nominal_detail' => $nominalBBM_detail,
                        'keterangan_detail' => $keteranganBBM_detail
                    ];
                    (new JurnalUmumHeader())->processStore($jurnalRequest);
                }
            }
        }




        if ($data['nomUangjalan'] != 0) {
            $allSP = "";
            foreach ($data['nobuktiRIC'] as $key => $value) {
                if ($key == 0) {
                    $allSP = $allSP . "'$value'";
                } else {
                    $allSP = $allSP . ',' . "'$value'";
                }
            }
            $nobuktiPenerimaanKasgantung = PengembalianKasGantungHeader::from(DB::raw("pengembaliankasgantungheader with (readuncommitted)"))->where('penerimaan_nobukti', $data['nobuktiUangjalan'])->first();


            $gajiSupirUangjalan = GajisUpirUangJalan::from(DB::raw("gajisupiruangjalan with (readuncommitted)"))
                ->select(DB::raw("absensisupirheader.kasgantung_nobukti, kasgantungheader.coakaskeluar, sum(gajisupiruangjalan.nominal) as nominal"))
                ->join(DB::raw("absensisupirheader with (readuncommitted)"), 'gajisupiruangjalan.absensisupir_nobukti', 'absensisupirheader.nobukti')
                ->join(DB::raw("kasgantungheader with (readuncommitted)"), 'absensisupirheader.kasgantung_nobukti', 'kasgantungheader.nobukti')
                ->whereRaw("gajisupiruangjalan.gajisupir_nobukti in ($allSP)")
                ->groupBy('absensisupirheader.kasgantung_nobukti', 'kasgantungheader.coakaskeluar')
                ->get();

            foreach ($gajiSupirUangjalan as $key => $value) {
                $nominalUangJalan[] = $value->nominal;
                $keteranganUangJalan[] = 'POSTING UANG JALAN ' . $prosesGajiSupirHeader->nobukti;
                $kasgantung_nobukti[] = $value->kasgantung_nobukti;
                $coaDetailKasGantung[] = $value->coakaskeluar;
            }
            $pengembalianKasGantungHeader = [
                'tglbukti' => $prosesGajiSupirHeader->tglbukti,
                'bank_id' => $data['bank_id'],
                'tgldari' => date('Y-m-d', strtotime($data['tgldari'])),
                'tglsampai' => date('Y-m-d', strtotime($data['tglsampai'])),
                'postingdari' => 'EDIT PROSES GAJI SUPIR',
                'tglkasmasuk' => $prosesGajiSupirHeader->tglbukti,
                'nominal' => $nominalUangJalan,
                'keterangandetail' => $keteranganUangJalan,
                'kasgantung_nobukti' => $kasgantung_nobukti,
                'kasgantungdetail_id' => $kasgantung_nobukti,
                'coadetail' => $coaDetailKasGantung
            ];
            if ($nobuktiPenerimaanKasgantung != null) {

                $newPengembalianKasgantung = new PengembalianKasGantungHeader();
                $newPengembalianKasgantung = $newPengembalianKasgantung->findAll($nobuktiPenerimaanKasgantung->id);
                (new PengembalianKasGantungHeader())->processUpdate($newPengembalianKasgantung, $pengembalianKasGantungHeader);
            } else {
                (new PengembalianKasGantungHeader())->processStore($pengembalianKasGantungHeader);
            }
        }

        //UPDATE EBS DI PENGELUARAN
        $noWarkat[] = '';
        $tglJatuhTempo[] = $prosesGajiSupirHeader->tglbukti;
        $nominalDetailPengeluaran[] = $totalKBT;
        $coaDebetPengeluaran[] = $memoDebet['JURNAL'];
        $keteranganDetailPengeluaran[] = "Rincian Borongan Supir periode " . date('d-m-Y', strtotime($data['tgldari'])) . " s/d " . date('d-m-Y', strtotime($data['tglsampai']));
        $pengeluaranHeaderRequest = [
            'tglbukti' => $prosesGajiSupirHeader->tglbukti,
            'pelanggan_id' => 0,
            'postingdari' => 'EDIT PROSES GAJI SUPIR',
            'dibayarke' => 'PROSES GAJI SUPIR',
            'bank_id' => $data['bank_id'],
            'nowarkat' => $noWarkat,
            'tgljatuhtempo' => $tglJatuhTempo,
            'nominal_detail' => $nominalDetailPengeluaran,
            'coadebet' => $coaDebetPengeluaran,
            'keterangan_detail' => $keteranganDetailPengeluaran,
        ];

        $get = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))
            ->where('pengeluaranheader.nobukti', $prosesGajiSupirHeader->pengeluaran_nobukti)->first();
        $newPengeluaran = new PengeluaranHeader();
        $newPengeluaran = $newPengeluaran->findAll($get->id);
        $pengeluaranHeader = (new PengeluaranHeader())->processUpdate($newPengeluaran, $pengeluaranHeaderRequest);

        $prosesGajiSupirHeader->pengeluaran_nobukti = $pengeluaranHeader->nobukti;

        $prosesGajiSupirHeader->save();
        // UPDATE EBS JURNAL
        $getData = $prosesGajiSupirHeader->getDataJurnal($data['nobuktiRIC']);
        $nominalJurnal = [];
        $coaDebetJurnal = [];
        $coaKreditJurnal = [];
        $keteranganJurnal = [];

        $coadebet = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->select('memo')->where('grp', 'JURNAL PROSES GAJI SUPIR')->where('kelompok', 'PROSES GAJI SUPIR')->where('subgrp', 'DEBET')->first();
        $coakredit = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->select('memo')->where('grp', 'JURNAL PROSES GAJI SUPIR')->where('kelompok', 'PROSES GAJI SUPIR')->where('subgrp', 'KREDIT')->first();

        $memodebet = json_decode($coadebet->memo, true);
        $memokredit = json_decode($coakredit->memo, true);

        for ($i = 0; $i < count($getData); $i++) {
            $tglbuktiJurnal[] = $getData[$i]->tglbukti;
            $nominalJurnal[] = $getData[$i]->nominal;
            $coaDebetJurnal[] = $memodebet['JURNAL'];
            $coaKreditJurnal[] = $memokredit['JURNAL'];
            $keteranganJurnal[] = $getData[$i]->keterangan;
        }

        $jurnalRequest = [
            'tanpaprosesnobukti' => 1,
            'nobukti' => $prosesGajiSupirHeader->nobukti,
            'tglbukti' => $prosesGajiSupirHeader->tglbukti,
            'postingdari' => "EDIT PROSES GAJI SUPIR",
            'modifiedby' => auth('api')->user()->name,
            'statusformat' => "0",
            'tglbukti_detail' => $tglbuktiJurnal,
            'coakredit_detail' => $coaKreditJurnal,
            'coadebet_detail' => $coaDebetJurnal,
            'nominal_detail' => $nominalJurnal,
            'keterangan_detail' => $keteranganJurnal
        ];
        $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $nobuktiEbsOld)->first();
        $newJurnal = new JurnalUmumHeader();
        $newJurnal = $newJurnal->find($getJurnal->id);
        (new JurnalUmumHeader())->processUpdate($newJurnal, $jurnalRequest);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($prosesGajiSupirDetail->getTable()),
            'postingdari' => 'EDIT PROSES GAJI SUPIR DETAIL',
            'idtrans' => $prosesGajiSupirHeaderLogTrail->id,
            'nobuktitrans' => $prosesGajiSupirHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $prosesGajiSupirDetails,
            'modifiedby' => auth('api')->user()->user
        ]);


        return $prosesGajiSupirHeader;
    }

    public function processDestroy($id, $postingDari = "", $nobuktiUangjalan): ProsesGajiSupirHeader
    {
        $prosesGajiSupirDetails = ProsesGajiSupirDetail::lockForUpdate()->where('prosesgajisupir_id', $id)->get();

        $prosesGajiSupirHeader = new ProsesGajiSupirHeader();
        $prosesGajiSupirHeader = $prosesGajiSupirHeader->lockAndDestroy($id);

        $prosesGajiSupirHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $prosesGajiSupirHeader->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $prosesGajiSupirHeader->id,
            'nobuktitrans' => $prosesGajiSupirHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $prosesGajiSupirHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'PROSESGAJISUPIRDETAIL',
            'postingdari' => $postingDari,
            'idtrans' => $prosesGajiSupirHeaderLogTrail['id'],
            'nobuktitrans' => $prosesGajiSupirHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $prosesGajiSupirDetails->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);


        $getPengeluaran = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))->where('nobukti', $prosesGajiSupirHeader->pengeluaran_nobukti)->first();
        (new PengeluaranHeader())->processDestroy($getPengeluaran->id, $postingDari);

        $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $prosesGajiSupirHeader->nobukti)->first();
        (new JurnalUmumHeader())->processDestroy($getJurnal->id, $postingDari);

        foreach ($prosesGajiSupirDetails as $key => $value) {
            $fetchPS = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $value->gajisupir_nobukti)->where('supir_id', '0')->first();
            if ($fetchPS != null) {

                $penerimaanPS = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $fetchPS->penerimaantrucking_nobukti)->first();

                $penerimaanTruckingHeaderPS = [
                    'ebs' => true,
                    'postingdari' => 'PROSES GAJI SUPIR',
                    'bank_id' => 0,
                    'penerimaan_nobukti' => '',
                ];
                $newPenerimaanTruckingPS = new PenerimaanTruckingHeader();
                $newPenerimaanTruckingPS = $newPenerimaanTruckingPS->findAll($penerimaanPS->id);
                (new PenerimaanTruckingHeader())->processUpdate($newPenerimaanTruckingPS, $penerimaanTruckingHeaderPS);

                $getPenerimaanPS = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))->where('nobukti', $penerimaanPS->penerimaan_nobukti)->first();
                if ($getPenerimaanPS != null) {
                    (new PenerimaanHeader())->processDestroy($getPenerimaanPS->id, $postingDari);
                }
            }

            $fetchPP = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $value->gajisupir_nobukti)->where('supir_id', '!=', '0')->first();

            if ($fetchPP != null) {

                $penerimaanPP = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $fetchPP->penerimaantrucking_nobukti)->first();

                $penerimaanTruckingHeaderPP = [
                    'ebs' => true,
                    'postingdari' => 'PROSES GAJI SUPIR',
                    'bank_id' => 0,
                    'penerimaan_nobukti' => '',
                ];
                $newPenerimaanTruckingPP = new PenerimaanTruckingHeader();
                $newPenerimaanTruckingPP = $newPenerimaanTruckingPP->findAll($penerimaanPP->id);
                (new PenerimaanTruckingHeader())->processUpdate($newPenerimaanTruckingPP, $penerimaanTruckingHeaderPP);

                $getPenerimaanPP = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))->where('nobukti', $penerimaanPP->penerimaan_nobukti)->first();
                if ($getPenerimaanPP != null) {
                    (new PenerimaanHeader())->processDestroy($getPenerimaanPP->id, $postingDari);
                }
            }

            $fetchDeposito = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))->where('gajisupir_nobukti', $value->gajisupir_nobukti)->first();

            if ($fetchDeposito != null) {

                $penerimaanDeposito = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $fetchDeposito->penerimaantrucking_nobukti)->first();

                $penerimaanTruckingHeaderDeposito = [
                    'ebs' => true,
                    'postingdari' => 'PROSES GAJI SUPIR',
                    'bank_id' => 0,
                    'penerimaan_nobukti' => '',
                ];
                $newPenerimaanTruckingDeposito = new PenerimaanTruckingHeader();
                $newPenerimaanTruckingDeposito = $newPenerimaanTruckingDeposito->findAll($penerimaanDeposito->id);
                (new PenerimaanTruckingHeader())->processUpdate($newPenerimaanTruckingDeposito, $penerimaanTruckingHeaderDeposito);

                $getPenerimaanDeposito = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))->where('nobukti', $penerimaanDeposito->penerimaan_nobukti)->first();
                if ($getPenerimaanDeposito != null) {
                    (new PenerimaanHeader())->processDestroy($getPenerimaanDeposito->id, $postingDari);
                }
            }

            $fetchBBM = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))->where('gajisupir_nobukti', $value->gajisupir_nobukti)->first();

            if ($fetchBBM != null) {

                $penerimaanBBM = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->first();

                $penerimaanTruckingHeaderBBM = [
                    'ebs' => true,
                    'postingdari' => 'PROSES GAJI SUPIR',
                    'bank_id' => 0,
                    'penerimaan_nobukti' => '',
                ];
                $newPenerimaanTruckingBBM = new PenerimaanTruckingHeader();
                $newPenerimaanTruckingBBM = $newPenerimaanTruckingBBM->findAll($penerimaanBBM->id);
                (new PenerimaanTruckingHeader())->processUpdate($newPenerimaanTruckingBBM, $penerimaanTruckingHeaderBBM);

                $getPenerimaanBBM = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))->where('nobukti', $penerimaanBBM->penerimaan_nobukti)->first();
                if ($getPenerimaanBBM != null) {
                    (new PenerimaanHeader())->processDestroy($getPenerimaanBBM->id, $postingDari);
                }
                $getJurnalHeader = JurnalUmumHeader::lockForUpdate()->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->first();
                (new JurnalUmumHeader())->processDestroy($getJurnalHeader->id, 'PROSES GAJI SUPIR');
            }
        }

        if ($nobuktiUangjalan != null) {

            $nobuktiPenerimaanKasgantung = PengembalianKasGantungHeader::from(DB::raw("pengembaliankasgantungheader with (readuncommitted)"))->where('penerimaan_nobukti', $nobuktiUangjalan)->first();
            (new PengembalianKasGantungHeader())->processDestroy($nobuktiPenerimaanKasgantung->id, $postingDari);
        }
        return $prosesGajiSupirHeader;
    }
}
