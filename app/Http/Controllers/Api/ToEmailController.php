<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\ToEmail;
use App\Http\Requests\StoreToEmailRequest;
use App\Http\Requests\UpdateToEmailRequest;
use Illuminate\Support\Facades\DB;

class ToEmailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreToEmailRequest  $request
     * @return \Illuminate\Http\Response
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ToEmail  $toEmail
     * @return \Illuminate\Http\Response
     */
    public function show(ToEmail $toemail)
    {
        return response([
            'status' => true,
            'data' => $toemail->findAll($toemail->id)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateToEmailRequest  $request
     * @param  \App\Models\ToEmail  $toEmail
     * @return \Illuminate\Http\Response
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ToEmail  $toEmail
     * @return \Illuminate\Http\Response
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
}
