<?php

namespace App\Http\Controllers\Api;



use App\Http\Controllers\Controller;
use App\Http\Requests\UbahPasswordRequest;
use App\Models\UbahPassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UbahPasswordController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
    } 
    /**
    * @ClassName
    */
    public function store(UbahPasswordRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $ubah = User::find($request->id);
            $data = [
                'password' => Hash::make($request->password),
            ];
            $ubahPassword = (new UbahPassword())->processUpdate($ubah, $data);
            $ubahPassword->position = $this->getPosition($ubahPassword, $ubahPassword->getTable())->position;
            if ($request->limit==0) {
                $ubahPassword->page = ceil($ubahPassword->position / (10));
            } else {
                $ubahPassword->page = ceil($ubahPassword->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $ubahPassword
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
}