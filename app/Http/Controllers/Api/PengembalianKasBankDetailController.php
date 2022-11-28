<?php

namespace App\Http\Controllers\Api;

use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use App\Models\PengembalianKasBankDetail;
use App\Http\Requests\StorePengembalianKasBankDetailRequest;
use App\Http\Requests\UpdatePengembalianKasBankDetailRequest;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PengembalianKasBankDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'pengembaliankasbank_id' => $request->pengembaliankasbank_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];
        try {
            $query = PengembalianKasBankDetail::from('pengembaliankasbankdetail as detail');

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['pengembaliankasbank_id'])) {
                $query->where('detail.pengembaliankasbank_id', $params['pengembaliankasbank_id']);
            }

            if ($params['withHeader']) {
                $query->join('pengeluaranheader', 'pengeluaranheader.id', 'detail.pengembaliankasbank_id');
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('pengembaliankasbank_id', $params['whereIn']);
            }

            if ($params['forReport']) {
                $query->select(
                    'header.nobukti',
                    'header.tglbukti',
                    'header.dibayarke',
                    'header.keterangan as keteranganheader',
                    'header.transferkeac',
                    'header.transferkean',
                    'header.transferkebank',
                    
                    'bank.namabank as bank',
                    'detail.nowarkat',
                    'detail.tgljatuhtempo',
                    'detail.nominal',
                    'detail.keterangan',
                    'detail.bulanbeban',
                    'detail.coadebet',
                    'detail.coakredit',
                    'alatbayar.namaalatbayar as alatbayar_id'

                )
                    ->join('pengeluaranheader as header','header.id','detail.pengembaliankasbank_id')
                    ->leftJoin('bank', 'bank.id', '=', 'header.bank_id')
                    
                    ->leftJoin('alatbayar', 'alatbayar.id', '=', 'detail.alatbayar_id');

                    $pengeluaranDetail = $query->get();
            } else {
                $query->select(
                    'detail.pengembaliankasbank_id',
                    'detail.nobukti',
                    'detail.nowarkat',
                    'detail.tgljatuhtempo',
                    'detail.nominal',
                    'detail.keterangan',
                    'detail.bulanbeban',
                    'detail.coadebet',
                    'detail.coakredit',
                    'alatbayar.namaalatbayar as alatbayar_id',

                )
                    ->leftJoin('alatbayar', 'alatbayar.id', '=', 'detail.alatbayar_id');

                $pengeluaranDetail = $query->get();
            }
            
           
            return response([
                'data' => $pengeluaranDetail,
                
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function store(StorePengembalianKasBankDetailRequest $request)
    { 
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'alatbayar_id' => 'required',
            'tgljatuhtempo' => 'required',
            'nominal' => 'required',
            'keterangan' => 'required',
         ], [
             'alatbayar_id.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
             'tgljatuhtempo.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
             'nominal.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
             'keterangan.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
              ], [
             'alatbayar_id' => 'coa detail',
             'tgljatuhtempo' => 'keterangandetail Detail',
             'nominal' => 'coa detail',
             'keterangan' => 'keterangandetail Detail',
            ],
         );         
         if (!$validator->passes()) {
            return [
                'error' => true,
                'errors' => $validator->messages()
            ];
        }

        try {
            $pengembalianKasBankDetail = new PengembalianKasBankDetail();
            $entriLuar = $request->entriluar ?? 0;

            $pengembalianKasBankDetail->pengembaliankasbank_id = $request->pengembaliankasbank_id;
            $pengembalianKasBankDetail->nobukti = $request->nobukti;
            $pengembalianKasBankDetail->alatbayar_id = $request->alatbayar_id ?? '';
            $pengembalianKasBankDetail->nowarkat = $request->nowarkat ?? '';
            $pengembalianKasBankDetail->tgljatuhtempo = $request->tgljatuhtempo ?? '';
            $pengembalianKasBankDetail->nominal = $request->nominal ?? '';
            $pengembalianKasBankDetail->coadebet = $request->coadebet ?? '';
            $pengembalianKasBankDetail->coakredit = $request->coakredit ?? '';
            $pengembalianKasBankDetail->keterangan = $request->keterangan ?? '';
            $pengembalianKasBankDetail->bulanbeban = $request->bulanbeban ?? '';
            $pengembalianKasBankDetail->modifiedby = $request->modifiedby;
            

            if ($pengembalianKasBankDetail->save()) {
                DB::commit();
                return [
                    'error' => false,
                    'id' => $pengembalianKasBankDetail->id,
                    'tabel' => $pengembalianKasBankDetail->getTable(),
                ];
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PengembalianKasBankDetail  $pengembalianKasBankDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(PengembalianKasBankDetail $pengembalianKasBankDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePengembalianKasBankDetailRequest  $request
     * @param  \App\Models\PengembalianKasBankDetail  $pengembalianKasBankDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePengembalianKasBankDetailRequest $request, PengembalianKasBankDetail $pengembalianKasBankDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PengembalianKasBankDetail  $pengembalianKasBankDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(PengembalianKasBankDetail $pengembalianKasBankDetail)
    {
        //
    }
}
