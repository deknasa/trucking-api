<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanTitipanEmkl  extends MyModel
{
    use HasFactory;

    protected $table = 'laporantitipanemkl';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function getData()
    {
        $data = [
            [
                "judul" => "Transporindo Agugng Sejahtera",
                "judulLaporan" => "Pengembalian Titipan Emkl",
                "jenisorder" => "Muatan",
                "trado" => "BK 1234 ZXC",
                "trado_id" => "1",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "day" => date('D',strtotime('2023-01-15')),
                "tujuan" => 'kemayoran',
                "shipper" => 'fickha sentral',
                "container_id" => '1',
                "container" => '20"',
                "nosp" => '54432',
                "nominal" => '50000',
            ],
            [
                "judul" => "Transporindo Agugng Sejahtera",
                "judulLaporan" => "Pengembalian Titipan Emkl",
                "jenisorder" => "Muatan",
                "trado" => "BK 1234 ZXC",
                "trado_id" => "1",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "day" => date('D',strtotime('2023-01-15')),
                "tujuan" => 'bekasi',
                "shipper" => 'bintang jasa',
                "container_id" => '1',
                "container" => '20"',
                "nosp" => '65347',
                "nominal" => '50000',
            ],
            [
                "judul" => "Transporindo Agugng Sejahtera",
                "judulLaporan" => "Pengembalian Titipan Emkl",
                "jenisorder" => "Muatan",
                "trado" => "BK 1234 ZXC",
                "trado_id" => "1",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "day" => date('D',strtotime('2023-01-15')),
                "tujuan" => 'cikarang',
                "shipper" => 'energi unggul',
                "container_id" => '1',
                "container" => '20"',
                "nosp" => '57337',
                "nominal" => '50000',
            ],
            [
                "judul" => "Transporindo Agugng Sejahtera",
                "judulLaporan" => "Pengembalian Titipan Emkl",
                "jenisorder" => "Muatan",
                "trado" => "BK 1234 ZXC",
                "trado_id" => "1",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "day" => date('D',strtotime('2023-01-15')),
                "tujuan" => 'cikarang',
                "shipper" => 'marketama',
                "container_id" => '1',
                "container" => '20"',
                "nosp" => '78676',
                "nominal" => '50000',
            ],   
            [
                "judul" => "Transporindo Agugng Sejahtera",
                "judulLaporan" => "Pengembalian Titipan Emkl",
                "jenisorder" => "Muatan",
                "trado" => "BK 2234 ZXC",
                "trado_id" => "2",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "day" => date('D',strtotime('2023-01-15')),
                "tujuan" => 'kemayoran',
                "shipper" => 'fickha sentral',
                "container_id" => '1',
                "container" => '20"',
                "nosp" => '54432',
                "nominal" => '50000',
            ],
            [
               "judul" => "Transporindo Agugng Sejahtera",
               "judulLaporan" => "Pengembalian Titipan Emkl",
               "jenisorder" => "Muatan",
               "trado" => "BK 2234 ZXC",
                "trado_id" => "2",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "day" => date('D',strtotime('2023-01-15')),
                "tujuan" => 'bekasi',
                "shipper" => 'bintang jasa',
                "container_id" => '1',
                "container" => '20"',
                "nosp" => '65347',
                "nominal" => '50000',
            ],
            [
               "judul" => "Transporindo Agugng Sejahtera",
               "judulLaporan" => "Pengembalian Titipan Emkl",
               "jenisorder" => "Muatan",
               "trado" => "BK 2234 ZXC",
                "trado_id" => "2",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "day" => date('D',strtotime('2023-01-15')),
                "tujuan" => 'cikarang',
                "shipper" => 'energi unggul',
                "container_id" => '1',
                "container" => '20"',
                "nosp" => '57337',
                "nominal" => '50000',
            ],
            [
               "judul" => "Transporindo Agugng Sejahtera",
               "judulLaporan" => "Pengembalian Titipan Emkl",
               "jenisorder" => "Muatan",
               "trado" => "BK 2234 ZXC",
                "trado_id" => "2",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "day" => date('D',strtotime('2023-01-15')),
                "tujuan" => 'cikarang',
                "shipper" => 'marketama',
                "container_id" => '1',
                "container" => '20"',
                "nosp" => '78676',
                "nominal" => '50000',
            ],   
        ];

        return $data;
    }


}
