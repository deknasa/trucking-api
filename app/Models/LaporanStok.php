<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\KartuStok;

class LaporanStok extends MyModel
{
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

    public function getReport($bulan, $tahun)
    {


        $tgl = '01-' . $bulan . '-' . $tahun;

        $tgldari = date('Y-m-d', strtotime($tgl));
        $tgl2 = date('t-m-Y', strtotime($tgl));
        $tglsampai = date('Y-m-d', strtotime($tgl2));
        $tglsampai1 = date('Y-m-d', strtotime('+1 days', strtotime($tgl2)));



        // $tglsampai= date("Y-m-d", strtotime("+1 day", strtotime($tgldari)));


        $temprekapall = '##temprekapall' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekapall, function ($table) {
            $table->id();
            $table->longText('lokasi')->nullable();
            $table->string('kodebarang', 1000)->nullable();
            $table->string('namabarang', 1000)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->string('kategori_id', 500)->nullable();
            $table->double('qtymasuk', 15, 2)->nullable();
            $table->double('nilaimasuk', 15, 2)->nullable();
            $table->double('qtykeluar', 15, 2)->nullable();
            $table->double('nilaikeluar', 15, 2)->nullable();
            $table->double('qtysaldo', 15, 2)->nullable();
            $table->double('nilaisaldo', 15, 2)->nullable();
            $table->string('modifiedby', 100)->nullable();
        });

        $tempstoktransaksi = '##tempstoktransaksi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempstoktransaksi, function ($table) {
            $table->id();
            $table->string('kodebarang', 1000)->nullable();
        });



        $idgudangkantor = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select('a.text')
            ->where('grp', 'GUDANG KANTOR')
            ->where('subgrp', 'GUDANG KANTOR')
            ->first()->text ?? 0;


        $filtergudang = Parameter::where('grp', 'STOK PERSEDIAAN')->where('subgrp', 'STOK PERSEDIAAN')->where('text', 'GUDANG')->first();

        $trado_id = 0;
        $gandengan_id = 0;


        $stokdari_id = 0;
        $stoksampai_id = 0;

        // dd($filter);
        $kartustok = new KartuStok();
        DB::table($temprekapall)->insertUsing([
            'lokasi',
            'kodebarang',
            'namabarang',
            'tglbukti',
            'nobukti',
            'kategori_id',
            'qtymasuk',
            'nilaimasuk',
            'qtykeluar',
            'nilaikeluar',
            'qtysaldo',
            'nilaisaldo',
            'modifiedby',
        ], (new KartuStok())->getlaporan($tgldari, $tglsampai, $stokdari_id, $stoksampai_id, $idgudangkantor, $trado_id, $gandengan_id, $filtergudang));

        // dd(db::table($temprekapall)->where('kodebarang','3021/04831105 SWL')->get());

        $querystoktransaksi = DB::table($temprekapall)->from(db::raw($temprekapall . " as a"))
            ->select(
                'a.kodebarang',
            )
            ->whereRaw("upper(a.nobukti)<>'SALDO AWAL'")
            ->groupby('a.kodebarang');


        DB::table($tempstoktransaksi)->insertUsing([
            'kodebarang',
        ],  $querystoktransaksi);

        DB::delete(DB::raw("delete " . $temprekapall . " from " . $temprekapall . " as a left outer join " . $tempstoktransaksi . " b on a.kodebarang=b.kodebarang 
                            WHERE isnull(b.kodebarang,'')='' and isnull(a.qtysaldo,0)=0"));



        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';
        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($temprekapall)->from(
            DB::raw($temprekapall . " a")
        )
            ->select(
                DB::raw("'Laporan Saldo Inventory' as header"),
                'a.lokasi',
                'a.lokasi as namalokasi',
                DB::raw("'' as kategori"),
                DB::raw("'" . $tgldari . "' as tgldari"),
                DB::raw("'" . $tglsampai . "' as tglsampai"),
                DB::raw("'' as vulkanisirke"),
                DB::raw("'' as keterangan"),
                'a.nobukti as nobukti',
                'a.kodebarang as id',
                'a.kodebarang',
                'a.namabarang',
                'a.tglbukti as tglbukti',
                'a.qtymasuk as qtymasuk',
                'a.nilaimasuk as nominalmasuk',
                'a.qtykeluar as qtykeluar',
                'a.nilaikeluar as nominalkeluar',
                'a.qtysaldo as qtysaldo',
                'a.nilaisaldo as nominalsaldo',
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
                db::raw("(case when a.nobukti='SALDO AWAL' then 1 else 0 end) as baris"),
                DB::raw("'" . $getJudul->text . "' as judul"),

            )
            ->orderBy('a.namabarang', 'asc')
            ->orderBy('a.tglbukti', 'asc')
            ->orderBy(db::raw("(case when UPPER(isnull(a.nobukti,''))='SALDO AWAL' then '' else isnull(a.nobukti,'') end)"), 'asc');



        // 'header' => 'Laporan Saldo Inventory',
        //         'lokasi' => 'GUDANG',
        //         'namalokasi' => 'GUDANG KANTOR',
        //         'kategori' => 'sparepart',
        //         'tgldari' => '2023-07-20',
        //         'tglsampai' => '2023-07-20',
        //         'stokdari' => 'BAN DALAM SWALLOW 900',
        //         'stoksampai' => 'BAN DALAM SWALLOW 900',
        //         'vulkanisirke' => 'Vul Ke: 0',
        //         'kodebarang' => '04819203',
        //         'namabarang' => 'BAN DALAM SWALLOW 900',
        //         'tanggal' => '08-07-2023',
        //         'qty' => '200',
        //         'satuan' => 'buah',
        //         'nominal' => '8300000',

        // dd(DB::table($temprekapall)->get());

        $data = $query->get();
        return $data;
    }
}
