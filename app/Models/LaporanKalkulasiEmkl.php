<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanKalkulasiEmkl extends Model
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

    public function getReport($periode,$jenis)
    {
        $periode = $periode ;
        $statusposting = $jenis;
        $parameter = new Parameter();
        $idstatusposting = $parameter->cekId('STATUS POSTING', 'STATUS POSTING', 'POSTING') ?? 0;
        $penerimaanTruckingHeader = '##penerimaantruckingheader' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $jenisorderan=db::table("jenisorder")->from(db::raw("jenisorder a with (readuncommitted)"))
        ->select(
            'a.keterangan'
        )
        ->where('a.id',$jenis)
        ->first()->keterangan ?? '';


        // dd(db::table($penerimaanTruckingDetailrekap)->get());

        $temporderan = '##temporderan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temporderan, function ($table) {
            $table->BigInteger('id');
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->longtext('shipper')->nullable();
            $table->longtext('tujuan')->nullable();
            $table->longtext('container')->nullable();
            $table->longtext('jenisorder')->nullable();
            $table->longtext('marketing')->nullable();
            $table->longtext('kapal')->nullable();
            $table->longtext('voy')->nullable();
            $table->longtext('penerima')->nullable();
            $table->longtext('destination')->nullable();
            $table->longtext('nocontseal')->nullable();
            $table->longtext('lokasibongkarmuat')->nullable();
        });

        $queryorderan=db::table("jobemkl")->from(db::raw("jobemkl a with (readuncommitted)"))
        ->select (
            'a.id',
            'a.nobukti',
            'a.tglbukti',
            db::raw("isnull(b.namapelanggan,'') as shipper"),
            db::raw("isnull(c.keterangan,'') as tujuan"),
            db::raw("isnull(d.kodecontainer,'') as container"),
            db::raw("isnull(e.keterangan,'') as jenisorder"),
            db::raw("isnull(f.keterangan,'') as marketing"),
            'a.kapal',
            db::raw("isnull(a.voy,'') as voy"),
            db::raw("isnull(a.penerima,'') as penerima"),
            'a.destination',
            db::raw("trim(isnull(a.nocont,''))+' / '+trim(isnull(a.noseal,''))as nocontseal"),
            'a.lokasibongkarmuat',
        )
        ->leftjoin(db::raw("pelanggan b with (readuncommitted)"),'a.shipper_id','b.id')
        ->leftjoin(db::raw("tujuan c with (readuncommitted)"),'a.tujuan_id','c.id')
        ->leftjoin(db::raw("container d with (readuncommitted)"),'a.container_id','d.id')
        ->leftjoin(db::raw("jenisorder e with (readuncommitted)"),'a.jenisorder_id','e.id')
        ->leftjoin(db::raw("marketing f with (readuncommitted)"),'a.marketing_id','f.id')
        ->whereraw("format(a.tglbukti,'MM-yyyy')='$periode'")
        ->where('a.jenisorder_id', '=', $jenis);
      
        // dd($queryorderan->get());
        DB::table($temporderan)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'shipper',
            'tujuan',
            'container',
            'jenisorder',
            'marketing',
            'kapal',
            'voy',
            'penerima',
            'destination',
            'nocontseal',
            'lokasibongkarmuat',
        ], $queryorderan);

        $tempLaporan = '##tempLaporan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempLaporan, function ($table) {
            $table->BigInteger('id');
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->longtext('shipper')->nullable();
            $table->longtext('tujuan')->nullable();
            $table->longtext('container')->nullable();
            $table->longtext('jenisorder')->nullable();
            $table->longtext('marketing')->nullable();
            $table->longtext('kapal')->nullable();
            $table->longtext('voy')->nullable();
            $table->longtext('penerima')->nullable();
            $table->longtext('destination')->nullable();
            $table->longtext('nocontseal')->nullable();
            $table->longtext('lokasibongkarmuat')->nullable();
            $table->double('biayadoor',15,2)->nullable();
            $table->double('biayado',15,2)->nullable();
            $table->double('uangkawal',15,2)->nullable();
            $table->double('uangburuh',15,2)->nullable();
            $table->double('biayacleaning',15,2)->nullable();
            $table->double('biayalain',15,2)->nullable();
            $table->double('nominalinvoicedoor',15,2)->nullable();
            $table->longtext('invoiceemkl_nobuktiinvoicetambahan')->nullable();
            $table->double('nominalthc',15,2)->nullable();
            $table->longtext('nobuktipengeluaranthc')->nullable();
            $table->longtext('nobuktiinvoicethc')->nullable();
            $table->double('nominalsto',15,2)->nullable();
            $table->longtext('nobuktipengeluaransto')->nullable();
            $table->longtext('nobuktiinvoicesto')->nullable();
            $table->double('nominalstodem',15,2)->nullable();
            $table->longtext('nobuktipengeluaranstodem')->nullable();
            $table->longtext('nobuktiinvoicestodem')->nullable();

        });

        $tempinvoicebongkaranutama = '##tempinvoicebongkaranutama' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempinvoicebongkaranutama, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->longtext('invoiceemkl_nobuktiinvoicetambahan')->nullable();
            $table->double('biayadoor',15,2)->nullable();
            $table->double('biayado',15,2)->nullable();
            $table->double('uangkawal',15,2)->nullable();
            $table->double('uangburuh',15,2)->nullable();
            $table->double('biayacleaning',15,2)->nullable();
            $table->double('biayalain',15,2)->nullable();
            $table->double('nominal',15,2)->nullable();
        });



        $parameter = new Parameter();
        $paramdoor = $parameter->cekText('BIAYA EMKL', 'DOORING') ?? 0;
        $paramkawal = $parameter->cekText('BIAYA EMKL', 'KAWAL') ?? 0;
        $paramburuh = $parameter->cekText('BIAYA EMKL', 'BURUH') ?? 0;
        $paramcleaning = $parameter->cekText('BIAYA EMKL', 'CLEANING') ?? 0;
        $paramdokumen = $parameter->cekText('BIAYA EMKL', 'DOKUMEN') ?? 0;
        $paramlain = $parameter->cekText('BIAYA EMKL', 'LAIN') ?? 0;

        $queryinvoicebongkaranutama=db::table("invoiceemklheader")->from(db::raw("invoiceemklheader a with (readuncommitted)"))
        ->select(
            'c.nobukti',
            'a.nobuktiinvoicetambahan as invoiceemkl_nobuktiinvoicetambahan',
            db::raw("isnull(door.nominal,0) as biayadoor"),
            db::raw("isnull(dokumen.nominal,0) as biayado"),
            db::raw("isnull(kawal.nominal,0) as uangkawal"),
            db::raw("isnull(buruh.nominal,0) as uangburuh"),
            db::raw("isnull(cleaning.nominal,0) as biayacleaning"),
            db::raw("isnull(lain.nominal,0) as biayalain"),
            db::raw("b.nominal as nominal"),
            
        )
        ->join(db::raw("invoiceemkldetail b with (readuncommitted)"),'a.nobukti','b.nobukti')
        ->join(db::raw("jobemkl c with (readuncommitted)"),'b.jobemkl_nobukti','c.nobukti')
        ->leftJoin(db::raw("invoiceemkldetailrincianbiaya door with (readuncommitted)"), function ($join)  use ($paramdoor) {
            $join->on('b.nobukti', '=', 'door.nobukti');
            $join->on('b.id', '=', 'door.invoiceemkldetail_id');
            $join->on('door.biayaemkl_id', '=', DB::raw($paramdoor));
        })                            
        ->leftJoin(db::raw("invoiceemkldetailrincianbiaya kawal with (readuncommitted)"), function ($join)  use ($paramkawal) {
            $join->on('b.nobukti', '=', 'kawal.nobukti');
            $join->on('b.id', '=', 'kawal.invoiceemkldetail_id');
            $join->on('kawal.biayaemkl_id', '=', DB::raw($paramkawal));
        })                            
        ->leftJoin(db::raw("invoiceemkldetailrincianbiaya buruh with (readuncommitted)"), function ($join)  use ($paramburuh) {
            $join->on('b.nobukti', '=', 'buruh.nobukti');
            $join->on('b.id', '=', 'buruh.invoiceemkldetail_id');
            $join->on('buruh.biayaemkl_id', '=', DB::raw($paramburuh));
        })                            
        ->leftJoin(db::raw("invoiceemkldetailrincianbiaya cleaning with (readuncommitted)"), function ($join)  use ($paramcleaning) {
            $join->on('b.nobukti', '=', 'cleaning.nobukti');
            $join->on('b.id', '=', 'cleaning.invoiceemkldetail_id');
            $join->on('cleaning.biayaemkl_id', '=', DB::raw($paramcleaning));
        })                            
        ->leftJoin(db::raw("invoiceemkldetailrincianbiaya dokumen with (readuncommitted)"), function ($join)  use ($paramdokumen) {
            $join->on('b.nobukti', '=', 'dokumen.nobukti');
            $join->on('b.id', '=', 'dokumen.invoiceemkldetail_id');
            $join->on('dokumen.biayaemkl_id', '=', DB::raw($paramdokumen));
        })                              
        ->leftJoin(db::raw("invoiceemkldetailrincianbiaya lain with (readuncommitted)"), function ($join)  use ($paramlain) {
            $join->on('b.nobukti', '=', 'lain.nobukti');
            $join->on('b.id', '=', 'lain.invoiceemkldetail_id');
            $join->on('lain.biayaemkl_id', '=', DB::raw($paramlain));
        })             
        ->where('a.jenisorder_id',2)
        ->where('a.statusinvoice',722);

        DB::table($tempinvoicebongkaranutama)->insertUsing([
            'nobukti',
            'invoiceemkl_nobuktiinvoicetambahan',
            'biayadoor',
            'biayado',
            'uangkawal',
            'uangburuh',
            'biayacleaning',
            'biayalain',
            'nominal',
        ], $queryinvoicebongkaranutama);     
        
        $tempinvoicebongkarantambahanlist = '##tempinvoicebongkarantambahanlist' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempinvoicebongkarantambahanlist, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->longtext('invoiceemkl_nobuktiinvoicetambahan')->nullable();
            $table->double('biayalain',15,2)->nullable();
            $table->double('nominal',15,2)->nullable();
        });


        $queryinvoicebongkarantambahanlist=db::table("invoiceemklheader")->from(db::raw("invoiceemklheader a with (readuncommitted)"))
        ->select(
            'c.nobukti',
            'a.nobuktiinvoicetambahan as invoiceemkl_nobuktiinvoicetambahan',
            db::raw("isnull(lain.nominal,0) as biayalain"),
            db::raw("b.nominal as nominal"),
            
        )
        ->join(db::raw("invoiceemkldetail b with (readuncommitted)"),'a.nobukti','b.nobukti')
        ->join(db::raw("jobemkl c with (readuncommitted)"),'b.jobemkl_nobukti','c.nobukti')
        ->leftJoin(db::raw("invoiceemkldetailrincianbiaya lain with (readuncommitted)"), function ($join)  use ($paramlain) {
            $join->on('b.nobukti', '=', 'lain.nobukti');
            $join->on('b.id', '=', 'lain.invoiceemkldetail_id');
            $join->on('lain.biayaemkl_id', '=', DB::raw($paramlain));
        })             
        ->where('a.jenisorder_id',2)
        ->where('a.statusinvoice',723);

        DB::table($tempinvoicebongkarantambahanlist)->insertUsing([
            'nobukti',
            'invoiceemkl_nobuktiinvoicetambahan',
            'biayalain',
            'nominal',
        ], $queryinvoicebongkarantambahanlist);    


        $tempinvoicebongkarantambahan = '##tempinvoicebongkarantambahan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempinvoicebongkarantambahan, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->longtext('invoiceemkl_nobuktiinvoicetambahan')->nullable();
            $table->double('biayalain',15,2)->nullable();
            $table->double('nominal',15,2)->nullable();
        });        

        $queryinvoicebongkarantambahan=db::table($tempinvoicebongkarantambahanlist)->from(db::raw($tempinvoicebongkarantambahanlist . " a "))
        ->select(
            'a.nobukti',
            db::raw("string_agg(a.invoiceemkl_nobuktiinvoicetambahan,',') as invoiceemkl_nobuktiinvoicetambahan"),
            db::raw("sum(isnull(a.biayalain,0)) as biayalain"),
            db::raw("sum(isnull(a.nominal,0)) as nominal"),
            
        )
        ->groupBy('a.nobukti');


        DB::table($tempinvoicebongkarantambahan)->insertUsing([
            'nobukti',
            'invoiceemkl_nobuktiinvoicetambahan',
            'biayalain',
            'nominal',
        ], $queryinvoicebongkarantambahan);    
        // dd(db::table($tempinvoicebongkarantambahan)->get())        ;        

        $tempinvoicebongkaranthclist = '##tempinvoicebongkaranthclist' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempinvoicebongkaranthclist, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->longtext('nobuktiinvoice')->nullable();
            $table->longtext('nobuktipengeluaran')->nullable();
            $table->double('nominal',15,2)->nullable();
        });        

        $queryinvoicebongkaranthclist=db::table("invoiceemklheader")->from(db::raw("invoiceemklheader a with (readuncommitted)"))
        ->select(
            'c.nobukti',
            'a.nobuktiinvoicereimbursement as nobuktiinvoice',
            'b.nobukti as nobuktipengeluaran',
            db::raw("isnull(b2.nominal,0) as nominal"),
            
        )
        ->join(db::raw("pengeluaranheader b with (readuncommitted)"),'a.pengeluaranheader_nobukti','b.nobukti')
        ->join(db::raw("pengeluarandetail b1 with (readuncommitted)"),'b.nobukti','b1.nobukti')
        ->Join(db::raw("pengeluarandetailrincianjob b2 (readuncommitted)"), function ($join)   {
            $join->on('b.id', '=', 'b2.pengeluaran_id');
            $join->on('b1.id', '=', 'b2.pengeluarandetail_id');
        })             
        ->join(db::raw("jobemkl c with (readuncommitted)"),'b2.jobemkl_nobukti','c.nobukti')
        ->where('a.jenisorder_id',2)
        ->where('b.statusjenisbiaya',745)
        ->whereraw("isnull(a.nobuktiinvoicereimbursement,'')<>''");        

        DB::table($tempinvoicebongkaranthclist)->insertUsing([
            'nobukti',
            'nobuktiinvoice',
            'nobuktipengeluaran',
            'nominal',
        ], $queryinvoicebongkaranthclist);          

        $tempinvoicebongkaranthc = '##tempinvoicebongkaranthc' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempinvoicebongkaranthc, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->longtext('nobuktiinvoice')->nullable();
            $table->longtext('nobuktipengeluaran')->nullable();
            $table->double('nominal',15,2)->nullable();
        });              
        
        $queryinvoicebongkaranthc=db::table($tempinvoicebongkaranthclist)->from(db::raw($tempinvoicebongkaranthclist . " a "))
        ->select(
            'a.nobukti',
            db::raw("string_agg(a.nobuktiinvoice,',') as nobuktiinvoice"),
            db::raw("string_agg(a.nobuktipengeluaran,',') as nobuktipengeluaran"),
            db::raw("sum(isnull(a.nominal,0)) as nominal"),
            
        )
        ->groupBy('a.nobukti');

  

