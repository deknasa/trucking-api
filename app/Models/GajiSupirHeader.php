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
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
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

                'gajisupirheader.id',
                'gajisupirheader.nobukti',
                'gajisupirheader.tglbukti',
                'gajisupirheader.supir_id',
                'supir.namasupir as supir',
                'gajisupirheader.tgldari',
                'gajisupirheader.tglsampai',
                'gajisupirheader.uangJalantidakterhitung as uangjalantidakterhitung',
                'gajisupirheader.uangmakanharian',
                'gajisupirheader.deposito',
                'gajisupirheader.bbm',
                'gajisupirheader.potonganpinjaman',
                'gajisupirheader.potonganpinjamansemua'

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
        if ($this->params['sortIndex'] == 'id') {
            $query->orderBy($sp . '.nobuktitrip', $this->params['sortOrder']);
        } else {
            $query->orderBy($sp . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }

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
        $models =  $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'supir_id',  'nominal', 'tgldari', 'tglsampai', 'total', 'statuscetak', 'userbukacetak', 'tglbukacetak', 'jumlahcetak', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function getPinjSemua()
    {
        $temp = $this->createTempPinjSemua();
        $query = PengeluaranTruckingDetail::from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
            ->select(DB::raw("pengeluarantruckingdetail.nobukti as pinjSemua_nobukti,row_number() Over(Order By pengeluarantruckingdetail.nobukti) as id,$temp.tglbukti,pengeluarantruckingdetail.supir_id, 'SEMUA' as pinjSemua_supir,pengeluarantruckingdetail.keterangan as pinjSemua_keterangan,$temp.sisa as pinjSemua_sisa"))
            // ->distinct('pengeluarantruckingheader.tglbukti')
            ->join(DB::raw("$temp with (readuncommitted)"), $temp . '.nobukti', 'pengeluarantruckingdetail.nobukti')
            // ->leftJoin(DB::raw("penerimaantruckingdetail with (readuncommitted)"), 'penerimaantruckingdetail.pengeluarantruckingheader_nobukti', 'pengeluarantruckingdetail.nobukti')
            ->orderBy($temp . '.tglbukti', 'asc')
            ->orderBy($temp . '.nobukti', 'asc')
            ->where("$temp.sisa", '>', '0')
            ->where("pengeluarantruckingdetail.supir_id", 0);

        return $query->get();
    }

    public function createTempPinjSemua()
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('pengeluarantruckingheader')
            ->from(
                DB::raw("pengeluarantruckingheader with (readuncommitted)")
            )
            ->select(DB::raw("pengeluarantruckingdetail.nobukti, pengeluarantruckingheader.tglbukti, (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"))
            ->leftJoin(DB::raw("pengeluarantruckingdetail with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', 'pengeluarantruckingheader.nobukti')
            ->where("pengeluarantruckingdetail.supir_id", 0)
            ->where("pengeluarantruckingdetail.nobukti", 'LIKE', '%PJT%')
            ->orderBy('pengeluarantruckingheader.tglbukti', 'asc')
            ->orderBy('pengeluarantruckingdetail.nobukti', 'asc');
        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->date('tglbukti');
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'tglbukti', 'sisa'], $fetch);


        return $temp;
    }


    public function getPinjPribadi($supir_id)
    {
        $tempPribadi = $this->createTempPinjPribadi($supir_id);

        $query = PengeluaranTruckingDetail::from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By pengeluarantruckingheader.tglbukti asc,pengeluarantruckingdetail.nobukti) as pinjPribadi_id,pengeluarantruckingheader.tglbukti,pengeluarantruckingdetail.nobukti as pinjPribadi_nobukti,pengeluarantruckingdetail.keterangan as pinjPribadi_keterangan," . $tempPribadi . ".sisa as pinjPribadi_sisa"))
            ->leftJoin(DB::raw("$tempPribadi with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', $tempPribadi . ".nobukti")
            ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', "pengeluarantruckingheader.nobukti")
            ->whereRaw("pengeluarantruckingdetail.supir_id = $supir_id")
            ->whereRaw("pengeluarantruckingdetail.nobukti = $tempPribadi.nobukti")
            ->where(function ($query) use ($tempPribadi) {
                $query->whereRaw("$tempPribadi.sisa != 0")
                    ->orWhereRaw("$tempPribadi.sisa is null");
            })
            ->orderBy('pengeluarantruckingheader.tglbukti', 'asc')
            ->orderBy('pengeluarantruckingdetail.nobukti', 'asc');

        return $query->get();
    }

    public function createTempPinjPribadi($supir_id)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("pengeluarantruckingdetail.nobukti, (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"))
            // ->leftJoin(DB::raw("penerimaantruckingdetail with (readuncommitted)"), 'penerimaantruckingdetail.pengeluarantruckingheader_nobukti', 'pengeluarantruckingdetail.nobukti')
            ->whereRaw("pengeluarantruckingdetail.supir_id = $supir_id")
            ->where("pengeluarantruckingdetail.nobukti",  'LIKE', "%PJT%")
            ->groupBy('pengeluarantruckingdetail.nobukti', 'pengeluarantruckingdetail.nominal');

        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'sisa'], $fetch);


        return $temp;
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

    public function getAbsensi($supir_id, $tglDari, $tglSampai)
    {
        $this->setRequestParameters();
        $query = DB::table("absensisupirdetail")->from(DB::raw("absensisupirdetail with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By absensisupirheader.nobukti) as absensi_id"), 'absensisupirheader.nobukti as absensi_nobukti', 'absensisupirheader.tglbukti as absensi_tglbukti', 'absensisupirdetail.uangjalan as absensi_uangjalan')
            ->leftJoin(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirheader.nobukti', 'absensisupirdetail.nobukti')
            ->whereBetween('absensisupirheader.tglbukti', [$tglDari, $tglSampai])
            ->where('absensisupirdetail.supir_id', $supir_id)
            ->whereRaw("absensisupirheader.nobukti not in (select absensisupir_nobukti from gajisupiruangjalan where supir_id=$supir_id)");

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        if ($this->params['sortIndex'] == 'absensi_uangjalan') {
            $query->orderBy('absensisupirdetail.uangjalan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'absensi_nobukti') {
            $query->orderBy('absensisupirdetail.nobukti', $this->params['sortOrder']);
        } else {
            $query->orderBy('absensisupirheader.tglbukti', $this->params['sortOrder']);
        }
        $this->filterAbsensi($query, 'absensisupirdetail', 'absensisupirheader');
        $this->paginate($query);
        $data = $query->get();
        $this->totalUangJalan = $query->sum('uangjalan');
        return $data;
    }


    public function getEditAbsensi($id)
    {
        $this->setRequestParameters();
        $query = DB::table("gajisupiruangjalan")->from(DB::raw("gajisupiruangjalan with (readuncommitted)"))
            ->select(
                DB::raw("row_number() Over(Order By gajisupiruangjalan.absensisupir_nobukti) as absensi_id"),
                'gajisupiruangjalan.gajisupir_id as gajisupir_id',
                'gajisupiruangjalan.absensisupir_nobukti as absensi_nobukti',
                'absensisupirheader.tglbukti as absensi_tglbukti',
                'gajisupiruangjalan.nominal as absensi_uangjalan'
            )
            ->join(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirheader.nobukti', 'gajisupiruangjalan.absensisupir_nobukti')
            ->where('gajisupiruangjalan.gajisupir_id', $id);

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        if ($this->params['sortIndex'] == 'absensi_uangjalan') {
            $query->orderBy('gajisupiruangjalan.nominal', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'absensi_nobukti') {
            $query->orderBy('gajisupiruangjalan.absensisupir_nobukti', $this->params['sortOrder']);
        } else {
            $query->orderBy('absensisupirheader.tglbukti', $this->params['sortOrder']);
        }
        $this->filterAbsensi($query, 'gajisupiruangjalan');
        $this->paginate($query);
        $data = $query->get();
        $this->totalUangJalan = $query->sum('gajisupiruangjalan.nominal');
        return $data;
    }

    public function getAllEditAbsensi($id, $supir_id, $dari, $sampai)
    {
        $this->setRequestParameters();
        $temp = '##tempAbsensi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $getUangjalan = DB::table("gajisupiruangjalan")->from(DB::raw("gajisupiruangjalan with (readuncommitted)"))
            ->select(
                'gajisupiruangjalan.gajisupir_id as gajisupir_id',
                'gajisupiruangjalan.absensisupir_nobukti',
                'absensisupirheader.tglbukti',
                'gajisupiruangjalan.nominal'
            )
            ->join(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirheader.nobukti', 'gajisupiruangjalan.absensisupir_nobukti')
            ->where('gajisupiruangjalan.gajisupir_id', $id);
        Schema::create($temp, function ($table) {
            $table->bigInteger('gajisupir_id')->nullable();
            $table->string('absensisupir_nobukti')->nullable();
            $table->date('tglbukti')->nullable();
            $table->bigInteger('nominal')->nullable();
        });

        DB::table($temp)->insertUsing(['gajisupir_id', 'absensisupir_nobukti', 'tglbukti', 'nominal'], $getUangjalan);

        $fetch = DB::table("absensisupirdetail")->from(DB::raw("absensisupirdetail with (readuncommitted)"))
            ->select('absensisupirheader.nobukti as absensisupir_nobukti', 'absensisupirheader.tglbukti', 'absensisupirdetail.uangjalan as nominal')
            ->leftJoin(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirheader.nobukti', 'absensisupirdetail.nobukti')
            ->whereBetween('absensisupirheader.tglbukti', [$dari, $sampai])
            ->where('absensisupirdetail.supir_id', $supir_id)
            ->whereRaw("absensisupirheader.nobukti not in (select absensisupir_nobukti from gajisupiruangjalan where supir_id=$supir_id)");

        DB::table($temp)->insertUsing(['absensisupir_nobukti', 'tglbukti', 'nominal'], $fetch);

        $query = DB::table($temp)->from(DB::raw("$temp with (readuncommitted)"))
            ->select(
                DB::raw("row_number() Over(Order By $temp.absensisupir_nobukti) as absensi_id"),
                "$temp.gajisupir_id",
                "$temp.absensisupir_nobukti as absensi_nobukti",
                "$temp.tglbukti as absensi_tglbukti",
                "$temp.nominal as absensi_uangjalan"
            );

        if ($this->params['sortIndex'] == 'absensi_uangjalan') {
            $query->orderBy($temp . '.nominal', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'absensi_nobukti') {
            $query->orderBy($temp . '.absensisupir_nobukti', $this->params['sortOrder']);
        } else {
            $query->orderBy($temp . '.tglbukti', $this->params['sortOrder']);
        }
        $this->filterAbsensi($query, $temp);
        $this->paginate($query);
        $data = $query->get();

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $this->totalUangJalan = $query->sum($temp . '.nominal');
        return $data;
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
                        } else if ($filters['field'] == 'total' || $filters['field'] == 'uangjalan' || $filters['field'] == 'bbm' || $filters['field'] == 'deposito' || $filters['field'] == 'potonganpinjaman' || $filters['field'] == 'potonganpinjamansemua' || $filters['field'] == 'uangmakanharian') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgldari' || $filters['field'] == 'tglsampai' || $filters['field'] == 'tglbukacetak') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . "." .  $filters['field'] . " LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

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
                            } else if ($filters['field'] == 'total' || $filters['field'] == 'uangjalan' || $filters['field'] == 'bbm' || $filters['field'] == 'deposito' || $filters['field'] == 'potonganpinjaman' || $filters['field'] == 'potonganpinjamansemua' || $filters['field'] == 'uangmakanharian') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgldari' || $filters['field'] == 'tglsampai' || $filters['field'] == 'tglbukacetak') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw($this->table . "." .  $filters['field'] . " LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

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
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statusritasi') {
                                $query = $query->where('parameter.text', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'gajisupir' || $filters['field'] == 'gajikenek' || $filters['field'] == 'komisisupir' || $filters['field'] == 'tolsupir' || $filters['field'] == 'upahritasi' || $filters['field'] == 'biayaextra') {
                                $query = $query->whereRaw("format(" . $table . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where($table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statusritasi') {
                                $query = $query->orWhere('parameter.text', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'gajisupir' || $filters['field'] == 'gajikenek' || $filters['field'] == 'komisisupir' || $filters['field'] == 'tolsupir' || $filters['field'] == 'upahritasi' || $filters['field'] == 'biayaextra') {
                                $query = $query->orWhereRaw("format(" . $table . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere($table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
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

    public function filterAbsensi($query, $table1, $table2 = null, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'absensi_uangjalan') {
                                if ($table1 == 'absensisupirdetail') {
                                    $query = $query->whereRaw("format(absensisupirdetail.uangjalan, '#,#0.00') LIKE '%$filters[data]%'");
                                } else {
                                    $query = $query->whereRaw("format($table1.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                                }
                            } else if ($filters['field'] == 'absensi_nobukti') {
                                if ($table2 != null) {
                                    $query = $query->where('absensisupirheader.nobukti', 'LIKE', "%$filters[data]%");
                                } else {
                                    $query = $query->where($table1 . '.absensisupir_nobukti', 'LIKE', "%$filters[data]%");
                                }
                            } else {
                                if ($table1 == 'absensisupirdetail' || $table1 == 'gajisupiruangjalan') {
                                    $query = $query->whereRaw("format(absensisupirheader.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else {
                                    $query = $query->whereRaw("format($table1.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                }
                            }
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'absensi_uangjalan') {
                                if ($table1 == 'absensisupirdetail') {
                                    $query = $query->orWhereRaw("format(absensisupirdetail.uangjalan, '#,#0.00') LIKE '%$filters[data]%'");
                                } else {
                                    $query = $query->orWhereRaw("format($table1.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                                }
                            } else if ($filters['field'] == 'absensi_nobukti') {
                                if ($table2 != null) {
                                    $query = $query->orWhere('absensisupirheader.nobukti', 'LIKE', "%$filters[data]%");
                                } else {
                                    $query = $query->orWhere($table1 . '.absensisupir_nobukti', 'LIKE', "%$filters[data]%");
                                }
                            } else {
                                if ($table1 == 'absensisupirdetail' || $table1 == 'gajisupiruangjalan') {
                                    $query = $query->orWhereRaw("format(absensisupirheader.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else {
                                    $query = $query->orWhereRaw("format($table1.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                }
                            }
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

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function validasiBayarPotSemua($nobukti)
    {
        $fetch = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("pengeluarantruckingdetail.nobukti, (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"))
            ->where("pengeluarantruckingdetail.supir_id", 0)
            ->where("pengeluarantruckingdetail.nobukti", $nobukti);

        return $fetch->first();
    }
    public function validasiBayarPotPribadi($nobukti)
    {
        $fetch = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("pengeluarantruckingdetail.nobukti, (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"))
            ->where("pengeluarantruckingdetail.nobukti", $nobukti);

        return $fetch->first();
    }
}
