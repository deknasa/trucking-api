<?php

namespace App\Http\Controllers\Api;

use App\Models\SuratPengantar;
use App\Models\SuratPengantarBiayaTambahan;
use App\Models\Pelanggan;
use App\Models\UpahSupir;
use App\Models\UpahSupirRincian;
use App\Models\Container;
use App\Models\StatusContainer;
use App\Models\Trado;
use App\Models\Supir;
use App\Models\Agen;
use App\Models\JenisOrder;
use App\Models\Tarif;
use App\Models\TarifRincian;
use App\Models\Kota;
use App\Models\Parameter;
use App\Http\Requests\StoreSuratPengantarRequest;
use App\Http\Requests\UpdateSuratPengantarRequest;
use App\Http\Requests\DestroySuratPengantarRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRangeRequest;
use App\Models\OrderanTrucking;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\GetUpahSupirRangeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class SuratPengantarController extends Controller
{
    /**
     * @ClassName 
     * SuratPengantar
     * @Detail1 SuratPengantarBiayaTambahanController
     */
    public function index(GetIndexRangeRequest $request)
    {
        $suratPengantar = new SuratPengantar();

        return response([
            'data' => $suratPengantar->get(),
            'attributes' => [
                'totalRows' => $suratPengantar->totalRows,
                'totalPages' => $suratPengantar->totalPages
            ]
        ]);
    }

    public function default()
    {
        $suratPengantar = new SuratPengantar();
        return response([
            'status' => true,
            'data' => $suratPengantar->default()
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreSuratPengantarRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'jobtrucking' => $request->jobtrucking,
                'upah_id' => $request->upah_id,
                'container_id' => $request->container_id,
                'tglbukti' => $request->tglbukti,
                'keterangan' => $request->keterangan,
                'nourutorder' => $request->nourutorder,
                'dari_id' => $request->dari_id,
                'sampai_id' => $request->sampai_id,
                'statuscontainer_id' => $request->statuscontainer_id,
                'statusgandengan' => $request->statusgandengan,
                'trado_id' => $request->trado_id,
                'supir_id' => $request->supir_id,
                'gandengan_id' => $request->gandengan_id,
                'statuslongtrip' => $request->statuslongtrip,
                'statusperalihan' => $request->statusperalihan,
                'nominalperalihan' => $request->nominalperalihan,
                'biayatambahan_id' => $request->biayatambahan_id,
                'penyesuaian' => $request->penyesuaian,
                'nosp' => $request->nosp,
                'nosptagihlain' => $request->nosptagihlain,
                'qtyton' => $request->qtyton,
                'statusgudangsama' => $request->statusgudangsama,
                'statusbatalmuat' => $request->statusbatalmuat,
                'gudang' => $request->gudang,
                'keterangan_detail' => $request->keterangan_detail,
                'nominal' => $request->nominal,
                'nominalTagih' => $request->nominalTagih,

            ];
            $suratPengantar = (new SuratPengantar())->processStore($data);
            $suratPengantar->position = $this->getPosition($suratPengantar, $suratPengantar->getTable())->position;
            $suratPengantar->page = ceil($suratPengantar->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $suratPengantar
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {

        $data = SuratPengantar::findAll($id);
        $detail = SuratPengantarBiayaTambahan::where('suratpengantar_id', $id)->get();
        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateSuratPengantarRequest $request, SuratPengantar $suratpengantar): JsonResponse
    {

        DB::beginTransaction();
        try {
            $data = [
                'jobtrucking' => $request->jobtrucking,
                'upah_id' => $request->upah_id,
                'tarif_id' => $request->tarifrincian_id,
                'container_id' => $request->container_id,
                'tglbukti' => $request->tglbukti,
                'keterangan' => $request->keterangan,
                'nourutorder' => $request->nourutorder,
                'dari_id' => $request->dari_id,
                'sampai_id' => $request->sampai_id,
                'statuscontainer_id' => $request->statuscontainer_id,
                'statusgandengan' => $request->statusgandengan,
                'trado_id' => $request->trado_id,
                'supir_id' => $request->supir_id,
                'penyesuaian' => $request->penyesuaian,
                'gandengan_id' => $request->gandengan_id,
                'statuslongtrip' => $request->statuslongtrip,
                'statusperalihan' => $request->statusperalihan,
                'nominalperalihan' => $request->nominalperalihan,
                'persentaseperalihan' => $request->persentaseperalihan,
                'biayatambahan_id' => $request->biayatambahan_id,
                'nosp' => $request->nosp,
                'nosptagihlain' => $request->nosptagihlain,
                'qtyton' => $request->qtyton,
                'statusgudangsama' => $request->statusgudangsama,
                'statusbatalmuat' => $request->statusbatalmuat,
                'statusupahzona' => $request->statusupahzona,
                'gudang' => $request->gudang,
                'lokasibongkarmuat' => $request->lokasibongkarmuat,
                'keterangan_detail' => $request->keterangan_detail,
                'nominal' => $request->nominal,
                'nominalTagih' => $request->nominalTagih,
                'komisisupir' => $request->komisisupir,
                'gajikenek' => $request->gajikenek
            ];
            $suratPengantar = (new SuratPengantar())->processUpdate($suratpengantar, $data);
            $suratPengantar->position = $this->getPosition($suratPengantar, $suratPengantar->getTable())->position;
            if ($request->limit == 0) {
                $suratPengantar->page = ceil($suratPengantar->position / (10));
            } else {
                $suratPengantar->page = ceil($suratPengantar->position / ($request->limit ?? 10));
            }
            // $suratPengantar->position = $suratpengantar->id;
            // $suratPengantar->page = 1;


            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $suratPengantar
            ]);
        } catch (\Throwable $th) {

            DB::rollBack();
            throw $th;
        }
    }

    public function getpelabuhan($id)
    {

        $suratpengantar = new SuratPengantar();
        return response([
            "data" => $suratpengantar->getpelabuhan($id)
        ]);
    }

    /**
     * @ClassName 
     */
    public function destroy(DestroySuratPengantarRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $suratPengantar = (new SuratPengantar())->processDestroy($id);
            $selected = $this->getPosition($suratPengantar, $suratPengantar->getTable(), true);
            $suratPengantar->position = $selected->position;
            $suratPengantar->id = $selected->id;
            if ($request->limit == 0) {
                $suratPengantar->page = ceil($suratPengantar->position / (10));
            } else {
                $suratPengantar->page = ceil($suratPengantar->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $suratPengantar
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('suratpengantar')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function cekUpahSupir(Request $request)
    {

        $upahSupir =  DB::table('upahsupir')->from(
            DB::raw("upahsupir with (readuncommitted)")
        )
            ->select('upahsupirrincian.nominalsupir', 'upahsupirrincian.nominalkenek', 'upahsupirrincian.nominalkomisi')
            ->join(DB::raw("upahsupirrincian with (readuncommitted)"), 'upahsupir.id', 'upahsupirrincian.upahsupir_id')
            ->where('upahsupir.kotadari_id', $request->dari_id)
            ->where('upahsupir.kotasampai_id', $request->sampai_id)
            ->where('upahsupirrincian.container_id', $request->container_id)
            ->where('upahsupirrincian.statuscontainer_id', $request->statuscontainer_id)
            ->first();

        if (!isset($upahSupir)) {
            $upahSupir =  DB::table('upahsupir')->from(
                DB::raw("upahsupir with (readuncommitted)")
            )
                ->select('upahsupirrincian.nominalsupir', 'upahsupirrincian.nominalkenek', 'upahsupirrincian.nominalkomisi')
                ->join(DB::raw("upahsupirrincian with (readuncommitted)"), 'upahsupir.id', 'upahsupirrincian.upahsupir_id')
                ->where('upahsupir.kotasampai_id', $request->dari_id)
                ->where('upahsupir.kotadari_id', $request->sampai_id)
                ->where('upahsupirrincian.container_id', $request->container_id)
                ->where('upahsupirrincian.statuscontainer_id', $request->statuscontainer_id)
                ->first();
        }
        if ($upahSupir != null) {
            $data = [
                'message' => '',
                'errors' => 'belum approve',
                'kodestatus' => '1',
            ];

            return response($data);
        } else {
            $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'USBA')
                ->first();
            return response([
                'message' => "$query->keterangan",
            ], 422);
        }
    }

    public function cekValidasi($id, Request $request)
    {

        $nobuktilist = $request->nobukti ?? '';



        $querysp = DB::table('suratpengantar')->from(
            DB::raw("suratpengantar a with (readuncommitted)")
        )
            ->select('a.id')
            ->where('a.nobukti', $nobuktilist)
            ->first();
        if (isset($querysp)) {
            goto validasilanjut;
        } else {

            $data1 = [
                'kondisi' => true,
                'keterangan' => '',
            ];

            $edit = true;
            $query = DB::table('error')
                ->select(
                    DB::raw("'No Bukti " . $nobuktilist . " '+ltrim(rtrim(keterangan)) as keterangan")
                )
                ->where('kodeerror', '=', 'BMS')
                ->get();
            $keterangan = $query['0'];
            $data = [
                'status' => false,
                'message' => $keterangan,
                'errors' => '',
                'edit' => $edit,
                'kondisi' => $data1['kondisi'],
            ];

            return response($data);
        }

        validasilanjut:;
        $suratPengantar = new SuratPengantar();
        $nobukti = DB::table('SuratPengantar')->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->where('id', $id)->first();
        //validasi Hari ini
        // $todayValidation = SuratPengantar::todayValidation($nobukti->id);
        $isEditAble = SuratPengantar::isEditAble($nobukti->id);

        $edit = true;
        // if (!$todayValidation) {
        //     $edit = false;
        // if ($isEditAble) {
        //     $edit = true;
        // }
        // }
        // else {
        if (!$isEditAble) {
            $edit = false;
        }
        // }

        $cekdata = $suratPengantar->cekvalidasihapus($nobukti->nobukti, $nobukti->jobtrucking);
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
                'edit' => $edit,
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        } else {
            $data = [
                'status' => false,
                'message' => '',
                'errors' => '',
                'edit' => $edit,
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        }
    }

    public function getTarifOmset($id)
    {

        $iddata = $id ?? 0;
        $tarifrincian = new TarifRincian();
        $omset = $tarifrincian->getid($iddata);


        return response([
            "dataTarif" => $omset
        ]);
    }

    public function getOrderanTrucking($id)
    {

        $suratPengantar = new SuratPengantar();
        return response([
            "data" => $suratPengantar->getOrderanTrucking($id)
        ]);
    }
    /**
     * @ClassName 
     */
    public function approvalBatalMuat($id)
    {
        DB::beginTransaction();
        try {
            $suratPengantar = SuratPengantar::lockForUpdate()->findOrFail($id);

            $statusBatalMuat = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS BATAL MUAT')->where('text', '=', 'BATAL MUAT')->first();
            $statusBukanBatalMuat = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS BATAL MUAT')->where('text', '=', 'BUKAN BATAL MUAT')->first();
            // statusapprovaleditabsensi,tglapprovaleditabsensi,userapprovaleditabsensi 
            if ($suratPengantar->statusbatalmuat == $statusBatalMuat->id) {
                $suratPengantar->statusbatalmuat = $statusBukanBatalMuat->id;
                $aksi = $statusBukanBatalMuat->text;
            } else {
                $suratPengantar->statusbatalmuat = $statusBatalMuat->id;
                $aksi = $statusBatalMuat->text;
            }

            if ($suratPengantar->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($suratPengantar->getTable()),
                    'postingdari' => 'APPROVED BATAL MUAT',
                    'idtrans' => $suratPengantar->id,
                    'nobuktitrans' => $suratPengantar->id,
                    'aksi' => $aksi,
                    'datajson' => $suratPengantar->toArray(),
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
    /**
     * @ClassName 
     */
    public function approvalEditTujuan($id)
    {
        DB::beginTransaction();
        try {
            $suratPengantar = SuratPengantar::lockForUpdate()->findOrFail($id);

            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            $jambatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', '=', 'JAMBATASAPPROVAL')->where('subgrp', '=', 'JAMBATASAPPROVAL')->first();
            $tglbatas = date('Y-m-d') . ' ' . $jambatas->text ?? '00:00:00';
            // statusapprovaleditabsensi,tglapprovaleditabsensi,userapprovaleditabsensi 
            if ($suratPengantar->statusapprovaleditsuratpengantar == $statusApproval->id) {
                $suratPengantar->statusapprovaleditsuratpengantar = $statusNonApproval->id;
                $suratPengantar->tglapprovaleditsuratpengantar = date('Y-m-d', strtotime("1900-01-01"));
                $suratPengantar->tglbataseditsuratpengantar = '';
                $suratPengantar->userapprovaleditsuratpengantar = '';
                $aksi = $statusNonApproval->text;
            } else {
                $suratPengantar->statusapprovaleditsuratpengantar = $statusApproval->id;
                $suratPengantar->tglapprovaleditsuratpengantar = date('Y-m-d H:i:s');
                $suratPengantar->tglbataseditsuratpengantar = $tglbatas;
                $suratPengantar->userapprovaleditsuratpengantar = auth('api')->user()->name;
                $aksi = $statusApproval->text;
            }

            if ($suratPengantar->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($suratPengantar->getTable()),
                    'postingdari' => "$aksi EDIT SURAT PENGANTAR",
                    'idtrans' => $suratPengantar->id,
                    'nobuktitrans' => $suratPengantar->nobukti,
                    'aksi' => $aksi,
                    'datajson' => $suratPengantar->toArray(),
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

    /**
     * @ClassName 
     */
    public function approvalTitipanEmkl($id)
    {
        DB::beginTransaction();
        try {
            $suratPengantar = SuratPengantar::lockForUpdate()->findOrFail($id);

            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            $jambatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', '=', 'JAMBATASAPPROVAL')->where('subgrp', '=', 'JAMBATASAPPROVAL')->first();
            $tglbatas = date('Y-m-d') . ' ' . $jambatas->text ?? '00:00:00';
            
            if ($suratPengantar->statusapprovalbiayatitipanemkl == $statusApproval->id) {
                $suratPengantar->statusapprovalbiayatitipanemkl = $statusNonApproval->id;
                $suratPengantar->tglapprovalbiayatitipanemkl = date('Y-m-d', strtotime("1900-01-01"));
                $suratPengantar->userapprovalbiayatitipanemkl = '';
                $aksi = $statusNonApproval->text;
            } else {
                $suratPengantar->statusapprovalbiayatitipanemkl = $statusApproval->id;
                $suratPengantar->tglapprovalbiayatitipanemkl = date('Y-m-d H:i:s');
                $suratPengantar->userapprovalbiayatitipanemkl = auth('api')->user()->name;
                $aksi = $statusApproval->text;
            }

            if ($suratPengantar->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($suratPengantar->getTable()),
                    'postingdari' => "$aksi TITIPAN EMKL SURAT PENGANTAR",
                    'idtrans' => $suratPengantar->id,
                    'nobuktitrans' => $suratPengantar->nobukti,
                    'aksi' => $aksi,
                    'datajson' => $suratPengantar->toArray(),
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

    public function getGaji($dari, $sampai, $container, $statuscontainer)
    {


        $data = DB::table('upahsupir')
            ->select('upahsupirrincian.nominalsupir', 'upahsupirrincian.nominalkenek', 'upahsupirrincian.nominalkomisi')
            ->join('upahsupirrincian', 'upahsupir.id', 'upahsupirrincian.upahsupir_id')
            ->where('upahsupir.kotadari_id', $dari)
            ->where('upahsupir.kotasampai_id', $sampai)
            ->where('upahsupirrincian.container_id', $container)
            ->where('upahsupirrincian.statuscontainer_id', $statuscontainer)

            //  dd($data->toSql());
            ->first();

        // dd($data);
        if ($data != null) {
            return response([
                'data' => $data
            ]);
        } else {
            $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'USBA')
                ->first();
            return response([
                'message' => "$query->keterangan",
            ], 422);
        }
    }

    /**
     * @ClassName 
     */
    public function report()
    {
    }

    /**
     * @ClassName 
     */
    public function export(GetUpahSupirRangeRequest $request)
    {
        $dari = date('Y-m-d', strtotime($request->dari));
        $sampai = date('Y-m-d', strtotime($request->sampai));
        $suratPengantar = new SuratPengantar();
        return response([
            'data' => $suratPengantar->getExport($dari, $sampai),
        ]);
    }

    public function addrow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'keterangan_detail.*' => 'required',
            'nominal.*' => 'required|numeric|gt:0',
            'nominalTagih.*' => 'required|numeric|gt:0',

        ], [
            'keterangan_detail.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            'nominal.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            'nominalTagih.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
        ], [
            'keterangan_detail' => 'keterangan',
            'nominal' => 'nominal',
            'nominalTagih' => 'nominal Tagih',
            'keterangan_detail.*' => 'keterangan',
            'nominal.*' => 'nominal',
            'nominalTagih.*' => 'nominal Tagih',
        ]);
        if ($validator->fails()) {
        
            return response()->json( [
                "message"=> "The given data was invalid.",
                "errors"=> $validator->messages()
            ],422);
        }
        return true;
    }
}
