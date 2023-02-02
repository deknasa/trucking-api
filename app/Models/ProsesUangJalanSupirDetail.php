<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProsesUangJalanSupirDetail extends MyModel
{
    use HasFactory;
    protected $table = 'prosesuangjalansupirdetail';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function findTransfer($id)
    {
        $status = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'TRANSFER')->first();
        $query = ProsesUangJalanSupirDetail::from(DB::raw("prosesuangjalansupirdetail with (readuncommitted)"))
            ->select('prosesuangjalansupirdetail.id as idtransfer','prosesuangjalansupirdetail.pengeluarantrucking_nobukti', 'prosesuangjalansupirdetail.pengeluarantrucking_tglbukti', 'prosesuangjalansupirdetail.pengeluarantrucking_bank_id', 'prosesuangjalansupirdetail.keterangan', 'prosesuangjalansupirdetail.nominal', 'bank.namabank as bank')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'prosesuangjalansupirdetail.pengeluarantrucking_bank_id', 'bank.id')
            ->where('prosesuangjalansupirdetail.prosesuangjalansupir_id', $id)
            ->where('prosesuangjalansupirdetail.statusprosesuangjalan', $status->id)
            ->get();
        return $query;
    }

    public function adjustTransfer($id)
    {
        $status = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'ADJUST TRANSFER')->first();
        $query = ProsesUangJalanSupirDetail::from(DB::raw("prosesuangjalansupirdetail with (readuncommitted)"))
            ->select('prosesuangjalansupirdetail.id as idadjust','prosesuangjalansupirdetail.penerimaantrucking_nobukti as penerimaan_nobukti', 'prosesuangjalansupirdetail.penerimaantrucking_tglbukti as tgladjust', 'prosesuangjalansupirdetail.penerimaantrucking_bank_id as bank_idadjust', 'prosesuangjalansupirdetail.keterangan as keteranganadjust', 'prosesuangjalansupirdetail.nominal as nilaiadjust', 'bank.namabank as bankadjust')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'prosesuangjalansupirdetail.penerimaantrucking_bank_id', 'bank.id')
            ->where('prosesuangjalansupirdetail.prosesuangjalansupir_id', $id)
            ->where('prosesuangjalansupirdetail.statusprosesuangjalan', $status->id)
            ->first();
        return $query;
    }

    public function deposito($id)
    {
        $status = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'DEPOSITO SUPIR')->first();
        $query = ProsesUangJalanSupirDetail::from(DB::raw("prosesuangjalansupirdetail with (readuncommitted)"))
            ->select('prosesuangjalansupirdetail.id as iddeposit','prosesuangjalansupirdetail.penerimaantrucking_nobukti as penerimaandeposit_nobukti', 'prosesuangjalansupirdetail.penerimaantrucking_tglbukti as tgldeposit', 'prosesuangjalansupirdetail.penerimaantrucking_bank_id as bank_iddeposit', 'prosesuangjalansupirdetail.keterangan as keterangandeposit', 'prosesuangjalansupirdetail.nominal as nilaideposit', 'bank.namabank as bankdeposit','penerimaantruckingheader.nobukti as nobuktideposit')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'prosesuangjalansupirdetail.penerimaantrucking_bank_id', 'bank.id')
            ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingheader.penerimaan_nobukti', 'prosesuangjalansupirdetail.penerimaantrucking_nobukti')
            ->where('prosesuangjalansupirdetail.prosesuangjalansupir_id', $id)
            ->where('prosesuangjalansupirdetail.statusprosesuangjalan', $status->id)
            ->first();
        return $query;
    }

    public function pengembalian($id)
    {
        $penerimaanTrucking = $this->createTempPenerimaanTrucking($id);

        $status = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'PENGEMBALIAN PINJAMAN')->first();
       $pjt = PengeluaranTrucking::from(DB::raw("pengeluarantrucking with (readuncommitted)"))->where('kodepengeluaran', 'PJT')->first();
        $query = PengeluaranTruckingDetail::from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
            ->select(
                DB::raw("
                    pengeluarantruckingheader.id, pengeluarantruckingdetail.nobukti, pengeluarantruckingheader.tglbukti, supir.namasupir, pengeluarantruckingdetail.nominal as jlhpinjaman,
                    (SELECT (penerimaantruckingdetail.nominal)
                    FROM penerimaantruckingdetail 
                    WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingheader.nobukti) AS totalbayar,
                    (SELECT (pengeluarantruckingdetail.nominal - penerimaantruckingdetail.nominal)
                        FROM penerimaantruckingdetail 
                        WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingheader.nobukti) AS sisa, $penerimaanTrucking.keterangan, $penerimaanTrucking.nominal, $penerimaanTrucking.pengeluarantruckingheader_nobukti
                ")
            )
            ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'pengeluarantruckingdetail.nobukti')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingdetail.supir_id', 'supir.id')
            ->leftJoin(DB::raw("$penerimaanTrucking with (readuncommitted)"), "$penerimaanTrucking.pengeluarantruckingheader_nobukti", 'pengeluarantruckingdetail.nobukti')
            ->where('pengeluarantruckingheader.pengeluarantrucking_id', $pjt->id)
            ->whereRaw("isnull($penerimaanTrucking.pengeluarantruckingheader_nobukti,'') != ''")
            ->get();
        $bank = ProsesUangJalanSupirDetail::from(DB::raw("prosesuangjalansupirdetail with (readuncommitted)"))
        ->select('penerimaantrucking_bank_id as bank_idpengembalian', 'bank.namabank as bankpengembalian')
        ->leftJoin(DB::raw("bank with (readuncommitted)"), 'prosesuangjalansupirdetail.penerimaantrucking_bank_id', 'bank.id')
        ->where('prosesuangjalansupirdetail.prosesuangjalansupir_id', $id)
        ->where('prosesuangjalansupirdetail.statusprosesuangjalan', $status->id)
        ->first();

        $datapengembalian = [
            'detail' => $query,
            'bank' => $bank
        ];
        return $datapengembalian;
    }
    public function createTempPenerimaanTrucking($id)
    {
        
        $pjp = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))->where('kodepenerimaan', 'PJP')->first();
        $temp = '##tempPenerimaanTrucking' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = ProsesUangJalanSupirDetail::from(DB::raw("prosesuangjalansupirdetail with (readuncommitted)"))
            ->select(DB::raw("penerimaantruckingheader.id, penerimaantruckingdetail.pengeluarantruckingheader_nobukti, prosesuangjalansupirdetail.keterangan, prosesuangjalansupirdetail.nominal"))
            ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'prosesuangjalansupirdetail.penerimaantrucking_nobukti', 'penerimaantruckingheader.penerimaan_nobukti')
            ->leftJoin(DB::raw("penerimaantruckingdetail with (readuncommitted)"), 'penerimaantruckingheader.nobukti', 'penerimaantruckingdetail.nobukti')
            ->where('penerimaantruckingheader.penerimaantrucking_id', $pjp->id)
            ->where('prosesuangjalansupirdetail.prosesuangjalansupir_id', $id);

        Schema::create($temp, function ($table) {
            $table->bigInteger('id');
            $table->string('pengeluarantruckingheader_nobukti');
            $table->string('keterangan');
            $table->bigInteger('nominal');
        });

        $tes = DB::table($temp)->insertUsing(['id', 'pengeluarantruckingheader_nobukti', 'keterangan','nominal'], $fetch);

        return $temp;
    }

}
