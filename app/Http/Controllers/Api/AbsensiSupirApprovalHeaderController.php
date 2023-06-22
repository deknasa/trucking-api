<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSupirApprovalHeader;
use App\Models\AbsensiSupirHeader;
use App\Models\AbsensiSupirApprovalDetail;

use App\Models\KasGantungHeader;
use App\Models\PengeluaranDetail;
use App\Models\PengeluaranHeader;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\Parameter;

use App\Http\Requests\StoreAbsensiSupirApprovalHeaderRequest;
use App\Http\Requests\UpdateAbsensiSupirApprovalHeaderRequest;
use App\Http\Requests\StoreAbsensiSupirApprovalDetailRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreKasGantungHeaderRequest;
use App\Http\Requests\UpdateKasGantungHeaderRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Exception;

class AbsensiSupirApprovalHeaderController extends Controller
{
    /**
     * @ClassName 
     * AbsensiSupirApprovalHeader
     * @Detail1 AbsensiSupirApprovalDetailController
     */
    public function index(GetIndexRangeRequest $request)
    {
        $absensiSupirApprovalHeader = new AbsensiSupirApprovalHeader();

        return response([
            'data' => $absensiSupirApprovalHeader->get(),
            'attributes' => [
                'totalRows' => $absensiSupirApprovalHeader->totalRows,
                'totalPages' => $absensiSupirApprovalHeader->totalPages
            ]
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreAbsensiSupirApprovalHeaderRequest $request)
    {
       
// dd($request->all());
        DB::beginTransaction();
        try {
            $data =[
                "tglbukti"=>$request->tglbukti,
                "absensisupir_nobukti"=>$request->absensisupir_nobukti,
                "kasgantung_nobukti"=>$request->kasgantung_nobukti,
                "pengeluaran_nobukti"=>$request->pengeluaran_nobukti,
                "tglkaskeluar"=>$request->tglkaskeluar,
                'supir_id' => $request->supir_id,
                'trado_id' => $request->trado_id,
                'uangjalan' => $request->uangjalan,
                'supir_id' => $request->supir_id,
                'trado_id' => $request->trado_id,
                'uangjalan' => $request->uangjalan,
            ];
            /* Store header */
            $absensiSupirApprovalHeader = (new AbsensiSupirApprovalHeader())->processStore($data);
            /* Set position and page */
            $absensiSupirApprovalHeader->position = $this->getPosition($absensiSupirApprovalHeader, $absensiSupirApprovalHeader->getTable())->position;
            $absensiSupirApprovalHeader->page = ceil($absensiSupirApprovalHeader->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $absensiSupirApprovalHeader->page = ceil($absensiSupirApprovalHeader->position / ($request->limit ?? 10));
            }


            $dataKasgantung = [
                "tglbukti" => $kasgantung->tglbukti,
                // "keterangan" => $absensiSupirApprovalHeader->keterangan,
                "bank_id" => $bank->id,
                "penerima_id" => $kasgantung->penerima_id,
                "coakaskeluar" => $coakaskeluar,
                "postingdari" => 'ENTRY ABSENSI SUsPIR APPROVAL',
                "tglkaskeluar" => $request->tglbukti,
                'keterangan_detail' => $details['keterangan'],
                'nominal' => $details['nominal'],
                'approvalabsensisupir' => true,
                'absensisupirapprovalheader_id' => $absensiSupirApprovalHeader->id,
                'absensisupir_nobukti' => $absensiSupirApprovalHeader->absensisupir_nobukti,
                "from" => "AbsensiSupirApprovalHeader"
            ];


            $data = new UpdateKasGantungHeaderRequest($dataKasgantung);
            // dump($data);
            $kasgantungStore = app(KasGantungHeaderController::class)->update($data, $kasgantung);

            $kasgantung = $kasgantungStore->original['data'];

            $absensiSupirApprovalHeader->pengeluaran_nobukti = $kasgantung->pengeluaran_nobukti;
            $absensiSupirApprovalHeader->tglkaskeluar = $kasgantung->tglkaskeluar;
            $absensiSupirApprovalHeader->save();



            $logTrail = [
                'namatabel' => strtoupper($absensiSupirApprovalHeader->getTable()),
                'postingdari' => 'ENTRY ABSENSI SUPIR APPROVAL HEADER',
                'idtrans' => $absensiSupirApprovalHeader->id,
                'nobuktitrans' => $absensiSupirApprovalHeader->nobukti,
                'aksi' => 'ADD',
                'datajson' => $absensiSupirApprovalHeader->toArray(),
                'modifiedby' => $absensiSupirApprovalHeader->modifiedby
            ];
            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            if ($request->trado_id) {
                /* Store detail */
                $detaillog = [];
                $jurnalDetail = [];
                for ($i = 0; $i < count($request->trado_id); $i++) {
                    $supirId = ($request->supir_id[$i] != null) ? $request->supir_id[$i] : 0;
                    $datadetail = [
                        "absensisupirapproval_id" => $absensiSupirApprovalHeader->id,
                        "nobukti" => $absensiSupirApprovalHeader->nobukti,
                        "trado_id" => $request->trado_id[$i],
                        "supir_id" => $supirId,
                        "modifiedby" => auth('api')->user()->name
                    ];
                    $data = new StoreAbsensiSupirApprovalDetailRequest($datadetail);
                    $absensiSupirApprovalDetail = app(AbsensiSupirApprovalDetailController::class)->store($data);

                    if ($absensiSupirApprovalDetail['error']) {
                        return response($absensiSupirApprovalDetail, 422);
                    } else {
                        $iddetail = $absensiSupirApprovalDetail['id'];
                        $tabeldetail = $absensiSupirApprovalDetail['tabel'];
                    }
                    $datadetaillog = [
                        "id" => $iddetail,
                        "absensisupirapproval_id" => $absensiSupirApprovalHeader->id,
                        "nobukti" => $absensiSupirApprovalHeader->nobukti,
                        "trado_id" => $request->trado_id[$i],
                        "supir_id" => $supirId,
                        "modifiedby" => auth('api')->user()->name,
                        'created_at' => date('d-m-Y H:i:s', strtotime($absensiSupirApprovalHeader->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($absensiSupirApprovalHeader->updated_at)),
                    ];


                    $detaillog[] = $datadetaillog;
                }
                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'ENTRY ABSENSI SUPIR APPROVAL DETAIL',
                    'idtrans' =>  $iddetail,
                    'nobuktitrans' => $absensiSupirApprovalHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => auth('api')->user()->name,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);

                $jurnalHeader = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $absensiSupirApprovalHeader->nobukti,
                    'tglbukti' => date('Y-m-d', strtotime($absensiSupirApprovalHeader->tglbukti)),
                    // 'keterangan' => $absensiSupirApprovalHeader->keterangan,
                    'postingdari' => 'ENTRY ABSENSI SUPIR APPROVAL DETAIL',
                    'statusapproval' => $absensiSupirApprovalHeader->statusapproval,
                    'userapproval' => "",
                    'tglapproval' => "",
                    'statusformat' => 0,
                    'modifiedby' => auth('api')->user()->name,
                ];

                $jurnalDetail = [
                    [
                        'nobukti' => $absensiSupirApprovalHeader->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($absensiSupirApprovalHeader->tglbukti)),
                        'coa' =>  $memodebet['JURNAL'],
                        'nominal' => $total,
                        // 'keterangan' => $request->keterangan,
                        'modifiedby' => auth('api')->user()->name,
                        'baris' => $i,
                    ], [
                        'nobukti' => $absensiSupirApprovalHeader->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($absensiSupirApprovalHeader->tglbukti)),
                        'coa' =>  $kasgantung->coakaskeluar,
                        'nominal' => -$total,
                        // 'keterangan' => $request->keterangan,
                        'modifiedby' => auth('api')->user()->name,
                        'baris' => $i,
                    ]
                ];
            }

            // $queryabsen = DB::table('absensisupirapproval')
            //     ->from(
            //         DB::raw("absensisupirheader a with (readuncommitted)")
            //     )
            //     ->select(
            //         'b.pengeluaran_nobukti',
            //         'b.tglkaskeluar'
            //     )
            //     ->join(DB::raw("kasgantungheader b"), 'a.kasgantung_nobukti', 'b.nobukti')
            //     ->first();
            // if (isset($queryabsen)) {
            //     $absensisupirapprovalheader  = AbsensiSupirApprovalHeader::lockForUpdate()->where("id", $absensiSupirApprovalHeader->id)
            //         ->firstorFail();
            //     $absensisupirapprovalheader->pengeluaran_nobukti = $queryabsen->pengeluaran_nobukti;
            //     $absensisupirapprovalheader->tglkaskeluar = $queryabsen->tglkaskeluar;
            //     $absensisupirapprovalheader->save();
            // }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $absensiSupirApprovalHeader
<<<<<<< HEAD
            ], 201);
