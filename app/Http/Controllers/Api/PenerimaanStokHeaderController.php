<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Error;
use App\Models\Gudang;
use App\Models\MyModel;
use App\Models\Parameter;
use App\Models\HutangDetail;
use App\Models\HutangHeader;


use Illuminate\Http\Request;
use App\Models\PenerimaanStok;
use App\Models\StokPersediaan;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\PenerimaanStokDetail;
use App\Models\PenerimaanStokHeader;

use App\Models\PengeluaranStokDetail;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;

use Illuminate\Validation\ValidationException;

use App\Http\Requests\StoreHutangDetailRequest;
use App\Http\Requests\StoreHutangHeaderRequest;
use App\Http\Requests\UpdateHutangHeaderRequest;
use App\Http\Requests\DestroyHutangHeaderRequest;
use App\Http\Requests\StorePenerimaanStokDetailRequest;
use App\Http\Requests\StorePenerimaanStokHeaderRequest;
use App\Http\Requests\UpdatePenerimaanStokHeaderRequest;
use App\Http\Requests\DestroyPenerimaanStokHeaderRequest;


class PenerimaanStokHeaderController extends Controller
{
    /**
     * @ClassName 
     * PenerimaanStokHeader
     * @Detail PenerimaanStokDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        if ($request->reload == '') {
            $penerimaanStokHeader = new PenerimaanStokHeader();
            $penerimaanStokHeader->updateApproval();
            return response([
                'data' => $penerimaanStokHeader->get(),
                'attributes' => [
                    'totalRows' => $penerimaanStokHeader->totalRows,
                    'totalPages' => $penerimaanStokHeader->totalPages
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
    public function store(StorePenerimaanStokHeaderRequest $request)
    {
        DB::beginTransaction();
        try {
            /* Store header */
            $data = [
                "penerimaanstok_id" => $request->penerimaanstok_id ?? null,
                "tglbukti" => $request->tglbukti ?? null,
                "gudangdari_id" => $request->gudangdari_id ?? null,
                "gudang_id" => $request->gudang_id ?? null,
                "gudangke_id" => $request->gudangke_id ?? null,
                "tradodari_id" => $request->tradodari_id ?? null,
                "trado_id" => $request->trado_id ?? null,
                "tradoke_id" => $request->tradoke_id ?? null,
                "gandengandari_id" => $request->gandengandari_id ?? null,
                "gandengan_id" => $request->gandengan_id ?? null,
                "gandenganke_id" => $request->gandenganke_id ?? null,
                "penerimaanstok_nobukti" => $request->penerimaanstok_nobukti ?? null,
                "pengeluaranstok_nobukti" => $request->pengeluaranstok_nobukti ?? null,
                "nobon" => $request->nobon ?? null,
                "keterangan" => $request->keterangan ?? null,
                "coa" => $request->coa ?? null,
                "supplier_id" => $request->supplier_id ?? null,
                "sortname" => $request->sortname ?? null,
                "sortorder" => $request->sortorder ?? null,
                "detail_stok_id" => $request->detail_stok_id ?? [],
                "detail_vulkanisirke" => $request->detail_vulkanisirke ?? [],
                "detail_keterangan" => $request->detail_keterangan ?? [],
                "detail_qty" => $request->detail_qty ?? [],
                "detail_harga" => $request->detail_harga ?? [],
                "detail_statusban" => ($request->statusban) ? $request->statusban : $request->detail_statusban,
                "detail_penerimaanstoknobukti" => $request->detail_penerimaanstoknobukti ?? [],
                "detail_penerimaanstoknobukti_id" => $request->detail_penerimaanstoknobukti_id ?? [],
                "detail_persentasediscount" => $request->detail_persentasediscount ?? [],
                "detail_nominaldiscount" => $request->detail_nominaldiscount ?? [],
                "totalItem" => $request->totalItem ?? [],
                "totalsebelum" => $request->total_sebelum ?? [],
            ];
            $penerimaanStokHeader = (new PenerimaanStokHeader())->processStore($data);


