<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Http\Requests\DestroyMenuRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreAcosRequest;

use App\Models\Menu;
use App\Models\LogTrail;
use App\Models\Acos;
use ReflectionClass;

use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;

class MenuController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $menu = new Menu();

        return response([
            'data' => $menu->get(),
            'attributes' => [
                'totalRows' => $menu->totalRows,
                'totalPages' => $menu->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreMenuRequest $request)
    {
        DB::beginTransaction();

        try {
            $class = $this->listFolderFiles($request['controller']);
            if ($class <> '') {

                foreach ($class as $value) {
                    $namaclass = str_replace('controller', '', strtolower($value['class']));
                    $dataacos = [
                        'class' => $namaclass,
                        'method' => $value['method'],
                        'nama' => $value['name'],
                        'modifiedby' => auth('api')->user()->name,
                    ];

                    $data = new StoreAcosRequest($dataacos);
                    $dataaco = app(AcosController::class)->store($data);

                    if ($dataaco['error']) {
                        return response($dataaco, 422);
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
            $menu->menuname = ucwords(strtolower($request->menuname));
            $menu->menuseq = $request->menuseq;
            $menu->menuparent = $request->menuparent ?? 0;
            $menu->menuicon = strtolower($request->menuicon);
            $menu->menuexe = strtolower($request->menuexe);
            $menu->modifiedby = auth('api')->user()->name;
            $menu->link = "";
            $menu->aco_id = $menuacoid;

            if (Menu::select('menukode')
                ->where('menuparent', '=', $request->menuparent)
                ->exists()
            ) {

                if ($request->menuparent == 0) {
                    $list = Menu::select('menukode')
                        ->where('menuparent', '=', '0')
                        ->where(DB::raw('right(menukode,1)'), '<>', '9')
                        ->orderBy('menukode', 'desc')
                        ->first();
                    // dd('test1');
                    $menukode = $list->menukode + 1;
                } else {


                    if (Menu::select('menukode')
                        ->where('menuparent', '=', $request->menuparent)
                        ->where(DB::raw('right(menukode,1)'), '<>', '9')
                        ->exists()
                    ) {
                        $list = Menu::select('menukode')
                            ->where('menuparent', '=', $request->menuparent)
                            ->where(DB::raw('right(menukode,1)'), '<>', '9')
                            ->orderBy('menukode', 'desc')
                            ->first();

                        $kodeakhir = substr($list->menukode, -1);
                        $arrayangka = array('1', '2', '3', '4', '5', '6', '7', '8');
                        if (in_array($kodeakhir, $arrayangka)) {

                            $menukode = $list->menukode + 1;
                        } else {
                            $kodeawal = substr($list->menukode, 0, strlen($list->menukode) - 1);
                            $menukode = $kodeawal . chr((ord($kodeakhir) + 1));
                        }
                    } else {
                        $list = Menu::select('menukode')
                            ->where('id', '=', $request->menuparent)
                            ->where(DB::raw('right(menukode,1)'), '<>', '9')
                            ->orderBy('menukode', 'desc')
                            ->first();
                        // dd('test3');
                        $menukode = $list->menukode . '1';
                    }
                }
            } else {
                if ($request->menuparent == 0) {
                    $menukode = 0;
                    $list = Menu::select('menukode')
                        ->where('menuparent', '=', '0')
                        ->where(DB::raw('right(menukode,1)'), '<>', 'Z')
                        ->orderBy('menukode', 'desc')
                        ->first();

                    $arrayangka = array('1', '2', '3', '4', '5', '6', '7', '8', '9');
                    $kodeakhir =$list->menukode;;
                    if (in_array($kodeakhir, $arrayangka)) {

                        $menukode = $list->menukode + 1;
                    } else {
                        $menukode =  chr((ord($kodeakhir) + 1));
                    }
                    // $menukode = $list->menukode + 1;
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
                    $list = Menu::select('menukode')
                        ->where('id', '=', $request->menuparent)
                        ->where(DB::raw('right(menukode,1)'), '<>', '9')
                        ->orderBy('menukode', 'desc')
                        ->first();
                    // dd('test4');
                    $menukode = $list->menukode . '1';
                }
            }

            if (strtoupper($request->menuname) == 'LOGOUT') {
                $menukode = 'Z';
            }
            $menu->menukode = $menukode;
            TOP:
            if ($menu->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($menu->getTable()),
                    'postingdari' => 'ENTRY MENU',
                    'idtrans' => $menu->id,
                    'nobuktitrans' => $menu->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $menu->toArray(),
                    'modifiedby' => $menu->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($menu, $menu->getTable());
            $menu->position = $selected->position;
            $menu->page = ceil($menu->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $menu
            ]);
        } catch (QueryException $queryException) {
            if (isset($queryException->errorInfo[1]) && is_array($queryException->errorInfo)) {
                // Check if deadlock
                if ($queryException->errorInfo[1] === 1205) {
                    goto TOP;
                }
            }

            throw $queryException;
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show(Menu $menu)
    {
        return response([
            'status' => true,
            'data' => $menu
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateMenuRequest $request, Menu $menu)
    {
        DB::beginTransaction();

        try {
            $class = $this->listFolderFiles($request['controller']);

            if ($class <> '') {

                foreach ($class as $value) {
                    $namaclass = str_replace('controller', '', strtolower($value['class']));
                    if (Acos::select('id')
                        ->where('class', '=', $namaclass)
                        ->exists()
                    ) {
                        $dataacos = [
                            'class' => $namaclass,
                            'method' => $value['method'],
                            'nama' => $value['name'],
                            'modifiedby' => auth('api')->user()->name,
                        ];

                        $data = new StoreAcosRequest($dataacos);
                        $dataaco = app(AcosController::class)->store($data);

                        if ($dataaco['error']) {
                            return response($dataaco, 422);
                        }
                    }
                }
            }

            $menu = new Menu();
            $menu = Menu::lockForUpdate()->findOrFail($request->id);
            $menu->menuname = ucwords(strtolower($request->menuname));
            $menu->menuseq = $request->menuseq;
            $menu->menuicon = strtolower($request->menuicon);

            if ($menu->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($menu->getTable()),
                    'postingdari' => 'EDIT MENU',
                    'idtrans' => $menu->id,
                    'nobuktitrans' => $menu->id,
                    'aksi' => 'EDIT',
                    'datajson' => $menu->toArray(),
                    'modifiedby' => $menu->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($menu, $menu->getTable());
            $menu->position = $selected->position;
            $menu->page = ceil($menu->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $menu
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

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

        if ($menu) {
            $logTrail = [
                'namatabel' => strtoupper($menu->getTable()),
                'postingdari' => 'DELETE MENU',
                'idtrans' => $menu->id,
                'nobuktitrans' => $menu->id,
                'aksi' => 'DELETE',
                'datajson' => $menu->toArray(),
                'modifiedby' => $menu->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $selected = $this->getPosition($menu, $menu->getTable(), true);
            $menu->position = $selected->position;
            $menu->id = $selected->id;
            $menu->page = ceil($menu->position / ($request->limit ?? 10));
    
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $menu
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $menus = $decodedResponse['data'];

        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'ID',
                'index' => 'id',
            ],
            [
                'label' => 'Menu Name',
                'index' => 'menuname',
            ],
            [
                'label' => 'Menu Parent',
                'index' => 'menuparent',
            ],
            [
                'label' => 'Menu Icon',
                'index' => 'menuicon',
            ],
            [
                'label' => 'Aco ID',
                'index' => 'aco_id',
            ],
            [
                'label' => 'Link',
                'index' => 'link',
            ],
            [
                'label' => 'Menu Exe',
                'index' => 'menuexe',
            ],
            [
                'label' => 'Menu Kode',
                'index' => 'menukode',
            ],
        ];

        $this->toExcel('Menu', $menus, $columns);
    }


    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('menu')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function combomenuparent(Request $request)
    {

        $params = [
            'status' => $request->status ?? '',
        ];
        $temp = '##temp' . rand(1, 10000);

        Schema::create($temp, function ($table) {
            $table->string('id', 10)->default('');
            $table->string('menuparent', 150)->default(0);
            $table->string('param', 50)->default(0);
        });

        if ($params['status'] == 'entry') {
            DB::table($temp)->insert(
                [
                    'id' => '0',
                    'menuparent' => 'UTAMA',
                    'param' => '',
                ]
            );

            $queryall = Menu::select(
                DB::raw("menu.id as id, upper(menu.menuname) as menuparent, upper(menu.menuname) as param")
            )
                ->where('menu.aco_id', "=", '0');

            $query = DB::table($temp)
                ->unionAll($queryall);
        } else {

            DB::table($temp)->insert(
                [
                    'id' => '0',
                    'menuparent' => 'ALL',
                    'param' => '',
                ]
            );


            $queryall = Menu::select(
                DB::raw("menu.id as id, upper(menu.menuname) as menuparent, upper(menu.menuname) as param")
            )
                ->where('menu.aco_id', "=", '0');

            $query = DB::table($temp)
                ->unionAll($queryall);
        }

        $data = $query->get();

        return response([
            'data' => $data
        ]);
    }
    public function getdatanamaacos(Request $request)
    {
        // dd($request->aco_id);
        if (Acos::select('class as nama')
            ->where('id', '=', $request->aco_id)
            ->exists()
        ) {
            $data = Acos::select('class as nama')
                ->where('id', '=', $request->aco_id)
                ->first();
        } else {
            $data = "";
        }

        return response([
            'data' => $data
        ]);
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

                        foreach ($methods as $method) {
                            if (isset($method['docComment']['ClassName'])) {
                                $data[] = [
                                    'class' => $class,
                                    'method' => $method['name'],
                                    'name' => $method['name'] . ' ' . $class
                                ];
                            }
                        }
                    }
                }
            }
        }
        return $data ?? '';
    }

    public function listclassall()
    {
        $dir = base_path('app/http') . '/controllers/api/';
        $ffs = scandir($dir);
        unset($ffs[0], $ffs[1]);
        if (count($ffs) < 1)
            return;
        $i = 0;
        $data[] = [
            'class' => 'NON CONTROLLER',
        ];
        foreach ($ffs as $ff) {
            if (is_dir($dir . '/' . $ff))
                $this->listFolderFiles($dir . '/' . $ff);
            elseif (is_file($dir . '/' . $ff) && strpos($ff, '.php') !== false) {
                $classes = $this->get_php_classes(file_get_contents($dir . '/' . $ff));
                foreach ($classes as $class) {

                    if (!class_exists($class)) {
                        include_once($dir . $ff);
                    }

                    $data[] = [
                        'class' => $class,
                    ];
                }
            }
        }

        return response([
            'data' => $data ?? []
        ]);
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
                }

                $methods[] = $arr;
            }
        }

        return $methods;
    }
    public function get_method_comment($obj, $method)
    {
        $comment = $obj->getMethod($method)->getDocComment();
        //define the regular expression pattern to use for string matching
        $pattern = "#(@[a-zA-Z]+\s*[a-zA-Z0-9, ()_].*)#";

        //perform the regular expression on the string provided
        preg_match_all($pattern, $comment, $matches, PREG_PATTERN_ORDER);
        $comments = [];
        foreach ($matches[0] as $match) {
            $comment = preg_split('/[\s]/', $match, 2);
            $comments[trim($comment[0], '@')] = $comment[1];
        }

        return $comments;
    }
}
