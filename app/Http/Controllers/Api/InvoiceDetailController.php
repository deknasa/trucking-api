<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoiceDetail;
use App\Http\Requests\StoreInvoiceDetailRequest;
use App\Http\Requests\UpdateInvoiceDetailRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InvoiceDetailController extends Controller
{
    
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'invoice_id' => $request->invoice_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn,
            'forReport' => $request->forReport ?? false,
            'forExport' => $request->forExport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];
        try {
            $query = InvoiceDetail::from(DB::raw("invoicedetail as detail with (readuncommitted)"));

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['invoice_id'])) {
                $query->where('detail.invoice_id', $params['invoice_id']);
            }
            
            if ($params['withHeader']) {
                $query->join('invoiceheader', 'invoiceheader.id', 'detail.invoice_id');
            }

            if ($params['whereIn'] > 0) {
                $query->whereIn('invoice_id', $params['whereIn']);
            }
            if ($params['forReport']) {
                $query->select(
                    'header.id as id_header',
                    'header.nobukti as nobukti_header',
                    'header.tglbukti',
                    'header.nominal as nominal_header',
                    'agen.namaagen as agen',
                    'cabang.namacabang as cabang',
                    'detail.orderantrucking_nobukti',
                    'detail.nominal as nominal_detail',
                    'suratpengantar.nocont',
                    'suratpengantar.tglsp',
                    'suratpengantar.keterangan',
                    'kota.keterangan as tujuan',
                    'detail.invoice_id'
                )
                ->distinct('detail.orderantrucking_nobukti')
                ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"),'detail.orderantrucking_nobukti','suratpengantar.jobtrucking')
                ->leftJoin(DB::raw("invoiceheader as header with (readuncommitted)"),'header.id','detail.invoice_id')
                ->leftJoin(DB::raw("agen with (readuncommitted)"),'header.agen_id','agen.id')
                ->leftJoin(DB::raw("cabang with (readuncommitted)"),'header.cabang_id','cabang.id')
                ->leftJoin(DB::raw("kota with (readuncommitted)"),'suratpengantar.sampai_id','kota.id');

                $invoiceDetail = $query->get();
            } else if ($params['forExport']) {
                $query->select(
                   'suratpengantar.tglsp',
                   'agen.namaagen as agen_id',
                   'kota.keterangan as tujuan',
                   'suratpengantar.nocont',
                   'detail.nominal as omset',
                   'detail.keterangan as keterangan_detail' 
                )
                
                ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"),'detail.suratpengantar_nobukti','suratpengantar.nobukti')
                ->leftJoin(DB::raw("agen with (readuncommitted)"),'suratpengantar.agen_id','agen.id')
                ->leftJoin(DB::raw("kota with (readuncommitted)"),'suratpengantar.sampai_id','kota.id');

                $invoiceDetail = $query->get();
            } else {
                $query->select(
                    'detail.nobukti',
                    'detail.keterangan',
                    'detail.nominal',
                    'detail.orderantrucking_nobukti',
                    'detail.suratpengantar_nobukti',
                );

                $invoiceDetail = $query->get();
            }
           

            return response([
                'data' => $invoiceDetail
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }

    
    public function store(StoreInvoiceDetailRequest $request)
    {
        DB::beginTransaction();

        
        try {
            $invoiceDetail = new InvoiceDetail();
            
            $invoiceDetail->invoice_id = $request->invoice_id;
            $invoiceDetail->nobukti = $request->nobukti;
            $invoiceDetail->nominal = $request->nominal;
            $invoiceDetail->keterangan = $request->keterangan;
            $invoiceDetail->orderantrucking_nobukti = $request->orderantrucking_nobukti;
            $invoiceDetail->suratpengantar_nobukti = $request->suratpengantar_nobukti;
            
            $invoiceDetail->modifiedby = auth('api')->user()->name;
            
            $invoiceDetail->save();
           
            DB::commit();
           
            return [
                'error' => false,
                'detail' => $invoiceDetail,
                'id' => $invoiceDetail->id,
                'tabel' => $invoiceDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }      
    }

    
}
