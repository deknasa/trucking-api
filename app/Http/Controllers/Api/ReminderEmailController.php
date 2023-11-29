<?php

namespace App\Http\Controllers\Api;
use App\Models\ReminderEmail;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\ReminderEmailService;
use App\DataTransferObject\ReminderEmailDTO;
use App\Http\Requests\StoreReminderEmailRequest;
use App\Http\Requests\UpdateReminderEmailRequest;

class ReminderEmailController extends Controller
{
    protected $service;    
    function __construct(ReminderEmailService $service) {
        $this->service = $service;
    }
    
    /**
     * @ClassName 
     */
    public function index()
    {
        
        $reminderEmail = new ReminderEmail();
        return response([
            'data' => $reminderEmail->get(),
            'attributes' => [
                'totalRows' => $reminderEmail->totalRows,
                'totalPages' => $reminderEmail->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreReminderEmailRequest $request)
    {
        DB::beginTransaction();
        try {
            $reminderEmail = $this->service->store(
                ReminderEmailDTO::dataRequest($request)
            );
            $reminderEmail->position = $this->getPosition($reminderEmail, $reminderEmail->getTable())->position;
            if ($request->limit==0) {
                $reminderEmail->page = ceil($reminderEmail->position / (10));
            } else {
                $reminderEmail->page = ceil($reminderEmail->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $reminderEmail
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

    }

    /**
     * @ClassName 
     */
    public function show(ReminderEmail $reminderemail)
    {
        return response([
            'status' => true,
            'data' => $reminderemail->findAll($reminderemail->id)
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(StoreReminderEmailRequest $request, ReminderEmail $reminderemail)
    {
        DB::beginTransaction();
        try {
            $reminderEmail = $this->service->update(
                $reminderemail,
                ReminderEmailDTO::dataRequest($request)
            );
            $reminderEmail->position = $this->getPosition($reminderEmail, $reminderEmail->getTable())->position;
            if ($request->limit==0) {
                $reminderEmail->page = ceil($reminderEmail->position / (10));
            } else {
                $reminderEmail->page = ceil($reminderEmail->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $reminderEmail
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function destroy(ReminderEmail $reminderemail)
    {
        DB::beginTransaction();

        try {
          
            $reminderemail = (new ReminderEmail())->processDestroy($reminderemail);
            $reminderemail->position = $this->getPosition($reminderemail, $reminderemail->getTable())->position;
            if (request()->limit==0) {
                $reminderemail->page = ceil($reminderemail->position / (10));
            } else {
                $reminderemail->page = ceil($reminderemail->position / (request()->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $reminderemail
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
