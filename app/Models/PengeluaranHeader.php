<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PengeluaranHeader extends MyModel
{
    use HasFactory;

    protected $table = 'pengeluaranheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function pengeluarandetail() {
        return $this->hasMany(pengeluarandetail::class, 'pengeluaran_id');
    }

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->select(
            'pengeluaranheader.id',
            'pengeluaranheader.nobukti',
            'pengeluaranheader.tglbukti',

            'pelanggan.namapelanggan as pelanggan_id',

            'pengeluaranheader.keterangan',
            'pengeluaranheader.postingdari',
            'pengeluaranheader.dibayarke',
            'cabang.namacabang as cabang_id',
            'bank.namabank as bank_id',
            'statusjenistransaksi.text as statusjenistransaksi',
            'statusapproval.memo as statusapproval',
            DB::raw('(case when (year(pengeluaranheader.tglapproval) <= 2000) then null else pengeluaranheader.tglapproval end ) as tglapproval'),
            'pengeluaranheader.userapproval',
            'pengeluaranheader.transferkeac',
            'pengeluaranheader.transferkean',
            'pengeluaranheader.transferkebank',
            DB::raw('(case when (year(pengeluaranheader.tglbukacetak) <= 2000) then null else pengeluaranheader.tglbukacetak end ) as tglbukacetak'),
            'statuscetak.memo as statuscetak',
            'pengeluaranheader.userbukacetak',
            'pengeluaranheader.jumlahcetak',
            'pengeluaranheader.modifiedby',
            'pengeluaranheader.created_at',
            'pengeluaranheader.updated_at'

        )
        ->leftJoin('pelanggan', 'pengeluaranheader.pelanggan_id', 'pelanggan.id')
        ->leftJoin('cabang', 'pengeluaranheader.cabang_id', 'cabang.id')
        ->leftJoin('bank', 'pengeluaranheader.bank_id', 'bank.id')
        ->leftJoin('parameter as statusapproval' , 'pengeluaranheader.statusapproval', 'statusapproval.id')
        ->leftJoin('parameter as statuscetak' , 'pengeluaranheader.statuscetak', 'statuscetak.id')
        ->leftJoin('parameter as statusjenistransaksi' , 'pengeluaranheader.statusjenistransaksi', 'statusjenistransaksi.id');

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
        $query = DB::table('pengeluaranheader')->select(
            'pengeluaranheader.id',
            'pengeluaranheader.nobukti',
            'pengeluaranheader.tglbukti',
            'pengeluaranheader.pelanggan_id',
            'pelanggan.namapelanggan as pelanggan',
            'pengeluaranheader.keterangan',
            'pengeluaranheader.cabang_id',
            'cabang.namacabang as cabang',
            'pengeluaranheader.statusjenistransaksi',
            'pengeluaranheader.dibayarke',
            'pengeluaranheader.bank_id',
            'bank.namabank as bank',
            'pengeluaranheader.transferkeac',
            'pengeluaranheader.transferkean',
            'pengeluaranheader.transferkebank',
        )
        ->leftJoin('pelanggan', 'pengeluaranheader.pelanggan_id', 'pelanggan.id')
        ->leftJoin('cabang', 'pengeluaranheader.cabang_id', 'cabang.id')
        ->leftJoin('bank', 'pengeluaranheader.bank_id', 'bank.id')
        ->where('pengeluaranheader.id',$id);

        $data = $query->first();

        return $data;
    }
    
    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
                "$this->table.id,
                 $this->table.nobukti,
                 $this->table.tglbukti,
                 'pelanggan.namapelanggan as pelanggan_id',
                 $this->table.keterangan,
                 $this->table.postingdari,
                 $this->table.dibayarke,
                 'cabang.namacabang as cabang_id',
                 'bank.namabank as bank_id',
                 'statusjenistransaksi.text as statusjenistransaksi',
                 'statusapproval.text as statusapproval',
                 $this->table.transferkeac,
                 $this->table.transferkean,
                 $this->table.transferkebank,
                 'statuscetak.text as statuscetak',
                 $this->table.userbukacetak,
                 $this->table.tglbukacetak,
                 $this->table.jumlahcetak,
                 $this->table.modifiedby,
                 $this->table.created_at,
                 $this->table.updated_at"
            )
        )
        ->leftJoin('pelanggan', 'pengeluaranheader.pelanggan_id', 'pelanggan.id')
        ->leftJoin('cabang', 'pengeluaranheader.cabang_id', 'cabang.id')
        ->leftJoin('bank', 'pengeluaranheader.bank_id', 'bank.id')
        ->leftJoin('parameter as statusapproval' , 'pengeluaranheader.statusapproval', 'statusapproval.id')
        ->leftJoin('parameter as statuscetak' , 'pengeluaranheader.statuscetak', 'statuscetak.id')
        ->leftJoin('parameter as statusjenistransaksi' , 'pengeluaranheader.statusjenistransaksi', 'statusjenistransaksi.id');

    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti')->default('');
            $table->string('pelanggan_id', 1000)->default('');
            $table->string('keterangan', 1000)->default('');
            $table->string('postingdari', 1000)->default('');
            $table->string('dibayarke', 1000)->default('');
            $table->string('cabang_id', 1000)->default('');
            $table->string('bank_id', 1000)->default('');
            $table->string('statusjenistransaksi', 1000)->default('');
            $table->string('statusapproval')->default('');
            $table->string('transferkeac')->default('');
            $table->string('transferkean')->default('');
            $table->string('transferkebank')->default('');
            $table->string('statuscetak',1000)->default('');
            $table->string('userbukacetak',50)->default('');
            $table->date('tglbukacetak')->default('1900/1/1');
            $table->integer('jumlahcetak')->Length(11)->default('0');
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti','pelanggan_id', 'keterangan', 'postingdari', 'dibayarke', 'cabang_id', 'bank_id','statusjenistransaksi','statusapproval','transferkeac','transferkean','transferkebank','statuscetak','userbukacetak','tglbukacetak','jumlahcetak', 'modifiedby','created_at', 'updated_at'], $models);

        return $temp;
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
                                $query = $query->where('statusapproval.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusjenistransaksi') {
                                $query = $query->where('statusjenistransaksi.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'pelanggan_id') {
                                $query = $query->where('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'cabang_id') {
                                $query = $query->where('cabang.namacabang', 'LIKE', "%$filters[data]%");
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
                                $query = $query->orWhere('statusapproval.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusjenistransaksi') {
                                $query = $query->orWhere('statusjenistransaksi.text', '=', "$filters[data]");
                            }else if ($filters['field'] == 'pelanggan_id') {
                                $query = $query->orWhere('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'cabang_id') {
                                $query = $query->orWhere('cabang.namacabang', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'bank_id') {
                                $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                            }else {
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
            
            if (request()->approve && request()->periode) {
                $query->where('pengeluaranheader.statusapproval','<>', request()->approve)
                      ->whereYear('pengeluaranheader.tglbukti','=', request()->year)
                      ->whereMonth('pengeluaranheader.tglbukti','=', request()->month);
                return $query;
            }

            return $query;
        }
    
        public function paginate($query)
        {
            return $query->skip($this->params['offset'])->take($this->params['limit']);
        }

        public function getRekapPengeluaranHeader($bank,$tglbukti)
        {
            $this->setRequestParameters();
    
            $query = DB::table($this->table)->select(
                'pengeluaranheader.nobukti',
                'pengeluaranheader.keterangan as keterangan_detail',
                'pengeluaranheader.tglbukti',
                DB::raw('SUM(pengeluarandetail.nominal) AS nominal')
            )
            ->where('pengeluaranheader.bank_id',$bank)
            ->where('pengeluaranheader.tglbukti',$tglbukti)
            ->whereRaw(" NOT EXISTS (
                SELECT pengeluaran_nobukti
                FROM rekappengeluarandetail
                WHERE pengeluaran_nobukti = pengeluaranheader.nobukti   
              )")
            ->leftJoin('pengeluarandetail', 'pengeluaranheader.id', 'pengeluarandetail.pengeluaran_id')
            ->groupBy('pengeluaranheader.nobukti','pengeluaranheader.keterangan' ,'pengeluaranheader.tglbukti');
            $data = $query->get();
                
            return $data;
        }
    
    
}
