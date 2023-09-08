<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProsesUangJalanSupirHeader extends MyModel
{
    use HasFactory;
    protected $table = 'prosesuangjalansupirheader';

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


        $query = DB::table($this->table)->from(DB::raw("prosesuangjalansupirheader with (readuncommitted)"))
            ->select(
                'prosesuangjalansupirheader.id',
                'prosesuangjalansupirheader.nobukti',
                'prosesuangjalansupirheader.tglbukti',
                'prosesuangjalansupirheader.absensisupir_nobukti',
                'prosesuangjalansupirheader.nominaluangjalan',
                'prosesuangjalansupirheader.userapproval',
                DB::raw('(case when (year(prosesuangjalansupirheader.tglapproval) <= 2000) then null else prosesuangjalansupirheader.tglapproval end ) as tglapproval'),
                'statusapproval.memo as statusapproval',
                'trado.kodetrado as trado_id',
                'statuscetak.memo as statuscetak',
                'supir.namasupir as supir_id',
                'prosesuangjalansupirheader.modifiedby',
                'prosesuangjalansupirheader.created_at',
                'prosesuangjalansupirheader.updated_at'
            )
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'prosesuangjalansupirheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'prosesuangjalansupirheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'prosesuangjalansupirheader.trado_id', 'trado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'prosesuangjalansupirheader.supir_id', 'supir.id');

        if (request()->tgldari) {
            $query->whereBetween('tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function getPinjaman($supirId)
    {
        $tempPribadi = $this->createTempPinjaman($supirId);

        $query = PengeluaranTruckingDetail::from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By pengeluarantruckingdetail.nobukti) as id,pengeluarantruckingheader.tglbukti,pengeluarantruckingdetail.nobukti as nobuktipengeluaran,pengeluarantruckingdetail.keterangan as keteranganpinjaman," . $tempPribadi . ".sisa"))
            ->leftJoin(DB::raw("$tempPribadi with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', $tempPribadi . ".nobukti")
            ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', "pengeluarantruckingheader.nobukti")
            ->whereRaw("pengeluarantruckingdetail.supir_id = $supirId")
            ->whereRaw("pengeluarantruckingdetail.nobukti = $tempPribadi.nobukti")
            ->where(function ($query) use ($tempPribadi) {
                $query->whereRaw("$tempPribadi.sisa != 0")
                    ->orWhereRaw("$tempPribadi.sisa is null");
            })
            ->orderBy('pengeluarantruckingheader.tglbukti', 'asc')
            ->orderBy('pengeluarantruckingdetail.nobukti', 'asc');

        return $query->get();
    }

    public function createTempPinjaman($supirId)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("pengeluarantruckingdetail.nobukti, (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"))
            // ->leftJoin(DB::raw("penerimaantruckingdetail with (readuncommitted)"), 'penerimaantruckingdetail.pengeluarantruckingheader_nobukti', 'pengeluarantruckingdetail.nobukti')
            ->whereRaw("pengeluarantruckingdetail.supir_id = $supirId")
            ->where("pengeluarantruckingdetail.nobukti",  'LIKE', "%PJT%")
            ->groupBy('pengeluarantruckingdetail.nobukti', 'pengeluarantruckingdetail.nominal');

        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'sisa'], $fetch);


        return $temp;
    }

    public function getPengembalian($id)
    {
        $status = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'PENGEMBALIAN PINJAMAN')->first();
        $getNobukti = ProsesUangJalanSupirDetail::from(DB::raw("prosesuangjalansupirdetail with (readuncommitted)"))
            ->select('penerimaantruckingheader.nobukti')
            ->join(DB::raw("penerimaantruckingheader with (readuncommitted)"), "prosesuangjalansupirdetail.penerimaantrucking_nobukti", 'penerimaantruckingheader.penerimaan_nobukti')
            ->where('prosesuangjalansupirdetail.prosesuangjalansupir_id', $id)
            ->where('prosesuangjalansupirdetail.statusprosesuangjalan', $status->id)
            ->first();

        $query = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("penerimaantruckingdetail.penerimaantruckingheader_id as id,pengeluarantruckingdetail.nobukti as nobuktipengeluaran,pengeluarantruckingdetail.keterangan as keteranganpinjaman, 
            penerimaantruckingdetail.nominal as nombayar,
            (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) 
            FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"))
            ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'pengeluarantruckingdetail.nobukti')
            ->leftJoin(DB::raw("penerimaantruckingdetail with (readuncommitted)"), 'penerimaantruckingdetail.pengeluarantruckingheader_nobukti', 'pengeluarantruckingdetail.nobukti')
            ->where('penerimaantruckingdetail.nobukti', $getNobukti->nobukti);

        return $query->get();
    }

    public function findAll($id)
    {
        $query = ProsesUangJalanSupirHeader::from(DB::raw("prosesuangjalansupirheader with (readuncommitted)"))
            ->select(
                'prosesuangjalansupirheader.id',
                'prosesuangjalansupirheader.nobukti',
                'prosesuangjalansupirheader.tglbukti',
                'prosesuangjalansupirheader.absensisupir_nobukti as absensisupir',
                'prosesuangjalansupirheader.supir_id',
                'supir.namasupir as supir',
                'prosesuangjalansupirheader.trado_id',
                'trado.kodetrado as trado'
            )
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'prosesuangjalansupirheader.supir_id', 'supir.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'prosesuangjalansupirheader.trado_id', 'trado.id')
            ->where('prosesuangjalansupirheader.id', $id);

        return $query->first();
    }
    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
                "$this->table.id,
                 $this->table.nobukti,
                 $this->table.tglbukti,
                 $this->table.absensisupir_nobukti,
                 $this->table.trado_id,
                 $this->table.supir_id,
                 $this->table.nominaluangjalan,
                 $this->table.statusapproval,
                 $this->table.modifiedby,
                 $this->table.updated_at"
            )
        );
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('absensisupir_nobukti', 1000)->nullable();
            $table->bigInteger('trado_id')->nullable();
            $table->bigInteger('supir_id')->nullable();
            $table->float('nominaluangjalan')->nullable();
            $table->bigInteger('statusapproval')->nullable();
            $table->string('modifiedby')->default();
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
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'absensisupir_nobukti', 'trado_id', 'supir_id', 'nominaluangjalan', 'statusapproval', 'modifiedby', 'updated_at'], $models);

        return $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'trado_id') {
            return $query->orderBy('trado.kodetrado', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'supir_id') {
            return $query->orderBy('supir.namasupir', $this->params['sortOrder']);
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
                            $query = $query->where('statuscetak.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('statusapproval.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'trado_id') {
                            $query = $query->where('trado.kodetrado', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'supir_id') {
                            $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'nominaluangjalan') {
                            $query = $query->whereRaw("format($this->table.nominaluangjalan, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglapproval') {
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
                                $query = $query->orWhere('statuscetak.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusapproval') {
                                $query = $query->orWhere('statusapproval.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'trado_id') {
                                $query = $query->orWhere('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'supir_id') {
                                $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominaluangjalan') {
                                $query = $query->orWhereRaw("format($this->table.nominaluangjalan, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglapproval') {
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

        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function getNominalAbsensi($nobukti)
    {
        $query = DB::table("absensisupirheader")->from(DB::raw("absensisupirheader with (readuncommitted)"))
            ->where('nobukti', $nobukti)
            ->first();
        return $query;
    }

    public function getSisaPinjamanForValidation($nobukti)
    {
        $fetch = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("pengeluarantruckingdetail.nobukti, (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"))

            ->where("pengeluarantruckingdetail.nobukti", $nobukti)
            ->groupBy('pengeluarantruckingdetail.nobukti', 'pengeluarantruckingdetail.nominal');

        return $fetch->first();
    }

    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($this->table)->from(DB::raw("prosesuangjalansupirheader with (readuncommitted)"))
            ->select(
                'prosesuangjalansupirheader.id',
                'prosesuangjalansupirheader.nobukti',
                'prosesuangjalansupirheader.tglbukti',
                'prosesuangjalansupirheader.absensisupir_nobukti',
                'prosesuangjalansupirheader.nominaluangjalan',
                'trado.kodetrado as trado_id',
                'supir.namasupir as supir_id',
                'statuscetak.memo as statuscetak',
                "statuscetak.id as  statuscetak_id",
                'prosesuangjalansupirheader.jumlahcetak',
                DB::raw("'Laporan Proses Uang Jalan Supir' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'prosesuangjalansupirheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'prosesuangjalansupirheader.trado_id', 'trado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'prosesuangjalansupirheader.supir_id', 'supir.id');

        $data = $query->first();
        return $data;
    }
    public function processStore(array $data): ProsesUangJalanSupirHeader
    {

        $dataAbsensiSupir = AbsensiSupirHeader::from(DB::raw("absensisupirheader with (readuncommitted)"))->where('nobukti', $data['absensisupir'])->first();

        $group = 'PROSES UANG JALAN BUKTI';
        $subGroup = 'PROSES UANG JALAN BUKTI';
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $format = DB::table('parameter')
            ->where('grp', $group)
            ->where('subgrp', $subGroup)
            ->first();

        $prosesUangJalanSupir = new ProsesUangJalanSupirHeader();

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $prosesUangJalanSupir->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $prosesUangJalanSupir->absensisupir_nobukti = $data['absensisupir'];
        $prosesUangJalanSupir->trado_id = $data['trado_id'];
        $prosesUangJalanSupir->supir_id = $data['supir_id'];
        $prosesUangJalanSupir->statuscetak = $statusCetak->id ?? 0;
        $prosesUangJalanSupir->nominaluangjalan = $dataAbsensiSupir->nominal;
        $prosesUangJalanSupir->statusapproval = $statusApproval->id;
        $prosesUangJalanSupir->statusformat = $format->id;
        $prosesUangJalanSupir->modifiedby = auth('api')->user()->name;

        $prosesUangJalanSupir->nobukti = (new RunningNumberService)->get($group, $subGroup, $prosesUangJalanSupir->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$prosesUangJalanSupir->save()) {
            throw new \Exception("Error storing proses uang jalan supir.");
        }

        $prosesUangJalanSupirLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($prosesUangJalanSupir->getTable()),
            'postingdari' => 'ENTRY PROSES UANG JALAN SUPIR',
            'idtrans' => $prosesUangJalanSupir->id,
            'nobuktitrans' => $prosesUangJalanSupir->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $prosesUangJalanSupir->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        $statusTransfer = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'TRANSFER')->first();
        $statusAdjust = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'ADJUST TRANSFER')->first();
        $statusPengembalian = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'PENGEMBALIAN PINJAMAN')->first();
        $statusDeposit = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'DEPOSITO SUPIR')->first();
        $statusPosting = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING')->where('text', 'POSTING')->first();
        $detailLog = [];
        //INSERT PENGELUARAN DARI LIST TRANSFER            
        $detaillogTransfer = [];
        for ($i = 0; $i < count($data['nilaitransfer']); $i++) {
            $bankid = $data['bank_idtransfer'][$i];
            $coatransfer = Bank::from(DB::raw("bank with (readuncommitted)"))->where('id', $bankid)->first();

            // PENGELUARAN TRUCKING HEADER
            $fetchFormatBLS =  DB::table('pengeluarantrucking')
                ->where('kodepengeluaran', 'BLS')
                ->first();
            $supirIdTransfer = [];
            $nominalTransfer = [];
            $keteranganTransfer = [];

            $supirIdTransfer[] = $data['supir_id'];
            $nominalTransfer[] = $data['nilaitransfer'][$i];
            $keteranganTransfer[] = $data['keterangantransfer'][$i];

            $pengeluaranTruckingHeader = [
                'tglbukti' => date('Y-m-d', strtotime($data['tgltransfer'][$i])),
                'pengeluarantrucking_id' => $fetchFormatBLS->id,
                'bank_id' => $bankid,
                'coa' => $coatransfer->coa,
                'pengeluaran_nobukti' => '',
                'statusposting' => $statusPosting->id,
                'postingdari' => 'ENTRY PROSES UANG JALAN',
                'supir_id' => $supirIdTransfer,
                'nominal' => $nominalTransfer,
                'keterangan' => $keteranganTransfer
            ];

            $dataPengeluaran = (new PengeluaranTruckingHeader())->processStore($pengeluaranTruckingHeader);

            $datadetail = [
                'prosesuangjalansupir_id' => $prosesUangJalanSupir->id,
                'nobukti' => $prosesUangJalanSupir->nobukti,
                'penerimaantrucking_bank_id' => '',
                'penerimaantrucking_tglbukti' => '',
                'penerimaantrucking_nobukti' => '',
                'pengeluarantrucking_bank_id' => $bankid,
                'pengeluarantrucking_tglbukti' => date('Y-m-d', strtotime($data['tgltransfer'][$i])),
                'pengeluarantrucking_nobukti' => $dataPengeluaran->pengeluaran_nobukti,
                'pengembaliankasgantung_bank_id' => '',
                'pengembaliankasgantung_tglbukti' => '',
                'pengembaliankasgantung_nobukti' => '',
                'statusprosesuangjalan' => $statusTransfer->id,
                'nominal' => $data['nilaitransfer'][$i],
                'keterangan' => $data['keterangantransfer'][$i],
                'modifiedby' => $prosesUangJalanSupir->modifiedby,

            ];

            //STORE PROSES UANG JALAN DETAIL
            $prosesUangJalanSupirDetail = (new ProsesUangJalanSupirDetail())->processStore($prosesUangJalanSupir, $datadetail);

            $prosesUangJalanSupirDetails[] = $prosesUangJalanSupirDetail->toArray();
        }
        // END PENGELUARAN DARI LIST TRANSFER 


        // INSERT PENGEMBALIAN KAS GANTUNG

        $nominalAdjust[] = $data['nilaiadjust'];
        $keteranganAdjust[] = $data['keteranganadjust'];
        $kasgantungNobukti[] = $dataAbsensiSupir->kasgantung_nobukti;

        $pengembalianKasgantung = [
            'tglbukti' => date('Y-m-d', strtotime($data['tgladjust'])),
            'pelanggan_id' => '',
            'bank_id' => $data['bank_idadjust'],
            'postingdari' => 'ENTRY PROSES UANG JALAN SUPIR',
            'tgldari' => $data['tgladjust'],
            'tglsampai' => $data['tgladjust'],
            'tglkasmasuk' => date('Y-m-d', strtotime($data['tgladjust'])),
            'diterimadari' => $data['supir'],
            'nominal' => $nominalAdjust,
            'keterangandetail' => $keteranganAdjust,
            'kasgantung_nobukti' => $kasgantungNobukti,
            'kasgantungdetail_id' => $kasgantungNobukti
        ];
        $dataKasgantung = (new PengembalianKasGantungHeader())->processStore($pengembalianKasgantung);

        $datadetail = [
            'prosesuangjalansupir_id' => $prosesUangJalanSupir->id,
            'nobukti' => $prosesUangJalanSupir->nobukti,
            'penerimaantrucking_bank_id' => $data['bank_idadjust'],
            'penerimaantrucking_tglbukti' => date('Y-m-d', strtotime($data['tgladjust'])),
            'penerimaantrucking_nobukti' => $dataKasgantung->penerimaan_nobukti,
            'pengeluarantrucking_bank_id' => '',
            'pengeluarantrucking_tglbukti' => '',
            'pengeluarantrucking_nobukti' => '',
            'pengembaliankasgantung_bank_id' => $data['bank_idadjust'],
            'pengembaliankasgantung_tglbukti' => date('Y-m-d', strtotime($data['tgladjust'])),
            'pengembaliankasgantung_nobukti' => $dataKasgantung->nobukti,
            'statusprosesuangjalan' => $statusAdjust->id,
            'nominal' => $data['nilaiadjust'],
            'keterangan' => $data['keteranganadjust'],
            'modifiedby' => $prosesUangJalanSupir->modifiedby,

        ];

        //STORE PROSES UANG JALAN DETAIL
        $prosesUangJalanSupirDetail = (new ProsesUangJalanSupirDetail())->processStore($prosesUangJalanSupir, $datadetail);

        $prosesUangJalanSupirDetails[] = $prosesUangJalanSupirDetail->toArray();
        // END PENERIMAAN DARI ADJUST TRANSFER / PENGEMBALIAN KAS GANTUNG


        // INSERT PENERIMAAN DARI DEPOSITO
        $bankidDeposit = $data['bank_iddeposit'];
        if ($bankidDeposit != '') {
            // INSERT PENERIMAAN TRUCKING DEPOSITO
            $fetchFormatDPO = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))
                ->where('kodepenerimaan', 'DPO')
                ->first();

            $supirIdDeposito[] = $data['supir_id'];
            $nominalDeposito[] = $data['nilaideposit'];
            $keteranganDeposito[] = $data['keterangandeposit'];

            $penerimaanTruckingHeaderDPO = [
                'tglbukti' => date('Y-m-d', strtotime($data['tgldeposit'])),
                'penerimaantrucking_id' => $fetchFormatDPO->id,
                'bank_id' => $bankidDeposit,
                'coa' => $fetchFormatDPO->coapostingkredit,
                'penerimaan_nobukti' => '',
                'postingdari' => 'ENTRY PROSES UANG JALAN',
                'supir_id' => $supirIdDeposito,
                'nominal' => $nominalDeposito,
                'keterangan' => $keteranganDeposito
            ];

            $dataPenerimaanDepo = (new PenerimaanTruckingHeader())->processStore($penerimaanTruckingHeaderDPO);

            $datadetail = [
                'prosesuangjalansupir_id' => $prosesUangJalanSupir->id,
                'nobukti' => $prosesUangJalanSupir->nobukti,
                'penerimaantrucking_bank_id' => $bankidDeposit,
                'penerimaantrucking_tglbukti' => date('Y-m-d', strtotime($data['tgldeposit'])),
                'penerimaantrucking_nobukti' => $dataPenerimaanDepo->penerimaan_nobukti,
                'pengeluarantrucking_bank_id' => '',
                'pengeluarantrucking_tglbukti' => '',
                'pengeluarantrucking_nobukti' => '',
                'pengembaliankasgantung_bank_id' => '',
                'pengembaliankasgantung_tglbukti' => '',
                'pengembaliankasgantung_nobukti' => '',
                'statusprosesuangjalan' => $statusDeposit->id,
                'nominal' => $data['nilaideposit'],
                'keterangan' => $data['keterangandeposit'],
                'modifiedby' => $prosesUangJalanSupir->modifiedby,

            ];

            //STORE PROSES UANG JALAN DETAIL
            $prosesUangJalanSupirDetail = (new ProsesUangJalanSupirDetail())->processStore($prosesUangJalanSupir, $datadetail);

            $prosesUangJalanSupirDetails[] = $prosesUangJalanSupirDetail->toArray();
        }
        // END PENERIMAAN DARI DEPOSITO
        // INSERT PENERIMAAN DARI PENGEMBALIAN PINJAMAN
        $detaillogPinjaman = [];

        if ($data['pjt_id']) {

            for ($i = 0; $i < count($data['pjt_id']); $i++) {
                $bankidPengembalian = $data['bank_idpengembalian'];

                // PENERIMAAN TRUCKING HEADER
                $fetchFormatPJP =  DB::table('penerimaantrucking')
                    ->where('kodepenerimaan', 'PJP')
                    ->first();
                $statusformaPJP = $fetchFormatPJP->format;

                $supirPengembalian = [];
                $pengeluaranTruckingPengembalian = [];
                $nominalPengembalian = [];
                $keteranganPengembalian = [];

                $supirPengembalian[] = $data['supir_id'];
                $pengeluaranTruckingPengembalian[] = $data['pengeluarantruckingheader_nobukti'][$i];
                $nominalPengembalian[] = $data['nombayar'][$i];
                $keteranganPengembalian[] = $data['keteranganpinjaman'][$i];

                $penerimaanTruckingHeaderPJP = [
                    'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'penerimaantrucking_id' => $fetchFormatPJP->id,
                    'bank_id' => $bankidPengembalian,
                    'coa' => $fetchFormatPJP->coapostingkredit,
                    'penerimaan_nobukti' => '',
                    'postingdari' => 'ENTRY PROSES UANG JALAN',
                    'diterimadari' => $data['supir'],
                    'supir_id' => $supirPengembalian,
                    'pengeluarantruckingheader_nobukti' => $pengeluaranTruckingPengembalian,
                    'keterangan' => $keteranganPengembalian,
                    'nominal' => $nominalPengembalian
                ];

                $dataPenerimaanPinjaman = (new PenerimaanTruckingHeader())->processStore($penerimaanTruckingHeaderPJP);


                $datadetail = [
                    'prosesuangjalansupir_id' => $prosesUangJalanSupir->id,
                    'nobukti' => $prosesUangJalanSupir->nobukti,
                    'penerimaantrucking_bank_id' => $bankidPengembalian,
                    'penerimaantrucking_tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'penerimaantrucking_nobukti' => $dataPenerimaanPinjaman->penerimaan_nobukti,
                    'pengeluarantrucking_bank_id' => '',
                    'pengeluarantrucking_tglbukti' => '',
                    'pengeluarantrucking_nobukti' => '',
                    'pengembaliankasgantung_bank_id' => '',
                    'pengembaliankasgantung_tglbukti' => '',
                    'pengembaliankasgantung_nobukti' => '',
                    'statusprosesuangjalan' => $statusPengembalian->id,
                    'nominal' => $data['nombayar'][$i],
                    'keterangan' => $data['keteranganpinjaman'][$i],
                    'modifiedby' => $prosesUangJalanSupir->modifiedby,

                ];

                //STORE PROSES UANG JALAN DETAIL
                $prosesUangJalanSupirDetail = (new ProsesUangJalanSupirDetail())->processStore($prosesUangJalanSupir, $datadetail);

                $prosesUangJalanSupirDetails[] = $prosesUangJalanSupirDetail->toArray();
            }
        }
        // END PENERIMAAN PENGEMBALIAN PINJAMAN


        (new LogTrail())->processStore([
            'namatabel' => strtoupper($prosesUangJalanSupir->getTable()),
            'postingdari' =>  'ENTRY PROSES UANG JALAN SUPIR DETAIL',
            'idtrans' =>  $prosesUangJalanSupirLogTrail->id,
            'nobuktitrans' => $prosesUangJalanSupir->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $prosesUangJalanSupirDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);
        return $prosesUangJalanSupir;
    }

    public function processUpdate(ProsesUangJalanSupirHeader $prosesUangJalanSupirHeader, array $data): ProsesUangJalanSupirHeader
    {
        $getTgl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'EDIT TANGGAL BUKTI')->where('subgrp', 'PROSES UANG JALAN')->first();

        if (trim($getTgl->text) == 'YA') {
            $group = 'PROSES UANG JALAN BUKTI';
            $subGroup = 'PROSES UANG JALAN BUKTI';
            $querycek = DB::table('prosesuangjalansupirheader')->from(
                DB::raw("prosesuangjalansupirheader a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.id', $prosesUangJalanSupirHeader->id)
                ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
                ->first();

            if (isset($querycek)) {
                $nobukti = $querycek->nobukti;
            } else {
                $nobukti = (new RunningNumberService)->get($group, $subGroup, $prosesUangJalanSupirHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
            }

            $prosesUangJalanSupirHeader->nobukti = $nobukti;
            $prosesUangJalanSupirHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        }
        
        $prosesUangJalanSupirHeader->modifiedby = auth('api')->user()->name;

        if (!$prosesUangJalanSupirHeader->save()) {
            throw new \Exception("Error updating proses uang jalan supir header.");
        }

        $prosesUangJalanSupirHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($prosesUangJalanSupirHeader->getTable()),
            'postingdari' => 'EDIT PROSES UANG JALAN SUPIR',
            'idtrans' => $prosesUangJalanSupirHeader->id,
            'nobuktitrans' => $prosesUangJalanSupirHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $prosesUangJalanSupirHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        $id = $prosesUangJalanSupirHeader->id;
        $detail = new ProsesUangJalanSupirDetail();
        $detailTransfer = $detail->findTransfer($id);


        $detailLog = [];
        foreach ($detailTransfer as $key => $value) {
            $pengeluarantrucking_nobukti = $value['pengeluarantrucking_nobukti'];
            $fetchFormatBLS =  DB::table('pengeluarantrucking')
                ->where('kodepengeluaran', 'BLS')
                ->first();

            $getPengeluaranTrucking = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))->where("pengeluaran_nobukti", $pengeluarantrucking_nobukti)->first();

            $supirIdTransfer = [];
            $nominalTransfer = [];
            $keteranganTransfer = [];

            $bankid = $data['bank_idtransfer'][$key];
            $supirIdTransfer[] = $prosesUangJalanSupirHeader->supir_id;
            $nominalTransfer[] = $value['nominal'];
            $keteranganTransfer[] = $data['keterangantransfer'][$key];

            $pengeluaranTruckingHeader = [
                'tglbukti' => date('Y-m-d', strtotime($data['tgltransfer'][$key])),
                'pengeluarantrucking_id' => $fetchFormatBLS->id,
                'bank_id' => $bankid,
                'coa' => $fetchFormatBLS->coapostingdebet,
                'pengeluaran_nobukti' => '',
                'postingdari' => 'ENTRY PROSES UANG JALAN',
                'supir_id' => $supirIdTransfer,
                'nominal' => $nominalTransfer,
                'keterangan' => $keteranganTransfer
            ];

            $newPengeluaranTrucking = new PengeluaranTruckingHeader();
            $newPengeluaranTrucking = $newPengeluaranTrucking->findAll($getPengeluaranTrucking->id);
            $pengeluaranTrucking = (new PengeluaranTruckingHeader())->processUpdate($newPengeluaranTrucking, $pengeluaranTruckingHeader);

            $editProsesDetailTransfer = ProsesUangJalanSupirDetail::find($value['idtransfer']);
            $editProsesDetailTransfer->keterangan = $data['keterangantransfer'][$key];
            $editProsesDetailTransfer->update();

            $detailLog[] = $editProsesDetailTransfer->toArray();
        }


        // UPDATE ADJUST 
        $dataAbsensiSupir = AbsensiSupirHeader::from(DB::raw("absensisupirheader with (readuncommitted)"))->where('nobukti', $prosesUangJalanSupirHeader['absensisupir_nobukti'])->first();

        $detailAdjust = $detail->adjustTransfer($id);
        $bankAdjust = Bank::from(DB::raw("bank with (readuncommitted)"))
            ->select('bank.coa')->whereRaw("bank.id = $detailAdjust->bank_idadjust")
            ->first();
        $coaKasMasuk = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('memo')->where('grp', 'JURNAL PENGEMBALIAN KAS GANTUNG')->where('subgrp', 'KREDIT')->first();
        $memoAdjust = json_decode($coaKasMasuk->memo, true);
        $penerimaanAdjust = $detailAdjust->penerimaan_nobukti;

        $nominalAdjust[] = $detailAdjust['nilaiadjust'];
        $keteranganAdjust[] = $data['keteranganadjust'];
        $kasgantungNobukti[] = $dataAbsensiSupir->kasgantung_nobukti;

        $pengembalianKasgantung = [
            'tglbukti' => $detailAdjust->tgladjust,
            'tgldari' => $detailAdjust->tgladjust,
            'tglsampai' => $detailAdjust->tgladjust,
            'bank_id' => $detailAdjust->bank_idadjust,
            'postingdari' => 'EDIT PROSES UANG JALAN SUPIR',
            'penerimaanheader_nobukti' => $penerimaanAdjust,
            'nominal' => $nominalAdjust,
            'keterangandetail' => $keteranganAdjust,
            'kasgantung_nobukti' => $kasgantungNobukti,
            'kasgantungdetail_id' => $kasgantungNobukti
        ];
        $getPengembalianKasgantung = PengembalianKasGantungHeader::from(DB::raw("pengembaliankasgantungheader with (readuncommitted)"))->where("penerimaan_nobukti", $penerimaanAdjust)->first();
        $newPengembalianKasgantung = new PengembalianKasGantungHeader();
        $newPengembalianKasgantung = $newPengembalianKasgantung->findAll($getPengembalianKasgantung->id);
        $pengembalianKasgantung = (new PengembalianKasGantungHeader())->processUpdate($newPengembalianKasgantung, $pengembalianKasgantung);

        $editProsesDetailAdjust = ProsesUangJalanSupirDetail::find($detailAdjust->idadjust);
        $editProsesDetailAdjust->keterangan = $data['keteranganadjust'];
        $editProsesDetailAdjust->update();

        $detailLog[] = $editProsesDetailAdjust->toArray();


        // UPDATE DEPOSITO
        $detailDeposito = $detail->deposito($id);
        if ($detailDeposito != null) {
            $fetchFormatDPO = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))
                ->where('kodepenerimaan', 'DPO')
                ->first();
            $getPenerimaanTruckingDPO = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where("penerimaan_nobukti", $detailDeposito->penerimaandeposit_nobukti)->first();

            $supirIdDeposito[] = $prosesUangJalanSupirHeader->supir_id;
            $nominalDeposito[] = $detailDeposito->nilaideposit;
            $keteranganDeposito[] = $data['keterangandeposit'];

            $penerimaanTruckingHeaderDPO = [
                'tglbukti' => date('Y-m-d', strtotime($detailDeposito->tgldeposit)),
                'penerimaantrucking_id' => $fetchFormatDPO->id,
                'bank_id' => $detailDeposito->bank_iddeposit,
                'coa' => $fetchFormatDPO->coapostingkredit,
                'postingdari' => 'EDIT PROSES UANG JALAN SUPIR',
                'supir_id' => $supirIdDeposito,
                'nominal' => $nominalDeposito,
                'keterangan' => $keteranganDeposito
            ];

            $newPenerimaanTruckingDPO = new PenerimaanTruckingHeader();
            $newPenerimaanTruckingDPO = $newPenerimaanTruckingDPO->findAll($getPenerimaanTruckingDPO->id);
            (new PenerimaanTruckingHeader())->processUpdate($newPenerimaanTruckingDPO, $penerimaanTruckingHeaderDPO);

            $editProsesDetailDeposit = ProsesUangJalanSupirDetail::find($detailDeposito->iddeposit);
            $editProsesDetailDeposit->keterangan = $data['keterangandeposit'];
            $editProsesDetailDeposit->update();

            $detailLog[] = $editProsesDetailDeposit->toArray();
        }

        (new LogTrail())->processStore([
            'namatabel' => 'PROSESUANGJALANSUPIRDETAIL',
            'postingdari' =>  'EDIT PROSES UANG JALAN SUPIR DETAIL',
            'idtrans' =>  $prosesUangJalanSupirHeaderLogTrail->id,
            'nobuktitrans' => $prosesUangJalanSupirHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $detailLog,
            'modifiedby' => auth('api')->user()->user,
        ]);


        return $prosesUangJalanSupirHeader;
    }


    public function processDestroy($id, $postingDari = ''): ProsesUangJalanSupirHeader
    {
        $getDetail = ProsesUangJalanSupirDetail::lockForUpdate()->where('prosesuangjalansupir_id', $id)->get();


        $prosesUangJalanSupirHeader = new ProsesUangJalanSupirHeader();
        $prosesUangJalanSupirHeader = $prosesUangJalanSupirHeader->lockAndDestroy($id);

        $prosesUangJalanSupirHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $prosesUangJalanSupirHeader->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $prosesUangJalanSupirHeader->id,
            'nobuktitrans' => $prosesUangJalanSupirHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $prosesUangJalanSupirHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'PROSESUANGJALANSUPIRDETAIL',
            'postingdari' => $postingDari,
            'idtrans' => $prosesUangJalanSupirHeaderLogTrail['id'],
            'nobuktitrans' => $prosesUangJalanSupirHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $getDetail->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        $transfer = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'TRANSFER')->first();
        $adjust = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'ADJUST TRANSFER')->first();
        $pengembalian = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'PENGEMBALIAN PINJAMAN')->first();
        $deposito = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'DEPOSITO SUPIR')->first();
        foreach ($getDetail as $key) {

            if ($key->statusprosesuangjalan == $transfer->id) {

                $getPengeluaranTrucking = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))->where('pengeluaran_nobukti', $key->pengeluarantrucking_nobukti)->first();
                if ($getPengeluaranTrucking != null) {
                    (new PengeluaranTruckingHeader())->processDestroy($getPengeluaranTrucking->id, $postingDari);
                }
            } else if ($key->statusprosesuangjalan == $adjust->id) {
                if ($key->pengembaliankasgantung_nobukti != '') {

                    $getPengembalianKasgantung = PengembalianKasGantungHeader::from(DB::raw("pengembaliankasgantungheader with (readuncommitted)"))->where('nobukti', $key->pengembaliankasgantung_nobukti)->first();
                    if ($getPengembalianKasgantung != null) {
                        (new PengembalianKasGantungHeader())->processDestroy($getPengembalianKasgantung->id, $postingDari);
                    }
                }
            } else if ($key->statusprosesuangjalan == $pengembalian->id) {

                if ($key->penerimaantrucking_nobukti != '') {
                    $getPenerimaanTrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where('penerimaan_nobukti', $key->penerimaantrucking_nobukti)->first();
                    if ($getPenerimaanTrucking != null) {
                        (new PenerimaanTruckingHeader())->processDestroy($getPenerimaanTrucking->id, $postingDari);
                    }
                }
            } else if ($key->statusprosesuangjalan == $deposito->id) {
                if ($key->penerimaantrucking_nobukti != '') {
                    $getPenerimaanTrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where('penerimaan_nobukti', $key->penerimaantrucking_nobukti)->first();
                    if ($getPenerimaanTrucking != null) {
                        (new PenerimaanTruckingHeader())->processDestroy($getPenerimaanTrucking->id, $postingDari);
                    }
                }
            }
        }

        return $prosesUangJalanSupirHeader;
    }
}
