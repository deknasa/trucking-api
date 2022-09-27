<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PelunasanPiutangDetail;
use App\Http\Requests\StorePelunasanPiutangDetailRequest;
use App\Http\Requests\UpdatePelunasanPiutangDetailRequest;

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


    public function store(StorePelunasanPiutangDetailRequest $request)
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'pelanggan_id' => 'required',
        ], [
            'pelanggan_id.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            
        ]);
        // dd($request->all());

        if (!$validator->passes()) {
            return [
                'error' => true,
                'errors' => $validator->messages()
            ];
        }
        try {
            $pelunasanpiutangdetail = new PelunasanPiutangDetail();
            
            $pelunasanpiutangdetail->pelunasanpiutang_id = $request->pelunasanpiutang_id;
            $pelunasanpiutangdetail->nobukti = $request->nobukti;
            $pelunasanpiutangdetail->tgl = $request->tgl;
            $pelunasanpiutangdetail->pelanggan_id = $request->pelanggan_id;
            $pelunasanpiutangdetail->agen_id = $request->agen_id;
            $pelunasanpiutangdetail->nominal = $request->nominal;
            $pelunasanpiutangdetail->piutang_nobukti = $request->piutang_nobukti;
            $pelunasanpiutangdetail->cicilan = $request->cicilan;
            $pelunasanpiutangdetail->tglcair = $request->tglcair;
            $pelunasanpiutangdetail->keterangan = $request->keterangan;
            $pelunasanpiutangdetail->tgljt = $request->tgljt;
            $pelunasanpiutangdetail->penyesuaian = $request->penyesuaian;
            $pelunasanpiutangdetail->coapenyesuaian = $request->coapenyesuaian;
            $pelunasanpiutangdetail->invoice_bukti = $request->invoice_bukti;
            $pelunasanpiutangdetail->keteranganpenyesuaian = $request->keteranganpenyesuaian;
            $pelunasanpiutangdetail->nominallebihbayar = $request->nominallebihbayar;
            $pelunasanpiutangdetail->coalebihbayar = $request->coalebihbayar;
            
            $pelunasanpiutangdetail->modifiedby = auth('api')->user()->name;
            
            $pelunasanpiutangdetail->save();
           
            DB::commit();
            if ($validator->passes()) {
                return [
                    'error' => false,
                    'id' => $pelunasanpiutangdetail->id,
                    'tabel' => $pelunasanpiutangdetail->getTable(),
                ];
            }
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }        
    }

   
}
