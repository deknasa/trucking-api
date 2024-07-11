<?php

namespace App\Models;


use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanArusDanaPusat extends MyModel
{
    use HasFactory;

    public function getMingguan()
    {
        $this->setRequestParameters();
        $ptahun1 = date('Y', strtotime('-1 years'));
        $ptahun2 = date('Y');

        $tempBulan = '##tempBulan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempBulan, function ($table) {
            $table->id();
            $table->longText('FKode')->nullable();
            $table->string('FTahun', 1000)->nullable();
            $table->unsignedBigInteger('FMingguke')->nullable();
            $table->unsignedBigInteger('fBulanKe')->nullable();
            $table->date('Ftgldr')->nullable();
            $table->date('Ftglsd')->nullable();
        });

        while ($ptahun1 <= $ptahun2) {
            $ptahun = $ptahun1;
            $pawal = 1;
            while ($pawal <= 12) {
                $ptgl1 = date('Y-m-d', strtotime($ptahun . '-' . $pawal . '-01'));
                $ptgl3 = date('Y-m-d', strtotime($ptahun . '-' . $pawal . '-01' . ' +32 days'));
                $tahun = date('Y', strtotime($ptgl3));
                $bulan = date('m', strtotime($ptgl3));
                $ptgl2 = date('Y-m-d', strtotime($tahun . '-' . $bulan . '-01' . ' -1 days'));
                $pminggu = 1;
                $hit = 0;
                while ($ptgl1 <= $ptgl2) {
                    if ($hit == 0) {
                        $ptgldr = $ptgl1;
                    }
                    $datepart = DB::select("select datepart(dw," . $ptgl1 . ") as dpart");
                    $dpart = json_decode(json_encode($datepart), true)[0]['dpart'];
                    if ($dpart == 7) {
                        $ptglsd = $ptgl1;

                        DB::table($tempBulan)->insert(
                            [
                                'FKode' => 'Minggu Ke ' . $pminggu . ' Bulan ' . $pawal . ' Tahun ' . $ptahun,
                                'FTahun' => $ptahun,
                                'FMingguke' => $pminggu,
                                'fBulanKe' => $pawal,
                                'Ftgldr' => $ptgldr,
                                'Ftglsd' => $ptglsd,
                            ]
                        );
                        $pminggu = $pminggu + 1;
                        $hit = -1;
                    }
                    if ($ptgl1 == $ptgl2) {
                        $ptglsd = $ptgl1;
                        DB::table($tempBulan)->insert(
                            [
                                'FKode' => 'Minggu Ke ' . $pminggu . ' Bulan ' . $pawal . ' Tahun ' . $ptahun,
                                'FTahun' => $ptahun,
                                'FMingguke' => $pminggu,
                                'fBulanKe' => $pawal,
                                'Ftgldr' => $ptgldr,
                                'Ftglsd' => $ptglsd,
                            ]
                        );
                        $pminggu = $pminggu + 1;
                        $hit = -1;
                    }
                    $hit = $hit + 1;
                    $ptgl1 = date("Y-m-d", strtotime("+1 day", strtotime($ptgl1)));
                }
                $pawal = $pawal + 1;
            }
            $ptahun1 = $ptahun1 + 1;
        }

        $query = db::table($tempBulan)->from(db::raw($tempBulan . " a"))
            ->select(
                'a.fKode',
                'a.fTahun',
                'a.fMingguKe',
                'a.fBulanKe',
                'a.fTglDr',
                'a.fTglSd',
            );
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        return $query->get();

        // 


    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                          
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw( "a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                          
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

    public function sort($query)
    {
            return $query->orderBy( 'a.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
    public function getMingguanoLD()
    {
        $pTahun1 = date('Y', strtotime('-1 years'));
        $pTahun2 = date('Y');
        // dd($pTahun1,$pTahun2);
        $tempBulan = '##tempBulan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempBulan, function ($table) {
            $table->longText('FKode')->nullable();
            $table->string('FTahun', 1000)->nullable();
            $table->unsignedBigInteger('FMingguke')->nullable();
            $table->unsignedBigInteger('FBlnke')->nullable();
            $table->date('Ftgldr')->nullable();
            $table->date('Ftglsd')->nullable();
        });
        $dataToInsert = [];
        while ($pTahun1 <= $pTahun2) {
            $pTahun = $pTahun1;
            $pAwal = 1;
            while ($pAwal <= 12) {
                $pTgl1 = date('Y-m-d', strtotime($pTahun . '-' . $pAwal . '-01'));
                $pTgl3 = date('Y-m-d', strtotime($pTahun . '-' . $pAwal . '-01' . ' +32 days'));
                $tahun = date('Y', strtotime($pTgl3));
                $bulan = date('m', strtotime($pTgl3));
                $pTgl2 = date('Y-m-d', strtotime($tahun . '-' . $bulan . '-01' . ' -1 days'));

                $pMinggu = 1;
                $hit = 0;
                while ($pTgl1 <= $pTgl2) {
                    if ($hit == 0) {
                        $pTglDr = $pTgl1;
                        // var_dump('hit 0');
                    }
                    if (date('N', strtotime($pTgl1)) == 7) {
                        $pTglSd = $pTgl1;
                        $values = 'Minggu Ke ' . trim($pMinggu) . ' Bulan ' . trim($pAwal) . ' Tahun ' . trim($pTahun);
                        // DB::table($tempBulan)->insert([
                        //     'FKode' => $values,
                        //     'FTahun' => $pTahun,
                        //     'FMingguke' => $pMinggu,
                        //     'FBlnke' => $pAwal,
                        //     'Ftgldr' => $pTglDr,
                        //     'Ftglsd' => $pTglSd
                        // ]);
                        $dataToInsert[] = [
                            'FKode' => $values,
                            'FTahun' => $pTahun,
                            'FMingguke' => $pMinggu,
                            'FBlnke' => $pAwal,
                            'Ftgldr' => $pTglDr,
                            'Ftglsd' => $pTglSd
                        ];
                        $pMinggu = $pMinggu + 1;
                        $hit -= 1;
                        // var_dump('n 7');
                    }
                    if ($pTgl1 == $pTgl2) {
                        $pTglSd = $pTgl1;
                        $values = 'Minggu Ke ' . trim($pMinggu) . ' Bulan ' . trim($pAwal) . ' Tahun ' . trim($pTahun);
                        // Insert into the temporary table using Eloquent
                        // DB::table($tempBulan)->insert([
                        //     'FKode' => $values,
                        //     'FTahun' => $pTahun,
                        //     'FMingguke' => $pMinggu,
                        //     'FBlnke' => $pAwal,
                        //     'Ftgldr' => $pTglDr,
                        //     'Ftglsd' => $pTglSd
                        // ]);
                        $dataToInsert[] = [
                            'FKode' => $values,
                            'FTahun' => $pTahun,
                            'FMingguke' => $pMinggu,
                            'FBlnke' => $pAwal,
                            'Ftgldr' => $pTglDr,
                            'Ftglsd' => $pTglSd
                        ];
                        $pMinggu = $pMinggu + 1;
                        $hit -= 1;

                        // var_dump('ptgl2');
                    }

                    $hit = $hit + 1;
                    $pTgl1 = date('Y-m-d', strtotime($pTgl1 . ' +1 days'));
                }

                $pAwal += 1;
            }
            $pTahun1 += 1;
        }
        // dd($data);
        // DB::table($tempBulan)->insert($data);
        DB::table($tempBulan)->insert($dataToInsert);

        dd(DB::table($tempBulan)->get());
    }

    public function getReport($tgldari, $tglsampai, $cabang_id, $minggu)
    {
        $tempcabang = '##tempcabang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempcabang, function ($table) {
            $table->id();
            $table->integer('cabang_id')->nullable();
            $table->string('coa', 50)->nullable();
        });

        DB::table($tempcabang)->insert(
            ['cabang_id' => 2, 'coa' => '05.03.01.02',]
        );
        DB::table($tempcabang)->insert(
            ['cabang_id' => 3, 'coa' => '05.03.01.03',]
        );
        DB::table($tempcabang)->insert(
            ['cabang_id' => 4, 'coa' => '05.03.01.04',]
        );
        DB::table($tempcabang)->insert(
            ['cabang_id' => 5, 'coa' => '05.03.01.05',]
        );
        DB::table($tempcabang)->insert(
            ['cabang_id' => 8, 'coa' => '05.03.01.06',]
        );
        DB::table($tempcabang)->insert(
            ['cabang_id' => 9, 'coa' => '05.03.01.07',]
        );
        

        $tempdata = '##tempdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdata, function ($table) {
            $table->id();
            $table->integer('urut')->nullable();
            $table->string('FNJ', 100)->nullable();
            $table->string('FNTrans', 100)->nullable();
            $table->date('FTgl')->nullable();
            $table->string('FCoa', 100)->nullable();
            $table->longtext('FKetCoa')->nullable();
            $table->double('FDebet', 15, 2)->nullable();
            $table->double('FCredit', 15, 2)->nullable();
            $table->double('FSaldo', 15, 2)->nullable();
            $table->longtext('FKetD')->nullable();
            $table->integer('Order')->nullable();
            $table->unsignedBigInteger('FSeqtime')->nullable();
            $table->string('cabang', 100)->nullable();
            $table->integer('cabang_id')->nullable();
        });

        $querytempdata = db::table("jurnalumumpusatdetail")->from(db::raw("jurnalumumpusatdetail D with (readuncommitted)"))
            ->select(
                db::raw("2 AS urut"),
                'D.nobukti as fnj',
                'D.nobukti as fntrans',
                'D.tglbukti as ftgl',
                'CM.coa as fcoa',
                'CM.keterangancoa as fketcoa',
                db::raw("CASE SIGN(-1 * D.Nominal) WHEN 1 THEN -1 * D.Nominal ELSE 0 END AS fdebet"),
                db::raw("CASE SIGN(-1 * D.Nominal) WHEN 1 THEN 0 ELSE D.Nominal END AS fcredit"),
                db::raw("-1 * D.Nominal AS fsaldo"),
                db::raw("CASE D.keterangan WHEN '' THEN H.keterangan ELSE D.keterangan END AS FKetD"),
                db::raw("A.[Order]"),
                db::raw("0 AS FSeqTime "),
                'c.namacabang as cabang',
                'c.id as cabang_id',
            )
            ->join(db::raw("jurnalumumpusatheader H with (readuncommitted)"), 'h.nobukti', 'd.nobukti')
            ->join(db::raw("akunpusat CM with (readuncommitted)"), 'CM.coa', 'd.coa')
            ->join(db::raw("typeakuntansi A with (readuncommitted)"), 'A.id', 'CM.type_id')
            ->join(db::raw($tempcabang . " MC "), 'MC.coa', 'D.coa')
            ->join(db::raw("cabang c "), 'mc.cabang_id', 'c.id')
            ->whereraw("D.tglbukti BETWEEN '" . date('Y-m-d', strtotime($tgldari)) . "' AND '" . date('Y-m-d', strtotime($tglsampai)) . "'");


        DB::table($tempdata)->insertUsing([
            'urut',
            'FNJ',
            'FNTrans',
            'FTgl',
            'FCoa',
            'FKetCoa',
            'FDebet',
            'FCredit',
            'FSaldo',
            'FKetD',
            'Order',
            'FSeqtime',
            'cabang',
            'cabang_id'
        ], $querytempdata);

        


        $querytempdata = db::table("jurnalumumdetail")->from(db::raw("jurnalumumdetail D with (readuncommitted)"))
            ->select(
                db::raw("2 AS urut"),
                'D.nobukti as fnj',
                'D.nobukti as fntrans',
                'D.tglbukti as ftgl',
                'CM.coa as fcoa',
                'CM.keterangancoa as fketcoa',
                db::raw("CASE SIGN(-1 * D.Nominal) WHEN 1 THEN -1 * D.Nominal ELSE 0 END AS fdebet"),
                db::raw("CASE SIGN(-1 * D.Nominal) WHEN 1 THEN 0 ELSE D.Nominal END AS fcredit"),
                db::raw("-1 * D.Nominal AS fsaldo"),
                db::raw("CASE D.keterangan WHEN '' THEN H.keterangan ELSE D.keterangan END AS FKetD"),
                db::raw("A.[Order]"),
                db::raw("0 AS FSeqTime "),
                'c.namacabang as cabang',
                'c.id as cabang_id',
            )
            ->join(db::raw("jurnalumumheader H with (readuncommitted)"), 'h.nobukti', 'd.nobukti')
            ->join(db::raw("akunpusat CM with (readuncommitted)"), 'CM.coa', 'd.coa')
            ->join(db::raw("typeakuntansi A with (readuncommitted)"), 'A.id', 'CM.type_id')
            ->join(db::raw($tempcabang . " MC "), 'MC.coa', 'D.coa')
            ->leftjoin(db::raw($tempdata . " d1"), 'h.nobukti', 'd1.fntrans')
            ->join(db::raw("cabang c "), 'mc.cabang_id', 'c.id')
            ->whereraw("D.tglbukti  BETWEEN '" . date('Y-m-d', strtotime($tgldari)) . "' AND '" . date('Y-m-d', strtotime($tglsampai)) . "'")
            ->whereraw("isnull(d1.fntrans,'')=''");


        DB::table($tempdata)->insertUsing([
            'urut',
            'FNJ',
            'FNTrans',
            'FTgl',
            'FCoa',
            'FKetCoa',
            'FDebet',
            'FCredit',
            'FSaldo',
            'FKetD',
            'Order',
            'FSeqtime',
            'cabang',
            'cabang_id'
        ], $querytempdata);

        $pKdPerkDr = db::table($tempcabang)->from(db::raw($tempcabang . " a"))
            ->select(
                'a.coa'
            )
            ->where('a.cabang_id', $cabang_id)
            ->first()->coa ?? '';

        $pKdPerkSd = db::table($tempcabang)->from(db::raw($tempcabang . " a"))
            ->select(
                'a.coa'
            )
            ->where('a.cabang_id', $cabang_id)
            ->first()->coa ?? '';

        if ($cabang_id != 0) {
            DB::delete(DB::raw("delete " . $tempdata . " where fcoa not in('" . $pKdPerkDr . "')"));
        }

        $namacabang = db::table("cabang")->from(db::raw("cabang a"))
            ->select(
                'a.namacabang'
            )
            ->where('a.id', $cabang_id)
            ->first()->namacabang ?? '';

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

            // dd($cabang_id);
            if ($cabang_id !=0 ) {
                $query = db::table($tempdata)->from(db::raw($tempdata . " a"))
                ->select(
                    db::raw($cabang_id . " as cabang_id"),
                    db::raw("'" . $namacabang . "' as namacabang"),
                    db::raw("'" . $minggu . "' as mingguke"),
                    'a.FTgl as tanggal',
                    'a.FDebet as debet',
                    'a.FCredit as kredit',
                    'a.FSaldo as saldo',
                    'a.FKetD as keterangan',
                    DB::raw("'ARUS DANA PUSAT - CABANG MINGGUAN' as judulLaporan"),
                    DB::raw("'" . $getJudul->text . "' as judul"),
                    DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                    DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
                )
                ->orderby('a.cabang_id','asc')
                ->orderby('a.id','asc')
                ->get();
            } else {
                $query = db::table($tempdata)->from(db::raw($tempdata . " a"))
                ->select(
                    db::raw("a.cabang_id as cabang_id"),
                    db::raw("a.cabang as namacabang"),
                    db::raw("'" . $minggu . "' as mingguke"),
                    'a.FTgl as tanggal',
                    'a.FDebet as debet',
                    'a.FCredit as kredit',
                    'a.FSaldo as saldo',
                    'a.FKetD as keterangan',
                    DB::raw("'ARUS DANA PUSAT - CABANG MINGGUAN' as judulLaporan"),
                    DB::raw("'" . $getJudul->text . "' as judul"),
                    DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                    DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
                )
                ->orderby('a.cabang_id','asc')
                ->orderby('a.id','asc')
                ->get();
            }
            // dd($query);


        //  return $query;

        // 






        $data = $query;

        // $data = [
        //     [
        //         'cabang_id' => '2',
        //         'namacabang' => 'CABANG MEDAN',
        //         'mingguke' => $minggu . 'MINGGU KE 4 BULAN 4 TAHUN 2024',
        //         'tanggal' => '2024-04-24',
        //         'keterangan' => 'MINGGUAN TRUCKING',
        //         'debet' => 0,
        //         'kredit' => 90000000,
        //         'saldo' => '-90000000',
        //         'judulLaporan' => 'ARUS DANA PUSAT - CABANG MINGGUAN',
        //         'judul' => $getJudul->text,
        //         'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
        //         'usercetak' => 'User : ' . auth('api')->user()->name
        //     ], [
        //         'cabang_id' => '2',
        //         'namacabang' => 'CABANG MEDAN',
        //         'mingguke' => $minggu . 'MINGGU KE 4 BULAN 4 TAHUN 2024',
        //         'tanggal' => '2024-04-25',
        //         'keterangan' => 'PENYETORAN ATAS PELUNASAN INV 21, 23, 27, 28',
        //         'debet' => 183206500,
        //         'kredit' => 0,
        //         'saldo' => 93206500,
        //         'judulLaporan' => 'ARUS DANA PUSAT - CABANG MINGGUAN',
        //         'judul' => $getJudul->text,
        //         'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
        //         'usercetak' => 'User : ' . auth('api')->user()->name
        //     ], [
        //         'cabang_id' => '2',
        //         'namacabang' => 'CABANG MEDAN',
        //         'mingguke' => $minggu . 'MINGGU KE 4 BULAN 4 TAHUN 2024',
        //         'tanggal' => '2024-04-26',
        //         'keterangan' => 'MINGGUAN TRUCKING',
        //         'debet' => 0,
        //         'kredit' => 90000000,
        //         'saldo' => 3206500,
        //         'judulLaporan' => 'ARUS DANA PUSAT - CABANG MINGGUAN',
        //         'judul' => $getJudul->text,
        //         'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
        //         'usercetak' => 'User : ' . auth('api')->user()->name
        //     ], [
        //         'cabang_id' => '3',
        //         'namacabang' => 'CABANG JAKARTA',
        //         'mingguke' => $minggu . 'MINGGU KE 4 BULAN 4 TAHUN 2024',
        //         'tanggal' => '2024-04-24',
        //         'keterangan' => 'B. KOMISI ROBERT BLN FEB  2024',
        //         'debet' => 0,
        //         'kredit' => 25216264,
        //         'saldo' => '-25216264',
        //         'judulLaporan' => 'ARUS DANA PUSAT - CABANG MINGGUAN',
        //         'judul' => $getJudul->text,
        //         'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
        //         'usercetak' => 'User : ' . auth('api')->user()->name
        //     ], [
        //         'cabang_id' => '3',
        //         'namacabang' => 'CABANG JAKARTA',
        //         'mingguke' => $minggu . 'MINGGU KE 4 BULAN 4 TAHUN 2024',
        //         'tanggal' => '2024-04-24',
        //         'keterangan' => 'MINGGUAN TRUCKING',
        //         'debet' => 0,
        //         'kredit' => 100000000,
        //         'saldo' => '-125216264',
        //         'judulLaporan' => 'ARUS DANA PUSAT - CABANG MINGGUAN',
        //         'judul' => $getJudul->text,
        //         'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
        //         'usercetak' => 'User : ' . auth('api')->user()->name
        //     ], [
        //         'cabang_id' => '3',
        //         'namacabang' => 'CABANG JAKARTA',
        //         'mingguke' => $minggu . 'MINGGU KE 4 BULAN 4 TAHUN 2024',
        //         'tanggal' => '2024-04-26',
        //         'keterangan' => 'MINGGUAN TRUCKING',
        //         'debet' => 0,
        //         'kredit' => 100000000,
        //         'saldo' => '-225216264',
        //         'judulLaporan' => 'ARUS DANA PUSAT - CABANG MINGGUAN',
        //         'judul' => $getJudul->text,
        //         'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
        //         'usercetak' => 'User : ' . auth('api')->user()->name
        //     ], [
        //         'cabang_id' => '4',
        //         'namacabang' => 'CABANG SURABAYA',
        //         'mingguke' => $minggu . 'MINGGU KE 4 BULAN 4 TAHUN 2024',
        //         'tanggal' => '2024-04-24',
        //         'keterangan' => 'B. KOMISI CITRA BLN FEB  2024',
        //         'debet' => 0,
        //         'kredit' => 6427173,
        //         'saldo' => '-6427173',
        //         'judulLaporan' => 'ARUS DANA PUSAT - CABANG MINGGUAN',
        //         'judul' => $getJudul->text,
        //         'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
        //         'usercetak' => 'User : ' . auth('api')->user()->name
        //     ], [
        //         'cabang_id' => '4',
        //         'namacabang' => 'CABANG SURABAYA',
        //         'mingguke' => $minggu . 'MINGGU KE 4 BULAN 4 TAHUN 2024',
        //         'tanggal' => '2024-04-26',
        //         'keterangan' => 'MINGGUAN TRUCKING',
        //         'debet' => 0,
        //         'kredit' => 130000000,
        //         'saldo' => '-136427173',
        //         'judulLaporan' => 'ARUS DANA PUSAT - CABANG MINGGUAN',
        //         'judul' => $getJudul->text,
        //         'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
        //         'usercetak' => 'User : ' . auth('api')->user()->name
        //     ]
        // ];

        return $data;
    }
}