// 
        DB::table($tempinvoicebongkaranthc)->insertUsing([
            'nobukti',
            'nobuktiinvoice',
            'nobuktipengeluaran',
            'nominal',
        ], $queryinvoicebongkaranthc);  


        
// storage
$tempinvoicebongkaranstolist = '##tempinvoicebongkaranstolist' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
Schema::create($tempinvoicebongkaranstolist, function ($table) {
    $table->string('nobukti', 50)->nullable();
    $table->longtext('nobuktiinvoice')->nullable();
    $table->longtext('nobuktipengeluaran')->nullable();
    $table->double('nominal',15,2)->nullable();
});        

$queryinvoicebongkaranstolist=db::table("invoiceemklheader")->from(db::raw("invoiceemklheader a with (readuncommitted)"))
->select(
    'c.nobukti',
    'a.nobuktiinvoicereimbursement as nobuktiinvoice',
    'b.nobukti as nobuktipengeluaran',
    db::raw("isnull(b2.nominal,0) as nominal"),
    
)
->join(db::raw("pengeluaranheader b with (readuncommitted)"),'a.pengeluaranheader_nobukti','b.nobukti')
->join(db::raw("pengeluarandetail b1 with (readuncommitted)"),'b.nobukti','b1.nobukti')
->Join(db::raw("pengeluarandetailrincianjob b2 (readuncommitted)"), function ($join)   {
    $join->on('b.id', '=', 'b2.pengeluaran_id');
    $join->on('b1.id', '=', 'b2.pengeluarandetail_id');
})             
->join(db::raw("jobemkl c with (readuncommitted)"),'b2.jobemkl_nobukti','c.nobukti')
->where('a.jenisorder_id',2)
->where('b.statusjenisbiaya',746)
->whereraw("isnull(a.nobuktiinvoicereimbursement,'')<>''");        

