<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Models\PengeluaranDetail;
use App\Http\Requests\StorePengeluaranDetailRequest;
use App\Models\JurnalUmumHeader;
use App\Models\User;
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
        $params = [
            'id' => $request->id,
            'pengeluaran_id' => $request->pengeluaran_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];
        try {

           
            $query = PengeluaranDetail::from(DB::raw("pengeluarandetail as detail with (readuncommitted)"));

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['pengeluaran_id'])) {
                $query->where('detail.pengeluaran_id', $params['pengeluaran_id']);
            }

            if ($params['withHeader']) {
                $query->join('pengeluaranheader', 'pengeluaranheader.id', 'detail.pengeluaran_id');
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('pengeluaran_id', $params['whereIn']);
            }

            if ($params['forReport']) {
                $query->select(
                    'header.nobukti',
                    'header.tglbukti',
                    'header.dibayarke',
                    'header.transferkeac',
                    'header.transferkean',
                    'header.transferkebank',
                    'pelanggan.namapelanggan as pelanggan',
                    'bank.namabank as bank',
                    'detail.nowarkat',
                    'detail.tgljatuhtempo',
                    'detail.nominal',
                    'detail.keterangan',
                    DB::raw("(case when year(isnull(detail.bulanbeban,'1900/1/1'))=1900 then null else detail.bulanbeban end) as bulanbeban"),
                    'detail.coadebet',
                    'detail.coakredit',
                    'alatbayar.namaalatbayar as alatbayar_id'

                )
                    ->leftJoin(DB::raw("pengeluaranheader as header with (readuncommitted)"), 'header.id', 'detail.pengeluaran_id')
                    ->leftJoin(DB::raw("bank with (readuncommitted)"), 'bank.id', '=', 'header.bank_id')
                    ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'pelanggan.id', '=', 'header.pelanggan_id')
                    ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'alatbayar.id', '=', 'header.alatbayar_id');

                $pengeluaranDetail = $query->get();
            } else {

                $query->select(
                    'detail.pengeluaran_id',
                    'detail.nobukti',
                    'detail.nowarkat',
                    'detail.nominal',
                    'detail.keterangan',
                    DB::raw("(case when year(isnull(detail.bulanbeban,'1900/1/1'))<2000 then null else detail.bulanbeban end) as bulanbeban"),
                    DB::raw("(case when year(isnull(detail.tgljatuhtempo,'1900/1/1'))<2000 then null else detail.tgljatuhtempo end) as tgljatuhtempo"),
                    'debet.keterangancoa as coadebet',
                    'kredit.keterangancoa as coakredit',

                )
                ->leftJoin(DB::raw("akunpusat as debet with (readuncommitted)"), 'detail.coadebet', 'debet.coa')
                ->leftJoin(DB::raw("akunpusat as kredit with (readuncommitted)"), 'detail.coakredit', 'kredit.coa');
                $pengeluaranDetail = $query->get();
                //  dd($pengeluaranDetail);
            }
      

            return response([
                'data' => $pengeluaranDetail
            ]);
        } catch (\Throwable $th) {
            throw $th;
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
