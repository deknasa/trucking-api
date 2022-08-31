<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanTruckingDetail;
use App\Http\Requests\StorePenerimaanTruckingDetailRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PenerimaanTruckingDetailController extends Controller
{
    /**
     * @ClassName
     */
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'penerimaantruckingheader_id' => $request->penerimaantrucking_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];
        try {
            $query = PenerimaanTruckingDetail::from('penerimaantruckingdetail as detail');
            
            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }
            
            if (isset($params['penerimaantruckingheader_id'])) {
                $query->where('detail.penerimaantruckingheader_id', $params['penerimaantruckingheader_id']);
            }

            if ($params['withHeader']) {
                $query->join('penerimaantrucking', 'penerimaantrucking.id', 'detail.penerimaantruckingheader_id');
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('penerimaantruckingheader_id', $params['whereIn']);
            }

            if ($params['forReport']) {
                $query->select(
                    'detail.nobukti',
                    'detail.nominal',
                    'supir.namasupir as supir_id',
                )->leftJoin('supir', 'supir.id', '=', 'detail.supir_id');
              
                $penerimaantruckingDetail = $query->get();
            } else {
                //   DB::enableQueryLog();
                $query->select(
                    'detail.nobukti',
                    'detail.nominal',
                    'supir.namasupir as supir_id',
                )->leftJoin('supir', 'supir.id', '=', 'detail.supir_id');
            
                $penerimaantruckingDetail = $query->get();
            }

            return response([
                'data' => $penerimaantruckingDetail
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }

    public function store(StorePenerimaanTruckingDetailRequest $request)
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
            $penerimaanDetail = new PenerimaanTruckingDetail();

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
            $penerimaanDetail->pelanggan_id = $request->pelanggan_id;
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
