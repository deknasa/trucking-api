<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJurnalUmumPusatDetailRequest;
use App\Models\JurnalUmumPusatHeader;
use App\Http\Requests\StoreJurnalUmumPusatHeaderRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateJurnalUmumPusatHeaderRequest;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\JurnalUmumPusatDetail;
use App\Models\LogTrail;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JurnalUmumPusatHeaderController extends Controller
{
    /**
     * @ClassName 
     * JurnalUmumPusatHeader
     * @Detail1 JurnalUmumPusatDetailController
     * @Keterangan TAMPILKAN DATA
     */

    public function index()
    {
        $jurnalUmumPusat = new JurnalUmumPusatHeader();
        return response([
            'data' => $jurnalUmumPusat->get(),
            'attributes' => [
                'totalRows' => $jurnalUmumPusat->totalRows,
                'totalPages' => $jurnalUmumPusat->totalPages
            ]
        ]);
    }

    public function importdatacabang()
    {
        // dd('test');
        $jurnalUmumPusat = new JurnalUmumPusatHeader();
        return response([
            'data' => $jurnalUmumPusat->getimportdatacabang(),
            'attributes' => [
                'totalRows' => $jurnalUmumPusat->totalRows,
                'totalPages' => $jurnalUmumPusat->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreJurnalUmumPusatHeaderRequest $request)
    {
        DB::BeginTransaction();
        try {

            $statusApp = Parameter::where('id', $request->approve)->first();
            if ($request->approve == 3) {

                for ($i = 0; $i < count($request->jurnalId); $i++) {

                    $jurnalUmumPusat = new JurnalUmumPusatHeader();
                    $get = JurnalUmumHeader::where('id', $request->jurnalId[$i])->first();

                    $jurnalUmumPusat->nobukti = $get->nobukti;
                    $jurnalUmumPusat->tglbukti = $get->tglbukti;
                    $jurnalUmumPusat->postingdari = $get->postingdari;
                    $jurnalUmumPusat->statusapproval = $request->approve;
                    $jurnalUmumPusat->userapproval = auth('api')->user()->name;
                    $jurnalUmumPusat->tglapproval = date('Y-m-d H:i:s');
                    $jurnalUmumPusat->statusformat = $get->statusformat;
                    $jurnalUmumPusat->modifiedby = auth('api')->user()->name;

                    $jurnalUmumPusat->save();

                    $logTrail = [
                        'namatabel' => strtoupper($jurnalUmumPusat->getTable()),
                        'postingdari' => 'ENTRY JURNAL UMUM PUSAT HEADER',
                        'idtrans' => $jurnalUmumPusat->id,
                        'nobuktitrans' => $jurnalUmumPusat->nobukti,
                        'aksi' => 'ENTRY',
                        'datajson' => $jurnalUmumPusat->toArray(),
                        'modifiedby' => $jurnalUmumPusat->modifiedby
                    ];

                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                    $jurnalApprove = JurnalUmumHeader::lockForUpdate()->findOrFail($request->jurnalId[$i]);
                    $jurnalApprove->statusapproval = $request->approve;
                    $jurnalApprove->userapproval = auth('api')->user()->name;
                    $jurnalApprove->tglapproval = date('Y-m-d H:i:s');

                    $jurnalApprove->save();

                    $logTrail = [
                        'namatabel' => strtoupper($jurnalApprove->getTable()),
                        'postingdari' => 'APPROVED JURNAL',
                        'idtrans' => $jurnalApprove->id,
                        'nobuktitrans' => $jurnalApprove->nobukti,
                        'aksi' => $statusApp->text,
                        'datajson' => $jurnalApprove->toArray(),
                        'modifiedby' => auth('api')->user()->name
                    ];

                    $validatedlogTrail = new StoreLogTrailRequest($logTrail);
                    app(LogTrailController::class)->store($validatedlogTrail);

                    /* Store detail */
                    $detaillog = [];

                    $jurnalDetail = JurnalUmumDetail::where('jurnalumum_id', $request->jurnalId[$i])->get();

                    foreach ($jurnalDetail as $index => $value) {
                        $datadetail = [
                            'jurnalumumpusat_id' => $jurnalUmumPusat->id,
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

                        $detaillog[] = $datadetails['detail']->toArray();
                    }
                    $datalogtrail = [
                        'namatabel' => strtoupper($tabeldetail),
                        'postingdari' => 'ENTRY JURNAL UMUM PUSAT DETAIL',
                        'idtrans' =>  $storedLogTrail['id'],
                        'nobuktitrans' => $jurnalUmumPusat->nobukti,
                        'aksi' => 'ENTRY',
                        'datajson' => $detaillog,
                        'modifiedby' => $jurnalUmumPusat->modifiedby,
                    ];

                    $data = new StoreLogTrailRequest($datalogtrail);
                    app(LogTrailController::class)->store($data);
                }
            } else {

                for ($i = 0; $i < count($request->jurnalId); $i++) {

                    $get = JurnalUmumHeader::lockForUpdate()->where('id', $request->jurnalId[$i])->first();
                    $jurnalUmumPusat = JurnalUmumPusatHeader::lockForUpdate()->where('nobukti', $get->nobukti)->first();
                    if ($jurnalUmumPusat != null) {

                        $getDetail = JurnalUmumPusatDetail::where('jurnalumumpusat_id', $jurnalUmumPusat->id)->get();
                        $jurnalumum = new JurnalUmumPusatHeader();
                        $jurnalumum = $jurnalumum->lockAndDestroy($jurnalUmumPusat->id);
                        $logTrail = [
                            'namatabel' => strtoupper($jurnalUmumPusat->getTable()),
                            'postingdari' => 'DELETE JURNAL UMUM PUSAT HEADER',
                            'idtrans' => $jurnalUmumPusat->id,
                            'nobuktitrans' => $jurnalUmumPusat->nobukti,
                            'aksi' => 'DELETE',
                            'datajson' => $jurnalUmumPusat->toArray(),
                            'modifiedby' => auth('api')->user()->name
                        ];

                        $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                        $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);


                        // DELETE JURNAL DETAIL

                        $logTrailJurnalDetail = [
                            'namatabel' => 'JURNALUMUMPUSATDETAIL',
                            'postingdari' => 'DELETE JURNAL UMUM PUSAT DETAIL',
                            'idtrans' => $storedLogTrail['id'],
                            'nobuktitrans' => $jurnalUmumPusat->nobukti,
                            'aksi' => 'DELETE',
                            'datajson' => $getDetail->toArray(),
                            'modifiedby' => auth('api')->user()->name
                        ];

                        $validatedLogTrailJurnalDetail = new StoreLogTrailRequest($logTrailJurnalDetail);
                        app(LogTrailController::class)->store($validatedLogTrailJurnalDetail);
                    }

                    $jurnalApprove = JurnalUmumHeader::lockForUpdate()->findOrFail($request->jurnalId[$i]);
                    $jurnalApprove->statusapproval = $request->approve;
                    $jurnalApprove->userapproval = auth('api')->user()->name;
                    $jurnalApprove->tglapproval = date('Y-m-d H:i:s');

                    $jurnalApprove->save();

                    $logTrail = [
                        'namatabel' => strtoupper($jurnalApprove->getTable()),
                        'postingdari' => 'APPROVED JURNAL',
                        'idtrans' => $jurnalApprove->id,
                        'nobuktitrans' => $jurnalApprove->nobukti,
                        'aksi' => $statusApp->text,
                        'datajson' => $jurnalApprove->toArray(),
                        'modifiedby' => auth('api')->user()->name
                    ];

                    $validatedlogTrail = new StoreLogTrailRequest($logTrail);
                    app(LogTrailController::class)->store($validatedlogTrail);
                }
            }
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $jurnalUmumPusat
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
    }

    public function storeimportdatacabang(Request $request)
    {
        DB::BeginTransaction();
        try {

            for ($i = 0; $i < count($request->header_id); $i++) {

                $header_nobukti = $request->header_nobukti[$i] ?? '';
                $header_cabang_id = $request->header_cabang_id[$i] ?? 0;
                $queryjurnalumumheader = db::table("jurnalumumpusatheader")->from(db::raw("jurnalumumpusatheader a with (readuncommitted)"))
                    ->select(
                        'a.id'
                    )
                    ->where('a.nobukti', $header_nobukti)
                    ->where('a.cabang_id', $header_cabang_id)
                    ->first();
                if (!isset($queryjurnalumumheader)) {
                    $jurnalUmumPusat = new JurnalUmumPusatHeader();
                    $jurnalUmumPusat->nobukti = $request->header_nobukti[$i] ?? '';
                    $jurnalUmumPusat->tglbukti = $request->header_tglbukti[$i] ?? '1900/1/1';
                    $jurnalUmumPusat->postingdari = $request->header_postingdari[$i] ?? '';
                    $jurnalUmumPusat->statusapproval = $request->header_statusapproval[$i] ?? 0;
                    $jurnalUmumPusat->userapproval = $request->header_userapproval[$i] ?? '';
                    $jurnalUmumPusat->tglapproval = $request->header_tglapproval[$i] ?? '1900/1/1';
                    $jurnalUmumPusat->statusformat = $request->header_statusformat[$i] ?? 0;
                    $jurnalUmumPusat->modifiedby =  $request->header_modifiedby[$i] ?? '';

                    $jurnalUmumPusat->save();

                    $logTrail = [
                        'namatabel' => strtoupper($jurnalUmumPusat->getTable()),
                        'postingdari' => 'IMPORT DATA CABANG JURNAL UMUM PUSAT HEADER',
                        'idtrans' => $jurnalUmumPusat->id,
                        'nobuktitrans' => $jurnalUmumPusat->nobukti,
                        'aksi' => 'ENTRY',
                        'datajson' => $jurnalUmumPusat->toArray(),
                        'modifiedby' => $jurnalUmumPusat->modifiedby
                    ];

                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                }
                $jurnalUmumPusatdetail = new JurnalUmumPusatDetail();

                $jurnalUmumPusatdetail->jurnalumumpusat_id = $jurnalUmumPusat->id;
                $jurnalUmumPusatdetail->nobukti = $request->detail_nobukti[$i] ?? '';
                $jurnalUmumPusatdetail->tglbukti = $request->detail_tglbukti[$i] ?? '1900/1/1';
                $jurnalUmumPusatdetail->coa = $request->detail_coa[$i] ?? '';
                $jurnalUmumPusatdetail->nominal = $request->detail_nominal[$i] ?? '0';
                $jurnalUmumPusatdetail->keterangan = $request->detail_keterangan[$i] ?? '';
                $jurnalUmumPusatdetail->modifiedby = $request->detail_modifiedby[$i] ?? '';
                $jurnalUmumPusatdetail->baris = $request->detail_baris[$i] ?? '0';


                $jurnalUmumPusatdetail->save();
                $logTrail = [
                    'namatabel' => strtoupper($jurnalUmumPusatdetail->getTable()),
                    'postingdari' => 'IMPORT DATA CABANG JURNAL UMUM PUSAT DETAIL',
                    'idtrans' => $jurnalUmumPusat->id,
                    'nobuktitrans' => $jurnalUmumPusat->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $jurnalUmumPusat->toArray(),
                    'modifiedby' => $jurnalUmumPusat->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            }


            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $jurnalUmumPusat
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
    }

    public function show($id)
    {
        $data = JurnalUmumPusatHeader::find($id);

        $nobukti = $data['nobukti'];

        $query = DB::table('jurnalumumpusatdetail AS A')
            ->select(['A.coa as coadebet', 'b.coa as coakredit', 'A.nominal', 'A.keterangan'])
            ->join(
                DB::raw("(SELECT baris,coa FROM jurnalumumpusatdetail WHERE nobukti='$nobukti' AND nominal<0) B"),
                function ($join) {
                    $join->on('A.baris', '=', 'B.baris');
                }
            )
            ->where([
                ['A.nobukti', '=', $nobukti],
                ['A.nominal', '>=', '0']
            ])
            ->get();


        return response([
            'status' => true,
            'data' => $data,
            'detail' => $query
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy($id, Request $request)
    {
        DB::beginTransaction();

        try {
            $jurnalUmumPusat = new JurnalUmumPusatHeader();
            JurnalUmumPusatDetail::where('jurnalumumpusat_id', $id)->lockForUpdate()->delete();
            JurnalUmumPusatHeader::destroy($id);

            $logTrail = [
                'namatabel' => strtoupper($jurnalUmumPusat->getTable()),
                'postingdari' => 'DELETE JURNAL UMUM',
                'idtrans' => $id,
                'nobuktitrans' => $jurnalUmumPusat->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $jurnalUmumPusat->toArray(),
                'modifiedby' => $jurnalUmumPusat->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $selected = $this->getPosition($jurnalUmumPusat, $jurnalUmumPusat->getTable(), true);
            $jurnalUmumPusat->position = $selected->position;
            $jurnalUmumPusat->id = $selected->id;
            $jurnalUmumPusat->page = ceil($jurnalUmumPusat->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $jurnalUmumPusat
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report()
    {
    }
}
