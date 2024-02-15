<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Models\BccEmail;
use App\Http\Requests\StoreBccEmailRequest;
use App\Http\Requests\UpdateBccEmailRequest;
use Illuminate\Support\Facades\DB;

class BccEmailController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $bccEmail = new BccEmail();
        return response([
            'data' => $bccEmail->get(),
            'attributes' => [
                'totalRows' => $bccEmail->totalRows,
                'totalPages' => $bccEmail->totalPages
            ]
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreBccEmailRequest $request)
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

            $bccEmail = (new BccEmail())->processStore($data);
            if ($request->from == '') {
                $bccEmail->position = $this->getPosition($bccEmail, $bccEmail->getTable())->position;
                if ($request->limit == 0) {
                    $bccEmail->page = ceil($bccEmail->position / (10));
                } else {
                    $bccEmail->page = ceil($bccEmail->position / ($request->limit ?? 10));
                }
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $bccEmail->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('bccemail', 'add', $data);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $bccEmail
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(BccEmail $bccemail)
    {
        return response([
            'status' => true,
            'data' => $bccemail->findAll($bccemail->id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateBccEmailRequest $request, BccEmail $bccemail)
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

            $bccEmail = (new BccEmail())->processUpdate($bccemail, $data);
            if ($request->from == '') {
                $bccEmail->position = $this->getPosition($bccEmail, $bccEmail->getTable())->position;
                if ($request->limit == 0) {
                    $bccEmail->page = ceil($bccEmail->position / (10));
                } else {
                    $bccEmail->page = ceil($bccEmail->position / ($request->limit ?? 10));
                }
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $bccemail->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('bccemail', 'edit', $data);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $bccEmail
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
    public function destroy(BccEmail $bccemail)
    {
        DB::beginTransaction();

        try {

            $bccEmail = (new BccEmail())->processDestroy($bccemail);
            if (request()->from == '') {
                $bccEmail->position = $this->getPosition($bccEmail, $bccEmail->getTable())->position;
                if (request()->limit == 0) {
                    $bccEmail->page = ceil($bccEmail->position / (10));
                } else {
                    $bccEmail->page = ceil($bccEmail->position / (request()->limit ?? 10));
                }
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $bccemail;

            $data["accessTokenTnl"] = request()->accessTokenTnl ?? '';

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('bccemail', 'delete', $data);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $bccEmail
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
            (new BccEmail())->processApprovalnonaktif($data);

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
