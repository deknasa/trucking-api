<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GajiSupirPelunasanPinjaman extends MyModel
{
    use HasFactory;

    protected $table = 'gajisupirpelunasanpinjaman';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function getPinjamanPribadi($nobukti, $supir_id)
    {
        $this->setRequestParameters();

        $temp = $this->createTempPinjamanPribadi($nobukti, $supir_id);
        $query = DB::table('pengeluarantruckingdetail')
        ->from(
            DB::raw("pengeluarantruckingdetail with (readuncommitted)")
        )
        ->select(DB::raw("pengeluarantruckingdetail.id,pengeluarantruckingdetail.nobukti,pengeluarantruckingdetail.nominal as sisaawal ,pengeluarantruckingdetail.keterangan, $temp.nominal, $temp.gajisupir_id,
        (SELECT (pengeluarantruckingdetail.nominal - COALESCE(SUM(gajisupirpelunasanpinjaman.nominal),0))
            FROM gajisupirpelunasanpinjaman WHERE pengeluarantruckingdetail.nobukti= gajisupirpelunasanpinjaman.pengeluarantrucking_nobukti) AS sisa"))
        ->leftJoin(DB::raw("$temp with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', $temp . ".pengeluarantrucking_nobukti")
        ->whereRaw("pengeluarantruckingdetail.supir_id = $supir_id");

        return $query->get();
    }
    
    public function createTempPinjamanPribadi($nobukti, $supir_id)
    {
        $temp = '##tempPribadi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('gajisupirpelunasanpinjaman')->from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
            ->select(DB::raw("nominal, pengeluarantrucking_nobukti,gajisupir_id"))
            ->whereRaw("gajisupir_nobukti = '$nobukti'")
            ->whereRaw("supir_id = $supir_id");

        Schema::create($temp, function ($table) {
            $table->bigInteger('nominal');
            $table->string('pengeluarantrucking_nobukti');
            $table->bigInteger('gajisupir_id');
        });

        $tes = DB::table($temp)->insertUsing(['nominal','pengeluarantrucking_nobukti','gajisupir_id'], $fetch);

        return $temp;
    }
    

    public function getPinjamanSemua($nobukti)
    {
        $this->setRequestParameters();

        $temp = $this->createTempPinjamanSemua($nobukti);
        $query = DB::table('pengeluarantruckingdetail')
        ->from(
            DB::raw("pengeluarantruckingdetail with (readuncommitted)")
        )
        ->select(DB::raw("pengeluarantruckingdetail.id,pengeluarantruckingdetail.nobukti,pengeluarantruckingdetail.nominal as sisaawal ,pengeluarantruckingdetail.keterangan, $temp.nominal, $temp.gajisupir_id,
        (SELECT (pengeluarantruckingdetail.nominal - COALESCE(SUM(gajisupirpelunasanpinjaman.nominal),0))
            FROM gajisupirpelunasanpinjaman WHERE pengeluarantruckingdetail.nobukti= gajisupirpelunasanpinjaman.pengeluarantrucking_nobukti) AS sisa"))
        ->leftJoin(DB::raw("$temp with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', $temp . ".pengeluarantrucking_nobukti")
        ->whereRaw("pengeluarantruckingdetail.supir_id = 0");

        return $query->get();
    }
    
    public function createTempPinjamanSemua($nobukti)
    {
        $temp = '##tempPribadi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('gajisupirpelunasanpinjaman')->from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
            ->select(DB::raw("nominal, pengeluarantrucking_nobukti,gajisupir_id"))
            ->whereRaw("gajisupir_nobukti = '$nobukti'")
            ->whereRaw("supir_id = 0");

        Schema::create($temp, function ($table) {
            $table->bigInteger('nominal');
            $table->string('pengeluarantrucking_nobukti');
            $table->bigInteger('gajisupir_id');
        });

        $tes = DB::table($temp)->insertUsing(['nominal','pengeluarantrucking_nobukti','gajisupir_id'], $fetch);


        return $temp;
    }
}
