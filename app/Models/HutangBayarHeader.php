<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\RunningNumberService;
use Psy\CodeCleaner\FunctionReturnInWriteContextPass;

class HutangBayarHeader extends MyModel
{
    use HasFactory;
    protected $table = 'hutangbayarheader';

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

        $query = DB::table($this->table)->from(DB::raw("hutangbayarheader with (readuncommitted)"))
            ->select(
                'hutangbayarheader.id',
                'hutangbayarheader.nobukti',
                'hutangbayarheader.tglbukti',
                'hutangbayarheader.pengeluaran_nobukti',
                'akunpusat.keterangancoa as coa',
                'hutangbayarheader.userapproval',
                'statusapproval.memo as statusapproval',
                DB::raw('(case when (year(hutangbayarheader.tglapproval) <= 2000) then null else hutangbayarheader.tglapproval end ) as tglapproval'),
                DB::raw('(case when (year(hutangbayarheader.tglbukacetak) <= 2000) then null else hutangbayarheader.tglbukacetak end ) as tglbukacetak'),
                'statuscetak.memo as statuscetak',
                'hutangbayarheader.userbukacetak',
                'hutangbayarheader.jumlahcetak',
                'hutangbayarheader.modifiedby',
                'hutangbayarheader.created_at',
                'hutangbayarheader.updated_at',

                'bank.namabank as bank_id',
                'supplier.namasupplier as supplier_id',
                'alatbayar.keterangan as alatbayar_id',
                'hutangbayarheader.tglcair'

            )
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'hutangbayarheader.coa', 'akunpusat.coa')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'hutangbayarheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'hutangbayarheader.supplier_id', 'supplier.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'hutangbayarheader.alatbayar_id', 'alatbayar.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'hutangbayarheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'hutangbayarheader.statusapproval', 'statusapproval.id');
        if (request()->tgldari) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(hutangbayarheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(hutangbayarheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("hutangbayarheader.statuscetak", $statusCetak);
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

        $query = DB::table('hutangbayarheader')->from(DB::raw("hutangbayarheader with (readuncommitted)"))
            ->select(
                'hutangbayarheader.id',
                'hutangbayarheader.nobukti',
                'hutangbayarheader.tglbukti',
                'hutangbayarheader.modifiedby',
                'hutangbayarheader.updated_at',
                'hutangbayarheader.bank_id',
                'bank.namabank as bank',
                'hutangbayarheader.statuscetak',
                'hutangbayarheader.supplier_id',
                'supplier.namasupplier as supplier',
                'hutangbayarheader.pengeluaran_nobukti',
                'hutangbayarheader.alatbayar_id',
                'alatbayar.keterangan as alatbayar',
                'hutangbayarheader.tglcair'
            )
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'hutangbayarheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'hutangbayarheader.supplier_id', 'supplier.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'hutangbayarheader.alatbayar_id', 'alatbayar.id')
            ->where('hutangbayarheader.id', $id);


        $data = $query->first();

        return $data;
    }

    public function hutangbayardetail()
    {
        return $this->hasMany(HutangBayarDetail::class, 'hutangbayar_id');
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
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'hutangbayarheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'hutangbayarheader.supplier_id', 'supplier.id')
            ->join(DB::raw("parameter as statuscetak with (readuncommitted)"), 'hutangbayarheader.statuscetak', 'statuscetak.id')
            ->join(DB::raw("parameter as statusapproval with (readuncommitted)"), 'hutangbayarheader.statusapproval', 'statusapproval.id');
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
            $query->where('hutangbayarheader.statuscetak', '<>', request()->cetak)
                ->whereYear('hutangbayarheader.tglbukti', '=', request()->year)
                ->whereMonth('hutangbayarheader.tglbukti', '=', request()->month);
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
        $hutangBayar = DB::table('hutangbayarheader')
            ->from(
                DB::raw("hutangbayarheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.pengeluaran_nobukti', 'b.nobukti')
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

    
    public function getPembayaran($id, $supplierId)
    {
        $this->setRequestParameters();

        $tempHutang = $this->createTempHutang($supplierId);
        $tempPembayaran = $this->createTempPembayaran($id, $supplierId);


        // $hutang = DB::table("$tempHutang as A")->from(DB::raw("$tempHutang as A with (readuncommitted)"))
        //     ->select(DB::raw("A.id as id,null as hutangbayar_id,A.nobukti as hutang_nobukti, A.tglbukti as tglbukti, null as bayar, null as keterangan, null as potongan, A.nominalhutang, A.sisa as sisa"))
        //     // ->distinct("A.nobukti")
        //     ->leftJoin(DB::raw("$tempPembayaran as B with (readuncommitted)"), "A.nobukti", "B.hutang_nobukti")
        //     ->whereRaw("isnull(b.hutang_nobukti,'') = ''")
        //     ->whereRaw("a.sisa > 0");


        // $pembayaran = DB::table($tempPembayaran)->from(DB::raw("$tempPembayaran with (readuncommitted)"))
        //     ->select(DB::raw("id,hutangbayar_id,hutang_nobukti,tglbukti,bayar,keterangan,potongan,nominalhutang,sisa"))
        //     ->unionAll($hutang);

        $temp = '##tempGet' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $pembayaran = DB::table($tempPembayaran)->from(DB::raw("$tempPembayaran with (readuncommitted)"))
            ->select(DB::raw("hutangbayar_id,hutang_nobukti,tglbukti,bayar,keterangan,potongan,nominalhutang,sisa"));

        Schema::create($temp, function ($table) {
            $table->bigInteger('hutangbayar_id')->nullable();
            $table->string('hutang_nobukti');
            $table->date('tglbukti')->nullable();
            $table->bigInteger('bayar')->nullable();
            $table->string('keterangan')->nullable();
            $table->bigInteger('potongan')->nullable();
            $table->bigInteger('nominalhutang');
            $table->bigInteger('sisa')->nullable();
        });

        DB::table($temp)->insertUsing(['hutangbayar_id', 'hutang_nobukti', 'tglbukti', 'bayar', 'keterangan',  'potongan', 'nominalhutang', 'sisa'], $pembayaran);

        $hutang = DB::table("$tempHutang as A")->from(DB::raw("$tempHutang as A with (readuncommitted)"))
            ->select(DB::raw("null as hutangbayar_id,A.nobukti as hutang_nobukti, A.tglbukti as tglbukti, 0 as bayar, null as keterangan, 0 as potongan, A.nominalhutang, A.sisa as sisa"))
            // ->distinct("A.nobukti")
            ->leftJoin(DB::raw("$tempPembayaran as B with (readuncommitted)"), "A.nobukti", "B.hutang_nobukti")
            ->whereRaw("isnull(b.hutang_nobukti,'') = ''")
            ->whereRaw("a.sisa > 0");
        DB::table($temp)->insertUsing(['hutangbayar_id', 'hutang_nobukti', 'tglbukti', 'bayar', 'keterangan',  'potongan', 'nominalhutang', 'sisa'], $hutang);

        $data = DB::table($temp)->from(DB::raw("$temp with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $temp.hutang_nobukti) as id,hutangbayar_id,hutang_nobukti as nobukti,tglbukti,bayar,keterangan,nominalhutang as nominal,
            (case when potongan IS NULL then 0 else potongan end) as potongan,
            (case when sisa IS NULL then 0 else sisa end) as sisa,
            (case when bayar IS NULL then 0 else (bayar + coalesce(potongan,0)) end) as total"))
            ->get();

        return $data;
    }

    
    public function createTempHutang($supplierId)
    {
        $temp = '##tempHutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('hutangheader')->from(DB::raw("hutangheader with (readuncommitted)"))
            ->select(DB::raw("hutangheader.nobukti,hutangheader.tglbukti,hutangheader.supplier_id,hutangheader.total as nominalhutang, (SELECT (hutangheader.total - COALESCE(SUM(hutangbayardetail.nominal),0) - COALESCE(SUM(hutangbayardetail.potongan),0)) FROM hutangbayardetail WHERE hutangbayardetail.hutang_nobukti= hutangheader.nobukti) AS sisa"))
            ->leftJoin(DB::raw("hutangbayardetail with (readuncommitted)"), 'hutangheader.nobukti', 'hutangbayardetail.hutang_nobukti')
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

        $fetch = DB::table('hutangbayardetail as hbd')->from(DB::raw("hutangbayardetail as hbd with (readuncommitted)"))
            ->select(DB::raw("hbd.hutangbayar_id,hbd.hutang_nobukti,hutangheader.tglbukti,hbd.nominal as bayar, hbd.keterangan,hbd.potongan,hutangheader.total as nominalhutang, (SELECT (hutangheader.total - SUM(hutangbayardetail.nominal) - SUM(hutangbayardetail.potongan)) FROM hutangbayardetail WHERE hutangbayardetail.hutang_nobukti= hutangheader.nobukti) AS sisa"))
            ->join(DB::raw("hutangheader with (readuncommitted)"), 'hbd.hutang_nobukti', 'hutangheader.nobukti')
            ->whereRaw("hbd.hutangbayar_id = $id");

        Schema::create($tempo, function ($table) {
            $table->bigInteger('hutangbayar_id')->nullable();
            $table->string('hutang_nobukti');
            $table->date('tglbukti')->nullable();
            $table->bigInteger('bayar')->nullable();
            $table->string('keterangan')->nullable();
            $table->bigInteger('potongan')->nullable();
            $table->bigInteger('nominalhutang');
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($tempo)->insertUsing(['hutangbayar_id', 'hutang_nobukti', 'tglbukti', 'bayar', 'keterangan',  'potongan', 'nominalhutang', 'sisa'], $fetch);

        return $tempo;
    }
    
    public function processStore(array $data): HutangBayarHeader
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
        $hutangBayarHeader = new HutangBayarHeader();

        $hutangBayarHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $hutangBayarHeader->bank_id = $data['bank_id'];
        $hutangBayarHeader->supplier_id = $data['supplier_id'] ?? '';
        $hutangBayarHeader->coa = $memo['JURNAL'];
        $hutangBayarHeader->pengeluaran_nobukti = '';
        $hutangBayarHeader->statusapproval = $statusApproval->id ?? $data['statusapproval'];
        $hutangBayarHeader->userapproval = '';
        $hutangBayarHeader->tglapproval = '';
        $hutangBayarHeader->alatbayar_id = $data['alatbayar_id'];
        $hutangBayarHeader->tglcair = date('Y-m-d', strtotime($data['tglcair']));
        $hutangBayarHeader->statuscetak = $statusCetak->id;
        $hutangBayarHeader->statusformat = $format->id;
        $hutangBayarHeader->modifiedby = auth('api')->user()->name;
        $hutangBayarHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $hutangBayarHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
        

        if (!$hutangBayarHeader->save()) {
            throw new \Exception("Error storing pembayaran Hutang header.");
        }

        $hutangBayarHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($hutangBayarHeader->getTable()),
            'postingdari' => $data['postingdari'] ??strtoupper('ENTRY hutang Bayar Header'),
            'idtrans' => $hutangBayarHeader->id,
            'nobuktitrans' => $hutangBayarHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $hutangBayarHeader->toArray(),
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
            ->select('bank.coa')->whereRaw("bank.id = $hutangBayarHeader->bank_id")
            ->first();
        $coakredits = $bank->coa;
        if (isset($queryalatbayar)) {
            $coakredits =  $queryalatbayar->coa;
        }

        $hutangBayarDetails=[];
        $nominal_detail=[];
        $keterangan_detail=[];
        $coadebet=[];
        $coakredit=[];
        $tgljatuhtempo=[];
        $nowarkat=[];

        $statusketerangan = DB::table('parameter')->from(
            db::raw("parameter a with (readuncommitted)")
        )
            ->select(
                'a.text'
            )
            ->where('grp', '=', 'KETERANGAN DEFAULT HUTANG USAHA')
            ->where('subgrp', '=', 'KETERANGAN DEFAULT HUTANG USAHA')
            ->first();

            $querysupplier = DB::table('hutangbayarheader')->from(
                db::raw("hutangbayarheader a with (readuncommitted)")
            )
                ->select(
                    'b.namasupplier'
                )
                ->join(DB::raw("supplier b with (readuncommitted)"), 'a.supplier_id', 'b.id')
                ->where('a.id', '=', $hutangBayarHeader->id)
                ->first();

        $supplier=$querysupplier->namasupplier ?? '';
    
        $statusketerangan=$statusketerangan->text ?? '';


        for ($i = 0; $i < count($data['hutang_id']); $i++) {
            $hutang = HutangDetail::where('nobukti', $data['hutang_nobukti'][$i])->first();

            $hutangBayarDetail = (new HutangBayarDetail())->processStore($hutangBayarHeader, [
                'hutangbayar_id' => $hutangBayarHeader->id,
                'nobukti' => $hutangBayarHeader->nobukti,
                'hutang_nobukti' => $hutang->nobukti,
                'nominal' => $data['bayar'][$i],
                'cicilan' => '',
                'userid' => '',
                'potongan' => $data['potongan'][$i],
                'keterangan' => $data['keterangan'][$i],
                'modifiedby' => $hutangBayarHeader->modifiedby                
            ]);
            $hutangBayarDetails[] = $hutangBayarDetail->toArray();
            $nominal_detail []= $data['bayar'][$i] - $data['potongan'][$i];
            $keterangan_detail []= $data['keterangan'][$i];
            $coadebet []= $data['coadebet'] ?? $coa;
            $coakredit []= $data['coakredit'] ?? $coakredits;
            $tgljatuhtempo[] = $hutang->tgljatuhtempo;
            $nowarkat[] = "";
            $statusketerangandefault []=$statusketerangan . ' ' . $supplier. ' '.$data['keterangan'][$i];

        }
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($hutangBayarDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY HUTANG BAYAR DETAIL'),
            'idtrans' =>  $hutangBayarHeaderLogTrail->id,
            'nobuktitrans' => $hutangBayarHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $hutangBayarDetails,
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
            "keterangan_detail"=>$statusketerangandefault,
            "bulanbeban"
        ];


        $pengeluaranHeader = (new PengeluaranHeader())->processStore($pengeluaranRequest);
        $hutangBayarHeader->pengeluaran_nobukti = $pengeluaranHeader->nobukti;
        $hutangBayarHeader->save();
        return $hutangBayarHeader;

    }
    
    public function processUpdate(HutangBayarHeader $hutangBayarHeader, array $data): HutangBayarHeader
    {
        $bankid = $data['bank_id'];

        /*STORE HEADER*/         

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
        
        $group = 'PEMBAYARAN HUTANG BUKTI';
        $subGroup = 'PEMBAYARAN HUTANG BUKTI';

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMBAYARAN HUTANG')->where('subgrp', 'DEBET')->first();
        $memo = json_decode($getCoaDebet->memo, true);

        $querycek = DB::table('hutangbayarheader')->from(
            DB::raw("hutangbayarheader a with (readuncommitted)")
        )
            ->select(
                'a.nobukti'
            )
            ->where('a.id', $hutangBayarHeader->id)
            ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
            ->first();

        if (isset($querycek)) {
            $nobukti = $querycek->nobukti;
        } else {
            $nobukti = (new RunningNumberService)->get($group, $subGroup, $hutangBayarHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
        }

        $hutangBayarHeader->nobukti = $nobukti;
        $hutangBayarHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $hutangBayarHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $hutangBayarHeader->tglcair = date('Y-m-d', strtotime($data['tglcair']));
        $hutangBayarHeader->supplier_id = $data['supplier_id'] ?? '';
        $hutangBayarHeader->modifiedby = auth('api')->user()->name;
        $hutangBayarHeader->coa = $memo['JURNAL'];


        if (!$hutangBayarHeader->save()) {
            throw new \Exception("Error Update pembayaran Hutang header.");
        }
        
        
        // $pengeluaranHeader = PengeluaranHeader::where('nobukti', $hutangBayarHeader->pengeluaran_nobukti)->lockForUpdate()->first();
        // /*DELETE EXISTING JURNAL*/
        // $JurnalUmumDetail = JurnalUmumDetail::where('nobukti', $pengeluaranHeader->nobukti)->lockForUpdate()->delete();
        // $JurnalUmumHeader = JurnalUmumHeader::where('nobukti', $pengeluaranHeader->nobukti)->lockForUpdate()->delete();
        // /*DELETE EXISTING Pengeluaran*/
        // $pengeluaranDetail = PengeluaranDetail::where('pengeluaran_id', $pengeluaranHeader->id)->lockForUpdate()->delete();
        // $pengeluaranHeader->delete();
        /*DELETE EXISTING hutang bayar*/
        HutangBayarDetail::where('hutangbayar_id', $hutangBayarHeader->id)->lockForUpdate()->delete();
        

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
            ->select('bank.coa')->whereRaw("bank.id = $hutangBayarHeader->bank_id")
            ->first();
        $coakredits = $bank->coa;
        if (isset($queryalatbayar)) {
            $coakredits =  $queryalatbayar->coa;
        }

        $hutangBayarDetails=[];
        $nominal_detail=[];
        $keterangan_detail=[];
        $coadebet=[];
        $coakredit=[];
        $tgljatuhtempo=[];
        $nowarkat=[];

        for ($i = 0; $i < count($data['hutang_id']); $i++) {
            $hutang = HutangDetail::where('nobukti', $data['hutang_nobukti'][$i])->first();

            $hutangBayarDetail = (new HutangBayarDetail())->processStore($hutangBayarHeader, [
                'hutangbayar_id' => $hutangBayarHeader->id,
                'nobukti' => $hutangBayarHeader->nobukti,
                'hutang_nobukti' => $hutang->nobukti,
                'nominal' => $data['bayar'][$i],
                'cicilan' => '',
                'userid' => '',
                'potongan' => $data['potongan'][$i],
                'keterangan' => $data['keterangan'][$i],
                'modifiedby' => $hutangBayarHeader->modifiedby
            ]);

            $hutangBayarDetails[] = $hutangBayarDetail->toArray();
            $nominal_detail []= $data['bayar'][$i] - $data['potongan'][$i];
            $keterangan_detail []= $data['keterangan'][$i];
            $coadebet []= $coa;
            $coakredit []= $coakredits;
            $tgljatuhtempo[] = $hutang->tgljatuhtempo;
            $nowarkat[] = "";
        }


        /*STORE PENGELUARAN*/
        $supplier = Supplier::from(DB::raw("supplier with (readuncommitted)"))->where('id', $data['supplier_id'])->first();

        $pengeluaranRequest = [
            'tglbukti' => $data['tglbukti'],
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


        $pengeluaranHeader = PengeluaranHeader::where('nobukti',$hutangBayarHeader->pengeluaran_nobukti)->first();
        $pengeluaranHeader = (new PengeluaranHeader())->processUpdate($pengeluaranHeader,$pengeluaranRequest);
        $hutangBayarHeader->pengeluaran_nobukti = $pengeluaranHeader->nobukti;
        $hutangBayarHeader->save();

        
        $hutangBayarHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($hutangBayarHeader->getTable()),
            'postingdari' => $data['postingdari'] ??strtoupper('ENTRY hutang Bayar Header'),
            'idtrans' => $hutangBayarHeader->id,
            'nobuktitrans' => $hutangBayarHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $hutangBayarHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($hutangBayarDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY HUTANG BAYAR DETAIL'),
            'idtrans' =>  $hutangBayarHeaderLogTrail->id,
            'nobuktitrans' => $hutangBayarHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $hutangBayarDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);
        return $hutangBayarHeader;
    }


    public function processDestroy($id): HutangBayarHeader
    {
        $hutangBayarHeader = HutangBayarHeader::findOrFail($id);
        $dataHeader =  $hutangBayarHeader->toArray();
        $pengeluaranDetail = HutangBayarDetail::where('hutangbayar_id', '=', $hutangBayarHeader->id)->get();
        $dataDetail = $pengeluaranDetail->toArray();
        
        $pengeluaranHeader = PengeluaranHeader::where('nobukti', $hutangBayarHeader->pengeluaran_nobukti)->lockForUpdate()->first();
        /*DELETE EXISTING JURNAL*/
        $JurnalUmumDetail = JurnalUmumDetail::where('nobukti', $pengeluaranHeader->nobukti)->lockForUpdate()->delete();
        $JurnalUmumHeader = JurnalUmumHeader::where('nobukti', $pengeluaranHeader->nobukti)->lockForUpdate()->delete();
        /*DELETE EXISTING Pengeluaran*/
        $pengeluaranDetail = PengeluaranDetail::where('pengeluaran_id', $pengeluaranHeader->id)->lockForUpdate()->delete();
        $pengeluaranHeader->delete();
        /*DELETE EXISTING hutang bayar*/
        HutangBayarDetail::where('hutangbayar_id', $hutangBayarHeader->id)->lockForUpdate()->delete();

         $hutangBayarHeader = $hutangBayarHeader->lockAndDestroy($id);
         $hutangLogTrail = (new LogTrail())->processStore([
             'namatabel' => $this->table,
             'postingdari' => strtoupper('DELETE hutang bayar Header'),
             'idtrans' => $hutangBayarHeader->id,
             'nobuktitrans' => $hutangBayarHeader->nobukti,
             'aksi' => 'DELETE',
             'datajson' =>$dataHeader,
             'modifiedby' => auth('api')->user()->name
         ]);
 
         (new LogTrail())->processStore([
             'namatabel' => (new LogTrail())->table,
             'postingdari' => strtoupper('DELETE hutang bayar detail'),
             'idtrans' => $hutangLogTrail['id'],
             'nobuktitrans' => $hutangBayarHeader->nobukti,
             'aksi' => 'DELETE',
             'datajson' =>$dataDetail,
             'modifiedby' => auth('api')->user()->name
         ]);
 
         return $hutangBayarHeader;
        
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

        $query = DB::table($this->table)->from(DB::raw("hutangbayarheader with (readuncommitted)"))
            ->select(
                'hutangbayarheader.id',
                'hutangbayarheader.nobukti',
                'hutangbayarheader.tglbukti',
                'supplier.namasupplier as supplier_id',
                'statuscetak.memo as statuscetak',
                'statuscetak.id as  statuscetak_id',
                'hutangbayarheader.jumlahcetak',
                DB::raw("'Cetak Pembayaran Hutang' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :".auth('api')->user()->name."' as usercetak")
            )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'hutangbayarheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'hutangbayarheader.coa', 'akunpusat.coa')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'hutangbayarheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'hutangbayarheader.supplier_id', 'supplier.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'hutangbayarheader.alatbayar_id', 'alatbayar.id');
        
        if (request()->tgldari) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(hutangbayarheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(hutangbayarheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("hutangbayarheader.statuscetak", $statusCetak);
        }
        $data = $query->first();
        return $data;
    }
}
