<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyBiayaExtraSupirHeaderRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreBiayaExtraSupirDetailRequest;
use App\Http\Requests\StoreBiayaExtraSupirHeaderRequest;
use App\Http\Requests\UpdateBiayaExtraSupirHeaderRequest;
use App\Models\BiayaExtraSupirDetail;
use App\Models\BiayaExtraSupirHeader;
use App\Models\Error;
use App\Models\Parameter;

class BiayaExtraSupirHeaderController extends Controller
{
    /**
     * @ClassName 
     * BiayaExtraSupirHeaderController
     * @Detail BiayaExtraSupirDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $biayaExtraSupir = new BiayaExtraSupirHeader();
        return response([
            "data" => $biayaExtraSupir->get(),
            "attributes" => [
                'totalRows' => $biayaExtraSupir->totalRows,
                'totalPages' => $biayaExtraSupir->totalPages
            ]
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreBiayaExtraSupirHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'suratpengantar_nobukti' => $request->suratpengantar_nobukti,
                'keteranganbiaya' => $request->keteranganbiaya,
                'nominal' => $request->nominal,
                'nominaltagih' => $request->nominaltagih
            ];
            $biayaExtraSupir = (new BiayaExtraSupirHeader())->processStore($data);
            if ($request->button == 'btnSubmit') {
                $biayaExtraSupir->position = $this->getPosition($biayaExtraSupir, $biayaExtraSupir->getTable())->position;
                if ($request->limit == 0) {
                    $biayaExtraSupir->page = ceil($biayaExtraSupir->position / (10));
                } else {
                    $biayaExtraSupir->page = ceil($biayaExtraSupir->position / ($request->limit ?? 10));
                }
                $biayaExtraSupir->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
                $biayaExtraSupir->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $biayaExtraSupir
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $data = BiayaExtraSupirHeader::findAll($id);
        $detail = BiayaExtraSupirDetail::findAll($id);
        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }


    /**
     * @ClassName
     * @Keterangan EDIT DATA
     */

    public function update(UpdateBiayaExtraSupirHeaderRequest $request, BiayaExtraSupirHeader $biayaextrasupirheader)
    {
        DB::beginTransaction();
        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'suratpengantar_nobukti' => $request->suratpengantar_nobukti,
                'keteranganbiaya' => $request->keteranganbiaya,
                'nominal' => $request->nominal,
                'nominaltagih' => $request->nominaltagih
            ];
            /* Store header */
            $biayaExtraSupir = (new BiayaExtraSupirHeader())->processUpdate($biayaextrasupirheader, $data);
            /* Set position and page */
            $biayaExtraSupir->position = $this->getPosition($biayaExtraSupir, $biayaExtraSupir->getTable())->position;
            if ($request->limit == 0) {
                $biayaExtraSupir->page = ceil($biayaExtraSupir->position / (10));
            } else {
                $biayaExtraSupir->page = ceil($biayaExtraSupir->position / ($request->limit ?? 10));
            }
            $biayaExtraSupir->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $biayaExtraSupir->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));


            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $biayaExtraSupir
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
    public function destroy(DestroyBiayaExtraSupirHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $biayaExtraSupir = (new BiayaExtraSupirHeader())->processDestroy($id);
            $selected = $this->getPosition($biayaExtraSupir, $biayaExtraSupir->getTable(), true);
            $biayaExtraSupir->position = $selected->position;
            $biayaExtraSupir->id = $selected->id;
            if ($request->limit == 0) {
                $biayaExtraSupir->page = ceil($biayaExtraSupir->position / (10));
            } else {
                $biayaExtraSupir->page = ceil($biayaExtraSupir->position / ($request->limit ?? 10));
            }
            $biayaExtraSupir->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $biayaExtraSupir->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $biayaExtraSupir
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function cekvalidasi($id)
    {
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        $biayaExtraSupir = BiayaExtraSupirHeader::find($id);

        if (!isset($biayaExtraSupir)) {
            $keteranganerror = $error->cekKeteranganError('DTA') ?? '';
            $keterror = 'No Bukti <b>' . request()->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'message' => $keterror,
                'error' => true,
                'kodeerror' => 'SAP',
                'statuspesan' => 'warning',
            ];

            return response($data);
        }

        $parameter = new Parameter();

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));
        $user = auth('api')->user()->name;

        if ($tgltutup >= $biayaExtraSupir->tglbukti) {
            $keteranganerror = $error->cekKeteranganError('TUTUPBUKU') ?? '';
            $keterror = 'No Bukti <b>' . $biayaExtraSupir->nobukti . '</b><br>' . $keteranganerror . '<br> ( ' . date('d-m-Y', strtotime($tgltutup)) . ' ) <br> ' . $keterangantambahanerror;
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'TUTUPBUKU',
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

    public function cekValidasiAksi($id)
    {        
        $cekdata = (new BiayaExtraSupirHeader())->cekvalidasiaksi($id);
        if ($cekdata['kondisi'] == true) {
            $data = [
                'error' => true,
                'message' => $cekdata['keterangan'] ?? '',
                'statuspesan' => 'warning',
                'kodeerror' => $cekdata['kodeerror'],
                'editcoa' => $cekdata['editcoa']
            ];

            return response($data);
        } else {

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
                'editcoa' => false
            ];

            return response($data);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('biayaextrasupirheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
    public function addrow(StoreBiayaExtraSupirDetailRequest $request)
    {
       return true;
    }
}
