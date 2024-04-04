<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BukaPenerimaanStok extends MyModel
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

    protected $table = 'bukapenerimaanstok';


    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table("bukapenerimaanstok")->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "bukapenerimaanstok.id",
            "bukapenerimaanstok.tglbukti",
            "bukapenerimaanstok.penerimaanstok_id",
            "penerimaanstok.kodepenerimaan as penerimaanstok",
            "bukapenerimaanstok.tglbatas",
            "bukapenerimaanstok.modifiedby",
            "bukapenerimaanstok.created_at",
            "bukapenerimaanstok.updated_at",
        )
            ->leftJoin('penerimaanstok', 'bukapenerimaanstok.penerimaanstok_id', 'penerimaanstok.id');

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

        $query = DB::table("bukapenerimaanstok")->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "bukapenerimaanstok.id",
            "bukapenerimaanstok.tglbukti",
            "bukapenerimaanstok.penerimaanstok_id",
            "penerimaanstok.kodepenerimaan as penerimaanstok",
            "bukapenerimaanstok.tglbatas",
            "bukapenerimaanstok.modifiedby",
            "bukapenerimaanstok.created_at",
            "bukapenerimaanstok.updated_at",
        )
            ->where('bukapenerimaanstok.id', $id)
            ->leftJoin('penerimaanstok', 'bukapenerimaanstok.penerimaanstok_id', 'penerimaanstok.id');

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
                            if ($filters['field'] == 'penerimaanstok') {
                                $query = $query->where('penerimaanstok.kodepenerimaan', 'LIKE', "%$filters[data]%");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    }
                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'penerimaanstok') {
                                $query = $query->Orwhere('penerimaanstok.kodepenerimaan', 'LIKE', "%$filters[data]%");
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
            $table->dateTime('penerimaanstok_id')->nullable();
            $table->string('penerimaanstok', 1000)->nullable();
            $table->dateTime('tglbatas')->nullable();
            $table->string('modifiedby', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });


        $query = DB::table($modelTable);
        $query = BukaPenerimaanStok::from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "bukapenerimaanstok.id",
            "bukapenerimaanstok.tglbukti",
            "bukapenerimaanstok.penerimaanstok_id",
            "penerimaanstok.kodepenerimaan as penerimaanstok",
            "bukapenerimaanstok.tglbatas",
            "bukapenerimaanstok.modifiedby",
            "bukapenerimaanstok.created_at",
            "bukapenerimaanstok.updated_at",
        )->leftJoin('penerimaanstok', 'bukapenerimaanstok.penerimaanstok_id', 'penerimaanstok.id');

        $query = $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing([
            'id',
            'tglbukti',
            'penerimaanstok_id',
            'penerimaanstok',
            'tglbatas',
            'modifiedby',
            'created_at', 'updated_at'
        ], $models);

        return $temp;
    }


    public function processStore(array $data): BukaPenerimaanStok
    {
        $jambatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', '=', 'JAMBATASAPPROVAL')->where('subgrp', '=', 'JAMBATASAPPROVAL')->first();
        $tglbatas = date('Y-m-d') . ' ' . $jambatas->text ?? '00:00:00';
        $bukaPenerimaanStok = new BukaPenerimaanStok();
        $bukaPenerimaanStok->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $bukaPenerimaanStok->penerimaanstok_id = $data['penerimaanstok_id'];
        $bukaPenerimaanStok->tglbatas = $tglbatas;
        $bukaPenerimaanStok->modifiedby = auth('api')->user()->name;
        $bukaPenerimaanStok->info = html_entity_decode(request()->info);

        if (!$bukaPenerimaanStok->save()) {
            throw new \Exception("Error Update Buka Penerimaan Stok.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($bukaPenerimaanStok->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY Buka Penerimaan Stok '),
            'idtrans' => $bukaPenerimaanStok->id,
            'nobuktitrans' =>  $bukaPenerimaanStok->id,
            'aksi' => 'ENTRY',
            'datajson' => $bukaPenerimaanStok->toArray(),
            'modifiedby' => $bukaPenerimaanStok->modifiedby
        ]);

        return $bukaPenerimaanStok;
    }

    public function processDestroy($id, $postingdari = ""): BukaPenerimaanStok
    {
        $bukaPenerimaanStok = BukaPenerimaanStok::findOrFail($id);
        $dataHeader =  $bukaPenerimaanStok->toArray();

        $bukaPenerimaanStok = $bukaPenerimaanStok->lockAndDestroy($id);
        $hutangLogTrail = (new LogTrail())->processStore([
            'namatabel' => $this->table,
            'postingdari' => ($postingdari == "") ? $postingdari : strtoupper('DELETE Buka Penerimaan Stok'),
            'idtrans' => $bukaPenerimaanStok->id,
            'nobuktitrans' =>  $bukaPenerimaanStok->id,
            'aksi' => 'ENTRY',
            'datajson' => $bukaPenerimaanStok->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);



        return $bukaPenerimaanStok;
    }

    public function processTanggalBatasUpdate($id)
    {
        $jambatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', '=', 'JAMBATASAPPROVAL')->where('subgrp', '=', 'JAMBATASAPPROVAL')->first();
        $tglbatas = date('Y-m-d') . ' ' . $jambatas->text ?? '00:00:00';
        $bukaPenerimaanStok = BukaPenerimaanStok::where('id', $id)->first();
        $bukaPenerimaanStok->tglbatas = $tglbatas;
        $bukaPenerimaanStok->save();
        return $bukaPenerimaanStok;
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

        $bukaPenerimaanStok = DB::table("bukapenerimaanstok")->from(DB::raw("bukapenerimaanstok with (readuncommitted)"))
            ->select('bukapenerimaanstok.penerimaanstok_id', 'bukapenerimaanstok.tglbukti')
            ->where('bukapenerimaanstok.tglbukti', '<', date('Y-m-d'))
            ->where('bukapenerimaanstok.tglbatas', '>', date('Y-m-d H:i:s'))
            ->where('bukapenerimaanstok.tglbukti', '>', $tutupbuku->text)
            ->where('bukapenerimaanstok.penerimaanstok_id', $id)
            ->get();

        return $bukaPenerimaanStok;
    }
}
