<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AbsensiSupirHeader extends MyModel
{
    use HasFactory;

    protected $table = 'absensisupirheader';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'tgl' => 'date:d-m-Y',
    ];

    public function absensiSupirDetail()
    {
        return $this->hasMany(AbsensiSupirDetail::class, 'absensi_id');
    }

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("absensisupirheader with (readuncommitted)"))
        ->select(
            'absensisupirheader.id',
            'absensisupirheader.nobukti',
            'absensisupirheader.tglbukti',
            'absensisupirheader.keterangan',
            'absensisupirheader.kasgantung_nobukti',
            'absensisupirheader.nominal',
            DB::raw('(case when (year(absensisupirheader.tglbukacetak) <= 2000) then null else absensisupirheader.tglbukacetak end ) as tglbukacetak'),
            'statuscetak.memo as statuscetak',
            'absensisupirheader.userbukacetak',
            'absensisupirheader.jumlahcetak',
            'absensisupirheader.modifiedby',
            'absensisupirheader.created_at',
            'absensisupirheader.updated_at'
        )
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'absensisupirheader.statuscetak', 'statuscetak.id');

           

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);
        // dd('test');
        // dd($query);
        $data = $query->get();
        return $data;
    }

    public function findAll($id)
    {
        $query = DB::table('absensisupirheader')->from(DB::raw("absensisupirheader with (readuncommitted)"))
            ->select(
                'absensisupirheader.id',
                'absensisupirheader.nobukti',
                'absensisupirheader.kasgantung_nobukti',
                'absensisupirheader.tglbukti',
                'absensisupirheader.keterangan',
                'absensisupirheader.tglbukacetak',
                'absensisupirheader.statuscetak',
                'absensisupirheader.userbukacetak',
                'absensisupirheader.jumlahcetak',
    
            )
            ->where('id', $id);
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
            $this->table.keterangan,
            $this->table.kasgantung_nobukti,
            $this->table.nominal,
            'statuscetak.text as statuscetak',
            $this->table.userbukacetak,
            $this->table.tglbukacetak,
            $this->table.jumlahcetak,
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )
        )
        ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)") , 'absensisupirheader.statuscetak', 'statuscetak.id');
    }

    public function createTemp(string $modelTable)
    {

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->string('tglbukti', 1000)->default('');
            $table->string('keterangan', 1000)->default('');
            $table->string('kasgantung_nobukti', 1000)->default('');
            $table->string('nominal', 1000)->default('');
            $table->string('statuscetak',1000)->default('');
            $table->string('userbukacetak',50)->default('');
            $table->date('tglbukacetak')->default('1900/1/1');
            $table->integer('jumlahcetak')->Length(11)->default('0');
            $table->string('modifiedby', 1000)->default('');
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
            'id',
            'nobukti',
            'tglbukti',
            'keterangan',
            'kasgantung_nobukti',
            'nominal',
            'statuscetak',
            'userbukacetak',
            'tglbukacetak',
            'jumlahcetak',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $models);

        return $temp;
    }

    public function getAbsensi($id)
    {
        $query = DB::table('absensisupirdetail')->from(DB::raw("absensisupirdetail with (readuncommitted)"))
            ->select(
                'absensisupirdetail.keterangan as keterangan_detail',
                'absensisupirdetail.jam',
                'absensisupirdetail.uangjalan',
                'absensisupirdetail.absensi_id',
                'absensisupirdetail.id',
                'trado.keterangan as trado',
                'supirutama.namasupir as supir',
                'trado.id as trado_id',
                'supirutama.id as supir_id',
                'absensisupirheader.kasgantung_nobukti',
            )
            ->leftJoin(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirdetail.absensi_id', 'absensisupirheader.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'absensisupirdetail.trado_id', 'trado.id')
            ->leftJoin(DB::raw("supir as supirutama with (readuncommitted)"), 'absensisupirdetail.supir_id', 'supirutama.id')
            ->whereRaw("not EXISTS (
            SELECT absensisupirapprovalheader.absensisupir_nobukti
    FROM absensisupirdetail          
    left join absensisupirapprovalheader on absensisupirapprovalheader.absensisupir_nobukti= absensisupirdetail.nobukti
    WHERE absensisupirapprovalheader.absensisupir_nobukti = absensisupirheader.nobukti 
          )")
            ->where('absensi_id', $id);
        $data = $query->get();

        return $data;
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
                        $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
