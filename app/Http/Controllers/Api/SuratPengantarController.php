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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SuratPengantarController extends Controller
{
    /**
     * @ClassName 
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
            $suratPengantar = (new SuratPengantar())->processStore($request->all());
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
            $suratPengantar = (new SuratPengantar())->processUpdate($suratpengantar, $request->all());
            $suratPengantar->position = $this->getPosition($suratPengantar, $suratPengantar->getTable())->position;
            $suratPengantar->page = ceil($suratPengantar->position / ($request->limit ?? 10));

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
            $suratPengantar->page = ceil($suratPengantar->position / ($request->limit ?? 10));

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

    public function cekValidasi($id)
    {
     
        $suratPengantar = new SuratPengantar();
        $nobukti = DB::table('SuratPengantar')->from(DB::raw("suratpengantar with (readuncommitted)"))
        ->where('id', $id)->first();
        //validasi Hari ini
        $todayValidation = SuratPengantar::todayValidation($nobukti->id);
        $isEditAble = SuratPengantar::isEditAble($nobukti->id);
        $edit =true;
        if(!$todayValidation && !$isEditAble){
            $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'SATL')->get();
            $keterangan = $query['0'];
            $edit = false;
        }



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
        try{
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

        }catch (\Throwable $th) {
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
        try{
            $suratPengantar = SuratPengantar::lockForUpdate()->findOrFail($id);

            $statusEditTujuan = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS EDIT TUJUAN')->where('text', '=', 'EDIT TUJUAN')->first();
            $statusTidakBolehEditTujuan = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS EDIT TUJUAN')->where('text', '=', 'TIDAK BOLEH EDIT TUJUAN')->first();
            // statusapprovaleditabsensi,tglapprovaleditabsensi,userapprovaleditabsensi 
            if ($suratPengantar->statusedittujuan == $statusEditTujuan->id) {
                $suratPengantar->statusedittujuan = $statusTidakBolehEditTujuan->id;
                $aksi = $statusTidakBolehEditTujuan->text;
            } else {
                $suratPengantar->statusedittujuan = $statusEditTujuan->id;
                $aksi = $statusEditTujuan->text;
            }

            if ($suratPengantar->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($suratPengantar->getTable()),
                    'postingdari' => 'APPROVED EDIT TUJUAN',
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

        }catch (\Throwable $th) {
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
}
