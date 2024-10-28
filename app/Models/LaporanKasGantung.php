<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanKasGantung extends MyModel
{
    use HasFactory;

    protected $table = 'pengembaliankasgantungheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function getExport($periode)
    {
        //   return $periode;
        $pengembaliankasgantungheader = '##pengembaliankasgantungheader' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($pengembaliankasgantungheader, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->bigInteger('pelanggan_id')->nullable();
            $table->longText('keterangan')->nullable();
            $table->bigInteger('bank_id')->nullable();
            $table->date('tgldari')->nullable();
            $table->date('tglsampai')->nullable();
            $table->string('penerimaan_nobukti', 50)->nullable();
            $table->string('coakasmasuk', 50)->nullable();
            $table->string('postingdari', 50)->nullable();
            $table->date('tglkasmasuk')->nullable();
            $table->bigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $dataheader = DB::table('pengembaliankasgantungheader')->from(DB::raw("pengembaliankasgantungheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                'A.id',
                'A.nobukti',
                'A.tglbukti',
                'A.pelanggan_id',
                'A.keterangan',
                'A.bank_id',
                'A.tgldari',
                'A.tglsampai',
                'A.penerimaan_nobukti',
                'A.coakasmasuk',
                'A.postingdari',
                'A.tglkasmasuk',
                'A.statusformat',
                'A.statuscetak',
                'A.userbukacetak',
                'A.tglbukacetak',
                'A.jumlahcetak',
                'A.modifiedby',
                'A.created_at',
                'A.updated_at'
            ])
            ->where('A.tglbukti', '<', $periode);


        DB::table($pengembaliankasgantungheader)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'pelanggan_id',
            'keterangan',
            'bank_id',
            'tgldari',
            'tglsampai',
            'penerimaan_nobukti',
            'coakasmasuk',
            'postingdari',
            'tglkasmasuk',
            'statusformat',
            'statuscetak',
            'userbukacetak',
            'tglbukacetak',
            'jumlahcetak',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $dataheader);

        //   dd($dataheader->get());

        //NOTE - pengembalian kas gantung detail
        $pengembaliankasgantungdetail = '##pengembaliankasgantungdetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($pengembaliankasgantungdetail, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->bigInteger('pengembaliankasgantung_id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->float('nominal')->nullable();
            $table->string('coa')->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->string('kasgantung_nobukti', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $datadetail = DB::table('pengembaliankasgantungdetail')->from(DB::raw("pengembaliankasgantungdetail AS A WITH (READUNCOMMITTED)"))
            ->select([
                'A.id',
                'A.pengembaliankasgantung_id',
                'A.nobukti',
                'A.nominal',
                'A.coa',
                'A.keterangan',
                'A.modifiedby',
                'A.kasgantung_nobukti',
                'A.created_at',
                'A.updated_at'
            ])
            ->join(DB::raw("pengembaliankasgantungheader as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti');


        DB::table($pengembaliankasgantungdetail)->insertUsing([
            'id',
            'pengembaliankasgantung_id',
            'nobukti',
            'nominal',
            'coa',
            'keterangan',
            'modifiedby',
            'kasgantung_nobukti',
            'created_at',
            'updated_at',
        ], $datadetail);
        // dd($datadetail->get());

        //NOTE - kas gantung header
        $kasgantungheader = '##kasgantungheader' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($kasgantungheader, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();
            $table->bigInteger('penerima_id')->nullable();
            $table->bigInteger('bank_id')->nullable();
            $table->string('pengeluaran_nobukti', 50)->nullable();
            $table->string('coakaskeluar', 50)->nullable();
            $table->string('postingdari', 50)->nullable();
            $table->date('tglkaskeluar')->nullable();
            $table->bigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $kasheader = DB::table('kasgantungheader')->from(DB::raw("kasgantungheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                'A.id',
                'A.nobukti',
                'A.tglbukti',
                'A.keterangan',
                'A.penerima_id',
                'A.bank_id',
                'A.pengeluaran_nobukti',
                'A.coakaskeluar',
                'A.postingdari',
                'A.tglkaskeluar',
                'A.statusformat',
                'A.statuscetak',
                'A.userbukacetak',
                'A.tglbukacetak',
                'A.jumlahcetak',
                'A.modifiedby',
                'A.created_at',
                'A.updated_at'
            ])
            ->where('A.tglbukti', '<=', $periode);
        //  dd($kasheader->get());

        DB::table($kasgantungheader)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'keterangan',
            'penerima_id',
            'bank_id',
            'pengeluaran_nobukti',
            'coakaskeluar',
            'postingdari',
            'tglkaskeluar',
            'statusformat',
            'statuscetak',
            'userbukacetak',
            'tglbukacetak',
            'jumlahcetak',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $kasheader);
        // dd($kasheader->get());


        //NOTE - kasgantungdetail
        $kasgantungdetail = '##kasgantungdetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($kasgantungdetail, function ($table) {
            $table->string('nobukti', 50);
            $table->longText('keterangan');
            $table->float('nominal');
        });

        $kasdetail = DB::table('kasgantungdetail')->from(DB::raw("kasgantungdetail AS A WITH (READUNCOMMITTED)"))
            ->select([
                'A.nobukti',
                DB::raw('MAX(A.keterangan)'),
                DB::raw('SUM(A.nominal) as nominal')
            ])
            ->join(DB::raw("kasgantungheader as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->groupBy('A.nobukti');

        DB::table($kasgantungdetail)->insertUsing([
            'nobukti',
            'keterangan',
            'nominal',
        ], $kasdetail);
        //  dd('$kasdetail->get()');


        //NOTE - pengembaliankasgantungheader2
        $pengembaliankasgantungheader2 = '##pengembaliankasgantungheader2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($pengembaliankasgantungheader2, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->bigInteger('pelanggan_id')->nullable();
            $table->longText('keterangan')->nullable();
            $table->bigInteger('bank_id')->nullable();
            $table->date('tgldari')->nullable();
            $table->date('tglsampai')->nullable();
            $table->string('penerimaan_nobukti', 50)->nullable();
            $table->string('coakasmasuk', 50)->nullable();
            $table->string('postingdari', 50)->nullable();
            $table->date('tglkasmasuk')->nullable();
            $table->bigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $dataheader2 = DB::table('pengembaliankasgantungheader')->from(DB::raw("pengembaliankasgantungheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                'A.id',
                'A.nobukti',
                'A.tglbukti',
                'A.pelanggan_id',
                'A.keterangan',
                'A.bank_id',
                'A.tgldari',
                'A.tglsampai',
                'A.penerimaan_nobukti',
                'A.coakasmasuk',
                'A.postingdari',
                'A.tglkasmasuk',
                'A.statusformat',
                'A.statuscetak',
                'A.userbukacetak',
                'A.tglbukacetak',
                'A.jumlahcetak',
                'A.modifiedby',
                'A.created_at',
                'A.updated_at',
            ])
            ->where('A.tglbukti', '<', $periode);


        DB::table($pengembaliankasgantungheader2)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'pelanggan_id',
            'keterangan',
            'bank_id',
            'tgldari',
            'tglsampai',
            'penerimaan_nobukti',
            'coakasmasuk',
            'postingdari',
            'tglkasmasuk',
            'statusformat',
            'statuscetak',
            'userbukacetak',
            'tglbukacetak',
            'jumlahcetak',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $dataheader2);




        //NOTE - pengembaliankasgantungdetail2
        $pengembaliankasgantungdetail2 = '##pengembaliankasgantungdetail2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($pengembaliankasgantungdetail2, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->string('kasgantung_nobukti', 50)->nullable();
            $table->float('nominal')->nullable();
            $table->longText('keterangan')->nullable();
        });

        $kasdetail2 = DB::table('pengembaliankasgantungdetail')->from(DB::raw("pengembaliankasgantungdetail AS A WITH (READUNCOMMITTED)"))
            ->select([
                'A.nobukti',
                'A.kasgantung_nobukti',
                DB::raw('SUM(A.nominal) as nominal'),
                DB::raw('MAX(A.keterangan)'),
            ])
            ->join(DB::raw($pengembaliankasgantungheader2 . " as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->groupBy('A.nobukti', 'A.kasgantung_nobukti');


        DB::table($pengembaliankasgantungdetail2)->insertUsing([
            'nobukti',
            'kasgantung_nobukti',
            'nominal',
            'keterangan',
        ], $kasdetail2);
        // dd($kasdetail2->get());



        //NOTE - TempLaporan
        $TempLaporan = '##TempLaporan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempLaporan, function ($table) {
            $table->bigIncrements('id');
            $table->dateTime('tglbuktikasgantung');
            $table->dateTime('tglbukti');
            $table->string('nobukti', 50);
            $table->longText('keterangan');
            $table->integer('flag');
            $table->float('debet');
            $table->float('kredit');
            $table->float('saldo')->nullable();
        });

        $temp_kasgantungheader = DB::table('kasgantungheader')->from(DB::raw($kasgantungheader . " AS a"))
            ->select([
                'A.tglbukti',
                'A.tglbukti',
                'C.nobukti',
                'C.keterangan',
                DB::raw('0 as flag'),
                'C.nominal as debet',
                DB::raw('0 as kredit'),
            ])
            ->leftJoin(DB::raw($pengembaliankasgantungdetail . " AS b"), function ($join) {
                $join->on('a.nobukti', '=', 'b.kasgantung_nobukti')
                    ->whereNull('b.nobukti');
            })
            ->join(DB::raw($kasgantungdetail . " AS c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
            ->orderBy('a.tglbukti', 'asc')
            ->orderBy('c.nobukti', 'desc');

        DB::table($TempLaporan)->insertUsing([
            'tglbuktikasgantung',
            'tglbukti',
            'nobukti',
            'keterangan',
            'flag',
            'debet',
            'kredit'
        ], $temp_kasgantungheader);
        // dd($temp_kasgantungheader->get());






        //NOTE - TempLaporan
        $temp_pengembaliankasgantungheader2 = DB::table('pengembaliankasgantungheader2')->from(DB::raw($pengembaliankasgantungheader2 . " AS a"))
            ->select([
                'B.tglbukti',
                'A.tglbukti',
                'C.kasgantung_nobukti as nobukti',
                'C.keterangan',
                DB::raw('1 as flag'),
                DB::raw('0 as debet'),
                'c.nominal as kredit',
            ])
            ->join(DB::raw($pengembaliankasgantungdetail2 . " c with (readuncommitted)"), 'a.nobukti', '=', 'c.nobukti')
            ->join('kasgantungheader as b', 'c.kasgantung_nobukti', 'b.nobukti')
            ->orderBy('a.tglbukti', 'asc')
            ->orderBy('c.kasgantung_nobukti', 'desc');

        DB::table($TempLaporan)->insertUsing([
            'tglbuktikasgantung',
            'tglbukti',
            'nobukti',
            'keterangan',
            'flag',
            'debet',
            'kredit'
        ], $temp_pengembaliankasgantungheader2);
        // dd($temp_pengembaliankasgantungheader2->get());

        //NOTE - TempLaporan2
        $TempLaporan2 = '##TempLaporan2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempLaporan2, function ($table) {
            $table->bigIncrements('id');
            $table->dateTime('tglbukti');
            $table->string('nobukti', 50);
            $table->longText('keterangan');
            $table->float('debet');
            $table->float('kredit');
            $table->float('saldo')->nullable();
        });

        $select_TempLaporan = DB::table('TempLaporan')->from(DB::raw($TempLaporan . " AS a"))
            ->select([
                'A.tglbukti',
                'A.nobukti',
                'A.keterangan',
                'A.debet',
                'A.kredit',
                DB::raw('0 as saldo'),

            ])
            ->orderBy('a.tglbuktikasgantung', 'asc')
            ->orderBy('a.nobukti', 'asc')
            ->orderBy('a.flag', 'desc');

        DB::table($TempLaporan2)->insertUsing([
            'tglbukti',
            'nobukti',
            'keterangan',
            'debet',
            'kredit',
            'saldo'
        ], $select_TempLaporan);

        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';
        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();



        $select_TempLaporan2 = DB::table('TempLaporan2')->from(DB::raw($TempLaporan2 . " AS a"))
            ->select([
                'A.tglbukti as tanggal',
                'A.nobukti',
                'A.keterangan',
                'A.debet',
                'A.kredit',
                DB::raw('sum((isnull(A.saldo, 0) + A.debet) - A.kredit) over (order by id asc) as Saldo'),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
                DB::raw("'Laporan Kas Gantung' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
            ])
            ->orderBy('a.id', 'asc');
        // dd($select_TempLaporan2->get());

        $data = $select_TempLaporan2->get();
        return $data;
    }

    public function getReport($periode, $prosesneraca, $bank_id)
    {
        // if ($prosesneraca == 1) {
        //     $periode = date("Y-m-d", strtotime("+1 day", strtotime($periode)));
        // }

        //   return $periode;
        $prosesneraca = $prosesneraca ?? 0;
        $pengembaliankasgantungheader = '##pengembaliankasgantungheader' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $temprekapsaldopengembaliankasgantung = '##temprekapsaldopengembaliankasgantung' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekapsaldopengembaliankasgantung, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $querypengembalian = DB::table('pengembaliankasgantungheader')->from(DB::raw("pengembaliankasgantungheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                'b.kasgantung_nobukti as nobukti',
                db::raw("sum(b.nominal) as nominal")
            ])
            ->join(db::raw("pengembaliankasgantungdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->where('A.tglbukti', '<', $periode)
            ->where('A.bank_id', '=', $bank_id)
            ->groupBY('b.kasgantung_nobukti');

        DB::table($temprekapsaldopengembaliankasgantung)->insertUsing([
            'nobukti',
            'nominal',
        ], $querypengembalian);


        $temprekapsaldokasgantung = '##temprekapsaldokasgantung' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekapsaldokasgantung, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->longtext('keterangan')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        // dd('test');
        
        $querysaldokasgantung = DB::table('kasgantungheader')->from(DB::raw("kasgantungheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                'A.nobukti',
                db::raw("max(b.keterangan) as keterangan"),
                db::raw("sum(b.nominal) as nominal")
            ])
            ->join(db::raw("kasgantungdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->where('A.tglbukti', '<', $periode)
            ->where('A.bank_id', '=', $bank_id)
            ->groupBY('a.nobukti');

        // dd($querysaldokasgantung->tosql());

        DB::table($temprekapsaldokasgantung)->insertUsing([
            'nobukti',
            'keterangan',
            'nominal',
        ], $querysaldokasgantung);

        $tempsisasaldokasgantung = '##tempsisasaldokasgantung' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsisasaldokasgantung, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->longtext('keterangan')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $querysisa = db::table($temprekapsaldokasgantung)->from(db::raw($temprekapsaldokasgantung . " a"))
            ->select(
                'a.nobukti',
                'a.keterangan',
                db::raw("(isnull(a.nominal,0)-isnull(b.nominal,0)) as nominal")
            )
            ->leftjoin(db::raw($temprekapsaldopengembaliankasgantung . " b"), 'a.nobukti', 'b.nobukti')
            ->whereraw("(isnull(a.nominal,0)-isnull(b.nominal,0))>0");

        DB::table($tempsisasaldokasgantung)->insertUsing([
            'nobukti',
            'keterangan',
            'nominal',
        ], $querysisa);


        Schema::create($pengembaliankasgantungheader, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->bigInteger('pelanggan_id')->nullable();
            $table->longText('keterangan')->nullable();
            $table->bigInteger('bank_id')->nullable();
            $table->date('tgldari')->nullable();
            $table->date('tglsampai')->nullable();
            $table->string('penerimaan_nobukti', 50)->nullable();
            $table->string('coakasmasuk', 50)->nullable();
            $table->string('postingdari', 50)->nullable();
            $table->date('tglkasmasuk')->nullable();
            $table->bigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $dataheader = DB::table('pengembaliankasgantungheader')->from(DB::raw("pengembaliankasgantungheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                'A.id',
                'A.nobukti',
                'A.tglbukti',
                'A.pelanggan_id',
                'A.keterangan',
                'A.bank_id',
                'A.tgldari',
                'A.tglsampai',
                'A.penerimaan_nobukti',
                'A.coakasmasuk',
                'A.postingdari',
                'A.tglkasmasuk',
                'A.statusformat',
                'A.statuscetak',
                'A.userbukacetak',
                'A.tglbukacetak',
                'A.jumlahcetak',
                'A.modifiedby',
                'A.created_at',
                'A.updated_at'
            ])
            ->where('A.bank_id', '=', $bank_id)
            ->where('A.tglbukti', '=', $periode);


        DB::table($pengembaliankasgantungheader)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'pelanggan_id',
            'keterangan',
            'bank_id',
            'tgldari',
            'tglsampai',
            'penerimaan_nobukti',
            'coakasmasuk',
            'postingdari',
            'tglkasmasuk',
            'statusformat',
            'statuscetak',
            'userbukacetak',
            'tglbukacetak',
            'jumlahcetak',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $dataheader);

        //    dd($dataheader->get());

        //NOTE - pengembalian kas gantung detail
        $pengembaliankasgantungdetail = '##pengembaliankasgantungdetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($pengembaliankasgantungdetail, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->bigInteger('pengembaliankasgantung_id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->float('nominal')->nullable();
            $table->string('coa')->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->string('kasgantung_nobukti', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $datadetail = DB::table('pengembaliankasgantungdetail')->from(DB::raw("pengembaliankasgantungdetail AS A WITH (READUNCOMMITTED)"))
            ->select([
                'A.id',
                'A.pengembaliankasgantung_id',
                'A.nobukti',
                'A.nominal',
                'A.coa',
                'A.keterangan',
                'A.modifiedby',
                'A.kasgantung_nobukti',
                'A.created_at',
                'A.updated_at'
            ])
            ->join(DB::raw($pengembaliankasgantungheader . " as c "), 'a.nobukti', 'c.nobukti');


        DB::table($pengembaliankasgantungdetail)->insertUsing([
            'id',
            'pengembaliankasgantung_id',
            'nobukti',
            'nominal',
            'coa',
            'keterangan',
            'modifiedby',
            'kasgantung_nobukti',
            'created_at',
            'updated_at',
        ], $datadetail);
        // dd($datadetail->get());

        //NOTE - kas gantung header
        $kasgantungheader = '##kasgantungheader' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($kasgantungheader, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();
            $table->bigInteger('penerima_id')->nullable();
            $table->bigInteger('bank_id')->nullable();
            $table->string('pengeluaran_nobukti', 50)->nullable();
            $table->string('coakaskeluar', 50)->nullable();
            $table->string('postingdari', 50)->nullable();
            $table->date('tglkaskeluar')->nullable();
            $table->bigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        // 
        $kasheader = DB::table('kasgantungheader')->from(DB::raw("kasgantungheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                'A.id',
                'A.nobukti',
                'A.tglbukti',
                'A.keterangan',
                'A.penerima_id',
                'A.bank_id',
                'A.pengeluaran_nobukti',
                'A.coakaskeluar',
                'A.postingdari',
                'A.tglkaskeluar',
                'A.statusformat',
                'A.statuscetak',
                'A.userbukacetak',
                'A.tglbukacetak',
                'A.jumlahcetak',
                'A.modifiedby',
                'A.created_at',
                'A.updated_at'
            ])
            ->join(db::raw($tempsisasaldokasgantung . " b"), 'a.nobukti', 'b.nobukti');
        //  dd($kasheader->get());

        DB::table($kasgantungheader)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'keterangan',
            'penerima_id',
            'bank_id',
            'pengeluaran_nobukti',
            'coakaskeluar',
            'postingdari',
            'tglkaskeluar',
            'statusformat',
            'statuscetak',
            'userbukacetak',
            'tglbukacetak',
            'jumlahcetak',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $kasheader);

        // 


        $kasheader = DB::table('kasgantungheader')->from(DB::raw("kasgantungheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                'A.id',
                'A.nobukti',
                'A.tglbukti',
                'A.keterangan',
                'A.penerima_id',
                'A.bank_id',
                'A.pengeluaran_nobukti',
                'A.coakaskeluar',
                'A.postingdari',
                'A.tglkaskeluar',
                'A.statusformat',
                'A.statuscetak',
                'A.userbukacetak',
                'A.tglbukacetak',
                'A.jumlahcetak',
                'A.modifiedby',
                'A.created_at',
                'A.updated_at'
            ])
            ->where('A.bank_id', '=', $bank_id)
            ->where('A.tglbukti', '=', $periode);
        //  dd($kasheader->get());

        DB::table($kasgantungheader)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'keterangan',
            'penerima_id',
            'bank_id',
            'pengeluaran_nobukti',
            'coakaskeluar',
            'postingdari',
            'tglkaskeluar',
            'statusformat',
            'statuscetak',
            'userbukacetak',
            'tglbukacetak',
            'jumlahcetak',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $kasheader);
        // dd($kasheader->get());


        //NOTE - kasgantungdetail
        $kasgantungdetail = '##kasgantungdetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($kasgantungdetail, function ($table) {
            $table->string('nobukti', 50);
            $table->longText('keterangan');
            $table->float('nominal');
        });

        // 
        $kasdetail = DB::table($tempsisasaldokasgantung)->from(DB::raw($tempsisasaldokasgantung . " AS A"))
            ->select([
                'A.nobukti',
                DB::raw('A.keterangan'),
                DB::raw('(A.nominal) as nominal')
            ]);

        DB::table($kasgantungdetail)->insertUsing([
            'nobukti',
            'keterangan',
            'nominal',
        ], $kasdetail);


        // 

        $kasdetail = DB::table('kasgantungdetail')->from(DB::raw("kasgantungdetail AS A WITH (READUNCOMMITTED)"))
            ->select([
                'A.nobukti',
                DB::raw('MAX(A.keterangan)'),
                DB::raw('SUM(A.nominal) as nominal')
            ])
            ->join(DB::raw("kasgantungheader as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw($kasgantungheader . " as c "), 'a.nobukti', 'c.nobukti')
            ->where('b.tglbukti', '=', $periode)
            ->groupBy('A.nobukti');

        DB::table($kasgantungdetail)->insertUsing([
            'nobukti',
            'keterangan',
            'nominal',
        ], $kasdetail);
        //  dd(db::table($kasgantungdetail)->get());


        //NOTE - pengembaliankasgantungheader2
        $pengembaliankasgantungheader2 = '##pengembaliankasgantungheader2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($pengembaliankasgantungheader2, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->bigInteger('pelanggan_id')->nullable();
            $table->longText('keterangan')->nullable();
            $table->bigInteger('bank_id')->nullable();
            $table->date('tgldari')->nullable();
            $table->date('tglsampai')->nullable();
            $table->string('penerimaan_nobukti', 50)->nullable();
            $table->string('coakasmasuk', 50)->nullable();
            $table->string('postingdari', 50)->nullable();
            $table->date('tglkasmasuk')->nullable();
            $table->bigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });



        $dataheader2 = DB::table('pengembaliankasgantungheader')->from(DB::raw("pengembaliankasgantungheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                'A.id',
                'A.nobukti',
                'A.tglbukti',
                'A.pelanggan_id',
                'A.keterangan',
                'A.bank_id',
                'A.tgldari',
                'A.tglsampai',
                'A.penerimaan_nobukti',
                'A.coakasmasuk',
                'A.postingdari',
                'A.tglkasmasuk',
                'A.statusformat',
                'A.statuscetak',
                'A.userbukacetak',
                'A.tglbukacetak',
                'A.jumlahcetak',
                'A.modifiedby',
                'A.created_at',
                'A.updated_at',
            ])
            ->where('A.bank_id', '=', $bank_id)
            ->where('A.tglbukti', '=', $periode);


        // dd($dataheader2->get());

        DB::table($pengembaliankasgantungheader2)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'pelanggan_id',
            'keterangan',
            'bank_id',
            'tgldari',
            'tglsampai',
            'penerimaan_nobukti',
            'coakasmasuk',
            'postingdari',
            'tglkasmasuk',
            'statusformat',
            'statuscetak',
            'userbukacetak',
            'tglbukacetak',
            'jumlahcetak',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $dataheader2);




        //NOTE - pengembaliankasgantungdetail2
        $pengembaliankasgantungdetail2 = '##pengembaliankasgantungdetail2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($pengembaliankasgantungdetail2, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->string('kasgantung_nobukti', 50)->nullable();
            $table->float('nominal')->nullable();
            $table->longText('keterangan')->nullable();
        });

        $kasdetail2 = DB::table('pengembaliankasgantungdetail')->from(DB::raw("pengembaliankasgantungdetail AS A WITH (READUNCOMMITTED)"))
            ->select([
                'A.nobukti',
                'A.kasgantung_nobukti',
                DB::raw('SUM(A.nominal) as nominal'),
                DB::raw('MAX(A.keterangan)'),
            ])
            ->join(DB::raw($pengembaliankasgantungheader2 . " as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->groupBy('A.nobukti', 'A.kasgantung_nobukti');

        // dd($kasdetail2->get());

        DB::table($pengembaliankasgantungdetail2)->insertUsing([
            'nobukti',
            'kasgantung_nobukti',
            'nominal',
            'keterangan',
        ], $kasdetail2);
        // dd($kasdetail2->get());

        //NOTE - TempLaporan
        $TempLaporan = '##TempLaporan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempLaporan, function ($table) {
            $table->bigIncrements('id');
            $table->dateTime('tglbuktikasgantung');
            $table->dateTime('tglbukti');
            $table->string('nobukti', 50);
            $table->longText('keterangan');
            $table->integer('flag');
            $table->float('debet');
            $table->float('kredit');
            $table->float('saldo')->nullable();
            $table->longText('penerimaan_nobukti');
            $table->unsignedBigInteger('nilaikosongdebet')->nullable();
            $table->unsignedBigInteger('nilaikosongkredit')->nullable();
        });



        $temp_kasgantungheader = DB::table('kasgantungheader')->from(DB::raw($kasgantungheader . " AS a"))
            ->select([
                'A.tglbukti',
                'A.tglbukti',
                'C.nobukti',
                'C.keterangan',
                DB::raw('0 as flag'),
                'C.nominal as debet',
                DB::raw('0 as kredit'),
                db::raw("c.nobukti as penerimaan_nobukti"),
                DB::raw("0  as nilaikosongdebet"),
                DB::raw("1  as nilaikosongkredit"),
            ])
            ->leftJoin(DB::raw($pengembaliankasgantungdetail . " AS b"), function ($join) {
                $join->on('a.nobukti', '=', 'b.kasgantung_nobukti')
                    ->whereNull('b.nobukti');
            })
            ->join(DB::raw($kasgantungdetail . " AS c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
            ->orderBy('a.tglbukti', 'asc')
            ->orderBy('c.nobukti', 'desc');

        DB::table($TempLaporan)->insertUsing([
            'tglbuktikasgantung',
            'tglbukti',
            'nobukti',
            'keterangan',
            'flag',
            'debet',
            'kredit',
            'penerimaan_nobukti',
            'nilaikosongdebet',
            'nilaikosongkredit'
        ], $temp_kasgantungheader);
        // dd($temp_kasgantungheader->get());

        // dd(db::table($pengembaliankasgantungdetail2)->get());






        //NOTE - TempLaporan
        $temp_pengembaliankasgantungheader2 = DB::table('pengembaliankasgantungheader2')->from(DB::raw($pengembaliankasgantungheader2 . " AS a"))
            ->select([
                'B.tglbukti',
                'A.tglbukti',
                'C.kasgantung_nobukti as nobukti',
                'C.keterangan',
                DB::raw('1 as flag'),
                DB::raw('0 as debet'),
                'c.nominal as kredit',
                'a.penerimaan_nobukti',
                DB::raw("1  as nilaikosongdebet"),
                DB::raw("0  as nilaikosongkredit"),
            ])
            ->join(DB::raw($pengembaliankasgantungdetail2 . " c with (readuncommitted)"), 'a.nobukti', '=', 'c.nobukti')
            ->join(DB::raw($kasgantungheader . " b with (readuncommitted)"), 'c.kasgantung_nobukti', '=', 'b.nobukti')
            // ->join('kasgantungheader as b', 'c.kasgantung_nobukti', 'b.nobukti')
            ->orderBy('a.tglbukti', 'asc')
            ->orderBy('c.kasgantung_nobukti', 'desc');

        //    dd($temp_pengembaliankasgantungheader2->get());

        DB::table($TempLaporan)->insertUsing([
            'tglbuktikasgantung',
            'tglbukti',
            'nobukti',
            'keterangan',
            'flag',
            'debet',
            'kredit',
            'penerimaan_nobukti',
            'nilaikosongdebet',
            'nilaikosongkredit'
        ], $temp_pengembaliankasgantungheader2);
        // dd($temp_pengembaliankasgantungheader2->get());

        //NOTE - TempLaporan2
        $TempLaporan2 = '##TempLaporan2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempLaporan2, function ($table) {
            $table->bigIncrements('id');
            $table->date('tglbukti');
            $table->string('nobukti', 50);
            $table->longText('keterangan');
            $table->float('debet');
            $table->float('kredit');
            $table->float('saldo')->nullable();
            $table->unsignedBigInteger('nilaikosongdebet')->nullable();
            $table->unsignedBigInteger('nilaikosongkredit')->nullable();
        });

        $select_TempLaporan = DB::table('TempLaporan')->from(DB::raw($TempLaporan . " AS a"))
            ->select([
                'A.tglbukti',
                db::raw("a.penerimaan_nobukti as nobukti"),
                'A.keterangan',
                'A.debet',
                'A.kredit',
                DB::raw('0 as saldo'),
                'a.nilaikosongdebet',
                'a.nilaikosongkredit'

            ])
            ->orderBy('a.tglbuktikasgantung', 'asc')
            ->orderBy('a.nobukti', 'asc')
            ->orderBy('a.flag', 'asc');

        // dd($select_TempLaporan ->get());

        DB::table($TempLaporan2)->insertUsing([
            'tglbukti',
            'nobukti',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'nilaikosongdebet',
            'nilaikosongkredit'
        ], $select_TempLaporan);

        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();
        $select_TempLaporan2 = DB::table('TempLaporan2')->from(DB::raw($TempLaporan2 . " AS a"))
            ->select([
                'A.tglbukti as tanggal',
                'A.nobukti',
                'A.keterangan',
                'A.debet',
                'A.kredit',
                DB::raw('sum((isnull(A.saldo, 0) + A.debet) - A.kredit) over (order by id asc) as Saldo'),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
                DB::raw("'Laporan Kas Gantung' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),                
                'a.nilaikosongdebet',
                'a.nilaikosongkredit'

            ])
            ->orderBy('a.id', 'asc');
        // dd($select_TempLaporan2->get());

        if ($prosesneraca == 1) {
            $data = $select_TempLaporan2;
            // dd($data->get());
        } else {
            $data = $select_TempLaporan2->get();
        }
        // dd($data);

        return $data;
    }
}
