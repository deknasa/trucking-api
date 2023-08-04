<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;


class KaryawanLogAbsensi extends MyModel
{
    use HasFactory;

    use HasFactory;
    protected $table = 'karyawanlogabsensi';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function get()
    {
        $this->setRequestParameters();

        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'KaryawanLogAbsensiController';

        $aktif = request()->aktif ?? '';
        if ($proses == 'reload') {
            $temtabel = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel a with (readuncommitted)")
            )
                ->select(
                    'id',
                    'class',
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            if (isset($querydata)) {
                Schema::dropIfExists($querydata->namatabel);
                DB::table('listtemporarytabel')->where('id', $querydata->id)->delete();
            }

            DB::table('listtemporarytabel')->insert(
                [
                    'class' => $class,
                    'namatabel' => $temtabel,
                    'modifiedby' => $user,
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s'),
                ]
            );

            $tempkaryawanabsen = '##tempkaryawanabsen' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempkaryawanabsen, function ($table) {
                $table->integer('idabsen')->nullable();
                $table->string('karyawan', 1000)->nullable();
            });

            $querykaryawanabsen = DB::table('logabsensi')->from(
                db::raw("logabsensi a with (readuncommitted)")
            )
                ->select(
                    'a.id as idabsen',
                    DB::raw("max(a.personname) as karyawan")
                )
                ->groupBy('a.id');



            DB::table($tempkaryawanabsen)->insertUsing([
                'idabsen',
                'karyawan',
            ], $querykaryawanabsen);



            Schema::create($temtabel, function (Blueprint $table) {
                $table->id();
                $table->integer('idabsen')->nullable();
                $table->string('karyawan', 1000)->nullable();
                $table->date('tglresign')->nullable();
                $table->integer('statusaktif')->nullable();
            });

            $memoaktif = DB::table('parameter')->from(
                db::raw("parameter a with (readuncommitted)")
            )
                ->select(
                    'id',
                    'memo'
                )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('subgrp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $querykaryawan = DB::table($tempkaryawanabsen)->from(
                db::raw($tempkaryawanabsen . " a with (readuncommitted)")
            )
                ->select(
                    'a.idabsen',
                    'a.karyawan',
                    db::raw("isnull(b.tglresign,null) as tglresign"),
                    db::raw("isnull(c.id,'" . $memoaktif->id . "') as statusaktif")
                )
                ->leftjoin(DB::raw("karyawanlogabsensi b with (readuncommitted)"), 'a.idabsen', 'b.idabsen')
                ->leftjoin(DB::raw("parameter c with (readuncommitted)"), 'b.statusaktif', 'c.id')
                ->orderBY('a.idabsen', 'asc');

            DB::table($temtabel)->insertUsing([
                'idabsen',
                'karyawan',
                'tglresign',
                'statusaktif',
            ], $querykaryawan);
        } else {
            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel with (readuncommitted)")
            )
                ->select(
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            // dd($querydata);
            $temtabel = $querydata->namatabel;
        }

        $query = DB::table($temtabel)->from(
            db::raw($temtabel . " a")
        )
            ->select(
                'idabsen as id',
                'karyawan',
                DB::raw("(case when year(isnull(a.tglresign,'1900/1/1'))=1900 then null else a.tglresign end) as tglresign"),
                'parameter.memo as statusaktif'
            )->leftJoin(DB::raw("parameter with (readuncommitted)"), 'a.statusaktif', 'parameter.id');


        // dd(request()->forReport);

        $report = request()->forReport ?? false;


