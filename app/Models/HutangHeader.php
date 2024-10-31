<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\RunningNumberService;

class HutangHeader extends MyModel
{
    use HasFactory;

    protected $table = 'hutangheader';

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

        $tempbayar = '##tempbayar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempbayar, function ($table) {
            $table->string('hutang_nobukti', 100)->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $query = DB::table('pelunasanhutangdetail')->from(
            DB::raw("pelunasanhutangdetail as a with (readuncommitted)")
        )
            ->select(
                'a.hutang_nobukti',
                DB::raw("sum(a.nominal+a.potongan) as nominal")
            )
            ->groupby('hutang_nobukti');

        DB::table($tempbayar)->insertUsing([
            'hutang_nobukti',
            'nominal',
        ], $query);

        $tempTable = '##tempTable' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempTable, function ($table) {
            $table->string('nobukti')->nullable();
            $table->string('nobukti_asal')->nullable();
            $table->date('tgldariheaderspb')->nullable();
            $table->date('tglsampaiheaderspb')->nullable();
        });
        $getDataLain = DB::table("penerimaanstokheader")->from(DB::raw("penerimaanstokheader as a with (readuncommitted)"))
            ->select(DB::raw("b.nobukti, a.nobukti as nobukti_asal, cast((format(b.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderspb, cast(cast(format((cast((format(b.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderspb"))
            ->join(DB::raw("hutangheader as b with (readuncommitted)"), 'a.hutang_nobukti', 'b.nobukti');
        if (request()->tgldari && request()->tglsampai) {
            $getDataLain->whereBetween('b.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        DB::table($tempTable)->insertUsing(['nobukti', 'nobukti_asal', 'tgldariheaderspb', 'tglsampaiheaderspb'], $getDataLain);

        $this->setRequestParameters();
        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';
        $query = DB::table($this->table)->from(DB::raw("hutangheader with (readuncommitted)"))
            ->select(
                'hutangheader.id',
                'hutangheader.nobukti',
                'hutangheader.tglbukti',
                'hutangheader.postingdari',

                'akunpusat.keterangancoa as coa',
                'supplier.namasupplier as supplier_id',
                'hutangheader.total',
                DB::raw("isnull(c.nominal,0) as nominalbayar"),
                DB::raw("hutangheader.total-isnull(c.nominal,0) as sisahutang"),

                'parameter.memo as statuscetak',
                'statusapproval.memo as statusapproval',
                'hutangheader.userapproval',
                'hutangheader.userbukacetak',
                'hutangheader.jumlahcetak',
                DB::raw('(case when (year(hutangheader.tglbukacetak) <= 2000) then null else hutangheader.tglbukacetak end ) as tglbukacetak'),
                DB::raw('(case when (year(hutangheader.tglapproval) <= 2000) then null else hutangheader.tglapproval end ) as tglapproval'),

                'hutangheader.modifiedby',
                'hutangheader.created_at',
                'hutangheader.updated_at',
                DB::raw("isnull(d.nobukti_asal,'') as nobukti_spb"),
                'd.tgldariheaderspb',
                'd.tglsampaiheaderspb',
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'hutangheader.statuscetak', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'hutangheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'hutangheader.coa', 'akunpusat.coa')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'hutangheader.supplier_id', 'supplier.id')
            ->leftJoin(DB::raw($tempbayar . " as c"), 'hutangheader.nobukti', 'c.hutang_nobukti')
            ->leftJoin(DB::raw($tempTable . " as d"), 'hutangheader.nobukti', 'd.nobukti');
        if (request()->tgldari) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(hutangheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(hutangheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("hutangheader.statuscetak", $statusCetak);
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
        $query = DB::table('hutangheader')->from(
            DB::raw("hutangheader with (readuncommitted)")
        )
            ->select(
                'hutangheader.id',
                'hutangheader.nobukti',
                'hutangheader.tglbukti',
                'supplier.namasupplier as supplier',
                'supplier.id as supplier_id',
                'hutangheader.statuscetak',
                'hutangheader.statusapproval',
                'hutangheader.total',

                'hutangheader.modifiedby',
                'hutangheader.updated_at'
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'hutangheader.statuscetak', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'hutangheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'hutangheader.supplier_id', 'supplier.id')

            ->where('hutangheader.id', $id);

        $data = $query->first();
        return $data;
    }

    public function selectColumns($query)
    {
        $tempbayar = '##tempbayar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempbayar, function ($table) {
            $table->string('hutang_nobukti', 100)->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $tes = DB::table('pelunasanhutangdetail')->from(
            DB::raw("pelunasanhutangdetail as a with (readuncommitted)")
        )
            ->select(
                'a.hutang_nobukti',
                DB::raw("sum(a.nominal+a.potongan) as nominal")
            )
            ->groupby('hutang_nobukti');

        DB::table($tempbayar)->insertUsing([
            'hutang_nobukti',
            'nominal',
        ], $tes);
        
        $tempTable = '##tempTable' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempTable, function ($table) {
            $table->string('nobukti')->nullable();
            $table->string('nobukti_asal')->nullable();
        });
        $getDataLain = DB::table("penerimaanstokheader")->from(DB::raw("penerimaanstokheader as a with (readuncommitted)"))
            ->select(DB::raw("b.nobukti, a.nobukti as nobukti_asal"))
            ->join(DB::raw("hutangheader as b with (readuncommitted)"), 'a.hutang_nobukti', 'b.nobukti');
        if (request()->tgldari && request()->tglsampai) {
            $getDataLain->whereBetween('b.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        DB::table($tempTable)->insertUsing(['nobukti', 'nobukti_asal'], $getDataLain);

        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->table.id,
                 $this->table.nobukti,
                 $this->table.tglbukti,
                 akunpusat.keterangancoa as coa,
                 $this->table.postingdari,
                 'supplier.namasupplier as supplier_id',
                 $this->table.total,
                 isnull(c.nominal,0) as nominalbayar,
                hutangheader.total-isnull(c.nominal,0) as sisahutang,
                 'parameter.text as statuscetak',
                 'statusapproval.text as statusapproval',
                 $this->table.userapproval,
                 $this->table.tglapproval,
                 $this->table.userbukacetak,
                 $this->table.tglbukacetak,
                 $this->table.jumlahcetak,
                 $this->table.modifiedby,
                 $this->table.created_at,
                 $this->table.updated_at,
                 isnull(d.nobukti_asal,'') as nobukti_spb"
                )

            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'hutangheader.statuscetak', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'hutangheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'hutangheader.coa', 'akunpusat.coa')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'hutangheader.supplier_id', 'supplier.id')
            ->leftJoin(DB::raw($tempbayar . " as c"), 'hutangheader.nobukti', 'c.hutang_nobukti')
            ->leftJoin(DB::raw($tempTable . " as d"), 'hutangheader.nobukti', 'd.nobukti');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->string('coa', 255)->nullable();
            $table->string('postingdari', 255)->nullable();
            $table->string('supplier_id', 50)->nullable();
            $table->double('total', 15, 2)->nullable();
            $table->double('nominalbayar', 15, 2)->nullable();
            $table->double('sisahutang', 15, 2)->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('statusapproval', 1000)->nullable();
            $table->string('userapproval', 50)->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->string('nobukti_spb', 50)->nullable();
            $table->increments('position');
        });
        // if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
        //     request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
        //     request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
        // }
        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        $models = $query
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);

        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'coa', 'postingdari', 'supplier_id', 'total', 'nominalbayar', 'sisahutang', 'statuscetak', 'statusapproval', 'userapproval', 'tglapproval', 'userbukacetak', 'tglbukacetak', 'jumlahcetak', 'modifiedby', 'created_at', 'updated_at','nobukti_spb'], $models);

        return $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'nominalbayar') {
            return $query->orderBy('c.nominal', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'sisahutang') {
            return $query->orderBy(DB::raw("(hutangheader.total - isnull(c.nominal,0))"), $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'coa') {
            return $query->orderBy('akunpusat.keterangancoa', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'supplier_id') {
            return $query->orderBy('supplier.namasupplier', $this->params['sortOrder']);
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
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statuscetak') {
                                $query->where('parameter.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusapproval') {
                                $query->where('statusapproval.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'supplier_id') {
                                $query->where('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nobukti_spb') {
                                $query->where('d.nobukti_asal', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coa') {
                                $query->where('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'total') {
                                $query = $query->whereRaw("format(hutangheader.total, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'nominalbayar') {
                                $query = $query->whereRaw("format(c.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'sisahutang') {
                                $query = $query->whereRaw("format((hutangheader.total - isnull(c.nominal,0)), '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'statuscetak') {
                                    $query->orWhere('parameter.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statusapproval') {
                                    $query->orWhere('statusapproval.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'supplier_id') {
                                    $query->orWhere('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'nobukti_spb') {
                                    $query->orWhere('d.nobukti_asal', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'coa') {
                                    $query->orWhere('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'total') {
                                    $query = $query->orWhereRaw("format(hutangheader.total, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'nominalbayar') {
                                    $query = $query->orWhereRaw("format(c.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'sisahutang') {
                                    $query = $query->orWhereRaw("format((hutangheader.total - isnull(c.nominal,0)), '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else {
                                    // $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
            $query->where('hutangheader.statuscetak', '<>', request()->cetak)
                ->whereYear('hutangheader.tglbukti', '=', request()->year)
                ->whereMonth('hutangheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function cekvalidasiaksi($nobukti)
    {
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        $pelunasanHutang = DB::table('pelunasanhutangdetail')
            ->from(
                DB::raw("pelunasanhutangdetail as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.hutang_nobukti'
            )
            ->where('a.hutang_nobukti', '=', $nobukti)
            ->first();
        $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
        if (isset($pelunasanHutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti Pembayaran hutang <b>' . $pelunasanHutang->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Pembayaran Hutang '. $pelunasanHutang->nobukti,
                'kodeerror' => 'SATL2'
            ];
            goto selesai;
        }

        $penerimaanStok = DB::table('penerimaanstokheader')
            ->from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.hutang_nobukti'
            )
            ->where('a.hutang_nobukti', '=', $nobukti)
            ->first();
        $keteranganerror = $error->cekKeteranganError('TDT') ?? '';

        if (isset($penerimaanStok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti Penerimaan Stok <b>' . $penerimaanStok->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Penerimaan Stok '. $penerimaanStok->nobukti,
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }

        $hutangExtra = DB::table('hutangextraheader')
            ->from(
                DB::raw("hutangextraheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.hutang_nobukti'
            )
            ->where('a.hutang_nobukti', '=', $nobukti)
            ->first();
        $keteranganerror = $error->cekKeteranganError('TDT') ?? '';

        if (isset($hutangExtra)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti Hutang Extra <b>' . $hutangExtra->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Hutang Extra '. $hutangExtra->nobukti,
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }

        $jurnalpusat = DB::table('jurnalumumpusatheader')
            ->from(
                DB::raw("jurnalumumpusatheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        $keteranganerror = $error->cekKeteranganError('SAPP') ?? '';
        if (isset($jurnalpusat)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $jurnalpusat->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Approval Jurnal '. $jurnalpusat->nobukti,
                'kodeerror' => 'SAP'
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

    public function getHutang($id)
    {
        $this->setRequestParameters();

        $temp = $this->createTempHutang($id);

        // $approval = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
        //     ->where('grp', 'STATUS APPROVAL')
        //     ->where('subgrp', 'STATUS APPROVAL')
        //     ->where('text', 'APPROVAL')
        //     ->first();

        // $approvalId = $approval->id;

        $query = DB::table('hutangheader')->from(DB::raw("hutangheader with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By hutangheader.id) as id,hutangheader.nobukti as nobukti,hutangheader.tglbukti as tglhutang, hutangheader.total as nominal," . $temp . ".sisa, 0 as total"))
            ->join(DB::raw("$temp with (readuncommitted)"), 'hutangheader.nobukti', $temp . ".nobukti")
            ->whereRaw("hutangheader.nobukti = $temp.nobukti")
            // ->whereRaw("hutangheader.statusapproval = $approvalId")
            ->where(function ($query) use ($temp) {
                $query->whereRaw("$temp.sisa != 0")
                    ->orWhereRaw("$temp.sisa is null");
            });
        $data = $query->get();

        return $data;
    }

    public function createTempHutang($id)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = DB::table('hutangheader')->from(
            DB::raw("hutangheader with (readuncommitted)")
        )
            ->select(DB::raw("hutangheader.nobukti,sum(pelunasanhutangdetail.nominal) as terbayar, (SELECT (hutangheader.total - coalesce(SUM(pelunasanhutangdetail.nominal),0) - coalesce(SUM(pelunasanhutangdetail.potongan),0)) FROM pelunasanhutangdetail WHERE pelunasanhutangdetail.hutang_nobukti= hutangheader.nobukti) AS sisa"))
            ->leftJoin(DB::raw("pelunasanhutangdetail with (readuncommitted)"), 'pelunasanhutangdetail.hutang_nobukti', 'hutangheader.nobukti')
            ->whereRaw("hutangheader.supplier_id = $id")
            ->groupBy('hutangheader.nobukti', 'hutangheader.total');
        // ->get();
        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->bigInteger('terbayar')->nullable();
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'terbayar', 'sisa'], $fetch);

        return $temp;
    }

    public function hutangdetail()
    {
        return $this->hasMany(HutangDetail::class, 'hutang_id');
    }

    public function processStore(array $data): HutangHeader
    {
        // dd($data);

        /*STORE HEADER*/
        $group = 'HUTANG BUKTI';
        $subGroup = 'HUTANG BUKTI';

        $format = DB::table('parameter')->where('grp', $group)->where('subgrp', $subGroup)->first();

        $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL HUTANG MANUAL')->where('subgrp', 'DEBET')->first();
        $memo = json_decode($getCoaDebet->memo, true);
        $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL HUTANG MANUAL')->where('subgrp', 'KREDIT')->first();
        $memoKredit = json_decode($getCoaKredit->memo, true);
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $proseslain = $data['proseslain'] ?? "";
        if ($proseslain == "") {
            $total = array_sum($data['total_detail']);
            $tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
            $coa = $memo['JURNAL'];
            $coakredit = $memoKredit['JURNAL'];
        } else {
            $total = $data['total'];
            $tglbukti = $data['tglbukti'];
            $coa = $data['coa'];
            $coakredit = ($data['coakredit'] == null) ? $memoKredit['JURNAL'] : $data['coakredit'];
        }
        $hutangHeader = new HutangHeader();

        $hutangHeader->tglbukti = $tglbukti;
        $hutangHeader->coa = $coa;
        $hutangHeader->coakredit = $coakredit;
        $hutangHeader->supplier_id = $data['supplier_id'];
        $hutangHeader->postingdari = $data['postingdari'] ?? 'ENTRY HUTANG';
        $hutangHeader->statusformat = $format->id;
        $hutangHeader->statuscetak = $statusCetak->id;
        $hutangHeader->statusapproval = $statusApproval->id;
        $hutangHeader->total = $total;
        $hutangHeader->modifiedby = auth('api')->user()->name;
        $hutangHeader->info = html_entity_decode(request()->info);
        $hutangHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $hutangHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));


        if (!$hutangHeader->save()) {
            throw new \Exception("Error storing Hutang header.");
        }

        $hutangHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($hutangHeader->getTable()),
            'postingdari' => strtoupper('ENTRY Hutang Header'),
            'idtrans' => $hutangHeader->id,
            'nobuktitrans' => $hutangHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $hutangHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        /* Store detail */

        $hutangDetails = [];
        $coakredit_detail = [];
        $coadebet_detail = [];
        $nominal_detail = [];
        $keterangan_detail = [];

        for ($i = 0; $i < count($data['total_detail']); $i++) {

            $hutangDetail = (new HutangDetail())->processStore($hutangHeader, [
                'hutang_id' => $hutangHeader->id,
                'nobukti' => $hutangHeader->nobukti,
                'tgljatuhtempo' => date('Y-m-d', strtotime($data['tgljatuhtempo'][$i])),
                'total' => $data['total_detail'][$i],
                'cicilan' => '',
                'totalbayar' => '',
                'keterangan' => $data['keterangan_detail'][$i],
                'modifiedby' => $hutangHeader->modifiedby,
            ]);
            $hutangDetails[] = $hutangDetail->toArray();
            $coakredit_detail[] = ($data['coakredit'] == null) ? $memoKredit['JURNAL'] : $data['coakredit'];
            $coadebet_detail[] = ($data['coadebet'] == null) ? $memo['JURNAL'] : $data['coadebet'];
            $nominal_detail[] = $data['total_detail'][$i];
            $keterangan_detail[] = $data['keterangan_detail'][$i];
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($hutangDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY hutang Header'),
            'idtrans' =>  $hutangHeaderLogTrail->id,
            'nobuktitrans' => $hutangHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $hutangDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        /*STORE JURNAL*/
        $jurnalRequest = [
            'tanpaprosesnobukti' => 1,
            'nobukti' => $hutangHeader->nobukti,
            'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
            'postingdari' =>  $data['postingdari'] ?? 'ENTRY HUTANG',
            'statusapproval' => $statusApproval->id,
            'userapproval' => "",
            'tglapproval' => "",
            'modifiedby' => auth('api')->user()->name,
            'statusformat' => "0",
            'coakredit_detail' => $coakredit_detail,
            'coadebet_detail' => $coadebet_detail,
            'nominal_detail' => $nominal_detail,
            'keterangan_detail' => $keterangan_detail
        ];

        $jurnalUmumHeader = (new JurnalUmumHeader())->processStore($jurnalRequest);

        return $hutangHeader;
    }

    public function processUpdate(HutangHeader $hutangHeader, array $data): HutangHeader
    {
        $nobuktiOld = $hutangHeader->nobukti;
        $getTgl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'EDIT TANGGAL BUKTI')->where('subgrp', 'HUTANG')->first();
        /*STORE HEADER*/
        $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL HUTANG MANUAL')->where('subgrp', 'DEBET')->first();
        $memo = json_decode($getCoaDebet->memo, true);
        $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL HUTANG MANUAL')->where('subgrp', 'KREDIT')->first();
        $memoKredit = json_decode($getCoaKredit->memo, true);
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $proseslain = $data['proseslain'] ?? "";
        if ($proseslain == "") {
            $total = array_sum($data['total_detail']);
            $coa = $memo['JURNAL'];
            $coakredit = $memoKredit['JURNAL'];
        } else {
            $total = $data['total'];
            $coa = $data['coa'];
            $coakredit = ($data['coakredit'] == null) ? $memoKredit['JURNAL'] : $data['coakredit'];
        }

        if (trim($getTgl->text) == 'YA') {
            $group = 'HUTANG BUKTI';
            $subGroup = 'HUTANG BUKTI';
            $querycek = DB::table('hutangheader')->from(
                DB::raw("hutangheader a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.id', $hutangHeader->id)
                ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
                ->first();

            if (isset($querycek)) {
                $nobukti = $querycek->nobukti;
            } else {
                $nobukti = (new RunningNumberService)->get($group, $subGroup, $hutangHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
            }

            $hutangHeader->nobukti = $nobukti;
            $hutangHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        }
        $hutangHeader->coa = $coa;
        $hutangHeader->coakredit = $coakredit;
        $hutangHeader->supplier_id = $data['supplier_id'];
        $hutangHeader->postingdari = $data['postingdari'] ?? 'EDIT HUTANG';
        $hutangHeader->statuscetak = $statusCetak->id;
        $hutangHeader->statusapproval = $statusApproval->id;
        $hutangHeader->total = $total;
        $hutangHeader->modifiedby = auth('api')->user()->name;
        $hutangHeader->info = html_entity_decode(request()->info);


        if (!$hutangHeader->save()) {
            throw new \Exception("Error storing Hutang header.");
        }

        $hutangHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($hutangHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('EDIT HUTANG HEADER'),
            'idtrans' => $hutangHeader->id,
            'nobuktitrans' => $hutangHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $hutangHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        /*DELETE EXISTING HUTANG*/
        $hutangDetail = HutangDetail::where('hutang_id', $hutangHeader->id)->lockForUpdate()->delete();

        /* Store detail */

        $hutangDetails = [];
        $coakredit_detail = [];
        $coadebet_detail = [];
        $nominal_detail = [];
        $keterangan_detail = [];

        for ($i = 0; $i < count($data['total_detail']); $i++) {

            $hutangDetail = (new HutangDetail())->processStore($hutangHeader, [
                'hutang_id' => $hutangHeader->id,
                'nobukti' => $hutangHeader->nobukti,
                'tgljatuhtempo' => date('Y-m-d', strtotime($data['tgljatuhtempo'][$i])),
                'total' => $data['total_detail'][$i],
                'cicilan' => '',
                'totalbayar' => '',
                'keterangan' => $data['keterangan_detail'][$i],
                'modifiedby' => $hutangHeader->modifiedby,
            ]);
            $hutangDetails[] = $hutangDetail->toArray();
            $coakredit_detail[] = ($data['coakredit'] == null) ? $memoKredit['JURNAL'] : $data['coakredit'];
            $coadebet_detail[] = ($data['coadebet'] == null) ? $memo['JURNAL'] : $data['coadebet'];
            $nominal_detail[] = $data['total_detail'][$i];
            $keterangan_detail[] = $data['keterangan_detail'][$i];
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($hutangDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('EDIT hutang Header'),
            'idtrans' =>  $hutangHeaderLogTrail->id,
            'nobuktitrans' => $hutangHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $hutangDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        /*STORE JURNAL*/
        $jurnalRequest = [
            'tanpaprosesnobukti' => 1,
            'nobukti' => $hutangHeader->nobukti,
            'tglbukti' => $hutangHeader->tglbukti,
            'postingdari' =>  $data['postingdari'] ?? "EDIT HUTANG HEADER",
            'statusformat' => "0",
            'coakredit_detail' => $coakredit_detail,
            'coadebet_detail' => $coadebet_detail,
            'nominal_detail' => $nominal_detail,
            'keterangan_detail' => $keterangan_detail
        ];

        $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $nobuktiOld)->first();
        $newJurnal = new JurnalUmumHeader();
        $newJurnal = $newJurnal->find($getJurnal->id);
        $jurnalumumHeader = (new JurnalUmumHeader())->processUpdate($newJurnal, $jurnalRequest);


        return $hutangHeader;
    }

    public function processDestroy($id, $postingDari = ''): HutangHeader
    {
        $hutangDetail = HutangDetail::where('hutang_id', '=', $id)->get();
        $dataDetail = $hutangDetail->toArray();

        /*DELETE EXISTING HUTANG*/

        $hutangHeader = new HutangHeader();
        $hutangHeader = $hutangHeader->lockAndDestroy($id);

        $hutangLogTrail = (new LogTrail())->processStore([
            'namatabel' => $hutangHeader->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $hutangHeader->id,
            'nobuktitrans' => $hutangHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $hutangHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);


        (new LogTrail())->processStore([
            'namatabel' => 'HUTANGDETAIL',
            'postingdari' => $postingDari,
            'idtrans' => $hutangLogTrail['id'],
            'nobuktitrans' => $hutangHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $dataDetail,
            'modifiedby' => auth('api')->user()->name
        ]);

        $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $hutangHeader->nobukti)->first();
        $jurnalumumHeader = (new JurnalUmumHeader())->processDestroy($getJurnal->id, $postingDari);
        return $hutangHeader;
    }

    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($this->table)->from(DB::raw("hutangheader with (readuncommitted)"))
            ->select(
                'hutangheader.id',
                'hutangheader.nobukti',
                'hutangheader.tglbukti',
                'supplier.namasupplier as supplier_id',
                'statuscetak.memo as statuscetak',
                'statuscetak.id as  statuscetak_id',
                'hutangheader.userbukacetak',
                'hutangheader.jumlahcetak',
                DB::raw("'Bukti Hutang' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'hutangheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'hutangheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'hutangheader.coa', 'akunpusat.coa')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'hutangheader.supplier_id', 'supplier.id');

        $data = $query->first();
        return $data;
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

        for ($i = 0; $i < count($data['hutangId']); $i++) {

            $hutangHeader = HutangHeader::find($data['hutangId'][$i]);
            if ($hutangHeader->statusapproval == $statusApproval->id) {
                $hutangHeader->statusapproval = $statusNonApproval->id;
                $hutangHeader->tglapproval = date('Y-m-d', strtotime("1900-01-01"));
                $hutangHeader->userapproval = '';
                $aksi = $statusNonApproval->text;
            } else {
                $hutangHeader->statusapproval = $statusApproval->id;
                $hutangHeader->tglapproval = date('Y-m-d H:i:s');
                $hutangHeader->userapproval = auth('api')->user()->name;
                $aksi = $statusApproval->text;
            }

            $hutangHeader->save();
            (new LogTrail())->processStore([
                'namatabel' => strtoupper($hutangHeader->getTable()),
                'postingdari' => 'APPROVAL HUTANG',
                'idtrans' => $hutangHeader->id,
                'nobuktitrans' => $hutangHeader->nobukti,
                'aksi' => $aksi,
                'datajson' => $hutangHeader->toArray(),
                'modifiedby' => auth('api')->user()->user
            ]);
        }

        return $hutangHeader;
    }
}
