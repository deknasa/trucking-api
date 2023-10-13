<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanSupirLebihDariTrado extends MyModel
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

    public function get()
    {
        
        $this->setRequestParameters();

        $aktif = request()->aktif ?? '';

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
        ->select('text')
        ->where('grp', 'JUDULAN LAPORAN')
        ->where('subgrp', 'JUDULAN LAPORAN')
        ->first();
        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'akuntansi.id',
                'akuntansi.kodeakuntansi',
                'akuntansi.keterangan',
                'parameter.memo as statusaktif',
                'akuntansi.modifiedby',
                'akuntansi.created_at',
                'akuntansi.updated_at',
                DB::raw("'Laporan Akuntansi' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'akuntansi.statusaktif', 'parameter.id');

            
            $this->filter($query);
            

        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('akuntansi.statusaktif', '=', $statusaktif->id);
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        

        $this->sort($query);
        $this->paginate($query);
        $data = $query->get();
        return $data;
       

    }
    


    public function getReport($dari, $sampai)
    {
        
        $Templist = '##Templist' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Templist, function ($table) {
            $table->integer('supir_id')->nullable();
            $table->integer('trado_id')->nullable();
            $table->date('tglbukti')->nullable();
        });

        $selectTempList = DB::table('suratpengantar')
            ->from(DB::raw("suratpengantar AS A WITH (READUNCOMMITTED)"))
            ->select([
                'A.supir_id',
                'A.trado_id',
                'A.tglbukti',
            ])
            ->where('A.tglbukti', '>=', $dari)
            ->where('A.tglbukti', '<=', $sampai)
            ->groupBy('A.supir_id', 'A.trado_id', 'A.tglbukti');


        DB::table($Templist)->insertUsing([
            'supir_id',
            'trado_id',
            'tglbukti',
        ], $selectTempList);
        // dd($selectTempList->get());



        $Templistdata = '##Templistdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Templistdata, function ($table) {
            $table->integer('supir_id')->nullable();
            $table->date('tglbukti')->nullable();
            $table->integer('jumlah')->nullable();
        });

        $selectTemplistdata = DB::table($Templist)->from(DB::raw($Templist))
            ->select([
                'supir_id',
                'tglbukti',
                DB::raw('COUNT(supir_id) as jumlah'),
            ])
            ->groupBy('supir_id', 'tglbukti');

        DB::table($Templistdata)->insertUsing([
            'supir_id',
            'tglbukti',
            'jumlah',
        ], $selectTemplistdata);
        // dd(DB::table($Templistdata)->get());

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
        $selectTemplistdata_2 = DB::table($Templistdata)->from(DB::raw($Templistdata . " AS a"))
            ->select(
                'b.namasupir',
                'a.tglbukti',
                'a.jumlah',
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
                DB::raw("'LAPORAN 1 SUPIR LEBIH DARI 1 TRADO' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("supir as b with (readuncommitted)"), 'a.supir_id', 'b.id')
            ->where('a.jumlah', '>=', '1');
        // dd($selectTemplistdata_2->get());
       

        $data = $selectTemplistdata_2->get();
        return $data;


    }

    public function getExport($dari, $sampai)
    {
        
        $Templist = '##Templist' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Templist, function ($table) {
            $table->integer('supir_id')->nullable();
            $table->integer('trado_id')->nullable();
            $table->date('tglbukti')->nullable();
        });

        $selectTempList = DB::table('suratpengantar')
            ->from(DB::raw("suratpengantar AS A WITH (READUNCOMMITTED)"))
            ->select([
                'A.supir_id',
                'A.trado_id',
                'A.tglbukti',
            ])
            ->where('A.tglbukti', '>=', $dari)
            ->where('A.tglbukti', '<=', $sampai)
            ->groupBy('A.supir_id', 'A.trado_id', 'A.tglbukti');


        DB::table($Templist)->insertUsing([
            'supir_id',
            'trado_id',
            'tglbukti',
        ], $selectTempList);
        // dd($selectTempList->get());



        $Templistdata = '##Templistdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Templistdata, function ($table) {
            $table->integer('supir_id')->nullable();
            $table->date('tglbukti')->nullable();
            $table->integer('jumlah')->nullable();
        });

        $selectTemplistdata = DB::table($Templist)->from(DB::raw($Templist))
            ->select([
                'supir_id',
                'tglbukti',
                DB::raw('COUNT(supir_id) as jumlah'),
            ])
            ->groupBy('supir_id', 'tglbukti');

        DB::table($Templistdata)->insertUsing([
            'supir_id',
            'tglbukti',
            'jumlah',
        ], $selectTemplistdata);
        // dd(DB::table($Templistdata)->get());

        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
        ->select('text')
        ->where('grp', 'DISETUJUI')
        ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

    $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
        ->select('text')
        ->where('grp', 'DIPERIKSA')
        ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';

        $selectTemplistdata_2 = DB::table($Templistdata)->from(DB::raw($Templistdata . " AS a"))
            ->select(
                'b.namasupir',
                'a.tglbukti',
                'a.jumlah',
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
            )
            ->leftJoin(DB::raw("supir as b with (readuncommitted)"), 'a.supir_id', 'b.id')
            ->where('a.jumlah', '>=', '1');
        // dd($selectTemplistdata_2->get());
       

        $data = $selectTemplistdata_2->get();
        return $data;



    }
}
