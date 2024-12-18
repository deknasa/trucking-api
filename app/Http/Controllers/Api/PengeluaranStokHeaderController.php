<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Bank;
use App\Models\Stok;
use App\Models\Error;

use App\Models\MyModel;
use App\Models\Parameter;
use App\Models\HutangHeader;
use Illuminate\Http\Request;
use App\Models\StokPersediaan;
use App\Models\PengeluaranStok;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\PenerimaanDetail;
use App\Models\PenerimaanHeader;
use App\Models\HutangBayarDetail;
use App\Models\HutangBayarHeader;
use App\Models\PengeluaranDetail;
use App\Models\PengeluaranHeader;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanStokDetail;
use App\Models\PenerimaanStokHeader;
use App\Models\PengeluaranStokDetail;
use App\Models\PengeluaranStokHeader;
use Illuminate\Support\Facades\Schema;
use App\Models\PengeluaranStokDetailFifo;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StorePenerimaanDetailRequest;
use App\Http\Requests\StorePenerimaanHeaderRequest;
use App\Http\Requests\StoreHutangBayarHeaderRequest;
use App\Http\Requests\UpdateJurnalUmumHeaderRequest;
use App\Http\Requests\StorePengeluaranStokDetailRequest;
use App\Http\Requests\StorePengeluaranStokHeaderRequest;
use App\Http\Requests\UpdatePengeluaranStokHeaderRequest;
use App\Http\Requests\DestroyPengeluaranStokHeaderRequest;
use App\Http\Requests\StorePengeluaranStokDetailFifoRequest;
use App\Models\Locking;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PengeluaranStokHeaderController extends Controller
{
    /**
     * @ClassName 
     * PengeluaranStokHeader
     * @Detail PengeluaranStokDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        if ($request->reload == '') {

            $pengeluaranStokHeader = new PengeluaranStokHeader();
            $pengeluaranStokHeader->updateApproval();
            return response([
                'data' => $pengeluaranStokHeader->get(),
                'attributes' => [
                    'totalRows' => $pengeluaranStokHeader->totalRows,
                    'totalPages' => $pengeluaranStokHeader->totalPages
                ]
            ]);
        } else {
            return response([
                'data' => [],
                'attributes' => [
                    'totalRows' => [],
                    'totalPages' => []
                ]
            ]);
        }
    }
    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StorePengeluaranStokHeaderRequest $request)
    {
        DB::beginTransaction();
        try {


            $data = [
                "tglbukti" => $request->tglbukti,
                "pengeluaranstok" => $request->pengeluaranstok,
                "pengeluaranstok_id" => $request->pengeluaranstok_id,
                "penerimaanstok_nobukti" => $request->penerimaanstok_nobukti,
                "pengeluaranstok_nobukti" => $request->pengeluaranstok_nobukti,
                "pengeluarantrucking_nobukti" => $request->pengeluarantrucking_nobukti,
                "supplier" => $request->supplier,
                "supplier_id" => $request->supplier_id,
                "kerusakan" => $request->kerusakan,
                "kerusakan_id" => $request->kerusakan_id,
                "supir" => $request->supir,
                "supir_id" => $request->supir_id,
                "servicein_nobukti" => $request->servicein_nobukti,
                "trado" => $request->trado,
                "trado_id" => $request->trado_id,
                "gudang" => $request->gudang,
                "gudang_id" => $request->gudang_id,
                "gandengan" => $request->gandengan,
                "gandengan_id" => $request->gandengan_id,
                "statuspotongretur" => $request->statuspotongretur,
                "bank" => $request->bank,
                "bank_id" => $request->bank_id,
                "tglkasmasuk" => $request->tglkasmasuk,
                "penerimaan_nobukti" => $request->penerimaan_nobukti,
                "penerimaanstokproses_nobukti" => $request->penerimaanstokproses_nobukti,

                "detail_stok" => $request->detail_stok,
                "detail_stok_id" => $request->detail_stok_id,
                "jlhhari" => $request->jlhhari,
                "detail_statusoli" => $request->detail_statusoli,
                "detail_vulkanisirke" => $request->detail_vulkanisirke,
                "detail_keterangan" => $request->detail_keterangan,
                "detail_statusban" => ($request->statusban) ? $request->statusban : $request->detail_statusban,
                "detail_qty" => $request->detail_qty ?? $request->qty_afkir,
                "detail_harga" => $request->detail_harga,
                "detail_persentasediscount" => $request->detail_persentasediscount,
                "totalItem" => $request->totalItem,
            ];
            // dd($data);
            /* Store header */
            $pengeluaranStokHeader = (new PengeluaranStokHeader())->processStore($data);
            if ($request->button == 'btnSubmit') {
                /* Set position and page */
                $pengeluaranStokHeader->position = $this->getPosition($pengeluaranStokHeader, $pengeluaranStokHeader->getTable())->position;
                if ($request->limit == 0) {
                    $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / (10));
                } else {
                    $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / ($request->limit ?? 10));
                }
                $pengeluaranStokHeader->tgldariheader = date('Y-m-d', strtotime(request()->tgldariheader));
                $pengeluaranStokHeader->tglsampaiheader = date('Y-m-d', strtotime(request()->tglsampaiheader));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranStokHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show(PengeluaranStokHeader $pengeluaranStokHeader, $id)
    {
        return response([
            'status' => true,
            'data' => $pengeluaranStokHeader->find($id),
            'detail' => PengeluaranStokDetail::getAll($id),
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdatePengeluaranStokHeaderRequest $request, PengeluaranStokHeader $pengeluaranStokHeader, $id): JsonResponse
    {
        // dd($request);
        DB::beginTransaction();

        try {
            $data = [
                "id_detail" => $request->id_detail ?? 0,
                "tglbukti" => $request->tglbukti,
                "pengeluaranstok" => $request->pengeluaranstok,
                "pengeluaranstok_id" => $request->pengeluaranstok_id,
                "penerimaanstok_nobukti" => $request->penerimaanstok_nobukti,
                "pengeluarantrucking_nobukti" => $request->pengeluarantrucking_nobukti,
                "pengeluaranstok_nobukti" => $request->pengeluaranstok_nobukti,
                "supplier" => $request->supplier,
                "supplier_id" => $request->supplier_id,
                "kerusakan" => $request->kerusakan,
                "kerusakan_id" => $request->kerusakan_id,
                "supir" => $request->supir,
                "supir_id" => $request->supir_id,
                "servicein_nobukti" => $request->servicein_nobukti,
                "trado" => $request->trado,
                "trado_id" => $request->trado_id,
                "gudang" => $request->gudang,
                "gudang_id" => $request->gudang_id,
                "gandengan" => $request->gandengan,
                "gandengan_id" => $request->gandengan_id,
                "statuspotongretur" => $request->statuspotongretur,
                "bank" => $request->bank,
                "bank_id" => $request->bank_id,
                "tglkasmasuk" => $request->tglkasmasuk,
                "penerimaan_nobukti" => $request->penerimaan_nobukti,
                "penerimaanstokproses_nobukti" => $request->penerimaanstokproses_nobukti,

                "detail_stok" => $request->detail_stok,
                "detail_stok_id" => $request->detail_stok_id,
                "jlhhari" => $request->jlhhari,
                "detail_statusoli" => $request->detail_statusoli,
                "detail_vulkanisirke" => $request->detail_vulkanisirke,
                "detail_statusban" => ($request->statusban) ? $request->statusban : $request->detail_statusban,
                "detail_qty" => $request->detail_qty ?? $request->qty_afkir,
                "detail_keterangan" => $request->detail_keterangan,
                "detail_harga" => $request->detail_harga,
                "detail_persentasediscount" => $request->detail_persentasediscount,
                "totalItem" => $request->totalItem,
            ];
            // dd($data['detail_statusban']);
            /* Store header */
            $pengeluaranStokHeader = PengeluaranStokHeader::findOrFail($id);
            $pengeluaranStokHeader = (new PengeluaranStokHeader())->processUpdate($pengeluaranStokHeader, $data);
            /* Set position and page */
            $pengeluaranStokHeader->position = $this->getPosition($pengeluaranStokHeader, $pengeluaranStokHeader->getTable())->position;
            if ($request->limit == 0) {
                $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / (10));
            } else {
                $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / ($request->limit ?? 10));
            }

            $pengeluaranStokHeader->tgldariheader = date('Y-m-d', strtotime(request()->tglbukti));
            $pengeluaranStokHeader->tglsampaiheader = date('Y-m-d', strtotime(request()->tglbukti));
            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranStokHeader
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
    public function destroy(DestroyPengeluaranStokHeaderRequest $request, PengeluaranStokHeader $pengeluaranStokHeader, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $pengeluaranStokHeader = (new PengeluaranStokHeader())->processDestroy($id);
            $selected = $this->getPosition($pengeluaranStokHeader, $pengeluaranStokHeader->getTable(), true);
            $pengeluaranStokHeader->position = $selected->position;
            $pengeluaranStokHeader->id = $selected->id;
            if ($request->limit == 0) {
                $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / (10));
            } else {
                $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / ($request->limit ?? 10));
            }
            $pengeluaranStokHeader->tgldariheader = date('Y-m-d', strtotime($pengeluaranStokHeader->tglbukti));
            $pengeluaranStokHeader->tglsampaiheader = date('Y-m-d', strtotime($pengeluaranStokHeader->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $pengeluaranStokHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    public function storePenerimaan($penerimaanHeader)
    {
        try {


            $penerimaan = new StorePenerimaanHeaderRequest($penerimaanHeader);
            $header = app(PenerimaanHeaderController::class)->store($penerimaan);


            return [
                'status' => true,
                'data' => $header->original['data'],
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function storePembayaranHutang($penerimaanHeader)
    {
        try {
            $penerimaan = new StoreHutangBayarHeaderRequest($penerimaanHeader);
            $header = app(HutangBayarHeaderController::class)->store($penerimaan);
            return [
                'status' => true,
                'data' => $header->original['data'],
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function persediaanDari($stokId, $persediaan, $persediaanId, $qty)
    {
        $stokpersediaangudang = $this->checkTempat($stokId, $persediaan, $persediaanId); //stok persediaan 
        if (!$stokpersediaangudang) {
            return false;
        }
        $stokpersediaan = StokPersediaan::lockForUpdate()->find($stokpersediaangudang->id);
        if ($qty > $stokpersediaan->qty) { //check qty
            return false;
        }
        $result = $stokpersediaan->qty - $qty;
        $stokpersediaan->update(['qty' => $result]);
        return $stokpersediaan;
    }
    public function persediaanKe($stokId, $persediaan, $persediaanId, $qty)
    {
        $stokpersediaangudang = $this->checkTempat($stokId, $persediaan, $persediaanId); //stok persediaan 
        if (!$stokpersediaangudang) {
            $stokpersediaangudang = StokPersediaan::create(["stok_id" => $stokId, $persediaan => $persediaanId]);
        }
        $stokpersediaangudang->qty += $qty;
        $stokpersediaangudang->save();
        return $stokpersediaangudang;
    }

    public function persediaan($gudang, $trado, $gandengan)
    {
        $kolom = null;
        $value = 0;
        if (!empty($gudang)) {
            $kolom = "gudang";
            $value = $gudang;
        } elseif (!empty($trado)) {
            $kolom = "trado";
            $value = $trado;
        } elseif (!empty($gandengan)) {
            $kolom = "gandengan";
            $value = $gandengan;
        }
        return [
            "column" => $kolom,
            "value" => $value
        ];
    }
    public function checkTempat($stokId, $persediaan, $persediaanId)
    {
        $result = StokPersediaan::lockForUpdate()->where("stok_id", $stokId)->where("$persediaan", $persediaanId)->first();
        return (!$result) ? false : $result;
    }

    public function cekvalidasi($id)
    {

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';


        $parameter = new Parameter();

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));



        // $pengeluaran = PengeluaranStokHeader::findOrFail($id);
        $pengeluaran  = new PengeluaranStokHeader();
        $pengeluaran  = $pengeluaran->findOrFail($id);
        $nobukti = $pengeluaran->nobukti ?? '';
        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('pengeluaranstokheader', $id);
        $useredit = $getEditing->editing_by ?? '';

        if (!isset($pengeluaran)) {
            $keteranganerror = $error->cekKeteranganError('DTA') ?? '';
            $keterror = 'No Bukti <b>' . request()->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'message' => $keterror,
                'errors' => $keterror,
                'kodestatus' => '1',
                'statuspesan' => 'warning',
                'kodenobukti' => '1'
            ];

            return response($data);
        }

        $querypenerimaan = db::table("penerimaanstokheader")->from(db::raw("penerimaanstokheader a with (readuncommitted)"))
            ->select(
                'a.nobukti'
            )
            ->where('a.nobukti', $pengeluaran->penerimaanstokproses_nobukti)
            ->first();


        if (isset($querypenerimaan)) {
            $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
            $keterror = 'No Bukti <b>' . request()->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'message' => $keterror,
                'errors' => $keterror,
                'kodestatus' => '1',
                'statuspesan' => 'warning',
                'kodenobukti' => '1'
            ];

            return response($data);
        }

        if ($tgltutup >= $pengeluaran->tglbukti) {
            $keteranganerror = $error->cekKeteranganError('TUTUPBUKU') ?? '';
            $keterangan = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> ( ' . date('d-m-Y', strtotime($tgltutup)) . ' ) <br> ' . $keterangantambahanerror;
            $data = [
                'message' => $keterangan,
                'errors' => $keterangan,
                'kodestatus' => '1',
                'statuspesan' => 'warning',
                'kodenobukti' => '1'
            ];
            return response($data);
        }



        $penerimaan = $pengeluaran->penerimaan_nobukti ?? '';
        $idpenerimaan = db::table('penerimaanheader')->from(db::raw("penerimaanheader a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->where('a.nobukti', $penerimaan)
            ->first()->id ?? 0;
        if ($idpenerimaan != 0) {
            $validasipenerimaan = app(PenerimaanHeaderController::class)->cekvalidasi($idpenerimaan);
            $msg = json_decode(json_encode($validasipenerimaan), true)['original']['error'] ?? false;
            if ($msg == false) {
                goto lanjut;
            } else {
                return $validasipenerimaan;
            }
        }

        lanjut:

        $penerimaan = $pengeluaran->penerimaantrucking_nobukti ?? '';
        $idpenerimaan = db::table('penerimaantruckingheader')->from(db::raw("penerimaantruckingheader a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->where('a.nobukti', $penerimaan)
            ->first()->id ?? 0;
        if ($idpenerimaan != 0) {
            $validasipenerimaan = app(PenerimaanTruckingHeaderController::class)->cekvalidasi($idpenerimaan);
            $msg = json_decode(json_encode($validasipenerimaan), true)['original']['error'] ?? false;
            if ($msg == false) {
                goto lanjut1;
            } else {
                return $validasipenerimaan;
            }
        }

        lanjut1:

        $penerimaan = $pengeluaran->pengeluarantrucking_nobukti ?? '';
        $idpenerimaan = db::table('pengeluarantruckingheader')->from(db::raw("pengeluarantruckingheader a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->where('a.nobukti', $penerimaan)
            ->first()->id ?? 0;
        if ($idpenerimaan != 0) {
            $validasipenerimaan = app(PengeluaranTruckingHeaderController::class)->cekvalidasi($idpenerimaan);
            $msg = json_decode(json_encode($validasipenerimaan), true)['original']['error'] ?? false;
            if ($msg == false) {
                goto lanjut2;
            } else {
                return $validasipenerimaan;
            }
        }

        lanjut2:

        // cek hak
        $pengeluaranstok_id = $pengeluaran->pengeluaranstok_id;
        $aco_id = db::table("pengeluaranstok")->from(db::raw("pengeluaranstok a with (readuncommitted)"))
            ->select(
                'a.aco_id'
            )->where('a.id', $pengeluaranstok_id)
            ->first()->aco_id ?? 0;

        $user_id = auth('api')->user()->id;
        $user = auth('api')->user()->user;
        $role = db::table("userrole")->from(db::raw("userrole a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->join(db::raw("acl b with (readuncommitted)"), 'a.role_id', 'b.role_id')
            ->where('a.user_id', $user_id)
            ->where('b.aco_id', $aco_id)
            // ->tosql();
            ->first();

        $aksi = request()->aksi;

        $passes = true;
        // dd($role);
        if ($aksi == 'EDIT' || $aksi == 'DELETE') {
            if (!isset($role)) {
                $acl = db::table('useracl')->from(db::raw("useracl a with (readuncommitted)"))
                    ->select(
                        'a.id'
                    )->where('a.user_id', $user_id)
                    ->where('a.aco_id', $aco_id)
                    ->first();

                if (!isset($acl)) {
                    // $query = DB::table('error')
                    //     ->select(db::raw("'USER " . $user . " '+keterangan as keterangan"))
                    //     ->where('kodeerror', '=', 'TPH')
                    //     ->get();
                    // $keterangan = $query['0'];
                    $keteranganerror = $error->cekKeteranganError('TPH') ?? '';
                    $keterangan = 'USER ' . $user . ' ' . $error->cekKeteranganError('TPH') ?? '';
                    $data = [
                        'message' => $keterangan,
                        'errors' => $keterangan,
                        'kodestatus' => '1',
                        'kodenobukti' => '1',
                        'statuspesan' => 'warning',
                    ];
                    $passes = false;
                    return response($data);
                    goto selesai;
                }
            }
        }
        // 

        if ($aksi == 'PRINTER BESAR' || $aksi == 'PRINTER KECIL') {

            $peneimaan = $pengeluaran->findOrFail($id);
            $statusdatacetak = $peneimaan->statuscetak;
            $msg = 'PROSES CETAK TIDAK BISA LANJUT KARENA';

            $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
            if ($statusdatacetak == $statusCetak->id) {
                // $query = DB::table('error')
                //     ->select(db::raw("'$msg <br>'+keterangan as keterangan"))            
                //     ->where('kodeerror', '=', 'SDC')
                //     ->get();
                // $keterangan = $query['0'];
                $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
                $keterangan = 'No Bukti <b>' . request()->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

                $data = [
                    'message' => $keterangan,
                    'errors' => 'sudah cetak',
                    'kodestatus' => '1',
                    'kodenobukti' => '1',
                    'statuspesan' => 'warning',
                ];
            } else {
                $data = [
                    'message' => '',
                    'errors' => 'bisa',
                    'kodestatus' => '0',
                    'kodenobukti' => '1',
                    'statuspesan' => 'warning',
                ];
            }

            return response($data);
        } else {

            if ($aksi == 'EDIT') {
                $msg = 'PROSES EDIT TIDAK BISA LANJUT KARENA';
            } else {
                $msg = 'PROSES DELETE TIDAK BISA LANJUT KARENA';
            }
            $isInUsed = $pengeluaran->isInUsed($id);
            if ($isInUsed) {
                // $query = Error::from(DB::raw("error with (readuncommitted)"))
                // ->select(db::raw("'$msg <br>'+keterangan +' <br>(" . $isInUsed[1] . ")' as keterangan"))
                //     ->whereRaw("kodeerror = 'SATL2'")
                //     ->get();
                // $keterangan = $query['0'];
                $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
                $keterangan = 'No Bukti <b>' . request()->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

                $data = [
                    'message' => $keterangan,
                    'errors' => 'Penerimaan stok',
                    'kodestatus' => '1',
                    'kodenobukti' => '1',
                    'statuspesan' => 'warning',
                ];
                return response($data);
            }
            $isNobuktiApprovedJurnal = $pengeluaran->isNobuktiApprovedJurnal($id);
            if ($isNobuktiApprovedJurnal) {
                // $query = Error::from(DB::raw("error with (readuncommitted)"))
                //     ->select(db::raw("'$msg <br>'+keterangan +' <br>(" . $isNobuktiApprovedJurnal[1] . ")' as keterangan"))
                //     ->whereRaw("kodeerror = 'SAP'")
                //     ->get();
                // $keterangan = $query['0'];
                $keteranganerror = $error->cekKeteranganError('SAPP') ?? '';
                $keterangan = 'No Bukti <b>' . request()->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

                $data = [
                    'message' => $keterangan,
                    'errors' => 'Penerimaan stok',
                    'kodestatus' => '1',
                    'kodenobukti' => '1',
                    'statuspesan' => 'warning',
                ];
                return response($data);
            }
            $isKMTApprovedJurnal = $pengeluaran->isKMTApprovedJurnal($id);
            if ($isKMTApprovedJurnal) {
                // $query = Error::from(DB::raw("error with (readuncommitted)"))
                //     ->select(db::raw("'$msg <br>'+keterangan +' <br>(" . $isKMTApprovedJurnal[1] . ")' as keterangan"))
                //     ->whereRaw("kodeerror = 'SAP'")
                //     ->get();
                // $keterangan = $query['0'];
                $keteranganerror = $error->cekKeteranganError('SAP') ?? '';
                $keterangan = 'No Bukti <b>' . request()->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

                $data = [
                    'message' => $keterangan,
                    'errors' => 'Penerimaan stok',
                    'kodestatus' => '1',
                    'kodenobukti' => '1',
                    'statuspesan' => 'warning',
                ];
                return response($data);
            }
            $isPPHApprovedJurnal = $pengeluaran->isPPHApprovedJurnal($id);
            if ($isPPHApprovedJurnal) {
                // $query = Error::from(DB::raw("error with (readuncommitted)"))
                //     ->select(db::raw("'$msg <br>'+keterangan +' <br>(" . $isPPHApprovedJurnal[1] . ")' as keterangan"))
                //     ->whereRaw("kodeerror = 'SAP'")
                //     ->get();
                // $keterangan = $query['0'];
                $keteranganerror = $error->cekKeteranganError('SAP') ?? '';
                $keterangan = 'No Bukti <b>' . request()->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

                $data = [
                    'message' => $keterangan,
                    'errors' => 'APPROVAL JURNAL',
                    'kodestatus' => '1',
                    'kodenobukti' => '1',
                    'statuspesan' => 'warning',
                ];
                return response($data);
            }
            $printValidation = $pengeluaran->printValidation($id);
            if ($printValidation) {
                // $query = Error::from(DB::raw("error with (readuncommitted)"))
                //     ->select(db::raw("'$msg <br>'+keterangan as keterangan"))
                //     ->whereRaw("kodeerror = 'SDC'")
                //     ->first();
                // $keterangan = $query;
                $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
                $keterangan = 'No Bukti <b>' . request()->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

                $data = [
                    'message' => $keterangan,
                    'errors' => 'sudah cetak',
                    'kodestatus' => '1',
                    'kodenobukti' => '1',
                    'statuspesan' => 'warning',
                ];

                return response($data);
            }
            $todayValidation = $pengeluaran->todayValidation($pengeluaran->tglbukti);
            if (!$todayValidation) {
                // $query = Error::from(DB::raw("error with (readuncommitted)"))
                //     ->select('keterangan')
                //     ->whereRaw("kodeerror = 'TEPT'")
                //     ->first();
                // $keterangan = $query;
                $keteranganerror = $error->cekKeteranganError('TEPT') ?? '';
                $keterangan = 'No Bukti <b>' . request()->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

                // $keterangan = ['keterangan' => 'transaksi Sudah berbeda tanggal']; //$query['0'];
                $data = [
                    'message' => $keterangan,
                    'errors' => 'sudah cetak',
                    'kodestatus' => '1',
                    'kodenobukti' => '1',
                    'statuspesan' => 'warning',
                ];
            }
            $isEditAble = $pengeluaran->isEditAble($id);
            $isKeteranganEditAble = $pengeluaran->isKeteranganEditAble($id);
            if ((!$isEditAble) || (!$isKeteranganEditAble)) {
                // $query = Error::from(DB::raw("error with (readuncommitted)"))
                //     ->select(db::raw("'$msg <br>'+keterangan as keterangan"))
                //     ->whereRaw("kodeerror = 'TED2'")
                //     ->first();
                // $keterangan = $query;
                $keteranganerror = $error->cekKeteranganError('TED2') ?? '';
                $keterangan = 'No Bukti <b>' . request()->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

                // $keterangan = ['keterangan' => 'Transaksi Tidak Bisa diedit']; //$query['0'];
                $data = [
                    'message' => $keterangan,
                    'errors' => 'sudah cetak',
                    'kodestatus' => '1',
                    'kodenobukti' => '1',
                    'statuspesan' => 'warning',
                ];
            }

            //    dd($pengeluaran->tglbukti,$isEditAble,$printValidation);
            $nameUser = auth('api')->user()->name;
            if ($useredit != '' && $useredit != $nameUser) {
                $waktu = (new Parameter())->cekBatasWaktuEdit('pengeluaran stok header BUKTI');

                $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
                $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
                $totalminutes =  ($diffNow->days * 24 * 60) + ($diffNow->h * 60) + $diffNow->i;
                if ($totalminutes > $waktu) {
                    if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                        (new MyModel())->createLockEditing($id, 'pengeluaranstokheader', $useredit);
                    }

                    $data = [
                        'message' => '',
                        'error' => false,
                        'statuspesan' => 'success',
                    ];

                    // return response($data);
                } else {

                    $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                    $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                    $data = [
                        'error' => true,
                        'message' => $keterror,
                        'kodeerror' => 'SDE',
                        'statuspesan' => 'warning',
                    ];

                    return response($data);
                }
            }
            if ($todayValidation || (($isEditAble || $isKeteranganEditAble) && !$printValidation)) {
                (new MyModel())->createLockEditing($id, 'pengeluaranstokheader', $useredit);

                $data = [
                    'message' => '',
                    'errors' => 'bisa',
                    'kodestatus' => '0',
                    'kodenobukti' => '1',
                    'statuspesan' => 'warning',
                ];
            } else {
                return response($data);
            }


            return response($data);
            selesai:;
        }
    }

    private function storeJurnal($header, $detail)
    {
        DB::beginTransaction();

        try {

            $jurnal = new StoreJurnalUmumHeaderRequest($header);
            $jurnals = app(JurnalUmumHeaderController::class)->store($jurnal);

            $detailLog = [];

            foreach ($detail as $value) {
                $value['jurnalumum_id'] = $jurnals->original['data']['id'];
                $detail = new StoreJurnalUmumDetailRequest($value);
                $datadetails = app(JurnalUmumDetailController::class)->store($detail);
                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }

                $detailLog[] = $datadetails['detail']->toArray();
            }
            // dd($detail);
            $datalogtrail = [
                'namatabel' => strtoupper($tabeldetail),
                'postingdari' => 'ENTRY HUTANG',
                'idtrans' => $jurnals->original['idlogtrail'],
                'nobuktitrans' => $header['nobukti'],
                'aksi' => 'ENTRY',
                'datajson' => $detailLog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            DB::commit();
            return [
                'status' => true,
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $pengeluaranStokHeader = PengeluaranStokheader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($pengeluaranStokHeader->statuscetak != $statusSudahCetak->id) {
                $pengeluaranStokHeader->statuscetak = $statusSudahCetak->id;
                // $pengeluaranStokHeader->tglbukacetak = date('Y-m-d H:i:s');
                // $pengeluaranStokHeader->userbukacetak = auth('api')->user()->name;
                $pengeluaranStokHeader->jumlahcetak = $pengeluaranStokHeader->jumlahcetak + 1;
                if ($pengeluaranStokHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($pengeluaranStokHeader->getTable()),
                        'postingdari' => 'PRINT PENGELUARAN STOK HEADER',
                        'idtrans' => $pengeluaranStokHeader->id,
                        'nobuktitrans' => $pengeluaranStokHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $pengeluaranStokHeader->toArray(),
                        'modifiedby' => $pengeluaranStokHeader->modifiedby
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
     * @Keterangan CETAK DATA
     */
    public function report() {}

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id)
    {
        $pengeluaranStokHeader = new PengeluaranStokHeader();
        $pengeluaranstokheaders = $pengeluaranStokHeader->find($id);
        $data = $pengeluaranstokheaders;

        $pengeluaranStokDetail = new PengeluaranStokDetail();
        $pengeluaranstok_details = $pengeluaranStokDetail->get();

        $trado = $pengeluaranstokheaders->trado;
        $gandengan = $pengeluaranstokheaders->gandengan;
        $gudang = $pengeluaranstokheaders->gudang;
        $persediaan = $this->persediaan($gudang, $trado, $gandengan);
        $data->column = $persediaan['column'];
        $data->value = $persediaan['value'];
        $pengeluaranstokheaders = $data;
        $tglbukti = $pengeluaranstokheaders->tglbukti;
        $timeStamp = strtotime($tglbukti);
        $datetglbukti = date('d-m-Y', $timeStamp);
        $pengeluaranstokheaders->tglbukti = $datetglbukti;

        $tglkasmasuk = $pengeluaranstokheaders->tglkasmasuk;
        $timeStampDari = strtotime($tglkasmasuk);
        $datetglkasmasuk = date('d-m-Y', $timeStampDari);
        $pengeluaranstokheaders->tglkasmasuk = $datetglkasmasuk;

        switch ($pengeluaranstokheaders->statusformat) {
            case '135':
                //SPK
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
                $sheet->setCellValue('A1', $pengeluaranstokheaders->judul);
                $sheet->setCellValue('A2', 'Laporan Pengeluaran Stok (SPK)');
                $sheet->getStyle("A1")->getFont()->setSize(11);
                $sheet->getStyle("A2")->getFont()->setSize(11);
                $sheet->getStyle("A1")->getFont()->setBold(true);
                $sheet->getStyle("A2")->getFont()->setBold(true);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A2:F2');
                $header_start_row = 4;
                $detail_table_header_row = 9;
                $detail_start_row = $detail_table_header_row + 1;
                $alphabets = range('A', 'Z');
                $header_columns = [
                    [
                        'label' => 'No Bukti',
                        'index' => 'nobukti',
                    ],
                    [
                        'label' => 'Tanggal',
                        'index' => 'tglbukti',
                    ],
                    [
                        'label' => 'No Polisi',
                        'index' => 'trado',
                    ],
                    [
                        'label' => 'No PG',
                        'index' => 'penerimaanstok_nobukti',
                    ],
                ];
                $detail_columns = [
                    [
                        'label' => 'NO',
                    ],
                    [
                        'label' => 'NAMA BARANG',
                        'index' => 'stok'
                    ],
                    [
                        'label' => 'JUMLAH',
                        'index' => 'qty'
                    ],
                    [
                        'label' => '@',
                        'index' => 'harga',
                        'format' => 'currency'
                    ],
                    [
                        'label' => 'TOTAL',
                        'index' => 'total',
                        'format' => 'currency'
                    ],
                    [
                        'label' => 'KETERANGAN',
                        'index' => 'keterangan',
                    ]
                ];
                //LOOPING HEADER        
                foreach ($header_columns as $header_column) {
                    $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                    $sheet->setCellValue('C' . $header_start_row++, ': ' . $pengeluaranstokheaders->{$header_column['index']});
                }
                foreach ($detail_columns as $detail_columns_index => $detail_column) {
                    $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
                }
                $styleArray = array(
                    'borders' => array(
                        'allBorders' => array(
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ),
                    ),
                );
                $style_number = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    ],

                    'borders' => [
                        'top' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                        'right' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                        'bottom' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                        'left' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
                    ]
                ];
                $sheet->getStyle("A$detail_table_header_row:F$detail_table_header_row")->applyFromArray($styleArray);
                // LOOPING DETAIL
                $nominal = 0;
                foreach ($pengeluaranstok_details as $response_index => $response_detail) {

                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : 0);
                        $sheet->getStyle("A$detail_table_header_row:F$detail_table_header_row")->getFont()->setBold(true);
                        $sheet->getStyle("A$detail_table_header_row:F$detail_table_header_row")->getAlignment()->setHorizontal('center');
                    }
                    $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                    $sheet->setCellValue("B$detail_start_row", $response_detail->stok);
                    $sheet->setCellValue("C$detail_start_row", $response_detail->qty);
                    $sheet->setCellValue("D$detail_start_row", $response_detail->harga);
                    $sheet->setCellValue("E$detail_start_row", $response_detail->total);
                    $sheet->setCellValue("F$detail_start_row", $response_detail->keterangan);
                    $sheet->getStyle("F$detail_start_row")->getAlignment()->setWrapText(true);
                    $sheet->getColumnDimension('F')->setWidth(70);
                    $sheet->getStyle("A$detail_start_row:F$detail_start_row")->applyFromArray($styleArray);
                    $sheet->getStyle("D$detail_start_row:E$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $nominal += $response_detail->total;
                    $detail_start_row++;
                }

                $total_start_row = $detail_start_row;
                $sheet->mergeCells('A' . $total_start_row . ':D' . $total_start_row);
                $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':D' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                $sheet->setCellValue("E$total_start_row", $nominal)->getStyle("E$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
                $sheet->getStyle("E$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->getColumnDimension('C')->setAutoSize(true);
                $sheet->getColumnDimension('D')->setAutoSize(true);
                $sheet->getColumnDimension('E')->setAutoSize(true);
                $writer = new Xlsx($spreadsheet);
                $filename = 'LAPORAN PENGELUARAN STOK (SPK)' . date('dmYHis');
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');
                header('Filename: ' . $filename);
                $writer->save('php://output');
                break;
            case '139':
                //RTR
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
                $sheet->setCellValue('A1', $pengeluaranstokheaders->judul);
                $sheet->setCellValue('A2', 'Laporan Purchase Return (RTR)');
                $sheet->getStyle("A1")->getFont()->setSize(11);
                $sheet->getStyle("A2")->getFont()->setSize(11);
                $sheet->getStyle("A1")->getFont()->setBold(true);
                $sheet->getStyle("A2")->getFont()->setBold(true);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A2:F2');
                $header_start_row = 4;
                $detail_table_header_row = 9;
                $detail_start_row = $detail_table_header_row + 1;
                $alphabets = range('A', 'Z');
                $header_columns = [
                    [
                        'label' => 'No Bukti',
                        'index' => 'nobukti',
                    ],
                    [
                        'label' => 'Tanggal',
                        'index' => 'tglbukti',
                    ],
                    [
                        'label' => 'Supplier',
                        'index' => 'supplier',
                    ],
                    [
                        'label' => 'No SPB',
                        'index' => 'penerimaanstok_nobukti',
                    ]
                ];
                $detail_columns = [
                    [
                        'label' => 'NO',
                    ],
                    [
                        'label' => 'NAMA BARANG',
                        'index' => 'stok'
                    ],
                    [
                        'label' => 'JUMLAH',
                        'index' => 'qty'
                    ],
                    [
                        'label' => '@',
                        'index' => 'harga',
                        'format' => 'currency'
                    ],
                    [
                        'label' => 'TOTAL',
                        'index' => 'total',
                        'format' => 'currency'
                    ],
                    [
                        'label' => 'KETERANGAN',
                        'index' => 'keterangan',
                    ]
                ];
                //LOOPING HEADER        
                foreach ($header_columns as $header_column) {
                    $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                    $sheet->setCellValue('C' . $header_start_row++, ': ' . $pengeluaranstokheaders->{$header_column['index']});
                }
                foreach ($detail_columns as $detail_columns_index => $detail_column) {
                    $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
                }
                $styleArray = array(
                    'borders' => array(
                        'allBorders' => array(
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ),
                    ),
                );
                $style_number = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    ],

                    'borders' => [
                        'top' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                        'right' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                        'bottom' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                        'left' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
                    ]
                ];
                $sheet->getStyle("A$detail_table_header_row:F$detail_table_header_row")->applyFromArray($styleArray);
                // LOOPING DETAIL
                $nominal = 0;
                foreach ($pengeluaranstok_details as $response_index => $response_detail) {

                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : 0);
                        $sheet->getStyle("A$detail_table_header_row:F$detail_table_header_row")->getFont()->setBold(true);
                        $sheet->getStyle("A$detail_table_header_row:F$detail_table_header_row")->getAlignment()->setHorizontal('center');
                    }

                    $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                    $sheet->setCellValue("B$detail_start_row", $response_detail->stok);
                    $sheet->setCellValue("C$detail_start_row", $response_detail->qty);
                    $sheet->setCellValue("D$detail_start_row", $response_detail->harga);
                    $sheet->setCellValue("E$detail_start_row", $response_detail->total);
                    $sheet->setCellValue("F$detail_start_row", $response_detail->keterangan);
                    $sheet->getStyle("F$detail_start_row")->getAlignment()->setWrapText(true);
                    $sheet->getColumnDimension('F')->setWidth(70);
                    $sheet->getStyle("A$detail_start_row:F$detail_start_row")->applyFromArray($styleArray);
                    $sheet->getStyle("D$detail_start_row:E$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $nominal += $response_detail->total;
                    $detail_start_row++;
                }

                $total_start_row = $detail_start_row;
                $sheet->mergeCells('A' . $total_start_row . ':D' . $total_start_row);
                $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':D' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                $sheet->setCellValue("E$total_start_row", $nominal)->getStyle("E$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
                $sheet->getStyle("E$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->getColumnDimension('C')->setAutoSize(true);
                $sheet->getColumnDimension('D')->setAutoSize(true);
                $sheet->getColumnDimension('E')->setAutoSize(true);
                $writer = new Xlsx($spreadsheet);
                $filename = 'LAPORAN PURCHASE RETURN (RTR)' . date('dmYHis');
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');
                header('Filename: ' . $filename);
                $writer->save('php://output');
                break;
            case '221':
                //KOR
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
                $sheet->setCellValue('A1', $pengeluaranstokheaders->judul);
                $sheet->setCellValue('A2', 'Laporan Koreksi Stok (KOR)');
                $sheet->getStyle("A1")->getFont()->setSize(11);
                $sheet->getStyle("A2")->getFont()->setSize(11);
                $sheet->getStyle("A1")->getFont()->setBold(true);
                $sheet->getStyle("A2")->getFont()->setBold(true);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A2:F2');
                $header_start_row = 4;
                $detail_table_header_row = 8;
                $detail_start_row = $detail_table_header_row + 1;
                $alphabets = range('A', 'Z');
                $header_columns = [
                    [
                        'label' => 'No Bukti',
                        'index' => 'nobukti',
                    ],
                    [
                        'label' => 'Tanggal',
                        'index' => 'tglbukti',
                    ],
                    [
                        'label' => $data->column,
                        'index' => 'value',
                    ]
                ];
                $detail_columns = [
                    [
                        'label' => 'NO',
                    ],
                    [
                        'label' => 'NAMA BARANG',
                        'index' => 'stok'
                    ],
                    [
                        'label' => 'JUMLAH',
                        'index' => 'qty'
                    ],
                    [
                        'label' => '@',
                        'index' => 'harga',
                        'format' => 'currency'
                    ],
                    [
                        'label' => 'TOTAL',
                        'index' => 'total',
                        'format' => 'currency'
                    ],
                    [
                        'label' => 'KETERANGAN',
                        'index' => 'keterangan',
                    ]
                ];
                //LOOPING HEADER        
                foreach ($header_columns as $header_column) {
                    $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                    $sheet->setCellValue('C' . $header_start_row++, ': ' . $pengeluaranstokheaders->{$header_column['index']});
                }
                foreach ($detail_columns as $detail_columns_index => $detail_column) {
                    $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
                }
                $styleArray = array(
                    'borders' => array(
                        'allBorders' => array(
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ),
                    ),
                );
                $style_number = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    ],

                    'borders' => [
                        'top' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                        'right' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                        'bottom' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                        'left' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
                    ]
                ];
                $sheet->getStyle("A$detail_table_header_row:F$detail_table_header_row")->applyFromArray($styleArray);
                // LOOPING DETAIL
                $nominal = 0;
                foreach ($pengeluaranstok_details as $response_index => $response_detail) {

                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : 0);
                        $sheet->getStyle("A$detail_table_header_row:F$detail_table_header_row")->getFont()->setBold(true);
                        $sheet->getStyle("A$detail_table_header_row:F$detail_table_header_row")->getAlignment()->setHorizontal('center');
                    }
                    $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                    $sheet->setCellValue("B$detail_start_row", $response_detail->stok);
                    $sheet->setCellValue("C$detail_start_row", $response_detail->qty);
                    $sheet->setCellValue("D$detail_start_row", $response_detail->harga);
                    $sheet->setCellValue("E$detail_start_row", $response_detail->total);
                    $sheet->setCellValue("F$detail_start_row", $response_detail->keterangan);
                    $sheet->getStyle("F$detail_start_row")->getAlignment()->setWrapText(true);
                    $sheet->getColumnDimension('F')->setWidth(70);
                    $sheet->getStyle("A$detail_start_row:F$detail_start_row")->applyFromArray($styleArray);
                    $sheet->getStyle("D$detail_start_row:E$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                    $nominal += $response_detail->total;
                    $detail_start_row++;
                }

                $total_start_row = $detail_start_row;
                $sheet->mergeCells('A' . $total_start_row . ':D' . $total_start_row);
                $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':D' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                $sheet->setCellValue("E$total_start_row", $nominal)->getStyle("E$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
                $sheet->getStyle("E$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->getColumnDimension('C')->setAutoSize(true);
                $sheet->getColumnDimension('D')->setAutoSize(true);
                $sheet->getColumnDimension('E')->setAutoSize(true);
                $writer = new Xlsx($spreadsheet);
                $filename = 'LAPORAN KOREKSI STOK (KOR)' . date('dmYHis');
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');
                header('Filename: ' . $filename);
                $writer->save('php://output');
                break;
            case '340':
                //PJA
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
                $sheet->setCellValue('A1', $pengeluaranstokheaders->judul);
                $sheet->setCellValue('A2', 'Laporan Penjualan Stok Afkir(PJA)');
                $sheet->getStyle("A1")->getFont()->setSize(11);
                $sheet->getStyle("A2")->getFont()->setSize(11);
                $sheet->getStyle("A1")->getFont()->setBold(true);
                $sheet->getStyle("A2")->getFont()->setBold(true);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A2:F2');
                $header_start_row = 4;
                $header_right_start_row = 4;
                $detail_table_header_row = 8;
                $detail_start_row = $detail_table_header_row + 1;
                $alphabets = range('A', 'Z');
                $header_columns = [
                    [
                        'label' => 'No Bukti',
                        'index' => 'nobukti',
                    ],
                    [
                        'label' => 'Tanggal',
                        'index' => 'tglbukti',
                    ],
                    [
                        'label' => 'Supplier',
                        'index' => 'supplier',
                    ]
                ];
                $header_right_columns = [
                    [
                        'label' => 'Kas/Bank',
                        'index' => 'bank',
                    ],
                    [
                        'label' => 'Tanggal Post',
                        'index' => 'tglkasmasuk',
                    ]
                ];
                $detail_columns = [
                    [
                        'label' => 'NO',
                    ],
                    [
                        'label' => 'NAMA BARANG',
                        'index' => 'stok'
                    ],
                    [
                        'label' => 'JUMLAH',
                        'index' => 'qty'
                    ],
                    [
                        'label' => '@',
                        'index' => 'harga',
                        'format' => 'currency'
                    ],
                    [
                        'label' => 'TOTAL',
                        'index' => 'total',
                        'format' => 'currency'
                    ],
                    [
                        'label' => 'KETERANGAN',
                        'index' => 'keterangan',
                    ]
                ];
                //LOOPING HEADER        
                foreach ($header_columns as $header_column) {
                    $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                    $sheet->setCellValue('C' . $header_start_row++, ': ' . $pengeluaranstokheaders->{$header_column['index']});
                }
                foreach ($header_right_columns as $header_right_column) {
                    $sheet->setCellValue('D' . $header_right_start_row, $header_right_column['label']);
                    $sheet->setCellValue('E' . $header_right_start_row++, ': ' . $pengeluaranstokheaders->{$header_right_column['index']});
                }
                foreach ($detail_columns as $detail_columns_index => $detail_column) {
                    $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
                }
                $styleArray = array(
                    'borders' => array(
                        'allBorders' => array(
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ),
                    ),
                );
                $style_number = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    ],

                    'borders' => [
                        'top' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                        'right' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                        'bottom' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                        'left' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
                    ]
                ];
                $sheet->getStyle("A$detail_table_header_row:F$detail_table_header_row")->applyFromArray($styleArray);
                // LOOPING DETAIL
                $nominal = 0;
                foreach ($pengeluaranstok_details as $response_index => $response_detail) {

                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : 0);
                        $sheet->getStyle("A$detail_table_header_row:F$detail_table_header_row")->getFont()->setBold(true);
                        $sheet->getStyle("A$detail_table_header_row:F$detail_table_header_row")->getAlignment()->setHorizontal('center');
                    }
                    $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                    $sheet->setCellValue("B$detail_start_row", $response_detail->stok);
                    $sheet->setCellValue("C$detail_start_row", $response_detail->qty);
                    $sheet->setCellValue("D$detail_start_row", $response_detail->harga);
                    $sheet->setCellValue("E$detail_start_row", $response_detail->total);
                    $sheet->setCellValue("F$detail_start_row", $response_detail->keterangan);
                    $sheet->getStyle("F$detail_start_row")->getAlignment()->setWrapText(true);
                    $sheet->getColumnDimension('F')->setWidth(50);
                    $sheet->getStyle("A$detail_start_row:F$detail_start_row")->applyFromArray($styleArray);
                    $sheet->getStyle("D$detail_start_row:E$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                    $nominal += $response_detail->total;
                    $detail_start_row++;
                }

                $total_start_row = $detail_start_row;
                $sheet->mergeCells('A' . $total_start_row . ':D' . $total_start_row);
                $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':D' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                $sheet->setCellValue("E$total_start_row", $nominal)->getStyle("E$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
                $sheet->getStyle("E$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->getColumnDimension('C')->setAutoSize(true);
                $sheet->getColumnDimension('D')->setAutoSize(true);
                $sheet->getColumnDimension('E')->setAutoSize(true);
                $writer = new Xlsx($spreadsheet);
                $filename = 'LAPORAN PENJUALAN STOK AFKIR (PJA)' . date('dmYHis');
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');
                header('Filename: ' . $filename);
                $writer->save('php://output');
                break;
            case '353':
                //GST
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
                $sheet->setCellValue('A1', $pengeluaranstokheaders->judul);
                $sheet->setCellValue('A2', 'Laporan Sparepart Gantung Trucking(GST)');
                $sheet->getStyle("A1")->getFont()->setSize(11);
                $sheet->getStyle("A2")->getFont()->setSize(11);
                $sheet->getStyle("A1")->getFont()->setBold(true);
                $sheet->getStyle("A2")->getFont()->setBold(true);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A2:F2');
                $header_start_row = 4;
                $detail_table_header_row = 8;
                $detail_start_row = $detail_table_header_row + 1;
                $alphabets = range('A', 'Z');
                $header_columns = [
                    [
                        'label' => 'No Bukti',
                        'index' => 'nobukti',
                    ],
                    [
                        'label' => 'Tanggal',
                        'index' => 'tglbukti',
                    ],
                    [
                        'label' => $data->column,
                        'index' => 'value',
                    ]
                ];
                $detail_columns = [
                    [
                        'label' => 'NO',
                    ],
                    [
                        'label' => 'NAMA BARANG',
                        'index' => 'stok'
                    ],
                    [
                        'label' => 'JUMLAH',
                        'index' => 'qty'
                    ],
                    [
                        'label' => '@',
                        'index' => 'harga',
                        'format' => 'currency'
                    ],
                    [
                        'label' => 'TOTAL',
                        'index' => 'total',
                        'format' => 'currency'
                    ],
                    [
                        'label' => 'KETERANGAN',
                        'index' => 'keterangan',
                    ]
                ];
                //LOOPING HEADER        
                foreach ($header_columns as $header_column) {
                    $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                    $sheet->setCellValue('C' . $header_start_row++, ': ' . $pengeluaranstokheaders->{$header_column['index']});
                }
                foreach ($detail_columns as $detail_columns_index => $detail_column) {
                    $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
                }
                $styleArray = array(
                    'borders' => array(
                        'allBorders' => array(
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ),
                    ),
                );
                $style_number = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    ],

                    'borders' => [
                        'top' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                        'right' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                        'bottom' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                        'left' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
                    ]
                ];
                $sheet->getStyle("A$detail_table_header_row:F$detail_table_header_row")->applyFromArray($styleArray);
                // LOOPING DETAIL
                $nominal = 0;
                foreach ($pengeluaranstok_details as $response_index => $response_detail) {

                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : 0);
                        $sheet->getStyle("A$detail_table_header_row:F$detail_table_header_row")->getFont()->setBold(true);
                        $sheet->getStyle("A$detail_table_header_row:F$detail_table_header_row")->getAlignment()->setHorizontal('center');
                    }
                    $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                    $sheet->setCellValue("B$detail_start_row", $response_detail->stok);
                    $sheet->setCellValue("C$detail_start_row", $response_detail->qty);
                    $sheet->setCellValue("D$detail_start_row", $response_detail->harga);
                    $sheet->setCellValue("E$detail_start_row", $response_detail->total);
                    $sheet->setCellValue("F$detail_start_row", $response_detail->keterangan);
                    $sheet->getStyle("F$detail_start_row")->getAlignment()->setWrapText(true);
                    $sheet->getColumnDimension('F')->setWidth(70);
                    $sheet->getStyle("A$detail_start_row:F$detail_start_row")->applyFromArray($styleArray);
                    $sheet->getStyle("D$detail_start_row:E$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                    $nominal += $response_detail->total;
                    $detail_start_row++;
                }

                $total_start_row = $detail_start_row;
                $sheet->mergeCells('A' . $total_start_row . ':D' . $total_start_row);
                $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':D' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                $sheet->setCellValue("E$total_start_row", number_format((float) $nominal, '2', '.', ','))->getStyle("E$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
                $sheet->getStyle("E$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->getColumnDimension('C')->setAutoSize(true);
                $sheet->getColumnDimension('D')->setAutoSize(true);
                $sheet->getColumnDimension('E')->setAutoSize(true);
                $writer = new Xlsx($spreadsheet);
                $filename = 'LAPORAN SPAREPART GANTUNG TRUCKING (GST)' . date('dmYHis');
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');
                header('Filename: ' . $filename);
                $writer->save('php://output');
                break;
            default:
                break;
        }
    }


    /**
     * @ClassName 
     * @Keterangan APPROVAL EDIT DATA
     */
    public function approvalEdit($id)
    {
        DB::beginTransaction();
        try {
            $pengeluaranStokHeader = PengeluaranStokheader::lockForUpdate()->findOrFail($id);

            $statusBolehEdit = DB::table('pengeluaranstokheader')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
            $statusTidakBolehEdit = DB::table('pengeluaranstokheader')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            // statusapprovaleditabsensi,tglapprovaleditabsensi,userapprovaleditabsensi 
            if ($pengeluaranStokHeader->statusapprovaledit == $statusBolehEdit->id) {
                $pengeluaranStokHeader->statusapprovaledit = $statusTidakBolehEdit->id;
                $pengeluaranStokHeader->tglbatasedit = null;
                $aksi = $statusTidakBolehEdit->text;
            } else {
                $jambatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', '=', 'JAMBATASAPPROVAL')->where('subgrp', '=', 'JAMBATASAPPROVAL')->first();
                $tglbatasedit = date('Y-m-d') . ' ' . $jambatas->text ?? '00:00:00';
                $pengeluaranStokHeader->tglbatasedit = $tglbatasedit;
                $pengeluaranStokHeader->statusapprovaledit = $statusBolehEdit->id;
                $aksi = $statusBolehEdit->text;
            }
            $pengeluaranStokHeader->tglapprovaledit = date("Y-m-d", strtotime('today'));
            $pengeluaranStokHeader->userapprovaledit = auth('api')->user()->name;

            if ($pengeluaranStokHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pengeluaranStokHeader->getTable()),
                    'postingdari' => 'UN/APPROVED EDIT',
                    'idtrans' => $pengeluaranStokHeader->id,
                    'nobuktitrans' => $pengeluaranStokHeader->id,
                    'aksi' => $aksi,
                    'datajson' => $pengeluaranStokHeader->toArray(),
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
     * @Keterangan APPROVAL EDIT KETERANGAN
     */
    public function approvalEditKeterangan($id)
    {
        DB::beginTransaction();
        try {
            $pengeluaranStokHeader = PengeluaranStokheader::lockForUpdate()->findOrFail($id);

            $statusBolehEdit = DB::table('pengeluaranstokheader')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
            $statusTidakBolehEdit = DB::table('pengeluaranstokheader')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            // statusapprovaleditabsensi,tglapprovaleditabsensi,userapprovaleditabsensi 
            if ($pengeluaranStokHeader->statusapprovaleditketerangan == $statusBolehEdit->id) {
                $pengeluaranStokHeader->statusapprovaleditketerangan = $statusTidakBolehEdit->id;
                $pengeluaranStokHeader->tglbataseditketerangan = null;
                $aksi = $statusTidakBolehEdit->text;
            } else {
                $jambatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', '=', 'JAMBATASAPPROVAL')->where('subgrp', '=', 'JAMBATASAPPROVAL')->first();
                $tglbatasedit = date('Y-m-d') . ' ' . $jambatas->text ?? '00:00:00';
                $pengeluaranStokHeader->tglbataseditketerangan = $tglbatasedit;
                $pengeluaranStokHeader->statusapprovaleditketerangan = $statusBolehEdit->id;
                $aksi = $statusBolehEdit->text;
            }
            $pengeluaranStokHeader->tglapprovaleditketerangan = date("Y-m-d", strtotime('today'));
            $pengeluaranStokHeader->userapprovaleditketerangan = auth('api')->user()->name;

            if ($pengeluaranStokHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pengeluaranStokHeader->getTable()),
                    'postingdari' => 'UN/APPROVED EDIT KETERANGAN',
                    'idtrans' => $pengeluaranStokHeader->id,
                    'nobuktitrans' => $pengeluaranStokHeader->id,
                    'aksi' => $aksi,
                    'datajson' => $pengeluaranStokHeader->toArray(),
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
     * @Keterangan SPK 
     */
    public function pengeluaranstokspkstok() {}
    /**
     * @ClassName 
     * @Keterangan RETUR
     */
    public function pengeluaranstokreturstok() {}
    /**
     * @ClassName 
     * @Keterangan KOREKSI STOK MINUS
     */
    public function pengeluaranstokkoreksistok() {}
    /**
     * @ClassName 
     * @Keterangan PENJUALAN STOK AFKIR
     */
    public function pengeluaranstokpenjualanstokafkir() {}
    /**
     * @ClassName 
     * @Keterangan SPAREPART GANTUNG
     */
    public function pengeluaranstoksparepartgantungtrucking() {}
    /**
     * @ClassName 
     * @Keterangan KOREKSI VULKAN MINUS
     */
    public function pengeluaranstokkoreksivulkan() {}
    /**
     * @ClassName
     * @Keterangan STATUS AFKIR 
     */
    public function pengeluaranstoksetstatusafkir() {}

    /**
     * @ClassName 
     * @Keterangan APPROVAL BUKA CETAK
     */
    public function approvalbukacetak() {}
    /**
     * @ClassName 
     * @Keterangan APPROVAL KIRIM BERKAS
     */
    public function approvalkirimberkas() {}
}
