<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanDetail;
use App\Http\Requests\StorePenerimaanDetailRequest;
use App\Models\User;
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
                    'header.nobukti',
                    'header.tglbukti',
                    'header.tgllunas',
                    'header.keterangan',
                    'bank.namabank as bank',
                    'pelanggan.namapelanggan as pelanggan',
                    'detail.nowarkat',
                    'detail.tgljatuhtempo',
                    'detail.nominal',
                    'detail.keterangan as keterangan_detail',
                    'bd.namabank as bank_detail',
                    'pd.namapelanggan as pelanggan_detail',
                    'detail.invoice_nobukti',
                    'bpd.namabank as bankpelanggan_detail',
                    'detail.jenisbiaya',
                    'detail.bulanbeban',
                    'detail.coakredit',
                    'detail.coadebet',

                )
                    ->leftJoin('penerimaanheader as header', 'header.id', 'detail.penerimaan_id')
                    ->leftJoin('bank', 'bank.id', 'header.bank_id')
                    ->leftJoin('pelanggan', 'pelanggan.id', 'header.pelanggan_id')
                    ->leftJoin('bank as bd', 'bd.id', '=', 'detail.bank_id')
                    ->leftJoin('pelanggan as pd', 'pd.id', '=', 'detail.pelanggan_id')
                    ->leftJoin('bankpelanggan as bpd', 'bpd.id', '=', 'detail.bankpelanggan_id');
                $penerimaanDetail = $query->get();
            } else {
                $query->select(
                    'detail.nobukti',
                    'detail.nowarkat',
                    'detail.tgljatuhtempo',
                    'detail.nominal',
                    'detail.keterangan',
                    'bank.namabank as bank_id',
                    'pelanggan.namapelanggan as pelanggan_id', //
                    'detail.invoice_nobukti',
                    'bankpelanggan.namabank as bankpelanggan_id', ///
                    'detail.jenisbiaya',

                    'detail.pelunasanpiutang_nobukti',
                    'detail.bulanbeban',
                    'detail.coakredit',
                    'detail.coadebet',

                )
                    ->leftJoin('bank', 'bank.id', '=', 'detail.bank_id')
                    ->leftJoin('pelanggan', 'pelanggan.id', '=', 'detail.pelanggan_id')
                    ->leftJoin('bankpelanggan', 'bankpelanggan.id', '=', 'detail.bankpelanggan_id');


                $penerimaanDetail = $query->get();
            }

            return response([
                'data' => $penerimaanDetail,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function store(StorePenerimaanDetailRequest $request)
    {
        DB::beginTransaction();

        try {
            
            $penerimaanDetail = new PenerimaanDetail();

            $penerimaanDetail->penerimaan_id = $request->penerimaan_id;
            $penerimaanDetail->nobukti = $request->nobukti;
            $penerimaanDetail->nowarkat = $request->nowarkat;
            $penerimaanDetail->tgljatuhtempo = $request->tgljatuhtempo;
            $penerimaanDetail->nominal = $request->nominal;
            $penerimaanDetail->coadebet = $request->coadebet;
            $penerimaanDetail->coakredit = $request->coakredit;
            $penerimaanDetail->keterangan = $request->keterangan;
            $penerimaanDetail->bank_id = $request->bank_id;
            $penerimaanDetail->pelanggan_id = $request->pelanggan_id;
            $penerimaanDetail->invoice_nobukti = $request->invoice_nobukti;
            $penerimaanDetail->bankpelanggan_id = $request->bankpelanggan_id;
            $penerimaanDetail->jenisbiaya = $request->jenisbiaya;
            $penerimaanDetail->pelunasanpiutang_nobukti = $request->pelunasanpiutang_nobukti;
            $penerimaanDetail->bulanbeban = $request->bulanbeban;
            $penerimaanDetail->modifiedby = auth('api')->user()->name;
            
            $penerimaanDetail->save();
            

            DB::commit();

            return [
                'error' => false,
                'detail' => $penerimaanDetail,
                'id' => $penerimaanDetail->id,
                'tabel' => $penerimaanDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }
}
