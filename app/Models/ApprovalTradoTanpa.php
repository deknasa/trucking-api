<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApprovalTradoTanpa extends Model
{
    use HasFactory;

    public function firstOrFind($trado_id)
    {
        $trado = Trado::find($trado_id);

        $data = [
            "trado_id" => $trado->trado_id,
            "kodetrado" => $trado->kodetrado,
        ];

        $approvaltradogambar = DB::table('approvaltradogambar')
            ->select(
                'approvaltradogambar.id',
                'approvaltradogambar.kodetrado',
                'approvaltradogambar.statusapproval',
                'approvaltradogambar.tglbatas',
            )
            ->where('kodetrado', $trado->kodetrado)->first();

        $approvaltradoketerangan = DB::table('approvaltradoketerangan')
            ->select(
                'approvaltradoketerangan.id',
                'approvaltradoketerangan.kodetrado',
                'approvaltradoketerangan.statusapproval',
                'approvaltradoketerangan.tglbatas',
            )
            ->where('kodetrado', $trado->kodetrado)->first();

        // Mendefinisikan nilai default
        $default_date = '1970-01-01';

        // Mengambil nilai tglbatas yang bukan default
        $dates = array_filter([
            $approvaltradogambar->tglbatas ?? $default_date,
            $approvaltradoketerangan->tglbatas ?? $default_date
        ]);

        // Mendapatkan nilai terkecil
        // Mengecek apakah nilai_terkecil bukan default, dan mencetaknya jika bukan
        $nilai_terkecil = (min($dates) == $default_date) ? max($dates) : min($dates);
        $tglbatas = ($nilai_terkecil == $default_date) ? null : $nilai_terkecil;
        // dd($tglbatas,$default_date); // Output nilai terkecil dalam format tanggal


        $data["tglbatas"] = $tglbatas;
        $data["gambar_id"] = $approvaltradogambar->id ?? null;
        $data["gambar_statusapproval"] = $approvaltradogambar->statusapproval ?? null;
        $data["keterangan_id"] = $approvaltradoketerangan->id ?? null;
        $data["keterangan_statusapproval"] = $approvaltradoketerangan->statusapproval ?? null;


        return $data;
    }

    public function processStore(array $data)
    {

        // dd($data);
        $request = [
            "kodetrado" => $data['kodetrado'],
            "tglbatas" => $data['tglbatas'],
        ];
        $dataGambar = $request;
        $dataGambar["statusapproval"] = $data['gambar_statusapproval'];

        if ($data['gambar_id']) {
            $approvalSupirGambarData = ApprovalTradoGambar::find($data['gambar_id']);
            $approvalSupirGambar = (new ApprovalTradoGambar())->processUpdate($approvalSupirGambarData, $dataGambar);
        } else {
            $approvalSupirGambar = (new ApprovalTradoGambar())->processStore($dataGambar);
        }

        $dataKeterangan = $request;
        $dataKeterangan["statusapproval"] = $data['keterangan_statusapproval'];
        if ($data['keterangan_id']) {
            $approvalSupirKeteranganData = ApprovalTradoKeterangan::find($data['keterangan_id']);
            $approvalSupirKeterangan = (new ApprovalTradoKeterangan())->processUpdate($approvalSupirKeteranganData, $dataKeterangan);
        } else {
            $approvalSupirKeterangan = (new ApprovalTradoKeterangan())->processStore($dataKeterangan);
        }
        $this->tradoApprovalAktif($data,$approvalSupirGambar->statusapproval,$approvalSupirKeterangan->statusapproval);

        // ryan

        $statusgambar = $data['gambar_statusapproval'];
        $statusketerangan = $data['keterangan_statusapproval'];

        $statusAktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'AKTIF')->first();
        $statusNonAktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'NON AKTIF')->first();

        DB::table('trado')->where('kodetrado', $data['kodetrado'])->update([
            'statusaktif' => $statusNonAktif->id,
        ]);

        $statusApp = DB::table('parameter')->where('grp', 'STATUS APPROVAL')->where('subgrp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();

        $trado = DB::table('trado')->from(DB::raw("trado with (readuncommitted)"))
            ->where('kodetrado', $data['kodetrado'])
            ->first();

        $photobpkb = true;
        $photostnk = true;
        $phototrado = true;


        if (!is_null(json_decode($trado->photobpkb))) {
            foreach (json_decode($trado->photobpkb) as $value) {
                if ($value != '') {
                    if (!Storage::exists("trado/bpkb/$value")) {
                        $photobpkb = false;
                        goto selesai1;
                    }
                } else {
                    $photobpkb = false;
                    goto selesai1;
                }
            }
        } else {
            $photobpkb = false;
        }

        selesai1:
        if (!is_null(json_decode($trado->photobpkb))) {
            foreach (json_decode($trado->photostnk) as $value) {
                if ($value != '') {
                    if (!Storage::exists("trado/stnk/$value")) {
                        $photostnk = false;
                        goto selesai2;
                    }
                } else {
                    $photostnk = false;
                    goto selesai2;
                }
            }
        } else {
            $photostnk = false;
        }


        selesai2:
        if (!is_null(json_decode($trado->photobpkb))) {
            foreach (json_decode($trado->phototrado) as $value) {
                if ($value != '') {
                    if (!Storage::exists("trado/trado/$value")) {
                        $phototrado = false;
                        goto selesai3;
                    }
                } else {
                    $phototrado = false;
                    goto selesai3;
                }
            }
        } else {
            $phototrado = false;
        }

        selesai3:

      
        if ($photobpkb == true && $photostnk == true  && $phototrado == true) {
            $statusgambar = $statusApp->id;
        }

        $required = [
            "kodetrado" => $trado->kodetrado,
            "tahun" => $trado->tahun,
            "merek" => $trado->merek,
            "norangka" => $trado->norangka,
            "nomesin" => $trado->nomesin,
            "nama" => $trado->nama,
            "nostnk" => $trado->nostnk,
            "alamatstnk" => $trado->alamatstnk,
            "tglpajakstnk" => $trado->tglpajakstnk,
            "tipe" => $trado->tipe,
            "jenis" => $trado->jenis,
            "isisilinder" => $trado->isisilinder,
            "warna" => $trado->warna,
            "jenisbahanbakar" => $trado->jenisbahanbakar,
            "jumlahsumbu" => $trado->jumlahsumbu,
            "jumlahroda" => $trado->jumlahroda,
            "model" => $trado->model,
            "nobpkb" => $trado->nobpkb,
            "jumlahbanserap" => $trado->jumlahbanserap,
        ];
        $key = array_keys($required, null);
        // dd($key);
        $jumlah = count($key);

        if ($jumlah == 0) {
            $statusketerangan = $statusApp->id;
        }

        // dump($jumlah);
        // dump($statusgambar);
        // dump($statusketerangan);
        // dd($statusApp->id);
        if ($statusgambar == $statusApp->id && $statusketerangan == $statusApp->id) {
            DB::table('trado')->where('kodetrado', $data['kodetrado'])->update([
                'statusaktif' => $statusAktif->id,
            ]);
        }

        return [
            "trado_id" => $data['trado_id'],
            "kodetrado" => $data['kodetrado'],
            "tglbatas" => $data['tglbatas'],
        ];
    }

    public function cekApproval(Trado $trado)
    {

        $gambar = false;

        if (
            empty(json_decode($trado->phototrado)[0]) ||
            empty(json_decode($trado->photobpkb)[0]) ||
            empty(json_decode($trado->photostnk)[0])
        ) {
            $gambar = true;
        } else {
            $phototrado = json_decode($trado->phototrado)[0];
            $photobpkb = json_decode($trado->photobpkb)[0];
            $photostnk = json_decode($trado->photostnk)[0];
            if (
                !Storage::exists("trado/trado/$phototrado") ||
                !Storage::exists("trado/bpkb/$photobpkb") ||
                !Storage::exists("trado/stnk/$photostnk")
            ) {
                $gambar = true;
            }
        }
        $keterangan = false;

        if (
            empty($trado->statusaktif) ||
            empty($trado->tahun) ||
            empty($trado->merek) ||
            empty($trado->norangka) ||
            empty($trado->nomesin) ||
            empty($trado->nama) ||
            empty($trado->nostnk) ||
            empty($trado->alamatstnk) ||
            empty($trado->statusjenisplat) ||
            empty($trado->tglpajakstnk) ||
            empty($trado->tglstnkmati) ||
            empty($trado->tglasuransimati) ||
            empty($trado->tglspeksimati) ||
            empty($trado->tipe) ||
            empty($trado->jenis) ||
            empty($trado->isisilinder) ||
            empty($trado->warna) ||
            empty($trado->jenisbahanbakar) ||
            empty($trado->jumlahsumbu) ||
            empty($trado->jumlahroda) ||
            empty($trado->model) ||
            empty($trado->nobpkb) ||
            empty($trado->jumlahbanserap) ||
            empty($trado->statusgerobak) ||
            empty($trado->statusabsensisupir)
        ) {
            $keterangan = true;
        }

        return ["gambar" => $gambar, "keterangan" => $keterangan];
    }


    public function tradoApprovalAktif($data,$approvalTradoGambar,$approvalTradoKeterangan) {
        $trado = Trado::where('kodetrado',$data['kodetrado'])->first();
        $statusAktif = Parameter::from(DB::Raw("parameter with (readuncommitted)"))->select('id')->where('grp', '=', 'STATUS Aktif')->where('subgrp', '=', 'STATUS Aktif')->where('text', '=', 'aktif')->first();
        $statusApproval = Parameter::from(DB::Raw("parameter with (readuncommitted)"))->select('id')->where('grp', '=', 'STATUS APPROVAL')->where('subgrp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        if ($trado->statusaktif != $statusAktif->text) {
            $gambar = $approvalTradoGambar?? $statusApproval->id;
            $keterangan = $approvalTradoKeterangan?? $statusApproval->id;
            // dd($gambar,
            // $keterangan,($statusApproval->id == $gambar) && ($statusApproval->id == $keterangan));
            if (($statusApproval->id == $gambar) && ($statusApproval->id == $keterangan)) {
                // dd($statusAktif->id);
                $trado->statusaktif = $statusAktif->id;
                $trado->save();
            }
        }
    }
}
