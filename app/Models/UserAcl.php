<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;


class UserAcl extends MyModel
{
    use HasFactory;

    protected $table = 'useracl';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get($userid)
    {
        // dd($userid);
        $this->setRequestParameters();
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'UserAclController';

        if ($proses == 'reload') {
            $temtabel = 'tempusacl' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

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

            Schema::create($temtabel, function (Blueprint $table) {
                $table->integer('id')->nullable();
                $table->integer('acosid')->nullable();
                $table->string('class', 1000)->nullable();
                $table->string('method', 1000)->nullable();
                $table->string('nama', 1000)->nullable();
                $table->string('modifiedby', 100)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->string('menukode', 100)->nullable();
                $table->string('status', 100)->nullable();
                $table->longtext('keterangan')->nullable();
            });

            DB::table($temtabel)->insertUsing([
                'id',
                'acosid',
                'class',
                'method',
                'nama',
                'modifiedby',
                'created_at',
                'updated_at',
                'menukode',
                'status',
                'keterangan',
            ], $this->getdata($userid));
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

        $query = db::table($temtabel)->from(
            db::raw($temtabel . " a")
        )
            ->select(
                'a.id',
                'a.class',
                'a.method',
                'a.nama',
                'a.keterangan',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
            );


        // $query = DB::table($this->table);

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        // $this->selectColumns($query);
        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
        // $this->setRequestParameters();

        // $this->totalRows = $query->count();
        // $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        // $this->sort($query);
        // $this->filter($query);
        // $this->paginate($query);

        // return $query->get();
    }

    public function getdata($userid)
    {

        $tempmenu = '##tempmenu' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempmenu, function ($table) {
            $table->integer('aco_id')->nullable();
            $table->string('menu', 1000)->nullable();
            $table->string('menukode', 1000)->nullable();
        });


        $param1 = 0;
        $querymenu = DB::table("menu")->from(
            DB::raw("menu a with (readuncommitted)")
        )
            ->select(
                'a.aco_id',
                DB::raw("isnull(b.menuname,'')+
                (case when isnull(c.menuname,'')='' then '' else '->'+  isnull(c.menuname,'') end)+
                (case when isnull(d.menuname,'')='' then '' else '->'+  isnull(d.menuname,'') end)+
                (case when isnull(e.menuname,'')='' then '' else '->'+  isnull(e.menuname,'') end)+
                (case when isnull(f.menuname,'')='' then '' else '->'+  isnull(f.menuname,'') end)+
                (case when isnull(g.menuname,'')='' then '' else '->'+  isnull(g.menuname,'') end)+
                 (case when isnull(a.menuname,'')='' then '' else '->'+  isnull(a.menuname,'')	 end)
                 as menu
                "),
                'a.menukode',


            )
            ->leftjoin(db::raw("menu b with (readuncommitted)"), function ($join) use ($param1) {
                $join->on(db::raw("substring(a.menukode,1,1)"), '=', 'b.menukode');
                $join->on(db::raw("isnull(b.aco_id,0)"), '=', DB::raw($param1));
            })
            ->leftjoin(db::raw("menu c with (readuncommitted)"), function ($join) use ($param1) {
                $join->on(db::raw("substring(a.menukode,1,2)"), '=', 'c.menukode');
                $join->on(db::raw("isnull(c.aco_id,0)"), '=', DB::raw($param1));
            })
            ->leftjoin(db::raw("menu d with (readuncommitted)"), function ($join) use ($param1) {
                $join->on(db::raw("substring(a.menukode,1,3)"), '=', 'd.menukode');
                $join->on(db::raw("isnull(d.aco_id,0)"), '=', DB::raw($param1));
            })
            ->leftjoin(db::raw("menu e with (readuncommitted)"), function ($join) use ($param1) {
                $join->on(db::raw("substring(a.menukode,1,4)"), '=', 'e.menukode');
                $join->on(db::raw("isnull(e.aco_id,0)"), '=', DB::raw($param1));
            })
            ->leftjoin(db::raw("menu f with (readuncommitted)"), function ($join) use ($param1) {
                $join->on(db::raw("substring(a.menukode,1,5)"), '=', 'f.menukode');
                $join->on(db::raw("isnull(f.aco_id,0)"), '=', DB::raw($param1));
            })
            ->leftjoin(db::raw("menu g with (readuncommitted)"), function ($join) use ($param1) {
                $join->on(db::raw("substring(a.menukode,1,6)"), '=', 'g.menukode');
                $join->on(db::raw("isnull(g.aco_id,0)"), '=', DB::raw($param1));
            })
            ->whereRaw("a.aco_id<>0");


        DB::table($tempmenu)->insertUsing([
            'aco_id',
            'menu',
            'menukode',
        ], $querymenu);

        $tempacos = '##tempacos' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempacos, function ($table) {
            $table->integer('id')->nullable();
            $table->integer('idindex')->nullable();
        });


        $param1 = 'index';
        $queryacos = DB::table("acos")->from(
            DB::raw("acos a with (readuncommitted)")
        )
            ->select(
                'a.id',
                'b.id as idindex',
            )
            ->leftjoin(db::raw("acos b with (readuncommitted)"), function ($join) use ($param1) {
                $join->on('a.class', '=', 'b.class');
                $join->on('b.method', '=', DB::raw("'" . $param1 . "'"));
            });


        DB::table($tempacos)->insertUsing([
            'id',
            'idindex',
        ], $queryacos);



        $tempacos2 = '##tempacos2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempacos2, function ($table) {
            $table->id();
            $table->integer('idacos')->nullable();
            $table->string('class', 1000)->nullable();
            $table->string('method', 1000)->nullable();
            $table->string('nama', 1000)->nullable();
            $table->string('menukode', 1000)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->longText('keterangan')->nullable();
        });

        $queryacos2 = DB::table("acos")->from(
            DB::raw("acos a with (readuncommitted)")
        )
            ->select(
                'a.id as idacos',
                DB::raw("replace(isnull(c.menu,isnull(c1.menu,'')),'agen','CUSTOMER') as class"),
                'a.method',
                'a.nama',
                db::raw("isnull(c.menukode,isnull(c1.menukode,'')) as menukode"),
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
                db::raw("isnull(a.keterangan,'') as keterangan")
            )
            ->leftjoin(DB::raw($tempacos . " b"), 'a.id', 'b.id')
            ->leftjoin(DB::raw($tempmenu . " c"), 'b.idindex', 'c.aco_id')
            ->leftjoin(DB::raw($tempacos . " b1"), 'a.idheader', 'b1.id')
            ->leftjoin(DB::raw($tempmenu . " c1"), 'b1.idindex', 'c1.aco_id')
            ->whereRaw("isnull(c.menu,isnull(c1.menu,''))<>''")
            ->OrderBy(db::raw("isnull(c.menukode,isnull(c1.menukode,''))"), 'asc');

        DB::table($tempacos2)->insertUsing([
            'idacos',
            'class',
            'method',
            'nama',
            'menukode',
            'modifiedby',
            'created_at',
            'updated_at',
            'keterangan',
        ], $queryacos2);




        $tempuseracl = '##tempuseracl' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempuseracl, function ($table) {
            $table->id();
            $table->integer('aco_id')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $queryacl = db::table("useracl")->from(db::raw("useracl a with (readuncommitted)"))
            ->select(
                'a.aco_id',
                db::raw("max(a.modifiedby) as modifiedby"),
                db::raw("max(a.created_at) as created_at"),
                db::raw("max(a.updated_at) as updated_at"),

            )
            ->where('a.user_id', $userid)
            ->groupby('a.aco_id');

        DB::table($tempuseracl)->insertUsing([
            'aco_id',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $queryacl);


        // DD(db::table($tempuseracl)->get());

        $query = DB::table($tempacos2)->from(
            db::raw($tempacos2 . " a")
        )
            ->select(
                'a.id',
                'a.idacos as acosid',
                'a.class',
                DB::raw("isnull(a.method,'') as method"),
                'a.nama',
                'c.modifiedby',
                'c.created_at',
                'c.updated_at',
                'a.menukode',
                DB::raw("(case when isnull(c.aco_id,0)<>0 then 'AKTIF' else 'TIDAK AKTIF'  end) as status"),
                DB::raw("isnull(a.keterangan,'') as keterangan"),

                // 'c.aco_id'
                //  DB::raw("'AKTIF'   as status"),

            )
            ->join(db::raw($tempuseracl . " c "), 'a.idacos', 'c.aco_id')

            ->orderby('a.id', 'asc');

        // dd($query->get());
        return $query;
    }

    public function sort($query)
    {
        return $query->orderBy($this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field']) {
                                if (in_array($filters['field'], ['modifiedby', 'created_at', 'updated_at'])) {
                                    $query = $query->where('acos.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                } else {
                                    $query = $query->where($filters['field'], 'LIKE', "%$filters[data]%");
                                }
                            }
                        }
                    });

                    break;
                case "OR":

                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if (in_array($filters['field'], ['modifiedby', 'created_at', 'updated_at'])) {
                                $query = $query->orWhere('acos.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            } else {
                                $query = $query->orWhere($filters['field'], 'LIKE', "%$filters[data]%");
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

    public function processDestroy($id): UserAcl
    {
        $userAcl = new UserAcl();
        $userAcl = $userAcl->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($userAcl->getTable()),
            'postingdari' => 'DELETE PARAMETER',
            'idtrans' => $userAcl->id,
            'nobuktitrans' => $userAcl->id,
            'aksi' => 'DELETE',
            'datajson' => $userAcl->toArray(),
            'modifiedby' => $userAcl->modifiedby
        ]);

        return $userAcl;
    }


    public function processStore($data)
    {
        $userAcl = new UserAcl();
        $userAcl->aco_id = $data['aco_id'];
        $userAcl->user_id = $data['user_id'];
        $userAcl->modifiedby = auth('api')->user()->name;
        $userAcl->info = html_entity_decode(request()->info);

        if (!$userAcl->save()) {
            throw new \Exception("Error storing user acl.");
        }

        return $userAcl;
    }
}
