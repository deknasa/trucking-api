<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\ToEmail;
use App\Models\Parameter;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreToEmailRequest;
use App\Http\Requests\UpdateToEmailRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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

    public function cekValidasi($id)
    {
        $dataMaster = ToEmail::where('id',$id)->first();
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

                    (new MyModel())->updateEditingBy('toemail', $id, $aksi);
                }

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                // return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'Data <b>' .$dataMaster->email . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'SDE',
                    'statuspesan' => 'warning',
                ];

                return response($data);
            }            
            
        } else {
            
            (new MyModel())->updateEditingBy('toemail', $id, $aksi);
                
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
    public function store(StoreToEmailRequest $request)
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
            $toEmail = new ToEmail();
            $toEmail->processStore($data, $toEmail);
            // $toEmail = (new ToEmail())->processStore($data);
            if ($request->from == '') {
                $toEmail->position = $this->getPosition($toEmail, $toEmail->getTable())->position;
                if ($request->limit == 0) {
                    $toEmail->page = ceil($toEmail->position / (10));
                } else {
                    $toEmail->page = ceil($toEmail->position / ($request->limit ?? 10));
                }
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $toEmail->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->SaveTnlNew('toemail', 'add', $data);
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
    public function update(UpdateToEmailRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'id' => $request->id,
                'nama' => $request->nama,
                'email' => $request->email,
                'statusaktif' => $request->statusaktif,
                'reminderemail_id' => $request->reminderemail_id,
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
            ];
            $toEmail = new ToEmail();
            $toEmails = $toEmail->findOrFail($id);
            $toEmail = $toEmail->processUpdate($toEmails, $data);
            // $toEmail = (new ToEmail())->processUpdate($toemail, $data);
            if ($request->from == '') {
                $toEmail->position = $this->getPosition($toEmail, $toEmail->getTable())->position;
                if ($request->limit == 0) {
                    $toEmail->page = ceil($toEmail->position / (10));
                } else {
                    $toEmail->page = ceil($toEmail->position / ($request->limit ?? 10));
                }
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $toEmail->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->SaveTnlNew('toemail', 'edit', $data);
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
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            // $toEmail = (new ToEmail())->processDestroy($toemail);
            $toEmail = new ToEmail();
            $toEmails = $toEmail->findOrFail($id);
            $toEmail = $toEmail->processDestroy($toEmails);            
            if (request()->from == '') {
                $toEmail->position = $this->getPosition($toEmail, $toEmail->getTable())->position;
                if (request()->limit == 0) {
                    $toEmail->page = ceil($toEmail->position / (10));
                } else {
                    $toEmail->page = ceil($toEmail->position / (request()->limit ?? 10));
                }
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $id;

            $data["accessTokenTnl"] = $request->accessTokenTnl ?? '';

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                // DD('TEST');
                $this->SaveTnlNew('toemail', 'delete', $data);
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
