<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Error;
use App\Models\Trado;
use App\Models\Mekanik;
use App\Models\MyModel;
use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Models\ServiceInDetail;
use App\Models\ServiceInHeader;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreServiceInHeaderRequest;
use App\Http\Requests\UpdateServiceInHeaderRequest;
use App\Http\Requests\DestroyServiceInHeaderRequest;

class ServiceInHeaderController extends Controller
{
    /**
     * @ClassName 
     * ServiceInHeaderHeader
     * @Detail ServiceInDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $serviceInHeader = new ServiceInHeader();

        return response([
            'data' => $serviceInHeader->get(),
            'attributes' => [
                'totalRows' => $serviceInHeader->totalRows,
                'totalPages' => $serviceInHeader->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreServiceInHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'trado_id' => $request->trado_id,
                'tglmasuk' => $request->tglmasuk,
                'statusserviceout' => $request->statusserviceout,
                'karyawan_id' => $request->karyawan_id,
                'statusserviceout' => $request->statusserviceout,
                'keterangan_detail' => $request->keterangan_detail,
            ];
            $serviceInHeader = (new ServiceInHeader())->processStore($data);
            $serviceInHeader->position = $this->getPosition($serviceInHeader, $serviceInHeader->getTable())->position;
            if ($request->limit == 0) {
                $serviceInHeader->page = ceil($serviceInHeader->position / (10));
            } else {
                $serviceInHeader->page = ceil($serviceInHeader->position / ($request->limit ?? 10));
            }
            $serviceInHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $serviceInHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $serviceInHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show($id)
    {
        $serviceInHeader = (new ServiceInHeader)->findAll($id);
        $serviceInDetails = (new ServiceInDetail)->getAll($id);

        return response([
            'status' => true,
            'data' => $serviceInHeader,
            'detail' => $serviceInDetails
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateServiceInHeaderRequest $request, ServiceInHeader $serviceInHeader): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'trado_id' => $request->trado_id,
                'tglmasuk' => $request->tglmasuk,
                'statusserviceout' => $request->statusserviceout,
                'karyawan_id' => $request->karyawan_id,
                'statusserviceout' => $request->statusserviceout,
                'keterangan_detail' => $request->keterangan_detail,
            ];
            $serviceInHeader = (new ServiceInHeader())->processUpdate($serviceInHeader, $data);
            $serviceInHeader->position = $this->getPosition($serviceInHeader, $serviceInHeader->getTable())->position;
            if ($request->limit == 0) {
                $serviceInHeader->page = ceil($serviceInHeader->position / (10));
            } else {
                $serviceInHeader->page = ceil($serviceInHeader->position / ($request->limit ?? 10));
            }
            $serviceInHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $serviceInHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $serviceInHeader
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
    public function destroy(DestroyServiceInHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $serviceInHeader = (new ServiceInHeader())->processDestroy($id);
            $selected = $this->getPosition($serviceInHeader, $serviceInHeader->getTable(), true);
            $serviceInHeader->position = $selected->position;
            $serviceInHeader->id = $selected->id;
            if ($request->limit == 0) {
                $serviceInHeader->page = ceil($serviceInHeader->position / (10));
            } else {
                $serviceInHeader->page = ceil($serviceInHeader->position / ($request->limit ?? 10));
            }
            $serviceInHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $serviceInHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $serviceInHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function default()
    {

        $serviceInHeader = new ServiceInHeader();
        return response([
            'status' => true,
            'data' => $serviceInHeader->default(),
        ]);
    }

    public function cekvalidasi($id)
    {
        $pengeluaran = ServiceInHeader::findOrFail($id);
        $status = $pengeluaran->statusapproval;
        $nobukti = $pengeluaran->nobukti ?? '';
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        $aksi = request()->aksi ?? '';
        $user = auth('api')->user()->name;
        $useredit = $pengeluaran->editing_by ?? '';
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        $tgltutup = (new Parameter())->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));

        if ($statusdatacetak == $statusCetak->id) {

            $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'SDC',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($tgltutup >= $pengeluaran->tglbukti) {
            $keteranganerror = $error->cekKeteranganError('TUTUPBUKU') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> ( ' . date('d-m-Y', strtotime($tgltutup)) . ' ) <br> ' . $keterangantambahanerror;
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'TUTUPBUKU',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($useredit != '' && $useredit != $user) {

            $waktu = (new Parameter())->cekBatasWaktuEdit('Service In Header BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($pengeluaran->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {

                    (new MyModel())->updateEditingBy('ServiceInHeader', $id, $aksi);
                }

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                // return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'No Bukti <b>' . $pengeluaran->nobukti . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => ["keterangan" => $keterror],
                    'kodeerror' => 'SDE',
                    'statuspesan' => 'warning',
                ];

                return response($data);
            }
        } else {
            if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                (new MyModel())->updateEditingBy('ServiceInHeader', $id, $aksi);
            }
            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];
            return response($data);
        }
    }

    public function cekValidasiAksi($id)
    {
        $serviceinheader = new ServiceInHeader();
        $nobukti = ServiceInHeader::from(DB::raw("serviceinheader"))->where('id', $id)->first();
        $cekdata = $serviceinheader->cekvalidasiaksi($nobukti->nobukti);
        if ($cekdata['kondisi'] == true) {
            $data = [
                'error' => true,
                'message' => $cekdata['keterangan'] ?? '',
                'statuspesan' => 'warning',
                'editcoa' => $cekdata['editcoa']
            ];

            return response($data);
        } else {

            (new MyModel())->updateEditingBy('serviceinheader', $id, 'EDIT');

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
                'editcoa' => false
            ];

            return response($data);
        }
    }
    public function combo()
    {
        $data = [
            'mekanik' => Mekanik::all(),
            'trado' => Trado::all(),
        ];

        return response([
            'data' => $data
        ]);
    }
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('serviceinheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $serviceInHeader = ServiceInHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($serviceInHeader->statuscetak != $statusSudahCetak->id) {
                $serviceInHeader->statuscetak = $statusSudahCetak->id;
                $serviceInHeader->tglbukacetak = date('Y-m-d H:i:s');
                $serviceInHeader->userbukacetak = auth('api')->user()->name;
                $serviceInHeader->jumlahcetak = $serviceInHeader->jumlahcetak + 1;
                if ($serviceInHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($serviceInHeader->getTable()),
                        'postingdari' => 'PRINT SERVICE IN HEADER',
                        'idtrans' => $serviceInHeader->id,
                        'nobuktitrans' => $serviceInHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $serviceInHeader->toArray(),
                        'modifiedby' => $serviceInHeader->modifiedby
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

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id)
    {
        $serviceInHeader = new ServiceInHeader();
        return response([
            'data' => $serviceInHeader->getExport($id)
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

    /**
     * @ClassName 
     * @Keterangan APPROVAL KIRIM BERKAS
     */
    public function approvalkirimberkas()
    {
    }
}
