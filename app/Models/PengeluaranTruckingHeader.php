<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\RunningNumberService;
use Illuminate\Database\Schema\Blueprint;

class PengeluaranTruckingHeader extends MyModel
{
    use HasFactory;
    protected $table = 'pengeluarantruckingheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function cekvalidasiaksi($nobukti)
    {

        $error = new Error();
        $keteranganerror = $error->cekKeteranganError('TDT') ?? '';
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';



        $PengeluaranTruckingHeader = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader"))->where('nobukti', $nobukti)->first();
        $nobukti = $PengeluaranTruckingHeader->nobukti;

        $prosesUangJalan = DB::table('prosesuangjalansupirdetail')
            ->from(
                DB::raw("prosesuangjalansupirdetail as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.pengeluarantrucking_nobukti'
            )
            ->where('a.pengeluarantrucking_nobukti', '=', $nobukti)
            ->first();
        if (isset($prosesUangJalan)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>'. $nobukti . '</b><br>' .$keteranganerror.'<br> No Bukti Proses Uang Jalan Supir <b>'. $prosesUangJalan->nobukti .'</b> <br> '.$keterangantambahanerror,
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
        $pengeluaran = DB::table('pengeluarantruckingheader')
            ->from(
                DB::raw("pengeluarantruckingheader as a with (readuncommitted)")
            )
            ->select(
                'a.pengeluarantrucking_nobukti'
            )
            ->where('a.pengeluarantrucking_nobukti', '=', $PengeluaranTruckingHeader->nobukti)
            ->first();
        if (isset($pengeluaran)) {

            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>'. $nobukti . '</b><br>' .$keteranganerror.'<br> No Bukti Pengeluaran Trucking <b>'. $pengeluaran->pengeluarantrucking_nobukti .'</b> <br> '.$keterangantambahanerror,
                // 'keterangan' => 'pengeluaran Trucking',
                'kodeerror' => 'SATL2'
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
        $penerimaanTrucking = DB::table('penerimaantruckingdetail')
            ->from(
                DB::raw("penerimaantruckingdetail as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.pengeluarantruckingheader_nobukti'
            )
            ->where('a.pengeluarantruckingheader_nobukti', '=', $nobukti)
            ->first();
        if (isset($penerimaanTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>'. $nobukti . '</b><br>' .$keteranganerror.'<br> No Bukti Penerimaan Trucking <b>'. $penerimaanTrucking->nobukti .'</b> <br> '.$keterangantambahanerror,
                // 'keterangan' => 'Penerimaan Trucking ' . $penerimaanTrucking->nobukti,
                'kodeerror' => 'SATL2'
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('SAPP') ?? '';
        $approvalJurnal = DB::table('pengeluarantruckingheader')
            ->from(
                DB::raw("pengeluarantruckingheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.pengeluaran_nobukti'
            )
            ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.pengeluaran_nobukti', 'b.nobukti')
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($approvalJurnal)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>'. $approvalJurnal->nobukti . '</b><br>' .$keteranganerror.' <br> '.$keterangantambahanerror,
                // 'keterangan' => 'Approval Jurnal ' . $approvalJurnal->pengeluaran_nobukti,
                'kodeerror' => 'SAP'
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

    public function cekvalidasiklaim($id)
    {
        $error = new Error();
        $keteranganerror = $error->cekKeteranganError('MAX') ?? '';
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        $pengeluaranTruckingHeader = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader"))->where('id', $id)->first();

        $nobuktiPjt = $pengeluaranTruckingHeader->pengeluarantrucking_nobukti;
        $penerimaanTrucking = DB::table('penerimaantruckingdetail')
            ->from(
                DB::raw("penerimaantruckingdetail as a with (readuncommitted)")
            )
            ->select(
                'a.pengeluarantruckingheader_nobukti'
            )
            ->where('a.pengeluarantruckingheader_nobukti', '=', $nobuktiPjt)
            ->first();
        if (isset($penerimaanTrucking)) {
            $data = [
                'kondisi' => true,
                // 'keterangan' => 'Penerimaan Trucking',
                'keterangan' => 'No Bukti <b>'. $nobuktiPjt . '</b><br>' .$keteranganerror.'<br> No Bukti Proses Uang Jalan Supir <b>'. $penerimaanTrucking->pengeluarantruckingheader_nobukti .'</b> <br> '.$keterangantambahanerror,
                'kodeerror' => 'MAX'
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

    public function printValidation($id)
    {
        $query = DB::table($this->table)->from($this->table)->where('pengeluarantruckingheader.id', $id);
        $data = $query->first();
        $status = $data->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
        if ($status == $statusCetak->id) {
            return true;
        }
        return false;
    }

    public function get()
    {
        $this->setRequestParameters();
        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';

        $afkir = Parameter::from(DB::raw("pengeluaranstok with (readuncommitted)"))->where('kodepengeluaran', 'AFKIR')->first();


        $user_id = auth('api')->user()->id ?? 0;
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'PglrnTruckingHeaderController';

        // $temprole = '##temprole' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($temprole, function ($table) {
        //     $table->bigInteger('aco_id')->nullable();
        // });

        // $queryaco = db::table("useracl")->from(db::raw("useracl a with (readuncommitted)"))
        //     ->select('a.aco_id')
        //     ->join(db::raw("pengeluarantrucking b with (readuncommitted)"), 'a.aco_id', 'b.aco_id')
        //     ->where('a.user_id', $user_id);

        // DB::table($temprole)->insertUsing(['aco_id'], $queryaco);


        // $queryrole = db::table("acl")->from(db::raw("acl a with (readuncommitted)"))
        //     ->select('a.aco_id')
        //     ->join(db::raw("userrole b with (readuncommitted)"), 'a.role_id', 'b.role_id')
        //     ->join(db::raw("pengeluarantrucking c with (readuncommitted)"), 'a.aco_id', 'c.aco_id')
        //     ->leftjoin(db::raw($temprole . " d "), 'a.aco_id', 'd.aco_id')
        //     ->where('b.user_id', $user_id)
        //     ->whereRaw("isnull(d.aco_id,0)=0");

        // DB::table($temprole)->insertUsing(['aco_id'], $queryrole);

        if ($proses == 'reload') {
            $temtabel = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

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
                $table->string('nobukti', 50)->nullable();
                $table->dateTime('tglbukti')->nullable();
                $table->string('modifiedby', 200)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->string('pengeluaran_nobukti', 50)->nullable();
                $table->longText('penerimaantrucking_nobukti')->nullable();
                $table->longText('nobuktipenerimaan')->nullable();
                $table->string('pengeluarantrucking_id', 100)->nullable();
                $table->string('bank_id', 50)->nullable();
                $table->integer('trado_id')->nullable();
                $table->string('trado', 200)->nullable();
                $table->integer('tradoheader_id')->nullable();
                $table->string('supirheader', 200)->nullable();
                $table->string('supir', 200)->nullable();
                $table->string('karyawan', 200)->nullable();
                $table->string('gandengan', 50)->nullable();
                $table->string('pengeluarantrucking_nobukti', 50)->nullable();
                $table->dateTime('tglbukacetak')->nullable();
                $table->longText('statuscetak')->nullable();
                $table->longText('statuscetaktext')->nullable();
                $table->string('userbukacetak', 200)->nullable();
                $table->dateTime('tglkirimberkas')->nullable();
                $table->longText('statuskirimberkas')->nullable();
                $table->longText('statuskirimberkastext')->nullable();
                $table->string('userkirimberkas', 200)->nullable();
                $table->string('coa', 200)->nullable();
                $table->date('tgldariheaderpengeluaranheader')->nullable();
                $table->date('tglsampaiheaderpengeluaranheader')->nullable();
                $table->longText('statusposting')->nullable();
                $table->longText('statuspostingtext')->nullable();
                $table->double('qty')->nullable();
                $table->double('harga')->nullable();
                $table->integer('pengeluaranbank_id')->nullable();

            });
            // get namasupir pjt
            $tempSupir = '##tempsupir' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempSupir, function ($table) {
                $table->string('nobukti')->nullable();
                $table->string('supir')->nullable();
            });
            if (request()->pengeluaranheader_id == 1) {
                $getSupir = DB::table("pengeluarantruckingdetail")->from(DB::raw("pengeluarantruckingdetail"))
                    ->select(DB::raw("pengeluarantruckingdetail.nobukti, STRING_AGG(supir.namasupir, ', ') AS supir"))
                    ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingdetail.supir_id', 'supir.id')
                    ->whereRaw("nobukti like '%pjt%'")
                    ->groupBy("pengeluarantruckingdetail.nobukti");

                DB::table($tempSupir)->insertUsing(['nobukti', 'supir'], $getSupir);
            } else {

                $getSupir = DB::table("pengeluarantruckingheader")->from(DB::raw("pengeluarantruckingheader"))
                    ->select(DB::raw("pengeluarantruckingheader.nobukti, supir.namasupir AS supir"))
                    ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingheader.supir_id', 'supir.id')
                    ->where('pengeluarantruckingheader.pengeluarantrucking_id', '!=', 1);
                DB::table($tempSupir)->insertUsing(['nobukti', 'supir'], $getSupir);
            }

            $petik ='"';
            $url = config('app.url_fe').'penerimaantruckingheader';

            $getpenerimaantruckingdetail = DB::table("penerimaantruckingdetail")->from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
            ->select(DB::raw(" penerimaantruckingdetail.pengeluarantruckingheader_nobukti, STRING_AGG(penerimaantruckingdetail.nobukti, ', ') as nobuktipenerimaan,
            STRING_AGG('<a href=$petik".$url."?tgldari='+(format(penerimaantruckingheader.tglbukti,'yyyy-MM')+'-1')+'&tglsampai='+(format(penerimaantruckingheader.tglbukti,'yyyy-MM')+'-31')+'&nobukti='+penerimaantruckingheader.nobukti+'$petik 
            class=$petik link-color $petik target=$petik _blank $petik>'+penerimaantruckingdetail.nobukti+'</a>', ',') as url"))
            ->join(DB::raw("penerimaantruckingheader with (readuncommitted)"),'penerimaantruckingdetail.nobukti','penerimaantruckingheader.nobukti')
            ->whereRaw("isnull(penerimaantruckingdetail.pengeluarantruckingheader_nobukti,'') != ''")
            ->groupBy("penerimaantruckingdetail.pengeluarantruckingheader_nobukti");
            $tempurl = '##tempurl' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempurl, function (Blueprint $table) {
                $table->string('pengeluarantruckingheader_nobukti', 50)->nullable();
                $table->longText('nobuktipenerimaan')->nullable();
                $table->longText('url')->nullable();

            }); 
            DB::table($tempurl)->insertUsing(['pengeluarantruckingheader_nobukti', 'nobuktipenerimaan','url'], $getpenerimaantruckingdetail);

            $query = DB::table($this->table)->from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
                ->select(
                    'pengeluarantruckingheader.id',
                    'pengeluarantruckingheader.nobukti',
                    'pengeluarantruckingheader.tglbukti',
                    'pengeluarantruckingheader.modifiedby',
                    'pengeluarantruckingheader.created_at',
                    'pengeluarantruckingheader.updated_at',
                    'pengeluarantruckingheader.pengeluaran_nobukti',
                    db::raw("isnull(penerimaantruckingdetail.url,'') as penerimaantrucking_nobukti"),
                    db::raw("isnull(penerimaantruckingdetail.nobuktipenerimaan,'') as nobuktipenerimaan"),
                    'pengeluarantrucking.keterangan as pengeluarantrucking_id',
                    'bank.namabank as bank_id',
                    'pengeluarantruckingheader.trado_id',
                    'trado.keterangan as trado',
                    'pengeluarantruckingheader.trado_id as tradoheader_id',
                    'getsupir.supir as supirheader',
                    'getsupir.supir as supir',
                    'gandengan.kodegandengan as gandengan',
                    'pengeluarantruckingheader.pengeluarantrucking_nobukti',
                    DB::raw('(case when (year(pengeluarantruckingheader.tglbukacetak) <= 2000) then null else pengeluarantruckingheader.tglbukacetak end ) as tglbukacetak'),
                    'statuscetak.memo as statuscetak',
                    'statuscetak.text as statuscetaktext',
                    'pengeluarantruckingheader.userbukacetak',
                    DB::raw('(case when (year(pengeluarantruckingheader.tglkirimberkas) <= 2000) then null else pengeluarantruckingheader.tglkirimberkas end ) as tglkirimberkas'),
                    'statuskirimberkas.memo as statuskirimberkas',
                    'statuskirimberkas.text as statuskirimberkastext',
                    'pengeluarantruckingheader.userkirimberkas',
                    'akunpusat.keterangancoa as coa',
                    db::raw("cast((format(pengeluaranheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpengeluaranheader"),
                    db::raw("cast(cast(format((cast((format(pengeluaranheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpengeluaranheader"),
                    'statusposting.memo as statusposting',
                    'statusposting.text as statuspostingtext',
                    'pengeluaranheader.bank_id as pengeluaranbank_id',

                )
                // ->whereBetween('pengeluarantruckingheader.tglbukti', [date('Y-m-d',strtotime(request()->tgldari)), date('Y-m-d',strtotime(request()->tglsampai))])            
                ->leftJoin(DB::raw("pengeluaranheader with (readuncommitted)"), 'pengeluarantruckingheader.pengeluaran_nobukti', '=', 'pengeluaranheader.nobukti')
                ->leftJoin(DB::raw("pengeluarantruckingheader as b with (readuncommitted)"), 'pengeluarantruckingheader.pengeluarantrucking_nobukti', '=', 'b.nobukti')
                ->leftJoin(DB::raw("pengeluarantrucking with (readuncommitted)"), 'pengeluarantruckingheader.pengeluarantrucking_id', 'pengeluarantrucking.id')
                ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluarantruckingheader.bank_id', 'bank.id')
                ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pengeluarantruckingheader.coa', 'akunpusat.coa')
                ->leftJoin(DB::raw("trado with (readuncommitted)"), 'pengeluarantruckingheader.trado_id', 'trado.id')
                ->leftJoin(DB::raw("gandengan with (readuncommitted)"), 'pengeluarantruckingheader.gandengan_id', 'gandengan.id')
                ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'pengeluarantruckingheader.statuscetak', 'statuscetak.id')
                ->leftJoin(DB::raw("$tempSupir as getsupir with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'getsupir.nobukti')
                ->leftJoin(DB::raw("parameter as statusposting with (readuncommitted)"), 'pengeluarantruckingheader.statusposting', 'statusposting.id')
                ->leftJoin(DB::raw("parameter as statuskirimberkas with (readuncommitted)"), 'pengeluarantruckingheader.statuskirimberkas', 'statuskirimberkas.id')                
                ->leftJoin(DB::raw("$tempurl as penerimaantruckingdetail with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'penerimaantruckingdetail.pengeluarantruckingheader_nobukti');
            // ->join(db::raw($temprole . " d "), 'pengeluarantrucking.aco_id', 'd.aco_id');



            if (request()->pengeluaranstok_id && request()->pengeluaranstok_id == $afkir->id) {
                $query
                    ->addSelect('pengeluarantruckingdetail.qty')
                    ->addSelect('pengeluarantruckingdetail.harga')
                    ->leftJoin(DB::raw("pengeluarantruckingdetail with (readuncommitted)"), 'pengeluarantruckingheader.id', 'pengeluarantruckingdetail.pengeluarantruckingheader_id');
                if (request()->from_tnl == "YA") {
                    $query->where("pengeluarantruckingdetail.stoktnl_id", request()->stok_id);
                } else {
                    $query->where("pengeluarantruckingdetail.stok_id", request()->stok_id);
                }
            }
            if (request()->tgldari) {
                $query->whereBetween('pengeluarantruckingheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
            }
            if (request()->pengeluaranheader_id) {
                $query->where('pengeluarantruckingheader.pengeluarantrucking_id', request()->pengeluaranheader_id);
            }
            if ($periode != '') {
                $periode = explode("-", $periode);
                $query->whereRaw("MONTH(pengeluarantruckingheader.tglbukti) ='" . $periode[0] . "'")
                    ->whereRaw("year(pengeluarantruckingheader.tglbukti) ='" . $periode[1] . "'");
            }
            if ($statusCetak != '') {
                $query->where("pengeluarantruckingheader.statuscetak", $statusCetak);
            }
            $datadetail = json_decode($query->get(), true);
            foreach ($datadetail as $item) {
                $namakaryawan = '';
                if ($item['pengeluarantrucking_id'] == 'PENARIKAN DEPOSITO KARYAWAN' || $item['pengeluarantrucking_id'] == 'PINJAMAN KARYAWAN') {
                    // dd('test');
                    $querydetail1 = DB::table("pengeluarantruckingdetail")->from(DB::raw("pengeluarantruckingdetail  a with (readuncommitted)"))
                        ->select(
                            'b.namakaryawan',
                        )
                        ->join(db::raw("karyawan b with (readuncommitted)"), 'a.karyawan_id', 'b.id')
                        ->where('a.nobukti', $item['nobukti'])
                        ->groupby('b.namakaryawan');

                    // dd($querydetail1 );
                    $hit = 0;
                    $datadetail1 = json_decode($querydetail1->get(), true);
                    foreach ($datadetail1 as $itemdetail) {
                        $hit = $hit + 1;
                        if ($hit == 1) {
                            $namakaryawan = $namakaryawan . $itemdetail['namakaryawan'];
                        } else {
                            $namakaryawan = $namakaryawan . ',' . $itemdetail['namakaryawan'];
                        }
                    }
                }
                DB::table($temtabel)->insert([
                    'id' => $item['id'],
                    'nobukti' => $item['nobukti'],
                    'tglbukti' => $item['tglbukti'],
                    'modifiedby' => $item['modifiedby'],
                    'created_at' => $item['created_at'],
                    'updated_at' => $item['updated_at'],
                    'pengeluaran_nobukti' => $item['pengeluaran_nobukti'],
                    'penerimaantrucking_nobukti' => $item['penerimaantrucking_nobukti'],
                    'nobuktipenerimaan' => $item['nobuktipenerimaan'],
                    'pengeluarantrucking_id' => $item['pengeluarantrucking_id'],
                    'bank_id' => $item['bank_id'],
                    'trado_id' => $item['trado_id'],
                    'trado' => $item['trado'],
                    'tradoheader_id' => $item['tradoheader_id'],
                    'supirheader' => $item['supirheader'],
                    'supir' => $item['supir'],
                    'karyawan' => $namakaryawan,
                    'gandengan' => $item['gandengan'],
                    'pengeluarantrucking_nobukti' => $item['pengeluarantrucking_nobukti'],
                    'tglbukacetak' => $item['tglbukacetak'],
                    'statuscetak' => $item['statuscetak'],
                    'statuscetaktext' => $item['statuscetaktext'],
                    'userbukacetak' => $item['userbukacetak'],
                    'tglkirimberkas' => $item['tglkirimberkas'],
                    'statuskirimberkas' => $item['statuskirimberkas'],
                    'statuskirimberkastext' => $item['statuskirimberkastext'],
                    'userkirimberkas' => $item['userkirimberkas'],
                    'coa' => $item['coa'],
                    'tgldariheaderpengeluaranheader' => $item['tgldariheaderpengeluaranheader'],
                    'tglsampaiheaderpengeluaranheader' => $item['tglsampaiheaderpengeluaranheader'],
                    'statusposting' => $item['statusposting'],
                    'statuspostingtext' => $item['statuspostingtext'],
                    'qty' => $item['qty'] ?? '',
                    'harga' => $item['harga'] ?? '',
                    'pengeluaranbank_id' => $item['pengeluaranbank_id'] ?? '',
                    
                ]);
            }
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

            // dd($querydata);
            $temtabel = $querydata->namatabel;
        }
        $query = DB::table($temtabel)->from(DB::raw($temtabel . " a "))
            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                'a.pengeluaran_nobukti',
                'a.penerimaantrucking_nobukti',
                'a.pengeluarantrucking_id',
                'a.bank_id',
                'a.trado_id',
                'a.trado',
                'a.tradoheader_id',
                'a.supirheader',
                'a.supir',
                'a.karyawan',
                'a.gandengan',
                'a.pengeluarantrucking_nobukti',
                'a.tglbukacetak',
                'a.statuscetak',
                'a.userbukacetak',
                'a.tglkirimberkas',
                'a.statuskirimberkas',
                'a.userkirimberkas',
                'a.coa',
                'a.tgldariheaderpengeluaranheader',
                'a.tglsampaiheaderpengeluaranheader',
                'a.statusposting',
                'a.qty',
                'a.harga',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
                'a.pengeluaranbank_id',
            );

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function findAll($id)
    {
        $cek  = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
            ->select('pengeluarantrucking_id', 'statuscabang')
            ->where('id', $id)->first();
        if ($cek->pengeluarantrucking_id == 7) {
            if ($cek->statuscabang == 516) {
                $tabelTrado = (new Trado())->getTNLForKlaim();
                $tabelGandengan = (new Gandengan())->getTNLForKlaim();
                $query = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
                    ->select(
                        'pengeluarantruckingheader.id',
                        'pengeluarantruckingheader.nobukti',
                        'pengeluarantruckingheader.tglbukti',
                        'pengeluarantruckingheader.pengeluarantrucking_id',
                        'pengeluarantrucking.keterangan as pengeluarantrucking',
                        'pengeluarantrucking.kodepengeluaran as kodepengeluaran',
                        'pengeluarantruckingheader.bank_id',
                        'bank.namabank as bank',
                        'pengeluarantruckingheader.supir_id',
                        'pengeluarantruckingheader.supir_id as supirheader_id',
                        'trado.keterangan as trado',
                        'pengeluarantruckingheader.tradotnl_id as tradoheader_id',
                        'gandengan.keterangan as gandengan',
                        'pengeluarantruckingheader.gandengantnl_id as gandenganheader_id',
                        'supir.namasupir as supirheader',
                        'supir.namasupir as supir',
                        'pengeluarantruckingheader.pengeluarantrucking_nobukti',
                        'pengeluarantruckingheader.statusposting',
                        'pengeluarantruckingheader.statuscabang',
                        'pengeluarantruckingheader.coa',
                        'pengeluarantruckingheader.periodedari',
                        'pengeluarantruckingheader.periodesampai',
                        'pengeluarantruckingheader.periode',
                        'akunpusat.keterangancoa',
                        'pengeluarantruckingheader.pengeluaran_nobukti',
                        'pengeluarantruckingheader.jenisorder_id as jenisorderan_id',
                        'jenisorder.keterangan as jenisorderan'
                    )
                    ->leftJoin(DB::raw("pengeluarantrucking with (readuncommitted)"), 'pengeluarantruckingheader.pengeluarantrucking_id', 'pengeluarantrucking.id')
                    ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluarantruckingheader.bank_id', 'bank.id')
                    ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingheader.supir_id', 'supir.id')
                    ->leftJoin(DB::raw("$tabelTrado as trado with (readuncommitted)"), 'pengeluarantruckingheader.tradotnl_id', 'trado.id')
                    ->leftJoin(DB::raw("$tabelGandengan as gandengan with (readuncommitted)"), 'pengeluarantruckingheader.gandengantnl_id', 'gandengan.id')
                    ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'pengeluarantruckingheader.jenisorder_id', 'jenisorder.id')
                    ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pengeluarantruckingheader.coa', 'akunpusat.coa')
                    ->where('pengeluarantruckingheader.id', '=', $id);
            } else {

                $query = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
                    ->select(
                        'pengeluarantruckingheader.id',
                        'pengeluarantruckingheader.nobukti',
                        'pengeluarantruckingheader.tglbukti',
                        'pengeluarantruckingheader.pengeluarantrucking_id',
                        'pengeluarantrucking.keterangan as pengeluarantrucking',
                        'pengeluarantrucking.kodepengeluaran as kodepengeluaran',
                        'pengeluarantruckingheader.bank_id',
                        'bank.namabank as bank',
                        'pengeluarantruckingheader.supir_id',
                        'pengeluarantruckingheader.supir_id as supirheader_id',
                        'pengeluarantruckingheader.trado_id',
                        'trado.keterangan as trado',
                        'pengeluarantruckingheader.trado_id as tradoheader_id',
                        'pengeluarantruckingheader.gandengan_id',
                        'gandengan.keterangan as gandengan',
                        'pengeluarantruckingheader.gandengan_id as gandenganheader_id',
                        'supir.namasupir as supirheader',
                        'supir.namasupir as supir',
                        'pengeluarantruckingheader.pengeluarantrucking_nobukti',
                        'pengeluarantruckingheader.statusposting',
                        'pengeluarantruckingheader.statuscabang',
                        'pengeluarantruckingheader.coa',
                        'pengeluarantruckingheader.periodedari',
                        'pengeluarantruckingheader.periodesampai',
                        'pengeluarantruckingheader.periode',
                        'akunpusat.keterangancoa',
                        'pengeluarantruckingheader.pengeluaran_nobukti',
                        'pengeluarantruckingheader.jenisorder_id as jenisorderan_id',
                        'jenisorder.keterangan as jenisorderan'
                    )
                    ->leftJoin(DB::raw("pengeluarantrucking with (readuncommitted)"), 'pengeluarantruckingheader.pengeluarantrucking_id', 'pengeluarantrucking.id')
                    ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluarantruckingheader.bank_id', 'bank.id')
                    ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingheader.supir_id', 'supir.id')
                    ->leftJoin(DB::raw("trado with (readuncommitted)"), 'pengeluarantruckingheader.trado_id', 'trado.id')
                    ->leftJoin(DB::raw("gandengan with (readuncommitted)"), 'pengeluarantruckingheader.gandengan_id', 'gandengan.id')
                    ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'pengeluarantruckingheader.jenisorder_id', 'jenisorder.id')
                    ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pengeluarantruckingheader.coa', 'akunpusat.coa')
                    ->where('pengeluarantruckingheader.id', '=', $id);
            }

            $data = $query->first();
        } else {

            $query = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
                ->select(
                    'pengeluarantruckingheader.id',
                    'pengeluarantruckingheader.nobukti',
                    'pengeluarantruckingheader.tglbukti',
                    'pengeluarantruckingheader.pengeluarantrucking_id',
                    'pengeluarantrucking.keterangan as pengeluarantrucking',
                    'pengeluarantrucking.kodepengeluaran as kodepengeluaran',
                    'pengeluarantruckingheader.bank_id',
                    'bank.namabank as bank',
                    'pengeluarantruckingheader.agen_id',
                    'agen.namaagen as agen',
                    'pengeluarantruckingheader.container_id as containerheader_id',
                    'container.keterangan as containerheader',
                    'pengeluarantruckingheader.supir_id',
                    'pengeluarantruckingheader.supir_id as supirheader_id',
                    'pengeluarantruckingheader.karyawan_id as karyawanheader_id',
                    'pengeluarantruckingheader.trado_id',
                    'trado.keterangan as trado',
                    'pengeluarantruckingheader.trado_id as tradoheader_id',
                    'pengeluarantruckingheader.gandengan_id',
                    'gandengan.keterangan as gandengan',
                    'pengeluarantruckingheader.gandengan_id as gandenganheader_id',
                    'supir.namasupir as supirheader',
                    'supir.namasupir as supir',
                    'karyawan.namakaryawan as karyawanheader',
                    'pengeluarantruckingheader.pengeluarantrucking_nobukti',
                    'pengeluarantruckingheader.statusposting',
                    'pengeluarantruckingheader.coa',
                    'pengeluarantruckingheader.periodedari',
                    'pengeluarantruckingheader.periodesampai',
                    'pengeluarantruckingheader.periode',
                    'akunpusat.keterangancoa',
                    'pengeluarantruckingheader.pengeluaran_nobukti',
                    'pengeluarantruckingheader.jenisorder_id as jenisorderan_id',
                    'jenisorder.keterangan as jenisorderan'
                )
                ->leftJoin(DB::raw("pengeluarantrucking with (readuncommitted)"), 'pengeluarantruckingheader.pengeluarantrucking_id', 'pengeluarantrucking.id')
                ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluarantruckingheader.bank_id', 'bank.id')
                ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingheader.supir_id', 'supir.id')
                ->leftJoin(DB::raw("karyawan with (readuncommitted)"), 'pengeluarantruckingheader.karyawan_id', 'karyawan.id')
                ->leftJoin(DB::raw("trado with (readuncommitted)"), 'pengeluarantruckingheader.trado_id', 'trado.id')
                ->leftJoin(DB::raw("gandengan with (readuncommitted)"), 'pengeluarantruckingheader.gandengan_id', 'gandengan.id')
                ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'pengeluarantruckingheader.jenisorder_id', 'jenisorder.id')
                ->leftJoin(DB::raw("agen with (readuncommitted)"), 'pengeluarantruckingheader.agen_id', 'agen.id')
                ->leftJoin(DB::raw("container with (readuncommitted)"), 'pengeluarantruckingheader.container_id', 'container.id')
                ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pengeluarantruckingheader.coa', 'akunpusat.coa')
                ->where('pengeluarantruckingheader.id', '=', $id);


            $data = $query->first();
        }
        return $data;
    }

    public function getTarikDeposito($id, $supir_id)
    {
        $tempPribadi = $this->createTempTarikDeposito($id, $supir_id);
        $tempAll = $this->createTempDeposito($id, $supir_id);

        $temp = '##tempGet' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $deposito = DB::table($tempPribadi)->from(DB::raw("$tempPribadi with (readuncommitted)"))
            ->select(DB::raw("pengeluarantruckingheader_id,nobukti,keterangan,sisa,bayar"));

        Schema::create($temp, function ($table) {
            $table->bigInteger('pengeluarantruckingheader_id')->nullable();
            $table->string('nobukti');
            $table->string('keterangan')->nullable();
            $table->bigInteger('sisa')->nullable();
            $table->bigInteger('bayar')->nullable();
        });
        DB::table($temp)->insertUsing(['pengeluarantruckingheader_id', 'nobukti', 'keterangan', 'sisa', 'bayar'], $deposito);

        $pinjaman = DB::table($tempAll)->from(DB::raw("$tempAll with (readuncommitted)"))
            ->select(DB::raw("null as pengeluarantruckingheader_id,nobukti,keterangan,sisa, 0 as bayar"));
        DB::table($temp)->insertUsing(['pengeluarantruckingheader_id', 'nobukti', 'keterangan', 'sisa', 'bayar'], $pinjaman);

        $data = DB::table($temp)->from(DB::raw("$temp with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $temp.nobukti) as id,pengeluarantruckingheader_id,nobukti,keterangan,sisa,bayar as nominal"))
            ->get();

        return $data;
    }

    public function createTempDeposito($id, $supir_id)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetch = DB::table('penerimaantruckingdetail')
            ->from(
                DB::raw("penerimaantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("penerimaantruckingdetail.nobukti,penerimaantruckingdetail.keterangan,
        (SELECT (penerimaantruckingdetail.nominal - coalesce(SUM(pengeluarantruckingdetail.nominal),0)) FROM pengeluarantruckingdetail WHERE pengeluarantruckingdetail.penerimaantruckingheader_nobukti= penerimaantruckingdetail.nobukti) AS sisa"))
            ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingheader.nobukti', 'penerimaantruckingdetail.nobukti')
            ->whereRaw("penerimaantruckingdetail.supir_id = $supir_id")
            ->where("penerimaantruckingheader.penerimaantrucking_id", 3)
            ->whereRaw("penerimaantruckingheader.nobukti not in (select penerimaantruckingheader_nobukti from pengeluarantruckingdetail where pengeluarantruckingheader_id=$id)")
            ->orderBy('penerimaantruckingheader.tglbukti', 'asc')
            ->orderBy('penerimaantruckingdetail.nobukti', 'asc');
        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->string('keterangan');
            $table->bigInteger('sisa')->nullable();
        });
        // return $fetch->get();
        $tes = DB::table($temp)->insertUsing(['nobukti', 'keterangan', 'sisa'], $fetch);
        return $temp;
    }

    public function createTempTarikDeposito($id, $supir_id)
    {

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = DB::table('penerimaantruckingdetail')
            ->from(
                DB::raw("penerimaantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("pengeluarantruckingdetail.pengeluarantruckingheader_id,penerimaantruckingdetail.nobukti,pengeluarantruckingdetail.keterangan,pengeluarantruckingdetail.nominal as bayar ,(SELECT (penerimaantruckingdetail.nominal - coalesce(SUM(pengeluarantruckingdetail.nominal),0)) FROM pengeluarantruckingdetail WHERE pengeluarantruckingdetail.penerimaantruckingheader_nobukti= penerimaantruckingdetail.nobukti) AS sisa"))
            ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingheader.nobukti', 'penerimaantruckingdetail.nobukti')
            ->leftJoin(DB::raw("pengeluarantruckingdetail with (readuncommitted)"), 'pengeluarantruckingdetail.penerimaantruckingheader_nobukti', 'penerimaantruckingdetail.nobukti')
            ->whereRaw("penerimaantruckingdetail.supir_id = $supir_id")
            ->where("penerimaantruckingheader.penerimaantrucking_id", 3)
            ->where("pengeluarantruckingdetail.pengeluarantruckingheader_id", $id)
            ->orderBy('penerimaantruckingheader.tglbukti', 'asc')
            ->orderBy('penerimaantruckingdetail.nobukti', 'asc');

        Schema::create($temp, function ($table) {
            $table->bigInteger('pengeluarantruckingheader_id')->nullable();
            $table->string('nobukti');
            $table->string('keterangan');
            $table->bigInteger('bayar')->nullable();
            $table->bigInteger('sisa')->nullable();
        });
        $tes = DB::table($temp)->insertUsing(['pengeluarantruckingheader_id', 'nobukti', 'keterangan', 'bayar', 'sisa'], $fetch);
        return $temp;
    }

    public function getDeleteTarikDeposito($id, $supir_id)
    {
        $tempPribadi = $this->createTempTarikDeposito($id, $supir_id);

        $data = DB::table($tempPribadi)->from(DB::raw("$tempPribadi with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $tempPribadi.nobukti) as id,pengeluarantruckingheader_id,nobukti,keterangan,sisa,bayar as nominal"))
            ->get();

        return $data;
    }

    public function getTarikDepositoKaryawan($id, $karyawan_id)
    {
        $tempPribadi = $this->createTempTarikDepositoKaryawan($id, $karyawan_id);
        $tempAll = $this->createTempDepositoKaryawan($id, $karyawan_id);

        $temp = '##tempGet' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $deposito = DB::table($tempPribadi)->from(DB::raw("$tempPribadi with (readuncommitted)"))
            ->select(DB::raw("pengeluarantruckingheader_id,nobukti,keterangan,sisa,bayar"));

        Schema::create($temp, function ($table) {
            $table->bigInteger('pengeluarantruckingheader_id')->nullable();
            $table->string('nobukti');
            $table->string('keterangan')->nullable();
            $table->bigInteger('sisa')->nullable();
            $table->bigInteger('bayar')->nullable();
        });
        DB::table($temp)->insertUsing(['pengeluarantruckingheader_id', 'nobukti', 'keterangan', 'sisa', 'bayar'], $deposito);

        $pinjaman = DB::table($tempAll)->from(DB::raw("$tempAll with (readuncommitted)"))
            ->select(DB::raw("null as pengeluarantruckingheader_id,nobukti,keterangan,sisa, 0 as bayar"));
        DB::table($temp)->insertUsing(['pengeluarantruckingheader_id', 'nobukti', 'keterangan', 'sisa', 'bayar'], $pinjaman);

        $data = DB::table($temp)->from(DB::raw("$temp with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $temp.nobukti) as id,pengeluarantruckingheader_id,nobukti,keterangan,sisa,bayar as nominal"))
            ->get();

        return $data;
    }

    public function createTempDepositoKaryawan($id, $karyawan_id)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetch = DB::table('penerimaantruckingdetail')
            ->from(
                DB::raw("penerimaantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("penerimaantruckingdetail.nobukti,penerimaantruckingdetail.keterangan,
        (SELECT (penerimaantruckingdetail.nominal - coalesce(SUM(pengeluarantruckingdetail.nominal),0)) FROM pengeluarantruckingdetail WHERE pengeluarantruckingdetail.penerimaantruckingheader_nobukti= penerimaantruckingdetail.nobukti) AS sisa"))
            ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingheader.nobukti', 'penerimaantruckingdetail.nobukti')
            ->whereRaw("penerimaantruckingdetail.karyawan_id = $karyawan_id")
            ->where("penerimaantruckingheader.penerimaantrucking_id", 6)
            ->whereRaw("penerimaantruckingheader.nobukti not in (select penerimaantruckingheader_nobukti from pengeluarantruckingdetail where pengeluarantruckingheader_id=$id)")
            ->orderBy('penerimaantruckingheader.tglbukti', 'asc')
            ->orderBy('penerimaantruckingdetail.nobukti', 'asc');
        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->string('keterangan');
            $table->bigInteger('sisa')->nullable();
        });
        // return $fetch->get();
        $tes = DB::table($temp)->insertUsing(['nobukti', 'keterangan', 'sisa'], $fetch);
        return $temp;
    }

    public function createTempTarikDepositoKaryawan($id, $karyawan_id)
    {

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = DB::table('penerimaantruckingdetail')
            ->from(
                DB::raw("penerimaantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("pengeluarantruckingdetail.pengeluarantruckingheader_id,penerimaantruckingdetail.nobukti,pengeluarantruckingdetail.keterangan,pengeluarantruckingdetail.nominal as bayar ,(SELECT (penerimaantruckingdetail.nominal - coalesce(SUM(pengeluarantruckingdetail.nominal),0)) FROM pengeluarantruckingdetail WHERE pengeluarantruckingdetail.penerimaantruckingheader_nobukti= penerimaantruckingdetail.nobukti) AS sisa"))
            ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingheader.nobukti', 'penerimaantruckingdetail.nobukti')
            ->leftJoin(DB::raw("pengeluarantruckingdetail with (readuncommitted)"), 'pengeluarantruckingdetail.penerimaantruckingheader_nobukti', 'penerimaantruckingdetail.nobukti')
            ->whereRaw("penerimaantruckingdetail.karyawan_id = $karyawan_id")
            ->where("penerimaantruckingheader.penerimaantrucking_id", 6)
            ->where("pengeluarantruckingdetail.pengeluarantruckingheader_id", $id)
            ->orderBy('penerimaantruckingheader.tglbukti', 'asc')
            ->orderBy('penerimaantruckingdetail.nobukti', 'asc');

        Schema::create($temp, function ($table) {
            $table->bigInteger('pengeluarantruckingheader_id')->nullable();
            $table->string('nobukti');
            $table->string('keterangan');
            $table->bigInteger('bayar')->nullable();
            $table->bigInteger('sisa')->nullable();
        });
        $tes = DB::table($temp)->insertUsing(['pengeluarantruckingheader_id', 'nobukti', 'keterangan', 'bayar', 'sisa'], $fetch);
        return $temp;
    }

    public function getDeleteTarikDepositokaryawan($id, $karyawan_id)
    {
        $tempPribadi = $this->createTempTarikDepositoKaryawan($id, $karyawan_id);

        $data = DB::table($tempPribadi)->from(DB::raw("$tempPribadi with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $tempPribadi.nobukti) as id,pengeluarantruckingheader_id,nobukti,keterangan,sisa,bayar as nominal"))
            ->get();

        return $data;
    }

    public function getEditPelunasan($id, $periodedari, $periodesampai)
    {
        $tempPribadi = $this->createTempEditPelunasan($id, $periodedari, $periodesampai);
        $tempAll = $this->createTempPelunasan($id, $periodedari, $periodesampai);
        $temp = '##tempGet' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $pelunasan = DB::table($tempPribadi)->from(DB::raw("$tempPribadi with (readuncommitted)"))
            ->select(DB::raw("pengeluarantruckingheader_id,nobukti,keterangan,sisa,bayar"));

        Schema::create($temp, function ($table) {
            $table->bigInteger('pengeluarantruckingheader_id')->nullable();
            $table->string('nobukti');
            $table->longText('keterangan')->nullable();
            $table->bigInteger('sisa')->nullable();
            $table->bigInteger('bayar')->nullable();
        });

        DB::table($temp)->insertUsing(['pengeluarantruckingheader_id', 'nobukti', 'keterangan', 'sisa', 'bayar'], $pelunasan);


        $pinjaman = DB::table($tempAll)->from(DB::raw("$tempAll with (readuncommitted)"))
            ->select(DB::raw("null as pengeluarantruckingheader_id,nobukti,keterangan,sisa, 0 as bayar"))

            ->where(function ($pinjaman) use ($tempAll) {
                $pinjaman->whereRaw("$tempAll.sisa != 0")
                    ->orWhereRaw("$tempAll.sisa is null");
            });
        DB::table($temp)->insertUsing(['pengeluarantruckingheader_id', 'nobukti', 'keterangan', 'sisa', 'bayar'], $pinjaman);


        $data = DB::table($temp)->from(DB::raw("$temp with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $temp.nobukti) as id,pengeluarantruckingheader_id,nobukti,keterangan,sisa,bayar as nominal"))
            ->get();

        // echo json_encode($data);
        // die;

        return $data;
    }

    public function createTempPelunasan($id, $periodedari, $periodesampai)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetch = DB::table('penerimaantruckingdetail')
            ->from(
                DB::raw("penerimaantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("penerimaantruckingdetail.nobukti,  MAX(penerimaantruckingdetail.keterangan) as keterangan,
        (SELECT (SUM(penerimaantruckingdetail.nominal) - coalesce(SUM(pengeluarantruckingdetail.nominal),0)) FROM pengeluarantruckingdetail WHERE pengeluarantruckingdetail.penerimaantruckingheader_nobukti= penerimaantruckingdetail.nobukti) AS sisa"))
            ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingheader.nobukti', 'penerimaantruckingdetail.nobukti')
            ->whereBetween('penerimaantruckingheader.tglbukti', [date('Y-m-d', strtotime($periodedari)), date('Y-m-d', strtotime($periodesampai))])
            ->whereRaw("penerimaantruckingheader.nobukti not in (select penerimaantruckingheader_nobukti from pengeluarantruckingdetail where pengeluarantruckingheader_id=$id)")
            ->where("penerimaantruckingdetail.nobukti",  'LIKE', "%BBM%")
            ->groupBy('penerimaantruckingdetail.nobukti', 'penerimaantruckingheader.tglbukti')
            ->orderBy('penerimaantruckingheader.tglbukti', 'asc')
            ->orderBy('penerimaantruckingdetail.nobukti', 'asc');

        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->longText('keterangan')->nullable();
            $table->bigInteger('sisa')->nullable();
        });
        // return $fetch->get();
        $tes = DB::table($temp)->insertUsing(['nobukti', 'keterangan', 'sisa'], $fetch);
        return $temp;
    }

    public function createTempEditPelunasan($id, $periodedari, $periodesampai)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = DB::table('penerimaantruckingdetail')
            ->from(
                DB::raw("penerimaantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("max(pengeluarantruckingdetail.pengeluarantruckingheader_id) as pengeluarantruckingheader_id, penerimaantruckingdetail.nobukti, max(penerimaantruckingdetail.keterangan) as keterangan, max(pengeluarantruckingdetail.nominal) as bayar ,(SELECT (sum(penerimaantruckingdetail.nominal) - coalesce(SUM(pengeluarantruckingdetail.nominal),0)) FROM pengeluarantruckingdetail WHERE pengeluarantruckingdetail.penerimaantruckingheader_nobukti= penerimaantruckingdetail.nobukti) AS sisa"))
            ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingheader.nobukti', 'penerimaantruckingdetail.nobukti')
            ->leftJoin(DB::raw("pengeluarantruckingdetail with (readuncommitted)"), 'pengeluarantruckingdetail.penerimaantruckingheader_nobukti', 'penerimaantruckingdetail.nobukti')
            ->whereBetween('penerimaantruckingheader.tglbukti', [date('Y-m-d', strtotime($periodedari)), date('Y-m-d', strtotime($periodesampai))])
            ->where("pengeluarantruckingdetail.pengeluarantruckingheader_id", $id)
            ->where("penerimaantruckingdetail.nobukti",  'LIKE', "%BBM%")
            ->groupBy('penerimaantruckingdetail.nobukti')
            ->orderBy('penerimaantruckingdetail.nobukti', 'asc');

        Schema::create($temp, function ($table) {
            $table->bigInteger('pengeluarantruckingheader_id')->nullable();
            $table->string('nobukti');
            $table->longText('keterangan');
            $table->bigInteger('bayar')->nullable();
            $table->bigInteger('sisa')->nullable();
        });
        $tes = DB::table($temp)->insertUsing(['pengeluarantruckingheader_id', 'nobukti', 'keterangan', 'bayar', 'sisa'], $fetch);
        return $temp;

        echo json_encode($temp);
        die;
    }

    public function getDeleteEditPelunasan($id, $periodedari, $periodesampai)
    {
        $tempPribadi = $this->createTempEditPelunasan($id, $periodedari, $periodesampai);

        $data = DB::table($tempPribadi)->from(DB::raw("$tempPribadi with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $tempPribadi.nobukti) as id,pengeluarantruckingheader_id,nobukti,keterangan,sisa,bayar as nominal"))
            ->get();

        return $data;
    }

    // public function getTarikDeposito($id){
    //     $penerimaantrucking = DB::table($this->table)->from(DB::raw("penerimaantrucking with (readuncommitted)"))->where('kodepenerimaan','DPO')->first();
    //     // return $pengeluarantruckingheader->id;
    //     $query = DB::table($this->table)->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
    //     ->select(
    //         DB::raw("row_number() Over(Order By pengeluarantruckingdetail.id) as id"),
    //         // 'pengeluarantruckingheader.id',
    //         'pengeluarantruckingdetail.penerimaantruckingheader_nobukti as nobukti',
    //         // 'pengeluarantruckingdetail.tglbukti',
    //         'pengeluarantruckingdetail.keterangan',
    //         'pengeluarantruckingdetail.nominal'
    //     )
    //     ->where('pengeluarantruckingdetail.pengeluarantruckingheader_id',$id);


    //     return $query->get();
    // }

    // public function getPinjaman($supir_id)
    // {
    //     $penerimaantrucking = DB::table($this->table)->from(DB::raw("pengeluarantrucking with (readuncommitted)"))->where('kodepengeluaran','PJT')->first();
    //     // return response($penerimaantrucking->id,422);
    //     $query = DB::table($this->table)->from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
    //     ->select(
    //         DB::raw("row_number() Over(Order By pengeluarantruckingheader.id) as id"),
    //         'pengeluarantruckingheader.nobukti',
    //         'pengeluarantruckingheader.tglbukti',
    //         'pengeluarantruckingdetail.keterangan',
    //         // 'pengeluarantruckingdetail.nominal',
    //         DB::raw("sum(pengeluarantruckingdetail.nominal) as nominal")
    //     )
    //     ->where('pengeluarantruckingheader.pengeluarantrucking_id',$penerimaantrucking->id)
    //     ->where('pengeluarantruckingdetail.supir_id',$supir_id)
    //     ->leftJoin(DB::raw("pengeluarantruckingdetail with (readuncommitted)"), 'pengeluarantruckingdetail.pengeluarantruckingheader_id','pengeluarantruckingheader.id')
    //     ->groupBy(
    //         'pengeluarantruckingheader.id',
    //         'pengeluarantruckingheader.nobukti',
    //         'pengeluarantruckingheader.tglbukti',
    //         'pengeluarantruckingdetail.keterangan',
    //     );

    //     return $query->get();
    // }

    public function getEditInvoice($id, $tgldari, $tglsampai)
    {
        $this->setRequestParameters();
        $temp = '##tempGet' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $get = DB::table('pengeluarantruckingdetail')->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
            ->select(
                DB::raw("pengeluarantruckingdetail.id as pengeluarantrucking_id"),
                DB::raw("pengeluarantruckingdetail.invoice_nobukti as noinvoice_detail"),
                DB::raw("pengeluarantruckingdetail.orderantrucking_nobukti as nojobtrucking_detail"),
                DB::raw("container.keterangan as container_detail"),
                DB::raw("pengeluarantruckingdetail.nominal as nominal_detail"),
            )
            ->leftJoin(DB::raw("orderantrucking as ot with (readuncommitted)"), 'pengeluarantruckingdetail.orderantrucking_nobukti', 'ot.nobukti')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'ot.container_id', 'container.id')
            ->where('pengeluarantruckingdetail.pengeluarantruckingheader_id', '=', $id);

        Schema::create($temp, function ($table) {
            $table->bigInteger('pengeluarantrucking_id')->nullable();
            $table->string('noinvoice_detail');
            $table->string('nojobtrucking_detail')->nullable();
            $table->string('container_detail')->nullable();
            $table->bigInteger('nominal_detail')->nullable();
        });
        DB::table($temp)->insertUsing(['pengeluarantrucking_id', 'noinvoice_detail', 'nojobtrucking_detail', 'container_detail', 'nominal_detail'], $get);

        $fetch = InvoiceDetail::from(DB::raw("invoicedetail with (readuncommitted)"))
            ->select(DB::raw("
            null as pengeluarantrucking_id,
            invoicedetail.nobukti as noinvoice_detail,
            invoicedetail.orderantrucking_nobukti as nojobtrucking_detail,
            container.keterangan as container_detail,
            (case when container.nominalsumbangan IS NULL then 0 else container.nominalsumbangan end) as nominal_detail

            "))

            ->leftJoin(DB::raw("invoiceheader with (readuncommitted)"), 'invoicedetail.invoice_id', 'invoiceheader.id')
            ->leftJoin(DB::raw("orderantrucking as ot with (readuncommitted)"), 'invoicedetail.orderantrucking_nobukti', 'ot.nobukti')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'ot.container_id', 'container.id')
            ->whereRaw("invoicedetail.orderantrucking_nobukti not in (select orderantrucking_nobukti from pengeluarantruckingdetail where orderantrucking_nobukti != '')")
            ->whereBetween('invoiceheader.tglbukti', [date('Y-m-d', strtotime($tgldari)), date('Y-m-d', strtotime($tglsampai))]);

        DB::table($temp)->insertUsing(['pengeluarantrucking_id', 'noinvoice_detail', 'nojobtrucking_detail', 'container_detail', 'nominal_detail'], $fetch);


        $query = DB::table($temp)->from(DB::raw("$temp with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $temp.noinvoice_detail) as id_detail,pengeluarantrucking_id,noinvoice_detail,nojobtrucking_detail,container_detail,nominal_detail"));

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $this->totalNominal = $query->sum('nominal_detail');

        $this->paginate($query);
        return $query->get();
    }
    public function getShowInvoice($id, $tgldari, $tglsampai)
    {
        $aksi = request()->aksi ?? '';
        $this->setRequestParameters();
        $temp = '##tempGet' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = DB::table('pengeluarantruckingdetail')->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
            ->select(
                DB::raw("pengeluarantruckingdetail.id as pengeluarantrucking_id"),
                DB::raw("pengeluarantruckingdetail.invoice_nobukti as noinvoice_detail"),
                DB::raw("pengeluarantruckingdetail.orderantrucking_nobukti as nojobtrucking_detail"),
                DB::raw("container.keterangan as container_detail"),
                DB::raw("pengeluarantruckingdetail.nominal as nominal_detail"),
            )
            ->leftJoin(DB::raw("orderantrucking as ot with (readuncommitted)"), 'pengeluarantruckingdetail.orderantrucking_nobukti', 'ot.nobukti')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'ot.container_id', 'container.id')
            ->where('pengeluarantruckingdetail.pengeluarantruckingheader_id', '=', $id);


        Schema::create($temp, function ($table) {
            $table->bigInteger('pengeluarantrucking_id')->nullable();
            $table->string('noinvoice_detail');
            $table->string('nojobtrucking_detail')->nullable();
            $table->string('container_detail')->nullable();
            $table->bigInteger('nominal_detail')->nullable();
        });
        DB::table($temp)->insertUsing(['pengeluarantrucking_id', 'noinvoice_detail', 'nojobtrucking_detail', 'container_detail', 'nominal_detail'], $fetch);

        $query = DB::table($temp)->from(DB::raw("$temp with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $temp.noinvoice_detail) as id_detail,pengeluarantrucking_id,noinvoice_detail,nojobtrucking_detail,container_detail,nominal_detail"));
        if ($this->params['sortIndex'] == 'id') {
            $query->orderBy($temp . '.nojobtrucking_detail', $this->params['sortOrder']);
        } else {
            $query->orderBy($temp . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }

        $this->totalNominal = $query->sum('nominal_detail');
        if ($aksi == 'show') {

            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
            // $this->filter($query);
            $this->paginate($query);
        }
        return $query->get();
    }

    public function getEditOtok($aksi, $id, $tgldari, $tglsampai, $agen_id, $container_id)
    {
        $this->setRequestParameters();
        $temp = '##tempGet' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $get = DB::table('pengeluarantruckingdetail')->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
            ->select(
                DB::raw("pengeluarantruckingdetail.id as pengeluarantrucking_id"),
                DB::raw("pengeluarantruckingdetail.invoice_nobukti as noinvoice_detail"),
                DB::raw("pengeluarantruckingdetail.orderantrucking_nobukti as nojobtrucking_detail"),
                DB::raw("pengeluarantruckingdetail.nominal as nominal_detail"),
            )
            ->where('pengeluarantruckingdetail.pengeluarantruckingheader_id', '=', $id);

        Schema::create($temp, function ($table) {
            $table->bigInteger('pengeluarantrucking_id')->nullable();
            $table->string('noinvoice_detail');
            $table->string('nojobtrucking_detail')->nullable();
            $table->bigInteger('nominal_detail')->nullable();
        });
        DB::table($temp)->insertUsing(['pengeluarantrucking_id', 'noinvoice_detail', 'nojobtrucking_detail', 'nominal_detail'], $get);

        if ($aksi != 'show') {

            $fetch = InvoiceDetail::from(DB::raw("invoicedetail with (readuncommitted)"))
                ->select(DB::raw("
            null as pengeluarantrucking_id,
            invoicedetail.nobukti as noinvoice_detail,
            invoicedetail.orderantrucking_nobukti as nojobtrucking_detail, 
            (case when otobon.nominal IS NULL then 0 else otobon.nominal end) as nominal_detail
            "))

                ->leftJoin(DB::raw("invoiceheader with (readuncommitted)"), 'invoicedetail.invoice_id', 'invoiceheader.id')
                ->leftJoin(DB::raw("otobon with (readuncommitted)"), 'invoiceheader.agen_id', 'otobon.agen_id')
                ->whereRaw("invoicedetail.orderantrucking_nobukti not in (select orderantrucking_nobukti from pengeluarantruckingdetail where orderantrucking_nobukti != '' and pengeluarantruckingdetail.pengeluarantruckingheader_id = $id)")
                ->whereBetween('invoiceheader.tglbukti', [date('Y-m-d', strtotime($tgldari)), date('Y-m-d', strtotime($tglsampai))])
                ->where('otobon.agen_id', $agen_id)
                ->where('otobon.container_id', $container_id);
            DB::table($temp)->insertUsing(['pengeluarantrucking_id', 'noinvoice_detail', 'nojobtrucking_detail', 'nominal_detail'], $fetch);
        }

        $query = DB::table($temp)->from(DB::raw("$temp with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $temp.noinvoice_detail) as id_detail,pengeluarantrucking_id,noinvoice_detail,nojobtrucking_detail,nominal_detail"));

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $this->totalNominal = $query->sum('nominal_detail');

        $this->paginate($query);
        return $query->get();
    }

    public function getEditOtol($aksi, $id, $tgldari, $tglsampai, $agen_id, $container_id)
    {
        $this->setRequestParameters();
        $temp = '##tempGet' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $get = DB::table('pengeluarantruckingdetail')->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
            ->select(
                DB::raw("pengeluarantruckingdetail.id as pengeluarantrucking_id"),
                DB::raw("pengeluarantruckingdetail.invoice_nobukti as noinvoice_detail"),
                DB::raw("pengeluarantruckingdetail.orderantrucking_nobukti as nojobtrucking_detail"),
                DB::raw("pengeluarantruckingdetail.nominal as nominal_detail"),
            )
            ->where('pengeluarantruckingdetail.pengeluarantruckingheader_id', '=', $id);

        Schema::create($temp, function ($table) {
            $table->bigInteger('pengeluarantrucking_id')->nullable();
            $table->string('noinvoice_detail');
            $table->string('nojobtrucking_detail')->nullable();
            $table->bigInteger('nominal_detail')->nullable();
        });
        DB::table($temp)->insertUsing(['pengeluarantrucking_id', 'noinvoice_detail', 'nojobtrucking_detail', 'nominal_detail'], $get);

        if ($aksi != 'show') {

            $fetch = InvoiceDetail::from(DB::raw("invoicedetail with (readuncommitted)"))
                ->select(DB::raw("
            null as pengeluarantrucking_id,
            invoicedetail.nobukti as noinvoice_detail,
            invoicedetail.orderantrucking_nobukti as nojobtrucking_detail, 
            (case when lapangan.nominal IS NULL then 0 else lapangan.nominal end) as nominal_detail
            "))

                ->leftJoin(DB::raw("invoiceheader with (readuncommitted)"), 'invoicedetail.invoice_id', 'invoiceheader.id')
                ->leftJoin(DB::raw("lapangan with (readuncommitted)"), 'invoiceheader.agen_id', 'lapangan.agen_id')
                ->whereRaw("invoicedetail.orderantrucking_nobukti not in (select orderantrucking_nobukti from pengeluarantruckingdetail where orderantrucking_nobukti != '' and pengeluarantruckingdetail.pengeluarantruckingheader_id = $id)")
                ->whereBetween('invoiceheader.tglbukti', [date('Y-m-d', strtotime($tgldari)), date('Y-m-d', strtotime($tglsampai))])
                ->where('lapangan.agen_id', $agen_id)
                ->where('lapangan.container_id', $container_id);
            DB::table($temp)->insertUsing(['pengeluarantrucking_id', 'noinvoice_detail', 'nojobtrucking_detail', 'nominal_detail'], $fetch);
        }

        $query = DB::table($temp)->from(DB::raw("$temp with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $temp.noinvoice_detail) as id_detail,pengeluarantrucking_id,noinvoice_detail,nojobtrucking_detail,nominal_detail"));

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $this->totalNominal = $query->sum('nominal_detail');

        $this->paginate($query);
        return $query->get();
    }

    public function pengeluarantruckingdetail()
    {
        return $this->hasMany(PengeluaranTruckingDetail::class, 'pengeluarantruckingheader_id');
    }

    public function selectColumns()
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->integer('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->string('modifiedby', 200)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->string('pengeluaran_nobukti', 50)->nullable();
            $table->longText('penerimaantrucking_nobukti')->nullable();
            $table->longText('nobuktipenerimaan')->nullable();
            $table->integer('pengeluarantruckingid')->nullable();
            $table->string('pengeluarantrucking_id', 100)->nullable();
            $table->string('bank_id', 50)->nullable();
            $table->integer('trado_id')->nullable();
            $table->string('trado', 200)->nullable();
            $table->integer('tradoheader_id')->nullable();
            $table->string('supirheader', 200)->nullable();
            $table->string('supir', 200)->nullable();
            $table->string('karyawan', 200)->nullable();
            $table->string('gandengan', 50)->nullable();
            $table->string('pengeluarantrucking_nobukti', 50)->nullable();
            $table->dateTime('tglbukacetak')->nullable();
            $table->longText('statuscetak')->nullable();
            $table->longText('statuscetaktext')->nullable();
            $table->string('userbukacetak', 200)->nullable();
            $table->dateTime('tglkirimberkas')->nullable();
            $table->longText('statuskirimberkas')->nullable();
            $table->longText('statuskirimberkastext')->nullable();
            $table->string('userkirimberkas', 200)->nullable();
            $table->string('coa', 200)->nullable();
            $table->date('tgldariheaderpengeluaranheader')->nullable();
            $table->date('tglsampaiheaderpengeluaranheader')->nullable();
            $table->longText('statusposting')->nullable();
            $table->longText('statuspostingtext')->nullable();
            $table->double('qty')->nullable();
            $table->double('harga')->nullable();
        });
        $tempSupir = '##tempsupir' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempSupir, function ($table) {
            $table->string('nobukti')->nullable();
            $table->string('supir')->nullable();
        });
        if (request()->pengeluaranheader_id == 1) {
            $getSupir = DB::table("pengeluarantruckingdetail")->from(DB::raw("pengeluarantruckingdetail"))
                ->select(DB::raw("pengeluarantruckingdetail.nobukti, STRING_AGG(supir.namasupir, ', ') AS supir"))
                ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingdetail.supir_id', 'supir.id')
                ->whereRaw("nobukti like '%pjt%'")
                ->groupBy("pengeluarantruckingdetail.nobukti");

            DB::table($tempSupir)->insertUsing(['nobukti', 'supir'], $getSupir);
        } else {

            $getSupir = DB::table("pengeluarantruckingheader")->from(DB::raw("pengeluarantruckingheader"))
                ->select(DB::raw("pengeluarantruckingheader.nobukti, supir.namasupir AS supir"))
                ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingheader.supir_id', 'supir.id')
                ->where('pengeluarantruckingheader.pengeluarantrucking_id', '!=', 1);
            DB::table($tempSupir)->insertUsing(['nobukti', 'supir'], $getSupir);
        }

        $petik ='"';
        $url = config('app.url_fe').'penerimaantruckingheader';

        $getpenerimaantruckingdetail = DB::table("penerimaantruckingdetail")->from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
        ->select(DB::raw(" penerimaantruckingdetail.pengeluarantruckingheader_nobukti, STRING_AGG(penerimaantruckingdetail.nobukti, ', ') as nobuktipenerimaan,
        STRING_AGG('<a href=$petik".$url."?tgldari='+(format(penerimaantruckingheader.tglbukti,'yyyy-MM')+'-1')+'&tglsampai='+(format(penerimaantruckingheader.tglbukti,'yyyy-MM')+'-31')+'&nobukti='+penerimaantruckingheader.nobukti+'$petik 
        class=$petik link-color $petik target=$petik _blank $petik>'+penerimaantruckingdetail.nobukti+'</a>', ',') as url"))
        ->join(DB::raw("penerimaantruckingheader with (readuncommitted)"),'penerimaantruckingdetail.nobukti','penerimaantruckingheader.nobukti')
        ->whereRaw("isnull(penerimaantruckingdetail.pengeluarantruckingheader_nobukti,'') != ''")
        ->groupBy("penerimaantruckingdetail.pengeluarantruckingheader_nobukti");
        $tempurl = '##tempurl' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempurl, function (Blueprint $table) {
            $table->string('pengeluarantruckingheader_nobukti', 50)->nullable();
            $table->longText('nobuktipenerimaan')->nullable();
            $table->longText('url')->nullable();

        }); 
        DB::table($tempurl)->insertUsing(['pengeluarantruckingheader_nobukti', 'nobuktipenerimaan','url'], $getpenerimaantruckingdetail);
        $query = DB::table($this->table)->from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
            ->select(
                'pengeluarantruckingheader.id',
                'pengeluarantruckingheader.nobukti',
                'pengeluarantruckingheader.tglbukti',
                'pengeluarantruckingheader.modifiedby',
                'pengeluarantruckingheader.created_at',
                'pengeluarantruckingheader.updated_at',
                'pengeluarantruckingheader.pengeluaran_nobukti',
                db::raw("isnull(penerimaantruckingdetail.url,'') as penerimaantrucking_nobukti"),
                db::raw("isnull(penerimaantruckingdetail.nobuktipenerimaan,'') as nobuktipenerimaan"),
                'pengeluarantruckingheader.pengeluarantrucking_id as pengeluarantruckingid',
                'pengeluarantrucking.keterangan as pengeluarantrucking_id',
                'bank.namabank as bank_id',
                'pengeluarantruckingheader.trado_id',
                'trado.keterangan as trado',
                'pengeluarantruckingheader.trado_id as tradoheader_id',
                'getsupir.supir as supirheader',
                'getsupir.supir as supir',
                'gandengan.kodegandengan as gandengan',
                'pengeluarantruckingheader.pengeluarantrucking_nobukti',
                DB::raw('(case when (year(pengeluarantruckingheader.tglbukacetak) <= 2000) then null else pengeluarantruckingheader.tglbukacetak end ) as tglbukacetak'),
                'statuscetak.memo as statuscetak',
                'statuscetak.text as statuscetaktext',
                'pengeluarantruckingheader.userbukacetak',
                DB::raw('(case when (year(pengeluarantruckingheader.tglkirimberkas) <= 2000) then null else pengeluarantruckingheader.tglkirimberkas end ) as tglkirimberkas'),
                'statuskirimberkas.memo as statuskirimberkas',
                'statuskirimberkas.text as statuskirimberkastext',
                'pengeluarantruckingheader.userkirimberkas',
                'akunpusat.keterangancoa as coa',
                db::raw("cast((format(pengeluaranheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpengeluaranheader"),
                db::raw("cast(cast(format((cast((format(pengeluaranheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpengeluaranheader"),
                'statusposting.memo as statusposting',
                'statusposting.text as statuspostingtext'
            )
            // ->whereBetween('pengeluarantruckingheader.tglbukti', [date('Y-m-d',strtotime(request()->tgldari)), date('Y-m-d',strtotime(request()->tglsampai))])            
            ->leftJoin(DB::raw("pengeluaranheader with (readuncommitted)"), 'pengeluarantruckingheader.pengeluaran_nobukti', '=', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("pengeluarantruckingheader as b with (readuncommitted)"), 'pengeluarantruckingheader.pengeluarantrucking_nobukti', '=', 'b.nobukti')
            ->leftJoin(DB::raw("pengeluarantrucking with (readuncommitted)"), 'pengeluarantruckingheader.pengeluarantrucking_id', 'pengeluarantrucking.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluarantruckingheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pengeluarantruckingheader.coa', 'akunpusat.coa')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'pengeluarantruckingheader.trado_id', 'trado.id')
            ->leftJoin(DB::raw("gandengan with (readuncommitted)"), 'pengeluarantruckingheader.gandengan_id', 'gandengan.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'pengeluarantruckingheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("$tempSupir as getsupir with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'getsupir.nobukti')
            ->leftJoin(DB::raw("parameter as statusposting with (readuncommitted)"), 'pengeluarantruckingheader.statusposting', 'statusposting.id')
            ->leftJoin(DB::raw("parameter as statuskirimberkas with (readuncommitted)"), 'pengeluarantruckingheader.statuskirimberkas', 'statuskirimberkas.id')                
            ->leftJoin(DB::raw("$tempurl as penerimaantruckingdetail with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'penerimaantruckingdetail.pengeluarantruckingheader_nobukti');
        $afkir = Parameter::from(DB::raw("pengeluaranstok with (readuncommitted)"))->where('kodepengeluaran', 'AFKIR')->first();

        if (request()->pengeluaranstok_id && request()->pengeluaranstok_id == $afkir->id) {
            $query
                ->addSelect('pengeluarantruckingdetail.qty')
                ->addSelect('pengeluarantruckingdetail.harga')
                ->leftJoin(DB::raw("pengeluarantruckingdetail with (readuncommitted)"), 'pengeluarantruckingheader.id', 'pengeluarantruckingdetail.pengeluarantruckingheader_id');
            if (request()->from_tnl == "YA") {
                $query->where("pengeluarantruckingdetail.stoktnl_id", request()->stok_id);
            } else {
                $query->where("pengeluarantruckingdetail.stok_id", request()->stok_id);
            }
        }
        $datadetail = json_decode($query->get(), true);
        foreach ($datadetail as $item) {
            $namakaryawan = '';
            if ($item['pengeluarantrucking_id'] == 'PENARIKAN DEPOSITO KARYAWAN' || $item['pengeluarantrucking_id'] == 'PINJAMAN KARYAWAN') {
                // dd('test');
                $querydetail1 = DB::table("pengeluarantruckingdetail")->from(DB::raw("pengeluarantruckingdetail  a with (readuncommitted)"))
                    ->select(
                        'b.namakaryawan',
                    )
                    ->join(db::raw("karyawan b with (readuncommitted)"), 'a.karyawan_id', 'b.id')
                    ->where('a.nobukti', $item['nobukti'])
                    ->groupby('b.namakaryawan');

                // dd($querydetail1 );
                $hit = 0;
                $datadetail1 = json_decode($querydetail1->get(), true);
                foreach ($datadetail1 as $itemdetail) {
                    $hit = $hit + 1;
                    if ($hit == 1) {
                        $namakaryawan = $namakaryawan . $itemdetail['namakaryawan'];
                    } else {
                        $namakaryawan = $namakaryawan . ',' . $itemdetail['namakaryawan'];
                    }
                }
            }
            DB::table($temp)->insert([
                'id' => $item['id'],
                'nobukti' => $item['nobukti'],
                'tglbukti' => $item['tglbukti'],
                'modifiedby' => $item['modifiedby'],
                'created_at' => $item['created_at'],
                'updated_at' => $item['updated_at'],
                'pengeluaran_nobukti' => $item['pengeluaran_nobukti'],
                'penerimaantrucking_nobukti' => $item['penerimaantrucking_nobukti'],
                'nobuktipenerimaan' => $item['nobuktipenerimaan'],
                'pengeluarantruckingid' => $item['pengeluarantruckingid'],
                'pengeluarantrucking_id' => $item['pengeluarantrucking_id'],
                'bank_id' => $item['bank_id'],
                'trado_id' => $item['trado_id'],
                'trado' => $item['trado'],
                'tradoheader_id' => $item['tradoheader_id'],
                'supirheader' => $item['supirheader'],
                'supir' => $item['supir'],
                'karyawan' => $namakaryawan,
                'gandengan' => $item['gandengan'],
                'pengeluarantrucking_nobukti' => $item['pengeluarantrucking_nobukti'],
                'tglbukacetak' => $item['tglbukacetak'],
                'statuscetak' => $item['statuscetak'],
                'statuscetaktext' => $item['statuscetaktext'],
                'userbukacetak' => $item['userbukacetak'],
                'tglkirimberkas' => $item['tglkirimberkas'],
                'statuskirimberkas' => $item['statuskirimberkas'],
                'statuskirimberkastext' => $item['statuskirimberkastext'],
                'userkirimberkas' => $item['userkirimberkas'],
                'coa' => $item['coa'],
                'tgldariheaderpengeluaranheader' => $item['tgldariheaderpengeluaranheader'],
                'tglsampaiheaderpengeluaranheader' => $item['tglsampaiheaderpengeluaranheader'],
                'statusposting' => $item['statusposting'],
                'statuspostingtext' => $item['statuspostingtext'],
                'qty' => $item['qty'] ?? '',
                'harga' => $item['harga'] ?? '',
            ]);
        }
        $query = DB::table($temp)->from(DB::raw($temp . " a "))
            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                'a.pengeluaran_nobukti',
                'a.penerimaantrucking_nobukti',
                'a.nobuktipenerimaan',
                'a.pengeluarantruckingid',
                'a.pengeluarantrucking_id',
                'a.bank_id',
                'a.trado_id',
                'a.trado',
                'a.tradoheader_id',
                'a.supirheader',
                'a.supir',
                'a.karyawan',
                'a.gandengan',
                'a.pengeluarantrucking_nobukti',
                'a.tglbukacetak',
                'a.statuscetak',
                'a.statuscetaktext',
                'a.userbukacetak',
                'a.tglkirimberkas',
                'a.statuskirimberkas',
                'a.statuskirimberkastext',
                'a.userkirimberkas',
                'a.coa',
                'a.tgldariheaderpengeluaranheader',
                'a.tglsampaiheaderpengeluaranheader',
                'a.statusposting',
                'a.statuspostingtext',
                'a.qty',
                'a.harga',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
            );
        return $query;
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->integer('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->string('pengeluaran_nobukti', 50)->nullable();
            $table->longText('penerimaantrucking_nobukti')->nullable();
            $table->longText('nobuktipenerimaan')->nullable();
            $table->integer('pengeluarantruckingid')->nullable();
            $table->string('pengeluarantrucking_id', 100)->nullable();
            $table->string('bank_id', 50)->nullable();
            $table->integer('trado_id')->nullable();
            $table->string('trado', 200)->nullable();
            $table->integer('tradoheader_id')->nullable();
            $table->string('supirheader', 200)->nullable();
            $table->string('supir', 200)->nullable();
            $table->string('karyawan', 200)->nullable();
            $table->string('gandengan', 50)->nullable();
            $table->string('pengeluarantrucking_nobukti', 50)->nullable();
            $table->dateTime('tglbukacetak')->nullable();
            $table->longText('statuscetak')->nullable();
            $table->longText('statuscetaktext')->nullable();
            $table->string('userbukacetak', 200)->nullable();
            $table->dateTime('tglkirimberkas')->nullable();
            $table->longText('statuskirimberkas')->nullable();
            $table->longText('statuskirimberkastext')->nullable();
            $table->string('userkirimberkas', 200)->nullable();
            $table->string('coa', 200)->nullable();
            $table->date('tgldariheaderpengeluaranheader')->nullable();
            $table->date('tglsampaiheaderpengeluaranheader')->nullable();
            $table->longText('statusposting')->nullable();
            $table->longText('statuspostingtext')->nullable();
            $table->double('qty')->nullable();
            $table->double('harga')->nullable();
            $table->string('modifiedby', 200)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });
        if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
            request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
        }
        $this->setRequestParameters();
        // $query = DB::table($modelTable);
        $query = $this->selectColumns();
        $this->sort($query);
        $models = $this->filter($query);
        if (request()->tgldariheader) {
            $models->whereBetween('a.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        }
        if (request()->pengeluaranheader_id) {
            $models->where('a.pengeluarantruckingid', request()->pengeluaranheader_id);
        }

        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'pengeluaran_nobukti', 'penerimaantrucking_nobukti','nobuktipenerimaan', 'pengeluarantruckingid', 'pengeluarantrucking_id', 'bank_id', 'trado_id', 'trado', 'tradoheader_id', 'supirheader', 'supir', 'karyawan', 'gandengan', 'pengeluarantrucking_nobukti',  'tglbukacetak', 'statuscetak', 'statuscetaktext', 'userbukacetak','tglkirimberkas', 'statuskirimberkas', 'statuskirimberkastext', 'userkirimberkas', 'coa', 'tgldariheaderpengeluaranheader', 'tglsampaiheaderpengeluaranheader', 'statusposting', 'statuspostingtext', 'qty', 'harga', 'modifiedby', 'created_at', 'updated_at'], $models);


        return  $temp;
    }

    public function sort($query)
    {
        // if ($this->params['sortIndex'] == 'pengeluarantrucking_id') {
        //     return $query->orderBy('pengeluarantrucking.keterangan', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'bank_id') {
        //     return $query->orderBy('bank.namabank', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'coa') {
        //     return $query->orderBy('akunpusat.keterangancoa', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'gandengan') {
        //     return $query->orderBy('gandengan.kodegandengan', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'supir') {
        //     return $query->orderBy('getsupir.supir', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'trado') {
        //     return $query->orderBy('trado.keterangan', $this->params['sortOrder']);
        // } else {
        return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
        // }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statuscetak') {
                                $query = $query->where('a.statuscetaktext', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusposting') {
                                $query = $query->where('a.statuspostingtext', '=', "$filters[data]");
                            } else if ($filters['field'] == 'penerimaantrucking_nobukti') {
                                $query = $query->where('a.nobuktipenerimaan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglbukacetak') {
                                $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'statuscetak') {
                                    $query->orWhere('a.statuscetaktext', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statusposting') {
                                    $query = $query->orWhere('a.statuspostingtext', '=', "$filters[data]");
                                } else if ($filters['field'] == 'penerimaantrucking_nobukti') {
                                    $query = $query->orWhere('a.nobuktipenerimaan', 'LIKE', "%$filters[data]%");
                                }else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglbukacetak') {
                                    $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else {
                                    // $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                                }
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
        if (request()->cetak && request()->periode) {
            $query->where('pengeluarantruckingheader.statuscetak', '<>', request()->cetak)
                ->whereYear('pengeluarantruckingheader.tglbukti', '=', request()->year)
                ->whereMonth('pengeluarantruckingheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function getbiayalapangan()
    {
        $id = request()->id ?? '';

        $statusaktif = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('text', '=', 'AKTIF')
            ->first();

        if ($id != '' || $id != 0) {
            $temp = '##tempBLL' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temp, function ($table) {
                $table->bigInteger('id')->nullable();
                $table->string('supirbiaya', 1000)->nullable();
                $table->float('nominal')->nullable();
                $table->longText('keteranganbll')->nullable();
            });
            $get = DB::table("pengeluarantruckingdetail")->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
                ->select(DB::raw("supir.id, supir.namasupir as supirbiaya,pengeluarantruckingdetail.nominal, pengeluarantruckingdetail.keterangan as keteranganbll"))
                ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingdetail.supir_id', 'supir.id')
                ->where('supir.statusaktif', '=', $statusaktif->id)
                ->where('pengeluarantruckingdetail.pengeluarantruckingheader_id', $id)
                ->orderBy('supir.namasupir');

            DB::table($temp)->insertUsing(['id', 'supirbiaya', 'nominal', 'keteranganbll'], $get);

            $get2 = DB::table("supir")->from(DB::raw("supir with (readuncommitted)"))
                ->select('id', 'namasupir as supirbiaya')
                ->where('statusaktif', '=', $statusaktif->id)
                ->whereRaw("id not in (select supir_id from pengeluarantruckingdetail where pengeluarantruckingheader_id = $id)")
                ->orderBy('namasupir');
            DB::table($temp)->insertUsing(['id', 'supirbiaya'], $get2);

            $query = DB::table("$temp")->from(DB::raw("$temp with (readuncommitted)"))
                ->select(DB::raw("row_number() Over(Order By supirbiaya) as id, id as supir_id, supirbiaya, nominal, keteranganbll"))
                ->orderBy('supirbiaya')
                ->get();
        } else {

            $query = DB::table("supir")->from(DB::raw("supir with (readuncommitted)"))
                ->select(DB::raw("row_number() Over(Order By namasupir) as id, id as supir_id, namasupir as supirbiaya"))
                ->where('supir.statusaktif', '=', $statusaktif->id)
                ->orderBy('namasupir')
                ->get();
        }

        return $query;
    }

    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';
        $query = DB::table($this->table)->from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
            ->select(
                'pengeluarantruckingheader.id',
                'pengeluarantruckingheader.nobukti',
                'pengeluarantruckingheader.tglbukti',
                'pengeluarantruckingheader.pengeluaran_nobukti',
                'pengeluarantruckingheader.statusformat',
                'pengeluarantrucking.keterangan as pengeluarantrucking_id',
                'pengeluarantrucking.kodepengeluaran',
                'bank.namabank as bank_id',
                'agen.namaagen as agen_id',
                'container.keterangan as containerheader_id',
                'trado.kodetrado as trado',
                'supir.namasupir as supir',
                'pengeluarantruckingheader.pengeluarantrucking_nobukti',
                'pengeluarantruckingheader.periodedari',
                'pengeluarantruckingheader.periodesampai',
                'akunpusat.keterangancoa as coa',
                'statuscetak.memo as statuscetak',
                'statuscetak.id as  statuscetak_id',
                DB::raw("'Laporan Pengeluaran Trucking' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'pengeluarantruckingheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("pengeluarantrucking with (readuncommitted)"), 'pengeluarantruckingheader.pengeluarantrucking_id', 'pengeluarantrucking.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluarantruckingheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pengeluarantruckingheader.coa', 'akunpusat.coa')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'pengeluarantruckingheader.trado_id', 'trado.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'pengeluarantruckingheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'pengeluarantruckingheader.container_id', 'container.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingheader.supir_id', 'supir.id');

        if (request()->tgldari) {
            $query->whereBetween('pengeluarantruckingheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if (request()->pengeluaranheader_id) {
            $query->where('pengeluarantruckingheader.pengeluarantrucking_id', request()->pengeluaranheader_id);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(pengeluarantruckingheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(pengeluarantruckingheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("pengeluarantruckingheader.statuscetak", $statusCetak);
        }

        $data = $query->first();
        return $data;
    }

    public function storePinjamanPosting($postingPinjaman)
    {
        $postingPinjaman['tanpaprosesnobukti'] = 2;
        $pinjaman = $this->processStore($postingPinjaman);
        // throw new \Exception($xx->nobukti);
        return $pinjaman;
    }
    public function updatePinjamanPosting($nobukti, $postingPinjaman)
    {
        $postingPinjaman['tanpaprosesnobukti'] = 2;

        $pengeluaran = PengeluaranTruckingHeader::where('nobukti', $nobukti)->first();
        $pinjaman = $this->processUpdate($pengeluaran, $postingPinjaman);
        return $pinjaman;
    }

    public function deletePinjamanPosting($id)
    {
        $postingPinjaman['tanpaprosesnobukti'] = 2;

        $pengeluaran = PengeluaranTruckingHeader::where('id', $id)->first();
        $pinjaman = $this->processDestroy($pengeluaran->id);
        return $pinjaman;
    }


    public function processStore(array $data): PengeluaranTruckingHeader
    {
        $idpengeluaran = $data['pengeluarantrucking_id'];
        $fetchFormat =  DB::table('pengeluarantrucking')
            ->where('id', $idpengeluaran)
            ->first();
        $tanpaprosesnobukti = $data['tanpaprosesnobukti'] ?? 0;

        $klaim = DB::table('pengeluarantrucking')->from(DB::raw("pengeluarantrucking with (readuncommitted)"))->where('kodepengeluaran', "KLAIM")->first();
        $statusformat = $fetchFormat->format;
        $fetchGrp = Parameter::where('id', $statusformat)->first();
        $format = DB::table('parameter')->where('grp', $fetchGrp->grp)->where('subgrp', $fetchGrp->subgrp)->first();
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusPosting = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING')->where('id', '84')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $pinjamansupir = db::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->SELECT('text', 'memo')->where('grp', 'PINJAMAN SUPIR')->where('subgrp', 'PINJAMAN SUPIR NON POSTING')->first() ?? '';
        $statuspinjamanposting = $data['statusposting'] ?? $statusPosting->id;
        if ($idpengeluaran == $pinjamansupir->text) {
            if ($statuspinjamanposting == $statusPosting->id) {
                $memo = json_decode($pinjamansupir->memo, true);
                $data['coa'] = $memo['JURNAL'];
            } else {
                $data['coa'] = $fetchFormat->coapostingdebet;
            }
        } else {
            if ($fetchFormat->kodepengeluaran != 'BLS') {
                $data['coa'] = $fetchFormat->coapostingdebet;
            }
        }


        $tgldari = null;
        $tglsampai = null;
        $periode = null;
        if (array_key_exists('tgldari', $data)) {
            $tgldari = date('Y-m-d', strtotime($data['tgldari']));
        };
        if (array_key_exists('tglsampai', $data)) {
            $tglsampai = date('Y-m-d', strtotime($data['tglsampai']));
        };
        if (array_key_exists('periode', $data)) {
            $periode = date('Y-m-d', strtotime('01-' . $data['periode']));
        };

        $tradoHeader = $data['tradoheader_id'] ?? '';
        $gandenganHeader = $data['gandenganheader_id'] ?? '';
        $tradoTNL = '';
        $gandenganTNL = '';

        if (array_key_exists('statuscabang', $data)) {
            if ($data['statuscabang'] == 516) {
                $tradoTNL = $data['tradoheader_id'];
                $tradoHeader = '';
                $gandenganTNL = $data['gandenganheader_id'];
                $gandenganHeader = '';
            }
        }
        if (array_key_exists('postingpinjaman', $data)) {
            if ($data['postingpinjaman'] != '' && $data['postingpinjaman'] != 0) {
                $data['statusposting'] = $data['postingpinjaman'];
            }
        }

        $pengeluaranTruckingHeader = new PengeluaranTruckingHeader();

        $pengeluaranTruckingHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $pengeluaranTruckingHeader->pengeluarantrucking_id = $data['pengeluarantrucking_id'];
        $pengeluaranTruckingHeader->bank_id = (array_key_exists('bank_id', $data)) ? $data['bank_id'] : 0;
        $pengeluaranTruckingHeader->statusposting = $data['statusposting'] ?? $statusPosting->id;
        $pengeluaranTruckingHeader->coa = $data['coa'];
        $pengeluaranTruckingHeader->pengeluaran_nobukti = $data['pengeluaran_nobukti'] ?? '';
        $pengeluaranTruckingHeader->periodedari = $tgldari;
        $pengeluaranTruckingHeader->periodesampai = $tglsampai;
        $pengeluaranTruckingHeader->periode = $periode;
        $pengeluaranTruckingHeader->supir_id = $data['supirheader_id'] ?? '';
        $pengeluaranTruckingHeader->pemutihansupir_nobukti = $data['pemutihansupir_nobukti'] ?? '';
        $pengeluaranTruckingHeader->karyawan_id = $data['karyawanheader_id'] ?? '';
        $pengeluaranTruckingHeader->trado_id = $tradoHeader;
        $pengeluaranTruckingHeader->tradotnl_id = $tradoTNL;
        $pengeluaranTruckingHeader->gandengan_id = $gandenganHeader;
        $pengeluaranTruckingHeader->gandengantnl_id = $gandenganTNL;
        $pengeluaranTruckingHeader->statuscabang = $data['statuscabang'] ?? '';
        $pengeluaranTruckingHeader->jenisorder_id = $data['jenisorderan_id'] ?? '';
        $pengeluaranTruckingHeader->agen_id = $data['agen_id'] ?? '';
        $pengeluaranTruckingHeader->container_id = $data['containerheader_id'] ?? '';
        $pengeluaranTruckingHeader->statusformat = $data['statusformat'] ?? $format->id;
        $pengeluaranTruckingHeader->statuscetak = $statusCetak->id;
        $pengeluaranTruckingHeader->modifiedby = auth('api')->user()->name;
        $pengeluaranTruckingHeader->info = html_entity_decode(request()->info);
        $pengeluaranTruckingHeader->nobukti = (new RunningNumberService)->get($fetchGrp->grp, $fetchGrp->subgrp, $pengeluaranTruckingHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$pengeluaranTruckingHeader->save()) {
            throw new \Exception("Error storing pengeluaran Trucking Header.");
        }
        $pengeluaranTruckingDetails = [];
        $nominalBiaya = 0;
        for ($i = 0; $i < count($data['nominal']); $i++) {
            $qty = $data['qty'][$i] ?? 0;
            $harga = $data['harga'][$i] ?? 0;
            $totalHarga = $qty * $harga;

            if ($data['pengeluarantrucking_id'] == $klaim->id) {
                $data['nominaltagih'][$i] = $data['nominal'][$i];
                $tambahan =  $data['nominaltambahan'][$i] ?? 0;
                $totalNominal = $data['nominal'][$i] + $tambahan;
                $data['nominal'][$i] = $totalNominal;
            }
            if ($data['pengeluarantrucking_id'] == 9) {
                $data['nominaltagih'][$i] = $data['nominal'][$i];
            }
            $pengeluaranstok_nobukti = $data['pengeluaranstok_nobukti'][$i] ?? '';
            $stok = $data['stok_id'][$i] ?? null;
            $penerimaanstok_nobukti =  $data['penerimaanstok_nobukti'][$i] ?? '';
            $pengeluaranstok_nobuktiTNL = '';
            $penerimaanstok_nobuktiTNL = '';
            $stoktnl = '';

            if (array_key_exists('statuscabang', $data)) {
                if ($data['statuscabang'] == 516) {
                    $pengeluaranstok_nobuktiTNL = $data['pengeluaranstok_nobukti'][$i] ?? '';
                    $pengeluaranstok_nobukti = '';
                    $penerimaanstok_nobuktiTNL = $data['penerimaanstok_nobukti'][$i] ?? '';
                    $penerimaanstok_nobukti = '';
                    $stoktnl = $data['stok_id'][$i] ?? '';
                    $stok = '';
                }
            }
            $pengeluaranTruckingDetail = (new PengeluaranTruckingDetail())->processStore($pengeluaranTruckingHeader, [
                'pengeluarantruckingheader_id' => $pengeluaranTruckingHeader->id,
                'nobukti' => $pengeluaranTruckingHeader->nobukti,
                'supir_id' => $data['supir_id'][$i] ?? null,
                'karyawan_id' => $data['karyawan_id'][$i] ?? null,
                'stok_id' => $stok,
                'pengeluaranstok_nobukti' => $pengeluaranstok_nobukti,
                'penerimaanstok_nobukti' => $penerimaanstok_nobukti,
                'stoktnl_id' => $stoktnl,
                'pengeluaranstoktnl_nobukti' => $pengeluaranstok_nobuktiTNL,
                'penerimaanstoktnl_nobukti' => $penerimaanstok_nobuktiTNL,
                'qty' => $data['qty'][$i] ?? null,
                'harga' => $data['harga'][$i] ?? null,
                'total' => $totalHarga ?? 0,
                'trado_id' => $data['trado_id'][$i] ?? null,
                'penerimaantruckingheader_nobukti' => $data['penerimaantruckingheader_nobukti'][$i] ?? '',
                'invoice_nobukti' => $data['noinvoice_detail'][$i] ?? '',
                'orderantrucking_nobukti' => $data['nojobtrucking_detail'][$i] ?? '',
                'keterangan' => $data['keterangan'][$i] ?? '',
                'nominal' => $data['nominal'][$i],
                'modifiedby' => $pengeluaranTruckingHeader->modifiedby,

                // 'suratpengantar_id' => $data['suratpengantar_id'][$i] ?? null,
                'statustitipanemkl' => $data['statustitipanemkl'][$i] ?? null,
                'suratpengantar_nobukti' => $data['suratpengantar_nobukti'][$i] ?? null,
                'trado_id' => $data['trado_id'][$i] ?? null,
                'container_id' => $data['container_id'][$i] ?? null,
                'pelanggan_id' => $data['pelanggan_id'][$i] ?? null,
                'nominaltagih' => $data['nominaltagih'][$i] ?? 0,
                'jenisorder' => $data['jenisorder'][$i] ?? null,
                'nominaltambahan' => $data['nominaltambahan'][$i] ?? 0,
                'keterangantambahan' => $data['keterangantambahan'][$i] ?? '',
            ]);
            $pengeluaranTruckingDetails[] = $pengeluaranTruckingDetail->toArray();
            $nominal_detail[] = $pengeluaranTruckingDetail->nominal;
            $keterangan_detail[] = $data['keterangan'][$i];
            $nominalBiaya = $nominalBiaya + $data['nominal'][$i];
        }


        if (($tanpaprosesnobukti != 2) && ($data['statusposting'] != $statusPosting->id)) {


            if ($klaim->id == $data['pengeluarantrucking_id']) {
                if ($data['postingpinjaman'] != $statusPosting->id) {
                    $pinjaman = DB::table('pengeluarantrucking')->from(DB::raw("pengeluarantrucking with (readuncommitted)"))->where('kodepengeluaran', "PJT")->first();
                }

                $getnamasupir = DB::table('supir')->select('namasupir')->where('id', $data['supirheader_id'])->first();
                for ($i = 0; $i < count($data['nominal']); $i++) {
                    $pjt_supir_id[] = $data['supirheader_id'];
                    $pjt_nominal[] = $data['nominal'][$i];
                    $pjt_keterangan[] = "PINJAMAN SUPIR $getnamasupir->namasupir ATAS ".$data['keterangan'][$i];
                }
                $pjtRequest = [
                    "tglbukti" => $data['tglbukti'],
                    "pengeluarantrucking_id" => $pinjaman->id,
                    "statusposting" => $statusPosting->id,
                    'supir_id' => $pjt_supir_id,
                    'nominal' => $pjt_nominal,
                    'keterangan' => $pjt_keterangan,
                ];

                $pinjaman = $this->storePinjamanPosting($pjtRequest);
                // throw new \Exception($pinjaman->nobukti);

                $pengeluaranTruckingHeader->pengeluarantrucking_nobukti = $pinjaman->nobukti;
                $pengeluaranTruckingHeader->save();
            } else {
                $alatbayar = DB::table("alatbayar")->select('alatbayar.id', 'alatbayar.kodealatbayar')->join('bank', 'alatbayar.tipe', 'bank.tipe')->where('bank.id', $pengeluaranTruckingHeader->bank_id)->first();

                $queryPengeluaran = Bank::from(DB::raw("bank with (readuncommitted)"))->select('parameter.grp', 'parameter.subgrp', 'bank.formatpengeluaran', 'bank.coa', 'bank.tipe')->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpengeluaran', 'parameter.id')->where("bank.id", $data['bank_id'])->first();
                if ($fetchFormat->kodepengeluaran == 'BLL' || $fetchFormat->kodepengeluaran == 'BLN' || $fetchFormat->kodepengeluaran == 'BTU' || $fetchFormat->kodepengeluaran == 'BPT' || $fetchFormat->kodepengeluaran == 'BGS' || $fetchFormat->kodepengeluaran == 'BIT' || $fetchFormat->kodepengeluaran == 'BBT' || $fetchFormat->kodepengeluaran == 'BST' || $fetchFormat->kodepengeluaran == 'OTOK' || $fetchFormat->kodepengeluaran == 'OTOL' || $fetchFormat->kodepengeluaran == 'BSM') {
                    $nominal_detail = [];
                    $keterangan_detail = [];
                    $coakredit_detail[] = $queryPengeluaran->coa;
                    $coadebet_detail[] = $data['coa'];
                    $nowarkat[] = "";
                    $tglkasmasuk[] = (array_key_exists('tglkasmasuk', $data)) ? date('Y-m-d', strtotime($data['tglkasmasuk'])) : date('Y-m-d', strtotime($data['tglbukti']));
                    $nominal_detail[] = $nominalBiaya;
                    if ($fetchFormat->kodepengeluaran == 'BST' || $fetchFormat->kodepengeluaran == 'OTOK' || $fetchFormat->kodepengeluaran == 'OTOL' || $fetchFormat->kodepengeluaran == 'BSM') {
                        $keterangan_detail[] = "$fetchFormat->keterangan " . $data['tgldari'] . " s/d " . $data['tglsampai'] . " $pengeluaranTruckingHeader->nobukti";
                    } else if ($fetchFormat->kodepengeluaran == 'BBT') {
                        $keterangan_detail[] = $data['keterangan'][0];
                    } else {
                        $nonEmptyArray = array_filter($data['keterangan']);
                        $nonEmptyArray = array_values($nonEmptyArray);
                        $keterangan_detail[] = $nonEmptyArray[0] ?? "$fetchFormat->keterangan periode " . $data['periode'] . " $pengeluaranTruckingHeader->nobukti";
                    }
                } else {
                    // for ($i = 0; $i < count($nominal_detail); $i++) {
                    $nominal_detail = [];
                    $nominal_detail[] = $nominalBiaya;
                    $keterangan_detail = [];
                    $keterangan_detail[] = $data['keterangan'][0];
                    $coakredit_detail[] = $queryPengeluaran->coa;
                    $coadebet_detail[] = $data['coa'];
                    $nowarkat[] = "";
                    $tglkasmasuk[] = (array_key_exists('tglkasmasuk', $data)) ? date('Y-m-d', strtotime($data['tglkasmasuk'])) : date('Y-m-d', strtotime($data['tglbukti']));
                    // }
                }
                /*STORE PENGELUARAN*/
                $pengeluaranRequest = [
                    'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'pelanggan_id' => 0,
                    'postingdari' => $data['postingdari'] ?? "ENTRY PENGELUARAN TRUCKING",
                    'statusapproval' => $statusApproval->id,
                    'dibayarke' => '',
                    'alatbayar_id' => $alatbayar->id,
                    'bank_id' => $data['bank_id'],
                    'transferkeac' => "",
                    'transferkean' => "",
                    'transferkebank' => "",
                    'userapproval' => "",
                    'tglapproval' => "",

                    'nowarkat' => $nowarkat,
                    'tgljatuhtempo' => $tglkasmasuk,
                    "nominal_detail" => $nominal_detail,
                    'coadebet' => $coadebet_detail,
                    'coakredit' => $coakredit_detail,
                    "keterangan_detail" => $keterangan_detail,
                    'bulanbeban' => $tglkasmasuk,
                ];

                $pengeluaranHeader = (new PengeluaranHeader())->processStore($pengeluaranRequest);

                $pengeluaranTruckingHeader->pengeluaran_nobukti = $pengeluaranHeader->nobukti;
                $pengeluaranTruckingHeader->save();
            }
        }

        $pengeluaranTruckingHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengeluaranTruckingHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY pengeluaran trucking Header '),
            'idtrans' => $pengeluaranTruckingHeader->id,
            'nobuktitrans' => $pengeluaranTruckingHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pengeluaranTruckingHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengeluaranTruckingDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY PENGELUARAN TRUCKING DETAIL'),
            'idtrans' =>  $pengeluaranTruckingHeader->id,
            'nobuktitrans' => $pengeluaranTruckingHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pengeluaranTruckingDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);
        $pengeluaranTruckingHeader->save();
        return $pengeluaranTruckingHeader;
    }

    public function processUpdate(PengeluaranTruckingHeader $pengeluaranTruckingHeader, array $data): PengeluaranTruckingHeader
    {
        $idpengeluaran = $data['pengeluarantrucking_id'];
        $fetchFormat =  DB::table('pengeluarantrucking')
            ->where('id', $idpengeluaran)
            ->first();
        $tanpaprosesnobukti = $data['tanpaprosesnobukti'] ?? 0;
        $from = $data['from'] ?? 'not';
        $statusPosting = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING')->where('id', '84')->first();
        $pinjamansupir = db::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->SELECT('text', 'memo')->where('grp', 'PINJAMAN SUPIR')->where('subgrp', 'PINJAMAN SUPIR NON POSTING')->first() ?? '';
        $statuspinjamanposting = $pengeluaranTruckingHeader['statusposting'] ?? $statusPosting->id;

        if ($idpengeluaran == $pinjamansupir->text) {
            if ($statuspinjamanposting == $statusPosting->id) {
                $memo = json_decode($pinjamansupir->memo, true);
                $data['coa'] = $memo['JURNAL'];
            } else {
                $data['coa'] = $fetchFormat->coapostingdebet;
            }
        } else {
            if ($fetchFormat->kodepengeluaran != 'BLS') {
                $data['coa'] = $fetchFormat->coapostingdebet;
            }
        }

        if (array_key_exists('postingpinjaman', $data)) {
            if ($data['postingpinjaman'] != '' && $data['postingpinjaman'] != 0) {
                $data['statusposting'] = $data['postingpinjaman'];
            }
        }
        $klaim = DB::table('pengeluarantrucking')->from(DB::raw("pengeluarantrucking with (readuncommitted)"))->where('kodepengeluaran', "KLAIM")->first();
        $statusformat = $fetchFormat->format;
        $fetchGrp = Parameter::where('id', $statusformat)->first();
        $format = DB::table('parameter')->where('grp', $fetchGrp->grp)->where('subgrp', $fetchGrp->subgrp)->first();
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusPosting = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING')->where('id', '84')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        $getTgl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'EDIT TANGGAL BUKTI')->where('subgrp', 'PENGELUARAN TRUCKING')->first();

        if (trim($getTgl->text) == 'YA') {
            $querycek = DB::table('pengeluarantruckingheader')->from(
                DB::raw("pengeluarantruckingheader a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.id', $pengeluaranTruckingHeader->id)
                ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
                ->first();

            if (isset($querycek)) {
                $nobukti = $querycek->nobukti;
            } else {
                $nobukti = (new RunningNumberService)->get($fetchGrp->grp, $fetchGrp->subgrp, $pengeluaranTruckingHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
            }

            $pengeluaranTruckingHeader->nobukti = $nobukti;
            $pengeluaranTruckingHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        }

        $tgldari = null;
        $tglsampai = null;
        $periode = null;
        if (array_key_exists('tgldari', $data)) {
            $tgldari = date('Y-m-d', strtotime($data['tgldari']));
        };
        if (array_key_exists('tglsampai', $data)) {
            $tglsampai = date('Y-m-d', strtotime($data['tglsampai']));
        };
        if (array_key_exists('periode', $data)) {
            $periode = date('Y-m-d', strtotime('01-' . $data['periode']));
        };

        $tradoHeader = $data['tradoheader_id'] ?? '';
        $gandenganHeader = $data['gandenganheader_id'] ?? '';
        $tradoTNL = '';
        $gandenganTNL = '';

        if (array_key_exists('statuscabang', $data)) {
            if ($data['statuscabang'] == 516) {
                $tradoTNL = $data['tradoheader_id'];
                $tradoHeader = '';
                $gandenganTNL = $data['gandenganheader_id'];
                $gandenganHeader = '';
            }
        }
        if ($klaim->id == $data['pengeluarantrucking_id']) {
            $pengeluaranTruckingHeader->statusposting = $data['statusposting'] ?? $statusPosting->id;
        }
        $pengeluaranTruckingHeader->coa = $data['coa'];
        $pengeluaranTruckingHeader->periodedari = $tgldari;
        $pengeluaranTruckingHeader->periodesampai = $tglsampai;
        $pengeluaranTruckingHeader->periode = $periode;
        $pengeluaranTruckingHeader->supir_id = $data['supirheader_id'] ?? '';
        $pengeluaranTruckingHeader->karyawan_id = $data['karyawanheader_id'] ?? '';
        $pengeluaranTruckingHeader->trado_id = $tradoHeader;
        $pengeluaranTruckingHeader->tradotnl_id = $tradoTNL;
        $pengeluaranTruckingHeader->gandengan_id = $gandenganHeader;
        $pengeluaranTruckingHeader->gandengantnl_id = $gandenganTNL;
        $pengeluaranTruckingHeader->statuscabang = $data['statuscabang'] ?? '';
        $pengeluaranTruckingHeader->jenisorder_id = $data['jenisorderan_id'] ?? '';
        $pengeluaranTruckingHeader->agen_id = $data['agen_id'] ?? '';
        $pengeluaranTruckingHeader->container_id = $data['containerheader_id'] ?? '';
        $pengeluaranTruckingHeader->statusformat = $data['statusformat'] ?? $format->id;
        $pengeluaranTruckingHeader->modifiedby = auth('api')->user()->name;
        $pengeluaranTruckingHeader->editing_by = '';
        $pengeluaranTruckingHeader->editing_at = null;
        $pengeluaranTruckingHeader->info = html_entity_decode(request()->info);

        if (!$pengeluaranTruckingHeader->save()) {
            throw new \Exception("Error storing pengeluaran Trucking Header.");
        }

        if ($from == 'ebs') {
            $pengeluaranTruckingHeader->bank_id = $data['bank_id'];
            $pengeluaranTruckingHeader->pengeluaran_nobukti = $data['pengeluaran_nobukti'];

            $pengeluaranTruckingHeader->save();
            return $pengeluaranTruckingHeader;
        }

        /*DELETE EXISTING DETAIL*/
        PengeluaranTruckingDetail::where('pengeluarantruckingheader_id', $pengeluaranTruckingHeader->id)->delete();

        $pengeluaranTruckingDetails = [];
        $nominalBiaya = 0;
        for ($i = 0; $i < count($data['nominal']); $i++) {
            $qty = $data['qty'][$i] ?? 0;
            $harga = $data['harga'][$i] ?? 0;
            $totalHarga = $qty * $harga;

            if ($data['pengeluarantrucking_id'] == $klaim->id) {
                $data['nominaltagih'][$i] = $data['nominal'][$i];
                $tambahan =  $data['nominaltambahan'][$i] ?? 0;
                $totalNominal = $data['nominal'][$i] + $tambahan;
                $data['nominal'][$i] = $totalNominal;
            }
            if ($data['pengeluarantrucking_id'] == 9) {
                $data['nominaltagih'][$i] = $data['nominal'][$i];
            }
            $pengeluaranstok_nobukti = $data['pengeluaranstok_nobukti'][$i] ?? '';
            $stok = $data['stok_id'][$i] ?? null;
            $penerimaanstok_nobukti =  $data['penerimaanstok_nobukti'][$i] ?? '';
            $pengeluaranstok_nobuktiTNL = '';
            $penerimaanstok_nobuktiTNL = '';
            $stoktnl = '';

            if (array_key_exists('statuscabang', $data)) {
                if ($data['statuscabang'] == 516) {
                    $pengeluaranstok_nobuktiTNL = $data['pengeluaranstok_nobukti'][$i] ?? '';
                    $pengeluaranstok_nobukti = '';
                    $penerimaanstok_nobuktiTNL = $data['penerimaanstok_nobukti'][$i] ?? '';
                    $penerimaanstok_nobukti = '';
                    $stoktnl = $data['stok_id'][$i] ?? '';
                    $stok = '';
                }
            }
            $pengeluaranTruckingDetail = (new PengeluaranTruckingDetail())->processStore($pengeluaranTruckingHeader, [
                'pengeluarantruckingheader_id' => $pengeluaranTruckingHeader->id,
                'nobukti' => $pengeluaranTruckingHeader->nobukti,
                'supir_id' => $data['supir_id'][$i] ?? null,
                'karyawan_id' => $data['karyawan_id'][$i] ?? null,
                'stok_id' => $stok,
                'pengeluaranstok_nobukti' => $pengeluaranstok_nobukti,
                'penerimaanstok_nobukti' => $penerimaanstok_nobukti,
                'stoktnl_id' => $stoktnl,
                'pengeluaranstoktnl_nobukti' => $pengeluaranstok_nobuktiTNL,
                'penerimaanstoktnl_nobukti' => $penerimaanstok_nobuktiTNL,
                'qty' => $data['qty'][$i] ?? null,
                'harga' => $data['harga'][$i] ?? null,
                'total' => $totalHarga ?? 0,
                'trado_id' => $data['trado_id'][$i] ?? null,
                'penerimaantruckingheader_nobukti' => $data['penerimaantruckingheader_nobukti'][$i] ?? '',
                'invoice_nobukti' => $data['noinvoice_detail'][$i] ?? '',
                'orderantrucking_nobukti' => $data['nojobtrucking_detail'][$i] ?? '',
                'keterangan' => $data['keterangan'][$i] ?? '',
                'nominal' => $data['nominal'][$i],

                'statustitipanemkl' => $data['statustitipanemkl'][$i] ?? null,
                'suratpengantar_nobukti' => $data['suratpengantar_nobukti'][$i] ?? null,
                'trado_id' => $data['trado_id'][$i] ?? null,
                'container_id' => $data['container_id'][$i] ?? null,
                'pelanggan_id' => $data['pelanggan_id'][$i] ?? null,
                'nominaltagih' => $data['nominaltagih'][$i] ?? 0,
                'jenisorder' => $data['jenisorder'][$i] ?? null,
                'nominaltambahan' => $data['nominaltambahan'][$i] ?? 0,
                'keterangantambahan' => $data['keterangantambahan'][$i] ?? '',
                'modifiedby' => $pengeluaranTruckingHeader->modifiedby,
            ]);
            $pengeluaranTruckingDetails[] = $pengeluaranTruckingDetail->toArray();
            $nominal_detail[] = $pengeluaranTruckingDetail->nominal;
            $keterangan_detail[] = $data['keterangan'][$i];
            $nominalBiaya = $nominalBiaya + $data['nominal'][$i];
        }


        if (($tanpaprosesnobukti != 2)) {
            if ($klaim->id == $data['pengeluarantrucking_id']) {
                if ($pengeluaranTruckingDetail->statusPosting != $statusPosting->id) {
                    $pinjaman = DB::table('pengeluarantrucking')->from(DB::raw("pengeluarantrucking with (readuncommitted)"))->where('kodepengeluaran', "PJT")->first();
                }
                $getnamasupir = DB::table('supir')->select('namasupir')->where('id', $data['supirheader_id'])->first();

                for ($i = 0; $i < count($data['nominal']); $i++) {
                    $pjt_supir_id[] = $data['supirheader_id'];
                    $pjt_karyawan_id[] = $data['karyawan_id'];
                    $pjt_nominal[] = $data['nominal'][$i];
                    $pjt_keterangan[] = "PINJAMAN SUPIR $getnamasupir->namasupir ATAS ".$data['keterangan'][$i];
                }
                $pjtRequest = [
                    "tglbukti" => $data['tglbukti'],
                    "pengeluarantrucking_id" => $pinjaman->id,
                    "statusposting" => $statusPosting->id,
                    'supir_id' => $pjt_supir_id,
                    'karyawan_id' => $data['karyawan_id'],
                    'nominal' => $pjt_nominal,
                    'keterangan' => $pjt_keterangan,
                ];

                if ($pengeluaranTruckingDetail->statusPosting != $statusPosting->id) {
                    if ($pengeluaranTruckingHeader->pengeluarantrucking_nobukti != '') {
                        $pinjaman = $this->updatePinjamanPosting($pengeluaranTruckingHeader->pengeluarantrucking_nobukti, $pjtRequest);
                    } else {
                        $pinjaman = $this->storePinjamanPosting($pjtRequest);
                        $pengeluaranTruckingHeader->pengeluarantrucking_nobukti = $pinjaman->nobukti;
                        $pengeluaranTruckingHeader->save();
                    }
                }
            } else {
                if ($pengeluaranTruckingHeader->statusposting != $statusPosting->id) {
                    $alatbayar = DB::table("alatbayar")->select('alatbayar.id', 'alatbayar.kodealatbayar')->join('bank', 'alatbayar.tipe', 'bank.tipe')->where('bank.id', $pengeluaranTruckingHeader->bank_id)->first();
                    $queryPengeluaran = Bank::from(DB::raw("bank with (readuncommitted)"))->select('parameter.grp', 'parameter.subgrp', 'bank.formatpengeluaran', 'bank.coa', 'bank.tipe')->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpengeluaran', 'parameter.id')->where("bank.id", $data['bank_id'])->first();

                    if ($fetchFormat->kodepengeluaran == 'BLL' || $fetchFormat->kodepengeluaran == 'BLN' || $fetchFormat->kodepengeluaran == 'BTU' || $fetchFormat->kodepengeluaran == 'BPT' || $fetchFormat->kodepengeluaran == 'BGS' || $fetchFormat->kodepengeluaran == 'BIT' || $fetchFormat->kodepengeluaran == 'BBT' || $fetchFormat->kodepengeluaran == 'BST' || $fetchFormat->kodepengeluaran == 'OTOK' || $fetchFormat->kodepengeluaran == 'OTOL' || $fetchFormat->kodepengeluaran == 'BSM') {
                        $nominal_detail = [];
                        $keterangan_detail = [];
                        $coakredit_detail[] = $queryPengeluaran->coa;
                        $coadebet_detail[] = $data['coa'];
                        $nowarkat[] = "";
                        $tglkasmasuk[] = (array_key_exists('tglkasmasuk', $data)) ? date('Y-m-d', strtotime($data['tglkasmasuk'])) : date('Y-m-d', strtotime($data['tglbukti']));
                        $nominal_detail[] = $nominalBiaya;
                        if ($fetchFormat->kodepengeluaran == 'BST' || $fetchFormat->kodepengeluaran == 'OTOK' || $fetchFormat->kodepengeluaran == 'OTOL' || $fetchFormat->kodepengeluaran == 'BSM') {
                            $keterangan_detail[] = "$fetchFormat->keterangan " . $data['tgldari'] . " s/d " . $data['tglsampai'] . " $pengeluaranTruckingHeader->nobukti";
                        } else if ($fetchFormat->kodepengeluaran == 'BBT') {
                            $keterangan_detail[] = $data['keterangan'][0];
                        } else {
                            $nonEmptyArray = array_filter($data['keterangan']);
                            $nonEmptyArray = array_values($nonEmptyArray);
                            $keterangan_detail[] = $nonEmptyArray[0] ?? "$fetchFormat->keterangan periode " . $data['periode'] . " $pengeluaranTruckingHeader->nobukti";
                        }
                    } else {

                        // for ($i = 0; $i < count($nominal_detail); $i++) {
                        $nominal_detail = [];
                        $nominal_detail[] = $nominalBiaya;
                        $keterangan_detail = [];
                        $keterangan_detail[] = $data['keterangan'][0];
                        $coakredit_detail[] = $queryPengeluaran->coa;
                        $coadebet_detail[] = $data['coa'];
                        $nowarkat[] = "";
                        $tglkasmasuk[] = (array_key_exists('tglkasmasuk', $data)) ? date('Y-m-d', strtotime($data['tglkasmasuk'])) : date('Y-m-d', strtotime($data['tglbukti']));
                        // }
                    }
                    /*STORE PENGELUARAN*/
                    $pengeluaranRequest = [
                        'tglbukti' => $pengeluaranTruckingHeader->tglbukti,
                        'pelanggan_id' => 0,
                        'postingdari' => $data['postingdari'] ?? "EDIT PENGELUARAN TRUCKING",
                        'statusapproval' => $statusApproval->id,
                        'dibayarke' => '',
                        'alatbayar_id' => $alatbayar->id,
                        'bank_id' => $data['bank_id'],
                        'transferkeac' => "",
                        'transferkean' => "",
                        'transferkebank' => "",
                        'userapproval' => "",
                        'tglapproval' => "",

                        'nowarkat' => $nowarkat,
                        'tgljatuhtempo' => $tglkasmasuk,
                        "nominal_detail" => $nominal_detail,
                        'coadebet' => $coadebet_detail,
                        'coakredit' => $coakredit_detail,
                        "keterangan_detail" => $keterangan_detail,
                        'bulanbeban' => $tglkasmasuk,
                    ];

                    $pengeluaranHeader = PengeluaranHeader::where('nobukti', $pengeluaranTruckingHeader->pengeluaran_nobukti)->first();
                    $pengeluaranHeader = (new PengeluaranHeader())->processUpdate($pengeluaranHeader, $pengeluaranRequest);
                    $pengeluaranTruckingHeader->pengeluaran_nobukti = $pengeluaranHeader->nobukti;
                    $pengeluaranTruckingHeader->save();
                }
            }
        }

        $pengeluaranTruckingHeader->save();
        $pengeluaranTruckingHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengeluaranTruckingHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('EDIT PENGELUARAN TRUCKING HEADER '),
            'idtrans' => $pengeluaranTruckingHeader->id,
            'nobuktitrans' => $pengeluaranTruckingHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $pengeluaranTruckingHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengeluaranTruckingDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('EDIT PENGELUARAN TRUCKING DETAIL'),
            'idtrans' =>  $pengeluaranTruckingHeaderLogTrail->id,
            'nobuktitrans' => $pengeluaranTruckingHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $pengeluaranTruckingDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);
        return $pengeluaranTruckingHeader;
    }

    public function processDestroy($id, $postingDari = ''): PengeluaranTruckingHeader
    {
        $statusPosting = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING')->where('id', '84')->first();
        $klaim = DB::table('pengeluarantrucking')->from(DB::raw("pengeluarantrucking with (readuncommitted)"))->where('kodepengeluaran', "KLAIM")->first();

        $pengeluaranTruckingHeader = PengeluaranTruckingHeader::findOrFail($id);
        $dataHeader =  $pengeluaranTruckingHeader->toArray();
        $pengeluaranDetail = PengeluaranTruckingDetail::where('pengeluarantruckingheader_id', $pengeluaranTruckingHeader->id)->get();
        $dataDetail = $pengeluaranDetail->toArray();
        if ($klaim->id == $pengeluaranTruckingHeader->pengeluarantrucking_id) {
            if ($pengeluaranTruckingHeader->statusposting != $statusPosting->id) {
                $pinjaman = PengeluaranTruckingHeader::where('nobukti', $pengeluaranTruckingHeader->pengeluarantrucking_nobukti)->first();
                // dd($pinjaman);
                PengeluaranTruckingHeader::deletePinjamanPosting($pinjaman->id);
            }
        } else {
            if ($pengeluaranTruckingHeader->statusposting != $statusPosting->id) {
                $pengeluaranHeader = PengeluaranHeader::where('nobukti', $pengeluaranTruckingHeader->pengeluaran_nobukti)->lockForUpdate()->first();
                $PengeluaranHeader = (new PengeluaranHeader)->processDestroy($pengeluaranHeader->id, $postingDari);
            }
        }
        $pengeluaranTruckingHeader = $pengeluaranTruckingHeader->lockAndDestroy($id);

        $pengeluaranTruckingLogTrail = (new LogTrail())->processStore([
            'namatabel' => $this->table,
            'postingdari' => $postingDari,
            'idtrans' => $pengeluaranTruckingHeader->id,
            'nobuktitrans' => $pengeluaranTruckingHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $dataHeader,
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => (new LogTrail())->table,
            'postingdari' => $postingDari,
            'idtrans' => $pengeluaranTruckingLogTrail['id'],
            'nobuktitrans' => $pengeluaranTruckingHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $dataDetail,
            'modifiedby' => auth('api')->user()->name
        ]);

        return $pengeluaranTruckingHeader;
    }
}
