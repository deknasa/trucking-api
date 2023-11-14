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
        // $tahun = request()->tahun ?? 0;
        // $bulan = request()->bulan ?? 0;


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
            // ->whereRaw("a.tahun = " . $tahun)
            // ->whereRaw("a.bulan = " . $bulan)
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


        $query = db::table("saldoakunpusatdetail")->from(db::raw("saldoakunpusatdetail a with (readuncommitted)"))
            ->select(
                db::raw("a.tahun as tahun"),
            )
            ->whereRaw("MONTH(a.tglbukti) = " . $month)
            ->whereRaw("YEAR(a.tglbukti) = " . $year)
            ->groupBy('a.tahun');

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


        $query = db::table("saldoakunpusatdetail")->from(db::raw("saldoakunpusatdetail a with (readuncommitted)"))
            ->select(
                db::raw("a.bulan as bulan"),
            )
            ->whereRaw("MONTH(a.tglbukti) = " . $month)
            ->whereRaw("YEAR(a.tglbukti) = " . $year)
            ->whereRaw("a.tahun=". $tahun)
            ->groupBy('a.bulan');

        // dd($query->get());


        $data = $query->get();

        return $data;
    }
}
