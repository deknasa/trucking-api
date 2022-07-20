<?php

namespace App\Http\Controllers\Api;

use App\Helpers\App;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreSupirRequest;

use App\Http\Controllers\Controller;
use App\Models\Supir;
use App\Models\LogTrail;
use App\Models\Parameter;
use App\Models\Zona;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class SupirController extends Controller
{
     /**
     * @ClassName 
     */
    public function index()
    {
        $supir = new Supir();

        $rows = $supir->get();

        $baseUrl = asset('');

        foreach($rows as $key => $item) {
                $arrSupir       = json_decode($item->photosupir);
                $arrKtp         = json_decode($item->photoktp);
                $arrSim         = json_decode($item->photosim);
                $arrKk          = json_decode($item->photokk);
                $arrSkck        = json_decode($item->photoskck);
                $arrDomisili    = json_decode($item->photodomisili);

                $imgSupir='';
                if (!empty($arrSupir)) {
                    $count = count($arrSupir);
                    if ($count > 0) {
                        $total = $count / 3;
                        $idx=2;
                        for ($i=0; $i < $total; $i++) {
                            if ($i>0){
                                $idx+=3;
                            }

                            $imgSupir .= "<img src='".$baseUrl.'uploads/supir/'.$arrSupir[$idx]."' class='mr-2'>";
                        }
                    }
                }

                $imgKtp='';
                if (!empty($arrKtp)) {
                    $count = count($arrKtp);
                    if ($count > 0) {
                        $total = $count / 3;
                        $idx=2;
                        for ($i=0; $i < $total; $i++) {
                            if ($i>0){
                                $idx+=3;
                            }

                            $imgKtp .= "<img src='".$baseUrl.'uploads/ktp/'.$arrKtp[$idx]."' class='mr-2'>";
                        }
                    }
                }

                $imgsim='';
                if (!empty($arrSim)) {
                    $count = count($arrSim);
                    if ($count > 0) {
                        $total = $count / 3;
                        $idx=2;
                        for ($i=0; $i < $total; $i++) {
                            if ($i>0){
                                $idx+=3;
                            }

                            $imgsim .= "<img src='".$baseUrl.'uploads/sim/'.$arrSim[$idx]."' class='mr-2'>";
                        }
                    }
                }

                $imgkk='';
                if (!empty($arrKk)) {
                    $count = count($arrKk);
                    if ($count > 0) {
                        $total = $count / 3;
                        $idx=2;
                        for ($i=0; $i < $total; $i++) {
                            if ($i>0){
                                $idx+=3;
                            }

                            $imgkk .= "<img src='".$baseUrl.'uploads/kk/'.$arrKk[$idx]."' class='mr-2'>";
                        }
                    }
                }

                $imgskck='';
                if (!empty($arrSkck)) {
                    $count = count($arrSkck);
                    if ($count > 0) {
                        $total = $count / 3;
                        $idx=2;
                        for ($i=0; $i < $total; $i++) {
                            if ($i>0){
                                $idx+=3;
                            }

                            $imgskck .= "<img src='".$baseUrl.'uploads/skck/'.$arrSkck[$idx]."' class='mr-2'>";
                        }
                    }
                }

                $imgdomisili='';
                if (!empty($arrDomisili)) {
                    $count = count($arrDomisili);
                    if ($count > 0) {
                        $total = $count / 3;
                        $idx=2;
                        for ($i=0; $i < $total; $i++) {
                            if ($i>0){
                                $idx+=3;
                            }

                            $imgdomisili .= "<img src='".$baseUrl.'uploads/domisili/'.$arrDomisili[$idx]."' class='mr-2'>";
                        }
                    }
                }
            
                $rows[$key]->photosupir       = $imgSupir;
                $rows[$key]->photoktp         = $imgKtp;
                $rows[$key]->photosim         = $imgsim;
                $rows[$key]->photokk          = $imgkk;
                $rows[$key]->photoskck        = $imgskck;
                $rows[$key]->photodomisili    = $imgdomisili;
            }

        return response([
            'data' => $rows,
            'attributes' => [
                'totalRows' => $supir->totalRows,
                'totalPages' => $supir->totalPages
            ]
        ]);
    }
 /**
     * @ClassName 
     */
    public function store(StoreSupirRequest $request)
    {
        DB::beginTransaction();
        try {
            $supir = new Supir();
            $supir->namasupir = strtoupper($request->namasupir);
            $supir->alamat = strtoupper($request->alamat);
            $supir->kota = strtoupper($request->kota);
            $supir->telp = strtoupper($request->telp);
            $supir->statusaktif = $request->statusaktif;
            $supir->nominaldepositsa = $request->nominaldepositsa ?? 0;
            $supir->depositke = $request->depositke ?? 0;
            $supir->tgl = date('Y-m-d',strtotime($request->tgl));
            $supir->nominalpinjamansaldoawal = $request->nominalpinjamansaldoawal ?? 0;
            $supir->supirold_id = $request->supirold_id ?? 0;
            $supir->tglexpsim = date('Y-m-d',strtotime($request->tglexpsim));
            $supir->nosim = $request->nosim ?? '';
            $supir->keterangan = strtoupper($request->keterangan);
            $supir->noktp = $request->noktp ?? '';
            $supir->nokk = $request->nokk ?? '';
            $supir->statusadaupdategambar = $request->statusadaupdategambar ?? 0;
            $supir->statuslluarkota = $request->statusluarkota ?? 0;
            $supir->statuszonatertentu = $request->statuszonatertentu ?? 0;
            $supir->zona_id = strtoupper($request->zona_id);
            $supir->angsuranpinjaman = $request->angsuranpinjaman ?? 0;
            $supir->plafondeposito = strtoupper($request->plafondeposito ?? '');
            $supir->keteranganresign = strtoupper($request->keteranganresign ?? '');
            $supir->statusblacklist = $request->statusblacklist ?? 0;
            $supir->tglberhentisupir = date('Y-m-d',strtotime($request->tglberhentisupir));
            $supir->tgllahir = date('Y-m-d',strtotime($request->tgllahir));
            $supir->tglterbitsim = date('Y-m-d',strtotime($request->tglterbitsim));
            $supir->modifiedby = strtoupper(auth('api')->user()->name);
            // dd($supir->getAttributes());
            $supir->save();

            $upload = $this->upload_image($request,$supir->id,'ADD');

            DB::commit();
            /* Set position and page */
            $del = 0;
            $data = $this->getid($supir->id, $request, $del);
            $supir->position = @$data->row;
            // dd($cabang->position);

            if (isset($request->limit)) {
                $supir->page = ceil((int)$supir->position / ($request->limit ?? 10));
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $supir
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }
 /**
     * @ClassName 
     */
    public function update(StoreSupirRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $supir = Supir::find($id);
            $supir->namasupir = strtoupper($request->namasupir ?? '');
            $supir->alamat = strtoupper($request->alamat ?? '');
            $supir->kota = strtoupper($request->kota ?? '');
            $supir->telp = strtoupper($request->telp ?? '');
            $supir->statusaktif = $request->statusaktif;
            $supir->nominaldepositsa = $request->nominaldepositsa;
            $supir->depositke = $request->depositke;
            $supir->tgl = date('Y-m-d',strtotime($request->tgl));
            $supir->nominalpinjamansaldoawal = $request->nominalpinjamansaldoawal;
            $supir->supirold_id = $request->supirold_id ?? 0;
            $supir->tglexpsim = date('Y-m-d',strtotime($request->tglexpsim));
            $supir->nosim = $request->nosim ?? '';
            $supir->keterangan = strtoupper($request->keterangan  ?? '');
            $supir->noktp = $request->noktp ?? '';
            $supir->nokk = $request->nokk ?? '';
            $supir->statusadaupdategambar = $request->statusadaupdategambar ?? 0;
            $supir->statuslluarkota = $request->statusluarkota ?? 0;
            $supir->statuszonatertentu = $request->statuszonatertentu ?? 0;
            $supir->zona_id = strtoupper($request->zona_id ?? 1);
            $supir->angsuranpinjaman = $request->angsuranpinjaman;
            $supir->plafondeposito = strtoupper($request->plafondeposito ?? '');
            $supir->keteranganresign = strtoupper($request->keteranganresign ?? '');
            $supir->statusblacklist = $request->statusblacklist ?? 0;
            $supir->tglberhentisupir = date('Y-m-d',strtotime($request->tglberhentisupir));
            $supir->tgllahir = date('Y-m-d',strtotime($request->tgllahir));
            $supir->tglterbitsim = date('Y-m-d',strtotime($request->tglterbitsim));
            $supir->modifiedby = strtoupper(auth('api')->user()->name);

            $upload = $this->upload_image($request,$supir->id,'ADD');

            $supir->save();
            // $datajson = [
            //     'id' => $supir->id,
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
            $supir->position = DB::table((new Supir())->getTable())->orderBy($request->sortIndex ?? 'id', $request->sortOrder ?? 'asc')
                ->where($request->sortIndex, $request->sortOrder == 'desc' ? '>=' : '<=', $supir->{$request->sortIndex})
                ->where('id', '<=', $supir->id)
                ->count();

            if (isset($request->limit)) {
                $supir->page = ceil($supir->position / ($request->limit ?? 10));
            }

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $supir
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function show(supir $supir)
    {
        return response([
            'status' => true,
            'data' => $supir
        ]);
    }
 /**
     * @ClassName 
     */
    public function destroy(Supir $supir, Request $request)
    {
        DB::beginTransaction();
        try {
            $photosupir     = json_decode($supir->photosupir,true);
            $photoktp       = json_decode($supir->photoktp   ,true);
            $photosim       = json_decode($supir->photosim,true);
            $photokk        = json_decode($supir->photokk,true);
            $photoskck      = json_decode($supir->photoskck,true);
            $photodomisili  = json_decode($supir->photodomisili,true);

            if (!empty($photosupir)) {
                foreach($photosupir as $item) {
                    $path = public_path().'/uploads/supir/'.$item;
                    if (File::exists($path)) {
                        File::delete($path);
                    }
                }
            }

            if (!empty($photoktp)) {
                foreach($photoktp as $item) {
                    $path = public_path().'/uploads/ktp/'.$item;
                    if (File::exists($path)) {
                        File::delete($path);
                    }
                }
            }

            if (!empty($photosim)) {
                foreach($photosim as $item) {
                    $path = public_path().'/uploads/sim/'.$item;
                    if (File::exists($path)) {
                        File::delete($path);
                    }
                }
            }

            if (!empty($photokk)) {
                foreach($photokk as $item) {
                    $path = public_path().'/uploads/kk/'.$item;
                    if (File::exists($path)) {
                        File::delete($path);
                    }
                }
            }

            if (!empty($photoskck)) {
                foreach($photoskck as $item) {
                    $path = public_path().'/uploads/skck/'.$item;
                    if (File::exists($path)) {
                        File::delete($path);
                    }
                }
            }

            if (!empty($photodomisili)) {
                foreach($photodomisili as $item) {
                    $path = public_path().'/uploads/domisili/'.$item;
                    if (File::exists($path)) {
                        File::delete($path);
                    }
                }
            }

            Supir::destroy($supir->id);

            $logtrail = new LogTrail();
            $logtrail->namatabel = 'Supir';
            $logtrail->postingdari = 'DELETE Supir';
            $logtrail->idtrans = $supir->id;
            $logtrail->nobuktitrans = $supir->id;
            $logtrail->aksi = 'DELETE';
            $logtrail->datajson = '';

            $logtrail->save();

            

            $del = 1;
            $data = $this->getid($supir->id, $request, $del);
            $supir->position = @$data->row;
            $supir->id = @$data->id;
            if (isset($request->limit)) {
                $supir->page = ceil($supir->position / ($request->limit ?? 10));
            }

            DB::commit();
            // dd($supir);
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $supir
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('supir')->getColumns();

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
            'status' => Parameter::where(['grp'=>'status aktif'])->get(),
            'supir' => DB::table((new Supir())->getTable())->get(),
            'updategambar' => Parameter::where(['grp'=>'status ada update gambar'])->get(),
            'luarkota' => Parameter::where(['grp'=>'status luar kota'])->get(),
            'zonatertentu' => Parameter::where(['grp'=>'status zona tertentu'])->get(),
            'pameran' => Parameter::where(['grp'=>'status pameran'])->get(),
            'blacklist' => Parameter::where(['grp'=>'status blacklist'])->get(),
            'zona' => Zona::all(),
            // 'mobilstoring' => Parameter::where(['grp'=>'status mobil storing'])->get(),
            // 'appeditban' => Parameter::where(['grp'=>'status app edit ban'])->get(),
            // 'lewatvalidasi' => Parameter::where(['grp'=>'status lewat validasi'])->get(),
            // 'mandor' => DB::table('mandor')->get(),
        ];

        return response([
            'data' => $data
        ]);
    }

    public function upload_image(Request $request,$id,$aksi) {
        try {
            if ($aksi == 'EDIT') {
                $imageOld = json_decode($request->g_all);

                $get = DB::table((new Supir())->getTable())->where('id',$id)->first();

                $photosupir     = json_decode(strtolower($get->photosupir),true);
                $photoktp       = json_decode(strtolower($get->photoktp),true);
                $photosim       = json_decode(strtolower($get->photosim),true);
                $photokk        = json_decode(strtolower($get->photokk),true);
                $photoskck      = json_decode(strtolower($get->photoskck),true);
                $photodomisili  = json_decode(strtolower($get->photodomisili),true);

                $supir      = (array)$imageOld->supir;
                $ktp        = (array)$imageOld->ktp;
                $sim        = (array)$imageOld->sim;
                $kk         = (array)$imageOld->kk;
                $skck       = (array)$imageOld->skck;
                $domisili   = (array)$imageOld->domisili;

                if(!empty($supir)) {
                    foreach($supir as $item) {
                        $item = strtolower($item);
                        $ori    = $item;
                        $medium = substr_replace($item,"medium",0,3);
                        $small  = substr_replace($item,"small",0,3);

                        $data['supir'][] = strtoupper($ori);
                        $data['supir'][] = strtoupper($medium);
                        $data['supir'][] = strtoupper($small);
                    }

                    $diff = array_diff($photosupir,$data['supir']);
                    
                    foreach($diff as $val) {
                        $path = public_path().'/uploads/supir/'.$val;
                        if (File::exists($path)) {
                            File::delete($path);
                        }
                    }
                } else {
                    if (!empty($photosupir)) {
                        foreach($photosupir as $item) {
                            $path = public_path().'/uploads/supir/'.$item;
                            if (File::exists($path)) {
                                File::delete($path);
                            }
                        }
                    }
                }
                
                if(!empty($ktp)) {
                    foreach($ktp as $item) {
                        $item = strtolower($item);
                        $ori = $item;
                        $medium = substr_replace($item,"medium",0,3);
                        $small = substr_replace($item,"small",0,3);

                        $data['ktp'][] = strtoupper($ori);
                        $data['ktp'][] = strtoupper($medium);
                        $data['ktp'][] = strtoupper($small);
                    }

                    $diff = array_diff($photoktp,$data['ktp']);
                        
                    foreach($diff as $val) {
                        $path = public_path().'/uploads/ktp/'.$val;
                        if (File::exists($path)) {
                            File::delete($path);
                        }
                    }
                } else {
                    if (!empty($photoktp)) {
                        foreach($photoktp as $item) {
                            $path = public_path().'/uploads/ktp/'.$item;
                            if (File::exists($path)) {
                                File::delete($path);
                            }
                        }
                    }
                }

                if(!empty($sim)) {
                    foreach($sim as $item) {
                        $item = strtolower($item);
                        $ori = $item;
                        $medium = substr_replace($item,"medium",0,3);
                        $small = substr_replace($item,"small",0,3);

                        $data['sim'][] = strtoupper($ori);
                        $data['sim'][] = strtoupper($medium);
                        $data['sim'][] = strtoupper($small);
                    }
                    
                    $diff = array_diff($photosim,$data['sim']);
                        
                    foreach($diff as $val) {
                        $path = public_path().'/uploads/sim/'.$val;
                        if (File::exists($path)) {
                            File::delete($path);
                        }
                    }
                } else {
                    if (!empty($photosim)) {
                        foreach($photosim as $item) {
                            $path = public_path().'/uploads/sim/'.$item;
                            if (File::exists($path)) {
                                File::delete($path);
                            }
                        }
                    }
                }

                if(!empty($kk)) {
                    foreach($kk as $item) {
                        $item = strtolower($item);
                        $ori = $item;
                        $medium = substr_replace($item,"medium",0,3);
                        $small = substr_replace($item,"small",0,3);

                        $data['kk'][] = strtoupper($ori);
                        $data['kk'][] = strtoupper($medium);
                        $data['kk'][] = strtoupper($small);
                    }
                    
                    $diff = array_diff($photokk,$data['kk']);
                        
                    foreach($diff as $val) {
                        $path = public_path().'/uploads/kk/'.$val;
                        if (File::exists($path)) {
                            File::delete($path);
                        }
                    }
                } else {
                    if (!empty($photokk)) {
                        foreach($photokk as $item) {
                            $path = public_path().'/uploads/kk/'.$item;
                            if (File::exists($path)) {
                                File::delete($path);
                            }
                        }
                    }
                }


                if(!empty($skck)) {
                    foreach($skck as $item) {
                        $item = strtolower($item);
                        $ori = $item;
                        $medium = substr_replace($item,"medium",0,3);
                        $small = substr_replace($item,"small",0,3);

                        $data['skck'][] = strtoupper($ori);
                        $data['skck'][] = strtoupper($medium);
                        $data['skck'][] = strtoupper($small);
                    }
                    
                    $diff = array_diff($photoskck,$data['skck']);
                        
                    foreach($diff as $val) {
                        $path = public_path().'/uploads/skck/'.$val;
                        if (File::exists($path)) {
                            File::delete($path);
                        }
                    }
                } else {
                    if (!empty($photoskck)) {
                        foreach($photoskck as $item) {
                            $path = public_path().'/uploads/skck/'.$item;
                            if (File::exists($path)) {
                                File::delete($path);
                            }
                        }
                    }
                }                         


                if(!empty($domisili)) {
                    foreach($domisili as $item) {
                        $item = strtolower($item);
                        $ori = $item;
                        $medium = substr_replace($item,"medium",0,3);
                        $small = substr_replace($item,"small",0,3);

                        $data['domisili'][] = strtoupper($ori);
                        $data['domisili'][] = strtoupper($medium);
                        $data['domisili'][] = strtoupper($small);
                    }
                    
                    $diff = array_diff($photodomisili,$data['domisili']);
                        
                    foreach($diff as $val) {
                        $path = public_path().'/uploads/domisili/'.$val;
                        if (File::exists($path)) {
                            File::delete($path);
                        }
                    }
                } else {
                    if (!empty($photodomisili)) {
                        foreach($photodomisili as $item) {
                            $path = public_path().'/uploads/domisili/'.$item;
                            if (File::exists($path)) {
                                File::delete($path);
                            }
                        }
                    }
                }                       
        }

        // UPLOAD SUPIR
        if ($request->file('g_supir')) {
            foreach($request->file('g_supir') as $image) {
                $basePath = public_path().'/uploads/supir/';
                $uniqueName = time().rand().rand(10,100).'.'.$image->getClientOriginalName();
                $name = "ori-".$uniqueName;
                $image->move($basePath,$name);

                $path = $basePath.$name;
                $data['supir'][] = $name;
                $imageResizes = App::imageResize($basePath,$path,$uniqueName);
                $data['supir'][] = $imageResizes[0];
                $data['supir'][] = $imageResizes[1];
            }
        }

        // UPLOAD KTP
        if ($request->file('g_ktp')) {
            foreach($request->file('g_ktp') as $image) {
                $basePath = public_path().'/uploads/ktp/';
                $uniqueName = time().rand().rand(10,100).'.'.$image->getClientOriginalName();
                $name = "ori-".$uniqueName;
                $image->move($basePath,$name);
                
                $path = $basePath.$name;
                $data['ktp'][] = $name;
                $imageResizes = App::imageResize($basePath,$path,$uniqueName);
                $data['ktp'][] = $imageResizes[0];
                $data['ktp'][] = $imageResizes[1];
            }
        }

        // UPLOAD SIM
        if ($request->file('g_sim')) {
            foreach($request->file('g_sim') as $image) {
                $basePath = public_path().'/uploads/sim/';
                $uniqueName = time().rand().rand(10,100).'.'.$image->getClientOriginalName();
                $name = "ori-".$uniqueName;
                $image->move($basePath,$name);
                
                $path = $basePath.$name;
                $data['sim'][] = $name;
                $imageResizes = App::imageResize($basePath,$path,$uniqueName);
                $data['sim'][] = $imageResizes[0];
                $data['sim'][] = $imageResizes[1];
            }
        }

        // UPLOAD KK
        if ($request->file('g_kk')) {
            foreach($request->file('g_kk') as $image) {
                $basePath = public_path().'/uploads/kk/';
                $uniqueName = time().rand().rand(10,100).'.'.$image->getClientOriginalName();
                $name = "ori-".$uniqueName;
                $image->move($basePath,$name);
                
                $path = $basePath.$name;
                $data['kk'][] = $name;
                $imageResizes = App::imageResize($basePath,$path,$uniqueName);
                $data['kk'][] = $imageResizes[0];
                $data['kk'][] = $imageResizes[1];
            }
        }

        // UPLOAD SKCK
        if ($request->file('g_skck')) {
            foreach($request->file('g_skck') as $image) {
                $basePath = public_path().'/uploads/skck/';
                $uniqueName = time().rand().rand(10,100).'.'.$image->getClientOriginalName();
                $name = "ori-".$uniqueName;
                $image->move($basePath,$name);
                
                $path = $basePath.$name;
                $data['skck'][] = $name;
                $imageResizes = App::imageResize($basePath,$path,$uniqueName);
                $data['skck'][] = $imageResizes[0];
                $data['skck'][] = $imageResizes[1];
            }
        }

        // UPLOAD DOMISILI
        if ($request->file('g_domisili')) {
            foreach($request->file('g_domisili') as $image) {
                $basePath = public_path().'/uploads/domisili/';
                $uniqueName = time().rand().rand(10,100).'.'.$image->getClientOriginalName();
                $name = "ori-".$uniqueName;
                $image->move($basePath,$name);
                
                $path = $basePath.$name;
                $data['domisili'][] = $name;
                $imageResizes = App::imageResize($basePath,$path,$uniqueName);
                $data['domisili'][] = $imageResizes[0];
                $data['domisili'][] = $imageResizes[1];
            }
        }

        $supir = Supir::find($id);
        $supir->photosupir = json_encode($data['supir'] ?? []);
        $supir->photoktp = json_encode($data['ktp'] ?? []);
        $supir->photosim = json_encode($data['sim'] ?? []);
        $supir->photokk = json_encode($data['kk'] ?? []);
        $supir->photoskck = json_encode($data['skck'] ?? []);
        $supir->photodomisili = json_encode($data['domisili'] ?? []);
        $supir->save();

        return [
            'status' => true,
            'message' => 'Berhasil disimpan',
        ];

        } catch (\Throwable $th) {
            dd($th->getMessage());
            return response($th->getMessage());
        }
    }

    public function uploadImage(Request $request,$id) {
        $aksi = 'ENTRY';
        try {
            if (isset($request['contents'])) {
                $aksi = 'EDIT';
                $request['contents'] = json_decode($request['contents']);
                $get = DB::table((new Supir())->getTable())->where('id',$id)->first();

                $photosupir     = json_decode(strtolower($get->photosupir),true);
                $photoktp       = json_decode(strtolower($get->photoktp),true);
                $photosim       = json_decode(strtolower($get->photosim),true);
                $photokk        = json_decode(strtolower($get->photokk),true);
                $photoskck      = json_decode(strtolower($get->photoskck),true);
                $photodomisili  = json_decode(strtolower($get->photodomisili),true);

                $supir      = (array)$request['contents']->supir;
                $ktp        = (array)$request['contents']->ktp;
                $sim        = (array)$request['contents']->sim;
                $kk         = (array)$request['contents']->kk;
                $skck       = (array)$request['contents']->skck;
                $domisili   = (array)$request['contents']->domisili;

                if(!empty($supir)) {
                    foreach($supir as $item) {
                        $item = strtolower($item);
                        $ori    = $item;
                        $medium = substr_replace($item,"medium",0,3);
                        $small  = substr_replace($item,"small",0,3);

                        $data['supir'][] = $ori;
                        $data['supir'][] = $medium;
                        $data['supir'][] = $small;
                    }

                    $diff = array_diff($photosupir,$data['supir']);
                    
                    foreach($diff as $val) {
                        $path = public_path().'/uploads/supir/'.$val;
                        if (File::exists($path)) {
                            File::delete($path);
                        }
                    }
                } else {
                    if (!empty($photosupir)) {
                        foreach($photosupir as $item) {
                            $path = public_path().'/uploads/supir/'.$item;
                            if (File::exists($path)) {
                                File::delete($path);
                            }
                        }
                    }
                }
                
                if(!empty($ktp)) {
                    foreach($ktp as $item) {
                        $item = strtolower($item);
                        $ori = $item;
                        $medium = substr_replace($item,"medium",0,3);
                        $small = substr_replace($item,"small",0,3);

                        $data['ktp'][] = $ori;
                        $data['ktp'][] = $medium;
                        $data['ktp'][] = $small;
                    }

                    $diff = array_diff($photoktp,$data['ktp']);
                        
                    foreach($diff as $val) {
                        $path = public_path().'/uploads/ktp/'.$val;
                        if (File::exists($path)) {
                            File::delete($path);
                        }
                    }
                } else {
                    if (!empty($photoktp)) {
                        foreach($photoktp as $item) {
                            $path = public_path().'/uploads/ktp/'.$item;
                            if (File::exists($path)) {
                                File::delete($path);
                            }
                        }
                    }
                }

                if(!empty($sim)) {
                    foreach($sim as $item) {
                        $item = strtolower($item);
                        $ori = $item;
                        $medium = substr_replace($item,"medium",0,3);
                        $small = substr_replace($item,"small",0,3);

                        $data['sim'][] = $ori;
                        $data['sim'][] = $medium;
                        $data['sim'][] = $small;
                    }
                    
                    $diff = array_diff($photosim,$data['sim']);
                        
                    foreach($diff as $val) {
                        $path = public_path().'/uploads/sim/'.$val;
                        if (File::exists($path)) {
                            File::delete($path);
                        }
                    }
                } else {
                    if (!empty($photosim)) {
                        foreach($photosim as $item) {
                            $path = public_path().'/uploads/sim/'.$item;
                            if (File::exists($path)) {
                                File::delete($path);
                            }
                        }
                    }
                }

                if(!empty($kk)) {
                    foreach($kk as $item) {
                        $item = strtolower($item);
                        $ori = $item;
                        $medium = substr_replace($item,"medium",0,3);
                        $small = substr_replace($item,"small",0,3);

                        $data['kk'][] = $ori;
                        $data['kk'][] = $medium;
                        $data['kk'][] = $small;
                    }
                    
                    $diff = array_diff($photokk,$data['kk']);
                        
                    foreach($diff as $val) {
                        $path = public_path().'/uploads/kk/'.$val;
                        if (File::exists($path)) {
                            File::delete($path);
                        }
                    }
                } else {
                    if (!empty($photokk)) {
                        foreach($photokk as $item) {
                            $path = public_path().'/uploads/kk/'.$item;
                            if (File::exists($path)) {
                                File::delete($path);
                            }
                        }
                    }
                }


                if(!empty($skck)) {
                    foreach($skck as $item) {
                        $item = strtolower($item);
                        $ori = $item;
                        $medium = substr_replace($item,"medium",0,3);
                        $small = substr_replace($item,"small",0,3);

                        $data['skck'][] = $ori;
                        $data['skck'][] = $medium;
                        $data['skck'][] = $small;
                    }
                    
                    $diff = array_diff($photoskck,$data['skck']);
                        
                    foreach($diff as $val) {
                        $path = public_path().'/uploads/skck/'.$val;
                        if (File::exists($path)) {
                            File::delete($path);
                        }
                    }
                } else {
                    if (!empty($photoskck)) {
                        foreach($photoskck as $item) {
                            $path = public_path().'/uploads/skck/'.$item;
                            if (File::exists($path)) {
                                File::delete($path);
                            }
                        }
                    }
                }                         


                if(!empty($domisili)) {
                    foreach($domisili as $item) {
                        $item = strtolower($item);
                        $ori = $item;
                        $medium = substr_replace($item,"medium",0,3);
                        $small = substr_replace($item,"small",0,3);

                        $data['domisili'][] = $ori;
                        $data['domisili'][] = $medium;
                        $data['domisili'][] = $small;
                    }
                    
                    $diff = array_diff($photodomisili,$data['domisili']);
                        
                    foreach($diff as $val) {
                        $path = public_path().'/uploads/domisili/'.$val;
                        if (File::exists($path)) {
                            File::delete($path);
                        }
                    }
                } else {
                    if (!empty($photodomisili)) {
                        foreach($photodomisili as $item) {
                            $path = public_path().'/uploads/domisili/'.$item;
                            if (File::exists($path)) {
                                File::delete($path);
                            }
                        }
                    }
                }                       
        }

        // UPLOAD SUPIR
        if (isset($request['g_supir'])) {
            foreach($request['g_supir'] as $image) {
                $basePath = public_path().'/uploads/supir/';
                $uniqueName = time().rand().rand(10,100).'.'.$image->getClientOriginalName();
                $name = "ori-".$uniqueName;
                $image->move($basePath,$name);

                $path = $basePath.$name;
                $data['supir'][] = $name;
                $imageResizes = App::imageResize($basePath,$path,$uniqueName);
                $data['supir'][] = $imageResizes[0];
                $data['supir'][] = $imageResizes[1];
            }
        }

        // UPLOAD KTP
        if (isset($request['g_ktp'])) {
            foreach($request['g_ktp'] as $image) {
                $basePath = public_path().'/uploads/ktp/';
                $uniqueName = time().rand().rand(10,100).'.'.$image->getClientOriginalName();
                $name = "ori-".$uniqueName;
                $image->move($basePath,$name);
                
                $path = $basePath.$name;
                $data['ktp'][] = $name;
                $imageResizes = App::imageResize($basePath,$path,$uniqueName);
                $data['ktp'][] = $imageResizes[0];
                $data['ktp'][] = $imageResizes[1];
            }
        }

        // UPLOAD SIM
        if (isset($request['g_sim'])) {
            foreach($request['g_sim'] as $image) {
                $basePath = public_path().'/uploads/sim/';
                $uniqueName = time().rand().rand(10,100).'.'.$image->getClientOriginalName();
                $name = "ori-".$uniqueName;
                $image->move($basePath,$name);
                
                $path = $basePath.$name;
                $data['sim'][] = $name;
                $imageResizes = App::imageResize($basePath,$path,$uniqueName);
                $data['sim'][] = $imageResizes[0];
                $data['sim'][] = $imageResizes[1];
            }
        }

        // UPLOAD KK
        if (isset($request['g_kk'])) {
            foreach($request['g_kk'] as $image) {
                $basePath = public_path().'/uploads/kk/';
                $uniqueName = time().rand().rand(10,100).'.'.$image->getClientOriginalName();
                $name = "ori-".$uniqueName;
                $image->move($basePath,$name);
                
                $path = $basePath.$name;
                $data['kk'][] = $name;
                $imageResizes = App::imageResize($basePath,$path,$uniqueName);
                $data['kk'][] = $imageResizes[0];
                $data['kk'][] = $imageResizes[1];
            }
        }

        // UPLOAD SKCK
        if (isset($request['g_skck'])) {
            foreach($request['g_skck'] as $image) {
                $basePath = public_path().'/uploads/skck/';
                $uniqueName = time().rand().rand(10,100).'.'.$image->getClientOriginalName();
                $name = "ori-".$uniqueName;
                $image->move($basePath,$name);
                
                $path = $basePath.$name;
                $data['skck'][] = $name;
                $imageResizes = App::imageResize($basePath,$path,$uniqueName);
                $data['skck'][] = $imageResizes[0];
                $data['skck'][] = $imageResizes[1];
            }
        }

        // UPLOAD DOMISILI
        if (isset($request['g_domisili'])) {
            foreach($request['g_domisili'] as $image) {
                $basePath = public_path().'/uploads/domisili/';
                $uniqueName = time().rand().rand(10,100).'.'.$image->getClientOriginalName();
                $name = "ori-".$uniqueName;
                $image->move($basePath,$name);
                
                $path = $basePath.$name;
                $data['domisili'][] = $name;
                $imageResizes = App::imageResize($basePath,$path,$uniqueName);
                $data['domisili'][] = $imageResizes[0];
                $data['domisili'][] = $imageResizes[1];
            }
        }

        $supir = Supir::find($id);
        $supir->photosupir = json_encode($data['supir'] ?? []);
        $supir->photoktp = json_encode($data['ktp'] ?? []);
        $supir->photosim = json_encode($data['sim'] ?? []);
        $supir->photokk = json_encode($data['kk'] ?? []);
        $supir->photoskck = json_encode($data['skck'] ?? []);
        $supir->photodomisili = json_encode($data['domisili'] ?? []);
        $supir->save();


        $datalogtrail = [
            'namatabel' => 'Supir',
            'postingdari' => $aksi.' Supir',
            'idtrans' => $supir->id,
            'nobuktitrans' => $supir->id,
            'aksi' => $aksi,
            'datajson' => json_encode($supir->getAttributes()),
            'modifiedby' => $supir->modifiedby,
        ];

        $data=new StoreLogTrailRequest($datalogtrail);
        app(LogTrailController::class)->store($data);

        $request->offset = $request->offset ?? 0;
        $request->limit = $request->limit ?? 100;
        $request->search = $request->search ?? [];
        $request->sortIndex = $request->sortIndex ?? 'id';
        $request->sortOrder = $request->sortOrder ?? 'asc';

        $del = 0;
        $data = $this->getid($supir->id, $request, $del);
        $supir->position = $data->row;
        if (isset($request->limit)) {
            $supir->page = ceil($supir->position / $request->limit);
        }

        return [
            'status' => true,
            'message' => 'Berhasil disimpan',
            'data' => $supir
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
            $table->string('namasupir', 100)->default('');
            $table->string('alamat', 100)->default('');
            $table->string('kota', 100)->default('');
            $table->string('telp', 30)->default('');
            $table->string('statusaktif',300)->default('')->nullable();
            $table->double('nominaldepositsa', 15,2)->default(0);
            $table->BigInteger('depositke')->default(0);
            $table->date('tgl')->default('1900/1/1');
            $table->double('nominalpinjamansaldoawal', 15,2)->default(0);
            $table->unsignedBigInteger('supirold_id')->default(0);
            $table->date('tglexpsim')->default('1900/1/1');
            $table->string('nosim', 30)->default('');
            $table->longText('keterangan')->default('');
            $table->string('noktp', 30)->default('');
            $table->string('nokk', 30)->default('');
            $table->integer('statusadaupdategambar')->length(11)->default(0);
            $table->integer('statuslluarkota')->length(11)->default(0);
            $table->integer('statuszonatertentu')->length(11)->default(0);
            $table->unsignedBigInteger('zona_id')->default(0);
            $table->double('angsuranpinjaman', 15,2)->default(0);
            $table->double('plafondeposito', 15,2)->default(0);
            $table->string('photosupir', 4000)->default('');
            $table->string('photoktp', 4000)->default('');
            $table->string('photosim', 4000)->default('');
            $table->string('photokk', 4000)->default('');
            $table->string('photoskck', 4000)->default('');
            $table->string('photodomisili', 4000)->default('');
            $table->longText('keteranganresign')->default('');
            $table->integer('statusblacklist')->length(11)->default(0);
            $table->date('tglberhentisupir')->default('1900/1/1');
            $table->date('tgllahir')->default('1900/1/1');
            $table->date('tglterbitsim')->default('1900/1/1');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });


        if ($request->sortIndex == 'id') {
            $query = DB::table((new Supir())->getTable())->select(
                'supir.id as id_',
                'supir.namasupir',
                'supir.alamat',
                'supir.kota',
                'supir.telp',
                'parameter.text as statusaktif',
                'supir.nominaldepositsa',
                'supir.depositke',
                'supir.tgl',
                'supir.nominalpinjamansaldoawal',
                'supir.supirold_id',
                'supir.tglexpsim',
                'supir.nosim',
                'supir.keterangan',
                'supir.noktp',
                'supir.nokk',
                'supir.statusadaupdategambar',
                'supir.statuslluarkota',
                'supir.statuszonatertentu',
                'supir.zona_id',
                'supir.angsuranpinjaman',
                'supir.plafondeposito',
                'supir.photosupir',
                'supir.photoktp',
                'supir.photosim',
                'supir.photokk',
                'supir.photoskck',
                'supir.photodomisili',
                'supir.keteranganresign',
                'supir.statusblacklist',
                'supir.tglberhentisupir',
                'supir.tgllahir',
                'supir.tglterbitsim',
                'supir.modifiedby',
                'supir.created_at',
                'supir.updated_at'
            )
                ->leftJoin('parameter', 'supir.statusaktif', '=', 'parameter.id')
                ->orderBy('supir.id', $request->sortOrder);
        } else if ($request->sortIndex == 'keterangan') {
            $query = DB::table((new Supir())->getTable())->select(
                'supir.id as id_',
                'supir.namasupir',
                'supir.alamat',
                'supir.kota',
                'supir.telp',
                'parameter.text as statusaktif',
                'supir.nominaldepositsa',
                'supir.depositke',
                'supir.tgl',
                'supir.nominalpinjamansaldoawal',
                'supir.supirold_id',
                'supir.tglexpsim',
                'supir.nosim',
                'supir.keterangan',
                'supir.noktp',
                'supir.nokk',
                'supir.statusadaupdategambar',
                'supir.statuslluarkota',
                'supir.statuszonatertentu',
                'supir.zona_id',
                'supir.angsuranpinjaman',
                'supir.plafondeposito',
                'supir.photosupir',
                'supir.photoktp',
                'supir.photosim',
                'supir.photokk',
                'supir.photoskck',
                'supir.photodomisili',
                'supir.keteranganresign',
                'supir.statusblacklist',
                'supir.tglberhentisupir',
                'supir.tgllahir',
                'supir.tglterbitsim',
                'supir.modifiedby',
                'supir.created_at',
                'supir.updated_at'
            )
                ->leftJoin('parameter', 'supir.statusaktif', '=', 'parameter.id')
                ->orderBy($request->sortIndex, $request->sortOrder)
                // ->orderBy('supir.keterangan', $request->sortOrder)
                ->orderBy('supir.id', $request->sortOrder);
        } else {
            if ($request->sortOrder == 'asc') {
                $query = DB::table((new Supir())->getTable())->select(
                'supir.id as id_',
                'supir.namasupir',
                'supir.alamat',
                'supir.kota',
                'supir.telp',
                'parameter.text as statusaktif',
                'supir.nominaldepositsa',
                'supir.depositke',
                'supir.tgl',
                'supir.nominalpinjamansaldoawal',
                'supir.supirold_id',
                'supir.tglexpsim',
                'supir.nosim',
                'supir.keterangan',
                'supir.noktp',
                'supir.nokk',
                'supir.statusadaupdategambar',
                'supir.statuslluarkota',
                'supir.statuszonatertentu',
                'supir.zona_id',
                'supir.angsuranpinjaman',
                'supir.plafondeposito',
                'supir.photosupir',
                'supir.photoktp',
                'supir.photosim',
                'supir.photokk',
                'supir.photoskck',
                'supir.photodomisili',
                'supir.keteranganresign',
                'supir.statusblacklist',
                'supir.tglberhentisupir',
                'supir.tgllahir',
                'supir.tglterbitsim',
                'supir.modifiedby',
                'supir.created_at',
                'supir.updated_at'
            )
                    ->leftJoin('parameter', 'supir.statusaktif', '=', 'parameter.id')
                    ->orderBy($request->sortIndex, $request->sortOrder)
                    ->orderBy('supir.id', $request->sortOrder);
            } else {
                $query = DB::table((new Supir())->getTable())->select(
                    'supir.id as id_',
                'supir.namasupir',
                'supir.alamat',
                'supir.kota',
                'supir.telp',
                'parameter.text as statusaktif',
                'supir.nominaldepositsa',
                'supir.depositke',
                'supir.tgl',
                'supir.nominalpinjamansaldoawal',
                'supir.supirold_id',
                'supir.tglexpsim',
                'supir.nosim',
                'supir.keterangan',
                'supir.noktp',
                'supir.nokk',
                'supir.statusadaupdategambar',
                'supir.statuslluarkota',
                'supir.statuszonatertentu',
                'supir.zona_id',
                'supir.angsuranpinjaman',
                'supir.plafondeposito',
                'supir.photosupir',
                'supir.photoktp',
                'supir.photosim',
                'supir.photokk',
                'supir.photoskck',
                'supir.photodomisili',
                'supir.keteranganresign',
                'supir.statusblacklist',
                'supir.tglberhentisupir',
                'supir.tgllahir',
                'supir.tglterbitsim',
                'supir.modifiedby',
                'supir.created_at',
                'supir.updated_at'
                )
                    ->leftJoin('parameter', 'supir.statusaktif', '=', 'parameter.id')
                    ->orderBy($request->sortIndex, $request->sortOrder)

                    ->orderBy('supir.id', 'asc');
            }
        }


        DB::table($temp)->insertUsing(['id_','namasupir','alamat','kota','telp','statusaktif','nominaldepositsa','depositke','tgl','nominalpinjamansaldoawal','supirold_id','tglexpsim','nosim','keterangan','noktp','nokk','statusadaupdategambar','statuslluarkota','statuszonatertentu','zona_id','angsuranpinjaman','plafondeposito','photosupir','photoktp','photosim','photokk','photoskck','photodomisili','keteranganresign','statusblacklist','tglberhentisupir','tgllahir','tglterbitsim','modifiedby','created_at','updated_at'], $query);


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
