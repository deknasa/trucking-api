<?php

namespace App\Http\Controllers\Api;

use App\Models\TripInap;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalTripInapRequest;
use App\Http\Requests\StoreTripInapRequest;
use App\Http\Requests\UpdateTripInapRequest;

class TripInapController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $tripInap = new TripInap();
        return response([
            'data' => $tripInap->get(),
            'attributes' => [
                'totalRows' => $tripInap->totalRows,
                'totalPages' => $tripInap->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreTripInapRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = [
                "absensi_id" => $request->absensi_id,
                "tglabsensi" => $request->tglabsensi,
                "trado_id" => $request->trado_id,
                "trado" => $request->trado,
                "suratpengantar_nobukti" => $request->suratpengantar_nobukti,
                "jammasukinap" => $request->jammasukinap,
                "jamkeluarinap" => $request->jamkeluarinap,
            ];

            $tripInap = (new TripInap())->processStore($data);
            $tripInap->position = $this->getPosition($tripInap, $tripInap->getTable())->position;
            if ($request->limit == 0) {
                $tripInap->page = ceil($tripInap->position / (10));
            } else {
                $tripInap->page = ceil($tripInap->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $tripInap
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(TripInap $tripinap)
    {
        return response([
            'data' => (new TripInap())->findAll($tripinap),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateTripInapRequest $request, TripInap $tripinap)
    {
        DB::beginTransaction();
        try {
            $data = [
                "absensi_id" => $request->absensi_id,
                "tglabsensi" => $request->tglabsensi,
                "trado_id" => $request->trado_id,
                "trado" => $request->trado,
                "suratpengantar_nobukti" => $request->suratpengantar_nobukti,
                "jammasukinap" => $request->jammasukinap,
                "jamkeluarinap" => $request->jamkeluarinap,
            ];
            $tripInap = (new TripInap())->processUpdate($tripinap, $data);
            $tripInap->position = $this->getPosition($tripInap, $tripInap->getTable())->position;
            if ($request->limit == 0) {
                $tripInap->page = ceil($tripInap->position / (10));
            } else {
                $tripInap->page = ceil($tripInap->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $tripInap
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
    public function destroy(Request $request, TripInap $tripinap)
    {
        DB::beginTransaction();

        try {
            $tripInap = (new TripInap())->processDestroy($tripinap->id, 'DELETE TRIP INAP');
            $selected = $this->getPosition($tripInap, $tripInap->getTable(), true);
            $tripInap->position = $selected->position;
            $tripInap->id = $selected->id;
            if ($request->limit == 0) {
                $tripInap->page = ceil($tripInap->position / (10));
            } else {
                $tripInap->page = ceil($tripInap->position / ($request->limit ?? 10));
            }
            $tripInap->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $tripInap->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $tripInap
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
    public function approval(ApprovalTripInapRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = [
                'Id' => $request->Id,
            ];
            $tripInap = (new TripInap())->processApprove($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
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
            $tarifs[$i]['jammasukinap'] = date('H:i', strtotime($params['jammasukinap']));
            $tarifs[$i]['jamkeluarinap'] = date('H:i', strtotime($params['jamkeluarinap']));


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
                'label' => 'Surat Pengantar',
                'index' => 'suratpengantar_nobukti',
            ],
            [
                'label' => 'Jam Masuk Inap',
                'index' => 'jammasukinap',
            ],
            [
                'label' => 'Jam Keluar Inap',
                'index' => 'jamkeluarinap',
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

        $this->toExcel('Laporan Trip Inap', $tarifs, $columns);
    }
}
