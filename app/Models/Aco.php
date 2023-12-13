<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
class Aco extends MyModel
{
    use HasFactory;

    protected $table = 'acos';

    public function get()
    {

        $this->setRequestParameters();
        $role_id = request()->role_id ?? 0;
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'AcoController';

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
                $table->string('class', 1000)->nullable();
                $table->string('method', 1000)->nullable();
                $table->string('nama', 1000)->nullable();
                $table->string('modifiedby', 100)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->string('menukode', 100)->nullable();
                $table->string('status', 100)->nullable();
            });

            DB::table($temtabel)->insertUsing([
                'id',
                'class',
                'method',
                'nama',
                'modifiedby',
                'created_at',
                'updated_at',
                'menukode',
                'status'
            ], $this->getdata($role_id ));
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
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
                'a.status',
            );

            // dd($query ->get());

        // $query = DB::table($this->table);

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        // $this->selectColumns($query);
        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function getdata($role_id )
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
                    $table->dateTime('created_at')->nullable();
                    $table->dateTime('updated_at')->nullable();
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
                        'a.updated_at'
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
                        'menukode',
                        'modifiedby',
                        'created_at',
                        'updated_at'
                    ], $queryacos2);
                    
                 
      

        $tempacl = '##tempacl' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempacl, function ($table) {
            $table->id();        
            $table->integer('aco_id')->nullable();
        });  

        $queryacl=db::table("acl")->from(db::raw("acl a with (readuncommitted)"))
            ->select(
                'a.aco_id'
            )
            ->where('a.role_id',$role_id)
            ->groupby('a.aco_id');

            DB::table($tempacl)->insertUsing([
                'aco_id',
            ], $queryacl);            

            
            // DD(db::table($tempacl)->get());

        $query = DB::table($tempacos2)->from(
            db::raw($tempacos2 . " a")
        )
            ->select(
                'a.idacos as id',
                'a.class',
                DB::raw("isnull(b.keterangan,a.method) as method"),
	            'a.nama',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
                'a.menukode',
                DB::raw("(case when isnull(c.aco_id,0)<>0 then 'AKTIF' else 'TIDAK AKTIF'  end) as status"),
                // 'c.aco_id'
                //  DB::raw("'AKTIF'   as status"),

            )
            ->leftjoin(db::raw("method b with (readuncommitted)"), 'a.method', 'b.method')
            ->leftjoin(db::raw($tempacl ." c "), 'a.idacos', 'c.aco_id')

            ->orderby('a.id','asc');
                
            // dd($query->get());
            return $query;
    }

    public function selectColumns($query)
    {
        return $query->select(
            "$this->table.id",
            "$this->table.class",
            "$this->table.method",
            "$this->table.nama",
            "$this->table.modifiedby",
            "$this->table.created_at",
            "$this->table.updated_at",
        );
    }

    public function sort($query)
    {
        // return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field']) {
                            $query = $query->where('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        $query = $query->orWhere('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
}
