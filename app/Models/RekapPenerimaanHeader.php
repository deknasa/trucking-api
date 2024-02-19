<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RekapPenerimaanHeader extends MyModel
{
    use HasFactory;

    protected $table = 'rekappenerimaanheader';

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

        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';
        $query = DB::table($this->table);
        $query = $this->selectColumns($query)
            ->leftJoin('parameter as statusapproval', 'rekappenerimaanheader.statusapproval', 'statusapproval.id')
            ->leftJoin('parameter as statuscetak', 'rekappenerimaanheader.statuscetak', 'statuscetak.id')
            ->leftJoin('bank', 'rekappenerimaanheader.bank_id', 'bank.id');
        if (request()->tgldari) {
            $query->whereBetween('tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(rekappenerimaanheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(rekappenerimaanheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("rekappenerimaanheader.statuscetak", $statusCetak);
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function cekvalidasiaksi($nobukti)
    {
        $hutangBayar = DB::table('rekappenerimaanheader')
            ->from(
                DB::raw("rekappenerimaanheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->join(DB::raw("rekappenerimaandetail c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
            ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'c.penerimaan_nobukti', 'b.nobukti')
            ->where('a.nobukti', '=', $nobukti)
            ->first();

        if (isset($hutangBayar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Approval Jurnal',
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

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'grp') {
            return $query
                ->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder'])
                ->orderBy($this->table . '.subgrp', $this->params['sortOrder'])
                ->orderBy($this->table . '.id', $this->params['sortOrder']);
        }

        if ($this->params['sortIndex'] == 'subgrp') {
            return $query
                ->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder'])
                ->orderBy($this->table . '.grp', $this->params['sortOrder'])
                ->orderBy($this->table . '.id', $this->params['sortOrder']);
        }
        if ($this->params['sortIndex'] == 'bank') {
            return $query->orderBy('bank.namabank', $this->params['sortOrder']);
        }
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }
    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statusapproval') {
                                $query = $query->where('statusapproval.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'statuscetak') {
                                $query = $query->where('statuscetak.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'bank') {
                                $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak' || $filters['field'] == 'tgltransaksi') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti_penerimaan') {
                                $query = $query->whereRaw("format(rekappenerimaandetail.tgltransaksi, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'nobukti_penerimaan') {
                                $query = $query->where('rekappenerimaandetail.penerimaan_nobukti', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'keterangan_detail') {
                                $query = $query->where('rekappenerimaandetail.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal_detail') {
                                $query = $query->whereRaw("format(rekappenerimaandetail.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'statusapproval') {
                                    $query = $query->orWhere('statusapproval.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'statuscetak') {
                                    $query = $query->orWhere('statuscetak.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'bank') {
                                    $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak' || $filters['field'] == 'tgltransaksi') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglbukti_penerimaan') {
                                    $query = $query->orWhereRaw("format(rekappenerimaandetail.tgltransaksi, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'nobukti_penerimaan') {
                                    $query = $query->orWhere('rekappenerimaandetail.penerimaan_nobukti', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'keterangan_detail') {
                                    $query = $query->orWhere('rekappenerimaandetail.keterangan', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'nominal_detail') {
                                    $query = $query->orWhereRaw("format(rekappenerimaandetail.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                                } else {
                                    // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                                }
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
            $query->where('rekappenerimaanheader.statuscetak', '<>', request()->cetak)
                ->whereYear('rekappenerimaanheader.tglbukti', '=', request()->year)
                ->whereMonth('rekappenerimaanheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->string('bank', 100)->nullable();
            $table->date('tgltransaksi')->nullable();
            $table->string('statusapproval', 100)->nullable();
            $table->string('userapproval', 50)->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('statuscetak', 100)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
            request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
        }
        $query = DB::table($modelTable);
        $query = $query->select(
            "$this->table.id",
            "$this->table.nobukti",
            "$this->table.tglbukti",
            "bank.namabank as bank",
            "$this->table.tgltransaksi",
            "statusapproval.text as  statusapproval",
            "$this->table.userapproval",
            DB::raw("(case when year(isnull($this->table.tglapproval,'1900/1/1'))=1900 then null else $this->table.tglapproval end) as tglapproval"),
            "statuscetak.text as  statuscetak",
            "$this->table.userbukacetak",
            DB::raw("(case when year(isnull($this->table.tglbukacetak,'1900/1/1'))=1900 then null else $this->table.tglbukacetak end) as tglbukacetak"),
            "$this->table.modifiedby",
            "$this->table.created_at",
            "$this->table.updated_at",

        )
            ->leftJoin('parameter as statusapproval', 'rekappenerimaanheader.statusapproval', 'statusapproval.id')
            ->leftJoin('parameter as statuscetak', 'rekappenerimaanheader.statuscetak', 'statuscetak.id')
            ->leftJoin('bank', 'rekappenerimaanheader.bank_id', 'bank.id');;
        $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);

        $this->sort($query);
        $models = $this->filter($query);

        DB::table($temp)->insertUsing([
            "id",
            "nobukti",
            "tglbukti",
            "bank",
            "tgltransaksi",
            "statusapproval",
            "userapproval",
            "tglapproval",
            "statuscetak",
            "userbukacetak",
            "tglbukacetak",
            "modifiedby",
            "created_at",
            "updated_at",
        ], $models);

        return  $temp;
    }

    public function selectColumns($query)
    {
        return $query->select(
            "$this->table.id",
            "$this->table.nobukti",
            "$this->table.tglbukti",
            "$this->table.bank_id",
            "$this->table.tgltransaksi",
            "$this->table.userapproval",
            DB::raw("(case when year(isnull($this->table.tglapproval,'1900/1/1'))=1900 then null else $this->table.tglapproval end) as tglapproval"),
            "$this->table.userbukacetak",
            DB::raw("(case when year(isnull($this->table.tglbukacetak,'1900/1/1'))=1900 then null else $this->table.tglbukacetak end) as tglbukacetak"),
            "$this->table.statusformat",
            "$this->table.modifiedby",
            "$this->table.created_at",
            "$this->table.updated_at",
            "bank.namabank as bank",
            "statusapproval.memo as  statusapproval",
            "statuscetak.memo as  statuscetak",

        );
    }
    public function getRekapPenerimaanHeader($id)
    {
        $this->setRequestParameters();

        $query = DB::table('rekappenerimaandetail')->select(
            "rekappenerimaandetail.nobukti",
            "rekappenerimaandetail.penerimaan_nobukti as nobukti_penerimaan",
            "rekappenerimaandetail.keterangan as keterangan_detail",
            "rekappenerimaandetail.tgltransaksi as tglbukti_penerimaan",
            "rekappenerimaandetail.nominal as nominal_detail"
        )
            ->where('rekappenerimaan_id', $id);
        $this->totalRows = $query->count();
        $this->totalNominal = $query->sum('nominal');
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        if ($this->params['sortIndex'] == 'id' || $this->params['sortIndex'] == 'nobukti_penerimaan') {
            $query->orderBy('rekappenerimaandetail.penerimaan_nobukti', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'tglbukti_penerimaan') {
            $query->orderBy('rekappenerimaandetail.tgltransaksi', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'nominal_detail') {
            $query->orderBy('rekappenerimaandetail.nominal', $this->params['sortOrder']);
        } else {
            $query->orderBy('rekappenerimaandetail.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }

        $this->filter($query);
        $this->paginate($query);
        $data = $query->get();

        return $data;
    }

    public function findAll($id)
    {
        $this->setRequestParameters();

        $query = DB::table($this->table);
        $query = $this->selectColumns($query)
            ->leftJoin('parameter as statusapproval', 'rekappenerimaanheader.statusapproval', 'statusapproval.id')
            ->leftJoin('parameter as statuscetak', 'rekappenerimaanheader.statuscetak', 'statuscetak.id')
            ->leftJoin('bank', 'rekappenerimaanheader.bank_id', 'bank.id');

        $data = $query->where("$this->table.id", $id)->first();
        return $data;
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
        )->select(
            "$this->table.id",
            "$this->table.nobukti",
            "$this->table.tglbukti",
            "$this->table.bank_id",
            "$this->table.tgltransaksi",
            "$this->table.jumlahcetak",
            "bank.namabank as bank",
            'statuscetak.memo as statuscetak',
            'statuscetak.id as  statuscetak_id',
            DB::raw("'Bukti Rekap Penerimaan ' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
        )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'rekappenerimaanheader.statuscetak', 'statuscetak.id')
            ->leftJoin('bank', 'rekappenerimaanheader.bank_id', 'bank.id');
        $data = $query->first();
        return $data;
    }

    public function processStore(array $data): RekapPenerimaanHeader
    {

        $group = 'REKAP PENERIMAAN BUKTI';
        $subgroup = 'REKAP PENERIMAAN BUKTI';

        $format = DB::table('parameter')
            ->where('grp', $group)
            ->where('subgrp', $subgroup)
            ->first();

        $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        $statuscetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();


        $rekapPenerimaanHeader = new RekapPenerimaanHeader();

        $rekapPenerimaanHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $rekapPenerimaanHeader->tgltransaksi  = date('Y-m-d', strtotime($data['tgltransaksi']));
        $rekapPenerimaanHeader->bank_id = $data['bank_id'];
        $rekapPenerimaanHeader->statusapproval = $statusNonApproval->id;
        $rekapPenerimaanHeader->statuscetak = $statuscetak->id;
        $rekapPenerimaanHeader->statusformat = $format->id;
        $rekapPenerimaanHeader->modifiedby = auth('api')->user()->name;
        $rekapPenerimaanHeader->info = html_entity_decode(request()->info);

        $rekapPenerimaanHeader->nobukti = (new RunningNumberService)->get($group, $subgroup, $rekapPenerimaanHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));


        if (!$rekapPenerimaanHeader->save()) {
            throw new \Exception("Error storing rekap penerimaan header.");
        }


        $rekapPenerimaanHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($rekapPenerimaanHeader->getTable()),
            'postingdari' => 'ENTRY REKAP PENERIMAAN HEADER',
            'idtrans' => $rekapPenerimaanHeader->id,
            'nobuktitrans' => $rekapPenerimaanHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $rekapPenerimaanHeader->toArray(),
            'modifiedby' => $rekapPenerimaanHeader->modifiedby
        ]);

        $rekapPenerimaanDetails = [];
        if ($data['penerimaan_nobukti']) {
            for ($i = 0; $i < count($data['penerimaan_nobukti']); $i++) {

                $rekapPenerimaanDetail = (new RekapPenerimaanDetail())->processStore($rekapPenerimaanHeader, [
                    "tgltransaksi_detail" => $data['tgltransaksi_detail'][$i],
                    "penerimaan_nobukti" => $data['penerimaan_nobukti'][$i],
                    "nominal" => $data['nominal'][$i],
                    "keterangandetail" => $data['keterangan_detail'][$i],
                ]);
                $rekapPenerimaanDetails[] = $rekapPenerimaanDetail->toArray();
            }
        }

        (new LogTrail())->processStore([

            'namatabel' => strtoupper($rekapPenerimaanDetail->getTable()),
            'postingdari' => 'ENTRY REKAP PENERIMAAN DETAIL',
            'idtrans' =>  $rekapPenerimaanHeaderLogTrail->id,
            'nobuktitrans' => $rekapPenerimaanHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $rekapPenerimaanDetails,
            'modifiedby' => auth('api')->user()->name,

        ]);

        return $rekapPenerimaanHeader;
    }

    public function processUpdate(RekapPenerimaanHeader $rekapPenerimaanheader, array $data): RekapPenerimaanHeader
    {
        $rekapPenerimaanheader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $rekapPenerimaanheader->tgltransaksi  = date('Y-m-d', strtotime($data['tgltransaksi']));
        $rekapPenerimaanheader->bank_id = $data['bank_id'];
        $rekapPenerimaanheader->modifiedby = auth('api')->user()->name;
        $rekapPenerimaanheader->info = html_entity_decode(request()->info);

        if (!$rekapPenerimaanheader->save()) {
            throw new \Exception("Error update rekap penerimaan header.");
        }

        $rekapPenerimaanHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($rekapPenerimaanheader->getTable()),
            'postingdari' => 'EDIR REKAP PENERIMAAN HEADER',
            'idtrans' => $rekapPenerimaanheader->id,
            'nobuktitrans' => $rekapPenerimaanheader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $rekapPenerimaanheader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        if ($data['penerimaan_nobukti']) {
            $rekapPenerimaanDetail = RekapPenerimaanDetail::where('rekappenerimaan_id', $rekapPenerimaanheader->id)->lockForUpdate()->delete();

            $rekapPenerimaanDetails = [];
            for ($i = 0; $i < count($data['penerimaan_nobukti']); $i++) {

                $rekapPenerimaanDetail = (new RekapPenerimaanDetail())->processStore($rekapPenerimaanheader, [
                    "rekappenerimaan_id" => $rekapPenerimaanheader->id,
                    "nobukti" =>  $rekapPenerimaanheader->nobukti,
                    "tgltransaksi" => $data['tgltransaksi_detail'][$i],
                    "penerimaan_nobukti" => $data['penerimaan_nobukti'][$i],
                    "nominal" => $data['nominal'][$i],
                    "keterangandetail" => $data['keterangan_detail'][$i],
                    "modifiedby" => auth('api')->user()->name
                ]);
                $rekapPenerimaanDetails[] = $rekapPenerimaanDetail->toArray();
            }

            (new LogTrail())->processStore([
                'namatabel' => strtoupper($rekapPenerimaanDetail->getTable()),
                'postingdari' => 'EDIT REKAP PENERIMAAN DETAIL',
                'idtrans' =>  $rekapPenerimaanHeaderLogTrail->id,
                'nobuktitrans' => $rekapPenerimaanheader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $rekapPenerimaanDetails,
                'modifiedby' => auth('api')->user()->name,

            ]);

            return $rekapPenerimaanheader;
        }
    }

    public function processDestroy($id, $postingdari = ""): RekapPenerimaanHeader
    {
        $getDetail = RekapPenerimaanDetail::lockForUpdate()->where('rekappenerimaan_id', $id)->get();

        $rekapPenerimaanHeader = new RekapPenerimaanHeader();
        $rekapPenerimaanHeader = $rekapPenerimaanHeader->lockAndDestroy($id);

        $rekapPenerimaanLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($rekapPenerimaanHeader->getTable()),
            'postingdari' => $postingdari,
            'idtrans' => $id,
            'nobuktitrans' => $rekapPenerimaanHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $rekapPenerimaanHeader->toArray(),
            'modifiedby' => $rekapPenerimaanHeader->modifiedby
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'REKAPPENERIMAANDETAIL',
            'postingdari' => $postingdari,
            'idtrans' => $rekapPenerimaanLogTrail['id'],
            'nobuktitrans' => $rekapPenerimaanHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $getDetail->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);


        return $rekapPenerimaanHeader;
    }
    public function processApproval(array $data)
    {
        // dd($data);

        $statusApproval = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        for ($i = 0; $i < count($data['rekapId']); $i++) {

            $rekapPenerimaanHeader = RekapPenerimaanHeader::find($data['rekapId'][$i]);
            if ($rekapPenerimaanHeader->statusapproval == $statusApproval->id) {
                $rekapPenerimaanHeader->statusapproval = $statusNonApproval->id;
                $rekapPenerimaanHeader->tglapproval = date('Y-m-d', strtotime("1900-01-01"));
                $rekapPenerimaanHeader->userapproval = '';
                $aksi = $statusNonApproval->text;
            } else {
                $rekapPenerimaanHeader->statusapproval = $statusApproval->id;
                $rekapPenerimaanHeader->tglapproval = date('Y-m-d H:i:s');
                $rekapPenerimaanHeader->userapproval = auth('api')->user()->name;
                $aksi = $statusApproval->text;
            }

            $rekapPenerimaanHeader->save();
            (new LogTrail())->processStore([
                'namatabel' => strtoupper($rekapPenerimaanHeader->getTable()),
                'postingdari' => 'APPROVAL REKAP PENERIMAAN',
                'idtrans' => $rekapPenerimaanHeader->id,
                'nobuktitrans' => $rekapPenerimaanHeader->nobukti,
                'aksi' => $aksi,
                'datajson' => $rekapPenerimaanHeader->toArray(),
                'modifiedby' => auth('api')->user()->user
            ]);
        }

        return $rekapPenerimaanHeader;
    }
}
