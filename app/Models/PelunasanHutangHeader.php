<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\RunningNumberService;
use Psy\CodeCleaner\FunctionReturnInWriteContextPass;

class PelunasanHutangHeader extends MyModel
{

    use HasFactory;
    protected $table = 'PelunasanHutangheader';

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

        $temppelunasan = '##temppelunasan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppelunasan, function ($table) {
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->double('potongan', 15, 2)->nullable();
            $table->double('total', 15, 2)->nullable();
        });

        $query = DB::table('pelunasanhutangdetail')->from(
            DB::raw("pelunasanhutangdetail as a with (readuncommitted)")
        )
            ->select(
                'a.nobukti',
                DB::raw("sum(a.nominal) as nominal"),
                DB::raw("sum(a.potongan) as potongan"),
                DB::raw("sum(a.nominal+a.potongan) as total")
            )
            ->groupby('nobukti');

        DB::table($temppelunasan)->insertUsing([
            'nobukti',
            'nominal',
            'potongan',
            'total',
        ], $query);

        // dd(DB::table($temppelunasan)->get());
        
        $this->setRequestParameters();
        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';

        $query = DB::table($this->table)->from(DB::raw("PelunasanHutangheader with (readuncommitted)"))
            ->select(
                'PelunasanHutangheader.id',
                'PelunasanHutangheader.nobukti',
                'PelunasanHutangheader.tglbukti',
                'PelunasanHutangheader.pengeluaran_nobukti',
                'akunpusat.keterangancoa as coa',
                'PelunasanHutangheader.userapproval',
                'statusapproval.memo as statusapproval',
                DB::raw('(case when (year(PelunasanHutangheader.tglapproval) <= 2000) then null else PelunasanHutangheader.tglapproval end ) as tglapproval'),
                DB::raw('(case when (year(PelunasanHutangheader.tglbukacetak) <= 2000) then null else PelunasanHutangheader.tglbukacetak end ) as tglbukacetak'),
                'statuscetak.memo as statuscetak',
                'c.nominal as nominal',
                'c.potongan as potongan',
                'c.total as total',
                'PelunasanHutangheader.userbukacetak',
                'PelunasanHutangheader.nowarkat',
                'PelunasanHutangheader.jumlahcetak',
                'PelunasanHutangheader.modifiedby',
                'PelunasanHutangheader.created_at',
                'PelunasanHutangheader.updated_at',

                'bank.namabank as bank_id',
                'supplier.namasupplier as supplier_id',
                'alatbayar.namaalatbayar as alatbayar_id',
                'PelunasanHutangheader.tglcair',                
                'PelunasanHutangheader.bank_id as pengeluaranbank_id',
                db::raw("cast((format(pengeluaranheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpengeluaranheader"),
                db::raw("cast(cast(format((cast((format(pengeluaranheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpengeluaranheader"),

            )
            ->leftJoin(DB::raw("pengeluaranheader with (readuncommitted)"), 'PelunasanHutangheader.pengeluaran_nobukti', '=', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'PelunasanHutangheader.coa', 'akunpusat.coa')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'PelunasanHutangheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'PelunasanHutangheader.supplier_id', 'supplier.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'PelunasanHutangheader.alatbayar_id', 'alatbayar.id')
            ->leftJoin(DB::raw($temppelunasan . " as c"), 'pelunasanHutangheader.nobukti', 'c.nobukti')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'PelunasanHutangheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'PelunasanHutangheader.statusapproval', 'statusapproval.id');
        if (request()->tgldari) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(PelunasanHutangheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(PelunasanHutangheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("PelunasanHutangheader.statuscetak", $statusCetak);
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

        $query = DB::table('PelunasanHutangheader')->from(DB::raw("PelunasanHutangheader with (readuncommitted)"))
            ->select(
                'PelunasanHutangheader.id',
                'PelunasanHutangheader.nobukti',
                'PelunasanHutangheader.tglbukti',
                'PelunasanHutangheader.modifiedby',
                'PelunasanHutangheader.updated_at',
                'PelunasanHutangheader.bank_id',
                'bank.namabank as bank',
                'PelunasanHutangheader.statuscetak',
                'PelunasanHutangheader.supplier_id',
                'supplier.namasupplier as supplier',
                'PelunasanHutangheader.pengeluaran_nobukti',
                'PelunasanHutangheader.alatbayar_id',
                'PelunasanHutangheader.nowarkat',
                'PelunasanHutangheader.statusapproval',
                'alatbayar.namaalatbayar as alatbayar',
                'PelunasanHutangheader.tglcair'
            )
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'PelunasanHutangheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'PelunasanHutangheader.supplier_id', 'supplier.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'PelunasanHutangheader.alatbayar_id', 'alatbayar.id')
            ->where('PelunasanHutangheader.id', $id);


        $data = $query->first();

        return $data;
    }

    public function PelunasanHutangdetail()
    {
        return $this->hasMany(PelunasanHutangDetail::class, 'PelunasanHutang_id');
    }

    public function selectColumns($query)
    { //sesuaikan dengan createtemp
        $temppelunasan = '##temppelunasanSel' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppelunasan, function ($table) {
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->double('potongan', 15, 2)->nullable();
            $table->double('total', 15, 2)->nullable();
        });

        $querytemppelunasan = DB::table('pelunasanhutangdetail')->from(
            DB::raw("pelunasanhutangdetail as a with (readuncommitted)")
        )
            ->select(
                'a.nobukti',
                DB::raw("sum(a.nominal) as nominal"),
                DB::raw("sum(a.potongan) as potongan"),
                DB::raw("sum(a.nominal+a.potongan) as total")
            )
            ->groupby('a.nobukti');

        DB::table($temppelunasan)->insertUsing([
            'nobukti',
            'nominal',
            'potongan',
            'total',
        ], $querytemppelunasan);

        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->table.id,
                $this->table.nobukti,
                $this->table.tglbukti,
                $this->table.pengeluaran_nobukti,
                'akunpusat.keterangancoa as coa',
                $this->table.userapproval,
                'statusapproval.text as statusapproval',
                $this->table.tglapproval,
                $this->table.tglbukacetak,
                'statuscetak.memo as statuscetak',
                'c.nominal as nominal',
                'c.potongan as potongan',
                'c.total as total',
                $this->table.userbukacetak,
                $this->table.jumlahcetak,
                $this->table.nowarkat,
                'bank.namabank as bank_id',
                'supplier.namasupplier as supplier_id',
                'alatbayar.namaalatbayar as alatbayar_id',
                $this->table.tglcair,
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at"
                )

            )
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'PelunasanHutangheader.coa', 'akunpusat.coa')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'PelunasanHutangheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'PelunasanHutangheader.supplier_id', 'supplier.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'PelunasanHutangheader.alatbayar_id', 'alatbayar.id')
            ->leftJoin(DB::raw($temppelunasan . " as c"), 'PelunasanHutangheader.nobukti', 'c.nobukti')
            ->join(DB::raw("parameter as statuscetak with (readuncommitted)"), 'PelunasanHutangheader.statuscetak', 'statuscetak.id')
            ->join(DB::raw("parameter as statusapproval with (readuncommitted)"), 'PelunasanHutangheader.statusapproval', 'statusapproval.id');
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('pengeluaran_nobukti')->nullable();
            $table->longText('coa')->nullable();
            $table->string('userapproval', 50)->nullable();
            $table->string('statusapproval', 1000)->nullable();
            $table->date('tglapproval')->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('nominal',100)->nullable();
            $table->string('potongan',100)->nullable();
            $table->string('total',100)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->string('nowarkat')->nullable();
            $table->string('bank_id')->nullable();
            $table->string('supplier_id')->nullable();
            $table->string('alatbayar_id')->nullable();
            $table->date('tglcair')->nullable();
            $table->string('modifiedby', 50)->nullable();
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
        DB::table($temp)->insertUsing(['id','nobukti','tglbukti','pengeluaran_nobukti','coa','userapproval','statusapproval','tglapproval','tglbukacetak','statuscetak','nominal','potongan','total','userbukacetak','jumlahcetak','nowarkat','bank_id','supplier_id','alatbayar_id','tglcair','modifiedby','created_at','updated_at'], $models);


        return  $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'bank_id') {
            return $query->orderBy('bank.namabank', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'alatbayar_id') {
            return $query->orderBy('alatbayar.namaalatbayar', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'supplier_id') {
            return $query->orderBy('supplier.namasupplier', $this->params['sortOrder']);
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
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statusapproval') {
                                $query = $query->where('statusapproval.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'statuscetak') {
                                $query = $query->where('statuscetak.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'supplier_id') {
                                $query = $query->where('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'bank_id') {
                                $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'alatbayar_id') {
                                $query = $query->where('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->whereRaw("format(c.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'potongan') {
                                $query = $query->whereRaw("format(c.potongan, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglcair' || $filters['field'] == 'tglbukacetak') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at' || $filters['field'] == 'tglapproval') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
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
                                } else if ($filters['field'] == 'supplier_id') {
                                    $query = $query->orWhere('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'bank_id') {
                                    $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'alatbayar_id') {
                                    $query = $query->orWhere('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'nominal') {
                                    $query = $query->orWhereRaw("format(c.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'potongan') {
                                    $query = $query->orWhereRaw("format(c.potongan, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgljatuhtempo' || $filters['field'] == 'tglbukacetak') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at' || $filters['field'] == 'tglapproval') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
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
            $query->where('PelunasanHutangheader.statuscetak', '<>', request()->cetak)
                ->whereYear('PelunasanHutangheader.tglbukti', '=', request()->year)
                ->whereMonth('PelunasanHutangheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }


    public function cekvalidasiaksi($nobukti, $pengeluaran)
    {
        $error = new Error();
        $keteranganerror = $error->cekKeteranganError('SAPP') ?? '';
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $PelunasanHutang = DB::table('PelunasanHutangheader')
            ->from(
                DB::raw("PelunasanHutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.pengeluaran_nobukti',
                'a.nobukti'
            )
            ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.pengeluaran_nobukti', 'b.nobukti')
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($PelunasanHutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' =>  'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti Approval Jurnal <b>' . $PelunasanHutang->pengeluaran_nobukti . '</b> <br> ' . $keterangantambahanerror,
                'kodeerror' => 'SAPP'
            ];
            goto selesai;
        }


        $cekPencairan = DB::table("pencairangiropengeluaranheader")
            ->from(DB::raw("pencairangiropengeluaranheader with (readuncommitted)"))
            ->where('pengeluaran_nobukti', $pengeluaran)
            ->first();
        if (isset($cekPencairan)) {
            $keteranganerror = $error->cekKeteranganError('SCG') ?? '';
            $data = [
                'kondisi' => true,
                'keterangan' =>  'No Bukti <b>' . $pengeluaran . '</b><br>' . $keteranganerror . '<br> No Bukti pencairan giro <b>' . $cekPencairan->nobukti . '</b> <br> ' . $keterangantambahanerror,
                'kodeerror' => 'SCG'
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


    public function getPembayaran($id, $supplierId)
    {
        $this->setRequestParameters();

        $tempHutang = $this->createTempHutang($supplierId);
        $tempPembayaran = $this->createTempPembayaran($id, $supplierId);


        // $hutang = DB::table("$tempHutang as A")->from(DB::raw("$tempHutang as A with (readuncommitted)"))
        //     ->select(DB::raw("A.id as id,null as PelunasanHutang_id,A.nobukti as hutang_nobukti, A.tglbukti as tglbukti, null as bayar, null as keterangan, null as potongan, A.nominalhutang, A.sisa as sisa"))
        //     // ->distinct("A.nobukti")
        //     ->leftJoin(DB::raw("$tempPembayaran as B with (readuncommitted)"), "A.nobukti", "B.hutang_nobukti")
        //     ->whereRaw("isnull(b.hutang_nobukti,'') = ''")
        //     ->whereRaw("a.sisa > 0");


        // $pembayaran = DB::table($tempPembayaran)->from(DB::raw("$tempPembayaran with (readuncommitted)"))
        //     ->select(DB::raw("id,PelunasanHutang_id,hutang_nobukti,tglbukti,bayar,keterangan,potongan,nominalhutang,sisa"))
        //     ->unionAll($hutang);

        $temp = '##tempGet' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $pembayaran = DB::table($tempPembayaran)->from(DB::raw("$tempPembayaran with (readuncommitted)"))
            ->select(DB::raw("PelunasanHutang_id,hutang_nobukti,tglbukti,bayar,keterangan,potongan,nominalhutang,sisa"));

        Schema::create($temp, function ($table) {
            $table->bigInteger('PelunasanHutang_id')->nullable();
            $table->string('hutang_nobukti');
            $table->date('tglbukti')->nullable();
            $table->bigInteger('bayar')->nullable();
            $table->longText('keterangan')->nullable();
            $table->bigInteger('potongan')->nullable();
            $table->bigInteger('nominalhutang');
            $table->bigInteger('sisa')->nullable();
        });

        DB::table($temp)->insertUsing(['PelunasanHutang_id', 'hutang_nobukti', 'tglbukti', 'bayar', 'keterangan',  'potongan', 'nominalhutang', 'sisa'], $pembayaran);

        $hutang = DB::table("$tempHutang as A")->from(DB::raw("$tempHutang as A with (readuncommitted)"))
            ->select(DB::raw("null as PelunasanHutang_id,A.nobukti as hutang_nobukti, A.tglbukti as tglbukti, null as bayar, null as keterangan, null as potongan, A.nominalhutang, A.sisa as sisa"))
            // ->distinct("A.nobukti")
            ->leftJoin(DB::raw("$tempPembayaran as B with (readuncommitted)"), "A.nobukti", "B.hutang_nobukti")
            ->whereRaw("isnull(b.hutang_nobukti,'') = ''")
            ->whereRaw("a.sisa > 0");
        DB::table($temp)->insertUsing(['PelunasanHutang_id', 'hutang_nobukti', 'tglbukti', 'bayar', 'keterangan',  'potongan', 'nominalhutang', 'sisa'], $hutang);

        $data = DB::table($temp)->from(DB::raw("$temp with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $temp.hutang_nobukti) as id,PelunasanHutang_id,hutang_nobukti as nobukti,tglbukti as tglhutang,keterangan,nominalhutang as nominal,
            (case when bayar IS NULL then 0 else bayar end) as bayar,
            (case when potongan IS NULL then 0 else potongan end) as potongan,
            (case when sisa IS NULL then 0 else sisa end) as sisa,
            (case when bayar IS NULL then 0 else (bayar + coalesce(potongan,0)) end) as total"))
            ->get();

        return $data;
    }


    public function createTempHutang($supplierId)
    {
        $temp = '##tempHutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        // $approval = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
        //     ->where('grp', 'STATUS APPROVAL')
        //     ->where('subgrp', 'STATUS APPROVAL')
        //     ->where('text', 'APPROVAL')
        //     ->first();

        // $approvalId = $approval->id;
        $fetch = DB::table('hutangheader')->from(DB::raw("hutangheader with (readuncommitted)"))
            ->select(DB::raw("hutangheader.nobukti,hutangheader.tglbukti,hutangheader.supplier_id,hutangheader.total as nominalhutang, (SELECT (hutangheader.total - COALESCE(SUM(PelunasanHutangdetail.nominal),0) - COALESCE(SUM(PelunasanHutangdetail.potongan),0)) FROM PelunasanHutangdetail WHERE PelunasanHutangdetail.hutang_nobukti= hutangheader.nobukti) AS sisa"))
            ->leftJoin(DB::raw("PelunasanHutangdetail with (readuncommitted)"), 'hutangheader.nobukti', 'PelunasanHutangdetail.hutang_nobukti')
            ->whereRaw("hutangheader.supplier_id = $supplierId")
            // ->whereRaw("hutangheader.statusapproval = $approvalId")
            ->groupBy('hutangheader.id', 'hutangheader.nobukti', 'hutangheader.supplier_id', 'hutangheader.total', 'hutangheader.tglbukti');

        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->date('tglbukti')->nullable();
            $table->bigInteger('supplier_id')->nullable();
            $table->bigInteger('nominalhutang');
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'tglbukti', 'supplier_id', 'nominalhutang', 'sisa'], $fetch);

        return $temp;
    }

    public function createTempPembayaran($id, $supplierId)
    {
        $tempo = '##tempPembayaran' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = DB::table('PelunasanHutangdetail as hbd')->from(DB::raw("PelunasanHutangdetail as hbd with (readuncommitted)"))
            ->select(DB::raw("hbd.PelunasanHutang_id,hbd.hutang_nobukti,hutangheader.tglbukti,hbd.nominal as bayar, hbd.keterangan,hbd.potongan,hutangheader.total as nominalhutang, (SELECT (hutangheader.total - SUM(isnull(PelunasanHutangdetail.nominal,0)) - SUM(isnull(PelunasanHutangdetail.potongan,0))) FROM PelunasanHutangdetail WHERE PelunasanHutangdetail.hutang_nobukti= hutangheader.nobukti) AS sisa"))
            ->join(DB::raw("hutangheader with (readuncommitted)"), 'hbd.hutang_nobukti', 'hutangheader.nobukti')
            ->whereRaw("hbd.PelunasanHutang_id = $id");

        Schema::create($tempo, function ($table) {
            $table->bigInteger('PelunasanHutang_id')->nullable();
            $table->string('hutang_nobukti');
            $table->date('tglbukti')->nullable();
            $table->bigInteger('bayar')->nullable();
            $table->longText('keterangan')->nullable();
            $table->bigInteger('potongan')->nullable();
            $table->bigInteger('nominalhutang');
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($tempo)->insertUsing(['PelunasanHutang_id', 'hutang_nobukti', 'tglbukti', 'bayar', 'keterangan',  'potongan', 'nominalhutang', 'sisa'], $fetch);

        return $tempo;
    }

    public function processStore(array $data): PelunasanHutangHeader
    {
        $bankid = $data['bank_id'];
        $group = 'PEMBAYARAN HUTANG BUKTI';
        $subGroup = 'PEMBAYARAN HUTANG BUKTI';
        /*STORE HEADER*/

        $format = DB::table('parameter')->where('grp', $group)->where('subgrp', $subGroup)->first();

        $statusbayarhutang = db::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
            ->select('id')->where('grp', 'PELUNASANHUTANG')->where('subgrp', 'PELUNASANHUTANG')->where('text', 'BANK/KAS')
            ->first()->id ?? 0;

        $bayarhutang = $data['statusbayarhutang'] ?? $statusbayarhutang;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMBAYARAN HUTANG')->where('subgrp', 'DEBET')->first();
        $memo = json_decode($getCoaDebet->memo, true);
        $PelunasanHutangHeader = new PelunasanHutangHeader();

        $PelunasanHutangHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $PelunasanHutangHeader->bank_id = $data['bank_id'] ?? 0;
        $PelunasanHutangHeader->supplier_id = $data['supplier_id'] ?? '';
        $PelunasanHutangHeader->coa = $memo['JURNAL'];
        $PelunasanHutangHeader->pengeluaran_nobukti = '';
        $PelunasanHutangHeader->statusapproval = $statusApproval->id ?? $data['statusapproval'];
        $PelunasanHutangHeader->statusbayarhutang = $bayarhutang;
        $PelunasanHutangHeader->userapproval = '';
        $PelunasanHutangHeader->tglapproval = '';
        $PelunasanHutangHeader->alatbayar_id = $data['alatbayar_id'];
        $PelunasanHutangHeader->nowarkat = $data['nowarkat'];
        $PelunasanHutangHeader->tglcair = date('Y-m-d', strtotime($data['tglcair']));
        $PelunasanHutangHeader->statuscetak = $statusCetak->id;
        $PelunasanHutangHeader->statusformat = $format->id;
        $PelunasanHutangHeader->modifiedby = auth('api')->user()->name;
        $PelunasanHutangHeader->info = html_entity_decode(request()->info);
        $PelunasanHutangHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $PelunasanHutangHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$PelunasanHutangHeader->save()) {
            throw new \Exception("Error storing pembayaran Hutang header.");
        }

        $PelunasanHutangHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($PelunasanHutangHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY PELUNASAN HUTANG Header'),
            'idtrans' => $PelunasanHutangHeader->id,
            'nobuktitrans' => $PelunasanHutangHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $PelunasanHutangHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);


        $coaDebet = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMBAYARAN HUTANG')->where('subgrp', 'DEBET')->first();
        $coaDebetpembelian = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMBAYARAN HUTANG PEMBELIAN STOK')->where('subgrp', 'DEBET')->first();
        $memo = json_decode($coaDebet->memo, true);
        $memopembelian = json_decode($coaDebetpembelian->memo, true);

        $query = HutangHeader::from(DB::raw("hutangheader a with (readuncommitted)"))
            ->select('a.nobukti')
            ->join(db::Raw("penerimaanstokheader b with (readuncommitted)"), 'a.nobukti', 'b.hutang_nobukti')
            ->first();

        if (isset($query)) {
            $coa = $memopembelian['JURNAL'];
        } else {
            $coa = $memo['JURNAL'];
        }
        $langsungcair = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS LANGSUNG CAIR')->where('text', 'TIDAK LANGSUNG CAIR')->first();
        $queryalatbayar = AlatBayar::from(db::raw("alatbayar a with (readuncommitted)"))->select('a.coa')->where('a.id', '=', $data['alatbayar_id'])->where('a.statuslangsungcair', '=', $langsungcair->id)->first();

        if ($PelunasanHutangHeader->bank_id == '') {
            $bankid = 0;
        } else {
            $bankid = $PelunasanHutangHeader->bank_id ?? 0;
        }
        $bank = Bank::from(DB::raw("bank with (readuncommitted)"))
            ->select('bank.coa')->whereRaw("bank.id = $bankid")
            ->first();
        if ($bank != null) {
            $coakredits = $bank->coa ?? '';
            if (isset($queryalatbayar)) {
                $coakredits =  $queryalatbayar->coa;
            }
        }
        $PelunasanHutangDetails = [];
        $nominal_detail = [];
        $keterangan_detail = [];
        $coadebet = [];
        $coakredit = [];
        $tgljatuhtempo = [];
        $nowarkat = [];

        $statusketerangan = DB::table('parameter')->from(
            db::raw("parameter a with (readuncommitted)")
        )
            ->select(
                'a.text'
            )
            ->where('grp', '=', 'KETERANGAN DEFAULT HUTANG USAHA')
            ->where('subgrp', '=', 'KETERANGAN DEFAULT HUTANG USAHA')
            ->first();

        $querysupplier = DB::table('PelunasanHutangheader')->from(
            db::raw("PelunasanHutangheader a with (readuncommitted)")
        )
            ->select(
                'b.namasupplier'
            )
            ->join(DB::raw("supplier b with (readuncommitted)"), 'a.supplier_id', 'b.id')
            ->where('a.id', '=', $PelunasanHutangHeader->id)
            ->first();

        $supplier = $querysupplier->namasupplier ?? '';

        $statusketerangan = $statusketerangan->text ?? '';
        $total = 0;
        for ($i = 0; $i < count($data['hutang_id']); $i++) {
            $hutang = HutangDetail::where('nobukti', $data['hutang_nobukti'][$i])->first();

            $PelunasanHutangDetail = (new PelunasanHutangDetail())->processStore($PelunasanHutangHeader, [
                'PelunasanHutang_id' => $PelunasanHutangHeader->id,
                'nobukti' => $PelunasanHutangHeader->nobukti,
                'hutang_nobukti' => $hutang->nobukti,
                'nominal' => $data['bayar'][$i],
                'cicilan' => '',
                'userid' => '',
                'potongan' => $data['potongan'][$i],
                'keterangan' => $data['keterangan'][$i],
                'modifiedby' => $PelunasanHutangHeader->modifiedby
            ]);
            $PelunasanHutangDetails[] = $PelunasanHutangDetail->toArray();
            $total = $total + ($data['bayar'][$i] - $data['potongan'][$i]);
            $keterangan_detail[] = $data['keterangan'][$i];
        }
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($PelunasanHutangDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY PELUNASAN HUTANG DETAIL'),
            'idtrans' =>  $PelunasanHutangHeaderLogTrail->id,
            'nobuktitrans' => $PelunasanHutangHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $PelunasanHutangDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        /*STORE PENGELUARAN*/
        // $supplier = Supplier::from(DB::raw("supplier with (readuncommitted)"))->where('id', $data['supplier_id'])->first();
        // if ($bayarhutang == $statusbayarhutang) {
        //     $coadebet[] = $data['coadebet'] ?? $coa;
        //     if ($bank != null) {
        //         $coakredit[] = $data['coakredit'] ?? $coakredits;
        //     }
        //     $tgljatuhtempo[] = $data['tglcair'];
        //     $nowarkat[] = "";
        //     $nominal_detail[] = $total;
        //     $statusketerangandefault[] = $statusketerangan . ' ' . $supplier->namasupplier . ' ' . $data['keterangan'][0];
        //     $pengeluaranRequest = [
        //         'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
        //         'pelanggan_id' => 0,
        //         'postingdari' => "ENTRY HUTANG BAYAR",
        //         'statusapproval' => $statusApproval->id,
        //         'dibayarke' => $supplier->namasupplier,
        //         'alatbayar_id' => $data['alatbayar_id'],
        //         'bank_id' => $data['bank_id'],
        //         'transferkeac' => $supplier->rekeningbank,
        //         'transferkean' => $supplier->namarekening,
        //         'transferkebank' => $supplier->bank,
        //         'userapproval' => "",
        //         'tglapproval' => "",

        //         'nowarkat' => $nowarkat,
        //         'tgljatuhtempo' => $tgljatuhtempo,
        //         "nominal_detail" => $nominal_detail,
        //         "coadebet" => $coadebet,
        //         "coakredit" => $coakredit,
        //         "keterangan_detail" => $statusketerangandefault,
        //         "bulanbeban"
        //     ];


        //     $pengeluaranHeader = (new PengeluaranHeader())->processStore($pengeluaranRequest);
        //     $PelunasanHutangHeader->pengeluaran_nobukti = $pengeluaranHeader->nobukti;
        // } else {
        $PelunasanHutangHeader->pengeluaran_nobukti = '';
        // }

        $PelunasanHutangHeader->save();
        return $PelunasanHutangHeader;
    }

    public function processUpdate(PelunasanHutangHeader $PelunasanHutangHeader, array $data): PelunasanHutangHeader
    {
        $bankid = $data['bank_id'] ?? 0;

        /*STORE HEADER*/
        $coaDebet = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMBAYARAN HUTANG')->where('subgrp', 'DEBET')->first();
        $coaDebetpembelian = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMBAYARAN HUTANG PEMBELIAN STOK')->where('subgrp', 'DEBET')->first();
        $memo = json_decode($coaDebet->memo, true);
        $memopembelian = json_decode($coaDebetpembelian->memo, true);

        $statusbayarhutang = db::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
            ->select('id')->where('grp', 'PELUNASANHUTANG')->where('subgrp', 'PELUNASANHUTANG')->where('text', 'BANK/KAS')
            ->first()->id ?? 0;

        $bayarhutang = $data['statusbayarhutang'] ?? $statusbayarhutang;

        $query = HutangHeader::from(DB::raw("hutangheader a with (readuncommitted)"))
            ->select('a.nobukti')
            ->join(db::Raw("penerimaanstokheader b with (readuncommitted)"), 'a.nobukti', 'b.hutang_nobukti')
            ->first();

        if (isset($query)) {
            $coa = $memopembelian['JURNAL'];
        } else {
            $coa = $memo['JURNAL'];
        }

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMBAYARAN HUTANG')->where('subgrp', 'DEBET')->first();
        $memo = json_decode($getCoaDebet->memo, true);

        $getTgl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'EDIT TANGGAL BUKTI')->where('subgrp', 'HUTANG BAYAR')->first();
        if (trim($getTgl->text) == 'YA') {
            $group = 'PEMBAYARAN HUTANG BUKTI';
            $subGroup = 'PEMBAYARAN HUTANG BUKTI';
            $querycek = DB::table('PelunasanHutangheader')->from(
                DB::raw("PelunasanHutangheader a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.id', $PelunasanHutangHeader->id)
                ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
                ->first();

            if (isset($querycek)) {
                $nobukti = $querycek->nobukti;
            } else {
                $nobukti = (new RunningNumberService)->get($group, $subGroup, $PelunasanHutangHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
            }

            $PelunasanHutangHeader->nobukti = $nobukti;
            $PelunasanHutangHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        }

        $PelunasanHutangHeader->tglcair = date('Y-m-d', strtotime($data['tglcair']));
        $PelunasanHutangHeader->nowarkat = $data['nowarkat'] ?? '';
        $PelunasanHutangHeader->supplier_id = $data['supplier_id'] ?? '';
        $PelunasanHutangHeader->bank_id = $data['bank_id'] ?? '';
        $PelunasanHutangHeader->alatbayar_id = $data['alatbayar_id'] ?? '';
        $PelunasanHutangHeader->modifiedby = auth('api')->user()->name;
        $PelunasanHutangHeader->info = html_entity_decode(request()->info);

        $PelunasanHutangHeader->coa = $memo['JURNAL'];
        $PelunasanHutangHeader->statusbayarhutang = $bayarhutang;

        if (!$PelunasanHutangHeader->save()) {
            throw new \Exception("Error Update pembayaran Hutang header.");
        }

        // $pengeluaranHeader = PengeluaranHeader::where('nobukti', $PelunasanHutangHeader->pengeluaran_nobukti)->lockForUpdate()->first();
        // /*DELETE EXISTING JURNAL*/
        // $JurnalUmumDetail = JurnalUmumDetail::where('nobukti', $pengeluaranHeader->nobukti)->lockForUpdate()->delete();
        // $JurnalUmumHeader = JurnalUmumHeader::where('nobukti', $pengeluaranHeader->nobukti)->lockForUpdate()->delete();
        // /*DELETE EXISTING Pengeluaran*/
        // $pengeluaranDetail = PengeluaranDetail::where('pengeluaran_id', $pengeluaranHeader->id)->lockForUpdate()->delete();
        // $pengeluaranHeader->delete();
        /*DELETE EXISTING hutang bayar*/
        PelunasanHutangDetail::where('PelunasanHutang_id', $PelunasanHutangHeader->id)->lockForUpdate()->delete();


        $coaDebet = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMBAYARAN HUTANG')->where('subgrp', 'DEBET')->first();
        $coaDebetpembelian = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMBAYARAN HUTANG PEMBELIAN STOK')->where('subgrp', 'DEBET')->first();
        $memo = json_decode($coaDebet->memo, true);
        $memopembelian = json_decode($coaDebetpembelian->memo, true);

        $query = HutangHeader::from(DB::raw("hutangheader a with (readuncommitted)"))
            ->select('a.nobukti')
            ->join(db::Raw("penerimaanstokheader b with (readuncommitted)"), 'a.nobukti', 'b.hutang_nobukti')
            ->first();

        if (isset($query)) {
            $coa = $memopembelian['JURNAL'];
        } else {
            $coa = $memo['JURNAL'];
        }
        $langsungcair = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS LANGSUNG CAIR')->where('text', 'TIDAK LANGSUNG CAIR')->first();
        $queryalatbayar = AlatBayar::from(db::raw("alatbayar a with (readuncommitted)"))->select('a.coa')->where('a.id', '=', $data['alatbayar_id'])->where('a.statuslangsungcair', '=', $langsungcair->id)->first();

        // if ($PelunasanHutangHeader->bank_id == '') {
        //     $bankid = 0;
        // } else {
        //     $bankid = $PelunasanHutangHeader->bank_id ?? 0;
        // }

        $bank = Bank::from(DB::raw("bank with (readuncommitted)"))
            ->select('bank.coa')->whereRaw("bank.id = '$PelunasanHutangHeader->bank_id'")
            ->first();

        $coakredits = $bank->coa ?? '';
        if (isset($queryalatbayar)) {
            $coakredits =  $queryalatbayar->coa;
        }

        $PelunasanHutangDetails = [];
        $nominal_detail = [];
        $keterangan_detail = [];
        $coadebet = [];
        $coakredit = [];
        $tgljatuhtempo = [];
        $nowarkat = [];

        $statusketerangan = DB::table('parameter')->from(
            db::raw("parameter a with (readuncommitted)")
        )
            ->select(
                'a.text'
            )
            ->where('grp', '=', 'KETERANGAN DEFAULT HUTANG USAHA')
            ->where('subgrp', '=', 'KETERANGAN DEFAULT HUTANG USAHA')
            ->first();

        $querysupplier = DB::table('PelunasanHutangheader')->from(
            db::raw("PelunasanHutangheader a with (readuncommitted)")
        )
            ->select(
                'b.namasupplier'
            )
            ->join(DB::raw("supplier b with (readuncommitted)"), 'a.supplier_id', 'b.id')
            ->where('a.id', '=', $PelunasanHutangHeader->id)
            ->first();

        $supplier = $querysupplier->namasupplier ?? '';

        $statusketerangan = $statusketerangan->text ?? '';
        $total = 0;
        for ($i = 0; $i < count($data['hutang_id']); $i++) {
            $hutang = HutangDetail::where('nobukti', $data['hutang_nobukti'][$i])->first();

            $PelunasanHutangDetail = (new PelunasanHutangDetail())->processStore($PelunasanHutangHeader, [
                'PelunasanHutang_id' => $PelunasanHutangHeader->id,
                'nobukti' => $PelunasanHutangHeader->nobukti,
                'hutang_nobukti' => $hutang->nobukti,
                'nominal' => $data['bayar'][$i],
                'cicilan' => '',
                'userid' => '',
                'potongan' => $data['potongan'][$i],
                'keterangan' => $data['keterangan'][$i],
                'modifiedby' => $PelunasanHutangHeader->modifiedby
            ]);

            $PelunasanHutangDetails[] = $PelunasanHutangDetail->toArray();
            $total = $total + ($data['bayar'][$i] - $data['potongan'][$i]);
            $keterangan_detail[] = $data['keterangan'][$i];
        }

        /*STORE PENGELUARAN*/
        $supplier = Supplier::from(DB::raw("supplier with (readuncommitted)"))->where('id', $data['supplier_id'])->first();

        if ($bayarhutang == $statusbayarhutang) {
            if ($bankid == 0) {
                // CHECK IF EXIST
                $getPengeluaran = DB::table("pengeluaranheader")->from(DB::raw("pengeluaranheader with (readuncommitted)"))->where('nobukti', $PelunasanHutangHeader->pengeluaran_nobukti)->first();
                if ($getPengeluaran != '') {
                    (new PengeluaranHeader())->processDestroy($getPengeluaran->id, 'UPDATE PELUNASAN HUTANG');
                    $PelunasanHutangHeader->pengeluaran_nobukti = '';
                }
            } else {
                $getPengeluaran = DB::table("pengeluaranheader")->from(DB::raw("pengeluaranheader with (readuncommitted)"))->where('nobukti', $PelunasanHutangHeader->pengeluaran_nobukti)->first();

                $coadebet[] = $coa;
                $coakredit[] = $coakredits;
                $tgljatuhtempo[] = $data['tglcair'];
                $nowarkat[] = $data['nowarkat'];
                $nominal_detail[] = $total;
                $statusketerangandefault[] = $statusketerangan . ' ' . $supplier->namasupplier . ' ' . $data['keterangan'][0];
                $pengeluaranRequest = [
                    'tglbukti' => $PelunasanHutangHeader->tglbukti,
                    'pelanggan_id' => 0,
                    'postingdari' => "EDIT PELUNASAN HUTANG",
                    'statusapproval' => $statusApproval->id,
                    'dibayarke' => $supplier->namasupplier,
                    'alatbayar_id' => $data['alatbayar_id'],
                    'bank_id' => $data['bank_id'],
                    'transferkeac' => $supplier->rekeningbank,
                    'transferkean' => $supplier->namarekening,
                    'transferkebank' => $supplier->bank,
                    'userapproval' => "",
                    'tglapproval' => "",
                    'from' => 'pelunasanhutang',

                    'nowarkat' => $nowarkat,
                    'tgljatuhtempo' => $tgljatuhtempo,
                    "nominal_detail" => $nominal_detail,
                    "coadebet" => $coadebet,
                    "coakredit" => $coakredit,
                    "keterangan_detail" => $statusketerangandefault,
                    "bulanbeban"
                ];
                if ($getPengeluaran != '') {
                    $pengeluaranHeader = PengeluaranHeader::where('nobukti', $PelunasanHutangHeader->pengeluaran_nobukti)->first();
                    $pengeluaranHeader = (new PengeluaranHeader())->processUpdate($pengeluaranHeader, $pengeluaranRequest);
                } else {
                    $pengeluaranHeader = (new PengeluaranHeader())->processStore($pengeluaranRequest);
                }
                $PelunasanHutangHeader->pengeluaran_nobukti = $pengeluaranHeader->nobukti;
            }
        } else {
            $PelunasanHutangHeader->pengeluaran_nobukti = '';
        }
        $PelunasanHutangHeader->save();


        $PelunasanHutangHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($PelunasanHutangHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY PELUNASAN HUTANG Header'),
            'idtrans' => $PelunasanHutangHeader->id,
            'nobuktitrans' => $PelunasanHutangHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $PelunasanHutangHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($PelunasanHutangDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY PELUNASAN HUTANG DETAIL'),
            'idtrans' =>  $PelunasanHutangHeaderLogTrail->id,
            'nobuktitrans' => $PelunasanHutangHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $PelunasanHutangDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);
        return $PelunasanHutangHeader;
    }


    public function processDestroy($id, $postingDari = ''): PelunasanHutangHeader
    {
        $PelunasanHutangHeader = PelunasanHutangHeader::findOrFail($id);
        $dataHeader =  $PelunasanHutangHeader->toArray();
        $pengeluaranDetail = PelunasanHutangDetail::where('PelunasanHutang_id', '=', $PelunasanHutangHeader->id)->get();
        $dataDetail = $pengeluaranDetail->toArray();

        $buktipengeluaran = $PelunasanHutangHeader->pengeluaran_nobukti ?? '';
        if ($buktipengeluaran != '') {
            $getPengeluaran = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))->where('nobukti', $PelunasanHutangHeader->pengeluaran_nobukti)->first();
            if ($getPengeluaran != null) {
                (new PengeluaranHeader())->processDestroy($getPengeluaran->id, $postingDari);
            }
        }

        /*DELETE EXISTING hutang bayar*/
        PelunasanHutangDetail::where('PelunasanHutang_id', $PelunasanHutangHeader->id)->lockForUpdate()->delete();

        $PelunasanHutangHeader = $PelunasanHutangHeader->lockAndDestroy($id);
        $hutangLogTrail = (new LogTrail())->processStore([
            'namatabel' => $this->table,
            'postingdari' => $postingDari,
            'idtrans' => $PelunasanHutangHeader->id,
            'nobuktitrans' => $PelunasanHutangHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $dataHeader,
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'PELUNASANHUTANGDETAIL',
            'postingdari' => $postingDari,
            'idtrans' => $hutangLogTrail['id'],
            'nobuktitrans' => $PelunasanHutangHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $dataDetail,
            'modifiedby' => auth('api')->user()->name
        ]);

        return $PelunasanHutangHeader;
    }


    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';

        $query = DB::table($this->table)->from(DB::raw("PelunasanHutangheader with (readuncommitted)"))
            ->select(
                'PelunasanHutangheader.id',
                'PelunasanHutangheader.nobukti',
                'PelunasanHutangheader.tglbukti',
                'PelunasanHutangheader.pengeluaran_nobukti',
                'akunpusat.keterangancoa as coa',
                'bank.namabank as bank_id',
                'supplier.namasupplier as supplier_id',
                'alatbayar.keterangan as alatbayar_id',
                'PelunasanHutangheader.tglcair',
                'statuscetak.memo as statuscetak',
                'statuscetak.id as  statuscetak_id',
                'PelunasanHutangheader.jumlahcetak',
                DB::raw("'Bukti Pembayaran Hutang' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'PelunasanHutangheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'PelunasanHutangheader.coa', 'akunpusat.coa')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'PelunasanHutangheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'PelunasanHutangheader.supplier_id', 'supplier.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'PelunasanHutangheader.alatbayar_id', 'alatbayar.id');

        if (request()->tgldari) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(PelunasanHutangheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(PelunasanHutangheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("PelunasanHutangheader.statuscetak", $statusCetak);
        }
        $data = $query->first();
        return $data;
    }

    public function processApproval(array $data)
    {
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        for ($i = 0; $i < count($data['bayarId']); $i++) {
            $PelunasanHutang = PelunasanHutangHeader::find($data['bayarId'][$i]);
            if ($PelunasanHutang->statusapproval == $statusApproval->id) {
                $PelunasanHutang->statusapproval = $statusNonApproval->id;
                $aksi = $statusNonApproval->text;
            } else {
                $PelunasanHutang->statusapproval = $statusApproval->id;
                $aksi = $statusApproval->text;
            }

            $PelunasanHutang->tglapproval = date('Y-m-d', time());
            $PelunasanHutang->userapproval = auth('api')->user()->name;
            $PelunasanHutang->save();

            (new LogTrail())->processStore([
                'namatabel' => strtoupper($PelunasanHutang->getTable()),
                'postingdari' => 'APPROVAL PELUNASAN HUTANG',
                'idtrans' => $PelunasanHutang->id,
                'nobuktitrans' => $PelunasanHutang->nobukti,
                'aksi' => $aksi,
                'datajson' => $PelunasanHutang->toArray(),
                'modifiedby' => auth('api')->user()->user
            ]);
        }
    }
}
