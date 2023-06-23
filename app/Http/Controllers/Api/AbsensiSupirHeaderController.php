<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAbsensiSupirDetailRequest;
use App\Http\Requests\StoreKasGantungDetailRequest;
use App\Http\Requests\StoreKasGantungHeaderRequest;
// use App\Http\Requests\UpdateAbsensiSupirHeaderRequest;
// use App\Http\Requests\StoreAbsensiSupirHeaderRequest;
use App\Models\AbsensiSupirHeader;
use App\Models\AbsensiSupirApprovalHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateKasGantungHeaderRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Models\AbsensiSupirDetail;
use App\Models\KasGantungDetail;
use App\Models\KasGantungHeader;
use App\Models\Parameter;
use App\Models\User;

use App\Http\Requests\AbsensiSupirHeaderRequest;



use Illuminate\Database\QueryException;

class AbsensiSupirHeaderController extends Controller
{
    /**
     * @ClassName 
     * AbsensiSupirHeader
     * @Detail1 AbsensiSupirDetailController
     */
    public function index(GetIndexRangeRequest $request)
    {
        $absensiSupirHeader = new AbsensiSupirHeader();

        return response([
            'data' => $absensiSupirHeader->get(),
            'attributes' => [
                'totalRows' => $absensiSupirHeader->totalRows,
                'totalPages' => $absensiSupirHeader->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function approvalEditAbsensi($id)
    {
        DB::beginTransaction();
        try {
            $absensiSupirHeader = AbsensiSupirHeader::lockForUpdate()->findOrFail($id);

            $statusBolehEdit = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS EDIT ABSENSI')->where('text', '=', 'BOLEH EDIT ABSENSI')->first();
            $statusTidakBolehEdit = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS EDIT ABSENSI')->where('text', '=', 'TIDAK BOLEH EDIT ABSENSI')->first();
            // statusapprovaleditabsensi,tglapprovaleditabsensi,userapprovaleditabsensi 
            if ($absensiSupirHeader->statusapprovaleditabsensi == $statusBolehEdit->id) {
                $absensiSupirHeader->statusapprovaleditabsensi = $statusTidakBolehEdit->id;
                $aksi = $statusTidakBolehEdit->text;
            } else {
                $absensiSupirHeader->statusapprovaleditabsensi = $statusBolehEdit->id;
                $aksi = $statusBolehEdit->text;
            }

            $absensiSupirHeader->tglapprovaleditabsensi = date("Y-m-d", strtotime('today'));
            $absensiSupirHeader->userapprovaleditabsensi = auth('api')->user()->name;

            if ($absensiSupirHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($absensiSupirHeader->getTable()),
                    'postingdari' => 'APPROVED SUPIR RESIGN',
                    'idtrans' => $absensiSupirHeader->id,
                    'nobuktitrans' => $absensiSupirHeader->id,
                    'aksi' => $aksi,
                    'datajson' => $absensiSupirHeader->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }



    public function default()
    {

        $absensisupirdetail = new AbsensiSupirDetail();

        return response([
            'status' => true,
            'detail' => $absensisupirdetail->getAll(0),
        ]);
    }

    public function show($id)
    {
        $data = AbsensiSupirHeader::findAll($id);
        $detail = AbsensiSupirDetail::getAll($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }

    public function detail($id)
    {
        return response([
            'data' => AbsensiSupirDetail::with('trado', 'supir', 'absenTrado')->where('absensi_id', $id)->get()
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(AbsensiSupirHeaderRequest $request)
    {

        DB::beginTransaction();
        try {
            $data = [
                "tglbukti" =>  $request->tglbukti,
                "trado_id" => $request->trado_id,
                "trado" => $request->trado,
                "supir_id" => $request->supir_id,
                "supir" => $request->supir,
                "keterangan_detail" => $request->keterangan_detail,
                "absen_id" => $request->absen_id,
                "absen" => null,
                "jam" => $request->jam,
                "uangjalan" => $request->uangjalan,
            ];


            /* Store header */
            $absensiSupirHeader = (new absensiSupirHeader())->processStore($data);
            /* Set position and page */
            $absensiSupirHeader->position = $this->getPosition($absensiSupirHeader, $absensiSupirHeader->getTable())->position;
            $absensiSupirHeader->page = ceil($absensiSupirHeader->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $absensiSupirHeader->page = ceil($absensiSupirHeader->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $absensiSupirHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }

        // DB::beginTransaction();
        // try {
        //     $group = 'ABSENSI';
        //     $subgroup = 'ABSENSI';
        //     $format = DB::table('parameter')
        //         ->where('grp', $group)
        //         ->where('subgrp', $subgroup)
        //         ->first();

        //     $content = new Request();
        //     $content['group'] = $group;
        //     $content['subgroup'] = $subgroup;
        //     $content['table'] = 'absensisupirheader';
        //     $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));


        //     /* Store header */
        //     $absensisupir = new AbsensiSupirHeader();
        //     $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        //     $statusEditAbsensi = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS EDIT ABSENSI')->where('default', 'YA')->first();

        //     // $absensisupir->tglapprovaleditabsensi  = 
        //     // $absensisupir->tglapprovaleditabsensi = 
        //     $absensisupir->nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
        //     $absensisupir->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
        //     $absensisupir->kasgantung_nobukti = $request->kasgantung_nobukti ?? '';
        //     $absensisupir->nominal = array_sum($request->uangjalan);
        //     $absensisupir->statusformat = $format->id;
        //     $absensisupir->statuscetak = $statusCetak->id ?? 0;
        //     $absensisupir->statusapprovaleditabsensi  = $statusEditAbsensi->id;
        //     $absensisupir->modifiedby = auth('api')->user()->name;

        //     if ($absensisupir->save()) {

        //         $detaillog = [];
        //         for ($i = 0; $i < count($request->trado_id); $i++) {
        //             /* Store Detail */
        //             $datadetail = [
        //                 'absensi_id' => $absensisupir->id,
        //                 'nobukti' => $absensisupir->nobukti,
        //                 'trado_id' => $request->trado_id[$i],
        //                 'supir_id' => $request->supir_id[$i],
        //                 'keterangan' => $request->keterangan_detail[$i],
        //                 'uangjalan' => $request->uangjalan[$i],
        //                 'absen_id' => $request->absen_id[$i] ?? '',
        //                 'jam' => $request->jam[$i],
        //                 'modifiedby' => $absensisupir->modifiedby,
        //             ];
        //             $data = new StoreAbsensiSupirDetailRequest($datadetail);
        //             $datadetails = app(AbsensiSupirDetailController::class)->store($data);
        //             if ($datadetails['error']) {
        //                 return response($datadetails, 422);
        //             } else {
        //                 $iddetail = $datadetails['id'];
        //                 $tabeldetail = $datadetails['tabel'];
        //             }

        //             $detaillog[] = $datadetails['detail'];
        //         }

        //         //GET NO BUKTI KAS GANTUNG

        //         $format = DB::table('parameter')
        //             ->where('grp', 'KAS GANTUNG')
        //             ->where('subgrp', 'NOMOR KAS GANTUNG')
        //             ->first();

        //         $noBuktiKasgantungRequest = new Request();
        //         $noBuktiKasgantungRequest['group'] = 'KAS GANTUNG';
        //         $noBuktiKasgantungRequest['subgroup'] = 'NOMOR KAS GANTUNG';
        //         $noBuktiKasgantungRequest['table'] = 'kasgantungheader';
        //         $noBuktiKasgantungRequest['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

        //         $nobuktiKasGantung = app(Controller::class)->getRunningNumber($noBuktiKasgantungRequest)->original['data'];

        //         $absensisupir->kasgantung_nobukti = $nobuktiKasGantung;
        //         $absensisupir->save();
        //         /* Store Header LogTrail */
        //         $logTrail = [
        //             'namatabel' => strtoupper($absensisupir->getTable()),
        //             'postingdari' => 'ENTRY ABSENSI SUPIR HEADER',
        //             'idtrans' => $absensisupir->id,
        //             'nobuktitrans' => $absensisupir->nobukti,
        //             'aksi' => 'ENTRY',
        //             'datajson' => $absensisupir->toArray(),
        //             'modifiedby' => $absensisupir->modifiedby
        //         ];

        //         $validatedLogTrail = new StoreLogTrailRequest($logTrail);
        //         $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

        //         // Store detail logtrail
        //         $detailLogTrail = [
        //             'namatabel' => strtoupper($tabeldetail),
        //             'postingdari' => 'ENTRY ABSENSI SUPIR DETAIL',
        //             'idtrans' => $storedLogTrail['id'],
        //             'nobuktitrans' => $absensisupir->nobukti,
        //             'aksi' => 'ENTRY',
        //             'datajson' => $detaillog,
        //             'modifiedby' => $absensisupir->modifiedby
        //         ];

        //         $validatedLogTrail = new StoreLogTrailRequest($detailLogTrail);
        //         $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);


        //         $bank = DB::table('bank')
        //             ->from(
        //                 DB::raw("bank with (readuncommitted)")
        //             )
        //             ->select(
        //                 'id'
        //             )
        //             ->where('tipe', '=', 'KAS')
        //             ->first();

        //         $kasGantungHeader = [
        //             'tanpaprosesnobukti' => 1,
        //             'nobukti' => $nobuktiKasGantung,
        //             'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
        //             'penerima_id' => '',
        //             'bank_id' => $bank->id ?? 0,
        //             'pengeluaran_nobukti' => '',
        //             'coakaskeluar' => '',
        //             'postingdari' => 'ENTRY ABSENSI SUPIR',
        //             'tglkaskeluar' => '1900/1/1',
        //             'statusformat' => $format->id,
        //             'modifiedby' => auth('api')->user()->name
        //         ];

        //         $kasGantungDetail = [];
        //         for ($i = 0; $i < count($request->uangjalan); $i++) {
        //             $detail = [];

        //             $detail = [
        //                 'entriluar' => 1,
        //                 'nobukti' => $nobuktiKasGantung,
        //                 'nominal' => $request->uangjalan[$i],
        //                 'coa' => '',
        //                 'keterangan' => $request->keterangan_detail[$i],
        //                 'modifiedby' =>  auth('api')->user()->name
        //             ];
        //             $kasGantungDetail[] = $detail;
        //         }

        //         $kasGantung = $this->storeKasGantung($kasGantungHeader, $kasGantungDetail);


        //         // if (!$kasGantung['status'] AND @$kasGantung['errorCode'] == 2601) {
        //         //     goto ATAS;
        //         // }
        //         if (!$kasGantung['status']) {
        //             throw new \Throwable($kasGantung['message']);
        //         }
        //     }

        //     DB::commit();

        //     /* Set position and page */
        //     $selected = $this->getPosition($absensisupir, $absensisupir->getTable());
        //     $absensisupir->position = $selected->position;
        //     $absensisupir->page = ceil($absensisupir->position / ($request->limit ?? 10));


        //     return response([
        //         'message' => 'Berhasil disimpan',
        //         'data' => $absensisupir
        //     ], 201);
        // } catch (\Throwable $th) {
        //     DB::rollBack();

        //     throw $th;
        // }
    }

    /**
     * @ClassName 
     */
    public function update(AbsensiSupirHeaderRequest $request, AbsensiSupirHeader $absensiSupirHeader)
    {
        DB::beginTransaction();
        try {
            $data = [
                "tglbukti" =>  $request->tglbukti,
                "trado_id" => $request->trado_id,
                "trado" => $request->trado,
                "supir_id" => $request->supir_id,
                "supir" => $request->supir,
                "keterangan_detail" => $request->keterangan_detail,
                "absen_id" => $request->absen_id,
                "absen" => null,
                "jam" => $request->jam,
                "uangjalan" => $request->uangjalan,
            ];


            /* Store header */
            // dd($absensiSupirHeader);
            $absensiSupirHeader = (new absensiSupirHeader())->processUpdate($absensiSupirHeader, $data);
            /* Set position and page */
            $absensiSupirHeader->position = $this->getPosition($absensiSupirHeader, $absensiSupirHeader->getTable())->position;
            $absensiSupirHeader->page = ceil($absensiSupirHeader->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $absensiSupirHeader->page = ceil($absensiSupirHeader->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $absensiSupirHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    /**
     * @ClassName 
     */
    public function destroy(AbsensiSupirHeaderRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            // dd($absensiSupirHeader);
            $absensiSupirHeader = (new AbsensiSupirHeader())->processDestroy($id);
            /* Set position and page */
            $absensiSupirHeader->position = $this->getPosition($absensiSupirHeader, $absensiSupirHeader->getTable())->position;
            $absensiSupirHeader->page = ceil($absensiSupirHeader->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $absensiSupirHeader->page = ceil($absensiSupirHeader->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $absensiSupirHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function storeKasGantung($kasGantungHeader, $kasGantungDetail)
    {
        try {


            $kasGantung = new StoreKasGantungHeaderRequest($kasGantungHeader);
            $header = app(KasGantungHeaderController::class)->store($kasGantung);

            $nobukti = $kasGantungHeader['nobukti'];
            $detailLog = [];
            foreach ($kasGantungDetail as $value) {

                $value['kasgantung_id'] = $header->original['data']['id'];
                $value['pengeluaran_nobukti'] = $header->original['data']['pengeluaran_nobukti'];
                $kasGantungDetail = new StoreKasGantungDetailRequest($value);
                $datadetails = app(KasGantungDetailController::class)->store($kasGantungDetail);

                $detailLog[] = $datadetails['detail']->toArray();
            }
            $datalogtrail = [
                'namatabel' => strtoupper($datadetails['tabel']),
                'postingdari' => 'ENTRY ABSENSI SUPIR',
                'idtrans' =>  $header->original['idlogtrail'],
                'nobuktitrans' => $nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detailLog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);


            return [
                'status' => true
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function approval($id)
    {
        return $id;
    }
    public function cekvalidasidelete($id)
    {
        $absensisupir = AbsensiSupirHeader::findOrFail($id);

        $passes = true;
        $keterangan = [];
        //validasi sudah dipakai di orderantrucking / sp
        $isUsedTrip = AbsensiSupirHeader::isUsedTrip($absensisupir->id);
        if ($isUsedTrip) {
            $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'SATL')->get();
            // $keterangan = $query['0'];
            $keterangan = ['keterangan' => $query['0']->keterangan]; //$query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => 'Tidak bisa edit di hari yang berbeda',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];
            $passes = false;
            return response($data);
        }
        $data = [
            'message' => '',
            'errors' => 'success',
            'kodestatus' => '0',
            'kodenobukti' => '1'
        ];

        return response($data);
    }

    public function cekvalidasi($id)
    {
        $absensisupir = AbsensiSupirHeader::findOrFail($id);

        $passes = true;
        $keterangan = [];
        //validasi Hari ini
        $todayValidation = AbsensiSupirHeader::todayValidation($absensisupir->id);
        if (!$todayValidation) {
            $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'SATL')->get();
            // $keterangan = $query['0'];
            $keterangan = ['keterangan' => 'transaksi Sudah beda tanggal']; //$query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => 'Tidak bisa edit di hari yang berbeda',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];
            $passes = false;
            // return response($data);
        }

        //validasi approval
        $isApproved = AbsensiSupirHeader::isApproved($absensisupir->nobukti);
        if (!$isApproved) {
            $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'SATL')->get();
            $keterangan = $query['0'];
            $keterangan = ['keterangan' => 'transaksi Sudah di approved']; //$query['0'];

            $data = [
                'message' => $keterangan,
                'errors' => 'sudah approve',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];
            $passes = false;
            // return response($data);
        }



        //validasi status edit
        $passes = true;
        $isEditAble = AbsensiSupirHeader::isEditAble($id);
        if (!$isEditAble) {
            $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'BAED')->get();
            $keterangan = $query['0'];

            $data = [
                'message' => $keterangan,
                'errors' => 'status approve edit tidak boleh',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];
            $passes = false;
            // return response($data);
        }


        $isDateAllowed = AbsensiSupirHeader::isDateAllowed($id);
        if (!$isDateAllowed) {
            $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'TEPT')->get();
            $keterangan = $query['0'];

            $data = [
                'message' => $keterangan,
                'errors' => 'data tidak bisa diedit pada tanggal ini',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];
            $passes = false;
            // return response($data);
        }

        //validasi cetak
        $printValidation = AbsensiSupirHeader::printValidation($id);
        if (!$printValidation) {
            $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'SDC')->get();
            $keterangan = $query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => 'status approve edit tidak boleh',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];
            $passes = false;

            // return response($data);
        }
        if (($todayValidation && $isApproved) || ($isEditAble && $printValidation) || $isDateAllowed) {
            $data = [
                'message' => '',
                'errors' => 'success',
                'kodestatus' => '0',
                'kodenobukti' => '1'
            ];
            return response($data);
        }

        return response($data);
    }
    /**
     * @ClassName 
     */
    public function cekabsensi(Request $request, $id)
    {
        // return $request;
        $absensiSupirDetail = new AbsensiSupirDetail();
        $absensiSupirHeader = new AbsensiSupirHeader();

        return response([
            'status' => true,
            'data' => $absensiSupirHeader->findAll($id),
            'detail' => $absensiSupirDetail->get(),
            'absenTrado' => $absensiSupirHeader->getTradoAbsensi($id),
        ]);
    }

    public function cekValidasiAksi($id)
    {
        $absensiSupirHeader = new AbsensiSupirHeader();
        $nobukti = AbsensiSupirHeader::from(DB::raw("absensisupirheader"))->where('id', $id)->first();
        $cekdata = $absensiSupirHeader->cekvalidasiaksi($nobukti->nobukti);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', 'SATL')
                ->get();
            $keterangan = $query['0'];

            $data = [
                'status' => false,
                'message' => $keterangan,
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        } else {
            $data = [
                'status' => false,
                'message' => '',
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('absensisupirheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    /**
     * @ClassName 
     */
    public function export($id)
    {
        $absensiSupirHeader = new AbsensiSupirHeader();
        return response([
            'data' => $absensiSupirHeader->getExport($id),
        ]);
    }
    /**
     * @ClassName 
     */
    public function report()
    {
    }
}
