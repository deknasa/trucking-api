<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PemutihanSupir extends MyModel
{
    use HasFactory;
    protected $table = 'pemutihansupirheader';

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

        $query = PemutihanSupir::from(DB::raw("pemutihansupirheader with (readuncommitted)"))
            ->select(
                'pemutihansupirheader.id',
                'pemutihansupirheader.nobukti',
                'pemutihansupirheader.tglbukti',
                'supir.namasupir as supir',
                'bank.namabank as bank',
                'pemutihansupirheader.penerimaan_nobukti',
                'akunpusat.keterangancoa as coa',
                'pemutihansupirheader.pengeluaransupir',
                'pemutihansupirheader.penerimaansupir',
                'pemutihansupirheader.modifiedby',
                'pemutihansupirheader.created_at',
                'pemutihansupirheader.updated_at'

            )
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pemutihansupirheader.supir_id', 'supir.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pemutihansupirheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pemutihansupirheader.coa', 'akunpusat.coa');

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
        $query = PemutihanSupir::from(DB::raw("pemutihansupirheader with (readuncommitted)"))
            ->select(
                'pemutihansupirheader.id',
                'pemutihansupirheader.nobukti',
                'pemutihansupirheader.tglbukti',
                'pemutihansupirheader.supir_id',
                'pemutihansupirheader.bank_id',
                'pemutihansupirheader.penerimaan_nobukti',
                'supir.namasupir as supir',
                'bank.namabank as bank'
            )
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pemutihansupirheader.supir_id', 'supir.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pemutihansupirheader.bank_id', 'bank.id')
            ->where('pemutihansupirheader.id', $id);

        return $query->first();
    }

    // public function getDataPemutihan($supirId)
    // {
    //     $kodePJT = PengeluaranTrucking::where('kodepengeluaran', 'PJT')->first();
    //     $kodePJP = PenerimaanTrucking::where('kodepenerimaan', 'PJP')->first();
    //     $pjt = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
    //         ->select(DB::raw("SUM(pengeluarantruckingdetail.nominal) as nominal"))
    //         ->join(DB::raw("pengeluarantruckingdetail with (readuncommitted)"), "pengeluarantruckingheader.id", "pengeluarantruckingdetail.pengeluarantruckingheader_id")
    //         ->where("pengeluarantruckingheader.pengeluarantrucking_id", $kodePJT->id)
    //         ->where("pengeluarantruckingdetail.supir_id", $supirId)
    //         ->first();
    //     $pjp = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
    //         ->select(DB::raw("SUM(penerimaantruckingdetail.nominal) as nominal"))
    //         ->join(DB::raw("penerimaantruckingdetail with (readuncommitted)"), "penerimaantruckingheader.id", "penerimaantruckingdetail.penerimaantruckingheader_id")
    //         ->where("penerimaantruckingheader.penerimaantrucking_id", $kodePJP->id)
    //         ->where("penerimaantruckingdetail.supir_id", $supirId)
    //         ->first();
    //     $pengeluaran = $pjt->nominal - $pjp->nominal;

    //     $kodePDT = PengeluaranTrucking::where('kodepengeluaran', 'PDT')->first();
    //     $kodeDPO = PenerimaanTrucking::where('kodepenerimaan', 'DPO')->first();
    //     $pdt = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
    //         ->select(DB::raw("SUM(pengeluarantruckingdetail.nominal) as nominal"))
    //         ->join(DB::raw("pengeluarantruckingdetail with (readuncommitted)"), "pengeluarantruckingheader.id", "pengeluarantruckingdetail.pengeluarantruckingheader_id")
    //         ->where("pengeluarantruckingheader.pengeluarantrucking_id", $kodePDT->id)
    //         ->where("pengeluarantruckingdetail.supir_id", $supirId)
    //         ->first();
    //     $dpo = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
    //         ->select(DB::raw("SUM(penerimaantruckingdetail.nominal) as nominal"))
    //         ->join(DB::raw("penerimaantruckingdetail with (readuncommitted)"), "penerimaantruckingheader.id", "penerimaantruckingdetail.penerimaantruckingheader_id")
    //         ->where("penerimaantruckingheader.penerimaantrucking_id", $kodeDPO->id)
    //         ->where("penerimaantruckingdetail.supir_id", $supirId)
    //         ->first();

    //     $penerimaan = $dpo->nominal - $pdt->nominal;
    //     return [
    //         'pengeluaran' => $pengeluaran,
    //         'penerimaan' => $penerimaan
    //     ];
    // }


    public function getPosting($supirId)
    {
        $tempSisa = '##tempSisa' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempSisa, function ($table) {
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->double('sisa', 15, 2)->nullable();
            $table->text('keterangan')->nullable();
        });

        $querySisa = DB::table('pengeluarantruckingdetail')->from(
            DB::raw("pengeluarantruckingdetail with (readuncommitted)")
        )
            ->select(
                'pengeluarantruckingdetail.nobukti',
                'pengeluarantruckingdetail.nominal',
                DB::raw("(SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail 
	            WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"),
                'pengeluarantruckingdetail.keterangan'
            )->where('pengeluarantruckingdetail.supir_id', $supirId);

        DB::table($tempSisa)->insertUsing([
            'nobukti',
            'nominal',
            'sisa',
            'keterangan'
        ], $querySisa);

        $this->setRequestParameters();

        $query = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
            ->select(DB::raw("
            row_number() Over(Order By pengeluarantruckingheader.id) as id_posting,
            pengeluarantruckingheader.nobukti as nobukti_posting, 
            pengeluarantruckingheader.tglbukti as tglbukti_posting, 
            pengeluarantruckingheader.pengeluaran_nobukti as pengeluaran_posting,
            c.nominal as nominal_posting,
            c.sisa AS sisa_posting,
            c.keterangan AS keterangan_posting
        "))
            ->join(DB::raw("$tempSisa as c with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'c.nobukti')
            ->where('pengeluarantruckingheader.pengeluaran_nobukti', '!=', '');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sortPosting($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();
        return $data;
    }

    public function getNonposting($supirId)
    {
        $tempSisa = '##tempSisa' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempSisa, function ($table) {
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->double('sisa', 15, 2)->nullable();
            $table->text('keterangan')->nullable();
        });

        $querySisa = DB::table('pengeluarantruckingdetail')->from(
            DB::raw("pengeluarantruckingdetail with (readuncommitted)")
        )
            ->select(
                'pengeluarantruckingdetail.nobukti',
                'pengeluarantruckingdetail.nominal',
                DB::raw("(SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail 
	            WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"),
                'pengeluarantruckingdetail.keterangan'
            )->where('pengeluarantruckingdetail.supir_id', $supirId);

        DB::table($tempSisa)->insertUsing([
            'nobukti',
            'nominal',
            'sisa',
            'keterangan'
        ], $querySisa);

        $this->setRequestParameters();

        $query = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
            ->select(DB::raw("
            row_number() Over(Order By pengeluarantruckingheader.id) as id_nonposting,
            pengeluarantruckingheader.nobukti as nobukti_nonposting, 
            pengeluarantruckingheader.tglbukti as tglbukti_nonposting, 
            pengeluarantruckingheader.pengeluaran_nobukti as pengeluaran_nonposting,
            c.nominal as nominal_nonposting,
            c.sisa AS sisa_nonposting,
            c.keterangan AS keterangan_nonposting
        "))
            ->join(DB::raw("$tempSisa as c with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'c.nobukti')
            ->where('pengeluarantruckingheader.pengeluaran_nobukti', '');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sortPosting($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();
        return $data;
    }


    public function getEditPost($id, $supir_id)
    {
        $temp = $this->createTempEdit($id, $supir_id, 'post');
        $this->setRequestParameters();
        $query = DB::table($temp)->from(DB::raw("$temp as A with (readuncommitted)"))
            ->select(
                DB::raw("row_number() Over(Order By A.tglbukti) as id_posting"),
                'A.nobukti as nobukti_posting',
                'A.tglbukti as tglbukti_posting',
                'A.pengeluaran as pengeluaran_posting',
                'A.nominal as nominal_posting',
                'A.sisa as sisa_posting',
                'A.keterangan as keterangan_posting'
            );
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sortEdit('A', $query);
        $this->filterEdit('A', $query);
        $this->paginate($query);

        $data = $query->get();
        return $data;
    }

    public function getEditNonPost($id, $supir_id)
    {
        $temp = $this->createTempEdit($id, $supir_id, 'non');
        $this->setRequestParameters();
        $query = DB::table($temp)->from(DB::raw("$temp as B with (readuncommitted)"))
            ->select(
                DB::raw("row_number() Over(Order By B.tglbukti) as id_nonposting"),
                'B.nobukti as nobukti_nonposting',
                'B.tglbukti as tglbukti_nonposting',
                'B.pengeluaran as pengeluaran_nonposting',
                'B.nominal as nominal_nonposting',
                'B.sisa as sisa_nonposting',
                'B.keterangan as keterangan_nonposting'
            );
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sortEdit('B', $query);
        $this->filterEdit('B', $query);
        $this->paginate($query);

        $data = $query->get();
        return $data;
    }

    public function createTempEdit($id, $supir_id, $aksi)
    {
        $tempEdit = '##tempEdit' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempEdit, function ($table) {
            $table->string('nobukti', 100)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('pengeluaran', 100)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->double('sisa', 15, 2)->nullable();
            $table->text('keterangan')->nullable();
        });

        $fetch = PemutihanSupirDetail::from(DB::raw("pemutihansupirdetail with (readuncommitted)"))
            ->select(DB::raw("
                    pemutihansupirdetail.pengeluarantrucking_nobukti as nobukti, 
                    pengeluarantruckingheader.tglbukti, 
                    pengeluarantruckingheader.pengeluaran_nobukti as pengeluaran,
                    pengeluarantruckingdetail.nominal,
                    (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail 
                        WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa,
                    pengeluarantruckingdetail.keterangan
                "))
            ->join(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pemutihansupirdetail.pengeluarantrucking_nobukti', 'pengeluarantruckingheader.nobukti')
            ->join(DB::raw("pengeluarantruckingdetail with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'pengeluarantruckingdetail.nobukti')
            ->where('pemutihansupirdetail.pemutihansupir_id', $id);

        if ($aksi == 'post') {
            $fetch->where('pengeluarantruckingheader.pengeluaran_nobukti', '!=', '');
        } else if ($aksi == 'non') {
            $fetch->where('pengeluarantruckingheader.pengeluaran_nobukti', '');
        }

        DB::table($tempEdit)->insertUsing([
            'nobukti',
            'tglbukti',
            'pengeluaran',
            'nominal',
            'sisa',
            'keterangan'
        ], $fetch);

        $fetch2 = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
            ->select(DB::raw("
                pengeluarantruckingheader.nobukti,
                pengeluarantruckingheader.tglbukti, 
                pengeluarantruckingheader.pengeluaran_nobukti as pengeluaran,
                pengeluarantruckingdetail.nominal,
                (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail 
                    WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa,
                pengeluarantruckingdetail.keterangan
        "))
            ->join(DB::raw("pengeluarantruckingdetail with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'pengeluarantruckingdetail.nobukti')
            ->where("pengeluarantruckingdetail.supir_id", $supir_id);
        if ($aksi == 'post') {
            $fetch2->where('pengeluarantruckingheader.pengeluaran_nobukti', '!=', '');
        } else if ($aksi == 'non') {
            $fetch2->where('pengeluarantruckingheader.pengeluaran_nobukti', '');
        }

        $fetch2->whereRaw("pengeluarantruckingheader.nobukti not in (select pengeluarantrucking_nobukti from pemutihansupirdetail)");

        DB::table($tempEdit)->insertUsing([
            'nobukti',
            'tglbukti',
            'pengeluaran',
            'nominal',
            'sisa',
            'keterangan'
        ], $fetch2);

        return $tempEdit;
    }

    public function getDeletePost($id, $supir_id)
    {
        $tempSisa = '##tempSisa' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempSisa, function ($table) {
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->double('sisa', 15, 2)->nullable();
            $table->text('keterangan')->nullable();
        });

        $querySisa = DB::table('pengeluarantruckingdetail')->from(
            DB::raw("pengeluarantruckingdetail with (readuncommitted)")
        )
            ->select(
                'pengeluarantruckingdetail.nobukti',
                'pengeluarantruckingdetail.nominal',
                DB::raw("(SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail 
	            WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"),
                'pengeluarantruckingdetail.keterangan'
            )->where('pengeluarantruckingdetail.supir_id', $supir_id);

        DB::table($tempSisa)->insertUsing([
            'nobukti',
            'nominal',
            'sisa',
            'keterangan'
        ], $querySisa);
        $this->setRequestParameters();

        $query = PemutihanSupirDetail::from(DB::raw("pemutihansupirdetail with (readuncommitted)"))
            ->select(DB::raw("
            row_number() Over(Order By pengeluarantruckingheader.id) as id_posting,
            pemutihansupirdetail.pengeluarantrucking_nobukti as nobukti_posting, 
            pengeluarantruckingheader.tglbukti as tglbukti_posting, 
            pengeluarantruckingheader.pengeluaran_nobukti as pengeluaran_posting,
            c.nominal as nominal_posting,
            c.sisa AS sisa_posting,
            c.keterangan AS keterangan_posting
        "))
            ->join(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pemutihansupirdetail.pengeluarantrucking_nobukti', 'pengeluarantruckingheader.nobukti')
            ->join(DB::raw("$tempSisa as c with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'c.nobukti')
            ->where('pemutihansupirdetail.pemutihansupir_id', $id)
            ->where('pengeluarantruckingheader.pengeluaran_nobukti', '!=', '');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sortPosting($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();
        return $data;
    }
    public function getDeleteNonPost($id, $supir_id)
    {
        $tempSisa = '##tempSisa' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempSisa, function ($table) {
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->double('sisa', 15, 2)->nullable();
            $table->text('keterangan')->nullable();
        });

        $querySisa = DB::table('pengeluarantruckingdetail')->from(
            DB::raw("pengeluarantruckingdetail with (readuncommitted)")
        )
            ->select(
                'pengeluarantruckingdetail.nobukti',
                'pengeluarantruckingdetail.nominal',
                DB::raw("(SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail 
	            WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"),
                'pengeluarantruckingdetail.keterangan'
            )->where('pengeluarantruckingdetail.supir_id', $supir_id);

        DB::table($tempSisa)->insertUsing([
            'nobukti',
            'nominal',
            'sisa',
            'keterangan'
        ], $querySisa);
        $this->setRequestParameters();

        $query = PemutihanSupirDetail::from(DB::raw("pemutihansupirdetail with (readuncommitted)"))
            ->select(DB::raw("
            row_number() Over(Order By pengeluarantruckingheader.id) as id_nonposting,
            pemutihansupirdetail.pengeluarantrucking_nobukti as nobukti_nonposting, 
            pengeluarantruckingheader.tglbukti as tglbukti_nonposting, 
            pengeluarantruckingheader.pengeluaran_nobukti as pengeluaran_nonposting,
            c.nominal as nominal_nonposting,
            c.sisa AS sisa_nonposting,
            c.keterangan AS keterangan_nonposting
        "))
            ->join(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pemutihansupirdetail.pengeluarantrucking_nobukti', 'pengeluarantruckingheader.nobukti')
            ->join(DB::raw("$tempSisa as c with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'c.nobukti')
            ->where('pemutihansupirdetail.pemutihansupir_id', $id)
            ->where('pengeluarantruckingheader.pengeluaran_nobukti', '');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sortPosting($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();
        return $data;
    }

    public function getPJT($supirId)
    {
        $temp = '##tempPJT' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $pjt = PengeluaranTrucking::where('kodepengeluaran', 'PJT')->first();

        $fetch = DB::table('pengeluarantruckingheader')->from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
            ->select(DB::raw("(SELECT (SUM(pengeluarantruckingdetail.nominal))
            FROM pengeluarantruckingdetail 
            WHERE pengeluarantruckingdetail.pengeluarantruckingheader_id= pengeluarantruckingheader.id and pengeluarantruckingdetail.supir_id=1) AS nominal"))
            ->join("pengeluarantruckingdetail", "pengeluarantruckingheader.id",  "pengeluarantruckingdetail.pengeluarantruckingheader_id")
            ->whereRaw("pengeluarantruckingheader.pengeluarantrucking_id = $pjt->id")
            ->whereRaw("pengeluarantruckingdetail.supir_id = $supirId");
        Schema::create($temp, function ($table) {
            $table->bigInteger('nominal');
        });

        $tes = DB::table($temp)->insertUsing(['nominal'], $fetch);

        return $temp;
    }

    public function selectColumns($query)
    {
        return $query->select(
            DB::raw("
                $this->table.id,
                $this->table.nobukti,
                $this->table.tglbukti,
                'supir.namasupir as supir',
                $this->table.pengeluaransupir,
                $this->table.penerimaansupir,
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at
            ")
        )
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pemutihansupirheader.supir_id', 'supir.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('supir', 1000)->nullable();
            $table->float('pengeluaransupir')->nullable();
            $table->float('penerimaansupir')->nullable();
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
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'supir', 'pengeluaransupir', 'penerimaansupir', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function supir()
    {
        return $this->belongsTo(Supir::class);
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'supir') {
            return $query->orderBy('supir.namasupir', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'bank') {
            return $query->orderBy('bank.namabank', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'coa') {
            return $query->orderBy('akunpusat.keterangancoa', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function sortPosting($query)
    {
        if ($this->params['sortIndex'] == 'nobukti_posting' || $this->params['sortIndex'] == 'nobukti_nonposting') {
            return $query->orderBy('pengeluarantruckingheader.nobukti', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'tglbukti_posting' || $this->params['sortIndex'] == 'tglbukti_nonposting') {
            return $query->orderBy('pengeluarantruckingheader.tglbukti', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'pengeluaran_posting' || $this->params['sortIndex'] == 'pengeluaran_nonposting') {
            return $query->orderBy('pengeluarantruckingheader.pengeluaran_nobukti', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'nominal_posting' || $this->params['sortIndex'] == 'nominal_nonposting') {
            return $query->orderBy('pengeluarantruckingheader.nominal', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'sisa_posting' || $this->params['sortIndex'] == 'sisa_nonposting') {
            return $query->orderBy('pengeluarantruckingheader.sisa', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'keterangan_posting' || $this->params['sortIndex'] == 'keterangan_nonposting') {
            return $query->orderBy('pengeluarantruckingheader.keterangan', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function sortEdit($table, $query)
    {
        if ($this->params['sortIndex'] == 'nobukti_posting' || $this->params['sortIndex'] == 'nobukti_nonposting') {
            return $query->orderBy($table . '.nobukti', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'tglbukti_posting' || $this->params['sortIndex'] == 'tglbukti_nonposting') {
            return $query->orderBy($table . '.tglbukti', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'pengeluaran_posting' || $this->params['sortIndex'] == 'pengeluaran_nonposting') {
            return $query->orderBy($table . '.pengeluaran', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'nominal_posting' || $this->params['sortIndex'] == 'nominal_nonposting') {
            return $query->orderBy($table . '.nominal', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'sisa_posting' || $this->params['sortIndex'] == 'sisa_nonposting') {
            return $query->orderBy($table . '.sisa', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'keterangan_posting' || $this->params['sortIndex'] == 'keterangan_nonposting') {
            return $query->orderBy($table . '.keterangan', $this->params['sortOrder']);
        }
    }


    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'supir') {
                            $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'bank') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'coa') {
                            $query = $query->where('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'nobukti_posting' || $filters['field'] == 'nobukti_nonposting') {
                            $query = $query->where('pengeluarantruckingheader.nobukti', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'tglbukti_posting' || $filters['field'] == 'tglbukti_nonposting') {
                            $query = $query->where('pengeluarantruckingheader.tglbukti', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'pengeluaran_posting' || $filters['field'] == 'pengeluaran_nonposting') {
                            $query = $query->where('pengeluarantruckingheader.pengeluaran_nobukti', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'nominal_posting' || $filters['field'] == 'nominal_nonposting') {
                            $query = $query->where('c.nominal', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'sisa_posting' || $filters['field'] == 'sisa_nonposting') {
                            $query = $query->where('c.sisa', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'keterangan_posting' || $filters['field'] == 'keterangan_nonposting') {
                            $query = $query->where('c.keterangan', 'LIKE', "%$filters[data]%");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . "." .  $filters['field'] . " LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'supir') {
                            $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'bank') {
                            $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'coa') {
                            $query = $query->orWhere('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'nobukti_posting' || $filters['field'] == 'nobukti_nonposting') {
                            $query = $query->orWhere('pengeluarantruckingheader.nobukti', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'tglbukti_posting' || $filters['field'] == 'tglbukti_nonposting') {
                            $query = $query->orWhere('pengeluarantruckingheader.tglbukti', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'pengeluaran_posting' || $filters['field'] == 'pengeluaran_nonposting') {
                            $query = $query->orWhere('pengeluarantruckingheader.pengeluaran_nobukti', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'nominal_posting' || $filters['field'] == 'nominal_nonposting') {
                            $query = $query->orWhere('c.nominal', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'sisa_posting' || $filters['field'] == 'sisa_nonposting') {
                            $query = $query->orWhere('c.sisa', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'keterangan_posting' || $filters['field'] == 'keterangan_nonposting') {
                            $query = $query->orWhere('c.keterangan', 'LIKE', "%$filters[data]%");
                        } else {
                            // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->OrwhereRaw($this->table . "." .  $filters['field'] . " LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

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
    public function filterEdit($table, $query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'nobukti_posting' || $filters['field'] == 'nobukti_nonposting') {
                            $query = $query->where($table . '.nobukti', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'tglbukti_posting' || $filters['field'] == 'tglbukti_nonposting') {
                            $query = $query->where($table . '.tglbukti', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'pengeluaran_posting' || $filters['field'] == 'pengeluaran_nonposting') {
                            $query = $query->where($table . '.pengeluaran', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'nominal_posting' || $filters['field'] == 'nominal_nonposting') {
                            $query = $query->where($table . '.nominal', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'sisa_posting' || $filters['field'] == 'sisa_nonposting') {
                            $query = $query->where($table . '.sisa', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($table . '.keterangan', 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'nobukti_posting' || $filters['field'] == 'nobukti_nonposting') {
                            $query = $query->orWhere($table . '.nobukti', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'tglbukti_posting' || $filters['field'] == 'tglbukti_nonposting') {
                            $query = $query->orWhere($table . '.tglbukti', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'pengeluaran_posting' || $filters['field'] == 'pengeluaran_nonposting') {
                            $query = $query->orWhere($table . '.pengeluaran', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'nominal_posting' || $filters['field'] == 'nominal_nonposting') {
                            $query = $query->orWhere($table . '.nominal', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'sisa_posting' || $filters['field'] == 'sisa_nonposting') {
                            $query = $query->orWhere($table . '.sisa', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->orWhere($table . '.keterangan', 'LIKE', "%$filters[data]%");
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
