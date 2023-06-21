<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\RunningNumberService;


class PengeluaranTruckingHeader extends MyModel
{
    use HasFactory;
    protected $table = 'pengeluarantruckingheader';

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

        $PengeluaranTruckingHeader = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader"))->where('nobukti', $nobukti)->first();
        $nobukti = $PengeluaranTruckingHeader->nobukti;

        $prosesUangJalan = DB::table('prosesuangjalansupirdetail')
            ->from(
                DB::raw("prosesuangjalansupirdetail as a with (readuncommitted)")
            )
            ->select(
                'a.pengeluarantrucking_nobukti'
            )
            ->where('a.pengeluarantrucking_nobukti', '=', $nobukti)
            ->first();
        if (isset($prosesUangJalan)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Proses Uang Jalan Supir',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }


        $pengeluaran = DB::table('pengeluarantruckingheader')
            ->from(
                DB::raw("pengeluarantruckingheader as a with (readuncommitted)")
            )
            ->select(
                'a.pengeluarantrucking_nobukti'
            )
            ->where('a.pengeluarantrucking_nobukti', '=', $PengeluaranTruckingHeader->nobukti)
            ->first();
        if (isset($pengeluaran)) {
            
            $data = [
                'kondisi' => true,
                'keterangan' => 'pengeluaran Trucking',
                'kodeerror' => 'SATL'
            ];
            goto selesai;
        }
        $penerimaanTrucking = DB::table('penerimaantruckingdetail')
            ->from(
                DB::raw("penerimaantruckingdetail as a with (readuncommitted)")
            )
            ->select(
                'a.pengeluarantruckingheader_nobukti'
            )
            ->where('a.pengeluarantruckingheader_nobukti', '=', $nobukti)
            ->first();
        if (isset($penerimaanTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan Trucking',
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

    public function cekvalidasiklaim($id)
    {
        $pengeluaranTruckingHeader = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader"))->where('id', $id)->first();
        
        $nobuktiPjt = $pengeluaranTruckingHeader->pengeluarantrucking_nobukti;
        $penerimaanTrucking = DB::table('penerimaantruckingdetail')
        ->from(
            DB::raw("penerimaantruckingdetail as a with (readuncommitted)")
        )
        ->select(
            'a.pengeluarantruckingheader_nobukti'
        )
        ->where('a.pengeluarantruckingheader_nobukti', '=', $nobuktiPjt)
        ->first();
        if (isset($penerimaanTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan Trucking',
                'kodeerror' => 'MAX'
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
        $query = DB::table($this->table)->from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
            ->select(
                'pengeluarantruckingheader.id',
                'pengeluarantruckingheader.nobukti',
                'pengeluarantruckingheader.tglbukti',
                'pengeluarantruckingheader.modifiedby',
                'pengeluarantruckingheader.created_at',
                'pengeluarantruckingheader.updated_at',
                'pengeluarantruckingheader.pengeluaran_nobukti',
                'pengeluarantrucking.keterangan as pengeluarantrucking_id',
                'bank.namabank as bank_id',
                'pengeluarantruckingheader.trado_id',
                'trado.keterangan as trado',
                'pengeluarantruckingheader.trado_id as tradoheader_id',
                'supir.namasupir as supirheader',
                'supir.namasupir as supir',
                'pengeluarantruckingheader.pengeluarantrucking_nobukti',
                DB::raw('(case when (year(pengeluarantruckingheader.tglbukacetak) <= 2000) then null else pengeluarantruckingheader.tglbukacetak end ) as tglbukacetak'),
                'statuscetak.memo as statuscetak',
                'pengeluarantruckingheader.userbukacetak',
                'akunpusat.keterangancoa as coa',
                'statusposting.memo as statusposting'
            )
            // ->whereBetween('pengeluarantruckingheader.tglbukti', [date('Y-m-d',strtotime(request()->tgldari)), date('Y-m-d',strtotime(request()->tglsampai))])
            ->leftJoin(DB::raw("pengeluarantrucking with (readuncommitted)"), 'pengeluarantruckingheader.pengeluarantrucking_id', 'pengeluarantrucking.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluarantruckingheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pengeluarantruckingheader.coa', 'akunpusat.coa')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'pengeluarantruckingheader.trado_id', 'trado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingheader.supir_id', 'supir.id')

            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'pengeluarantruckingheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusposting with (readuncommitted)"), 'pengeluarantruckingheader.statusposting', 'statusposting.id');


        if (request()->tgldari) {
            $query->whereBetween('pengeluarantruckingheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if (request()->pengeluaranheader_id) {
            $query->where('pengeluarantruckingheader.pengeluarantrucking_id', request()->pengeluaranheader_id);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(pengeluarantruckingheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(pengeluarantruckingheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("pengeluarantruckingheader.statuscetak", $statusCetak);
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
        $query = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
            ->select(
                'pengeluarantruckingheader.id',
                'pengeluarantruckingheader.nobukti',
                'pengeluarantruckingheader.tglbukti',
                'pengeluarantruckingheader.pengeluarantrucking_id',
                'pengeluarantrucking.keterangan as pengeluarantrucking',
                'pengeluarantrucking.kodepengeluaran as kodepengeluaran',
                'pengeluarantruckingheader.bank_id',
                'bank.namabank as bank',
                'pengeluarantruckingheader.supir_id',
                'pengeluarantruckingheader.supir_id as supirheader_id',
                'pengeluarantruckingheader.trado_id',
                'trado.keterangan as trado',
                'pengeluarantruckingheader.trado_id as tradoheader_id',
                'supir.namasupir as supirheader',
                'supir.namasupir as supir',
                'pengeluarantruckingheader.pengeluarantrucking_nobukti',
                'pengeluarantruckingheader.statusposting',
                'pengeluarantruckingheader.coa',
                'pengeluarantruckingheader.periodedari',
                'pengeluarantruckingheader.periodesampai',
                'akunpusat.keterangancoa',
                'pengeluarantruckingheader.pengeluaran_nobukti'
            )
            ->leftJoin(DB::raw("pengeluarantrucking with (readuncommitted)"), 'pengeluarantruckingheader.pengeluarantrucking_id', 'pengeluarantrucking.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluarantruckingheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingheader.supir_id', 'supir.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'pengeluarantruckingheader.trado_id', 'trado.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pengeluarantruckingheader.coa', 'akunpusat.coa')
            ->where('pengeluarantruckingheader.id', '=', $id);


        $data = $query->first();

        return $data;
    }

    public function getTarikDeposito($id, $supir_id)
    {
        $tempPribadi = $this->createTempTarikDeposito($id, $supir_id);
        $tempAll = $this->createTempDeposito($id, $supir_id);

        $temp = '##tempGet' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $deposito = DB::table($tempPribadi)->from(DB::raw("$tempPribadi with (readuncommitted)"))
            ->select(DB::raw("pengeluarantruckingheader_id,nobukti,keterangan,sisa,bayar"));

        Schema::create($temp, function ($table) {
            $table->bigInteger('pengeluarantruckingheader_id')->nullable();
            $table->string('nobukti');
            $table->string('keterangan')->nullable();
            $table->bigInteger('sisa')->nullable();
            $table->bigInteger('bayar')->nullable();
        });
        DB::table($temp)->insertUsing(['pengeluarantruckingheader_id', 'nobukti', 'keterangan', 'sisa', 'bayar'], $deposito);

        $pinjaman = DB::table($tempAll)->from(DB::raw("$tempAll with (readuncommitted)"))
            ->select(DB::raw("null as pengeluarantruckingheader_id,nobukti,keterangan,sisa, 0 as bayar"));
        DB::table($temp)->insertUsing(['pengeluarantruckingheader_id', 'nobukti', 'keterangan', 'sisa', 'bayar'], $pinjaman);

        $data = DB::table($temp)->from(DB::raw("$temp with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $temp.nobukti) as id,pengeluarantruckingheader_id,nobukti,keterangan,sisa,bayar as nominal"))
            ->get();

        return $data;
    }

    public function createTempDeposito($id, $supir_id)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetch = DB::table('penerimaantruckingdetail')
            ->from(
                DB::raw("penerimaantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("penerimaantruckingdetail.nobukti,penerimaantruckingdetail.keterangan,
        (SELECT (penerimaantruckingdetail.nominal - coalesce(SUM(pengeluarantruckingdetail.nominal),0)) FROM pengeluarantruckingdetail WHERE pengeluarantruckingdetail.penerimaantruckingheader_nobukti= penerimaantruckingdetail.nobukti) AS sisa"))
            ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingheader.nobukti', 'penerimaantruckingdetail.nobukti')
            ->whereRaw("penerimaantruckingdetail.supir_id = $supir_id")
            ->where("penerimaantruckingdetail.nobukti",  'LIKE', "%DPO%")
            ->whereRaw("penerimaantruckingheader.nobukti not in (select penerimaantruckingheader_nobukti from pengeluarantruckingdetail where pengeluarantruckingheader_id=$id)")
            ->orderBy('penerimaantruckingheader.tglbukti', 'asc')
            ->orderBy('penerimaantruckingdetail.nobukti', 'asc');

        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->string('keterangan');
            $table->bigInteger('sisa')->nullable();
        });
        // return $fetch->get();
        $tes = DB::table($temp)->insertUsing(['nobukti', 'keterangan', 'sisa'], $fetch);
        return $temp;
    }

    public function createTempTarikDeposito($id, $supir_id)
    {

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = DB::table('penerimaantruckingdetail')
            ->from(
                DB::raw("penerimaantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("pengeluarantruckingdetail.pengeluarantruckingheader_id,penerimaantruckingdetail.nobukti,penerimaantruckingdetail.keterangan,pengeluarantruckingdetail.nominal as bayar ,(SELECT (penerimaantruckingdetail.nominal - coalesce(SUM(pengeluarantruckingdetail.nominal),0)) FROM pengeluarantruckingdetail WHERE pengeluarantruckingdetail.penerimaantruckingheader_nobukti= penerimaantruckingdetail.nobukti) AS sisa"))
            ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingheader.nobukti', 'penerimaantruckingdetail.nobukti')
            ->leftJoin(DB::raw("pengeluarantruckingdetail with (readuncommitted)"), 'pengeluarantruckingdetail.penerimaantruckingheader_nobukti', 'penerimaantruckingdetail.nobukti')
            ->whereRaw("penerimaantruckingdetail.supir_id = $supir_id")
            ->where("penerimaantruckingdetail.nobukti",  'LIKE', "%DPO%")
            ->where("pengeluarantruckingdetail.pengeluarantruckingheader_id", $id)
            ->orderBy('penerimaantruckingheader.tglbukti', 'asc')
            ->orderBy('penerimaantruckingdetail.nobukti', 'asc');

        Schema::create($temp, function ($table) {
            $table->bigInteger('pengeluarantruckingheader_id')->nullable();
            $table->string('nobukti');
            $table->string('keterangan');
            $table->bigInteger('bayar')->nullable();
            $table->bigInteger('sisa')->nullable();
        });
        $tes = DB::table($temp)->insertUsing(['pengeluarantruckingheader_id', 'nobukti', 'keterangan', 'bayar', 'sisa'], $fetch);
        return $temp;
    }

    public function getDeleteTarikDeposito($id, $supir_id)
    {
        $tempPribadi = $this->createTempTarikDeposito($id, $supir_id);

        $data = DB::table($tempPribadi)->from(DB::raw("$tempPribadi with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $tempPribadi.nobukti) as id,pengeluarantruckingheader_id,nobukti,keterangan,sisa,bayar as nominal"))
            ->get();

        return $data;
    }

    public function getEditPelunasan($id, $periodedari, $periodesampai)
    {
        $tempPribadi = $this->createTempEditPelunasan($id, $periodedari, $periodesampai);
        $tempAll = $this->createTempPelunasan($id, $periodedari, $periodesampai);

        $temp = '##tempGet' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $pelunasan = DB::table($tempPribadi)->from(DB::raw("$tempPribadi with (readuncommitted)"))
            ->select(DB::raw("pengeluarantruckingheader_id,nobukti,keterangan,sisa,bayar"));


        Schema::create($temp, function ($table) {
            $table->bigInteger('pengeluarantruckingheader_id')->nullable();
            $table->string('nobukti');
            $table->string('keterangan')->nullable();
            $table->bigInteger('sisa')->nullable();
            $table->bigInteger('bayar')->nullable();
        });

        DB::table($temp)->insertUsing(['pengeluarantruckingheader_id', 'nobukti', 'keterangan', 'sisa', 'bayar'], $pelunasan);


        $pinjaman = DB::table($tempAll)->from(DB::raw("$tempAll with (readuncommitted)"))
            ->select(DB::raw("null as pengeluarantruckingheader_id,nobukti,keterangan,sisa, 0 as bayar"));
        DB::table($temp)->insertUsing(['pengeluarantruckingheader_id', 'nobukti', 'keterangan', 'sisa', 'bayar'], $pinjaman);


        $data = DB::table($temp)->from(DB::raw("$temp with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $temp.nobukti) as id,pengeluarantruckingheader_id,nobukti,keterangan,sisa,bayar as nominal"))
            ->get();

        // echo json_encode($data);
        // die;

        return $data;
    }

    public function createTempPelunasan($id, $periodedari, $periodesampai)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetch = DB::table('penerimaantruckingdetail')
            ->from(
                DB::raw("penerimaantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("penerimaantruckingdetail.nobukti,penerimaantruckingdetail.keterangan,
        (SELECT (penerimaantruckingdetail.nominal - coalesce(SUM(pengeluarantruckingdetail.nominal),0)) FROM pengeluarantruckingdetail WHERE pengeluarantruckingdetail.penerimaantruckingheader_nobukti= penerimaantruckingdetail.nobukti) AS sisa"))
            ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingheader.nobukti', 'penerimaantruckingdetail.nobukti')
            ->whereBetween('penerimaantruckingheader.tglbukti', [date('Y-m-d', strtotime($periodedari)), date('Y-m-d', strtotime($periodesampai))])
            ->whereRaw("penerimaantruckingheader.nobukti not in (select penerimaantruckingheader_nobukti from pengeluarantruckingdetail where pengeluarantruckingheader_id=$id)")
            ->orderBy('penerimaantruckingheader.tglbukti', 'asc')
            ->orderBy('penerimaantruckingdetail.nobukti', 'asc');

        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->string('keterangan');
            $table->bigInteger('sisa')->nullable();
        });
        // return $fetch->get();
        $tes = DB::table($temp)->insertUsing(['nobukti', 'keterangan', 'sisa'], $fetch);
        return $temp;
    }

    public function createTempEditPelunasan($id, $periodedari, $periodesampai)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = DB::table('penerimaantruckingdetail')
            ->from(
                DB::raw("penerimaantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("pengeluarantruckingdetail.pengeluarantruckingheader_id,penerimaantruckingdetail.nobukti,penerimaantruckingdetail.keterangan,pengeluarantruckingdetail.nominal as bayar ,(SELECT (penerimaantruckingdetail.nominal - coalesce(SUM(pengeluarantruckingdetail.nominal),0)) FROM pengeluarantruckingdetail WHERE pengeluarantruckingdetail.penerimaantruckingheader_nobukti= penerimaantruckingdetail.nobukti) AS sisa"))
            ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingheader.nobukti', 'penerimaantruckingdetail.nobukti')
            ->leftJoin(DB::raw("pengeluarantruckingdetail with (readuncommitted)"), 'pengeluarantruckingdetail.penerimaantruckingheader_nobukti', 'penerimaantruckingdetail.nobukti')
            ->whereBetween('penerimaantruckingheader.tglbukti', [date('Y-m-d', strtotime($periodedari)), date('Y-m-d', strtotime($periodesampai))])
            ->where("pengeluarantruckingdetail.pengeluarantruckingheader_id", $id)
            ->orderBy('penerimaantruckingheader.tglbukti', 'asc')
            ->orderBy('penerimaantruckingdetail.nobukti', 'asc');

        Schema::create($temp, function ($table) {
            $table->bigInteger('pengeluarantruckingheader_id')->nullable();
            $table->string('nobukti');
            $table->string('keterangan');
            $table->bigInteger('bayar')->nullable();
            $table->bigInteger('sisa')->nullable();
        });
        $tes = DB::table($temp)->insertUsing(['pengeluarantruckingheader_id', 'nobukti', 'keterangan', 'bayar', 'sisa'], $fetch);
        return $temp;

        echo json_encode($temp);
        die;
    }

    public function getDeleteEditPelunasan($id, $periodedari, $periodesampai)
    {
        $tempPribadi = $this->createTempEditPelunasan($id, $periodedari, $periodesampai);

        $data = DB::table($tempPribadi)->from(DB::raw("$tempPribadi with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $tempPribadi.nobukti) as id,pengeluarantruckingheader_id,nobukti,keterangan,sisa,bayar as nominal"))
            ->get();

        return $data;
    }

    // public function getTarikDeposito($id){
    //     $penerimaantrucking = DB::table($this->table)->from(DB::raw("penerimaantrucking with (readuncommitted)"))->where('kodepenerimaan','DPO')->first();
    //     // return $pengeluarantruckingheader->id;
    //     $query = DB::table($this->table)->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
    //     ->select(
    //         DB::raw("row_number() Over(Order By pengeluarantruckingdetail.id) as id"),
    //         // 'pengeluarantruckingheader.id',
    //         'pengeluarantruckingdetail.penerimaantruckingheader_nobukti as nobukti',
    //         // 'pengeluarantruckingdetail.tglbukti',
    //         'pengeluarantruckingdetail.keterangan',
    //         'pengeluarantruckingdetail.nominal'
    //     )
    //     ->where('pengeluarantruckingdetail.pengeluarantruckingheader_id',$id);


    //     return $query->get();
    // }

    // public function getPinjaman($supir_id)
    // {
    //     $penerimaantrucking = DB::table($this->table)->from(DB::raw("pengeluarantrucking with (readuncommitted)"))->where('kodepengeluaran','PJT')->first();
    //     // return response($penerimaantrucking->id,422);
    //     $query = DB::table($this->table)->from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
    //     ->select(
    //         DB::raw("row_number() Over(Order By pengeluarantruckingheader.id) as id"),
    //         'pengeluarantruckingheader.nobukti',
    //         'pengeluarantruckingheader.tglbukti',
    //         'pengeluarantruckingdetail.keterangan',
    //         // 'pengeluarantruckingdetail.nominal',
    //         DB::raw("sum(pengeluarantruckingdetail.nominal) as nominal")
    //     )
    //     ->where('pengeluarantruckingheader.pengeluarantrucking_id',$penerimaantrucking->id)
    //     ->where('pengeluarantruckingdetail.supir_id',$supir_id)
    //     ->leftJoin(DB::raw("pengeluarantruckingdetail with (readuncommitted)"), 'pengeluarantruckingdetail.pengeluarantruckingheader_id','pengeluarantruckingheader.id')
    //     ->groupBy(
    //         'pengeluarantruckingheader.id',
    //         'pengeluarantruckingheader.nobukti',
    //         'pengeluarantruckingheader.tglbukti',
    //         'pengeluarantruckingdetail.keterangan',
    //     );

    //     return $query->get();
    // }

    public function getEditInvoice($id, $tgldari, $tglsampai)
    {
        $this->setRequestParameters();
        $temp = '##tempGet' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $get = DB::table('pengeluarantruckingdetail')->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
            ->select(
                DB::raw("pengeluarantruckingdetail.id as pengeluarantrucking_id"),
                DB::raw("pengeluarantruckingdetail.invoice_nobukti as noinvoice_detail"),
                DB::raw("pengeluarantruckingdetail.orderantrucking_nobukti as nojobtrucking_detail"),
                DB::raw("container.keterangan as container_detail"),
                DB::raw("pengeluarantruckingdetail.nominal as nominal_detail"),
            )
            ->leftJoin(DB::raw("orderantrucking as ot with (readuncommitted)"), 'pengeluarantruckingdetail.orderantrucking_nobukti', 'ot.nobukti')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'ot.container_id', 'container.id')
            ->where('pengeluarantruckingdetail.pengeluarantruckingheader_id', '=', $id);

        Schema::create($temp, function ($table) {
            $table->bigInteger('pengeluarantrucking_id')->nullable();
            $table->string('noinvoice_detail');
            $table->string('nojobtrucking_detail')->nullable();
            $table->string('container_detail')->nullable();
            $table->bigInteger('nominal_detail')->nullable();
        });
        DB::table($temp)->insertUsing(['pengeluarantrucking_id', 'noinvoice_detail', 'nojobtrucking_detail', 'container_detail', 'nominal_detail'], $get);

        $fetch = InvoiceDetail::from(DB::raw("invoicedetail with (readuncommitted)"))
            ->select(DB::raw("
            null as pengeluarantrucking_id,
            invoicedetail.nobukti as noinvoice_detail,
            invoicedetail.orderantrucking_nobukti as nojobtrucking_detail,
            container.keterangan as container_detail,
            (case when container.nominalsumbangan IS NULL then 0 else container.nominalsumbangan end) as nominal_detail

            "))

            ->leftJoin(DB::raw("invoiceheader with (readuncommitted)"), 'invoicedetail.invoice_id', 'invoiceheader.id')
            ->leftJoin(DB::raw("orderantrucking as ot with (readuncommitted)"), 'invoicedetail.orderantrucking_nobukti', 'ot.nobukti')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'ot.container_id', 'container.id')
            ->whereRaw("invoicedetail.orderantrucking_nobukti not in (select orderantrucking_nobukti from pengeluarantruckingdetail where orderantrucking_nobukti != '')")
            ->whereBetween('invoiceheader.tglbukti', [date('Y-m-d', strtotime($tgldari)), date('Y-m-d', strtotime($tglsampai))]);

        DB::table($temp)->insertUsing(['pengeluarantrucking_id', 'noinvoice_detail', 'nojobtrucking_detail', 'container_detail', 'nominal_detail'], $fetch);


        $query = DB::table($temp)->from(DB::raw("$temp with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $temp.noinvoice_detail) as id_detail,pengeluarantrucking_id,noinvoice_detail,nojobtrucking_detail,container_detail,nominal_detail"));

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $this->totalNominal = $query->sum('nominal_detail');

        $this->paginate($query);
        return $query->get();
    }
    public function getShowInvoice($id, $tgldari, $tglsampai)
    {
        $this->setRequestParameters();
        $temp = '##tempGet' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = DB::table('pengeluarantruckingdetail')->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
            ->select(
                DB::raw("pengeluarantruckingdetail.id as pengeluarantrucking_id"),
                DB::raw("pengeluarantruckingdetail.invoice_nobukti as noinvoice_detail"),
                DB::raw("pengeluarantruckingdetail.orderantrucking_nobukti as nojobtrucking_detail"),
                DB::raw("container.keterangan as container_detail"),
                DB::raw("pengeluarantruckingdetail.nominal as nominal_detail"),
            )
            ->leftJoin(DB::raw("orderantrucking as ot with (readuncommitted)"), 'pengeluarantruckingdetail.orderantrucking_nobukti', 'ot.nobukti')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'ot.container_id', 'container.id')
            ->where('pengeluarantruckingdetail.pengeluarantruckingheader_id', '=', $id);
            

        Schema::create($temp, function ($table) {
            $table->bigInteger('pengeluarantrucking_id')->nullable();
            $table->string('noinvoice_detail');
            $table->string('nojobtrucking_detail')->nullable();
            $table->string('container_detail')->nullable();
            $table->bigInteger('nominal_detail')->nullable();
        });
        DB::table($temp)->insertUsing(['pengeluarantrucking_id', 'noinvoice_detail', 'nojobtrucking_detail', 'container_detail', 'nominal_detail'], $fetch);

        $query = DB::table($temp)->from(DB::raw("$temp with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $temp.noinvoice_detail) as id_detail,pengeluarantrucking_id,noinvoice_detail,nojobtrucking_detail,container_detail,nominal_detail"));
        if ($this->params['sortIndex'] == 'id') {
            $query->orderBy($temp . '.nojobtrucking_detail', $this->params['sortOrder']);
        } else {
            $query->orderBy($temp . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $this->totalNominal = $query->sum('nominal_detail');
        // $this->filter($query);
        $this->paginate($query);

        return $query->get();
    }

    public function pengeluarantruckingdetail()
    {
        return $this->hasMany(PengeluaranTruckingDetail::class, 'pengeluarantruckingheader_id');
    }

    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
                "$this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            'pengeluarantrucking.keterangan as pengeluarantrucking_id',
            'bank.namabank as bank_id',
            'statusposting.text as statusposting',
            'statuscetak.memo as statuscetak',
            $this->table.userbukacetak,
            $this->table.tglbukacetak,
            $this->table.coa,
            $this->table.pengeluaran_nobukti,
            $this->table.modifiedby,
            $this->table.updated_at"
            )
        )
            ->leftJoin('pengeluarantrucking', 'pengeluarantruckingheader.pengeluarantrucking_id', 'pengeluarantrucking.id')
            ->leftJoin('bank', 'pengeluarantruckingheader.bank_id', 'bank.id')
            ->leftJoin('parameter as statuscetak', 'pengeluarantruckingheader.statuscetak', 'statuscetak.id')
            ->leftJoin('parameter as statusposting', 'pengeluarantruckingheader.statusposting', 'statusposting.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('pengeluarantrucking_id', 1000)->nullable();
            $table->string('bank_id', 1000)->nullable();
            $table->string('statusposting', 1000)->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->string('coa', 1000)->nullable();
            $table->string('pengeluaran_nobukti', 1000)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        if (request()->tgldariheader) {
            $models  = $query->whereBetween('tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        }
        if (request()->pengeluaranheader_id) {
            $query->where('pengeluarantrucking_id', request()->pengeluaranheader_id);
        }
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'pengeluarantrucking_id', 'bank_id', 'statusposting', 'statuscetak', 'userbukacetak', 'tglbukacetak', 'coa', 'pengeluaran_nobukti', 'modifiedby', 'updated_at'], $models);


        return  $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'pengeluarantrucking_id') {
            return $query->orderBy('pengeluarantrucking.keterangan', $this->params['sortOrder']);
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
                        if ($filters['field'] == 'pengeluarantrucking_id') {
                            $query = $query->where('pengeluarantrucking.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'coa') {
                            $query = $query->where('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'statusposting') {
                            $query = $query->where('statusposting.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statuscetak') {
                            $query = $query->where('statuscetak.text', '=', "$filters[data]");
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
                            if ($filters['field'] == 'pengeluarantrucking_id') {
                                $query->orWhere('pengeluarantrucking.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'bank_id') {
                                $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coa') {
                                $query = $query->orWhere('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'statusposting') {
                                $query->orWhere('statusposting.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statuscetak') {
                                $query->orWhere('statuscetak.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglbukacetak') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
            $query->where('pengeluarantruckingheader.statuscetak', '<>', request()->cetak)
                ->whereYear('pengeluarantruckingheader.tglbukti', '=', request()->year)
                ->whereMonth('pengeluarantruckingheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function storePinjamanPosting($postingPinjaman)
    {
        $postingPinjaman['tanpaprosesnobukti'] = 2;
        $pinjaman = $this->processStore($postingPinjaman);
        // throw new \Exception($xx->nobukti);
        return $pinjaman;
    }
    public function updatePinjamanPosting($nobukti,$postingPinjaman)
    {
        $postingPinjaman['tanpaprosesnobukti'] = 2;

        $pengeluaran = PengeluaranTruckingHeader::where('nobukti',$nobukti)->first();
        $pinjaman = $this->processUpdate($pengeluaran,$postingPinjaman);
        return $pinjaman;
    }

    public function deletePinjamanPosting($id)
    {
        $postingPinjaman['tanpaprosesnobukti'] = 2;

        $pengeluaran = PengeluaranTruckingHeader::where('id',$id)->first();
        $pinjaman = $this->processDestroy($pengeluaran->id);
        return $pinjaman;
    }


    public function processStore(array $data): PengeluaranTruckingHeader
    {
        $idpengeluaran = $data['pengeluarantrucking_id'];
        $fetchFormat =  DB::table('pengeluarantrucking')
            ->where('id', $idpengeluaran)
            ->first();
        $tanpaprosesnobukti = $data['tanpaprosesnobukti'] ?? 0;

        if ($fetchFormat->kodepengeluaran != 'BLS') {
            $data['coa'] = $fetchFormat->coapostingdebet;
        }
        $klaim = DB::table('pengeluarantrucking')->from(DB::raw("pengeluarantrucking with (readuncommitted)"))->where('kodepengeluaran', "KLAIM")->first();
        $statusformat = $fetchFormat->format;
        $fetchGrp = Parameter::where('id', $statusformat)->first();
        $format = DB::table('parameter')->where('grp', $fetchGrp->grp)->where('subgrp', $fetchGrp->subgrp)->first();
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusPosting = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING')->where('text', 'BUKAN POSTING')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        
        $tgldari= null;
        $tglsampai= null;
        if (array_key_exists('tgldari',$data)) {  $tgldari = date('Y-m-d', strtotime($data['tgldari'])); } ;
        if (array_key_exists('tglsampai',$data)) {  $tglsampai = date('Y-m-d', strtotime($data['tglsampai'])); } ;

        $pengeluaranTruckingHeader = new PengeluaranTruckingHeader();

        $pengeluaranTruckingHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $pengeluaranTruckingHeader->pengeluarantrucking_id = $data['pengeluarantrucking_id'];
        $pengeluaranTruckingHeader->bank_id = (array_key_exists('bank_id',$data)) ? $data['bank_id'] : 0 ;
        $pengeluaranTruckingHeader->statusposting = $data['statusposting'] ?? $statusPosting->id ;
        $pengeluaranTruckingHeader->coa = $data['coa'];
        $pengeluaranTruckingHeader->pengeluaran_nobukti = $data['pengeluaran_nobukti'] ?? '';
        $pengeluaranTruckingHeader->periodedari = $tgldari;
        $pengeluaranTruckingHeader->periodesampai = $tglsampai;
        $pengeluaranTruckingHeader->supir_id = $data['supirheader_id'] ?? '';
        $pengeluaranTruckingHeader->trado_id = $data['tradoheader_id'] ?? '';
        $pengeluaranTruckingHeader->statusformat = $data['statusformat'] ?? $format->id;
        $pengeluaranTruckingHeader->statuscetak = $statusCetak->id;
        $pengeluaranTruckingHeader->modifiedby = auth('api')->user()->name;
        $pengeluaranTruckingHeader->nobukti = (new RunningNumberService)->get($fetchGrp->grp, $fetchGrp->subgrp, $pengeluaranTruckingHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
        
        if (!$pengeluaranTruckingHeader->save()) {
            throw new \Exception("Error storing pengeluaran Trucking Header.");
        }
        $pengeluaranTruckingDetails = [];
        for ($i = 0; $i < count($data['nominal']); $i++) {
            $pengeluaranTruckingDetail = (new PengeluaranTruckingDetail())->processStore($pengeluaranTruckingHeader, [
                'pengeluarantruckingheader_id' => $pengeluaranTruckingHeader->id,
                'nobukti' => $pengeluaranTruckingHeader->nobukti,
                'supir_id' => $data['supir_id'][$i] ?? null,
                'stok_id' => $data['stok_id'][$i] ?? null,
                'pengeluaranstok_nobukti' =>$data['pengeluaranstok_nobukti'][$i] ?? null,
                'qty' => $data['qty'][$i] ?? null,
                'harga' => $data['harga'][$i] ?? null,
                'trado_id' => $data['trado_id'][$i] ?? null,
                'penerimaantruckingheader_nobukti' => $data['penerimaantruckingheader_nobukti'][$i] ?? '',
                'invoice_nobukti' => $data['noinvoice_detail'][$i] ?? '',
                'orderantrucking_nobukti' => $data['nojobtrucking_detail'][$i] ?? '',
                'keterangan' => $data['keterangan'][$i] ?? '',
                'nominal' => $data['nominal'][$i],
                'modifiedby' => $pengeluaranTruckingHeader->modifiedby,
            ]);
            $pengeluaranTruckingDetails[] = $pengeluaranTruckingDetail->toArray();
            $nominal_detail []= $pengeluaranTruckingDetail->nominal;
            $keterangan_detail []= $data['keterangan'][$i];
        }
        
        
        if (($tanpaprosesnobukti != 2 ) && ($data['statusposting'] != $statusPosting->id)) {
            

            if ($klaim->id == $data['pengeluarantrucking_id']) {
                if ($data['postingpinjaman'] != $statusPosting->id) {
                    $pinjaman = DB::table('pengeluarantrucking')->from(DB::raw("pengeluarantrucking with (readuncommitted)"))->where('kodepengeluaran', "PJT")->first();
                }
                
                for ($i=0; $i < count($data['nominal']) ; $i++) { 
                   $pjt_supir_id[] = $data['supirheader_id'];
                   $pjt_nominal[] = $data['nominal'][$i];
                   $pjt_keterangan[] = $data['keterangan'][$i];
                    
                }
                $pjtRequest = [
                    "tglbukti" => $data['tglbukti'],
                    "pengeluarantrucking_id" => $pinjaman->id,
                    "statusposting" => $statusPosting->id,
                    'supir_id' => $pjt_supir_id,
                    'nominal' => $pjt_nominal,
                    'keterangan' => $pjt_keterangan,
                ];
                
                $pinjaman = $this->storePinjamanPosting($pjtRequest);
                // throw new \Exception($pinjaman->nobukti);
                
                $pengeluaranTruckingHeader->pengeluarantrucking_nobukti = $pinjaman->nobukti;
                $pengeluaranTruckingHeader->save();
            }else{
                $alatbayar = AlatBayar::where('bank_id', $pengeluaranTruckingHeader->bank_id)->first();
                $queryPengeluaran = Bank::from(DB::raw("bank with (readuncommitted)"))->select( 'parameter.grp', 'parameter.subgrp', 'bank.formatpengeluaran', 'bank.coa', 'bank.tipe')->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpengeluaran', 'parameter.id')->where("bank.id",$data['bank_id'])->first();
                for ($i = 0; $i < count($nominal_detail); $i++) {
                    $coakredit_detail = $queryPengeluaran->coa;
                    $coadebet_detail []= $data['coa'];
                    $nowarkat []= "" ;
                    $tglkasmasuk []= (array_key_exists('tglkasmasuk',$data))?date('Y-m-d', strtotime($data['tglkasmasuk'])) : date('Y-m-d', strtotime($data['tglbukti']));
                }
                /*STORE PENGELUARAN*/
                $pengeluaranRequest = [
                    'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'pelanggan_id' => 0,
                    'postingdari' => $data['postingdari'] ??"ENTRY PENGELUARAN TRUCKING",
                    'statusapproval' => $statusApproval->id,
                    'dibayarke' => '',
                    'alatbayar_id' => $alatbayar->id,
                    'bank_id'=> $data['bank_id'],
                    'transferkeac' => "",
                    'transferkean' => "",
                    'transferkebank' => "",
                    'userapproval'=>"",
                    'tglapproval'=>"",
        
                    'nowarkat'=>$nowarkat  ,
                    'tgljatuhtempo'=> $tglkasmasuk,
                    "nominal_detail"=>$nominal_detail,
                    'coadebet' => $coadebet_detail,
                    'coakredit' => $coakredit_detail,
                    "keterangan_detail"=>$keterangan_detail,
                    'bulanbeban' => $tglkasmasuk,
                ];
        
                $pengeluaranHeader = (new PengeluaranHeader())->processStore($pengeluaranRequest);
    
                $pengeluaranTruckingHeader->pengeluaran_nobukti = $pengeluaranHeader->nobukti;
                $pengeluaranTruckingHeader->save();
            }
        }

        $pengeluaranTruckingHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengeluaranTruckingHeader->getTable()),
            'postingdari' => $data['postingdari'] ??strtoupper('ENTRY penerimaan Header '),
            'idtrans' => $pengeluaranTruckingHeader->id,
            'nobuktitrans' => $pengeluaranTruckingHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pengeluaranTruckingHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengeluaranTruckingDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY HUTANG BAYAR DETAIL'),
            'idtrans' =>  $pengeluaranTruckingHeader->id,
            'nobuktitrans' => $pengeluaranTruckingHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pengeluaranTruckingDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);
        $pengeluaranTruckingHeader->save();
        return $pengeluaranTruckingHeader;
    }

    public function processUpdate(PengeluaranTruckingHeader $pengeluaranTruckingHeader, array $data): PengeluaranTruckingHeader
    {
        $idpengeluaran = $data['pengeluarantrucking_id'];
        $fetchFormat =  DB::table('pengeluarantrucking')
            ->where('id', $idpengeluaran)
            ->first();
        $tanpaprosesnobukti = $data['tanpaprosesnobukti'] ?? 0;
        $from = $data['from'] ?? 'not';
        if ($fetchFormat->kodepengeluaran != 'BLS') {
            $data['coa'] = $fetchFormat->coapostingdebet;
        }
        $klaim = DB::table('pengeluarantrucking')->from(DB::raw("pengeluarantrucking with (readuncommitted)"))->where('kodepengeluaran', "KLAIM")->first();
        $statusformat = $fetchFormat->format;
        $fetchGrp = Parameter::where('id', $statusformat)->first();
        $format = DB::table('parameter')->where('grp', $fetchGrp->grp)->where('subgrp', $fetchGrp->subgrp)->first();
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusPosting = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING')->where('text', 'BUKAN POSTING')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        
        $tgldari= null;
        $tglsampai= null;
        if (array_key_exists('tgldari',$data)) {  $tgldari = date('Y-m-d', strtotime($data['tgldari'])); } ;
        if (array_key_exists('tglsampai',$data)) {  $tglsampai = date('Y-m-d', strtotime($data['tglsampai'])); } ;
        
        $pengeluaranTruckingHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $pengeluaranTruckingHeader->coa = $data['coa'];
        $pengeluaranTruckingHeader->periodedari = $tgldari;
        $pengeluaranTruckingHeader->periodesampai = $tglsampai;
        $pengeluaranTruckingHeader->supir_id = $data['supirheader_id'] ?? '';
        $pengeluaranTruckingHeader->trado_id = $data['tradoheader_id'] ?? '';
        $pengeluaranTruckingHeader->statusformat = $data['statusformat'] ?? $format->id;
        $pengeluaranTruckingHeader->modifiedby = auth('api')->user()->name;
        
        if (!$pengeluaranTruckingHeader->save()) {
            throw new \Exception("Error storing pengeluaran Trucking Header.");
        }

        if ($from == 'ebs') {
            $pengeluaranTruckingHeader->bank_id = $data['bank_id'];
            $pengeluaranTruckingHeader->pengeluaran_nobukti = $data['pengeluaran_nobukti'];

            $pengeluaranTruckingHeader->save();
            return $pengeluaranTruckingHeader;
        }

        /*DELETE EXISTING DETAIL*/
        PengeluaranTruckingDetail::where('pengeluarantruckingheader_id', $pengeluaranTruckingHeader->id)->delete();

        $pengeluaranTruckingDetails = [];
        for ($i = 0; $i < count($data['nominal']); $i++) {
            $pengeluaranTruckingDetail = (new PengeluaranTruckingDetail())->processStore($pengeluaranTruckingHeader, [
                'pengeluarantruckingheader_id' => $pengeluaranTruckingHeader->id,
                'nobukti' => $pengeluaranTruckingHeader->nobukti,
                'supir_id' => $data['supir_id'][$i] ?? null,
                'stok_id' => $data['stok_id'][$i] ?? null,
                'pengeluaranstok_nobukti' =>$data['pengeluaranstok_nobukti'][$i] ?? null,
                'qty' => $data['qty'][$i] ?? null,
                'harga' => $data['harga'][$i] ?? null,
                'trado_id' => $data['trado_id'][$i] ?? null,
                'penerimaantruckingheader_nobukti' => $data['penerimaantruckingheader_nobukti'][$i] ?? '',
                'invoice_nobukti' => $data['noinvoice_detail'][$i] ?? '',
                'orderantrucking_nobukti' => $data['nojobtrucking_detail'][$i] ?? '',
                'keterangan' => $data['keterangan'][$i] ?? '',
                'nominal' => $data['nominal'][$i],
                'modifiedby' => $pengeluaranTruckingHeader->modifiedby,
            ]);
            $pengeluaranTruckingDetails[] = $pengeluaranTruckingDetail->toArray();
            $nominal_detail []= $pengeluaranTruckingDetail->nominal;
            $keterangan_detail []= $data['keterangan'][$i];
        }
        
        
        if (($tanpaprosesnobukti != 2 )) {
            if ($klaim->id == $data['pengeluarantrucking_id']) {
                if ($pengeluaranTruckingDetail->postingpinjaman != $statusPosting->id) {
                    $pinjaman = DB::table('pengeluarantrucking')->from(DB::raw("pengeluarantrucking with (readuncommitted)"))->where('kodepengeluaran', "PJT")->first();
                }
                
                for ($i=0; $i < count($data['nominal']) ; $i++) { 
                   $pjt_supir_id[] = $data['supirheader_id'];
                   $pjt_nominal[] = $data['nominal'][$i];
                   $pjt_keterangan[] = $data['keterangan'][$i];
                    
                }
                $pjtRequest = [
                    "tglbukti" => $data['tglbukti'],
                    "pengeluarantrucking_id" => $pinjaman->id,
                    "statusposting" => $statusPosting->id,
                    'supir_id' => $pjt_supir_id,
                    'nominal' => $pjt_nominal,
                    'keterangan' => $pjt_keterangan,
                ];
                
                $pinjaman = $this->updatePinjamanPosting($pengeluaranTruckingHeader->pengeluarantrucking_nobukti,$pjtRequest);
               
                
            }else{
                if ($pengeluaranTruckingHeader->statusposting != $statusPosting->id){
                    $alatbayar = AlatBayar::where('bank_id', $pengeluaranTruckingHeader->bank_id)->first();
                    $queryPengeluaran = Bank::from(DB::raw("bank with (readuncommitted)"))->select( 'parameter.grp', 'parameter.subgrp', 'bank.formatpengeluaran', 'bank.coa', 'bank.tipe')->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpengeluaran', 'parameter.id')->where("bank.id",$data['bank_id'])->first();
                    for ($i = 0; $i < count($nominal_detail); $i++) {
                        $coakredit_detail []= $queryPengeluaran->coa;
                        $coadebet_detail []= $data['coa'];
                        $nowarkat [] = "" ;
                        $tglkasmasuk [] = (array_key_exists('tglkasmasuk',$data))?date('Y-m-d', strtotime($data['tglkasmasuk'])) : date('Y-m-d', strtotime($data['tglbukti']));
    
                    }
                    /*STORE PENGELUARAN*/
                    $pengeluaranRequest = [
                        'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                        'pelanggan_id' => 0,
                        'postingdari' => $data['postingdari'] ??"ENTRY PENGELUARAN TRUCKING",
                        'statusapproval' => $statusApproval->id,
                        'dibayarke' => '',
                        'alatbayar_id' => $alatbayar->id,
                        'bank_id'=> $data['bank_id'],
                        'transferkeac' => "",
                        'transferkean' => "",
                        'transferkebank' => "",
                        'userapproval'=>"",
                        'tglapproval'=>"",
            
                        'nowarkat'=>$nowarkat,
                        'tgljatuhtempo'=> $tglkasmasuk,
                        "nominal_detail"=>$nominal_detail,
                        'coadebet' => $coadebet_detail,
                        'coakredit' => $coakredit_detail,
                        "keterangan_detail"=>$keterangan_detail,
                        'bulanbeban' => $tglkasmasuk,
                    ];
            
                    $pengeluaranHeader = PengeluaranHeader::where('nobukti',$pengeluaranTruckingHeader->pengeluaran_nobukti)->first();
                    $pengeluaranHeader = (new PengeluaranHeader())->processUpdate($pengeluaranHeader,$pengeluaranRequest);
                }
            }
        }

        $pengeluaranTruckingHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengeluaranTruckingHeader->getTable()),
            'postingdari' => $data['postingdari'] ??strtoupper('ENTRY penerimaan Header '),
            'idtrans' => $pengeluaranTruckingHeader->id,
            'nobuktitrans' => $pengeluaranTruckingHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pengeluaranTruckingHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengeluaranTruckingDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY HUTANG BAYAR DETAIL'),
            'idtrans' =>  $pengeluaranTruckingHeader->id,
            'nobuktitrans' => $pengeluaranTruckingHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pengeluaranTruckingDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);
        $pengeluaranTruckingHeader->save();
        return $pengeluaranTruckingHeader;
    }

    public function processDestroy($id, $postingDari = ''): PengeluaranTruckingHeader
    {
        $statusPosting = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING')->where('text', 'BUKAN POSTING')->first();
        $klaim = DB::table('pengeluarantrucking')->from(DB::raw("pengeluarantrucking with (readuncommitted)"))->where('kodepengeluaran', "KLAIM")->first();

        $pengeluaranTruckingHeader = PengeluaranTruckingHeader::findOrFail($id);
        $dataHeader =  $pengeluaranTruckingHeader->toArray();
        $pengeluaranDetail = PengeluaranTruckingDetail::where('pengeluarantruckingheader_id', $pengeluaranTruckingHeader->id)->get();
        $dataDetail = $pengeluaranDetail->toArray();
        if ($klaim->id == $pengeluaranTruckingHeader->pengeluarantrucking_id) {
            if ($pengeluaranTruckingHeader->postingpinjaman != $statusPosting->id) {
                $pinjaman = PengeluaranTruckingHeader::where('nobukti',$pengeluaranTruckingHeader->pengeluarantrucking_nobukti)->first();
                // dd($pinjaman);
                PengeluaranTruckingHeader::deletePinjamanPosting($pinjaman->id);
            }
        }else{
            if ($pengeluaranTruckingHeader->statusposting != $statusPosting->id) {
                $pengeluaranHeader = PengeluaranHeader::where('nobukti', $pengeluaranTruckingHeader->pengeluaran_nobukti)->lockForUpdate()->first();
                $PengeluaranHeader = (new PengeluaranHeader)->processDestroy($pengeluaranHeader->id, $postingDari);
            }
        }
        $pengeluaranTruckingHeader = $pengeluaranTruckingHeader->lockAndDestroy($id);
        
         $pengeluaranTruckingLogTrail = (new LogTrail())->processStore([
             'namatabel' => $this->table,
             'postingdari' => $postingDari,
             'idtrans' => $pengeluaranTruckingHeader->id,
             'nobuktitrans' => $pengeluaranTruckingHeader->nobukti,
             'aksi' => 'DELETE',
             'datajson' =>$dataHeader,
             'modifiedby' => auth('api')->user()->name
         ]);
 
         (new LogTrail())->processStore([
             'namatabel' => (new LogTrail())->table,
             'postingdari' => $postingDari,
             'idtrans' => $pengeluaranTruckingLogTrail['id'],
             'nobuktitrans' => $pengeluaranTruckingHeader->nobukti,
             'aksi' => 'DELETE',
             'datajson' =>$dataDetail,
             'modifiedby' => auth('api')->user()->name
         ]);
 
         return $pengeluaranTruckingHeader;


    }

}
