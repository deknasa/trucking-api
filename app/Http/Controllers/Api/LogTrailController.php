<?php


namespace App\Http\Controllers;

namespace App\Http\Controllers\Api;


use App\Models\LogTrail;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateLogTrailRequest;
use App\Http\Requests\DestroyLogTrailRequest;


use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
class LogTrailController extends Controller
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

        $totalRows = LogTrail::count();
        $totalPages = ceil($totalRows / $params['limit']);

        /* Sorting */
        if ($params['sortIndex'] == 'id') {
            $query = LogTrail::select(
                'logtrail.id',
                'logtrail.namatabel',
                'logtrail.postingdari',
                'logtrail.idtrans',
                'logtrail.nobuktitrans',
                'logtrail.aksi',
                'logtrail.modifiedby',
                'logtrail.created_at',
                'logtrail.updated_at'
            )
                ->orderBy('logtrail.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = LogTrail::select(
                    'logtrail.id',
                    'logtrail.namatabel',
                    'logtrail.postingdari',
                    'logtrail.idtrans',
                    'logtrail.nobuktitrans',
                    'logtrail.aksi',
                    'logtrail.modifiedby',
                    'logtrail.created_at',
                    'logtrail.updated_at'
                )
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('logtrail.id', $params['sortOrder']);
            } else {
                $query = LogTrail::select(
                    'logtrail.id',
                    'logtrail.namatabel',
                    'logtrail.postingdari',
                    'logtrail.idtrans',
                    'logtrail.nobuktitrans',
                    'logtrail.aksi',
                    'logtrail.modifiedby',
                    'logtrail.created_at',
                    'logtrail.updated_at'
                )
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('logtrail.id', 'asc');
            }
        }


        /* Searching */
        if (count($params['search']) > 0 && @$params['search']['rules'][0]['data'] != '') {
            switch ($params['search']['groupOp']) {
                case "AND":
                    foreach ($params['search']['rules'] as $index => $search) {

                        $query = $query->where($search['field'], 'LIKE', "%$search[data]%");
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
                        $query = $query->orWhere($search['field'], 'LIKE', "%$search[data]%");
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

        $roles = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows,
            'totalPages' => $totalPages
        ];

        // echo $time2-$time1;
        // echo '---';
        return response([
            'status' => true,
            'data' => $roles,
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
     * @param  \App\Http\Requests\StorelogtrailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLogTrailRequest $request)
    {
        DB::beginTransaction();
        try {
            $LogTrail = new LogTrail();

            $LogTrail->namatabel = $request->namatabel;
            $LogTrail->postingdari = $request->postingdari;
            $LogTrail->idtrans = $request->idtrans;
            $LogTrail->nobuktitrans = $request->nobuktitrans;
            $LogTrail->aksi = $request->aksi;
            $LogTrail->datajson = $request->datajson;
            $LogTrail->modifiedby = $request->modifiedby;

            $LogTrail->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\logtrail  $logtrail
     * @return \Illuminate\Http\Response
     */
    public function show(LogTrail $logtrail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\logtrail  $logtrail
     * @return \Illuminate\Http\Response
     */
    public function edit(LogTrail $logtrail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatelogtrailRequest  $request
     * @param  \App\Models\logtrail  $logtrail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLogTrailRequest $request, logtrail $logtrail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\logtrail  $logtrail
     * @return \Illuminate\Http\Response
     */
    public function destroy(LogTrail $logtrail)
    {
        //
    }

    public function detail(Request $request)
    
    {

            $query = LogTrail::select(
                'datajson as data' ,
            )
            ->where('id', '=',  $request->id);
    
            $data = $query->get();

            return response(
                $data->toArray(),
            );
        // $params = [
        //     'offset' => $request->offset ?? 0,
        //     'limit' => $request->limit ?? 100,
        //     'search' => $request->search ?? [],
        //     'sortIndex' => $request->sortIndex ?? 'id',
        //     'sortOrder' => $request->sortOrder ?? 'asc',
        // ];

        // $temp = '##temp' . rand(1, 10000);
        // Schema::create($temp, function ($table) {
        //     $table->id();
        //     $table->bigInteger('user_id')->default('0');
        //     $table->bigInteger('id_')->default('0');
        //     $table->string('modifiedby', 30)->default('');
        //     $table->dateTime('created_at')->default('1900/1/1');
        //     $table->dateTime('updated_at')->default('1900/1/1');

        //     $table->index('user_id');
        // });
        
        // $totalRows = UserRole::count();
        // $totalPages = ceil($totalRows / $params['limit']);

        // /* Sorting */
        // if ($params['sortIndex'] == 'id') {
        //     $query = UserRole::select(
        //         'userrole.id',
        //         'user.user as user',
        //         'role.rolename as rolename',
        //         'userrole.modifiedby',
        //         'userrole.created_at',
        //         'userrole.updated_at'
        //     )
        //         ->Join('user', 'userrole.user_id', '=', 'user.id')
        //         ->Join('role', 'userrole.role_id', '=', 'role.id')
        //         ->where('userrole.user_id', '=', $request->user_id)
        //         ->orderBy('userrole.id', $params['sortOrder']);
        // } else {
        //     if ($params['sortOrder'] == 'asc') {
        //         $query = UserRole::select(
        //             'userrole.id',
        //             'user.user as user',
        //             'role.rolename as rolename',
        //             'userrole.modifiedby',
        //             'userrole.created_at',
        //             'userrole.updated_at'
        //         )
        //             ->Join('user', 'userrole.user_id', '=', 'user.id')
        //             ->Join('role', 'userrole.role_id', '=', 'role.id')
        //             ->orderBy($params['sortIndex'], $params['sortOrder'])
        //             ->where('userrole.user_id', '=', $request->user_id)
        //             ->orderBy('userrole.id', $params['sortOrder']);
        //     } else {
        //         $query = UserRole::select(
        //             'userrole.id',
        //             'user.user as user',
        //             'role.rolename as rolename',
        //             'userrole.modifiedby',
        //             'userrole.created_at',
        //             'userrole.updated_at'
        //         )
        //             ->Join('user', 'userrole.user_id', '=', 'user.id')
        //             ->Join('role', 'userrole.role_id', '=', 'role.id')
        //             ->where('userrole.user_id', '=', $request->user_id)
        //             ->orderBy($params['sortIndex'], $params['sortOrder'])
        //             ->orderBy('userrole.id', 'asc');
        //     }
        // }


        // /* Searching */
        // if (count($params['search']) > 0 && @$params['search']['rules'][0]['data'] != '') {
        //     switch ($params['search']['groupOp']) {
        //         case "AND":
        //             foreach ($params['search']['rules'] as $index => $search) {
        //                 if ($search['field'] == 'user') {
        //                     $query = $query->where('user.user', 'LIKE', "%$search[data]%");
        //                 } else if ($search['field'] == 'rolename') {
        //                     $query = $query->where('role.rolename', 'LIKE', "%$search[data]%");
        //                 } else {
        //                     $query = $query->where($search['field'], 'LIKE', "%$search[data]%");
        //                 }
        //             }

        //             break;
        //         case "OR":
        //             foreach ($params['search']['rules'] as $index => $search) {
        //                 if ($search['field'] == 'user') {
        //                     $query = $query->orWhere('user.user', 'LIKE', "%$search[data]%");
        //                 } else if ($search['field'] == 'rolename') {
        //                     $query = $query->orWhere('role.rolename', 'LIKE', "%$search[data]%");
        //                 } else {
        //                     $query = $query->orWhere($search['field'], 'LIKE', "%$search[data]%");
        //                 }
        //             }

        //             break;
        //         default:

        //             break;
        //     }


        //     $totalRows = count($query->get());

        //     $totalPages = ceil($totalRows / $params['limit']);
        // }

        // /* Paging */
        // $query = $query->skip($params['offset'])
        //     ->take($params['limit']);

        // $userroles = $query->get();

        // /* Set attributes */
        // $attributes = [
        //     'totalRows' => $totalRows,
        //     'totalPages' => $totalPages
        // ];

        // // echo $time2-$time1;
        // // echo '---';
        // return response([
        //     'status' => true,
        //     'data' => $userroles,
        //     'attributes' => $attributes,
        //     'params' => $params
        // ]);

           
    }

}
