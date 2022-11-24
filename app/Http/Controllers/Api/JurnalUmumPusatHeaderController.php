<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJurnalUmumPusatDetailRequest;
use App\Models\JurnalUmumPusatHeader;
use App\Http\Requests\StoreJurnalUmumPusatHeaderRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateJurnalUmumPusatHeaderRequest;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\JurnalUmumPusatDetail;
use App\Models\LogTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JurnalUmumPusatHeaderController extends Controller
{
    /**
     * @ClassName
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

    /**
     * @ClassName
     */
    public function store(StoreJurnalUmumPusatHeaderRequest $request)
    {
        DB::BeginTransaction();
        try {

            $jurnalUmumPusat = new JurnalUmumPusatHeader();

            for ($i = 0; $i < count($request->jurnalumumId); $i++) {
                $jurnalUmum = JurnalUmumHeader::where('id', $request->jurnalumumId[$i])->first();

                $jurnalUmumPusat->nobukti = $jurnalUmum->nobukti;
                $jurnalUmumPusat->tglbukti = $jurnalUmum->tglbukti;
                $jurnalUmumPusat->keterangan = $jurnalUmum->keterangan;
                $jurnalUmumPusat->postingdari = $jurnalUmum->postingdari;
                $jurnalUmumPusat->statusapproval = $jurnalUmum->statusapproval;
                $jurnalUmumPusat->userapproval = $jurnalUmum->userapproval;
                $jurnalUmumPusat->tglapproval = $jurnalUmum->tglapproval;
                $jurnalUmumPusat->statusformat = $jurnalUmum->statusformat;
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

                /* Store detail */
                // $detaillog = [];

                $jurnalDetail = JurnalUmumDetail::where('jurnalumum_id', $request->jurnalumumId[$i])->get();

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

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();

            /* Set position and page */

            $selected = $this->getPosition($jurnalUmumPusat, $jurnalUmumPusat->getTable());
            $jurnalUmumPusat->position = $selected->position;
            $jurnalUmumPusat->page = ceil($jurnalUmumPusat->position / ($request->limit ?? 10));

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
     */
    public function destroy($id, Request $request)
    {
        DB::beginTransaction();
        
        try {
            $jurnalUmumPusat = new JurnalUmumPusatHeader();
            JurnalUmumPusatDetail::where('jurnalumumpusat_id', $id)->delete();
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
}
