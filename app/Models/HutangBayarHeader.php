<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


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

        $query = DB::table($this->table)->select(
            'hutangbayarheader.id',
            'hutangbayarheader.nobukti',
            'hutangbayarheader.tglbukti',
            'hutangbayarheader.keterangan',
            'hutangbayarheader.pengeluaran_nobukti',
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
            'akunpusat.coa as coa',

        )
            ->leftJoin('bank', 'hutangbayarheader.bank_id', 'bank.id')
            ->leftJoin('akunpusat', 'hutangbayarheader.coa', 'akunpusat.coa')
            ->leftJoin('supplier', 'hutangbayarheader.supplier_id', 'supplier.id')
            ->join('parameter as statuscetak', 'hutangbayarheader.statuscetak', 'statuscetak.id')
            ->join('parameter as statusapproval', 'hutangbayarheader.statusapproval', 'statusapproval.id');

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

        $query = DB::table('hutangbayarheader')->select(
            'hutangbayarheader.id',
            'hutangbayarheader.nobukti',
            'hutangbayarheader.tglbukti',
            'hutangbayarheader.keterangan',
            'hutangbayarheader.modifiedby',
            'hutangbayarheader.updated_at',
            'hutangbayarheader.bank_id',
            'bank.namabank as bank',
            'hutangbayarheader.coa',
            'hutangbayarheader.statuscetak',
            'hutangbayarheader.supplier_id',
            'supplier.namasupplier as supplier',
            'hutangbayarheader.pengeluaran_nobukti',


        )
            ->leftJoin('bank', 'hutangbayarheader.bank_id', 'bank.id')
            ->leftJoin('akunpusat', 'hutangbayarheader.coa', 'akunpusat.coa')
            ->leftJoin('supplier', 'hutangbayarheader.supplier_id', 'supplier.id')

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
        return $query->select(
            DB::raw(
                "$this->table.id,
                $this->table.nobukti,
                $this->table.tglbukti,
                $this->table.keterangan,
                $this->table.pengeluaran_nobukti,
                'bank.namabank as bank_id',
                'supplier.namasupplier as supplier_id',
                'akunpusat.coa as coa',
                'statusapproval.text as statusapproval',
                $this->table.userapproval,
                $this->table.tglapproval,
                'statuscetak.momo as statuscetak',
                $this->table.userbukacetak,
                $this->table.tglbukacetak,
                $this->table.jumlahcetak,
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at"
            )

        )
            ->leftJoin('bank', 'hutangbayarheader.bank_id', 'bank.id')
            ->leftJoin('supplier', 'hutangbayarheader.supplier_id', 'supplier.id')
            ->leftJoin('akunpusat', 'hutangbayarheader.coa', 'akunpusat.coa')
            ->join('parameter as statuscetak', 'hutangbayarheader.statuscetak', 'statuscetak.id')
            ->join('parameter as statusapproval', 'hutangbayarheader.statusapproval', 'statusapproval.id');

    }

    public function getPembayaran($id, $supplierId)
    {
        $this->setRequestParameters();

        $tempHutang = $this->createTempHutang($supplierId);
        $tempPembayaran = $this->createTempPembayaran($id);


        $hutang = DB::table("$tempHutang as A")
            ->select(DB::raw("A.id as id,null as hutangbayar_id,A.nobukti as hutang_nobukti, A.tglbukti as tglbukti, null as bayar, null as keterangan, null as tglcair, null as potongan, null as alatbayar_id, null as alatbayar, A.nominalhutang, A.sisa as sisa"))
            // ->distinct("A.nobukti")
            ->leftJoin("$tempPembayaran as B", "A.nobukti", "B.hutang_nobukti")
            ->whereRaw("isnull(b.hutang_nobukti,'') = ''")
            ->whereRaw("a.sisa > 0");


        $pembayaran = DB::table($tempPembayaran)
            ->select(DB::raw("id,hutangbayar_id,hutang_nobukti,tglbukti,bayar,keterangan,tglcair,potongan,alatbayar_id,alatbayar,nominalhutang,sisa"))
            ->unionAll($hutang);

        // $this->totalRows = $pembayaran->count();
        // $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        // $this->sort($pembayaran);
        // $this->filter($pembayaran);
        // $this->paginate($pembayaran);

        $data = $pembayaran->get();

        return $data;
    }

    public function createTempHutang($supplierId)
    {
        $temp = '##tempHutang' . rand(1, 10000);


        $fetch = DB::table('hutangheader')
            ->select(DB::raw("hutangheader.id,hutangheader.nobukti,hutangheader.tglbukti,hutangdetail.supplier_id,hutangheader.total as nominalhutang, (SELECT (hutangheader.total - COALESCE(SUM(hutangbayardetail.nominal),0)) FROM hutangbayardetail WHERE hutangbayardetail.hutang_nobukti= hutangheader.nobukti) AS sisa"))
            ->join('hutangdetail', 'hutangheader.id', 'hutangdetail.hutang_id')
            ->leftJoin('hutangbayardetail', 'hutangheader.nobukti', 'hutangbayardetail.hutang_nobukti')
            ->whereRaw("hutangdetail.supplier_id = $supplierId")
            ->groupBy('hutangheader.id', 'hutangheader.nobukti', 'hutangdetail.supplier_id', 'hutangheader.total', 'hutangheader.tglbukti');

        Schema::create($temp, function ($table) {
            $table->bigInteger('id');
            $table->string('nobukti');
            $table->date('tglbukti')->default('');
            $table->bigInteger('supplier_id')->default('0');
            $table->bigInteger('nominalhutang');
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'supplier_id', 'nominalhutang', 'sisa'], $fetch);

        return $temp;
    }

    public function createTempPembayaran($id)
    {
        $tempo = '##tempPembayaran' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = DB::table('hutangbayardetail as hbd')
            ->select(DB::raw("hutangheader.id,hbd.hutangbayar_id,hbd.hutang_nobukti,hutangheader.tglbukti,hbd.nominal as bayar, hbd.keterangan,hbd.tglcair,hbd.potongan,hbd.alatbayar_id,alatbayar.namaalatbayar as alatbayar,hutangheader.total as nominalhutang, (SELECT (hutangheader.total - SUM(hutangbayardetail.nominal)) FROM hutangbayardetail WHERE hutangbayardetail.hutang_nobukti= hutangheader.nobukti) AS sisa"))
            ->leftJoin('hutangheader', 'hbd.hutang_nobukti', 'hutangheader.nobukti')
            ->leftJoin('alatbayar', 'hbd.alatbayar_id', 'alatbayar.id')
            ->whereRaw("hbd.hutangbayar_id = $id");

        Schema::create($tempo, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->bigInteger('hutangbayar_id')->default('0');
            $table->string('hutang_nobukti');
            $table->date('tglbukti')->default('');
            $table->bigInteger('bayar')->nullable();
            $table->string('keterangan');
            $table->date('tglcair')->default('');
            $table->bigInteger('potongan')->default('0');
            $table->bigInteger('alatbayar_id')->default('0');
            $table->string('alatbayar');
            $table->bigInteger('nominalhutang');
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($tempo)->insertUsing(['id', 'hutangbayar_id', 'hutang_nobukti', 'tglbukti', 'bayar', 'keterangan', 'tglcair', 'potongan', 'alatbayar_id', 'alatbayar', 'nominalhutang', 'sisa'], $fetch);

        return $tempo;
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti')->default('1900/1/1');
            $table->longText('keterangan')->default('');
            $table->string('pengeluaran_nobukti')->default('0');
            $table->string('bank_id')->default('0');
            $table->string('supplier_id')->default('0');
            $table->string('coa', 50)->default('');
            $table->string('statusapproval',1000)->default('');
            $table->string('userapproval',50)->default('');
            $table->date('tglapproval')->default('1900/1/1');
            $table->string('statuscetak',1000)->default('');
            $table->string('userbukacetak',50)->default('');
            $table->date('tglbukacetak')->default('1900/1/1');
            $table->integer('jumlahcetak')->Length(11)->default('0');
            $table->string('modifiedby', 50)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'keterangan','pengeluaran_nobukti', 'bank_id', 'supplier_id', 'coa','statusapproval','userapproval','tglapproval', 'statuscetak','userbukacetak','tglbukacetak','jumlahcetak', 'modifiedby', 'created_at', 'updated_at'], $models);


        return  $temp;
    }

    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('statusapproval.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statuscetak') {
                            $query = $query->where('statuscetak.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'supplier_id') {
                            $query = $query->where('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->orWhere('statusapproval.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statuscetak') {
                            $query = $query->orWhere('statuscetak.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'supplier_id') {
                            $query = $query->orWhere('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                default:

                    break;
            }

            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }
        if (request()->cetak && request()->periode) {
            $query->where('hutangbayarheader.statuscetak','<>', request()->cetak)
                  ->whereYear('hutangbayarheader.tglbukti','=', request()->year)
                  ->whereMonth('hutangbayarheader.tglbukti','=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
