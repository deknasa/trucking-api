<?php

namespace App\Http\Controllers\Api;

use App\Helpers\App;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTradoRequest;
use App\Http\Requests\TradoRequest;
use App\Http\Requests\UpdateTradoRequest;
use App\Models\Trado;
use App\Models\LogTrail;
use App\Models\Parameter;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Models\Stok;
use App\Models\StokPersediaan;

use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class TradoController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $trado = new Trado();


        return response([
            'data' => $trado->get(),
            'attributes' => [
                'totalRows' => $trado->totalRows,
                'totalPages' => $trado->totalPages
            ]
        ]);
    }
    public function cekValidasi($id)
    {
        $trado = new Trado();
        $cekdata = $trado->cekvalidasihapus($id);
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
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        } else {
            $data = [
                'status' => false,
                'message' => '',
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        }
    }

    public function default()
    {

        $trado = new Trado();
        return response([
            'status' => true,
            'data' => $trado->default(),
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreTradoRequest $request)
    {
        DB::beginTransaction();
        try {
            $statusStandarisasi = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS STANDARISASI')->where('default', 'YA')->first();
            $statusMutasi = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS MUTASI')->where('default', 'YA')->first();
            $statusValidasi = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS VALIDASI KENDARAAN')->where('default', 'YA')->first();
            $statusMobStoring = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS MOBIL STORING')->where('default', 'YA')->first();
            $statusAppeditban = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL EDIT BAN')->where('default', 'YA')->first();
            $statusLewatValidasi = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS LEWAT VALIDASI')->where('default', 'YA')->first();
            $trado = new Trado();
            $trado->keterangan = $request->keterangan ?? '';
            $trado->kodetrado = $request->kodetrado;
            $trado->statusaktif = $request->statusaktif;
            $trado->tahun = $request->tahun;
            $trado->merek = $request->merek;
            $trado->norangka = $request->norangka;
            $trado->nomesin = $request->nomesin;
            $trado->nama = $request->nama;
            $trado->nostnk = $request->nostnk;
            $trado->alamatstnk = $request->alamatstnk;
            $trado->statusstandarisasi = $statusStandarisasi->id;
            $trado->statusjenisplat = $request->statusjenisplat;
            $trado->statusmutasi = $statusMutasi->id;
            $trado->tglpajakstnk = date('Y-m-d', strtotime($request->tglpajakstnk));
            $trado->statusvalidasikendaraan = $statusValidasi->id;
            $trado->tipe = $request->tipe;
            $trado->jenis = $request->jenis;
            $trado->isisilinder =  str_replace(',', '', $request->isisilinder);
            $trado->warna = $request->warna;
            $trado->jenisbahanbakar = $request->jenisbahanbakar;
            $trado->jumlahsumbu = $request->jumlahsumbu;
            $trado->jumlahroda = $request->jumlahroda;
            $trado->model = $request->model;
            $trado->nobpkb = $request->nobpkb;
            $trado->statusmobilstoring = $statusMobStoring->id;
            $trado->mandor_id = $request->mandor_id ?? 0;
            $trado->supir_id = $request->supir_id ?? 0;
            $trado->jumlahbanserap = $request->jumlahbanserap;
            $trado->statusgerobak = $request->statusgerobak;
            $trado->statusappeditban = $statusAppeditban->id;
            $trado->statuslewatvalidasi = $statusLewatValidasi->id;
            $trado->modifiedby = auth('api')->user()->name;

            $trado->photostnk = $this->storeFiles($request->photostnk, 'stnk');
            $trado->photobpkb = $this->storeFiles($request->photobpkb, 'bpkb');
            $trado->phototrado = $this->storeFiles($request->phototrado, 'trado');

            $trado->save();

            $logTrail = [
                'namatabel' => strtoupper($trado->getTable()),
                'postingdari' => 'ENTRY TRADO',
                'idtrans' => $trado->id,
                'nobuktitrans' => $trado->id,
                'aksi' => 'ENTRY',
                'datajson' => $trado->toArray(),
                'modifiedby' => $trado->modifiedby
            ];
            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);


            $param1 = $trado->id;
            $param2 = $trado->modifiedby;
            $stokgudang = Stok::from(DB::raw("stok with (readuncommitted)"))
                ->select(DB::raw(
                    "stok.id as stok_id,
                    0  as gudang_id,"
                        . $param1 . " as trado_id,
                0 as gandengan_id,
                0 as qty,'"
                        . $param2 . "' as modifiedby"
                ))
                ->leftjoin('stokpersediaan', function ($join) use ($param1) {
                    $join->on('stokpersediaan.stok_id', '=', 'stok.id');
                    $join->on('stokpersediaan.trado_id', '=', DB::raw("'" . $param1 . "'"));
                })
                ->where(DB::raw("isnull(stokpersediaan.id,0)"), '=', 0);



            $datadetail = json_decode($stokgudang->get(), true);

            $dataexist = $stokgudang->exists();
            $detaillogtrail = [];
            foreach ($datadetail as $item) {


                $stokpersediaan = new StokPersediaan();
                $stokpersediaan->stok_id = $item['stok_id'];
                $stokpersediaan->gudang_id = $item['gudang_id'];
                $stokpersediaan->trado_id = $item['trado_id'];
                $stokpersediaan->gandengan_id = $item['gandengan_id'];
                $stokpersediaan->qty = $item['qty'];
                $stokpersediaan->modifiedby = $item['modifiedby'];
                $stokpersediaan->save();
                $detaillogtrail[] = $stokpersediaan->toArray();
            }

            if ($dataexist == true) {

                $logTrail = [
                    'namatabel' => strtoupper($stokpersediaan->getTable()),
                    'postingdari' => 'STOK PERSEDIAAN',
                    'idtrans' => $trado->id,
                    'nobuktitrans' => $trado->id,
                    'aksi' => 'EDIT',
                    'datajson' => json_encode($detaillogtrail),
                    'modifiedby' => $trado->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);
            }


            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($trado, $trado->getTable());
            $trado->position = $selected->position;
            $trado->page = ceil($trado->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $trado
            ], 201);
        } catch (\Throwable $th) {
            $this->deleteFiles($trado);
            DB::rollBack();
            return response($th->getMessage());
        }
    }
    /**
     * @ClassName 
     */
    public function update(UpdateTradoRequest $request, Trado $trado)
    {
        DB::beginTransaction();
        try {
            $trado->keterangan = $request->keterangan ?? '';
            $trado->kodetrado = $request->kodetrado;
            $trado->statusaktif = $request->statusaktif;
            $trado->tahun = $request->tahun;
            $trado->merek = $request->merek;
            $trado->norangka = $request->norangka;
            $trado->nomesin = $request->nomesin;
            $trado->nama = $request->nama;
            $trado->nostnk = $request->nostnk;
            $trado->alamatstnk = $request->alamatstnk;
            $trado->statusjenisplat = $request->statusjenisplat;
            $trado->tipe = $request->tipe;
            $trado->jenis = $request->jenis;
            $trado->tglpajakstnk = date('Y-m-d', strtotime($request->tglpajakstnk));
            $trado->isisilinder =  str_replace(',', '', $request->isisilinder);
            $trado->warna = $request->warna;
            $trado->jenisbahanbakar = $request->jenisbahanbakar;
            $trado->jumlahsumbu = $request->jumlahsumbu;
            $trado->jumlahroda = $request->jumlahroda;
            $trado->model = $request->model;
            $trado->nobpkb = $request->nobpkb;
            $trado->mandor_id = $request->mandor_id ?? 0;
            $trado->supir_id = $request->supir_id ?? 0;
            $trado->jumlahbanserap = $request->jumlahbanserap;
            $trado->statusgerobak = $request->statusgerobak;

            $this->deleteFiles($trado);

            $trado->photostnk = $this->storeFiles($request->photostnk, 'stnk');
            $trado->photobpkb = $this->storeFiles($request->photobpkb, 'bpkb');
            $trado->phototrado = $this->storeFiles($request->phototrado, 'trado');
            $trado->save();
            if ($trado->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($trado->getTable()),
                    'postingdari' => 'EDIT TRADO',
                    'idtrans' => $trado->id,
                    'nobuktitrans' => $trado->id,
                    'aksi' => 'EDIT',
                    'datajson' => $trado->toArray(),
                    'modifiedby' => $trado->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);
            }

            $param1 = $trado->id;
            $param2 = $trado->modifiedby;
            $stokgudang = Stok::from(DB::raw("stok with (readuncommitted)"))
                ->select(DB::raw(
                    "stok.id as stok_id,
                    0  as gudang_id,"
                        . $param1 . " as trado_id,
                0 as gandengan_id,
                0 as qty,'"
                        . $param2 . "' as modifiedby"
                ))
                ->leftjoin('stokpersediaan', function ($join) use ($param1) {
                    $join->on('stokpersediaan.stok_id', '=', 'stok.id');
                    $join->on('stokpersediaan.trado_id', '=', DB::raw("'" . $param1 . "'"));
                })
                ->where(DB::raw("isnull(stokpersediaan.id,0)"), '=', 0);



            $datadetail = json_decode($stokgudang->get(), true);

            $dataexist = $stokgudang->exists();
            $detaillogtrail = [];
            foreach ($datadetail as $item) {


                $stokpersediaan = new StokPersediaan();
                $stokpersediaan->stok_id = $item['stok_id'];
                $stokpersediaan->gudang_id = $item['gudang_id'];
                $stokpersediaan->trado_id = $item['trado_id'];
                $stokpersediaan->gandengan_id = $item['gandengan_id'];
                $stokpersediaan->qty = $item['qty'];
                $stokpersediaan->modifiedby = $item['modifiedby'];
                $stokpersediaan->save();
                $detaillogtrail[] = $stokpersediaan->toArray();
            }

            if ($dataexist == true) {

                $logTrail = [
                    'namatabel' => strtoupper($stokpersediaan->getTable()),
                    'postingdari' => 'STOK PERSEDIAAN',
                    'idtrans' => $trado->id,
                    'nobuktitrans' => $trado->id,
                    'aksi' => 'EDIT',
                    'datajson' => json_encode($detaillogtrail),
                    'modifiedby' => $trado->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);
            }


            DB::commit();

            $selected = $this->getPosition($trado, $trado->getTable());
            $trado->position = $selected->position;
            $trado->page = ceil($trado->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $trado
            ]);
        } catch (\Throwable $th) {
            $this->deleteFiles($trado);
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function show($id)
    {
        $trado = Trado::findAll($id);
        return response([
            'status' => true,
            'data' => $trado
        ]);
    }
    /**
     * @ClassName 
     */
    public function destroy(Request $request, $id)
    {


        DB::beginTransaction();

        $trado = new Trado();
        $trado = $trado->lockAndDestroy($id);

        if ($trado) {
            $logTrail = [
                'namatabel' => strtoupper($trado->getTable()),
                'postingdari' => 'DELETE TRADO',
                'idtrans' => $trado->id,
                'nobuktitrans' => $trado->id,
                'aksi' => 'DELETE',
                'datajson' => $trado->toArray(),
                'modifiedby' => $trado->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);


            $this->deleteFiles($trado);
            dd('here');
            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($trado, $trado->getTable(), true);
            $trado->position = $selected->position;
            $trado->id = $selected->id;
            $trado->page = ceil($trado->position / ($request->limit ?? 10));

            // dd($trado);
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $trado
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('trado')->getColumns();

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
            'status' => Parameter::where(['grp' => 'status aktif'])->get(),
            'standarisasi' => Parameter::where(['grp' => 'status standarisasi'])->get(),
            'plat' => Parameter::where(['grp' => 'jenis plat'])->get(),
            'mutasi' => Parameter::where(['grp' => 'status mutasi'])->get(),
            'validasikendaraan' => Parameter::where(['grp' => 'status validasi kendaraan'])->get(),
            'mobilstoring' => Parameter::where(['grp' => 'status mobil storing'])->get(),
            'appeditban' => Parameter::where(['grp' => 'status app edit ban'])->get(),
            'lewatvalidasi' => Parameter::where(['grp' => 'status lewat validasi'])->get(),
            'mandor' => DB::table('mandor')->get(),
        ];

        return response([
            'data' => $data
        ]);
    }

    public function getImage(string $field, string $filename, string $type, string $aksi)
    {
        if (Storage::exists("trado/$field/$type" . '_' . "$filename")) {
            return response()->file(storage_path("app/trado/$field/$type" . '_' . "$filename"));
        } else {
            if (Storage::exists("trado/$field/$filename")) {
                return response()->file(storage_path("app/trado/$field/$filename"));
            } else {
                if ($aksi == 'show') {
                    return response()->file(storage_path("app/no-image.jpg"));
                }else{
                    return response('no-image');
                }
            }
        }
    }

    private function storeFiles(array $files, string $destinationFolder): string
    {
        $storedFiles = [];

        foreach ($files as $file) {
            $originalFileName = $file->hashName();
            $storedFile = Storage::putFileAs("trado/" . $destinationFolder, $file, $originalFileName);
            $resizedFiles = App::imageResize(storage_path("app/trado/$destinationFolder/"), storage_path("app/$storedFile"), $originalFileName);

            $storedFiles[] = $originalFileName;
        }

        return json_encode($storedFiles);
    }

    private function deleteFiles(Trado $trado)
    {
        $sizeTypes = ['', 'medium_', 'small_'];

        $relatedPhotoTrado = [];
        $relatedPhotoStnk = [];
        $relatedPhotoBpkb = [];

        $photoTrado = json_decode($trado->phototrado, true);
        $photoStnk = json_decode($trado->photostnk, true);
        $photoBpkb = json_decode($trado->photobpkb, true);

        if ($photoTrado != '') {
            foreach ($photoTrado as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoTrado[] = "trado/trado/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoTrado);
        }

        if ($photoStnk != '') {
            foreach ($photoStnk as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoStnk[] = "trado/stnk/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoStnk);
        }

        if ($photoBpkb != '') {
            foreach ($photoBpkb as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoBpkb[] = "trado/bpkb/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoBpkb);
        }
    }
}
