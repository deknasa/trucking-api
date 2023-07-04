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
                'PelunasanHutangheader.userbukacetak',
                'PelunasanHutangheader.jumlahcetak',
                'PelunasanHutangheader.modifiedby',
                'PelunasanHutangheader.created_at',
                'PelunasanHutangheader.updated_at',

                'bank.namabank as bank_id',
                'supplier.namasupplier as supplier_id',
                'alatbayar.keterangan as alatbayar_id',
                'PelunasanHutangheader.tglcair'

            )
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'PelunasanHutangheader.coa', 'akunpusat.coa')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'PelunasanHutangheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'PelunasanHutangheader.supplier_id', 'supplier.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'PelunasanHutangheader.alatbayar_id', 'alatbayar.id')
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
                'alatbayar.keterangan as alatbayar',
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
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->table.id,
                $this->table.nobukti,
                $this->table.tglbukti,
                $this->table.pengeluaran_nobukti,
                'bank.namabank as bank_id',
                'supplier.namasupplier as supplier_id',
                $this->table.coa,
                'statusapproval.text as statusapproval',
                $this->table.userapproval,
                $this->table.tglapproval,
                'statuscetak.memo as statuscetak',
                $this->table.userbukacetak,
                $this->table.tglbukacetak,
                $this->table.jumlahcetak,
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at"
                )

            )
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'PelunasanHutangheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'PelunasanHutangheader.supplier_id', 'supplier.id')
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
            $table->string('bank_id')->nullable();
            $table->string('supplier_id')->nullable();
            $table->string('coa', 50)->nullable();
            $table->string('statusapproval', 1000)->nullable();
            $table->string('userapproval', 50)->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        $models =  $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'pengeluaran_nobukti', 'bank_id', 'supplier_id', 'coa', 'statusapproval', 'userapproval', 'tglapproval', 'statuscetak', 'userbukacetak', 'tglbukacetak', 'jumlahcetak', 'modifiedby', 'created_at', 'updated_at'], $models);


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

    
    public function cekvalidasiaksi($nobukti)
    {
        $PelunasanHutang = DB::table('PelunasanHutangheader')
            ->from(
                DB::raw("PelunasanHutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.pengeluaran_nobukti', 'b.nobukti')
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($PelunasanHutang)) {
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
            $table->string('keterangan')->nullable();
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
            ->select(DB::raw("row_number() Over(Order By $temp.hutang_nobukti) as id,PelunasanHutang_id,hutang_nobukti as nobukti,tglbukti,bayar,keterangan,potongan,nominalhutang as nominal,sisa,
            (case when bayar IS NULL then 0 else (bayar + coalesce(potongan,0)) end) as total"))
            ->get();

        return $data;
    }

    
    public function createTempHutang($supplierId)
    {
        $temp = '##tempHutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('hutangheader')->from(DB::raw("hutangheader with (readuncommitted)"))
            ->select(DB::raw("hutangheader.nobukti,hutangheader.tglbukti,hutangheader.supplier_id,hutangheader.total as nominalhutang, (SELECT (hutangheader.total - COALESCE(SUM(PelunasanHutangdetail.nominal),0) - COALESCE(SUM(PelunasanHutangdetail.potongan),0)) FROM PelunasanHutangdetail WHERE PelunasanHutangdetail.hutang_nobukti= hutangheader.nobukti) AS sisa"))
            ->leftJoin(DB::raw("PelunasanHutangdetail with (readuncommitted)"), 'hutangheader.nobukti', 'PelunasanHutangdetail.hutang_nobukti')
            ->whereRaw("hutangheader.supplier_id = $supplierId")
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
            ->select(DB::raw("hbd.PelunasanHutang_id,hbd.hutang_nobukti,hutangheader.tglbukti,hbd.nominal as bayar, hbd.keterangan,hbd.potongan,hutangheader.total as nominalhutang, (SELECT (hutangheader.total - SUM(PelunasanHutangdetail.nominal) - SUM(PelunasanHutangdetail.potongan)) FROM PelunasanHutangdetail WHERE PelunasanHutangdetail.hutang_nobukti= hutangheader.nobukti) AS sisa"))
            ->join(DB::raw("hutangheader with (readuncommitted)"), 'hbd.hutang_nobukti', 'hutangheader.nobukti')
            ->whereRaw("hbd.PelunasanHutang_id = $id");

        Schema::create($tempo, function ($table) {
            $table->bigInteger('PelunasanHutang_id')->nullable();
            $table->string('hutang_nobukti');
            $table->date('tglbukti')->nullable();
            $table->bigInteger('bayar')->nullable();
            $table->string('keterangan')->nullable();
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

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMBAYARAN HUTANG')->where('subgrp', 'DEBET')->first();
        $memo = json_decode($getCoaDebet->memo, true);
        $PelunasanHutangHeader = new PelunasanHutangHeader();

        $PelunasanHutangHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $PelunasanHutangHeader->bank_id = $data['bank_id'];
        $PelunasanHutangHeader->supplier_id = $data['supplier_id'] ?? '';
        $PelunasanHutangHeader->coa = $memo['JURNAL'];
        $PelunasanHutangHeader->pengeluaran_nobukti = '';
        $PelunasanHutangHeader->statusapproval = $statusApproval->id ?? $data['statusapproval'];
        $PelunasanHutangHeader->userapproval = '';
        $PelunasanHutangHeader->tglapproval = '';
        $PelunasanHutangHeader->alatbayar_id = $data['alatbayar_id'];
        $PelunasanHutangHeader->tglcair = date('Y-m-d', strtotime($data['tglcair']));
        $PelunasanHutangHeader->statuscetak = $statusCetak->id;
        $PelunasanHutangHeader->statusformat = $format->id;
        $PelunasanHutangHeader->modifiedby = auth('api')->user()->name;
        $PelunasanHutangHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $PelunasanHutangHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$PelunasanHutangHeader->save()) {
            throw new \Exception("Error storing pembayaran Hutang header.");
        }

        $PelunasanHutangHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($PelunasanHutangHeader->getTable()),
            'postingdari' => $data['postingdari'] ??strtoupper('ENTRY hutang Bayar Header'),
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

        $bank = Bank::from(DB::raw("bank with (readuncommitted)"))
            ->select('bank.coa')->whereRaw("bank.id = $PelunasanHutangHeader->bank_id")
            ->first();
        $coakredits = $bank->coa;
        if (isset($queryalatbayar)) {
            $coakredits =  $queryalatbayar->coa;
        }

        $PelunasanHutangDetails=[];
        $nominal_detail=[];
        $keterangan_detail=[];
        $coadebet=[];
        $coakredit=[];
        $tgljatuhtempo=[];
        $nowarkat=[];

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
            $nominal_detail []= $data['bayar'][$i] - $data['potongan'][$i];
            $keterangan_detail []= $data['keterangan'][$i];
            $coadebet []= $data['coadebet'] ?? $coa;
            $coakredit []= $data['coakredit'] ?? $coakredits;
            $tgljatuhtempo[] = $hutang->tgljatuhtempo;
            $nowarkat[] = "";
        }
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($PelunasanHutangDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY HUTANG BAYAR DETAIL'),
            'idtrans' =>  $PelunasanHutangHeaderLogTrail->id,
            'nobuktitrans' => $PelunasanHutangHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $PelunasanHutangDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        /*STORE PENGELUARAN*/
        $supplier = Supplier::from(DB::raw("supplier with (readuncommitted)"))->where('id', $data['supplier_id'])->first();

        $pengeluaranRequest = [
            'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
            'pelanggan_id' => 0,
            'postingdari' => "ENTRY HUTANG BAYAR",
            'statusapproval' => $statusApproval->id,
            'dibayarke' => $supplier->namasupplier,
            'alatbayar_id' => $data['alatbayar_id'],
            'bank_id'=> $data['bank_id'],
            'transferkeac' => $supplier->rekeningbank,
            'transferkean' => $supplier->namarekening,
            'transferkebank' => $supplier->bank,
            'userapproval'=>"",
            'tglapproval'=>"",

            'nowarkat'=>$nowarkat,
            'tgljatuhtempo'=> $tgljatuhtempo,
            "nominal_detail"=>$nominal_detail,
            "coadebet"=>$coadebet,
            "coakredit"=>$coakredit,
            "keterangan_detail"=>$keterangan_detail,
            "bulanbeban"
        ];


        $pengeluaranHeader = (new PengeluaranHeader())->processStore($pengeluaranRequest);
        $PelunasanHutangHeader->pengeluaran_nobukti = $pengeluaranHeader->nobukti;
        $PelunasanHutangHeader->save();
        return $PelunasanHutangHeader;

    }
    
    public function processUpdate(PelunasanHutangHeader $PelunasanHutangHeader, array $data): PelunasanHutangHeader
    {
        $bankid = $data['bank_id'];

        /*STORE HEADER*/         
      

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMBAYARAN HUTANG')->where('subgrp', 'DEBET')->first();
        $memo = json_decode($getCoaDebet->memo, true);

        $PelunasanHutangHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $PelunasanHutangHeader->tglcair = date('Y-m-d', strtotime($data['tglcair']));
        $PelunasanHutangHeader->supplier_id = $data['supplier_id'] ?? '';
        $PelunasanHutangHeader->modifiedby = auth('api')->user()->name;

        if (!$PelunasanHutangHeader->save()) {
            throw new \Exception("Error Update pembayaran Hutang header.");
        }
        
        $PelunasanHutangHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($PelunasanHutangHeader->getTable()),
            'postingdari' => $data['postingdari'] ??strtoupper('ENTRY hutang Bayar Header'),
            'idtrans' => $PelunasanHutangHeader->id,
            'nobuktitrans' => $PelunasanHutangHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $PelunasanHutangHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        
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

        $bank = Bank::from(DB::raw("bank with (readuncommitted)"))
            ->select('bank.coa')->whereRaw("bank.id = $PelunasanHutangHeader->bank_id")
            ->first();
        $coakredits = $bank->coa;
        if (isset($queryalatbayar)) {
            $coakredits =  $queryalatbayar->coa;
        }

        $PelunasanHutangDetails=[];
        $nominal_detail=[];
        $keterangan_detail=[];
        $coadebet=[];
        $coakredit=[];
        $tgljatuhtempo=[];
        $nowarkat=[];

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
            $nominal_detail []= $data['bayar'][$i] - $data['potongan'][$i];
            $keterangan_detail []= $data['keterangan'][$i];
            $coadebet []= $coa;
            $coakredit []= $coakredits;
            $tgljatuhtempo[] = $hutang->tgljatuhtempo;
            $nowarkat[] = "";
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($PelunasanHutangDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY HUTANG BAYAR DETAIL'),
            'idtrans' =>  $PelunasanHutangHeaderLogTrail->id,
            'nobuktitrans' => $PelunasanHutangHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $PelunasanHutangDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        /*STORE PENGELUARAN*/
        $supplier = Supplier::from(DB::raw("supplier with (readuncommitted)"))->where('id', $data['supplier_id'])->first();

        $pengeluaranRequest = [
            'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
            'pelanggan_id' => 0,
            'postingdari' => "ENTRY HUTANG BAYAR",
            'statusapproval' => $statusApproval->id,
            'dibayarke' => $supplier->namasupplier,
            'alatbayar_id' => $data['alatbayar_id'],
            'bank_id'=> $data['bank_id'],
            'transferkeac' => $supplier->rekeningbank,
            'transferkean' => $supplier->namarekening,
            'transferkebank' => $supplier->bank,
            'userapproval'=>"",
            'tglapproval'=>"",

            'nowarkat'=>$nowarkat,
            'tgljatuhtempo'=> $tgljatuhtempo,
            "nominal_detail"=>$nominal_detail,
            "coadebet"=>$coadebet,
            "coakredit"=>$coakredit,
            "keterangan_detail"=>$keterangan_detail,
            "bulanbeban"
        ];


        $pengeluaranHeader = PengeluaranHeader::where('nobukti',$PelunasanHutangHeader->pengeluaran_nobukti)->first();
        $pengeluaranHeader = (new PengeluaranHeader())->processUpdate($pengeluaranHeader,$pengeluaranRequest);
        // $PelunasanHutangHeader->save();
        return $PelunasanHutangHeader;
    }


    public function processDestroy($id): PelunasanHutangHeader
    {
        $PelunasanHutangHeader = PelunasanHutangHeader::findOrFail($id);
        $dataHeader =  $PelunasanHutangHeader->toArray();
        $pengeluaranDetail = PelunasanHutangDetail::where('PelunasanHutang_id', '=', $PelunasanHutangHeader->id)->get();
        $dataDetail = $pengeluaranDetail->toArray();
        
        $pengeluaranHeader = PengeluaranHeader::where('nobukti', $PelunasanHutangHeader->pengeluaran_nobukti)->lockForUpdate()->first();
        /*DELETE EXISTING JURNAL*/
        $JurnalUmumDetail = JurnalUmumDetail::where('nobukti', $pengeluaranHeader->nobukti)->lockForUpdate()->delete();
        $JurnalUmumHeader = JurnalUmumHeader::where('nobukti', $pengeluaranHeader->nobukti)->lockForUpdate()->delete();
        /*DELETE EXISTING Pengeluaran*/
        $pengeluaranDetail = PengeluaranDetail::where('pengeluaran_id', $pengeluaranHeader->id)->lockForUpdate()->delete();
        $pengeluaranHeader->delete();
        /*DELETE EXISTING hutang bayar*/
        PelunasanHutangDetail::where('PelunasanHutang_id', $PelunasanHutangHeader->id)->lockForUpdate()->delete();

         $PelunasanHutangHeader = $PelunasanHutangHeader->lockAndDestroy($id);
         $hutangLogTrail = (new LogTrail())->processStore([
             'namatabel' => $this->table,
             'postingdari' => strtoupper('DELETE hutang bayar Header'),
             'idtrans' => $PelunasanHutangHeader->id,
             'nobuktitrans' => $PelunasanHutangHeader->nobukti,
             'aksi' => 'DELETE',
             'datajson' =>$dataHeader,
             'modifiedby' => auth('api')->user()->name
         ]);
 
         (new LogTrail())->processStore([
             'namatabel' => (new LogTrail())->table,
             'postingdari' => strtoupper('DELETE hutang bayar detail'),
             'idtrans' => $hutangLogTrail['id'],
             'nobuktitrans' => $PelunasanHutangHeader->nobukti,
             'aksi' => 'DELETE',
             'datajson' =>$dataDetail,
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
                DB::raw("'Laporan Pembayaran Hutang' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :".auth('api')->user()->name."' as usercetak")
            )
            ->where("$this->table.id", $id)
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

}
