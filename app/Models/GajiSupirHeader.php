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
                'parameter.memo as statuscetak',
                "parameter.text as statuscetak_text",
                'gajisupirheader.userbukacetak',
                'gajisupirheader.jumlahcetak',
                DB::raw('(case when (year(gajisupirheader.tglbukacetak) <= 2000) then null else gajisupirheader.tglbukacetak end ) as tglbukacetak'),
                'gajisupirheader.modifiedby',
                'gajisupirheader.created_at',
                'gajisupirheader.updated_at',
            )
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


    public function getEditTrip($gajiId)
    {
        $query = GajiSupirDetail::from(DB::raw("gajisupirdetail with (readuncommitted)"))
            ->select(

                'suratpengantar.id',
                'gajisupirdetail.suratpengantar_nobukti as nobuktitrip',
                'suratpengantar.tglbukti as tglbuktisp',
                'trado.keterangan as trado_id',
                'kotaDari.keterangan as dari_id',
                'kotaSampai.keterangan as sampai_id',
                'suratpengantar.nocont',
                'suratpengantar.nosp',
                'gajisupirdetail.gajisupir',
                'gajisupirdetail.gajikenek',
                'gajisupirdetail.komisisupir',
            )
            ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), 'gajisupirdetail.suratpengantar_nobukti', 'suratpengantar.nobukti')
            ->leftJoin(DB::raw("kota as kotaDari with (readuncommitted)"), 'suratpengantar.dari_id', 'kotaDari.id')
            ->leftJoin(DB::raw("kota as kotaSampai with (readuncommitted)"), 'suratpengantar.sampai_id', 'kotaSampai.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'suratpengantar.trado_id', 'trado.id')
            ->where('gajisupirdetail.gajisupir_id', $gajiId);

        $data = $query->get();
        $this->totalGajiSupir = $query->sum('gajisupirdetail.gajisupir');
        $this->totalGajiKenek = $query->sum('gajisupirdetail.gajikenek');
        $this->totalKomisiSupir = $query->sum('gajisupirdetail.komisisupir');
        return $data;
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
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti')->default('');
            $table->string('supir_id', 1000)->default('');
            $table->bigInteger('nominal')->default('0');
            $table->date('tgldari')->default('');
            $table->date('tglsampai')->default('');
            $table->bigInteger('total')->default('0');
            $table->string('statuscetak', 1000)->default('');
            $table->string('userbukacetak', 50)->default('');
            $table->date('tglbukacetak')->default('1900/1/1');
            $table->integer('jumlahcetak')->Length(11)->default('0');
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
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
        $tempRIC = $this->createTempGetRIC($gajiId);
        $tempSP = $this->createTempGetSP($supir_id, $dari, $sampai);

        $RIC = DB::table("$tempSP as A")->from(DB::raw("$tempSP as A with (readuncommitted)"))
            ->select(DB::raw("A.id,A.nobuktitrip, A.tglbuktisp, A.trado_id, A.dari_id, A.sampai_id, A.nocont, A.nosp, A.gajisupir, A.gajikenek, A.komisisupir"))
            ->leftJoin(DB::raw("$tempRIC as B with (readuncommitted)"), "A.nobuktitrip", "B.nobuktitrip");


        $SP = DB::table($tempRIC)->from(DB::raw("$tempRIC with (readuncommitted)"))
            ->select(DB::raw("id,nobuktitrip, tglbuktisp,trado_id,dari_id, sampai_id, nocont, nosp, gajisupir, gajikenek, komisisupir"))
            ->unionAll($RIC);

            
        $data = $SP->get();
        $this->totalGajiSupir = $data->sum('gajisupir');
        $this->totalGajiKenek = $data->sum('gajikenek');
        $this->totalKomisiSupir = $data->sum('komisisupir');
        return $data;
    }

    public function createTempGetRIC($gajiId)
    {
        $temp = '##tempRIC' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('gajisupirdetail')->from(DB::raw("gajisupirdetail with (readuncommitted)"))
            ->select(
                'suratpengantar.id',
                'gajisupirdetail.suratpengantar_nobukti as nobuktitrip',
                'suratpengantar.tglbukti as tglbuktisp',
                'trado.keterangan as trado_id',
                'kotaDari.keterangan as dari_id',
                'kotaSampai.keterangan as sampai_id',
                'suratpengantar.nocont',
                'suratpengantar.nosp',
                'gajisupirdetail.gajisupir',
                'gajisupirdetail.gajikenek',
                'gajisupirdetail.komisisupir'
            )
            ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), 'gajisupirdetail.suratpengantar_nobukti', 'suratpengantar.nobukti')
            ->leftJoin(DB::raw("kota as kotaDari with (readuncommitted)"), 'suratpengantar.dari_id', 'kotaDari.id')
            ->leftJoin(DB::raw("kota as kotaSampai with (readuncommitted)"), 'suratpengantar.sampai_id', 'kotaSampai.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'suratpengantar.trado_id', 'trado.id')
            ->where('gajisupirdetail.gajisupir_id', $gajiId);

        Schema::create($temp, function ($table) {
            $table->bigInteger('id');
            $table->string('nobuktitrip');
            $table->date('tglbuktisp')->default('');
            $table->string('trado_id');
            $table->string('dari_id');
            $table->string('sampai_id');
            $table->string('nocont');
            $table->string('nosp');
            $table->bigInteger('gajisupir')->nullable();
            $table->bigInteger('gajikenek')->nullable();
            $table->bigInteger('komisisupir')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['id', 'nobuktitrip', 'tglbuktisp', 'trado_id', 'dari_id', 'sampai_id','nocont','nosp','gajisupir','gajikenek','komisisupir'], $fetch);

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
                'trado.keterangan as trado_id',
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
            $table->date('tglbuktisp')->default('');
            $table->string('trado_id');
            $table->string('dari_id');
            $table->string('sampai_id');
            $table->string('nocont');
            $table->string('nosp');
            $table->bigInteger('gajisupir')->nullable();
            $table->bigInteger('gajikenek')->nullable();
            $table->bigInteger('komisisupir')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['id', 'nobuktitrip', 'tglbuktisp', 'trado_id', 'dari_id', 'sampai_id','nocont','nosp','gajisupir','gajikenek','komisisupir'], $fetch);

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
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statuscetak') {
                            $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'supir_id') {
                            $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
