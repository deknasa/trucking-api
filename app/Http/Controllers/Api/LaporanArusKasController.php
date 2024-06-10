<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanArusKas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanArusKasController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(Request $request)
    {
        return response([
            'data' => [],
            'attributes' => [
                'totalRows' => 0,
                'totalPages' => 0
            ]
        ]);
    }

    /**
     * @ClassName
     * @Keterangan CETAK DATA
     */
    public function report(Request $request)
    {
        $periode = $request->periode;
        $laporanaruskas = new LaporanArusKas();

        // $report = LaporanKasGantung::getReport($sampai, $jenis);
        $saldoawal =
            [
                'keterangancoa' => 'SALDO AWAL',
                'nominalawal' => 13787825468.08,
                'nominalakhir' => 15727550277.84,
            ];
        $report = [
            [
                'judulLaporan' => 'Laporan Arus Kas / Bank tahun 2024',
                'judul' => 'PT. TRANSPORINDO AGUNG SEJAHTERA',
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User :' . auth('api')->user()->name,
                'jenisarus' => 'ARUS KAS/BANK MASUK',
                'type' => 'PENDAPATAN',
                'keterangancoa' => 'CABANG JAKARTA',
                'nominalawal' => 1690901500,
                'nominalakhir' => 1449963000,
                'periodeawal' => 'Apr 2024',
                'periodeakhir' => 'May 2024'
            ],
            [
                'judulLaporan' => 'Laporan Arus Kas / Bank tahun 2024',
                'judul' => 'PT. TRANSPORINDO AGUNG SEJAHTERA',
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User :' . auth('api')->user()->name,
                'jenisarus' => 'ARUS KAS/BANK MASUK',
                'type' => 'PENDAPATAN',
                'keterangancoa' => 'CABANG MAKASSAR',
                'nominalawal' => 1532200522,
                'nominalakhir' => 1557215705,
                'periodeawal' => 'Apr 2024',
                'periodeakhir' => 'May 2024'
            ],
            [
                'judulLaporan' => 'Laporan Arus Kas / Bank tahun 2024',
                'judul' => 'PT. TRANSPORINDO AGUNG SEJAHTERA',
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User :' . auth('api')->user()->name,
                'jenisarus' => 'ARUS KAS/BANK MASUK',
                'type' => 'PENDAPATAN',
                'keterangancoa' => 'CABANG MANADO',
                'nominalawal' => 179592500,
                'nominalakhir' => 241842500,
                'periodeawal' => 'Apr 2024',
                'periodeakhir' => 'May 2024'
            ],
            [
                'judulLaporan' => 'Laporan Arus Kas / Bank tahun 2024',
                'judul' => 'PT. TRANSPORINDO AGUNG SEJAHTERA',
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User :' . auth('api')->user()->name,
                'jenisarus' => 'ARUS KAS/BANK MASUK',
                'type' => 'PENDAPATAN',
                'keterangancoa' => 'CABANG MEDAN',
                'nominalawal' => 859245750,
                'nominalakhir' => 653224000,
                'periodeawal' => 'Apr 2024',
                'periodeakhir' => 'May 2024'
            ],
            [
                'judulLaporan' => 'Laporan Arus Kas / Bank tahun 2024',
                'judul' => 'PT. TRANSPORINDO AGUNG SEJAHTERA',
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User :' . auth('api')->user()->name,
                'jenisarus' => 'ARUS KAS/BANK MASUK',
                'type' => 'PENDAPATAN',
                'keterangancoa' => 'CABANG SURABAYA',
                'nominalawal' => 1168012800,
                'nominalakhir' => 710078601,
                'periodeawal' => 'Apr 2024',
                'periodeakhir' => 'May 2024'
            ],
            [
                'judulLaporan' => 'Laporan Arus Kas / Bank tahun 2024',
                'judul' => 'PT. TRANSPORINDO AGUNG SEJAHTERA',
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User :' . auth('api')->user()->name,
                'jenisarus' => 'ARUS KAS/BANK KELUAR',
                'type' => 'BIAYA',
                'keterangancoa' => 'B.Asuransi - Karyawan',
                'nominalawal' => '-259662',
                'nominalakhir' => '-259662',
                'periodeawal' => 'Apr 2024',
                'periodeakhir' => 'May 2024'
            ],
            [
                'judulLaporan' => 'Laporan Arus Kas / Bank tahun 2024',
                'judul' => 'PT. TRANSPORINDO AGUNG SEJAHTERA',
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User :' . auth('api')->user()->name,
                'jenisarus' => 'ARUS KAS/BANK KELUAR',
                'type' => 'BIAYA',
                'keterangancoa' => 'B.BANK - ADM',
                'nominalawal' => '-5303244.19',
                'nominalakhir' => '-4700475.23',
                'periodeawal' => 'Apr 2024',
                'periodeakhir' => 'May 2024'
            ],
            [
                'judulLaporan' => 'Laporan Arus Kas / Bank tahun 2024',
                'judul' => 'PT. TRANSPORINDO AGUNG SEJAHTERA',
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User :' . auth('api')->user()->name,
                'jenisarus' => 'ARUS KAS/BANK KELUAR',
                'type' => 'BIAYA',
                'keterangancoa' => 'B.DIREKSI',
                'nominalawal' => '-3888300',
                'nominalakhir' => '-3368200',
                'periodeawal' => 'Apr 2024',
                'periodeakhir' => 'May 2024'
            ],
            [
                'judulLaporan' => 'Laporan Arus Kas / Bank tahun 2024',
                'judul' => 'PT. TRANSPORINDO AGUNG SEJAHTERA',
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User :' . auth('api')->user()->name,
                'jenisarus' => 'ARUS KAS/BANK KELUAR',
                'type' => 'BIAYA',
                'keterangancoa' => 'B.GAJI DIREKSI',
                'nominalawal' => '-120000000',
                'nominalakhir' => '-120000000',
                'periodeawal' => 'Apr 2024',
                'periodeakhir' => 'May 2024'
            ],
            [
                'judulLaporan' => 'Laporan Arus Kas / Bank tahun 2024',
                'judul' => 'PT. TRANSPORINDO AGUNG SEJAHTERA',
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User :' . auth('api')->user()->name,
                'jenisarus' => 'ARUS KAS/BANK KELUAR',
                'type' => 'BIAYA',
                'keterangancoa' => 'B.GENSET',
                'nominalawal' => '-12000',
                'nominalakhir' => 0,
                'periodeawal' => 'Apr 2024',
                'periodeakhir' => 'May 2024'
            ],
            [
                'judulLaporan' => 'Laporan Arus Kas / Bank tahun 2024',
                'judul' => 'PT. TRANSPORINDO AGUNG SEJAHTERA',
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User :' . auth('api')->user()->name,
                'jenisarus' => 'ARUS KAS/BANK KELUAR',
                'type' => 'BIAYA',
                'keterangancoa' => 'B.KANTOR - ADMINISTRASI',
                'nominalawal' => '-5510204',
                'nominalakhir' => 0,
                'periodeawal' => 'Apr 2024',
                'periodeakhir' => 'May 2024'
            ],
        ];
        return response([
            // 'data' => $laporankasgantung->getReport($periode)
            'data' => $report,
            'saldo' => $saldoawal,
        ]);
    }
    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $periode = $request->periode;
        $laporanaruskas = new LaporanArusKas();

        // $report = LaporanKasGantung::getReport($sampai, $jenis); 
        $saldoawal =
            [
                'keterangancoa' => 'SALDO AWAL',
                'nominalawal' => 13787825468.08,
                'nominalakhir' => 15727550277.84,
            ];
        $report = [
            [
                'judulLaporan' => 'Laporan Arus Kas / Bank tahun 2024',
                'judul' => 'PT. TRANSPORINDO AGUNG SEJAHTERA',
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User :' . auth('api')->user()->name,
                'jenisarus' => 'ARUS KAS/BANK MASUK',
                'type' => 'PENDAPATAN',
                'keterangancoa' => 'CABANG JAKARTA',
                'nominalawal' => 1690901500,
                'nominalakhir' => 1449963000,
                'periodeawal' => 'Apr 2024',
                'periodeakhir' => 'May 2024'
            ],
            [
                'judulLaporan' => 'Laporan Arus Kas / Bank tahun 2024',
                'judul' => 'PT. TRANSPORINDO AGUNG SEJAHTERA',
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User :' . auth('api')->user()->name,
                'jenisarus' => 'ARUS KAS/BANK MASUK',
                'type' => 'PENDAPATAN',
                'keterangancoa' => 'CABANG MAKASSAR',
                'nominalawal' => 1532200522,
                'nominalakhir' => 1557215705,
                'periodeawal' => 'Apr 2024',
                'periodeakhir' => 'May 2024'
            ],
            [
                'judulLaporan' => 'Laporan Arus Kas / Bank tahun 2024',
                'judul' => 'PT. TRANSPORINDO AGUNG SEJAHTERA',
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User :' . auth('api')->user()->name,
                'jenisarus' => 'ARUS KAS/BANK MASUK',
                'type' => 'PENDAPATAN',
                'keterangancoa' => 'CABANG MANADO',
                'nominalawal' => 179592500,
                'nominalakhir' => 241842500,
                'periodeawal' => 'Apr 2024',
                'periodeakhir' => 'May 2024'
            ],
            [
                'judulLaporan' => 'Laporan Arus Kas / Bank tahun 2024',
                'judul' => 'PT. TRANSPORINDO AGUNG SEJAHTERA',
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User :' . auth('api')->user()->name,
                'jenisarus' => 'ARUS KAS/BANK MASUK',
                'type' => 'PENDAPATAN',
                'keterangancoa' => 'CABANG MEDAN',
                'nominalawal' => 859245750,
                'nominalakhir' => 653224000,
                'periodeawal' => 'Apr 2024',
                'periodeakhir' => 'May 2024'
            ],
            [
                'judulLaporan' => 'Laporan Arus Kas / Bank tahun 2024',
                'judul' => 'PT. TRANSPORINDO AGUNG SEJAHTERA',
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User :' . auth('api')->user()->name,
                'jenisarus' => 'ARUS KAS/BANK MASUK',
                'type' => 'PENDAPATAN',
                'keterangancoa' => 'CABANG SURABAYA',
                'nominalawal' => 1168012800,
                'nominalakhir' => 710078601,
                'periodeawal' => 'Apr 2024',
                'periodeakhir' => 'May 2024'
            ],
            [
                'judulLaporan' => 'Laporan Arus Kas / Bank tahun 2024',
                'judul' => 'PT. TRANSPORINDO AGUNG SEJAHTERA',
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User :' . auth('api')->user()->name,
                'jenisarus' => 'ARUS KAS/BANK KELUAR',
                'type' => 'BIAYA',
                'keterangancoa' => 'B.Asuransi - Karyawan',
                'nominalawal' => '-259662',
                'nominalakhir' => '-259662',
                'periodeawal' => 'Apr 2024',
                'periodeakhir' => 'May 2024'
            ],
            [
                'judulLaporan' => 'Laporan Arus Kas / Bank tahun 2024',
                'judul' => 'PT. TRANSPORINDO AGUNG SEJAHTERA',
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User :' . auth('api')->user()->name,
                'jenisarus' => 'ARUS KAS/BANK KELUAR',
                'type' => 'BIAYA',
                'keterangancoa' => 'B.BANK - ADM',
                'nominalawal' => '-5303244.19',
                'nominalakhir' => '-4700475.23',
                'periodeawal' => 'Apr 2024',
                'periodeakhir' => 'May 2024'
            ],
            [
                'judulLaporan' => 'Laporan Arus Kas / Bank tahun 2024',
                'judul' => 'PT. TRANSPORINDO AGUNG SEJAHTERA',
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User :' . auth('api')->user()->name,
                'jenisarus' => 'ARUS KAS/BANK KELUAR',
                'type' => 'BIAYA',
                'keterangancoa' => 'B.DIREKSI',
                'nominalawal' => '-3888300',
                'nominalakhir' => '-3368200',
                'periodeawal' => 'Apr 2024',
                'periodeakhir' => 'May 2024'
            ],
            [
                'judulLaporan' => 'Laporan Arus Kas / Bank tahun 2024',
                'judul' => 'PT. TRANSPORINDO AGUNG SEJAHTERA',
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User :' . auth('api')->user()->name,
                'jenisarus' => 'ARUS KAS/BANK KELUAR',
                'type' => 'BIAYA',
                'keterangancoa' => 'B.GAJI DIREKSI',
                'nominalawal' => '-120000000',
                'nominalakhir' => '-120000000',
                'periodeawal' => 'Apr 2024',
                'periodeakhir' => 'May 2024'
            ],
            [
                'judulLaporan' => 'Laporan Arus Kas / Bank tahun 2024',
                'judul' => 'PT. TRANSPORINDO AGUNG SEJAHTERA',
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User :' . auth('api')->user()->name,
                'jenisarus' => 'ARUS KAS/BANK KELUAR',
                'type' => 'BIAYA',
                'keterangancoa' => 'B.GENSET',
                'nominalawal' => '-12000',
                'nominalakhir' => 0,
                'periodeawal' => 'Apr 2024',
                'periodeakhir' => 'May 2024'
            ],
            [
                'judulLaporan' => 'Laporan Arus Kas / Bank tahun 2024',
                'judul' => 'PT. TRANSPORINDO AGUNG SEJAHTERA',
                'tglcetak' => 'Tgl Cetak: ' . date('d-m-Y H:i:s'),
                'usercetak' => 'User :' . auth('api')->user()->name,
                'jenisarus' => 'ARUS KAS/BANK KELUAR',
                'type' => 'BIAYA',
                'keterangancoa' => 'B.KANTOR - ADMINISTRASI',
                'nominalawal' => '-5510204',
                'nominalakhir' => 0,
                'periodeawal' => 'Apr 2024',
                'periodeakhir' => 'May 2024'
            ],
        ];
        return response([
            // 'data' => $laporankasgantung->getReport($periode)
            'data' => $report,
            'saldo' => $saldoawal,
        ]);
    }
}
