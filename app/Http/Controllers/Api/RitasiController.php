<?php

namespace App\Http\Controllers\Api;

use App\Models\Ritasi;
use App\Http\Requests\StoreRitasiRequest;
use App\Http\Requests\UpdateRitasiRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;
use App\Models\Supir;
use App\Models\Trado;
use App\Http\Requests\GetUpahSupirRangeRequest;
use App\Models\Kota;
use App\Models\SuratPengantar;
use App\Models\UpahRitasi;
use App\Models\UpahRitasiRincian;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyRitasiRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Models\Error;
use App\Models\Locking;
use App\Models\MyModel;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RitasiController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $ritasi = new Ritasi();
        return response([
            'data' => $ritasi->get(),
            'attributes' => [
                'totalRows' => $ritasi->totalRows,
                'totalPages' => $ritasi->totalPages
            ]
        ]);
    }

    public function default()
    {
        $ritasi = new Ritasi();
        return response([
            'status' => true,
            'data' => $ritasi->default()
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreRitasiRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'statusritasi_id' => $request->statusritasi_id,
                'suratpengantar_nobukti' => $request->suratpengantar_nobukti,
                'supir_id' => $request->supir_id,
                'trado_id' => $request->trado_id,
                'dari_id' => $request->dari_id,
                'sampai_id' => $request->sampai_id,
            ];
            $ritasi = (new Ritasi())->processStore($data);
            if ($request->button == 'btnSubmit') {
                $ritasi->position = $this->getPosition($ritasi, $ritasi->getTable())->position;
                if ($request->limit == 0) {
                    $ritasi->page = ceil($ritasi->position / (10));
                } else {
                    $ritasi->page = ceil($ritasi->position / ($request->limit ?? 10));
                }
                $ritasi->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
                $ritasi->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $ritasi
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $ritasi = (new Ritasi)->find($id);
        return response([
            'status' => true,
            'data' => $ritasi
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateRitasiRequest $request, Ritasi $ritasi): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'statusritasi_id' => $request->statusritasi_id,
                'suratpengantar_nobukti' => $request->suratpengantar_nobukti,
                'supir_id' => $request->supir_id,
                'trado_id' => $request->trado_id,
                'dari_id' => $request->dari_id,
                'sampai_id' => $request->sampai_id,
            ];
            $ritasi = (new Ritasi())->processUpdate($ritasi, $data);
            $ritasi->position = $this->getPosition($ritasi, $ritasi->getTable())->position;
            if ($request->limit == 0) {
                $ritasi->page = ceil($ritasi->position / (10));
            } else {
                $ritasi->page = ceil($ritasi->position / ($request->limit ?? 10));
            }
            $ritasi->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $ritasi->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $ritasi
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
    public function destroy(DestroyRitasiRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $ritasi = (new Ritasi())->processDestroy($id);
            $selected = $this->getPosition($ritasi, $ritasi->getTable(), true);
            $ritasi->position = $selected->position;
            $ritasi->id = $selected->id;
            if ($request->limit == 0) {
                $ritasi->page = ceil($ritasi->position / (10));
            } else {
                $ritasi->page = ceil($ritasi->position / ($request->limit ?? 10));
            }
            $ritasi->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $ritasi->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $ritasi
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('ritasi')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function combo(Request $request)
    {
        $data = [
            'statusritasi' => Parameter::where(['grp' => 'status ritasi'])->get(),
            'suratpengantar' => SuratPengantar::all(),
            'supir' => Supir::all(),
            'trado' => Trado::all(),
            'kota' => Kota::all(),
        ];

        return response([
            'data' => $data
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
     * @Keterangan EXPORT KE EXCEL
     */
    public function export()
    {
        $ritasi = new Ritasi();
        return response([
            'data' => $ritasi->getExport()
        ]);
    }

    public function cekValidasi($id)
    {
        $ritasi = new Ritasi();
        $nobukti = DB::table("ritasi")->from(DB::raw("ritasi"))->where('id', $id)->first();
        $cekdata = $ritasi->cekvalidasiaksi($nobukti->nobukti);
        $parameter = new Parameter();

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));
        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('ritasi', $id);
        $useredit = $getEditing->editing_by ?? '';

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
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
        } else if ($tgltutup >= $nobukti->tglbukti) {
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
            $waktu = (new Parameter())->cekBatasWaktuEdit('suratpengantar');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            $totalminutes =  ($diffNow->days * 24 * 60) + ($diffNow->h * 60) + $diffNow->i;
            if ($totalminutes > $waktu) {
                (new MyModel())->createLockEditing($id, 'ritasi', $useredit);

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'No Bukti <b>' . $nobukti->nobukti . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'SDE',
                    'statuspesan' => 'warning',
                    // 'force' => $force
                ];

                return response($data);
            }
        } else {
            (new MyModel())->createLockEditing($id, 'ritasi',$useredit);  

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }
}
