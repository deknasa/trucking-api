<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class Acl extends MyModel
{
    use HasFactory;

    protected $table = 'acl';

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

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();


        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'aclController';


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


            Schema::create($temtabel, function (Blueprint $table) {
                $table->longText('rolename')->nullable();
                $table->integer('role_id')->nullable();
                $table->integer('id')->nullable();
                $table->integer('id_')->nullable();
                $table->string('modifiedby', 100)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
            });

            $query = DB::table($this->table)->from(
                DB::raw($this->table . " with (readuncommitted)")
            )->select(
                DB::raw("role.rolename as rolename,
                            acl.role_id as role_id,
                            min(acl.id) as id,
                            min(acl.id) as id_,
                            max(acl.modifiedby) as modifiedby,
                            max(acl.created_at) as created_at,
                                max(acl.updated_at) as updated_at"),
                DB::raw("'Laporan Absen Trado' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")

            )
                ->Join(DB::raw("role with (readuncommitted)"), 'acl.role_id', '=', 'role.id')
                ->groupby('acl.role_id', 'role.rolename');


            DB::table($temtabel)->insertUsing([
                'rolename',
                'role_id',
                'id',
                'id_',
                'modifiedby',
                'created_at',
                'updated_at',
            ], $query);
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

        $query = DB::table(DB::raw($temtabel))->from(
            DB::raw(DB::raw($temtabel) . " a with (readuncommitted)")
        )
            ->select(
                'a.rolename',
                'a.role_id',
                'a.id',
                'a.id_',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
            );


        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function getdata($userid)
    {

        $tempmenu = '##tempmenu' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempmenu, function ($table) {
            $table->integer('aco_id')->nullable();
            $table->string('menu', 1000)->nullable();
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
            $table->integer('id')->nullable();
            $table->string('class', 1000)->nullable();
            $table->string('method', 1000)->nullable();
            $table->string('nama', 1000)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->longText('keterangan')->nullable();
        });

        $queryacos2 = DB::table("acos")->from(
            DB::raw("acos a with (readuncommitted)")
        )
            ->select(
                'a.id',
                DB::raw("isnull(c.menu,isnull(c1.menu,'')) as class"),
                'a.method',
                'a.nama',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
                db::raw("isnull(a.keterangan,'') as keterangan"),
            )
            ->leftjoin(DB::raw($tempacos . " b"), 'a.id', 'b.id')
            ->leftjoin(DB::raw($tempmenu . " c"), 'b.idindex', 'c.aco_id')
            ->leftjoin(DB::raw($tempacos . " b1"), 'a.idheader', 'b1.id')
            ->leftjoin(DB::raw($tempmenu . " c1"), 'b1.idindex', 'c1.aco_id')
            ->whereRaw("isnull(c.menu,isnull(c1.menu,''))<>''");

        DB::table($tempacos2)->insertUsing([
            'id',
            'class',
            'method',
            'nama',
            'modifiedby',
            'created_at',
            'updated_at',
            'keterangan',
        ], $queryacos2);



        $query = DB::table($tempacos2)->from(
            db::raw($tempacos2 . " a")
        )
            ->select(
                'a.id',
                'a.class',
                'a.keterangan',
                'a.nama',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at'
            )
            ->join(db::raw("useracl c with (readuncommitted)"), 'a.id', 'c.aco_id')
            ->where('c.user_id', '=', $userid)
            ->orderby('a.id', 'asc');

        return $query;
    }

    public function getdataedit($role_id )
    {

        $tempmenu = '##tempmenu' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempmenu, function ($table) {
            $table->integer('aco_id')->nullable();
            $table->string('menu', 1000)->nullable();
            $table->string('menukode', 1000)->nullable();
        });


        $param1=0;
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
                $join->on(db::raw("isnull(b.aco_id,0)"), '=', DB::raw( $param1 ));
            })
            ->leftjoin(db::raw("menu c with (readuncommitted)"), function ($join) use ($param1) {
                $join->on(db::raw("substring(a.menukode,1,2)"), '=', 'c.menukode');
                $join->on(db::raw("isnull(c.aco_id,0)"), '=', DB::raw( $param1 ));
            })
            ->leftjoin(db::raw("menu d with (readuncommitted)"), function ($join) use ($param1) {
                $join->on(db::raw("substring(a.menukode,1,3)"), '=', 'd.menukode');
                $join->on(db::raw("isnull(d.aco_id,0)"), '=', DB::raw( $param1 ));
            })
            ->leftjoin(db::raw("menu e with (readuncommitted)"), function ($join) use ($param1) {
                $join->on(db::raw("substring(a.menukode,1,4)"), '=', 'e.menukode');
                $join->on(db::raw("isnull(e.aco_id,0)"), '=', DB::raw( $param1 ));
            })
            ->leftjoin(db::raw("menu f with (readuncommitted)"), function ($join) use ($param1) {
                $join->on(db::raw("substring(a.menukode,1,5)"), '=', 'f.menukode');
                $join->on(db::raw("isnull(f.aco_id,0)"), '=', DB::raw( $param1 ));
            })
            ->leftjoin(db::raw("menu g with (readuncommitted)"), function ($join) use ($param1) {
                $join->on(db::raw("substring(a.menukode,1,6)"), '=', 'g.menukode');
                $join->on(db::raw("isnull(g.aco_id,0)"), '=', DB::raw( $param1 ));
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
            
            
            $param1='index';
            $queryacos = DB::table("acos")->from(
                DB::raw("acos a with (readuncommitted)")
            )
                ->select(
                    'a.id',
                    'b.id as idindex',
                )
                ->leftjoin(db::raw("acos b with (readuncommitted)"), function ($join) use ($param1) {
                    $join->on('a.class', '=', 'b.class');
                    $join->on('b.method', '=', DB::raw("'". $param1 ."'"));
                });


                DB::table($tempacos)->insertUsing([
                    'id',
                    'idindex',
                ], $queryacos);



                $tempacos2 = '##tempacos2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempacos2, function ($table) {
                    $table->id();        
                    $table->integer('idacos')->nullable();
                    $table->string('class',1000)->nullable();
                    $table->string('method',1000)->nullable();
                    $table->string('nama',1000)->nullable();
                    $table->string('menukode', 1000)->nullable();
                    $table->string('modifiedby',50)->nullable();
                    $table->integer('idheader')->nullable();                    
                    $table->dateTime('created_at')->nullable();
                    $table->dateTime('updated_at')->nullable();
                    $table->longtext('keterangan')->nullable();

                });  

                $queryacos2 = DB::table("acos")->from(
                    DB::raw("acos a with (readuncommitted)")
                )
                    ->select(
                        'a.id as idacos',
                        DB::raw("replace(isnull(c.menu,isnull(c1.menu,'')),'agen','CUSTOMER') as class"),
                        'a.method',
                        'a.nama',
                        db::raw("isnull(a.idheader,0) as idheader"),                        
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
                    ->OrderBy(db::raw("isnull(c.menukode,isnull(c1.menukode,''))"),'asc');
                
                    DB::table($tempacos2)->insertUsing([
                        'idacos',
                        'class',
                        'method',
                        'nama',
                        'idheader',                        
                        'menukode',
                        'modifiedby',
                        'created_at',
                        'updated_at',
                        'keterangan',
                    ], $queryacos2);
                    
                 
      

        $tempacl = '##tempacl' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempacl, function ($table) {
            $table->id();        
            $table->integer('aco_id')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

        });  

        $queryacl=db::table("acl")->from(db::raw("acl a with (readuncommitted)"))
            ->select(
                'a.aco_id',
                db::raw("max(a.modifiedby) as modifiedby"),
                db::raw("max(a.created_at) as created_at"),
                db::raw("max(a.updated_at) as updated_at"),
                
            )
            ->where('a.role_id',$role_id)
            ->groupby('a.aco_id');

            DB::table($tempacl)->insertUsing([
                'aco_id',
                'modifiedby',
                'created_at',
                'updated_at',
            ], $queryacl);            

            
            // DD(db::table($tempacl)->get());

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
                DB::raw("isnull(a.keterangan,'')+(case when isnull(a.idheader,0)=0 then '' else ' Detail' end) as keterangan"),

                // 'c.aco_id'
                //  DB::raw("'AKTIF'   as status"),

            )
            ->join(db::raw($tempacl ." c "), 'a.idacos', 'c.aco_id')

            ->orderby('a.id','asc');
                
            // dd($query->get());
            return $query;
    }

    public function getAclRole($roleid)
    {

        $this->setRequestParameters();
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'UserAclController';

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
            ], $this->getdataedit($roleid));
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
            db::raw($temtabel . " acl")
        )
            ->select(
                'acl.id',
                'acl.acosid',
                'acl.class',
                'acl.method',
                'acl.nama',
                'acl.modifiedby',
                'acl.created_at',
                'acl.updated_at',
                'acl.menukode',
                'acl.keterangan',
            );


        // $query = DB::table($this->table);

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        // $this->selectColumns($query);
        $this->sort($query);

        $this->filter($query);

        $this->paginate($query);

        $data = $query->get();
        // dd($data);
        return $data;
        // $this->setRequestParameters();

        // $this->totalRows = $query->count();
        // $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        // $this->sort($query);
        // $this->filter($query);
        // $this->paginate($query);

        // return $query->get();
    }
    public function getdataaclrole($roleid)
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

            // dd($querymenu->get());

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
        // dd('test');
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
            ->OrderBy(db::raw("isnull(c.menukode,isnull(c1.menukode,''))"),'asc');
            // ->OrderBy('a.id','asc');

            // DD($queryacos2->get());

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

        
// dd(db::table($tempacos2)->orderby('id','asc')->get());

