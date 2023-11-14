<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class SaldoAwalBukuBesar extends MyModel
{
    use HasFactory;

    protected $table = 'saldoawalbukubesar';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function getimportdatacabang()
    {
        // dd(request()->periode);
        $this->setRequestParameters();
        $periode = request()->periode ?? date('m-Y');
        $month = substr($periode, 0, 2);
        $year = substr($periode, 3);
        $tahun = request()->tahun ?? 0;
        $bulan = request()->bulan ?? 0;



        $query = db::table("saldoawalbukubesar")->from(db::raw("saldoawalbukubesar a with (readuncommitted)"))
            ->select(
                'a.id',
                'a.bulan',
                'a.coa',
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
            ->whereRaw("cast(right(a.bulan,4) as integer) = " . $tahun)
            ->whereRaw("cast(left(a.bulan,2) as integer) = " . $bulan)            
            ->orderby('a.id', 'asc');

        // dd($query->get());


        $data = $query->get();

        return $data;
    }
    public function getimportdatacabangtahun()
    {
        $this->setRequestParameters();
        $periode = request()->periode ?? date('m-Y');
        $month = substr($periode, 0, 2);
        $year = substr($periode, 3);


        $query = db::table("saldoawalbukubesar")->from(db::raw("saldoawalbukubesar a with (readuncommitted)"))
            ->select(
                db::raw("cast(right(a.bulan,4) as integer) as tahun"),
            )
            ->whereRaw("MONTH(a.tglbukti) = " . $month)
            ->whereRaw("YEAR(a.tglbukti) = " . $year)
            ->groupBy(db::raw("cast(right(a.bulan,4) as integer)"));

        // dd($query->get());


        $data = $query->get();

        return $data;
    }

    public function getimportdatacabangbulan()
    {
        $this->setRequestParameters();
        $periode = request()->periode ?? date('m-Y');
        $tahun = request()->tahun ?? 0;
        $month = substr($periode, 0, 2);
        $year = substr($periode, 3);


        $query = db::table("saldoawalbukubesar")->from(db::raw("saldoawalbukubesar a with (readuncommitted)"))
            ->select(
                db::raw("cast(left(a.bulan,2) as integer) as bulan"),
            )
            ->whereRaw("MONTH(a.tglbukti) = " . $month)
            ->whereRaw("YEAR(a.tglbukti) = " . $year)
            ->whereRaw("cast(right(a.bulan,4) as integer)=". $tahun)
            ->groupBy(db::raw("cast(left(a.bulan,2) as integer)"));

        // dd($query->get());


        $data = $query->get();

        return $data;
    }
}
