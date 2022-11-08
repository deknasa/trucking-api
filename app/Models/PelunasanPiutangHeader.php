<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class PelunasanPiutangHeader extends MyModel
{
    use HasFactory;

    protected $table = 'pelunasanpiutangheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get() {
        $this->setRequestParameters();

        $query = DB::table($this->table)->select(
            'pelunasanpiutangheader.id',
            'pelunasanpiutangheader.nobukti',
            'pelunasanpiutangheader.tglbukti',
            'pelunasanpiutangheader.keterangan',
            'pelunasanpiutangheader.modifiedby',
            'pelunasanpiutangheader.updated_at',

            'bank.namabank as bank_id',
            'agen.namaagen as agen_id',
            'cabang.namacabang as cabang_id',
        )
            ->leftJoin('bank', 'pelunasanpiutangheader.bank_id', 'bank.id')
            ->leftJoin('agen', 'pelunasanpiutangheader.agen_id', 'agen.id')
            ->leftJoin('cabang' , 'pelunasanpiutangheader.cabang_id', 'cabang.id');
            
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function getPelunasanPiutang($id,$agenid)
    {
        $this->setRequestParameters();
      
        $tempPiutang = $this->createTempPiutang($id,$agenid);
        $tempPelunasan = $this->createTempPelunasan($id,$agenid);

        
        $piutang = DB::table("$tempPiutang as A")
            ->select(DB::raw("A.id as id,null as pelunasanpiutang_id,A.nobukti as piutang_nobukti, A.tglbukti as tglbukti, A.agen_id as agen_id, A.invoice_nobukti as invoice_nobukti,null as nominal, null as keterangan, null as penyesuaian, null as keteranganpenyesuaian, null as nominallebihbayar, A.nominalpiutang, A.sisa as sisa"))
            ->distinct("A.nobukti")
            ->leftJoin("$tempPelunasan as B","A.nobukti","B.piutang_nobukti")
            ->whereRaw("isnull(b.piutang_nobukti,'') = ''")
            ->whereRaw("a.sisa > 0");
           

        $pelunasan = DB::table($tempPelunasan)
            ->select(DB::raw("id,pelunasanpiutang_id,piutang_nobukti,tglbukti,agen_id,invoice_nobukti,nominal,keterangan,penyesuaian,keteranganpenyesuaian,nominallebihbayar,nominalpiutang,sisa"))
            ->unionAll($piutang);
        
        // $this->totalRows = $pelunasan->count();
        // $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        // $this->sort($pelunasan);
        // $this->filter($pelunasan);
        // $this->paginate($pelunasan);
       
        $data = $pelunasan->get();

        return $data;
    }
    
    public function createTempPiutang($id,$agenid) {
        $temp = '##tempPiutang' . rand(1, 10000);


        $fetch = DB::table('piutangheader')
        ->select(DB::raw("piutangheader.id,piutangheader.nobukti,piutangheader.tglbukti,piutangheader.agen_id,piutangheader.nominal as nominalpiutang,piutangheader.invoice_nobukti, (SELECT (piutangheader.nominal - COALESCE(SUM(pelunasanpiutangdetail.nominal),0)) FROM pelunasanpiutangdetail WHERE pelunasanpiutangdetail.piutang_nobukti= piutangheader.nobukti) AS sisa"))
        ->leftJoin('pelunasanpiutangdetail','pelunasanpiutangdetail.agen_id','piutangheader.agen_id')
        ->whereRaw("piutangheader.agen_id = $agenid")
        ->groupBy('piutangheader.id','piutangheader.nobukti','piutangheader.agen_id','piutangheader.nominal','piutangheader.tglbukti','piutangheader.invoice_nobukti');
               
        Schema::create($temp, function ($table) {
            $table->bigInteger('id');
            $table->string('nobukti');
            $table->date('tglbukti')->default('');
            $table->bigInteger('agen_id')->default('0');
            $table->bigInteger('nominalpiutang');
            $table->string('invoice_nobukti');
            $table->bigInteger('sisa')->nullable();
            
        });
    
        $tes = DB::table($temp)->insertUsing(['id','nobukti','tglbukti','agen_id','nominalpiutang','invoice_nobukti','sisa'], $fetch);
       
        return $temp;
    }

    public function createTempPelunasan($id,$agenid) {
        $tempo = '##tempPelunasan' . rand(1, 10000);
        
        $fetch = DB::table('pelunasanpiutangdetail as ppd')
        ->select(DB::raw("piutangheader.id,ppd.pelunasanpiutang_id,ppd.piutang_nobukti,piutangheader.tglbukti,ppd.agen_id,ppd.nominal,ppd.keterangan,ppd.penyesuaian,ppd.keteranganpenyesuaian,ppd.nominallebihbayar, piutangheader.nominal as nominalpiutang,ppd.invoice_nobukti, (SELECT (piutangheader.nominal - SUM(pelunasanpiutangdetail.nominal)) FROM pelunasanpiutangdetail WHERE pelunasanpiutangdetail.piutang_nobukti= piutangheader.nobukti) AS sisa"))
        ->leftJoin('piutangheader','ppd.piutang_nobukti','piutangheader.nobukti')
        ->whereRaw("ppd.pelunasanpiutang_id = $id");
               
        Schema::create($tempo, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->bigInteger('pelunasanpiutang_id')->default('0');
            $table->string('piutang_nobukti');
            $table->date('tglbukti')->default('');
            $table->bigInteger('agen_id')->default('0');
            $table->bigInteger('nominal')->nullable();
            $table->string('keterangan');
            $table->bigInteger('penyesuaian')->default('0');
            $table->string('keteranganpenyesuaian');
            $table->bigInteger('nominallebihbayar')->default('0');
            $table->bigInteger('nominalpiutang');
            $table->string('invoice_nobukti');
            $table->bigInteger('sisa')->nullable();
        });
    
        $tes = DB::table($tempo)->insertUsing(['id','pelunasanpiutang_id','piutang_nobukti','tglbukti','agen_id','nominal','keterangan','penyesuaian','keteranganpenyesuaian','nominallebihbayar','nominalpiutang','invoice_nobukti','sisa'], $fetch);
        
        return $tempo;
    }

    public function getDeletePelunasanPiutang($id, $agenId) {
       
        $query = DB::table('pelunasanpiutangdetail')
            ->select(
                DB::raw("
                pelunasanpiutangdetail.pelunasanpiutang_id, 
                pelunasanpiutangdetail.piutang_nobukti, 
                piutangheader.tglbukti, 
                piutangheader.nominal as nominalpiutang, 
                pelunasanpiutangdetail.nominal as nominal, 
                pelunasanpiutangdetail.invoice_nobukti, 
                pelunasanpiutangdetail.keterangan, pelunasanpiutangdetail.penyesuaian,
                pelunasanpiutangdetail.coapenyesuaian, pelunasanpiutangdetail.keteranganpenyesuaian, pelunasanpiutangdetail.nominallebihbayar, 
                pelunasanpiutangdetail.coalebihbayar,
                (SELECT (piutangheader.nominal - SUM(pelunasanpiutangdetail.nominal))
                    FROM pelunasanpiutangdetail 
                    WHERE pelunasanpiutangdetail.piutang_nobukti= piutangheader.nobukti) AS sisa")
            )
        ->join('piutangheader','pelunasanpiutangdetail.piutang_nobukti','piutangheader.nobukti')
        ->whereRaw("pelunasanpiutangdetail.pelunasanpiutang_id = $id")
        ->whereRaw("pelunasanpiutangdetail.agen_id = $agenId");
        
        $data = $query->get();
        return $data;
    }

    public function getPelunasanNotaKredit($id)
    {
        $this->setRequestParameters();

        $query = DB::table('pelunasanpiutangdetail')
        ->select(DB::raw('
        pelunasanpiutangdetail.id as detail_id,
        pelunasanpiutangdetail.nobukti,
        pelunasanpiutangdetail.tglcair,
        pelunasanpiutangdetail.nominal as nominalbayar,
        pelunasanpiutangdetail.nominal as nominal,
        pelunasanpiutangdetail.piutang_nobukti,
        pelunasanpiutangdetail.invoice_nobukti,
        pelunasanpiutangdetail.keterangan,
        pelunasanpiutangdetail.coapenyesuaian,
        COALESCE (pelunasanpiutangdetail.penyesuaian, 0) as penyesuaian '))

        ->leftJoin('piutangheader','piutangheader.nobukti','pelunasanpiutangdetail.piutang_nobukti')
        ->leftJoin('pelanggan', 'pelunasanpiutangdetail.pelanggan_id', 'pelanggan.id')
        ->leftJoin('agen', 'pelunasanpiutangdetail.agen_id', 'agen.id')
        ->whereRaw(" NOT EXISTS (
            SELECT notakreditheader.pelunasanpiutang_nobukti
            FROM notakreditdetail
			left join notakreditheader on notakreditdetail.notakredit_id = notakreditheader.id
            WHERE notakreditheader.pelunasanpiutang_nobukti = pelunasanpiutangdetail.nobukti   
          )")
        ->where('pelunasanpiutangdetail.penyesuaian', '>', 0)
        ->where('pelunasanpiutangdetail.pelunasanpiutang_id' , $id);
        
            
       

        $data = $query->get();

        return $data;
    }

    public function getPelunasanNotaDebet($id)
    {
        $this->setRequestParameters();

        $query = DB::table('pelunasanpiutangdetail')
        ->select(DB::raw('
        pelunasanpiutangdetail.id as detail_id,
        pelunasanpiutangdetail.nobukti,
        pelunasanpiutangdetail.tglcair,
        pelunasanpiutangdetail.nominal as nominalbayar,
        pelunasanpiutangdetail.nominal as nominal,
        pelunasanpiutangdetail.piutang_nobukti,
        pelunasanpiutangdetail.invoice_nobukti,
        pelunasanpiutangdetail.keterangan,
        pelunasanpiutangdetail.coalebihbayar,
        COALESCE (pelunasanpiutangdetail.nominallebihbayar, 0) as lebihbayar '))

        ->leftJoin('piutangheader','piutangheader.nobukti','pelunasanpiutangdetail.piutang_nobukti')
        ->leftJoin('pelanggan', 'pelunasanpiutangdetail.pelanggan_id', 'pelanggan.id')
        ->leftJoin('agen', 'pelunasanpiutangdetail.agen_id', 'agen.id')
        ->whereRaw(" NOT EXISTS (
            SELECT notadebetheader.pelunasanpiutang_nobukti
            FROM notadebetdetail
			left join notadebetheader on notadebetdetail.notadebet_id = notadebetheader.id
            WHERE notadebetheader.pelunasanpiutang_nobukti = pelunasanpiutangdetail.nobukti   
          )")
        ->where('pelunasanpiutangdetail.nominallebihbayar', '>', 0)
        ->where('pelunasanpiutangdetail.pelunasanpiutang_id' , $id);
        
            
       

        $data = $query->get();

        return $data;
    }

    public function findAll($id) {
      
        $query = DB::table('pelunasanpiutangheader')->select(
            'pelunasanpiutangheader.id',
            'pelunasanpiutangheader.nobukti',
            'pelunasanpiutangheader.tglbukti',
            'pelunasanpiutangheader.keterangan',
            'pelunasanpiutangheader.bank_id',
            'pelunasanpiutangheader.agen_id',
            'pelunasanpiutangheader.cabang_id',

            'bank.namabank as bank',
            'agen.namaagen as agen',
            'cabang.namacabang as cabang',
        )
            ->leftJoin('bank', 'pelunasanpiutangheader.bank_id', 'bank.id')
            ->leftJoin('agen', 'pelunasanpiutangheader.agen_id', 'agen.id')
            ->leftJoin('cabang' , 'pelunasanpiutangheader.cabang_id', 'cabang.id')
            ->where('pelunasanpiutangheader.id', $id);

        $data = $query->first();

        return $data;
    }

    
    public function pelunasanpiutangdetail() {
        return $this->hasMany(PelunasanPiutangDetail::class, 'pelunasanpiutang_id');
    }

    public function selectColumns($query)
    {
        return $query->select(
            DB::raw("
            $this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            $this->table.keterangan,
            'bank.namabank as bank_id',
            'agen.namaagen as agen_id',
            'cabang.namacabang as cabang_id',
            $this->table.modifiedby,
            $this->table.updated_at
            ")
        )
            ->leftJoin('bank', 'pelunasanpiutangheader.bank_id', 'bank.id')
            ->leftJoin('agen', 'pelunasanpiutangheader.agen_id', 'agen.id')
            ->leftJoin('cabang' , 'pelunasanpiutangheader.cabang_id', 'cabang.id');
            
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table){
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti')->default('');
            $table->string('keterangan', 1000)->default('');
            $table->string('bank_id')->default('');
            $table->string('agen_id')->default('');
            $table->string('cabang_id')->default('');
            $table->string('modifiedby')->default();
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id','nobukti','tglbukti','keterangan','bank_id','agen_id','cabang_id','modifiedby','updated_at'], $models);

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
                        if ($filters['field'] == 'agen_id') {
                            $query = $query->where('agen.namaagen', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'cabang_id') {
                            $query = $query->where('cabang.namacabang', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'agen_id') {
                            $query = $query->orWhere('agen.namaagen', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'cabang_id') {
                            $query = $query->orWhere('cabang.namacabang', 'LIKE', "%$filters[data]%");
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

        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

}
