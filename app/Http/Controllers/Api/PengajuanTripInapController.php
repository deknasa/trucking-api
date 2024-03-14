<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Models\PengajuanTripInap;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalPengajuanTripInapRequest;
use App\Http\Requests\DestroyPengajuanTripInapRequest;
use App\Http\Requests\StorePengajuanTripInapRequest;
use App\Http\Requests\UpdatePengajuanTripInapRequest;
use App\Models\Error;
use App\Models\Parameter;

use function PHPUnit\Framework\isEmpty;

class PengajuanTripInapController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $pengajuanTripInap = new PengajuanTripInap();
        return response([
            'data' => $pengajuanTripInap->get(),
            'attributes' => [
                'totalRows' => $pengajuanTripInap->totalRows,
                'totalPages' => $pengajuanTripInap->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StorePengajuanTripInapRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = [
                "absensi_id" => $request->absensi_id,
                "tglabsensi" => $request->tglabsensi,
                "trado_id" => $request->trado_id,
                "supir_id" => $request->supir_id,
            ];

            $pengajuanTripInap = (new PengajuanTripInap())->processStore($data);
            $pengajuanTripInap->position = $this->getPosition($pengajuanTripInap, $pengajuanTripInap->getTable())->position;
            if ($request->limit == 0) {
                $pengajuanTripInap->page = ceil($pengajuanTripInap->position / (10));
            } else {
                $pengajuanTripInap->page = ceil($pengajuanTripInap->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pengajuanTripInap
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(PengajuanTripInap $pengajuantripinap)
    {
        return response([
            'data' => (new PengajuanTripInap())->findAll($pengajuantripinap),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdatePengajuanTripInapRequest $request, PengajuanTripInap $pengajuantripinap)
    {
        DB::beginTransaction();
        try {
            $data = [
                "absensi_id" => $request->absensi_id,
                "tglabsensi" => $request->tglabsensi,
                "trado_id" => $request->trado_id,
                "supir_id" => $request->supir_id,
                "trado" => $request->trado,
            ];
            $pengajuanTripInap = (new PengajuanTripInap())->processUpdate($pengajuantripinap, $data);
            $pengajuanTripInap->position = $this->getPosition($pengajuanTripInap, $pengajuanTripInap->getTable())->position;
            if ($request->limit == 0) {
                $pengajuanTripInap->page = ceil($pengajuanTripInap->position / (10));
            } else {
                $pengajuanTripInap->page = ceil($pengajuanTripInap->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pengajuanTripInap
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
    public function destroy(DestroyPengajuanTripInapRequest $request, PengajuanTripInap $pengajuantripinap)
    {
        DB::beginTransaction();

        try {
            $pengajuanTripInap = (new PengajuanTripInap())->processDestroy($pengajuantripinap->id, 'DELETE Trip Inap');
            $selected = $this->getPosition($pengajuanTripInap, $pengajuanTripInap->getTable(), true);
            $pengajuanTripInap->position = $selected->position;
            $pengajuanTripInap->id = $selected->id;
            if ($request->limit == 0) {
                $pengajuanTripInap->page = ceil($pengajuanTripInap->position / (10));
            } else {
                $pengajuanTripInap->page = ceil($pengajuanTripInap->position / ($request->limit ?? 10));
            }
            $pengajuanTripInap->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pengajuanTripInap->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $pengajuanTripInap
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName
     * @Keterangan APPROVAL DATA
     */
    public function approval(ApprovalPengajuanTripInapRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = [
                'Id' => $request->Id,
            ];
            $pengajuanTripInap = (new PengajuanTripInap())->processApprove($data);

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
    public function report()
    {
    }

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export()
    {

        $dari = date('Y-m-d', strtotime(request()->dari));
        $sampai = date('Y-m-d', strtotime(request()->sampai));
        $cekData = DB::table("pengajuantripinap")->from(DB::raw("pengajuantripinap with (readuncommitted)"))
            ->whereBetween('tglabsensi', [$dari, $sampai])
            ->first();

        if ($cekData != null) {
            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $tarifs = $decodedResponse['data'];
            $i = 0;
            foreach ($tarifs as $index => $params) {

                // $tarifRincian = new TarifRincian();

                $statusapproval = $params['statusapproval'];

                $result = json_decode($statusapproval, true);

                $statusapproval = $result['MEMO'];

                $tarifs[$i]['statusapproval'] = $statusapproval;

                // $tarifs[$i]['rincian'] = json_decode($tarifRincian->getAll($tarifs[$i]['id']), true);


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Tgl Absensi',
                    'index' => 'tglabsensi',
                ],
                [
                    'label' => 'Trado',
                    'index' => 'trado',
                ],
                [
                    'label' => 'Supir',
                    'index' => 'supir',
                ],
                [
                    'label' => 'Status Approval',
                    'index' => 'statusapproval',
                ],
                [
                    'label' => 'Tgl Approval',
                    'index' => 'tglapproval',
                ],
                [
                    'label' => 'User Approval',
                    'index' => 'userapproval',
                ],

            ];

            $this->toExcel('Laporan Pengajuan Trip Inap', $tarifs, $columns);
        } else {
            return response([
                'errors' => [
                    "export" => "tidak ada data"
                ],
                'message' => "The given data was invalid.",
            ], 422);
        }
    }

    public function cekValidasi($id)
    {
        $pengajuan = PengajuanTripInap::find($id);
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        if ($pengajuan == '') {
            $keterangan = $error->cekKeteranganError('DTA') ?? '';

            $keterror = $keterangan . ' <br> ' . $keterangantambahanerror;
            $data = [
                'message' => $keterror,
                'error' => true,
                'kodeerror' => 'SAP',
                'statuspesan' => 'warning',
            ];
            return response($data);
        }

        $status = $pengajuan->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        if ($status == $statusApproval->id) {
            $keteranganerror = $error->cekKeteranganError('SAP') ?? '';
            $getDetail = DB::table("pengajuantripinap")->from(DB::raw("pengajuantripinap with (readuncommitted)"))
                ->select('trado.kodetrado', 'supir.namasupir', 'pengajuantripinap.tglabsensi')
                ->join(DB::raw("trado with (readuncommitted)"), 'pengajuantripinap.trado_id', 'trado.id')
                ->join(DB::raw("supir with (readuncommitted)"), 'pengajuantripinap.supir_id', 'supir.id')
                ->where('pengajuantripinap.id', $id)
                ->first();

            $keterror = 'Absensi <b>' . date('d-m-Y', strtotime($getDetail->tglabsensi)) . '</b> trado <b>' . $getDetail->kodetrado . '</b> supir <b>' . $getDetail->namasupir . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'message' => $keterror,
                'error' => true,
                'kodeerror' => 'SAP',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else  if ($pengajuan->statusapprovallewatbataspengajuan == $statusApproval->id) {
            $keteranganerror = $error->cekKeteranganError('SAP') ?? '';
            $getDetail = DB::table("pengajuantripinap")->from(DB::raw("pengajuantripinap with (readuncommitted)"))
                ->select('trado.kodetrado', 'supir.namasupir', 'pengajuantripinap.tglabsensi')
                ->join(DB::raw("trado with (readuncommitted)"), 'pengajuantripinap.trado_id', 'trado.id')
                ->join(DB::raw("supir with (readuncommitted)"), 'pengajuantripinap.supir_id', 'supir.id')
                ->where('pengajuantripinap.id', $id)
                ->first();

            $keterror = 'Absensi <b>' . date('d-m-Y', strtotime($getDetail->tglabsensi)) . '</b> trado <b>' . $getDetail->kodetrado . '</b> supir <b>' . $getDetail->namasupir . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'message' => $keterror,
                'error' => true,
                'kodeerror' => 'SAP',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {

            $data = [
                'message' => '',
                'error' => false,
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }


    /**
     * @ClassName
     * @Keterangan APPROVAL BATAS PENGAJUAN TRIP INAP
     */
    public function approvalbataspengajuan(ApprovalPengajuanTripInapRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = [
                'Id' => $request->Id,
            ];
            $pengajuanTripInap = (new PengajuanTripInap())->processApproveBatasPengajuan($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
