<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanPinjamanSupirKaryawan extends MyModel
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



    public function getReport($sampai,$prosesneraca, $jenis)
    {
        $pengeluarantrucking_id = 8;
        $penerimaantrucking_id = 4;
        $prosesneraca = $prosesneraca ?? 0;

        $parameter = new Parameter();
        $idstatusposting = $parameter->cekId('STATUS POSTING', 'STATUS POSTING', 'POSTING') ?? 0; 

        if ($prosesneraca==1) {
            $jenis=$idstatusposting;
        }

        $temphistory = '##temphistory' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temphistory, function ($table) {
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->integer('karyawan_id')->nullable();
            $table->double('nominal')->nullable();
            $table->integer('tipe')->nullable();
            $table->string('namakaryawan', 1000)->nullable();            
        });

        if ($jenis==0) {
            $queryhistory = DB::table('pengeluarantruckingheader')->from(
                DB::raw("pengeluarantruckingheader a with (readuncommitted) ")
            )
                ->select(
                    'a.nobukti',
                    'a.tglbukti',
                    'b.karyawan_id',
                    'b.nominal',
                    DB::raw("1 as tipe"),
                    db::raw("isnull(c.namakaryawan,'') as namakaryawan")
                )
                ->join(DB::raw("pengeluarantruckingdetail as b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
                ->leftjoin(DB::raw("karyawan as c with (readuncommitted) "), 'b.karyawan_id', 'c.id')
    
                ->where('a.pengeluarantrucking_id', '=', $pengeluarantrucking_id)
                ->whereRaw("a.tglbukti<='" . date('Y/m/d', strtotime($sampai)) . "'")
                ->whereRaw("isnull(b.karyawan_id,0)<>0")
                ->OrderBy('c.namakaryawan', 'asc')
                ->OrderBy('a.tglbukti', 'asc')
                ->OrderBy('a.nobukti', 'asc');
        } else {
            $queryhistory = DB::table('pengeluarantruckingheader')->from(
                DB::raw("pengeluarantruckingheader a with (readuncommitted) ")
            )
                ->select(
                    'a.nobukti',
                    'a.tglbukti',
                    'b.karyawan_id',
                    'b.nominal',
                    DB::raw("1 as tipe"),
                    db::raw("isnull(c.namakaryawan,'') as namakaryawan")
                )
                ->join(DB::raw("pengeluarantruckingdetail as b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
                ->leftjoin(DB::raw("karyawan as c with (readuncommitted) "), 'b.karyawan_id', 'c.id')
    
                ->where('a.pengeluarantrucking_id', '=', $pengeluarantrucking_id)
                ->whereRaw("a.tglbukti<='" . date('Y/m/d', strtotime($sampai)) . "'")
                ->whereRaw("isnull(b.karyawan_id,0)<>0")
                ->where('a.statusposting', '=', $jenis)
                ->OrderBy('c.namakaryawan', 'asc')
                ->OrderBy('a.tglbukti', 'asc')
                ->OrderBy('a.nobukti', 'asc');
  
        }
       

            // dd($queryhistory->tosql());
        DB::table($temphistory)->insertUsing([
            'nobukti',
            'tglbukti',
            'karyawan_id',
            'nominal',
            'tipe',
            'namakaryawan',
        ], $queryhistory);


        // dd(db::table($temphistory)->get());

      
        if ($jenis==0) {
            $queryhistory = DB::table('penerimaantruckingheader')->from(
                DB::raw("penerimaantruckingheader a with (readuncommitted) ")
            )
                ->select(
                    'c.nobukti',
                    'a.tglbukti',
                    'b.karyawan_id',
                    db::raw("(b.nominal*-1) as nominal"),
                    DB::raw("1 as tipe"),
                    db::raw("isnull(f.namakaryawan,'') as namakaryawan")
    
                )
                ->join(DB::raw("penerimaantruckingdetail as b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
                ->join(DB::raw("pengeluarantruckingheader as c with (readuncommitted) "), 'b.pengeluarantruckingheader_nobukti', 'c.nobukti')
                ->leftjoin(DB::raw("karyawan as f with (readuncommitted) "), 'b.karyawan_id', 'f.id')
                // ->Join(DB::raw("penerimaanheader as g with (readuncommitted)"), 'a.penerimaan_nobukti', 'g.nobukti')

    
                ->where('a.penerimaantrucking_id', '=', $penerimaantrucking_id)
                ->whereRaw("a.tglbukti<'" . date('Y/m/d', strtotime($sampai)) . "'")
                ->whereRaw("isnull(b.karyawan_id,0)<>0")
                ->OrderBy('f.namakaryawan', 'asc')
                ->OrderBy('a.tglbukti', 'asc')
                ->OrderBy('a.nobukti', 'asc');
    
        } else {
            $queryhistory = DB::table('penerimaantruckingheader')->from(
                DB::raw("penerimaantruckingheader a with (readuncommitted) ")
            )
                ->select(
                    'c.nobukti',
                    'a.tglbukti',
                    'b.karyawan_id',
                    db::raw("(b.nominal*-1) as nominal"),
                    DB::raw("1 as tipe"),
                    db::raw("isnull(f.namakaryawan,'') as namakaryawan")
    
                )
                ->join(DB::raw("penerimaantruckingdetail as b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
                ->join(DB::raw("pengeluarantruckingheader as c with (readuncommitted) "), 'b.pengeluarantruckingheader_nobukti', 'c.nobukti')
                ->leftjoin(DB::raw("karyawan as f with (readuncommitted) "), 'b.karyawan_id', 'f.id')
                // ->Join(DB::raw("penerimaanheader as g with (readuncommitted)"), 'a.penerimaan_nobukti', 'g.nobukti')

    
                ->where('a.penerimaantrucking_id', '=', $penerimaantrucking_id)
                ->whereRaw("a.tglbukti<'" . date('Y/m/d', strtotime($sampai)) . "'")
                ->whereRaw("isnull(b.karyawan_id,0)<>0")
                ->where('c.statusposting', '=', $jenis)
                ->OrderBy('f.namakaryawan', 'asc')
                ->OrderBy('a.tglbukti', 'asc')
                ->OrderBy('a.nobukti', 'asc');
         
             
        }
       
   
//  dd($queryhistory->get());

        DB::table($temphistory)->insertUsing([
            'nobukti',
            'tglbukti',
            'karyawan_id',
            'nominal',
            'tipe',
            'namakaryawan',

        ], $queryhistory);

        $temprekapdata = '##temprekapdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temprekapdata, function ($table) {
            $table->id();
            $table->string('nobukti', 1000)->nullable();
            $table->string('nobuktipelunasan', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->date('tglbuktipelunasan')->nullable();
            $table->double('nominal')->nullable();
            $table->string('namakaryawan', 1000)->nullable();
        });

        $queryrekapdata = DB::table($temphistory)->from(
            DB::raw($temphistory . " a  ")
        )
            ->select(
                'a.nobukti',
                DB::raw("'' as nobuktipelunasan"),
                DB::raw("min(a.tglbukti) as tglbukti"),
                DB::raw("'1900/1/1' as tglbuktipelunasan"),
                DB::raw("sum(a.nominal) as nominal"),
                DB::raw("max(a.namakaryawan) as namakaryawan"),

            )
            ->groupBy('a.nobukti');
        DB::table($temprekapdata)->insertUsing([
            'nobukti',
            'nobuktipelunasan',
            'tglbukti',
            'tglbuktipelunasan',
            'nominal',
            'namakaryawan',
        ], $queryrekapdata);

        DB::delete(DB::raw("delete " . $temprekapdata . " WHERE isnull(nominal,0)=0"));

        if ($jenis==0) {
            $queryrekapdata = DB::table('penerimaantruckingheader')->from(
                DB::raw("penerimaantruckingheader a with (readuncommitted) ")
            )
                ->select(
                    'b.pengeluarantruckingheader_nobukti as nobukti',
                    DB::raw("isnull(a.penerimaan_nobukti,'') as nobuktipelunasan"),
                    'e.tglbukti',
                    'a.tglbukti as tglbuktipelunasan',
                    DB::raw("(b.nominal*-1) as nominal"),
                    db::raw("isnull(f.namakaryawan,'') as namakaryawan")
    
                )
                ->join(DB::raw("penerimaantruckingdetail as b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
                ->leftjoin(DB::raw("pengeluarantruckingheader as e with (readuncommitted) "), 'b.pengeluarantruckingheader_nobukti', 'e.nobukti')
                ->leftjoin(DB::raw("karyawan as f with (readuncommitted) "), 'b.karyawan_id', 'f.id')
                // ->Join(DB::raw("penerimaanheader as g with (readuncommitted)"), 'a.penerimaan_nobukti', 'g.nobukti')

                ->where('a.penerimaantrucking_id', '=', $penerimaantrucking_id)
                ->whereRaw("a.tglbukti='" . date('Y/m/d', strtotime($sampai)) . "'")
                ->whereRaw("isnull(b.karyawan_id,0)<>0")
                ->OrderBy('f.namakaryawan', 'asc')
                ->OrderBy('a.tglbukti', 'asc')
                ->OrderBy('a.nobukti', 'asc');
        } else {
            $queryrekapdata = DB::table('penerimaantruckingheader')->from(
                DB::raw("penerimaantruckingheader a with (readuncommitted) ")
            )
                ->select(
                    'b.pengeluarantruckingheader_nobukti as nobukti',
                    DB::raw("isnull(a.penerimaan_nobukti,'') as nobuktipelunasan"),
                    'e.tglbukti',
                    'a.tglbukti as tglbuktipelunasan',
                    DB::raw("(b.nominal*-1) as nominal"),
                    db::raw("isnull(f.namakaryawan,'') as namakaryawan")
    
                )
                ->join(DB::raw("penerimaantruckingdetail as b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
                ->leftjoin(DB::raw("pengeluarantruckingheader as e with (readuncommitted) "), 'b.pengeluarantruckingheader_nobukti', 'e.nobukti')
                ->leftjoin(DB::raw("karyawan as f with (readuncommitted) "), 'b.karyawan_id', 'f.id')
                // ->Join(DB::raw("penerimaanheader as g with (readuncommitted)"), 'a.penerimaan_nobukti', 'g.nobukti')

                ->where('a.penerimaantrucking_id', '=', $penerimaantrucking_id)
                ->whereRaw("a.tglbukti='" . date('Y/m/d', strtotime($sampai)) . "'")
                ->whereRaw("isnull(b.karyawan_id,0)<>0")
                ->where('e.statusposting', '=', $jenis)            
                ->OrderBy('f.namakaryawan', 'asc')
                ->OrderBy('a.tglbukti', 'asc')
                ->OrderBy('a.nobukti', 'asc');
        }
      

            // dd($queryrekapdata->get());


        DB::table($temprekapdata)->insertUsing([
            'nobukti',
            'nobuktipelunasan',
            'tglbukti',
            'tglbuktipelunasan',
            'nominal',
            'namakaryawan',            
        ], $queryrekapdata);

        $temprekapdatahasil = '##temprekapdatahasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temprekapdatahasil, function ($table) {
            $table->id();
            $table->string('nobukti', 1000)->nullable();
            $table->string('nobuktipelunasan', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->date('tglbuktipelunasan')->nullable();
            $table->double('nominal')->nullable();
            $table->string('namakaryawan', 1000)->nullable();
        });

        $queryrekapdatahasil = DB::table($temprekapdata)->from(
            DB::raw($temprekapdata . " a ")
        )
            ->select(
                'a.nobukti',
                'a.nobuktipelunasan',
                'a.tglbukti',
                'a.tglbuktipelunasan',
                'a.nominal',
                'a.namakaryawan'                
            )
            ->OrderBy('a.namakaryawan', 'asc')
            ->OrderBy('a.tglbukti', 'asc')
            ->OrderBy('a.nobukti', 'asc');


        DB::table($temprekapdatahasil)->insertUsing([
            'nobukti',
            'nobuktipelunasan',
            'tglbukti',
            'tglbuktipelunasan',
            'nominal',
            'namakaryawan',
        ], $queryrekapdatahasil);

        $temphasil = '##temphasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temphasil, function ($table) {
            $table->id();
            $table->string('nobukti', 1000)->nullable();
            $table->string('namakaryawan', 1000)->nullable();
            $table->string('nobuktipelunasan', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->date('tglbuktipelunasan')->nullable();
            $table->double('debet')->nullable();
            $table->double('kredit')->nullable();
            $table->double('saldo')->nullable();
        });

        $queryhasil = DB::table($temprekapdatahasil)->from(
            DB::raw($temprekapdatahasil . " a ")
        )
            ->select(
                'a.nobukti',
                'a.namakaryawan',
                'a.nobuktipelunasan',
                'a.tglbukti',
                'a.tglbuktipelunasan',
                DB::raw("(case when isnull(a.nobuktipelunasan,'')='' then a.nominal else 0 end) as debet"),
                DB::raw("(case when isnull(a.nobuktipelunasan,'')='' then 0 else a.nominal end) as kredit"),
                DB::raw("0 as saldo")

            )
            ->OrderBy('a.id', 'asc');

        DB::table($temphasil)->insertUsing([
            'nobukti',
            'namakaryawan',
            'nobuktipelunasan',
            'tglbukti',
            'tglbuktipelunasan',
            'debet',
            'kredit',
            'saldo',
        ], $queryhasil);


        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

            $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';


            
        if ($jenis == 0) {
            $judul1 = 'POSTING / NON POSTING';
        } else {
            $parameter = new Parameter();
            $judul1 = $parameter->cekdataText($jenis) ?? '';
        }

        $query = DB::table($temphasil)->from(
            DB::raw($temphasil . " a ")
        )
            ->select(
                // db::raw("(a.nobukti) + ' '+(case when isnull(a.nobuktipelunasan,'')='' then '' else '( '+isnull(a.nobuktipelunasan,'')+' )' end) as nobukti"),
                // 'a.id',
                db::raw("
                (case when isnull(a.nobuktipelunasan,'')='' then a.nobukti else isnull(a.nobuktipelunasan,'') end) as nobukti"),
                'a.namakaryawan',
                'a.nobuktipelunasan',
                db::raw("
                (case when isnull(a.nobuktipelunasan,'')='' then a.tglbukti else isnull(c.tglbukti,'1900/1/1') end) as tglbukti"),
                'a.tglbuktipelunasan',
                'b.keterangan',
                'a.debet',
                db::raw("abs(a.kredit) as kredit"),
                DB::raw("sum ((isnull(a.saldo,0)+a.debet-a.kredit)) over (order by a.id asc) as Saldo"),
                DB::raw("'LAPORAN PINJAMAN KARYAWAN " . $judul1 . "' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),

            )
            ->leftjoin(DB::raw("pengeluarantruckingdetail as b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
            ->leftjoin(DB::raw("penerimaanheader as c with (readuncommitted) "), 'a.nobuktipelunasan', 'c.nobukti')

            ->OrderBy('a.id', 'asc');

            if ($prosesneraca == 1) {
                $data = $query;

            } else {
                $data = $query->get();

            }

        return $data;
    }
}
