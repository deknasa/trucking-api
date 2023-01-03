<?php

namespace App\Http\Controllers\Api;

use App\Models\HutangHeader;
use App\Models\HutangDetail;
use App\Http\Requests\StoreHutangHeaderRequest;
use App\Http\Requests\StoreHutangDetailRequest;
use App\Http\Requests\UpdateHutangDetailRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\LogTrail;
use App\Models\AkunPusat;
use App\Models\Bank;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\Parameter;
use App\Models\User;
use Illuminate\Support\Facades\Validator;



class HutangDetailController extends Controller
{
    /**
     * @ClassName
     */
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'hutang_id' => $request->hutang_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];
        try {
            $query = HutangDetail::from(DB::raw("hutangdetail as detail with (readuncommitted)"));

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['hutang_id'])) {
                $query->where('detail.hutang_id', $params['hutang_id']);
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('hutang_id', $params['whereIn']);
            }
            if ($params['forReport']) {
                $query->select(
                    'header.nobukti',
                    'header.tglbukti',
                    'pelanggan.namapelanggan as pelanggan_id',
                    'header.coa',
                    'header.keterangan as keteranganheader',
                    'header.total as totalheader',
                    'supplier.namasupplier as supplier_id',
                    'detail.tgljatuhtempo',
                    'detail.total',
                    'detail.keterangan'
                )->leftJoin(DB::raw("hutangheader as header with (readuncommitted)"),'header.id','detail.hutang_id')
                ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'header.pelanggan_id', 'pelanggan.id')
                ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'detail.supplier_id', 'supplier.id');

                $hutangDetail = $query->get();
            } else {
                $query->select(
                    'detail.nobukti',
                    'detail.tgljatuhtempo',
                    'detail.total',
                    'detail.keterangan',

                    'supplier.namasupplier as supplier_id',

                )
                ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'detail.supplier_id', 'supplier.id');

                $hutangDetail = $query->get();
            }
            return response([
                'data' => $hutangDetail
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }

    public function store(StoreHutangDetailRequest $request)
    {
        DB::beginTransaction();

        
        try {
            $hutangdetail = new HutangDetail();
            $hutangdetail->hutang_id = $request->hutang_id;
            $hutangdetail->nobukti = $request->nobukti;
            $hutangdetail->supplier_id = $request->supplier_id;
            $hutangdetail->tgljatuhtempo = date('Y-m-d', strtotime($request->tgljatuhtempo));
            $hutangdetail->total = $request->total;
            $hutangdetail->cicilan = $request->cicilan;
            $hutangdetail->totalbayar = $request->totalbayar;
            $hutangdetail->keterangan = $request->keterangan;
            $hutangdetail->modifiedby = auth('api')->user()->name;
           
            $hutangdetail->save();
           
            DB::commit();
            return [
                'error' => false,
                'detail' => $hutangdetail,
                'id' => $hutangdetail->id,
                'tabel' => $hutangdetail->getTable(),
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }        
    }

}

