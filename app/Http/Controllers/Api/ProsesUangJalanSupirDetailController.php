<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProsesUangJalanSupirDetail;
use App\Http\Requests\StoreProsesUangJalanSupirDetailRequest;
use App\Http\Requests\UpdateProsesUangJalanSupirDetailRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProsesUangJalanSupirDetailController extends Controller
{
    /**
     * @ClassName
     */
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'prosesuangjalansupir_id' => $request->prosesuangjalansupir_id,
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
            $query = ProsesUangJalanSupirDetail::from(DB::raw("prosesuangjalansupirdetail as detail with (readuncommitted)"));

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['prosesuangjalansupir_id'])) {
                $query->where('detail.prosesuangjalansupir_id', $params['prosesuangjalansupir_id']);
            }

            if ($params['withHeader']) {
                $query->join('penerimaan', 'penerimaan.id', 'detail.prosesuangjalansupir_id');
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('prosesuangjalansupir_id', $params['whereIn']);
            }

            if ($params['forReport']) {
                $query->select(
                    'header.nobukti',
                    'header.tglbukti',
                    'header.tgllunas',
                    'bank.namabank as bank',
                    'detail.nowarkat',
                    'detail.tgljatuhtempo',
                    'detail.nominal',
                    'detail.keterangan as keterangan_detail',
                    'bd.namabank as bank_detail',
                    'detail.invoice_nobukti',
                    'bpd.namabank as bankpelanggan_detail',
                    DB::raw("(case when year(isnull(detail.bulanbeban,'1900/1/1'))=1900 then null else detail.bulanbeban end) as bulanbeban"),
                    'detail.coakredit',
                    'detail.coadebet',

                )
                    ->leftJoin(DB::raw("penerimaanheader as header with (readuncommitted)"), 'header.id', 'detail.prosesuangjalansupir_id')
                    ->leftJoin(DB::raw("bank with (readuncommitted)"), 'bank.id', 'header.bank_id')
                    ->leftJoin(DB::raw("bank as bd with (readuncommitted)"), 'bd.id', '=', 'detail.bank_id')
                    ->leftJoin(DB::raw("bankpelanggan as bpd with (readuncommitted)"), 'bpd.id', '=', 'detail.bankpelanggan_id');
                $penerimaanDetail = $query->get();
            } else {
                $query->select(
                    'detail.nobukti',
                    'penerimaanbank.namabank as penerimaantrucking_bank_id',
                    DB::raw("(case when year(isnull(detail.penerimaantrucking_tglbukti,'1900/1/1'))=1900 then null else detail.penerimaantrucking_tglbukti end) as penerimaantrucking_tglbukti"),
                    'detail.penerimaantrucking_nobukti',
                    'pengeluaranbank.namabank as pengeluarantrucking_bank_id',
                    DB::raw("(case when year(isnull(detail.pengeluarantrucking_tglbukti,'1900/1/1'))=1900 then null else detail.pengeluarantrucking_tglbukti end) as pengeluarantrucking_tglbukti"),
                    'detail.pengeluarantrucking_nobukti',
                    'pengembalianbank.namabank as pengembaliankasgantung_bank_id',
                    DB::raw("(case when year(isnull(detail.pengembaliankasgantung_tglbukti,'1900/1/1'))=1900 then null else detail.pengembaliankasgantung_tglbukti end) as pengembaliankasgantung_tglbukti"),
                    'detail.pengembaliankasgantung_nobukti',
                    'detail.nominal',
                    'detail.keterangan',
                    'parameter.text as statusprosesuangjalan',

                )
                    ->leftJoin(DB::raw("bank as penerimaanbank with (readuncommitted)"), 'penerimaanbank.id', '=', 'detail.penerimaantrucking_bank_id')
                    ->leftJoin(DB::raw("bank as pengeluaranbank with (readuncommitted)"), 'pengeluaranbank.id', '=', 'detail.pengeluarantrucking_bank_id')
                    ->leftJoin(DB::raw("bank as pengembalianbank with (readuncommitted)"), 'pengembalianbank.id', '=', 'detail.pengembaliankasgantung_bank_id')
                    ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'parameter.id', '=', 'detail.statusprosesuangjalan');

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

    public function store(StoreProsesUangJalanSupirDetailRequest $request)
    {
        DB::beginTransaction();

        try {
            
            $prosesUangJalan = new ProsesUangJalanSupirDetail();

            $prosesUangJalan->prosesuangjalansupir_id = $request->prosesuangjalansupir_id;
            $prosesUangJalan->nobukti = $request->nobukti;
            $prosesUangJalan->penerimaantrucking_bank_id = $request->penerimaantrucking_bank_id ?? '';
            $prosesUangJalan->penerimaantrucking_tglbukti = $request->penerimaantrucking_tglbukti ?? '';
            $prosesUangJalan->penerimaantrucking_nobukti = $request->penerimaantrucking_nobukti ?? '';
            $prosesUangJalan->pengeluarantrucking_bank_id = $request->pengeluarantrucking_bank_id ?? '';
            $prosesUangJalan->pengeluarantrucking_tglbukti = $request->pengeluarantrucking_tglbukti ?? '';
            $prosesUangJalan->pengeluarantrucking_nobukti = $request->pengeluarantrucking_nobukti ?? '';
            $prosesUangJalan->pengembaliankasgantung_bank_id = $request->pengembaliankasgantung_bank_id ?? '';
            $prosesUangJalan->pengembaliankasgantung_tglbukti = $request->pengembaliankasgantung_tglbukti ?? '';
            $prosesUangJalan->pengembaliankasgantung_nobukti = $request->pengembaliankasgantung_nobukti ?? '';
            $prosesUangJalan->statusprosesuangjalan = $request->statusprosesuangjalan;
            $prosesUangJalan->nominal = $request->nominal;
            $prosesUangJalan->keterangan = $request->keterangan;
            $prosesUangJalan->modifiedby = auth('api')->user()->name;
            
            $prosesUangJalan->save();
            DB::commit();

            return [
                'error' => false,
                'detail' => $prosesUangJalan,
                'id' => $prosesUangJalan->id,
                'tabel' => $prosesUangJalan->getTable(),
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
