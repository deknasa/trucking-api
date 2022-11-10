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
            $query = HutangDetail::from('hutangdetail as detail');

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
                    'detail.nobukti',
                    'detail.supplier_id',
                    'detail.tgljatuhtempo',
                    'detail.total',
                    'detail.cicilan',
                    'detail.totalbayar',
                    'detail.keterangan'
                );

                $hutangDetail = $query->get();
            } else {
                $query->select(
                    'detail.nobukti',
                    'detail.tgljatuhtempo',
                    'detail.total',
                    'detail.cicilan',
                    'detail.totalbayar',
                    'detail.keterangan',

                    'supplier.namasupplier as supplier_id',

                )
                ->leftJoin('supplier', 'detail.supplier_id', 'supplier.id')
                ;

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

    // public function show($id)
    // {
    //     $data = HutangHeader::with(
    //         'hutangdetail',
    //     )->find($id);

    //     return response([
    //         'status' => true,
    //         'data' => $data
    //     ]);
    // }

    // public function combo(Request $request)
    // {
    //     $data = [
    //         'bank'          => Bank::all(),
    //         'coa'           => AkunPusat::all(),
    //         'parameter'     => Parameter::all(),
       
    //         'statuskas'     => Parameter::where('grp', 'STATUS KAS')->get(),
    //         'statusapproval' => Parameter::where('grp', 'STATUS APPROVAL')->get(),
    //         'statusberkas'  => Parameter::where('grp', 'STATUS BERKAS')->get(),

    //     ];

    //     return response([
    //         'data' => $data
    //     ]);
    // }


    /**
     * @ClassName
     */
//     public function update(StoreHutangHeaderRequest $request, HutangHeader $hutangHeader, $id)
//     {
//         DB::beginTransaction();

//         try {
//             /* Store header */

//             $hutangHeader = HutangHeader::findOrFail($id);
//             $hutangHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
//             $hutangHeader->keterangan = $request->keterangan ?? '';
//             $hutangHeader->postingdari = $request->postingdari ?? 'ENTRU HUTANG';
//             $hutangHeader->diterimadari = $request->diterimadari ?? '';
//             $hutangHeader->bank_id = $request->bank_id ?? '';
//             $hutangHeader->coa = $request->coa ?? '';
//             $hutangHeader->modifiedby = auth('api')->user()->name;

//             if ($hutangHeader->save()) {
//                 $logTrail = [
//                     'namatabel' => strtoupper($hutangHeader->getTable()),
//                     'postingdari' => 'EDIT HUTANG',
//                     'idtrans' => $hutangHeader->id,
//                     'nobuktitrans' => $hutangHeader->nobukti,
//                     'aksi' => 'ENTRY',
//                     'datajson' => $hutangHeader->toArray(),
//                     'modifiedby' => $hutangHeader->modifiedby
//                 ];

//                 $validatedLogTrail = new StoreLogTrailRequest($logTrail);
//                 $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
//             }

//             /* Delete existing detail */
//             $hutangHeader->hutangDetail()->delete();
//             JurnalUmumDetail::where('nobukti', $hutangHeader->nobukti)->delete();
//             JurnalUmumHeader::where('nobukti', $hutangHeader->nobukti)->delete();

//             /* Store detail */
//             $detaillog = [];

//             $total = 0;
//             for ($i = 0; $i < count($request->nominal); $i++) {
//                 $nominal = str_replace(',', '', str_replace('.', '', $request->nominal[$i]));
//                 $datadetail = [
//                     'hutang_id' => $hutangHeader->id,
//                     'nobukti' => $hutangHeader->nobukti,
//                     'supir_id' => $hutangHeader->supir,
//                     'nominal' => $nominal,
//                     'modifiedby' => auth('api')->user()->name,
//                 ];

//                 $data = new StoreHutangDetailRequest($datadetail);
//                 $datadetails = app(HutangDetailController::class)->store($data);

//                 if ($datadetails['error']) {
//                     return response($datadetails, 422);
//                 } else {
//                     $iddetail = $datadetails
//                     ['id'];
//                     $tabeldetail = $datadetails['tabel'];
//                 }

//                 $datadetaillog = [
//                     'hutang_id' => $hutangHeader->id,
//                     'nobukti' => $hutangHeader->nobukti,
//                     'supir_id' => $hutangHeader->supir_id[$i],
//                     'nominal' => $nominal,
//                     'modifiedby' => auth('api')->user()->name,
//                     'created_at' => date('d-m-Y H:i:s', strtotime($hutangHeader->created_at)),
//                     'updated_at' => date('d-m-Y H:i:s', strtotime($hutangHeader->updated_at)),
//                 ];
//                 $detaillog[] = $datadetaillog;

//                 $total += $nominal;
//             }

//             $dataid = LogTrail::select('id')
//                 ->where('nobuktitrans', '=', $hutangHeader->nobukti)
//                 ->where('namatabel', '=', $hutangHeader->getTable())
//                 ->orderBy('id', 'DESC')
//                 ->first();

//                 $datalogtrail = [
//                     'namatabel' => $tabeldetail,
//                     'postingdari' => 'EDIT HUTANG',
//                     'idtrans' =>  $dataid->id,
//                     'nobuktitrans' => $hutangHeader->nobukti,
//                     'aksi' => 'ENTRY',
//                     'datajson' => $detaillog,
//                     'modifiedby' => auth('api')->user()->name,
//                 ];

//             $data = new StoreLogTrailRequest($datalogtrail);
//             app(LogTrailController::class)->store($data);

//             $request->sortname = $request->sortname ?? 'id';
//             $request->sortorder = $request->sortorder ?? 'asc';

//                 DB::commit();

//                 /* Set position and page */
//         $hutangHeader->position = DB::table((new HutangHeader())->getTable())->orderBy($request->sortname, $request->sortorder)
//         ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $hutangHeader->{$request->sortname})
//         ->where('id', '<=', $hutangHeader->id)
//         ->count();

//     if (isset($request->limit)) {
//         $hutangHeader->page = ceil($hutangHeader->position / $request->limit);
//     }

//     return response([
//         'status' => true,
//         'message' => 'Berhasil disimpan',
//         'data' => $hutangHeader
//     ]);
// } catch (\Throwable $th) {
//     DB::rollBack();
//     throw $th;
// }

//     return response($hutangHeader->hutangdetail());
// }

    /**
     * @ClassName
     */
    // public function destroy($id, JurnalUmumHeader $jurnalumumheader, Request $request)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $get = HutangHeader::find($id);
    //         // $get = JurnalUmumDetail::find($id);
    //         // $get = JurnalUmumHeader::find($id);

    //         $delete = HutangDetail::where('hutang_id', $id)->delete();
    //         // $delete = JurnalUmumHeader::where('nobukti', $get->nobukti)->delete();
    //         // $delete = JurnalUmumDetail::where('nobukti', $get->nobukti)->delete();

    //         $delete = HutangHeader::destroy($id);
    //         // $delete = JurnalUmumHeader::destroy($id);
    //         // $delete = JurnalUmumDetail::destroy($id);


    //         $datalogtrail = [
    //             'namatabel' => $get->getTable(),
    //             'postingdari' => 'DELETE HUTANG',
    //             'idtrans' => $id,
    //             'nobuktitrans' => '',
    //             'aksi' => 'HAPUS',
    //             'datajson' => '',
    //             'modifiedby' => $get->modifiedby,
    //         ];

    //         $data = new StoreLogTrailRequest($datalogtrail);
    //         app(LogTrailController::class)->store($data);

    //         if ($delete) {
    //             DB::commit();
    //             return response([
    //                 'status' => true,
    //                 'message' => 'Berhasil dihapus'
    //             ]);
    //         } else {
    //             DB::rollBack();
    //             return response([
    //                 'status' => false,
    //                 'message' => 'Gagal dihapus'
    //             ]);
    //         }
    //     } catch (\Throwable $th) {
    //         DB::rollBack();
    //         throw $th;
    //     }
    // }

    /**
     * @ClassName
     */
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
                'id' => $hutangdetail->id,
                'tabel' => $hutangdetail->getTable(),
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }        
    }

}

