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
            ->select('prosesuangjalansupirdetail.id as idtransfer', 'pengeluarantruckingheader.pengeluaran_nobukti as pengeluarantrucking_nobukti', 'prosesuangjalansupirdetail.pengeluarantrucking_tglbukti', 'prosesuangjalansupirdetail.pengeluarantrucking_bank_id', 'prosesuangjalansupirdetail.keterangan', 'prosesuangjalansupirdetail.nominal', 'bank.namabank as bank')
            ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'prosesuangjalansupirdetail.pengeluarantrucking_nobukti', 'pengeluarantruckingheader.nobukti')
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
            ->select('prosesuangjalansupirdetail.id as idadjust', 'penerimaantruckingheader.penerimaan_nobukti', 'prosesuangjalansupirdetail.penerimaantrucking_tglbukti as tgladjust', 'prosesuangjalansupirdetail.penerimaantrucking_bank_id as bank_idadjust', 'prosesuangjalansupirdetail.keterangan as keteranganadjust', 'prosesuangjalansupirdetail.nominal as nilaiadjust', 'bank.namabank as bankadjust')
            ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'prosesuangjalansupirdetail.penerimaantrucking_nobukti', 'penerimaantruckingheader.nobukti')
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
            ->select('prosesuangjalansupirdetail.id as iddeposit', 'penerimaantruckingheader.penerimaan_nobukti as penerimaandeposit_nobukti', 'prosesuangjalansupirdetail.penerimaantrucking_tglbukti as tgldeposit', 'prosesuangjalansupirdetail.penerimaantrucking_bank_id as bank_iddeposit', 'prosesuangjalansupirdetail.keterangan as keterangandeposit', 'prosesuangjalansupirdetail.nominal as nilaideposit', 'bank.namabank as bankdeposit', 'penerimaantruckingheader.nobukti as nobuktideposit')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'prosesuangjalansupirdetail.penerimaantrucking_bank_id', 'bank.id')
            ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingheader.nobukti', 'prosesuangjalansupirdetail.penerimaantrucking_nobukti')
            ->where('prosesuangjalansupirdetail.prosesuangjalansupir_id', $id)
            ->where('prosesuangjalansupirdetail.statusprosesuangjalan', $status->id)
            ->first();
        return $query;
    }

    public function pengembalian($id)
    {
        $status = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'PENGEMBALIAN PINJAMAN')->first();
        // ambil data yang sudah pernah dibuat
        //     $penerimaanTrucking = $this->createTempPenerimaanTrucking($id,$status->id);

        //    $pjt = PengeluaranTrucking::from(DB::raw("pengeluarantrucking with (readuncommitted)"))->where('kodepengeluaran', 'PJT')->first();
        //     $query = PengeluaranTruckingDetail::from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
        //         ->select(
        //             DB::raw("
        //                 pengeluarantruckingheader.id, pengeluarantruckingdetail.nobukti, pengeluarantruckingheader.tglbukti, supir.namasupir, pengeluarantruckingdetail.nominal as jlhpinjaman,
        //                 (SELECT sum(penerimaantruckingdetail.nominal)
        //                 FROM penerimaantruckingdetail 
        //                 WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingheader.nobukti) AS totalbayar,
        //                 (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0))
        //                     FROM penerimaantruckingdetail 
        //                     WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingheader.nobukti) AS sisa, $penerimaanTrucking.keterangan, $penerimaanTrucking.nominal, $penerimaanTrucking.pengeluarantruckingheader_nobukti
        //             ")
        //         )
        //         ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'pengeluarantruckingdetail.nobukti')
        //         ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingdetail.supir_id', 'supir.id')
        //         ->leftJoin(DB::raw("$penerimaanTrucking with (readuncommitted)"), "$penerimaanTrucking.pengeluarantruckingheader_nobukti", 'pengeluarantruckingdetail.nobukti')
        //         ->where('pengeluarantruckingheader.pengeluarantrucking_id', $pjt->id)
        //         ->whereRaw("isnull($penerimaanTrucking.pengeluarantruckingheader_nobukti,'') != ''")
        //         ->get();
        $bank = ProsesUangJalanSupirDetail::from(DB::raw("prosesuangjalansupirdetail with (readuncommitted)"))
            ->select('penerimaantrucking_bank_id as bank_idpengembalian', 'bank.namabank as bankpengembalian', 'penerimaantruckingheader.penerimaan_nobukti as penerimaanpengembalian_nobukti')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'prosesuangjalansupirdetail.penerimaantrucking_bank_id', 'bank.id')
            ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingheader.nobukti', 'prosesuangjalansupirdetail.penerimaantrucking_nobukti')
            ->where('prosesuangjalansupirdetail.prosesuangjalansupir_id', $id)
            ->where('prosesuangjalansupirdetail.statusprosesuangjalan', $status->id)
            ->first();

        return $bank;
    }
    public function createTempPenerimaanTrucking($id, $statusId)
    {
        $getPenerimaan = ProsesUangJalanSupirDetail::from(DB::raw("prosesuangjalansupirdetail with (readuncommitted)"))->where('statusprosesuangjalan', $statusId)->first();

        $pjp = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))->where('kodepenerimaan', 'PJP')->first();
        $temp = '##tempPenerimaanTrucking' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = ProsesUangJalanSupirDetail::from(DB::raw("prosesuangjalansupirdetail with (readuncommitted)"))
            ->select(DB::raw("penerimaantruckingheader.id, penerimaantruckingdetail.pengeluarantruckingheader_nobukti, prosesuangjalansupirdetail.keterangan, prosesuangjalansupirdetail.nominal"))
            ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'prosesuangjalansupirdetail.penerimaantrucking_nobukti', 'penerimaantruckingheader.penerimaan_nobukti')
            ->leftJoin(DB::raw("penerimaantruckingdetail with (readuncommitted)"), 'penerimaantruckingheader.nobukti', 'penerimaantruckingdetail.nobukti')
            ->where('penerimaantruckingheader.penerimaantrucking_id', $pjp->id)
            ->where('penerimaantruckingheader.penerimaan_nobukti', $getPenerimaan->penerimaantrucking_nobukti);

        Schema::create($temp, function ($table) {
            $table->bigInteger('id');
            $table->string('pengeluarantruckingheader_nobukti');
            $table->string('keterangan');
            $table->bigInteger('nominal');
        });

        $tes = DB::table($temp)->insertUsing(['id', 'pengeluarantruckingheader_nobukti', 'keterangan', 'nominal'], $fetch);

        return $temp;
    }

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));

        if (isset(request()->forReport) && request()->forReport) {
            $query->select(
                DB::raw("
                    (case when isnull(prosesuangjalansupirdetail.penerimaantrucking_nobukti,'') = '' then b.pengeluaran_nobukti else c.penerimaan_nobukti end)
                    as nobukti_proses, prosesuangjalansupirdetail.nominal, parameter.text as statusproses, prosesuangjalansupirdetail.keterangan,
                    (case when isnull(bankmasuk.namabank, '') = '' then bankkeluar.namabank else bankmasuk.namabank end)
                    as bank
                ")
            )
                ->leftJoin(DB::raw("pengeluarantruckingheader as b with (readuncommitted)"), 'b.nobukti', '=', $this->table . '.pengeluarantrucking_nobukti')
                ->leftJoin(DB::raw("penerimaantruckingheader as c with (readuncommitted)"), 'c.nobukti', '=', $this->table . '.penerimaantrucking_nobukti')
                ->leftJoin(DB::raw("bank as bankmasuk with (readuncommitted)"), 'bankmasuk.id', '=', $this->table . '.penerimaantrucking_bank_id')
                ->leftJoin(DB::raw("bank as bankkeluar with (readuncommitted)"), 'bankkeluar.id', '=', $this->table . '.pengeluarantrucking_bank_id')
                ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'parameter.id', '=', $this->table . '.statusprosesuangjalan');

            $query->where($this->table . '.prosesuangjalansupir_id', '=', request()->prosesuangjalansupir_id);
        } else if (isset(request()->forExport) && request()->forExport) {
            $query->select(
                $this->table . '.nobukti',
                'penerimaanbank.namabank as penerimaantrucking_bank_id',
                DB::raw("(case when year(isnull($this->table.penerimaantrucking_tglbukti,'1900/1/1'))=1900 then null else $this->table.penerimaantrucking_tglbukti end) as penerimaantrucking_tglbukti"),
                $this->table . '.penerimaantrucking_nobukti',
                'pengeluaranbank.namabank as pengeluarantrucking_bank_id',
                DB::raw("(case when year(isnull($this->table.pengeluarantrucking_tglbukti,'1900/1/1'))=1900 then null else $this->table.pengeluarantrucking_tglbukti end) as pengeluarantrucking_tglbukti"),
                $this->table . '.pengeluarantrucking_nobukti',
                'pengembalianbank.namabank as pengembaliankasgantung_bank_id',
                DB::raw("(case when year(isnull($this->table.pengembaliankasgantung_tglbukti,'1900/1/1'))=1900 then null else $this->table.pengembaliankasgantung_tglbukti end) as pengembaliankasgantung_tglbukti"),
                $this->table . '.pengembaliankasgantung_nobukti',
                $this->table . '.nominal',
                $this->table . '.keterangan',
                'parameter.text as statusprosesuangjalan',

            )
                ->leftJoin(DB::raw("bank as penerimaanbank with (readuncommitted)"), 'penerimaanbank.id', '=', $this->table . '.penerimaantrucking_bank_id')
                ->leftJoin(DB::raw("bank as pengeluaranbank with (readuncommitted)"), 'pengeluaranbank.id', '=', $this->table . '.pengeluarantrucking_bank_id')
                ->leftJoin(DB::raw("bank as pengembalianbank with (readuncommitted)"), 'pengembalianbank.id', '=', $this->table . '.pengembaliankasgantung_bank_id')
                ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'parameter.id', '=', $this->table . '.statusprosesuangjalan');

            $query->where($this->table . '.prosesuangjalansupir_id', '=', request()->prosesuangjalansupir_id);
        } else {
            $query->select(
                $this->table . '.nobukti',
                'penerimaanbank.namabank as penerimaantrucking_bank_id',
                DB::raw("(case when year(isnull($this->table.penerimaantrucking_tglbukti,'1900/1/1'))=1900 then null else $this->table.penerimaantrucking_tglbukti end) as penerimaantrucking_tglbukti"),
                'penerimaantruckingheader.penerimaan_nobukti as penerimaantrucking_nobukti',
                'pengeluaranbank.namabank as pengeluarantrucking_bank_id',
                DB::raw("(case when year(isnull($this->table.pengeluarantrucking_tglbukti,'1900/1/1'))=1900 then null else $this->table.pengeluarantrucking_tglbukti end) as pengeluarantrucking_tglbukti"),
                'pengeluarantruckingheader.pengeluaran_nobukti as pengeluarantrucking_nobukti',
                $this->table . '.nominal',
                $this->table . '.keterangan',
                'parameter.text as statusprosesuangjalan',
                db::raw("cast((format(penerimaantruckingheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpenerimaantruckingheader"),
                db::raw("cast(cast(format((cast((format(penerimaantruckingheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpenerimaantruckingheader"),
                db::raw("cast((format(pengeluarantruckingheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpengeluarantruckingheader"),
                db::raw("cast(cast(format((cast((format(pengeluarantruckingheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpengeluarantruckingheader"),

            )
                ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"),  $this->table . '.pengeluarantrucking_nobukti', '=', 'pengeluarantruckingheader.nobukti')
                ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"),  $this->table . '.penerimaantrucking_nobukti', '=', 'penerimaantruckingheader.nobukti')
                ->leftJoin(DB::raw("bank as penerimaanbank with (readuncommitted)"), 'penerimaanbank.id', '=', $this->table . '.penerimaantrucking_bank_id')
                ->leftJoin(DB::raw("bank as pengeluaranbank with (readuncommitted)"), 'pengeluaranbank.id', '=', $this->table . '.pengeluarantrucking_bank_id')
                ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'parameter.id', '=', $this->table . '.statusprosesuangjalan');

            $this->sort($query);
            $query->where($this->table . '.prosesuangjalansupir_id', '=', request()->prosesuangjalansupir_id);
            $this->filter($query);

            $this->totalNominal = $query->sum('nominal');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->paginate($query);
        }

        return $query->get();
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'penerimaantrucking_bank_id') {
            return $query->orderBy('penerimaanbank.namabank', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'pengeluarantrucking_bank_id') {
            return $query->orderBy('pengeluaranbank.namabank', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'pengembaliankasgantung_bank_id') {
            return $query->orderBy('pengembalianbank.namabank', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'statusprosesuangjalan') {
            return $query->orderBy('parameter.text', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'penerimaantrucking_bank_id') {
                                $query = $query->where('penerimaanbank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'pengeluarantrucking_bank_id') {
                                $query = $query->where('pengeluaranbank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'pengembaliankasgantung_bank_id') {
                                $query = $query->where('pengembalianbank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'penerimaantrucking_nobukti') {
                                $query = $query->where('penerimaantruckingheader.penerimaan_nobukti', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'pengeluarantrucking_nobukti') {
                                $query = $query->where('pengeluarantruckingheader.pengeluaran_nobukti', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'statusprosesuangjalan') {
                                $query = $query->where('parameter.text', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->whereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'penerimaantrucking_tglbukti' || $filters['field'] == 'pengeluarantrucking_tglbukti' || $filters['field'] == 'pengembaliankasgantung_tglbukti') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'penerimaantrucking_bank_id') {
                                $query = $query->orWhere('penerimaanbank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'pengeluarantrucking_bank_id') {
                                $query = $query->orWhere('pengeluaranbank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'pengembaliankasgantung_bank_id') {
                                $query = $query->orWhere('pengembalianbank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'statusprosesuangjalan') {
                                $query = $query->orWhere('parameter.text', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'penerimaantrucking_nobukti') {
                                $query = $query->orWhere('penerimaantruckingheader.penerimaan_nobukti', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'pengeluarantrucking_nobukti') {
                                $query = $query->orWhere('pengeluarantruckingheader.pengeluaran_nobukti', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->orWhereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'penerimaantrucking_tglbukti' || $filters['field'] == 'pengeluarantrucking_tglbukti' || $filters['field'] == 'pengembaliankasgantung_tglbukti') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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

        return $query;
    }
    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function processStore(ProsesUangJalanSupirHeader $prosesUangJalanSupirHeader, array $data): ProsesUangJalanSupirDetail
    {
        $prosesUangJalan = new ProsesUangJalanSupirDetail();
        $prosesUangJalan->prosesuangjalansupir_id = $prosesUangJalanSupirHeader->id;
        $prosesUangJalan->nobukti = $prosesUangJalanSupirHeader->nobukti;
        $prosesUangJalan->penerimaantrucking_bank_id = $data['penerimaantrucking_bank_id'] ?? '';
        $prosesUangJalan->penerimaantrucking_tglbukti = $data['penerimaantrucking_tglbukti'] ?? '';
        $prosesUangJalan->penerimaantrucking_nobukti = $data['penerimaantrucking_nobukti'] ?? '';
        $prosesUangJalan->pengeluarantrucking_bank_id = $data['pengeluarantrucking_bank_id'] ?? '';
        $prosesUangJalan->pengeluarantrucking_tglbukti = $data['pengeluarantrucking_tglbukti'] ?? '';
        $prosesUangJalan->pengeluarantrucking_nobukti = $data['pengeluarantrucking_nobukti'] ?? '';
        $prosesUangJalan->pengembaliankasgantung_bank_id = $data['pengembaliankasgantung_bank_id'] ?? '';
        $prosesUangJalan->pengembaliankasgantung_tglbukti = $data['pengembaliankasgantung_tglbukti'] ?? '';
        $prosesUangJalan->pengembaliankasgantung_nobukti = $data['pengembaliankasgantung_nobukti'] ?? '';
        $prosesUangJalan->statusprosesuangjalan = $data['statusprosesuangjalan'];
        $prosesUangJalan->nominal = $data['nominal'];
        $prosesUangJalan->keterangan = $data['keterangan'];
        $prosesUangJalan->modifiedby = auth('api')->user()->name;
        $prosesUangJalan->info = html_entity_decode(request()->info);

        if (!$prosesUangJalan->save()) {
            throw new \Exception("Error storing proses uang jalan detail.");
        }

        return $prosesUangJalan;
    }
}
