<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\SuratPengantar;
use App\Models\KasGantungDetail;
use App\Models\AbsensiSupirDetail;
use App\Models\AbsensiSupirHeader;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreAbsensiSupirDetailRequest;
use App\Models\Parameter;
use Illuminate\Database\Eloquent\Factories\HasFactory;



class AbsensiSupirDetailController extends Controller
{
    /**
     * @ClassName
     * @Keterangan TAMPILKAN DATA
     */
    public function index(Request $request)
    {
        $absensiSupirDetail = new AbsensiSupirDetail();

        $idUser = auth('api')->user()->id;
        $getuser = User::select('name')
            ->where('user.id', $idUser)->first();

        return response([
            'data' => $absensiSupirDetail->get(),
            'user' => $getuser,
            "totalNominal" => $absensiSupirDetail->totalNominal,
            "jlhtrip" => $absensiSupirDetail->jlhtrip,
            'attributes' => [
                'totalRows' => $absensiSupirDetail->totalRows,
                "totalPages" => $absensiSupirDetail->totalPages,
            ]

        ]);
    }

    public function store(StoreAbsensiSupirDetailRequest $request)
    {
        $absensiSupirDetail = new AbsensiSupirDetail();
        $absensiSupirDetail->absensi_id = $request->absensi_id ?? '';
        $absensiSupirDetail->nobukti = $request->nobukti ?? '';
        $absensiSupirDetail->trado_id = $request->trado_id ?? '';
        $absensiSupirDetail->absen_id = $request->absen_id ?? '';
        $absensiSupirDetail->supir_id = $request->supir_id ?? '';
        $absensiSupirDetail->jam = $request->jam ?? '';
        $absensiSupirDetail->uangjalan = $request->uangjalan ?? '';
        $absensiSupirDetail->keterangan = $request->keterangan ?? '';
        $absensiSupirDetail->modifiedby = $request->modifiedby ?? '';

        if (!$absensiSupirDetail->save()) {
            throw new \Exception("Gagal menyimpan absensi supir detail.");
        }

        return [
            'error' => false,
            'detail' => $absensiSupirDetail,
            'id' => $absensiSupirDetail->id,
            'tabel' => $absensiSupirDetail->getTable(),
        ];
    }


