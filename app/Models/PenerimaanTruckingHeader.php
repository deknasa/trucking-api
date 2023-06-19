<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\RunningNumberService;


class PenerimaanTruckingHeader extends MyModel
{
    use HasFactory;
    protected $table = 'penerimaantruckingheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function cekvalidasiaksi($nobukti)
    {

        $prosesUangJalan = DB::table('prosesuangjalansupirdetail')
            ->from(
                DB::raw("prosesuangjalansupirdetail as a with (readuncommitted)")
            )
            ->select(
                'a.penerimaantrucking_nobukti'
            )
            ->where('a.penerimaantrucking_nobukti', '=', $nobukti)
            ->first();
        if (isset($prosesUangJalan)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Proses Uang Jalan Supir',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }

        $pengeluaranTrucking = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail as a with (readuncommitted)")
            )
            ->select(
                'a.penerimaantruckingheader_nobukti'
            )
            ->where('a.penerimaantruckingheader_nobukti', '=', $nobukti)
            ->first();
        if (isset($pengeluaranTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pengeluaran Trucking',
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
        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';
        $query = DB::table($this->table)->from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
            ->select(
                'penerimaantruckingheader.id',
                'penerimaantruckingheader.nobukti',
                'penerimaantruckingheader.tglbukti',

                'penerimaantrucking.keterangan as penerimaantrucking_id',
                'penerimaantruckingheader.penerimaan_nobukti',

                'bank.namabank as bank_id',
                DB::raw('(case when (year(penerimaantruckingheader.tglbukacetak) <= 2000) then null else penerimaantruckingheader.tglbukacetak end ) as tglbukacetak'),
                'parameter.memo as statuscetak',
                'penerimaantruckingheader.userbukacetak',
                'penerimaantruckingheader.jumlahcetak',
                'akunpusat.keterangancoa as coa',
                'penerimaantruckingheader.modifiedby',
                'penerimaantruckingheader.created_at',
                'penerimaantruckingheader.updated_at',
            )
            ->leftJoin(DB::raw("penerimaantrucking with (readuncommitted)"), 'penerimaantruckingheader.penerimaantrucking_id', 'penerimaantrucking.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'penerimaantruckingheader.coa', 'akunpusat.coa')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'penerimaantruckingheader.statuscetak', 'parameter.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'penerimaantruckingheader.bank_id', 'bank.id');
        if (request()->tgldari) {
            $query->whereBetween('penerimaantruckingheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if (request()->penerimaanheader_id) {
            $query->where('penerimaantrucking_id', request()->penerimaanheader_id);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(penerimaantruckingheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(penerimaantruckingheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("penerimaantruckingheader.statuscetak", $statusCetak);
        }

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

        $query = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
            ->select(
                'penerimaantruckingheader.id',
                'penerimaantruckingheader.nobukti',
                'penerimaantruckingheader.tglbukti',
                'penerimaantrucking.kodepenerimaan',
                'penerimaantruckingheader.penerimaantrucking_id',
                'penerimaantrucking.kodepenerimaan as penerimaantrucking',
                'penerimaantruckingheader.bank_id',
                'penerimaantruckingheader.supir_id as supirheader_id',
                'bank.namabank as bank',
                'supir.namasupir as supir',
                'penerimaantruckingheader.coa',
                'akunpusat.keterangancoa',
                'penerimaantruckingheader.penerimaan_nobukti'
            )
            ->leftJoin(DB::raw("penerimaantrucking with (readuncommitted)"), 'penerimaantruckingheader.penerimaantrucking_id', 'penerimaantrucking.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'penerimaantruckingheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'penerimaantruckingheader.supir_id', 'supir.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'penerimaantruckingheader.coa', 'akunpusat.coa')
            ->where('penerimaantruckingheader.id', '=', $id);


        $data = $query->first();

        return $data;
    }

    public function penerimaantruckingdetail()
    {
        return $this->hasMany(PenerimaanTruckingDetail::class, 'penerimaantruckingheader_id');
    }

    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
                "$this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            'penerimaantrucking.keterangan as penerimaantrucking_id',
            'bank.namabank as bank_id',
            $this->table.coa,
            $this->table.penerimaan_nobukti,
            'parameter.text as statuscetak',
            $this->table.userbukacetak,
            $this->table.tglbukacetak,
            $this->table.jumlahcetak,
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )
        )
            ->leftJoin('penerimaantrucking', 'penerimaantruckingheader.penerimaantrucking_id', 'penerimaantrucking.id')
            ->leftJoin('parameter', 'penerimaantruckingheader.statuscetak', 'parameter.id')
            ->leftJoin('bank', 'penerimaantruckingheader.bank_id', 'bank.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('penerimaantrucking_id', 1000)->nullable();
            $table->string('bank_id', 1000)->nullable();
            $table->string('coa', 1000)->nullable();
            $table->string('penerimaan_nobukti', 1000)->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        if (request()->tgldariheader) {
            $query->whereBetween('tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        }
        if (request()->penerimaanheader_id) {
            $query->where('penerimaantrucking_id', request()->penerimaanheader_id);
        }
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'penerimaantrucking_id', 'bank_id', 'coa', 'penerimaan_nobukti', 'statuscetak', 'userbukacetak', 'tglbukacetak', 'jumlahcetak', 'modifiedby', 'created_at', 'updated_at'], $models);


        return  $temp;
    }

    public function getDeposito($supir_id)
    {
        $tempPribadi = $this->createTempDeposito($supir_id);

        $query = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By penerimaantruckingdetail.nobukti) as id,penerimaantruckingheader.tglbukti,penerimaantruckingdetail.nobukti,penerimaantruckingdetail.keterangan," . $tempPribadi . ".sisa"))
            ->leftJoin(DB::raw("$tempPribadi with (readuncommitted)"), 'penerimaantruckingdetail.nobukti', $tempPribadi . ".nobukti")
            ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingdetail.nobukti', "penerimaantruckingheader.nobukti")
            ->whereRaw("penerimaantruckingdetail.supir_id = $supir_id")
            ->whereRaw("penerimaantruckingdetail.nobukti = $tempPribadi.nobukti")
            ->where(function ($query) use ($tempPribadi) {
                $query->whereRaw("$tempPribadi.sisa != 0")
                    ->orWhereRaw("$tempPribadi.sisa is null");
            })
            ->orderBy('penerimaantruckingheader.tglbukti', 'asc')
            ->orderBy('penerimaantruckingdetail.nobukti', 'asc');

        return $query->get();
    }

    public function createTempDeposito($supir_id)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('penerimaantruckingdetail')
            ->from(
                DB::raw("penerimaantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("penerimaantruckingdetail.nobukti, (SELECT (penerimaantruckingdetail.nominal - coalesce(SUM(pengeluarantruckingdetail.nominal),0)) FROM pengeluarantruckingdetail WHERE pengeluarantruckingdetail.penerimaantruckingheader_nobukti= penerimaantruckingdetail.nobukti) AS sisa"))
            // ->leftJoin(DB::raw("penerimaantruckingdetail with (readuncommitted)"), 'penerimaantruckingdetail.pengeluarantruckingheader_nobukti', 'pengeluarantruckingdetail.nobukti')
            ->whereRaw("penerimaantruckingdetail.supir_id = $supir_id")
            ->where("penerimaantruckingdetail.nobukti",  'LIKE', "%DPO%")
            ->groupBy('penerimaantruckingdetail.nobukti', 'penerimaantruckingdetail.nominal');

        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'sisa'], $fetch);


        return $temp;
    }

    public function getPelunasan($tgldari, $tglsampai)
    {
        $tempPribadi = $this->createTempPelunasan($tgldari, $tglsampai);

        $query = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By penerimaantruckingdetail.nobukti) as id, penerimaantruckingheader.tglbukti, penerimaantruckingdetail.nobukti, penerimaantruckingdetail.keterangan, {$tempPribadi}.sisa"))
            ->leftJoin(DB::raw("{$tempPribadi} with (readuncommitted)"), 'penerimaantruckingdetail.nobukti', '=', "{$tempPribadi}.nobukti")
            ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingdetail.nobukti', "penerimaantruckingheader.nobukti")
            ->whereBetween('penerimaantruckingheader.tglbukti', [date('Y-m-d', strtotime($tgldari)), date('Y-m-d', strtotime($tglsampai))])
            ->where('penerimaantruckingheader.penerimaantrucking_id', '=', 1)
            ->where(function ($query) use ($tempPribadi) {
                $query->whereRaw("$tempPribadi.sisa != 0")
                    ->orWhereRaw("$tempPribadi.sisa is null");
            })
            ->orderBy('penerimaantruckingheader.tglbukti', 'asc')
            ->orderBy('penerimaantruckingdetail.nobukti', 'asc');

        return $query->get();
    }

    public function createTempPelunasan($tgldari, $tglsampai)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = DB::table('penerimaantruckingdetail')
            ->from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
            ->leftJoin('penerimaantruckingheader', 'penerimaantruckingdetail.nobukti', '=', 'penerimaantruckingheader.nobukti')
            ->select(DB::raw("penerimaantruckingdetail.nobukti, (SELECT (penerimaantruckingdetail.nominal - COALESCE(SUM(pengeluarantruckingdetail.nominal), 0)) FROM pengeluarantruckingdetail WHERE pengeluarantruckingdetail.penerimaantruckingheader_nobukti = penerimaantruckingdetail.nobukti) AS sisa"))
            ->whereBetween('penerimaantruckingheader.tglbukti', [date('Y-m-d', strtotime($tgldari)), date('Y-m-d', strtotime($tglsampai))])
            ->where('penerimaantruckingheader.penerimaantrucking_id', '=', 1)
            ->groupBy('penerimaantruckingdetail.nobukti', 'penerimaantruckingdetail.nominal');

        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'sisa'], $fetch);

        return $temp;
    }

    public function getPinjaman($supir_id)
    {
        $tempPribadi = $this->createTempPinjPribadi($supir_id);

        $query = PengeluaranTruckingDetail::from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By pengeluarantruckingdetail.nobukti) as id,pengeluarantruckingheader.tglbukti,pengeluarantruckingdetail.nobukti,pengeluarantruckingdetail.keterangan," . $tempPribadi . ".sisa"))
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

    public function getPengembalianPinjaman($id, $supir_id)
    {
        // return $supir_id;
        $tempPribadi = $this->createTempPengembalianPinjaman($id, $supir_id);
        $tempAll = $this->createTempPinjaman($id, $supir_id);

        $temp = '##tempGet' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $pengembalian = DB::table($tempPribadi)->from(DB::raw("$tempPribadi with (readuncommitted)"))
            ->select(DB::raw("penerimaantrucking_id,nobukti,keterangan,sisa,bayar"));

        Schema::create($temp, function ($table) {
            $table->bigInteger('penerimaantrucking_id')->nullable();
            $table->string('nobukti');
            $table->string('keterangan')->nullable();
            $table->bigInteger('sisa')->nullable();
            $table->bigInteger('bayar')->nullable();
        });
        DB::table($temp)->insertUsing(['penerimaantrucking_id', 'nobukti', 'keterangan', 'sisa', 'bayar'], $pengembalian);

        $pinjaman = DB::table($tempAll)->from(DB::raw("$tempAll with (readuncommitted)"))
            ->select(DB::raw("null as penerimaantrucking_id,nobukti,keterangan,sisa, 0 as bayar"))
            ->where('sisa', '!=', '0');

        DB::table($temp)->insertUsing(['penerimaantrucking_id', 'nobukti', 'keterangan', 'sisa', 'bayar'], $pinjaman);

        $data = DB::table($temp)->from(DB::raw("$temp with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $temp.nobukti) as id,penerimaantrucking_id,nobukti,keterangan,sisa,bayar as nominal"))
            ->get();

        return $data;
    }

    public function createTempPinjaman($id, $supir_id)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetch = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("pengeluarantruckingdetail.nobukti,pengeluarantruckingdetail.keterangan,
            (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) 
            FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa "))
            ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'pengeluarantruckingdetail.nobukti')
            ->whereRaw("pengeluarantruckingdetail.supir_id = $supir_id")
            ->where("pengeluarantruckingdetail.nobukti",  'LIKE', "%PJT%")
            ->whereRaw("pengeluarantruckingheader.nobukti not in (select pengeluarantruckingheader_nobukti from penerimaantruckingdetail where penerimaantruckingheader_id=$id)")
            ->orderBy('pengeluarantruckingheader.tglbukti', 'asc')
            ->orderBy('pengeluarantruckingdetail.nobukti', 'asc');
        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->string('keterangan');
            $table->bigInteger('sisa')->nullable();
        });
        // return $fetch->get();
        $tes = DB::table($temp)->insertUsing(['nobukti', 'keterangan', 'sisa'], $fetch);
        return $temp;
    }

    public function createTempPengembalianPinjaman($id, $supir_id)
    {
        $temp = '##tempPengembalian' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("penerimaantruckingdetail.penerimaantruckingheader_id,pengeluarantruckingdetail.nobukti,pengeluarantruckingdetail.keterangan, penerimaantruckingdetail.nominal as bayar ,
                (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) 
                FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa "))
            ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'pengeluarantruckingdetail.nobukti')
            ->leftJoin(DB::raw("penerimaantruckingdetail with (readuncommitted)"), 'penerimaantruckingdetail.pengeluarantruckingheader_nobukti', 'pengeluarantruckingdetail.nobukti')
            ->whereRaw("pengeluarantruckingdetail.supir_id = $supir_id")
            ->where("pengeluarantruckingdetail.nobukti",  'LIKE', "%PJT%")
            ->where("penerimaantruckingdetail.penerimaantruckingheader_id", $id)
            ->orderBy('pengeluarantruckingheader.tglbukti', 'asc')
            ->orderBy('pengeluarantruckingdetail.nobukti', 'asc');
        Schema::create($temp, function ($table) {
            $table->bigInteger('penerimaantrucking_id')->nullable();
            $table->string('nobukti');
            $table->string('keterangan');
            $table->bigInteger('bayar')->nullable();
            $table->bigInteger('sisa')->nullable();
        });
        // return $fetch->get();
        $tes = DB::table($temp)->insertUsing(['penerimaantrucking_id', 'nobukti', 'keterangan', 'bayar', 'sisa'], $fetch);


        return $temp;
    }

    public function getDeletePengembalianPinjaman($id, $supir_id)
    {
        // return $supir_id;
        $tempPribadi = $this->createTempPengembalianPinjaman($id, $supir_id);

        $data = DB::table($tempPribadi)->from(DB::raw("$tempPribadi with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $tempPribadi.nobukti) as id,penerimaantrucking_id,nobukti,keterangan,sisa,bayar as nominal"))
            ->get();

        return $data;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'penerimaantrucking_id') {
            return $query->orderBy('penerimaantrucking.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'bank_id') {
            return $query->orderBy('bank.namabank', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'coa') {
            return $query->orderBy('akunpusat.keterangancoa', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statuscetak') {
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'penerimaantrucking_id') {
                            $query = $query->where('penerimaantrucking.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'coa') {
                            $query = $query->where('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglbukacetak') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statuscetak') {
                                $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'penerimaantrucking_id') {
                                $query = $query->orWhere('penerimaantrucking.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'bank_id') {
                                $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coa') {
                                $query = $query->orWhere('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglbukacetak') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
            $query->where('penerimaantruckingheader.statuscetak', '<>', request()->cetak)
                ->whereYear('penerimaantruckingheader.tglbukti', '=', request()->year)
                ->whereMonth('penerimaantruckingheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }


    public function processStore(array $data): PenerimaanTruckingHeader
    {
        $idpenerimaan = $data['penerimaantrucking_id'];
        $fetchFormat =  DB::table('penerimaantrucking')->where('id', $idpenerimaan)->first();

        $tanpaprosesnobukti = array_key_exists("tanpaprosesnobukti", $data) ? $data['tanpaprosesnobukti'] : 0;
        if ($fetchFormat->kodepenerimaan == 'PJP') {
            $data['coa'] = $fetchFormat->coapostingkredit;
        } else if ($fetchFormat->kodepenerimaan == 'BBM') {
            $data['coa'] = $fetchFormat->coakredit;
        }

        $statusformat = $fetchFormat->format;
        $fetchGrp = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('id', $statusformat)->first();
        $format = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', $fetchGrp->grp)->where('subgrp', $fetchGrp->subgrp)->first();

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        $coadebet = '';
        if ($tanpaprosesnobukti != 2) {
            // throw new \Exception($data['bank_id']);
            $bank = $data['bank_id'];
            $querySubgrpPenerimaan = Bank::from(DB::raw("bank with (readuncommitted)"))
                ->select('parameter.grp', 'parameter.subgrp', 'bank.formatpenerimaan', 'bank.coa', 'bank.tipe')
                ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')
                ->where('bank.id', $data['bank_id'])
                ->first();
            $coadebet = $querySubgrpPenerimaan->coa;
        }

        $penerimaanTruckingHeader = new PenerimaanTruckingHeader();

        $penerimaanTruckingHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $penerimaanTruckingHeader->penerimaantrucking_id = $data['penerimaantrucking_id'] ?? $idpenerimaan;
        $penerimaanTruckingHeader->bank_id = $data['bank_id'];
        $penerimaanTruckingHeader->coa = $data['coa'] ?? '';
        $penerimaanTruckingHeader->supir_id = $data['supirheader_id'] ?? '';
        $penerimaanTruckingHeader->penerimaan_nobukti = $data['penerimaan_nobukti'] ?? '';
        $penerimaanTruckingHeader->statusformat = $data['statusformat'] ?? $format->id;
        $penerimaanTruckingHeader->statuscetak = $statusCetak->id;
        $penerimaanTruckingHeader->modifiedby = auth('api')->user()->name;
        $penerimaanTruckingHeader->nobukti = (new RunningNumberService)->get($fetchGrp->grp, $fetchGrp->subgrp, $penerimaanTruckingHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$penerimaanTruckingHeader->save()) {
            throw new \Exception("Error storing Penerimaan Trucking header.");
        }

        $penerimaanTruckingHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerimaanTruckingHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY penerimaan Trucking Header '),
            'idtrans' => $penerimaanTruckingHeader->id,
            'nobuktitrans' => $penerimaanTruckingHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $penerimaanTruckingHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);


        $penerimaanTruckingDetails = [];
        for ($i = 0; $i < count($data['nominal']); $i++) {
            $penerimaanTruckingDetail = (new PenerimaanTruckingDetail())->processStore($penerimaanTruckingHeader, [
                'penerimaantruckingheader_id' => $penerimaanTruckingHeader->id,
                'nobukti' => $penerimaanTruckingHeader->nobukti,
                'supir_id' =>   $data['supir_id'][$i] ?? '',
                'pengeluarantruckingheader_nobukti' => $data['pengeluarantruckingheader_nobukti'][$i] ?? '',
                'keterangan' =>  $data['keterangan'][$i],
                'nominal' =>  $data['nominal'][$i],
                'modifiedby' => $penerimaanTruckingHeader->modifiedby,
            ]);
            $penerimaanDetails[] = $penerimaanTruckingDetail->toArray();
            $coakredit_detail[] = $data['coa'];
            $coadebet_detail[] = $coadebet;
            $nominal_detail[] = $data['nominal'][$i];
            $keterangan_detail[] = $data['keterangan'][$i];
            $tgljatuhtempo[] = date('Y-m-d', strtotime($data['tglbukti']));
        }

        $penerimaanTruckingDetailLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerimaanTruckingHeaderLogTrail->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY penerimaan Trucking detail '),
            'idtrans' => $penerimaanTruckingHeaderLogTrail->id,
            'nobuktitrans' => $penerimaanTruckingHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $penerimaanTruckingDetails,
            'modifiedby' => auth('api')->user()->user
        ]);

        //if tanpaprosesnobukti NOT 2 STORE PENERIMAAN
        if ($tanpaprosesnobukti != 2) {


            /*STORE PENERIMAAN*/
            $penerimaanRequest = [
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'postingdari' => (array_key_exists("postingdari", $data)) ? $data['postingdari'] : "ENTRY PENERIMAAN TRUCKING HEADER",
                'statusapproval' => $statusApproval->id,
                'pelanggan_id' => 0,
                'agen_id' => 0,
                'diterimadari' => "PENERIMAAN TRUCKING HEADER",
                'tgllunas' => date('Y-m-d', strtotime($data['tglbukti'])),
                'statusformat' => $format->id,
                'bank_id' => $penerimaanTruckingHeader->bank_id,

                'nowarkat' => null,
                'tgljatuhtempo' => $tgljatuhtempo,
                'nominal_detail' => $nominal_detail,
                'coadebet' => $coadebet_detail,
                'coakredit' => $coakredit_detail,
                'keterangan_detail' => $keterangan_detail,
                'invoice_nobukti' => null,
                'bankpelanggan_id' => null,
                'pelunasanpiutang_nobukti' => null,
                'bulanbeban' => null,
            ];
            $penerimaanHeader = (new PenerimaanHeader())->processStore($penerimaanRequest);

            $penerimaanTruckingHeader->penerimaan_nobukti = $penerimaanHeader->nobukti;
            $penerimaanTruckingHeader->save();
        }

        return $penerimaanTruckingHeader;
    }

    public function processUpdate(PenerimaanTruckingHeader $penerimaanTruckingHeader, array $data): PenerimaanTruckingHeader
    {
        $idpenerimaan = $data['penerimaantrucking_id'];
        $fetchFormat =  DB::table('penerimaantrucking')->where('id', $idpenerimaan)->first();

        $tanpaprosesnobukti = array_key_exists("tanpaprosesnobukti", $data) ? $data['tanpaprosesnobukti'] : 0;
        if ($fetchFormat->kodepenerimaan == 'PJP') {
            $data['coa'] = $fetchFormat->coapostingkredit;
        } else if ($fetchFormat->kodepenerimaan == 'BBM') {
            $data['coa'] = $fetchFormat->coakredit;
        }

        $statusformat = $fetchFormat->format;
        $fetchGrp = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('id', $statusformat)->first();
        $format = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', $fetchGrp->grp)->where('subgrp', $fetchGrp->subgrp)->first();

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        $coadebet = '';
        if ($tanpaprosesnobukti != 2) {
            // throw new \Exception($data['bank_id']);
            $bank = $data['bank_id'];
            $querySubgrpPenerimaan = Bank::from(DB::raw("bank with (readuncommitted)"))
                ->select('parameter.grp', 'parameter.subgrp', 'bank.formatpenerimaan', 'bank.coa', 'bank.tipe')
                ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')
                ->where('bank.id', $data['bank_id'])
                ->first();
            $coadebet = $querySubgrpPenerimaan->coa;
        }

        $penerimaanTruckingHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $penerimaanTruckingHeader->bank_id = $data['bank_id'];
        $penerimaanTruckingHeader->coa = $data['coa'] ?? '';
        $penerimaanTruckingHeader->supir_id = $data['supirheader_id'] ?? '';
        $penerimaanTruckingHeader->modifiedby = auth('api')->user()->name;

        if (!$penerimaanTruckingHeader->save()) {
            throw new \Exception("Error storing Penerimaan Trucking header.");
        }

        $penerimaanTruckingHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerimaanTruckingHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY penerimaan Trucking Header '),
            'idtrans' => $penerimaanTruckingHeader->id,
            'nobuktitrans' => $penerimaanTruckingHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $penerimaanTruckingHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        /*DELETE EXISTING PenerimaanTruckingDetail*/
        $penerimaanTruckingDetail = PenerimaanTruckingDetail::where('penerimaantruckingheader_id', $penerimaanTruckingHeader->id)->lockForUpdate()->delete();

        $penerimaanTruckingDetails = [];
        for ($i = 0; $i < count($data['nominal']); $i++) {
            $penerimaanTruckingDetail = (new PenerimaanTruckingDetail())->processStore($penerimaanTruckingHeader, [
                'penerimaantruckingheader_id' => $penerimaanTruckingHeader->id,
                'nobukti' => $penerimaanTruckingHeader->nobukti,
                'supir_id' =>   $data['supir_id'][$i] ?? '',
                'pengeluarantruckingheader_nobukti' => $data['pengeluarantruckingheader_nobukti'][$i] ?? '',
                'keterangan' =>  $data['keterangan'][$i],
                'nominal' =>  $data['nominal'][$i],
                'modifiedby' => $penerimaanTruckingHeader->modifiedby,
            ]);
            $penerimaanDetails[] = $penerimaanTruckingDetail->toArray();
            $coakredit_detail[] = $data['coa'];
            $coadebet_detail[] = $coadebet;
            $nominal_detail[] = $data['nominal'][$i];
            $keterangan_detail[] = $data['keterangan'][$i];
            $tgljatuhtempo[] = date('Y-m-d', strtotime($data['tglbukti']));
        }

        $penerimaanTruckingDetailLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerimaanTruckingHeaderLogTrail->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY penerimaan Trucking detail '),
            'idtrans' => $penerimaanTruckingHeaderLogTrail->id,
            'nobuktitrans' => $penerimaanTruckingHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $penerimaanTruckingDetails,
            'modifiedby' => auth('api')->user()->user
        ]);

        //if tanpaprosesnobukti NOT 2 STORE PENERIMAAN
        if ($tanpaprosesnobukti != 2) {


            /*UPDATE PENERIMAAN*/
            $penerimaanRequest = [
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'postingdari' => (array_key_exists("postingdari", $data)) ? $data['postingdari'] : "ENTRY PENERIMAAN TRUCKING HEADER",
                'statusapproval' => $statusApproval->id,
                'pelanggan_id' => 0,
                'agen_id' => 0,
                'diterimadari' => "PENERIMAAN TRUCKING HEADER",
                'tgllunas' => date('Y-m-d', strtotime($data['tglbukti'])),
                'statusformat' => $format->id,
                'bank_id' => $penerimaanTruckingHeader->bank_id,

                'nowarkat' => null,
                'tgljatuhtempo' => $tgljatuhtempo,
                'nominal_detail' => $nominal_detail,
                'coadebet' => $coadebet_detail,
                'coakredit' => $coakredit_detail,
                'keterangan_detail' => $keterangan_detail,
                'invoice_nobukti' => null,
                'bankpelanggan_id' => null,
                'pelunasanpiutang_nobukti' => null,
                'bulanbeban' => null,
            ];
            $penerimaanHeader = PenerimaanHeader::where('nobukti', $penerimaanTruckingHeader->penerimaan_nobukti)->first();
            (new PenerimaanHeader())->processUpdate($penerimaanHeader, $penerimaanRequest);
        }

        return $penerimaanTruckingHeader;
    }


    public function processDestroy($id, $postingDari): PenerimaanTruckingHeader
    {
        $penerimaanTruckingDetails = PenerimaanTruckingDetail::lockForUpdate()->where('penerimaantruckingheader_id', $id)->get();

        $penerimaanTruckingHeader = new penerimaanTruckingHeader();
        $penerimaanTruckingHeader = $penerimaanTruckingHeader->lockAndDestroy($id);

        $penerimaanTruckingHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $penerimaanTruckingHeader->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $penerimaanTruckingHeader->id,
            'nobuktitrans' => $penerimaanTruckingHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $penerimaanTruckingHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'penerimaantruckingdetail',
            'postingdari' => $postingDari,
            'idtrans' => $penerimaanTruckingHeaderLogTrail['id'],
            'nobuktitrans' => $penerimaanTruckingHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $penerimaanTruckingDetails->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        if ($postingDari != 'EDIT GAJI SUPIR' && $postingDari != 'DELETE GAJI SUPIR') {
            dd($postingDari);

            $penerimaanHeader = PenerimaanHeader::where('nobukti', $penerimaanTruckingHeader->penerimaan_nobukti)->first();
            // throw new \Exception($penerimaanHeader->nobukti);

            (new PenerimaanHeader())->processDestroy($penerimaanHeader->id, $postingDari);
            $penerimaanTruckingHeader->delete();
        }
        return $penerimaanTruckingHeader;
    }
}
