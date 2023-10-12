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

    public function getData($tanggal, $prosesneraca)
    {

        $prosesneraca = $prosesneraca ?? 0;

        $penerimaantrucking_id = 5;
        $pengeluarantrucking_id = 9;

        $tempprosestitipan = '##tempprosestitipan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($tempprosestitipan, function ($table) {
            $table->string('pengeluarantruckingheader_nobukti', 1000)->nullable();
        });

        $queryprosestitipan = DB::table('penerimaantruckingdetail')->from(
            DB::raw("penerimaantruckingdetail a with (readuncommitted) ")
        )
            ->select(
                'a.pengeluarantruckingheader_nobukti',
            )
            ->join(DB::raw("penerimaantruckingheader as b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
            ->whereRaw("b.tglbukti<='" . $tanggal . "'")
            ->where('b.penerimaantrucking_id', '=', $penerimaantrucking_id);

        DB::table($tempprosestitipan)->insertUsing([
            'pengeluarantruckingheader_nobukti',
        ], $queryprosestitipan);

        $tempbiayatitipan = '##tempbiayatitipan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($tempbiayatitipan, function ($table) {
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();
            $table->integer('jenisorder_id')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });



        $querybiayatitipan = DB::table('pengeluarantruckingdetail')->from(
            DB::raw("pengeluarantruckingdetail a with (readuncommitted) ")
        )
            ->select(
                'b.nobukti',
                db::raw("max(b.tglbukti) as tglbukti"),
                db::raw("max((case when isnull(b.keterangan,'')='' then isnull(a.keterangan,'') else  isnull(b.keterangan,'') end)) as keterangan"),
                db::raw("max(b.jenisorder_id) as jenisorder_id"),
                db::raw("sum(a.nominal) as nominal"),
            )
            ->join(DB::raw("pengeluarantruckingheader as b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
            ->leftjoin(DB::raw($tempprosestitipan . " as c with (readuncommitted) "), 'a.nobukti', 'c.pengeluarantruckingheader_nobukti')
            ->whereRaw("isnull(c.pengeluarantruckingheader_nobukti,'')=''")
            ->whereRaw("b.tglbukti<='" . $tanggal . "'")
            ->groupBy('b.nobukti');

        DB::table($tempbiayatitipan)->insertUsing([
            'nobukti',
            'tglbukti',
            'keterangan',
            'jenisorder_id',
            'nominal',
        ], $querybiayatitipan);

        
        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($tempbiayatitipan)->from(
            DB::raw($tempbiayatitipan . " a with (readuncommitted) ")
        )
            ->select(
                DB::raw("'" . $getJudul->text . "' as judul"),
                db::raw("'Biaya Titipan Emkl Yang Belum Lunas' as judullaporan"),
                db::raw("ROW_NUMBER() OVER(ORDER BY a.tglbukti,a.nobukti) as urut"),
                'a.nobukti',
                'a.tglbukti',
                'a.keterangan',
                'a.nominal',
                'b.keterangan as jenisorder'
            )
            ->join(DB::raw("jenisorder as b with (readuncommitted) "), 'a.jenisorder_id', 'b.id')
            ->orderBy('a.tglbukti', 'asc')
            ->orderBy('a.nobukti', 'asc');




        if ($prosesneraca == 1) {
            $data = $query;
        } else {
            $data = $query->get();
        }

        // $data = [
        //     [
        //         "judul" => "Transporindo Agugng Sejahtera",
        //         "judulLaporan" => "Pengembalian Titipan Emkl",
        //         "nobukti" => "BTT 013/022220/2000",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
        //         "nominal" => '50000',
        //         "saldo" => '50000',
        //     ],
        //     [
        //         "judul" => "Transporindo Agugng Sejahtera",
        //         "judulLaporan" => "Pengembalian Titipan Emkl",
        //         "nobukti" => "BTT 013/022220/2000",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
        //         "nominal" => '50000',
        //         "saldo" => '50000',
        //     ],
        //     [
        //         "judul" => "Transporindo Agugng Sejahtera",
        //         "judulLaporan" => "Pengembalian Titipan Emkl",
        //         "nobukti" => "BTT 013/022220/2000",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
        //         "nominal" => '50000',
        //         "saldo" => '50000',
        //     ],
        //     [
        //         "judul" => "Transporindo Agugng Sejahtera",
        //         "judulLaporan" => "Pengembalian Titipan Emkl",
        //         "nobukti" => "BTT 013/022220/2000",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
        //         "nominal" => '50000',
        //         "saldo" => '50000',
        //     ],
        //     [
        //         "judul" => "Transporindo Agugng Sejahtera",
        //         "judulLaporan" => "Pengembalian Titipan Emkl",
        //         "nobukti" => "BTT 013/022220/2000",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
        //         "nominal" => '50000',
        //         "saldo" => '50000',
        //     ],
        //     [
        //         "judul" => "Transporindo Agugng Sejahtera",
        //         "judulLaporan" => "Pengembalian Titipan Emkl",
        //         "nobukti" => "BTT 013/022220/2000",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
        //         "nominal" => '50000',
        //         "saldo" => '50000',
        //     ],
        //     [
        //         "judul" => "Transporindo Agugng Sejahtera",
        //         "judulLaporan" => "Pengembalian Titipan Emkl",
        //         "nobukti" => "BTT 013/022220/2000",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
        //         "nominal" => '50000',
        //         "saldo" => '50000',
        //     ],
        //     [
        //         "judul" => "Transporindo Agugng Sejahtera",
        //         "judulLaporan" => "Pengembalian Titipan Emkl",
        //         "nobukti" => "BTT 013/022220/2000",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
        //         "nominal" => '50000',
        //         "saldo" => '50000',
        //     ],
        //     [
        //         "judul" => "Transporindo Agugng Sejahtera",
        //         "judulLaporan" => "Pengembalian Titipan Emkl",
        //         "nobukti" => "BTT 013/022220/2000",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
        //         "nominal" => '50000',
        //         "saldo" => '50000',
        //     ],
        //     [
        //         "judul" => "Transporindo Agugng Sejahtera",
        //         "judulLaporan" => "Pengembalian Titipan Emkl",
        //         "nobukti" => "BTT 013/022220/2000",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
        //         "nominal" => '50000',
        //         "saldo" => '50000',
        //     ],
        //     [
        //         "judul" => "Transporindo Agugng Sejahtera",
        //         "judulLaporan" => "Pengembalian Titipan Emkl",
        //         "nobukti" => "BTT 013/022220/2000",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
        //         "nominal" => '50000',
        //         "saldo" => '50000',
        //     ],
        //     [
        //         "judul" => "Transporindo Agugng Sejahtera",
        //         "judulLaporan" => "Pengembalian Titipan Emkl",
        //         "nobukti" => "BTT 013/022220/2000",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
        //         "nominal" => '50000',
        //         "saldo" => '50000',
        //     ],
        //     [
        //         "judul" => "Transporindo Agugng Sejahtera",
        //         "judulLaporan" => "Pengembalian Titipan Emkl",
        //         "nobukti" => "BTT 013/022220/2000",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
        //         "nominal" => '50000',
        //         "saldo" => '50000',
        //     ],
        //     [
        //         "judul" => "Transporindo Agugng Sejahtera",
        //         "judulLaporan" => "Pengembalian Titipan Emkl",
        //         "nobukti" => "BTT 013/022220/2000",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
        //         "nominal" => '50000',
        //         "saldo" => '50000',
        //     ],
        //     [
        //         "judul" => "Transporindo Agugng Sejahtera",
        //         "judulLaporan" => "Pengembalian Titipan Emkl",
        //         "nobukti" => "BTT 013/022220/2000",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
        //         "nominal" => '50000',
        //         "saldo" => '50000',
        //     ],
        //     [
        //         "judul" => "Transporindo Agugng Sejahtera",
        //         "judulLaporan" => "Pengembalian Titipan Emkl",
        //         "nobukti" => "BTT 013/022220/2000",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
        //         "nominal" => '50000',
        //         "saldo" => '50000',
        //     ],
        //     [
        //         "judul" => "Transporindo Agugng Sejahtera",
        //         "judulLaporan" => "Pengembalian Titipan Emkl",
        //         "nobukti" => "BTT 013/022220/2000",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
        //         "nominal" => '50000',
        //         "saldo" => '50000',
        //     ],
        //     [
        //         "judul" => "Transporindo Agugng Sejahtera",
        //         "judulLaporan" => "Pengembalian Titipan Emkl",
        //         "nobukti" => "BTT 013/022220/2000",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
        //         "nominal" => '50000',
        //         "saldo" => '50000',
        //     ],
        //     [
        //         "judul" => "Transporindo Agugng Sejahtera",
        //         "judulLaporan" => "Pengembalian Titipan Emkl",
        //         "nobukti" => "BTT 013/022220/2000",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
        //         "nominal" => '50000',
        //         "saldo" => '50000',
        //     ],
        //     [
        //         "judul" => "Transporindo Agugng Sejahtera",
        //         "judulLaporan" => "Pengembalian Titipan Emkl",
        //         "nobukti" => "BTT 013/022220/2000",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "keterangan" => 'loremipsusdasdasdasd PS C:\xampp\htdocs\trucking-laravel> php artisan make:model LaporanTitipanEmkl',
        //         "nominal" => '50000',
        //         "saldo" => '50000',
        //     ],

        // ];

        return $data;
    }
}
