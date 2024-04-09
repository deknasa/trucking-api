<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Error;
use App\Models\CcEmail;
use App\Models\MyModel;
use App\Models\Parameter;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCcEmailRequest;
use App\Http\Requests\UpdateCcEmailRequest;
use App\Http\Requests\ApprovalKaryawanRequest;

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

    public function cekValidasi($id)
    {
        $dataMaster = CcEmail::where('id',$id)->first();
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

                    (new MyModel())->updateEditingBy('ccemail', $id, $aksi);
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
            
            (new MyModel())->updateEditingBy('ccemail', $id, $aksi);
                
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
