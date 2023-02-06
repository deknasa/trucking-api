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
    public function cekValidasi($id) {
        $trado= new Trado();
        $cekdata=$trado->cekvalidasihapus($id);
        if ($cekdata['kondisi']==true) {
            $query = DB::table('error')
            ->select(
                DB::raw("ltrim(rtrim(keterangan))+' (".$cekdata['keterangan'].")' as keterangan")
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
            $trado = new Trado();
            $trado->keterangan = $request->keterangan;
            $trado->statusaktif = $request->statusaktif;
            $trado->kmawal = str_replace(',', '', $request->kmawal);
            $trado->kmakhirgantioli = str_replace(',', '', $request->kmakhirgantioli);
            $trado->tglakhirgantioli = date('Y-m-d', strtotime($request->tglakhirgantioli));
            $trado->tglstnkmati = date('Y-m-d', strtotime($request->tglstnkmati));
            $trado->tglasuransimati = date('Y-m-d', strtotime($request->tglasuransimati));
            $trado->tahun = $request->tahun;
            $trado->akhirproduksi = $request->akhirproduksi;
            $trado->merek = $request->merek;
            $trado->norangka = $request->norangka;
            $trado->nomesin = $request->nomesin;
            $trado->nama = $request->nama;
            $trado->nostnk = $request->nostnk;
            $trado->alamatstnk = $request->alamatstnk;
            $trado->tglstandarisasi = date('Y-m-d', strtotime($request->tglstandarisasi));
            $trado->tglserviceopname = date('Y-m-d', strtotime($request->tglserviceopname));
            $trado->statusstandarisasi = $request->statusstandarisasi;
            $trado->keteranganprogressstandarisasi = $request->keteranganprogressstandarisasi;
            $trado->statusjenisplat = $request->statusjenisplat;
            $trado->tglspeksimati = date('Y-m-d', strtotime($request->tglspeksimati));
            $trado->tglpajakstnk = date('Y-m-d', strtotime($request->tglpajakstnk));
            $trado->tglgantiakiterakhir = date('Y-m-d', strtotime($request->tglgantiakiterakhir));
            $trado->statusmutasi = $request->statusmutasi;
            $trado->statusvalidasikendaraan = $request->statusvalidasikendaraan;
            $trado->tipe = $request->tipe;
            $trado->jenis = $request->jenis;
            $trado->isisilinder =  str_replace(',', '', $request->isisilinder);
            $trado->warna = $request->warna;
            $trado->jenisbahanbakar = $request->jenisbahanbakar;
            $trado->jumlahsumbu = $request->jumlahsumbu;
            $trado->jumlahroda = $request->jumlahroda;
            $trado->model = $request->model;
            $trado->nobpkb = $request->nobpkb;
            $trado->statusmobilstoring = $request->statusmobilstoring;
            $trado->mandor_id = $request->mandor_id;
            $trado->supir_id = $request->supir_id;
            $trado->jumlahbanserap = $request->jumlahbanserap;
            $trado->statusappeditban = $request->statusappeditban;
            $trado->statuslewatvalidasi = $request->statuslewatvalidasi;
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
            $trado->keterangan = $request->keterangan;
            $trado->statusaktif = $request->statusaktif;
            $trado->kmawal = str_replace(',', '', $request->kmawal);
            $trado->kmakhirgantioli = str_replace(',', '', $request->kmakhirgantioli);
            $trado->tglakhirgantioli = date('Y-m-d', strtotime($request->tglakhirgantioli));
            $trado->tglstnkmati = date('Y-m-d', strtotime($request->tglstnkmati));
            $trado->tglasuransimati = date('Y-m-d', strtotime($request->tglasuransimati));
            $trado->tahun = $request->tahun;
            $trado->akhirproduksi = $request->akhirproduksi;
            $trado->merek = $request->merek;
            $trado->norangka = $request->norangka;
            $trado->nomesin = $request->nomesin;
            $trado->nama = $request->nama;
            $trado->nostnk = $request->nostnk;
            $trado->alamatstnk = $request->alamatstnk;
            $trado->tglstandarisasi = date('Y-m-d', strtotime($request->tglstandarisasi));
            $trado->tglserviceopname = date('Y-m-d', strtotime($request->tglserviceopname));
            $trado->statusstandarisasi = $request->statusstandarisasi;
            $trado->keteranganprogressstandarisasi = $request->keteranganprogressstandarisasi;
            $trado->statusjenisplat = $request->statusjenisplat;
            $trado->tglspeksimati = date('Y-m-d', strtotime($request->tglspeksimati));
            $trado->tglpajakstnk = date('Y-m-d', strtotime($request->tglpajakstnk));
            $trado->tglgantiakiterakhir = date('Y-m-d', strtotime($request->tglgantiakiterakhir));
            $trado->statusmutasi = $request->statusmutasi;
            $trado->statusvalidasikendaraan = $request->statusvalidasikendaraan;
            $trado->tipe = $request->tipe;
            $trado->jenis = $request->jenis;
            $trado->isisilinder =  str_replace(',', '', $request->isisilinder);
            $trado->warna = $request->warna;
            $trado->jenisbahanbakar = $request->jenisbahanbakar;
            $trado->jumlahsumbu = $request->jumlahsumbu;
            $trado->jumlahroda = $request->jumlahroda;
            $trado->model = $request->model;
            $trado->nobpkb = $request->nobpkb;
            $trado->statusmobilstoring = $request->statusmobilstoring;
            $trado->mandor_id = $request->mandor_id;
            $trado->supir_id = $request->supir_id;
            $trado->jumlahbanserap = $request->jumlahbanserap;
            $trado->statusappeditban = $request->statusappeditban;
            $trado->statuslewatvalidasi = $request->statuslewatvalidasi;

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

    public function getImage(string $field, string $filename, string $type)
    {
        return response()->file(storage_path("app/$field/$type-$filename"));
    }

    private function storeFiles(array $files, string $destinationFolder): string
    {
        $storedFiles = [];

        foreach ($files as $file) {
            $originalFileName = $file->hashName();
            $storedFile = Storage::putFileAs($destinationFolder, $file, 'ori-' . $originalFileName);
            $resizedFiles = App::imageResize(storage_path("app/$destinationFolder/"), storage_path("app/$storedFile"), $originalFileName);

            $storedFiles[] = $originalFileName;
        }

        return json_encode($storedFiles);
    }

    private function deleteFiles(Trado $trado)
    {
        $sizeTypes = ['ori', 'medium', 'small'];

        $relatedPhotoTrado = [];
        $relatedPhotoStnk = [];
        $relatedPhotoBpkb = [];

        $photoTrado = json_decode($trado->phototrado, true);
        $photoStnk = json_decode($trado->photostnk, true);
        $photoBpkb = json_decode($trado->photobpkb, true);

        if ($photoTrado != '') {
            foreach ($photoTrado as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoTrado[] = "trado/$sizeType-$path";
                }
            }
            Storage::delete($relatedPhotoTrado);
        }

        if ($photoStnk != '') {
            foreach ($photoStnk as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoStnk[] = "stnk/$sizeType-$path";
                }
            }
            Storage::delete($relatedPhotoStnk);
        }

        if ($photoBpkb != '') {
            foreach ($photoBpkb as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoBpkb[] = "bpkb/$sizeType-$path";
                }
            }
            Storage::delete($relatedPhotoBpkb);
        }
    }
}
