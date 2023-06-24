<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\MandorAbsensiSupir;
use App\Models\AbsensiSupirHeader;
use App\Models\AbsensiSupirDetail;
use App\Models\Trado;
use App\Models\Parameter;
use App\Http\Requests\StoreAbsensiSupirDetailRequest;
use App\Http\Requests\StoreKasGantungDetailRequest;
use App\Http\Requests\StoreKasGantungHeaderRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\MandorAbsensiSupirRequest;


use App\Models\Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MandorAbsensiSupirController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $mandorabsensisupir = new MandorAbsensiSupir();
        return response([
            'data' => $mandorabsensisupir->get(),
            'attributes' => [
                'total' => $mandorabsensisupir->totalPages,
                'records' => $mandorabsensisupir->totalRows,
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(MandorAbsensiSupirRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = [
                "tglbukti" =>$request->tglbukti,
                "kasgantung_nobukti" =>$request->kasgantung_nobukti,
                "uangjalan" =>[0],
                "trado_id" => $request->trado_id,
                "supir_id" => $request->supir_id,
                "keterangan" => $request->keterangan,
                "absen_id" => $request->absen_id,
                "jam" => $request->jam,
            ];
            $AbsensiSupirHeader = (new MandorAbsensiSupir())->processStore($data);
            // $AbsensiSupirHeader->position = $this->getPosition($AbsensiSupirHeader, $AbsensiSupirHeader->getTable())->position;
            // $AbsensiSupirHeader->page = ceil($AbsensiSupirHeader->position / ($request->limit ?? 10));
            // if (isset($request->limit)) {
            //     $AbsensiSupirHeader->page = ceil($AbsensiSupirHeader->position / ($request->limit ?? 10));
            // }        
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
        $isTradoAbsen = $mandorabsensisupir->isAbsen($id);
        if (!$isTradoAbsen) {
            $isTradoAbsen = $mandorabsensisupir->getTrado($id);
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
     */
    public function update(MandorAbsensiSupirRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = [
                "tglbukti" =>$request->tglbukti,
                "kasgantung_nobukti" =>$request->kasgantung_nobukti,
                "uangjalan" =>[0],
                "trado_id" => $request->trado_id,
                "supir_id" => $request->supir_id,
                "keterangan" => $request->keterangan,
                "absen_id" => $request->absen_id,
                "jam" => $request->jam,
            ];
            $AbsensiSupirDetail = AbsensiSupirDetail::findOrFail($id);
            $AbsensiSupirDetail = (new MandorAbsensiSupir())->processUpdate($AbsensiSupirDetail,$data);
            // $AbsensiSupirHeader->position = $this->getPosition($AbsensiSupirHeader, $AbsensiSupirHeader->getTable())->position;
            // $AbsensiSupirHeader->page = ceil($AbsensiSupirHeader->position / ($request->limit ?? 10));
            // if (isset($request->limit)) {
            //     $AbsensiSupirHeader->page = ceil($AbsensiSupirHeader->position / ($request->limit ?? 10));
            // }        
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
     */
    public function destroy(MandorAbsensiSupirRequest $request, $id)
    {

        DB::beginTransaction();
        try {
            $AbsensiSupirDetail = AbsensiSupirDetail::findOrFail($id);
            
            $AbsensiSupirDetail = (new MandorAbsensiSupir())->processDestroy($AbsensiSupirDetail->id);
            // $AbsensiSupirHeader->position = $this->getPosition($AbsensiSupirHeader, $AbsensiSupirHeader->getTable())->position;
            // $AbsensiSupirHeader->page = ceil($AbsensiSupirHeader->position / ($request->limit ?? 10));
            // if (isset($request->limit)) {
            //     $AbsensiSupirHeader->page = ceil($AbsensiSupirHeader->position / ($request->limit ?? 10));
            // }        
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

    public function cekValidasi($tradoId)
    {

        $now = date("Y-m-d");
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
    }
    
    public function cekValidasiAdd($tradoId)
    {

        $now = date("Y-m-d");
        $getAbsen = AbsensiSupirHeader::from(DB::raw("absensisupirheader with (readuncommitted)"))->where('tglbukti', $now)->first();
        
        if ($getAbsen != null) {
            $cekAbsen = AbsensiSupirDetail::from(DB::raw("absensisupirdetail with (readuncommitted)"))->where('nobukti', $getAbsen->nobukti)->where('trado_id', $tradoId)->first();
            if ($cekAbsen != null) {
                $getError = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->where('kodeerror', '=', 'SPI')
                ->first();

                return response([
                    'errors' => true,
                    'message' => 'ABSENSI '.$getError->keterangan
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

}
