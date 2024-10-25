<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RekapPengeluaranHeader extends MyModel
{
    use HasFactory;

    protected $table = 'rekappengeluaranheader';

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
        $class = 'RekapPengeluaranHeaderController';
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
            $getNominal = DB::table("rekappengeluarandetail")->from(DB::raw("rekappengeluarandetail with (readuncommitted)"))
                ->select(DB::raw("rekappengeluaranheader.nobukti,SUM(rekappengeluarandetail.nominal) AS nominal"))
                ->join(DB::raw("rekappengeluaranheader with (readuncommitted)"), 'rekappengeluaranheader.id', 'rekappengeluarandetail.rekappengeluaran_id')
                ->groupBy("rekappengeluaranheader.nobukti");
            if (request()->tgldari && request()->tglsampai) {
                $getNominal->whereBetween('rekappengeluaranheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
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
                DB::raw('(case when (year(rekappengeluaranheader.tglapproval) <= 2000) then null else rekappengeluaranheader.tglapproval end ) as tglapproval'),
                "statuscetak.memo as  statuscetak",
                "statuscetak.text as  statuscetaktext",
                "$this->table.userbukacetak",
                DB::raw("(case when year(isnull($this->table.tglbukacetak,'1900/1/1'))=1900 then null else $this->table.tglbukacetak end) as tglbukacetak"),
                "$this->table.modifiedby",
                "$this->table.created_at",
                "$this->table.updated_at",

            )
                ->leftJoin('parameter as statusapproval', 'rekappengeluaranheader.statusapproval', 'statusapproval.id')
                ->leftJoin('parameter as statuscetak', 'rekappengeluaranheader.statuscetak', 'statuscetak.id')
                ->leftJoin(DB::raw("$tempNominal as nominal with (readuncommitted)"), 'rekappengeluaranheader.nobukti', 'nominal.nobukti')
                ->leftJoin('bank', 'rekappengeluaranheader.bank_id', 'bank.id');
            if (request()->tgldari) {
                $query->whereBetween('tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
            }
            if ($periode != '') {
                $periode = explode("-", $periode);
                $query->whereRaw("MONTH(rekappengeluaranheader.tglbukti) ='" . $periode[0] . "'")
                    ->whereRaw("year(rekappengeluaranheader.tglbukti) ='" . $periode[1] . "'");
            }
            if ($statusCetak != '') {
                $query->where("rekappengeluaranheader.statuscetak", $statusCetak);
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


        $hutangBayar = DB::table('rekappengeluaranheader')
            ->from(
                DB::raw("rekappengeluaranheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->join(DB::raw("rekappengeluarandetail c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
            ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'c.pengeluaran_nobukti', 'b.nobukti')
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
                                // } else if ($filters['field'] == 'tglbukti_pengeluaran') {
                                //     $query = $query->whereRaw("format(rekappengeluarandetail.tgltransaksi, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                // } else if ($filters['field'] == 'nobukti_pengeluaran') {
                                //     $query = $query->where('rekappengeluarandetail.pengeluaran_nobukti', 'LIKE', "%$filters[data]%");
                                // } else if ($filters['field'] == 'keterangan_detail') {
                                //     $query = $query->where('rekappengeluarandetail.keterangan', 'LIKE', "%$filters[data]%");
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
                                    // } else if ($filters['field'] == 'tglbukti_pengeluaran') {
                                    //     $query = $query->orWhereRaw("format(rekappengeluarandetail.tgltransaksi, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                    // } else if ($filters['field'] == 'nobukti_pengeluaran') {
                                    //     $query = $query->orWhere('rekappengeluarandetail.pengeluaran_nobukti', 'LIKE', "%$filters[data]%");
                                    // } else if ($filters['field'] == 'keterangan_detail') {
                                    //     $query = $query->orWhere('rekappengeluarandetail.keterangan', 'LIKE', "%$filters[data]%");
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
        //     $query->where('rekappengeluaranheader.statuscetak', '<>', request()->cetak)
        //         ->whereYear('rekappengeluaranheader.tglbukti', '=', request()->year)
        //         ->whereMonth('rekappengeluaranheader.tglbukti', '=', request()->month);
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

        // if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
        //     request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
        //     request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
        // }


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
        $getNominal = DB::table("rekappengeluarandetail")->from(DB::raw("rekappengeluarandetail with (readuncommitted)"))
            ->select(DB::raw("rekappengeluaranheader.nobukti,SUM(rekappengeluarandetail.nominal) AS nominal"))
            ->join(DB::raw("rekappengeluaranheader with (readuncommitted)"), 'rekappengeluaranheader.id', 'rekappengeluarandetail.rekappengeluaran_id')
            ->groupBy("rekappengeluaranheader.nobukti");
        if (request()->tgldari && request()->tglsampai) {
            $getNominal->whereBetween('rekappengeluaranheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
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
            DB::raw('(case when (year(rekappengeluaranheader.tglapproval) <= 2000) then null else rekappengeluaranheader.tglapproval end ) as tglapproval'),
            "statuscetak.memo as  statuscetak",
            "statuscetak.text as  statuscetaktext",
            "$this->table.userbukacetak",
            DB::raw("(case when year(isnull($this->table.tglbukacetak,'1900/1/1'))=1900 then null else $this->table.tglbukacetak end) as tglbukacetak"),
            "$this->table.modifiedby",
            "$this->table.created_at",
            "$this->table.updated_at",

        )
            ->leftJoin('parameter as statusapproval', 'rekappengeluaranheader.statusapproval', 'statusapproval.id')
            ->leftJoin('parameter as statuscetak', 'rekappengeluaranheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("$tempNominal as nominal with (readuncommitted)"), 'rekappengeluaranheader.nobukti', 'nominal.nobukti')
            ->leftJoin('bank', 'rekappengeluaranheader.bank_id', 'bank.id');
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
    public function getRekapPengeluaranHeader($id)
    {
        $this->setRequestParameters();

        $query = DB::table('rekappengeluarandetail')->select(
            "rekappengeluarandetail.nobukti",
            "rekappengeluarandetail.pengeluaran_nobukti as nobukti_pengeluaran",
            "rekappengeluarandetail.keterangan as keterangan_detail",
            "rekappengeluarandetail.tgltransaksi as tglbukti_pengeluaran",
            "rekappengeluarandetail.nominal as nominal_detail"
        )
            ->where('rekappengeluaran_id', $id);
        $this->totalRows = $query->count();
        $this->totalNominal = $query->sum('nominal');
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        if ($this->params['sortIndex'] == 'id' || $this->params['sortIndex'] == 'nobukti_pengeluaran') {
            $query->orderBy('rekappengeluarandetail.pengeluaran_nobukti', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'tglbukti_pengeluaran') {
            $query->orderBy('rekappengeluarandetail.tgltransaksi', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'nominal_detail') {
            $query->orderBy('rekappengeluarandetail.nominal', $this->params['sortOrder']);
        } else {
            $query->orderBy('rekappengeluarandetail.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
        $this->filter($query);
        $this->paginate($query);
        $data = $query->get();

        return $data;
    }

    public function findAll($id)
    {
        $this->setRequestParameters();

        $query = DB::table('rekappengeluaranheader')
            ->select(
                'rekappengeluaranheader.id',
                'rekappengeluaranheader.nobukti',
                'rekappengeluaranheader.tglbukti',
                'rekappengeluaranheader.tgltransaksi',
                'rekappengeluaranheader.bank_id',
                'bank.namabank as bank',
                'bank.formatcetakan as formatcetakan',
            )
            ->leftJoin('bank', 'rekappengeluaranheader.bank_id', 'bank.id');

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
            "$this->table.keterangan",
            "$this->table.jumlahcetak",
            "bank.namabank as bank",
            "bank.formatcetakan as formatcetakan",
            DB::raw("'Bukti Rekap Pengeluaran ' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
        )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'rekappengeluaranheader.statuscetak', 'statuscetak.id')
            ->leftJoin('bank', 'rekappengeluaranheader.bank_id', 'bank.id');

        $data = $query->first();
        return $data;
    }

    public function processStore(array $data): RekapPengeluaranHeader
    {

        $group = 'REKAP PENGELUARAN BUKTI';
        $subGroup = 'REKAP PENGELUARAN BUKTI';

        $format = DB::table('parameter')
            ->where('grp', $group)
            ->where('subgrp', $subGroup)
            ->first();

        $rekapPengeluaranHeader = new RekapPengeluaranHeader();
        $statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        $statusCetak = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $rekapPengeluaranHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $rekapPengeluaranHeader->tgltransaksi  = date('Y-m-d', strtotime($data['tgltransaksi']));
        $rekapPengeluaranHeader->bank_id = $data['bank_id'];
        $rekapPengeluaranHeader->statusapproval = $statusNonApproval->id;
        $rekapPengeluaranHeader->statuscetak = $statusCetak->id;
        $rekapPengeluaranHeader->statusformat = $format->id;
        $rekapPengeluaranHeader->modifiedby = auth('api')->user()->name;
        $rekapPengeluaranHeader->info = html_entity_decode(request()->info);
        $rekapPengeluaranHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $rekapPengeluaranHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$rekapPengeluaranHeader->save()) {
            throw new \Exception("Error storing rekap pengeluaran header.");
        }

        $rekapPengeluaranHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($rekapPengeluaranHeader->getTable()),
            'postingdari' => 'ENTRY REKAP PENGELUARAN HEADER',
            'idtrans' => $rekapPengeluaranHeader->id,
            'nobuktitrans' => $rekapPengeluaranHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $rekapPengeluaranHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        $rekapPengeluaranDetails = [];

        for ($i = 0; $i < count($data['pengeluaran_nobukti']); $i++) {

            $rekapPengeluaranDetail = (new RekapPengeluaranDetail())->processStore($rekapPengeluaranHeader, [
                "tgltransaksi" => $data['tgltransaksi_detail'][$i],
                "pengeluaran_nobukti" => $data['pengeluaran_nobukti'][$i],
                "nominal" => $data['nominal'][$i],
                "keterangan" => $data['keterangan_detail'][$i],
            ]);

            $rekapPengeluaranDetails[] = $rekapPengeluaranDetail->toArray();
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($rekapPengeluaranDetail->getTable()),
            'postingdari' =>  'ENTRY REKAP PENGELUARAN DETAIL',
            'idtrans' =>  $rekapPengeluaranHeaderLogTrail->id,
            'nobuktitrans' => $rekapPengeluaranHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $rekapPengeluaranDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        return $rekapPengeluaranHeader;
    }

    public function processUpdate(RekapPengeluaranHeader $rekapPengeluaranHeader, array $data): RekapPengeluaranHeader
    {
        $rekapPengeluaranHeader->modifiedby = auth('api')->user()->name;
        $rekapPengeluaranHeader->info = html_entity_decode(request()->info);

        if (!$rekapPengeluaranHeader->save()) {
            throw new \Exception("Error updating rekap pengeluaran header.");
        }

        $rekapPengeluaranHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($rekapPengeluaranHeader->getTable()),
            'postingdari' => 'EDIT REKAP PENGELUARAN HEADER',
            'idtrans' => $rekapPengeluaranHeader->id,
            'nobuktitrans' => $rekapPengeluaranHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $rekapPengeluaranHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        RekapPengeluaranDetail::where('rekappengeluaran_id', $rekapPengeluaranHeader->id)->lockForUpdate()->delete();
        $rekapPengeluaranDetails = [];

        for ($i = 0; $i < count($data['pengeluaran_nobukti']); $i++) {

            $rekapPengeluaranDetail = (new RekapPengeluaranDetail())->processStore($rekapPengeluaranHeader, [
                "tgltransaksi" => $data['tgltransaksi_detail'][$i],
                "pengeluaran_nobukti" => $data['pengeluaran_nobukti'][$i],
                "nominal" => $data['nominal'][$i],
                "keterangan" => $data['keterangan_detail'][$i],
            ]);

            $rekapPengeluaranDetails[] = $rekapPengeluaranDetail->toArray();
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($rekapPengeluaranDetail->getTable()),
            'postingdari' =>  'EDIT REKAP PENGELUARAN DETAIL',
            'idtrans' =>  $rekapPengeluaranHeaderLogTrail->id,
            'nobuktitrans' => $rekapPengeluaranHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $rekapPengeluaranDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);


        return $rekapPengeluaranHeader;
    }

    public function processDestroy($id, $postingDari = ''): RekapPengeluaranHeader
    {
        $getDetail = RekapPengeluaranDetail::lockForUpdate()->where('rekappengeluaran_id', $id)->get();

        $rekapPengeluaranHeader = new RekapPengeluaranHeader();
        $rekapPengeluaranHeader = $rekapPengeluaranHeader->lockAndDestroy($id);

        $rekapPengeluaranHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $rekapPengeluaranHeader->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $rekapPengeluaranHeader->id,
            'nobuktitrans' => $rekapPengeluaranHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $rekapPengeluaranHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'REKAPPENGELUARANDETAIL',
            'postingdari' => $postingDari,
            'idtrans' => $rekapPengeluaranHeaderLogTrail['id'],
            'nobuktitrans' => $rekapPengeluaranHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $getDetail->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);
        return $rekapPengeluaranHeader;
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

            $rekapPengeluaranHeader = RekapPengeluaranHeader::find($data['rekapId'][$i]);
            if ($rekapPengeluaranHeader->statusapproval == $statusApproval->id) {
                $rekapPengeluaranHeader->statusapproval = $statusNonApproval->id;
                $rekapPengeluaranHeader->tglapproval = date('Y-m-d', strtotime("1900-01-01"));
                $rekapPengeluaranHeader->userapproval = '';
                $aksi = $statusNonApproval->text;
            } else {
                $rekapPengeluaranHeader->statusapproval = $statusApproval->id;
                $rekapPengeluaranHeader->tglapproval = date('Y-m-d H:i:s');
                $rekapPengeluaranHeader->userapproval = auth('api')->user()->name;
                $aksi = $statusApproval->text;
            }

            $rekapPengeluaranHeader->save();
            (new LogTrail())->processStore([
                'namatabel' => strtoupper($rekapPengeluaranHeader->getTable()),
                'postingdari' => 'APPROVAL REKAP PENGELUARAN',
                'idtrans' => $rekapPengeluaranHeader->id,
                'nobuktitrans' => $rekapPengeluaranHeader->nobukti,
                'aksi' => $aksi,
                'datajson' => $rekapPengeluaranHeader->toArray(),
                'modifiedby' => auth('api')->user()->user
            ]);
        }

        return $rekapPengeluaranHeader;
    }
}
