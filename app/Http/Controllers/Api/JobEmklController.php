<?php

namespace App\Http\Controllers\Api;

use App\Models\JobEmkl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class JobEmklController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $jobEmkl = new JobEmkl();

        return response([
            'data' => $jobEmkl->get(),
            'attributes' => [
                'totalRows' => $jobEmkl->totalRows,
                'totalPages' => $jobEmkl->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                "tglbukti" => $request->tglbukti ?? '',
                "shipper_id" => $request->shipper_id ?? '',
                "shipper" => $request->shipper ?? '',
                "tujuan_id" => $request->tujuan_id ?? '',
                "tujuan" => $request->tujuan ?? '',
                "container_id" => $request->container_id ?? '',
                "container" => $request->container ?? '',
                "jenisorder_id" => $request->jenisorder_id ?? '',
                "jenisorder" => $request->jenisorder ?? '',
                "kapal" => $request->kapal ?? '',
                "destination" => $request->destination ?? '',
                "nocont" => $request->nocont ?? '',
                "noseal" => $request->noseal ?? '',
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
            ];
            $jobEmkl = new JobEmkl();
            $jobEmkl->processStore($data, $jobEmkl);            
            if ($request->from == '') {
                $jobEmkl->position = $this->getPosition($jobEmkl, $jobEmkl->getTable())->position;
                if ($request->limit == 0) {
                    $jobEmkl->page = ceil($jobEmkl->position / (10));
                } else {
                    $jobEmkl->page = ceil($jobEmkl->position / ($request->limit ?? 10));
                }
            }


            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $jobEmkl->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->SaveTnlNew('jenistrado', 'add', $data);
            }


            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $jobEmkl
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        
    }
    
    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(Request $request, $id)
    {
        //
    }
    
    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy($id)
    {
        //
    }

    public function default()
    {

        $jobEmkl = new JobEmkl();
        return response([
            'status' => true,
            'data' => [],
        ]);
    }
    public function fieldLength()
    {

        $jobEmkl = new JobEmkl();
        return response([
            'status' => true,
            'data' => [],
        ]);
    }
}
