<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Models\PengeluaranDetail;
use App\Http\Requests\StorePengeluaranDetailRequest;
use App\Models\JurnalUmumHeader;
use App\Models\PengeluaranHeader;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\isNull;

class PengeluaranDetailController extends Controller
{
    /**
     * @ClassName
     */
    public function index(Request $request)
    {
        $pengeluaranDetail = new PengeluaranDetail();

        return response([
            'data' => $pengeluaranDetail->get(),
            'attributes' => [
                'totalRows' => $pengeluaranDetail->totalRows,
                'totalPages' => $pengeluaranDetail->totalPages,
                'totalNominal' => $pengeluaranDetail->totalNominal
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function getPengeluaran(): JsonResponse
    {
        $pengeluaranDetail = new PengeluaranDetail();
        if (request()->nobukti != 'false' && request()->nobukti != null) {
            $fetch = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))->where('nobukti', request()->nobukti)->first();
            request()->pengeluaran_id = $fetch->id;
            return response()->json([
                'data' => $pengeluaranDetail->get(request()->pengeluaran_id),
                'attributes' => [
                    'totalRows' => $pengeluaranDetail->totalRows,
                    'totalPages' => $pengeluaranDetail->totalPages,
                    'totalNominal' => $pengeluaranDetail->totalNominal
                ]
            ]);
        } else {
            return response()->json([
                'data' => [],
                'attributes' => [
                    'totalRows' => $pengeluaranDetail->totalRows,
                    'totalPages' => $pengeluaranDetail->totalPages,
                    'totalNominal' => 0
                ]
            ]);
        }
    }

    public function store(StorePengeluaranDetailRequest $request)
    {
        DB::beginTransaction();


        try {
            $pengeluaranDetail = new PengeluaranDetail();

            $pengeluaranDetail->pengeluaran_id = $request->pengeluaran_id;
            $pengeluaranDetail->nobukti = $request->nobukti;
            $pengeluaranDetail->nowarkat = $request->nowarkat ?? '';
            $pengeluaranDetail->tgljatuhtempo = $request->tgljatuhtempo ?? '';
            $pengeluaranDetail->nominal = $request->nominal ?? '';
            $pengeluaranDetail->coadebet = $request->coadebet ?? '';
            $pengeluaranDetail->coakredit = $request->coakredit ?? '';
            $pengeluaranDetail->keterangan = $request->keterangan ?? '';
            $pengeluaranDetail->bulanbeban = $request->bulanbeban ?? '';
            $pengeluaranDetail->modifiedby = $request->modifiedby;
            $pengeluaranDetail->save();

            $datadetail = $pengeluaranDetail;
            if ($request->entridetail == 1) {
                $nobukti = $pengeluaranDetail->nobukti;
                $getBaris = DB::table('jurnalumumdetail')->from(
                    DB::raw("jurnalumumdetail with (readuncommitted)")
                )->select('baris')->where('nobukti', $nobukti)->orderByDesc('baris')->first();




                if (is_null($getBaris)) {
                    $baris = 0;
                } else {
                    $baris = $getBaris->baris + 1;
                }
                $detailLogJurnal = [];
                for ($x = 0; $x <= 1; $x++) {

                    if ($x == 1) {
                        $jurnaldetail = [
                            'jurnalumum_id' => $request->jurnal_id,
                            'nobukti' => $nobukti,
                            'tglbukti' => $request->tglbukti,
                            'coa' =>  $pengeluaranDetail['coakredit'],
                            'nominal' => -$pengeluaranDetail['nominal'],
                            'keterangan' => $pengeluaranDetail['keterangan'],
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $baris,
                        ];
                    } else {
                        $jurnaldetail = [
                            'jurnalumum_id' => $request->jurnal_id,
                            'nobukti' => $nobukti,
                            'tglbukti' => $request->tglbukti,
                            'coa' =>  $pengeluaranDetail['coadebet'],
                            'nominal' => $pengeluaranDetail['nominal'],
                            'keterangan' => $pengeluaranDetail['keterangan'],
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $baris,
                        ];
                    }
                    $detail = new StoreJurnalUmumDetailRequest($jurnaldetail);
                    $detailJurnal = app(JurnalUmumDetailController::class)->store($detail);


                    $detailLogJurnal[] = $detailJurnal['detail']->toArray();
                }

                $datadetail = [];
                $datadetail = [
                    'pengeluarandetail' => $pengeluaranDetail,
                    'jurnaldetail' => $detailLogJurnal
                ];
            }

            DB::commit();
            return [
                'error' => false,
                'detail' => $datadetail,
                'id' => $pengeluaranDetail->id,
                'tabel' => $pengeluaranDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