            /* Set position and page */
            $penerimaanStokHeader->position = $this->getPosition($penerimaanStokHeader, $penerimaanStokHeader->getTable())->position;
            if ($request->limit == 0) {
                $penerimaanStokHeader->page = ceil($penerimaanStokHeader->position / (10));
            } else {
                $penerimaanStokHeader->page = ceil($penerimaanStokHeader->position / ($request->limit ?? 10));
            }
            $penerimaanStokHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $penerimaanStokHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanStokHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show(PenerimaanStokHeader $penerimaanStokHeader, $id)
    {
        return response([
            'status' => true,
            'data' => $penerimaanStokHeader->find($id),
            'detail' => PenerimaanStokDetail::getAll($id),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdatePenerimaanStokHeaderRequest $request, PenerimaanStokHeader $penerimaanStokHeader, $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            /* Store header */
            $data = [
                "penerimaanstok_id" => $request->penerimaanstok_id ?? null,
                "tglbukti" => $request->tglbukti ?? null,
                "gudangdari_id" => $request->gudangdari_id ?? null,
                "gudang_id" => $request->gudang_id ?? null,
                "gudangke_id" => $request->gudangke_id ?? null,
                "tradodari_id" => $request->tradodari_id ?? null,
                "trado_id" => $request->trado_id ?? null,
                "tradoke_id" => $request->tradoke_id ?? null,
                "gandengandari_id" => $request->gandengandari_id ?? null,
                "gandengan_id" => $request->gandengan_id ?? null,
                "gandenganke_id" => $request->gandenganke_id ?? null,
                "penerimaanstok_nobukti" => $request->penerimaanstok_nobukti ?? null,
                "pengeluaranstok_nobukti" => $request->pengeluaranstok_nobukti ?? null,
                "nobon" => $request->nobon ?? null,
                "keterangan" => $request->keterangan ?? null,
                "coa" => $request->coa ?? null,
                "supplier_id" => $request->supplier_id ?? null,
                "sortname" => $request->sortname ?? null,
                "sortorder" => $request->sortorder ?? null,
                "detail_stok_id" => $request->detail_stok_id ?? [],
                "detail_stok_id_old" => $request->detail_stok_id_old ?? [],
                "detail_vulkanisirke" => $request->detail_vulkanisirke ?? [],
                "detail_keterangan" => $request->detail_keterangan ?? [],
                "detail_qty" => $request->detail_qty ?? [],
                "detail_harga" => $request->detail_harga ?? [],
                "detail_statusban" => ($request->statusban) ? $request->statusban : $request->detail_statusban,
                "detail_penerimaanstoknobukti" => $request->detail_penerimaanstoknobukti ?? [],
                "detail_penerimaanstoknobukti_id" => $request->detail_penerimaanstoknobukti_id ?? [],
                "detail_persentasediscount" => $request->detail_persentasediscount ?? [],
                "detail_nominaldiscount" => $request->detail_nominaldiscount ?? [],
                "totalItem" => $request->totalItem ?? [],
                "totalsebelum" => $request->total_sebelum ?? [],
            ];
            $penerimaanStokHeader = PenerimaanStokHeader::findOrFail($id);
            $penerimaanStokHeader = (new PenerimaanStokHeader())->processUpdate($penerimaanStokHeader, $data);
            /* Set position and page */
            $penerimaanStokHeader->position = $this->getPosition($penerimaanStokHeader, $penerimaanStokHeader->getTable())->position;
            if ($request->limit == 0) {
                $penerimaanStokHeader->page = ceil($penerimaanStokHeader->position / (10));
            } else {
                $penerimaanStokHeader->page = ceil($penerimaanStokHeader->position / ($request->limit ?? 10));
            }
            $penerimaanStokHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $penerimaanStokHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanStokHeader
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
    public function destroy(DestroyPenerimaanStokHeaderRequest $request, PenerimaanStokHeader $penerimaanStokHeader, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $penerimaanStokHeader = (new PenerimaanStokHeader())->processDestroy($id);
            $selected = $this->getPosition($penerimaanStokHeader, $penerimaanStokHeader->getTable(), true);
            $penerimaanStokHeader->position = $selected->position;
            $penerimaanStokHeader->id = $selected->id;
            if ($request->limit == 0) {
                $penerimaanStokHeader->page = ceil($penerimaanStokHeader->position / (10));
            } else {
                $penerimaanStokHeader->page = ceil($penerimaanStokHeader->position / ($request->limit ?? 10));
            }
            $penerimaanStokHeader->tgldariheader = date('Y-m-01', strtotime($penerimaanStokHeader->tglbukti));
            $penerimaanStokHeader->tglsampaiheader = date('Y-m-t', strtotime($penerimaanStokHeader->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $penerimaanStokHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function persediaan($gudang, $trado, $gandengan)
    {
        $kolom = null;
        $nama = null;
        $value = 0;
        if (!empty($gudang)) {
            $kolom = "gudang";
            $nama = "GUDANG";
            $value = $gudang;
        } elseif (!empty($trado)) {
            $kolom = "trado";
            $nama = "TRADO";
            $value = $trado;
        } elseif (!empty($gandengan)) {
            $kolom = "gandengan";
            $nama = "GANDENGAN";
            $value = $gandengan;
        }
        return [
            "column" => $kolom,
            "value" => $value,
            "nama" => $nama,
        ];
    }

    public function persediaanDariReturn($stokId, $persediaan, $persediaanId, $qty)
    {
        $stokpersediaangudang = $this->checkTempat($stokId, $persediaan, $persediaanId); //stok persediaan 
        if (!$stokpersediaangudang) {
            return false;
        }
        $stokpersediaan = StokPersediaan::lockForUpdate()->find($stokpersediaangudang->id);
        if ($qty > $stokpersediaan->qty) { //check qty
            return false;
        }
        $result = $stokpersediaan->qty + $qty;
        $stokpersediaan->update(['qty' => $result]);
        return $stokpersediaan;
    }
    public function persediaanKeReturn($stokId, $persediaan, $persediaanId, $qty)
    {
        $stokpersediaangudang = $this->checkTempat($stokId, $persediaan, $persediaanId); //stok persediaan 
        if (!$stokpersediaangudang) {
            return false;
        }
        $stokpersediaangudang->qty -= $qty;
        $stokpersediaangudang->save();
        return $stokpersediaangudang;
    }

    public function checkTempat($stokId, $persediaan, $persediaanId)
    {
        $result = StokPersediaan::lockForUpdate()->where("stok_id", $stokId)->where("$persediaan", $persediaanId)->first();
        return (!$result) ? false : $result;
    }

    public function getPengeluaranStok($id)
    {
        $penerimaanStokHeader  = (new PenerimaanStokHeader)->getDetailPengeluaran($id);
        return response([
            'status' => true,
            'detail' => $penerimaanStokHeader,
        ]);
    }

    public function cekvalidasi($id)
    {

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        $parameter = new Parameter();

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));





        $penerimaanStokHeader  = new PenerimaanStokHeader();
        $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();
        $po = Parameter::where('grp', 'PO STOK')->where('subgrp', 'PO STOK')->first();

        $aksi = request()->aksi ?? '';
        $peneimaan = $penerimaanStokHeader->findOrFail($id);
        $nobukti=$peneimaan->nobukti ?? '';
        $user = auth('api')->user()->name;
        $useredit = $peneimaan->editing_by ?? '';
        if (!isset($peneimaan)) {
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

        if ($tgltutup >= $peneimaan->tglbukti) {
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
        

        $penerimaanstok_id = $peneimaan->penerimaanstok_id;
        $aco_id = db::table("penerimaanstok")->from(db::raw("penerimaanstok a with (readuncommitted)"))
            ->select(
                'a.aco_id'
            )->where('a.id', $penerimaanstok_id)
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
                        'statuspesan' => 'warning',
                        'kodenobukti' => '1'
                    ];
                    $passes = false;
                    return response($data);
                }
            }
        }



  

        // dd($penerimaanstok_id);
        // dd(auth('api')->user()->id);



        if ($aksi == 'PRINTER BESAR' || $aksi == 'PRINTER KECIL') {
            $msg = 'PROSES CETAK TIDAK BISA LANJUT KARENA';
            $statusdatacetak = $peneimaan->statuscetak;
            $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
            if ($statusdatacetak == $statusCetak->id) {
                // $query = DB::table('error')
                //     ->select(db::raw("'$msg <br>'+keterangan as keterangan"))
                //     ->whereRaw("kodeerror = 'SDC'")
                //     ->where('kodeerror', '=', 'SDC')
                //     ->get();
                // $keterangan = $query['0'];
                $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
                $keterangan = 'No Bukti <b>' . request()->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

                $data = [
                    'message' => $keterangan,
                    'errors' => 'sudah cetak',
                    'kodestatus' => '1',
                    'statuspesan' => 'warning',
                    'kodenobukti' => '1'
                ];
            } else {
                $data = [
                    'message' => '',
                    'errors' => 'bisa',
                    'kodestatus' => '0',
                    'statuspesan' => 'warning',
                    'kodenobukti' => '1'
                ];
            }
        } else {

            if ($aksi == 'EDIT') {
                $msg = 'PROSES EDIT TIDAK BISA LANJUT KARENA';
            } else {
                $msg = 'PROSES DELETE TIDAK BISA LANJUT KARENA';
            }

            $isEhtUsed = $penerimaanStokHeader->isEhtUsed($id);
            if ($isEhtUsed) {
                // $query = Error::from(DB::raw("error with (readuncommitted)"))
                //     ->select(db::raw("'$msg <br>'+keterangan +' <br>(" . $isEhtUsed[1] . ")' as keterangan"))
                //     ->whereRaw("kodeerror = 'SAP'")
                //     ->get();
                // $keterangan = $query['0'];
                $keteranganerror = $error->cekKeteranganError('SAP') ?? '';
                $keterangan = 'No Bukti <b>' . request()->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

                $dataUsed = [
                    'message' => $keterangan,
                    'errors' => 'Hutang',
                    'kodestatus' => '1',
                    'statuspesan' => 'warning',
                    'kodenobukti' => '1'
                ];
            }

            $isEHTApprovedJurnal = $penerimaanStokHeader->isEHTApprovedJurnal($id);
            if ($isEHTApprovedJurnal) {
                // $query = Error::from(DB::raw("error with (readuncommitted)"))
                //     ->select(db::raw("'$msg <br>'+keterangan +' <br>(" . $isEHTApprovedJurnal[1] . ")' as keterangan"))
                //     ->whereRaw("kodeerror = 'SAP'")
                //     ->get();
                // $keterangan = $query['0'];
                $keteranganerror = $error->cekKeteranganError('SAPP') ?? '';
                $keterangan = 'No Bukti <b>' . request()->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

                $dataUsed = [
                    'message' => $keterangan,
                    'errors' => 'Approval Jurnal',
                    'kodestatus' => '1',
                    'statuspesan' => 'warning',
                    'kodenobukti' => '1'
                ];
            }

            $isPOUsed = $penerimaanStokHeader->isPOUsed($id);
            if ($isPOUsed) {
                // $query = Error::from(DB::raw("error with (readuncommitted)"))
                //     ->select(db::raw("'$msg <br>'+keterangan +' <br>(" . $isPOUsed[1] . ")' as keterangan"))
                //     ->whereRaw("kodeerror = 'SATL2'")
                //     // ->select('keterangan')
                //     // ->whereRaw("kodeerror = 'SATL'")
                //     ->get();
                // $keterangan = $query['0'];
                $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
                $keterangan = 'No Bukti <b>' . request()->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

                $dataUsed = [
                    'message' => $keterangan,
                    'errors' => 'sudah approve',
                    'kodestatus' => '1',
                    'statuspesan' => 'warning',
                    'kodenobukti' => '1'
                ];
            }
            $todayValidation = $penerimaanStokHeader->todayValidation($peneimaan->tglbukti);
            if (!$todayValidation) {
                // $query = Error::from(DB::raw("error with (readuncommitted)"))
                //     ->select('keterangan')
                //     ->whereRaw("kodeerror = 'TEPT'")
                //     ->first();
                // $keterangan = $query;
                // $keterangan = ['keterangan' => 'transaksi Sudah berbeda tanggal']; //$query['0'];
                $keteranganerror = $error->cekKeteranganError('TEPT') ?? '';
                $keterangan = 'No Bukti <b>' . request()->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

                $data = [
                    'message' => $keterangan,
                    'errors' => 'sudah cetak',
                    'kodestatus' => '1',
                    'statuspesan' => 'warning',
                    'kodenobukti' => '1'
                ];
            }
            $isEditAble = $penerimaanStokHeader->isEditAble($id);
            $isKeteranganEditAble = $penerimaanStokHeader->isKeteranganEditAble($id);
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
                    'errors' => 'Transaksi Tidak Bisa diedit',
                    'kodestatus' => '1',
                    'statuspesan' => 'warning',
                    'kodenobukti' => '1'
                ];
            }
            $printValidation = $penerimaanStokHeader->printValidation($id);
            if ($printValidation) {
                // $query = Error::from(DB::raw("error with (readuncommitted)"))
                //     ->select(db::raw("'$msg <br>'+keterangan as keterangan"))
                //     ->whereRaw("kodeerror = 'SDC'")
                //     ->get();
                // $keterangan = $query['0'];
                $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
                $keterangan = 'No Bukti <b>' . request()->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

                $data = [
                    'message' => $keterangan,
                    'errors' => 'sudah cetak',
                    'kodestatus' => '1',
                    'statuspesan' => 'warning',
                    'kodenobukti' => '1'
                ];

                return response($data);
            }
            // $isOutUsed = $penerimaanStokHeader->isOutUsed($id);
            // if ($isOutUsed) {
            //     $query = Error::from(DB::raw("error with (readuncommitted)"))
            //         ->select(db::raw("'$msg <br>'+keterangan +' <br>(" . $isOutUsed[1] . ")' as keterangan"))
            //         ->whereRaw("kodeerror = 'SATL2'")
            //         ->first();

            //     $keterangan = $query;
            //     $data = [
            //         'message' => $keterangan,
            //         'errors' => 'Pengeluaran stok',
            //         'kodestatus' => '1',
            //         'kodenobukti' => '1'
            //     ];
            // }
            $isPGUsed = $penerimaanStokHeader->isPGUsed($id);

            if ($isPGUsed) {
                // $query = Error::from(DB::raw("error with (readuncommitted)"))
                //     ->select(db::raw("'$msg <br>'+keterangan +' <br>(" . $isPGUsed[1] . ")' as keterangan"))
                //     ->whereRaw("kodeerror = 'SATL2'")
                //     ->first();

                // $keterangan = $query;
                $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
                $keterangan = 'No Bukti <b>' . request()->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

                $data = [
                    'message' => $keterangan,
                    'errors' => 'Pengeluaran stok',
                    'kodestatus' => '1',
                    'statuspesan' => 'warning',
                    'kodenobukti' => '1'
                ];
            }
            // dd(!$isOutUsed||!$isPGUsed,$isOutUsed,$isPGUsed);
            // dd($dataOut);

            if (($todayValidation || (($isEditAble || $isKeteranganEditAble) && !$printValidation))) {
                if ($spb->text == $peneimaan->penerimaanstok_id) {
                    //ika sudah digunakan di eth, jurnal, dan po
                    if ($isEhtUsed || $isEHTApprovedJurnal) {
                        return response($dataUsed);
                    }
                }
                if ($po->text == $peneimaan->penerimaanstok_id) {
                    //ika sudah digunakan di spb
                    if ($isPOUsed) {
                        return response($dataUsed);
                    }
                }

                if ($useredit != '' && $useredit != $user) {
                    $waktu = (new Parameter())->cekBatasWaktuEdit('Nota Kredit Header BUKTI');
                    
                    $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($peneimaan->editing_at)));
                    $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
                    if ($diffNow->i > $waktu) {
                        if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                            (new MyModel())->updateEditingBy('penerimaanstokheader', $id, $aksi);
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
                
                if (!$isPGUsed) {
                    // if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                        (new MyModel())->updateEditingBy('penerimaanstokheader', $id, $aksi);
                    // }
        
                    $data = [
                        'message' => '',
                        'errors' => 'bisa',
                        'kodestatus' => '0',
                        'statuspesan' => 'warning',
                        'kodenobukti' => '1'
                    ];
                    return response($data);
                }
            }
        }
        return response($data);
    }

