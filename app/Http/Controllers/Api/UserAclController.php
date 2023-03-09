<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreUserAclRequest;
use App\Http\Requests\UpdateUserAclRequest;
use App\Http\Requests\DestroyUserAclRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\UserAcl;
use App\Models\Parameter;
use App\Models\User;
use App\Models\Acos;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Rules\NotExistsRule;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Requests\StoreAclRequest;
use Illuminate\Http\JsonResponse;

class UserAclController extends Controller
{
    /**
     * @ClassName 
     */
    public function index(User $user): JsonResponse
    {
        $userAcls = new UserAcl();

        return response()->json([
            'data' => $userAcls->get($user->acls()),
            'attributes' => [
                'totalRows' => $userAcls->totalRows,
                'totalPages' => $userAcls->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreAclRequest $request, User $user): JsonResponse
    {
        DB::beginTransaction();

        try {
            $user->acls()->detach();

            foreach ($request->aco_ids as $aco_id) {
                $user->acls()->attach($aco_id, [
                    'modifiedby' => auth('api')->user()->name
                ]);
            }

            $logTrail = [
                'namatabel' => strtoupper($user->getTable()),
                'postingdari' => 'ENTRY USER ACL',
                'idtrans' => $user->id,
                'nobuktitrans' => $user->id,
                'aksi' => 'ENTRY',
                'datajson' => $user->load('acls')->toArray(),
                'modifiedby' => $user->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'user' => $user->load('acls')
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
}
