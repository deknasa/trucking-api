<?php

namespace App\Http\Controllers\Api;

use App\Models\ReminderEmail;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\ReminderEmailService;
use App\DataTransferObject\ReminderEmailDTO;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\StoreReminderEmailRequest;
use App\Http\Requests\UpdateReminderEmailRequest;

class ReminderEmailController extends Controller
{
    protected $service;
    function __construct(ReminderEmailService $service)
    {
        $this->service = $service;
    }

    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
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
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreReminderEmailRequest $request)
    {
        // $data = [
        //     'id' => $request->id,
        //     'keterangan' => $request->keterangan,
        //     'statusaktif' => $request->statusaktif,
        //     'tas_id' => $request->tas_id ?? '',
        //     "accessTokenTnl" => $request->accessTokenTnl ?? '',
        // ];

        DB::beginTransaction();
        try {
            $reminderEmail = $this->service->store(
                ReminderEmailDTO::dataRequest($request)
            );
            $reminderEmail->position = $this->getPosition($reminderEmail, $reminderEmail->getTable())->position;
            if ($request->limit == 0) {
                $reminderEmail->page = ceil($reminderEmail->position / (10));
            } else {
                $reminderEmail->page = ceil($reminderEmail->position / ($request->limit ?? 10));
            }

            // $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            // // $data['tas_id'] = $reminderEmail->id;

            // if ($cekStatusPostingTnl->text == 'POSTING TNL') {
            //     $this->saveToTnl('reminderemail', 'add', ReminderEmailDTO::dataRequest($request));
            // }

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
     * @Keterangan EDIT DATA
     */
    public function update(StoreReminderEmailRequest $request, ReminderEmail $reminderemail)
    {
        // $data = [
        //     'id' => $request->id,
        //     'keterangan' => $request->keterangan,
        //     'statusaktif' => $request->statusaktif,
        //     "accessTokenTnl" => $request->accessTokenTnl ?? '',
        // ];

        DB::beginTransaction();
        try {
            $reminderEmail = $this->service->update(
                $reminderemail,
                ReminderEmailDTO::dataRequest($request)
            );
            $reminderEmail->position = $this->getPosition($reminderEmail, $reminderEmail->getTable())->position;
            if ($request->limit == 0) {
                $reminderEmail->page = ceil($reminderEmail->position / (10));
            } else {
                $reminderEmail->page = ceil($reminderEmail->position / ($request->limit ?? 10));
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            // $data['tas_id'] = $reminderEmail->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('reminderemail', 'edit',ReminderEmailDTO::dataRequest($request));
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
     * @Keterangan HAPUS DATA
     */
    public function destroy(ReminderEmail $reminderemail)
    {
        DB::beginTransaction();

        try {

            $reminderemail = (new ReminderEmail())->processDestroy($reminderemail);
            $reminderemail->position = $this->getPosition($reminderemail, $reminderemail->getTable())->position;
            if (request()->limit == 0) {
                $reminderemail->page = ceil($reminderemail->position / (10));
            } else {
                $reminderemail->page = ceil($reminderemail->position / (request()->limit ?? 10));
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $reminderemail;

            $data["accessTokenTnl"] = request()->accessTokenTnl ?? '';

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('reminderemail', 'delete', $data);
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
            (new ReminderEmail())->processApprovalnonaktif($data);

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
