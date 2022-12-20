<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PengembalianKasBankHeader extends MyModel
{
    use HasFactory;

    protected $table = 'PengembalianKasBankHeader';

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
            'pengembaliankasbankheader.id',
            'pengembaliankasbankheader.nobukti',
            'pengembaliankasbankheader.tglbukti',
            'pengembaliankasbankheader.pengeluaran_nobukti',

            'pengembaliankasbankheader.keterangan',
            'pengembaliankasbankheader.postingdari',
            'pengembaliankasbankheader.dibayarke',
            'cabang.namacabang as cabang',
            'pengembaliankasbankheader.cabang_id',
            'bank.namabank as bank',
            'pengembaliankasbankheader.bank_id',
            
            'statusjenistransaksi.text as statusjenistransaksi',
            'statusapproval.text as statusapproval',
            'statuscetak.memo as statuscetak',
            'pengembaliankasbankheader.tglapproval',
            'pengembaliankasbankheader.userapproval',
            'pengembaliankasbankheader.transferkeac',
            'pengembaliankasbankheader.transferkean',
            'pengembaliankasbankheader.transferkebank',

            'pengembaliankasbankheader.modifiedby',
            'pengembaliankasbankheader.created_at',
            'pengembaliankasbankheader.updated_at'

        )
        ->leftJoin('cabang', 'pengembaliankasbankheader.cabang_id', 'cabang.id')
        ->leftJoin('bank', 'pengembaliankasbankheader.bank_id', 'bank.id')
        ->leftJoin('parameter as statusapproval' , 'pengembaliankasbankheader.statusapproval', 'statusapproval.id')
        ->leftJoin('parameter as statuscetak' , 'pengembaliankasbankheader.statuscetak', 'statuscetak.id')
        ->leftJoin('parameter as statusjenistransaksi' , 'pengembaliankasbankheader.statusjenistransaksi', 'statusjenistransaksi.id');

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
        $query = DB::table('pengembaliankasbankheader')->select(
            'pengembaliankasbankheader.id',
            'pengembaliankasbankheader.nobukti',
            'pengembaliankasbankheader.tglbukti',
            'pengembaliankasbankheader.pengeluaran_nobukti',

            'pengembaliankasbankheader.keterangan',
            'pengembaliankasbankheader.postingdari',
            'pengembaliankasbankheader.dibayarke',
            'cabang.namacabang as cabang',
            'pengembaliankasbankheader.cabang_id',
            'bank.namabank as bank',
            'pengembaliankasbankheader.bank_id',
            
            'pengembaliankasbankheader.statusjenistransaksi',
            'statusapproval.text as statusapproval',
            'pengembaliankasbankheader.tglapproval',
            'pengembaliankasbankheader.userapproval',
            'pengembaliankasbankheader.transferkeac',
            'pengembaliankasbankheader.transferkean',
            'pengembaliankasbankheader.transferkebank',

            'pengembaliankasbankheader.modifiedby',
            'pengembaliankasbankheader.created_at',
            'pengembaliankasbankheader.updated_at'

        )
        ->leftJoin('cabang', 'pengembaliankasbankheader.cabang_id', 'cabang.id')
        ->leftJoin('bank', 'pengembaliankasbankheader.bank_id', 'bank.id')
        ->leftJoin('parameter as statusapproval' , 'pengembaliankasbankheader.statusapproval', 'statusapproval.id')
        ->leftJoin('parameter as statusjenistransaksi' , 'pengembaliankasbankheader.statusjenistransaksi', 'statusjenistransaksi.id')

        ->where('pengembaliankasbankheader.id',$id);

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
                 $this->table.pengeluaran_nobukti,
                 $this->table.keterangan,
                 $this->table.postingdari,
                 $this->table.dibayarke,
                 'cabang.namacabang as cabang_id',
                 'bank.namabank as bank_id',
                 'statusjenistransaksi.text as statusjenistransaksi',
                 'statusapproval.text as statusapproval',
                 'statuscetak.memo as statuscetak',
                 $this->table.transferkeac,
                 $this->table.transferkean,
                 $this->table.transferkebank,
                 $this->table.modifiedby,
                 $this->table.created_at,
                 $this->table.updated_at"
            )
        )
        // ->leftJoin('pengeluaran', 'pengembaliankasbankheader.pengeluaran_nobukti', 'pengeluaranheader.nobukti')
        ->leftJoin('cabang', 'pengembaliankasbankheader.cabang_id', 'cabang.id')
        ->leftJoin('bank', 'pengembaliankasbankheader.bank_id', 'bank.id')
        ->leftJoin('parameter as statusapproval' , 'pengembaliankasbankheader.statusapproval', 'statusapproval.id')
        ->leftJoin('parameter as statuscetak' , 'pengembaliankasbankheader.statuscetak', 'statuscetak.id')
        ->leftJoin('parameter as statusjenistransaksi' , 'pengembaliankasbankheader.statusjenistransaksi', 'statusjenistransaksi.id');

    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti')->default('');
            $table->string('pengeluaran_nobukti', 1000)->default('');
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
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti','pengeluaran_nobukti', 'keterangan', 'postingdari', 'dibayarke', 'cabang_id', 'bank_id','statusjenistransaksi','statusapproval','transferkeac','transferkean','transferkebank', 'modifiedby','created_at', 'updated_at'], $models);

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
                            // } else if ($filters['field'] == 'pelanggan_id') {
                            //     $query = $query->where('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
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
                            // }else if ($filters['field'] == 'pelanggan_id') {
                            //     $query = $query->orWhere('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
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
            if (request()->cetak && request()->periode) {
                $query->where('pengembaliankasbankheader.statuscetak','<>', request()->cetak)
                      ->whereYear('pengembaliankasbankheader.tglbukti','=', request()->year)
                      ->whereMonth('pengembaliankasbankheader.tglbukti','=', request()->month);
                return $query;
            }
            return $query;
        }
    
        public function paginate($query)
        {
            return $query->skip($this->params['offset'])->take($this->params['limit']);
        }

}
