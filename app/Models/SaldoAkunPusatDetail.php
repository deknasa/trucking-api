<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SaldoAkunPusatDetail extends MyModel
{
    use HasFactory;

    protected $table = 'saldoakunpusatdetail';

    public function getimportdatacabang()
    {
        // dd(request()->periode);
        $this->setRequestParameters();
        $periode = request()->periode ?? date('m-Y');
        $month = substr($periode, 0, 2);
        $year = substr($periode, 3);


        $query = db::table("saldoakunpusatdetail")->from(db::raw("saldoakunpusatdetail a with (readuncommitted)"))
            ->select(
                'a.id',
                'a.coa',
                'a.bulan',
                'a.tahun',
                'a.nominal',
                'a.info',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
                'a.cabang_id',
                'a.tglbukti',
            )
            ->whereRaw("MONTH(a.tglbukti) = " . $month)
            ->whereRaw("YEAR(a.tglbukti) = " . $year)
            ->orderby('a.id', 'asc');

        // dd($query->get());


        $data = $query->get();

        return $data;
    }
}