    /**
     * @ClassName 
     * @Keterangan APPROVAL EDIT DATA
     */
    public function approvalEdit(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $penerimaanStokHeader = PenerimaanStokHeader::lockForUpdate()->findOrFail($id);
            $opnameheader = DB::table('penerimaanstokheader')->from(DB::raw("opnameheader with (readuncommitted)"))->orderBy('id', 'desc')->first();
            $isBeforeOpname = false;
            $opnameTglBukti = null;
            if ($opnameheader) {
                $is_before_opname = (strtotime($opnameheader->tglbukti) > strtotime($penerimaanStokHeader->tglbukti));
                $opnameTglBukti = $opnameheader->tglbukti;
            }
            if ($request->to == 'show') {
                return response([
                    'status' => true,
                    'is_before_opname' => $isBeforeOpname,
                    'last_opname' => $opnameTglBukti,
                    'data' => $penerimaanStokHeader->find($id),
                    'detail' => PenerimaanStokDetail::getAll($id),
                ]);
            }
            $jambatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', '=', 'JAMBATASAPPROVAL')->where('subgrp', '=', 'JAMBATASAPPROVAL')->first();
            $tglbatas = date('Y-m-d') . ' ' . $jambatas->text ?? '00:00:00';
            $statusBolehEdit = DB::table('penerimaanstokheader')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
            $statusTidakBolehEdit = DB::table('penerimaanstokheader')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            // statusapprovaleditabsensi,tglapprovaleditabsensi,userapprovaleditabsensi 
            if ($penerimaanStokHeader->statusapprovaledit == $statusBolehEdit->id) {
                $penerimaanStokHeader->statusapprovaledit = $statusTidakBolehEdit->id;
                $penerimaanStokHeader->tglbatasedit = null;
                $aksi = $statusTidakBolehEdit->text;
            } else {
                $tglbatasedit = $tglbatas;
                $penerimaanStokHeader->tglbatasedit = $tglbatasedit;
                $penerimaanStokHeader->statusapprovaledit = $statusBolehEdit->id;
                $aksi = $statusBolehEdit->text;
            }
            $penerimaanStokHeader->tglapprovaledit = date("Y-m-d", strtotime('today'));
            $penerimaanStokHeader->userapprovaledit = auth('api')->user()->name;

            if ($penerimaanStokHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerimaanStokHeader->getTable()),
                    'postingdari' => 'UN/APPROVED EDIT',
                    'idtrans' => $penerimaanStokHeader->id,
                    'nobuktitrans' => $penerimaanStokHeader->id,
                    'aksi' => $aksi,
                    'datajson' => $penerimaanStokHeader->toArray(),
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
            $penerimaanStokHeader = PenerimaanStokHeader::lockForUpdate()->findOrFail($id);
            $jambatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', '=', 'JAMBATASAPPROVAL')->where('subgrp', '=', 'JAMBATASAPPROVAL')->first();
            $tglbatas = date('Y-m-d') . ' ' . $jambatas->text ?? '00:00:00';
            $statusBolehEdit = DB::table('penerimaanstokheader')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
            $statusTidakBolehEdit = DB::table('penerimaanstokheader')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            // statusapprovaleditabsensi,tglapprovaleditabsensi,userapprovaleditabsensi 
            if ($penerimaanStokHeader->statusapprovaleditketerangan == $statusBolehEdit->id) {
                $penerimaanStokHeader->statusapprovaleditketerangan = $statusTidakBolehEdit->id;
                $penerimaanStokHeader->tglbataseditketerangan = null;
                $aksi = $statusTidakBolehEdit->text;
            } else {
                $tglbatasedit = $tglbatas;
                $penerimaanStokHeader->tglbataseditketerangan = $tglbatasedit;
                $penerimaanStokHeader->statusapprovaleditketerangan = $statusBolehEdit->id;
                $aksi = $statusBolehEdit->text;
            }
            $penerimaanStokHeader->tglapprovaleditketerangan = date("Y-m-d", strtotime('today'));
            $penerimaanStokHeader->userapprovaleditketerangan = auth('api')->user()->name;

            if ($penerimaanStokHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerimaanStokHeader->getTable()),
                    'postingdari' => 'UN/APPROVED EDIT KETERANGAN',
                    'idtrans' => $penerimaanStokHeader->id,
                    'nobuktitrans' => $penerimaanStokHeader->id,
                    'aksi' => $aksi,
                    'datajson' => $penerimaanStokHeader->toArray(),
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
    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $penerimaanStokHeader = PenerimaanStokheader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($penerimaanStokHeader->statuscetak != $statusSudahCetak->id) {
                $penerimaanStokHeader->statuscetak = $statusSudahCetak->id;
                $penerimaanStokHeader->tglbukacetak = date('Y-m-d H:i:s');
                $penerimaanStokHeader->userbukacetak = auth('api')->user()->name;
                $penerimaanStokHeader->jumlahcetak = $penerimaanStokHeader->jumlahcetak + 1;
                if ($penerimaanStokHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($penerimaanStokHeader->getTable()),
                        'postingdari' => 'PRINT PENERIMAAN STOK HEADER',
                        'idtrans' => $penerimaanStokHeader->id,
                        'nobuktitrans' => $penerimaanStokHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $penerimaanStokHeader->toArray(),
                        'modifiedby' => $penerimaanStokHeader->modifiedby
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

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('penerimaanstokheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

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
    }

    /**
     * @ClassName 
     * @Keterangan PG DO
     */
    public function penerimaanstokpgdo()
    {
    }
    /**
     * @ClassName 
     * @Keterangan PO
     */
    public function penerimaanstokpostok()
    {
    }
    /**
     * @ClassName 
     * @Keterangan PEMBELIAN
     */
    public function penerimaanstokbelistok()
    {
    }
    /**
     * @ClassName 
     * @Keterangan KOREKSI STOK PLUS
     */
    public function penerimaanstokkoreksistok()
    {
    }
    /**
     * @ClassName 
     * @Keterangan PINDAH GUDANG
     */
    public function penerimaanstokpindahgudang()
    {
    }
    /**
     * @ClassName 
     * @Keterangan PERBAIKAN STOK
     */
    public function penerimaanstokperbaikanstok()
    {
    }
    /**
     * @ClassName 
     * @Keterangan SALDO STOCK
     */
    public function penerimaanstoksaldostoktrucking()
    {
    }
    /**
     * @ClassName 
     * @Keterangan PENGEMBALIAN SPAREPART GANTUNG
     */
    public function penerimaanstokpengembaliansparepartgantungtrucking()
    {
    }
    /**
     * @ClassName 
     * @Keterangan PENGEMBALIAN SPK
     */
    public function penerimaanstokpengembalianspk()
    {
    }
    /**
     * @ClassName 
     * @Keterangan KOREKSI VULKAN PLUS
     */
    public function penerimaanstokkoreksivulkan()
    {
    }
    /**
     * @ClassName 
     * @Keterangan PENAMBAHAN NILAI
     */
    public function penerimaanstokpenambahannilai()
    {
    }
    /**
     * @ClassName 
     * @Keterangan APPROVAL BUKA CETAK
     */
    public function approvalbukacetak()
    {
    }
}