DB::table($tempinvoicebongkaranstolist)->insertUsing([
    'nobukti',
    'nobuktiinvoice',
    'nobuktipengeluaran',
    'nominal',
], $queryinvoicebongkaranstolist);          

$tempinvoicebongkaransto = '##tempinvoicebongkaransto' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
Schema::create($tempinvoicebongkaransto, function ($table) {
    $table->string('nobukti', 50)->nullable();
    $table->longtext('nobuktiinvoice')->nullable();
    $table->longtext('nobuktipengeluaran')->nullable();
    $table->double('nominal',15,2)->nullable();
});              

$queryinvoicebongkaransto=db::table($tempinvoicebongkaranstolist)->from(db::raw($tempinvoicebongkaranstolist . " a "))
->select(
    'a.nobukti',
    db::raw("string_agg(a.nobuktiinvoice,',') as nobuktiinvoice"),
    db::raw("string_agg(a.nobuktipengeluaran,',') as nobuktipengeluaran"),
    db::raw("sum(isnull(a.nominal,0)) as nominal"),
    
)
->groupBy('a.nobukti');

// / 
        DB::table($tempinvoicebongkaransto)->insertUsing([
            'nobukti',
            'nobuktiinvoice',
            'nobuktipengeluaran',
            'nominal',
        ], $queryinvoicebongkaransto);  
        // 
        // dd('test');


        
