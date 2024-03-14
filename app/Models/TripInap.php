<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TripInap extends MyModel
{
    use HasFactory;
    protected $table = 'tripinap';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {
        $this->setRequestParameters();
        $dari = request()->dari ?? '';
        $sampai = request()->sampai ?? '';


        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();
        $query = DB::table($this->table)->from(
            DB::raw("tripinap with (readuncommitted)")
        )
            ->select(
                'tripinap.id',
                'tripinap.tglabsensi',
                'tripinap.trado_id',
                'trado.kodetrado as trado',
                'tripinap.supir_id',
                'supir.namasupir as supir',
                'tripinap.suratpengantar_nobukti',
                DB::raw('(case when (year(tripinap.jammasukinap) <= 2000) then null else tripinap.jammasukinap end ) as jammasukinap'),
                DB::raw('(case when (year(tripinap.jamkeluarinap) <= 2000) then null else tripinap.jamkeluarinap end ) as jamkeluarinap'),
                'tripinap.statusapproval as statusapproval_id',
                'approval.memo as statusapproval',
                'tripinap.tglapproval',
                'tripinap.userapproval',
                'tripinap.modifiedby',
                'tripinap.created_at',
                'tripinap.updated_at',
                DB::raw("'Laporan Trip Inap' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter as approval with (readuncommitted)"), 'tripinap.statusapproval', 'approval.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'tripinap.trado_id', 'trado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'tripinap.supir_id', 'supir.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        if ($dari != '' && $sampai != '') {
            $query->whereBetween('tripinap.tglabsensi', [date('Y-m-d', strtotime($dari)), date('Y-m-d', strtotime($sampai))]);
        }
        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                'tripinap.id',
                'tripinap.tglabsensi',
                'tripinap.trado_id',
                'trado.kodetrado as trado',
                'tripinap.supir_id',
                'supir.namasupir as supir',
                'tripinap.suratpengantar_nobukti',
                'tripinap.jammasukinap',
                'tripinap.jamkeluarinap',
                'tripinap.statusapproval as statusapproval_id',
                'approval.memo as statusapproval',
                'tripinap.tglapproval',
                'tripinap.userapproval',
                'tripinap.modifiedby',
                'tripinap.created_at',
                'tripinap.updated_at',
            )
            ->leftJoin(DB::raw("parameter as approval with (readuncommitted)"), 'tripinap.statusapproval', 'approval.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'tripinap.trado_id', 'trado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'tripinap.supir_id', 'supir.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->date('tglabsensi')->nullable();
            $table->bigInteger('trado_id')->nullable();
            $table->string('trado', 1000)->nullable();
            $table->bigInteger('supir_id')->nullable();
            $table->string('supir', 1000)->nullable();
            $table->string('suratpengantar_nobukti', 1000)->nullable();
            $table->dateTime('jammasukinap')->nullable();
            $table->dateTime('jamkeluarinap')->nullable();
            $table->string('statusapproval_id', 1000)->nullable();
            $table->string('statusapproval', 1000)->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('userapproval', 1000)->nullable();
            $table->string('modifiedby')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        $models = $query;
        DB::table($temp)->insertUsing([
            'id',
            'tglabsensi',
            'trado_id',
            'trado',
            'supir_id',
            'supir',
            'suratpengantar_nobukti',
            'jammasukinap',
            'jamkeluarinap',
            'statusapproval_id',
            'statusapproval',
            'tglapproval',
            'userapproval',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $models);

        return $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'trado') {
            return $query->orderBy('trado.kodetrado', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'supir') {
            return $query->orderBy('supir.namasupir', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'statusapproval') {
            return $query->orderBy('approval.text', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statusapproval') {
                                $query = $query->where('approval.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'trado') {
                                $query = $query->where('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'supir') {
                                $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglabsensi' || $filters['field'] == 'tglapproval') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at' || $filters['field'] == 'jammasukinap' || $filters['field'] == 'jamkeluarinap') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'statusapproval') {
                                    $query = $query->orWhere('approval.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'trado') {
                                    $query = $query->orWhere('trado.kodetrado', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'supir') {
                                    $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'tglabsensi' || $filters['field'] == 'tglapproval') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at' || $filters['field'] == 'jammasukinap' || $filters['field'] == 'jamkeluarinap') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else {
                                    // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                                }
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

    public function findAll(TripInap $tripInap)
    {

        return $query = DB::table($this->table)->from(
            DB::raw("tripinap with (readuncommitted)")
        )
            ->select(
                'tripinap.id',
                DB::raw("format(tripinap.tglabsensi,'dd-MM-yyyy')as tglabsensi"),
                'tripinap.trado_id',
                'absensisupirheader.id as absensi_id',
                DB::raw("(trim(trado.kodetrado)+' - '+trim(supir.namasupir)) as trado"),
                'tripinap.supir_id',
                'supir.namasupir as supir',
                'tripinap.suratpengantar_nobukti',
                'tripinap.jammasukinap',
                'tripinap.jamkeluarinap',
                'tripinap.statusapproval as statusapproval_id',
                'approval.memo as statusapproval',
                'tripinap.tglapproval',
                'tripinap.userapproval',
                'tripinap.modifiedby',
                'tripinap.created_at',
                'tripinap.updated_at',
            )
            ->leftJoin(DB::raw("parameter as approval with (readuncommitted)"), 'tripinap.statusapproval', 'approval.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'tripinap.trado_id', 'trado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'tripinap.supir_id', 'supir.id')
            ->leftJoin(DB::raw("absensisupirheader with (readuncommitted)"), 'tripinap.tglabsensi', 'absensisupirheader.tglbukti')
            ->where('tripinap.id', $tripInap->id)
            ->first();
    }


    public function processStore(array $data)
    {
        $suratPengantar = (new SuratPengantar())->select('supir_id')->where('nobukti', $data["suratpengantar_nobukti"])->first();
        $statusapproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

        $tripInap = new TripInap();
        $tripInap->tglabsensi =  date('Y-m-d', strtotime($data['tglabsensi']));
        $tripInap->trado_id = $data["trado_id"];
        $tripInap->supir_id = $suratPengantar->supir_id;
        $tripInap->suratpengantar_nobukti = $data["suratpengantar_nobukti"];
        $tripInap->jammasukinap = date('Y-m-d H:i:s', strtotime($data["jammasukinap"]));
        $tripInap->jamkeluarinap = date('Y-m-d H:i:s', strtotime($data["jamkeluarinap"]));
        $tripInap->statusapproval = $statusapproval->id;
        $tripInap->modifiedby = auth('api')->user()->name;
        if (!$tripInap->save()) {
            throw new \Exception("Error storing Trip Inap.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($tripInap->getTable()),
            'postingdari' => 'ENTRY TRIP INAP',
            'idtrans' => $tripInap->id,
            'nobuktitrans' => $tripInap->id,
            'aksi' => 'ENTRY',
            'datajson' => $tripInap->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $tripInap;
    }

    public function processUpdate(TripInap $tripInap, array $data)
    {
        $suratPengantar = (new SuratPengantar())->select('supir_id')->where('nobukti', $data["suratpengantar_nobukti"])->first();

        $tripInap->tglabsensi =  date('Y-m-d', strtotime($data['tglabsensi']));
        $tripInap->trado_id = $data["trado_id"];
        $tripInap->supir_id = $suratPengantar->supir_id;
        $tripInap->suratpengantar_nobukti = $data["suratpengantar_nobukti"];
        $tripInap->jammasukinap = date('Y-m-d H:i:s', strtotime($data["jammasukinap"]));
        $tripInap->jamkeluarinap = date('Y-m-d H:i:s', strtotime($data["jamkeluarinap"]));
        $tripInap->modifiedby = auth('api')->user()->name;
        if (!$tripInap->save()) {
            throw new \Exception("Error storing Trip Inap.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($tripInap->getTable()),
            'postingdari' => 'EDIT TRIP INAP',
            'idtrans' => $tripInap->id,
            'nobuktitrans' => $tripInap->id,
            'aksi' => 'EDIT',
            'datajson' => $tripInap->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $tripInap;
    }

    public function processDestroy($id, $postingDari = ''): TripInap
    {

        $tripInap = new TripInap();
        $tripInap = $tripInap->lockAndDestroy($id);

        $tripInapLogTrail = (new LogTrail())->processStore([
            'namatabel' => $tripInap->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $tripInap->id,
            'nobuktitrans' => $tripInap->id,
            'aksi' => 'DELETE',
            'datajson' => $tripInap->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $tripInap;
    }

    public function processApprove(array $data)
    {

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $tripInap = TripInap::find($data['Id'][$i]);

            if ($tripInap->statusapproval == $statusApproval->id) {
                $tripInap->statusapproval = $statusNonApproval->id;
                $aksi = $statusNonApproval->text;
            } else {
                $tripInap->statusapproval = $statusApproval->id;
                $aksi = $statusApproval->text;
            }

            $tripInap->tglapproval = date('Y-m-d', time());
            $tripInap->userapproval = auth('api')->user()->name;
            if ($tripInap->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($tripInap->getTable()),
                    'postingdari' => 'APPROVAL TRIP INAP',
                    'idtrans' => $tripInap->id,
                    'nobuktitrans' => $tripInap->id,
                    'aksi' => $aksi,
                    'datajson' => $tripInap->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $tripInap;
    }
}
