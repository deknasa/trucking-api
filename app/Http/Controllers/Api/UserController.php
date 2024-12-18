<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\DestroyUserRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\StoreAclRequest;
use App\Http\Requests\StoreUserRoleRequest;
use App\Models\Aco;
use App\Models\User;
use App\Models\Parameter;
use App\Models\Cabang;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * @ClassName 
     * user
     * @Detail AcosController
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $user = new User();
        return response([
            'data' => $user->get(),
            'attributes' => [
                'totalRows' => $user->totalRows,
                'totalPages' => $user->totalPages
            ]
        ]);
    }
    public function default()
    {
        $user = new User();
        return response([
            'status' => true,
            'data' => $user->default()
        ]);
    }

    public function getRoles(User $user): JsonResponse
    {
        return response()->json([
            'data' => $user->roles
        ]);
    }

    public function storeRoles(StoreUserRoleRequest $request, User $user): JsonResponse
    {
        DB::beginTransaction();

        try {
            $user->roles()->detach();

            if (is_array($request->role_ids)) {
                foreach ($request->role_ids as $role_id) {
                    $user->roles()->attach($role_id, [
                        'modifiedby' => auth('api')->user()->name
                    ]);
                }
            }


            $logTrail = [
                'namatabel' => strtoupper($user->getTable()),
                'postingdari' => 'ENTRY USER ROLE',
                'idtrans' => $user->id,
                'nobuktitrans' => $user->id,
                'aksi' => 'ENTRY',
                'datajson' => $user->load('roles')->toArray(),
                'modifiedby' => $user->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'user' => $user->load('roles')
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'user' => strtoupper($request->user),
                'name' => strtoupper($request->name),
                'email' => strtoupper($request->email),
                'password' => $request->password,
                'cabang_id' => $request->cabang_id ?? '',
                'karyawan_id' => $request->karyawan_id,
                'dashboard' => strtoupper($request->dashboard),
                'statusaktif' => $request->statusaktif,
                'statusakses' => $request->statusakses,
            ];
            $user = (new User())->processStore($data);
            $user->position = $this->getPosition($user, $user->getTable())->position;
            if ($request->limit==0) {
                $user->page = ceil($user->position / (10));
            } else {
                $user->page = ceil($user->position / ($request->limit ?? 10));
            }

            DB::commit();


            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $user
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show($id)
    {
        request()->user_id = $id;
        request()->limit = 0;
        $detail = (new Aco())->getUserAcl();
        return response([
            'status' => true,
            'data' => (new User())->findAll($id),
            'roles' => (new User())->getRole($id),
            'detail' => $detail
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        DB::beginTransaction();

        try {
            $acos = json_decode($request->aco_ids, true);
            $data = [
                'user' => strtoupper($request->user),
                'name' => strtoupper($request->name),
                'email' => strtoupper($request->email),
                'password' => Hash::make($request->password),
                'cabang_id' => $request->cabang_id ?? '',
                'karyawan_id' => $request->karyawan_id,
                'dashboard' => strtoupper($request->dashboard),
                'statusaktif' => $request->statusaktif,
                'statusakses' => $request->statusakses,
                'role_ids' => $request->role_ids,
                'aco_ids' => $acos['aco_ids']
            ];
            $user = (new User())->processUpdate($user, $data);
            $user->position = $this->getPosition($user, $user->getTable())->position;
            if ($request->limit==0) {
                $user->page = ceil($user->position / (10));
            } else {
                $user->page = ceil($user->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $user
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(DestroyUserRequest $request, $id)
    {
        DB::beginTransaction();

        try {

            $user = (new User())->processDestroy($id);
            $selected = $this->getPosition($user, $user->getTable(), true);
            $user->position = $selected->position;
            $user->id = $selected->id;
            if ($request->limit==0) {
                $user->page = ceil($user->position / (10));
            } else {
                $user->page = ceil($user->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $user
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report()
    {
    }

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(RangeExportReportRequest $request)
    {
        if (request()->cekExport) {

            if (request()->offset == "-1" && request()->limit == '1') {

                return response([
                    'errors' => [
                        "export" => app(ErrorController::class)->geterror('DTA')->keterangan
                    ],
                    'status' => false,
                    'message' => "The given data was invalid."
                ], 422);
            } else {
                return response([
                    'status' => true,
                ]);
            }
        } else {

            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $users = $decodedResponse['data'];


            $judulLaporan = $users[0]['judulLaporan'];

            // $judulLaporan = $users[0]['judulLaporan'];

            $i = 0;
            foreach ($users as $index => $params) {

                $statusaktif = $params['statusaktif'];
                $statusakses = $params['statusakses'];

                $result = json_decode($statusaktif, true);
                $resultAkses = json_decode($statusakses, true);

                $statusaktif = $result['MEMO'];
                $statusakses = $resultAkses['MEMO'];

                $users[$i]['statusaktif'] = $statusaktif;
                $users[$i]['statusakses'] = $statusakses;

                $i++;
            }


            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'User',
                    'index' => 'user',
                ],
                [
                    'label' => 'Nama User',
                    'index' => 'name',
                ],
                [
                    'label' => 'Email',
                    'index' => 'email',
                ],
                [
                    'label' => 'Cabang',
                    'index' => 'cabang_id',
                ],
                [
                    'label' => 'Status',
                    'index' => 'statusaktif',
                ],
                [
                    'label' => 'status akses',
                    'index' => 'statusakses',
                ],
            ];

            $this->toExcel($judulLaporan, $users, $columns);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('user')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function combostatus(Request $request)
    {

        $params = [
            'status' => $request->status ?? '',
            'grp' => $request->grp ?? '',
            'subgrp' => $request->subgrp ?? '',
        ];
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        if ($params['status'] == 'entry') {
            $query = Parameter::select('id', 'text as keterangan')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);
        } else {
            Schema::create($temp, function ($table) {
                $table->integer('id')->length(11)->nullable();
                $table->string('parameter', 50)->nullable();
                $table->string('param', 50)->nullable();
            });

            DB::table($temp)->insert(
                [
                    'id' => '0',
                    'parameter' => 'ALL',
                    'param' => '',
                ]
            );

            $queryall = Parameter::select('id', 'text as parameter', 'text as param')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);

            $query = DB::table($temp)
                ->unionAll($queryall);
        }

        $data = $query->get();

        return response([
            'data' => $data
        ]);
    }

    public function combocabang(Request $request)
    {
        $params = [
            'status' => $request->status ?? '',
        ];
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        if ($params['status'] == 'entry') {
            $query = Cabang::select('cabang.id as id', 'cabang.namacabang as namacabang')
                ->leftJoin('parameter', 'cabang.statusaktif', '=', 'parameter.id')
                ->where('parameter.text', "=", 'AKTIF');
        } else {
            Schema::create($temp, function ($table) {
                $table->string('id', 10)->nullable();
                $table->string('namacabang', 150)->nullable();
                $table->string('param', 50)->nullable();
            });

            DB::table($temp)->insert(
                [
                    'id' => '0',
                    'namacabang' => 'ALL',
                    'param' => '',
                ]
            );


            $queryall = Cabang::select('cabang.id as id', 'cabang.namacabang as namacabang', 'cabang.namacabang as param')
                ->leftJoin('parameter', 'cabang.statusaktif', '=', 'parameter.id')
                ->where('parameter.text', "=", 'AKTIF');

            $query = DB::table($temp)
                ->unionAll($queryall);
        }

        $data = $query->get();

        return response([
            'data' => $data->toArray(),
        ]);
    }

    public function getuserid(Request $request)
    {

        $params = [
            'user' => $request->user ?? '',
        ];

        $query = User::select('id')
            ->where('user', "=", $params['user']);

        $data = $query->first();

        return response([
            'data' => $data
        ]);
    }
}
