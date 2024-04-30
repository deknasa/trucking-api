<?php

namespace App\Models;

use App\Services\RunningNumberService;
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
                'pemutihansupirheader.pengeluaran_nobukti',
                'akunpusat.keterangancoa as coa',
                'statuscetak.memo as statuscetak',
                'pemutihansupirheader.pengeluaransupir',
                'pemutihansupirheader.penerimaansupir',
                'pemutihansupirheader.penerimaantruckingposting_nobukti',
                'pemutihansupirheader.penerimaantruckingnonposting_nobukti',
                'pemutihansupirheader.modifiedby',
                'pemutihansupirheader.created_at',
                'pemutihansupirheader.updated_at',
                db::raw("cast((format(penerimaanheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpenerimaanheader"),
                db::raw("cast(cast(format((cast((format(penerimaanheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpenerimaanheader"),
                db::raw("cast((format(posting.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderposting"),
                db::raw("cast(cast(format((cast((format(posting.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderposting"),
                db::raw("cast((format(nonposting.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheadernonposting"),
                db::raw("cast(cast(format((cast((format(nonposting.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheadernonposting"),

            )
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pemutihansupirheader.supir_id', 'supir.id')
            ->leftJoin(DB::raw("penerimaanheader with (readuncommitted)"), 'pemutihansupirheader.penerimaan_nobukti', '=', 'penerimaanheader.nobukti')
            ->leftJoin(DB::raw("penerimaantruckingheader as posting with (readuncommitted)"), 'pemutihansupirheader.penerimaantruckingposting_nobukti', '=', 'posting.nobukti')
            ->leftJoin(DB::raw("penerimaantruckingheader as nonposting with (readuncommitted)"), 'pemutihansupirheader.penerimaantruckingnonposting_nobukti', '=', 'nonposting.nobukti')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'pemutihansupirheader.statuscetak', 'statuscetak.id')
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
                'pemutihansupirheader.pengeluaran_nobukti',
                'supir.namasupir as supir',
                'bank.namabank as bank'
            )
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pemutihansupirheader.supir_id', 'supir.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pemutihansupirheader.bank_id', 'bank.id')
            ->where('pemutihansupirheader.id', $id);

        return $query->first();
    }
    public function cekvalidasiaksi($id)
    {

        $error = new Error();
        $keteranganerror = $error->cekKeteranganError('SAPP') ?? '';
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $pelunasan = DB::table("pemutihansupirheader")->from(DB::raw("pemutihansupirheader"))->where('id', $id)->first();

        $pemutihansupir = DB::table('supir')
            ->from(
                DB::raw("supir as a with (readuncommitted)")
            )
            ->select(
                'a.namaalias',
            )
            ->where('a.pemutihansupir_nobukti', '=', $pelunasan->nobukti)
            ->first();

        if (isset($pemutihansupir)) {

            $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $pelunasan->nobukti . '</b><br>' . $keteranganerror . ' SUPIR <b>' . $pemutihansupir->namaalias . '</b><br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Approval Jurnal ' . $pelunasanPiutang->penerimaan_nobukti,
                'kodeerror' => 'SATL2'
            ];
            goto selesai;
        }
        
        $pemutihansupir = DB::table('supir')
            ->from(
                DB::raw("supir as a with (readuncommitted)")
            )
            ->select(
                'a.namaalias',
            )
            ->where('a.supirlama_id', '=', $pelunasan->supir_id)
            ->first();

        if (isset($pemutihansupir)) {

            $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $pelunasan->nobukti . '</b><br>' . $keteranganerror . ' SUPIR <b>' . $pemutihansupir->namaalias . '</b><br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Approval Jurnal ' . $pelunasanPiutang->penerimaan_nobukti,
                'kodeerror' => 'SATL2'
            ];
            goto selesai;
        }

        $pelunasan = DB::table("pemutihansupirheader")->from(DB::raw("pemutihansupirheader"))->where('id', $id)->first();

        $pemutihansupir = DB::table('pemutihansupirheader')
            ->from(
                DB::raw("pemutihansupirheader as a with (readuncommitted)")
            )
            ->select(
                'a.penerimaan_nobukti',
                'a.nobukti'
            )
            ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.penerimaan_nobukti', 'b.nobukti')
            ->where('a.penerimaan_nobukti', '=', $pelunasan->penerimaan_nobukti)
            ->first();

        if (isset($pemutihansupir)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $pemutihansupir->penerimaan_nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Approval Jurnal ' . $pelunasanPiutang->penerimaan_nobukti,
                'kodeerror' => 'SAPP'
            ];
            goto selesai;
        }

        $pemutihansupir = DB::table('pemutihansupirheader')
            ->from(
                DB::raw("pemutihansupirheader as a with (readuncommitted)")
            )
            ->select(
                'a.pengeluaran_nobukti',
                'a.nobukti'
            )
            ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.pengeluaran_nobukti', 'b.nobukti')
            ->where('a.pengeluaran_nobukti', '=', $pelunasan->pengeluaran_nobukti)
            ->first();

        if (isset($pemutihansupir)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $pemutihansupir->pengeluaran_nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Approval Jurnal ' . $pelunasanPiutang->pengeluaran_nobukti,
                'kodeerror' => 'SAPP'
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
            )->where('pengeluarantruckingdetail.supir_id', $supirId)
            ->whereRaw("pengeluarantruckingdetail.nobukti LIKE '%PJT%'");

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
            ->where('pengeluarantruckingheader.pengeluarantrucking_id', 1)
            ->where('pengeluarantruckingheader.pengeluaran_nobukti', '!=', '')
            ->where(function ($query) {
                $query->whereRaw("c.sisa != 0")
                    ->orWhereRaw("c.sisa is null");
            });

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
            )->where('pengeluarantruckingdetail.supir_id', $supirId)
            ->whereRaw("pengeluarantruckingdetail.nobukti LIKE '%PJT%'");

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
            ->where('pengeluarantruckingheader.pengeluarantrucking_id', 1)
            ->where('pengeluarantruckingheader.pengeluaran_nobukti', '')
            ->where(function ($query) {
                $query->whereRaw("c.sisa != 0")
                    ->orWhereRaw("c.sisa is null");
            });

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
                    pemutihansupirdetail.nominal AS sisa,
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
            ->where('pengeluarantruckingheader.pengeluarantrucking_id', 1)
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
            )->where('pengeluarantruckingdetail.supir_id', $supir_id)
            ->whereRaw("pengeluarantruckingdetail.nobukti LIKE '%PJT%'");

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
            pemutihansupirdetail.nominal AS sisa_posting,
            c.keterangan AS keterangan_posting
        "))
            ->join(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pemutihansupirdetail.pengeluarantrucking_nobukti', 'pengeluarantruckingheader.nobukti')
            ->join(DB::raw("$tempSisa as c with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'c.nobukti')
            ->where('pemutihansupirdetail.pemutihansupir_id', $id)
            ->where('pengeluarantruckingheader.pengeluarantrucking_id', 1)
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
            )->where('pengeluarantruckingdetail.supir_id', $supir_id)
            ->whereRaw("pengeluarantruckingdetail.nobukti LIKE '%PJT%'");

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
            pemutihansupirdetail.nominal AS sisa_nonposting,
            c.keterangan AS keterangan_nonposting
        "))
            ->join(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pemutihansupirdetail.pengeluarantrucking_nobukti', 'pengeluarantruckingheader.nobukti')
            ->join(DB::raw("$tempSisa as c with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'c.nobukti')
            ->where('pemutihansupirdetail.pemutihansupir_id', $id)
            ->where('pengeluarantruckingheader.pengeluarantrucking_id', 1)
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
                'bank.namabank as bank',
                $this->table.penerimaan_nobukti,
                $this->table.pengeluaran_nobukti,
                'akunpusat.keterangancoa as coa',
                'statuscetak.memo as statuscetak',
                $this->table.pengeluaransupir,
                $this->table.penerimaansupir,
                $this->table.penerimaantruckingposting_nobukti,
                $this->table.penerimaantruckingnonposting_nobukti,
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at
            ")
        )
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pemutihansupirheader.supir_id', 'supir.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'pemutihansupirheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pemutihansupirheader.coa', 'akunpusat.coa')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pemutihansupirheader.bank_id', 'bank.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('supir', 1000)->nullable();
            $table->string('bank', 1000)->nullable();
            $table->string('penerimaan_nobukti')->nullable();
            $table->string('pengeluaran_nobukti')->nullable();
            $table->string('coa')->nullable();
            $table->string('statuscetak')->nullable();
            $table->float('pengeluaransupir')->nullable();
            $table->float('penerimaansupir')->nullable();
            $table->string('penerimaantruckingposting_nobukti')->nullable();
            $table->string('penerimaantruckingnonposting_nobukti')->nullable();
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });
        if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
            request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
        }
        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        $models =  $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'supir', 'bank',  'penerimaan_nobukti', 'pengeluaran_nobukti', 'coa', 'statuscetak', 'pengeluaransupir', 'penerimaansupir', 'penerimaantruckingposting_nobukti', 'penerimaantruckingnonposting_nobukti', 'modifiedby', 'created_at', 'updated_at'], $models);

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
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statuscetak') {
                                $query = $query->where('statuscetak.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'supir') {
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
                                $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statuscetak') {
                                $query = $query->orWhere('statuscetak.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'supir') {
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
                                $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
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
                'pemutihansupirheader.jumlahcetak',
                'statuscetak.memo as statuscetak',
                "statuscetak.id as  statuscetak_id",
                DB::raw("'Laporan Pemutihan Supir' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'pemutihansupirheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pemutihansupirheader.supir_id', 'supir.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pemutihansupirheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pemutihansupirheader.coa', 'akunpusat.coa');

        $data = $query->first();
        return $data;
    }

    public function processStore(array $data): PemutihanSupir
    {

        $group = 'PEMUTIHAN SUPIR BUKTI';
        $subgroup = 'PEMUTIHAN SUPIR BUKTI';

        $format = DB::table('parameter')
            ->where('grp', $group)
            ->where('subgrp', $subgroup)
            ->first();

        $pemutihanSupir = new PemutihanSupir();

        $coaPengembalian = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))->where('kodepenerimaan', 'PJP')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $pemutihanSupir->nobukti = (new RunningNumberService)->get($group, $subgroup, $pemutihanSupir->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
        $pemutihanSupir->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $pemutihanSupir->supir_id = $data['supir_id'];
        $pemutihanSupir->pengeluaransupir = $data['pengeluaransupir'];
        $pemutihanSupir->penerimaansupir = $data['penerimaansupir'] ?? 0;
        $pemutihanSupir->bank_id = $data['bank_id'];
        $pemutihanSupir->coa = $coaPengembalian->coapostingkredit;
        $pemutihanSupir->statuscetak = $statusCetak->id ?? 0;
        $pemutihanSupir->statusformat = $format->id;
        $pemutihanSupir->modifiedby = auth('api')->user()->name;
        $pemutihanSupir->info = html_entity_decode(request()->info);

        $pemutihanSupir->penerimaan_nobukti = (new RunningNumberService)->get($group, $subgroup, $pemutihanSupir->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        // GET NO BUKTI PENERIMAAN
        // $querysubgrppenerimaan = Bank::from(DB::raw("bank with (readuncommitted)"))
        //     ->select(
        //         'parameter.grp',
        //         'parameter.subgrp',
        //         'bank.formatpenerimaan',
        //         'bank.coa',
        //         'bank.tipe'
        //     )
        //     ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')
        //     ->whereRaw("bank.id = $pemutihanSupir->bank_id")
        //     ->first();

        if (!$pemutihanSupir->save()) {
            throw new \Exception("Error storing pemutihan supir.");
        }

        $pemutihanSupirLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($pemutihanSupir->getTable()),
            'postingdari' => $data['postingdari'] ?? 'ENTRY PEMUTIHAN SUPIR',
            'idtrans' => $pemutihanSupir->id,
            'nobuktitrans' => $pemutihanSupir->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pemutihanSupir->toArray(),
            'modifiedby' => $pemutihanSupir->modifiedby
        ]);

        $noBukti = [];
        $coaDebet = [];
        $coaPostingKredit = [];
        $detaillog = [];
        $nominal = [];
        $keterangan = [];

        $posting = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING')->where('text', 'POSTING')->first();

        $formatPenerimaan = DB::table('parameter')
            ->where('grp', $group)
            ->where('subgrp', $subgroup)
            ->first();

        $fetchFormat =  DB::table('penerimaantrucking')->where('kodepenerimaan', 'PJP')->first();
        $getJurnal = DB::table("parameter")->where('grp', 'JURNAL PENGELUARAN PEMUTIHAN')->first()->text ?? '';
        $namaSupir = DB::table("supir")->where('id', $data['supir_id'])->first();
        if ($data['postingId']) {
            for ($i = 0; $i < count($data['postingId']); $i++) {
                $pemutihanSupirDetail = (new PemutihanSupirDetail())->processStore($pemutihanSupir, [
                    'pemutihansupir_id' => $pemutihanSupir->id,
                    'nobukti' => $pemutihanSupir->nobukti,
                    'pengeluarantrucking_nobukti' => $data['posting_nobukti'][$i],
                    'nominal' => $data['posting_nominal'][$i],
                    'statusposting' => $posting->id,
                    'modifiedby' => auth('api')->user()->name
                ]);

                $detaillog[] = $pemutihanSupirDetail;

                $noBukti = $pemutihanSupir->nobukti;
                $nominal[] = $data['posting_nominal'][$i];
                $supir[] = $data['supir_id'];
                $tgljatuhtempo[] = $data['tglbukti'];
                $coapengeluaran[] = $getJurnal;
                $nowarkat[] = '';
                $keterangan_detail[] = 'PENGEMBALIAN PINJAMAN DARI PEMUTIHAN SUPIR ' . $namaSupir->namasupir . ' ' . $data['posting_nobukti'][$i];
            }

            $dataPinjaman = [
                'penerimaantrucking_id' => $fetchFormat->id,
                'bank_id' => $data['bank_id'],
                'supirheader_id' => $data['supir_id'],
                'supirheader' =>  $namaSupir->namasupir,
                'tglbukti' => $data['tglbukti'],
                'from' => 'pemutihan',
                // 'supirheader_id' => 0,
                'karyawanheader_id' => 0,
                'postingdari' => "ENTRY PEMUTIHAN SUPIR",
                'jenisorder_id' => '',
                'supir_id' => $supir,
                'nominal' => $data['posting_nominal'],
                'pengeluarantruckingheader_nobukti' => $data['posting_nobukti'],
                'keterangan' => $data['posting_keterangan'],
            ];

            $penerimaanHeader = (new PenerimaanTruckingHeader())->processStore($dataPinjaman);

            $pemutihanSupir->penerimaan_nobukti = $penerimaanHeader->penerimaan_nobukti;
            $pemutihanSupir->penerimaantruckingposting_nobukti = $penerimaanHeader->nobukti;
            // jurnal kb b.kantor lain
            /*STORE PENGELUARAN*/
            $alatbayar = DB::table("alatbayar")->select('alatbayar.id', 'alatbayar.kodealatbayar')->join('bank', 'alatbayar.tipe', 'bank.tipe')->where('bank.id', $data['bank_id'])->first();

            $pengeluaranRequest = [
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'pelanggan_id' => 0,
                'postingdari' => "ENTRY PEMUTIHAN SUPIR",
                'alatbayar_id' => $alatbayar->id,
                'bank_id' => $data['bank_id'],
                'transferkeac' => "",
                'transferkean' => "",
                'transferkebank' => "",
                'userapproval' => "",
                'tglapproval' => "",

                'nowarkat' => $nowarkat,
                'tgljatuhtempo' => $tgljatuhtempo,
                "nominal_detail" => $data['posting_nominal'],
                'coadebet' => $coapengeluaran,
                "keterangan_detail" => $keterangan_detail,
                'bulanbeban' => $tgljatuhtempo,
            ];

            $pengeluaranHeader = (new PengeluaranHeader())->processStore($pengeluaranRequest);

            $pemutihanSupir->pengeluaran_nobukti = $pengeluaranHeader->nobukti;
        }
        if ($data['nonpostingId']) {
            $nonPosting = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING')->where('id', '84')->first();
            for ($i = 0; $i < count($data['nonpostingId']); $i++) {
                $pemutihanSupirDetail = (new PemutihanSupirDetail())->processStore($pemutihanSupir, [
                    'pemutihansupir_id' => $pemutihanSupir->id,
                    'nobukti' => $pemutihanSupir->nobukti,
                    'pengeluarantrucking_nobukti' => $data['nonposting_nobukti'][$i],
                    'nominal' => $data['nonposting_nominal'][$i],
                    'statusposting' => $nonPosting->id,
                    'modifiedby' => auth('api')->user()->name
                ]);
                $supir[] = $data['supir_id'];
            }

            $dataPinjaman = [
                'tanpaprosesnobukti' => 2,
                'penerimaantrucking_id' => $fetchFormat->id,
                'bank_id' => '',
                'supirheader_id' => $data['supir_id'],
                'supirheader' =>  $namaSupir->namasupir,
                'tglbukti' => $data['tglbukti'],
                'from' => 'pemutihan',
                // 'supirheader_id' => 0,
                'karyawanheader_id' => 0,
                'jenisorder_id' => '',
                'supir_id' => $supir,
                'nominal' => $data['nonposting_nominal'],
                'keterangan' => $data['nonposting_keterangan'],
                'pengeluarantruckingheader_nobukti' => $data['nonposting_nobukti'],
            ];

            $penerimaanHeader = (new PenerimaanTruckingHeader())->processStore($dataPinjaman);
            $pemutihanSupir->penerimaantruckingnonposting_nobukti = $penerimaanHeader->nobukti;
        }

        $pemutihanSupir->save();

        $pemutihanSupirDetailLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($pemutihanSupirDetail->getTable()),
            'postingdari' => 'ENTRY PEMUTIHAN SUPIR DETAIL',
            'idtrans' =>  $pemutihanSupirLogTrail->id,
            'nobuktitrans' => $pemutihanSupir->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $detaillog,
            'modifiedby' => $pemutihanSupir->modifiedby,
        ]);

        $getSupirLama = DB::table('supir')->from(DB::raw("supir with (readuncommitted)"))->where('supirlama_id', $data['supir_id'])->first();
        if (isset($getSupirLama)) {
            $supir_id[] = $getSupirLama->id;
            $getPosting = DB::table("pemutihansupirdetail")->from(DB::raw("pemutihansupirdetail with (readuncommitted)"))
                ->select(DB::raw("sum(nominal) as nominal"))
                ->where('nobukti', $pemutihanSupir->nobukti)
                ->where('statusposting', 83)
                ->first();
            if ($getPosting->nominal != '') {

                $nominalposting[] = $getPosting->nominal;
                $keterangan[] = 'PINJAMAN DARI PEMUTIHAN ' . $pemutihanSupir->nobukti . ' (POSTING)';

                $pengeluaranRequest = [
                    'tglbukti' => date('Y-m-d'),
                    'pengeluarantrucking_id' => 1,
                    'statusposting' => 84,
                    'supir_id' => $supir_id,
                    'pemutihansupir_nobukti' => $pemutihanSupir->nobukti,
                    'nominal' => $nominalposting,
                    'keterangan' => $keterangan
                ];
                (new PengeluaranTruckingHeader())->processStore($pengeluaranRequest);
            }
            $getNonPosting = DB::table("pemutihansupirdetail")->from(DB::raw("pemutihansupirdetail with (readuncommitted)"))
                ->select(DB::raw("sum(nominal) as nominal"))
                ->where('nobukti', $pemutihanSupir->nobukti)
                ->where('statusposting', 84)
                ->first();

            if ($getNonPosting->nominal != '') {

                $nominalnonposting[] = $getNonPosting->nominal;
                $keteranganNon[] = 'PINJAMAN DARI PEMUTIHAN ' . $pemutihanSupir->nobukti . ' (NON POSTING)';

                $pengeluaranRequestNon = [
                    'tglbukti' => date('Y-m-d'),
                    'pengeluarantrucking_id' => 1,
                    'statusposting' => 84,
                    'supir_id' => $supir_id,
                    'pemutihansupir_nobukti' => $pemutihanSupir->nobukti,
                    'nominal' => $nominalnonposting,
                    'keterangan' => $keteranganNon
                ];
                (new PengeluaranTruckingHeader())->processStore($pengeluaranRequestNon);
            }
        }
        return $pemutihanSupir;
    }

    public function processUpdate(PemutihanSupir $pemutihanSupir, array $data): PemutihanSupir
    {
        $getTgl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'EDIT TANGGAL BUKTI')->where('subgrp', 'PEMUTIHAN SUPIR')->first();
        if (trim($getTgl->text) == 'YA') {
            $group = 'PEMUTIHAN SUPIR BUKTI';
            $subgroup = 'PEMUTIHAN SUPIR BUKTI';

            $coaPengembalian = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))->where('kodepenerimaan', 'PJP')->first();
            $querycek = DB::table('pemutihansupirheader')->from(
                DB::raw("pemutihansupirheader a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.id', $pemutihanSupir->id)
                ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
                ->first();

            if (isset($querycek)) {
                $nobukti = $querycek->nobukti;
            } else {
                $nobukti = (new RunningNumberService)->get($group, $subgroup, $pemutihanSupir->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
            }

            $pemutihanSupir->nobukti = $nobukti;
            $pemutihanSupir->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        }

        $pemutihanSupir->pengeluaransupir =  $data['pengeluaransupir'];
        $pemutihanSupir->penerimaansupir = $data['penerimaansupir'] ?? 0;
        $pemutihanSupir->coa = $data['coa'];
        $pemutihanSupir->modifiedby = auth('api')->user()->name;
        $pemutihanSupir->info = html_entity_decode(request()->info);

        // GET NO BUKTI PENERIMAAN
        // $querysubgrppenerimaan = Bank::from(DB::raw("bank with (readuncommitted)"))
        //     ->select(
        //         'parameter.grp',
        //         'parameter.subgrp',
        //         'bank.formatpenerimaan',
        //         'bank.coa',
        //         'bank.tipe'
        //     )
        //     ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')
        //     ->whereRaw("bank.id =" . $data['bank_id'])
        //     ->first();


        if (!$pemutihanSupir->save()) {
            throw new \Exception("Error update pemutihan supir.");
        }

        $pemutihanSupirdetail = PemutihanSupirDetail::where('pemutihansupir_id', $pemutihanSupir->id)->delete();

        $coadebet = Bank::from(DB::raw("bank with (readuncommitted)"))->where('id', $pemutihanSupir->bank_id)->first();
        $detaillog = [];
        $penerimaanDetail = [];
        $posting = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING')->where('text', 'POSTING')->first();

        $noBukti = [];
        $coaDebet = [];
        $coaPostingKredit = [];
        $detaillog = [];
        $nominal = [];
        $keterangan = [];
        $fetchFormat =  DB::table('penerimaantrucking')->where('kodepenerimaan', 'PJP')->first();
        $getJurnal = DB::table("parameter")->where('grp', 'JURNAL PENGELUARAN PEMUTIHAN')->first()->text ?? '';
        $namaSupir = DB::table("supir")->where('id', $data['supir_id'])->first();

        if ($data['postingId']) {

            for ($i = 0; $i < count($data['postingId']); $i++) {
                $pemutihanSupirDetail = (new PemutihanSupirDetail())->processStore($pemutihanSupir, [
                    'pemutihansupir_id' => $pemutihanSupir->id,
                    'nobukti' => $pemutihanSupir->nobukti,
                    'pengeluarantrucking_nobukti' => $data['posting_nobukti'][$i],
                    'nominal' => $data['posting_nominal'][$i],
                    'statusposting' => $posting->id,
                    'modifiedby' => auth('api')->user()->name
                ]);

                $detaillog[] = $pemutihanSupirDetail;

                $noBukti = $pemutihanSupir->nobukti;
                $nominal[] = $data['posting_nominal'][$i];
                $supir[] = $data['supir_id'];
                $tgljatuhtempo[] = $data['tglbukti'];
                $coapengeluaran[] = $getJurnal;
                $nowarkat[] = '';
                $keterangan_detail[] = 'PENGEMBALIAN PINJAMAN DARI PEMUTIHAN SUPIR ' . $namaSupir->namasupir . ' ' . $data['posting_nobukti'][$i];
            }
            $penerimaanRequest = [
                'penerimaantrucking_id' => $fetchFormat->id,
                'bank_id' => $data['bank_id'],
                'supirheader_id' => $data['supir_id'],
                'supirheader' =>  $namaSupir->namasupir,
                'tglbukti' => $data['tglbukti'],
                'from' => 'pemutihan',
                // 'supirheader_id' => 0,
                'postingdari' => "EDIT PEMUTIHAN SUPIR",
                'karyawanheader_id' => 0,
                'jenisorder_id' => '',
                'supir_id' => $supir,
                'nominal' => $data['posting_nominal'],
                'pengeluarantruckingheader_nobukti' => $data['posting_nobukti'],
                'keterangan' => $data['posting_keterangan']
            ];
            $get = DB::table("penerimaantruckingheader")->from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                ->where('nobukti', $pemutihanSupir->penerimaantruckingposting_nobukti)->first();
            if ($get != null) {
                $newPenerimaan = new PenerimaanTruckingHeader();
                $newPenerimaan = $newPenerimaan->findAll($get->id);
                $penerimaanHeader = (new PenerimaanTruckingHeader())->processUpdate($newPenerimaan, $penerimaanRequest);
            } else {
                $penerimaanHeader = (new PenerimaanTruckingHeader())->processStore($penerimaanRequest);
            }

            $pemutihanSupir->penerimaan_nobukti = $penerimaanHeader->penerimaan_nobukti;
            $pemutihanSupir->penerimaantruckingposting_nobukti = $penerimaanHeader->nobukti;

            /*STORE PENGELUARAN*/
            $alatbayar = DB::table("alatbayar")->select('alatbayar.id', 'alatbayar.kodealatbayar')->join('bank', 'alatbayar.tipe', 'bank.tipe')->where('bank.id', $data['bank_id'])->first();
            $pengeluaranRequest = [
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'pelanggan_id' => 0,
                'postingdari' => "EDIT PEMUTIHAN SUPIR",
                'alatbayar_id' => $alatbayar->id,
                'bank_id' => $data['bank_id'],
                'transferkeac' => "",
                'transferkean' => "",
                'transferkebank' => "",
                'userapproval' => "",
                'tglapproval' => "",

                'nowarkat' => $nowarkat,
                'tgljatuhtempo' => $tgljatuhtempo,
                "nominal_detail" => $data['posting_nominal'],
                'coadebet' => $coapengeluaran,
                "keterangan_detail" => $keterangan_detail,
                'bulanbeban' => $tgljatuhtempo,
            ];

            $pengeluaranHeader = PengeluaranHeader::where('nobukti', $pemutihanSupir->pengeluaran_nobukti)->first();
            if (isset($pengeluaranHeader)) {
                $pengeluaranHeader = (new PengeluaranHeader())->processUpdate($pengeluaranHeader, $pengeluaranRequest);
                $pemutihanSupir->pengeluaran_nobukti = $pengeluaranHeader->nobukti;
            }
        }

        if ($data['nonpostingId']) {

            $nonPosting = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING')->where('id', '84')->first();
            for ($i = 0; $i < count($data['nonpostingId']); $i++) {
                $pemutihanSupirDetail = (new PemutihanSupirDetail())->processStore($pemutihanSupir, [
                    'pemutihansupir_id' => $pemutihanSupir->id,
                    'nobukti' => $pemutihanSupir->nobukti,
                    'pengeluarantrucking_nobukti' => $data['nonposting_nobukti'][$i],
                    'nominal' => $data['nonposting_nominal'][$i],
                    'statusposting' => $nonPosting->id,
                    'modifiedby' => auth('api')->user()->name
                ]);
                $supir[] = $data['supir_id'];
            }

            $penerimaanRequest = [
                'tanpaprosesnobukti' => 2,
                'penerimaantrucking_id' => $fetchFormat->id,
                'bank_id' => '',
                'supirheader_id' => $data['supir_id'],
                'tglbukti' => $data['tglbukti'],
                // 'supirheader_id' => 0,
                'karyawanheader_id' => 0,
                'jenisorder_id' => '',
                'supir_id' => $supir,
                'nominal' => $data['nonposting_nominal'],
                'pengeluarantruckingheader_nobukti' => $data['nonposting_nobukti'],
                'keterangan' => $data['nonposting_keterangan']
            ];
            $get = DB::table("penerimaantruckingheader")->from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                ->where('nobukti', $pemutihanSupir->penerimaantruckingnonposting_nobukti)->first();
            if ($get != null) {
                $newPenerimaan = new PenerimaanTruckingHeader();
                $newPenerimaan = $newPenerimaan->findAll($get->id);
                $penerimaanHeader = (new PenerimaanTruckingHeader())->processUpdate($newPenerimaan, $penerimaanRequest);
            } else {
                $penerimaanHeader = (new PenerimaanTruckingHeader())->processStore($penerimaanRequest);
            }
            $pemutihanSupir->penerimaantruckingnonposting_nobukti = $penerimaanHeader->nobukti;
        }


        $pemutihanSupir->save();

        $pemutihanSupirLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($pemutihanSupir->getTable()),
            'postingdari' => 'EDIT PEMUTIHAN SUPIR',
            'idtrans' => $pemutihanSupir->id,
            'nobuktitrans' => $pemutihanSupir->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $pemutihanSupir->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        $pemutihanSupirDetailLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($pemutihanSupirDetail->getTable()),
            'postingdari' => 'EDIT PEMUTIHAN SUPIR DETAIL',
            'idtrans' =>  $pemutihanSupirLogTrail->id,
            'nobuktitrans' => $pemutihanSupir->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $detaillog,
            'modifiedby' => $pemutihanSupir->modifiedby,
        ]);

        return $pemutihanSupir;
    }

    public function processDestroy($id, $postingdari = ""): PemutihanSupir
    {
        $getDetail = PemutihanSupirDetail::lockForUpdate()->where('pemutihansupir_id', $id)->get();

        $pemutihanSupir = new PemutihanSupir();
        $pemutihanSupir = $pemutihanSupir->lockAndDestroy($id);

        $pemutihanSupirLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($pemutihanSupir->getTable()),
            'postingdari' => $postingdari,
            'idtrans' =>  $pemutihanSupir->id,
            'nobuktitrans' => $pemutihanSupir->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $pemutihanSupir->toArray(),
            'modifiedby' => auth('api')->user()->name,
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'PEMUTIHANSUPIRDETAIL',
            'postingdari' => $postingdari,
            'idtrans' => $pemutihanSupirLogTrail['id'],
            'nobuktitrans' => $pemutihanSupir->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $getDetail->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        $get = DB::table("penerimaantruckingheader")->from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
            ->where('nobukti', $pemutihanSupir->penerimaantruckingnonposting_nobukti)->first();
        if (isset($get)) {
            $getPenerimaan = DB::table('penerimaantruckingheader')->from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where('nobukti', $pemutihanSupir->penerimaantruckingnonposting_nobukti)->first();

            (new PenerimaanTruckingHeader())->processDestroy($getPenerimaan->id, $postingdari);
        }

        $get = DB::table("penerimaantruckingheader")->from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
            ->where('nobukti', $pemutihanSupir->penerimaantruckingposting_nobukti)->first();
        if (isset($get)) {
            $getPenerimaan = DB::table('penerimaantruckingheader')->from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where('nobukti', $pemutihanSupir->penerimaantruckingposting_nobukti)->first();

            (new PenerimaanTruckingHeader())->processDestroy($getPenerimaan->id, $postingdari);
        }
        $get = DB::table("pengeluaranheader")->from(DB::raw("pengeluaranheader with (readuncommitted)"))
            ->where('nobukti', $pemutihanSupir->pengeluaran_nobukti)->first();
        if (isset($get)) {
            (new PengeluaranHeader())->processDestroy($get->id, $postingdari);
        }

        return $pemutihanSupir;
    }
}
