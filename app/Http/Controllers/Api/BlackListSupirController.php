<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Models\BlackListSupir;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlackListSupirRequest;
use App\Http\Requests\UpdateBlackListSupirRequest;

class BlackListSupirController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $blackListSupir = new BlackListSupir();

        return response([
            'data' => $blackListSupir->get(),
            'attributes' => [
                'totalRows' => $blackListSupir->totalRows,
                'totalPages' => $blackListSupir->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $dataMaster = BlackListSupir::where('id',$id)->first();
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

                    (new MyModel())->updateEditingBy('BlackListSupir', $id, $aksi);
                }

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                // return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'Data <b>' . $dataMaster->namasupir . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'SDE',
                    'statuspesan' => 'warning',
                ];

                return response($data);
            }            
            
        } else {
            (new MyModel())->updateEditingBy('BlackListSupir', $id, $aksi);
            
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
    public function store(StoreBlackListSupirRequest $request)
    {
        DB::beginTransaction();
        try {
            
            $data =[
                "namasupir" => $request->namasupir,
                "noktp" => $request->noktp,
                "nosim" => $request->nosim,
            ];
            /* Store header */
            $blackListSupir = (new BlackListSupir())->processStore($data);
            /* Set position and page */
            $blackListSupir->position = $this->getPosition($blackListSupir, $blackListSupir->getTable())->position;
            if ($request->limit==0) {
                $blackListSupir->page = ceil($blackListSupir->position / (10));
            } else {
                $blackListSupir->page = ceil($blackListSupir->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $blackListSupir
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function show(BlackListSupir $blackListSupir,$id)
    {
        $blackListSupir = new BlackListSupir();
        return response([
            'data' => $blackListSupir->findOrFail($id),
            'attributes' => [
                'totalRows' => $blackListSupir->totalRows,
                'totalPages' => $blackListSupir->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateBlackListSupirRequest $request, BlackListSupir $blackListSupir, $id)
    {
        DB::beginTransaction();
        try {
            $data =[
                "namasupir" => $request->namasupir,
                "noktp" => $request->noktp,
                "nosim" => $request->nosim,
            ];
            /* Store header */
            $blackListSupir = BlackListSupir::findOrFail($id);
            $blackListSupir = (new BlackListSupir())->processUpdate($blackListSupir,$data);
            /* Set position and page */
            $blackListSupir->position = $this->getPosition($blackListSupir, $blackListSupir->getTable())->position;
            if ($request->limit==0) {
                $blackListSupir->page = ceil($blackListSupir->position / (10));
            } else {
                $blackListSupir->page = ceil($blackListSupir->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $blackListSupir
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
    public function destroy(BlackListSupir $blackListSupir,$id, Request $request)
    {
        DB::beginTransaction();
        try {
            // dd($blackListSupir);
            $blackListSupir = (new BlackListSupir())->processDestroy($id);
            /* Set position and page */
            $blackListSupir->position = $this->getPosition($blackListSupir, $blackListSupir->getTable())->position;
            if ($request->limit==0) {
                $blackListSupir->page = ceil($blackListSupir->position / (10));
            } else {
                $blackListSupir->page = ceil($blackListSupir->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $blackListSupir
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
