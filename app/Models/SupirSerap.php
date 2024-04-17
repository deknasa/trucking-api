<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SupirSerap extends MyModel
{
    use HasFactory;

    protected $table = 'supirserap';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {
        $this->setRequestParameters();
        $forReport = request()->forReport ?? false;
        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'supirserap.id',
                'supirserap.tglabsensi',
                'supirserap.keterangan',
                'trado.kodetrado as trado_id',
                'supir.namasupir as supir_id',
                'serap.namasupir as supirserap_id',
                'parameter.memo as statusapproval',
                'parameter.text as statusapprovaltext',
                'supirserap.userapproval',
                DB::raw('(case when (year(supirserap.tglapproval) <= 2000) then null else supirserap.tglapproval end ) as tglapproval'),
                'supirserap.modifiedby',
                'supirserap.created_at',
                'supirserap.updated_at',
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'supirserap.trado_id', 'trado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'supirserap.supir_id', 'supir.id')
            ->leftJoin(DB::raw("supir as serap with (readuncommitted)"), 'supirserap.supirserap_id', 'serap.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'supirserap.statusapproval', 'parameter.id');

        if ($forReport) {
            $dari = date('Y-m-d', strtotime(request()->dari));
            $sampai = date('Y-m-d', strtotime(request()->sampai));
            $query->whereBetween('supirserap.tglabsensi', [$dari, $sampai])
                ->orderBy('supirserap.tglabsensi');
        } else {
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->sort($query);
            $this->paginate($query);
            $this->filter($query);
        }
        $data = $query->get();

        return $data;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'trado_id') {
            return $query->orderBy('trado.kodetrado', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'supir_id') {
            return $query->orderBy('supir.namasupir', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'supirserap_id') {
            return $query->orderBy('serap.namasupir', $this->params['sortOrder']);
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
                                $query = $query->where('parameter.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'trado_id') {
                                $query = $query->where('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'supir_id') {
                                $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'supirserap_id') {
                                $query = $query->where('serap.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglabsensi') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
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
                                    $query = $query->orWhere('parameter.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'trado_id') {
                                    $query = $query->orWhere('trado.kodetrado', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'supir_id') {
                                    $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'supirserap_id') {
                                    $query = $query->orWhere('serap.namasupir', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'tglabsensi') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
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


    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
                "$this->table.id,
                 $this->table.tglabsensi,
                 $this->table.keterangan,
                 trado.kodetrado as trado_id,
                 supir.namasupir as supir_id,
                 serap.namasupir as supirserap_id,
                 parameter.memo as statusapproval,
                 $this->table.userapproval,
                 (case when (year(supirserap.tglapproval) <= 2000) then null else supirserap.tglapproval end ) as tglapproval,
                 $this->table.modifiedby,
                 $this->table.created_at,
                 $this->table.updated_at"
            )
        )
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'supirserap.trado_id', 'trado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'supirserap.supir_id', 'supir.id')
            ->leftJoin(DB::raw("supir as serap with (readuncommitted)"), 'supirserap.supirserap_id', 'serap.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'supirserap.statusapproval', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->date('tglabsensi')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('trado_id')->nullable();
            $table->string('supir_id')->nullable();
            $table->string('supirserap_id')->nullable();
            $table->string('statusapproval')->nullable();
            $table->string('userapproval')->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });
        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'tglabsensi','keterangan', 'trado_id', 'supir_id', 'supirserap_id', 'statusapproval', 'userapproval', 'tglapproval', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function findAll($id)
    {
        $query = DB::table("supirserap")->from(DB::raw("supirserap with (readuncommitted)"))
            ->select(
                'supirserap.id',
                'supirserap.tglabsensi',
                'supirserap.keterangan',
                'supirserap.trado_id',
                'supirserap.supir_id',
                'supirserap.supirserap_id',
                'trado.kodetrado as trado',
                'supir.namasupir as supir',
                'serap.namasupir as supirserap'
            )
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'supirserap.trado_id', 'trado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'supirserap.supir_id', 'supir.id')
            ->leftJoin(DB::raw("supir as serap with (readuncommitted)"), 'supirserap.supirserap_id', 'serap.id')
            ->where('supirserap.id', $id);

        return $query->first();
    }

    public function isSupirSerap($trado_id,$supir_id,$tglabsensi) {
        $query = $this
        ->where('supirserap_id',$supir_id)
        ->where('tglabsensi',$tglabsensi);
        return $query->count();
    }

    public function processStore(array $data): SupirSerap
    {
        $supirSerap = new SupirSerap();

        $statusNonApproval = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $supirSerap->tglabsensi = date('Y-m-d', strtotime($data['tglabsensi']));
        $supirSerap->trado_id = $data['trado_id'];
        $supirSerap->supir_id = $data['supir_id'];
        $supirSerap->supirserap_id = $data['supirserap_id'];
        $supirSerap->keterangan = $data['keterangan'];
        $supirSerap->statusapproval = $statusNonApproval->id;
        $supirSerap->modifiedby = auth('api')->user()->user;

        if (!$supirSerap->save()) {
            throw new \Exception("Error storing supir serap");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($supirSerap->getTable()),
            'postingdari' => 'ENTRY SUPIR SERAP',
            'idtrans' => $supirSerap->id,
            'nobuktitrans' => $supirSerap->id,
            'aksi' => 'ENTRY',
            'datajson' => $supirSerap->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        return $supirSerap;
    }

    public function processUpdate(SupirSerap $supirSerap, array $data): SupirSerap
    {
        $supirSerap->tglabsensi = date('Y-m-d', strtotime($data['tglabsensi']));
        $supirSerap->trado_id = $data['trado_id'];
        $supirSerap->supir_id = $data['supir_id'];
        $supirSerap->supirserap_id = $data['supirserap_id'];
        $supirSerap->keterangan = $data['keterangan'];
        $supirSerap->modifiedby = auth('api')->user()->user;

        if (!$supirSerap->save()) {
            throw new \Exception("Error updating supir serap");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($supirSerap->getTable()),
            'postingdari' => 'EDIT SUPIR SERAP',
            'idtrans' => $supirSerap->id,
            'nobuktitrans' => $supirSerap->id,
            'aksi' => 'EDIT',
            'datajson' => $supirSerap->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        return $supirSerap;
    }

    public function processDestroy($id, $postingDari = ''): SupirSerap
    {
        $supirSerap = new SupirSerap();
        $supirSerap = $supirSerap->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => $supirSerap->getTable(),
            'postingdari' => 'DELETE SUPIR SERAP',
            'idtrans' => $supirSerap->id,
            'nobuktitrans' => $supirSerap->id,
            'aksi' => 'DELETE',
            'datajson' => $supirSerap->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $supirSerap;
    }

    public function processApproval(array $data)
    {
        $statusApproval = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        

            $supirSerap = SupirSerap::find($data['serapId']);
            if ($supirSerap->statusapproval == $statusApproval->id) {
                $supirSerap->statusapproval = $statusNonApproval->id;
                $supirSerap->tglapproval = date('Y-m-d', strtotime("1900-01-01"));
                $supirSerap->userapproval = '';
                $aksi = $statusNonApproval->text;
            } else {
                $supirSerap->statusapproval = $statusApproval->id;
                $supirSerap->tglapproval = date('Y-m-d H:i:s');
                $supirSerap->userapproval = auth('api')->user()->name;
                $aksi = $statusApproval->text;
            }

            $supirSerap->save();
            (new LogTrail())->processStore([
                'namatabel' => strtoupper($supirSerap->getTable()),
                'postingdari' => 'APPROVAL SUPIR SERAP',
                'idtrans' => $supirSerap->id,
                'nobuktitrans' => $supirSerap->id,
                'aksi' => $aksi,
                'datajson' => $supirSerap->toArray(),
                'modifiedby' => auth('api')->user()->user
            ]);
        

        return $supirSerap;
    }

    function processStoreToAbsensi(SupirSerap $supirSerap) {
        
        $absensiSupirHeader = AbsensiSupirHeader::where('tglbukti',$supirSerap->tglabsensi)->first();
        $jam = date('H:i',strtotime('now'));

    
        $parameter = new Parameter();
        $idstatussupirserap=$parameter->cekId('SUPIR SERAP','SUPIR SERAP','YA') ?? 0;
        // dd($idstatussupirserap);

        $absensiSupirDetail = AbsensiSupirDetail::processStore($absensiSupirHeader, [
            'absensi_id' => $absensiSupirHeader->id,
            'nobukti' => $absensiSupirHeader->nobukti,
            'trado_id' => $supirSerap->trado_id,
            'supir_id' => $supirSerap->supirserap_id,
            'supirold_id' => $supirSerap->supirserap_id,
            'keterangan' => '',
            'absen_id' => '',
            'jam' => $jam,
            'modifiedby' => auth('api')->user()->user,
            'statussupirserap' => $idstatussupirserap,
            
        ]);

        (new LogTrail())->processStore([
            'namatabel' => $this->table,
            'postingdari' => strtoupper('ENTRY ABSENSI SUPIR Detail'),
            'idtrans' => $absensiSupirDetail->id,
            'nobuktitrans' => $absensiSupirHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $absensiSupirDetail->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $absensiSupirDetail;
    
    }
    function processDestroyToAbsensi(SupirSerap $supirSerap) {
        
        $absensiSupirHeader = AbsensiSupirHeader::where('tglbukti',$supirSerap->tglabsensi)->first();
        $jam = date('H:i',strtotime('now'));
        $detailLog='';
        $detailLog='';
        $absensiSupirDetail = (new AbsensiSupirDetail())->from(DB::raw("absensisupirdetail as detail with (readuncommitted)"))
            ->select('detail.id','header.nobukti','detail.uangjalan')
            ->whereRaw("detail.trado_id = $supirSerap->trado_id and header.tglbukti = '$supirSerap->tglabsensi' and (detail.supir_id = $supirSerap->supirserap_id or detail.supirold_id = $supirSerap->supirserap_id)")
            ->leftJoin(DB::raw("absensisupirheader as header with (readuncommitted)"), 'header.id', 'detail.absensi_id')
            ->first();
        if ($absensiSupirDetail) {
            $detailLog = $absensiSupirDetail->toArray();
            $absensiSupirDetail->delete();
        }


        (new LogTrail())->processStore([
            'namatabel' => $this->table,
            'postingdari' => strtoupper('delete ABSENSI SUPIR Detail'),
            'idtrans' => $absensiSupirDetail->id ?? 0,
            'nobuktitrans' => $absensiSupirDetail->nobukti ?? '',
            'aksi' => 'ENTRY',
            'datajson' => $detailLog,
            'modifiedby' => auth('api')->user()->name
        ]);

        return $absensiSupirDetail;
    
    }
}
