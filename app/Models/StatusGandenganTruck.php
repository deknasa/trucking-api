<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusGandenganTruck extends Model
{
    use HasFactory;

    public function get()
    {
        $data = [
            0 => [
                'id' => 1,
                'nogandengan' => 'GANDENGAN T-03 PANJANG',
                'container' => '40"',
                'gudang' => 'TITI KUNING',
                'lokasiawal' => 'BELAWAN',
                'orderan' => 'BONGKARAN',
                'sp' => 'FULL',
                'jenis' => 'PANJANG'
            ],
            1 => [
                'id' => 2,
                'nogandengan' => 'GANDENGAN T-07 PANJANG',
                'container' => '',
                'gudang' => 'KANDANG',
                'lokasiawal' => 'BELAWAN RANGKA',
                'orderan' => '',
                'sp' => '',
                'jenis' => 'PANJANG'
            ]
        ];

        return $data;
    }
}
