<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class stokpersediaan extends MyModel
{
    use HasFactory;

    protected $table = 'stokpersediaan';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];



    public function default()
    {

        $tempStokDari = '##tempStokDari' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($tempStokDari, function ($table) {
            $table->unsignedBigInteger('gudang_id')->nullable();
            $table->string('gudang', 255)->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->string('trado', 255)->nullable();
            $table->unsignedBigInteger('gandengan_id')->nullable();
            $table->string('gandengan', 255)->nullable();
            $table->unsignedBigInteger('keterangan')->nullable();
        });
        $gudang = Gudang::from(
            DB::raw('gudang with (readuncommitted)')
        )
            ->select(
                'id as gudang_id',
                'gudang as gudang',

            )
            ->orderBy('id', 'asc')
            ->limit(1)
            ->first();

        $trado = Trado::from(
            DB::raw('trado with (readuncommitted)')
        )
            ->select(
                'id as trado_id',
                'kodetrado as trado',

            )
            ->orderBy('id', 'asc')
            ->limit(1)
            ->first();

        $gandengan = Gandengan::from(
            DB::raw('gandengan with (readuncommitted)')
        )
            ->select(
                'id as gandengan_id',
                'keterangan as gandengan',

            )
            ->orderBy('id', 'asc')
            ->limit(1)
            ->first();

        $filter = Parameter::from(
            DB::raw('parameter with (readuncommitted)')
        )
            ->where('grp', 'STOK PERSEDIAAN')
            ->where('default', 'YA')
            ->first();

        DB::table($tempStokDari)->insert(
            [
                "gudang_id" => $gudang->gudang_id,
                "gudang" => $gudang->gudang,
                "trado_id" => $trado->trado_id,
                "trado" => $trado->trado,
                "gandengan_id" => $gandengan->gandengan_id,
                "gandengan" => $gandengan->gandengan,
                "keterangan" => $filter->id
            ]
        );
        $query = DB::table($tempStokDari)->from(
            DB::raw($tempStokDari)
        )
            ->select(
                'gudang_id',
                'gudang',
                'trado_id',
                'trado',
                'gandengan_id',
                'gandengan',
                'keterangan'
            );

        $data = $query->first();
        return $data;
    }

    public function getallstokpersediaan()
    {

        $tempkartustok = '##tempkartustok' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempkartustok, function ($table) {
            $table->id();
            $table->unsignedBigInteger('gudang_id')->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->unsignedBigInteger('gandengan_id')->nullable();
            $table->unsignedBigInteger('stok_id')->nullable();
            $table->string('lokasi', 1000)->nullable();
            $table->double('qty', 15, 2)->nullable();
        });


        $querykartustok = db::table('kartustok')->from(
            DB::raw("kartustok as a with (readuncommitted)")
        )
            ->select(
                'a.gudang_id',
                'a.trado_id',
                'a.gandengan_id',
                'a.stok_id',
                'a.lokasi',
                DB::raw("sum(isnull(a.qtymasuk,0)-isnull(a.qtykeluar,0)) as qty"),
            )
            ->groupBy('a.gudang_id')
            ->groupBy('a.trado_id')
            ->groupBy('a.gandengan_id')
            ->groupBy('a.stok_id')
            ->groupBy('a.lokasi');

            DB::table($tempkartustok)->insertUsing([
                'gudang_id',
                'trado_id',
                'gandengan_id',
                'stok_id',
                'lokasi', 
                'qty', 
            ], $querykartustok);            

        $query=db::table($tempkartustok)->from(db::raw(
            $tempkartustok . " a"
        ))
        ->select(
            'a.gudang_id',
            'a.trado_id',
            'a.gandengan_id',
            'a.lokasi',
            'b.namastok as stok_id',
            'a.qty',
            db::raw("'ADMIN' as modifiedby")
        )
        ->join(db::raw("stok b with (readuncommitted)"),'a.stok_id','b.id')
        ->orderBy('a.lokasi')
        ->orderBy('b.id');

        return $query;


    }

    public function get()
    {
        $this->setRequestParameters();

        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'StokPersediaanController';

        if ($proses == 'reload') {
            $temtabel = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel a with (readuncommitted)")
            )
                ->select(
                    'id',
                    'class',
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            if (isset($querydata)) {
                Schema::dropIfExists($querydata->namatabel);
                DB::table('listtemporarytabel')->where('id', $querydata->id)->delete();
            }


            DB::table('listtemporarytabel')->insert(
                [
                    'class' => $class,
                    'namatabel' => $temtabel,
                    'modifiedby' => $user,
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s'),
                ]
            );


            Schema::create($temtabel, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('gudang_id')->nullable();
                $table->unsignedBigInteger('trado_id')->nullable();
                $table->unsignedBigInteger('gandengan_id')->nullable();
                $table->string('lokasi', 1000)->nullable();
                $table->string('stok_id', 1000)->nullable();
                $table->double('qty', 15, 2)->nullable();
                $table->string('modifiedby', 100)->nullable();
            });
          
            DB::table($temtabel)->insertUsing([
                'gudang_id',
                'trado_id',
                'gandengan_id',
                'lokasi',
                'stok_id',
                'qty',
                'modifiedby',
            ], $this->getallstokpersediaan());
        } else {
            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel with (readuncommitted)")
            )
                ->select(
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            // dd($querydata);
            $temtabel = $querydata->namatabel;
        }

        // 

        $query = DB::table( $temtabel)->select(
            'id',
            'lokasi',
            'stok_id',
            'qty',
            'modifiedby'
        );

        if (request()->keterangan && request()->data) {

            $parameter = Parameter::where('id', request()->keterangan)->first();
            if ($parameter->text == 'GUDANG') {
                $gudang_id = request()->data;
                $query->where('gudang_id', $gudang_id);
            }
            if ($parameter->text == 'TRADO') {
                $trado_id = request()->data;
                $query->where('trado_id', $trado_id);
            }
            if ($parameter->text == 'GANDENGAN') {
                $gandengan_id = request()->data;
                $query->where('gandengan_id', $gandengan_id);
            }
        } 

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
        return $query->orderBy($this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {

        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw( "[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {

                                // $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw("[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    });

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
