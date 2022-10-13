<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KasGantungDetail;
use App\Http\Requests\StoreKasGantungDetailRequest;
use App\Http\Requests\StorePengeluaranDetailRequest;
use App\Http\Requests\UpdateKasGantungDetailRequest;
use App\Models\PengeluaranHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class KasGantungDetailController extends Controller
{

    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'kasgantung_id' => $request->kasgantung_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        try {
            $query = KasGantungDetail::from('kasgantungdetail as detail');

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['kasgantung_id'])) {
                $query->where('detail.kasgantung_id', $params['kasgantung_id']);
            }

            // if ($params['withHeader']) {
            //     $query->join('kasgantungheader', 'kasgantungheader.id', 'detail.kasgantung_id');
            // }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('kasgantung_id', $params['whereIn']);
            }

            if ($params['forReport']) {
                $query->select(
                    // 'header.id as id_header',
                    // 'header.nobukti as nobukti_header',
               
               
                    // 'header.tgl as tgl_header',
                    // 'header.keterangan as keterangan_header',
                    // 'header.kasgantung_nobukti as kasgantung_nobukti_header',
                    // 'header.nominal as nominal_header',
                    // 'trado.nama as trado',
                    // 'supir.namasupir as supir',
                    // 'absentrado.kodeabsen as status',
                    'detail.keterangan as keterangan_detail',
                    'detail.nominal',
                    'detail.uangjalan',
                    'detail.kasgantung_id'
                )
                    ->join('kasgantungheader as header', 'header.id', 'detail.kasgantung_id')
                    // ->join('trado', 'trado.id', '=', 'detail.trado_id', 'full outer')
                    // ->join('supir', 'supir.id', '=', 'detail.supir_id', 'full outer')
                    // ->join('absentrado', 'absentrado.id', '=', 'detail.absen_id', 'full outer')
                    ->orderBy('header.nobukti', 'asc');

                $kasgantungDetail = $query->get();
            } else {
                // $query->with('trado', 'supir', 'absenTrado');
                $query->select(
                    'detail.keterangan',
                    'detail.nominal',
                    'detail.nobukti',
                    'akunpusat.keterangancoa as coa',
                )->join('akunpusat','detail.coa','akunpusat.coa');
                $kasgantungDetail = $query->get();
            }

            return response([
                'data' => $kasgantungDetail
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }


    public function create()
    {
        //
    }

    public function store(StoreKasGantungDetailRequest $request)
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'nominal' => 'required',
        ], [
            'nominal.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
        ], [
            'nominal' => 'Nominal',
        ]);
        if (!$validator->passes()) {
            return [
                'error' => true,
                'errors' => $validator->messages()
            ];
        }

        try {
            $kasgantungDetail = new KasGantungDetail();
            $entriluar = $request->entriluar ?? 0;

            $kasgantungDetail->kasgantung_id = $request->kasgantung_id;
            $kasgantungDetail->nobukti = $request->nobukti;
            $kasgantungDetail->nominal = $request->nominal;
            $kasgantungDetail->coa = $request->coa ?? '';
            $kasgantungDetail->keterangan = $request->keterangan ?? '';
            $kasgantungDetail->modifiedby = $request->modifiedby;
            $kasgantungDetail->save();

            // insert ke pengeluaran
            // if($entriluar == 1) {
            //     $nobukti = $request->pengeluaran_nobukti;
            //     $fetchId = PengeluaranHeader::select('id')
            //     ->where('nobukti','=',$nobukti)
            //     ->first();
            //     $id = $fetchId->id;
            //     $pengeluaranDetail = [
            //         'pengeluaran_id' => $id,
            //         'entriluar' => 1,
            //         'nobukti' => $nobukti,
            //         'alatbayar_id' => '',
            //         'nowarkat' => '',
            //         'tgljatuhtempo' => '',
            //         'nominal' => $request->nominal,
            //         'coadebet' => '',
            //         'coakredit' => '',
            //         'keterangan' => $request->keterangan_detail,
            //         'bulanbeban' => '',
            //         'modifiedby' => $request->modifiedby
            //     ];

            //     $detail = new StorePengeluaranDetailRequest($pengeluaranDetail);
            //     $tes = app(PengeluaranDetailController::class)->store($detail);
            // }

            DB::commit();
            if ($validator->passes()) {
                return [
                    'error' => false,
                    'id' => $kasgantungDetail->id,
                    'tabel' => $kasgantungDetail->getTable(),
                ];
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }        
    }

    public function show(KasGantungDetail $kasGantungDetail)
    {
        //
    }

    public function edit(KasGantungDetail $kasGantungDetail)
    {
        //
    }

    public function update(UpdateKasGantungDetailRequest $request, KasGantungDetail $kasGantungDetail)
    {
        //
    }

    public function destroy(KasGantungDetail $kasGantungDetail)
    {
        //
    }
}
