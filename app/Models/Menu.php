<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use Illuminate\Support\Facades\Schema;


class Menu extends MyModel
{
    use HasFactory;

    protected $table = 'menu';

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

        $query = DB::table($this->table)
            ->from(DB::raw($this->table . " as menu with (readuncommitted)"))
            ->select(
                'menu.id',
                'menu.menuname',
                'menu.menuseq',
                'menu.menuparent',
                'menu2.menuname as menuparent2',
                'menu.menuicon',
                'menu.aco_id',
                'menu.link',
                'menu.menuexe',
                'menu.menukode',
                'menu.modifiedby',
                'menu.created_at',
                'menu.updated_at',
                DB::raw("'Laporan Menu' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :".auth('api')->user()->name."' as usercetak")
            )
            ->leftJoin(DB::raw("menu as menu2 with (readuncommitted)"), 'menu2.id', '=', 'menu.menuparent');

      

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        // $this->selectColumns($query);
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
                DB::raw(
                    "$this->table.id,
                $this->table.menuname,
                isnull(menu2.menuname,'') as menuparent,
                $this->table.menuicon,
                isnull(acos.nama,'') as aco_id,
                $this->table.link,
                $this->table.menuexe,
                $this->table.menukode,
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at"
                )
            )
            ->leftJoin(DB::raw("menu as menu2 with (readuncommitted)"), 'menu2.id', '=', 'menu.menuparent')
            ->leftJoin(DB::raw("acos with (readuncommitted)"), 'acos.id', '=', 'menu.aco_id');
    }
    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('menuname', 50)->nullable();
            $table->string('menuparent', 250)->nullable();
            $table->string('menuicon', 50)->nullable();
            $table->string('aco_id', 100)->nullable();
            $table->string('link', 100)->nullable();
            $table->string('menuexe', 100)->nullable();
            $table->string('menukode', 100)->nullable();
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
        DB::table($temp)->insertUsing(['id', 'menuname', 'menuparent', 'menuicon', 'aco_id', 'link', 'menuexe', 'menukode', 'modifiedby', 'created_at', 'updated_at'], $models);


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
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'menuparent') {
                            $query = $query->where('menu2.menuname', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'aco_id') {
                            $query = $query->where('acos.nama', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'menuparent') {
                            $query = $query->orWhere('menu2.menuname', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'aco_id') {
                            $query = $query->orWhere('acos.nama', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
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

    public function validasiNonController($menuname)
    {
        $validasiQuery = DB::table('menu')
            ->from(
                DB::raw("menu as a with (readuncommitted)")
            )
            ->select(
                'a.aco_id'
            )
            ->where('a.menuname', '=', $menuname)
            ->first();

        return $validasiQuery;
    }

    public function get_class_methods($class, $comment = false)
    {
        $class = 'App\Http\Controllers\Api' . '\\' . $class;
        $r = new ReflectionClass($class);
        $methods = array();

        foreach ($r->getMethods() as $m) {
            if ($m->class == $class) {
                $arr = ['name' => $m->name];
                if ($comment === true) {
                    $arr['docComment'] = $this->get_method_comment($r, $m->name);
                    // if(array_key_exists("ClassName",$arr['docComment'])) { $arr['detail'] = $arr['docComment']['ClassName'];}   else  {$arr['detail'] = [];}
                }
                $methods[] = $arr;
            }
        }
        // dd($methods);
        return $methods;
    }

    public function get_php_classes($php_code, $methods = false)
    {
        $classes = array();
        $tokens = token_get_all($php_code);

        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if ($tokens[$i - 2][0] == T_CLASS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING) {
                $classes[] = $tokens[$i][1]; // assigning class name to classes array variable

            }
        }
        return $classes;
    }

    public function listFolderFiles($controller)
    {
        $dir = base_path('app/http') . '/controllers/api/';
        $ffs = scandir($dir);
        unset($ffs[0], $ffs[1]);
        if (count($ffs) < 1)
            return;
        $i = 0;
        foreach ($ffs as $ff) {
            if (is_dir($dir . '/' . $ff))
                $this->listFolderFiles($dir . '/' . $ff);
            elseif (is_file($dir . '/' . $ff) && strpos($ff, '.php') !== false) {
                $classes = $this->get_php_classes(file_get_contents($dir . '/' . $ff));
                foreach ($classes as $class) {
                    if ($class == $controller) {


                        if (!class_exists($class)) {
                            include_once($dir . $ff);
                        }

                        $methods = $this->get_class_methods($class, true);
                        // dd($methods);
                        foreach ($methods as $method) {
                            if (isset($method['docComment']['ClassName'])) {
                                if (isset($method['docComment']['Detail1'])) {
                                    $detail1 = $method['docComment']['Detail1'];
                                } else {
                                    $detail1 = '';
                                }
                                if (isset($method['docComment']['Detail2'])) {
                                    $detail2 = $method['docComment']['Detail2'];
                                } else {
                                    $detail2 = '';
                                }
                                if (isset($method['docComment']['Detail3'])) {
                                    $detail3 = $method['docComment']['Detail3'];
                                } else {
                                    $detail3 = '';
                                }
                                $data[] = [
                                    'class' => $class,
                                    'method' => $method['name'],
                                    'name' => $method['name'] . ' ' . $class,
                                    'detail1' => trim($detail1),
                                    'detail2' => trim($detail2),
                                    'detail3' => trim($detail3)
                                ];
                            }
                        }
                    }
                }
            }
        }
        return $data ?? '';
    }

    public function get_method_comment($obj, $method)
    {
        $comment = $obj->getMethod($method)->getDocComment();
        //define the regular expression pattern to use for string matching
        $pattern = "#(@[a-zA-Z]+\s*[a-zA-Z0-9, ()_].*)#";
        //perform the regular expression on the string provided
        preg_match_all($pattern, $comment, $matches, PREG_PATTERN_ORDER);
        // dd($matches[0]);
        $comments = [];
        foreach ($matches[0] as $match) {
            $comment = preg_split('/[\s]/', $match, 2);
            $comments[trim($comment[0], '@')] = $comment[1];
        }

        return $comments;
    }

    public function processStore(array $data): Menu
    {

        $class = $this->listFolderFiles($data['controller']);
        if ($class <> '') {
            foreach ($class as $value) {

                $namaclass = str_replace('controller', '', strtolower($value['class']));

                $dataaco = (new Acos())->processStore([
                    'class' => $namaclass,
                    'method' => $value['method'],
                    'nama' => $value['name'],
                    'modifiedby' => auth('api')->user()->user,
                ]);

                if ($value['detail1'] != '') {
                    $classdetail1 = $this->listFolderFiles($value['detail1']);
                    foreach ($classdetail1 as $valuedetail1) {
                        $namaclass = str_replace('controller', '', strtolower($valuedetail1['class']));
                        $dataaco = (new Acos())->processStore([
                            'class' => $namaclass,
                            'method' => $value['method'],
                            'nama' => $value['name'],
                            'modifiedby' => auth('api')->user()->user,
                        ]);
                    }
                }

                if ($value['detail2'] != '') {
                    $classdetail2 = $this->listFolderFiles($value['detail2']);
                    foreach ($classdetail2 as $valuedetail2) {
                        $namaclass = str_replace('controller', '', strtolower($valuedetail2['class']));
                        $dataaco = (new Acos())->processStore([
                            'class' => $namaclass,
                            'method' => $value['method'],
                            'nama' => $value['name'],
                            'modifiedby' => auth('api')->user()->user,
                        ]);
                    }
                }

                if ($value['detail3'] != '') {
                    $classdetail3 = $this->listFolderFiles($value['detail3']);
                    foreach ($classdetail3 as $valuedetail3) {
                        $namaclass = str_replace('controller', '', strtolower($valuedetail3['class']));
                        $dataaco = (new Acos())->processStore([
                            'class' => $namaclass,
                            'method' => $value['method'],
                            'nama' => $value['name'],
                            'modifiedby' => auth('api')->user()->user,
                        ]);
                    }
                }
            }



            $list = Acos::select('id')
                ->where('class', '=', $namaclass)
                ->where('method', '=', 'index')
                ->orderBy('id', 'asc')
                ->first();
            $menuacoid = $list->id;
        } else {
            $menuacoid = 0;
        }

        $menu = new Menu();
        $menu->menuname = ucwords(strtolower($data['menuname']));
        $menu->menuseq = $data['menuseq'];
        $menu->menuparent = $data['menuparent'] ?? 0;
        $menu->menuicon = strtolower($data['menuicon']);
        $menu->menuexe = strtolower($data['menuexe']);
        $menu->modifiedby = auth('api')->user()->user;
        $menu->link = "";
        $menu->aco_id = $menuacoid;

        if (Menu::select('menukode')
            ->where('menuparent', '=', $data['menuparent'])
            ->exists()
        ) {

            if ($data['menuparent'] == 0) {

                $list = Menu::select('menukode')
                    ->where('menuparent', '=', '0')
                    ->where(DB::raw('right(menukode,1)'), '<>', '9')
                    ->where(DB::raw('left(menukode,1)'), '<>', 'Z')
                    ->orderBy('menukode', 'desc')
                    ->first();
                $menukode = chr(ord($list->menukode) + 1);
            } else {


                if (Menu::select('menukode')
                    ->where('menuparent', '=', $data['menuparent'])
                    ->where(DB::raw('right(menukode,1)'), '<>', 'Z')
                    ->exists()
                ) {

                    $list = Menu::select('menukode')
                        ->where('menuparent', '=', $data['menuparent'])
                        ->where(DB::raw('right(menukode,1)'), '<>', 'Z')
                        ->orderBy('menukode', 'desc')
                        ->first();

                    $kodeakhir = substr($list->menukode, -1);
                    $arrayangka = array('1', '2', '3', '4', '5', '6', '7', '8');
                    if (in_array($kodeakhir, $arrayangka)) {

                        $menukode = $list->menukode + 1;
                    } else if ($kodeakhir == '9') {
                        $kodeawal = substr($list->menukode, 0, strlen($list->menukode) - 1);
                        $menukode = $kodeawal . 'A';
                    } else {
                        $kodeawal = substr($list->menukode, 0, strlen($list->menukode) - 1);
                        $menukode = $kodeawal . chr((ord($kodeakhir) + 1));
                    }
                } else {

                    $list = Menu::select('menukode')
                        ->where('id', '=', $data['menuparent'])
                        ->where(DB::raw('right(menukode,1)'), '<>', '9')
                        ->orderBy('menukode', 'desc')
                        ->first();
                    $menukode = $list->menukode . '1';
                }
            }
        } else {

            if ($data['menuparent'] == 0) {
                $menukode = 0;
                $list = Menu::select('menukode')
                    ->where('menuparent', '=', '0')
                    ->where(DB::raw('right(menukode,1)'), '<>', 'Z')
                    ->orderBy('menukode', 'desc')
                    ->first();

                $arrayangka = array('1', '2', '3', '4', '5', '6', '7', '8', '9');
                $kodeakhir = $list->menukode;;
                if (in_array($kodeakhir, $arrayangka)) {

                    $menukode = $list->menukode + 1;
                } else {
                    $menukode =  chr((ord($kodeakhir) + 1));
                }
                $kodeakhir = substr($list->menukode, -1);
                $arrayangka = array('1', '2', '3', '4', '5', '6', '7', '8');
                if (in_array($kodeakhir, $arrayangka)) {

                    $menukode = $list->menukode + 1;
                } else if ($kodeakhir == '9') {
                    $menukode = 'A';
                } else {
                    $menukode = chr((ord($kodeakhir) + 1));
                }
            } else {
                // dd('test');
                $list = Menu::select('menukode')
                    ->where('id', '=', $data['menuparent'])
                    // ->where(DB::raw('right(menukode,1)'), '<>', '9')
                    ->orderBy('menukode', 'desc')
                    ->first();

                if (isset($list)) {
                    $menukode = $list->menukode . '1';
                }
            }
        }

        if (strtoupper($data['menuname']) == 'LOGOUT') {
            $menukode = 'Z';
        }
        $menu->menukode = $menukode;
        TOP:
        if (!$menu->save()) {
            throw new \Exception("Error storing menu.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($menu->getTable()),
            'postingdari' => 'ENTRY MENU',
            'idtrans' => $menu->id,
            'nobuktitrans' => $menu->id,
            'aksi' => 'ENTRY',
            'datajson' => $menu->toArray(),
            'modifiedby' => $menu->modifiedby
        ]);

        return $menu;
    }

    public function processUpdate(Menu $menu, array $data): Menu
    {
    
        $query = DB::table('menu')
            ->from(
                DB::raw("menu a with (readuncommitted)")
            )
            ->select(
                DB::raw("trim(replace(b.nama,'index ','')) as controller")
            )
            ->join(DB::raw("acos b with(readuncommitted)"), 'a.aco_id', 'b.id')
            ->where('a.id', '=', $data['id'])
            ->first();

        if ($query != null) {
            $controller = $query->controller;
        }


        if ($query != null) {
            
            $class = $this->listFolderFiles($controller);
            if ($class <> '') {

                foreach ($class as $value) {
                    $namaclass = str_replace('controller', '', strtolower($value['class']));
                    $queryacos = DB::table('acos')
                        ->from(
                            db::raw("acos a with(readuncommitted)")
                        )
                        ->select(
                            'a.id'
                        )
                        ->where('a.class', '=', $namaclass)
                        ->where('a.method', '=', $value['method'])
                        ->where('a.nama', '=', $value['name'])
                        ->first();

                    if (!isset($queryacos)) {
                        if (Acos::select('id')
                            ->where('class', '=', $namaclass)
                            ->exists()
                        ) {
                            $dataaco = (new Acos())->processStore([
                                'class' => $namaclass,
                                'method' => $value['method'],
                                'nama' => $value['name'],
                                'modifiedby' => auth('api')->user()->user,
                            ]);
                        }
                    }
                    // cek detail1

                    if ($value['detail1'] != '') {
                        $classdetail1 = $this->listFolderFiles($value['detail1']);
                        // dd($classdetail1);
                        foreach ($classdetail1 as $valuedetail1) {
                            $namaclass = str_replace('controller', '', strtolower($valuedetail1['class']));

                            $queryacos = DB::table('acos')
                                ->from(
                                    db::raw("acos a with(readuncommitted)")
                                )
                                ->select(
                                    'a.id'
                                )
                                ->where('a.class', '=', $namaclass)
                                ->where('a.method', '=', $valuedetail1['method'])
                                ->where('a.nama', '=', $valuedetail1['name'])
                                ->first();

                            if (!isset($queryacos)) {
                                // if (Acos::select('id')
                                //     ->where('class', '=', $namaclass)
                                //     ->exists()
                                // ) {

                                    $dataaco = (new Acos())->processStore([
                                        'class' => $namaclass,
                                        'method' => $valuedetail1['method'],
                                        'nama' => $valuedetail1['name'],
                                        'modifiedby' => auth('api')->user()->user,
                                    ]);
                                // }
                            }
                        }
                    }

                    // 
                    // cek detail2
                    if ($value['detail2'] != '') {
                        $classdetail2 = $this->listFolderFiles($value['detail2']);
                        foreach ($classdetail2 as $valuedetail2) {
                            $namaclass = str_replace('controller', '', strtolower($valuedetail2['class']));

                            $queryacos = DB::table('acos')
                                ->from(
                                    db::raw("acos a with(readuncommitted)")
                                )
                                ->select(
                                    'a.id'
                                )
                                ->where('a.class', '=', $namaclass)
                                ->where('a.method', '=', $valuedetail2['method'])
                                ->where('a.nama', '=', $valuedetail2['name'])
                                ->first();

                            if (!isset($queryacos)) {
                                // if (Acos::select('id')
                                //     ->where('class', '=', $namaclass)
                                //     ->exists()
                                // ) {

                                    $dataaco = (new Acos())->processStore([
                                        'class' => $namaclass,
                                        'method' => $valuedetail2['method'],
                                        'nama' => $valuedetail2['name'],
                                        'modifiedby' => auth('api')->user()->user,
                                    ]);
                                // }
                            }
                        }
                    }

                    //  
                    // cek detail3
                    if ($value['detail3'] != '') {
                        $classdetail3 = $this->listFolderFiles($value['detail3']);
                        foreach ($classdetail3 as $valuedetail3) {
                            $namaclass = str_replace('controller', '', strtolower($valuedetail3['class']));

                            $queryacos = DB::table('acos')
                                ->from(
                                    db::raw("acos a with(readuncommitted)")
                                )
                                ->select(
                                    'a.id'
                                )
                                ->where('a.class', '=', $namaclass)
                                ->where('a.method', '=', $valuedetail3['method'])
                                ->where('a.nama', '=', $valuedetail3['name'])
                                ->first();

                            if (!isset($queryacos)) {
                                // if (Acos::select('id')
                                //     ->where('class', '=', $namaclass)
                                //     ->exists()
                                // ) {

                                    $dataaco = (new Acos())->processStore([
                                        'class' => $namaclass,
                                        'method' => $valuedetail3['method'],
                                        'nama' => $valuedetail3['name'],
                                        'modifiedby' => auth('api')->user()->user,
                                    ]);
                                // }
                            }
                        }
                    }

                    //                                         

                }
            }
        }

        $menu = new Menu();
        $menu = Menu::lockForUpdate()->findOrFail($data['id']);
        $menu->menuname = ucwords(strtolower($data['menuname']));
        $menu->menuseq = $data['menuseq'];
        $menu->menuicon = strtolower($data['menuicon']);

        if (!$menu->save()) {
            throw new \Exception('Error updating menu.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($menu->getTable()),
            'postingdari' => 'EDIT MENU',
            'idtrans' => $menu->id,
            'nobuktitrans' => $menu->id,
            'aksi' => 'EDIT',
            'datajson' => $menu->toArray(),
            'modifiedby' => $menu->modifiedby
        ]);

        return $menu;
    }

    public function processDestroy($id): Menu
    {
        $list = Menu::Select('aco_id')
            ->where('id', '=', $id)
            ->first();


        if (Acos::select('id')
            ->where('id', '=', $list->aco_id)
            ->exists()
        ) {
            $list = Acos::select('class')
                ->where('id', '=', $list->aco_id)
                ->first();

            Acos::where('class', $list->class)->delete();
        }

        $menu = new Menu();
        $menu = $menu->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($menu->getTable()),
            'postingdari' => 'DELETE MENU',
            'idtrans' => $menu->id,
            'nobuktitrans' => $menu->id,
            'aksi' => 'DELETE',
            'datajson' => $menu->toArray(),
            'modifiedby' => $menu->modifiedby
        ]);

        return $menu;
    }
}
