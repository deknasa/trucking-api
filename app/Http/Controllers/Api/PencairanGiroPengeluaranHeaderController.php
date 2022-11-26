<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLogTrailRequest;
use App\Models\PencairanGiroPengeluaranHeader;
use App\Http\Requests\StorePencairanGiroPengeluaranHeaderRequest;
use App\Http\Requests\UpdatePencairanGiroPengeluaranHeaderRequest;
use App\Models\Parameter;
use App\Models\PengeluaranDetail;
use App\Models\PengeluaranHeader;
use Illuminate\Support\Facades\DB;

class PencairanGiroPengeluaranHeaderController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $pencairanGiro = new PencairanGiroPengeluaranHeader();

        return response([
            'data' => $pencairanGiro->get(),
            'attributes' => [
                'totalRows' => $pencairanGiro->totalRows,
                'totalPages' => $pencairanGiro->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StorePencairanGiroPengeluaranHeaderRequest $request)
    {
        DB::BeginTransaction();
        try {

            $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

            for ($i = 0; $i < count($request->pengeluaranId); $i++) {
                $pencairanGiro = new PencairanGiroPengeluaranHeader();

                $pengeluaran = PengeluaranHeader::select('nobukti')->where('id', $request->pengeluaranId[$i])->first();

                $pencairanGiro->tglbukti = $request->tglbukti;
                $pencairanGiro->keterangan = $request->keterangan;
                $pencairanGiro->pengeluaran_nobukti = $pengeluaran->nobukti;
                $pencairanGiro->statusapproval = $statusApproval->id;
                $pencairanGiro->userapproval = '';
                $pencairanGiro->tglapproval = '';
                $pencairanGiro->modifiedby = auth('api')->user()->name;

                $pencairanGiro->save();

                $logTrail = [
                    'namatabel' => strtoupper($pencairanGiro->getTable()),
                    'postingdari' => 'ENTRY PENCAIRAN GIRO PENGELUARAN HEADER',
                    'idtrans' => $pencairanGiro->id,
                    'nobuktitrans' => $pencairanGiro->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $pencairanGiro->toArray(),
                    'modifiedby' => $pencairanGiro->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                // STORE DETAIL

                $pengeluaranDetail = PengeluaranDetail::where('pengeluaran_id',$request->pengeluaranId[$i])->get();

                foreach ($pengeluaranDetail as $index => $value) {
                    $datadetail = [
                        'pencairangiropengeluaran_id' => $jurnalUmumPusat->id,
                        'nobukti' => $jurnalUmumPusat->nobukti,
                        'tglbukti' => $jurnalUmumPusat->tglbukti,
                        'coa' => $value->coa,
                        'nominal' => $value->nominal,
                        'keterangan' => $value->keterangan,
                        'modifiedby' => $jurnalUmumPusat->modifiedby,
                        'baris' => $value->baris,
                    ];

                    //STORE 
                    $data = new StoreJurnalUmumPusatDetailRequest($datadetail);

                    $datadetails = app(JurnalUmumPusatDetailController::class)->store($data);

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }

                    $datadetaillog = [
                        'id' => $iddetail,
                        'jurnalumumpusat_id' => $jurnalUmumPusat->id,
                        'nobukti' => $jurnalUmumPusat->nobukti,
                        'tglbukti' => $jurnalUmumPusat->tglbukti,
                        'coa' => $value->coa,
                        'nominal' => $value->nominal,
                        'keterangan' => $value->keterangan,
                        'modifiedby' => $jurnalUmumPusat->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($jurnalUmumPusat->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($jurnalUmumPusat->updated_at)),
                        'baris' => $value->baris,
                    ];

                    $detaillog[] = $datadetaillog;

                    $datalogtrail = [
                        'namatabel' => $tabeldetail,
                        'postingdari' => 'ENTRY JURNAL UMUM PUSAT DETAIL',
                        'idtrans' =>  $iddetail,
                        'nobuktitrans' => $jurnalUmumPusat->nobukti,
                        'aksi' => 'ENTRY',
                        'datajson' => $detaillog,
                        'modifiedby' => $request->modifiedby,
                    ];

                    $data = new StoreLogTrailRequest($datalogtrail);
                    app(LogTrailController::class)->store($data);
                }
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
    }


    public function show(PencairanGiroPengeluaranHeader $pencairanGiroPengeluaranHeader)
    {
        //
    }

    /**
     * @ClassName
     */
    public function destroy(PencairanGiroPengeluaranHeader $pencairanGiroPengeluaranHeader)
    {
        //
    }
}
