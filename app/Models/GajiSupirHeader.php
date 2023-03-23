<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GajiSupirHeader extends MyModel
{
    use HasFactory;

    protected $table = 'gajisupirheader';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];
    public function cekvalidasiaksi($nobukti)
    {
        $rekap = DB::table('prosesgajisupirdetail')
            ->from(
                DB::raw("prosesgajisupirdetail as a with (readuncommitted)")
            )
            ->select(
                'a.gajisupir_nobukti'
            )
            ->where('a.gajisupir_nobukti', '=', $nobukti)
            ->first();
        if (isset($rekap)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'PROSES GAJI SUPIR',
                'kodeerror' => 'SATL'
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

        $query = DB::table($this->table)->from(DB::raw("gajisupirheader with (readuncommitted)"))
            ->select(
                'gajisupirheader.id',
                'gajisupirheader.nobukti',
                'gajisupirheader.tglbukti',
                'supir.namasupir as supir_id',
                // 'gajisupirheader.keterangan',
                'gajisupirheader.nominal',
                'gajisupirheader.tgldari',
                'gajisupirheader.tglsampai',
                'gajisupirheader.total',
                'gajisupirheader.uangjalan',
                'gajisupirheader.bbm',
                'gajisupirheader.deposito',
                'gajisupirheader.potonganpinjaman',
                'gajisupirheader.potonganpinjamansemua',
                'gajisupirheader.uangmakanharian',
                'parameter.memo as statuscetak',
                "parameter.text as statuscetak_text",
                'gajisupirheader.userbukacetak',
                'gajisupirheader.jumlahcetak',
                DB::raw('(case when (year(gajisupirheader.tglbukacetak) <= 2000) then null else gajisupirheader.tglbukacetak end ) as tglbukacetak'),
                'gajisupirheader.modifiedby',
                'gajisupirheader.created_at',
                'gajisupirheader.updated_at',
            )
            ->whereBetween($this->table.'.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'gajisupirheader.statuscetak', 'parameter.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'gajisupirheader.supir_id', 'supir.id');
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }
    public function findAll($id)
    {

        $query = DB::table('gajisupirheader')->from(DB::raw("gajisupirheader with (readuncommitted)"))
            ->select(
                'gajisupirheader.*',
                'supir.namasupir as supir',

            )
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'gajisupirheader.supir_id', 'supir.id')
            ->where('gajisupirheader.id', $id);

        $data = $query->first();

        return $data;
    }

    public function getTrip($supirId, $tglDari, $tglSampai)
    {

        $this->setRequestParameters();
        $sp = $this->createTempGetTrip($supirId, $tglDari, $tglSampai);
        $query = DB::table($sp)
            ->select(
                DB::raw("row_number() Over(Order By $sp.nobuktitrip) as id"),
                DB::raw("(case when $sp.nobuktitrip IS NULL then '-' else $sp.nobuktitrip end) as nobuktitrip"),
                "$sp.tglbuktisp",
                "$sp.trado_id",
                "$sp.dari_id",
                "$sp.sampai_id",
                "$sp.nocont",
                "$sp.nosp",
                DB::raw("(case when $sp.ritasi_nobukti IS NULL then '-' else $sp.ritasi_nobukti end) as ritasi_nobukti"),
                DB::raw("(case when $sp.gajisupir IS NULL then 0 else $sp.gajisupir end) as gajisupir"),
                DB::raw("(case when $sp.gajikenek IS NULL then 0 else $sp.gajikenek end) as gajikenek"),
                DB::raw("(case when $sp.komisisupir IS NULL then 0 else $sp.komisisupir end) as komisisupir"),
                DB::raw("(case when $sp.tolsupir IS NULL then 0 else $sp.tolsupir end) as tolsupir"),
                DB::raw("(case when $sp.upahritasi IS NULL then 0 else $sp.upahritasi end) as upahritasi"),
                DB::raw("(case when $sp.biayaextra IS NULL then 0 else $sp.biayaextra end) as biayaextra"),
                "parameter.text as statusritasi",
                DB::raw("(case when $sp.keteranganbiaya IS NULL then '-' else $sp.keteranganbiaya end) as keteranganbiaya")
            )->leftJoin(DB::raw("parameter with (readuncommitted)"), 'parameter.id', $sp . '.statusritasi');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $query->orderBy($sp . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        $this->filterTrip($query, $sp);
        $this->paginate($query);
        $data = $query->get();

        // dd($query->get());
        $this->totalGajiSupir = $query->sum('gajisupir');
        $this->totalGajiKenek = $query->sum('gajikenek');
        $this->totalKomisiSupir = $query->sum('komisisupir');
        $this->totalUpahRitasi = $query->sum('upahritasi');
        $this->totalBiayaExtra = $query->sum('biayaextra');
        $this->totalTolSupir = $query->sum('tolsupir');
        return $data;
    }

    public function createTempGetTrip($supirId, $tglDari, $tglSampai)
    {
        $temp = '##tempSP' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select(
                'suratpengantar.nobukti as nobuktitrip',
                'suratpengantar.tglbukti as tglbuktisp',
                'trado.kodetrado as trado_id',
                'kotaDari.keterangan as dari_id',
                'kotaSampai.keterangan as sampai_id',
                'suratpengantar.nocont',
                'suratpengantar.nosp',
                DB::raw("(case when ritasi.suratpengantar_urutke > 1 then 0 else suratpengantar.gajisupir end) as gajisupir"),
                DB::raw("(case when ritasi.suratpengantar_urutke > 1 then 0 else suratpengantar.gajikenek end) as gajikenek"),
                DB::raw("(case when ritasi.suratpengantar_urutke > 1 then 0 else suratpengantar.komisisupir end) as komisisupir"),
                DB::raw("(case when ritasi.suratpengantar_urutke > 1 then 0 else suratpengantar.tolsupir end) as tolsupir"),
                'ritasi.gaji as upahritasi',
                'ritasi.nobukti as ritasi_nobukti',
                'ritasi.statusritasi',
                'suratpengantarbiayatambahan.nominal as biayaextra',
                'suratpengantarbiayatambahan.keteranganbiaya'
            )
            ->leftJoin(DB::raw("kota as kotaDari with (readuncommitted)"), 'suratpengantar.dari_id', 'kotaDari.id')
            ->leftJoin(DB::raw("kota as kotaSampai with (readuncommitted)"), 'suratpengantar.sampai_id', 'kotaSampai.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'suratpengantar.trado_id', 'trado.id')
            ->leftJoin(DB::raw("ritasi with (readuncommitted)"), 'suratpengantar.nobukti', 'ritasi.suratpengantar_nobukti')
            ->leftJoin(DB::raw("suratpengantarbiayatambahan with (readuncommitted)"), 'suratpengantar.id', 'suratpengantarbiayatambahan.suratpengantar_id')
            ->where('suratpengantar.supir_id', $supirId)
            ->where('suratpengantar.tglbukti', '>=', $tglDari)
            ->where('suratpengantar.tglbukti', '<=', $tglSampai)
            ->whereRaw("suratpengantar.nobukti not in(select suratpengantar_nobukti from gajisupirdetail)");

        Schema::create($temp, function ($table) {
            $table->string('nobuktitrip')->nullable();
            $table->date('tglbuktisp')->nullable();
            $table->string('trado_id');
            $table->string('dari_id');
            $table->string('sampai_id');
            $table->string('nocont')->nullable();
            $table->string('nosp')->nullable();
            $table->bigInteger('gajisupir')->nullable();
            $table->bigInteger('gajikenek')->nullable();
            $table->bigInteger('komisisupir')->nullable();
            $table->bigInteger('tolsupir')->nullable();
            $table->bigInteger('upahritasi')->nullable();
            $table->string('ritasi_nobukti')->nullable();
            $table->bigInteger('statusritasi')->nullable();
            $table->bigInteger('biayaextra')->nullable();
            $table->string('keteranganbiaya')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobuktitrip', 'tglbuktisp', 'trado_id', 'dari_id', 'sampai_id', 'nocont', 'nosp', 'gajisupir', 'gajikenek', 'komisisupir', 'tolsupir', 'upahritasi', 'ritasi_nobukti', 'statusritasi', 'biayaextra', 'keteranganbiaya'], $fetch);

        $fetch = Ritasi::from(DB::raw("ritasi with (readuncommitted)"))
            ->select(
                DB::raw("ritasi.tglbukti as tglbuktisp,trado.kodetrado as trado_id,kotaDari.keterangan as dari_id,kotaSampai.keterangan as sampai_id, ritasi.gaji as upahritasi, ritasi.nobukti as ritasi_nobukti,ritasi.statusritasi")
            )
            ->leftJoin(DB::raw("kota as kotaDari with (readuncommitted)"), 'ritasi.dari_id', 'kotaDari.id')
            ->leftJoin(DB::raw("kota as kotaSampai with (readuncommitted)"), 'ritasi.sampai_id', 'kotaSampai.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'ritasi.trado_id', 'trado.id')
            ->where('ritasi.supir_id', $supirId)
            ->where('ritasi.tglbukti', '>=', $tglDari)
            ->where('ritasi.tglbukti', '<=', $tglSampai)
            ->whereRaw("ritasi.suratpengantar_nobukti = ''")
            ->whereRaw("ritasi.nobukti not in(select ritasi_nobukti from gajisupirdetail)");
        $tes = DB::table($temp)->insertUsing(['tglbuktisp', 'trado_id', 'dari_id', 'sampai_id', 'upahritasi', 'ritasi_nobukti', 'statusritasi'], $fetch);

        return $temp;
    }

    public function getEditTrip($gajiId)
    {
        $this->setRequestParameters();
        $sp = $this->createTempEdit($gajiId);
        $query = DB::table($sp)
            ->select(

                DB::raw("row_number() Over(Order By $sp.nobuktitrip) as id"),
                DB::raw("(case when $sp.nobuktitrip IS NULL then '-' else $sp.nobuktitrip end) as nobuktitrip"),
                "$sp.tglbuktisp",
                "$sp.trado_id",
                "$sp.dari_id",
                "$sp.sampai_id",
                "$sp.nocont",
                "$sp.nosp",
                DB::raw("(case when $sp.ritasi_nobukti IS NULL then '-' else $sp.ritasi_nobukti end) as ritasi_nobukti"),
                DB::raw("(case when $sp.gajisupir IS NULL then 0 else $sp.gajisupir end) as gajisupir"),
                DB::raw("(case when $sp.gajikenek IS NULL then 0 else $sp.gajikenek end) as gajikenek"),
                DB::raw("(case when $sp.komisisupir IS NULL then 0 else $sp.komisisupir end) as komisisupir"),
                DB::raw("(case when $sp.tolsupir IS NULL then 0 else $sp.tolsupir end) as tolsupir"),
                DB::raw("(case when $sp.upahritasi IS NULL then 0 else $sp.upahritasi end) as upahritasi"),
                DB::raw("(case when $sp.biayaextra IS NULL then 0 else $sp.biayaextra end) as biayaextra"),
                "parameter.text as statusritasi",
                DB::raw("(case when $sp.keteranganbiaya IS NULL then '-' else $sp.keteranganbiaya end) as keteranganbiaya")
            )->leftJoin(DB::raw("parameter with (readuncommitted)"), 'parameter.id', $sp . '.statusritasi');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $query->orderBy($sp . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        $this->filterTrip($query, $sp);
        $this->paginate($query);
        $data = $query->get();

        // dd($query->get());
        $this->totalGajiSupir = $query->sum('gajisupir');
        $this->totalGajiKenek = $query->sum('gajikenek');
        $this->totalKomisiSupir = $query->sum('komisisupir');
        $this->totalUpahRitasi = $query->sum('upahritasi');
        $this->totalBiayaExtra = $query->sum('biayaextra');
        $this->totalTolSupir = $query->sum('tolsupir');
        return $data;
    }

    public function createTempEdit($gajiId)
    {

        $temp = '##tempRIC' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('gajisupirdetail')->from(DB::raw("gajisupirdetail with (readuncommitted)"))
            ->select(
                'gajisupirdetail.suratpengantar_nobukti as nobuktitrip',
                'suratpengantar.tglbukti as tglbuktisp',
                'trado.kodetrado as trado_id',
                'kotaDari.keterangan as dari_id',
                'kotaSampai.keterangan as sampai_id',
                'suratpengantar.nocont',
                'suratpengantar.nosp',
                'gajisupirdetail.gajisupir',
                'gajisupirdetail.gajikenek',
                'gajisupirdetail.komisisupir',
                'gajisupirdetail.tolsupir',
                'gajisupirdetail.gajiritasi as upahritasi',
                'gajisupirdetail.ritasi_nobukti',
                'ritasi.statusritasi',
                'gajisupirdetail.biayatambahan as biayaextra',
                'gajisupirdetail.keteranganbiayatambahan as keteranganbiaya'
            )
            ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), 'gajisupirdetail.suratpengantar_nobukti', 'suratpengantar.nobukti')
            ->leftJoin(DB::raw("kota as kotaDari with (readuncommitted)"), 'suratpengantar.dari_id', 'kotaDari.id')
            ->leftJoin(DB::raw("kota as kotaSampai with (readuncommitted)"), 'suratpengantar.sampai_id', 'kotaSampai.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'suratpengantar.trado_id', 'trado.id')
            ->leftJoin(DB::raw("ritasi with (readuncommitted)"), 'gajisupirdetail.ritasi_nobukti', 'ritasi.nobukti')
            ->where('gajisupirdetail.suratpengantar_nobukti', '!=', '-')
            ->where('gajisupirdetail.gajisupir_id', $gajiId);


        Schema::create($temp, function ($table) {
            $table->string('nobuktitrip')->nullable();
            $table->date('tglbuktisp')->nullable()->nullable();
            $table->string('trado_id')->nullable();
            $table->string('dari_id')->nullable();
            $table->string('sampai_id')->nullable();
            $table->string('nocont')->nullable();
            $table->string('nosp')->nullable();
            $table->bigInteger('gajisupir')->nullable();
            $table->bigInteger('gajikenek')->nullable();
            $table->bigInteger('komisisupir')->nullable();
            $table->bigInteger('tolsupir')->nullable();
            $table->bigInteger('upahritasi')->nullable();
            $table->string('ritasi_nobukti')->nullable();
            $table->bigInteger('statusritasi')->nullable();
            $table->bigInteger('biayaextra')->nullable();
            $table->string('keteranganbiaya')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobuktitrip', 'tglbuktisp', 'trado_id', 'dari_id', 'sampai_id', 'nocont', 'nosp', 'gajisupir', 'gajikenek', 'komisisupir', 'tolsupir', 'upahritasi', 'ritasi_nobukti', 'statusritasi', 'biayaextra', 'keteranganbiaya'], $fetch);

        $fetch = DB::table('gajisupirdetail')->from(DB::raw("gajisupirdetail with (readuncommitted)"))
            ->select(
                'gajisupirdetail.suratpengantar_nobukti as nobuktitrip',
                'ritasi.tglbukti as tglbuktisp',
                'trado.kodetrado as trado_id',
                'kotaDari.keterangan as dari_id',
                'kotaSampai.keterangan as sampai_id',
                'gajisupirdetail.gajisupir',
                'gajisupirdetail.gajikenek',
                'gajisupirdetail.komisisupir',
                'gajisupirdetail.tolsupir',
                'gajisupirdetail.gajiritasi as upahritasi',
                'gajisupirdetail.ritasi_nobukti',
                'ritasi.statusritasi',
                'gajisupirdetail.biayatambahan as biayaextra',
                'gajisupirdetail.keteranganbiayatambahan as keteranganbiaya'
            )
            ->leftJoin(DB::raw("ritasi with (readuncommitted)"), 'gajisupirdetail.ritasi_nobukti', 'ritasi.nobukti')
            ->leftJoin(DB::raw("kota as kotaDari with (readuncommitted)"), 'ritasi.dari_id', 'kotaDari.id')
            ->leftJoin(DB::raw("kota as kotaSampai with (readuncommitted)"), 'ritasi.sampai_id', 'kotaSampai.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'ritasi.trado_id', 'trado.id')
            ->where('gajisupirdetail.suratpengantar_nobukti', '-')
            ->where('gajisupirdetail.gajisupir_id', $gajiId);

        $tes = DB::table($temp)->insertUsing(['nobuktitrip', 'tglbuktisp', 'trado_id', 'dari_id', 'sampai_id', 'gajisupir', 'gajikenek', 'komisisupir', 'tolsupir', 'upahritasi', 'ritasi_nobukti', 'statusritasi', 'biayaextra', 'keteranganbiaya'], $fetch);

        return $temp;
    }
    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw("
            $this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            'supir.namasupir as supir_id',
            $this->table.nominal,
            $this->table.tgldari,
            $this->table.tglsampai,
            $this->table.total,
            'parameter.text as statuscetak',
            $this->table.userbukacetak,
            $this->table.tglbukacetak,
            $this->table.jumlahcetak,
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at
            ")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'gajisupirheader.statuscetak', 'parameter.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'gajisupirheader.supir_id', 'supir.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('supir_id', 1000)->nullable();
            $table->bigInteger('nominal')->nullable();
            $table->date('tgldari')->nullable();
            $table->date('tglsampai')->nullable();
            $table->bigInteger('total')->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'supir_id',  'nominal', 'tgldari', 'tglsampai', 'total', 'statuscetak', 'userbukacetak', 'tglbukacetak', 'jumlahcetak', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function getPinjSemua()
    {
        $query = PengeluaranTruckingDetail::from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
            ->select(DB::raw(" pengeluarantruckingdetail.nobukti,pengeluarantruckingdetail.id, pengeluarantruckingdetail.supir_id,pengeluarantruckingdetail.keterangan, (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"))
            ->distinct('pengeluarantruckingdetail.nobukti')
            ->leftJoin(DB::raw("penerimaantruckingdetail with (readuncommitted)"), 'penerimaantruckingdetail.pengeluarantruckingheader_nobukti', 'pengeluarantruckingdetail.nobukti')
            ->where("pengeluarantruckingdetail.supir_id", 0);

        return $query->get();
    }

    public function getPinjPribadi($supir_id)
    {
        $query = PengeluaranTruckingDetail::from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
            ->select(DB::raw(" pengeluarantruckingdetail.nobukti,pengeluarantruckingdetail.id, pengeluarantruckingdetail.keterangan, (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"))
            ->leftJoin(DB::raw("penerimaantruckingdetail with (readuncommitted)"), 'penerimaantruckingdetail.pengeluarantruckingheader_nobukti', 'pengeluarantruckingdetail.nobukti')
            ->where("pengeluarantruckingdetail.nobukti",  'LIKE', "%PJT%")
            ->whereRaw("pengeluarantruckingdetail.nobukti not in (select pengeluarantruckingheader_nobukti from penerimaantruckingdetail)")
            ->where("pengeluarantruckingdetail.supir_id", $supir_id);

        return $query->get();
    }

    public function getUangJalan($supir_id, $dari, $sampai)
    {
        $query = AbsensiSupirHeader::from(DB::raw("absensisupirheader with (readuncommitted)"))
            ->select(DB::raw("SUM(absensisupirdetail.uangjalan) as uangjalan"))
            ->leftJoin(DB::raw("absensisupirdetail with (readuncommitted)"), 'absensisupirheader.nobukti', 'absensisupirdetail.nobukti')
            ->whereRaw("absensisupirheader.tglbukti >= '$dari'")
            ->whereRaw("absensisupirheader.tglbukti <= '$sampai'")
            ->whereRaw("absensisupirdetail.supir_id = $supir_id");

        return $query->first();
    }

    public function getAllEditTrip($gajiId, $supir_id, $dari, $sampai)
    {
        $this->setRequestParameters();
        $tempRIC = $this->createTempGetRIC($gajiId, $supir_id, $dari, $sampai);
        $query = DB::table($tempRIC)
            ->select(
                DB::raw("row_number() Over(Order By $tempRIC.nobuktitrip) as id"),
                DB::raw("(case when $tempRIC.nobuktitrip IS NULL then '-' else $tempRIC.nobuktitrip end) as nobuktitrip"),
                "$tempRIC.tglbuktisp",
                "$tempRIC.trado_id",
                "$tempRIC.dari_id",
                "$tempRIC.sampai_id",
                "$tempRIC.nocont",
                "$tempRIC.nosp",
                DB::raw("(case when $tempRIC.ritasi_nobukti IS NULL then '-' else $tempRIC.ritasi_nobukti end) as ritasi_nobukti"),
                DB::raw("(case when $tempRIC.gajisupir IS NULL then 0 else $tempRIC.gajisupir end) as gajisupir"),
                DB::raw("(case when $tempRIC.gajikenek IS NULL then 0 else $tempRIC.gajikenek end) as gajikenek"),
                DB::raw("(case when $tempRIC.komisisupir IS NULL then 0 else $tempRIC.komisisupir end) as komisisupir"),
                DB::raw("(case when $tempRIC.tolsupir IS NULL then 0 else $tempRIC.tolsupir end) as tolsupir"),
                DB::raw("(case when $tempRIC.upahritasi IS NULL then 0 else $tempRIC.upahritasi end) as upahritasi"),
                DB::raw("(case when $tempRIC.biayaextra IS NULL then 0 else $tempRIC.biayaextra end) as biayaextra"),
                "parameter.text as statusritasi",
                DB::raw("(case when $tempRIC.keteranganbiaya IS NULL then '-' else $tempRIC.keteranganbiaya end) as keteranganbiaya")
            )->leftJoin(DB::raw("parameter with (readuncommitted)"), 'parameter.id', $tempRIC . '.statusritasi');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $query->orderBy($tempRIC . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        $this->filterTrip($query, $tempRIC);
        $this->paginate($query);
        $data = $query->get();

        // dd($query->get());
        $this->totalGajiSupir = $query->sum('gajisupir');
        $this->totalGajiKenek = $query->sum('gajikenek');
        $this->totalKomisiSupir = $query->sum('komisisupir');
        $this->totalUpahRitasi = $query->sum('upahritasi');
        $this->totalBiayaExtra = $query->sum('biayaextra');
        $this->totalTolSupir = $query->sum('tolsupir');
        return $data;
    }

    public function createTempGetRIC($gajiId, $supir_id, $dari, $sampai)
    {
        $temp = '##tempRIC' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('gajisupirdetail')->from(DB::raw("gajisupirdetail with (readuncommitted)"))
            ->select(
                'gajisupirdetail.suratpengantar_nobukti as nobuktitrip',
                'suratpengantar.tglbukti as tglbuktisp',
                'trado.kodetrado as trado_id',
                'kotaDari.keterangan as dari_id',
                'kotaSampai.keterangan as sampai_id',
                'suratpengantar.nocont',
                'suratpengantar.nosp',
                'gajisupirdetail.gajisupir',
                'gajisupirdetail.gajikenek',
                'gajisupirdetail.komisisupir',
                'gajisupirdetail.tolsupir',
                'gajisupirdetail.gajiritasi as upahritasi',
                'gajisupirdetail.ritasi_nobukti',
                'ritasi.statusritasi',
                'gajisupirdetail.biayatambahan as biayaextra',
                'gajisupirdetail.keteranganbiayatambahan as keteranganbiaya'
            )
            ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), 'gajisupirdetail.suratpengantar_nobukti', 'suratpengantar.nobukti')
            ->leftJoin(DB::raw("kota as kotaDari with (readuncommitted)"), 'suratpengantar.dari_id', 'kotaDari.id')
            ->leftJoin(DB::raw("kota as kotaSampai with (readuncommitted)"), 'suratpengantar.sampai_id', 'kotaSampai.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'suratpengantar.trado_id', 'trado.id')
            ->leftJoin(DB::raw("ritasi with (readuncommitted)"), 'gajisupirdetail.ritasi_nobukti', 'ritasi.nobukti')
            ->where('gajisupirdetail.suratpengantar_nobukti', '!=', '-')
            ->where('gajisupirdetail.gajisupir_id', $gajiId);


        Schema::create($temp, function ($table) {
            $table->string('nobuktitrip')->nullable();
            $table->date('tglbuktisp')->nullable()->nullable();
            $table->string('trado_id')->nullable();
            $table->string('dari_id')->nullable();
            $table->string('sampai_id')->nullable();
            $table->string('nocont')->nullable();
            $table->string('nosp')->nullable();
            $table->bigInteger('gajisupir')->nullable();
            $table->bigInteger('gajikenek')->nullable();
            $table->bigInteger('komisisupir')->nullable();
            $table->bigInteger('tolsupir')->nullable();
            $table->bigInteger('upahritasi')->nullable();
            $table->string('ritasi_nobukti')->nullable();
            $table->bigInteger('statusritasi')->nullable();
            $table->bigInteger('biayaextra')->nullable();
            $table->string('keteranganbiaya')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobuktitrip', 'tglbuktisp', 'trado_id', 'dari_id', 'sampai_id', 'nocont', 'nosp', 'gajisupir', 'gajikenek', 'komisisupir', 'tolsupir', 'upahritasi', 'ritasi_nobukti', 'statusritasi', 'biayaextra', 'keteranganbiaya'], $fetch);

        $fetch = DB::table('gajisupirdetail')->from(DB::raw("gajisupirdetail with (readuncommitted)"))
            ->select(
                'gajisupirdetail.suratpengantar_nobukti as nobuktitrip',
                'ritasi.tglbukti as tglbuktisp',
                'trado.kodetrado as trado_id',
                'kotaDari.keterangan as dari_id',
                'kotaSampai.keterangan as sampai_id',
                'gajisupirdetail.gajisupir',
                'gajisupirdetail.gajikenek',
                'gajisupirdetail.komisisupir',
                'gajisupirdetail.tolsupir',
                'gajisupirdetail.gajiritasi as upahritasi',
                'gajisupirdetail.ritasi_nobukti',
                'ritasi.statusritasi',
                'gajisupirdetail.biayatambahan as biayaextra',
                'gajisupirdetail.keteranganbiayatambahan as keteranganbiaya'
            )
            ->leftJoin(DB::raw("ritasi with (readuncommitted)"), 'gajisupirdetail.ritasi_nobukti', 'ritasi.nobukti')
            ->leftJoin(DB::raw("kota as kotaDari with (readuncommitted)"), 'ritasi.dari_id', 'kotaDari.id')
            ->leftJoin(DB::raw("kota as kotaSampai with (readuncommitted)"), 'ritasi.sampai_id', 'kotaSampai.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'ritasi.trado_id', 'trado.id')
            ->where('gajisupirdetail.suratpengantar_nobukti', '-')
            ->where('gajisupirdetail.gajisupir_id', $gajiId);

        $tes = DB::table($temp)->insertUsing(['nobuktitrip', 'tglbuktisp', 'trado_id', 'dari_id', 'sampai_id', 'gajisupir', 'gajikenek', 'komisisupir', 'tolsupir', 'upahritasi', 'ritasi_nobukti', 'statusritasi', 'biayaextra', 'keteranganbiaya'], $fetch);

        $fetch = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select(
                'suratpengantar.nobukti as nobuktitrip',
                'suratpengantar.tglbukti as tglbuktisp',
                'trado.kodetrado as trado_id',
                'kotaDari.keterangan as dari_id',
                'kotaSampai.keterangan as sampai_id',
                'suratpengantar.nocont',
                'suratpengantar.nosp',

                DB::raw("(case when ritasi.suratpengantar_urutke > 1 then 0 else suratpengantar.gajisupir end) as gajisupir"),
                DB::raw("(case when ritasi.suratpengantar_urutke > 1 then 0 else suratpengantar.gajikenek end) as gajikenek"),
                DB::raw("(case when ritasi.suratpengantar_urutke > 1 then 0 else suratpengantar.komisisupir end) as komisisupir"),
                DB::raw("(case when ritasi.suratpengantar_urutke > 1 then 0 else suratpengantar.tolsupir end) as tolsupir"),
                'ritasi.gaji as upahritasi',
                'ritasi.nobukti as ritasi_nobukti',
                'ritasi.statusritasi',
                'suratpengantarbiayatambahan.nominal as biayaextra',
                'suratpengantarbiayatambahan.keteranganbiaya'
            )
            ->leftJoin(DB::raw("kota as kotaDari with (readuncommitted)"), 'suratpengantar.dari_id', 'kotaDari.id')
            ->leftJoin(DB::raw("kota as kotaSampai with (readuncommitted)"), 'suratpengantar.sampai_id', 'kotaSampai.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'suratpengantar.trado_id', 'trado.id')
            ->leftJoin(DB::raw("ritasi with (readuncommitted)"), 'suratpengantar.nobukti', 'ritasi.suratpengantar_nobukti')
            ->leftJoin(DB::raw("suratpengantarbiayatambahan with (readuncommitted)"), 'suratpengantar.id', 'suratpengantarbiayatambahan.suratpengantar_id')
            ->where('suratpengantar.supir_id', $supir_id)
            ->where('suratpengantar.tglbukti', '>=', $dari)
            ->where('suratpengantar.tglbukti', '<=', $sampai)
            ->where(function ($query) {
                $query->whereRaw("suratpengantar.nobukti not in(select suratpengantar_nobukti from gajisupirdetail)")
                    ->orWhereRaw("ritasi.nobukti not in(select ritasi_nobukti from gajisupirdetail)");
            });

        $tes = DB::table($temp)->insertUsing(['nobuktitrip', 'tglbuktisp', 'trado_id', 'dari_id', 'sampai_id', 'nocont', 'nosp', 'gajisupir', 'gajikenek', 'komisisupir', 'tolsupir', 'upahritasi', 'ritasi_nobukti', 'statusritasi', 'biayaextra', 'keteranganbiaya'], $fetch);

        $fetch = Ritasi::from(DB::raw("ritasi with (readuncommitted)"))
            ->select(
                DB::raw("ritasi.tglbukti as tglbuktisp,trado.kodetrado as trado_id,kotaDari.keterangan as dari_id,kotaSampai.keterangan as sampai_id, ritasi.gaji as upahritasi,ritasi.nobukti as ritasi_nobukti,ritasi.statusritasi")
            )
            ->leftJoin(DB::raw("kota as kotaDari with (readuncommitted)"), 'ritasi.dari_id', 'kotaDari.id')
            ->leftJoin(DB::raw("kota as kotaSampai with (readuncommitted)"), 'ritasi.sampai_id', 'kotaSampai.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'ritasi.trado_id', 'trado.id')
            ->where('ritasi.supir_id', $supir_id)
            ->where('ritasi.tglbukti', '>=', $dari)
            ->where('ritasi.tglbukti', '<=', $sampai)
            ->whereRaw("ritasi.suratpengantar_nobukti = ''")
            ->whereRaw("ritasi.nobukti not in(select ritasi_nobukti from gajisupirdetail)");
        $tes = DB::table($temp)->insertUsing(['tglbuktisp', 'trado_id', 'dari_id', 'sampai_id', 'upahritasi', 'ritasi_nobukti', 'statusritasi'], $fetch);

        return $temp;
    }

    public function createTempGetSP($supir_id, $dari, $sampai)
    {
        $temp = '##tempSP' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('suratpengantar')->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select(
                'suratpengantar.id',
                'suratpengantar.nobukti as nobuktitrip',
                'suratpengantar.tglbukti as tglbuktisp',
                'trado.kodetrado as trado_id',
                'kotaDari.keterangan as dari_id',
                'kotaSampai.keterangan as sampai_id',
                'suratpengantar.nocont',
                'suratpengantar.nosp',
                'suratpengantar.gajisupir',
                'suratpengantar.gajikenek',
                'suratpengantar.komisisupir'
            )
            ->leftJoin(DB::raw("kota as kotaDari with (readuncommitted)"), 'suratpengantar.dari_id', 'kotaDari.id')
            ->leftJoin(DB::raw("kota as kotaSampai with (readuncommitted)"), 'suratpengantar.sampai_id', 'kotaSampai.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'suratpengantar.trado_id', 'trado.id')
            ->where('suratpengantar.supir_id', $supir_id)
            ->where('suratpengantar.tglbukti', '>=', $dari)
            ->where('suratpengantar.tglbukti', '<=', $sampai)
            ->whereRaw("suratpengantar.nobukti not in(select suratpengantar_nobukti from gajisupirdetail)");

        Schema::create($temp, function ($table) {
            $table->bigInteger('id');
            $table->string('nobuktitrip');
            $table->date('tglbuktisp')->nullable();
            $table->string('trado_id');
            $table->string('dari_id');
            $table->string('sampai_id');
            $table->string('nocont');
            $table->string('nosp');
            $table->bigInteger('gajisupir')->nullable();
            $table->bigInteger('gajikenek')->nullable();
            $table->bigInteger('komisisupir')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['id', 'nobuktitrip', 'tglbuktisp', 'trado_id', 'dari_id', 'sampai_id', 'nocont', 'nosp', 'gajisupir', 'gajikenek', 'komisisupir'], $fetch);

        return $temp;
    }


    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statuscetak') {
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'supir_id') {
                            $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statuscetak') {
                                $query->orWhere('parameter.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'supir_id') {
                                $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
                            } else {
                                $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
            $query->where('gajisupirheader.statuscetak', '<>', request()->cetak)
                ->whereYear('gajisupirheader.tglbukti', '=', request()->year)
                ->whereMonth('gajisupirheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function filterTrip($query, $table, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusritasi') {
                            $query = $query->where('parameter.text', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusritasi') {
                            $query = $query->orWhere('parameter.text', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->orWhere($table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                default:

                    break;
            }

            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }
        if (request()->cetak && request()->periode) {
            $query->where('gajisupirheader.statuscetak', '<>', request()->cetak)
                ->whereYear('gajisupirheader.tglbukti', '=', request()->year)
                ->whereMonth('gajisupirheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
