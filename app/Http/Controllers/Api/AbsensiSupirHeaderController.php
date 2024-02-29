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
     * @Detail AbsensiSupirDetailController
     * @Keterangan TAMPILKAN DATA
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
     * @Keterangan APPROVAL EDIT ABSENSI
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
                $absensiSupirHeader->tglapprovaleditabsensi = date('Y-m-d', strtotime("1900-01-01"));
                $absensiSupirHeader->userapprovaleditabsensi = '';
                $absensiSupirHeader->tglbataseditabsensi = null;
                $aksi = $statusTidakBolehEdit->text;
            } else {
                $tglbtas = date("Y-m-d", strtotime('today'));
                $tglbtas = date("Y-m-d H:i:s", strtotime($tglbtas . ' 23:59:00'));
                $absensiSupirHeader->tglbataseditabsensi = $tglbtas;
                $absensiSupirHeader->statusapprovaleditabsensi = $statusBolehEdit->id;
                $aksi = $statusBolehEdit->text;
                $absensiSupirHeader->tglapprovaleditabsensi = date("Y-m-d", strtotime('today'));
                $absensiSupirHeader->userapprovaleditabsensi = auth('api')->user()->name;
            }


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
        $detail = (new AbsensiSupirDetail())->getAll($id);

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
     * @Keterangan SIMPAN DATA
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
                "supirold_id" => $request->supirold_id,
                "supir" => $request->supir,
                "keterangan_detail" => $request->keterangan_detail,
                "absen_id" => $request->absen_id,
                "absen" => null,
                "jam" => $request->jam,
                "uangjalan" => $request->uangjalan,
                'tglbataseditabsensi' => $request->tglbataseditabsensi,
            ];


            /* Store header */
            $absensiSupirHeader = (new absensiSupirHeader())->processStore($data);
            /* Set position and page */
            $absensiSupirHeader->position = $this->getPosition($absensiSupirHeader, $absensiSupirHeader->getTable())->position;
            if ($request->limit == 0) {
                $absensiSupirHeader->page = ceil($absensiSupirHeader->position / (10));
            } else {
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
     * @Keterangan EDIT DATA
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
                'tglbataseditabsensi' => $request->tglbataseditabsensi,

            ];


            /* Store header */
            $absensiSupirHeader = (new absensiSupirHeader())->processUpdate($absensiSupirHeader, $data);
            /* Set position and page */
            $absensiSupirHeader->position = $this->getPosition($absensiSupirHeader, $absensiSupirHeader->getTable())->position;
            if ($request->limit == 0) {
                $absensiSupirHeader->page = ceil($absensiSupirHeader->position / (10));
            } else {
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
     * @Keterangan HAPUS DATA
     */
    public function destroy(AbsensiSupirHeaderRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            // dd($absensiSupirHeader);
            $absensiSupirHeader = (new AbsensiSupirHeader())->processDestroy($id);
            /* Set position and page */
            $selected = $this->getPosition($absensiSupirHeader, $absensiSupirHeader->getTable(), true);
            $absensiSupirHeader->position = $selected->position;
            $absensiSupirHeader->id = $selected->id;
            if ($request->limit == 0) {
                $absensiSupirHeader->page = ceil($absensiSupirHeader->position / (10));
            } else {
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

        $aksi = request()->aksi ?? '';
        $passes = true;
        $keterangan = [];

        if ($aksi == 'PRINTER BESAR' || $aksi == 'PRINTER KECIL') {
            //validasi cetak
            $printValidation = AbsensiSupirHeader::printValidation($id);
            if (!$printValidation) {
                $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'SDC')->get();
                $keterangan = $query['0'];
                $data = [
                    'message' => $keterangan,
                    'errors' => 'Sudah cetak',
                    'kodestatus' => '1',
                    'kodenobukti' => '1'
                ];
                $passes = false;
            } else {
                $data = [
                    'message' => '',
                    'errors' => 'success',
                    'kodestatus' => '0',
                    'kodenobukti' => '1'
                ];
            }
            return response($data);
        } else {

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

            //validasi cetak
            $printValidation = AbsensiSupirHeader::printValidation($id);
            if (!$printValidation) {
                $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'SDC')->get();
                $keterangan = $query['0'];
                $data = [
                    'message' => $keterangan,
                    'errors' => 'Sudah cetak',
                    'kodestatus' => '1',
                    'kodenobukti' => '1'
                ];
                $passes = false;

                // return response($data);
            }

            //validasi Hari ini
            $todayValidation = AbsensiSupirHeader::todayValidation($absensisupir->tglbukti);
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
    }
    /**
     * @ClassName 
     * @Keterangan CEK ABSENSI
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
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id)
    {
        $absensiSupirHeader = new AbsensiSupirHeader();
        return response([
            'data' => $absensiSupirHeader->getExport($id)
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report()
    {
    }

    /**
     * @ClassName 
     * @Keterangan APPROVAL BUKA CETAK
     */
    public function approvalbukacetak()
    {
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $absensiSupirHeader = AbsensiSupirHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($absensiSupirHeader->statuscetak != $statusSudahCetak->id) {
                $absensiSupirHeader->statuscetak = $statusSudahCetak->id;
                $absensiSupirHeader->tglbukacetak = date('Y-m-d H:i:s');
                $absensiSupirHeader->userbukacetak = auth('api')->user()->name;
                $absensiSupirHeader->jumlahcetak = $absensiSupirHeader->jumlahcetak + 1;
                if ($absensiSupirHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($absensiSupirHeader->getTable()),
                        'postingdari' => 'PRINT ABSENSI SUPIR HEADER',
                        'idtrans' => $absensiSupirHeader->id,
                        'nobuktitrans' => $absensiSupirHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $absensiSupirHeader->toArray(),
                        'modifiedby' => $absensiSupirHeader->modifiedby
                    ];
                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                    DB::commit();
                }
            }
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
