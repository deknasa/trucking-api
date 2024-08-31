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
use App\Models\GajisUpirUangJalan;
use App\Models\JurnalUmumDetail;
use App\Models\PenerimaanTruckingHeader;
use App\Models\PengembalianKasGantungDetail;
use App\Models\PengembalianKasGantungHeader;
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
     * @Keterangan TAMPILKAN DATA
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
                'totalUangMakanBerjenjang' => $prosesGajiSupir->totalUangMakanBerjenjang,
                'totalBiayaExtraHeader' => $prosesGajiSupir->totalBiayaExtraHeader,
                'totalPinjaman' => $prosesGajiSupir->totalPinjaman,
                'totalPinjamanSemua' => $prosesGajiSupir->totalPinjamanSemua,
                'totalDeposito' => $prosesGajiSupir->totalDeposito,
                'totalKomisi' => $prosesGajiSupir->totalKomisi,
                'totalGajiSupir' => $prosesGajiSupir->totalGajiSupir,
                'totalGajiKenek' => $prosesGajiSupir->totalGajiKenek,
                'totalBiayaExtra' => $prosesGajiSupir->totalBiayaExtra,
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
                ->join(DB::raw("gajisupirheader with (readuncommitted)"), 'gajisupirpelunasanpinjaman.gajisupir_nobukti', 'gajisupirheader.nobukti')
                ->whereRaw("gajisupir_nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where nobukti='$nobuktiEbs')")
                ->where('gajisupirpelunasanpinjaman.supir_id', '0')
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
                ->join(DB::raw("gajisupirheader with (readuncommitted)"), 'gajisupirpelunasanpinjaman.gajisupir_nobukti', 'gajisupirheader.nobukti')
                ->whereRaw("gajisupir_nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where nobukti='$nobuktiEbs')")
                ->where('gajisupirpelunasanpinjaman.supir_id', '!=', '0')
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
                ->join(DB::raw("gajisupirheader with (readuncommitted)"), 'gajisupirdeposito.gajisupir_nobukti', 'gajisupirheader.nobukti')
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
                ->join(DB::raw("gajisupirheader with (readuncommitted)"), 'gajisupirbbm.gajisupir_nobukti', 'gajisupirheader.nobukti')
                ->whereRaw("gajisupir_nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where nobukti='$nobuktiEbs')")
                ->first();
            if ($fetch != null) {
                $penerimaantrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $fetch->penerimaantrucking_nobukti)
                    ->first();
                request()->nobukti = $penerimaantrucking->penerimaan_nobukti;
            }
        }

        if (request()->tab == 'ebs') {

            $fetch = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))
                ->join(DB::raw("gajisupirheader with (readuncommitted)"), 'gajisupirbbm.gajisupir_nobukti', 'gajisupirheader.nobukti')
                ->whereRaw("gajisupir_nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where nobukti='$nobuktiEbs')")
                ->first();
            if ($fetch != null) {
                $penerimaantrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $fetch->penerimaantrucking_nobukti)
                    ->first();
                request()->nobukti = $penerimaantrucking->penerimaan_nobukti;
            }
        }

        if (request()->tab == 'pengembalian') {

            $isTangki = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'ABSENSI TANGKI')->first()->text ?? 'TIDAK';
            if ($isTangki == 'YA') {

                $fetch = GajisUpirUangJalan::from(DB::raw("gajisupiruangjalan with (readuncommitted)"))
                    ->select(DB::raw("gajisupiruangjalan.kasgantung_nobukti,kasgantungheader.coakaskeluar, sum(gajisupiruangjalan.nominal) as nominal"))
                    ->join(DB::raw("kasgantungheader with (readuncommitted)"), 'gajisupiruangjalan.kasgantung_nobukti', 'kasgantungheader.nobukti')
                    ->join(DB::raw("gajisupirheader with (readuncommitted)"), 'gajisupiruangjalan.gajisupir_nobukti', 'gajisupirheader.nobukti')
                    ->join(DB::raw("pengembaliankasgantungdetail a with (readuncommitted)"), 'kasgantungheader.nobukti', 'a.kasgantung_nobukti')
                    ->whereRaw("gajisupiruangjalan.gajisupir_nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where nobukti='$nobuktiEbs')")
                    ->groupBy('gajisupiruangjalan.kasgantung_nobukti', 'kasgantungheader.coakaskeluar')
                    ->first();
            } else {

                $fetch = GajisUpirUangJalan::from(DB::raw("gajisupiruangjalan with (readuncommitted)"))
                    ->select(DB::raw("absensisupirheader.kasgantung_nobukti,kasgantungheader.coakaskeluar, sum(gajisupiruangjalan.nominal) as nominal"))
                    ->join(DB::raw("absensisupirheader with (readuncommitted)"), 'gajisupiruangjalan.absensisupir_nobukti', 'absensisupirheader.nobukti')
                    ->join(DB::raw("kasgantungheader with (readuncommitted)"), 'absensisupirheader.kasgantung_nobukti', 'kasgantungheader.nobukti')
                    ->join(DB::raw("gajisupirheader with (readuncommitted)"), 'gajisupiruangjalan.gajisupir_nobukti', 'gajisupirheader.nobukti')
                    ->join(DB::raw("pengembaliankasgantungdetail a with (readuncommitted)"), 'kasgantungheader.nobukti', 'a.kasgantung_nobukti')
                    ->whereRaw("gajisupiruangjalan.gajisupir_nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where nobukti='$nobuktiEbs')")
                    ->groupBy('absensisupirheader.kasgantung_nobukti', 'kasgantungheader.coakaskeluar')
                    ->first();
                // dd($fetch);

            }
            if ($fetch != null) {
                // $penerimaantrucking = PengembalianKasGantungHeader::from(DB::raw("pengembaliankasgantungheader with (readuncommitted)"))
                //     ->leftJoin(DB::raw("pengembaliankasgantungdetail with (readuncommitted)"), 'pengembaliankasgantungheader.id', 'pengembaliankasgantungdetail.pengembaliankasgantung_id')
                //     ->where('pengembaliankasgantungdetail.kasgantung_nobukti', $fetch->kasgantung_nobukti)
                //     ->first();
                $penerimaantrucking = db::table("prosesgajisupirheader")->from(db::raw("prosesgajisupirheader with (readuncommitted)"))
                    ->select('pengembaliankasgantungheader.penerimaan_nobukti', 'pengembaliankasgantungheader.bank_id', 'bank.namabank')
                    ->join(db::raw("pengembaliankasgantungheader with (readuncommitted)"), 'prosesgajisupirheader.pengembaliankasgantung_nobukti', 'pengembaliankasgantungheader.nobukti')
                    ->join(db::raw("bank with (readuncommitted)"), 'pengembaliankasgantungheader.bank_id', 'bank.id')
                    ->where('prosesgajisupirheader.nobukti', $nobuktiEbs)
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
