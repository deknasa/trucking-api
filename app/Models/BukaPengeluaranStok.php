<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BukaPengeluaranStok extends MyModel
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

    protected $table = 'bukapengeluaranstok';


    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table("bukapengeluaranstok")->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "bukapengeluaranstok.id",
            "bukapengeluaranstok.tglbukti",
            "bukapengeluaranstok.pengeluaranstok_id",
            "pengeluaranstok.kodepengeluaran as pengeluaranstok",
            "bukapengeluaranstok.tglbatas",
            "bukapengeluaranstok.modifiedby",
            "bukapengeluaranstok.created_at",
            "bukapengeluaranstok.updated_at",
        )
            ->leftJoin('pengeluaranstok', 'bukapengeluaranstok.pengeluaranstok_id', 'pengeluaranstok.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }
    public function findAll($id)
    {
        $this->setRequestParameters();

        $query = DB::table("bukapengeluaranstok")->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "bukapengeluaranstok.id",
            "bukapengeluaranstok.tglbukti",
            "bukapengeluaranstok.pengeluaranstok_id",
            "pengeluaranstok.kodepengeluaran as pengeluaranstok",
            "bukapengeluaranstok.tglbatas",
            "bukapengeluaranstok.modifiedby",
            "bukapengeluaranstok.created_at",
            "bukapengeluaranstok.updated_at",
        )
            ->where('bukapengeluaranstok.id', $id)
            ->leftJoin('pengeluaranstok', 'bukapengeluaranstok.pengeluaranstok_id', 'pengeluaranstok.id');

        $data = $query->first();

        return $data;
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'pengeluaranstok') {
                                $query = $query->where('pengeluaranstok.kodepengeluaran', 'LIKE', "%$filters[data]%");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    }
                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'pengeluaranstok') {
                                $query = $query->Orwhere('pengeluaranstok.kodepengeluaran', 'LIKE', "%$filters[data]%");
                            } else {
                                $query = $query->Orwhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
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
            $table->date('tglbukti')->nullable();
            $table->dateTime('pengeluaranstok_id')->nullable();
            $table->string('pengeluaranstok', 1000)->nullable();
            $table->dateTime('tglbatas')->nullable();
            $table->string('modifiedby', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });


        $query = DB::table($modelTable);
        $query = BukaPengeluaranStok::from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "bukapengeluaranstok.id",
            "bukapengeluaranstok.tglbukti",
            "bukapengeluaranstok.pengeluaranstok_id",
            "pengeluaranstok.kodepengeluaran as pengeluaranstok",
            "bukapengeluaranstok.tglbatas",
            "bukapengeluaranstok.modifiedby",
            "bukapengeluaranstok.created_at",
            "bukapengeluaranstok.updated_at",
        )
        ->leftJoin('pengeluaranstok', 'bukapengeluaranstok.pengeluaranstok_id', 'pengeluaranstok.id');


        $query = $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing([
            'id',
            'tglbukti',
            'pengeluaranstok_id',
            'pengeluaranstok',
            'tglbatas',
            'modifiedby',
            'created_at', 'updated_at'
        ], $models);

        return $temp;
    }


    public function processStore(array $data): BukaPengeluaranStok
    {
        $jambatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', '=', 'JAMBATASAPPROVAL')->where('subgrp', '=', 'JAMBATASAPPROVAL')->first();
        $tglbatas = date('Y-m-d') . ' ' . $jambatas->text ?? '00:00:00';
        $bukaPengeluaranStok = new BukaPengeluaranStok();
        $bukaPengeluaranStok->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $bukaPengeluaranStok->pengeluaranstok_id = $data['pengeluaranstok_id'];
        $bukaPengeluaranStok->tglbatas = $tglbatas;
        $bukaPengeluaranStok->modifiedby = auth('api')->user()->name;
        $bukaPengeluaranStok->info = html_entity_decode(request()->info);

        if (!$bukaPengeluaranStok->save()) {
            throw new \Exception("Error Update Buka Pengeluaran Stok.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($bukaPengeluaranStok->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY Buka Pengeluaran Stok '),
            'idtrans' => $bukaPengeluaranStok->id,
            'nobuktitrans' =>  $bukaPengeluaranStok->id,
            'aksi' => 'ENTRY',
            'datajson' => $bukaPengeluaranStok->toArray(),
            'modifiedby' => $bukaPengeluaranStok->modifiedby
        ]);

        return $bukaPengeluaranStok;
    }

    public function processDestroy($id, $postingdari = ""): BukaPengeluaranStok
    {
        $bukaPengeluaranStok = BukaPengeluaranStok::findOrFail($id);
        $dataHeader =  $bukaPengeluaranStok->toArray();

        $bukaPengeluaranStok = $bukaPengeluaranStok->lockAndDestroy($id);
        $hutangLogTrail = (new LogTrail())->processStore([
            'namatabel' => $this->table,
            'postingdari' => ($postingdari == "") ? $postingdari : strtoupper('DELETE Buka Pengeluaran Stok'),
            'idtrans' => $bukaPengeluaranStok->id,
            'nobuktitrans' =>  $bukaPengeluaranStok->id,
            'aksi' => 'ENTRY',
            'datajson' => $bukaPengeluaranStok->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);



        return $bukaPengeluaranStok;
    }

    public function processTanggalBatasUpdate($id)
    {
        $jambatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', '=', 'JAMBATASAPPROVAL')->where('subgrp', '=', 'JAMBATASAPPROVAL')->first();
        $tglbatas = date('Y-m-d') . ' ' . $jambatas->text ?? '00:00:00';
        $bukaPengeluaranStok = BukaPengeluaranStok::where('id', $id)->first();
        $bukaPengeluaranStok->tglbatas = $tglbatas;
        $bukaPengeluaranStok->save();
        return $bukaPengeluaranStok;
    }

    public function isTanggalAvaillable($id)
    {

        $tutupbuku = DB::table('parameter')->from(
            DB::raw("parameter as a with (readuncommitted)")
        )
            ->where('a.grp', '=', 'TUTUP BUKU')
            ->where('a.subgrp', '=', 'TUTUP BUKU')
            ->first();
        $approval = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where("grp", 'STATUS APPROVAL')->where("text", "APPROVAL")->first();

        $bukaPengeluaranStok = DB::table("bukapengeluaranstok")->from(DB::raw("bukapengeluaranstok with (readuncommitted)"))
            ->select('bukapengeluaranstok.pengeluaranstok_id', 'bukapengeluaranstok.tglbukti')
            ->where('bukapengeluaranstok.tglbukti', '<', date('Y-m-d'))
            ->where('bukapengeluaranstok.tglbatas', '>', date('Y-m-d H:i:s'))
            ->where('bukapengeluaranstok.tglbukti', '>', $tutupbuku->text)
            ->where('bukapengeluaranstok.pengeluaranstok_id', $id)
            ->get();

        return $bukaPengeluaranStok;
    }
}
