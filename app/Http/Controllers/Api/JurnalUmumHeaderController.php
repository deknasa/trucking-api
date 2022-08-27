<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JurnalUmumHeader;
use App\Models\JurnalUmumDetail;

use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\UpdateJurnalUmumHeaderRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JurnalUmumHeaderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Http\Requests\StoreJurnalUmumHeaderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreJurnalUmumHeaderRequest $request)
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'nobukti' => 'required',
        ], [
            'nobukti.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
        ], [
            'nobukti' => 'NoBukti',
        ]);
        if (!$validator->passes()) {
            return [
                'error' => true,
                'messages' => $validator->messages()
            ];
        }

        try {
            $jurnalumumHeader = new JurnalUmumHeader();

            $jurnalumumHeader->nobukti = $request->nobukti;
            $jurnalumumHeader->tglbukti = $request->tgl;
            $jurnalumumHeader->keterangan = $request->keterangan ?? '';
            $jurnalumumHeader->postingdari = $request->postingdari;
            $jurnalumumHeader->statusapproval = $request->statusapproval;
            $jurnalumumHeader->userapproval = $request->userapproval;
            $jurnalumumHeader->tglapproval = $request->tglapproval;
            $jurnalumumHeader->modifiedby = $request->modifiedby;
            
            $jurnalumumHeader->save();
            
           
            DB::commit();
            if ($validator->passes()) {
                return [
                    'error' => false,
                    'id' => $jurnalumumHeader->id,
                    'tabel' => $jurnalumumHeader->getTable(),
                ];
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\JurnalUmumHeader  $jurnalUmumHeader
     * @return \Illuminate\Http\Response
     */
    public function show(JurnalUmumHeader $jurnalUmumHeader)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\JurnalUmumHeader  $jurnalUmumHeader
     * @return \Illuminate\Http\Response
     */
    public function edit(JurnalUmumHeader $jurnalUmumHeader)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateJurnalUmumHeaderRequest  $request
     * @param  \App\Models\JurnalUmumHeader  $jurnalUmumHeader
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateJurnalUmumHeaderRequest $request, JurnalUmumHeader $jurnalUmumHeader)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\JurnalUmumHeader  $jurnalUmumHeader
     * @return \Illuminate\Http\Response
     */
    public function destroy(JurnalUmumHeader $jurnalUmumHeader, $id)
    {
        DB::beginTransaction();

        try {
            $get = JurnalUmumHeader::find($id);
            $delete = JurnalUmumDetail::where('jurnalumum_id',$id)->delete();
            $delete = JurnalUmumHeader::destroy($id);
            
            $datalogtrail = [
                'namatabel' => $kasGantungHeader->getTable(),
                'postingdari' => 'HAPUS JURNAL UMUM',
                'idtrans' => $id,
                'nobuktitrans' => $get->nobukti,
                'aksi' => 'HAPUS',
                'datajson' => '',
                'modifiedby' => $get->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            if ($delete) {
                DB::commit();
                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus'
                ]);
            } else {
                DB::rollBack();
                return response([
                    'status' => false,
                    'message' => 'Gagal dihapus'
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }
}
