<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BukaAbsensi extends MyModel
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
    protected $table = 'bukaabsensi';

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table("bukaabsensi")->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "bukaabsensi.id",
            "bukaabsensi.tglabsensi",
            "bukaAbsensi.mandor_user_id",
            'user.name as user',
            "bukaabsensi.tglbatas",
            "bukaabsensi.modifiedby",
            "bukaabsensi.created_at",
            "bukaabsensi.updated_at",
        )
        ->leftJoin(DB::raw("[user]"), 'bukaabsensi.mandor_user_id', db::raw("[user].id"));

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function findAll($id){
        return $query = $this->select(
            "bukaabsensi.id",
            "bukaabsensi.tglabsensi",
            "bukaabsensi.mandor_user_id",
            'user.name as user',
            "bukaabsensi.tglbatas",
            "bukaabsensi.modifiedby",
            "bukaabsensi.created_at",
            "bukaabsensi.updated_at",
        )
        ->leftJoin(DB::raw("[user]"), 'bukaabsensi.mandor_user_id', db::raw("[user].id"))
        ->where('bukaabsensi.id',$id)
        ->first();
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'tglabsensi') {
                                $query = $query->whereRaw("format(bukaabsensi." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at' || $filters['field'] == 'tglbatas') {
                                $query = $query->whereRaw("format(bukaabsensi." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else{

                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'tglabsensi') {
                                $query = $query->orWhereRaw("format(bukaabsensi." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at' || $filters['field'] == 'tglbatas') {
                                $query = $query->orWhereRaw("format(bukaabsensi." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            }else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
            $table->date('tglabsensi')->nullable();
            $table->dateTime('tglbatas')->nullable();
            $table->string('modifiedby', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });


        $query = DB::table($modelTable);
        $query = BukaAbsensi::from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "bukaabsensi.id",
            "bukaabsensi.tglabsensi",
            "bukaabsensi.tglbatas",
            "bukaabsensi.modifiedby",
            "bukaabsensi.created_at",
            "bukaabsensi.updated_at",
        );

        $query = $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing([
            'id',
            'tglabsensi',
            'tglbatas',
            'modifiedby',
            'created_at', 'updated_at'
        ], $models);

        return $temp;
    }

    public function processStore(array $data): BukaAbsensi
    {
        // $jambatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', '=', 'BATAS JAM EDIT ABSENSI')->where('subgrp', '=', 'BATAS JAM EDIT ABSENSI')->first();
        $jambatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', '=', 'JAMBATASAPPROVAL')->where('subgrp', '=', 'JAMBATASAPPROVAL')->first();
        $tglbatas = date('Y-m-d') . ' ' . $jambatas->text ?? '00:00:00';
        // if (strtotime('now') > strtotime($tglbatas)) {
            // $tglbatas = date('Y-m-d', strtotime('tomorrow')) . ' ' . $jambatas->text ?? '00:00:00';
        // }

        $bukaAbsensi = new BukaAbsensi();
        $bukaAbsensi->tglabsensi = date('Y-m-d', strtotime($data['tglabsensi']));
        $bukaAbsensi->mandor_user_id = $data['user_id'];
        $bukaAbsensi->tglbatas = $tglbatas;
        $bukaAbsensi->modifiedby = auth('api')->user()->name;
        $bukaAbsensi->info = html_entity_decode(request()->info);

        $absensiSupirHeader = AbsensiSupirHeader::where('tglbukti', $bukaAbsensi->tglabsensi)->first();
        if ($absensiSupirHeader) {
            $absensiSupirHeader->tglbataseditabsensi = $tglbatas;
            $absensiSupirHeader->modifiedby = auth('api')->user()->name;
            $absensiSupirHeader->save();
        }
        if (!$bukaAbsensi->save()) {
            throw new \Exception("Error Update Buka Absensi.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($bukaAbsensi->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY Buka Absensi '),
            'idtrans' => $bukaAbsensi->id,
            'nobuktitrans' =>  $bukaAbsensi->id,
            'aksi' => 'ENTRY',
            'datajson' => $bukaAbsensi->toArray(),
            'modifiedby' => $bukaAbsensi->modifiedby
        ]);

        return $bukaAbsensi;
    }
    public function processUpdate(BukaAbsensi $bukaAbsensi, array $data): BukaAbsensi
    {
        $bukaAbsensi = new BukaAbsensi();
        $bukaAbsensi->tglabsensi = date('Y-m-d', strtotime($data['tglabsensi']));
        $bukaAbsensi->mandor_user_id = $data['user_id'];
        $bukaAbsensi->modifiedby = auth('api')->user()->name;
        $bukaAbsensi->info = html_entity_decode(request()->info);

        if (!$bukaAbsensi->save()) {
            throw new \Exception("Error Update Buka Absensi.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($bukaAbsensi->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY Buka Absensi '),
            'idtrans' => $bukaAbsensi->id,
            'nobuktitrans' =>  $bukaAbsensi->id,
            'aksi' => 'ENTRY',
            'datajson' => $bukaAbsensi->toArray(),
            'modifiedby' => $bukaAbsensi->modifiedby
        ]);

        return $bukaAbsensi;
    }

    public function processDestroy($id, $postingdari = ""): BukaAbsensi
    {
        $jambatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', '=', 'BATAS JAM EDIT ABSENSI')->where('subgrp', '=', 'BATAS JAM EDIT ABSENSI')->first();

        $bukaAbsensi = BukaAbsensi::findOrFail($id);
        $dataHeader =  $bukaAbsensi->toArray();

        $absensiSupirHeader = AbsensiSupirHeader::where('tglbukti', $bukaAbsensi->tglabsensi)->first();
        $tglbatas = $bukaAbsensi->tglabsensi . ' ' . $jambatas->text ?? '00:00:00';
        if ($absensiSupirHeader) {
            $absensiSupirHeader->tglbataseditabsensi = $tglbatas;
            $absensiSupirHeader->modifiedby = auth('api')->user()->name;
            $absensiSupirHeader->save();
        }
        $bukaAbsensi = $bukaAbsensi->lockAndDestroy($id);
        $hutangLogTrail = (new LogTrail())->processStore([
            'namatabel' => $this->table,
            'postingdari' => ($postingdari == "") ? $postingdari : strtoupper('DELETE Buka Absensi'),
            'idtrans' => $bukaAbsensi->id,
            'nobuktitrans' =>  $bukaAbsensi->id,
            'aksi' => 'ENTRY',
            'datajson' => $bukaAbsensi->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);



        return $bukaAbsensi;
    }

    public function processTanggalBatasUpdate(array $data)
    {
        // $jambatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', '=', 'BATAS JAM EDIT ABSENSI')->where('subgrp', '=', 'BATAS JAM EDIT ABSENSI')->first();
        // $tglbatas = date('Y-m-d') . ' ' . $jambatas->text ?? '00:00:00';

        // if (strtotime('now') > strtotime($tglbatas)) {
        //     $tglbatas = date('Y-m-d', strtotime('tomorrow')) . ' ' . $jambatas->text ?? '00:00:00';
        // }
                $jambatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', '=', 'JAMBATASAPPROVAL')->where('subgrp', '=', 'JAMBATASAPPROVAL')->first();
                $tglbatas = date('Y-m-d') . ' ' . $jambatas->text ?? '00:00:00';
        
        for ($i = 0; $i < count($data['id']); $i++) {
            $bukaAbsensi = BukaAbsensi::where('id', $data['id'][$i])->first();
            $absensiSupirHeader = AbsensiSupirHeader::where('tglbukti', $bukaAbsensi->tglabsensi)->first();
            if ($absensiSupirHeader) {
                $absensiSupirHeader->tglbataseditabsensi = $tglbatas;
                $absensiSupirHeader->statusapprovaleditabsensi = 248;
                $absensiSupirHeader->tglapprovaleditabsensi = date('Y-m-d H:i:s');
                $absensiSupirHeader->userapprovaleditabsensi = auth('api')->user()->name;
                $absensiSupirHeader->modifiedby = auth('api')->user()->name;
                $absensiSupirHeader->save();
            }
            $bukaAbsensi->tglbatas = $tglbatas;
            $bukaAbsensi->modifiedby = auth('api')->user()->name;
            $bukaAbsensi->save();

            (new LogTrail())->processStore([
                'namatabel' => strtoupper($bukaAbsensi->getTable()),
                'postingdari' =>  "UPDATE TANGGAL BATAS ABSENSI",
                'idtrans' => $bukaAbsensi->id,
                'nobuktitrans' => $bukaAbsensi->nobukti,
                'aksi' => 'UPDATE TANGGAL BATAS',
                'datajson' => $bukaAbsensi->toArray(),
                'modifiedby' => auth('api')->user()->user
            ]);
        }

        return $bukaAbsensi;
    }
}
