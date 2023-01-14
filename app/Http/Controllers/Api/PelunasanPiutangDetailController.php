<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PelunasanPiutangDetail;
use App\Http\Requests\StorePelunasanPiutangDetailRequest;
use App\Http\Requests\UpdatePelunasanPiutangDetailRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PelunasanPiutangDetailController extends Controller
{

    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'pelunasanpiutang_id' => $request->pelunasanpiutang_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];
        try {
            $query = PelunasanPiutangDetail::from(DB::raw("pelunasanpiutangdetail as detail with (readuncommitted)"));

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['pelunasanpiutang_id'])) {
                $query->where('detail.pelunasanpiutang_id', $params['pelunasanpiutang_id']);
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('pelunasanpiutang_id', $params['whereIn']);
            }
            if ($params['forReport']) {
                $query->select(
                    'header.nobukti',
                    'header.tglbukti',
                    'header.keterangan as keterangan_header',
                    'bank.namabank as bank',
                    'agen.namaagen as agen',
                    'cabang.namacabang as cabang',
                    'detail.nominal',
                    'detail.keterangan as keterangan_detail',
                    'detail.nominal',
                    'detail.piutang_nobukti',
                    'detail.tglcair',
                    'detail.tgljt',
                    'agen_detail.namaagen as agen_detail',
                    'pelanggan.namapelanggan as pelanggan',
                )
                    ->leftJoin(DB::raw("pelunasanpiutangheader as header with (readuncommitted)"), 'header.id', 'detail.pelunasanpiutang_id')
                    ->leftJoin(DB::raw("bank with (readuncommitted)"), 'header.bank_id', 'bank.id')
                    ->leftJoin(DB::raw("cabang with (readuncommitted)"), 'header.cabang_id', 'cabang.id')
                    ->leftJoin(DB::raw("agen with (readuncommitted)"), 'header.agen_id', 'agen.id')
                    ->leftJoin(DB::raw("agen as agen_detail with (readuncommitted)"), 'detail.agen_id', 'agen_detail.id')
                    ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'detail.pelanggan_id', 'pelanggan.id');

                $piutangDetail = $query->get();
            } else {
                $query->select(
                    'detail.nobukti',
                    'detail.nominal',
                    'detail.keterangan',
                    'detail.piutang_nobukti',
                    'detail.nominallebihbayar',
                    'detail.potongan',
                    'detail.keteranganpotongan',
                    'detail.coapotongan',
                    'detail.invoice_nobukti'
                );
                $piutangDetail = $query->get();
            }

            $idUser = auth('api')->user()->id;
            $getuser = User::select('name', 'cabang.namacabang as cabang_id')
                ->where('user.id', $idUser)->join('cabang', 'user.cabang_id', 'cabang.id')->first();


            return response([
                'data' => $piutangDetail,
                'user' => $getuser,
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }


    public function store(StorePelunasanPiutangDetailRequest $request)
    {
        DB::beginTransaction();

        try {
            $pelunasanpiutangdetail = new PelunasanPiutangDetail();

            $pelunasanpiutangdetail->pelunasanpiutang_id = $request->pelunasanpiutang_id;
            $pelunasanpiutangdetail->nobukti = $request->nobukti;
            $pelunasanpiutangdetail->nominal = $request->nominal;
            $pelunasanpiutangdetail->piutang_nobukti = $request->piutang_nobukti;
            $pelunasanpiutangdetail->keterangan = $request->keterangan;
            $pelunasanpiutangdetail->potongan = $request->potongan;
            $pelunasanpiutangdetail->coapotongan = $request->coapotongan;
            $pelunasanpiutangdetail->invoice_nobukti = $request->invoice_nobukti;
            $pelunasanpiutangdetail->keteranganpotongan = $request->keteranganpotongan;
            $pelunasanpiutangdetail->nominallebihbayar = $request->nominallebihbayar;
            $pelunasanpiutangdetail->coalebihbayar = $request->coalebihbayar;

            $pelunasanpiutangdetail->modifiedby = auth('api')->user()->name;
            $pelunasanpiutangdetail->save();
            DB::commit();
            return [
                'error' => false,
                'detail' => $pelunasanpiutangdetail,
                'id' => $pelunasanpiutangdetail->id,
                'tabel' => $pelunasanpiutangdetail->getTable(),
            ];
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }
    }
}
