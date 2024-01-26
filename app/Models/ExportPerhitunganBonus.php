<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExportPerhitunganBonus extends Model
{
    use HasFactory;


    public function getReport() {
        
        return $data = 
        [
            [
                "perkiraan" =>  "Pendapatan - Usaha Jakarta",
                "bulankesatu" => "1450580500",
                "bulankedua" => "1488409000",
                "bulanketiga" => "1615542000",
            ],
            [
                "perkiraan" => "Pendapatan - Lain",
                "bulankesatu" => "16427500.00",
                "bulankedua" => "20385060.00",
                "bulanketiga" => "20745500.00",
            ],
            [
                "perkiraan"=>"Pendapatan - Bunga",
                "bulankesatu"=>"478158.86",
                "bulankedua"=>"213181.58",
                "bulanketiga"=>"179752.96",
            ],
            [
                "perkiraan" =>"Potongan Pendapatan Usaha",
                "bulankesatu" =>"0",
                "bulankedua" =>"-81000.00",
                "bulanketiga" =>"-300000.00"
            ],
            [
                "perkiraan" =>"TOTAL PENDAPATAN",
                "bulankesatu" =>"1467486158.86",
                "bulankedua" =>"1508926241.58",
                "bulanketiga" =>"1636167252.96"
            ]
        ];
            




    }
}