        $this->filter($query);

        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('karyawan.statusaktif', '=', $statusaktif->id);
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function findAll($id)
    {

        $query = DB::table('karyawanlogabsensi')->from(
            db::raw("karyawanlogabsensi as a")
        )
            ->select(
                'a.idabsen as id',
                'b.personname as karyawan',
                'a.tglresign',
                'a.statusaktif'
            )
            ->leftJoin(DB::raw("logabsensi b with (readuncommitted)"), 'a.idabsen', 'b.id')
            ->where('a.idabsen', $id)
            ->first();
        if ($query == null) {

            $query = DB::table('logabsensi')->from(
                db::raw("logabsensi a with (readuncommitted)")
            )
                ->select(
                    'a.id',
                    DB::raw("a.personname as karyawan")
                )
                ->where('a.id', $id)
                ->first();
        }
        return $query;
    }
    public function sort($query)
    {
        return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'tglresign') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusaktif') {
                                $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'tglresign') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
                 $this->table.idabsen,
                 logabsensi.personname as karyawan,
                 $this->table.tglresign,
                 $this->table.statusaktif,
                 $this->table.modifiedby,
                 $this->table.created_at,
                 $this->table.updated_at"
            )
        )
            ->leftJoin(DB::raw("logabsensi with (readuncommitted)"), 'karyawanlogabsensi.idabsen', 'logabsensi.id');
    }

    public function createTemp($idabsen, bool $isDeleting = false)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->integer('id')->nullable();
            $table->integer('idabsen')->nullable();
            $table->string('karyawan')->default();
            $table->date('tglresign')->nullable();
            $table->integer('statusaktif')->nullable();
            $table->increments('position');
        });
        $this->setRequestParameters();
        $tempkaryawanabsen = '##tempkaryawanabsen' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $temtabel = '##temtabel' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($tempkaryawanabsen, function ($table) {
            $table->integer('idabsen')->nullable();
            $table->string('karyawan', 1000)->nullable();
        });

        $querykaryawanabsen = DB::table('logabsensi')->from(
            db::raw("logabsensi a with (readuncommitted)")
        )
            ->select(
                'a.id as idabsen',
                DB::raw("max(a.personname) as karyawan")
            )
            ->groupBy('a.id');



        DB::table($tempkaryawanabsen)->insertUsing([
            'idabsen',
            'karyawan',
        ], $querykaryawanabsen);

        Schema::create($temtabel, function (Blueprint $table) {
            $table->id();
            $table->integer('idabsen')->nullable();
            $table->string('karyawan', 1000)->nullable();
            $table->date('tglresign')->nullable();
            $table->integer('statusaktif')->nullable();
        });

        $memoaktif = DB::table('parameter')->from(
            db::raw("parameter a with (readuncommitted)")
        )
            ->select(
                'id',
                'memo'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('text', '=', 'AKTIF')
            ->first();

        $querykaryawan = DB::table($tempkaryawanabsen)->from(
            db::raw($tempkaryawanabsen . " a with (readuncommitted)")
        )
            ->select(
                'a.idabsen',
                'a.karyawan',
                db::raw("isnull(b.tglresign,null) as tglresign"),
                db::raw("isnull(c.id,'" . $memoaktif->id . "') as statusaktif")
            )
            ->leftjoin(DB::raw("karyawanlogabsensi b with (readuncommitted)"), 'a.idabsen', 'b.idabsen')
            ->leftjoin(DB::raw("parameter c with (readuncommitted)"), 'b.statusaktif', 'c.id')
            ->orderBY('a.idabsen', 'asc');

        DB::table($temtabel)->insertUsing([
            'idabsen',
            'karyawan',
            'tglresign',
            'statusaktif',
        ], $querykaryawan);


        $query = DB::table($temtabel)->from(
            DB::raw("$temtabel a with (readuncommitted)")
        )
            ->select(
                DB::raw("row_number() Over(Order By a.karyawan) as id"),
                'a.idabsen',
                'a.karyawan',
                'a.tglresign',
                'a.statusaktif'
            );
        $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'idabsen', 'karyawan', 'tglresign', 'statusaktif'], $models);
        if ($isDeleting) {
            $indexRow = request()->indexRow ?? 1;
            $limit = request()->limit ?? 10;
            $page = request()->page ?? 1;
            if ($page == 1) {
                $position = $indexRow + 1;
            } else {
                $page = $page - 1;
                $row = $page * $limit;
                $position = $indexRow + $row + 1;
            }

            if (!DB::table($temp)->where('position', '=', $position)->exists()) {
                $position -= 1;
            }

            $query = DB::table($temp)
                ->select('position', 'id')
                ->where('position', '=', $position)
                ->orderBy('position');
        } else {
            $query = DB::table($temp)->select('position')->where('idabsen', $idabsen)->orderBy('position');
        }
        return $query->first();
    }


    public function processUpdate($id, array $data): KaryawanLogAbsensi
    {
        $cekKaryawan = DB::table("karyawanlogabsensi")->from(DB::raw("karyawanlogabsensi with (readuncommitted)"))->where('idabsen', $id)->first();
        if ($cekKaryawan == null) {
            $logAbsensi = DB::table('logabsensi')->from(
                db::raw("logabsensi a with (readuncommitted)")
            )
                ->select(
                    'a.id',
                    DB::raw("a.personname as karyawan")
                )
                ->where('a.id', $id)
                ->first();

            $karyawanLogAbsensi = new KaryawanLogAbsensi();
            $karyawanLogAbsensi->idabsen = $logAbsensi->id;
            $karyawanLogAbsensi->tglresign = date('Y-m-d', strtotime($data['tglresign']));
            $karyawanLogAbsensi->statusaktif = $data['statusaktif'];
            $karyawanLogAbsensi->modifiedby = auth('api')->user()->name;
            if (!$karyawanLogAbsensi->save()) {
                throw new \Exception('Error storing KARYAWAN LOG ABSENSI.');
            }

            (new LogTrail())->processStore([
                'namatabel' => $karyawanLogAbsensi->getTable(),
                'postingdari' => 'ENTRY KARYAWAN LOG ABSENSI',
                'idtrans' => $karyawanLogAbsensi->id,
                'nobuktitrans' => $karyawanLogAbsensi->id,
                'aksi' => 'ENTRY',
                'datajson' => $karyawanLogAbsensi->toArray(),
            ]);
        } else {
            $karyawanLogAbsensi = KaryawanLogAbsensi::where('idabsen', $id)->first();
            $karyawanLogAbsensi->tglresign = date('Y-m-d', strtotime($data['tglresign']));
            $karyawanLogAbsensi->statusaktif = $data['statusaktif'];
            $karyawanLogAbsensi->modifiedby = auth('api')->user()->name;

            if (!$karyawanLogAbsensi->save()) {
                throw new \Exception('Error updating karyawanLogAbsensi.');
            }

            (new LogTrail())->processStore([
                'namatabel' => $karyawanLogAbsensi->getTable(),
                'postingdari' => 'EDIT KARYAWAN LOG ABSENSI',
                'idtrans' => $karyawanLogAbsensi->id,
                'nobuktitrans' => $karyawanLogAbsensi->id,
                'aksi' => 'EDIT',
                'datajson' => $karyawanLogAbsensi->toArray(),
            ]);
        }

        return $karyawanLogAbsensi;
    }

    public function processDestroy($id, $postingDari = ''): KaryawanLogAbsensi
    {
        $karyawanLogAbsensi = new KaryawanLogAbsensi();
        $cekKaryawan = DB::table("karyawanlogabsensi")->from(DB::raw("karyawanlogabsensi with (readuncommitted)"))->where('idabsen', $id)->first();

        if ($cekKaryawan != null) {

            $karyawanLogAbsensi = $karyawanLogAbsensi->lockAndDestroy($cekKaryawan->id);

            $karyawanLogAbsensiLogTrail = (new LogTrail())->processStore([
                'namatabel' => $karyawanLogAbsensi->getTable(),
                'postingdari' => $postingDari,
                'idtrans' => $karyawanLogAbsensi->id,
                'nobuktitrans' => $karyawanLogAbsensi->id,
                'aksi' => 'DELETE',
                'datajson' => $karyawanLogAbsensi->toArray(),
                'modifiedby' => auth('api')->user()->name
            ]);
        }
        return $karyawanLogAbsensi;
    }
}
