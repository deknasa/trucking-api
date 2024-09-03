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

    public function getReport($kelompok_id, $statusreuse, $statusban, $filter, $jenistgltampil, $priode, $stokdari_id, $stoksampai_id, $dataFilter, $prosesneraca, $jenislaporan)
    {

        // dd('test');
        // dd($priode);




        $prosesneraca = $prosesneraca ?? 0;
        $priode1 = date('Y-m-d', strtotime($priode));
        $priode = date("Y-m-d", strtotime("+1 day", strtotime($priode)));
        // $priode = date("Y-m-d", strtotime($priode));
        // $tglsampai= date("Y-m-d", strtotime("+1 day", strtotime($tgldari)));

        $tglsaldo = '2023/9/30';

        $temprekapall = '##temprekapall' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekapall, function ($table) {
            $table->id();
            $table->unsignedBigInteger('stok_id')->nullable();
            $table->unsignedBigInteger('gudang_id')->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->unsignedBigInteger('gandengan_id')->nullable();
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
            $table->integer('urutfifo')->nullable();
            $table->integer('iddata')->nullable();
            $table->datetime('tglinput')->nullable();
        });

        $filtergudang = Parameter::where('grp', 'STOK PERSEDIAAN')->where('subgrp', 'STOK PERSEDIAAN')->where('text', 'GUDANG')->first();
        $filtertrado = Parameter::where('grp', 'STOK PERSEDIAAN')->where('subgrp', 'STOK PERSEDIAAN')->where('text', 'TRADO')->first();
        $filtergandengan = Parameter::where('grp', 'STOK PERSEDIAAN')->where('subgrp', 'STOK PERSEDIAAN')->where('text', 'GANDENGAN')->first();

        $gudang_id = $gudang_id ?? 0;
        $trado_id = $trado_id ?? 0;
        $gandengan_id = $gandengan_id ?? 0;
        if ($filter == $filtergudang->id) {
            $gudang_id = $dataFilter ?? 0;
            $filterdata = $filtergudang->text;
        } else if ($filter == $filtertrado->id) {
            $trado_id = $dataFilter ?? 0;
            $filterdata = $filtertrado->text;
        } else if ($filter == $filtergandengan->id) {
            $gandengan_id = $dataFilter ?? 0;
            $filterdata = $filtergandengan->text;
        } else {
            $gudang_id = $dataFilter ?? 0;
            $filterdata = '';
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
        $parameter = new Parameter();
        $stokgantung = false;
        $idincludestokgantung = $parameter->cekId('JENIS LAPORAN', 'JENIS LAPORAN', 'INCLUDE SPAREPART GANTUNG') ?? 0;
        // dd($jenislaporan,$idincludestokgantung);
        if ($jenislaporan == $idincludestokgantung) {
            $stokgantung = true;
        } else {
            $stokgantung = false;
        }
        // dd($stokgantung);
        // $stokgantung = true;
        DB::table($temprekapall)->insertUsing([
            'stok_id',
            'gudang_id',
            'trado_id',
            'gandengan_id',
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
            'urutfifo',
            'iddata',
            'tglinput',
        ], (new KartuStok())->getlaporan($priode, $priode, $stokdari_id, $stoksampai_id, $gudang_id, $trado_id, $gandengan_id, $filterdata, $stokgantung));

        // dd($priode);
        // dd(db::table($temprekapall)->get());
        // dd(db::table($temprekapall)->whereraw("namabarang='KAMPAS KOPLING 6D22 17 IN'")->get());

        $querytgl = $priode1;
        $tempmaxin = '##tempmaxin' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempmaxin, function ($table) {
            $table->integer('stok_id')->nullable();
            $table->integer('gudang_id')->nullable();
            $table->integer('trado_id')->nullable();
            $table->integer('gandengan_id')->nullable();
            $table->date('tglbukti')->nullable();
        });

        $querymaxin = db::table($temprekapall)->from(db::raw($temprekapall . " a"))
            ->select(
                db::raw("isnull(a.stok_id,0) as stok_id"),
                db::raw("isnull(a.gudang_id,0) as gudang_id"),
                db::raw("isnull(a.trado_id,0) as trado_id"),
                db::raw("isnull(a.gandengan_id,0) as gandengan_id"),
                db::raw("max(e.tglbukti) as tglbukti"),
            )
            ->join(DB::raw("kartustok as e"), function ($join) {
                $join->on('a.stok_id', '=', db::raw("isnull(e.stok_id,0)"));
                $join->on('a.trado_id', '=', db::raw("isnull(e.trado_id,0)"));
                $join->on('a.gudang_id', '=', db::raw("isnull(e.gudang_id,0)"));
                $join->on('a.gandengan_id', '=', db::raw("isnull(e.gandengan_id,0)"));
            })
            ->whereRaw("isnull(e.qtymasuk,0)<>0")
            ->whereRaw("e.tglbukti<='" . $querytgl . "'")
            ->groupby(db::raw("isnull(a.stok_id,0)"))
            ->groupby(db::raw("isnull(a.gudang_id,0)"))
            ->groupby(db::raw("isnull(a.trado_id,0)"))
            ->groupby(db::raw("isnull(a.gandengan_id,0)"));

        // dd($querymaxin->get());

        DB::table($tempmaxin)->insertUsing([
            'stok_id',
            'gudang_id',
            'trado_id',
            'gandengan_id',
            'tglbukti',
        ],  $querymaxin);


        DB::delete(DB::raw("delete " . $temprekapall . "  WHERE upper(nobukti) not in('SALDO AWAL','SPAREPART GANTUNG')"));

        // dd(db::table($tempmaxin)->get());
        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';


        $priode2 = date('m/d/Y', strtotime($priode1));


        $user = auth('api')->user()->name;
        $tutupqty = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.text as id'
            )
            ->where('a.grp', 'OPNAME STOK')
            ->where('a.subgrp', 'OPNAME STOK')
            ->where('a.kelompok', 'OPNAME STOK')
            ->first()->id ?? 0;

        $cabangpst = 1;
        $cabangpusat = db::table("user")->from(db::raw("[user] a with (readuncommitted)"))
            ->select(
                'a.user'
            )
            ->join(db::raw("cabang b with (readuncommitted)"), 'a.cabang_id', 'b.id')
            ->where('a.cabang_id', $cabangpst)
            ->where('a.user',  $user)
            ->first();

        if (isset($cabangpusat)) {
            $pusat = 1;
        } else {
            if ($tutupqty == '4') {
                $pusat = 1;
            } else {
                $pusat = 0;
            }
        }
        // dd($tutupqty);
        // dd($pusat);

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        if ($kelompok_id == '') {
            $kategori = 'ALL KATEGORI';
        } else {
            $kategori = db::table("kelompok")->from(db::raw("kelompok a with (readuncommitted)"))
                ->select(
                    'a.kodekelompok as kategori'
                )->where('a.id', $kelompok_id)
                ->first()->kategori ?? '';
        }





        $tempumuraki = '##tempumuraki' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempumuraki, function ($table) {
            $table->Integer('stok_id')->nullable();
            $table->integer('jumlahhari')->nullable();
            $table->date('tglawal')->nullable();
        });

        $tempumuraki1 = '##tempumuraki1' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempumuraki1, function ($table) {
            $table->Integer('stok_id')->nullable();
            $table->integer('jumlahhari')->nullable();
            $table->date('tglawal')->nullable();
        });

        DB::table($tempumuraki1)->insertUsing([
            'stok_id',
            'jumlahhari',
            'tglawal',
        ], (new SaldoUmurAki())->getallstok());

        $querytempumuraki1 = DB::table($tempumuraki1)->select(
            'stok_id',
            db::raw('max(jumlahhari) as jumlahhari'),
            db::raw('max(tglawal) as tglawal'),
        )->groupBy('stok_id');

        DB::table($tempumuraki)->insertUsing([
            'stok_id',
            'jumlahhari',
            'tglawal',
        ], $querytempumuraki1);

        $hariaki = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.text as id'
            )
            ->where('a.grp', 'HARIAKI')
            ->where('a.subgrp', 'HARIAKI')
            ->where('a.text', 'TANGGAL')
            ->first();
        if (isset($hariaki)) {
            $bytgl = 1;
        } else {
            $bytgl = 0;
        }

        //update total vulkanisir
        $reuse = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select('a.id')
            ->where('grp', 'STATUS REUSE')
            ->where('subgrp', 'STATUS REUSE')
            ->where('text', 'REUSE')
            ->first()->id ?? 0;


        $tempvulkan = '##tempvulkan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempvulkan, function ($table) {
            $table->integer('stok_id')->nullable();
            $table->integer('vulkan')->nullable();
        });

        $tempvulkanplus = '##tempvulkanplus' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempvulkanplus, function ($table) {
            $table->integer('stok_id')->nullable();
            $table->integer('vulkan')->nullable();
        });


        $tempvulkanminus = '##tempvulkanminus' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempvulkanminus, function ($table) {
            $table->integer('stok_id')->nullable();
            $table->integer('vulkan')->nullable();
        });


        $queryvulkanplus = db::table("stok")->from(db::raw("stok a with (readuncommitted)"))
            ->select(
                db::raw("a.id as stok_id"),
                db::raw("sum(b.vulkanisirke) as vulkan"),
            )
            ->join(db::raw("penerimaanstokdetail b with (readuncommitted)"), 'a.id', 'b.stok_id')
            ->join(db::raw("penerimaanstokheader c with (readuncommitted)"), 'b.nobukti', 'c.nobukti')
            ->where('a.statusreuse', $reuse)
            ->whereraw("c.tglbukti<='" . $querytgl . "'")
            ->groupby('a.id');

        DB::table($tempvulkanplus)->insertUsing([
            'stok_id',
            'vulkan',
        ],  $queryvulkanplus);

        $queryvulkanminus = db::table("stok")->from(db::raw("stok a with (readuncommitted)"))
            ->select(
                db::raw("a.id as stok_id"),
                db::raw("sum(b.vulkanisirke) as vulkan"),
            )
            ->join(db::raw("pengeluaranstokdetail b with (readuncommitted)"), 'a.id', 'b.stok_id')
            ->join(db::raw("pengeluaranstokheader c with (readuncommitted)"), 'b.nobukti', 'c.nobukti')
            ->where('a.statusreuse', $reuse)
            ->whereraw("c.tglbukti<='" . $querytgl . "'")
            ->groupby('a.id');

        DB::table($tempvulkanminus)->insertUsing([
            'stok_id',
            'vulkan',
        ],  $queryvulkanminus);


        $queryvulkan = db::table("stok")->from(db::raw("stok a with (readuncommitted)"))
            ->select(
                db::raw("a.id  as stok_id"),
                db::raw("((isnull(a.vulkanisirawal,0)+isnull(b.vulkan,0))-isnull(c.vulkan,0)) as vulkan"),
            )
            ->leftjoin(db::raw($tempvulkanplus . " b "), 'a.id', 'b.stok_id')
            ->leftjoin(db::raw($tempvulkanminus . " c "), 'a.id', 'c.stok_id')
            ->where('a.statusreuse', $reuse);

        DB::table($tempvulkan)->insertUsing([
            'stok_id',
            'vulkan',
        ],  $queryvulkan);


        $tempurut = '##tempurut' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempurut, function ($table) {
            $table->Integer('stok_id')->nullable();
            $table->integer('kelompok_id')->nullable();
            $table->integer('urut')->nullable();
        });

        $tempurutfilter = '##tempurutfilter' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempurutfilter, function ($table) {
            $table->Integer('stok_id')->nullable();
            $table->integer('kelompok_id')->nullable();
            $table->string('lokasi', 1000)->nullable();
            $table->string('kodebarang', 1000)->nullable();
        });

        $queryurutfilter = DB::table($temprekapall)->from(
            DB::raw($temprekapall . " a")
        )->select(
            'a.stok_id',
            'c.id as kelompok_id',
            'a.lokasi',
            'a.kodebarang',
        )
            ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id')
            ->leftjoin(db::raw("kelompok c with (readuncommitted)"), 'b.kelompok_id', 'c.id')
            ->whereraw("(a.qtysaldo<>0 or a.nilaisaldo<>0)");

        DB::table($tempurutfilter)->insertUsing([
            'stok_id',
            'kelompok_id',
            'lokasi',
            'kodebarang',
        ],  $queryurutfilter);


        $queryurut = DB::table($tempurutfilter)->from(
            DB::raw($tempurutfilter . " a")
        )->select(
            'a.stok_id',
            'c.id as kelompok_id',
            DB::raw('ROW_NUMBER() OVER (PARTITION BY a.lokasi,c.kodekelompok ORDER BY a.lokasi,c.kodekelompok,a.kodebarang) as urut')
        )
            ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id')
            ->leftjoin(db::raw("kelompok c with (readuncommitted)"), 'b.kelompok_id', 'c.id')
            ->OrderBY('a.lokasi', 'asc')
            ->OrderBY('c.kodekelompok', 'asc')
            ->OrderBY('a.kodebarang', 'asc');

        DB::table($tempurut)->insertUsing([
            'stok_id',
            'kelompok_id',
            'urut',
        ],  $queryurut);


        // dd(db::table($tempurut)->where('kelompok_id',2)->get());

        // end update vulkanisir
        $stokdari = db::table('stok')->from(db::raw('stok with (readuncommitted)'))->select('namastok')->where('id', $stokdari_id)->first();
        $dariStok = ($stokdari != '') ? $stokdari->namastok : '';
        $stoksampai = db::table('stok')->from(db::raw('stok with (readuncommitted)'))->select('namastok')->where('id', $stoksampai_id)->first();
        $sampaiStok = ($stoksampai != '') ? $stoksampai->namastok : '';
        $query = DB::table($temprekapall)->from(
            DB::raw($temprekapall . " a")
        )
            ->select(
                DB::raw("upper('Laporan Saldo Inventory') as header"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                db::raw("(case when isnull(a.gudang_id,0)<>0 then 'GUDANG'
                when isnull(a.trado_id,0)<>0 then 'TRADO'
                when isnull(a.gandengan_id,0)<>0 then 'GANDENGAN'
                when isnull(a.lokasi,'')='SPAREPART GANTUNG' then 'SPAREPART GANTUNG'
                else 'GUDANG' END) AS lokasi
                "),
                'a.lokasi as namalokasi',
                db::raw("isnull(c.kodekelompok,'') as kategori"),
                DB::raw("'" . $priode1 . "' as tgldari"),
                DB::raw("'" . $priode1 . "' as tglsampai"),
                DB::raw("'" . $dariStok . "' as stokdari"),
                DB::raw("'" . $sampaiStok . "' as stoksampai"),
                db::raw("
                (case when isnull(c1.stok_id,0)<>0 then ' ( '+
                    (case when " . $bytgl . "=1 then 'TGL PAKAI '+format(c1.tglawal,'dd-MM-yyyy')+',' else '' end)+
                    'UMUR AKI : '+format(isnull(c1.jumlahhari,0),'#,#0')+' HARI )' 
                      when isnull(b.kelompok_id,0)=1 then '( VULKE:'+format(isnull(d1.vulkan,0),'#,#0')+' )' 
                else replicate(' ',14-len(trim(str(isnull(d2.urut,0)))))+ trim(str(isnull(d2.urut,0))) end)
                as vulkanisirke"),

                // DB::raw("'VulKe :'+trim(str(isnull(b.totalvulkanisir,0))) as vulkanisirke"),
                'a.stok_id as stok_id',
                'a.kodebarang',
                // db::raw("(case when isnull(b.keterangan,'')='' then b.namastok else b.keterangan end)+ ' '+
                db::raw("isnull(c.kodekelompok,'')+' - '+trim(b.namastok)+ ' '+
                (case when isnull(c1.stok_id,0)<>0 then ' ( '+
                (case when " . $bytgl . "=1 then 'TGL PAKAI '+format(c1.tglawal,'dd-MM-yyyy')+',' else '' end)+
                'UMUR AKI : '+format(isnull(c1.jumlahhari,0),'#,#0')+' HARI )' 
                  when isnull(b.kelompok_id,0)=1 then ' ( VULKE:'+format(isnull(d1.vulkan,0),'#,#0')+', STATUS BAN :'+isnull(parameter.text,'') +' )' 
            else '' end) 
                as namabarang"),
                // DB::raw("isnull(e.tglbukti,'" . $tglsaldo . "') as tanggal"),
                DB::raw("isnull(e.tglbukti,'1900/1/1') as tanggal"),
                db::raw("(case when " . $pusat . "=0 then 0 else a.qtysaldo  end) as qty"),
                DB::raw("isnull(d.satuan,'') as satuan"),
                db::raw("(case when " . $pusat . "=0 then 0 else a.nilaisaldo  end) as nominal"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),

            )
            ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id')
            ->leftjoin(db::raw("kelompok c with (readuncommitted)"), 'b.kelompok_id', 'c.id')
            ->leftjoin(db::raw("satuan d with (readuncommitted)"), 'b.satuan_id', 'd.id')
            ->leftjoin(DB::raw($tempmaxin . " as e"), function ($join) {
                $join->on('a.stok_id', '=', 'e.stok_id');
                $join->on('a.trado_id', '=', 'e.trado_id');
                $join->on('a.gudang_id', '=', 'e.gudang_id');
                $join->on('a.gandengan_id', '=', 'e.gandengan_id');
            })
            ->leftJoin(db::raw($tempumuraki . " c1"), "b.id", "c1.stok_id")
            ->leftJoin(db::raw($tempvulkan . " d1"), "b.id", "d1.stok_id")
            ->leftJoin("parameter", "b.statusban", "parameter.id")
            ->leftJoin(db::raw($tempurut . " d2"), "b.id", "d2.stok_id")

            ->whereraw("(a.qtysaldo<>0 or a.nilaisaldo<>0)");


        if ($prosesneraca != 1) {
            $query->whereRaw("(isnull(b.kelompok_id,0)=" . $kelompok_id . " or " . $kelompok_id . "='')");
        }

        if ($statusreuse != '') {
            $query->whereRaw("(isnull(b.statusreuse,0)=" . $statusreuse . ")");
        }

        if ($statusban != '') {
            $query->whereRaw("(isnull(b.statusban,0)=" . $statusban . ")");
        }

        $query->OrderBY('a.lokasi', 'asc');
        $query->OrderBY('c.kodekelompok', 'asc');
        $query->OrderBY('a.kodebarang', 'asc');







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


        if ($prosesneraca == 1) {
            $data = $query;
            // dd($data->get());
        } else {
            $data = $query->get();
        }
        return $data;
    }
}