// storage demurage
$tempinvoicebongkaranstodemlist = '##tempinvoicebongkaranstodemlist' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
Schema::create($tempinvoicebongkaranstodemlist, function ($table) {
    $table->string('nobukti', 50)->nullable();
    $table->longtext('nobuktiinvoice')->nullable();
    $table->longtext('nobuktipengeluaran')->nullable();
    $table->double('nominal',15,2)->nullable();
});        

$queryinvoicebongkaranstodemlist=db::table("invoiceemklheader")->from(db::raw("invoiceemklheader a with (readuncommitted)"))
->select(
    'c.nobukti',
    'a.nobuktiinvoicereimbursement as nobuktiinvoice',
    'b.nobukti as nobuktipengeluaran',
    db::raw("isnull(b2.nominal,0) as nominal"),
    
)
->join(db::raw("pengeluaranheader b with (readuncommitted)"),'a.pengeluaranheader_nobukti','b.nobukti')
->join(db::raw("pengeluarandetail b1 with (readuncommitted)"),'b.nobukti','b1.nobukti')
->Join(db::raw("pengeluarandetailrincianjob b2 (readuncommitted)"), function ($join)   {
    $join->on('b.id', '=', 'b2.pengeluaran_id');
    $join->on('b1.id', '=', 'b2.pengeluarandetail_id');
})             
->join(db::raw("jobemkl c with (readuncommitted)"),'b2.jobemkl_nobukti','c.nobukti')
->where('a.jenisorder_id',2)
->where('b.statusjenisbiaya',747)
->whereraw("isnull(a.nobuktiinvoicereimbursement,'')<>''");        

