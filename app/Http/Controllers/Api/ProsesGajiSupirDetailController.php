<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GajiSupirDetail;
use App\Http\Requests\StoreGajiSupirDetailRequest;
use App\Http\Requests\StoreProsesGajiSupirDetailRequest;
use App\Http\Requests\UpdateGajiSupirDetailRequest;
use App\Models\GajiSupirBBM;
use App\Models\GajiSupirDeposito;
use App\Models\GajiSupirPelunasanPinjaman;
use App\Models\JurnalUmumDetail;
use App\Models\PenerimaanTruckingHeader;
use App\Models\ProsesGajiSupirDetail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProsesGajiSupirDetailController extends Controller
{
    /**
     * @ClassName 
     */
    public function index(): JsonResponse
    {
        $prosesGajiSupir = new ProsesGajiSupirDetail();

        return response()->json([
            'data' => $prosesGajiSupir->get(),
            'attributes' => [
                'totalRows' => $prosesGajiSupir->totalRows,
                'totalPages' => $prosesGajiSupir->totalPages,
                'totalNominal' => $prosesGajiSupir->totalNominal,
                'totalUangJalan' => $prosesGajiSupir->totalUangJalan,
                'totalBBM' => $prosesGajiSupir->totalBBM,
                'totalUangMakan' => $prosesGajiSupir->totalUangMakan,
                'totalPinjaman' => $prosesGajiSupir->totalPinjaman,
                'totalPinjamanSemua' => $prosesGajiSupir->totalPinjamanSemua,
                'totalDeposito' => $prosesGajiSupir->totalDeposito,
                'totalKomisi' => $prosesGajiSupir->totalKomisi,
                'totalTol' => $prosesGajiSupir->totalTol,

            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function getJurnal(): JsonResponse
    {
        $jurnalDetail = new JurnalUmumDetail();
        $nobuktiEbs = request()->nobukti;
        if (request()->tab == 'potsemua') {

            $fetch = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
                ->whereRaw("gajisupir_nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where nobukti='$nobuktiEbs')")
                ->where('supir_id', '0')
                ->first();
            if ($fetch != null) {
                $penerimaantrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $fetch->penerimaantrucking_nobukti)
                    ->first();
                request()->nobukti = $penerimaantrucking->penerimaan_nobukti;
            }
        }
        if (request()->tab == 'potpribadi') {

            $fetch = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
                ->whereRaw("gajisupir_nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where nobukti='$nobuktiEbs')")
                ->where('supir_id', '!=', '0')
                ->first();
            if ($fetch != null) {
                $penerimaantrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $fetch->penerimaantrucking_nobukti)
                    ->first();
                request()->nobukti = $penerimaantrucking->penerimaan_nobukti;
            }
        }
        if (request()->tab == 'deposito') {

            $fetch = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))
                ->whereRaw("gajisupir_nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where nobukti='$nobuktiEbs')")
                ->first();
            if ($fetch != null) {
                $penerimaantrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $fetch->penerimaantrucking_nobukti)
                    ->first();
                request()->nobukti = $penerimaantrucking->penerimaan_nobukti;
            }
        }

        if (request()->tab == 'bbm') {

            $fetch = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))
                ->whereRaw("gajisupir_nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where nobukti='$nobuktiEbs')")
                ->first();
            if ($fetch != null) {
                $penerimaantrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $fetch->penerimaantrucking_nobukti)
                    ->first();
                request()->nobukti = $penerimaantrucking->penerimaan_nobukti;
            }
        }

        if ($fetch != null) {

            return response()->json([
                'data' => $jurnalDetail->getJurnalFromAnotherTable(request()->nobukti),
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

    public function store(StoreProsesGajiSupirDetailRequest $request)
    {
        DB::beginTransaction();
        try {
            $gajisupirdetail = new ProsesGajiSupirDetail();

            $gajisupirdetail->prosesgajisupir_id = $request->prosesgajisupir_id;
            $gajisupirdetail->nobukti = $request->nobukti;
            $gajisupirdetail->gajisupir_nobukti = $request->gajisupir_nobukti;
            $gajisupirdetail->supir_id = $request->supir_id;
            $gajisupirdetail->trado_id = $request->trado_id;
            $gajisupirdetail->nominal = $request->nominal;
            $gajisupirdetail->keterangan = $request->keterangan;

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
