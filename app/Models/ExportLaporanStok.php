<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExportLaporanStok extends MyModel
{
    use HasFactory;

    protected $table = '';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];



    public function getExport($periode)
    {
        $pengeluaranStok = PengeluaranStok::where('kodepengeluaran', 'SPK')->first();
        // data coba coba
        
        $month = substr($periode, 0, 2);
        $year = substr($periode, 3);
        $query = PengeluaranStokHeader::from(
            DB::raw("pengeluaranstokheader with (readuncommitted)")
        )->select(
            'pengeluaranstokheader.id',
            'pengeluaranstokheader.nobukti',
            'pengeluaranstokheader.tglbukti',
            'trado.keterangan as nobk',
            'stok.namastok',
            'pengeluaranstokdetail.qty',
            'pengeluaranstokdetail.qty as satuan',
            'pengeluaranstokdetail.harga',
            'pengeluaranstokdetail.total as nominal',
            'pengeluaranstokdetail.total as saldo',
            'pengeluaranstokdetail.keterangan',
        )
        ->leftJoin(DB::raw("trado with (readuncommitted)"), 'pengeluaranstokheader.trado_id', 'trado.id')
        ->leftJoin(DB::raw("pengeluaranstokdetail with (readuncommitted)"), 'pengeluaranstokdetail.pengeluaranstokheader_id', 'pengeluaranstokheader.id')
        ->leftJoin(DB::raw("stok with (readuncommitted)"), 'pengeluaranstokdetail.stok_id', 'stok.id')
        ->whereRaw("MONTH(pengeluaranstokheader.tglbukti) = $month")
        ->whereRaw("YEAR(pengeluaranstokheader.tglbukti) = $year");

        $data = $query->get();
        return $data;
    }
}
