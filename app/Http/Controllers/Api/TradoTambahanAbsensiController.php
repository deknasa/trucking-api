<?php

namespace App\Http\Controllers\Api;

use App\Models\Error;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\TradoTambahanAbsensi;
use App\Http\Requests\StoreTradoTambahanAbsensiRequest;
use App\Http\Requests\UpdateTradoTambahanAbsensiRequest;
use App\Http\Requests\ApprovalTradoTambahanAbsensiRequest;

class TradoTambahanAbsensiController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $tradoTambahanAbsensi = new TradoTambahanAbsensi();
        return response([
            'data' => $tradoTambahanAbsensi->get(),
            'attributes' => [
                'totalRows' => $tradoTambahanAbsensi->totalRows,
                'totalPages' => $tradoTambahanAbsensi->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreTradoTambahanAbsensiRequest $request) {
        DB::beginTransaction();
        try {
            $data = [
                "tglabsensi" => $request->tglabsensi,
                "trado_id" => $request->trado_id,
                "trado" => $request->trado,
                "supir_id" => $request->supir_id,
                "supir" => $request->supir,
                "statusjeniskendaraan" => $request->statusjeniskendaraan,
                "statusjeniskendaraannama" => $request->statusjeniskendaraannama,
                "keterangan" => $request->keterangan,
            ];
            $tradoTambahanAbsensi = (new TradoTambahanAbsensi())->processStore($data);
            if ($request->button == 'btnSubmit') {
                $tradoTambahanAbsensi->position = $this->getPosition($tradoTambahanAbsensi, $tradoTambahanAbsensi->getTable())->position;
                if ($request->limit == 0) {
                    $tradoTambahanAbsensi->page = ceil($tradoTambahanAbsensi->position / (10));
                } else {
                    $tradoTambahanAbsensi->page = ceil($tradoTambahanAbsensi->position / ($request->limit ?? 10));
                }
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $tradoTambahanAbsensi
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $tradoTambahanAbsensi = (new TradoTambahanAbsensi())->findAll($id);
        return response([
            'data' => $tradoTambahanAbsensi
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateTradoTambahanAbsensiRequest $request, TradoTambahanAbsensi $tradotambahanabsensi){
       
        DB::beginTransaction();
        try {
            $data = [
                "tglabsensi" => $request->tglabsensi,
                "trado_id" => $request->trado_id,
                "trado" => $request->trado,
                "supir_id" => $request->supir_id,
                "supir" => $request->supir,
                "statusjeniskendaraan" => $request->statusjeniskendaraan,
                "statusjeniskendaraannama" => $request->statusjeniskendaraannama,
                "keterangan" => $request->keterangan,
            ];
            
            $tradoTambahanAbsensi = (new TradoTambahanAbsensi())->processUpdate($tradotambahanabsensi, $data);

            $tradoTambahanAbsensi->position = $this->getPosition($tradoTambahanAbsensi, $tradoTambahanAbsensi->getTable())->position;
            if ($request->limit == 0) {
                $tradoTambahanAbsensi->page = ceil($tradoTambahanAbsensi->position / (10));
            } else {
                $tradoTambahanAbsensi->page = ceil($tradoTambahanAbsensi->position / ($request->limit ?? 10));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $tradoTambahanAbsensi
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(Request $request, $id){
        DB::beginTransaction();

        try {
            $tradoTambahanAbsensi = (new TradoTambahanAbsensi())->processDestroy($id);
            $selected = $this->getPosition($tradoTambahanAbsensi, $tradoTambahanAbsensi->getTable(), true);
            $tradoTambahanAbsensi->position = $selected->position;
            $tradoTambahanAbsensi->id = $selected->id;
            if ($request->limit == 0) {
                $tradoTambahanAbsensi->page = ceil($tradoTambahanAbsensi->position / (10));
            } else {
                $tradoTambahanAbsensi->page = ceil($tradoTambahanAbsensi->position / ($request->limit ?? 10));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $tradoTambahanAbsensi
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan APRROVAL DATA
     */
    public function approval(ApprovalTradoTambahanAbsensiRequest $request){
        DB::beginTransaction();

        try {

            $data = [
                'tradoTambahanId' => $request->tradoTambahanId
            ];
            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            for ($i = 0; $i < count($data['tradoTambahanId']); $i++) {
                $tradoTambahanAbsensi = (new TradoTambahanAbsensi())->processApproval(["tradoTambahanId" => $data['tradoTambahanId'][$i]]);
                if ($tradoTambahanAbsensi) {
                    if ($tradoTambahanAbsensi->statusapproval == $statusApproval->id) {
                        (new TradoTambahanAbsensi())->processStoreToAbsensi($tradoTambahanAbsensi);
                    } else {
                        (new TradoTambahanAbsensi())->processDestroyToAbsensi($tradoTambahanAbsensi);
                    }
                }
            }

            DB::commit();
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
     * @Keterangan CETAK DATA
     */
    public function report(){

    }

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request){

    }
    public function cekvalidasi($id)
    {
        $tradoTambahanAbsensi = TradoTambahanAbsensi::find($id);
        
        $status = $tradoTambahanAbsensi->statusapproval;
        $statusApproval = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();

        if ($status == $statusApproval->id) {
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->where('kodeerror', '=', 'SAP')
                ->first();
            $data = [
                'error' => true,
                'message' => $query->keterangan,
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {

            if (!$tradoTambahanAbsensi->statusjeniskendaraan) {
                return response([
                    'error' => false,
                    'message' => '',
                    'statuspesan' => 'success',
                ]);
            }
            $query = DB::table('absensisupirdetail')->from(DB::raw("absensisupirdetail as detail with (readuncommitted)"))
                ->select('header.nobukti')
                ->whereRaw("detail.trado_id = $tradoTambahanAbsensi->trado_id and header.tglbukti = '$tradoTambahanAbsensi->tglabsensi' and detail.statusjeniskendaraan = $tradoTambahanAbsensi->statusjeniskendaraan and (detail.supir_id = $tradoTambahanAbsensi->supir_id or detail.supirold_id = $tradoTambahanAbsensi->supir_id)")
                ->leftJoin(DB::raw("absensisupirheader as header with (readuncommitted)"), 'header.id', 'detail.absensi_id')
                ->first();
            if ($query != '') {

                $data = DB::table("tradoTambahanAbsensi")->from(DB::raw("tradoTambahanAbsensi with (readuncommitted)"))
                    ->select(DB::raw("tradoTambahanAbsensi.trado_id, tradoTambahanAbsensi.tglabsensi, tradoTambahanAbsensi.supir_id, trado.kodetrado, supir.namasupir"))
                    ->leftJoin(DB::raw("trado with (readuncommitted)"), 'tradoTambahanAbsensi.trado_id', 'trado.id')
                    ->leftJoin(DB::raw("supir with (readuncommitted)"), 'tradoTambahanAbsensi.supir_id', 'supir.id')
                    ->where('tradoTambahanAbsensi.id', $id)
                    ->first();
                $keterangan = 'supir serap ' . $data->namasupir . ' di trado ' . $data->kodetrado . ' tgl ' . date('d-m-Y', strtotime($data->tglabsensi)) . ' SUDAH DIINPUT DI ABSENSI ' . $query->nobukti;

                $data = [
                    'error' => true,
                    'message' => $keterangan,
                    'statuspesan' => 'warning',
                ];
            } else {

                $data = [
                    'error' => false,
                    'message' => '',
                    'statuspesan' => 'success',
                ];
            }
            return response($data);
        }
    }

}
