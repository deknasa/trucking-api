<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GajiSupirDetail;
use App\Http\Requests\StoreGajiSupirDetailRequest;
use App\Http\Requests\UpdateGajiSupirDetailRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GajiSupirDetailController extends Controller
{
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'gajisupir_id' => $request->gajisupir_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];
        try {
            $query = GajiSupirDetail::from('gajisupirdetail as detail');

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['gajisupir_id'])) {
                $query->where('detail.gajisupir_id', $params['gajisupir_id']);
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('gajisupir_id', $params['whereIn']);
            }
            if ($params['forReport']) {
                $query->select(
                    'header.id',
                    'header.nobukti',
                    'header.tglbukti',
                    'header.keterangan as keterangan_header',
                    'header.nominal',
                    'supir.namasupir as supir',
                    'detail.suratpengantar_nobukti',
                    'suratpengantar.tglsp',
                    'suratpengantar.nosp',
                    'suratpengantar.nocont',
                    'sampai.keterangan as sampai',
                    'dari.keterangan as dari',
                    'detail.gajisupir',
                    'detail.gajikenek',
                )
                ->join('gajisupirheader as header','header.id','detail.gajisupir_id')
                ->join('supir','header.supir_id','supir.id')
                ->join('suratpengantar','detail.suratpengantar_nobukti','suratpengantar.nobukti')
                ->join('kota as dari','suratpengantar.dari_id','dari.id')
                ->join('kota as sampai','suratpengantar.sampai_id','sampai.id');

                $gajisupirDetail = $query->get();
            } else {
                $query->select(
                    'detail.nobukti',
                    'detail.suratpengantar_nobukti',
                    'suratpengantar.tglsp',
                    'suratpengantar.nosp',
                    'suratpengantar.nocont',
                    'sampai.keterangan as sampai',
                    'dari.keterangan as dari',
                    'detail.gajisupir',
                    'detail.gajikenek',
                )
                ->join('suratpengantar','detail.suratpengantar_nobukti','suratpengantar.nobukti')
                ->join('kota as dari','suratpengantar.dari_id','dari.id')
                ->join('kota as sampai','suratpengantar.sampai_id','sampai.id');
                $gajisupirDetail = $query->get();
            }
            return response([
                'data' => $gajisupirDetail
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }

    
    public function store(StoreGajiSupirDetailRequest $request)
    {
        DB::beginTransaction();

        
        try {
            $gajisupirdetail = new GajiSupirDetail();
            
            $gajisupirdetail->gajisupir_id = $request->gajisupir_id;
            $gajisupirdetail->nobukti = $request->nobukti;
            $gajisupirdetail->nominaldeposito = $request->nominaldeposito;
            $gajisupirdetail->nourut = $request->nourut;
            $gajisupirdetail->suratpengantar_nobukti = $request->suratpengantar_nobukti;
            $gajisupirdetail->komisisupir = $request->komisisupir;
            $gajisupirdetail->tolsupir = $request->tolsupir;
            $gajisupirdetail->voucher = $request->voucher;
            $gajisupirdetail->novoucher = $request->novoucher;
            $gajisupirdetail->gajisupir = $request->gajisupir;
            $gajisupirdetail->gajikenek = $request->gajikenek;
            $gajisupirdetail->gajiritasi = $request->gajiritasi;
            $gajisupirdetail->nominalpengembalianpinjaman = $request->nominalpengembalianpinjaman;
            
            $gajisupirdetail->modifiedby = auth('api')->user()->name;
            
            $gajisupirdetail->save();
           
            DB::commit();
           
            return [
                'error' => false,
                'detail' => $gajisupirdetail,
                'id' => $gajisupirdetail->id,
                'tabel' => $gajisupirdetail->getTable(),
            ];
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }        
    }



}
