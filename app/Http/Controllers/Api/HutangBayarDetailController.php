<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\HutangBayarDetail;
use App\Http\Requests\StoreHutangBayarDetailRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class HutangBayarDetailController extends Controller
{
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'hutangbayar_id' => $request->hutangbayar_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
            'offset' => $request->offset ?? (($request->page - 1) * $request->limit),
            'limit' => $request->limit ?? 10,
        ];

        $totalRows = 0;
        try {
            $query = HutangBayarDetail::from(DB::raw("hutangbayardetail as detail with (readuncommitted)"));

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['hutangbayar_id'])) {
                $query->where('detail.hutangbayar_id', $params['hutangbayar_id']);
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('hutangbayar_id', $params['whereIn']);
            }
            if ($params['forReport']) {
                $query->select(
                    'header.nobukti',
                    'header.tglbukti',
                    'header.keterangan as keteranganheader',
                    'header.pengeluaran_nobukti',
                    'header.coa',
                    'bank.namabank as bank',
                    'supplier.namasupplier as supplier',
                    'pelanggan.namapelanggan as pelanggan',
                    'detail.nominal',
                    'detail.keterangan',
                    'header.tglcair',
                    'detail.potongan',
                    'detail.hutang_nobukti',
                    'alatbayar.namaalatbayar as alatbayar_id',

                )
                    ->leftJoin(DB::raw("hutangbayarheader as header with (readuncommitted)"), 'header.id', 'detail.hutangbayar_id')
                    ->leftJoin(DB::raw("bank with (readuncommitted)"), 'header.bank_id', 'bank.id')
                    ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'header.supplier_id', 'supplier.id')
                    ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'header.pelanggan_id', 'pelanggan.id')
                    ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'header.alatbayar_id', 'alatbayar.id');


                $hutangbayarDetail = $query->get();
            } else {
                $query->select(
                    'detail.nobukti',
                    'detail.nominal',
                    'detail.keterangan',
                    'detail.potongan',
                    'detail.hutang_nobukti'
                )
                    ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'detail.alatbayar_id', 'alatbayar.id');
                    $totalRows =  $query->count();
                    $query->skip($params['offset'])->take($params['limit']);
                $hutangbayarDetail = $query->get();
            }
            return response([
                'data' => $hutangbayarDetail,
                'total' => $params['limit'] > 0 ? ceil( $totalRows / $params['limit']) : 1,
                "records" =>$totalRows ?? 0,
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }

    public function store(StoreHutangBayarDetailRequest $request)
    {
        DB::beginTransaction();

        try {
            $hutangbayarDetail = new HutangBayarDetail();
            $hutangbayarDetail->hutangbayar_id = $request->hutangbayar_id;
            $hutangbayarDetail->nobukti = $request->nobukti;
            $hutangbayarDetail->nominal = $request->nominal;
            $hutangbayarDetail->hutang_nobukti = $request->hutang_nobukti;
            $hutangbayarDetail->cicilan = $request->cicilan;
            $hutangbayarDetail->potongan = $request->potongan;
            $hutangbayarDetail->keterangan = $request->keterangan;
            $hutangbayarDetail->modifiedby = auth('api')->user()->name;
            $hutangbayarDetail->save();

            DB::commit();
            return [
                'error' => false,
                'detail' => $hutangbayarDetail,
                'id' => $hutangbayarDetail->id,
                'tabel' => $hutangbayarDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }
    }
}
