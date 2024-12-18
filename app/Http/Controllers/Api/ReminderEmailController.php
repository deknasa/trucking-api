<?php

namespace App\Http\Controllers\Api;

use DateTime;

use App\Models\Error;
use App\Models\MyModel;
use App\Models\Parameter;
use App\Models\ReminderEmail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\ReminderEmailService;
use App\DataTransferObject\ReminderEmailDTO;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\StoreReminderEmailRequest;
use App\Http\Requests\UpdateReminderEmailRequest;
use Illuminate\Http\Request;

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

    public function cekValidasi($id)
    {
        $dataMaster = ReminderEmail::where('id', $id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';
        if ($useredit != '' && $useredit != $user) {

            $waktu = (new Parameter())->cekBatasWaktuEdit('BATAS WAKTU EDIT MASTER');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($dataMaster->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {

                    (new MyModel())->updateEditingBy('reminderemail', $id, $aksi);
                }

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                // return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'Data <b>' . $dataMaster->keterangan . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'SDE',
                    'statuspesan' => 'warning',
                ];

                return response($data);
            }
        } else {

            (new MyModel())->updateEditingBy('reminderemail', $id, $aksi);

            $data = [
                'error' => false,
                'message' => '',
                'kodeerror' => '',
                'statuspesan' => 'success',
            ];


            return response($data);
        }
    }


    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreReminderEmailRequest $request)
    {
        $data = [
            'id' => $request->id,
            'keterangan' => $request->keterangan,
            'statusaktif' => $request->statusaktif,
            'tas_id' => $request->tas_id ?? '',
            "key" => $request->key,
            "value" => $request->value,
            "accessTokenTnl" => $request->accessTokenTnl ?? '',
        ];

        // dd($request->input('accessTokenTnl'));
        DB::beginTransaction();
        try {
            // $reminderEmail = $this->service->store(
            //     [
            //         "keterangan" => $request->input('keterangan'),
            //         "statusaktif" => $request->input('statusaktif'),
            //         "tas_id" => $request->input('tas_id'),
            //         "accessTokenTnl" => $request->input('accessTokenTnl')
            //     ]
            // );
            $reminderEmail = new ReminderEmail();
            $reminderEmail->processStore($data, $reminderEmail);
            if ($request->from == '') {
                $reminderEmail->position = $this->getPosition($reminderEmail, $reminderEmail->getTable())->position;
                if ($request->limit == 0) {
                    $reminderEmail->page = ceil($reminderEmail->position / (10));
                } else {
                    $reminderEmail->page = ceil($reminderEmail->position / ($request->limit ?? 10));
                }
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            // $data = [
            //     "keterangan" => $request->input('keterangan'),
            //     "statusaktif" => $request->input('statusaktif'),
            //     "tas_id" => $request->input('tas_id'),
            //     "accessTokenTnl" => $request->input('accessTokenTnl')
            // ];
            $data['tas_id'] = $reminderEmail->id;

            // dd($data);
            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                // $this->saveToTnl('reminderemail', 'add',   $data);
                $this->SaveTnlNew('reminderemail', 'add',   $data);
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
     * @Keterangan EDIT DATA
     */
    public function update(UpdateReminderEmailRequest $request, $id)
    {
        $data = [
            'id' => $request->id,
            'keterangan' => $request->keterangan,
            'statusaktif' => $request->statusaktif,
            "key" => $request->key,
            "value" => $request->value,
            "accessTokenTnl" => $request->accessTokenTnl ?? '',
        ];
// dd('test');
        DB::beginTransaction();
        try {
            // $reminderEmail = $this->service->update(
            //     $reminderemail,
            //     [
            //         "keterangan" => $request->input('keterangan'),
            //         "statusaktif" => $request->input('statusaktif'),
            //         "tas_id" => $request->input('tas_id'),
            //         "accessTokenTnl" => $request->input('accessTokenTnl')
            //     ]
            // );

            $reminderEmail = new ReminderEmail();
            $reminderEmails = $reminderEmail->findOrFail($id);
            $reminderEmail = $reminderEmail->processUpdate($reminderEmails, $data);
            if ($request->from == '') {
                $reminderEmail->position = $this->getPosition($reminderEmail, $reminderEmail->getTable())->position;

                if ($request->limit == 0) {
                    $reminderEmail->page = ceil($reminderEmail->position / (10));
                } else {
                    $reminderEmail->page = ceil($reminderEmail->position / ($request->limit ?? 10));
                }
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            // $data = [
            //     "keterangan" => $request->input('keterangan'),
            //     "statusaktif" => $request->input('statusaktif'),
            //     "tas_id" => $request->input('tas_id'),
            //     "accessTokenTnl" => $request->input('accessTokenTnl')
            // ];
            $data['tas_id'] = $reminderEmail->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                // $this->saveToTnl('reminderemail', 'edit', $data);
                $this->SaveTnlNew('reminderemail', 'edit', $data);
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
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            // $reminderemail = (new ReminderEmail())->processDestroy($reminderemail);
            $reminderemail = new Reminderemail();
            $reminderemails = $reminderemail->findOrFail($id);
            $reminderemail = $reminderemail->processDestroy($reminderemails);
            if (request()->from == '') {
                $reminderemail->position = $this->getPosition($reminderemail, $reminderemail->getTable())->position;
                if (request()->limit == 0) {
                    $reminderemail->page = ceil($reminderemail->position / (10));
                } else {
                    $reminderemail->page = ceil($reminderemail->position / (request()->limit ?? 10));
                }
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
