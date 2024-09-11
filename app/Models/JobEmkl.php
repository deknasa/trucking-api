<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use App\Services\RunningNumberService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobEmkl extends MyModel
{
    use HasFactory;
    
    protected $table = 'jobemkl';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();
        $query = DB::table($this->table)->from(
            DB::raw($this->table . " as jobemkl")
        )
            ->select(
                "jobemkl.id",
                "jobemkl.nobukti",
                "jobemkl.tglbukti",
                "jobemkl.shipper_id",
                "pelanggan.namapelanggan as shipper",
                "jobemkl.tujuan_id",
                "tujuan.keterangan as tujuan",
                "jobemkl.container_id",
                "container.keterangan as container",
                "jobemkl.jenisorder_id",
                "jenisorder.keterangan as jenisorder",
                "jobemkl.kapal",
                "jobemkl.destination",
                "jobemkl.nocont",
                "jobemkl.noseal",
                "jobemkl.statusapprovaledit",
                "jobemkl.tglapprovaledit",
                "jobemkl.userapprovaledit",
                "jobemkl.tglbataseditjobemkl",
                "jobemkl.statusformat",
                "jobemkl.modifiedby",
                "jobemkl.editing_by",
                "jobemkl.created_at",
                "jobemkl.updated_at", 
                DB::raw("'Laporan Job EMKL' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            // ->whereBetween('jobemkl.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'jobemkl.container_id', '=', 'container.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'jobemkl.jenisorder_id', '=', 'jenisorder.id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'jobemkl.shipper_id', '=', 'pelanggan.id')
            ->leftJoin(DB::raw("tujuan with (readuncommitted)"), 'jobemkl.tujuan_id', '=', 'tujuan.id')
            // ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'jobemkl.statuslangsir', '=', 'parameter.id')
            // ->leftJoin(DB::raw("parameter AS param2 with (readuncommitted)"), 'jobemkl.statusperalihan', '=', 'param2.id')
            ->leftJoin(DB::raw("parameter AS param3 with (readuncommitted)"), 'jobemkl.statusapprovaledit', '=', 'param3.id');
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

        $data = JobEmkl::from(DB::raw("jobemkl with (readuncommitted)"))
            ->select(
                "jobemkl.id",
                "jobemkl.nobukti",
                "jobemkl.tglbukti",
                "jobemkl.shipper_id",
                "pelanggan.namapelanggan as shipper",
                "jobemkl.tujuan_id",
                "tujuan.keterangan as tujuan",
                "jobemkl.container_id",
                "container.keterangan as container",
                "jobemkl.jenisorder_id",
                "jenisorder.keterangan as jenisorder",
                "jobemkl.kapal",
                "jobemkl.destination",
                "jobemkl.nocont",
                "jobemkl.noseal",
                "jobemkl.statusapprovaledit",
                "jobemkl.tglapprovaledit",
                "jobemkl.userapprovaledit",
                "jobemkl.tglbataseditjobemkl",
                "jobemkl.statusformat",
                "jobemkl.modifiedby",
                "jobemkl.editing_by",
                "jobemkl.created_at",
                "jobemkl.updated_at",
            ) 
            // ->whereBetween('jobemkl.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'jobemkl.container_id', '=', 'container.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'jobemkl.jenisorder_id', '=', 'jenisorder.id')
            ->leftJoin(DB::raw("tujuan with (readuncommitted)"), 'jobemkl.tujuan_id', '=', 'tujuan.id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'jobemkl.shipper_id', '=', 'pelanggan.id')
            // ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'jobemkl.statuslangsir', '=', 'parameter.id')
            // ->leftJoin(DB::raw("parameter AS param2 with (readuncommitted)"), 'jobemkl.statusperalihan', '=', 'param2.id')
            ->leftJoin(DB::raw("parameter AS param3 with (readuncommitted)"), 'jobemkl.statusapprovaledit', '=', 'param3.id')
            ->where('jobemkl.id', $id)->first();

        return $data;
    }

    
    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                    "jobemkl.id",
                    "jobemkl.nobukti",
                    "jobemkl.tglbukti",
                    "jobemkl.shipper_id",
                    "pelanggan.namapelanggan as shipper",
                    "jobemkl.tujuan_id",
                    "tujuan.keterangan as tujuan",
                    "jobemkl.container_id",
                    "container.keterangan as container",
                    "jobemkl.jenisorder_id",
                    "jenisorder.keterangan as jenisorder",
                    "jobemkl.kapal",
                    "jobemkl.destination",
                    "jobemkl.nocont",
                    "jobemkl.noseal",
                    "jobemkl.statusapprovaledit",
                    "jobemkl.tglapprovaledit",
                    "jobemkl.userapprovaledit",
                    "jobemkl.tglbataseditjobemkl",
                    "jobemkl.statusformat",
                    "jobemkl.modifiedby",
                    "jobemkl.editing_by",
                    "jobemkl.created_at",
                    "jobemkl.updated_at",
                
            )
            ->whereBetween('jobemkl.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'jobemkl.container_id', '=', 'container.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'jobemkl.jenisorder_id', '=', 'jenisorder.id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'jobemkl.shipper_id', '=', 'pelanggan.id')
            ->leftJoin(DB::raw("tujuan with (readuncommitted)"), 'jobemkl.tujuan_id', '=', 'tujuan.id')
            // ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'jobemkl.statuslangsir', '=', 'parameter.id')
            // ->leftJoin(DB::raw("parameter AS param2 with (readuncommitted)"), 'jobemkl.statusperalihan', '=', 'param2.id')
            ->leftJoin(DB::raw("parameter AS param3 with (readuncommitted)"), 'jobemkl.statusapprovaledit', '=', 'param3.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('tglbukti', 50)->nullable();
            $table->string('shipper_id', 50)->nullable();
            $table->string('shipper', 50)->nullable();
            $table->string('tujuan_id', 50)->nullable();
            $table->string('tujuan', 50)->nullable();
            $table->string('container_id', 50)->nullable();
            $table->string('container', 50)->nullable();
            $table->string('jenisorder_id', 50)->nullable();
            $table->string('jenisorder', 50)->nullable();
            $table->string('kapal', 50)->nullable();
            $table->string('destination', 50)->nullable();
            $table->string('nocont', 50)->nullable();
            $table->string('noseal', 50)->nullable();
            $table->string('statusapprovaledit', 50)->nullable();
            $table->string('tglapprovaledit', 50)->nullable();
            $table->string('userapprovaledit', 50)->nullable();
            $table->string('tglbataseditjobemkl', 50)->nullable();
            $table->string('statusformat', 50)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->string('editing_by', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'shipper_id',
            'shipper',
            'tujuan_id',
            'tujuan',
            'container_id',
            'container',
            'jenisorder_id',
            'jenisorder',
            'kapal',
            'destination',
            'nocont',
            'noseal',
            'statusapprovaledit',
            'tglapprovaledit',
            'userapprovaledit',
            'tglbataseditjobemkl',
            'statusformat',
            'modifiedby',
            'editing_by',
            'created_at',
            'updated_at',
        ], $models);


        return  $temp;
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
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statusaktif') {
                                $query = $query->where('parameter.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'shipper' ) {
                                $query = $query->whereRaw("pelanggan.namapelanggan LIKE '%". escapeLike($filters['data']) ."%' escape '|'");
                            } else if ($filters['field'] == 'tujuan' ) {
                                $query = $query->whereRaw("tujuan.keterangan LIKE '%". escapeLike($filters['data']) ."%' escape '|'");
                            } else if ($filters['field'] == 'container' ) {
                                $query = $query->whereRaw("container.keterangan LIKE '%". escapeLike($filters['data']) ."%' escape '|'");
                            } else if ($filters['field'] == 'jenisorder' ) {
                                $query = $query->whereRaw("jenisorder.keterangan LIKE '%". escapeLike($filters['data']) ."%' escape '|'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->where('jobemkl.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw('jobemkl' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if (!array_key_exists("field",$filters)) {
                                // dd($filters);
                            } else if ($filters['field'] != '') {
                                if ($filters['field'] == 'statusaktif') {
                                    $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'shipper' ) {
                                    $query = $query->orWhereRaw("pelanggan.namapelanggan LIKE '%". escapeLike($filters['data']) ."%' escape '|'");
                                } else if ($filters['field'] == 'tujuan' ) {
                                    $query = $query->orWhereRaw("tujuan.keterangan LIKE '%". escapeLike($filters['data']) ."%' escape '|'");
                                } else if ($filters['field'] == 'container' ) {
                                    $query = $query->orWhereRaw("container.keterangan LIKE '%". escapeLike($filters['data']) ."%' escape '|'");
                                } else if ($filters['field'] == 'jenisorder' ) {
                                    $query = $query->orWhereRaw("jenisorder.keterangan LIKE '%". escapeLike($filters['data']) ."%' escape '|'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else {
                                    // $query = $query->orWhere('jobemkl.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw('jobemkl' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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


    public function processStore(array $data, JobEmkl $jobEmkl): JobEmkl
    {
        $fetchGrp = Parameter::where('grp', 'JOB EMKL BUKTI')->where('grp', 'JOB EMKL BUKTI')->first();
        $group = $fetchGrp->grp;
        $subGroup = $fetchGrp->subgrp;
        $statusformat = $fetchGrp->text;
       
        $jobEmkl->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $jobEmkl->shipper_id = $data['shipper_id'];
        $jobEmkl->tujuan_id = $data['tujuan_id'];
        $jobEmkl->container_id = $data['container_id'];
        $jobEmkl->jenisorder_id = $data['jenisorder_id'];
        $jobEmkl->kapal = $data['kapal'];
        $jobEmkl->destination = $data['destination'];
        $jobEmkl->nocont = $data['nocont'];
        $jobEmkl->noseal = $data['noseal'];
        $jobEmkl->statusformat = $fetchGrp->id;
        $jobEmkl->modifiedby = auth('api')->user()->name;
        $jobEmkl->info = html_entity_decode(request()->info);
        $data['sortname'] = $data['sortname'] ?? 'id';
        $data['sortorder'] = $data['sortorder'] ?? 'asc';
        $jobEmkl->nobukti = (new RunningNumberService)->get($group, $subGroup, $jobEmkl->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$jobEmkl->save()) {
            throw new \Exception('Error storing JOB EMKL.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($jobEmkl->getTable()),
            'postingdari' => 'ENTRY JOB EMKL',
            'idtrans' => $jobEmkl->id,
            'nobuktitrans' => $jobEmkl->id,
            'aksi' => 'ENTRY',
            'datajson' => $jobEmkl->toArray(),
            'modifiedby' => $jobEmkl->modifiedby
        ]);

        return $jobEmkl;
    }

    public function processUpdate(JobEmkl $jobEmkl, array $data) {
        $jobEmkl->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $jobEmkl->shipper_id = $data['shipper_id'];
        $jobEmkl->tujuan_id = $data['tujuan_id'];
        $jobEmkl->container_id = $data['container_id'];
        $jobEmkl->jenisorder_id = $data['jenisorder_id'];
        $jobEmkl->kapal = $data['kapal'];
        $jobEmkl->destination = $data['destination'];
        $jobEmkl->nocont = $data['nocont'];
        $jobEmkl->noseal = $data['noseal'];
        $jobEmkl->modifiedby = auth('api')->user()->name;
        $jobEmkl->info = html_entity_decode(request()->info);
        if (!$jobEmkl->save()) {
            throw new \Exception('Error updating Job Emkl.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($jobEmkl->getTable()),
            'postingdari' => 'EDIT job Emkl',
            'idtrans' => $jobEmkl->id,
            'nobuktitrans' => $jobEmkl->id,
            'aksi' => 'EDIT',
            'datajson' => $jobEmkl->toArray(),
            'modifiedby' => $jobEmkl->modifiedby
        ]);

        return $jobEmkl;
    }

    public function processDestroy(JobEmkl $jobEmkl): JobEmkl
    {
        // $jobEmkl = new JenisOrder();
        $jobEmkl = $jobEmkl->lockAndDestroy($jobEmkl->id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($jobEmkl->getTable()),
            'postingdari' => 'DELETE Job Emkl',
            'idtrans' => $jobEmkl->id,
            'nobuktitrans' => $jobEmkl->id,
            'aksi' => 'DELETE',
            'datajson' => $jobEmkl->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $jobEmkl;
    }

}
