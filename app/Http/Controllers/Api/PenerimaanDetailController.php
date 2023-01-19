<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanDetail;
use App\Http\Requests\StorePenerimaanDetailRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PenerimaanDetailController extends Controller
{
    /**
     * @ClassName
     */
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'penerimaan_id' => $request->penerimaan_id,
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
            $query = PenerimaanDetail::from(DB::raw("penerimaandetail as detail with (readuncommitted)"));

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['penerimaan_id'])) {
                $query->where('detail.penerimaan_id', $params['penerimaan_id']);
            }

            if ($params['withHeader']) {
                $query->join('penerimaan', 'penerimaan.id', 'detail.penerimaan_id');
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('penerimaan_id', $params['whereIn']);
            }

            if ($params['forReport']) {
                $query->select(
                    'header.nobukti',
                    'header.tglbukti',
                    'header.tgllunas',
                    'bank.namabank as bank',
                    'pelanggan.namapelanggan as pelanggan',
                    'detail.nowarkat',
                    'detail.tgljatuhtempo',
                    'detail.nominal',
                    'detail.keterangan as keterangan_detail',
                    'bd.namabank as bank_detail',
                    'detail.invoice_nobukti',
                    'bpd.namabank as bankpelanggan_detail',
                    'detail.jenisbiaya',
                    'detail.bulanbeban',
                    'detail.coakredit',
                    'detail.coadebet',

                )
                    ->leftJoin(DB::raw("penerimaanheader as header with (readuncommitted)"), 'header.id', 'detail.penerimaan_id')
                    ->leftJoin(DB::raw("bank with (readuncommitted)"), 'bank.id', 'header.bank_id')
                    ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'pelanggan.id', 'header.pelanggan_id')
                    ->leftJoin(DB::raw("bank as bd with (readuncommitted)"), 'bd.id', '=', 'detail.bank_id')
                    ->leftJoin(DB::raw("bankpelanggan as bpd with (readuncommitted)"), 'bpd.id', '=', 'detail.bankpelanggan_id');
                $penerimaanDetail = $query->get();
            } else {
                $query->select(
                    'detail.nobukti',
                    'detail.nowarkat',
                    'detail.tgljatuhtempo',
                    'detail.nominal',
                    'detail.keterangan',
                    'bank.namabank as bank_id',
                    'detail.invoice_nobukti',
                    'bankpelanggan.namabank as bankpelanggan_id', ///
                    'detail.jenisbiaya',

                    'detail.pelunasanpiutang_nobukti',
                    'detail.bulanbeban',
                    'detail.coakredit',
                    'detail.coadebet',

                )
                    ->leftJoin(DB::raw("bank with (readuncommitted)"), 'bank.id', '=', 'detail.bank_id')
                    ->leftJoin(DB::raw("bankpelanggan with (readuncommitted)"), 'bankpelanggan.id', '=', 'detail.bankpelanggan_id');

                    $totalRows =  $query->count();
                    $query->skip($params['offset'])->take($params['limit']);
                $penerimaanDetail = $query->get();
            }

            return response([
                'data' => $penerimaanDetail,
                'attributes' => [
                    'totalRows' => $totalRows ?? 0,
                    'totalPages' => $params['limit'] > 0 ? ceil( $totalRows / $params['limit']) : 1
                ]
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function store(StorePenerimaanDetailRequest $request)
    {
        DB::beginTransaction();

        try {
            
            $penerimaanDetail = new PenerimaanDetail();

            $penerimaanDetail->penerimaan_id = $request->penerimaan_id;
            $penerimaanDetail->nobukti = $request->nobukti;
            $penerimaanDetail->nowarkat = $request->nowarkat;
            $penerimaanDetail->tgljatuhtempo = $request->tgljatuhtempo;
            $penerimaanDetail->nominal = $request->nominal;
            $penerimaanDetail->coadebet = $request->coadebet;
            $penerimaanDetail->coakredit = $request->coakredit;
            $penerimaanDetail->keterangan = $request->keterangan;
            $penerimaanDetail->bank_id = $request->bank_id;
            $penerimaanDetail->invoice_nobukti = $request->invoice_nobukti;
            $penerimaanDetail->bankpelanggan_id = $request->bankpelanggan_id;
            $penerimaanDetail->jenisbiaya = $request->jenisbiaya;
            $penerimaanDetail->pelunasanpiutang_nobukti = $request->pelunasanpiutang_nobukti;
            $penerimaanDetail->bulanbeban = $request->bulanbeban;
            $penerimaanDetail->modifiedby = auth('api')->user()->name;
            
            $penerimaanDetail->save();
            

            DB::commit();

            return [
                'error' => false,
                'detail' => $penerimaanDetail,
                'id' => $penerimaanDetail->id,
                'tabel' => $penerimaanDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }
}
