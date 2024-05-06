<?php

namespace App\Models;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanArusDanaPusat extends Model
{
    use HasFactory;

    public function getMingguan()
    {
        $pTahun1 = date('Y', strtotime('-1 years'));
        $pTahun2 = date('Y');
        // dd($pTahun1,$pTahun2);
        $tempBulan = '##tempBulan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempBulan, function ($table) {
            $table->longText('FKode')->nullable();
            $table->string('FTahun', 1000)->nullable();
            $table->unsignedBigInteger('FMingguke')->nullable();
            $table->unsignedBigInteger('FBlnke')->nullable();
            $table->date('Ftgldr')->nullable();
            $table->date('Ftglsd')->nullable();
        });
        $dataToInsert = [];
        while ($pTahun1 <= $pTahun2) {
            $pTahun = $pTahun1;
            $pAwal = 1;
            while ($pAwal <= 12) {
                $pTgl1 = date('Y-m-d', strtotime($pTahun . '-' . $pAwal . '-01'));
                $pTgl3 = date('Y-m-d', strtotime($pTahun . '-' . $pAwal . '-01' . ' +32 days'));
                $tahun = date('Y', strtotime($pTgl3));
                $bulan = date('m', strtotime($pTgl3));
                $pTgl2 = date('Y-m-d', strtotime($tahun . '-' . $bulan . '-01' . ' -1 days'));

                $pMinggu = 1;
                $hit = 0;
                while ($pTgl1 <= $pTgl2) {
                    if ($hit == 0) {
                        $pTglDr = $pTgl1;
                        // var_dump('hit 0');
                    }
                    if (date('N', strtotime($pTgl1)) == 7) {
                        $pTglSd = $pTgl1;
                        $values = 'Minggu Ke ' . trim($pMinggu) . ' Bulan ' . trim($pAwal) . ' Tahun ' . trim($pTahun);
                        // DB::table($tempBulan)->insert([
                        //     'FKode' => $values,
                        //     'FTahun' => $pTahun,
                        //     'FMingguke' => $pMinggu,
                        //     'FBlnke' => $pAwal,
                        //     'Ftgldr' => $pTglDr,
                        //     'Ftglsd' => $pTglSd
                        // ]);
                        $dataToInsert[] = [
                            'FKode' => $values,
                            'FTahun' => $pTahun,
                            'FMingguke' => $pMinggu,
                            'FBlnke' => $pAwal,
                            'Ftgldr' => $pTglDr,
                            'Ftglsd' => $pTglSd
                        ];
                        $pMinggu = $pMinggu + 1;
                        $hit -= 1;
                        // var_dump('n 7');
                    }
                    if ($pTgl1 == $pTgl2) {
                        $pTglSd = $pTgl1;
                        $values = 'Minggu Ke ' . trim($pMinggu) . ' Bulan ' . trim($pAwal) . ' Tahun ' . trim($pTahun);
                        // Insert into the temporary table using Eloquent
                        // DB::table($tempBulan)->insert([
                        //     'FKode' => $values,
                        //     'FTahun' => $pTahun,
                        //     'FMingguke' => $pMinggu,
                        //     'FBlnke' => $pAwal,
                        //     'Ftgldr' => $pTglDr,
                        //     'Ftglsd' => $pTglSd
                        // ]);
                        $dataToInsert[] = [
                            'FKode' => $values,
                            'FTahun' => $pTahun,
                            'FMingguke' => $pMinggu,
                            'FBlnke' => $pAwal,
                            'Ftgldr' => $pTglDr,
                            'Ftglsd' => $pTglSd
                        ];
                        $pMinggu = $pMinggu + 1;
                        $hit -= 1;

                        // var_dump('ptgl2');
                    }

                    $hit = $hit + 1;
                    $pTgl1 = date('Y-m-d', strtotime($pTgl1 . ' +1 days'));
                }

                $pAwal += 1;
            }
            $pTahun1 += 1;
        }
        // dd($data);
        // DB::table($tempBulan)->insert($data);
        DB::table($tempBulan)->insert($dataToInsert);

        dd(DB::table($tempBulan)->get());
    }

    public function getReport($tgldari, $tglsampai, $cabang_id, $minggu)
    {

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $data = [
            [
                'cabang_id' => '2',
                'namacabang' => 'CABANG MEDAN',
                'mingguke' => $minggu . 'MINGGU KE 4 BULAN 4 TAHUN 2024',
                'tanggal' => '2024-04-24',
                'keterangan' => 'MINGGUAN TRUCKING',
                'debet' => 0,
                'kredit' => 90000000,
                'saldo' => '-90000000',
                'judulLaporan' => 'ARUS DANA PUSAT - CABANG MINGGUAN',
                'judul' => $getJudul->text,
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User : ' . auth('api')->user()->name
            ], [
                'cabang_id' => '2',
                'namacabang' => 'CABANG MEDAN',
                'mingguke' => $minggu . 'MINGGU KE 4 BULAN 4 TAHUN 2024',
                'tanggal' => '2024-04-25',
                'keterangan' => 'PENYETORAN ATAS PELUNASAN INV 21, 23, 27, 28',
                'debet' => 183206500,
                'kredit' => 0,
                'saldo' => 93206500,
                'judulLaporan' => 'ARUS DANA PUSAT - CABANG MINGGUAN',
                'judul' => $getJudul->text,
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User : ' . auth('api')->user()->name
            ], [
                'cabang_id' => '2',
                'namacabang' => 'CABANG MEDAN',
                'mingguke' => $minggu . 'MINGGU KE 4 BULAN 4 TAHUN 2024',
                'tanggal' => '2024-04-26',
                'keterangan' => 'MINGGUAN TRUCKING',
                'debet' => 0,
                'kredit' => 90000000,
                'saldo' => 3206500,
                'judulLaporan' => 'ARUS DANA PUSAT - CABANG MINGGUAN',
                'judul' => $getJudul->text,
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User : ' . auth('api')->user()->name
            ], [
                'cabang_id' => '3',
                'namacabang' => 'CABANG JAKARTA',
                'mingguke' => $minggu . 'MINGGU KE 4 BULAN 4 TAHUN 2024',
                'tanggal' => '2024-04-24',
                'keterangan' => 'B. KOMISI ROBERT BLN FEB  2024',
                'debet' => 0,
                'kredit' => 25216264,
                'saldo' => '-25216264',
                'judulLaporan' => 'ARUS DANA PUSAT - CABANG MINGGUAN',
                'judul' => $getJudul->text,
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User : ' . auth('api')->user()->name
            ], [
                'cabang_id' => '3',
                'namacabang' => 'CABANG JAKARTA',
                'mingguke' => $minggu . 'MINGGU KE 4 BULAN 4 TAHUN 2024',
                'tanggal' => '2024-04-24',
                'keterangan' => 'MINGGUAN TRUCKING',
                'debet' => 0,
                'kredit' => 100000000,
                'saldo' => '-125216264',
                'judulLaporan' => 'ARUS DANA PUSAT - CABANG MINGGUAN',
                'judul' => $getJudul->text,
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User : ' . auth('api')->user()->name
            ], [
                'cabang_id' => '3',
                'namacabang' => 'CABANG JAKARTA',
                'mingguke' => $minggu . 'MINGGU KE 4 BULAN 4 TAHUN 2024',
                'tanggal' => '2024-04-26',
                'keterangan' => 'MINGGUAN TRUCKING',
                'debet' => 0,
                'kredit' => 100000000,
                'saldo' => '-225216264',
                'judulLaporan' => 'ARUS DANA PUSAT - CABANG MINGGUAN',
                'judul' => $getJudul->text,
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User : ' . auth('api')->user()->name
            ], [
                'cabang_id' => '4',
                'namacabang' => 'CABANG SURABAYA',
                'mingguke' => $minggu . 'MINGGU KE 4 BULAN 4 TAHUN 2024',
                'tanggal' => '2024-04-24',
                'keterangan' => 'B. KOMISI CITRA BLN FEB  2024',
                'debet' => 0,
                'kredit' => 6427173,
                'saldo' => '-6427173',
                'judulLaporan' => 'ARUS DANA PUSAT - CABANG MINGGUAN',
                'judul' => $getJudul->text,
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User : ' . auth('api')->user()->name
            ], [
                'cabang_id' => '4',
                'namacabang' => 'CABANG SURABAYA',
                'mingguke' => $minggu . 'MINGGU KE 4 BULAN 4 TAHUN 2024',
                'tanggal' => '2024-04-26',
                'keterangan' => 'MINGGUAN TRUCKING',
                'debet' => 0,
                'kredit' => 130000000,
                'saldo' => '-136427173',
                'judulLaporan' => 'ARUS DANA PUSAT - CABANG MINGGUAN',
                'judul' => $getJudul->text,
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User : ' . auth('api')->user()->name
            ]
        ];

        return $data;
    }
}
