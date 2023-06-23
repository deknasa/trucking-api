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
     * @ClassName 
     */
    public function index(Request $request)
    {
        $notaDebetDetail = new NotaDebetDetail();

        return response([
            'data' => $notaDebetDetail->get(),
            'attributes' => [
                'totalRows' => $notaDebetDetail->totalRows,
                'totalPages' => $notaDebetDetail->totalPages,
                'totalNominal' => $notaDebetDetail->totalNominal,
                'totalNominalBayar' => $notaDebetDetail->totalNominalBayar,
                'totalLebihBayar' => $notaDebetDetail->totalLebihBayar,
            ]
        ]);
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
