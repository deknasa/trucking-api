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

        
        $piutang = DB::table($tempPiutang)
            ->select(DB::raw("null as pelunasanpiutang_id,$tempPiutang.nobukti as piutang_nobukti, null as tglbayar, $tempPiutang.tglbukti as tglbukti, $tempPiutang.agen_id as agen_id,null as nominal, null as keterangan, null as penyesuaian, null as keteranganpenyesuaian, null as nominallebihbayar, $tempPiutang.nominalpiutang, $tempPiutang.sisa"))
            ->distinct("$tempPiutang.nobukti")
            ->leftJoin($tempPelunasan,"$tempPiutang.agen_id","$tempPelunasan.agen_id")
            ->whereRaw("$tempPiutang.nobukti != $tempPelunasan.piutang_nobukti");
            // ->whereRaw("$tempPiutang.sisa is null")
            // ->orWhereRaw("$tempPiutang.sisa != $tempPelunasan.sisa");
            // ->where(function ($piutang) use ($tempPiutang,$tempPelunasan) {
            //     $piutang->whereRaw("$tempPiutang.sisa = $tempPelunasan.sisa")
            //           ->orWhereRaw("$tempPiutang.sisa is null");
            // });

        $pelunasan = DB::table($tempPelunasan)
            ->select(DB::raw("pelunasanpiutang_id,piutang_nobukti,tglbayar,tglbukti,agen_id,nominal,keterangan,penyesuaian,keteranganpenyesuaian,nominallebihbayar,nominalpiutang,sisa"))
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
        $temp = '##temp' . rand(1, 10000);


        $fetch = DB::table('piutangheader')
        ->select(DB::raw("piutangheader.nobukti,piutangheader.tglbukti,piutangheader.agen_id,piutangheader.nominal as nominalpiutang, (SELECT (piutangheader.nominal - SUM(pelunasanpiutangdetail.nominal)) FROM pelunasanpiutangdetail WHERE pelunasanpiutangdetail.piutang_nobukti= piutangheader.nobukti) AS sisa"))
        ->leftJoin('pelunasanpiutangdetail','pelunasanpiutangdetail.agen_id','piutangheader.agen_id')
        ->whereRaw("piutangheader.agen_id = $agenid")
        ->groupBy('piutangheader.nobukti','piutangheader.agen_id','piutangheader.nominal','piutangheader.tglbukti');
               
        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->date('tglbukti')->default('');
            $table->bigInteger('agen_id')->default('0');
            $table->bigInteger('nominalpiutang');
            $table->bigInteger('sisa')->nullable();
            
        });
    
        $tes = DB::table($temp)->insertUsing(['nobukti','tglbukti','agen_id','nominalpiutang','sisa'], $fetch);
       
        return $temp;
    }

    public function createTempPelunasan($id,$agenid) {
        $tempo = '##tempo' . rand(1, 10000);
        
        $fetch = DB::table('pelunasanpiutangdetail as ppd')
        ->select(DB::raw("ppd.pelunasanpiutang_id,ppd.piutang_nobukti,ppd.tgl as tglbayar,piutangheader.tglbukti,ppd.agen_id,ppd.nominal,ppd.keterangan,ppd.penyesuaian,ppd.keteranganpenyesuaian,ppd.nominallebihbayar, piutangheader.nominal as nominalpiutang, (SELECT (piutangheader.nominal - SUM(pelunasanpiutangdetail.nominal)) FROM pelunasanpiutangdetail WHERE pelunasanpiutangdetail.piutang_nobukti= piutangheader.nobukti) AS sisa"))
        ->leftJoin('piutangheader','ppd.piutang_nobukti','piutangheader.nobukti')
        ->whereRaw("ppd.pelunasanpiutang_id = $id");
               
        Schema::create($tempo, function ($table) {
            $table->bigInteger('pelunasanpiutang_id')->default('0');
            $table->string('piutang_nobukti');
            $table->date('tglbayar')->default('');
            $table->date('tglbukti')->default('');
            $table->bigInteger('agen_id')->default('0');
            $table->bigInteger('nominal')->nullable();
            $table->string('keterangan');
            $table->bigInteger('penyesuaian')->default('0');
            $table->string('keteranganpenyesuaian');
            $table->bigInteger('nominallebihbayar')->default('0');
            $table->bigInteger('nominalpiutang');
            $table->bigInteger('sisa')->nullable();
        });
    
        $tes = DB::table($tempo)->insertUsing(['pelunasanpiutang_id','piutang_nobukti','tglbayar','tglbukti','agen_id','nominal','keterangan','penyesuaian','keteranganpenyesuaian','nominallebihbayar','nominalpiutang','sisa'], $fetch);
        
        return $tempo;
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
            DB::raw(
                "$this->table.id,
                 $this->table.nobukti,
                 $this->table.tglbukti,
                 $this->table.keterangan,
                 $this->table.bank_id,
                 $this->table.agen_id,
                 $this->table.cabang_id,
                 $this->table.modifiedby,
                 $this->table.updated_at"
            )
        );
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table){
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti')->default('');
            $table->string('keterangan', 1000)->default('');
            $table->bigInteger('bank_id')->default('');
            $table->bigInteger('agen_id')->default('');
            $table->bigInteger('cabang_id')->default('');
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
