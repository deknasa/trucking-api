<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanRekapTitipanEmkl  extends MyModel
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
                "nobukti" => "BTT 013/022220/2000",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
                "nominal" => '50000',
                "saldo" => '50000',
            ],
            [
                "judul" => "Transporindo Agugng Sejahtera",
                "judulLaporan" => "Pengembalian Titipan Emkl",
                "nobukti" => "BTT 013/022220/2000",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
                "nominal" => '50000',
                "saldo" => '50000',
            ],
            [
                "judul" => "Transporindo Agugng Sejahtera",
                "judulLaporan" => "Pengembalian Titipan Emkl",
                "nobukti" => "BTT 013/022220/2000",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
                "nominal" => '50000',
                "saldo" => '50000',
            ],
            [
                "judul" => "Transporindo Agugng Sejahtera",
                "judulLaporan" => "Pengembalian Titipan Emkl",
                "nobukti" => "BTT 013/022220/2000",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
                "nominal" => '50000',
                "saldo" => '50000',
            ],
            [
                "judul" => "Transporindo Agugng Sejahtera",
                "judulLaporan" => "Pengembalian Titipan Emkl",
                "nobukti" => "BTT 013/022220/2000",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
                "nominal" => '50000',
                "saldo" => '50000',
            ],
            [
                "judul" => "Transporindo Agugng Sejahtera",
                "judulLaporan" => "Pengembalian Titipan Emkl",
                "nobukti" => "BTT 013/022220/2000",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
                "nominal" => '50000',
                "saldo" => '50000',
            ],
            [
                "judul" => "Transporindo Agugng Sejahtera",
                "judulLaporan" => "Pengembalian Titipan Emkl",
                "nobukti" => "BTT 013/022220/2000",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
                "nominal" => '50000',
                "saldo" => '50000',
            ],
            [
                "judul" => "Transporindo Agugng Sejahtera",
                "judulLaporan" => "Pengembalian Titipan Emkl",
                "nobukti" => "BTT 013/022220/2000",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
                "nominal" => '50000',
                "saldo" => '50000',
            ],
            [
                "judul" => "Transporindo Agugng Sejahtera",
                "judulLaporan" => "Pengembalian Titipan Emkl",
                "nobukti" => "BTT 013/022220/2000",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
                "nominal" => '50000',
                "saldo" => '50000',
            ],
            [
                "judul" => "Transporindo Agugng Sejahtera",
                "judulLaporan" => "Pengembalian Titipan Emkl",
                "nobukti" => "BTT 013/022220/2000",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
                "nominal" => '50000',
                "saldo" => '50000',
            ],
            [
                "judul" => "Transporindo Agugng Sejahtera",
                "judulLaporan" => "Pengembalian Titipan Emkl",
                "nobukti" => "BTT 013/022220/2000",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
                "nominal" => '50000',
                "saldo" => '50000',
            ],
            [
                "judul" => "Transporindo Agugng Sejahtera",
                "judulLaporan" => "Pengembalian Titipan Emkl",
                "nobukti" => "BTT 013/022220/2000",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
                "nominal" => '50000',
                "saldo" => '50000',
            ],
            [
                "judul" => "Transporindo Agugng Sejahtera",
                "judulLaporan" => "Pengembalian Titipan Emkl",
                "nobukti" => "BTT 013/022220/2000",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
                "nominal" => '50000',
                "saldo" => '50000',
            ],
            [
                "judul" => "Transporindo Agugng Sejahtera",
                "judulLaporan" => "Pengembalian Titipan Emkl",
                "nobukti" => "BTT 013/022220/2000",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
                "nominal" => '50000',
                "saldo" => '50000',
            ],
            [
                "judul" => "Transporindo Agugng Sejahtera",
                "judulLaporan" => "Pengembalian Titipan Emkl",
                "nobukti" => "BTT 013/022220/2000",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
                "nominal" => '50000',
                "saldo" => '50000',
            ],
            [
                "judul" => "Transporindo Agugng Sejahtera",
                "judulLaporan" => "Pengembalian Titipan Emkl",
                "nobukti" => "BTT 013/022220/2000",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
                "nominal" => '50000',
                "saldo" => '50000',
            ],
            [
                "judul" => "Transporindo Agugng Sejahtera",
                "judulLaporan" => "Pengembalian Titipan Emkl",
                "nobukti" => "BTT 013/022220/2000",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
                "nominal" => '50000',
                "saldo" => '50000',
            ],
            [
                "judul" => "Transporindo Agugng Sejahtera",
                "judulLaporan" => "Pengembalian Titipan Emkl",
                "nobukti" => "BTT 013/022220/2000",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
                "nominal" => '50000',
                "saldo" => '50000',
            ],
            [
                "judul" => "Transporindo Agugng Sejahtera",
                "judulLaporan" => "Pengembalian Titipan Emkl",
                "nobukti" => "BTT 013/022220/2000",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
                "nominal" => '50000',
                "saldo" => '50000',
            ],
            [
                "judul" => "Transporindo Agugng Sejahtera",
                "judulLaporan" => "Pengembalian Titipan Emkl",
                "nobukti" => "BTT 013/022220/2000",
                "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
                "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
                "nominal" => '50000',
                "saldo" => '50000',
            ],
           
        ];

        return $data;
    }


}