    public function getDetailAbsensi(Request $request)
    {
        $tglbukti = date('Y-m-d', strtotime($request->tgltrip));
        $id = '';
        if ($request->absensi_id != '') {
            $id = $request->absensi_id;
            if ($request->isProsesUangjalan == true) {
                $request->request->add(['isProsesUangjalan' => true]);
            }
            if ($request->from == 'pengajuantripinap') {
                $request->request->add(['getabsen' => true]);
            }
            if ($request->from == 'tripinap') {
                $request->request->add(['getabsen' => true]);
            }
        } else {

            $absensiSupirHeader = AbsensiSupirHeader::where('tglbukti', $tglbukti)->first();
            if (!$absensiSupirHeader) {
                return response([
                    'data' => [],
                    'total' => 0,
                    "records" => 0,
                ]);
            } else {

                $user_id = auth('api')->user()->id;
                $isMandor = auth()->user()->isMandor();
                $isAdmin = auth()->user()->isAdmin();
                if (!$isAdmin) {
                    if ($isMandor) {

                        $cekTanpaBatas = (new Parameter())->cekText('TANPA BATAS TRIP', 'TANPA BATAS TRIP');
                        if($cekTanpaBatas == 'YA'){
                            goto selesai;
                        }
                        $getBatasInput = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JAMBATASINPUTTRIP')->where('subgrp', 'JAMBATASINPUTTRIP')->first();
                        $getBatasHari = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'BATASHARIINPUTTRIP')->where('subgrp', 'BATASHARIINPUTTRIP')->first()->text;
                        $kondisi = true;
                        $batasHari = $getBatasHari;
                        $tanggal = date('Y-m-d', strtotime($tglbukti));
                        // if ($getBatasHari != 0) {

                        while ($kondisi) {
                            $cekHarilibur = DB::table("harilibur")->from(DB::raw("harilibur with (readuncommitted)"))
                                ->where('tgl', $tanggal)
                                ->first();

                            $todayIsSunday = date('l', strtotime($tanggal));
                            $tomorrowIsSunday = date('l', strtotime($tanggal . "+1 days"));
                            if ($cekHarilibur == '') {
                                $kondisi = false;
                                $allowed = true;
                                if (strtolower($todayIsSunday) == 'sunday') {
                                    $kondisi = true;
                                    $batasHari += 1;
                                }
                                if (strtolower($tomorrowIsSunday) == 'sunday') {
                                    $kondisi = true;
                                    $batasHari += 1;
                                }
                            } else {
                                $batasHari += 1;
                            }
                            $tanggal = date('Y-m-d', strtotime($tglbukti . "+$batasHari days"));
                        }
                        // } else {
                        //     $tanggal = date('Y-m-d', strtotime($tglbukti . "+$getBatasHari days"));
                        // }

                        if ($tanggal . ' ' . $getBatasInput->text < date('Y-m-d H:i:s')) {
                            if (request()->from == 'listtrip') {
                                $trip = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
                                    ->select('nobukti', 'jobtrucking', 'tglbukti', DB::raw("isnull(approvalbukatanggal_id,0) as approvalbukatanggal_id"), 'tglbataseditsuratpengantar')
                                    ->where('id', request()->trip_id)
                                    ->first();
                                if ($trip != '') {

                                    if (date('Y-m-d H:i:s') < date('Y-m-d H:i:s', strtotime($trip->tglbataseditsuratpengantar))) {
                                        goto selesai;
                                    }
                                }
                            }
                            // GET APPROVAL INPUTTRIP
                            $tempApp = '##tempApp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                            Schema::create($tempApp, function ($table) {
                                $table->unsignedBigInteger('id')->nullable();
                                $table->date('tglbukti')->nullable();
                                $table->unsignedBigInteger('jumlahtrip')->nullable();
                                $table->unsignedBigInteger('statusapproval')->nullable();
                                $table->unsignedBigInteger('user_id')->nullable();
                                $table->datetime('tglbatas')->nullable();
                            });

                            $querybukaabsen = DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip with (readuncommitted)"))
                                ->select('id', 'tglbukti', 'jumlahtrip', 'statusapproval', 'user_id', 'tglbatas')
                                ->where('tglbukti', $tglbukti);
                            DB::table($tempApp)->insertUsing([
                                'id',
                                'tglbukti',
                                'jumlahtrip',
                                'statusapproval',
                                'user_id',
                                'tglbatas',
                            ],  $querybukaabsen);

                            // GET MANDOR DETAIL
                            $tempMandor = '##tempMandor' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                            Schema::create($tempMandor, function ($table) {
                                $table->id();
                                $table->unsignedBigInteger('mandor_id')->nullable();
                            });

                            $querymandor = DB::table("mandordetail")->from(DB::raw("mandordetail with (readuncommitted)"))
                                ->select('mandor_id')->where('user_id', $user_id);
                            DB::table($tempMandor)->insertUsing([
                                'mandor_id',
                            ],  $querymandor);

                            // BUAT TEMPORARY SP GROUP BY TEMPO ID
                            $tempSP = '##tempSP' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                            Schema::create($tempSP, function ($table) {
                                $table->id();
                                $table->unsignedBigInteger('approvalbukatanggal_id')->nullable();
                                $table->unsignedBigInteger('jumlahtrip')->nullable();
                            });

                            $querySP = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
                                ->select('approvalbukatanggal_id', DB::raw("count(nobukti) as jumlahtrip"))
                                ->where('tglbukti', $tglbukti)
                                ->whereRaw("isnull(approvalbukatanggal_id,0) != 0")
                                ->groupBy('approvalbukatanggal_id');

                            DB::table($tempSP)->insertUsing([
                                'approvalbukatanggal_id',
                                'jumlahtrip'
                            ],  $querySP);
                            // GET APPROVAL BERDASARKAN MANDOR

                            $getAll = DB::table("mandordetail")->from(DB::raw("mandordetail as a"))
                                ->select('a.mandor_id', 'c.id', 'c.user_id', 'c.statusapproval', 'c.tglbatas', 'c.jumlahtrip', 'e.namamandor')
                                ->leftJoin(DB::raw("$tempMandor as b with (readuncommitted)"), 'a.mandor_id', 'b.mandor_id')
                                ->leftJoin(DB::raw("$tempApp as c with (readuncommitted)"), 'a.user_id', 'c.user_id')
                                ->leftJoin(DB::raw("$tempSP as d with (readuncommitted)"), 'c.id', 'd.approvalbukatanggal_id')
                                ->leftjoin(db::raw("mandor e "), 'a.mandor_id', 'e.id')
                                ->whereRaw('COALESCE(b.mandor_id, 0) <> 0')
                                ->whereRaw('COALESCE(c.user_id, 0) <> 0')
                                ->whereRaw('isnull(d.jumlahtrip,0) < c.jumlahtrip')
                                ->orderBy('c.tglbatas', 'desc')
                                ->first();
                            if ($getAll == '') {
                                return response([
                                    'data' => [],
                                    'total' => 0,
                                    "records" => 0,
                                ]);
                            } else {
                                if ($getAll->statusapproval == 4) {

                                    return response([
                                        'data' => [],
                                        'total' => 0,
                                        "records" => 0,
                                    ]);
                                }

                                $suratPengantar = SuratPengantar::where('tglbukti', '=', $tglbukti)->whereRaw("approvalbukatanggal_id = $getAll->id")->count();

                                $now = date('Y-m-d H:i:s');
                                if ($now > $getAll->tglbatas) {

                                    return response([
                                        'data' => [],
                                        'total' => 0,
                                        "records" => 0,
                                    ]);
                                }

                                if ($getAll->jumlahtrip < ($suratPengantar + 1)) {

                                    return response([
                                        'data' => [],
                                        'total' => 0,
                                        "records" => 0,
                                    ]);
                                }
                                if ($user_id != $getAll->user_id) {
                                    $querycekuser = db::table("mandordetail")->from(db::raw("mandordetail a with (readuncommitted)"))
                                        ->select(
                                            'a.user_id'
                                        )
                                        ->where('a.user_id', $user_id)
                                        ->first();
                                    if (!isset($querycekuser)) {

                                        return response([
                                            'data' => [],
                                            'total' => 0,
                                            "records" => 0,
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
                selesai:
                $id = $absensiSupirHeader->id;
                $request->request->add(['getabsen' => true]);
            }
        }
        $request->request->add(['absensi_id' => $id]);

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

    public function getProsesKGT(Request $request)
    {
        $KasGantungDetail = new KasGantungDetail;
        return response([
            'data' => $KasGantungDetail->getKgtAbsensi($request->nobukti),

            'attributes' => [
                'totalRows' => $KasGantungDetail->totalRows,
                "totalPages" => $KasGantungDetail->totalPages,
            ]

        ]);
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
