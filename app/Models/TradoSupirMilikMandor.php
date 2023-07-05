<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TradoSupirMilikMandor extends MyModel
{
    use HasFactory;

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $table = 'tradosupirmilikmandor';

    public function get()
    {
        $this->setRequestParameters();
        
        $query = TradoSupirMilikMandor::from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "tradosupirmilikmandor.id",
            "tradosupirmilikmandor.mandor_id",
            "tradosupirmilikmandor.supir_id",
            "tradosupirmilikmandor.trado_id",
            "mandor.namamandor as mandor",
            "supir.namasupir as supir",
            "trado.kodetrado as trado",
            "tradosupirmilikmandor.created_at",
            "tradosupirmilikmandor.updated_at",
        )->leftJoin('mandor', 'tradosupirmilikmandor.mandor_id', 'mandor.id')
        ->leftJoin('supir', 'tradosupirmilikmandor.supir_id', 'supir.id')
        ->leftJoin('trado', 'tradosupirmilikmandor.trado_id', 'trado.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function find($id)
    {
        $this->setRequestParameters();
        
        $query = TradoSupirMilikMandor::from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "tradosupirmilikmandor.id",
            "tradosupirmilikmandor.mandor_id",
            "tradosupirmilikmandor.supir_id",
            "tradosupirmilikmandor.trado_id",
            "mandor.namamandor as mandor",
            "supir.namasupir as supir",
            "trado.kodetrado as trado",
            "tradosupirmilikmandor.created_at",
            "tradosupirmilikmandor.updated_at",
        )->leftJoin('mandor', 'tradosupirmilikmandor.mandor_id', 'mandor.id')
        ->leftJoin('supir', 'tradosupirmilikmandor.supir_id', 'supir.id')
        ->leftJoin('trado', 'tradosupirmilikmandor.trado_id', 'trado.id')
        ->where('tradosupirmilikmandor.id',$id)
        ;

        

        $data = $query->first();

        return $data;
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
                        $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->unsignedBigInteger('mandor_id')->nullable();
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            // $table->string('modifiedby', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });


        $query = DB::table($modelTable);
        $query = TradoSupirMilikMandor::from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
                "tradosupirmilikmandor.id",
                "tradosupirmilikmandor.mandor_id",
                "tradosupirmilikmandor.supir_id",
                "tradosupirmilikmandor.trado_id",
                // "tradosupirmilikmandor.modifiedby",
                "tradosupirmilikmandor.created_at",
                "tradosupirmilikmandor.updated_at",
            );

        $query = $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing([
            'id',
            'mandor_id',
            'supir_id',
            'trado_id',
            // 'modifiedby',
            'created_at', 'updated_at'
        ], $models);

        return $temp;
    }

    public function processStore(array $data): TradoSupirMilikMandor
    {
        $tradoSupirMilikMandor = new TradoSupirMilikMandor();
        $tradoSupirMilikMandor->mandor_id = $data['mandor_id'];
        $tradoSupirMilikMandor->supir_id = $data['supir_id'];
        $tradoSupirMilikMandor->trado_id = $data['trado_id'];

        if (!$tradoSupirMilikMandor->save()) {
            throw new \Exception("Error store trado Supir Milik Mandor");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($tradoSupirMilikMandor->getTable()),
            'postingdari' => $data['postingdari'] ??strtoupper('ENTRY trado Supir Milik Mandor'),
            'idtrans' => $tradoSupirMilikMandor->id,
            'nobuktitrans' =>  $tradoSupirMilikMandor->id,
            'aksi' => 'ENTRY',
            'datajson' => $tradoSupirMilikMandor->toArray(),
            'modifiedby' => $tradoSupirMilikMandor->modifiedby
        ]);
        
        return $tradoSupirMilikMandor;
    }

    public function processUpdate(TradoSupirMilikMandor $tradoSupirMilikMandor ,array $data): TradoSupirMilikMandor
    {
        $tradoSupirMilikMandor->mandor_id = $data['mandor_id'];
        $tradoSupirMilikMandor->supir_id = $data['supir_id'];
        $tradoSupirMilikMandor->trado_id = $data['trado_id'];

        if (!$tradoSupirMilikMandor->save()) {
            throw new \Exception("Error update trado Supir Milik Mandor");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($tradoSupirMilikMandor->getTable()),
            'postingdari' => $data['postingdari'] ??strtoupper('EDIT trado Supir Milik Mandor'),
            'idtrans' => $tradoSupirMilikMandor->id,
            'nobuktitrans' =>  $tradoSupirMilikMandor->id,
            'aksi' => 'EDIT',
            'datajson' => $tradoSupirMilikMandor->toArray(),
            'modifiedby' => $tradoSupirMilikMandor->modifiedby
        ]);
        
        return $tradoSupirMilikMandor;
    }

    public function processDestroy($id,$postingdari =""): TradoSupirMilikMandor
    {
        $tradoSupirMilikMandor = TradoSupirMilikMandor::findOrFail($id);
        $dataHeader =  $tradoSupirMilikMandor->toArray();
      
        $tradoSupirMilikMandor = $tradoSupirMilikMandor->lockAndDestroy($id);
        $hutangLogTrail = (new LogTrail())->processStore([
            'namatabel' => $this->table,
            'postingdari' => ($postingdari =="") ? $postingdari :strtoupper('DELETE Black List Supir'),
            'idtrans' => $tradoSupirMilikMandor->id,
            'nobuktitrans' =>  $tradoSupirMilikMandor->id,
            'aksi' => 'DELETE',
            'datajson' => $tradoSupirMilikMandor->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);
        return $tradoSupirMilikMandor;
    }

}
