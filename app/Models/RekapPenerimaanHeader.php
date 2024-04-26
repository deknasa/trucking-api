<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
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
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'RekapPenerimaanHeaderController';
        if ($proses == 'reload') {
            $temtabel = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel a with (readuncommitted)")
            )
                ->select(
                    'id',
                    'class',
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            if (isset($querydata)) {
                Schema::dropIfExists($querydata->namatabel);
                DB::table('listtemporarytabel')->where('id', $querydata->id)->delete();
            }

            DB::table('listtemporarytabel')->insert(
                [
                    'class' => $class,
                    'namatabel' => $temtabel,
                    'modifiedby' => $user,
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s'),
                ]
            );

            Schema::create($temtabel, function (Blueprint $table) {
                $table->integer('id')->nullable();
                $table->string('nobukti', 50)->nullable();
                $table->date('tglbukti')->nullable();
                $table->double('nominal', 15, 2)->nullable();
                $table->string('bank', 1000)->nullable();
                $table->date('tgltransaksi')->nullable();
                $table->longtext('statusapproval')->nullable();
                $table->longtext('statusapprovaltext')->nullable();
                $table->longtext('userapproval')->nullable();
                $table->date('tglapproval')->nullable();
                $table->longtext('statuscetak')->nullable();
                $table->string('statuscetaktext', 200)->nullable();
                $table->string('userbukacetak', 200)->nullable();
                $table->date('tglbukacetak')->nullable();
                $table->string('modifiedby', 200)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
            });
            $tempNominal = '##tempNominal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempNominal, function ($table) {
                $table->string('nobukti')->nullable();
                $table->double('nominal', 15, 2)->nullable();
            });
            $getNominal = DB::table("rekappenerimaandetail")->from(DB::raw("rekappenerimaandetail with (readuncommitted)"))
                ->select(DB::raw("rekappenerimaanheader.nobukti,SUM(rekappenerimaandetail.nominal) AS nominal"))
                ->join(DB::raw("rekappenerimaanheader with (readuncommitted)"), 'rekappenerimaanheader.id', 'rekappenerimaandetail.rekappenerimaan_id')
                ->groupBy("rekappenerimaanheader.nobukti");
            if (request()->tgldari && request()->tglsampai) {
                $getNominal->whereBetween('rekappenerimaanheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
            }
            DB::table($tempNominal)->insertUsing(['nobukti', 'nominal'], $getNominal);

            $query = DB::table($this->table)->select(
                "$this->table.id",
                "$this->table.nobukti",
                "$this->table.tglbukti",
                'nominal.nominal',
                "bank.namabank as bank",
                "$this->table.tgltransaksi",
                "statusapproval.memo as  statusapproval",
                "statusapproval.text as  statusapprovaltext",
                "$this->table.userapproval",
                DB::raw("(case when year(isnull($this->table.tglapproval,'1900/1/1'))=1900 then null else $this->table.tglapproval end) as tglapproval"),
                "statuscetak.memo as  statuscetak",
                "statuscetak.text as  statuscetaktext",
                "$this->table.userbukacetak",
                DB::raw("(case when year(isnull($this->table.tglbukacetak,'1900/1/1'))=1900 then null else $this->table.tglbukacetak end) as tglbukacetak"),
                "$this->table.modifiedby",
                "$this->table.created_at",
                "$this->table.updated_at"
            )
                ->leftJoin('parameter as statusapproval', 'rekappenerimaanheader.statusapproval', 'statusapproval.id')
                ->leftJoin('parameter as statuscetak', 'rekappenerimaanheader.statuscetak', 'statuscetak.id')
                ->leftJoin(DB::raw("$tempNominal as nominal with (readuncommitted)"), 'rekappenerimaanheader.nobukti', 'nominal.nobukti')
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
            DB::table($temtabel)->insertUsing([
                'id',
                'nobukti',
                'tglbukti',
                'nominal',
                'bank',
                'tgltransaksi',
                'statusapproval',
                'statusapprovaltext',
                'userapproval',
                'tglapproval',
                'statuscetak',
                'statuscetaktext',
                'userbukacetak',
                'tglbukacetak',
                'modifiedby',
                'created_at',
                'updated_at',
            ], $query);
        } else {
            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel with (readuncommitted)")
            )
                ->select(
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            // dd($querydata);
            $temtabel = $querydata->namatabel;
        }

        $query = DB::table($temtabel)->from(DB::raw($temtabel . " a "))
            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                'a.nominal',
                'a.bank',
                'a.tgltransaksi',
                'a.statusapproval',
                'a.statusapprovaltext',
                'a.userapproval',
                'a.tglapproval',
                'a.statuscetak',
                'a.statuscetaktext',
                'a.userbukacetak',
                'a.tglbukacetak',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
            );


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
        $error = new Error();
        $keteranganerror = $error->cekKeteranganError('SBD') ?? '';
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';



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
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror,
                // 'keterangan' => 'Approval Jurnal',
                'kodeerror' => 'SBD'
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
        return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }
    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statusapproval') {
                                $query = $query->where('a.statusapprovaltext', '=', $filters['data']);
                            } else if ($filters['field'] == 'statuscetak') {
                                $query = $query->where('a.statuscetaktext', '=', $filters['data']);
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak' || $filters['field'] == 'tgltransaksi') {
                                $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                // } else if ($filters['field'] == 'tglbukti_penerimaan') {
                                //     $query = $query->whereRaw("format(rekappenerimaandetail.tgltransaksi, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                // } else if ($filters['field'] == 'nobukti_penerimaan') {
                                //     $query = $query->where('rekappenerimaandetail.penerimaan_nobukti', 'LIKE', "%$filters[data]%");
                                // } else if ($filters['field'] == 'keterangan_detail') {
                                //     $query = $query->where('rekappenerimaandetail.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->whereRaw("format(a.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'statusapproval') {
                                    $query = $query->orWhere('a.statusapprovaltext', '=', $filters['data']);
                                } else if ($filters['field'] == 'statuscetak') {
                                    $query = $query->orWhere('a.statuscetaktext', '=', $filters['data']);
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak' || $filters['field'] == 'tgltransaksi') {
                                    $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                    // } else if ($filters['field'] == 'tglbukti_penerimaan') {
                                    //     $query = $query->orWhereRaw("format(rekappenerimaandetail.tgltransaksi, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                    // } else if ($filters['field'] == 'nobukti_penerimaan') {
                                    //     $query = $query->orWhere('rekappenerimaandetail.penerimaan_nobukti', 'LIKE', "%$filters[data]%");
                                    // } else if ($filters['field'] == 'keterangan_detail') {
                                    //     $query = $query->orWhere('rekappenerimaandetail.keterangan', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'nominal') {
                                    $query = $query->orWhereRaw("format(a.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                                } else {
                                    // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
        // if (request()->cetak && request()->periode) {
        //     $query->where('rekappenerimaanheader.statuscetak', '<>', request()->cetak)
        //         ->whereYear('rekappenerimaanheader.tglbukti', '=', request()->year)
        //         ->whereMonth('rekappenerimaanheader.tglbukti', '=', request()->month);
        //     return $query;
        // }
        return $query;
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->integer('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->string('bank', 1000)->nullable();
            $table->date('tgltransaksi')->nullable();
            $table->longtext('statusapproval')->nullable();
            $table->longtext('statusapprovaltext')->nullable();
            $table->longtext('userapproval')->nullable();
            $table->date('tglapproval')->nullable();
            $table->longtext('statuscetak')->nullable();
            $table->string('statuscetaktext', 200)->nullable();
            $table->string('userbukacetak', 200)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->string('modifiedby', 200)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });
        if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
            request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
        }

        $this->setRequestParameters();
        $query = $this->selectColumns();

        $this->sort($query);
        $models = $this->filter($query);
        $models =  $query->whereBetween('a.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);

        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'nominal',
            'bank',
            'tgltransaksi',
            'statusapproval',
            'statusapprovaltext',
            'userapproval',
            'tglapproval',
            'statuscetak',
            'statuscetaktext',
            'userbukacetak',
            'tglbukacetak',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $models);

        return  $temp;
    }

    public function selectColumns()
    {
        $temp = '##tempselect' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->string('bank', 1000)->nullable();
            $table->date('tgltransaksi')->nullable();
            $table->longtext('statusapproval')->nullable();
            $table->longtext('statusapprovaltext')->nullable();
            $table->longtext('userapproval')->nullable();
            $table->date('tglapproval')->nullable();
            $table->longtext('statuscetak')->nullable();
            $table->string('statuscetaktext', 200)->nullable();
            $table->string('userbukacetak', 200)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->string('modifiedby', 200)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
        $tempNominal = '##tempNominal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempNominal, function ($table) {
            $table->string('nobukti')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });
        $getNominal = DB::table("rekappenerimaandetail")->from(DB::raw("rekappenerimaandetail with (readuncommitted)"))
            ->select(DB::raw("rekappenerimaanheader.nobukti,SUM(rekappenerimaandetail.nominal) AS nominal"))
            ->join(DB::raw("rekappenerimaanheader with (readuncommitted)"), 'rekappenerimaanheader.id', 'rekappenerimaandetail.rekappenerimaan_id')
            ->groupBy("rekappenerimaanheader.nobukti");
        if (request()->tgldari && request()->tglsampai) {
            $getNominal->whereBetween('rekappenerimaanheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        DB::table($tempNominal)->insertUsing(['nobukti', 'nominal'], $getNominal);
        $query = DB::table($this->table)->select(
            "$this->table.id",
            "$this->table.nobukti",
            "$this->table.tglbukti",
            'nominal.nominal',
            "bank.namabank as bank",
            "$this->table.tgltransaksi",
            "statusapproval.memo as  statusapproval",
            "statusapproval.text as  statusapprovaltext",
            "$this->table.userapproval",
            DB::raw("(case when year(isnull($this->table.tglapproval,'1900/1/1'))=1900 then null else $this->table.tglapproval end) as tglapproval"),
            "statuscetak.memo as  statuscetak",
            "statuscetak.text as  statuscetaktext",
            "$this->table.userbukacetak",
            DB::raw("(case when year(isnull($this->table.tglbukacetak,'1900/1/1'))=1900 then null else $this->table.tglbukacetak end) as tglbukacetak"),
            "$this->table.modifiedby",
            "$this->table.created_at",
            "$this->table.updated_at"
        )
            ->leftJoin('parameter as statusapproval', 'rekappenerimaanheader.statusapproval', 'statusapproval.id')
            ->leftJoin('parameter as statuscetak', 'rekappenerimaanheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("$tempNominal as nominal with (readuncommitted)"), 'rekappenerimaanheader.nobukti', 'nominal.nobukti')
            ->leftJoin('bank', 'rekappenerimaanheader.bank_id', 'bank.id');
        if (request()->tgldariheader) {
            $query->whereBetween('tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        }
        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'nominal',
            'bank',
            'tgltransaksi',
            'statusapproval',
            'statusapprovaltext',
            'userapproval',
            'tglapproval',
            'statuscetak',
            'statuscetaktext',
            'userbukacetak',
            'tglbukacetak',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $query);

        $query = DB::table($temp)->from(DB::raw($temp . " a "))
            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                'a.nominal',
                'a.bank',
                'a.tgltransaksi',
                'a.statusapproval',
                'a.statusapprovaltext',
                'a.userapproval',
                'a.tglapproval',
                'a.statuscetak',
                'a.statuscetaktext',
                'a.userbukacetak',
                'a.tglbukacetak',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
            );
        return $query;
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

        $query = DB::table('rekappenerimaanheader')
            ->select(
                'rekappenerimaanheader.id',
                'rekappenerimaanheader.nobukti',
                'rekappenerimaanheader.tglbukti',
                'rekappenerimaanheader.tgltransaksi',
                'rekappenerimaanheader.bank_id',
                'bank.namabank as bank',
            )
            ->leftJoin('bank', 'rekappenerimaanheader.bank_id', 'bank.id');

        $data = $query->where("rekappenerimaanheader.id", $id)->first();
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
            DB::raw("'Laporan Rekap Penerimaan ' as judulLaporan"),
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
