<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Models\ToEmail;
use App\Http\Requests\StoreToEmailRequest;
use App\Http\Requests\UpdateToEmailRequest;
use Illuminate\Support\Facades\DB;

class ToEmailController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $toEmail = new ToEmail();
        return response([
            'data' => $toEmail->get(),
            'attributes' => [
                'totalRows' => $toEmail->totalRows,
                'totalPages' => $toEmail->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreToEmailRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'nama' => $request->nama,
                'email' => $request->email,
                'statusaktif' => $request->statusaktif,
                'reminderemail_id' => $request->reminderemail_id,
            ];

            $toEmail = (new ToEmail())->processStore($data);
            $toEmail->position = $this->getPosition($toEmail, $toEmail->getTable())->position;
            if ($request->limit==0) {
                $toEmail->page = ceil($toEmail->position / (10));
            } else {
                $toEmail->page = ceil($toEmail->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $toEmail
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(ToEmail $toemail)
    {
        return response([
            'status' => true,
            'data' => $toemail->findAll($toemail->id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateToEmailRequest $request, ToEmail $toemail)
    {
        DB::beginTransaction();

        try {
            $data = [
                'nama' => $request->nama,
                'email' => $request->email,
                'statusaktif' => $request->statusaktif,
                'reminderemail_id' => $request->reminderemail_id,
            ];

            $toEmail = (new ToEmail())->processUpdate($toemail, $data);
            $toEmail->position = $this->getPosition($toEmail, $toEmail->getTable())->position;
            if ($request->limit==0) {
                $toEmail->page = ceil($toEmail->position / (10));
            } else {
                $toEmail->page = ceil($toEmail->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $toEmail
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
    public function destroy(ToEmail $toemail)
    {
        DB::beginTransaction();

        try {
          
            $toEmail = (new ToEmail())->processDestroy($toemail);
            $toEmail->position = $this->getPosition($toEmail, $toEmail->getTable())->position;
            if (request()->limit==0) {
                $toEmail->page = ceil($toEmail->position / (10));
            } else {
                $toEmail->page = ceil($toEmail->position / (request()->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $toEmail
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
            (new ToEmail())->processApprovalnonaktif($data);

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
