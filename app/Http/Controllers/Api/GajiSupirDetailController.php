<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GajiSupirDetail;
use App\Http\Requests\StoreGajiSupirDetailRequest;
use App\Http\Requests\UpdateGajiSupirDetailRequest;
use App\Models\AbsensiSupirDetail;
use App\Models\GajiSupirBBM;
use App\Models\GajiSupirDeposito;
use App\Models\GajiSupirPelunasanPinjaman;
use App\Models\JurnalUmumDetail;
use App\Models\PenerimaanTruckingDetail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GajiSupirDetailController extends Controller
{
    /**
     * @ClassName
     */
    public function index(): JsonResponse
    {
        $gajiSupir = new GajiSupirDetail();
        //($gajiSupir->get());
        return response()->json([
            'data' => $gajiSupir->get(),
            'attributes' => [
                'totalRows' => $gajiSupir->totalRows,
                'totalPages' => $gajiSupir->totalPages,
                'totalGajiSupir' => $gajiSupir->totalGajiSupir,
                'totalGajiKenek' => $gajiSupir->totalGajiKenek,
                'totalKomisiSupir' => $gajiSupir->totalKomisiSupir,
                'totalUpahRitasi' => $gajiSupir->totalUpahRitasi,
                'totalBiayaExtra' => $gajiSupir->totalBiayaExtra,
                'totalTolSupir' => $gajiSupir->totalTolSupir,
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function potSemua(): JsonResponse
    {
        $potSemua = new PenerimaanTruckingDetail();

        if (request()->nobukti != 'false' && request()->nobukti != null) {
            return response()->json([
                'data' => $potSemua->getPotSemua(request()->nobukti),
                'attributes' => [
                    'totalRows' => $potSemua->totalRows,
                    'totalPages' => $potSemua->totalPages,
                    'totalNominalPotSemua' => $potSemua->totalNominalPotSemua
                ]
            ]);
        } else {
            return response()->json([
                'data' => [],
                'attributes' => [
                    'totalRows' => $potSemua->totalRows,
                    'totalPages' => $potSemua->totalPages,
                    'totalNominalPotSemua' => 0
                ]
            ]);
        }
    }

    /**
     * @ClassName
     */
    public function potPribadi(): JsonResponse
    {
        $potPribadi = new PenerimaanTruckingDetail();

        if (request()->nobukti != 'false' && request()->nobukti != null) {
            return response()->json([
                'data' => $potPribadi->getPotPribadi(request()->nobukti),
                'attributes' => [
                    'totalRows' => $potPribadi->totalRows,
                    'totalPages' => $potPribadi->totalPages,
                    'totalNominalPotPribadi' => $potPribadi->totalNominalPotPribadi
                ]
            ]);
        } else {
            return response()->json([
                'data' => [],
                'attributes' => [
                    'totalRows' => $potPribadi->totalRows,
                    'totalPages' => $potPribadi->totalPages,
                    'totalNominalPotPribadi' => 0
                ]
            ]);
        }
    }

    /**
     * @ClassName
     */
    public function deposito(): JsonResponse
    {
        $deposito = new PenerimaanTruckingDetail();

        if (request()->nobukti != 'false' && request()->nobukti != null) {
            return response()->json([
                'data' => $deposito->getDeposito(request()->nobukti),
                'attributes' => [
                    'totalRows' => $deposito->totalRows,
                    'totalPages' => $deposito->totalPages,
                    'totalNominalDeposito' => $deposito->totalNominalDeposito
                ]
            ]);
        } else {
            return response()->json([
                'data' => [],
                'attributes' => [
                    'totalRows' => $deposito->totalRows,
                    'totalPages' => $deposito->totalPages,
                    'totalNominalDeposito' => 0
                ]
            ]);
        }
    }

    /**
     * @ClassName
     */
    public function jurnalBBM(): JsonResponse
    {
        $jurnalDetail = new JurnalUmumDetail();

        $fetch = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))->where('gajisupir_nobukti', request()->nobukti)->first();

        if ($fetch != null) {

            return response()->json([
                'data' => $jurnalDetail->getJurnalFromAnotherTable($fetch->penerimaantrucking_nobukti),
                'attributes' => [
                    'totalRows' => $jurnalDetail->totalRows,
                    'totalPages' => $jurnalDetail->totalPages,
                    'totalNominalDebet' => $jurnalDetail->totalNominalDebet,
                    'totalNominalKredit' => $jurnalDetail->totalNominalKredit,
                ]
            ]);
        } else {

            return response()->json([
                'data' => [],
                'attributes' => [
                    'totalRows' => $jurnalDetail->totalRows,
                    'totalPages' => $jurnalDetail->totalPages,
                    'totalNominalDebet' => 0,
                    'totalNominalKredit' => 0,
                ]
            ]);
        }
    }

    /**
     * @ClassName
     */
    public function absensi(): JsonResponse
    {
        $absensi = new AbsensiSupirDetail();

        if (request()->nobukti != 'false' && request()->nobukti != null) {
            return response()->json([
                'data' => $absensi->getAbsensiUangJalan(request()->nobukti),
                'attributes' => [
                    'totalRows' => $absensi->totalRows,
                    'totalPages' => $absensi->totalPages,
                    'totalUangJalan' => $absensi->totalUangJalan
                ]
            ]);
        } else {
            return response()->json([
                'data' => [],
                'attributes' => [
                    'totalRows' => $absensi->totalRows,
                    'totalPages' => $absensi->totalPages,
                    'totalUangJalan' => 0
                ]
            ]);
        }
    }
    public function store(StoreGajiSupirDetailRequest $request)
    {
        $gajisupirdetail = new GajiSupirDetail();

        $gajisupirdetail->gajisupir_id = $request->gajisupir_id;
        $gajisupirdetail->nobukti = $request->nobukti;
        $gajisupirdetail->nominaldeposito = $request->nominaldeposito;
        $gajisupirdetail->nourut = $request->nourut;
        $gajisupirdetail->suratpengantar_nobukti = $request->suratpengantar_nobukti;
        $gajisupirdetail->ritasi_nobukti = $request->ritasi_nobukti;
        $gajisupirdetail->komisisupir = $request->komisisupir;
        $gajisupirdetail->tolsupir = $request->tolsupir;
        $gajisupirdetail->voucher = $request->voucher;
        $gajisupirdetail->novoucher = $request->novoucher;
        $gajisupirdetail->gajisupir = $request->gajisupir;
        $gajisupirdetail->gajikenek = $request->gajikenek;
        $gajisupirdetail->gajiritasi = $request->gajiritasi;
        $gajisupirdetail->biayatambahan = $request->biayatambahan;
        $gajisupirdetail->keteranganbiayatambahan = $request->keteranganbiayatambahan;
        $gajisupirdetail->nominalpengembalianpinjaman = $request->nominalpengembalianpinjaman;

        $gajisupirdetail->modifiedby = auth('api')->user()->name;

        if (!$gajisupirdetail->save()) {
            throw new \Exception("Gagal menyimpan gaji supir detail.");
        }

        return [
            'error' => false,
            'detail' => $gajisupirdetail,
            'id' => $gajisupirdetail->id,
            'tabel' => $gajisupirdetail->getTable(),
        ];
    }
}
