<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Models\PengeluaranDetail;
use App\Http\Requests\StorePengeluaranDetailRequest;
use App\Models\JurnalUmumHeader;
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
            $query = PengeluaranDetail::from('pengeluarandetail as detail');

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
                    'detail.alatbayar_id',
                    'detail.nobukti',
                    'detail.nowarkat',
                    'detail.tgljatuhtempo',
                    'detail.nominal',
                    'detail.keterangan',
                    'detail.bulanbeban',
                    'alatbayar.namaalatbayar as alatbayar_id',
                    'bank.namabank as bank_id',
                    'akunpusat.keterangancoa as coadebet',
                    'bank.coa as coakredit',

                )
                    ->leftJoin('alatbayar', 'alatbayar.id', '=', 'detail.alatbayar_id')
                    ->leftJoin('bank', 'bank.id', '=', 'detail.bank_id')
                    ->leftJoin('akunpusat', 'pengeluarandetail.coadebet', '=', 'akunpusat.coa')
                    ->leftjoin('bank', 'pengeluarandetail.coakredit', '=', 'bank.namabank');
                $pengeluaranDetail = $query->get();
            } else {
                $query->select(
                    'detail.pengeluaran_id',
                    'detail.nobukti',
                    'detail.nowarkat',
                    'detail.tgljatuhtempo',
                    'detail.nominal',
                    'detail.keterangan',
                    'detail.bulanbeban',
                    'alatbayar.namaalatbayar as alatbayar_id',
                    // 'coakredit.keterangancoa as coakredit',
                    // 'akunpusat.keterangancoa as coadebet',
                    'akunpusat.keterangancoa as coadebet',
                    'akunpusat.keterangancoa as coakredit',

                )
                    ->leftJoin('alatbayar', 'alatbayar.id', '=', 'detail.alatbayar_id')
                    // ->leftJoin('akunpusat', 'detail.coadebet', '=', 'akunpusat.id')
                    // ->leftJoin('akunpusat as coakredit', 'detail.coakredit', '=', 'coakredit.id');
                    ->leftJoin('akunpusat', 'detail.coadebet', '=', 'akunpusat.coa')
                    ->leftJoin('akunpusat as coakredit', 'detail.coakredit', '=', 'akunpusat.coa');

                $pengeluaranDetail = $query->get();
                // dd{$pengeluaranDetail};
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
        $validator = Validator::make($request->all(), [
            'nominal' => 'required',
        ], [
            'nominal.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
        ], [
            'nominal' => 'Nominal',
        ]);
        if (!$validator->passes()) {
            return [
                'error' => true,
                'errors' => $validator->messages()
            ];
        }

        try {
            $pengeluaranDetail = new PengeluaranDetail();
            $entriLuar = $request->entriluar ?? 0;

            $pengeluaranDetail->pengeluaran_id = $request->pengeluaran_id;
            $pengeluaranDetail->nobukti = $request->nobukti;
            $pengeluaranDetail->alatbayar_id = $request->alatbayar_id ?? '';
            $pengeluaranDetail->nowarkat = $request->nowarkat ?? '';
            $pengeluaranDetail->tgljatuhtempo = $request->tgljatuhtempo ?? '';
            $pengeluaranDetail->nominal = $request->nominal ?? '';
            $pengeluaranDetail->coadebet = $request->coadebet ?? '';
            $pengeluaranDetail->coakredit = $request->coakredit ?? '';
            $pengeluaranDetail->keterangan = $request->keterangan ?? '';
            $pengeluaranDetail->bulanbeban = $request->bulanbeban ?? '';
            $pengeluaranDetail->modifiedby = $request->modifiedby;
            $pengeluaranDetail->save();

            if($entriLuar == 1) {
                $nobukti = $pengeluaranDetail['nobukti'];
                $fetchId = JurnalUmumHeader::select('id','tglbukti')
                ->where('nobukti','=',$nobukti)
                ->first();
                $id = $fetchId->id;

                $getBaris = DB::table('jurnalumumdetail')->select('baris')->where('nobukti', $nobukti)->orderByDesc('baris')->first();
                if(is_null($getBaris)) {
                    $baris = 0;
                }else{
                    $baris = $getBaris->baris+1;
                }
                
                for ($x = 0; $x <= 1; $x++) {
                    if ($x == 1) {
                        $datadetail = [
                            'jurnalumum_id' => $id,
                            'nobukti' => $pengeluaranDetail->nobukti,
                            'tglbukti' => $fetchId->tglbukti,
                            'coa' =>  $pengeluaranDetail->coakredit,
                            'nominal' => -$pengeluaranDetail->nominal,
                            'keterangan' => $pengeluaranDetail->keterangan,
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $baris,
                        ];
                    } else {
                        $datadetail = [
                            'jurnalumum_id' => $id,
                            'nobukti' => $pengeluaranDetail->nobukti,
                            'tglbukti' => $fetchId->tglbukti,
                            'coa' =>  $pengeluaranDetail->coadebet,
                            'nominal' => $pengeluaranDetail->nominal,
                            'keterangan' => $pengeluaranDetail->keterangan,
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $baris,
                        ];
                    }
                    $detail = new StoreJurnalUmumDetailRequest($datadetail);
                    $tes = app(JurnalUmumDetailController::class)->store($detail); 
                }
            }

            DB::commit();
            if ($validator->passes()) {
                return [
                    'error' => false,
                    'id' => $pengeluaranDetail->id,
                    'tabel' => $pengeluaranDetail->getTable(),
                ];
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
