<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanPemotonganPinjamanPerEBS extends MyModel
{
    use HasFactory;

    protected $table = '';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];



    public function getReport($dari, $sampai)
    {
        $TempListGajisupir = '##TempListGajisupir' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempListGajisupir, function ($table) {
            $table->string('nobukti', 50);
            $table->dateTime('tglbukti');
            $table->string('gajisupir_nobukti', 100);
            $table->string('namasupir', 1000);
            $table->decimal('total', 10, 2);
            $table->decimal('uangjalan', 10, 2);
            $table->decimal('bbm', 10, 2);
            $table->decimal('potonganpinjaman', 10, 2);
            $table->decimal('deposito', 10, 2);
            $table->decimal('potonganpinjamansemua', 10, 2);
            $table->string('nopolisi', 100);
            $table->date('tgldari');
            $table->date('tglsampai');
            $table->decimal('komisisupir', 10, 2);
            $table->decimal('tolsupir', 10, 2);
            $table->decimal('voucher', 10, 2);
            $table->date('tanggaldari');
            $table->date('tanggalsampai');
        });

        $select_TempListGajisupir = DB::table('prosesgajisupirheader')->from(DB::raw("prosesgajisupirheader AS H WITH (READUNCOMMITTED)"))
            ->select([
                'H.nobukti',
                'H.tglbukti',
                'D.gajisupir_nobukti',
                'S.namasupir',
                'E.total',
                'E.uangjalan',
                'E.bbm',
                'E.potonganpinjaman',
                'E.deposito',
                'E.potonganpinjamansemua',
                'f.kodetrado as nopolisi',
                'H.tgldari',
                'H.tglsampai',
                DB::raw('ISNULL(E.komisisupir,0) AS komisisupir'),
                DB::raw('ISNULL(E.tolsupir,0) AS tolsupir'),
                DB::raw('ISNULL(E.voucher,0) AS voucher'),
                DB::raw("'$dari' as tgldari"),
                DB::raw("'$sampai' as tglsampai"),

            ])
            ->join(DB::raw("prosesgajisupirdetail as D with (readuncommitted)"), 'H.nobukti', 'D.Nobukti')
            ->join(DB::raw("Supir as S with (readuncommitted)"), 'S.id', 'D.supir_id')
            ->join(DB::raw("gajisupirheader as E with (readuncommitted)"), 'D.gajisupir_nobukti', 'E.nobukti')
            ->join(DB::raw("trado as F with (readuncommitted)"), 'f.id', 'D.trado_id')
            ->whereBetween('H.tglbukti', [$dari, $sampai])
            ->where(function ($query) {
                $query->where('E.potonganpinjaman', '<>', 0)
                    ->orWhere('E.potonganpinjamansemua', '<>', 0);
            })
            ->orderBy('H.tglbukti')
            ->orderBy('S.namasupir');

        DB::table($TempListGajisupir)->insertUsing([
            'nobukti',
            'tglbukti',
            'gajisupir_nobukti',
            'namasupir',
            'total',
            'uangjalan',
            'bbm',
            'potonganpinjaman',
            'deposito',
            'potonganpinjamansemua',
            'nopolisi',
            'tgldari',
            'tglsampai',
            'komisisupir',
            'tolsupir',
            'voucher',
            'tanggaldari',
            'tanggalsampai',
        ], $select_TempListGajisupir);

        // dd($select_TempListGajisupir->get());
        $Temppinjaman = '##Temppinjaman' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppinjaman, function ($table) {
            $table->string('gajisupir_nobukti', 100);
            $table->longText('keterangan');
        });

        $TemppinjamanSemua = '##TemppinjamanSemua' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TemppinjamanSemua, function ($table) {
            $table->string('gajisupir_nobukti', 100);
            $table->longText('keterangan');
        });

        $select_TempListGajisupir = DB::table($TempListGajisupir . ' AS B')
            ->select([
                'B.gajisupir_nobukti',
                DB::raw("STUFF((SELECT DISTINCT ', ' + E.pengeluarantrucking_nobukti  
                      FROM $TempListGajisupir A
                      INNER JOIN gajisupirpelunasanpinjaman E WITH (READUNCOMMITTED) ON A.gajisupir_nobukti = E.gajisupir_nobukti
                      WHERE ISNULL(e.supir_id,0) <> 0
                      AND A.gajisupir_nobukti = B.gajisupir_nobukti
                      FOR XML PATH('')), 1, 2, '') AS FNTrans"),
            ]);
        // dd($select_TempListGajisupir->get());

        $select_TempListGajisupir2 = DB::table($TempListGajisupir . ' AS B')
            ->select([
                'B.gajisupir_nobukti',
                DB::raw("STUFF((SELECT DISTINCT ', ' + E.pengeluarantrucking_nobukti  
                      FROM $TempListGajisupir A
                      INNER JOIN gajisupirpelunasanpinjaman E WITH (READUNCOMMITTED) ON A.gajisupir_nobukti = E.gajisupir_nobukti
                      WHERE ISNULL(e.supir_id, 0) = 0
                      AND A.gajisupir_nobukti = B.gajisupir_nobukti
                      FOR XML PATH('')), 1, 2, '') AS FNTrans"),
            ]);

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
            
        $select_TempListGajisupir3 = DB::table($TempListGajisupir . ' AS A')
            ->select([
                'A.nobukti',
                DB::raw("CONVERT(varchar, A.tglbukti, 23) AS tglbukti"),
                'A.gajisupir_nobukti',
                'A.namasupir',
                'A.total',
                'A.uangjalan',
                'A.bbm',
                'A.potonganpinjaman',
                'A.deposito',
                'A.potonganpinjamansemua',
                'A.nopolisi',
                'A.tgldari',
                'A.tglsampai',
                'A.komisisupir',
                'A.tolsupir',
                'A.voucher',
                'A.tanggaldari',
                'A.tanggalsampai',
                DB::raw("ISNULL(b.keterangan, '') as keteranganpinjamansupir"),
                DB::raw("ISNULL(c.keterangan, '') as keteranganpinjamansupirsemua"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
                DB::raw("'LAPORAN PEMOTONGAN PEMINJAMAN SUPIR PER EBS' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            ])
            ->leftJoin(DB::raw($Temppinjaman . " AS B"), function ($join) {
                $join->on('A.gajisupir_nobukti', '=', 'B.gajisupir_nobukti');
            })
            ->leftJoin(DB::raw($TemppinjamanSemua . " AS C"), function ($join) {
                $join->on('A.gajisupir_nobukti', '=', 'C.gajisupir_nobukti');
            })
            ->orderBy('A.tglbukti', 'asc')
            ->orderBy('A.namasupir', 'asc');

        // dd($select_TempListGajisupir3->get());
        $data = $select_TempListGajisupir3->get();
        return $data;


        // DB::table($TemppinjamanSemua)->insertUsing([
        //     'gajisupir_nobukti',
        //     'keterangan',
        // ], $select_TemppinjamanSemua);






    }

    public function getExport($dari, $sampai)
    {
        $TempListGajisupir = '##TempListGajisupir' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempListGajisupir, function ($table) {
            $table->string('nobukti', 50);
            $table->dateTime('tglbukti');
            $table->string('gajisupir_nobukti', 100);
            $table->string('namasupir', 1000);
            $table->decimal('total', 10, 2);
            $table->decimal('uangjalan', 10, 2);
            $table->decimal('bbm', 10, 2);
            $table->decimal('potonganpinjaman', 10, 2);
            $table->decimal('deposito', 10, 2);
            $table->decimal('potonganpinjamansemua', 10, 2);
            $table->string('nopolisi', 100);
            $table->date('tgldari');
            $table->date('tglsampai');
            $table->decimal('komisisupir', 10, 2);
            $table->decimal('tolsupir', 10, 2);
            $table->decimal('voucher', 10, 2);
            $table->date('tanggaldari');
            $table->date('tanggalsampai');
        });

        $select_TempListGajisupir = DB::table('prosesgajisupirheader')->from(DB::raw("prosesgajisupirheader AS H WITH (READUNCOMMITTED)"))
            ->select([
                'H.nobukti',
                'H.tglbukti',
                'D.gajisupir_nobukti',
                'S.namasupir',
                'E.total',
                'E.uangjalan',
                'E.bbm',
                'E.potonganpinjaman',
                'E.deposito',
                'E.potonganpinjamansemua',
                'f.kodetrado as nopolisi',
                'H.tgldari',
                'H.tglsampai',
                DB::raw('ISNULL(E.komisisupir,0) AS komisisupir'),
                DB::raw('ISNULL(E.tolsupir,0) AS tolsupir'),
                DB::raw('ISNULL(E.voucher,0) AS voucher'),
                DB::raw("'$dari' as tgldari"),
                DB::raw("'$sampai' as tglsampai"),

            ])
            ->join(DB::raw("prosesgajisupirdetail as D with (readuncommitted)"), 'H.nobukti', 'D.Nobukti')
            ->join(DB::raw("Supir as S with (readuncommitted)"), 'S.id', 'D.supir_id')
            ->join(DB::raw("gajisupirheader as E with (readuncommitted)"), 'D.gajisupir_nobukti', 'E.nobukti')
            ->join(DB::raw("trado as F with (readuncommitted)"), 'f.id', 'D.trado_id')
            ->whereBetween('H.tglbukti', [$dari, $sampai])
            ->where(function ($query) {
                $query->where('E.potonganpinjaman', '<>', 0)
                    ->orWhere('E.potonganpinjamansemua', '<>', 0);
            })
            ->orderBy('H.tglbukti')
            ->orderBy('S.namasupir');

        DB::table($TempListGajisupir)->insertUsing([
            'nobukti',
            'tglbukti',
            'gajisupir_nobukti',
            'namasupir',
            'total',
            'uangjalan',
            'bbm',
            'potonganpinjaman',
            'deposito',
            'potonganpinjamansemua',
            'nopolisi',
            'tgldari',
            'tglsampai',
            'komisisupir',
            'tolsupir',
            'voucher',
            'tanggaldari',
            'tanggalsampai',
        ], $select_TempListGajisupir);

        // dd($select_TempListGajisupir->get());
        $Temppinjaman = '##Temppinjaman' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppinjaman, function ($table) {
            $table->string('gajisupir_nobukti', 100);
            $table->longText('keterangan');
        });

        $TemppinjamanSemua = '##TemppinjamanSemua' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TemppinjamanSemua, function ($table) {
            $table->string('gajisupir_nobukti', 100);
            $table->longText('keterangan');
        });

        $select_TempListGajisupir = DB::table($TempListGajisupir . ' AS B')
            ->select([
                'B.gajisupir_nobukti',
                DB::raw("STUFF((SELECT DISTINCT ', ' + E.pengeluarantrucking_nobukti  
                      FROM $TempListGajisupir A
                      INNER JOIN gajisupirpelunasanpinjaman E WITH (READUNCOMMITTED) ON A.gajisupir_nobukti = E.gajisupir_nobukti
                      WHERE ISNULL(e.supir_id,0) <> 0
                      AND A.gajisupir_nobukti = B.gajisupir_nobukti
                      FOR XML PATH('')), 1, 2, '') AS FNTrans"),
            ]);
        // dd($select_TempListGajisupir->get());

        $select_TempListGajisupir2 = DB::table($TempListGajisupir . ' AS B')
            ->select([
                'B.gajisupir_nobukti',
                DB::raw("STUFF((SELECT DISTINCT ', ' + E.pengeluarantrucking_nobukti  
                      FROM $TempListGajisupir A
                      INNER JOIN gajisupirpelunasanpinjaman E WITH (READUNCOMMITTED) ON A.gajisupir_nobukti = E.gajisupir_nobukti
                      WHERE ISNULL(e.supir_id, 0) = 0
                      AND A.gajisupir_nobukti = B.gajisupir_nobukti
                      FOR XML PATH('')), 1, 2, '') AS FNTrans"),
            ]);

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
        $select_TempListGajisupir3 = DB::table($TempListGajisupir . ' AS A')
            ->select([
                'A.nobukti',
                DB::raw("CONVERT(varchar, A.tglbukti, 23) AS tglbukti"),
                'A.gajisupir_nobukti',
                'A.namasupir',
                'A.total',
                'A.uangjalan',
                'A.bbm',
                'A.potonganpinjaman',
                'A.deposito',
                'A.potonganpinjamansemua',
                'A.nopolisi',
                'A.tgldari',
                'A.tglsampai',
                'A.komisisupir',
                'A.tolsupir',
                'A.voucher',
                'A.tanggaldari',
                'A.tanggalsampai',
                DB::raw("ISNULL(b.keterangan, '') as keteranganpinjamansupir"),
                DB::raw("ISNULL(c.keterangan, '') as keteranganpinjamansupirsemua"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
                DB::raw("'LAPORAN PEMOTONGAN PEMINJAMAN SUPIR PER EBS' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            ])
            ->leftJoin(DB::raw($Temppinjaman . " AS B"), function ($join) {
                $join->on('A.gajisupir_nobukti', '=', 'B.gajisupir_nobukti');
            })
            ->leftJoin(DB::raw($TemppinjamanSemua . " AS C"), function ($join) {
                $join->on('A.gajisupir_nobukti', '=', 'C.gajisupir_nobukti');
            })
            ->orderBy('A.tglbukti', 'asc')
            ->orderBy('A.namasupir', 'asc');

        // dd($select_TempListGajisupir3->get());
        $data = $select_TempListGajisupir3->get();
        return $data;


        // DB::table($TemppinjamanSemua)->insertUsing([
        //     'gajisupir_nobukti',
        //     'keterangan',
        // ], $select_TemppinjamanSemua);






    }



    // $sampai = date("Y-m-d", strtotime($sampai));
    // // data coba coba
    // $query = DB::table('penerimaantruckingdetail')->from(
    //     DB::raw("penerimaantruckingdetail with (readuncommitted)")
    // )->select(
    //     'penerimaantruckingdetail.id',
    //     'supir.namasupir',
    //     'penerimaantruckingdetail.nominal',
    // )
    // ->leftJoin(DB::raw("supir with (readuncommitted)"), 'penerimaantruckingdetail.supir_id', 'supir.id')
    // ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingdetail.penerimaantruckingheader_id', 'penerimaantruckingheader.id')
    // ->where('penerimaantruckingheader.tglbukti','<=',$sampai);

    // $data = $query->get();
    // return $data;

}
