<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PengembalianKasGantungHeader extends MyModel
{
    use HasFactory;

    protected $table = 'pengembaliankasgantungheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];


    public function cekvalidasiaksi($nobukti)
    {

        $prosesUangJalan = DB::table('prosesuangjalansupirdetail')
            ->from(
                DB::raw("prosesuangjalansupirdetail as a with (readuncommitted)")
            )
            ->select(
                'a.pengembaliankasgantung_nobukti'
            )
            ->where('a.pengembaliankasgantung_nobukti', '=', $nobukti)
            ->first();
        if (isset($prosesUangJalan)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Proses Uang Jalan Supir',
                'kodeerror' => 'TDT'
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

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('bank', 255)->nullable();
        });


        $bank = DB::table('bank')->from(
            DB::raw('bank with (readuncommitted)')
        )
            ->select(
                'id as bank_id',
                'namabank as bank',

            )
            ->where('tipe', '=', 'KAS')
            ->first();

        DB::table($tempdefault)->insert(
            ["bank_id" => $bank->bank_id, "bank" => $bank->bank]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'bank_id',
                'bank',
            );

        $data = $query->first();

        return $data;
    }

    public function get()
    {
        $this->setRequestParameters();


        $query = DB::table($this->table)->select(
            'pengembaliankasgantungheader.id',
            'pengembaliankasgantungheader.nobukti',
            'pengembaliankasgantungheader.tglbukti',
            'pengembaliankasgantungheader.keterangan',
            'bank.namabank as bank',
            DB::raw('(case when (year(pengembaliankasgantungheader.tgldari) <= 2000) then null else pengembaliankasgantungheader.tgldari end ) as tgldari'),
            DB::raw('(case when (year(pengembaliankasgantungheader.tglsampai) <= 2000) then null else pengembaliankasgantungheader.tglsampai end ) as tglsampai'),
            'pengembaliankasgantungheader.penerimaan_nobukti',
            'akunpusat.keterangancoa as coa',
            'pengembaliankasgantungheader.postingdari',
            DB::raw('(case when (year(pengembaliankasgantungheader.tglkasmasuk) <= 2000) then null else pengembaliankasgantungheader.tglkasmasuk end ) as tglkasmasuk'),
            DB::raw('(case when (year(pengembaliankasgantungheader.tglbukacetak) <= 2000) then null else pengembaliankasgantungheader.tglbukacetak end ) as tglbukacetak'),
            'statuscetak.memo as statuscetak',
            'pengembaliankasgantungheader.userbukacetak',
            'pengembaliankasgantungheader.jumlahcetak',
            'pengembaliankasgantungheader.modifiedby',
            'pengembaliankasgantungheader.created_at',
            'pengembaliankasgantungheader.updated_at'

        )
            ->whereBetween('pengembaliankasgantungheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])

            ->leftJoin('akunpusat', 'pengembaliankasgantungheader.coakasmasuk', 'akunpusat.coa')
            ->leftJoin('bank', 'pengembaliankasgantungheader.bank_id', 'bank.id')
            ->leftJoin('parameter as statuscetak', 'pengembaliankasgantungheader.statuscetak', 'statuscetak.id');



        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }


    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('bank_id', 1000)->nullable();
            $table->date('tgldari')->nullable();
            $table->date('tglsampai')->nullable();
            $table->string('penerimaan_nobukti', 50)->nullable();
            $table->string('coakasmasuk', 50)->nullable();
            $table->string('postingdari', 50)->nullable();
            $table->date('tglkasmasuk')->nullable();
            $table->string('statusformat', 1000)->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->increments('position');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $query = DB::table($modelTable);
        $query = $this->select(
            "id",
            "nobukti",
            "tglbukti",
            "keterangan",
            "bank_id",
            "tgldari",
            "tglsampai",
            "penerimaan_nobukti",
            "coakasmasuk",
            "postingdari",
            "tglkasmasuk",
            "statusformat",
            "statuscetak",
            "userbukacetak",
            "tglbukacetak",
            "jumlahcetak",
            "modifiedby",
        );
        $query = $this->sort($query);
        $models = $this->filter($query);
        if (request()->tgldariheader) {
            $models =  $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        }
        DB::table($temp)->insertUsing([
            "id",
            "nobukti",
            "tglbukti",
            "keterangan",
            "bank_id",
            "tgldari",
            "tglsampai",
            "penerimaan_nobukti",
            "coakasmasuk",
            "postingdari",
            "tglkasmasuk",
            "statusformat",
            "statuscetak",
            "userbukacetak",
            "tglbukacetak",
            "jumlahcetak",
            "modifiedby",
        ], $models);
        return $temp;
    }

    public function selectColumns($query)
    {
        return $query->select(
            "$this->table.id",
            "$this->table.nobukti",
            "$this->table.tglbukti",
            "$this->table.keterangan",
            "$this->table.bank_id",
            "$this->table.tgldari",
            "$this->table.tglsampai",
            "$this->table.penerimaan_nobukti",
            "$this->table.coakasmasuk",
            "$this->table.postingdari",
            "$this->table.tglkasmasuk",
            "$this->table.statusformat",
            "$this->table.statuscetak",
            "$this->table.userbukacetak",
            "$this->table.tglbukacetak",
            "$this->table.jumlahcetak",
            "$this->table.modifiedby",
            "bank.namabank as bank",
            "akunpusat.coa as coa",
        );
    }


    public function getPengembalian($id)
    {
        $this->setRequestParameters();
        
        $query = DB::table('kasgantungheader')
            ->select(DB::raw("kasgantungheader.id as detail_id, sum(kasgantungdetail.nominal) as nominal,  kasgantungheader.id, kasgantungheader.nobukti, kasgantungheader.tglbukti, pengembaliankasgantungdetail.coa as coadetail, pengembaliankasgantungdetail.keterangan as keterangandetail, pengembaliankasgantungheader.id as pengembaliankasgantungheader_id"))
            ->whereRaw(" EXISTS (
                SELECT pengembaliankasgantungdetail.kasgantung_nobukti 
                FROM pengembaliankasgantungdetail with (readuncommitted)
                WHERE pengembaliankasgantungdetail.kasgantung_nobukti = kasgantungheader.nobukti
                
                and pengembaliankasgantungdetail.pengembaliankasgantung_id = " . $id . "
          )")
            ->whereRaw('pengembaliankasgantungdetail.kasgantung_nobukti = kasgantungheader.nobukti')
            ->whereRaw('pengembaliankasgantungdetail.pengembaliankasgantung_id = ' . $id)
            ->leftJoin('kasgantungdetail', 'kasgantungdetail.kasgantung_id', 'kasgantungheader.id')
            ->leftJoin('pengembaliankasgantungdetail', 'kasgantungheader.nobukti', 'pengembaliankasgantungdetail.kasgantung_nobukti')
            ->leftJoin('pengembaliankasgantungheader', 'pengembaliankasgantungdetail.pengembaliankasgantung_id', 'pengembaliankasgantungheader.id')
            ->groupBy(
                'kasgantungheader.id',
                'kasgantungheader.nobukti',
                'kasgantungheader.tglbukti',
                'pengembaliankasgantungdetail.coa',
                'pengembaliankasgantungdetail.keterangan',
                'pengembaliankasgantungheader.id',
            );
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }



    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'grp') {
            return $query
                ->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder'])
                ->orderBy($this->table . '.subgrp', $this->params['sortOrder'])
                ->orderBy($this->table . '.id', $this->params['sortOrder']);
        }

        if ($this->params['sortIndex'] == 'subgrp') {
            return $query
                ->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder'])
                ->orderBy($this->table . '.grp', $this->params['sortOrder'])
                ->orderBy($this->table . '.id', $this->params['sortOrder']);
        }
        if ($this->params['sortIndex'] == 'bank') {
            return $query->orderBy('bank.namabank', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'coa') {
            return $query->orderBy('akunpusat.keterangancoa', $this->params['sortOrder']);
        }
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statuscetak') {
                            $query = $query->where('statuscetak.text', '=', $filters['data']);
                        } 
                        else if ($filters['field'] == 'bank') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'coa') {
                            $query = $query->where('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglkasmasuk' || $filters['field'] == 'tgldari' || $filters['field'] == 'tglsampai' || $filters['field'] == 'tglbukacetak') {
                            $query = $query->whereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statuscetak') {
                                $query = $query->orWhere('statuscetak.text', '=', $filters['data']);
                            } 
                            else if ($filters['field'] == 'bank') {
                                $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coa') {
                                $query = $query->orWhere('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglkasmasuk' || $filters['field'] == 'tgldari' || $filters['field'] == 'tglsampai' || $filters['field'] == 'tglbukacetak') {
                                $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
            $query->where('pengembaliankasgantungheader.statuscetak', '<>', request()->cetak)
                ->whereYear('pengembaliankasgantungheader.tglbukti', '=', request()->year)
                ->whereMonth('pengembaliankasgantungheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }
    public function findAll($id)
    {
        $this->setRequestParameters();

        $query = PengembalianKasGantungHeader::from(DB::raw("pengembaliankasgantungheader with (readuncommitted)"));
        $query = $this->selectColumns($query)
            ->leftJoin('bank', 'pengembaliankasgantungheader.bank_id', 'bank.id')
            ->leftJoin('penerimaanheader', 'pengembaliankasgantungheader.penerimaan_nobukti', 'penerimaanheader.nobukti')
            ->leftJoin('akunpusat', 'pengembaliankasgantungheader.coakasmasuk', 'akunpusat.coa');

        $data = $query->where("$this->table.id", $id)->first();
        return $data;
    }
    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
