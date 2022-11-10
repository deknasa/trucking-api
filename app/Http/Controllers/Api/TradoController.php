<?php

namespace App\Http\Controllers\Api;

use App\Helpers\App;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\TradoRequest;
use App\Models\Trado;
use App\Models\LogTrail;
use App\Models\Parameter;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class TradoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @ClassName 
     */
    public function index()
    {
        $trado = new Trado();

        $rows = $trado->get();

        $baseUrl = asset('');

        foreach ($rows as $key => $item) {
            $arrtrado   = json_decode($item->phototrado, true);
            $arrstnk    = json_decode($item->photostnk, true);
            $arrbpkb    = json_decode($item->photobpkb, true);

            $imgtrado = '';
            if (!empty($arrtrado)) {
                foreach($arrtrado as $index => $file){
                    $imgtrado .= "<img src='" . $baseUrl . "uploads/". $file . "' class='mr-2' style='width:50px; height:50px'>"; 
                }
            }

            $imgbpkb = '';
            if (!empty($arrbpkb)) {
                foreach($arrbpkb as $index => $file){
                    $imgbpkb .= "<img src='" . $baseUrl . "uploads/". $file . "' class='mr-2' style='width:50px; height:50px'>"; 
                }
            }

            $imgstnk = '';
            if (!empty($arrstnk)) {
                // $count = count($arrstnk);
                // if ($count > 0) {
                //     $total = $count / 3;
                //     $idx = 2;
                //     for ($i = 0; $i < $total; $i++) {
                //         if ($i > 0) {
                //             $idx += 3;
                //         }

                //         $imgstnk .= "<img src='" . $baseUrl . 'uploads/stnk/' . $arrstnk[$idx] . "' class='mr-2'>";
                //     }
                // }
                Foreach($arrstnk as $index => $file){
                    $imgstnk .= "<img src='" . $baseUrl . "uploads/". $file . "' class='mr-2' style='width:50px; height:50px'>"; 
                }
            }

            $rows[$key]->phototrado   = $imgtrado;
            $rows[$key]->photobpkb    = $imgbpkb;
            $rows[$key]->photostnk    = $imgstnk;
        }

        return response([
            'data' => $rows,
            'attributes' => [
                'totalRows' => $trado->totalRows,
                'totalPages' => $trado->totalPages
            ]
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(TradoRequest $request)
    {
        DB::beginTransaction();
        try {
            $trado = new Trado();
            $trado->keterangan = $request->keterangan;
            $trado->statusaktif = $request->statusaktif;
            $trado->kmawal = $request->kmawal;
            $trado->kmakhirgantioli = $request->kmakhirgantioli;
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
            $trado->statusjenisplat = $request->jenisplat;
            $trado->tglspeksimati = date('Y-m-d', strtotime($request->tglspeksimati));
            $trado->tglpajakstnk = date('Y-m-d', strtotime($request->tglpajakstnk));
            $trado->tglgantiakiterakhir = date('Y-m-d', strtotime($request->tglgantiakiterakhir));
            $trado->statusmutasi = $request->statusmutasi;
            $trado->statusvalidasikendaraan = $request->statusvalidasikendaraan;
            $trado->tipe = $request->tipe;
            $trado->jenis = $request->jenis;
            $trado->isisilinder = $request->isisilinder;
            $trado->warna = $request->warna;
            $trado->jenisbahanbakar = $request->bahanbakar;
            $trado->jumlahsumbu = $request->jlhsumbu;
            $trado->jumlahroda = $request->jlhroda;
            $trado->model = $request->model;
            $trado->nobpkb = $request->nobpkb;
            $trado->statusmobilstoring = $request->statusmobilstoring;
            $trado->mandor_id = $request->mandor_id;
            $trado->jumlahbanserap = $request->jlhbanserap;
            $trado->statusappeditban = $request->statusappeditban;
            $trado->statuslewatvalidasi = $request->statuslewatvalidasi;
            $trado->modifiedby = auth('api')->user()->name;

            $trado->save();

            $upload = $this->upload_image($request, $trado->id, 'ADD');

            DB::commit();
            /* Set position and page */
            // $del = 0;
            // // $data = $this->getid($trado->id, $request, $del);
            // $trado->position = @$data->row;

            // if (isset($request->limit)) {
            //     $trado->page = ceil($trado->position / $request->limit);
            // }

            /* Set position and page */
            // $selected = $this->getPosition($trado, $trado->getTable());
            // $trado->position = $selected->position;
            // $trado->page = ceil($trado->position / ($request->limit ?? 10));

            /* Set position and page */
            $selected = $this->getPosition($trado, $trado->getTable());
            $trado->position = $selected->position;
            $trado->page = ceil($trado->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $trado
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }
    /**
     * @ClassName 
     */
    public function update(TradoRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $trado = Trado::find($id);
            $trado->keterangan = $request->keterangan;
            $trado->statusaktif = $request->statusaktif;
            $trado->kmawal = $request->kmawal;
            $trado->kmakhirgantioli = 0;
            $trado->tglakhirgantioli = '';
            $trado->tglstnkmati = date('Y-m-d', strtotime($request->tglstnkmati));
            $trado->tglasuransimati = date('Y-m-d', strtotime($request->tglasuransimati));
            $trado->tahun = $request->tahun;
            $trado->akhirproduksi = $request->akhirproduksi;
            $trado->merek = strtoupper($request->merek);
            $trado->norangka = strtoupper($request->norangka);
            $trado->nomesin = strtoupper($request->nomesin);
            $trado->nama = strtoupper($request->nama);
            $trado->nostnk = strtoupper($request->nostnk);
            $trado->alamatstnk = strtoupper($request->alamatstnk);
            $trado->modifiedby = strtoupper(auth('api')->user()->name);
            $trado->tglstandarisasi = date('Y-m-d', strtotime($request->tglstandarisasi));
            $trado->tglserviceopname = date('Y-m-d', strtotime($request->tglserviceopname));
            $trado->statusstandarisasi = $request->statusstandarisasi;
            $trado->keteranganprogressstandarisasi = strtoupper($request->keteranganprogressstandarisasi);
            // $trado->statusjenisplat = strtoupper($request->statusjenisplat);
            $trado->tglspeksimati = date('Y-m-d', strtotime($request->tglspeksimati));
            $trado->tglgantiakiterakhir = date('Y-m-d', strtotime($request->tglgantiakiterakhir));
            $trado->statusmutasi = $request->statusmutasi;
            $trado->statusvalidasikendaraan = $request->statusvalidasikendaraan;
            $trado->tipe = strtoupper($request->tipe);
            $trado->jenis = strtoupper($request->jenis);
            $trado->isisilinder = strtoupper($request->isisilinder);
            $trado->warna = strtoupper($request->warna);
            $trado->jenisbahanbakar = strtoupper($request->jenisbahanbakar);
            $trado->jumlahsumbu = strtoupper($request->jumlahsumbu);
            $trado->jumlahroda = strtoupper($request->jumlahroda);
            $trado->model = strtoupper($request->model);
            $trado->nobpkb = strtoupper($request->nobpkb);
            $trado->statusmobilstoring = strtoupper($request->statusmobilstoring);
            $trado->mandor_id = $request->mandor_id;
            $trado->jumlahbanserap = strtoupper($request->jumlahbanserap);
            $trado->statusappeditban = strtoupper($request->statusappeditban);
            $trado->statuslewatvalidasi = strtoupper($request->statuslewatvalidasi);

            $upload = $this->upload_image($request, $id, 'EDIT');

            $trado->save();
            // $datajson = [
            //     'id' => $trado->id,
            //     'kodecabang' => strtoupper($request->kodecabang),
            //     'namacabang' => strtoupper($request->namacabang),
            //     'statusaktif' => $request->statusaktif,
            // ];

            // $logtrail = new LogTrail();
            // $logtrail->namatabel = 'CABANG';
            // $logtrail->postingdari = 'EDIT CABANG';
            // $logtrail->idtrans = $cabang->id;
            // $logtrail->nobuktitrans = $cabang->id;
            // $logtrail->aksi = 'EDIT';
            // $logtrail->datajson = json_encode($datajson);

            // $logtrail->save();
            DB::commit();

            /* Set position and page */
            // $selected = $this->getPosition($trado, $trado->getTable());
            // $trado->position = $selected->position;
            // $trado->page = ceil($trado->position / ($request->limit ?? 10));

             /* Set position and page */
             $selected = $this->getPosition($trado, $trado->getTable());
             $trado->position = $selected->position;
             $trado->page = ceil($trado->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $trado
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function show(Trado $trado)
    {
        return response([
            'status' => true,
            'data' => $trado
        ]);
    }
    /**
     * @ClassName 
     */
    public function destroy(Trado $trado, Request $request)
    {
        DB::beginTransaction();
        try {
            $photostnk      = json_decode($trado->photostnk, true);
            $photobpkb      = json_decode($trado->photobpkb, true);
            $phototrado     = json_decode($trado->phototrado, true);

            if (!empty($phototrado)) {
                foreach ($phototrado as $item) {
                    
                    $path = public_path() . '/uploads/' . $item;
                    if (File::exists($path)) {
                        File::delete($path);
                    }
                }
            }

            if (!empty($photobpkb)) {
                foreach ($photobpkb as $item) {
                    $path = public_path() . '/uploads/' . $item;
                    if (File::exists($path)) {
                        File::delete($path);
                    }
                }
            }

            if (!empty($photostnk)) {
                foreach ($photostnk as $item) {
                    $path = public_path() . '/uploads/' . $item;
                    if (File::exists($path)) {
                        File::delete($path);
                    }
                }
            }

            $delete = $trado->delete();

            if ($delete) {
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
            }

            DB::commit();

            // $del = 1;
            // $data = $this->getid($trado->id, $request, $del);
            // $trado->position = $data->row  ?? 0;
            // $trado->id = $data->id  ?? 0;
            // if (isset($request->limit)) {
            //     $trado->page = ceil($trado->position / $request->limit);
            // }

            // $selected = $this->getPosition($trado, $trado->getTable(), true);
            // $trado->position = $selected->position;
            // $trado->id = $selected->id;
            // $trado->page = ceil($trado->position / ($request->limit ?? 10));

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
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
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
    public function getImage($id,$field) {  
        $trado = Trado::select($field)->where('id',$id)->first();
        $kategori = substr($field,5);
        $length = strlen($kategori)+1;
        $arrtrado = json_decode($trado->$field, true);
        $files = [];
        foreach($arrtrado as $index => $value){
            // $substr = substr($file)
            // dd();
            $name = substr($value,$length);
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $file = [[
                'name' => $name,
                'size' => Storage::disk('local')->size($value),
                'ext' => 'image/'.strtolower($ext)
            ]];
            
            $files = array_merge($files, $file);
        }

        return response([
            'file' => $files,
            'base' => asset('').'uploads/'.strtoupper($kategori)
        ]);

    }

    public function upload_image(Request $request, $id, $aksi)
    {

        try {
            if ($aksi == 'EDIT') {

                $imageOld = json_decode($request->g_all);
                $get = DB::table((new Trado())->getTable())->where('id', $id)->first();

                $phototrado   = json_decode($get->phototrado, true);
                $photostnk    = json_decode($get->photostnk, true);
                $photobpkb    = json_decode($get->photobpkb, true);

                // $trado  = $request->file('g_trado');
                // $bpkb   = $request->file('g_bpkb');
                // $stnk   = $request->file('g_stnk');
                $trado  = (array)$imageOld->trado;
                $bpkb   = (array)$imageOld->bpkb;
                $stnk   = (array)$imageOld->stnk;

                if (!empty($trado)) {
                    foreach ($trado as $item) {
                        $ori    = $item;
                        $medium = substr_replace($item, "medium", 0, 3);
                        $small  = substr_replace($item, "small", 0, 3);

                        $data['trado'][] = strtoupper($ori);
                        $data['trado'][] = strtoupper($medium);
                        $data['trado'][] = strtoupper($small);
                    }

                    $diff = array_diff($phototrado, $data['trado']);

                    foreach ($diff as $val) {
                        $path = public_path() . '/uploads/trado/' . $val;
                        if (File::exists($path)) {
                            File::delete($path);
                        }
                    }
                } else {
                    if (!empty($phototrado)) {
                        foreach ($phototrado as $item) {
                            $path = public_path() . '/uploads/trado/' . $item;
                            if (File::exists($path)) {
                                File::delete($path);
                            }
                        }
                    }
                }

                if (!empty($bpkb)) {
                    foreach ($bpkb as $item) {
                        $ori = $item;
                        $medium = substr_replace($item, "medium", 0, 3);
                        $small = substr_replace($item, "small", 0, 3);

                        $data['bpkb'][] = strtoupper($ori);
                        $data['bpkb'][] = strtoupper($medium);
                        $data['bpkb'][] = strtoupper($small);
                    }

                    $diff = array_diff($photobpkb, $data['bpkb']);

                    foreach ($diff as $val) {
                        $path = public_path() . '/uploads/bpkb/' . $val;
                        if (File::exists($path)) {
                            File::delete($path);
                        }
                    }
                } else {
                    if (!empty($photobpkb)) {
                        foreach ($photobpkb as $item) {
                            $path = public_path() . '/uploads/bpkb/' . $item;
                            if (File::exists($path)) {
                                File::delete($path);
                            }
                        }
                    }
                }
                if (!empty($stnk)) {
                    foreach ($stnk as $item) {
                        $ori = $item;
                        $medium = substr_replace($item, "medium", 0, 3);
                        $small = substr_replace($item, "small", 0, 3);

                        $data['stnk'][] = strtoupper($ori);
                        $data['stnk'][] = strtoupper($medium);
                        $data['stnk'][] = strtoupper($small);
                    }

                    $diff = array_diff($photostnk, $data['stnk']);

                    foreach ($diff as $val) {
                        $path = public_path() . '/uploads/stnk/' . $val;
                        if (File::exists($path)) {
                            File::delete($path);
                        }
                    }
                } else {
                    if (!empty($photostnk)) {
                        foreach ($photostnk as $item) {
                            $path = public_path() . '/uploads/stnk/' . $item;
                            if (File::exists($path)) {
                                File::delete($path);
                            }
                        }
                    }
                }
            }

            // UPLOAD TRADO
            if ($request->phototrado) {
                foreach ($request->phototrado as $index => $file) {
                    // $basePath = public_path() . '/uploads/trado/';
                    // $uniqueName = time() . rand() . rand(10, 100) . '.' . $image->getClientOriginalName();
                    // $name = "ori-" . $uniqueName;
                    // $image->move($basePath, $name);

                    // $path = $basePath . $name;
                    // $data['trado'][] = $name;
                    // $imageResizes = App::imageResize($basePath, $path, $uniqueName);
                    // $data['trado'][] = $imageResizes[0];
                    // $data['trado'][] = $imageResizes[1];
                    $data['trado']['file'.($index+1)] = Storage::disk('local')->put('trado', $file);
                }
            }

            // UPLOAD BPKB
            if ($request->photobpkb) {
                foreach ($request->photobpkb as $index => $file) {
                    // $basePath = public_path() . '/uploads/bpkb/';
                    // $uniqueName = time() . rand() . rand(10, 100) . '.' . $image->getClientOriginalName();
                    // $name = "ori-" . $uniqueName;
                    // $image->move($basePath, $name);

                    // $path = $basePath . $name;
                    // $data['bpkb'][] = $name;
                    // $imageResizes = App::imageResize($basePath, $path, $uniqueName);
                    // $data['bpkb'][] = $imageResizes[0];
                    // $data['bpkb'][] = $imageResizes[1];
                    $data['bpkb']['file'.($index+1)] = Storage::disk('local')->put('bpkb', $file);
                }
            }

            // UPLOAD STNK
            if ($request->photostnk) {
                foreach ($request->photostnk as $index => $file) {
                    // $basePath = public_path() . '/uploads/stnk/';
                    // $uniqueName = time() . rand() . rand(10, 100) . '.' . $image->getClientOriginalName();
                    // $name = "ori-" . $uniqueName;
                    // $image->move($basePath, $name);

                    // $path = $basePath . $name;
                    // $data['stnk'][] = $name;
                    // $imageResizes = App::imageResize($basePath, $path, $uniqueName);
                    // $data['stnk'][] = $imageResizes[0];
                    // $data['stnk'][] = $imageResizes[1];
                    $data['stnk']['file'.($index+1)] = Storage::disk('local')->put('stnk', $file);
                }
            }

            $trado = Trado::find($id);
            $trado->phototrado = json_encode($data['trado'] ?? []);
            $trado->photobpkb = json_encode($data['bpkb'] ?? []);
            $trado->photostnk = json_encode($data['stnk'] ?? []);
            $trado->save();

            return [
                'status' => true,
                'message' => 'Berhasil disimpan',
            ];
        } catch (\Throwable $th) {
            dd($th->getMessage());
            return response($th->getMessage());
        }
    }

    public function uploadImage(Request $request, $id)
    {
        $aksi = 'ENTRY';
        try {
            if (isset($request['contents'])) {
                $aksi = 'EDIT';
                $request['contents'] = json_decode($request['contents']);
                $get = DB::table((new Trado())->getTable())->where('id', $id)->first();

                $phototrado   = json_decode($get->phototrado, true);
                $photostnk    = json_decode($get->photostnk, true);
                $photobpkb    = json_decode($get->photobpkb, true);

                $trado  = (array)$request['contents']->trado;
                $bpkb   = (array)$request['contents']->bpkb;
                $stnk   = (array)$request['contents']->stnk;

                if (!empty($trado)) {
                    foreach ($trado as $item) {
                        $ori    = $item;
                        $medium = substr_replace($item, "medium", 0, 3);
                        $small  = substr_replace($item, "small", 0, 3);

                        $data['trado'][] = $ori;
                        $data['trado'][] = $medium;
                        $data['trado'][] = $small;
                    }

                    $diff = array_diff($phototrado, $data['trado']);

                    foreach ($diff as $val) {
                        $path = public_path() . '/uploads/trado/' . $val;
                        if (File::exists($path)) {
                            File::delete($path);
                        }
                    }
                } else {
                    if (!empty($phototrado)) {
                        foreach ($phototrado as $item) {
                            $path = public_path() . '/uploads/trado/' . $item;
                            if (File::exists($path)) {
                                File::delete($path);
                            }
                        }
                    }
                }

                if (!empty($bpkb)) {
                    foreach ($bpkb as $item) {
                        $ori = $item;
                        $medium = substr_replace($item, "medium", 0, 3);
                        $small = substr_replace($item, "small", 0, 3);

                        $data['bpkb'][] = $ori;
                        $data['bpkb'][] = $medium;
                        $data['bpkb'][] = $small;
                    }

                    $diff = array_diff($photobpkb, $data['bpkb']);

                    foreach ($diff as $val) {
                        $path = public_path() . '/uploads/bpkb/' . $val;
                        if (File::exists($path)) {
                            File::delete($path);
                        }
                    }
                } else {
                    if (!empty($photobpkb)) {
                        foreach ($photobpkb as $item) {
                            $path = public_path() . '/uploads/bpkb/' . $item;
                            if (File::exists($path)) {
                                File::delete($path);
                            }
                        }
                    }
                }

                if (!empty($stnk)) {
                    foreach ($stnk as $item) {
                        $ori = $item;
                        $medium = substr_replace($item, "medium", 0, 3);
                        $small = substr_replace($item, "small", 0, 3);

                        $data['stnk'][] = $ori;
                        $data['stnk'][] = $medium;
                        $data['stnk'][] = $small;
                    }

                    $diff = array_diff($photostnk, $data['stnk']);

                    foreach ($diff as $val) {
                        $path = public_path() . '/uploads/stnk/' . $val;
                        if (File::exists($path)) {
                            File::delete($path);
                        }
                    }
                } else {
                    if (!empty($photostnk)) {
                        foreach ($photostnk as $item) {
                            $path = public_path() . '/uploads/stnk/' . $item;
                            if (File::exists($path)) {
                                File::delete($path);
                            }
                        }
                    }
                }
            }

            // UPLOAD TRADO
            if (isset($request['g_trado'])) {
                foreach ($request['g_trado'] as $image) {
                    $basePath = public_path() . '/uploads/trado/';
                    $uniqueName = time() . rand() . rand(10, 100) . '.' . $image->getClientOriginalName();
                    $name = "ori-" . $uniqueName;
                    $image->move($basePath, $name);

                    $path = $basePath . $name;
                    $data['trado'][] = $name;
                    $imageResizes = App::imageResize($basePath, $path, $uniqueName);
                    $data['trado'][] = $imageResizes[0];
                    $data['trado'][] = $imageResizes[1];
                }
            }

            // UPLOAD BPKB
            if (isset($request['g_bpkb'])) {
                foreach ($request['g_bpkb'] as $image) {
                    $basePath = public_path() . '/uploads/bpkb/';
                    $uniqueName = time() . rand() . rand(10, 100) . '.' . $image->getClientOriginalName();
                    $name = "ori-" . $uniqueName;
                    $image->move($basePath, $name);

                    $path = $basePath . $name;
                    $data['bpkb'][] = $name;
                    $imageResizes = App::imageResize($basePath, $path, $uniqueName);
                    $data['bpkb'][] = $imageResizes[0];
                    $data['bpkb'][] = $imageResizes[1];
                }
            }

            // UPLOAD STNK
            if (isset($request['g_stnk'])) {
                foreach ($request['g_stnk'] as $image) {
                    $basePath = public_path() . '/uploads/stnk/';
                    $uniqueName = time() . rand() . rand(10, 100) . '.' . $image->getClientOriginalName();
                    $name = "ori-" . $uniqueName;
                    $image->move($basePath, $name);

                    $path = $basePath . $name;
                    $data['stnk'][] = $name;
                    $imageResizes = App::imageResize($basePath, $path, $uniqueName);
                    $data['stnk'][] = $imageResizes[0];
                    $data['stnk'][] = $imageResizes[1];
                }
            }

            $trado = Trado::find($id);
            $trado->phototrado = json_encode($data['trado'] ?? []);
            $trado->photobpkb = json_encode($data['bpkb'] ?? []);
            $trado->photostnk = json_encode($data['stnk'] ?? []);
            $trado->save();


            $datalogtrail = [
                'namatabel' => 'TRADO',
                'postingdari' => $aksi . ' TRADO',
                'idtrans' => $trado->id,
                'nobuktitrans' => $trado->id,
                'aksi' => $aksi,
                'datajson' => json_encode($trado->getAttributes()),
                'modifiedby' => $trado->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->offset = $request->offset ?? 0;
            $request->limit = $request->limit ?? 100;
            $request->search = $request->search ?? [];
            $request->sortIndex = $request->sortIndex ?? 'id';
            $request->sortOrder = $request->sortOrder ?? 'asc';

            $del = 0;
            $data = $this->getid($trado->id, $request, $del);
            $trado->position = $data->row;
            if (isset($request->limit)) {
                $trado->page = ceil($trado->position / $request->limit);
            }

            return [
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $trado
            ];
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function getid($id, $request, $del)
    {
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->id();
            $table->bigInteger('id_')->default('0');
            $table->longText('keterangan')->default('');
            $table->string('statusaktif', 300)->default('');
            $table->double('kmawal', 15, 2)->default(0);
            $table->double('kmakhirgantioli', 15, 2)->default(0);
            $table->date('tglakhirgantioli')->default('1900/1/1');
            $table->date('tglstnkmati')->default('1900/1/1');
            $table->date('tglasuransimati')->default('1900/1/1');
            $table->string('tahun', 40)->default('');
            $table->string('akhirproduksi', 40)->default('');
            $table->string('merek', 40)->default('');
            $table->string('norangka', 40)->default('');
            $table->string('nomesin', 40)->default('');
            $table->string('nama', 40)->default('');
            $table->string('nostnk', 30)->default('');
            $table->string('alamatstnk', 30)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->date('tglstandarisasi')->default('1900/1/1');
            $table->date('tglserviceopname')->default('1900/1/1');
            $table->integer('statusstandarisasi')->length(11)->default(0);
            $table->string('keteranganprogressstandarisasi', 100)->default('');
            // $table->integer('statusjenisplat')->length(11)->default(0);
            $table->date('tglspeksimati')->default('1900/1/1');
            $table->date('tglpajakstnk')->default('1900/1/1');
            $table->date('tglgantiakiterakhir')->default('1900/1/1');
            $table->integer('statusmutasi')->length(11)->default(0);
            $table->integer('statusvalidasikendaraan')->length(11)->default(0);
            $table->string('tipe', 30)->default('');
            $table->string('jenis', 30)->default('');
            $table->integer('isisilinder')->length(11)->default(0);
            $table->string('warna', 30)->default('');
            // $table->string('jenisbahanbakar', 30)->default('');
            $table->integer('jumlahsumbu')->length(11)->default(0);
            $table->integer('jumlahroda')->length(11)->default(0);
            $table->string('model', 50)->default('');
            $table->string('nobpkb', 50)->default('');
            $table->integer('statusmobilstoring')->length(11)->default(0);
            $table->integer('mandor_id')->length(11)->default(0);
            $table->integer('jumlahbanserap')->length(11)->default(0);
            $table->integer('statusappeditban')->length(11)->default(0);
            $table->integer('statuslewatvalidasi')->length(11)->default(0);
            $table->string('photostnk', 1500)->default('');
            $table->string('photobpkb', 1500)->default('');
            $table->string('phototrado', 1500)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });



        if ($request->sortIndex == 'id') {
            $query = DB::table((new Trado())->getTable())->select(
                'trado.id as id_',
                'trado.keterangan',
                'parameter.text as statusaktif',
                'trado.kmawal',
                'trado.kmakhirgantioli',
                'trado.tglakhirgantioli',
                'trado.tglstnkmati',
                'trado.tglasuransimati',
                'trado.tahun',
                'trado.akhirproduksi',
                'trado.merek',
                'trado.norangka',
                'trado.nomesin',
                'trado.nama',
                'trado.nostnk',
                'trado.alamatstnk',
                'trado.modifiedby',
                'trado.tglstandarisasi',
                'trado.tglserviceopname',
                'trado.statusstandarisasi',
                'trado.keteranganprogressstandarisasi',
                // 'trado.statusjenisplat',
                'trado.tglspeksimati',
                'trado.tglpajakstnk',
                'trado.tglgantiakiterakhir',
                'trado.statusmutasi',
                'trado.statusvalidasikendaraan',
                'trado.tipe',
                'trado.jenis',
                'trado.isisilinder',
                'trado.warna',
                // 'trado.jenisbahanbakar',
                'trado.jumlahsumbu',
                'trado.jumlahroda',
                'trado.model',
                'trado.nobpkb',
                'trado.statusmobilstoring',
                'trado.mandor_id',
                'trado.jumlahbanserap',
                'trado.statusappeditban',
                'trado.statuslewatvalidasi',
                'trado.photostnk',
                'trado.photobpkb',
                'trado.phototrado',
                'trado.created_at',
                'trado.updated_at'
            )
                ->leftJoin('parameter', 'trado.statusaktif', '=', 'parameter.id')
                ->orderBy('trado.id', $request->sortOrder);
        } else if ($request->sortIndex == 'keterangan') {
            $query = DB::table((new Trado())->getTable())->select(
                'trado.id as id_',
                'trado.keterangan',
                'parameter.text as statusaktif',
                'trado.kmawal',
                'trado.kmakhirgantioli',
                'trado.tglakhirgantioli',
                'trado.tglstnkmati',
                'trado.tglasuransimati',
                'trado.tahun',
                'trado.akhirproduksi',
                'trado.merek',
                'trado.norangka',
                'trado.nomesin',
                'trado.nama',
                'trado.nostnk',
                'trado.alamatstnk',
                'trado.modifiedby',
                'trado.tglstandarisasi',
                'trado.tglserviceopname',
                'trado.statusstandarisasi',
                'trado.keteranganprogressstandarisasi',
                // 'trado.statusjenisplat',
                'trado.tglspeksimati',
                'trado.tglpajakstnk',
                'trado.tglgantiakiterakhir',
                'trado.statusmutasi',
                'trado.statusvalidasikendaraan',
                'trado.tipe',
                'trado.jenis',
                'trado.isisilinder',
                'trado.warna',
                // 'trado.jenisbahanbakar',
                'trado.jumlahsumbu',
                'trado.jumlahroda',
                'trado.model',
                'trado.nobpkb',
                'trado.statusmobilstoring',
                'trado.mandor_id',
                'trado.jumlahbanserap',
                'trado.statusappeditban',
                'trado.statuslewatvalidasi',
                'trado.photostnk',
                'trado.photobpkb',
                'trado.phototrado',
                'trado.created_at',
                'trado.updated_at'
            )
                ->leftJoin('parameter', 'trado.statusaktif', '=', 'parameter.id')
                ->orderBy($request->sortIndex, $request->sortOrder)
                // ->orderBy('trado.keterangan', $request->sortOrder)
                ->orderBy('trado.id', $request->sortOrder);
        } else {
            if ($request->sortOrder == 'asc') {
                $query = DB::table((new Trado())->getTable())->select(
                    'trado.id as id_',
                    'trado.keterangan',
                    'parameter.text as statusaktif',
                    'trado.kmawal',
                    'trado.kmakhirgantioli',
                    'trado.tglakhirgantioli',
                    'trado.tglstnkmati',
                    'trado.tglasuransimati',
                    'trado.tahun',
                    'trado.akhirproduksi',
                    'trado.merek',
                    'trado.norangka',
                    'trado.nomesin',
                    'trado.nama',
                    'trado.nostnk',
                    'trado.alamatstnk',
                    'trado.modifiedby',
                    'trado.tglstandarisasi',
                    'trado.tglserviceopname',
                    'trado.statusstandarisasi',
                    'trado.keteranganprogressstandarisasi',
                    // 'trado.statusjenisplat',
                    'trado.tglspeksimati',
                    'trado.tglpajakstnk',
                    'trado.tglgantiakiterakhir',
                    'trado.statusmutasi',
                    'trado.statusvalidasikendaraan',
                    'trado.tipe',
                    'trado.jenis',
                    'trado.isisilinder',
                    'trado.warna',
                    // 'trado.jenisbahanbakar',
                    'trado.jumlahsumbu',
                    'trado.jumlahroda',
                    'trado.model',
                    'trado.nobpkb',
                    'trado.statusmobilstoring',
                    'trado.mandor_id',
                    'trado.jumlahbanserap',
                    'trado.statusappeditban',
                    'trado.statuslewatvalidasi',
                    'trado.photostnk',
                    'trado.photobpkb',
                    'trado.phototrado',
                    'trado.created_at',
                    'trado.updated_at'
                )
                    ->leftJoin('parameter', 'trado.statusaktif', '=', 'parameter.id')
                    ->orderBy($request->sortIndex, $request->sortOrder)
                    ->orderBy('trado.id', $request->sortOrder);
            } else {
                $query = DB::table((new Trado())->getTable())->select(
                    'trado.id as id_',
                    'trado.keterangan',
                    'parameter.text as statusaktif',
                    'trado.kmawal',
                    'trado.kmakhirgantioli',
                    'trado.tglakhirgantioli',
                    'trado.tglstnkmati',
                    'trado.tglasuransimati',
                    'trado.tahun',
                    'trado.akhirproduksi',
                    'trado.merek',
                    'trado.norangka',
                    'trado.nomesin',
                    'trado.nama',
                    'trado.nostnk',
                    'trado.alamatstnk',
                    'trado.modifiedby',
                    'trado.tglstandarisasi',
                    'trado.tglserviceopname',
                    'trado.statusstandarisasi',
                    'trado.keteranganprogressstandarisasi',
                    // 'trado.statusjenisplat',
                    'trado.tglspeksimati',
                    'trado.tglpajakstnk',
                    'trado.tglgantiakiterakhir',
                    'trado.statusmutasi',
                    'trado.statusvalidasikendaraan',
                    'trado.tipe',
                    'trado.jenis',
                    'trado.isisilinder',
                    'trado.warna',
                    // 'trado.jenisbahanbakar',
                    'trado.jumlahsumbu',
                    'trado.jumlahroda',
                    'trado.model',
                    'trado.nobpkb',
                    'trado.statusmobilstoring',
                    'trado.mandor_id',
                    'trado.jumlahbanserap',
                    'trado.statusappeditban',
                    'trado.statuslewatvalidasi',
                    'trado.photostnk',
                    'trado.photobpkb',
                    'trado.phototrado',
                    'trado.created_at',
                    'trado.updated_at'
                )
                    ->leftJoin('parameter', 'trado.statusaktif', '=', 'parameter.id')
                    ->orderBy($request->sortIndex, $request->sortOrder)

                    ->orderBy('trado.id', 'asc');
            }
        }


        DB::table($temp)->insertUsing([
            'id_', 'keterangan', 'statusaktif', 'kmawal', 'kmakhirgantioli', 'tglakhirgantioli', 'tglstnkmati', 'tglasuransimati', 'tahun', 'akhirproduksi', 'merek', 'norangka', 'nomesin', 'nama', 'nostnk', 'alamatstnk', 'modifiedby', 'tglstandarisasi', 'tglserviceopname', 'statusstandarisasi', 'keteranganprogressstandarisasi', 'tglspeksimati', 'tglpajakstnk', 'tglgantiakiterakhir', 'statusmutasi', 'statusvalidasikendaraan', 'tipe', 'jenis', 'isisilinder', 'warna',
            // 'jenisbahanbakar',
            'jumlahsumbu', 'jumlahroda', 'model', 'nobpkb', 'statusmobilstoring', 'mandor_id', 'jumlahbanserap', 'statusappeditban', 'statuslewatvalidasi', 'photostnk', 'photobpkb', 'phototrado', 'created_at', 'updated_at'
        ], $query);


        if ($del == 1) {
            if ($request->page == 1) {
                $baris = $request->indexRow + 1;
            } else {
                $hal = $request->page - 1;
                $bar = $hal * $request->limit;
                $baris = $request->indexRow + $bar + 1;
            }


            if (DB::table($temp)
                ->where('id', '=', $baris)->exists()
            ) {
                $querydata = DB::table($temp)
                    ->select('id as row', 'id_ as id')
                    ->where('id', '=', $baris)
                    ->orderBy('id');
            } else {
                $querydata = DB::table($temp)
                    ->select('id as row', 'id_ as id')
                    ->where('id', '=', ($baris - 1))
                    ->orderBy('id');
            }
        } else {
            $querydata = DB::table($temp)
                ->select('id as row')
                ->where('id_', '=',  $id)
                ->orderBy('id');
        }


        $data = $querydata->first();
        return $data;
    }
}
