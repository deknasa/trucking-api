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
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(Request $request)
    {
        $notaKreditDetail = new NotaKreditDetail();

        return response([
            'data' => $notaKreditDetail->get(),
            'attributes' => [
                'totalRows' => $notaKreditDetail->totalRows,
                'totalPages' => $notaKreditDetail->totalPages,
                'totalNominal' => $notaKreditDetail->totalNominal,
                'totalNominalBayar' => $notaKreditDetail->totalNominalBayar,
                'totalPenyesuaian' => $notaKreditDetail->totalPenyesuaian,
            ]
        ]);
    }


    public function store(StoreNotaKreditDetailRequest $request)
    {
        DB::beginTransaction();

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
        }
    }
}
