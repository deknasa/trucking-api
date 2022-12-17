<?php

namespace App\Http\Controllers\Api;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Controllers\Controller;

use App\Models\NotaKreditDetail;
use App\Http\Requests\StoreNotaKreditDetailRequest;
use App\Http\Requests\UpdateNotaKreditDetailRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class NotaKreditDetailController extends Controller
{
    
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'notakredit_id' => $request->notakredit_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];
        try {
            $query = NotaKreditDetail::from('notakreditdetail as detail');

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['notakredit_id'])) {
                $query->where('detail.notakredit_id', $params['notakredit_id']);
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('notakredit_id', $params['whereIn']);
            }

            if ($params['forReport']) {
                $query->select(
                    "header.nobukti as nobukti_header",
                    "header.tglbukti",
                    "header.keterangan as keterangan_header",
                    "detail.id",
                    "detail.notakredit_id",
                    "detail.nobukti",
                    "detail.tglterima",
                    "detail.invoice_nobukti",
                    "detail.nominal",
                    "detail.nominalbayar",
                    "detail.penyesuaian",
                    "detail.keterangan",
                    "detail.coaadjust",
                    "detail.modifiedby"
                )
                ->leftJoin('notakreditheader as header', 'header.id', 'detail.notakredit_id');

                $pengeluaranStokDetail = $query->get();
            } else {
                $query->select(
                    "detail.id",
                    "detail.notakredit_id",
                    "detail.nobukti",
                    "detail.tglterima",
                    "detail.invoice_nobukti",
                    "detail.nominal",
                    "detail.nominalbayar",
                    "detail.penyesuaian",
                    "detail.keterangan",
                    "detail.coaadjust",
                    "detail.modifiedby"
                )
                // ->leftJoin('pengeluaranstok','pengeluaranstokheader.pengeluaranstok_id','pengeluaranstok.id')

                ->leftJoin('notakreditheader', 'detail.notakredit_id', 'notakreditheader.id')
                ->leftJoin('invoiceheader', 'detail.invoice_nobukti', 'invoiceheader.nobukti')
                ->leftJoin('akunpusat', 'detail.coaadjust', 'akunpusat.coa');
                $pengeluaranStokDetail = $query->get();
            }

            return response([
                'data' => $pengeluaranStokDetail
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }

    
    public function store(StoreNotaKreditDetailRequest $request)
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'keterangandetail' => 'required',
         ], [
             'keterangandetail.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
              ], [
             'keterangandetail' => 'keterangan Detail',
            ],
         );         
         if (!$validator->passes()) {
            return [
                'error' => true,
                'errors' => $validator->messages()
            ];
        }
        try {

            $notaKreditDetail = new NotaKreditDetail();
            $notaKreditDetail->notakredit_id = $request->notakredit_id;
            $notaKreditDetail->nobukti = $request->nobukti;
            $notaKreditDetail->tglterima = $request->tglterima;
            $notaKreditDetail->invoice_nobukti = $request->invoice_nobukti;
            $notaKreditDetail->nominal = $request->nominal;
            $notaKreditDetail->nominalbayar = $request->nominalbayar;
            $notaKreditDetail->penyesuaian = $request->penyesuaian;
            $notaKreditDetail->keterangan = $request->keterangandetail;
            $notaKreditDetail->coaadjust = $request->coaadjust;
            $notaKreditDetail->modifiedby = $request->modifiedby;
            
            
            if ($notaKreditDetail->save()) {
                DB::commit();
                return [
                    'error' => false,
                    'id' => $notaKreditDetail->id,
                    'data' => $notaKreditDetail,
                    'tabel' => $notaKreditDetail->getTable(),
                ];
            }
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }

        
    }

    
}
