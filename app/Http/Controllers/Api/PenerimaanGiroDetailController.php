<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanGiroDetail;
use App\Http\Requests\StorePenerimaanGiroDetailRequest;
use App\Http\Requests\UpdatePenerimaanGiroDetailRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenerimaanGiroDetailController extends Controller
{
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'penerimaangiro_id' => $request->penerimaangiro_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];
        try {
            $query = PenerimaanGiroDetail::from(DB::raw("penerimaangirodetail as detail with (readuncommitted)"));

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['penerimaangiro_id'])) {
                $query->where('detail.penerimaangiro_id', $params['penerimaangiro_id']);
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('penerimaangiro_id', $params['whereIn']);
            }
            if ($params['forReport']) {
                $query->select(
                    'header.nobukti',
                    'header.tglbukti',
                    'ph.namapelanggan as pelangganheader',
                    'header.tgllunas',
                    'header.diterimadari',
                    'detail.nowarkat',
                    'detail.tgljatuhtempo',
                    'detail.coadebet',
                    'detail.coakredit',
                    'bank.namabank as bank_id',
                    'pelanggan.namapelanggan as pelanggan_id',
                    'bankpelanggan.namabank as bankpelanggan_id',
                    'detail.invoice_nobukti',
                    'detail.pelunasanpiutang_nobukti',
                    'detail.jenisbiaya',
                    'detail.bulanbeban',
                    'detail.keterangan',
                    'detail.nominal'
                ) 
                ->leftJoin(DB::raw("penerimaangiroheader as header with (readuncommitted)"),'header.id','detail.penerimaangiro_id')
                ->leftJoin(DB::raw("pelanggan as ph with (readuncommitted)"), 'header.pelanggan_id', 'ph.id')
                ->leftJoin(DB::raw("bank with (readuncommitted)"), 'detail.bank_id', 'bank.id')
                ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'detail.pelanggan_id', 'pelanggan.id')
                ->leftJoin(DB::raw("bankpelanggan with (readuncommitted)"), 'detail.bankpelanggan_id', 'bankpelanggan.id');
                
                $pengeluaranTruckingDetail = $query->get();
            } else {
                $query->select(
                    'detail.nobukti',
                    'detail.nowarkat',
                    'detail.tgljatuhtempo',
                    'detail.coadebet',
                    'detail.coakredit',
                    'bank.namabank as bank_id',
                    'pelanggan.namapelanggan as pelanggan_id',
                    'bankpelanggan.namabank as bankpelanggan_id',
                    'detail.invoice_nobukti',
                    'detail.pelunasanpiutang_nobukti',
                    'detail.jenisbiaya',
                    'detail.bulanbeban',
                    'detail.keterangan',
                    'detail.nominal'
                )
                ->leftJoin(DB::raw("bank with (readuncommitted)"), 'detail.bank_id', 'bank.id')
                ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'detail.pelanggan_id', 'pelanggan.id')
                ->leftJoin(DB::raw("bankpelanggan with (readuncommitted)"), 'detail.bankpelanggan_id', 'bankpelanggan.id');
                
                $pengeluaranTruckingDetail = $query->get();
            }
           
            return response([
                'data' => $pengeluaranTruckingDetail
                
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }


    public function store(StorePenerimaanGiroDetailRequest $request)
    {
        DB::beginTransaction();
       
        try {
            $penerimaangiroDetail = new PenerimaanGiroDetail();
            
            $penerimaangiroDetail->penerimaangiro_id = $request->penerimaangiro_id;
            $penerimaangiroDetail->nobukti = $request->nobukti;
            $penerimaangiroDetail->nowarkat = $request->nowarkat;
            $penerimaangiroDetail->tgljatuhtempo = $request->tgljatuhtempo;
            $penerimaangiroDetail->nominal = $request->nominal;
            $penerimaangiroDetail->coadebet = $request->coadebet;
            $penerimaangiroDetail->coakredit = $request->coakredit;
            $penerimaangiroDetail->keterangan = $request->keterangan;
            $penerimaangiroDetail->bank_id = $request->bank_id;
            $penerimaangiroDetail->pelanggan_id = $request->pelanggan_id;
            $penerimaangiroDetail->invoice_nobukti = $request->invoice_nobukti;
            $penerimaangiroDetail->bankpelanggan_id = $request->bankpelanggan_id;
            $penerimaangiroDetail->jenisbiaya = $request->jenisbiaya;
            $penerimaangiroDetail->pelunasanpiutang_nobukti = $request->pelunasanpiutang_nobukti;
            $penerimaangiroDetail->bulanbeban = $request->bulanbeban;
            $penerimaangiroDetail->modifiedby = auth('api')->user()->name;
            
            $penerimaangiroDetail->save();
           
            DB::commit();
            return [
                'error' => false,
                'detail' => $penerimaangiroDetail,
                'id' => $penerimaangiroDetail->id,
                'tabel' => $penerimaangiroDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }     
    }

}
