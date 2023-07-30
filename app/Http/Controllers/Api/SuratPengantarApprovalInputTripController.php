<?php

namespace App\Http\Controllers\Api;

use App\Models\SuratPengantarApprovalInputTrip;
use App\Http\Controllers\Controller;
use App\Http\Requests\DestroySuratPengantarApprovalInputTripRequest;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSuratPengantarApprovalInputTripRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateSuratPengantarApprovalInputTripRequest;
use App\Models\ApprovalBukaTanggalSuratPengantar;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SuratPengantarApprovalInputTripController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $suratPengantarApprovalInputTrip = new SuratPengantarApprovalInputTrip();

        return response([
            'data' => $suratPengantarApprovalInputTrip->get(),
            'attributes' => [
                'totalRows' => $suratPengantarApprovalInputTrip->totalRows,
                'totalPages' => $suratPengantarApprovalInputTrip->totalPages
            ]
        ]);
    }

    public function default()
    {

        $suratPengantarApprovalInputTrip = new SuratPengantarApprovalInputTrip();
        return response([
            'status' => true,
            'data' => $suratPengantarApprovalInputTrip->default(),
        ]);
    }


    /**
     * @ClassName
     */
    public function store(StoreSuratPengantarApprovalInputTripRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'jumlahtrip' => $request->jumlahtrip,
                'statusapproval' => $request->statusapproval,
            ];
            $approvalTrip = (new SuratPengantarApprovalInputTrip())->processStore($data);
            $approvalTrip->position = $this->getPosition($approvalTrip, $approvalTrip->getTable())->position;
            if ($request->limit==0) {
                $approvalTrip->page = ceil($approvalTrip->position / (10));
            } else {
                $approvalTrip->page = ceil($approvalTrip->position / ($request->limit ?? 10));
            }
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan.',
                'data' => $approvalTrip
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(SuratPengantarApprovalInputTrip $suratpengantarapprovalinputtrip)
    {
        return response([
            'status' => true,
            'data' => $suratpengantarapprovalinputtrip
        ]);
    }

    /**
     * @ClassName
     */
    public function update(UpdateSuratPengantarApprovalInputTripRequest $request, SuratPengantarApprovalInputTrip $suratpengantarapprovalinputtrip): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'jumlahtrip' => $request->jumlahtrip,
                'statusapproval' => $request->statusapproval,
            ];
            $approvalBukaTanggal = (new SuratPengantarApprovalInputTrip())->processUpdate($suratpengantarapprovalinputtrip, $data);
            $approvalBukaTanggal->position = $this->getPosition($approvalBukaTanggal, $approvalBukaTanggal->getTable())->position;
            if ($request->limit==0) {
                $approvalBukaTanggal->page = ceil($approvalBukaTanggal->position / (10));
            } else {
                $approvalBukaTanggal->page = ceil($approvalBukaTanggal->position / ($request->limit ?? 10));
            }
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah.',
                'data' => $approvalBukaTanggal
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function destroy(DestroySuratPengantarApprovalInputTripRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $approvalBukaTanggal = (new SuratPengantarApprovalInputTrip())->processDestroy($id);
            $selected = $this->getPosition($approvalBukaTanggal, $approvalBukaTanggal->getTable(), true);
            $approvalBukaTanggal->position = $selected->position;
            $approvalBukaTanggal->id = $selected->id;
            if ($request->limit==0) {
                $approvalBukaTanggal->page = ceil($approvalBukaTanggal->position / (10));
            } else {
                $approvalBukaTanggal->page = ceil($approvalBukaTanggal->position / ($request->limit ?? 10));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $approvalBukaTanggal
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function isTanggalAvaillable()
    {
        $suratPengantarApprovalInputTrip = new SuratPengantarApprovalInputTrip;
        return response([
            'status' => true,
            'data' => $suratPengantarApprovalInputTrip->isTanggalAvaillable()
        ], 201);
    }

    
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('suratpengantarapprovalinputtrip')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function updateApproval()
    {
        DB::beginTransaction();
        try {
            $approvalBukaTanggal = (new SuratPengantarApprovalInputTrip())->updateApproval();
            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $approvalBukaTanggal
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    
    public function cekvalidasi($id)
    {
        $approvalBukaTanggal = new SuratPengantarApprovalInputTrip();
        $cekdata = $approvalBukaTanggal->cekvalidasiaksi($id);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', $cekdata['kodeerror'])
                ->first();

            $data = [
                'error' => true,
                'message' => $query->keterangan,
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }
}
