<?php

namespace App\Http\Controllers;

use App\Models\JurnalUmumPusatDetail;
use App\Http\Requests\StoreJurnalUmumPusatDetailRequest;
use App\Http\Requests\UpdateJurnalUmumPusatDetailRequest;
use App\Models\JurnalUmumPusatHeader;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JurnalUmumPusatDetailController extends Controller
{
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'jurnalumumpusat_id' => $request->jurnalumumpusat_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'forExport' => $request->forExport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];
        try {
            $query = JurnalUmumPusatDetail::from('jurnalumumpusatdetail as detail');

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['jurnalumumpusat_id'])) {
                $query->where('detail.jurnalumumpusat_id', $params['jurnalumumpusat_id']);
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('jurnalumumpusat_id', $params['whereIn']);
            }
            if ($params['forReport']) {
                $id = $params['jurnalumumpusat_id'];
                $data = JurnalUmumPusatHeader::find($id);
                $nobukti = $data['nobukti'];

                $jurnalUmumDetail = DB::table('jurnalumumpusatdetail AS A')
                    ->select(['A.coa as coadebet', 'b.coa as coakredit', 'A.nominal', 'A.keterangan as keterangandetail', 'header.nobukti', 'header.tglbukti','header.keterangan','A.jurnalumumpusat_id'])
                    ->join(
                        DB::raw("(SELECT baris,coa FROM jurnalumumpusatdetail WHERE nobukti='$nobukti' AND nominal<0) B"),
                        function ($join) {
                            $join->on('A.baris', '=', 'B.baris');
                        }
                    )
                    ->join('jurnalumumpusatheader as header','header.id','A.jurnalumumpusat_id')
                    ->where([
                        ['A.nobukti', '=', $nobukti],
                        ['A.nominal', '>=', '0']
                    ])
                    ->get();
            } else if ($params['forExport']) {
                $id = $params['jurnalumumpusat_id'];
                $data = JurnalUmumPusatHeader::find($id);
                $nobukti = $data['nobukti'];

                $jurnalUmumDetail = DB::table('jurnalumumpusatdetail AS A')
                    ->select(['A.coa as coadebet', 'b.coa as coakredit', 'A.nominal', 'A.keterangan', 'A.nobukti', 'A.tglbukti'])
                    ->join(
                        DB::raw("(SELECT baris,coa FROM jurnalumumpusatdetail WHERE nobukti='$nobukti' AND nominal<0) B"),
                        function ($join) {
                            $join->on('A.baris', '=', 'B.baris');
                        }
                    )
                    ->where([
                        ['A.nobukti', '=', $nobukti],
                        ['A.nominal', '>=', '0']
                    ])
                    ->get();
            } else {
                $id = $request->jurnalumumpusat_id;
                $data = JurnalUmumPusatHeader::find($id);
                $nobukti = $data['nobukti'];

                $jurnalUmumDetail = DB::table('jurnalumumpusatdetail AS A')
                    ->select(['A.coa as coadebet', 'b.coa as coakredit', 'A.nominal', 'A.keterangan', 'A.nobukti', 'A.tglbukti'])
                    ->join(
                        DB::raw("(SELECT baris,coa FROM jurnalumumpusatdetail WHERE nobukti='$nobukti' AND nominal<0) B"),
                        function ($join) {
                            $join->on('A.baris', '=', 'B.baris');
                        }
                    )
                    ->where([
                        ['A.nobukti', '=', $nobukti],
                        ['A.nominal', '>=', '0']
                    ])
                    ->get();
            }

            $idUser = auth('api')->user()->id;
            $getuser = User::select('name','cabang.namacabang as cabang_id')
            ->where('user.id',$idUser)->join('cabang','user.cabang_id','cabang.id')->first();
           

            return response([
                'data' => $jurnalUmumDetail,
                'user' => $getuser,
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }

       public function store(StoreJurnalUmumPusatDetailRequest $request)
    {
        DB::beginTransaction();

        try {
            $jurnalUmumPusatDetail = new JurnalUmumPusatDetail();

            $jurnalUmumPusatDetail->jurnalumumpusat_id = $request->jurnalumumpusat_id;
            $jurnalUmumPusatDetail->nobukti = $request->nobukti;
            $jurnalUmumPusatDetail->tglbukti = $request->tglbukti;
            $jurnalUmumPusatDetail->coa = $request->coa;
            $jurnalUmumPusatDetail->nominal = $request->nominal;
            $jurnalUmumPusatDetail->keterangan = $request->keterangan;
            $jurnalUmumPusatDetail->modifiedby = auth('api')->user()->name;
            $jurnalUmumPusatDetail->baris = $request->baris;
            $jurnalUmumPusatDetail->save();

            DB::commit();

            return [
                'error' => false,
                'id' => $jurnalUmumPusatDetail->id,
                'tabel' => $jurnalUmumPusatDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            
            throw $th;
        }
    }

}
