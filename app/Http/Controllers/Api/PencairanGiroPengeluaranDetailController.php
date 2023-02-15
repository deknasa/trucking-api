<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PencairanGiroPengeluaranDetail;
use App\Http\Requests\StorePencairanGiroPengeluaranDetailRequest;
use App\Http\Requests\UpdatePencairanGiroPengeluaranDetailRequest;
use App\Models\PengeluaranDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PencairanGiroPengeluaranDetailController extends Controller
{
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'pengeluaran_id' => $request->pengeluaran_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'forExport' => $request->forExport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];
        try {
            $query = PengeluaranDetail::from(DB::raw("pengeluarandetail as detail with (readuncommitted)"));

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['pengeluaran_id'])) {
                $query->where('detail.pengeluaran_id', $params['pengeluaran_id']);
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('pengeluaran_id', $params['whereIn']);
            }
            if ($params['forReport']) {
                
            } else {
                $query->select(
                    'detail.nobukti','detail.nowarkat','detail.tgljatuhtempo', 'detail.nominal','detail.coadebet','detail.coakredit','detail.keterangan','detail.bulanbeban'
                );
                
                $pengeluaranDetail = $query->get();
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

   
    public function store(StorePencairanGiroPengeluaranDetailRequest $request)
    {
        DB::beginTransaction();

        try {
            $pencairanGiroDetail = new PencairanGiroPengeluaranDetail();
            
            $pencairanGiroDetail->pencairangiropengeluaran_id = $request->pencairangiropengeluaran_id;
            $pencairanGiroDetail->nobukti = $request->nobukti;
            $pencairanGiroDetail->alatbayar_id = $request->alatbayar_id;
            $pencairanGiroDetail->nowarkat = $request->nowarkat;
            $pencairanGiroDetail->tgljatuhtempo = $request->tgljatuhtempo;
            $pencairanGiroDetail->nominal = $request->nominal;
            $pencairanGiroDetail->coadebet = $request->coadebet;
            $pencairanGiroDetail->coakredit = $request->coakredit;
            $pencairanGiroDetail->keterangan = $request->keterangan;
            $pencairanGiroDetail->bulanbeban = $request->bulanbeban;
            $pencairanGiroDetail->modifiedby = auth('api')->user()->name;
            $pencairanGiroDetail->save();

            DB::commit();

            return [
                'error' => false,
                'detail' => $pencairanGiroDetail,
                'id' => $pencairanGiroDetail->id,
                'tabel' => $pencairanGiroDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            
            throw $th;
        }
    }

   
}
