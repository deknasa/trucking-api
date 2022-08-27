<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengeluaranDetail;
use App\Http\Requests\StorePengeluaranDetailRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PengeluaranDetailController extends Controller
{
    /**
     * @ClassName
     */
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'pengeluaran_id' => $request->pengeluaran_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];
        try {
            $query = PengeluaranDetail::from('pengeluarandetail as detail');

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['pengeluaran_id'])) {
                $query->where('detail.pengeluaran_id', $params['pengeluaran_id']);
            }

            if ($params['withHeader']) {
                $query->join('pengeluaranheader', 'pengeluaranheader.id', 'detail.pengeluaran_id');
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('pengeluaran_id', $params['whereIn']);
            }

            if ($params['forReport']) {
                $query->select(
                    'detail.alatbayar_id',
                    'detail.nobukti',
                    'detail.nowarkat',
                    'detail.tgljatuhtempo',
                    'detail.nominal',
                    'detail.keterangan',
                    'detail.bulanbeban',
                    'alatbayar.namaalatbayar as alatbayar_id',
                    'bank.namabank as bank_id',
                    'akunpusat.keterangancoa as coadebet',
                    'bank.coa as coakredit',

                )
                    ->leftJoin('alatbayar', 'alatbayar.id', '=', 'detail.alatbayar_id')
                    ->leftJoin('akunpusat', 'pengeluarandetail.coadebet', '=', 'akunpusat.coa')
                    ->leftjoin('bank', 'pengeluarandetail.coakredit', '=', 'bank.namabank')
                    ->leftJoin('bank', 'bank.id', '=', 'detail.bank_id');
                $pengeluaranDetail = $query->get();
            } else {
                $query->select(
                    'detail.pengeluaran_id',
                    'detail.nobukti',
                    'detail.nowarkat',
                    'detail.tgljatuhtempo',
                    'detail.nominal',
                    'detail.keterangan',
                    'detail.bulanbeban',
                    'alatbayar.namaalatbayar as alatbayar_id',
                    'coakredit.keterangancoa as coakredit',
                    'akunpusat.keterangancoa as coadebet',

                )
                    ->leftJoin('alatbayar', 'alatbayar.id', '=', 'detail.alatbayar_id')
                    ->leftJoin('akunpusat', 'detail.coadebet', '=', 'akunpusat.id')
                    ->leftJoin('akunpusat as coakredit', 'detail.coakredit', '=', 'coakredit.id');

                $pengeluaranDetail = $query->get();
                // dd{$pengeluaranDetail};
            }
            return response([
                'data' => $pengeluaranDetail
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }

    public function store(StorePengeluaranDetailRequest $request)
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
            $pengeluaranDetail = new PengeluaranDetail();

            $pengeluaranDetail->pengeluaran_id = $request->pengeluaran_id;
            $pengeluaranDetail->nobukti = $request->nobukti;
            $pengeluaranDetail->alatbayar_id = $request->alatbayar_id;
            $pengeluaranDetail->nowarkat = $request->nowarkat;
            $pengeluaranDetail->tgljatuhtempo = $request->tgljatuhtempo;
            $pengeluaranDetail->nominal = $request->nominal;
            $pengeluaranDetail->coadebet = $request->coadebet;
            $pengeluaranDetail->coakredit = $request->coakredit;
            $pengeluaranDetail->keterangan = $request->keterangan ?? '';
            $pengeluaranDetail->bulanbeban = $request->bulanbeban;
            $pengeluaranDetail->modifiedby = $request->modifiedby;

            $pengeluaranDetail->save();

            DB::commit();
            if ($validator->passes()) {
                return [
                    'error' => false,
                    'id' => $pengeluaranDetail->id,
                    'tabel' => $pengeluaranDetail->getTable(),
                ];
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }
}
