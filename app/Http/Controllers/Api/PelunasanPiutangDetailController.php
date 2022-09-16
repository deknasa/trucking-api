<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PelunasanPiutangDetail;
use App\Http\Requests\StorePiutangDetailRequest;
use App\Http\Requests\UpdatePiutangDetailRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PelunasanPiutangDetailController extends Controller
{
    
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'pelunasanpiutang_id' => $request->pelunasanpiutang_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];
        try {
            $query = PelunasanPiutangDetail::from('pelunasanpiutangdetail as detail');

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['pelunasanpiutang_id'])) {
                $query->where('detail.pelunasanpiutang_id', $params['pelunasanpiutang_id']);
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('pelunasanpiutang_id', $params['whereIn']);
            }
            if ($params['forReport']) {
                $query->select(
                    'detail.nominal',
                    'detail.keterangan'
                );

                $piutangDetail = $query->get();
            } else {
                $query->select(
                    'detail.nobukti',
                    'detail.tgl',
                    'detail.nominal',
                    'detail.keterangan',
                    'detail.piutang_nobukti',

                    'pelanggan.namapelanggan as pelanggan_id',
                    'agen.namaagen as agen_id',
                )
                ->leftJoin('pelanggan', 'detail.pelanggan_id', 'pelanggan.id')
                ->leftJoin('agen', 'detail.agen_id', 'agen.id');
                $piutangDetail = $query->get();
            }

            return response([
                'data' => $piutangDetail
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }


    public function store(StorePiutangDetailRequest $request)
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'nominal' => 'required',
           'keterangan' => 'required'
        ], [
            'nominal.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            'keterangan.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan
        ]);
        // dd($request->all());

        if (!$validator->passes()) {
            return [
                'error' => true,
                'errors' => $validator->messages()
            ];
        }
        try {
            $piutangdetail = new PiutangDetail();
            
            $piutangdetail->piutang_id = $request->piutang_id;
            $piutangdetail->nobukti = $request->nobukti;
            $piutangdetail->nominal = $request->nominal;
            $piutangdetail->keterangan = $request->keterangan;
            $piutangdetail->invoice_nobukti = $request->invoice_nobukti;
            $piutangdetail->modifiedby = auth('api')->user()->name;
            
            $piutangdetail->save();
           
            DB::commit();
            if ($validator->passes()) {
                return [
                    'error' => false,
                    'id' => $piutangdetail->id,
                    'tabel' => $piutangdetail->getTable(),
                ];
            }
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }        
    }

   
}
