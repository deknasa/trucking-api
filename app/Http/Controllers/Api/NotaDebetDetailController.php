<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Models\NotaDebetDetail;
use App\Http\Requests\StoreNotaDebetDetailRequest;
use App\Http\Requests\UpdateNotaDebetDetailRequest;

class NotaDebetDetailController extends Controller
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
            'notadebet_id' => $request->notadebet_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];
        try {
            $query = NotaDebetDetail::from('notadebetdetail as detail');

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['notadebet_id'])) {
                $query->where('detail.notadebet_id', $params['notadebet_id']);
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('notadebet_id', $params['whereIn']);
            }

            if ($params['forReport']) {
                $query->select(
                    "detail.id",
                    "detail.notadebet_id",
                    "detail.nobukti",
                    "detail.tglterima",
                    "detail.invoice_nobukti",
                    "detail.nominal",
                    "detail.nominalbayar",
                    "detail.lebihbayar",
                    "detail.keterangan",
                    "detail.coalebihbayar",
                    "detail.modifiedby"
                );

                $notadebet = $query->get();
            } else {
                $query->select(
                    "detail.id",
                    "detail.notadebet_id",
                    "detail.nobukti",
                    "detail.tglterima",
                    "detail.invoice_nobukti",
                    "detail.nominal",
                    "detail.nominalbayar",
                    "detail.lebihbayar",
                    "detail.keterangan",
                    "akunpusat.keterangancoa as coalebihbayar",
                    "detail.modifiedby"
                )
                // ->leftJoin('pengeluaranstok','pengeluaranstokheader.pengeluaranstok_id','pengeluaranstok.id')

                ->leftJoin('notadebetheader', 'detail.notadebet_id', 'notadebetheader.id')
                ->leftJoin('invoiceheader', 'detail.invoice_nobukti', 'invoiceheader.nobukti')
                ->leftJoin('akunpusat', 'detail.coalebihbayar', 'akunpusat.coa');
                $notadebet = $query->get();
            }

            return response([
                'data' => $notadebet
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }
    public function store(StoreNotaDebetDetailRequest $request)
    {
        DB::beginTransaction();
        
        try {

            $notaDebetDetail = new NotaDebetDetail();
            $notaDebetDetail->notadebet_id = $request->notadebet_id;
            $notaDebetDetail->nobukti = $request->nobukti;
            $notaDebetDetail->tglterima = $request->tglterima;
            $notaDebetDetail->invoice_nobukti = $request->invoice_nobukti;
            $notaDebetDetail->nominal = $request->nominal;
            $notaDebetDetail->nominalbayar = $request->nominalbayar;
            $notaDebetDetail->lebihbayar = $request->lebihbayar;
            $notaDebetDetail->keterangan = $request->keterangandetail;
            $notaDebetDetail->coalebihbayar = $request->coalebihbayar;
            $notaDebetDetail->modifiedby = $request->modifiedby;
            
            DB::commit();
            if ($notaDebetDetail->save()) {
                return [
                    'error' => false,
                    'id' => $notaDebetDetail->id,
                    'data' => $notaDebetDetail,
                    'tabel' => $notaDebetDetail->getTable(),
                ];
            }
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }

        
    }
}
