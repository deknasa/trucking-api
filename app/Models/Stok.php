<?php

namespace App\Models;

use App\Helpers\App;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class Stok extends MyModel
{
    use HasFactory;

    protected $table = 'stok';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function cekvalidasihapus($id)
    {

        $pengeluaranStok = DB::table('pengeluaranstokdetail')
            ->from(
                DB::raw("pengeluaranstokdetail as a with (readuncommitted)")
            )
            ->select(
                'a.stok_id'
            )
            ->where('a.stok_id', '=', $id)
            ->first();
        if (isset($pengeluaranStok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pengeluaran Stok',
            ];


            goto selesai;
        }

        $penerimaanStok = DB::table('penerimaanstokdetail')
            ->from(
                DB::raw("penerimaanstokdetail as a with (readuncommitted)")
            )
            ->select(
                'a.stok_id'
            )
            ->where('a.stok_id', '=', $id)
            ->first();
        if (isset($penerimaanStok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan Stok',
            ];


            goto selesai;
        }


        $data = [
            'kondisi' => false,
            'keterangan' => '',
        ];

        selesai:
        return $data;
    }

    public function get()
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $aktif = request()->aktif ?? '$spb->text == $penerimaanstok_id';
        $statusreuse = request()->statusreuse ?? '';
        $dari = request()->dari ?? '';
        $approveReuse = request()->approveReuse ?? false;
        $kelompok = request()->kelompok_id ?? '';
        $isLookup = boolval(request()->isLookup) ?? false;
        $from = request()->from ?? '';
        $KelompokId_stok = request()->KelompokId ?? '';//dari lookup
        $penerimaanstok_id = request()->penerimaanstok_id ?? '';
        $pengeluaranstok_id = request()->pengeluaranstok_id ?? '';
        $penerimaanstokheader_nobukti = request()->penerimaanstokheader_nobukti ?? '';
        $StokId_stok = request()->StokId ?? '';//dari lookup
        $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
        $pg = Parameter::where('grp', 'PG STOK')->where('subgrp', 'PG STOK')->first();
        $po = Parameter::where('grp', 'PO STOK')->where('subgrp', 'PO STOK')->first();
        $korv = DB::table('penerimaanstok')->where('kodepenerimaan', 'KORV')->first();
        $nobukti = request()->nobukti ?? '';

        
        if ($isLookup==true) {
            $isLookupint=1;
        } else {
            $isLookupint=0;
        }
        $tempdatastokpg = '##tempdatastokpg' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdatastokpg, function ($table) {
            $table->Integer('stok_id')->nullable();
        });
        if ($StokId_stok!='') {
            if (($pg->text == $penerimaanstok_id) || ($spk->text == $pengeluaranstok_id)) {
        
            $querysubkelompok=db::table('stok')->from(db::raw("stok a with (readuncommitted)"))
            ->select(
                'a.id as stok_id'
            )
            ->join(db::raw("subkelompok b with (readuncommitted)"),'a.subkelompok_id','b.id')
            ->whereraw("(case when isnull(b.kelompokpindahgudang_id,0)=0 then a.kelompok_id else  isnull(b.kelompokpindahgudang_id,0) end)=". $KelompokId_stok);

             
            // dd($querysubkelompok->get());

            DB::table($tempdatastokpg)->insertUsing([
                'stok_id',
            ], $querysubkelompok);
   
        }
        }

        // $parameter = new Parameter();

        // $idpg = $parameter->cekText('PG STOK', 'PG STOK') ?? 0;

        // if ($penerimaanstok_id==$idpg) {
        //     $kelompok='';
        //     $KelompokId_stok ='';  
        // }
      
 
        $tempumuraki2 = '##tempumuraki2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempumuraki2, function ($table) {
            $table->Integer('stok_id')->nullable();
            $table->integer('jumlahhari')->nullable();
            $table->date('tglawal')->nullable();
        });
        $tempvulkan = '##tempvulkan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempvulkan, function ($table) {
            $table->integer('stok_id')->nullable();
            $table->integer('vulkan')->nullable();
        });

        if ($isLookup==true) {
            goto lanjut;
        }

        $tempumuraki = '##tempumuraki' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempumuraki, function ($table) {
            $table->Integer('stok_id')->nullable();
            $table->integer('jumlahhari')->nullable();
            $table->date('tglawal')->nullable();
        });

        DB::table($tempumuraki)->insertUsing([
            'stok_id',
            'jumlahhari',
            'tglawal',
        ], (new SaldoUmurAki())->getallstok());


        $queryaki = db::table($tempumuraki)->from(db::raw($tempumuraki . " a "))
            ->select(
                'a.stok_id',
                db::raw("max(a.jumlahhari) as jumlahhari"),
                db::raw("max(a.tglawal) as tglawal"),
            )
            ->groupby('a.stok_id');

        DB::table($tempumuraki2)->insertUsing([
            'stok_id',
            'jumlahhari',
            'tglawal',
        ],  $queryaki);

        //update total vulkanisir
        
        DB::table($tempvulkan)->insertUsing([
            'stok_id',
            'vulkan',
        ], $this->getVulkan());
    
        
        lanjut:
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'StokController';
        $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();
        $retur = Parameter::where('grp', 'RETUR STOK')->where('subgrp', 'RETUR STOK')->first();

        if ($proses == 'reload') {
            $tempbukti = '##tempbukti' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempbukti, function (Blueprint $table) {
                $table->bigInteger('id')->nullable();
                $table->integer('stok_id')->nullable();
            });

            $querybukti=db::table("penerimaanstokdetail")->from(db::raw("penerimaanstokdetail a with (readuncommitted)"))
            ->select(
                'a.id',
                'a.stok_id'
            )
            ->where('a.nobukti',$nobukti);
            
            DB::table($tempbukti)->insertUsing([
                'id',
                'stok_id',

            ], $querybukti);

            $querybukti=db::table("pengeluaranstokdetail")->from(db::raw("pengeluaranstokdetail a with (readuncommitted)"))
            ->select(
                'a.id',
                'a.stok_id'
            )
            ->where('a.nobukti',$nobukti);
            
            DB::table($tempbukti)->insertUsing([
                'id',
                'stok_id',

            ], $querybukti);            

            $querybukti=db::table("stok")->from(db::raw("stok a with (readuncommitted)"))
            ->select(
                db::raw("0 as id"),
                'a.id as stok_id'
            )
            ->leftjoin(db::raw($tempbukti . " b"),'a.id','b.stok_id')
            ->whereraw("isnull(b.stok_id,0)=0");
            
            DB::table($tempbukti)->insertUsing([
                'id',
                'stok_id',

            ], $querybukti);          



            $temtabel = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel a with (readuncommitted)")
            )
                ->select(
                    'id',
                    'class',
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            if (isset($querydata)) {
                Schema::dropIfExists($querydata->namatabel);
                DB::table('listtemporarytabel')->where('id', $querydata->id)->delete();
            }

            DB::table('listtemporarytabel')->insert(
                [
                    'class' => $class,
                    'namatabel' => $temtabel,
                    'modifiedby' => $user,
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s'),
                ]
            );

            Schema::create($temtabel, function (Blueprint $table) {
                $table->bigInteger('id')->nullable();
                $table->string('namastok',3000)->nullable();
                $table->longtext('statusaktif')->nullable();
                $table->longtext('statusservicerutin')->nullable();
                $table->string('servicerutin_text',1000)->nullable();
                $table->double('qtymin',15,2)->nullable();
                $table->double('qtymax',15,2)->nullable();
                $table->longtext('keterangan')->nullable();
                $table->longtext('gambar')->nullable();
                $table->string('namaterpusat',3000)->nullable();
                $table->longtext('statusapprovaltanpaklaim')->nullable();
                $table->longtext('statusban')->nullable();
                $table->bigInteger('statusban_id')->nullable();
                $table->longtext('statusreuse')->nullable();
                $table->string('modifiedby',3000)->nullable();
                $table->double('totalvulkanisir',15,2)->nullable();
                $table->double('vulkanisirawal',15,2)->nullable();
                $table->string('jenistrado',3000)->nullable();
                $table->string('satuan',3000)->nullable();
                $table->string('kelompok',3000)->nullable();
                $table->string('subkelompok',3000)->nullable();
                $table->string('kategori',3000)->nullable();
                $table->string('merk',3000)->nullable();
                $table->datetime('created_at')->nullable();
                $table->datetime('updated_at')->nullable();
                $table->string('judulLaporan',3000)->nullable();
                $table->string('judul',3000)->nullable();
                $table->string('tglcetak',3000)->nullable();
                $table->string('usercetak',3000)->nullable();
                $table->double('umuraki',15,2)->nullable();
                $table->double('vulkan',15,2)->nullable();
                $table->longtext('penerimaanstokdetail_keterangan')->nullable();
                $table->double('penerimaanstokdetail_qty',15,2)->nullable();
                $table->double('penerimaanstokdetail_harga',15,2)->nullable();
                $table->double('penerimaanstokdetail_total',15,2)->nullable();
                $table->bigInteger('statusaktif_id')->nullable();
                $table->bigInteger('statusreuse_id')->nullable();
                $table->bigInteger('kelompok_id')->nullable();
                $table->bigInteger('iddetail')->nullable();

            });

          

            

            if ($penerimaanstokheader_nobukti && $retur->text == $pengeluaranstok_id) {
                $query = DB::table($this->table)->select(
                    'stok.id',
                    'stok.namastok',
                    'parameter.memo as statusaktif',
                    'service.memo as statusservicerutin',
                    'service.text as servicerutin_text',
                    'stok.qtymin',
                    'stok.qtymax',
                    'stok.keterangan',
                    'stok.gambar',
                    'stok.namaterpusat',
                    'statusapprovaltanpaklaim.memo as statusapprovaltanpaklaim',                    
                    'statusban.text as statusban',
                    'stok.statusban as statusban_id',
                    'statusreuse.memo as statusreuse',
                    'stok.modifiedby',
                    'stok.totalvulkanisir',
                    'stok.vulkanisirawal',
                    'jenistrado.keterangan as jenistrado',
                    'satuan.satuan as satuan',
                    'kelompok.kodekelompok as kelompok',
                    'subkelompok.kodesubkelompok as subkelompok',
                    'kategori.kodekategori as kategori',
                    'merk.keterangan as merk',
                    'stok.created_at',
                    'stok.updated_at',
                    DB::raw("'Laporan Stok' as judulLaporan"),
                    DB::raw("'" . $getJudul->text . "' as judul"),
                    DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                    DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                    DB::raw("isnull(c1.jumlahhari,0) as umuraki"),
                    DB::raw("isnull(d1.vulkan,0) as vulkan"),
                    DB::raw("'' as penerimaanstokdetail_keterangan"),
                    DB::raw("'' as penerimaanstokdetail_qty"),
                    DB::raw("'' as penerimaanstokdetail_harga"),
                    DB::raw("'' as penerimaanstokdetail_total"),
                    'stok.statusaktif as statusaktif_id',
                    'stok.statusreuse as statusreuse_id',
                    'stok.kelompok_id as kelompok_id',
                    'c12.id as iddetail',
        
                )
               ->leftJoin('jenistrado', 'stok.jenistrado_id', 'jenistrado.id')
                ->leftJoin('satuan', 'stok.satuan_id', 'satuan.id')
                ->leftJoin('kelompok', 'stok.kelompok_id', 'kelompok.id')
                ->leftJoin('subkelompok', 'stok.subkelompok_id', 'subkelompok.id')
                ->leftJoin('kategori', 'stok.kategori_id', 'kategori.id')
                ->leftJoin('parameter', 'stok.statusaktif', 'parameter.id')
                ->leftJoin(DB::raw("parameter as service with (readuncommitted)"), 'stok.statusservicerutin', 'service.id')
                ->leftJoin(DB::raw("parameter as statusban with (readuncommitted)"), 'stok.statusban', 'statusban.id')
                ->leftJoin(DB::raw("parameter as statusreuse with (readuncommitted)"), 'stok.statusreuse', 'statusreuse.id')
                ->leftJoin(DB::raw("parameter as statusapprovaltanpaklaim with (readuncommitted)"), 'stok.statusapprovaltanpaklaim', 'statusapprovaltanpaklaim.id')                    
                ->leftJoin('merk', 'stok.merk_id', 'merk.id')
                ->leftJoin(db::raw($tempvulkan . " d1"), "stok.id", "d1.stok_id")
                ->leftJoin(db::raw($tempumuraki2 . " c1"), "stok.id", "c1.stok_id")
                ->Join(db::raw($tempbukti . " c12"), "stok.id", "c12.stok_id");

                // if (($from == "pengeluaranstok") ||($from == "penerimaanstok")) {
                //     $query->where(function ($query) {
                //         $query->where('stok.jenistrado_id', '!=', 0)
                //               ->where('stok.kelompok_id', '!=', 0)
                //               ->where('stok.kategori_id', '!=', 0)
                //               ->where('stok.subkelompok_id', '!=', 0)
                //               ->where('stok.merk_id', '!=', 0)
                //               ->where('stok.satuan_id', '!=', 0);
                //     });
                // }
                
            } else {
                $query = DB::table($this->table)->select(
                    'stok.id',
                    'stok.namastok',
                    'parameter.memo as statusaktif',
                    'service.memo as statusservicerutin',
                    'service.text as servicerutin_text',
                    'stok.qtymin',
                    'stok.qtymax',
                    'stok.keterangan',
                    'stok.gambar',
                    'stok.namaterpusat',
                    'statusapprovaltanpaklaim.memo as statusapprovaltanpaklaim',                    
                    'statusban.text as statusban',
                    'stok.statusban as statusban_id',
                    'statusreuse.memo as statusreuse',
                    'stok.modifiedby',
                    'stok.totalvulkanisir',
                    'stok.vulkanisirawal',
                    'jenistrado.keterangan as jenistrado',
                    'satuan.satuan as satuan',
                    'kelompok.kodekelompok as kelompok',
                    'subkelompok.kodesubkelompok as subkelompok',
                    'kategori.kodekategori as kategori',
                    'merk.keterangan as merk',
                    'stok.created_at',
                    'stok.updated_at',
                    DB::raw("'Laporan Stok' as judulLaporan"),
                    DB::raw("'" . $getJudul->text . "' as judul"),
                    DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                    DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                    DB::raw("isnull(c1.jumlahhari,0) as umuraki"),
                    DB::raw("isnull(d1.vulkan,0) as vulkan"),
                    DB::raw("'' as penerimaanstokdetail_keterangan"),
                    DB::raw("'' as penerimaanstokdetail_qty"),
                    DB::raw("'' as penerimaanstokdetail_harga"),
                    DB::raw("'' as penerimaanstokdetail_total"),
                    'stok.statusaktif as statusaktif_id',
                    'stok.statusreuse as statusreuse_id',
                    db::raw("(case when ".$isLookupint."=1 then 
                        (case when isnull(subkelompok.kelompokpindahgudang_id,0)=0 then stok.kelompok_id else  isnull(subkelompok.kelompokpindahgudang_id,0) end)
                        else stok.kelompok_id  end) as kelompok_id
                        "),
                        'c12.id as iddetail',
                    
                )
                    ->leftJoin('jenistrado', 'stok.jenistrado_id', 'jenistrado.id')
                    ->leftJoin('satuan', 'stok.satuan_id', 'satuan.id')
                    ->leftJoin('kelompok', 'stok.kelompok_id', 'kelompok.id')
                    ->leftJoin('subkelompok', 'stok.subkelompok_id', 'subkelompok.id')
                    ->leftJoin('kategori', 'stok.kategori_id', 'kategori.id')
                    ->leftJoin('parameter', 'stok.statusaktif', 'parameter.id')
                    ->leftJoin(DB::raw("parameter as service with (readuncommitted)"), 'stok.statusservicerutin', 'service.id')
                    ->leftJoin(DB::raw("parameter as statusban with (readuncommitted)"), 'stok.statusban', 'statusban.id')
                    ->leftJoin(DB::raw("parameter as statusreuse with (readuncommitted)"), 'stok.statusreuse', 'statusreuse.id')
                    ->leftJoin(DB::raw("parameter as statusapprovaltanpaklaim with (readuncommitted)"), 'stok.statusapprovaltanpaklaim', 'statusapprovaltanpaklaim.id')                    
                    ->leftJoin('merk', 'stok.merk_id', 'merk.id')
                    ->leftJoin(db::raw($tempvulkan . " d1"), "stok.id", "d1.stok_id")
                    ->leftJoin(db::raw($tempumuraki2 . " c1"), "stok.id", "c1.stok_id")
                    ->Join(db::raw($tempbukti . " c12"), "stok.id", "c12.stok_id");

                    // if (($from == "pengeluaranstok") ||($from == "penerimaanstok")) {
                    //     $query->where(function ($query) {
                    //         $query->where('stok.jenistrado_id', '!=', 0)
                    //               ->where('stok.kelompok_id', '!=', 0)
                    //               ->where('stok.kategori_id', '!=', 0)
                    //               ->where('stok.subkelompok_id', '!=', 0)
                    //               ->where('stok.merk_id', '!=', 0)
                    //               ->where('stok.satuan_id', '!=', 0);
                    //     });
                    // }
            }
            DB::table($temtabel)->insertUsing([
                'id',
                'namastok',
                'statusaktif',
                'statusservicerutin',
                'servicerutin_text',
                'qtymin',
                'qtymax',
                'keterangan',
                'gambar',
                'namaterpusat',
                'statusapprovaltanpaklaim',
                'statusban',
                'statusban_id',
                'statusreuse',
                'modifiedby',
                'totalvulkanisir',
                'vulkanisirawal',
                'jenistrado',
                'satuan',
                'kelompok',
                'subkelompok',
                'kategori',
                'merk',
                'created_at',
                'updated_at',
                'judulLaporan',
                'judul',
                'tglcetak',
                'usercetak',
                'umuraki',
                'vulkan',
                'penerimaanstokdetail_keterangan',
                'penerimaanstokdetail_qty',
                'penerimaanstokdetail_harga',
                'penerimaanstokdetail_total',
                'statusaktif_id',
                'statusreuse_id',
                'kelompok_id',
                'iddetail',
            ], $query);
        } else {
            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel with (readuncommitted)")
            )
                ->select(
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            $temtabel = $querydata->namatabel;
        }

        // $querydata = DB::table('listtemporarytabel')->from(
        //     DB::raw("listtemporarytabel with (readuncommitted)")
        // )
        //     ->select(
        //         'namatabel',
        //     )
        //     ->where('class', '=', $class)
        //     ->where('modifiedby', '=', $user)
        //     ->first();

        // $temtabel = $querydata->namatabel;
        $query = DB::table(DB::raw($temtabel))->from(
            DB::raw(DB::raw($temtabel) . " stok with (readuncommitted)")
        )
            ->select(
                'stok.id',
                'stok.namastok',
                'stok.statusaktif',
                'stok.statusservicerutin',
                'stok.servicerutin_text',
                'stok.qtymin',
                'stok.qtymax',
                'stok.keterangan',
                'stok.gambar',
                'stok.namaterpusat',
                'stok.statusapprovaltanpaklaim',
                'stok.statusban',
                'stok.statusban_id',
                'stok.statusreuse',
                'stok.modifiedby',
                'stok.totalvulkanisir',
                'stok.vulkanisirawal',
                'stok.jenistrado',
                'stok.satuan',
                'stok.kelompok',
                'stok.subkelompok',
                'stok.kategori',
                'stok.merk',
                'stok.created_at',
                'stok.updated_at',
                'stok.judulLaporan',
                'stok.judul',
                'stok.tglcetak',
                'stok.usercetak',
                'stok.umuraki',
                'stok.vulkan',
                'stok.penerimaanstokdetail_keterangan',
                'stok.penerimaanstokdetail_qty',
                'stok.penerimaanstokdetail_harga',
                'stok.penerimaanstokdetail_total',
                'stok.kelompok_id',
                'stok.iddetail',
            );



        // end update vulkanisir
       

       


        $this->filter($query);
        // dd($query->toSql());
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('stok.statusaktif_id', '=', $statusaktif->id);
        }
        if (($statusreuse == 'REUSE') || ($pg->id == $penerimaanstok_id) || ($korv->id == $penerimaanstok_id)) {

            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS REUSE')
                ->where('text', '=', 'REUSE')
                ->first();



            $query->where('stok.statusreuse_id', '=', $statusaktif->id);
        }

        if (($dari != 'index') &&($po->text != $penerimaanstok_id)) {
            $query->whereNotNull('stok.namaterpusat');
        }
        if ($approveReuse) {
            $query->join('approvalstokreuse', 'stok.id', 'approvalstokreuse.stok_id');
        }

        if ($kelompok != '') {
            $query->where('stok.kelompok_id', '=', $kelompok);
        }
        if ($KelompokId_stok != '') {
            if (($pg->text == $penerimaanstok_id) || ($spk->text == $pengeluaranstok_id)) {
                $query->join(db::raw($tempdatastokpg . " as stokpg"),'stok.id','stokpg.stok_id');
                // $query->where('stok.kelompok_id', '=', $KelompokId_stok);
            }
        }
        if ($penerimaanstokheader_nobukti) {
            if ($retur->text == $pengeluaranstok_id) {
            // if ($spb->text == $penerimaanstok_id || $retur->text == $pengeluaranstok_id) {
                $query->leftJoin('penerimaanstokdetail', 'stok.id', 'penerimaanstokdetail.stok_id')
                    ->where('penerimaanstokdetail.nobukti', $penerimaanstokheader_nobukti);
            }
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->paginate($query);
        // dd($query->toSql());
        $data = $query->get();

        return $data;
    }

    public function getSupplierStok($supplier_id) {
        $this->setRequestParameters();
        $tempSetStok = '##tempSetStok' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempSetStok, function ($table) {
            $table->integer('id')->nullable();
            $table->integer('jenistrado_id')->nullable();
            $table->integer('kelompok_id')->nullable();
            $table->integer('subkelompok_id')->nullable();
            $table->integer('kategori_id')->nullable();
            $table->integer('merk_id')->nullable();
            $table->integer('satuan_id')->nullable();
            $table->string('namastok',200)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->integer('statusreuse')->length(11)->nullable();
            $table->integer('statusban')->length(11)->nullable();
            $table->integer('statusservicerutin')->length(11)->nullable();
            $table->double('qtymin',15,2)->nullable();
            $table->double('qtymax',15,2)->nullable();
            $table->double('hargabelimin',15,2)->nullable();
            $table->double('hargabelimax',15,2)->nullable();
            $table->double('vulkanisirawal',15,2)->nullable();
            $table->double('totalvulkanisir',15,2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->longText('gambar')->nullable();
            $table->longText('namaterpusat')->nullable();
            $table->integer('statusapprovaltanpaklaim')->Length(11)->nullable();
            $table->string('userapprovaltanpaklaim', 50)->nullable();
            $table->date('tglapprovaltanpaklaim')->nullable();
            $table->longText('info')->nullable();
            $table->integer('tas_id')->nullable();
            $table->string('editing_by',50)->nullable();
            $table->dateTime('editing_at')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->integer('statuspembulatanlebih2decimal')->Length(11)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

        });
        $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();

        $query = DB::table('stok')
        ->distinct()
        ->select(
            'stok.id',
            'stok.jenistrado_id',
            'stok.kelompok_id',
            'stok.subkelompok_id',
            'stok.kategori_id',
            'stok.merk_id',
            'stok.satuan_id',
            'stok.namastok',
            'stok.statusaktif',
            'stok.statusreuse',
            'stok.statusban',
            'stok.statusservicerutin',
            'stok.qtymin',
            'stok.qtymax',
            'stok.hargabelimin',
            'stok.hargabelimax',
            'stok.vulkanisirawal',
            'stok.totalvulkanisir',
            'stok.keterangan',
            'stok.gambar',
            'stok.namaterpusat',
            'stok.statusapprovaltanpaklaim',
            'stok.userapprovaltanpaklaim',
            'stok.tglapprovaltanpaklaim',
            'stok.info',
            'stok.tas_id',
            'stok.editing_by',
            'stok.editing_at',
            'stok.modifiedby',
            'stok.statuspembulatanlebih2decimal',
            'stok.created_at',
            'stok.updated_at',
        )
        ->leftJoin('penerimaanstokdetail', 'penerimaanstokdetail.stok_id', '=', 'stok.id')
        ->leftJoin('penerimaanstokheader', 'penerimaanstokheader.id', '=', 'penerimaanstokdetail.penerimaanstokheader_id')
        ->where('penerimaanstokheader.penerimaanstok_id', $spb->text)
        ->where('penerimaanstokheader.supplier_id', $supplier_id);

        DB::table($tempSetStok)->insertUsing(["id","jenistrado_id","kelompok_id","subkelompok_id","kategori_id","merk_id","satuan_id","namastok","statusaktif","statusreuse","statusban","statusservicerutin","qtymin","qtymax","hargabelimin","hargabelimax","vulkanisirawal","totalvulkanisir","keterangan","gambar","namaterpusat","statusapprovaltanpaklaim","userapprovaltanpaklaim","tglapprovaltanpaklaim","info","tas_id","editing_by","editing_at","modifiedby","statuspembulatanlebih2decimal","created_at","updated_at"], $query);
        $query = DB::table($tempSetStok)->from(db::raw("$tempSetStok stok with (readuncommitted)"))
        ->select(
            'stok.id',
            'stok.namastok',
            'parameter.memo as statusaktif',
            'service.memo as statusservicerutin',
            'service.text as servicerutin_text',
            'stok.qtymin',
            'stok.qtymax',
            'stok.keterangan',
            'stok.gambar',
            'stok.namaterpusat',
            'statusapprovaltanpaklaim.memo as statusapprovaltanpaklaim',                    
            'statusban.text as statusban',
            'stok.statusban as statusban_id',
            'statusreuse.memo as statusreuse',
            'stok.modifiedby',
            'stok.totalvulkanisir',
            'stok.vulkanisirawal',
            'jenistrado.keterangan as jenistrado',
            'satuan.satuan as satuan',
            'kelompok.kodekelompok as kelompok',
            'subkelompok.kodesubkelompok as subkelompok',
            'kategori.kodekategori as kategori',
            'merk.keterangan as merk',
            'stok.created_at',
            'stok.updated_at',
           
            'stok.statusaktif as statusaktif_id',
            'stok.statusreuse as statusreuse_id',
        )
        ->leftJoin('jenistrado', 'stok.jenistrado_id', 'jenistrado.id')
        ->leftJoin('satuan', 'stok.satuan_id', 'satuan.id')
        ->leftJoin('kelompok', 'stok.kelompok_id', 'kelompok.id')
        ->leftJoin('subkelompok', 'stok.subkelompok_id', 'subkelompok.id')
        ->leftJoin('kategori', 'stok.kategori_id', 'kategori.id')
        ->leftJoin('parameter', 'stok.statusaktif', 'parameter.id')
        ->leftJoin(DB::raw("parameter as service with (readuncommitted)"), 'stok.statusservicerutin', 'service.id')
        ->leftJoin(DB::raw("parameter as statusban with (readuncommitted)"), 'stok.statusban', 'statusban.id')
        ->leftJoin(DB::raw("parameter as statusreuse with (readuncommitted)"), 'stok.statusreuse', 'statusreuse.id')
        ->leftJoin(DB::raw("parameter as statusapprovaltanpaklaim with (readuncommitted)"), 'stok.statusapprovaltanpaklaim', 'statusapprovaltanpaklaim.id')             
        ->leftJoin('merk', 'stok.merk_id', 'merk.id');
        
        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);
        $data = $query->get();

        return $data;
    }
    
    public function getTNLForKlaim()
    {
        // $server = config('app.url_tnl');
        // $getToken = Http::withHeaders([
        //     'Content-Type' => 'application/json',
        //     'Accept' => 'application/json'
        // ])
        //     ->post($server . 'token', [
        //         'user' => 'ADMIN',
        //         'password' => getenv('PASSWORD_TNL'),
        //         'ipclient' => '',
        //         'ipserver' => '',
        //         'latitude' => '',
        //         'longitude' => '',
        //         'browser' => '',
        //         'os' => '',
        //     ]);
        // $access_token = json_decode($getToken, TRUE)['access_token'];

        // $getStok = Http::withHeaders([
        //     'Accept' => 'application/json',
        //     'Authorization' => 'Bearer ' . $access_token,
        //     'Content-Type' => 'application/json',
        // ])

        //     ->get($server . "stok?limit=0&aktif=AKTIF&isLookup=true");

        // $data = $getStok->json()['data'];

        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'StokTruckingController';

        $temtabel = 'tempstoktnl' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

        $querydata = DB::table('listtemporarytabel')->from(
            DB::raw("listtemporarytabel a with (readuncommitted)")
        )
            ->select(
                'id',
                'class',
                'namatabel',
            )
            ->where('class', '=', $class)
            ->where('modifiedby', '=', $user)
            ->first();

        if (isset($querydata)) {
            Schema::dropIfExists($querydata->namatabel);
            DB::table('listtemporarytabel')->where('id', $querydata->id)->delete();
        }
        DB::table('listtemporarytabel')->insert(
            [
                'class' => $class,
                'namatabel' => $temtabel,
                'modifiedby' => $user,
                'created_at' => date('Y/m/d H:i:s'),
                'updated_at' => date('Y/m/d H:i:s'),
            ]
        );
        Schema::create($temtabel, function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('namastok', 300)->nullable();
        });
        $query = DB::connection('srvtnl')->table('stok')->from(DB::raw("stok with (Readuncommitted)"))->select(
            'stok.id',
            'stok.namastok',
        )->where('stok.statusaktif', '=', 1)->get();

        foreach ($query as $row) {
            DB::table($temtabel)->insert([
                'id' => $row->id,
                'namastok' => $row->namastok,

            ]);
            // DB::table($temtabel)->insert($row);
        }
        
        return $temtabel;
    }

    public function showTNLForKlaim($id)
    {
        $server = config('app.url_tnl');
        $getToken = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])
            ->post($server . 'token', [
                'user' => 'ADMIN',
                'password' => config('app.password_tnl'),
                'ipclient' => '',
                'ipserver' => '',
                'latitude' => '',
                'longitude' => '',
                'browser' => '',
                'os' => '',
            ]);
        $access_token = json_decode($getToken, TRUE)['access_token'];

        $getStok = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json',
        ])
        ->get($server . "stok/".$id);

        $data = $getStok->json()['data'];

        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'StokTruckingControllerShow';

        $temtabel = 'tempstokshow' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

        $querydata = DB::table('listtemporarytabel')->from(
            DB::raw("listtemporarytabel a with (readuncommitted)")
        )
            ->select(
                'id',
                'class',
                'namatabel',
            )
            ->where('class', '=', $class)
            ->where('modifiedby', '=', $user)
            ->first();

        if (isset($querydata)) {
            Schema::dropIfExists($querydata->namatabel);
            DB::table('listtemporarytabel')->where('id', $querydata->id)->delete();
        }
        DB::table('listtemporarytabel')->insert(
            [
                'class' => $class,
                'namatabel' => $temtabel,
                'modifiedby' => $user,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        );
        Schema::create($temtabel, function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('namastok', 300)->nullable();
            $table->string('statusaktif', 300)->nullable();
            $table->double('qtymin', 15, 2)->nullable();
            $table->double('qtymax', 15, 2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->longText('gambar')->nullable();
            $table->string('namaterpusat', 300)->nullable();
            $table->string('modifiedby', 300)->nullable();
            $table->integer('jenistrado_id')->nullable();
            $table->integer('kelompok_id')->nullable();
            $table->double('totalvulkanisir', 15, 2)->nullable();
            $table->integer('statusreuse')->nullable();
            $table->integer('statusservicerutin')->nullable();
            $table->integer('subkelompok_id')->nullable();
            $table->integer('satuan_id')->nullable();
            $table->integer('kategori_id')->nullable();
            $table->integer('merk_id')->nullable();
            $table->integer('statusban')->nullable();
            $table->string('jenistrado', 300)->nullable();
            $table->string('kelompok', 300)->nullable();
            $table->string('subkelompok', 300)->nullable();
            $table->string('satuan', 300)->nullable();
            $table->string('kategori', 300)->nullable();
            $table->string('merk', 300)->nullable();
            $table->string('statusaktifnama', 300)->nullable();
            $table->string('statusreusenama', 300)->nullable();
            $table->string('statusbannama', 300)->nullable();
            $table->string('statusservicerutinnama', 300)->nullable();
        });

            DB::table($temtabel)->insert($data);


        return $temtabel;
    }

    public function showTnlForSPB($id){
        $query = DB::connection('srvtnl')->table('stok')->from(DB::raw("stok with (Readuncommitted)"))->select(
            'stok.id',
            'stok.jenistrado_id',
            'stok.kelompok_id',
            'stok.subkelompok_id',
            'stok.kategori_id',
            'stok.merk_id',
            'stok.satuan_id',
            'stok.namastok',
            'stok.statusaktif',
            'stok.statusreuse',
            'stok.statusban',
            'stok.statusservicerutin',
            'stok.qtymin',
            'stok.qtymax',
            'stok.hargabelimin',
            'stok.hargabelimax',
            'stok.vulkanisirawal',
            'stok.totalvulkanisir',
            'stok.keterangan',
            'stok.gambar',
            'stok.namaterpusat',
            'stok.statusapprovaltanpaklaim',
            'stok.userapprovaltanpaklaim',
            'stok.tglapprovaltanpaklaim',
            'stok.info',
            'stok.tas_id',
            'stok.editing_by',
            'stok.editing_at',
            'stok.modifiedby',
            'stok.statuspembulatanlebih2decimal',
        )->where('stok.tas_id', '=', $id)->first();
       return $query;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->nullable();
            $table->string('statusaktifnama')->nullable();
            $table->unsignedBigInteger('statusreuse')->nullable();
            $table->string('statusreusenama')->nullable();
            $table->unsignedBigInteger('statusban')->nullable();
            $table->string('statusbannama')->nullable();
        });

        $statusaktif = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id',
                'text'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('default', '=', 'YA')
            ->first();

        $statusreuse = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id',
                'text'
            )
            ->where('grp', '=', 'STATUS REUSE')
            ->where('subgrp', '=', 'STATUS REUSE')
            ->where('default', '=', 'YA')
            ->first();

        $statusban = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id',
                'text'
            )
            ->where('grp', '=', 'STATUS BAN')
            ->where('subgrp', '=', 'STATUS BAN')
            ->where('default', '=', 'YA')
            ->first();

        DB::table($tempdefault)->insert([
            "statusaktif" => $statusaktif->id,
            "statusaktifnama" => $statusaktif->text,
            "statusreuse" => $statusreuse->id,
            "statusreusenama" => $statusreuse->text,
            "statusban" => $statusban->id,
            "statusbannama" => $statusban->text
        ]);

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusaktifnama',
                'statusreuse',
                'statusreusenama',
                'statusban',
                'statusbannama',
            );

        $data = $query->first();
        // dd($data);
        return $data;
    }
    public function findAll($id)
    {
        $data = DB::table('stok')->select(
            'stok.id',
            'stok.namastok',
            'stok.statusaktif',
            'stok.qtymin',
            'stok.qtymax',
            'stok.keterangan',
            'stok.gambar',
            'stok.namaterpusat',
            'stok.modifiedby',
            'stok.jenistrado_id',
            'stok.kelompok_id',
            'stok.totalvulkanisir',
            'stok.statusreuse',
            'stok.statusservicerutin',
            'stok.subkelompok_id',
            'stok.satuan_id',
            'stok.kategori_id',
            'stok.merk_id',
            'stok.statusban',
            'jenistrado.keterangan as jenistrado',
            'kelompok.kodekelompok as kelompok',
            'subkelompok.kodesubkelompok as subkelompok',
            'satuan.satuan as satuan',
            'kategori.kodekategori as kategori',
            'merk.keterangan as merk',
            'statusaktif.text as statusaktifnama',
            'statusreuse.text as statusreusenama',
            'statusban.text as statusbannama',
            'statusservicerutin.text as statusservicerutinnama',
        )
            ->leftJoin('jenistrado', 'stok.jenistrado_id', 'jenistrado.id')
            ->leftJoin('kelompok', 'stok.kelompok_id', 'kelompok.id')
            ->leftJoin('subkelompok', 'stok.subkelompok_id', 'subkelompok.id')
            ->leftJoin('satuan', 'stok.satuan_id', 'satuan.id')
            ->leftJoin('kategori', 'stok.kategori_id', 'kategori.id')
            ->leftJoin('merk', 'stok.merk_id', 'merk.id')
            ->leftJoin('parameter as statusaktif', 'stok.statusaktif', '=', 'statusaktif.id')
            ->leftJoin('parameter as statusreuse', 'stok.statusreuse', '=', 'statusreuse.id')
            ->leftJoin('parameter as statusban', 'stok.statusban', '=', 'statusban.id')
            ->leftJoin('parameter as statusservicerutin', 'stok.statusservicerutin', '=', 'statusservicerutin.id')
            ->where('stok.id', $id)
            ->first();

        return $data;
    }

    public function getGambarName($id)
    {
        $query = DB::table("stok")->from(DB::raw("stok with (readuncommitted)"))
            ->select('gambar')
            ->where('id', $id)
            ->first();

        return $query;
    }

    public function createTemp(string $modelTable)
    {
        //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('namastok',3000)->nullable();
            $table->longtext('statusaktif')->nullable();
            $table->longtext('statusservicerutin')->nullable();
            $table->string('servicerutin_text',1000)->nullable();
            $table->double('qtymin',15,2)->nullable();
            $table->double('qtymax',15,2)->nullable();
            $table->longtext('keterangan')->nullable();
            $table->longtext('gambar')->nullable();
            $table->string('namaterpusat',3000)->nullable();
            $table->longtext('statusapprovaltanpaklaim')->nullable();
            $table->longtext('statusban')->nullable();
            $table->bigInteger('statusban_id')->nullable();
            $table->longtext('statusreuse')->nullable();
            $table->string('modifiedby',3000)->nullable();
            $table->double('totalvulkanisir',15,2)->nullable();
            $table->double('vulkanisirawal',15,2)->nullable();
            $table->string('jenistrado',3000)->nullable();
            $table->string('satuan',3000)->nullable();
            $table->string('kelompok',3000)->nullable();
            $table->string('subkelompok',3000)->nullable();
            $table->string('kategori',3000)->nullable();
            $table->string('merk',3000)->nullable();
            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();
            $table->string('judulLaporan',3000)->nullable();
            $table->string('judul',3000)->nullable();
            $table->string('tglcetak',3000)->nullable();
            $table->string('usercetak',3000)->nullable();
            $table->double('umuraki',15,2)->nullable();
            $table->double('vulkan',15,2)->nullable();
            $table->longtext('penerimaanstokdetail_keterangan')->nullable();
            $table->double('penerimaanstokdetail_qty',15,2)->nullable();
            $table->double('penerimaanstokdetail_harga',15,2)->nullable();
            $table->double('penerimaanstokdetail_total',15,2)->nullable();
            $table->bigInteger('statusaktif_id')->nullable();
            $table->bigInteger('statusreuse_id')->nullable();
            $table->bigInteger('kelompok_id')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns(null);
        $query = $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing([
            'stok.id',
            'stok.namastok',
            'stok.statusaktif',
            'stok.statusservicerutin',
            'stok.servicerutin_text',
            'stok.qtymin',
            'stok.qtymax',
            'stok.keterangan',
            'stok.gambar',
            'stok.namaterpusat',
            'stok.statusapprovaltanpaklaim',
            'stok.statusban',
            'stok.statusban_id',
            'stok.statusreuse',
            'stok.modifiedby',
            'stok.totalvulkanisir',
            'stok.vulkanisirawal',
            'stok.jenistrado',
            'stok.satuan',
            'stok.kelompok',
            'stok.subkelompok',
            'stok.kategori',
            'stok.merk',
            'stok.created_at',
            'stok.updated_at',
            'stok.umuraki',
            'stok.vulkan',
            'stok.penerimaanstokdetail_keterangan',
            'stok.penerimaanstokdetail_qty',
            'stok.penerimaanstokdetail_harga',
            'stok.penerimaanstokdetail_total',
            'stok.kelompok_id',
        ], $models);

        return  $temp;
    }


    public function selectColumns($query)
    {
        $tempumuraki = '##tempumuraki' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempumuraki, function ($table) {
            $table->Integer('stok_id')->nullable();
            $table->integer('jumlahhari')->nullable();
            $table->date('tglawal')->nullable();
        });

        DB::table($tempumuraki)->insertUsing([
            'stok_id',
            'jumlahhari',
            'tglawal',
        ], (new SaldoUmurAki())->getallstok());

        $tempumuraki2 = '##tempumuraki2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempumuraki2, function ($table) {
            $table->Integer('stok_id')->nullable();
            $table->integer('jumlahhari')->nullable();
            $table->date('tglawal')->nullable();
        });

        $queryaki = db::table($tempumuraki)->from(db::raw($tempumuraki . " a "))
            ->select(
                'a.stok_id',
                db::raw("max(a.jumlahhari) as jumlahhari"),
                db::raw("max(a.tglawal) as tglawal"),
            )
            ->groupby('a.stok_id');

        DB::table($tempumuraki2)->insertUsing([
            'stok_id',
            'jumlahhari',
            'tglawal',
        ],  $queryaki);

        //update total vulkanisir

        $querytgl = date('Y/m/d');

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
        $temtabel = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temtabel, function (Blueprint $table) {
            $table->bigInteger('id')->nullable();
            $table->string('namastok',3000)->nullable();
            $table->longtext('statusaktif')->nullable();
            $table->longtext('statusservicerutin')->nullable();
            $table->string('servicerutin_text',1000)->nullable();
            $table->double('qtymin',15,2)->nullable();
            $table->double('qtymax',15,2)->nullable();
            $table->longtext('keterangan')->nullable();
            $table->longtext('gambar')->nullable();
            $table->string('namaterpusat',3000)->nullable();
            $table->longtext('statusapprovaltanpaklaim')->nullable();
            $table->longtext('statusban')->nullable();
            $table->bigInteger('statusban_id')->nullable();
            $table->longtext('statusreuse')->nullable();
            $table->string('modifiedby',3000)->nullable();
            $table->double('totalvulkanisir',15,2)->nullable();
            $table->double('vulkanisirawal',15,2)->nullable();
            $table->string('jenistrado',3000)->nullable();
            $table->string('satuan',3000)->nullable();
            $table->string('kelompok',3000)->nullable();
            $table->string('subkelompok',3000)->nullable();
            $table->string('kategori',3000)->nullable();
            $table->string('merk',3000)->nullable();
            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();
            $table->string('judulLaporan',3000)->nullable();
            $table->string('judul',3000)->nullable();
            $table->string('tglcetak',3000)->nullable();
            $table->string('usercetak',3000)->nullable();
            $table->double('umuraki',15,2)->nullable();
            $table->double('vulkan',15,2)->nullable();
            $table->longtext('penerimaanstokdetail_keterangan')->nullable();
            $table->double('penerimaanstokdetail_qty',15,2)->nullable();
            $table->double('penerimaanstokdetail_harga',15,2)->nullable();
            $table->double('penerimaanstokdetail_total',15,2)->nullable();
            $table->bigInteger('statusaktif_id')->nullable();
            $table->bigInteger('statusreuse_id')->nullable();
            $table->bigInteger('kelompok_id')->nullable();

        });
        $query = DB::table($this->table)->select(
            'stok.id',
            'stok.namastok',
            'parameter.memo as statusaktif',
            'service.memo as statusservicerutin',
            'service.text as servicerutin_text',
            'stok.qtymin',
            'stok.qtymax',
            'stok.keterangan',
            'stok.gambar',
            'stok.namaterpusat',
            'statusapprovaltanpaklaim.memo as statusapprovaltanpaklaim',                    
            'statusban.text as statusban',
            'stok.statusban as statusban_id',
            'statusreuse.memo as statusreuse',
            'stok.modifiedby',
            'stok.totalvulkanisir',
            'stok.vulkanisirawal',
            'jenistrado.keterangan as jenistrado',
            'satuan.satuan as satuan',
            'kelompok.kodekelompok as kelompok',
            'subkelompok.kodesubkelompok as subkelompok',
            'kategori.kodekategori as kategori',
            'merk.keterangan as merk',
            'stok.created_at',
            'stok.updated_at',
            DB::raw("isnull(c1.jumlahhari,0) as umuraki"),
            DB::raw("isnull(d1.vulkan,0) as vulkan"),
            DB::raw("'' as penerimaanstokdetail_keterangan"),
            DB::raw("'' as penerimaanstokdetail_qty"),
            DB::raw("'' as penerimaanstokdetail_harga"),
            DB::raw("'' as penerimaanstokdetail_total"),
            'stok.statusaktif as statusaktif_id',
            'stok.statusreuse as statusreuse_id',
            'stok.kelompok_id as kelompok_id',
            
        )
            ->leftJoin('jenistrado', 'stok.jenistrado_id', 'jenistrado.id')
            ->leftJoin('satuan', 'stok.satuan_id', 'satuan.id')
            ->leftJoin('kelompok', 'stok.kelompok_id', 'kelompok.id')
            ->leftJoin('subkelompok', 'stok.subkelompok_id', 'subkelompok.id')
            ->leftJoin('kategori', 'stok.kategori_id', 'kategori.id')
            ->leftJoin('parameter', 'stok.statusaktif', 'parameter.id')
            ->leftJoin(DB::raw("parameter as service with (readuncommitted)"), 'stok.statusservicerutin', 'service.id')
            ->leftJoin(DB::raw("parameter as statusban with (readuncommitted)"), 'stok.statusban', 'statusban.id')
            ->leftJoin(DB::raw("parameter as statusreuse with (readuncommitted)"), 'stok.statusreuse', 'statusreuse.id')
            ->leftJoin(DB::raw("parameter as statusapprovaltanpaklaim with (readuncommitted)"), 'stok.statusapprovaltanpaklaim', 'statusapprovaltanpaklaim.id')                    
            ->leftJoin('merk', 'stok.merk_id', 'merk.id')
            ->leftJoin(db::raw($tempvulkan . " d1"), "stok.id", "d1.stok_id")
            ->leftJoin(db::raw($tempumuraki2 . " c1"), "stok.id", "c1.stok_id");

            DB::table($temtabel)->insertUsing([
                'id',
                'namastok',
                'statusaktif',
                'statusservicerutin',
                'servicerutin_text',
                'qtymin',
                'qtymax',
                'keterangan',
                'gambar',
                'namaterpusat',
                'statusapprovaltanpaklaim',
                'statusban',
                'statusban_id',
                'statusreuse',
                'modifiedby',
                'totalvulkanisir',
                'vulkanisirawal',
                'jenistrado',
                'satuan',
                'kelompok',
                'subkelompok',
                'kategori',
                'merk',
                'created_at',
                'updated_at',
                'umuraki',
                'vulkan',
                'penerimaanstokdetail_keterangan',
                'penerimaanstokdetail_qty',
                'penerimaanstokdetail_harga',
                'penerimaanstokdetail_total',
                'statusaktif_id',
                'statusreuse_id',
                'kelompok_id',
            ], $query);

            $query = DB::table(DB::raw($temtabel))->from(
                DB::raw(DB::raw($temtabel) . " stok with (readuncommitted)")
            )
                ->select(
                    'stok.id',
                    'stok.namastok',
                    'stok.statusaktif',
                    'stok.statusservicerutin',
                    'stok.servicerutin_text',
                    'stok.qtymin',
                    'stok.qtymax',
                    'stok.keterangan',
                    'stok.gambar',
                    'stok.namaterpusat',
                    'stok.statusapprovaltanpaklaim',
                    'stok.statusban',
                    'stok.statusban_id',
                    'stok.statusreuse',
                    'stok.modifiedby',
                    'stok.totalvulkanisir',
                    'stok.vulkanisirawal',
                    'stok.jenistrado',
                    'stok.satuan',
                    'stok.kelompok',
                    'stok.subkelompok',
                    'stok.kategori',
                    'stok.merk',
                    'stok.created_at',
                    'stok.updated_at',
                    'stok.umuraki',
                    'stok.vulkan',
                    'stok.penerimaanstokdetail_keterangan',
                    'stok.penerimaanstokdetail_qty',
                    'stok.penerimaanstokdetail_harga',
                    'stok.penerimaanstokdetail_total',
                    'stok.kelompok_id',
                );
    
    
    
            return $query;
    }


    // public function sort($query)
    // {
    //     if ($this->params['sortIndex'] == 'jenistrado') {
    //         return $query->orderBy('jenistrado.keterangan', $this->params['sortOrder']);
    //     } else if ($this->params['sortIndex'] == 'kelompok') {
    //         return $query->orderBy('kelompok.kodekelompok', $this->params['sortOrder']);
    //     } else if ($this->params['sortIndex'] == 'subkelompok') {
    //         return $query->orderBy('subkelompok.kodesubkelompok', $this->params['sortOrder']);
    //     } else if ($this->params['sortIndex'] == 'kategori') {
    //         return $query->orderBy('kategori.kodekategori', $this->params['sortOrder']);
    //     } else if ($this->params['sortIndex'] == 'merk') {
    //         return $query->orderBy('merk.keterangan', $this->params['sortOrder']);
    //     } else if ($this->params['sortIndex'] == 'umuraki') {
    //         return $query->orderBy('c1.jumlahhari', $this->params['sortOrder']);
    //     } else if ($this->params['sortIndex'] == 'vulkan') {
    //         return $query->orderBy('d1.vulkan', $this->params['sortOrder']);
    //     } else {
    //         return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    //     }
    // }

    public function sort($query)
    {
            return $query->orderBy('stok.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    // public function filter($query, $relationFields = [])
    // {
    //     if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
    //         switch ($this->params['filters']['groupOp']) {
    //             case "AND":
    //                 foreach ($this->params['filters']['rules'] as $index => $filters) {
    //                     if ($filters['field'] == 'statusservicerutin') {
    //                         $query = $query->where('service.text', '=', $filters['data']);
    //                     } else if ($filters['field'] == 'check') {
                            
    //                     } else if ($filters['field'] == 'statusaktif') {
    //                         $query = $query->where('parameter.text', '=', $filters['data']);
    //                     } else if ($filters['field'] == 'statusreuse') {
    //                         $query = $query->where('statusreuse.text', '=', $filters['data']);
    //                     } else if ($filters['field'] == 'jenistrado') {
    //                         $query = $query->whereRaw('jenistrado.keterangan LIKE' . "'%$filters[data]%'");
    //                     } else if ($filters['field'] == 'kelompok') {
    //                         $query = $query->whereRaw('kelompok.kodekelompok LIKE' . "'%$filters[data]%'");
    //                     } else if ($filters['field'] == 'subkelompok') {
    //                         $query = $query->whereRaw('subkelompok.kodesubkelompok LIKE' . "'%$filters[data]%'");
    //                     } else if ($filters['field'] == 'kategori') {
    //                         $query = $query->whereRaw('kategori.kodekategori LIKE' . "'%$filters[data]%'");
    //                     } else if ($filters['field'] == 'merk') {
    //                         $query = $query->whereRaw('merk.keterangan LIKE' . "'%$filters[data]%'");
    //                     } else if ($filters['field'] == 'umuraki') {
    //                         $query = $query->whereRaw('c1.jumlahhari LIKE' . "'%$filters[data]%'");
    //                     } else if ($filters['field'] == 'vulkan') {
    //                         $query = $query->whereRaw('d1.vulkan LIKE' . "'%$filters[data]%'");
    //                     } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
    //                         $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
    //                     } else {
    //                         // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
    //                         $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
    //                     }
    //                 }

    //                 break;
    //             case "OR":
    //                 $query->where(function ($query) {
    //                     foreach ($this->params['filters']['rules'] as $index => $filters) {
    //                         if ($filters['field'] == 'statusservicerutin') {
    //                             $query = $query->orWhere('service.text', '=', $filters['data']);
    //                         } else if ($filters['field'] == 'check') {
                                
    //                         } else if ($filters['field'] == 'jenistrado') {
    //                             $query = $query->orWhereRaw('jenistrado.keterangan LIKE ' . "'%$filters[data]%'");
    //                         } else if ($filters['field'] == 'statusaktif') {
    //                             $query = $query->orWhereRaw('parameter.text LIKE ' . "'%$filters[data]%'");
    //                         } else if ($filters['field'] == 'statusreuse') {
    //                             $query = $query->orWhereRaw('statusreuse.text LIKE ' . "'%$filters[data]%'");
    //                         } else if ($filters['field'] == 'kelompok') {
    //                             $query = $query->orWhereRaw('kelompok.kodekelompok LIKE ' . "'%$filters[data]%'");
    //                         } else if ($filters['field'] == 'subkelompok') {
    //                             $query = $query->orWhereRaw('subkelompok.kodesubkelompok LIKE ' . "'%$filters[data]%'");
    //                         } else if ($filters['field'] == 'kategori') {
    //                             $query = $query->orWhereRaw('kategori.kodekategori LIKE ' . "'%$filters[data]%'");
    //                         } else if ($filters['field'] == 'merk') {
    //                             $query = $query->orWhereRaw('merk.keterangan LIKE ' . "'%$filters[data]%'");
    //                         } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
    //                             $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
    //                         } else if ($filters['field'] == 'umuraki') {
    //                             $query = $query->orwhereRaw('c1.jumlahhari LIKE' . "'%$filters[data]%'");
    //                         } else if ($filters['field'] == 'vulkan') {
    //                             $query = $query->orwhereRaw('d1.vulkan LIKE' . "'%$filters[data]%'");
    //                         } else {
    //                             // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
    //                             $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
    //                         }
    //                     }
    //                 });

    //                 break;
    //             default:

    //                 break;
    //         }

    //         $this->totalRows = $query->count();
    //         $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
    //     }

    //     return $query;
    // }

    
    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'check') {
                        } else if ($filters['field'] == 'statusreuse') {
                            if ($filters['data']) {
                                $query = $query->whereRaw('stok.statusreuse_id =' . "'$filters[data]'");
                            }
                        } else {
                            $query = $query->whereRaw("stok.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'check') {
                            // } else if ($filters['field'] == 'statusreuse') {
                            //     if ($filters['data']) {
                            //         $query = $query->orWhereRaw('stok.statusreuse_id =' . "'$filters[data]'");
                            //     }
                            } else {
                                $query = $query->OrwhereRaw( "stok.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    });

                    break;
                default:

                    break;
            }

            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }

        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function processStore(array $data, Stok $stok): Stok
    {
        $kelompokBan = Kelompok::where("kodekelompok","BAN")->first();
        $kelompokAki = Kelompok::where("kodekelompok","AKI")->first();

        // $stok = new stok();
        $stok->keterangan = $data['keterangan'];
        $stok->namastok = $data['namastok'];
        $stok->statusaktif = $data['statusaktif'];
        $stok->kelompok_id = $data['kelompok_id'];
        $stok->subkelompok_id = $data['subkelompok_id'];
        $stok->kategori_id = $data['kategori_id'];
        $stok->merk_id = $data['merk_id'] ?? 0;
        $stok->jenistrado_id = $data['jenistrado_id'] ?? 0;
        $stok->keterangan = $data['keterangan'] ?? '';
        $stok->qtymin = $data['qtymin'] ?? 0;
        $stok->qtymax = $data['qtymax'] ?? 0;
        $stok->statusreuse = $data['statusreuse'];
        $stok->statusban = $data['statusban'];
        $stok->satuan_id = $data['satuan_id'];
        $stok->statusservicerutin = $data['statusservicerutin'];
        $stok->vulkanisirawal = $data['vulkanisirawal'];
        $stok->hargabelimin = $data['hargabelimin'];
        $stok->hargabelimax = $data['hargabelimax'];
        $stok->tas_id = $data['tas_id'];
        $stok->modifiedby = auth('api')->user()->name;
        $stok->info = html_entity_decode(request()->info);
        $stok->tas_id = $data['tas_id'] ?? '';        
        if ($data['gambar']) {
            if(request()->from != '' || array_key_exists('from',$data)  ){
                $stok->gambar = $this->storeFilesBase64($data['gambar'], 'stok');
            }else{
                $stok->gambar = $this->storeFiles($data['gambar'], 'stok');
            }
        } else {
            $stok->gambar = '';
        }

        if (!$stok->save()) {
            throw new \Exception("Error storing stok.");
        }

        if( ($data['kelompok_id'] == $kelompokBan->id) || ($data['kelompok_id'] == $kelompokAki->id) ){
            $stok->namaterpusat = $stok->namastok;
            $stok->save();
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($stok->getTable()),
            'postingdari' => 'ENTRY STOK',
            'idtrans' => $stok->id,
            'nobuktitrans' => $stok->id,
            'aksi' => 'ENTRY',
            'datajson' => $stok->toArray(),
            'modifiedby' => $stok->modifiedby
        ]);


        return $stok;
    }
    public function processUpdate(Stok $stok, array $data): Stok
    {
        $kelompokBan = Kelompok::where("kodekelompok","BAN")->first();
        $kelompokAki = Kelompok::where("kodekelompok","AKI")->first();

        $stok->keterangan = $data['keterangan'];
        $stok->namastok = $data['namastok'];
        $stok->statusaktif = $data['statusaktif'];
        $stok->kelompok_id = $data['kelompok_id'];
        $stok->subkelompok_id = $data['subkelompok_id'];
        $stok->kategori_id = $data['kategori_id'];
        $stok->merk_id =  $data['merk_id'] ?? 0;
        $stok->jenistrado_id = $data['jenistrado_id'] ?? 0;
        $stok->keterangan = $data['keterangan'] ?? '';
        $stok->qtymin = $data['qtymin'] ?? 0;
        $stok->qtymax = $data['qtymax'] ?? 0;
        $stok->statusban = $data['statusban'];
        $stok->satuan_id = $data['satuan_id'];
        $stok->statusservicerutin = $data['statusservicerutin'];
        $stok->hargabelimin = $data['hargabelimin'];
        $stok->hargabelimax = $data['hargabelimax'];
        $stok->modifiedby = auth('api')->user()->name;
        $stok->info = html_entity_decode(request()->info);

        $statusPakai = $this->cekvalidasihapus($stok->id);
        if (!$statusPakai['kondisi']) {
            $stok->statusreuse = $data['statusreuse'];
            $stok->vulkanisirawal = $data['vulkanisirawal'];
        }

        $this->deleteFiles($stok,array_key_exists('from',$data));
        if ($data['gambar']) {
            if(request()->from != ''|| array_key_exists('from',$data) ){
                $stok->gambar = $this->storeFilesBase64($data['gambar'], 'stok');
            }else{
                $stok->gambar = $this->storeFiles($data['gambar'], 'stok');
            }
        } else {
            $stok->gambar = '';
        }
        if (!$stok->save()) {
            throw new \Exception("Error updating stok.");
        }
        if( ($data['kelompok_id'] == $kelompokBan->id) || ($data['kelompok_id'] == $kelompokAki->id) ){
            $stok->namaterpusat = $stok->namastok;
            $stok->save();
        }


        (new LogTrail())->processStore([
            'namatabel' => strtoupper($stok->getTable()),
            'postingdari' => 'EDIT STOK',
            'idtrans' => $stok->id,
            'nobuktitrans' => $stok->id,
            'aksi' => 'ENTRY',
            'datajson' => $stok->toArray(),
            'modifiedby' => $stok->modifiedby
        ]);

        return $stok;
    }

    public function processDestroy(Stok $stok): Stok
    {
        // $stok = new Stok;
        $stok = $stok->lockAndDestroy($stok->id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($stok->getTable()),
            'postingdari' => 'DELETE STOK',
            'idtrans' => $stok->id,
            'nobuktitrans' => $stok->id,
            'aksi' => 'DELETE',
            'datajson' => $stok->toArray(),
            'modifiedby' => $stok->modifiedby
        ]);

        return $stok;
    }

    public function processApprovalklaim($data)
    {
        $statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();
        for ($i = 0; $i < count($data['id']); $i++) {
            $stok = Stok::find($data['id'][$i]);
            if ($stok->statusapprovaltanpaklaim == $statusApproval->id) {
                $stok->statusapprovaltanpaklaim = $statusNonApproval->id;
            } else {
                $stok->statusapprovaltanpaklaim = $statusApproval->id;
            }
    
            $stok->tglapprovaltanpaklaim = date('Y-m-d', time());
            $stok->userapprovaltanpaklaim = auth('api')->user()->name;
    
            if ($stok->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($stok->getTable()),
                    'postingdari' => 'UN/APPROVE STOK TANPA KALIM',
                    'idtrans' => $stok->id,
                    'nobuktitrans' => $stok->id,
                    'aksi' => 'UN/APPROVE',
                    'datajson' => $stok->toArray(),
                    'modifiedby' => $stok->modifiedby
                ]);
    
                
            }
        }
        return $stok;
    }
    
    public function processApprovalReuse($data)
    {
        $statusReuse = DB::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))->select('a.id')->where('grp', 'STATUS REUSE')->where('subgrp', 'STATUS REUSE')->where('text', 'REUSE')->first();
        $statusNonReuse = DB::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))->select('a.id')->where('grp', 'STATUS REUSE')->where('subgrp', 'STATUS REUSE')->where('text', 'NON REUSE')->first();
        for ($i = 0; $i < count($data['id']); $i++) {
            $stok = Stok::find($data['id'][$i]);
            if ($stok->statusreuse == $statusReuse->id) {
                $stok->statusreuse = $statusNonReuse->id;
            } else {
                $stok->statusreuse = $statusReuse->id;
                (new ApprovalStokReuse)->processStokReuseStore($stok);
            }
    
            // $stok->tglapprovalstatusreuse = date('Y-m-d', time());
            // $stok->userapprovalstatusreuse = auth('api')->user()->name;
    
            if ($stok->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($stok->getTable()),
                    'postingdari' => 'UN/APPROVE STOK Reuse',
                    'idtrans' => $stok->id,
                    'nobuktitrans' => $stok->id,
                    'aksi' => 'UN/APPROVE',
                    'datajson' => $stok->toArray(),
                    'modifiedby' => $stok->modifiedby
                ]);
    
                
            }
        }
        return $stok;
    }

    public function processApprovalnonaktif(array $data)
    {
        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $penerimaanStok = $this->where('id',$data['Id'][$i])->first();

            $penerimaanStok->statusaktif = $statusnonaktif->id;
            $penerimaanStok->modifiedby = auth('api')->user()->name;
            $penerimaanStok->info = html_entity_decode(request()->info);
            $aksi = $statusnonaktif->text;

            if (!$penerimaanStok->save()) {
                throw new \Exception("Error update service in header.");
            }
            (new LogTrail())->processStore([
                'namatabel' => strtoupper($penerimaanStok->getTable()),
                'postingdari' => 'APPROVAL NON AKTIF PENEIRMAAN STOK',
                'idtrans' => $penerimaanStok->id,
                'nobuktitrans' => $penerimaanStok->id,
                'aksi' => $aksi,
                'datajson' => $penerimaanStok->toArray(),
                'modifiedby' => auth('api')->user()->user
            ]);

        }
        return $penerimaanStok;
    }

    public function getvulkanisir($id)
    {

        $tempvulkan = '##tempvulkan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempvulkan, function ($table) {
            $table->integer('stok_id')->nullable();
            $table->integer('vulkan')->nullable();
        });

        DB::table($tempvulkan)->insertUsing([
            'stok_id',
            'vulkan',
        ], $this->getVulkan());
        $queryvulkan = Stok::from(db::raw("stok a with (readuncommitted)"))
            ->select(
                'a.statusban',
                DB::raw("isnull(d1.vulkan,0) as vulkan"),
            )
            ->leftJoin(db::raw($tempvulkan . " d1"), "a.id", "d1.stok_id")
            ->where('a.id', $id)
            ->groupby('a.id', 'a.statusban','d1.vulkan')
            ->first();

            $totalvulkan = $queryvulkan->vulkan;
        return ['totalvulkan' => $totalvulkan, 'statusban' => $queryvulkan->statusban];
    }


    private function storeFiles(array $files, string $destinationFolder): string
    {
        $storedFiles = [];

        foreach ($files as $file) {
            $originalFileName = $file->hashName();
            $storedFile = Storage::putFileAs($destinationFolder, $file, $originalFileName);
            $resizedFiles = App::imageResize(storage_path("app/$destinationFolder/"), storage_path("app/$storedFile"), $originalFileName);

            $storedFiles[] = $originalFileName;
        }

        return json_encode($storedFiles);
    }

    private function storeFilesBase64(array $files, string $destinationFolder): string
    {
        $storedFiles = [];
        foreach ($files as $file) {
            $originalFileName = "$destinationFolder-" . hash('sha256', $file) . '.jpg';
            $imageData = base64_decode($file);
            $storedFile = Storage::disk('toTnl')->put( $destinationFolder . '/' . $originalFileName, $imageData);
            $pathDestination = Storage::disk('toTnl')->getDriver()->getAdapter()->applyPathPrefix(null);
            $resizedFiles = App::imageResize($pathDestination.$destinationFolder.'/', $pathDestination.$destinationFolder.'/'.$originalFileName, $originalFileName);

            $storedFiles[] = $originalFileName;
        }

        return json_encode($storedFiles);
    }


    private function deleteFiles(Stok $stok,$from = null)
    {
        $sizeTypes = ['', 'medium_', 'small_'];

        $relatedPhotoStok = [];
        $photoStok = json_decode($stok->gambar, true);
        if ($photoStok) {
            foreach ($photoStok as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoStok[] = "stok/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoStok);
            if ($from) {
                Storage::disk('toTnl')->delete($relatedPhotoStok);
            }
        }
    }

    public function processKonsolidasi($data)
    {
        if ($data['stok_id' . config('app.kode_cabang')] != '') {
            $query = DB::table('stok')->where('id', $data['stok_id' . config('app.kode_cabang')])->update([
                'namaterpusat' => strtoupper($data['namaterpusat']),
            ]);
        }
        if ($data['stok_id' . config('app.kode_cabang') . 'del'] != '') {
            $query = DB::table('stok')->where('id', $data['stok_id' . config('app.kode_cabang').'del'])->update([
                'namaterpusat' => '',
            ]);
        }
        $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->select('cabang.kodecabang')
            ->join(db::raw("parameter with (readuncommitted)"), 'cabang.id', 'parameter.text')
            ->where('parameter.grp', 'ID CABANG')
            ->first();

        unset($data['cekKoneksi'][$getCabang->kodecabang]);

        $this->saveToCabang($data);
    }

    public function saveToCabang($data)
    {
        $cekKoneksi = $data['cekKoneksi'];
        if (array_key_exists('TNL', $cekKoneksi)) {

            if ($data['stok_idjkttnl'] != '') {
                $accessTokenJktTnlStok = session('access_token_jkttnl_stok');
                $data['konsolidasi'] = true;
                if (!$accessTokenJktTnlStok) {
                    $postRequest = [
                        'user' => config('app.user_api'),
                        'password' => config('app.pass_api'),
                        'ipclient' => '',
                        'ipserver' => '',
                        'latitude' => '',
                        'longitude' => '',
                        'browser' => '',
                        'os' => '',
                    ];
                    $token = $this->getToken(config('app.url_token_jkttnl'), $postRequest);

                    $token = json_decode($token, TRUE);
                    if ($token != '') {

                        if (array_key_exists('access_token', $token)) {
                            $accessToken = $token['access_token'];
                            session(['access_token_jkttnl_stok' => $token['access_token']]);

                            $send = $this->postData(config('app.url_post_konsol_jkttnl'), 'POST', $accessToken, $data);
                            $send = json_decode($send, TRUE);
                            if (array_key_exists('status', $send)) {
                                goto selesai;
                            } else {
                                throw new \Exception($send['message']);
                            }
                        } else {
                            throw new \Exception("server Jakarta TNL tidak bisa diakses");
                        }
                    } else {
                        throw new \Exception("server Jakarta TNL tidak bisa diakses");
                    }
                } else {
                    $send = $this->postData(config('app.url_post_konsol_jkttnl'), 'POST', $accessTokenJktTnlStok, $data);

                    $send = json_decode($send, TRUE);
                    if (array_key_exists('status', $send)) {
                        goto selesai;
                    } else {
                        throw new \Exception($send['message']);
                    }
                }
            }
            if ($data['stok_idjkttnldel'] != '') {
                $accessTokenJktTnlStok = session('access_token_jkttnl_stok');
                $data['konsolidasi'] = true;
                if (!$accessTokenJktTnlStok) {
                    $postRequest = [
                        'user' => config('app.user_api'),
                        'password' => config('app.pass_api'),
                        'ipclient' => '',
                        'ipserver' => '',
                        'latitude' => '',
                        'longitude' => '',
                        'browser' => '',
                        'os' => '',
                    ];
                    $token = $this->getToken(config('app.url_token_jkttnl'), $postRequest);

                    $token = json_decode($token, TRUE);
                    if ($token != '') {

                        if (array_key_exists('access_token', $token)) {
                            $accessToken = $token['access_token'];
                            session(['access_token_jkttnl_stok' => $token['access_token']]);

                            $send = $this->postData(config('app.url_post_konsol_jkttnl'), 'POST', $accessToken, $data);
                            $send = json_decode($send, TRUE);
                            if (array_key_exists('status', $send)) {
                                goto selesai;
                            } else {
                                throw new \Exception($send['message']);
                            }
                        } else {
                            throw new \Exception("server Jakarta TNL tidak bisa diakses");
                        }
                    } else {
                        throw new \Exception("server Jakarta TNL tidak bisa diakses");
                    }
                } else {
                    $send = $this->postData(config('app.url_post_konsol_jkttnl'), 'POST', $accessTokenJktTnlStok, $data);

                    $send = json_decode($send, TRUE);
                    if (array_key_exists('status', $send)) {
                        goto selesai;
                    } else {
                        throw new \Exception($send['message']);
                    }
                }
            }
        }


        selesai:
        return true;
    }


    public function http_request(string $url, string $method = 'GET', array $headers = null, array $body = null): string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if (!empty($body)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, ['Content-Type: application/json']));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }
    public function getToken($server, $postRequest)
    {
        $token = $this->http_request(
            $server,
            'POST',
            [
                'Accept: application/json'
            ],
            $postRequest
        );

        return $token;
    }
    public function postData($server, $method, $accessToken, $data)
    {
        $send = $this->http_request(
            $server,
            $method,
            [
                'Authorization: Bearer ' . $accessToken,
                'Accept: application/json',
                'Content-Type: application/json'
            ],
            $data
        );
        return $send;
    }

    function getVulkan($tanggal = null ){
        $querytgl = ($tanggal)??date('Y/m/d');

        $reuse = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select('a.id')
            ->where('grp', 'STATUS REUSE')
            ->where('subgrp', 'STATUS REUSE')
            ->where('text', 'REUSE')
            ->first()->id ?? 0;

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
        
        return $queryvulkan;
       
    }
    function getVulkanTnl($tanggal = null ){
        $querytgl = ($tanggal)??date('Y/m/d');

        $reuse = db::connection('srvtnl')->table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select('a.id')
            ->where('grp', 'STATUS REUSE')
            ->where('subgrp', 'STATUS REUSE')
            ->where('text', 'REUSE')
            ->first()->id ?? 0;

        $tempvulkanplus = '##tempvulkanplus' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::connection('srvtnl')->create($tempvulkanplus, function ($table) {
            $table->integer('stok_id')->nullable();
            $table->integer('vulkan')->nullable();
        });


        $tempvulkanminus = '##tempvulkanminus' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::connection('srvtnl')->create($tempvulkanminus, function ($table) {
            $table->integer('stok_id')->nullable();
            $table->integer('vulkan')->nullable();
        });


        $queryvulkanplus = db::connection('srvtnl')->table("stok")->from(db::raw("stok a with (readuncommitted)"))
            ->select(
                db::raw("a.id as stok_id"),
                db::raw("sum(b.vulkanisirke) as vulkan"),
            )
            ->join(db::raw("penerimaanstokdetail b with (readuncommitted)"), 'a.id', 'b.stok_id')
            ->join(db::raw("penerimaanstokheader c with (readuncommitted)"), 'b.nobukti', 'c.nobukti')
            ->where('a.statusreuse', $reuse)
            ->whereraw("c.tglbukti<='" . $querytgl . "'")
            ->groupby('a.id');

        DB::connection('srvtnl')->table($tempvulkanplus)->insertUsing([
            'stok_id',
            'vulkan',
        ],  $queryvulkanplus);

        $queryvulkanminus = db::connection('srvtnl')->table("stok")->from(db::raw("stok a with (readuncommitted)"))
            ->select(
                db::raw("a.id as stok_id"),
                db::raw("sum(b.vulkanisirke) as vulkan"),
            )
            ->join(db::raw("pengeluaranstokdetail b with (readuncommitted)"), 'a.id', 'b.stok_id')
            ->join(db::raw("pengeluaranstokheader c with (readuncommitted)"), 'b.nobukti', 'c.nobukti')
            ->where('a.statusreuse', $reuse)
            ->whereraw("c.tglbukti<='" . $querytgl . "'")
            ->groupby('a.id');

        DB::connection('srvtnl')->table($tempvulkanminus)->insertUsing([
            'stok_id',
            'vulkan',
        ],  $queryvulkanminus);


        $queryvulkan = db::connection('srvtnl')->table("stok")->from(db::raw("stok a with (readuncommitted)"))
            ->select(
                db::raw("a.id  as stok_id"),
                db::raw("((isnull(a.vulkanisirawal,0)+isnull(b.vulkan,0))-isnull(c.vulkan,0)) as vulkan"),
            )
            ->leftjoin(db::raw($tempvulkanplus . " b "), 'a.id', 'b.stok_id')
            ->leftjoin(db::raw($tempvulkanminus . " c "), 'a.id', 'c.stok_id')
            ->where('a.statusreuse', $reuse);
        
        return $queryvulkan;
       
    }
    
    public function processApprovalaktif(array $data)
    {
        $statusaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $stok = Stok::find($data['Id'][$i]);

            $stok->statusaktif = $statusaktif->id;
            $aksi = $statusaktif->text;

            // dd($stok);
            if ($stok->save()) {

                (new LogTrail())->processStore([

                    'namatabel' => strtoupper($stok->getTable()),
                    'postingdari' => 'APPROVAL AKTIF STOK',
                    'idtrans' => $stok->id,
                    'nobuktitrans' => $stok->id,
                    'aksi' => $aksi,
                    'datajson' => $stok->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $stok;
    }    
}