$tempacl = '##tempacl' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
Schema::create($tempacl, function ($table) {
    $table->id();        
    $table->integer('aco_id')->nullable();
    $table->string('modifiedby', 50)->nullable();
    $table->dateTime('created_at')->nullable();
    $table->dateTime('updated_at')->nullable();    
});  

$queryacl=db::table("acl")->from(db::raw("acl a with (readuncommitted)"))
    ->select(
        'a.aco_id',
        db::raw("max(a.modifiedby) as modifiedby"),
        db::raw("max(a.created_at) as created_at"),
        db::raw("max(a.updated_at) as updated_at"),
    )
    ->where('a.role_id',$roleid)
    ->groupby('a.aco_id');

    DB::table($tempacl)->insertUsing([
        'aco_id',
        'modifiedby',
        'created_at',
        'updated_at',
    ], $queryacl);       


        $query = DB::table($tempacos2)->from(
            db::raw($tempacos2 . " a")
        )
            ->select(
                'a.id as id',
                'a.idacos as idacos',
                'a.class',
                DB::raw("isnull(a.method,'') as method"),
                'a.nama',
                'c.modifiedby',
                'c.created_at',
                'c.updated_at',
                'a.menukode',
                DB::raw("isnull(a.keterangan,'') as keterangan"),
            )
            ->join(db::raw($tempacl ." c "), 'a.id', 'c.aco_id')
            ->orderby('a.id', 'asc');

        // dd( $query->get());
        return $query;
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('modifiedby', 30)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $query = Acl::from(
            DB::raw("Acl with (readuncommitted)")
        )->select(
            DB::raw("acl.role_id as id,
                        max(acl.modifiedby) as modifiedby,
                        max(acl.created_at) as created_at,
                            max(acl.updated_at) as updated_at")
        )
            ->Join(DB::raw("role with (readuncommitted)"), 'acl.role_id', '=', 'role.id')
            ->groupby('acl.role_id');

        DB::table($temp)->insertUsing(['id', 'modifiedby', 'created_at', 'updated_at'], $query);

        return $temp;
    }

    public function sort($query)
    {
        // return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        return $query->orderBy( 'acl.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        // if ($filters['field'] == 'rolename') {
                        //     $query = $query->where('role.rolename', 'LIKE', "%$filters[data]%");
                        // } else if ($filters['field'] == 'class') {
                        //     $query = $query->where('acos.class', 'LIKE', "%$filters[data]%");
                        // } else if ($filters['field'] == 'method') {
                        //     $query = $query->where('acos.method', 'LIKE', "%$filters[data]%");
                        // } else if ($filters['field'] == 'created_at') {
                        //     $query = $query->where('acos.created_at', 'LIKE', "%$filters[data]%");
                        // } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->where( 'acl.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        // }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        // if ($filters['field'] == 'rolename') {
                        //     $query = $query->orWhere('role.rolename', 'LIKE', "%$filters[data]%");
                        // } elseif ($filters['field'] == 'class') {
                        //     $query = $query->orWhere('acos.class', 'LIKE', "%$filters[data]%");
                        // } elseif ($filters['field'] == 'method') {
                        //     $query = $query->orWhere('acos.method', 'LIKE', "%$filters[data]%");
                        // } elseif ($filters['field'] == 'created_at') {
                        //     $query = $query->orWhere('acos.created_at', 'LIKE', "%$filters[data]%");
                        // } else {
                        //     $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->orWhere('acl.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        // }
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

    public function processStore($data)
    {
        $acl = new Acl();
        $acl->aco_id = $data['aco_id'];
        $acl->role_id = $data['role_id'];
        $acl->modifiedby = auth('api')->user()->name;
        $acl->info = html_entity_decode(request()->info);

        if (!$acl->save()) {
            throw new \Exception("Error storing acl.");
        }

        return $acl;
    }
}
