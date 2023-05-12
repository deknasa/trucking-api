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

        $tempPinjaman = $this->createTempPinjamanPribadi($nobukti, $supir_id);
        $tempPengeluaran = $this->createTempPengeluaranPribadi($supir_id);

        $temp = '##tempGet' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetch = DB::table($tempPinjaman)->from(DB::raw("$tempPinjaman with (readuncommitted)"))
            ->select(DB::raw("tglbukti,pengeluarantrucking_nobukti as nobukti,sisaawal,keterangan,gajisupir_id,nominal,sisa"));

        Schema::create($temp, function ($table) {
            $table->date('tglbukti')->nullable();
            $table->string('nobukti');
            $table->bigInteger('sisaawal')->nullable();
            $table->string('keterangan')->nullable();
            $table->bigInteger('gajisupir_id')->nullable();
            $table->bigInteger('nominal')->nullable();
            $table->bigInteger('sisa')->nullable();
        });

        DB::table($temp)->insertUsing(['tglbukti', 'nobukti', 'sisaawal', 'keterangan', 'gajisupir_id', 'nominal', 'sisa'], $fetch);

        $fetchPengeluaran = DB::table($tempPengeluaran)->from(DB::raw("$tempPengeluaran as A with (readuncommitted)"))
            ->select(DB::raw("A.tglbukti,A.nobukti,A.sisaawal,A.keterangan,null as gajisupir_id, null as nominal,A.sisa"))
            ->leftJoin(DB::raw("$tempPinjaman as B with (readuncommitted)"), "A.nobukti", "B.pengeluarantrucking_nobukti")
            ->whereRaw("isnull(b.pengeluarantrucking_nobukti,'') = ''")
            ->where('A.sisa', '>', '0');

        DB::table($temp)->insertUsing(['tglbukti', 'nobukti', 'sisaawal', 'keterangan', 'gajisupir_id', 'nominal', 'sisa'], $fetchPengeluaran);

        $data = DB::table($temp)
            ->select(DB::raw("row_number() Over(Order By $temp.nobukti) as pinjPribadi_id,tglbukti,nobukti as pinjPribadi_nobukti,sisaawal,keterangan as pinjPribadi_keterangan,nominal as nominalPP,gajisupir_id,sisa as pinjPribadi_sisa"))
            ->orderBy("$temp.tglbukti", 'asc')
            ->orderBy("$temp.nobukti", 'asc')
            ->get();

        return $data;
    }

    public function createTempPengeluaranPribadi($supir_id)
    {
        $temp = '##tempPengeluaran' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetchSisa = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail with (readuncommitted)")
            )->select(DB::raw("pengeluarantruckingheader.tglbukti,pengeluarantruckingdetail.nobukti, (SELECT (pengeluarantruckingdetail.nominal - COALESCE(SUM(gajisupirpelunasanpinjaman.nominal),0))
                FROM gajisupirpelunasanpinjaman WHERE pengeluarantruckingdetail.nobukti= gajisupirpelunasanpinjaman.pengeluarantrucking_nobukti) AS sisaawal,pengeluarantruckingdetail.keterangan,
            (SELECT (pengeluarantruckingdetail.nominal - COALESCE(SUM(gajisupirpelunasanpinjaman.nominal),0))
                FROM gajisupirpelunasanpinjaman WHERE pengeluarantruckingdetail.nobukti= gajisupirpelunasanpinjaman.pengeluarantrucking_nobukti) AS sisa"))
            ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', 'pengeluarantruckingheader.nobukti')
            ->whereRaw("pengeluarantruckingdetail.supir_id = $supir_id")
            ->orderBy('pengeluarantruckingheader.tglbukti', 'asc')
            ->orderBy('pengeluarantruckingdetail.nobukti', 'asc');

        Schema::create($temp, function ($table) {

            $table->date('tglbukti');
            $table->string('nobukti');
            $table->bigInteger('sisaawal');
            $table->string('keterangan');
            $table->bigInteger('sisa');
        });

        DB::table($temp)->insertUsing(['tglbukti', 'nobukti', 'sisaawal', 'keterangan', 'sisa'], $fetchSisa);

        return $temp;
    }

    public function createTempPinjamanPribadi($nobukti, $supir_id)
    {
        $temp = '##tempPribadi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('gajisupirpelunasanpinjaman')->from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
            ->select(DB::raw("pengeluarantruckingheader.tglbukti,gajisupirpelunasanpinjaman.pengeluarantrucking_nobukti, (SELECT (pengeluarantruckingdetail.nominal - COALESCE(SUM(gajisupirpelunasanpinjaman.nominal),0))
            FROM gajisupirpelunasanpinjaman WHERE gajisupirpelunasanpinjaman.pengeluarantrucking_nobukti=pengeluarantruckingdetail.nobukti 
            and gajisupirpelunasanpinjaman.gajisupir_nobukti! = '$nobukti') as sisaawal, pengeluarantruckingdetail.keterangan,gajisupirpelunasanpinjaman.gajisupir_id, gajisupirpelunasanpinjaman.nominal, (SELECT (pengeluarantruckingdetail.nominal - COALESCE(SUM(gajisupirpelunasanpinjaman.nominal),0))
        FROM gajisupirpelunasanpinjaman WHERE gajisupirpelunasanpinjaman.pengeluarantrucking_nobukti=pengeluarantruckingdetail.nobukti) AS sisa"))
            ->join(DB::raw("pengeluarantruckingdetail with (readuncommitted)"), 'gajisupirpelunasanpinjaman.pengeluarantrucking_nobukti', 'pengeluarantruckingdetail.nobukti')
            ->join(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', 'pengeluarantruckingheader.nobukti')
            ->whereRaw("gajisupirpelunasanpinjaman.gajisupir_nobukti = '$nobukti'")
            ->whereRaw("gajisupirpelunasanpinjaman.supir_id = $supir_id");

        Schema::create($temp, function ($table) {
            $table->date('tglbukti');
            $table->string('pengeluarantrucking_nobukti');
            $table->bigInteger('sisaawal');
            $table->string('keterangan');
            $table->bigInteger('gajisupir_id');
            $table->bigInteger('nominal');
            $table->bigInteger('sisa');
        });

        $tes = DB::table($temp)->insertUsing(['tglbukti', 'pengeluarantrucking_nobukti', 'sisaawal', 'keterangan', 'gajisupir_id', 'nominal', 'sisa'], $fetch);

        return $temp;
    }


    public function getPinjamanSemua($nobukti)
    {
        $this->setRequestParameters();

        $tempPinjaman = $this->createTempPinjamanSemua($nobukti);
        $tempPengeluaran = $this->createTempPengeluaran();

        $temp = '##tempGet' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetch = DB::table($tempPinjaman)->from(DB::raw("$tempPinjaman with (readuncommitted)"))
            ->select(DB::raw("tglbukti,pengeluarantrucking_nobukti as nobukti,sisaawal,keterangan,gajisupir_id,nominal,sisa"));

        Schema::create($temp, function ($table) {
            $table->date('tglbukti')->nullable();
            $table->string('nobukti');
            $table->bigInteger('sisaawal')->nullable();
            $table->string('keterangan')->nullable();
            $table->bigInteger('gajisupir_id')->nullable();
            $table->bigInteger('nominal')->nullable();
            $table->bigInteger('sisa')->nullable();
        });

        DB::table($temp)->insertUsing(['tglbukti', 'nobukti', 'sisaawal', 'keterangan', 'gajisupir_id', 'nominal', 'sisa'], $fetch);

        $fetchPengeluaran = DB::table($tempPengeluaran)->from(DB::raw("$tempPengeluaran as A with (readuncommitted)"))
            ->select(DB::raw("A.tglbukti,A.nobukti,A.sisaawal,A.keterangan,null as gajisupir_id, null as nominal,A.sisa"))
            ->leftJoin(DB::raw("$tempPinjaman as B with (readuncommitted)"), "A.nobukti", "B.pengeluarantrucking_nobukti")
            ->whereRaw("isnull(b.pengeluarantrucking_nobukti,'') = ''")
            ->where('A.sisa', '>', '0');

        DB::table($temp)->insertUsing(['tglbukti', 'nobukti', 'sisaawal', 'keterangan', 'gajisupir_id', 'nominal', 'sisa'], $fetchPengeluaran);

        $data = DB::table($temp)
            ->select(DB::raw("row_number() Over(Order By $temp.nobukti) as id,tglbukti,nobukti as pinjSemua_nobukti,sisaawal,keterangan as pinjSemua_keterangan,nominal as nominalPS,gajisupir_id,sisa as pinjSemua_sisa,'SEMUA' as pinjSemua_supir"))
            ->orderBy("$temp.tglbukti", 'asc')
            ->orderBy("$temp.nobukti", 'asc')
            ->get();

        return $data;
    }

    public function createTempPengeluaran()
    {
        $temp = '##tempPengeluaran' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetchSisa = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail with (readuncommitted)")
            )->select(DB::raw("pengeluarantruckingheader.tglbukti,pengeluarantruckingdetail.nobukti, (SELECT (pengeluarantruckingdetail.nominal - COALESCE(SUM(gajisupirpelunasanpinjaman.nominal),0))
            FROM gajisupirpelunasanpinjaman WHERE pengeluarantruckingdetail.nobukti= gajisupirpelunasanpinjaman.pengeluarantrucking_nobukti) AS sisaawal,pengeluarantruckingdetail.keterangan,
            (SELECT (pengeluarantruckingdetail.nominal - COALESCE(SUM(gajisupirpelunasanpinjaman.nominal),0))
                FROM gajisupirpelunasanpinjaman WHERE pengeluarantruckingdetail.nobukti= gajisupirpelunasanpinjaman.pengeluarantrucking_nobukti) AS sisa"))
            ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', 'pengeluarantruckingheader.nobukti')
            ->whereRaw("pengeluarantruckingdetail.supir_id = 0")
            ->orderBy('pengeluarantruckingheader.tglbukti', 'asc')
            ->orderBy('pengeluarantruckingdetail.nobukti', 'asc');

        Schema::create($temp, function ($table) {

            $table->date('tglbukti');
            $table->string('nobukti');
            $table->bigInteger('sisaawal');
            $table->string('keterangan');
            $table->bigInteger('sisa');
        });

        DB::table($temp)->insertUsing(['tglbukti', 'nobukti', 'sisaawal', 'keterangan', 'sisa'], $fetchSisa);

        return $temp;
    }

    public function createTempPinjamanSemua($nobukti)
    {
        $temp = '##tempPribadi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('gajisupirpelunasanpinjaman')->from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
            ->select(DB::raw("pengeluarantruckingheader.tglbukti,gajisupirpelunasanpinjaman.pengeluarantrucking_nobukti, (SELECT (pengeluarantruckingdetail.nominal - COALESCE(SUM(gajisupirpelunasanpinjaman.nominal),0))
            FROM gajisupirpelunasanpinjaman WHERE gajisupirpelunasanpinjaman.pengeluarantrucking_nobukti=pengeluarantruckingdetail.nobukti 
            and gajisupirpelunasanpinjaman.gajisupir_nobukti! = '$nobukti') as sisaawal, pengeluarantruckingdetail.keterangan,gajisupirpelunasanpinjaman.gajisupir_id, gajisupirpelunasanpinjaman.nominal, (SELECT (pengeluarantruckingdetail.nominal - COALESCE(SUM(gajisupirpelunasanpinjaman.nominal),0))
            FROM gajisupirpelunasanpinjaman WHERE gajisupirpelunasanpinjaman.pengeluarantrucking_nobukti=pengeluarantruckingdetail.nobukti) AS sisa"))
            ->join(DB::raw("pengeluarantruckingdetail with (readuncommitted)"), 'gajisupirpelunasanpinjaman.pengeluarantrucking_nobukti', 'pengeluarantruckingdetail.nobukti')
            ->join(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', 'pengeluarantruckingheader.nobukti')
            ->whereRaw("gajisupirpelunasanpinjaman.gajisupir_nobukti = '$nobukti'")
            ->whereRaw("gajisupirpelunasanpinjaman.supir_id = 0");

        Schema::create($temp, function ($table) {
            $table->date('tglbukti');
            $table->string('pengeluarantrucking_nobukti');
            $table->bigInteger('sisaawal');
            $table->string('keterangan');
            $table->bigInteger('gajisupir_id');
            $table->bigInteger('nominal');
            $table->bigInteger('sisa');
        });

        $tes = DB::table($temp)->insertUsing(['tglbukti', 'pengeluarantrucking_nobukti', 'sisaawal', 'keterangan', 'gajisupir_id', 'nominal', 'sisa'], $fetch);

        return $temp;
    }

    public function getDeletePinjSemua($nobukti)
    {
        $tempPinjaman = $this->createTempPinjamanSemua($nobukti);
        $data = DB::table($tempPinjaman)->from(DB::raw("$tempPinjaman with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $tempPinjaman.pengeluarantrucking_nobukti) as id,gajisupir_id, 'SEMUA' as pinjSemua_supir,pengeluarantrucking_nobukti as pinjSemua_nobukti,keterangan as pinjSemua_keterangan,sisa as pinjSemua_sisa,nominal as nominalPS"))
            ->get();

        return $data;
    }
    public function getDeletePinjPribadi($nobukti, $supirId)
    {

        $tempPinjaman = $this->createTempPinjamanPribadi($nobukti, $supirId);

        $data = DB::table($tempPinjaman)->from(DB::raw("$tempPinjaman with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $tempPinjaman.pengeluarantrucking_nobukti) as pinjPribadi_id,gajisupir_id, pengeluarantrucking_nobukti as pinjPribadi_nobukti,keterangan as pinjPribadi_keterangan,sisa as pinjPribadi_sisa,nominal as nominalPP"))
            ->get();
        return $data;
    }
}
