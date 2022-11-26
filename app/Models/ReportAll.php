<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportAll extends MyModel
{
    use HasFactory;

    public function getReport($tgl, $table)
    {
        
        $month = substr($tgl,0,2);
        $year = substr($tgl,3);
        $query = DB::table($table)
        ->whereRaw("MONTH($table.tglbukti) = $month")
        ->whereRaw("YEAR($table.tglbukti) = $year");

        $getData = $query->get();

        return response([
            'data' => $getData,
            'user' => auth('api')->user()->name
        ]);
    }
}