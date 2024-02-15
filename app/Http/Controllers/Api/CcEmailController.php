<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Models\CcEmail;
use App\Http\Requests\StoreCcEmailRequest;
use App\Http\Requests\UpdateCcEmailRequest;
use Illuminate\Support\Facades\DB;

class CcEmailController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $ccEmail = new CcEmail();
        return response([
            'data' => $ccEmail->get(),
            'attributes' => [
                'totalRows' => $ccEmail->totalRows,
                'totalPages' => $ccEmail->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreCcEmailRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'nama' => $request->nama,
                'email' => $request->email,
                'statusaktif' => $request->statusaktif,
                'reminderemail_id' => $request->reminderemail_id,
                'tas_id' => $request->tas_id ?? '',
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
            ];

            $ccEmail = (new CcEmail())->processStore($data);
            if ($request->from == '') {
                $ccEmail->position = $this->getPosition($ccEmail, $ccEmail->getTable())->position;
                if ($request->limit == 0) {
                    $ccEmail->page = ceil($ccEmail->position / (10));
                } else {
                    $ccEmail->page = ceil($ccEmail->position / ($request->limit ?? 10));
                }
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $ccEmail->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('ccemail', 'add', $data);
            }


            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $ccEmail
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(CcEmail $ccemail)
    {
        return response([
            'status' => true,
            'data' => $ccemail->findAll($ccemail->id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateCcEmailRequest $request, CcEmail $ccemail)
    {
        DB::beginTransaction();

        try {
            $data = [
                'nama' => $request->nama,
                'email' => $request->email,
                'statusaktif' => $request->statusaktif,
                'reminderemail_id' => $request->reminderemail_id,
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
            ];

            $ccEmail = (new CcEmail())->processUpdate($ccemail, $data);
            if ($request->from == '') {
                $ccEmail->position = $this->getPosition($ccEmail, $ccEmail->getTable())->position;
                if ($request->limit == 0) {
                    $ccEmail->page = ceil($ccEmail->position / (10));
                } else {
                    $ccEmail->page = ceil($ccEmail->position / ($request->limit ?? 10));
                }
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $ccEmail->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('ccemail', 'edit', $data);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $ccEmail
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(CcEmail $ccemail)
    {
        DB::beginTransaction();

        try {

            $ccEmail = (new CcEmail())->processDestroy($ccemail);
            if (request()->from == '') {
                $ccEmail->position = $this->getPosition($ccEmail, $ccEmail->getTable())->position;
                if (request()->limit == 0) {
                    $ccEmail->page = ceil($ccEmail->position / (10));
                } else {
                    $ccEmail->page = ceil($ccEmail->position / (request()->limit ?? 10));
                }
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $ccemail;

            $data["accessTokenTnl"] = request()->accessTokenTnl ?? '';

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('ccemail', 'delete', $data);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $ccEmail
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    /**
     * @ClassName 
     * @Keterangan APRROVAL NON AKTIF
     */
    public function approvalnonaktif(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
            ];
            (new CcEmail())->processApprovalnonaktif($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
