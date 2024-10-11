<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BlackListSupir extends MyModel
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

    protected $table = 'blacklistsupir';

    public function get()
    {
        $this->setRequestParameters();

        $query = BlackListSupir::from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "blacklistsupir.id",
            "blacklistsupir.namasupir",
            "blacklistsupir.noktp",
            "blacklistsupir.nosim",
            // "blacklistsupir.modifiedby",
            "blacklistsupir.created_at",
            "blacklistsupir.updated_at",
        );



        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

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
            $table->string('namasupir')->nullable();
            $table->string('noktp')->nullable();
            $table->string('nosim')->nullable();
            // $table->string('modifiedby', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });


        $query = DB::table($modelTable);
        $query = BlackListSupir::from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "blacklistsupir.id",
            "blacklistsupir.namasupir",
            "blacklistsupir.noktp",
            "blacklistsupir.nosim",
            // "blacklistsupir.modifiedby",
            "blacklistsupir.created_at",
            "blacklistsupir.updated_at",
        );

        $query = $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing([
            'id',
            'namasupir',
            'noktp',
            'nosim',
            // 'modifiedby',
            'created_at',
            'updated_at'
        ], $models);

        return $temp;
    }

    public function processStore(array $data)
    {

        $blackListSupir = new BlackListSupir();
        // $blackListSupir->namasupir = $data['namasupir'];
        // $blackListSupir->noktp = $data['noktp'];
        // $blackListSupir->nosim = $data['nosim'];

        // if (!$blackListSupir->save()) {
        //     throw new \Exception("Error store Black List Supir");
        // }

        // (new LogTrail())->processStore([
        //     'namatabel' => strtoupper($blackListSupir->getTable()),
        //     'postingdari' => $data['postingdari'] ??strtoupper('ENTRY Black List Supir '),
        //     'idtrans' => $blackListSupir->id,
        //     'nobuktitrans' =>  $blackListSupir->id,
        //     'aksi' => 'ENTRY',
        //     'datajson' => $blackListSupir->toArray(),
        //     'modifiedby' => $blackListSupir->modifiedby
        // ]);

        // return $blackListSupir;
        $idcabang = (new Parameter())->cekText('ID CABANG','ID CABANG');
        DB::connection('sqlsrvaws')->table('blacklistsupir')->insert(
            [
                'namasupir' => $data['namasupir'],
                'noktp' => $data['noktp'],
                'nosim' => $data['nosim'],
                'modifiedby' => auth('api')->user()->name,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'info' => html_entity_decode(request()->info),
                'editing_at' => null,
                'editing_by' => null,
                'cabang_id' => $idcabang
            ]
        );
        return $blackListSupir;
    }

    public function processUpdate(BlackListSupir $blackListSupir, array $data): BlackListSupir
    {
        // $blackListSupir->namasupir = $data['namasupir'];
        // $blackListSupir->noktp = $data['noktp'];
        // $blackListSupir->nosim = $data['nosim'];

        // if (!$blackListSupir->save()) {
        //     throw new \Exception("Error update Black List Supir.");
        // }

        // (new LogTrail())->processStore([
        //     'namatabel' => strtoupper($blackListSupir->getTable()),
        //     'postingdari' => $data['postingdari'] ?? strtoupper('EDIT Black List Supir '),
        //     'idtrans' => $blackListSupir->id,
        //     'nobuktitrans' =>  $blackListSupir->id,
        //     'aksi' => 'EDIT',
        //     'datajson' => $blackListSupir->toArray(),
        //     'modifiedby' => $blackListSupir->modifiedby
        // ]);
        DB::connection('sqlsrvaws')->table('blacklistsupir')
        ->where('id', $blackListSupir->id)
        ->update(
            [
                'namasupir' => $data['namasupir'],
                'noktp' => $data['noktp'],
                'nosim' => $data['nosim'],
                'modifiedby' => auth('api')->user()->name,
                'updated_at' => date('Y-m-d H:i:s'),
                'info' => html_entity_decode(request()->info),
            ]
        );

        return $blackListSupir;
    }

    public function processDestroy($id, $postingdari = ""): BlackListSupir
    {
        $blackListSupir = BlackListSupir::findOrFail($id);
        DB::connection('sqlsrvaws')->table('blacklistsupir')->where('id',$id)->delete();
        // $dataHeader =  $blackListSupir->toArray();

        // $blackListSupir = $blackListSupir->lockAndDestroy($id);
        // $hutangLogTrail = (new LogTrail())->processStore([
        //     'namatabel' => $this->table,
        //     'postingdari' => ($postingdari == "") ? $postingdari : strtoupper('DELETE Black List Supir'),
        //     'idtrans' => $blackListSupir->id,
        //     'nobuktitrans' =>  $blackListSupir->id,
        //     'aksi' => 'DELETE',
        //     'datajson' => $blackListSupir->toArray(),
        //     'modifiedby' => auth('api')->user()->name
        // ]);
        return $blackListSupir;
    }
}
