<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;



class ServiceOutHeader extends MyModel
{
    use HasFactory;

    protected $table = 'serviceoutheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function serviceoutdetail()
    {
        return $this->hasMany(ServiceOutDetail::class, 'serviceout_id');
    }

    public function get()
    {
        $this->setRequestParameters();
        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';
        $query = DB::table($this->table)->from(DB::raw("serviceoutheader with (readuncommitted)"))
            ->select(
                'serviceoutheader.id',
                'serviceoutheader.nobukti',
                'serviceoutheader.tglbukti',

                'trado.kodetrado as trado_id',
                'statuscetak.memo as statuscetak',

                'serviceoutheader.tglkeluar',
                'serviceoutheader.modifiedby',
                'serviceoutheader.created_at',
                'serviceoutheader.updated_at'

            )
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'serviceoutheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'serviceoutheader.trado_id', 'trado.id');
        if (request()->tgldari) {
            $query->whereBetween('serviceoutheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(serviceoutheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(serviceoutheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("serviceoutheader.statuscetak", $statusCetak);
        }

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

        $query = DB::table('serviceoutheader')->select(
            'serviceoutheader.id',
            'serviceoutheader.nobukti',
            'serviceoutheader.tglbukti',
            'serviceoutheader.trado_id',

            'trado.kodetrado as trado',
            'statuscetak.memo as statuscetak',

            'serviceoutheader.tglkeluar',
            'serviceoutheader.modifiedby',
            'serviceoutheader.created_at',
            'serviceoutheader.updated_at'

        )
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'serviceoutheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'serviceoutheader.trado_id', 'trado.id')
            ->where('serviceoutheader.id', $id);
        $data = $query->first();

        return $data;
    }

    public function selectColumns($query)
    { //sesuaikan dengan createtemp

        return $query->select(
            DB::raw(
                "$this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            'trado.kodetrado as trado_id',
            $this->table.tglkeluar,
            'statuscetak.memo as statuscetak',

            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )

        )
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'serviceoutheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'serviceoutheader.trado_id', 'trado.id');
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->string('trado_id')->nullable();
            $table->date('tglkeluar')->nullable();
            $table->string('statuscetak', 50)->nullable();

            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });
        if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
            request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
        }
        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        $models = $query->whereBetween('serviceoutheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti',  'trado_id', 'tglkeluar', 'statuscetak', 'modifiedby', 'created_at', 'updated_at'], $models);


        return  $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'trado_id') {
            return $query->orderBy('trado.kodetrado', $this->params['sortOrder']);
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
                            if ($filters['field'] == 'statuscetak') {
                                $query = $query->where('statuscetak.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'trado_id') {
                                $query = $query->where('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglkeluar') {
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
                                if ($filters['field'] == 'statuscetak') {
                                    $query = $query->orWhere('statuscetak.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'trado_id') {
                                    $query = $query->orWhere('trado.kodetrado', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglkeluar') {
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
        if (request()->cetak && request()->periode) {
            $query->where('serviceoutheader.statuscetak', '<>', request()->cetak)
                ->whereYear('serviceoutheader.tglbukti', '=', request()->year)
                ->whereMonth('serviceoutheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function processStore(array $data): ServiceOutHeader
    {
        $group = 'SERVICE OUT BUKTI';
        $subgroup = 'SERVICE OUT BUKTI';

        $format = DB::table('parameter')
            ->where('grp', $group)
            ->where('subgrp', $subgroup)
            ->first();

        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $serviceout = new ServiceOutHeader();
        $serviceout->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $serviceout->trado_id = $data['trado_id'];
        $serviceout->tglkeluar = date('Y-m-d H:i:s', strtotime($data['tglkeluar']));
        $serviceout->statusformat =  $format->id;
        $serviceout->statuscetak = $statusCetak->id;
        $serviceout->modifiedby = auth('api')->user()->name;
        $serviceout->info = html_entity_decode(request()->info);

        $serviceout->nobukti = (new RunningNumberService)->get($group, $subgroup, $serviceout->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$serviceout->save()) {
            throw new \Exception("Error storing service in header.");
        }

        $serviceOutHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($serviceout->getTable()),
            'postingdari' => 'ENTRY SERVICE OUT HEADER',
            'idtrans' => $serviceout->id,
            'nobuktitrans' => $serviceout->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $serviceout->toArray(),
            'modifiedby' => $serviceout->modifiedby
        ]);

        /* Store detail */
        $detaillog = [];
        for ($i = 0; $i < count($data['keterangan_detail']); $i++) {
            $datadetail = (new ServiceOutDetail())->processStore($serviceout, [
                'serviceout_id' => $serviceout->id,
                'nobukti' => $serviceout->nobukti,
                'servicein_nobukti' => $data['servicein_nobukti'][$i],
                'keterangan' => $data['keterangan_detail'][$i],
                'modifiedby' => $serviceout->modifiedby,
            ]);

            $detaillog[] = $datadetail->toArray();
        }


        (new LogTrail())->processStore([
            'namatabel' => strtoupper($datadetail->getTable()),
            'postingdari' => 'ENTRY SERVICE OUT',
            'idtrans' =>  $serviceOutHeaderLogTrail->id,
            'nobuktitrans' => $serviceout->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $detaillog,
            'modifiedby' => auth('api')->user()->user,
        ]);


        return $serviceout;
    }

    public function processUpdate(ServiceOutHeader $serviceoutheader, array $data): ServiceOutHeader
    {
        $getTgl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'EDIT TANGGAL BUKTI')->where('subgrp', 'SERVICE OUT')->first();

        if (trim($getTgl->text) == 'YA') {
            $group = 'SERVICE OUT BUKTI';
            $subgroup = 'SERVICE OUT BUKTI';

            $querycek = DB::table('serviceoutheader')->from(
                DB::raw("serviceoutheader a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.id', $serviceoutheader->id)
                ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
                ->first();

            if (isset($querycek)) {
                $nobukti = $querycek->nobukti;
            } else {
                $nobukti = (new RunningNumberService)->get($group, $subgroup, $serviceoutheader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
            }

            $serviceoutheader->nobukti = $nobukti;
            $serviceoutheader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        }

        $serviceoutheader->trado_id = $data['trado_id'];
        $serviceoutheader->tglkeluar = date('Y-m-d H:i:s', strtotime($data['tglkeluar']));
        $serviceoutheader->modifiedby = auth('api')->user()->name;
        $serviceoutheader->info = html_entity_decode(request()->info);

        if (!$serviceoutheader->save()) {
            throw new \Exception("Error updating service in header.");
        }

        $serviceOutHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($serviceoutheader->getTable()),
            'postingdari' => 'EDIT SERVICE OUT HEADER',
            'idtrans' => $serviceoutheader->id,
            'nobuktitrans' => $serviceoutheader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $serviceoutheader->toArray(),
            'modifiedby' => $serviceoutheader->modifiedby
        ]);

        ServiceOutDetail::where('serviceout_id', $serviceoutheader->id)->lockForUpdate()->delete();

        $detaillog = [];
        for ($i = 0; $i < count($data['keterangan_detail']); $i++) {

            $datadetail = (new ServiceOutDetail())->processStore($serviceoutheader, [
                'serviceout_id' => $serviceoutheader->id,
                'nobukti' => $serviceoutheader->nobukti,
                'servicein_nobukti' => $data['servicein_nobukti'][$i],
                'keterangan' => $data['keterangan_detail'][$i],
                'modifiedby' => $serviceoutheader->modifiedby,
            ]);

            $detaillog[] = $datadetail->toArray();
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($datadetail->getTable()),
            'postingdari' => 'EDIT SERVICE OUT DETAIL',
            'idtrans' =>  $serviceOutHeaderLogTrail->id,
            'nobuktitrans' => $serviceoutheader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $detaillog,
            'modifiedby' => $serviceoutheader->modifiedby,
        ]);

        return $serviceoutheader;
    }

    public function processDestroy($id): ServiceOutHeader
    {
        $getDetail = ServiceOutDetail::lockForUpdate()->where('serviceout_id', $id)->get();

        $serviceOut = new ServiceOutHeader();
        $serviceOut = $serviceOut->lockAndDestroy($id);

        $serviceOutHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($serviceOut->getTable()),
            'postingdari' => 'DELETE SERVICE OUT HEADER',
            'idtrans' => $serviceOut->id,
            'nobuktitrans' => $serviceOut->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $serviceOut->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'SERVICEOUTDETAIL',
            'postingdari' => 'DELETE SERVICE OUT DETAIL',
            'idtrans' => $serviceOutHeaderLogTrail['id'],
            'nobuktitrans' => $serviceOut->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $getDetail->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $serviceOut;
    }

    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($this->table)->from(DB::raw("serviceoutheader with (readuncommitted)"))
            ->select(
                'serviceoutheader.id',
                'serviceoutheader.nobukti',
                'serviceoutheader.tglbukti',
                'trado.kodetrado as trado_id',
                'serviceoutheader.tglkeluar',
                'statuscetak.memo as statuscetak',
                "statuscetak.id as  statuscetak_id",
                'serviceoutheader.jumlahcetak',
                DB::raw("'Bukti Service Out' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'serviceoutheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'serviceoutheader.trado_id', 'trado.id');

        $data = $query->first();
        return $data;
    }
}
