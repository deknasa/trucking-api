<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\BccEmail;
use App\Http\Requests\StoreBccEmailRequest;
use App\Http\Requests\UpdateBccEmailRequest;
use Illuminate\Support\Facades\DB;

class BccEmailController extends Controller
{
    /**
     * @ClassName 
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
            ];

            $bccEmail = (new BccEmail())->processStore($data);
            $bccEmail->position = $this->getPosition($bccEmail, $bccEmail->getTable())->position;
            if ($request->limit==0) {
                $bccEmail->page = ceil($bccEmail->position / (10));
            } else {
                $bccEmail->page = ceil($bccEmail->position / ($request->limit ?? 10));
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
     */
    public function show(BccEmail $bccemail)
    {
        return response([
            'status' => true,
            'data' => $bccemail->findAll($bccemail->id)
        ]);
    }

    /**
     * @ClassName 
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
            ];

            $bccEmail = (new BccEmail())->processUpdate($bccemail, $data);
            $bccEmail->position = $this->getPosition($bccEmail, $bccEmail->getTable())->position;
            if ($request->limit==0) {
                $bccEmail->page = ceil($bccEmail->position / (10));
            } else {
                $bccEmail->page = ceil($bccEmail->position / ($request->limit ?? 10));
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
     */
    public function destroy(BccEmail $bccemail)
    {
        DB::beginTransaction();

        try {
          
            $bccEmail = (new BccEmail())->processDestroy($bccemail);
            $bccEmail->position = $this->getPosition($bccEmail, $bccEmail->getTable())->position;
            if (request()->limit==0) {
                $bccEmail->page = ceil($bccEmail->position / (10));
            } else {
                $bccEmail->page = ceil($bccEmail->position / (request()->limit ?? 10));
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
}
