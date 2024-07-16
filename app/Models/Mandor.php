<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\Controller;

class Mandor extends MyModel
{
    use HasFactory;

    protected $table = 'mandor';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public $detailTasId;

    public function get()
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $aktif = request()->aktif ?? '';

        $querymandor = db::table("mandordetail")->from(db::raw("mandordetail a with (readuncommitted)"))
            ->select(
                'a.mandor_id',
                'b.user as name'
                )
                ->join(db::raw("[user] b with (readuncommitted)"),'a.user_id','b.id')
                ->orderBy('a.mandor_id','asc');

        $tempmandordetail = '##tempmandordetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempmandordetail, function ($table) {
            $table->id();
            $table->unsignedBigInteger('mandor_id')->nullable();
            $table->longText('name')->nullable();
        });

        DB::table($tempmandordetail)->insertUsing([
            'mandor_id',
            'name',
        ],  $querymandor);

        $tempmandordetailrekap = '##tempmandordetailrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempmandordetailrekap, function ($table) {
            $table->id();
            $table->unsignedBigInteger('mandor_id')->nullable();
            $table->longText('name')->nullable();
        });

        $querylist=db::table($tempmandordetail)->from(db::raw($tempmandordetail . " b"))
        ->select(
            db::raw("
             distinct b.mandor_id,Stuff((SELECT DISTINCT ', ' + a.name
              FROM ".$tempmandordetail ." a
              WHERE  a.mandor_id=B.mandor_id
              FOR XML PATH('')), 1, 2, '') AS name
            ")
        );

        DB::table($tempmandordetailrekap)->insertUsing([
            'mandor_id',
            'name',
        ],  $querylist);



        $query = DB::table($this->table)->from(DB::raw("mandor with (readuncommitted)"))
            ->select(
                'mandor.id',
                'mandor.namamandor',
                'mandor.keterangan',
                'mandor.user_id',
                'user.name as user',
                'parameter.memo as statusaktif',
                'mandor.modifiedby',
                'mandor.created_at',
                'mandor.updated_at',
                DB::raw("'Laporan Mandor' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'mandor.statusaktif', '=', 'parameter.id')
            // ->leftJoin(DB::raw("[user] with (readuncommitted)"), 'mandor.user_id', '=', db::raw("[user].id"));
            ->leftJoin(DB::raw($tempmandordetailrekap . " as [user]"), 'mandor.id', db::raw("[user].mandor_id"));



        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('mandor.statusaktif', '=', $statusaktif->id);
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    function findAll($id)
    {
        return $query = DB::table($this->table)->from(DB::raw("mandor with (readuncommitted)"))
            ->select(
                'mandor.id',
                'mandor.namamandor',
                'mandor.keterangan',
                'mandor.user_id',
                'user.name as user',
                'mandor.statusaktif',
                'parameter.text as statusaktifnama',
                'mandor.modifiedby',
                'mandor.created_at',
                'mandor.updated_at',
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'mandor.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("[user] with (readuncommitted)"), 'mandor.user_id', '=', 'user.id')
            ->where('mandor.id', $id)
            ->first();
    }
    public function cekvalidasihapus($id)
    {

        $trado = DB::table('trado')
            ->from(
                DB::raw("trado as a with (readuncommitted)")
            )
            ->select(
                'a.mandor_id'
            )
            ->where('a.mandor_id', '=', $id)
            ->first();
        if (isset($trado)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Trado',
            ];


            goto selesai;
        }


        $data = [
            'kondisi' => false,
            'keterangan' => '',
        ];

        selesai:
        return $data;
    }
    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->nullable();
            $table->string('statusaktifnama', 300)->nullable();
        });

        $statusaktif = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id',
                'text'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('default', '=', 'YA')
            ->first();

        DB::table($tempdefault)->insert(["statusaktif" => $statusaktif->id, "statusaktifnama" =>$statusaktif->text ?? ""]);

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusaktifnama'
            );

        $data = $query->first();
        // dd($data);
        return $data;
    }
    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->table.id,
            $this->table.namamandor,
            $this->table.keterangan,            
            'user.name as user',
            'parameter.text as statusaktif',
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
                )
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'mandor.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("[user] with (readuncommitted)"), 'mandor.user_id', '=', db::raw("[user].id"));
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('namamandor', 1000)->nullable();
            $table->string('keterangan', 1000)->nullable();
            $table->string('user', 1000)->nullable();
            $table->string('statusaktif', 1000)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'namamandor', 'keterangan', 'user', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $models);


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
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else  if ($filters['field'] == 'user') {
                                $query = $query->whereRaw("[user].[name] LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->where('mandor.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw('mandor' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'statusaktif') {
                                    $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else  if ($filters['field'] == 'user') {
                                    $query = $query->orWhereRaw("[user].[name] LIKE '%$filters[data]%'");
                                } else {
                                    // $query = $query->orWhere('mandor.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw('mandor' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function processStore(array $data, Mandor $mandor,$connecTnl = null): Mandor
    {
        // $mandor = new Mandor();
        $mandor->namamandor = $data['namamandor'];
        $mandor->keterangan = $data['keterangan'] ?? '';
        $mandor->statusaktif = $data['statusaktif'];
        $mandor->tas_id = $data['tas_id'] ?? '';
        $mandor->modifiedby = auth('api')->user()->user;
        $mandor->info = html_entity_decode(request()->info);
        $data['sortname'] = $data['sortname'] ?? 'id';
        $data['sortorder'] = $data['sortorder'] ?? 'asc';

        if (!$mandor->save()) {
            throw new \Exception('Error storing mandor.');
        }
// dd($mandor);
        $mandorLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($mandor->getTable()),
            'postingdari' => 'ENTRY MANDOR',
            'idtrans' => $mandor->id,
            'nobuktitrans' => $mandor->id,
            'aksi' => 'ENTRY',
            'datajson' => $mandor->toArray(),
            'modifiedby' => $mandor->modifiedby
        ]);

        // dd($data['users']);
        if (is_iterable($data['users'])) {
            $mandorDetails = [];
            for ($i = 0; $i < count($data['users']); $i++) {
                $datadetail = [
                    'user_id' => $data['users'][$i],
                    'mandor_id' => $mandor->id,
                    'tas_id' => $data['detail_tas_id'][$i]??0,
                ];
                $mandorDetail = new MandorDetail();
                if ($connecTnl) {
                    $mandorDetail->setConnection('srvtnl');
                }
                
                $mandorDetail->processStore($datadetail, $mandorDetail);
                
                $mandor->detailTasId[] = $mandorDetail->id;

                $mandorDetails[] = $mandorDetail->toArray();
            }
            
            $logtrail = new LogTrail();
            if ($connecTnl) {
                $logtrail->setConnection('srvtnl');
            }
            $logtrail->processStore([
                'namatabel' => strtoupper($mandorDetail->getTable()),
                'postingdari' => 'ENTRY MANDOR DETAIL',
                'idtrans' =>  $mandorLogTrail->id,
                'nobuktitrans' => $mandor->id,
                'aksi' => 'ENTRY',
                'datajson' => $mandorDetails,
                'modifiedby' => auth('api')->user()->user,
            ]);
        }
        return $mandor;
    }

    public function processUpdate(Mandor $mandor, array $data,$connecTnl = null): Mandor
    {
        $mandor->namamandor = $data['namamandor'];
        $mandor->keterangan = $data['keterangan'] ?? '';
        $mandor->statusaktif = $data['statusaktif'];
        $mandor->modifiedby = auth('api')->user()->user;
        $mandor->info = html_entity_decode(request()->info);


        if (!$mandor->save()) {

            throw new \Exception('Error updating mandor.');
        }

        $mandorLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($mandor->getTable()),
            'postingdari' => 'EDIT MANDOR',
            'idtrans' => $mandor->id,
            'nobuktitrans' => $mandor->id,
            'aksi' => 'EDIT',
            'datajson' => $mandor->toArray(),
            'modifiedby' => $mandor->modifiedby
        ]);

        
        if (is_iterable($data['users'])) {
            $mandorDetail = new MandorDetail();
            if ($connecTnl) {
                $mandorDetail->setConnection('srvtnl');
            }
            $mandorDetail->where('mandor_id', $mandor->id)->delete();
            $mandorDetails = [];
            for ($i = 0; $i < count($data['users']); $i++) {
                $datadetail = [
                    'user_id' => $data['users'][$i],
                    'mandor_id' => $mandor->id,
                    'tas_id' => $data['detail_tas_id'][$i]??0,
                ];
                $mandorDetail = new MandorDetail();
                if ($connecTnl) {
                    $mandorDetail->setConnection('srvtnl');
                }
                $mandorDetail->processStore($datadetail, $mandorDetail);
                $mandor->detailTasId[] = $mandorDetail->id;

                $mandorDetails[] = $mandorDetail->toArray();
            }
            (new LogTrail())->processStore([
                'namatabel' => strtoupper($mandorDetail->getTable()),
                'postingdari' => 'EDIT MANDOR DETAIL',
                'idtrans' =>  $mandorLogTrail->id,
                'nobuktitrans' => $mandor->id,
                'aksi' => 'EDIT',
                'datajson' => $mandorDetails,
                'modifiedby' => auth('api')->user()->user,
            ]);
        } else {
            $checkDetail = DB::table('mandordetail')->from(DB::raw("mandordetail with (readuncommitted)"));
            if ($connecTnl) {
                $checkDetail->connection('srvtnl');
            }
            $checkDetailExist = $checkDetail->where('mandor_id', $mandor->id)->first();
            if ($checkDetailExist != '') {
                $mandorDetail = DB::table('mandordetail')->from(DB::raw("mandordetail with (readuncommitted)"));
                if ($connecTnl) {
                    $mandorDetail->connection('srvtnl');
                }
                $mandorDetail->where('mandor_id', $mandor->id)->get();
                $mandorDetail = new MandorDetail();
                if ($connecTnl) {
                    $mandorDetail->setConnection('srvtnl');
                }
                $mandorDetail->where('mandor_id', $mandor->id)->delete();

                (new LogTrail())->processStore([
                    'namatabel' => strtoupper('mandordetail'),
                    'postingdari' => 'EDIT MANDOR DELETE DETAIL',
                    'idtrans' =>  $mandorLogTrail->id,
                    'nobuktitrans' => $mandor->id,
                    'aksi' => 'EDIT',
                    'datajson' => $mandorDetail->toArray(),
                    'modifiedby' => auth('api')->user()->user,
                ]);
            }
        }

        return $mandor;
    }

    public function processDestroy(Mandor $mandor): Mandor
    {

        // $mandorDetails = MandorDetail::lockForUpdate()->where('mandor_id', $mandor->id)->get();
        // $mandor = new Mandor();
        $mandor = $mandor->lockAndDestroy($mandor->id);

        $mandorLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($mandor->getTable()),
            'postingdari' => 'DELETE MANDOR',
            'idtrans' => $mandor->id,
            'nobuktitrans' => $mandor->id,
            'aksi' => 'DELETE',
            'datajson' => $mandor->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

       

        return $mandor;
    }

    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $mandor = Mandor::find($data['Id'][$i]);

            $mandor->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            if ($mandor->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($mandor->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF MANDOR',
                    'idtrans' => $mandor->id,
                    'nobuktitrans' => $mandor->id,
                    'aksi' => $aksi,
                    'datajson' => $mandor->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }


        return $mandor;
    }
}
