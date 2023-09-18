<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\KartuStok;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanSaldoInventory extends MyModel
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

    public function getReport($kelompok_id, $statusreuse, $statusban, $filter, $jenistgltampil, $priode, $stokdari_id, $stoksampai_id, $dataFilter)
    {

        // dd('test');
        // dd($priode);
        $priode1= date('Y-m-d', strtotime($priode));
        $priode= date("Y-m-d", strtotime("+1 day", strtotime($priode)));
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

        $filtergudang = Parameter::where('grp', 'STOK PERSEDIAAN')->where('subgrp', 'STOK PERSEDIAAN')->where('text', 'GUDANG')->first();
        $filtertrado = Parameter::where('grp', 'STOK PERSEDIAAN')->where('subgrp', 'STOK PERSEDIAAN')->where('text', 'TRADO')->first();
        $filtergandengan = Parameter::where('grp', 'STOK PERSEDIAAN')->where('subgrp', 'STOK PERSEDIAAN')->where('text', 'GANDENGAN')->first();

        $gudang_id = $gudang_id ?? 0;
        $trado_id = $trado_id ?? 0;
        $gandengan_id = $gandengan_id ?? 0;
        if ($filter == $filtergudang->id) {
            $gudang_id = $dataFilter ?? 0;
            $filterdata=$filtergudang->text;
        } else if ($filter == $filtertrado->id) {
            $trado_id = $dataFilter ?? 0;
            $filterdata=$filtertrado->text;
        } else if ($filter == $filtergandengan->id) {
            $gandengan_id = $dataFilter ?? 0;
            $filterdata=$filtergandengan->text;
        } else {
            $gudang_id = $dataFilter ?? 0;
            $filterdata=$filtergudang->text;
        }

        // dump($priode);
        // dump($priode); 
        // dump($stokdari_id);
        // dump($stoksampai_id);
        // dump($gudang_id);
        // dump($trado_id); 
        // dump($gandengan_id);
        // dd( $filterdata);

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
        ], (new KartuStok())->getlaporan($priode, $priode, $stokdari_id, $stoksampai_id, $gudang_id, $trado_id, $gandengan_id, $filterdata));

        // dd(db::table($temprekapall)->get());

        // DB::delete(DB::raw("delete " . $temprekapall . "  WHERE upper(nobukti)<>'SALDO AWAL'"));

        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
        ->select('text')
        ->where('grp', 'DISETUJUI')
        ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

    $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
        ->select('text')
        ->where('grp', 'DIPERIKSA')
        ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';


        $priode2= date('m/d/Y', strtotime($priode1));
       
        $query = DB::table($temprekapall)->from(
            DB::raw($temprekapall . " a")
        )
            ->select(
                DB::raw("'Laporan Saldo Inventory' as header"),
                'a.lokasi',
                'a.lokasi as namalokasi',
                DB::raw("'' as kategori"),
                DB::raw("'".$priode1."' as tgldari"),
                DB::raw("'".$priode1."' as tglsampai"),
                DB::raw("'' as stokdari"),
                DB::raw("'' as stoksampai"),
                DB::raw("'' as vulkanisirke"),
                'a.kodebarang as id',
                'a.kodebarang',
                'a.namabarang',
                DB::raw("'".$priode2."' as tanggal"),
                'a.qtysaldo as qty',
                DB::raw("'' as satuan"),
                'a.nilaisaldo as nominal',
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),

            );
           


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

        $data=$query->get();
        return $data;
    }
}
