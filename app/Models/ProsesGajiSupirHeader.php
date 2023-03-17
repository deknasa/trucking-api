<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProsesGajiSupirHeader extends MyModel
{
    use HasFactory;

    protected $table = 'prosesgajisupirheader';

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

        $query = DB::table($this->table)->from(DB::raw("prosesgajisupirheader with (readuncommitted)"))
            ->select(
                'prosesgajisupirheader.id',
                'prosesgajisupirheader.nobukti',
                'prosesgajisupirheader.tglbukti',
                'prosesgajisupirheader.tgldari',
                'prosesgajisupirheader.tglsampai',
                'statusapproval.memo as statusapproval',
                'prosesgajisupirheader.userapproval',
                DB::raw('(case when (year(prosesgajisupirheader.tglapproval) <= 2000) then null else prosesgajisupirheader.tglapproval end ) as tglapproval'),
                DB::raw('(case when (year(prosesgajisupirheader.tglbukacetak) <= 2000) then null else prosesgajisupirheader.tglbukacetak end ) as tglbukacetak'),
                'statuscetak.memo as statuscetak',
                'prosesgajisupirheader.userbukacetak',
                'prosesgajisupirheader.jumlahcetak',
                'prosesgajisupirheader.periode',
                'prosesgajisupirheader.modifiedby',
                'prosesgajisupirheader.created_at',
                'prosesgajisupirheader.updated_at',
            )
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'prosesgajisupirheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'prosesgajisupirheader.statusapproval', 'statusapproval.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function getEdit($gajiId)
    {
        $this->setRequestParameters();
        $query = ProsesGajiSupirDetail::from(DB::raw("prosesgajisupirdetail with (readuncommitted)"))
            ->select(
                'gajisupirheader.id as idric',
                'prosesgajisupirdetail.gajisupir_nobukti as nobuktiric',
                'gajisupirheader.tglbukti as tglbuktiric',
                'supir.namasupir as supir_id',
                'gajisupirheader.tgldari as tgldariric',
                'gajisupirheader.tglsampai as tglsampairic',
                'gajisupirheader.nominal'
            )
            ->leftJoin(DB::raw("gajisupirheader with (readuncommitted)"), 'prosesgajisupirdetail.gajisupir_nobukti', 'gajisupirheader.nobukti')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'gajisupirheader.supir_id', 'supir.id')
            ->where('prosesgajisupirdetail.prosesgajisupir_id', $gajiId);

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $query->orderBy('gajisupirheader.' . $this->params['sortIndex'], $this->params['sortOrder']);
        $this->filter($query);
        $this->paginate($query);
        $data = $query->get();

        $this->totalNominal = $query->sum('gajisupirheader.nominal');
        return $data;
    }
    public function selectColumns($query)
    {
        return $query->select(
            DB::raw("
            $this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            $this->table.tgldari,
            $this->table.tglsampai,
            'statusapproval.text as statusapproval',
            $this->table.userapproval,
            $this->table.tglapproval,
            'statuscetak.text as statuscetak',
            $this->table.userbukacetak,
            $this->table.tglbukacetak,
            $this->table.jumlahcetak,
            $this->table.periode,
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at
            ")
        )
            ->leftJoin('parameter as statuscetak', 'prosesgajisupirheader.statuscetak', 'statuscetak.id')
            ->leftJoin('parameter as statusapproval', 'prosesgajisupirheader.statusapproval', 'statusapproval.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti')->default('');
            $table->date('tgldari')->default('');
            $table->date('tglsampai')->default('');
            $table->string('statusapproval', 1000)->default('');
            $table->string('userapproval', 1000)->default('');
            $table->date('tglapproval')->default('');
            $table->string('statuscetak', 1000)->default('');
            $table->string('userbukacetak', 50)->default('');
            $table->date('tglbukacetak')->default('1900/1/1');
            $table->integer('jumlahcetak')->Length(11)->default('0');
            $table->date('periode')->default('');
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
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'tgldari', 'tglsampai', 'statusapproval', 'userapproval', 'tglapproval', 'statuscetak', 'userbukacetak', 'tglbukacetak', 'jumlahcetak', 'periode', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function getRic($dari, $sampai)
    {
        $getRic = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->whereRaw("tglbukti >= '$dari'")->whereRaw("tglbukti <= '$sampai'");
    }

    public function getPotSemua($dari, $sampai)
    {
        $gajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->whereRaw("tglbukti >= '$dari'")->whereRaw("tglbukti <= '$sampai'")
            ->whereRaw("gajisupirheader.nobukti not in(select gajisupir_nobukti from prosesgajisupirdetail)")->get();

        $total = 0;
        foreach ($gajiSupir as $key => $value) {
            $ricId = $value->id;
            $potongan = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
                ->select('nominal')
                ->where('gajisupir_id', $ricId)
                ->where('supir_id', 0)
                ->get();

            $nominal = $potongan->sum('nominal');
            if ($nominal != 0) {
                $total = $total + $nominal;
            }
        }
        return $total;
    }

    public function getPotPribadi($dari, $sampai)
    {
        $gajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->whereRaw("tglbukti >= '$dari'")->whereRaw("tglbukti <= '$sampai'")
            ->whereRaw("gajisupirheader.nobukti not in(select gajisupir_nobukti from prosesgajisupirdetail)")->get();
        $total = 0;
        foreach ($gajiSupir as $key => $value) {
            $ricId = $value->id;
            $potongan = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
                ->select('nominal')
                ->where('gajisupir_id', $ricId)
                ->where('supir_id', '!=', 0)
                ->get();

            $nominal = $potongan->sum('nominal');
            if ($nominal != 0) {
                $total = $total + $nominal;
            }
        }
        return $total;
    }

    public function getDeposito($dari, $sampai)
    {
        $gajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->whereRaw("tglbukti >= '$dari'")->whereRaw("tglbukti <= '$sampai'")
            ->whereRaw("gajisupirheader.nobukti not in(select gajisupir_nobukti from prosesgajisupirdetail)")->get();
        $total = 0;
        foreach ($gajiSupir as $key => $value) {
            $ricId = $value->id;
            $potongan = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))
                ->where('gajisupir_id', $ricId)
                ->first();

            if ($potongan != null) {
                $total = $total + $potongan->nominal;
            }
        }
        return $total;
    }

    public function getBBM($dari, $sampai)
    {
        $gajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->whereRaw("tglbukti >= '$dari'")->whereRaw("tglbukti <= '$sampai'")
            ->whereRaw("gajisupirheader.nobukti not in(select gajisupir_nobukti from prosesgajisupirdetail)")->get();
        $total = 0;
        foreach ($gajiSupir as $key => $value) {
            $ricId = $value->id;
            $potongan = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))
                ->where('gajisupir_id', $ricId)
                ->first();

            if ($potongan != null) {
                $total = $total + $potongan->nominal;
            }
        }
        return $total;
    }

    public function getPinjaman($dari, $sampai)
    {
        $gajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->whereRaw("tglbukti >= '$dari'")->whereRaw("tglbukti <= '$sampai'")
            ->whereRaw("gajisupirheader.nobukti not in(select gajisupir_nobukti from prosesgajisupirdetail)")->get();
        $total = 0;
        foreach ($gajiSupir as $key => $value) {
            $ricId = $value->id;
            $potongan = GajiSupirPinjaman::from(DB::raw("gajisupirpinjaman with (readuncommitted)"))
                ->where('gajisupir_id', $ricId)
                ->first();

            if ($potongan != null) {
                $total = $total + $potongan->nominal;
            }
        }
        return $total;
    }

    public function showPotSemua($id)
    {
        $gajidetail = ProsesGajiSupirDetail::from(DB::raw("prosesgajisupirdetail with (readuncommitted)"))->where('prosesgajisupir_id', $id)->get();
        $total = 0;
        $tes = '';
        foreach ($gajidetail as $key => $value) {
            $potongan = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
                ->where('gajisupir_nobukti', $value->gajisupir_nobukti)
                ->where('supir_id', '0')
                ->get();

            $nominal = $potongan->sum('nominal');
            if ($nominal != 0) {
                $total = $total + $nominal;
            }

            $fetchPS = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $value->gajisupir_nobukti)->where('supir_id', '0')->first();
            if (isset($fetchPS)) {
                $tes = $fetchPS->penerimaantrucking_nobukti;
            }
        }

        
        if ($tes != '') {

            $penerimaan = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                ->select('penerimaantruckingheader.bank_id', 'bank.namabank as bank', 'penerimaantruckingheader.penerimaan_nobukti')
                ->join(DB::raw("bank with (readuncommitted)"), 'penerimaantruckingheader.bank_id', 'bank.id')->where('nobukti', $tes)->first();
            $data = [
                'bank_idPS' => $penerimaan->bank_id,
                'bankPS' => $penerimaan->bank,
                'nobuktiPS' => $penerimaan->penerimaan_nobukti,
                'nomPS' => $total
            ];
            return $data;
        }
    }
    public function showPotPribadi($id)
    {
        $gajidetail = ProsesGajiSupirDetail::from(DB::raw("prosesgajisupirdetail with (readuncommitted)"))->where('prosesgajisupir_id', $id)->get();
        $total = 0;
        $tes = '';
        foreach ($gajidetail as $key => $value) {
            $potongan = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
                ->where('gajisupir_nobukti', $value->gajisupir_nobukti)
                ->where('supir_id', '!=' ,'0')
                ->get();

            $nominal = $potongan->sum('nominal');
            if ($nominal != 0) {
                $total = $total + $nominal;
            }

            $fetchPP = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $value->gajisupir_nobukti)->where('supir_id', '!=' , '0')->first();
            if (isset($fetchPP)) {
                $tes = $fetchPP->penerimaantrucking_nobukti;
            }
        }

        
        if ($tes != '') {

            $penerimaan = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                ->select('penerimaantruckingheader.bank_id', 'bank.namabank as bank', 'penerimaantruckingheader.penerimaan_nobukti')
                ->join(DB::raw("bank with (readuncommitted)"), 'penerimaantruckingheader.bank_id', 'bank.id')->where('nobukti', $tes)->first();
            $data = [
                'bank_idPP' => $penerimaan->bank_id,
                'bankPP' => $penerimaan->bank,
                'nobuktiPP' => $penerimaan->penerimaan_nobukti,
                'nomPP' => $total
            ];
            return $data;
        }
    }
    public function showDeposito($id)
    {
        $gajidetail = ProsesGajiSupirDetail::from(DB::raw("prosesgajisupirdetail with (readuncommitted)"))->where('prosesgajisupir_id', $id)->get();
        $total = 0;
        $tes = '';
        foreach ($gajidetail as $key => $value) {
            $potongan = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))
                ->where('gajisupir_nobukti', $value->gajisupir_nobukti)
                ->get();

            $nominal = $potongan->sum('nominal');
            if ($nominal != 0) {
                $total = $total + $nominal;
            }

            $fetchDeposito = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))->where('gajisupir_nobukti', $value->gajisupir_nobukti)->first();
            if (isset($fetchDeposito)) {
                $tes = $fetchDeposito->penerimaantrucking_nobukti;
            }
        }

        
        if ($tes != '') {

            $penerimaan = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                ->select('penerimaantruckingheader.bank_id', 'bank.namabank as bank', 'penerimaantruckingheader.penerimaan_nobukti')
                ->join(DB::raw("bank with (readuncommitted)"), 'penerimaantruckingheader.bank_id', 'bank.id')->where('nobukti', $tes)->first();
            $data = [
                'bank_idDeposito' => $penerimaan->bank_id,
                'bankDeposito' => $penerimaan->bank,
                'nobuktiDeposito' => $penerimaan->penerimaan_nobukti,
                'nomDeposito' => $total
            ];
            return $data;
        }
    }
    public function showBBM($id)
    {
        $gajidetail = ProsesGajiSupirDetail::from(DB::raw("prosesgajisupirdetail with (readuncommitted)"))->where('prosesgajisupir_id', $id)->get();
        $total = 0;
        $tes = '';
        foreach ($gajidetail as $key => $value) {
            $potongan = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))
                ->where('gajisupir_nobukti', $value->gajisupir_nobukti)
                ->get();

            $nominal = $potongan->sum('nominal');
            if ($nominal != 0) {
                $total = $total + $nominal;
            }

            $fetchBBM = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))->where('gajisupir_nobukti', $value->gajisupir_nobukti)->first();
            if (isset($fetchBBM)) {
                $tes = $fetchBBM->penerimaantrucking_nobukti;
            }
        }

        
        if ($tes != '') {

            $penerimaan = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                ->select('penerimaantruckingheader.bank_id', 'bank.namabank as bank', 'penerimaantruckingheader.penerimaan_nobukti')
                ->join(DB::raw("bank with (readuncommitted)"), 'penerimaantruckingheader.bank_id', 'bank.id')->where('nobukti', $tes)->first();
            $data = [
                'bank_idBBM' => $penerimaan->bank_id,
                'bankBBM' => $penerimaan->bank,
                'nobuktiBBM' => $penerimaan->penerimaan_nobukti,
                'nomBBM' => $total
            ];
            return $data;
        }
    }
    public function showPinjaman($id)
    {
        $gajidetail = ProsesGajiSupirDetail::from(DB::raw("prosesgajisupirdetail with (readuncommitted)"))->where('prosesgajisupir_id', $id)->get();
        $total = 0;
        $tes = '';
        foreach ($gajidetail as $key => $value) {
            $potongan = GajiSupirPinjaman::from(DB::raw("gajisupirpinjaman with (readuncommitted)"))
                ->where('gajisupir_nobukti', $value->gajisupir_nobukti)
                ->get();

            $nominal = $potongan->sum('nominal');
            if ($nominal != 0) {
                $total = $total + $nominal;
            }

            $fetchBBM = GajiSupirPinjaman::from(DB::raw("gajisupirpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $value->gajisupir_nobukti)->first();
            if (isset($fetchBBM)) {
                $tes = $fetchBBM->pengeluarantrucking_nobukti;
            }
        }

        
        if ($tes != '') {

            $pengeluaran = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
                ->select('pengeluarantruckingheader.bank_id', 'bank.namabank as bank', 'pengeluarantruckingheader.pengeluaran_nobukti')
                ->join(DB::raw("bank with (readuncommitted)"), 'pengeluarantruckingheader.bank_id', 'bank.id')->where('nobukti', $tes)->first();
            $data = [
                'bank_idPinjaman' => $pengeluaran->bank_id,
                'bankPinjaman' => $pengeluaran->bank,
                'nobuktiPinjaman' => $pengeluaran->pengeluaran_nobukti,
                'nomPinjaman' => $total
            ];
            return $data;
        }
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
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('statusapproval.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statuscetak') {
                            $query = $query->where('statuscetak.text', '=', "$filters[data]");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->orWhere('statusapproval.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statuscetak') {
                            $query = $query->orWhere('statuscetak.text', '=', "$filters[data]");
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

        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
