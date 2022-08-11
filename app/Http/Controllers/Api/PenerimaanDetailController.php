<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanDetail;
use App\Http\Requests\StorePenerimaanDetailRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PenerimaanDetailController extends Controller
{
    /**
     * @ClassName
     */
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'penerimaan_id' => $request->penerimaan_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        try {
            $query = PenerimaanDetail::from('penerimaandetail as detail');

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['penerimaan_id'])) {
                $query->where('detail.penerimaan_id', $params['penerimaan_id']);
            }

            if ($params['withHeader']) {
                $query->join('penerimaan', 'penerimaan.id', 'detail.penerimaan_id');
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('penerimaan_id', $params['whereIn']);
            }

            if ($params['forReport']) {
                $query->select(
                    'detail.nowarkat',
                    'detail.tgljatuhtempo',
                    'detail.nominal',
                    'detail.coadebet',
                    'detail.coakredit',
                    'detail.keterangan',

                    'bank.namabank as bank_id', //
                    'pelanggan.namapelanggan as pelanggan_id', //

                    'detail.invoice_nobukti',
                    
                    'bankpelanggan.namabank as bankpelanggan_id', ///
                    
                    'detail.jenisbiaya',
                    'detail.penerimaanpiutang_nobukti',
                    
                    'detail.bulanbeban',
                    
                    )
                    ->leftJoin('bank', 'bank.id', '=', 'detail.bank_id')
                    ->leftJoin('pelanggan', 'pelanggan.id', '=', 'detail.pelanggan_id')
                    ->leftJoin('bankpelanggan', 'bankpelanggan.id', '=', 'detail.bankpelanggan_id');
                    // ->join('pelunasanpiutangheader', 'pelunasanpiutangheader.id', '=', 'detail.pelunasanpiutangheader_id');
                    
                    $penerimaanDetail = $query->get();
                } else {
             //   DB::enableQueryLog();
                $query->select(
                    'detail.nowarkat',
                    'detail.tgljatuhtempo',
                    'detail.nominal',
                    'detail.coadebet',
                    'detail.coakredit',
                    'detail.keterangan',

                    'bank.namabank as bank_id', //
                    'pelanggan.namapelanggan as pelanggan_id', //

                    'detail.invoice_nobukti',

                    'bankpelanggan.namabank as bankpelanggan_id', ///

                    'detail.jenisbiaya',
                    'detail.penerimaanpiutang_nobukti',

                    'detail.bulanbeban',

                )
                    ->leftJoin('bank', 'bank.id', '=', 'detail.bank_id')
                    ->leftJoin('pelanggan', 'pelanggan.id', '=', 'detail.pelanggan_id')
                    ->leftJoin('bankpelanggan', 'bankpelanggan.id', '=', 'detail.bankpelanggan_id');
                $penerimaanDetail = $query->get();
            }

            return response([
                'data' => $penerimaanDetail
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }

    public function store(StorePenerimaanDetailRequest $request)
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'nominal' => 'required',
        ], [
            'nominal.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
        ], [
            'nominal' => 'Nominal',
        ]);
        if (!$validator->passes()) {
            return [
                'error' => true,
                'errors' => $validator->messages()
            ];
        }

        try {
            $penerimaanDetail = new PenerimaanDetail();

            $penerimaanDetail->penerimaan_id = $request->penerimaan_id;
            $penerimaanDetail->nobukti = $request->nobukti;
            $penerimaanDetail->nowarkat = $request->nowarkat;
            $penerimaanDetail->tgljatuhtempo = $request->tgljatuhtempo;
            $penerimaanDetail->nominal = $request->nominal;
            $penerimaanDetail->coadebet = $request->coadebet;
            $penerimaanDetail->coakredit = $request->coakredit;
            $penerimaanDetail->keterangan = $request->keterangan ?? '';
            $penerimaanDetail->bank_id = $request->bank_id;
            $penerimaanDetail->bankpelanggan_id = $request->bankpelanggan_id;
            $penerimaanDetail->jenisbiaya = $request->jenisbiaya;
            $penerimaanDetail->modifiedby = $request->modifiedby;
            
            $penerimaanDetail->save();
            
           
            DB::commit();
            if ($validator->passes()) {
                return [
                    'error' => false,
                    'id' => $penerimaanDetail->id,
                    'tabel' => $penerimaanDetail->getTable(),
                ];
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }        
    }
}
