<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

    public function get()
    {
        $this->setRequestParameters();
        $query = DB::table($this->table)->select(
            'stokpersediaan.id',
            'stok.namastok as stok_id',
            'stokpersediaan.qty',
            'stokpersediaan.modifiedby'
        )
            ->leftJoin('stok', 'stokpersediaan.stok_id', 'stok.id');

        if (request()->keterangan && request()->data) {

            $parameter = Parameter::where('id', request()->keterangan)->first();
            if ($parameter->text == 'GUDANG') {
                $gudang_id = request()->data;
                $query->where('stokpersediaan.gudang_id', $gudang_id);
            }
            if ($parameter->text == 'TRADO') {
                $trado_id = request()->data;
                $query->where('stokpersediaan.trado_id', $trado_id);
            }
            if ($parameter->text == 'GANDENGAN') {
                $gandengan_id = request()->data;
                $query->where('stokpersediaan.gandengan_id', $gandengan_id);
            }
        } else {
            $gudang = Gudang::first();
            $query->where('stokpersediaan.gudang_id', $gudang->id);
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
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {

        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'stok_id') {
                            $query = $query->where('stok.namastok', 'LIKE', "%$filters[data]%");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . "." .  $filters['field'] . " LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {

                            if ($filters['field'] == 'stok_id') {
                                $query = $query->orWhere('stok.namastok', 'LIKE', "%$filters[data]%");
                            } else {
                                // $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
