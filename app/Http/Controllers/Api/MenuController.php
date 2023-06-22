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
use App\Http\Requests\RangeExportReportRequest;
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
            $data = [
                'menuname' => ucwords(strtolower($request->menuname)),
                'menuseq' => $request->menuseq,
                'menuparent' => $request->menuparent ?? 0,
                'menuicon' => strtolower($request->menuicon),
                'menuexe' => strtolower($request->menuexe),
                'controller' => $request->controller
            ];


            $menu = (new Menu())->processStore($data);
            $menu->position = $this->getPosition($menu, $menu->getTable())->position;
            $menu->page = ceil($menu->position / ($request->limit ?? 10));
            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $menu
            ]);
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
            $data = [
                'id' => $request->id,
                'menuname' => ucwords(strtolower($request->menuname)),
                'menuseq' => $request->menuseq,
                'menuparent' => $request->menuparent ?? 0,
                'menuicon' => strtolower($request->menuicon),
                'menuexe' => strtolower($request->menuexe),
                'controller' => $request->controller
            ];

            $menu = (new Menu())->processUpdate($menu, $data);
            $menu->position = $this->getPosition($menu, $menu->getTable())->position;
            $menu->page = ceil($menu->position / ($request->limit ?? 10));

            DB::commit();

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
    public function destroy(DestroyMenuRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $menu = (new Menu())->processDestroy($id);
            $selected = $this->getPosition($menu, $menu->getTable(), true);
            $menu->position = $selected->position;
            $menu->id = $selected->id;
            $menu->page = ceil($menu->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil dihapus',
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
    public function export(RangeExportReportRequest $request)
    {
        if (request()->cekExport) {
            return response([
                'status' => true,
            ]);
        } else {
            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $menus = $decodedResponse['data'];

            $judulLaporan = $menus[0]['judulLaporan'];

            $columns = [
                [
                    'label' => 'No',
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

            $this->toExcel($judulLaporan, $menus, $columns);
        }
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
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->string('id', 10)->nullable();
            $table->string('menuparent', 150)->nullable();
            $table->string('param', 50)->nullable();
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

    /**
     * @ClassName 
     */
    public function report()
    {
    }
}
