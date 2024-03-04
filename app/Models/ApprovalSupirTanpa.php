<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApprovalSupirTanpa extends Model
{
    use HasFactory;

    public function firstOrFind($supir_id){
        $supir = Supir::find($supir_id);

        $data = [
            "supir_id"=>$supir->supir_id,
            "namasupir"=>$supir->namasupir,
            "noktp"=>$supir->noktp,
        ];

        $approvalsupirgambar = DB::table('approvalsupirgambar')
        ->select(
            'approvalsupirgambar.id',
            'approvalsupirgambar.namasupir',
            'approvalsupirgambar.noktp',
            'approvalsupirgambar.statusapproval',
            'approvalsupirgambar.tglbatas',
        )
        ->where('noktp',$supir->noktp)->first();
        
        $approvalsupirketerangan = DB::table('approvalsupirketerangan')
        ->select(
            'approvalsupirketerangan.id',
            'approvalsupirketerangan.namasupir',
            'approvalsupirketerangan.noktp',
            'approvalsupirketerangan.statusapproval',
            'approvalsupirketerangan.tglbatas',
        )
        ->where('noktp',$supir->noktp)->first();
        
        // Mendefinisikan nilai default
        $default_date = '1970-01-01';
        
        // Mengambil nilai tglbatas yang bukan default
        $dates = array_filter([
            $approvalsupirgambar->tglbatas ?? $default_date,
            $approvalsupirketerangan->tglbatas ?? $default_date
        ]);
        
        // Mendapatkan nilai terkecil
        // Mengecek apakah nilai_terkecil bukan default, dan mencetaknya jika bukan
        $nilai_terkecil = (min($dates) == $default_date)? max($dates) : min($dates);
        $tglbatas = ($nilai_terkecil == $default_date) ? null : $nilai_terkecil;
        // dd($tglbatas,$default_date); // Output nilai terkecil dalam format tanggal
        
        
        $data["tglbatas"] = $tglbatas;
        $data["gambar_id"] = $approvalsupirgambar->id ?? null;
        $data["gambar_statusapproval"] = $approvalsupirgambar->statusapproval ?? null;
        $data["keterangan_id"] = $approvalsupirketerangan->id ?? null;
        $data["keterangan_statusapproval"] = $approvalsupirketerangan->statusapproval ?? null;
        
        
        return $data;
    }
    
    public function processStore(array $data) {
        
        $request=[
            "namasupir" => $data['namasupir'],
            "noktp" => $data['noktp'],
            "tglbatas" => $data['tglbatas'],
        ];
        $dataGambar = $request;
        $dataGambar["statusapproval"] = $data['gambar_statusapproval'];
        if ($data['gambar_id']) {
            $approvalSupirGambarData = ApprovalSupirGambar::find($data['gambar_id']);
            $approvalSupirGambar = (new ApprovalSupirGambar())->processUpdate($approvalSupirGambarData,$dataGambar);
        }else{
            $approvalSupirGambar = (new ApprovalSupirGambar())->processStore($dataGambar);
        }
        
        $dataKeterangan = $request;
        $dataKeterangan["statusapproval"] = $data['keterangan_statusapproval'];
        if ($data['keterangan_id']) {
            $approvalSupirKeteranganData = ApprovalSupirKeterangan::find($data['keterangan_id']);
            $approvalSupirKeterangan = (new ApprovalSupirKeterangan())->processUpdate($approvalSupirKeteranganData,$dataKeterangan);
        }else{
            $approvalSupirKeterangan = (new ApprovalSupirKeterangan())->processStore($dataKeterangan);
        }

        return [
            "supir_id"=>$data['supir_id'],
            "namasupir"=>$data['namasupir'],
            "noktp"=>$data['noktp'],
            "tglbatas"=>$data['tglbatas'],  
        ];
    }
    public function cekApproval(Supir $supir) {
        
        $gambar = false;
       
        if (
            empty(json_decode($supir->photosupir)[0]) ||
            empty(json_decode($supir->photoktp)[0]) ||
            empty(json_decode($supir->photosim)[0]) ||
            empty(json_decode($supir->photokk)[0]) ||
            empty(json_decode($supir->photoskck)[0]) ||
            empty(json_decode($supir->photodomisili)[0]) ||
            empty(json_decode($supir->photovaksin)[0]) ||
            empty(json_decode($supir->pdfsuratperjanjian)[0])
        ) {
            $gambar = true;
        }else{
            $photosupir =json_decode($supir->photosupir)[0];
            $photoktp =json_decode($supir->photoktp)[0];
            $photosim =json_decode($supir->photosim)[0];
            $photokk =json_decode($supir->photokk)[0];
            $photoskck =json_decode($supir->photoskck)[0];
            $photodomisili =json_decode($supir->photodomisili)[0];
            $photovaksin =json_decode($supir->photovaksin)[0];
            $suratperjanjian =json_decode($supir->pdfsuratperjanjian)[0];
            if (
                !Storage::exists("supir/profil/$photosupir") ||
                !Storage::exists("supir/ktp/$photoktp") ||
                !Storage::exists("supir/sim/$photosim") ||
                !Storage::exists("supir/kk/$photokk") ||
                !Storage::exists("supir/skck/$photoskck") ||
                !Storage::exists("supir/domisili/$photodomisili") ||
                !Storage::exists("supir/vaksin/$photovaksin") ||
                !Storage::exists("supir/suratperjanjian/$suratperjanjian")
            ) {
                $gambar = true;
            }
        }
        $keterangan = false;
        if (
            empty($supir->alamat) ||
            empty($supir->namaalias) ||
            empty($supir->kota) ||
            empty($supir->telp) ||
            empty($supir->statusaktif) ||
            empty($supir->tglmasuk) ||
            empty($supir->tglexpsim) ||
            empty($supir->nosim) ||
            empty($supir->nokk) ||
            empty($supir->tgllahir) ||
            empty($supir->tglterbitsim)
        ) {
            $keterangan = true;
        }

        return ["gambar"=>$gambar, "keterangan"=>$keterangan];
        
    }
}