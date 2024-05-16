<?php

namespace App\Http\Controllers\Api;

use stdClass;

use App\Models\Error;
use App\Models\Trado;
use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Models\AbsensiSupirDetail;
use App\Models\AbsensiSupirHeader;
use App\Models\MandorAbsensiSupir;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\MandorAbsensiSupirRequest;

use App\Http\Requests\GetMandorAbsensiSupirRequest;
use App\Http\Requests\MandorAbsensiSupirAllRequest;
use App\Http\Requests\StoreKasGantungDetailRequest;
use App\Http\Requests\StoreKasGantungHeaderRequest;
use App\Http\Requests\StoreAbsensiSupirDetailRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\MandorAbsensiSupirAllSupirSerapRequest;

class MandorAbsensiSupirController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetMandorAbsensiSupirRequest $request)
    {
        $mandorabsensisupir = new MandorAbsensiSupir();
        return response([
            'data' => $mandorabsensisupir->get(),
            'attributes' => [
                'total' => $mandorabsensisupir->totalPages,
                'records' => $mandorabsensisupir->totalRows,
                'tradosupir' => $mandorabsensisupir->isTradoMilikSupir(),
                'defaultJenis' => $mandorabsensisupir->defaultJenis(),
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    // public function store(Request $request)
    public function store(MandorAbsensiSupirAllRequest $request,MandorAbsensiSupirAllSupirSerapRequest $request1)
    {
        $data = json_decode(request()->data, true);
        // dd('test');
        // dd(request()->deleted_id);
        // dd($data);
        // dd($data);
        if ($data == []) {
            goto selesai;
        }

        $deleted_id = request()->deleted_id ?? 0;

        // dd($request()->all());
        // 
      

        if ($deleted_id != 0) {
            $user = auth('api')->user()->name;
            $class = 'TemporaryAbsensiSupir';

            $temtabel = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel a with (readuncommitted)")
            )
                ->select(
                    'id',
                    'class',
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            if (isset($querydata)) {
                Schema::dropIfExists($querydata->namatabel);
                DB::table('listtemporarytabel')->where('id', $querydata->id)->delete();
            }

            DB::table('listtemporarytabel')->insert(
                [
                    'class' => $class,
                    'namatabel' => $temtabel,
                    'modifiedby' => $user,
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s'),
                ]
            );

            Schema::create($temtabel, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->string('nobukti')->nullable();
                $table->date('tglbukti')->nullable();
                $table->unsignedBigInteger('trado_id')->nullable();
                $table->unsignedBigInteger('supir_id')->nullable();
                $table->longText('keterangan')->nullable();
                $table->unsignedBigInteger('absen_id')->nullable();
                $table->unsignedBigInteger('statusjeniskendaraan')->nullable();
                $table->unsignedBigInteger('supirold_id')->nullable();
                $table->unsignedBigInteger('deleted_id')->nullable();
            });
        }
        // 
        DB::beginTransaction();
        try {

            $statusaktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'AKTIF')->first();
            foreach ($data as $key) {
                $insert = [
                    "tglbukti" => $key['tglbukti'],
                    "kasgantung_nobukti" => "",
                    "uangjalan" => [0],
                    "trado_id" => $key['trado_id'],
                    "supir_id" => $key['supir_id'],
                    "supirold_id" => $key['supirold_id'],
                    "keterangan" => $key['keterangan'],
                    "absen_id" => $key['absen_id'],
                    "statusjeniskendaraan" => $key['statusjeniskendaraan'],
                    "deleted_id" => $deleted_id,
                    "id" => $key['id'],
                    // "jam" => $key['jam'],
                ];

                $AbsensiSupirHeader = (new MandorAbsensiSupir())->processStore($insert);
            }
            $absensiTangki = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'ABSENSI TANGKI')->where('subgrp', 'ABSENSI TANGKI')->first();
            if ($absensiTangki->text == 'YA') {
                (new MandorAbsensiSupir())->processKasgantung($AbsensiSupirHeader->nobukti);
            }
            
            // dd($AbsensiSupirHeader->nobukti);
            // $user = auth('api')->user()->name;
            // $class = 'TemporaryAbsensiSupir';

            // $querydata = DB::table('listtemporarytabel')->from(
            //     DB::raw("listtemporarytabel with (readuncommitted)")
            // )
            //     ->select(
            //         'namatabel',
            //     )
            //     ->where('class', '=', $class)
            //     ->where('modifiedby', '=', $user)
            //     ->first();

            // $temtabel = $querydata->namatabel;
            // dd(db::table($temtabel)->get());
            // test


            // $data = [
            //     "tglbukti" =>$request->tglbukti,
            //     "kasgantung_nobukti" =>$request->kasgantung_nobukti,
            //     "uangjalan" =>[0],
            //     "trado_id" => $request->trado_id,
            //     "supir_id" => $request->supir_id,
            //     "keterangan" => $request->keterangan,
            //     "absen_id" => $request->absen_id,
            //     "jam" => $request->jam,
            // ];
            // $AbsensiSupirHeader = (new MandorAbsensiSupir())->processStore($data);
            // $AbsensiSupirHeader->position = $this->getPositionMandor($AbsensiSupirHeader->trado_id)->position;
            // if ($request->limit == 0) {
            //     $request->limit = DB::table('trado')->where('statusaktif',$statusaktif->id)->count();
            // }
            // $AbsensiSupirHeader->page = ceil($AbsensiSupirHeader->position / ($request->limit ?? 10));

            DB::commit();
            return response([
                'message' => 'Berhasil disimpan',
                'data' => $AbsensiSupirHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
        selesai:
        return response([
            'message' => 'Tidak Ada Data yang disimpan',
            // 'data' => $AbsensiSupirHeader
        ], 201);
    }
    public function store2(MandorAbsensiSupirRequest $request)
    {
        DB::beginTransaction();
        try {

            $statusaktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'AKTIF')->first();
            $data = [
                "tglbukti" => $request->tglbukti,
                "kasgantung_nobukti" => $request->kasgantung_nobukti,
                "uangjalan" => [0],
                "trado_id" => $request->trado_id,
                "supir_id" => $request->supir_id,
                "keterangan" => $request->keterangan,
                "absen_id" => $request->absen_id,
                "jam" => $request->jam,
            ];
            $AbsensiSupirHeader = (new MandorAbsensiSupir())->processStore($data);
            $AbsensiSupirHeader->position = $this->getPositionMandor($AbsensiSupirHeader->trado_id)->position;
            if ($request->limit == 0) {
                $request->limit = DB::table('trado')->where('statusaktif', $statusaktif->id)->count();
            }
            $AbsensiSupirHeader->page = ceil($AbsensiSupirHeader->position / ($request->limit ?? 10));

            DB::commit();
            return response([
                'message' => 'Berhasil disimpan',
                'data' => $AbsensiSupirHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function show($id)
    {

        $mandorabsensisupir = new MandorAbsensiSupir();
        $tglbukaabsensi = request()->tanggal ?? 'now';
        $supir_id = request()->supir_id ?? 0;
        $isTradoAbsen = $mandorabsensisupir->isAbsen($id, $tglbukaabsensi, $supir_id);
        if (!$isTradoAbsen) {
            $isTradoAbsen = $mandorabsensisupir->getTrado($id, $supir_id);
        }
        return response([
            'status' => true,
            'data' => $isTradoAbsen
        ]);
    }

    public function storeKasGantung($kasGantungHeader, $kasGantungDetail)
    {
        try {


            $kasGantung = new StoreKasGantungHeaderRequest($kasGantungHeader);
            $header = app(KasGantungHeaderController::class)->store($kasGantung);

            $nobukti = $kasGantungHeader['nobukti'];
            $detailLog = [];
            foreach ($kasGantungDetail as $value) {

                $value['kasgantung_id'] = $header->original['data']['id'];
                $value['pengeluaran_nobukti'] = $header->original['data']['pengeluaran_nobukti'];
                $kasGantungDetail = new StoreKasGantungDetailRequest($value);
                $datadetails = app(KasGantungDetailController::class)->store($kasGantungDetail);

                $detailLog[] = $datadetails['detail']->toArray();
            }
            $datalogtrail = [
                'namatabel' => strtoupper($datadetails['tabel']),
                'postingdari' => 'ENTRY ABSENSI SUPIR',
                'idtrans' =>  $header->original['idlogtrail'],
                'nobuktitrans' => $nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detailLog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);


            return [
                'status' => true
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(MandorAbsensiSupirRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $statusaktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'AKTIF')->first();
            $data = [
                "tglbukti" => $request->tglbukti,
                "kasgantung_nobukti" => $request->kasgantung_nobukti,
                "uangjalan" => [0],
                "trado_id" => $request->trado_id,
                "supir_id" => $request->supir_id,
                "keterangan" => $request->keterangan,
                "absen_id" => $request->absen_id,
                "statusjeniskendaraan" => $request->statusjeniskendaraan,
                "jam" => $request->jam,
            ];
            $AbsensiSupirDetail = AbsensiSupirDetail::findOrFail($id);
            $AbsensiSupirDetail = (new MandorAbsensiSupir())->processUpdate($AbsensiSupirDetail, $data);
            $AbsensiSupirDetail->position = $this->getPositionMandor($AbsensiSupirDetail->trado_id)->position;
            if ($request->limit == 0) {
                $request->limit = DB::table('trado')->where('statusaktif', $statusaktif->id)->count();
            }
            $AbsensiSupirDetail->page = ceil($AbsensiSupirDetail->position / ($request->limit ?? 10));

            DB::commit();
            return response([
                'message' => 'Berhasil disimpan',
                'data' => $AbsensiSupirDetail
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(MandorAbsensiSupirRequest $request, $id)
    {

        DB::beginTransaction();
        try {
            $AbsensiSupirDetail = AbsensiSupirDetail::findOrFail($id);
            $statusaktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'AKTIF')->first();


            $AbsensiSupirDetail = (new MandorAbsensiSupir())->processDestroy($AbsensiSupirDetail->id);
            // $AbsensiSupirDetail->position = $this->getPositionMandor(0,true)->position;
            // if ($request->limit == 0) {
            //     $request->limit = DB::table('trado')->where('statusaktif',$statusaktif->id)->count();
            // }
            // $AbsensiSupirDetail->page = ceil($AbsensiSupirDetail->position / ($request->limit ?? 10));

            DB::commit();
            return response([
                'message' => 'Berhasil disimpan',
                'data' => $AbsensiSupirDetail
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function cekValidasi(Request $request, $tradoId)
    {
        $now = date('Y-m-d', strtotime($request->tanggal));
        $supir_id = $request->supir_id;
        $getAbsen = AbsensiSupirHeader::from(DB::raw("absensisupirheader with (readuncommitted)"))->where('tglbukti', $now)->first();

        if ($getAbsen != null) {
            $cekAbsen = AbsensiSupirDetail::from(DB::raw("absensisupirdetail with (readuncommitted)"))->where('nobukti', $getAbsen->nobukti)->where('trado_id', $tradoId)->first();
            if ($cekAbsen != null) {

                return response([
                    'errors' => false
                ]);
            } else {
                $getError = Error::from(DB::raw("error with (readuncommitted)"))
                    ->select('keterangan')
                    ->where('kodeerror', '=', 'TAB')
                    ->first();

                return response([
                    'errors' => true,
                    'message' => $getError->keterangan
                ]);
            }
        }
        $getError = Error::from(DB::raw("error with (readuncommitted)"))
            ->select('keterangan')
            ->where('kodeerror', '=', 'TAB')
            ->first();

        return response([
            'errors' => true,
            'message' => $getError->keterangan
        ]);
    }

    public function cekValidasiAdd(Request $request, $tradoId)
    {
        $now = date('Y-m-d', strtotime($request->tanggal));
        $supir_id = $request->supir_id;

        // $now = date("Y-m-d");
        $getAbsen = AbsensiSupirHeader::from(DB::raw("absensisupirheader with (readuncommitted)"))->where('tglbukti', $now)->first();

        if ($getAbsen != null) {
            $cekAbsen = AbsensiSupirDetail::from(DB::raw("absensisupirdetail with (readuncommitted)"))->where('nobukti', $getAbsen->nobukti)->where('trado_id', $tradoId)->where('supir_id', $supir_id)->first();
            if ($cekAbsen != null) {
                $getError = Error::from(DB::raw("error with (readuncommitted)"))
                    ->select('keterangan')
                    ->where('kodeerror', '=', 'SPI')
                    ->first();

                return response([
                    'errors' => true,
                    'message' => 'ABSENSI ' . $getError->keterangan
                ]);
            } else {
                return response([
                    'errors' => false,
                ]);
            }
        } else {
            return response([
                'errors' => false,
            ]);
        }
    }

    public function getabsentrado($id)
    {

        $mandorabsensisupir = new MandorAbsensiSupir();
        return response([
            "data" => $mandorabsensisupir->getabsentrado($id)
        ]);
    }


    function getPositionMandor($trado_id, $isDeleting = false)
    {
        $data = new stdClass();
        $model = new MandorAbsensiSupir();
        $indexRow = request()->indexRow ?? 1;
        $limit = request()->limit ?? 10;
        $page = request()->page ?? 1;

        $temporaryTable = $model->createTemp('adas');
        if ($isDeleting) {
            if ($page == 1) {
                $position = $indexRow + 1;
            } else {
                $page = $page - 1;
                $row = $page * $limit;
                $position = $indexRow + $row + 1;
            }

            if (!DB::table($temporaryTable)->where('position', '=', $position)->exists()) {
                $position -= 1;
            }
            $query = DB::table($temporaryTable)
                ->select('position', 'id')
                ->where('position', '=', $position)
                ->orderBy('position');
        } else {
            $query = DB::table($temporaryTable)->select('position')->where('trado_id', $trado_id)->orderBy('position');
        }
        // dd($query->first());
        if ($query->first() == null) {
            $data->position = 0;
            $data->id = 0;
        } else {
            $data = $query->first();
        }
        return $data;
    }
}