=======
            ], 201);    
>>>>>>> a8acf1ecf07bf2c9c32e09b26f20fb000ded3599
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function show(AbsensiSupirApprovalHeader $absensiSupirApprovalHeader, $id)
    {
        $data = $absensiSupirApprovalHeader->find($id);
        $detail = AbsensiSupirApprovalDetail::getAll($id);

        // dd($detail);
        //  $detail = NotaDebetHeaderDetail::findAll($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }
    /**
     * @ClassName 
     */
    public function update(UpdateAbsensiSupirApprovalHeaderRequest $request, AbsensiSupirApprovalHeader $absensiSupirApprovalHeader, $id)
    {
        DB::beginTransaction();
        try {
            $data =[
                "tglbukti"=>$request->tglbukti,
                "absensisupir_nobukti"=>$request->absensisupir_nobukti,
                "kasgantung_nobukti"=>$request->kasgantung_nobukti,
                "pengeluaran_nobukti"=>$request->pengeluaran_nobukti,
                "tglkaskeluar"=>$request->tglkaskeluar,
                'supir_id' => $request->supir_id,
                'trado_id' => $request->trado_id,
                'uangjalan' => $request->uangjalan,
                'supir_id' => $request->supir_id,
                'trado_id' => $request->trado_id,
                'uangjalan' => $request->uangjalan,
            ];
            /* Store header */
            $absensiSupirApprovalHeader = (new AbsensiSupirApprovalHeader())->processStore($data);
            /* Set position and page */
            $absensiSupirApprovalHeader->position = $this->getPosition($absensiSupirApprovalHeader, $absensiSupirApprovalHeader->getTable())->position;
            $absensiSupirApprovalHeader->page = ceil($absensiSupirApprovalHeader->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $absensiSupirApprovalHeader->page = ceil($absensiSupirApprovalHeader->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $absensiSupirApprovalHeader
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }

        return response($request->all(), 442);
    }
    /**
     * @ClassName 
     */
    public function destroy(Request $request, $id)
    {


        DB::beginTransaction();
<<<<<<< HEAD

        $getDetail = AbsensiSupirApprovalDetail::lockForUpdate()->where('absensisupirapproval_id', $id)->get();

        $absensiSupirApprovalHeader = new AbsensiSupirApprovalHeader();
        $absensiSupirApprovalHeader = $absensiSupirApprovalHeader->lockAndDestroy($id);

        $pengeluaran = $absensiSupirApprovalHeader->pengeluaran_nobukti;
        $kasGantung = KasGantungHeader::where('pengeluaran_nobukti', $pengeluaran)->first();
        // return response($kasGantung,422);
        $kasGantung->pengeluaran_nobukti = '';
        $kasGantung->coakaskeluar = '';
        $kasGantung->kasgantungDetail()->update(['coa' => '']);
        $kasGantung->save();
        $request['postingdari'] = "DELETE ABSENSI SUPIR APPROVAL";


        if ($absensiSupirApprovalHeader) {
            $logTrail = [
                'namatabel' => strtoupper($absensiSupirApprovalHeader->getTable()),
                'postingdari' => 'DELETE ABSENSI SUPIR APPROVAL',
                'idtrans' => $id,
                'nobuktitrans' => $absensiSupirApprovalHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $absensiSupirApprovalHeader->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE ABSENSI SUPIR APPROVAL DETAIL
            $logTrailAbsensiApprovalDetail = [
                'namatabel' => 'ABSENSISUPIRAPPROVALDETAIL',
                'postingdari' => 'DELETE ABSENSI SUPIR APPROVAL DETAIL',
                'idtrans' => $storedLogTrail['id'] ?? '',
                'nobuktitrans' => $absensiSupirApprovalHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailAbsensiApprovalDetail = new StoreLogTrailRequest($logTrailAbsensiApprovalDetail);
            app(LogTrailController::class)->store($validatedLogTrailAbsensiApprovalDetail);

            $getPengeluaran = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))->where('nobukti', $pengeluaran)->first();
            app(PengeluaranHeaderController::class)->destroy($request, $getPengeluaran->id);
=======
        try {
            // dd($absensiSupirApprovalHeader);
            $absensiSupirApprovalHeader = (new AbsensiSupirApprovalHeader())->processDestroy($id);
            /* Set position and page */
            $absensiSupirApprovalHeader->position = $this->getPosition($absensiSupirApprovalHeader, $absensiSupirApprovalHeader->getTable())->position;
            $absensiSupirApprovalHeader->page = ceil($absensiSupirApprovalHeader->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $absensiSupirApprovalHeader->page = ceil($absensiSupirApprovalHeader->position / ($request->limit ?? 10));
            }
>>>>>>> a8acf1ecf07bf2c9c32e09b26f20fb000ded3599

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $absensiSupirApprovalHeader
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function approval($id)
    {
        DB::beginTransaction();
        $absensiSupirApprovalHeader = AbsensiSupirApprovalHeader::lockForUpdate()->findOrFail($id);
        try {
            $statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            if ($absensiSupirApprovalHeader->statusapproval == $statusApproval->id) {
                $absensiSupirApprovalHeader->statusapproval = $statusNonApproval->id;
            } else {
                $absensiSupirApprovalHeader->statusapproval = $statusApproval->id;
            }

            $absensiSupirApprovalHeader->tglapproval = date('Y-m-d', time());
            $absensiSupirApprovalHeader->userapproval = auth('api')->user()->name;

            if ($absensiSupirApprovalHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($absensiSupirApprovalHeader->getTable()),
                    'postingdari' => 'UN/APPROVE ABSENSI SUPIR APPROVAL',
                    'idtrans' => $absensiSupirApprovalHeader->id,
                    'nobuktitrans' => $absensiSupirApprovalHeader->id,
                    'aksi' => 'UN/APPROVE',
                    'datajson' => $absensiSupirApprovalHeader->toArray(),
                    'modifiedby' => $absensiSupirApprovalHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            return response([
                'message' => 'Berhasil',
                'data' => $absensiSupirApprovalHeader
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function fieldLength(Type $var = null)
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('AbsensiSupirApprovalHeader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function getAbsensi($absensi)
    {
        $absensiSupir = new AbsensiSupirHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $absensiSupir->getAbsensi($absensi),
            // 'data' => $absensi,
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $absensiSupir->totalRows,
                'totalPages' => $absensiSupir->totalPages,
                'totalUangJalan' => $absensiSupir->totalUangJalan,
            ]
        ]);
    }


    public function cekvalidasi($id)
    {

        $absensisupirapproval = AbsensiSupirApprovalHeader::find($id);
        $statusdatacetak = $absensisupirapproval->statuscetak;
        $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($statusdatacetak == $statusCetak->id) {
            $query = DB::table('error')
                ->select('keterangan')
                ->where('kodeerror', '=', 'SDC')
                ->get();
            $keterangan = $query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => 'sudah cetak',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];

            return response($data);
        } else {

            $data = [
                'message' => '',
                'errors' => 'belum approve',
                'kodestatus' => '0',
                'kodenobukti' => '1'
            ];

            return response($data);
        }
    }

    public function getApproval($absensi)
    {
        $absensiSupirApprovalHeader = new AbsensiSupirApprovalHeader();
        $absensiSupir = $absensiSupirApprovalHeader->find($absensi);
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $absensiSupirApprovalHeader->getApproval($absensiSupir->absensisupir_nobukti),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $absensiSupirApprovalHeader->totalRows,
                'totalPages' => $absensiSupirApprovalHeader->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function export($id)
    {
        $absensiSupirApprovalHeader = new AbsensiSupirApprovalHeader();

        return response([
            'data' => $absensiSupirApprovalHeader->getExport($id)
        ]);
    }

    /**
     * @ClassName 
     */
    public function report()
    {
    }
}
