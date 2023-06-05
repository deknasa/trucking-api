<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PengembalianKasBankHeader extends MyModel
{
    use HasFactory;

    protected $table = 'pengembaliankasbankheader';

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

            'pengembaliankasbankheader.postingdari',
            'pengembaliankasbankheader.dibayarke',
            'cabang.namacabang as cabang_id',
            'bank.namabank as bank_id',

            'statusjenistransaksi.text as statusjenistransaksi',
            'statusapproval.memo as statusapproval',
            'statuscetak.memo as statuscetak',
            DB::raw("(case when year(isnull($this->table.tglapproval,'1900/1/1'))<2000 then null else $this->table.tglapproval end) as tglapproval"),
            'pengembaliankasbankheader.userapproval',
            DB::raw("(case when year(isnull($this->table.tglbukacetak,'1900/1/1'))<2000 then null else $this->table.tglbukacetak end) as tglbukacetak"),
            'pengembaliankasbankheader.userbukacetak',
            'pengembaliankasbankheader.transferkeac',
            'pengembaliankasbankheader.transferkean',
            'pengembaliankasbankheader.transferkebank',

            'pengembaliankasbankheader.modifiedby',
            'pengembaliankasbankheader.created_at',
            'pengembaliankasbankheader.updated_at'

        )

            ->leftJoin('cabang', 'pengembaliankasbankheader.cabang_id', 'cabang.id')
            ->leftJoin('bank', 'pengembaliankasbankheader.bank_id', 'bank.id')
            ->leftJoin('parameter as statusapproval', 'pengembaliankasbankheader.statusapproval', 'statusapproval.id')
            ->leftJoin('parameter as statuscetak', 'pengembaliankasbankheader.statuscetak', 'statuscetak.id')
            ->leftJoin('parameter as statusjenistransaksi', 'pengembaliankasbankheader.statusjenistransaksi', 'statusjenistransaksi.id');
        if (request()->tgldari) {
            $query->whereBetween('tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
                ->where('pengembaliankasbankheader.bank_id', request()->bank_id);
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }
    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('bank', 255)->nullable();
            $table->unsignedBigInteger('alatbayar_id')->nullable();
            $table->string('alatbayar', 255)->nullable();
        });


        $bank = DB::table('bank')->from(
            DB::raw('bank with (readuncommitted)')
        )
            ->select(
                'id as bank_id',
                'namabank as bank',
                'statusdefault'

            )
            ->where('tipe', '=', 'BANK')
            ->first();


        $alatbayar = DB::table('alatbayar')->from(
            DB::raw('alatbayar with (readuncommitted)')
        )
            ->select(
                'id as alatbayar_id',
                'namaalatbayar as alatbayar',

            )
            ->where('statusdefault', '=', $bank->statusdefault)
            ->first();


        DB::table($tempdefault)->insert(
            ["bank_id" => $bank->bank_id, "bank" => $bank->bank, "alatbayar_id" => $alatbayar->alatbayar_id, "alatbayar" => $alatbayar->alatbayar]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'bank_id',
                'bank',
                'alatbayar_id',
                'alatbayar',
            );

        $data = $query->first();

        return $data;
    }

    public function findAll($id)
    {
        $query = DB::table('pengembaliankasbankheader')->select(
            'pengembaliankasbankheader.id',
            'pengembaliankasbankheader.nobukti',
            'pengembaliankasbankheader.tglbukti',
            'pengembaliankasbankheader.pengeluaran_nobukti',

            'pengembaliankasbankheader.postingdari',
            'pengembaliankasbankheader.dibayarke',
            'alatbayar.namaalatbayar as alatbayar',
            'pengembaliankasbankheader.alatbayar_id',
            'cabang.namacabang as cabang',
            'pengembaliankasbankheader.cabang_id',
            'bank.namabank as bank',
            'pengembaliankasbankheader.bank_id',

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
            ->leftJoin('alatbayar', 'pengembaliankasbankheader.alatbayar_id', 'alatbayar.id')
            ->leftJoin('bank', 'pengembaliankasbankheader.bank_id', 'bank.id')
            ->where('pengembaliankasbankheader.id', $id);

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
            ->leftJoin('parameter as statusapproval', 'pengembaliankasbankheader.statusapproval', 'statusapproval.id')
            ->leftJoin('parameter as statuscetak', 'pengembaliankasbankheader.statuscetak', 'statuscetak.id')
            ->leftJoin('parameter as statusjenistransaksi', 'pengembaliankasbankheader.statusjenistransaksi', 'statusjenistransaksi.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('pengeluaran_nobukti', 1000)->nullable();
            $table->string('postingdari', 1000)->nullable();
            $table->string('dibayarke', 1000)->nullable();
            $table->bigInteger('cabang_id')->nullable();
            $table->bigInteger('bank_id')->nullable();
            $table->bigInteger('statusjenistransaksi')->nullable();
            $table->bigInteger('statusapproval')->nullable();
            $table->string('transferkeac')->nullable();
            $table->string('transferkean')->nullable();
            $table->string('transferkebank')->nullable();
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });
        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->select(
            'id',
            'nobukti',
            'tglbukti',
            'pengeluaran_nobukti',
            'postingdari',
            'dibayarke',
            'cabang_id',
            'bank_id',
            'statusjenistransaksi',
            'statusapproval',
            'transferkeac',
            'transferkean',
            'transferkebank',
            'modifiedby',
            'created_at',
            'updated_at'
        );

        $this->sort($query);
        $models = $this->filter($query);
        if (request()->tgldariheader) {
            $models =  $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))])->where($this->table . '.bank_id', request()->bankheader);
        }

        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'pengeluaran_nobukti',
            'postingdari',
            'dibayarke',
            'cabang_id',
            'bank_id',
            'statusjenistransaksi',
            'statusapproval',
            'transferkeac',
            'transferkean',
            'transferkebank',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $models);

        return $temp;
    }
    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'bank_id') {
            return $query->orderBy('bank.namabank', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'cabang_id') {
            return $query->orderBy('cabang.namacabang', $this->params['sortOrder']);
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
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('statusapproval.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statuscetak') {
                            $query = $query->where('statuscetak.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statusjenistransaksi') {
                            $query = $query->where('statusjenistransaksi.text', '=', "$filters[data]");
                            // } else if ($filters['field'] == 'pelanggan_id') {
                            //     $query = $query->where('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'cabang_id') {
                            $query = $query->where('cabang.namacabang', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . "." .  $filters['field'] . " LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusapproval') {
                                $query = $query->orWhere('statusapproval.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statuscetak') {
                                $query = $query->orWhere('statuscetak.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusjenistransaksi') {
                                $query = $query->orWhere('statusjenistransaksi.text', '=', "$filters[data]");
                                // }else if ($filters['field'] == 'pelanggan_id') {
                                //     $query = $query->orWhere('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'cabang_id') {
                                $query = $query->orWhere('cabang.namacabang', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'bank_id') {
                                $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw($this->table . "." .  $filters['field'] . " LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

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
            $query->where('pengembaliankasbankheader.statuscetak', '<>', request()->cetak)
                ->whereYear('pengembaliankasbankheader.tglbukti', '=', request()->year)
                ->whereMonth('pengembaliankasbankheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
