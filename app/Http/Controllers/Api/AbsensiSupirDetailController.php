<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAbsensiSupirDetailRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\AbsensiSupirDetail;
use App\Models\AbsensiSupirHeader;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Factories\HasFactory;



class AbsensiSupirDetailController extends Controller
{
    public function index(Request $request)
    {
        $absensiSupirDetail = new AbsensiSupirDetail();

        $idUser = auth('api')->user()->id;
            $getuser = User::select('name', 'cabang.namacabang as cabang_id')
                ->where('user.id', $idUser)->join('cabang', 'user.cabang_id', 'cabang.id')->first();


            return response([
                'data' => $absensiSupirDetail->get(),
                'user' => $getuser,
                'total' => $absensiSupirDetail->totalRows,
                "records" => $absensiSupirDetail->totalPages,
                "totalNominal" => $absensiSupirDetail->totalNominal

            ]);
    }
   
    public function store(StoreAbsensiSupirDetailRequest $request)
    {
        DB::beginTransaction();

        try {
            $AbsensiSupirDetail = new AbsensiSupirDetail();

            $AbsensiSupirDetail->absensi_id = $request->absensi_id ?? '';
            $AbsensiSupirDetail->nobukti = $request->nobukti ?? '';
            $AbsensiSupirDetail->trado_id = $request->trado_id ?? '';
            $AbsensiSupirDetail->absen_id = $request->absen_id ?? '';
            $AbsensiSupirDetail->supir_id = $request->supir_id ?? '';
            $AbsensiSupirDetail->jam = $request->jam ?? '';
            $AbsensiSupirDetail->uangjalan = $request->uangjalan ?? '';
            $AbsensiSupirDetail->keterangan = $request->keterangan ?? '';
            $AbsensiSupirDetail->modifiedby = $request->modifiedby ?? '';

            $AbsensiSupirDetail->save();


            DB::commit();
            return [
                'error' => false,
                'detail' => $AbsensiSupirDetail,
                'id' => $AbsensiSupirDetail->id,
                'tabel' => $AbsensiSupirDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }


    public function getDetailAbsensi(Request $request)
    {
        $tglbukti = date('Y-m-d', strtotime('now'));
        $absensiSupirHeader = AbsensiSupirHeader::where('tglbukti', $tglbukti)->first();
        if (!$absensiSupirHeader) {
            return response([
                'data' => [],
                'total' => 0,
                "records" => 0,
            ]);
        }
        $request->request->add(['absensi_id'=> $absensiSupirHeader->id]);

        return $this->index($request);
    }

    public function update(Request $request, AbsensiSupirDetail $absensiSupirDetail)
    {
        // 
    }

    public function destroy(AbsensiSupirDetail $absensiSupirDetail)
    {
        // 
    }
    // public function index2(Request $request)
    // {

    //     $tempsp = '##tempsp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
    //     Schema::create($tempsp, function ($table) {
    //         $table->unsignedBigInteger('absensi_id')->nullable();
    //         $table->unsignedBigInteger('trado_id')->nullable();
    //         $table->unsignedBigInteger('supir_id')->nullable();
    //         $table->date('tglabsensi')->nullable();
    //         $table->string('nobukti', 100)->nullable();
    //     });

    //     $query=DB::table('absensisupirheader')->from(
    //         DB::raw("absensisupirheader as a with(readuncommitted)")
    //     )
    //     ->select(
    //         DB::raw("format(a.tglbukti,'yyyy/MM/dd') as tglbukti")
    //     )
    //     ->where('a.id','=',$request->absensi_id)
    //     ->first();
            

    //     $statustrip=DB::table("parameter")->from(
    //         DB::raw("parameter with (readuncommitted)")
    //     )
    //     ->select (
    //         'memo'
    //     )
    //     ->where('grp','=','TIDAK ADA TRIP')
    //     ->where('subgrp','=','TIDAK ADA TRIP')
    //     ->where('text','=','TIDAK ADA TRIP')
    //     ->first();


    //     $param1= $query->tglbukti;
    //     $querysp=DB::table('absensisupirdetail')->from(
    //         DB::raw("absensisupirdetail as a with (readuncommitted)")
    //     ) ->select (
    //         'a.absensi_id',
    //         'a.trado_id',
    //         'a.supir_id',
    //         'c.tglbukti as tglabsensi',
    //         'b.nobukti'
    //     ) 
    //     ->join(DB::raw("suratpengantar as b with(readuncommitted)"), function ($join) use ($param1) {
    //         $join->on('a.supir_id', '=', 'b.supir_id');
    //         $join->on('a.trado_id', '=', 'b.trado_id');
    //         $join->on('b.tglbukti', '=', DB::raw("'" . $param1 . "'"));
    //     })
    //     ->join(DB::raw("absensisupirheader as c with (readuncommitted)"),'a.absensi_id','c.id')
    //     ->where('c.id','=',$request->absensi_id);

    //     // dd($querysp);
    //     DB::table($tempsp)->insertUsing([
    //         'absensi_id',
    //         'trado_id',
    //         'supir_id',
    //         'tglabsensi',
    //         'nobukti',
    //     ], $querysp);        


    //     $queryspgroup=DB::table($tempsp)
    //     ->from(
    //         DB::raw($tempsp . " as a")
    //     )
    //     ->select(
    //         'a.trado_id',
    //         'a.supir_id',
    //        DB::raw("count(a.nobukti) as jumlah")   
    //     )
    //     ->groupBy('a.trado_id','a.supir_id');
   

    //     $tempspgroup = '##tempspgroup' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
    //     Schema::create($tempspgroup, function ($table) {
    //         $table->unsignedBigInteger('trado_id')->nullable();
    //         $table->unsignedBigInteger('supir_id')->nullable();
    //         $table->double('jumlah', 15, 2)->nullable();
    //     });

    //     DB::table($tempspgroup)->insertUsing([
    //         'trado_id',
    //         'supir_id',
    //         'jumlah',
    //     ], $queryspgroup);        

    //     $params = [
    //         'id' => $request->id,
    //         'absensi_id' => $request->absensi_id,
    //         'withHeader' => $request->withHeader ?? false,
    //         'whereIn' => $request->whereIn ?? [],
    //         'forReport' => $request->forReport ?? false,
    //         'sortIndex' => $request->sortOrder ?? 'id',
    //         'sortOrder' => $request->sortOrder ?? 'asc',
    //         'offset' => $request->offset ?? (($request->page - 1) * $request->limit),
    //         'limit' => $request->limit ?? 10,
    //     ];
    //     $totalRows = 0;
    //     try {
    //         $query = DB::table('absensisupirdetail')->from(
    //                 DB::raw("absensisupirdetail as detail with (readuncommitted)")
    //             );

    //         if (isset($params['id'])) {
    //             $query->where('detail.id', $params['id']);
    //         }

    //         if (isset($params['absensi_id'])) {
    //             $query->where('detail.absensi_id', $params['absensi_id']);
    //         }

    //         if ($params['withHeader']) {
    //             $query->join('absensisupirheader', 'absensisupirheader.id', 'detail.absensi_id');
    //         }

    //         if (count($params['whereIn']) > 0) {
    //             $query->whereIn('absensi_id', $params['whereIn']);
    //         }

    //         if ($params['forReport']) {
    //             $query->select(
    //                 'header.id as id_header',
    //                 'header.nobukti as nobukti_header',
    //                 'header.tglbukti as tgl_header',
    //                 'header.kasgantung_nobukti as kasgantung_nobukti_header',
    //                 'header.nominal as nominal_header',
    //                 'trado.keterangan as trado',
    //                 'supir.namasupir as supir',
    //                 'absentrado.kodeabsen as status',
    //                 'detail.keterangan as keterangan_detail',
    //                 'detail.jam',
    //                 'detail.uangjalan',
    //                 'detail.absensi_id',
    //                 DB::raw("isnull(c.jumlah,0) as jumlahtrip")

    //             )
    //                 ->leftjoin(DB::raw("absensisupirheader as header with (readuncommitted)"), 'header.id', 'detail.absensi_id')
    //                 ->leftjoin(DB::raw("trado with (readuncommitted)"), 'trado.id', 'detail.trado_id')
    //                 ->leftjoin(DB::raw("supir with (readuncommitted)"), 'supir.id', 'detail.supir_id')
    //                 ->leftjoin(DB::raw("absentrado with (readuncommitted)"), 'absentrado.id', 'detail.absen_id')
    //                 ->leftjoin(DB::raw($tempspgroup." as c"), function ($join)  {
    //                     $join->on('detail.supir_id', '=', 'c.supir_id');
    //                     $join->on('detail.trado_id', '=', 'c.trado_id');
    //                 });
            

    //             $absensiSupirDetail = $query->get();
    //         } else {
    //             $query->select(
    //                 'trado.keterangan as trado',
    //                 'supir.namasupir as supir',
    //                 'absentrado.kodeabsen as status',
    //                 'detail.keterangan as keterangan_detail',
    //                 'detail.jam',
    //                 'detail.id',
    //                 'detail.trado_id',
    //                 'detail.supir_id',
    //                 'detail.uangjalan',
    //                 'detail.absensi_id',
    //                 DB::raw("isnull(c.jumlah,0) as jumlahtrip"),
    //                 DB::Raw("(case when isnull(c.jumlah,0)=0  and isnull(absentrado.kodeabsen,'')='' then '".$statustrip->memo . "' else '' end) as statustrip")
    //             )
    //                 ->leftjoin(DB::raw("trado with (readuncommitted)"), 'trado.id', 'detail.trado_id')
    //                 ->leftjoin(DB::raw("supir with (readuncommitted)"), 'supir.id', 'detail.supir_id')
    //                 ->leftjoin(DB::raw("absentrado with (readuncommitted)"), 'absentrado.id', 'detail.absen_id')
    //                 ->leftjoin(DB::raw($tempspgroup." as c"), function ($join)  {
    //                     $join->on('detail.supir_id', '=', 'c.supir_id');
    //                     $join->on('detail.trado_id', '=', 'c.trado_id');
    //                 });

    //                 $totalRows =  $query->count();
    //             $query->skip($params['offset'])->take($params['limit']);
    //             $absensiSupirDetail = $query->get();
    //         }
    //         $idUser = auth('api')->user()->id;
    //         $getuser = User::select('name', 'cabang.namacabang as cabang_id')
    //             ->where('user.id', $idUser)->join('cabang', 'user.cabang_id', 'cabang.id')->first();


    //         return response([
    //             'data' => $absensiSupirDetail,
    //             'user' => $getuser,
    //             'total' => $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1,
    //             "records" => $totalRows

    //         ]);
    //     } catch (\Throwable $th) {
    //         return response([
    //             'message' => $th->getMessage()
    //         ]);
    //     }
    // }

}
