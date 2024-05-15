<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TradoTambahanAbsensi extends MyModel
{
    use HasFactory;

    protected $table = 'tradotambahanabsensi';

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
                'tradotambahanabsensi.id',
                'tradotambahanabsensi.tglabsensi',
                'tradotambahanabsensi.keterangan',
                'trado.kodetrado as trado_id',
                'supir.namasupir as supir_id',
                'jeniskendaraan.text as statusjeniskendaraan',
                'parameter.memo as statusapproval',
                'parameter.text as statusapprovaltext',
                'tradotambahanabsensi.userapproval',
                DB::raw('(case when (year(tradotambahanabsensi.tglapproval) <= 2000) then null else tradotambahanabsensi.tglapproval end ) as tglapproval'),
                'tradotambahanabsensi.modifiedby',
                'tradotambahanabsensi.created_at',
                'tradotambahanabsensi.updated_at',
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'tradotambahanabsensi.trado_id', 'trado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'tradotambahanabsensi.supir_id', 'supir.id')
            ->leftJoin(DB::raw("parameter as jeniskendaraan with (readuncommitted)"), 'tradotambahanabsensi.statusjeniskendaraan', 'jeniskendaraan.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'tradotambahanabsensi.statusapproval', 'parameter.id');

        if ($forReport) {
            $dari = date('Y-m-d', strtotime(request()->dari));
            $sampai = date('Y-m-d', strtotime(request()->sampai));
            $query->whereBetween('tradotambahanabsensi.tglabsensi', [$dari, $sampai])
                ->orderBy('tradotambahanabsensi.tglabsensi');
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
                            } else if ($filters['field'] == 'statusjeniskendaraan') {
                                $query = $query->where('jeniskendaraan.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'trado_id') {
                                $query = $query->where('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'supir_id') {
                                $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
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
                                }else if ($filters['field'] == 'statusjeniskendaraan') {
                                    $query = $query->orWhere('jeniskendaraan.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'trado_id') {
                                    $query = $query->orWhere('trado.kodetrado', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'supir_id') {
                                    $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
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
                 'jeniskendaraan.text as statusjeniskendaraan',
                 parameter.memo as statusapproval,
                 $this->table.userapproval,
                 (case when (year(tradotambahanabsensi.tglapproval) <= 2000) then null else tradotambahanabsensi.tglapproval end ) as tglapproval,
                 $this->table.modifiedby,
                 $this->table.created_at,
                 $this->table.updated_at"
            )
        )
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'tradotambahanabsensi.trado_id', 'trado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'tradotambahanabsensi.supir_id', 'supir.id')
            ->leftJoin(DB::raw("parameter as jeniskendaraan with (readuncommitted)"), 'tradotambahanabsensi.statusjeniskendaraan', 'jeniskendaraan.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'tradotambahanabsensi.statusapproval', 'parameter.id');
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
            $table->string('statusjeniskendaraan')->nullable();
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
        DB::table($temp)->insertUsing(['id', 'tglabsensi','keterangan','trado_id', 'supir_id', 'statusjeniskendaraan','statusapproval', 'userapproval', 'tglapproval', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function findAll($id)
    {
        $query = DB::table("tradotambahanabsensi")->from(DB::raw("tradotambahanabsensi with (readuncommitted)"))
            ->select(
                'tradotambahanabsensi.id',
                'tradotambahanabsensi.tglabsensi',
                'tradotambahanabsensi.keterangan',
                'tradotambahanabsensi.trado_id',
                'tradotambahanabsensi.supir_id',
                'tradotambahanabsensi.statusjeniskendaraan',
                'jeniskendaraan.text as statusjeniskendaraannama',
                'trado.kodetrado as trado',
                'supir.namasupir as supir',
            )
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'tradotambahanabsensi.trado_id', 'trado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'tradotambahanabsensi.supir_id', 'supir.id')
            ->leftJoin(DB::raw("parameter as jeniskendaraan with (readuncommitted)"), 'tradotambahanabsensi.statusjeniskendaraan', 'jeniskendaraan.id')
            ->where('tradotambahanabsensi.id', $id);

        return $query->first();
    }

    public function processStore(array $data): TradoTambahanAbsensi
    {
        $tradoTambahanAbsensi = new TradoTambahanAbsensi();

        $statusNonApproval = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $tradoTambahanAbsensi->tglabsensi = date('Y-m-d', strtotime($data['tglabsensi']));
        $tradoTambahanAbsensi->trado_id = $data['trado_id'];
        $tradoTambahanAbsensi->supir_id = $data['supir_id'];
        $tradoTambahanAbsensi->statusjeniskendaraan = $data['statusjeniskendaraan'];
        $tradoTambahanAbsensi->keterangan = $data['keterangan'];
        $tradoTambahanAbsensi->statusapproval = $statusNonApproval->id;
        $tradoTambahanAbsensi->modifiedby = auth('api')->user()->user;

        if (!$tradoTambahanAbsensi->save()) {
            throw new \Exception("Error storing Trado Tambahan Absensi");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($tradoTambahanAbsensi->getTable()),
            'postingdari' => 'ENTRY Trado Tambahan Absensi',
            'idtrans' => $tradoTambahanAbsensi->id,
            'nobuktitrans' => $tradoTambahanAbsensi->id,
            'aksi' => 'ENTRY',
            'datajson' => $tradoTambahanAbsensi->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        return $tradoTambahanAbsensi;
    }

    public function processUpdate(TradoTambahanAbsensi $tradoTambahanAbsensi, array $data): TradoTambahanAbsensi
    {
        $tradoTambahanAbsensi->tglabsensi = date('Y-m-d', strtotime($data['tglabsensi']));
        $tradoTambahanAbsensi->trado_id = $data['trado_id'];
        $tradoTambahanAbsensi->supir_id = $data['supir_id'];
        $tradoTambahanAbsensi->statusjeniskendaraan = $data['statusjeniskendaraan'];
        $tradoTambahanAbsensi->keterangan = $data['keterangan'];
        $tradoTambahanAbsensi->modifiedby = auth('api')->user()->user;

        if (!$tradoTambahanAbsensi->save()) {
            throw new \Exception("Error updating Trado Tambahan Absensi");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($tradoTambahanAbsensi->getTable()),
            'postingdari' => 'EDIT Trado Tambahan Absensi',
            'idtrans' => $tradoTambahanAbsensi->id,
            'nobuktitrans' => $tradoTambahanAbsensi->id,
            'aksi' => 'EDIT',
            'datajson' => $tradoTambahanAbsensi->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        return $tradoTambahanAbsensi;
    }

    public function processDestroy($id, $postingDari = ''): TradoTambahanAbsensi
    {
        $tradoTambahanAbsensi = new TradoTambahanAbsensi();
        $tradoTambahanAbsensi = $tradoTambahanAbsensi->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => $tradoTambahanAbsensi->getTable(),
            'postingdari' => 'DELETE Trado Tambahan Absensi',
            'idtrans' => $tradoTambahanAbsensi->id,
            'nobuktitrans' => $tradoTambahanAbsensi->id,
            'aksi' => 'DELETE',
            'datajson' => $tradoTambahanAbsensi->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $tradoTambahanAbsensi;
    }

    public function processApproval(array $data)
    {
        $statusApproval = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        

            $tradoTambahanAbsensi = TradoTambahanAbsensi::find($data['tradoTambahanId']);
            if ($tradoTambahanAbsensi->statusapproval == $statusApproval->id) {
                $tradoTambahanAbsensi->statusapproval = $statusNonApproval->id;
                $tradoTambahanAbsensi->tglapproval = date('Y-m-d', strtotime("1900-01-01"));
                $tradoTambahanAbsensi->userapproval = '';
                $aksi = $statusNonApproval->text;
            } else {
                $tradoTambahanAbsensi->statusapproval = $statusApproval->id;
                $tradoTambahanAbsensi->tglapproval = date('Y-m-d H:i:s');
                $tradoTambahanAbsensi->userapproval = auth('api')->user()->name;
                $aksi = $statusApproval->text;
            }

            $tradoTambahanAbsensi->save();
            (new LogTrail())->processStore([
                'namatabel' => strtoupper($tradoTambahanAbsensi->getTable()),
                'postingdari' => 'APPROVAL Trado Tambahan Absensi',
                'idtrans' => $tradoTambahanAbsensi->id,
                'nobuktitrans' => $tradoTambahanAbsensi->id,
                'aksi' => $aksi,
                'datajson' => $tradoTambahanAbsensi->toArray(),
                'modifiedby' => auth('api')->user()->user
            ]);
        

        return $tradoTambahanAbsensi;
    }

    function processStoreToAbsensi(TradoTambahanAbsensi $tradoTambahanAbsensi) {
        
        $absensiSupirHeader = AbsensiSupirHeader::where('tglbukti',$tradoTambahanAbsensi->tglabsensi)->first();
        $jam = date('H:i',strtotime('now'));

    
        $parameter = new Parameter();
        $idstatustambahantrado=$parameter->cekId('TAMBAHAN TRADO ABSENSI','TAMBAHAN TRADO ABSENSI','YA') ?? 0;
        // dd($idstatustambahantrado);

        $absensiSupirDetail = AbsensiSupirDetail::processStore($absensiSupirHeader, [
            'absensi_id' => $absensiSupirHeader->id,
            'nobukti' => $absensiSupirHeader->nobukti,
            'trado_id' => $tradoTambahanAbsensi->trado_id,
            'supir_id' => $tradoTambahanAbsensi->supir_id,
            'supirold_id' => $tradoTambahanAbsensi->supir_id,
            'statusjeniskendaraan' => $tradoTambahanAbsensi->statusjeniskendaraan,
            'keterangan' => '',
            'absen_id' => '',
            'jam' => $jam,
            'modifiedby' => auth('api')->user()->user,
            'statustambahantrado' => $idstatustambahantrado,
            
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
    function processDestroyToAbsensi(TradoTambahanAbsensi $tradoTambahanAbsensi) {
        
        $absensiSupirHeader = AbsensiSupirHeader::where('tglbukti',$tradoTambahanAbsensi->tglabsensi)->first();
        $jam = date('H:i',strtotime('now'));
        $detailLog='';
        $detailLog='';
        $absensiSupirDetail = (new AbsensiSupirDetail())->from(DB::raw("absensisupirdetail as detail with (readuncommitted)"))
            ->select('detail.id','header.nobukti','detail.uangjalan')
            ->whereRaw("detail.trado_id = $tradoTambahanAbsensi->trado_id and header.tglbukti = '$tradoTambahanAbsensi->tglabsensi' and detail.statusjeniskendaraan = $tradoTambahanAbsensi->statusjeniskendaraan and (detail.supir_id = $tradoTambahanAbsensi->supir_id or detail.supirold_id = $tradoTambahanAbsensi->supir_id)")
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
