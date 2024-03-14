<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PengajuanTripInap extends MyModel
{
    use HasFactory;
    protected $table = 'pengajuantripinap';

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
            DB::raw("pengajuantripinap with (readuncommitted)")
        )
            ->select(
                'pengajuantripinap.id',
                'pengajuantripinap.tglabsensi',
                'pengajuantripinap.trado_id',
                'trado.kodetrado as trado',
                'pengajuantripinap.supir_id',
                'supir.namasupir as supir',
                'pengajuantripinap.statusapproval as statusapproval_id',
                'approval.memo as statusapproval',
                'pengajuantripinap.tglapproval',
                'pengajuantripinap.userapproval',
                'approvalbatas.memo as statusapprovallewatbataspengajuan',
                DB::raw('(case when (year(pengajuantripinap.tglapprovallewatbataspengajuan) <= 2000) then null else pengajuantripinap.tglapprovallewatbataspengajuan end ) as tglapprovallewatbataspengajuan'),
                'pengajuantripinap.userapprovallewatbataspengajuan',
                DB::raw('(case when (year(pengajuantripinap.tglbataslewatbataspengajuan) <= 2000) then null else pengajuantripinap.tglbataslewatbataspengajuan end ) as tglbataslewatbataspengajuan'),
                'pengajuantripinap.modifiedby',
                'pengajuantripinap.created_at',
                'pengajuantripinap.updated_at',
                DB::raw("'Laporan Pengajuan Trip Inap' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter as approval with (readuncommitted)"), 'pengajuantripinap.statusapproval', 'approval.id')
            ->leftJoin(DB::raw("parameter as approvalbatas with (readuncommitted)"), 'pengajuantripinap.statusapprovallewatbataspengajuan', 'approvalbatas.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'pengajuantripinap.trado_id', 'trado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengajuantripinap.supir_id', 'supir.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        if ($dari != '' && $sampai != '') {
            $query->whereBetween('pengajuantripinap.tglabsensi', [date('Y-m-d', strtotime($dari)), date('Y-m-d', strtotime($sampai))]);
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
                'pengajuantripinap.id',
                'pengajuantripinap.tglabsensi',
                'pengajuantripinap.trado_id',
                'trado.kodetrado as trado',
                'pengajuantripinap.supir_id',
                'supir.namasupir as supir',
                'pengajuantripinap.statusapproval as statusapproval_id',
                'approval.memo as statusapproval',
                'pengajuantripinap.tglapproval',
                'pengajuantripinap.userapproval',
                'approvalbatas.memo as statusapprovallewatbataspengajuan',
                'pengajuantripinap.tglapprovallewatbataspengajuan',
                'pengajuantripinap.userapprovallewatbataspengajuan',
                'pengajuantripinap.tglbataslewatbataspengajuan',
                'pengajuantripinap.modifiedby',
                'pengajuantripinap.created_at',
                'pengajuantripinap.updated_at',
            )
            ->leftJoin(DB::raw("parameter as approval with (readuncommitted)"), 'pengajuantripinap.statusapproval', 'approval.id')
            ->leftJoin(DB::raw("parameter as approvalbatas with (readuncommitted)"), 'pengajuantripinap.statusapprovallewatbataspengajuan', 'approvalbatas.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'pengajuantripinap.trado_id', 'trado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengajuantripinap.supir_id', 'supir.id');
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
            $table->string('statusapproval_id', 1000)->nullable();
            $table->string('statusapproval', 1000)->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('userapproval', 1000)->nullable();
            $table->string('statusapprovallewatbataspengajuan', 1000)->nullable();
            $table->date('tglapprovallewatbataspengajuan')->nullable();
            $table->string('userapprovallewatbataspengajuan', 1000)->nullable();
            $table->dateTime('tglbataslewatbataspengajuan')->nullable();
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
            'statusapproval_id',
            'statusapproval',
            'tglapproval',
            'userapproval',
            'statusapprovallewatbataspengajuan',
            'tglapprovallewatbataspengajuan',
            'userapprovallewatbataspengajuan',
            'tglbataslewatbataspengajuan',
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
                            } else if ($filters['field'] == 'statusapprovallewatbataspengajuan') {
                                $query = $query->where('approvalbatas.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'trado') {
                                $query = $query->where('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'supir') {
                                $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglabsensi' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglapprovallewatbataspengajuan') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at' || $filters['field'] == 'tglbataslewatbataspengajuan') {
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
                                } else if ($filters['field'] == 'statusapprovallewatbataspengajuan') {
                                    $query = $query->orWhere('approvalbatas.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'trado') {
                                    $query = $query->orWhere('trado.kodetrado', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'supir') {
                                    $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'tglabsensi' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglapprovallewatbataspengajuan') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at' || $filters['field'] == 'tglbataslewatbataspengajuan') {
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

    public function findAll(PengajuanTripInap $pengajuanTripInap)
    {

        return $query = DB::table($this->table)->from(
            DB::raw("pengajuantripinap with (readuncommitted)")
        )
            ->select(
                'pengajuantripinap.id',
                DB::raw("format(pengajuantripinap.tglabsensi,'dd-MM-yyyy')as tglabsensi"),
                'pengajuantripinap.trado_id',
                'absensisupirheader.id as absensi_id',
                DB::raw("(trim(trado.kodetrado)+' - '+trim(supir.namasupir)) as trado"),
                'pengajuantripinap.supir_id',
                'supir.namasupir as supir',
                'pengajuantripinap.statusapproval as statusapproval_id',
                'approval.memo as statusapproval',
                'pengajuantripinap.tglapproval',
                'pengajuantripinap.userapproval',
                'pengajuantripinap.modifiedby',
                'pengajuantripinap.created_at',
                'pengajuantripinap.updated_at',
            )
            ->leftJoin(DB::raw("parameter as approval with (readuncommitted)"), 'pengajuantripinap.statusapproval', 'approval.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'pengajuantripinap.trado_id', 'trado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengajuantripinap.supir_id', 'supir.id')
            ->leftJoin(DB::raw("absensisupirheader with (readuncommitted)"), 'pengajuantripinap.tglabsensi', 'absensisupirheader.tglbukti')
            ->where('pengajuantripinap.id', $pengajuanTripInap->id)
            ->first();
    }

    public function processStore(array $data)
    {
        $statusapproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

        $pengajuanTripInap = new PengajuanTripInap();
        $pengajuanTripInap->tglabsensi =  date('Y-m-d', strtotime($data['tglabsensi']));
        $pengajuanTripInap->trado_id = $data["trado_id"];
        $pengajuanTripInap->supir_id = $data["supir_id"];
        $pengajuanTripInap->statusapproval = $statusapproval->id;
        $pengajuanTripInap->modifiedby = auth('api')->user()->name;
        if (!$pengajuanTripInap->save()) {
            throw new \Exception("Error storing pengajuan Trip Inap.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengajuanTripInap->getTable()),
            'postingdari' => 'ENTRY PENGAJUAN TRIP INAP',
            'idtrans' => $pengajuanTripInap->id,
            'nobuktitrans' => $pengajuanTripInap->id,
            'aksi' => 'ENTRY',
            'datajson' => $pengajuanTripInap->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $pengajuanTripInap;
    }

    public function processUpdate(PengajuanTripInap $tripInap, array $data)
    {

        $tripInap->tglabsensi =  date('Y-m-d', strtotime($data['tglabsensi']));
        $tripInap->trado_id = $data["trado_id"];
        $tripInap->supir_id = $data["supir_id"];
        $tripInap->modifiedby = auth('api')->user()->name;
        if (!$tripInap->save()) {
            throw new \Exception("Error storing pengajuan Trip Inap.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($tripInap->getTable()),
            'postingdari' => 'EDIT PENGAJUAN TRIP INAP',
            'idtrans' => $tripInap->id,
            'nobuktitrans' => $tripInap->id,
            'aksi' => 'EDIT',
            'datajson' => $tripInap->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $tripInap;
    }

    public function processDestroy($id, $postingDari = ''): PengajuanTripInap
    {

        $pengajuanTripInap = new PengajuanTripInap();
        $pengajuanTripInap = $pengajuanTripInap->lockAndDestroy($id);

        $tripInapLogTrail = (new LogTrail())->processStore([
            'namatabel' => $pengajuanTripInap->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $pengajuanTripInap->id,
            'nobuktitrans' => $pengajuanTripInap->id,
            'aksi' => 'DELETE',
            'datajson' => $pengajuanTripInap->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $pengajuanTripInap;
    }


    public function processApprove(array $data)
    {

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $tripInap = PengajuanTripInap::find($data['Id'][$i]);

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
                    'postingdari' => 'UN/APPROVAL PENGAJUAN TRIP INAP',
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

    public function processApproveBatasPengajuan(array $data)
    {

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $tripInap = PengajuanTripInap::find($data['Id'][$i]);

            if ($tripInap->statusapprovallewatbataspengajuan == $statusApproval->id) {
                $tripInap->statusapprovallewatbataspengajuan = $statusNonApproval->id;
                $tripInap->userapprovallewatbataspengajuan = '';
                $tripInap->tglapprovallewatbataspengajuan = '';
                $tripInap->tglbataslewatbataspengajuan = '';
                $aksi = $statusNonApproval->text;
            } else {
                $tripInap->statusapprovallewatbataspengajuan = $statusApproval->id;
                $tripInap->userapprovallewatbataspengajuan = auth('api')->user()->name;
                $tripInap->tglapprovallewatbataspengajuan = date('Y-m-d');
                $tripInap->tglbataslewatbataspengajuan = date('Y-m-d'). ' 23:59:59';
                $aksi = $statusApproval->text;
            }

            if ($tripInap->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($tripInap->getTable()),
                    'postingdari' => 'UN/APPROVAL BATAS PENGAJUAN TRIP INAP',
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