DB::table($tempinvoicebongkaranstodemlist)->insertUsing([
    'nobukti',
    'nobuktiinvoice',
    'nobuktipengeluaran',
    'nominal',
], $queryinvoicebongkaranstodemlist);          

$tempinvoicebongkaranstodem = '##tempinvoicebongkaranstodem' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
Schema::create($tempinvoicebongkaranstodem, function ($table) {
    $table->string('nobukti', 50)->nullable();
    $table->longtext('nobuktiinvoice')->nullable();
    $table->longtext('nobuktipengeluaran')->nullable();
    $table->double('nominal',15,2)->nullable();
});              

$queryinvoicebongkaranstodem=db::table($tempinvoicebongkaranstodemlist)->from(db::raw($tempinvoicebongkaranstodemlist . " a "))
->select(
    'a.nobukti',
    db::raw("string_agg(a.nobuktiinvoice,',') as nobuktiinvoice"),
    db::raw("string_agg(a.nobuktipengeluaran,',') as nobuktipengeluaran"),
    db::raw("sum(isnull(a.nominal,0)) as nominal"),
    
)
->groupBy('a.nobukti');

// / 
        DB::table($tempinvoicebongkaranstodem)->insertUsing([
            'nobukti',
            'nobuktiinvoice',
            'nobuktipengeluaran',
            'nominal',
        ], $queryinvoicebongkaranstodem);  
        // 
        $queryTempLaporan = DB::table($temporderan)->from(
            DB::raw($temporderan . " as a")
        )
            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                'a.shipper',
                'a.tujuan',
                'a.container',
                'a.jenisorder',
                'a.marketing',
                'a.kapal',
                'a.voy',
                'a.penerima',
                'a.destination',
                'a.nocontseal',
                'a.lokasibongkarmuat',
                db::raw("isnull(b.biayadoor,0) as biayadoor"),
                db::raw("isnull(b.biayado,0) as biayado"),
                db::raw("isnull(b.uangkawal,0) as uangkawal"),
                db::raw("isnull(b.uangburuh,0) as uangburuh"),
                db::raw("isnull(b.biayacleaning,0) as biayacleaning"),
                db::raw("(isnull(b.biayalain,0)+isnull(C.biayalain,0)) as biayalain"),
                db::raw("(isnull(b.nominal,0)+isnull(c.nominal,0)) as nominalinvoicedoor"),
                db::raw("isnull(b.invoiceemkl_nobuktiinvoicetambahan,'')+isnull(c.invoiceemkl_nobuktiinvoicetambahan,'') as invoiceemkl_nobuktiinvoicetambahan"),
                db::raw("isnull(d.nominal,0) as nominalthc"),
                db::raw("isnull(d.nobuktipengeluaran,'') as nobuktipengeluaranthc"),
                db::raw("isnull(d.nobuktiinvoice,'') as nobuktiinvoicethc"),
                db::raw("isnull(e.nominal,0) as nominalsto"),
                db::raw("isnull(e.nobuktipengeluaran,'') as nobuktipengeluaransto"),
                db::raw("isnull(e.nobuktiinvoice,'') as nobuktiinvoicesto"),
                db::raw("isnull(f.nominal,0) as nominalstodem"),
                db::raw("isnull(f.nobuktipengeluaran,'') as nobuktipengeluaranstodem"),
                db::raw("isnull(f.nobuktiinvoice,'') as nobuktiinvoicestodem"),

            )
            ->leftjoin(db::raw($tempinvoicebongkaranutama. " b"),'a.nobukti','b.nobukti')
            ->leftjoin(db::raw($tempinvoicebongkarantambahan. " c"),'a.nobukti','c.nobukti')
            ->leftjoin(db::raw($tempinvoicebongkaranthc. " d"),'a.nobukti','d.nobukti')
            ->leftjoin(db::raw($tempinvoicebongkaransto. " e"),'a.nobukti','e.nobukti')
            ->leftjoin(db::raw($tempinvoicebongkaranstodem. " f"),'a.nobukti','f.nobukti')
            
            ->orderBy('a.tglbukti', 'ASC')
            ->orderBy('a.nobukti', 'ASC');

  
        DB::table($tempLaporan)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'shipper',
            'tujuan',
            'container',
            'jenisorder',
            'marketing',
            'kapal',
            'voy',
            'penerima',
            'destination',
            'nocontseal',
            'lokasibongkarmuat',
            'biayadoor',
            'biayado',
            'uangkawal',
            'uangburuh',
            'biayacleaning',
            'biayalain',
            'nominalinvoicedoor',
            'invoiceemkl_nobuktiinvoicetambahan',
            'nominalthc',
            'nobuktipengeluaranthc',
            'nobuktiinvoicethc',
            'nominalsto',
            'nobuktipengeluaransto',
            'nobuktiinvoicesto',
            'nominalstodem',
            'nobuktipengeluaranstodem',
            'nobuktiinvoicestodem',

        ], $queryTempLaporan);



        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();
        $parameter = new Parameter();
        
        $queryRekap = DB::table($tempLaporan)->from(
            DB::raw($tempLaporan . " as a")
        )
            ->select(
                db::raw("cast(a.tglbukti as date) as tanggal"),
                'a.nobukti',
                'a.shipper',
                'a.tujuan',
                'a.container',
                'a.jenisorder',
                'a.marketing',
                'a.kapal',
                'a.voy',
                'a.penerima',
                'a.destination',
                'a.nocontseal',
                'a.lokasibongkarmuat', 
                'a.biayadoor',
                'a.biayado',
                'a.uangkawal',
                'a.uangburuh',
                'a.biayacleaning',
                'a.biayalain',
                'a.nominalinvoicedoor',
                'a.invoiceemkl_nobuktiinvoicetambahan',
                'a.nominalthc',
                'a.nobuktipengeluaranthc',
                'a.nobuktiinvoicethc',
                'a.nominalsto',
                'a.nobuktipengeluaransto',
                'a.nobuktiinvoicesto',
                'a.nominalstodem',
                'a.nobuktipengeluaranstodem',
                'a.nobuktiinvoicestodem',
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
                DB::raw("upper('Job Emkl ". $jenisorderan." Bulan : ".$periode ."') as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
            )
            ->orderBy('a.id');


            $data = $queryRekap->get();

            // dd($data);
        return $data;
    }
}
