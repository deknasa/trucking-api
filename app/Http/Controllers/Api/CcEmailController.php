<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\CcEmail;
use App\Http\Requests\StoreCcEmailRequest;
use App\Http\Requests\UpdateCcEmailRequest;
use Illuminate\Support\Facades\DB;

class CcEmailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
     * @param  \App\Http\Requests\StoreCcEmailRequest  $request
     * @return \Illuminate\Http\Response
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
            ];

            $ccEmail = (new CcEmail())->processStore($data);
            $ccEmail->position = $this->getPosition($ccEmail, $ccEmail->getTable())->position;
            if ($request->limit==0) {
                $ccEmail->page = ceil($ccEmail->position / (10));
            } else {
                $ccEmail->page = ceil($ccEmail->position / ($request->limit ?? 10));
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
     * Display the specified resource.
     *
     * @param  \App\Models\CcEmail  $ccEmail
     * @return \Illuminate\Http\Response
     */
    public function show(CcEmail $ccemail)
    {
        return response([
            'status' => true,
            'data' => $ccemail->findAll($ccemail->id)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCcEmailRequest  $request
     * @param  \App\Models\CcEmail  $ccEmail
     * @return \Illuminate\Http\Response
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
            ];

            $ccEmail = (new CcEmail())->processUpdate($ccemail, $data);
            $ccEmail->position = $this->getPosition($ccEmail, $ccEmail->getTable())->position;
            if ($request->limit==0) {
                $ccEmail->page = ceil($ccEmail->position / (10));
            } else {
                $ccEmail->page = ceil($ccEmail->position / ($request->limit ?? 10));
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CcEmail  $ccEmail
     * @return \Illuminate\Http\Response
     */
    public function destroy(CcEmail $ccemail)
    {
        DB::beginTransaction();

        try {
          
            $ccEmail = (new CcEmail())->processDestroy($ccemail);
            $ccEmail->position = $this->getPosition($ccEmail, $ccEmail->getTable())->position;
            if (request()->limit==0) {
                $ccEmail->page = ceil($ccEmail->position / (10));
            } else {
                $ccEmail->page = ceil($ccEmail->position / (request()->limit ?? 10));
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
}
