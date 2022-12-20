<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PenerimaanGiroHeader extends MyModel
{
    use HasFactory;

    protected $table = 'penerimaangiroheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get(){
        $this->setRequestParameters();

        $query = DB::table($this->table)->select(
            'penerimaangiroheader.id',
            'penerimaangiroheader.nobukti',
            'penerimaangiroheader.tglbukti',
            'penerimaangiroheader.keterangan',
            'pelanggan.namapelanggan as pelanggan_id',
            'penerimaangiroheader.postingdari',
            'penerimaangiroheader.diterimadari',
            'penerimaangiroheader.tgllunas',
            'statusapproval.memo as statusapproval',
            DB::raw('(case when (year(penerimaangiroheader.tglapproval) <= 2000) then null else penerimaangiroheader.tglapproval end ) as tglapproval'),
            'penerimaangiroheader.userapproval',
            'penerimaangiroheader.created_at',
            'statuscetak.memo as statuscetak',
            DB::raw('(case when (year(penerimaangiroheader.tglbukacetak) <= 2000) then null else penerimaangiroheader.tglbukacetak end ) as tglbukacetak'),
            'penerimaangiroheader.userbukacetak',
            'penerimaangiroheader.created_at',
            'penerimaangiroheader.modifiedby',
            'penerimaangiroheader.updated_at'
        )->leftJoin('pelanggan', 'penerimaangiroheader.pelanggan_id', 'pelanggan.id')
        ->leftJoin('parameter as statuscetak', 'penerimaangiroheader.statuscetak', 'statuscetak.id')
        ->leftJoin('parameter as statusapproval', 'penerimaangiroheader.statusapproval', 'statusapproval.id');
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
                "$this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            pelanggan.namapelanggan as pelanggan_id,
            $this->table.keterangan,
            $this->table.postingdari,
            $this->table.diterimadari,
            $this->table.tgllunas,
            statusapproval.text as statusapproval,
            $this->table.userapproval,
            $this->table.tglapproval,
            statuscetak.text as statuscetak,
            $this->table.userbukacetak,
            $this->table.tglbukacetak,
            $this->table.jumlahcetak,
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )
        )
            ->leftJoin('pelanggan', 'penerimaangiroheader.pelanggan_id', 'pelanggan.id')
            ->leftJoin('parameter as statuscetak', 'penerimaangiroheader.statuscetak', 'statuscetak.id')
            ->leftJoin('parameter as statusapproval', 'penerimaangiroheader.statusapproval', 'statusapproval.id');
    }

    public function findAll($id) 
    {
        $query = DB::table('penerimaangiroheader')->select(
            'penerimaangiroheader.id','penerimaangiroheader.nobukti','penerimaangiroheader.tglbukti','penerimaangiroheader.pelanggan_id','pelanggan.namapelanggan as pelanggan','penerimaangiroheader.keterangan','penerimaangiroheader.diterimadari','penerimaangiroheader.tgllunas','penerimaangiroheader.statuscetak'
        )->leftJoin('pelanggan','penerimaangiroheader.pelanggan_id','pelanggan.id')
        ->where('penerimaangiroheader.id',$id);
       
        $data = $query->first();

        return $data;
    }

    public function tarikPelunasan($id)
    {
        if($id != 'null'){
            $penerimaan = DB::table('penerimaangirodetail')->select('pelunasanpiutang_nobukti')->where('penerimaangiro_id',$id)->first();
            $data = DB::table('pelunasanpiutangheader')->select(DB::raw("pelunasanpiutangheader.id,pelunasanpiutangheader.nobukti,pelunasanpiutangheader.tglbukti, pelanggan.namapelanggan as pelanggan, (SELECT (SUM(pelunasanpiutangdetail.nominal)) FROM pelunasanpiutangdetail WHERE pelunasanpiutangdetail.nobukti= pelunasanpiutangheader.nobukti) AS nominal"))
            ->distinct("pelunasanpiutangheader.nobukti")
             ->join('pelunasanpiutangdetail','pelunasanpiutangheader.id','pelunasanpiutangdetail.pelunasanpiutang_id')
             ->join('pelanggan','pelunasanpiutangdetail.pelanggan_id','pelanggan.id')
     
             ->where('pelunasanpiutangheader.nobukti',$penerimaan->pelunasanpiutang_nobukti)
             ->get();

        }else{
            
            $data = DB::table('pelunasanpiutangheader')->select(DB::raw("pelunasanpiutangheader.id,pelunasanpiutangheader.nobukti,pelunasanpiutangheader.tglbukti, pelanggan.namapelanggan as pelanggan, (SELECT (SUM(pelunasanpiutangdetail.nominal)) FROM pelunasanpiutangdetail WHERE pelunasanpiutangdetail.nobukti= pelunasanpiutangheader.nobukti) AS nominal"))
            ->distinct("pelunasanpiutangheader.nobukti")
            ->join('pelunasanpiutangdetail','pelunasanpiutangheader.id','pelunasanpiutangdetail.pelunasanpiutang_id')
             ->join('pelanggan','pelunasanpiutangdetail.pelanggan_id','pelanggan.id')
            ->whereRaw("pelunasanpiutangheader.nobukti not in (select pelunasanpiutang_nobukti from penerimaangirodetail)")
            ->whereRaw("pelunasanpiutangheader.nobukti not in (select pelunasanpiutang_nobukti from penerimaandetail)")
             ->get();
     
        }
        
        return $data;

    }

    public function getPelunasan($id)
    {
        
       $data = DB::table('pelunasanpiutangdetail')->select('id','nominal','tgljt','keterangan','invoice_nobukti','nobukti')
        ->where('pelunasanpiutang_id',$id)
        ->get();

        return $data;

    }
    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti', 1000)->default('1900/1/1');
            $table->string('pelanggan_id', 1000)->default('');
            $table->string('keterangan', 3000)->default('');
            $table->string('postingdari', 1000)->default('');
            $table->string('diterimadari', 1000)->default('');
            $table->date('tgllunas', 1000)->default('1900/1/1');
            $table->string('statusapproval', 1000)->default('');
            $table->string('userapproval', 1000)->default('');
            $table->dateTime('tglapproval')->default('1900/1/1');
            $table->string('statuscetak', 1000)->nullable('');
            $table->string('userbukacetak', 1000)->default('');
            $table->dateTime('tglbukacetak')->default('1900/1/1');
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
        DB::table($temp)->insertUsing([
            'id', 'nobukti', 'tglbukti', 'pelanggan_id','keterangan', 'postingdari', 'diterimadari','tgllunas', 'statusapproval', 'userapproval', 'tglapproval','statuscetak', 'userbukacetak', 'tglbukacetak','jumlahcetak', 'modifiedby', 'created_at', 'updated_at'
        ], $models);


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
                            $query = $query->where('statusapproval.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statuscetak') {
                            $query = $query->where('statuscetak.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'pelanggan_id') {
                            $query = $query->where('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->orWhere('statusapproval.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statuscetak') {
                            $query = $query->orWhere('statuscetak.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'pelanggan_id') {
                            $query = $query->orWhere('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
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
            $query->where('penerimaangiroheader.statuscetak','<>', request()->cetak)
                  ->whereYear('penerimaangiroheader.tglbukti','=', request()->year)
                  ->whereMonth('penerimaangiroheader.tglbukti','=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

}
