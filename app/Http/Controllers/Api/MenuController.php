<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\LogTrail;
use App\Http\Requests\MenuRequest;
use App\Models\Parameter;
use Illuminate\Support\Facades\Schema;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $params = [
            'offset' => $request->offset ?? 0,
            'limit' => $request->limit ?? 100,
            'search' => $request->search ?? [],
            'sortIndex' => $request->sortIndex ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        $totalRows = Menu::count();
        $totalPages = ceil($totalRows / $params['limit']);

        /* Sorting */
        if ($params['sortIndex'] == 'id') {
            $query = Menu::select(
                'menu.id',
                'menu.menuname',
                'menu2.menuname as menuparent',
                'menu.menuicon',
                'acos.nama as aco_id',
                'menu.link',
                'menu.menuexe',
                'menu.menukode',
                'menu.modifiedby',
                'menu.created_at',
                'menu.updated_at'
            )
                ->leftJoin('menu as menu2', 'menu2.id', '=', 'menu.menuparent')
                ->leftJoin('acos', 'acos.id', '=', 'menu.aco_id')
                ->orderBy('menu.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = Menu::select(
                    'menu.id',
                    'menu.menuname',
                    'menu2.menuname as menuparent',
                    'menu.menuicon',
                    'acos.nama as aco_id',
                    'menu.link',
                    'menu.menuexe',
                    'menu.menukode',
                    'menu.modifiedby',
                    'menu.created_at',
                    'menu.updated_at'
                )
                    ->leftJoin('menu as menu2', 'menu2.id', '=', 'menu.menuparent')
                    ->leftJoin('acos', 'acos.id', '=', 'menu.aco_id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('menu.id', $params['sortOrder']);
            } else {
                $query = Menu::select(
                    'menu.id',
                    'menu.menuname',
                    'menu2.menuname as menuparent',
                    'menu.menuicon',
                    'acos.nama as aco_id',
                    'menu.link',
                    'menu.menuexe',
                    'menu.menukode',
                    'menu.modifiedby',
                    'menu.created_at',
                    'menu.updated_at'
                )
                    ->leftJoin('menu as menu2', 'menu2.id', '=', 'menu.menuparent')
                    ->leftJoin('acos', 'acos.id', '=', 'menu.aco_id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('menu.id', 'asc');
            }
        }


        /* Searching */
        if (count($params['search']) > 0 && @$params['search']['rules'][0]['data'] != '') {
            switch ($params['search']['groupOp']) {
                case "AND":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->where($search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->orWhere($search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                default:

                    break;
            }


            $totalRows = count($query->get());

            $totalPages = ceil($totalRows / $params['limit']);
        }

        /* Paging */
        $query = $query->skip($params['offset'])
            ->take($params['limit']);

        $cabangs = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows,
            'totalPages' => $totalPages
        ];

        // echo $time2-$time1;
        // echo '---';
        return response([
            'status' => true,
            'data' => $cabangs,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoremenuRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MenuRequest $request)
    {
        DB::beginTransaction();
        try {
            $menu = new Menu();
            $menu->menuname = strtoupper($request->menuname);
            $menu->menuseq = $request->menuseq;
            $menu->menuparent = $request->menuparent;
            $menu->menuicon = strtoupper($request->menuicon);
            $menu->link = $request->link;
            $menu->menuexe = $request->menuexe;
            $menu->menukode = $request->menukode;

            $menu->save();

            $datajson = [
                'id' => $menu->id,
                'menuname' => strtoupper($request->menuname),
                'menuseq' => $request->menuseq,
                'menuparent' => $request->menuparent,
                'menuicon' => strtoupper($request->menuicon),
                'link' => $request->link,
                'menuexe' => $request->menuexe,
                'menukode' => $request->menukode,
            ];

            $logtrail = new LogTrail();
            $logtrail->namatabel = 'MENU';
            $logtrail->postingdari = 'ENTRY MENU';
            $logtrail->idtrans = $menu->id;
            $logtrail->nobuktitrans = $menu->id;
            $logtrail->aksi = 'ENTRY';
            $logtrail->datajson = json_encode($datajson);

            $logtrail->save();

            DB::commit();
            /* Set position and page */
            $del = 0;
            $data = $this->getid($menu->id, $request, $del);
            $menu->position = $data->row;
            // dd($menu->position );
            if (isset($request->limit)) {
                $menu->page = ceil($menu->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $menu
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Menu  $Menu
     * @return \Illuminate\Http\Response
     */
    public function show(Menu $menu)
    {
        return response([
            'status' => true,
            'data' => $menu
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Menu  $Menu
     * @return \Illuminate\Http\Response
     */
    public function edit(Menu $menu)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatemenuRequest  $request
     * @param  \App\Models\Menu  $Menu
     * @return \Illuminate\Http\Response
     */
    public function update(MenuRequest $request, Menu $menu)
    {
        DB::beginTransaction();
        try {
            $menu->update(array_map('strtoupper', $request->validated()));

            $datajson = [
                'id' => $menu->id,
                'menuname' => strtoupper($request->menuname),
                'menuseq' => $request->menuseq,
                'menuparent' => $request->menuparent,
                'menuicon' => strtoupper($request->menuicon),
                'link' => $request->link,
                'menuexe' => $request->menuexe,
                'menukode' => $request->menukode,
            ];

            $logtrail = new LogTrail();
            $logtrail->namatabel = 'MENU';
            $logtrail->postingdari = 'EDIT MENU';
            $logtrail->idtrans = $menu->id;
            $logtrail->nobuktitrans = $menu->id;
            $logtrail->aksi = 'EDIT';
            $logtrail->datajson = json_encode($datajson);

            $logtrail->save();
            DB::commit();

            /* Set position and page */
            $menu->position = menu::orderBy($request->sortname ?? 'id', $request->sortorder ?? 'asc')
                ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $menu->{$request->sortname})
                ->where('id', '<=', $menu->id)
                ->count();

            if (isset($request->limit)) {
                $menu->page = ceil($menu->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $menu
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Menu  $Menu
     * @return \Illuminate\Http\Response
     */
    public function destroy(Menu $menu, MenuRequest $request)
    {
        DB::beginTransaction();
        try {

            Menu::destroy($menu->id);

            $logtrail = new LogTrail();
            $logtrail->namatabel = 'MENU';
            $logtrail->postingdari = 'DELETE MENU';
            $logtrail->idtrans = $menu->id;
            $logtrail->nobuktitrans = $menu->id;
            $logtrail->aksi = 'DELETE';
            $logtrail->datajson = '';

            $logtrail->save();

            DB::commit();
            mENU::destroy($menu->id);
            $del = 1;
            $data = $this->getid($menu->id, $request, $del);
            $menu->position = $data->row;
            $menu->id = $data->id;
            if (isset($request->limit)) {
                $menu->page = ceil($menu->position / $request->limit);
            }
            // dd($menu);
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $menu
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
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



    public function getid($id, $request, $del)
    {

        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->id();
            $table->bigInteger('id_')->default('0');
            $table->string('menuname', 50)->default('');
            $table->integer('menuseq')->length(11)->default('0');
            $table->string('menuparent', 50)->nullable()->change();
            $table->string('menuicon', 50)->default('');
            $table->string('aco_id', 100)->nullable()->change();
            $table->string('modifiedby', 50)->default('');
            $table->string('link', 2000)->default('');
            $table->string('menuexe', 200)->default('');
            $table->integer('menukode')->length(11)->default('0');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });



        if ($request->sortname == 'id') {
            $query = Menu::select(
                'menu.id as id_',
                'menu.menuname',
                'menu2.menuname as menuparent',
                'menu.menuicon',
                'acos.nama as aco_id',
                'menu.link',
                'menu.menuexe',
                'menu.menukode',
                'menu.modifiedby',
                'menu.created_at',
                'menu.updated_at'
            )
                ->leftJoin('menu as menu2', 'menu2.id', '=', 'menu.menuparent')
                ->leftJoin('acos', 'acos.id', '=', 'menu.aco_id')
                ->orderBy('menu.id', $request->sortorder);
        } else {
            if ($request->sortorder == 'asc') {
                $query = Menu::select(
                    'menu.id as id_',
                    'menu.menuname',
                    'menu2.menuname as menuparent',
                    'menu.menuicon',
                    'acos.nama as aco_id',
                    'menu.link',
                    'menu.menuexe',
                    'menu.menukode',
                    'menu.modifiedby',
                    'menu.created_at',
                    'menu.updated_at'
                )
                    ->leftJoin('menu as menu2', 'menu2.id', '=', 'menu.menuparent')
                    ->leftJoin('acos', 'acos.id', '=', 'menu.aco_id')
                    ->orderBy($request->sortname, $request->sortorder)
                    ->orderBy('menu.id', $request->sortorder);
            } else {
                $query = Menu::select(
                    'menu.id as id_',
                    'menu.menuname',
                    'menu2.menuname as menuparent',
                    'menu.menuicon',
                    'acos.nama as aco_id',
                    'menu.link',
                    'menu.menuexe',
                    'menu.menukode',
                    'menu.modifiedby',
                    'menu.created_at',
                    'menu.updated_at'
                )
                    ->leftJoin('menu as menu2', 'menu2.id', '=', 'menu.menuparent')
                    ->leftJoin('acos', 'acos.id', '=', 'menu.aco_id')
                    ->orderBy($request->sortname, $request->sortorder)
                    ->orderBy('menu.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'menuname', 'menuparent', 'menuicon', 'aco_id', 'link', 'menuexe', 'menukode', 'modifiedby', 'created_at', 'updated_at'], $query);


        if ($del == 1) {
            if ($request->page == 1) {
                $baris = $request->indexRow + 1;
            } else {
                $hal = $request->page - 1;
                $bar = $hal * $request->limit;
                $baris = $request->indexRow + $bar + 1;
            }


            if (DB::table($temp)
                ->where('id', '=', $baris)->exists()
            ) {
                $querydata = DB::table($temp)
                    ->select('id as row', 'id_ as id')
                    ->where('id', '=', $baris)
                    ->orderBy('id');
            } else {
                $querydata = DB::table($temp)
                    ->select('id as row', 'id_ as id')
                    ->where('id', '=', ($baris - 1))
                    ->orderBy('id');
            }
        } else {
            $querydata = DB::table($temp)
                ->select('id as row')
                ->where('id_', '=',  $id)
                ->orderBy('id');
        }


        $data = $querydata->first();
        return $data;
    }

    public function combomenuparent(Request $request)
    {

        $params = [
            'status' => $request->status ?? '',
        ];
        $temp = '##temp' . rand(1, 10000);
        if ($params['status'] == 'entry') {
            $query = Menu::select('menu.id as id', 'menu.menuname as menuparent')
                ->where('menu.menuname', "=", '0');
        } else {
            Schema::create($temp, function ($table) {
                $table->string('id', 10)->default('');
                $table->string('menuparent', 150)->default(0);
                $table->string('param', 50)->default(0);
            });

            DB::table($temp)->insert(
                [
                    'id' => '0',
                    'menuparent' => 'ALL',
                    'param' => '',
                ]
            );


            $queryall = Menu::select('menu.id as id', 'menu.menuname as menuparent', 'menu.menuname as param')
                ->where('menu.menuname', "=", '0');

            $query = DB::table($temp)
                ->unionAll($queryall);
        }

        $data = $query->get();

        return response([
            'data' => $data->toArray(),
        ]);
    }
}
