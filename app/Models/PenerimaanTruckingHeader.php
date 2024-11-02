<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\RunningNumberService;
use Illuminate\Database\Schema\Blueprint;

class PenerimaanTruckingHeader extends MyModel
{
    use HasFactory;
    protected $table = 'penerimaantruckingheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {
        $this->setRequestParameters();
        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';

        $user_id = auth('api')->user()->id ?? 0;
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'PnrmTruckingHeaderController';

        // $temprole = '##temprole' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($temprole, function ($table) {
        //     $table->bigInteger('aco_id')->nullable();
        // });

        // $queryaco = db::table("useracl")->from(db::raw("useracl a with (readuncommitted)"))
        //     ->select('a.aco_id')
        //     ->join(db::raw("penerimaantrucking b with (readuncommitted)"), 'a.aco_id', 'b.aco_id')
        //     ->where('a.user_id', $user_id);

        // DB::table($temprole)->insertUsing(['aco_id'], $queryaco);


        // $queryrole = db::table("acl")->from(db::raw("acl a with (readuncommitted)"))
        //     ->select('a.aco_id')
        //     ->join(db::raw("userrole b with (readuncommitted)"), 'a.role_id', 'b.role_id')
        //     ->join(db::raw("penerimaantrucking c with (readuncommitted)"), 'a.aco_id', 'c.aco_id')
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
                $table->string('penerimaantrucking_id', 50)->nullable();
                $table->string('penerimaan_nobukti', 50)->nullable();
                $table->longText('keteranganheader')->nullable();
                $table->string('bank_id', 50)->nullable();
                $table->longtext('supir_id')->nullable();
                $table->string('karyawan_id', 200)->nullable();
                $table->dateTime('tglbukacetak')->nullable();
                $table->longText('statuscetak')->nullable();
                $table->longText('statuscetaktext')->nullable();
                $table->string('userbukacetak', 200)->nullable();
                $table->integer('jumlahcetak')->nullable();
                $table->string('coa', 200)->nullable();
                $table->string('modifiedby', 200)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->date('tgldariheaderpenerimaanheader')->nullable();
                $table->date('tglsampaiheaderpenerimaanheader')->nullable();
                $table->integer('penerimaanbank_id')->nullable();
                $table->longText('nobukti_pelunasan')->nullable();
                $table->longText('url_pelunasan')->nullable();
            });

            $tempPelunasan = '##tempPelunasan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempPelunasan, function ($table) {
                $table->string('nobukti')->nullable();
                $table->longText('nobukti_pelunasan')->nullable();
                $table->longText('url_pelunasan')->nullable();
            });
            $petik = '"';
            $url = config('app.url_fe') . 'pengeluarantruckingheader';
            // DPO
            $getDataLain = DB::table("pengeluarantruckingdetail")->from(DB::raw("pengeluarantruckingdetail as a with (readuncommitted)"))
                ->select(DB::raw("b.nobukti, STRING_AGG(cast(a.nobukti as nvarchar(max)), ', ') as nobukti_pelunasan, STRING_AGG(cast('<a href=$petik" . $url . "?tgldari='+(format(c.tglbukti,'yyyy-MM')+'-1')+'&tglsampai='+(format(c.tglbukti,'yyyy-MM')+'-31')+'&nobukti='+a.nobukti+'$petik class=$petik link-color $petik target=$petik _blank $petik>'+a.nobukti+'</a>' as nvarchar(max)), ',') as url_pelunasan"))
                ->join(DB::raw("penerimaantruckingheader as b with (readuncommitted)"), 'a.penerimaantruckingheader_nobukti', 'b.nobukti')
                ->join(DB::raw("pengeluarantruckingheader as c with (readuncommitted)"), 'c.nobukti', 'a.nobukti')
                ->groupBy("b.nobukti");
            if (request()->tgldari && request()->tglsampai) {
                $getDataLain->whereBetween('b.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])->where('b.penerimaantrucking_id', 3);
            }

            DB::table($tempPelunasan)->insertUsing(['nobukti', 'nobukti_pelunasan', 'url_pelunasan'], $getDataLain);

            // BBM
            $getDataLain = DB::table("pengeluarantruckingdetail")->from(DB::raw("pengeluarantruckingdetail as a with (readuncommitted)"))
                ->select(DB::raw("b.nobukti, STRING_AGG(cast(a.nobukti as nvarchar(max)), ', ') as nobukti_pelunasan, STRING_AGG(cast('<a href=$petik" . $url . "?tgldari='+(format(c.tglbukti,'yyyy-MM')+'-1')+'&tglsampai='+(format(c.tglbukti,'yyyy-MM')+'-31')+'&nobukti='+a.nobukti+'$petik class=$petik link-color $petik target=$petik _blank $petik>'+a.nobukti+'</a>' as nvarchar(max)), ',') as url_pelunasan"))
                ->join(DB::raw("penerimaantruckingheader as b with (readuncommitted)"), 'a.penerimaantruckingheader_nobukti', 'b.nobukti')
                ->join(DB::raw("pengeluarantruckingheader as c with (readuncommitted)"), 'c.nobukti', 'a.nobukti')
                ->groupBy("b.nobukti");
            if (request()->tgldari && request()->tglsampai) {
                $getDataLain->whereBetween('b.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])->where('b.penerimaantrucking_id', 1);
            }

            DB::table($tempPelunasan)->insertUsing(['nobukti', 'nobukti_pelunasan', 'url_pelunasan'], $getDataLain);


            $query = DB::table($this->table)->from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                ->select(
                    'penerimaantruckingheader.id',
                    'penerimaantruckingheader.nobukti',
                    'penerimaantruckingheader.tglbukti',

                    'penerimaantrucking.keterangan as penerimaantrucking_id',
                    'penerimaantruckingheader.penerimaan_nobukti',
                    'penerimaantruckingheader.keterangan as keteranganheader',

                    'bank.namabank as bank_id',
                    'supir.namasupir as supir_id',
                    'karyawan.namakaryawan as karyawan_id',
                    DB::raw('(case when (year(penerimaantruckingheader.tglbukacetak) <= 2000) then null else penerimaantruckingheader.tglbukacetak end ) as tglbukacetak'),
                    'parameter.memo as statuscetak',
                    'parameter.text as statuscetaktext',
                    'penerimaantruckingheader.userbukacetak',
                    'penerimaantruckingheader.jumlahcetak',
                    'akunpusat.keterangancoa as coa',
                    'penerimaantruckingheader.modifiedby',
                    'penerimaantruckingheader.created_at',
                    'penerimaantruckingheader.updated_at',
                    db::raw("cast((format(penerimaanheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpenerimaanheader"),
                    db::raw("cast(cast(format((cast((format(penerimaanheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpenerimaanheader"),
                    'penerimaanheader.bank_id as penerimaanbank_id',
                    DB::raw("cast(isnull(asal.nobukti_pelunasan, '') as nvarchar(max)) as nobukti_pelunasan"),
                    DB::raw("cast(isnull(asal.url_pelunasan, '') as nvarchar(max)) as url_pelunasan")



                )
                ->leftJoin(DB::raw("penerimaanheader with (readuncommitted)"), 'penerimaantruckingheader.penerimaan_nobukti', '=', 'penerimaanheader.nobukti')
                ->leftJoin(DB::raw("penerimaantrucking with (readuncommitted)"), 'penerimaantruckingheader.penerimaantrucking_id', 'penerimaantrucking.id')
                ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'penerimaantruckingheader.coa', 'akunpusat.coa')
                ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'penerimaantruckingheader.statuscetak', 'parameter.id')
                ->leftJoin(DB::raw("bank with (readuncommitted)"), 'penerimaantruckingheader.bank_id', 'bank.id')
                ->leftJoin(DB::raw("supir with (readuncommitted)"), 'penerimaantruckingheader.supir_id', 'supir.id')
                ->leftJoin(DB::raw("$tempPelunasan as asal with (readuncommitted)"), 'penerimaantruckingheader.nobukti', 'asal.nobukti')
                ->leftJoin(DB::raw("karyawan with (readuncommitted)"), 'penerimaantruckingheader.karyawan_id', 'karyawan.id');
            // ->join(db::raw($temprole . " d "), 'penerimaantrucking.aco_id', 'd.aco_id');

            if (request()->tgldari) {
                $query->whereBetween('penerimaantruckingheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
            }
            if (request()->penerimaanheader_id) {
                $query->where('penerimaantrucking_id', request()->penerimaanheader_id);
            }
            if ($periode != '') {
                $periode = explode("-", $periode);
                $query->whereRaw("MONTH(penerimaantruckingheader.tglbukti) ='" . $periode[0] . "'")
                    ->whereRaw("year(penerimaantruckingheader.tglbukti) ='" . $periode[1] . "'");
            }
            if ($statusCetak != '') {
                $query->where("penerimaantruckingheader.statuscetak", $statusCetak);
            }

            $datadetail = json_decode($query->get(), true);
            foreach ($datadetail as $item) {
                $namasupir = $item['supir_id'] ?? '';
                if ($item['penerimaantrucking_id'] == 'DEPOSITO SUPIR' || $item['penerimaantrucking_id'] == 'PENGEMBALIAN PINJAMAN') {
                    // dd('test');
                    $querydetail1 = DB::table("penerimaantruckingdetail")->from(DB::raw("penerimaantruckingdetail  a with (readuncommitted)"))
                        ->select(
                            'b.namasupir',
                        )
                        ->join(db::raw("supir b with (readuncommitted)"), 'a.supir_id', 'b.id')
                        ->where('a.nobukti', $item['nobukti'])
                        ->groupby('b.namasupir');

                    // dd($querydetail1 );
                    $hit = 0;
                    $namasupir = '';
                    $datadetail1 = json_decode($querydetail1->get(), true);
                    foreach ($datadetail1 as $itemdetail) {
                        $hit = $hit + 1;
                        if ($hit == 1) {
                            $namasupir = $namasupir . $itemdetail['namasupir'];
                        } else {
                            $namasupir = $namasupir . ',' . $itemdetail['namasupir'];
                        }
                    }
                }

                $namakaryawan = '';
                if ($item['penerimaantrucking_id'] == 'DEPOSITO KARYAWAN' || $item['penerimaantrucking_id'] == 'PENGEMBALIAN PINJAMAN KARYAWAN') {
                    // dd('test');
                    $querydetail1 = DB::table("penerimaantruckingdetail")->from(DB::raw("penerimaantruckingdetail  a with (readuncommitted)"))
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
                    'penerimaantrucking_id' => $item['penerimaantrucking_id'],
                    'penerimaan_nobukti' => $item['penerimaan_nobukti'],
                    'keteranganheader' => $item['keteranganheader'],
                    'bank_id' => $item['bank_id'],
                    'supir_id' => $namasupir,
                    'karyawan_id' => $namakaryawan,
                    'tglbukacetak' => $item['tglbukacetak'],
                    'statuscetak' => $item['statuscetak'],
                    'statuscetaktext' => $item['statuscetaktext'],
                    'userbukacetak' => $item['userbukacetak'],
                    'jumlahcetak' => $item['jumlahcetak'],
                    'coa' => $item['coa'],
                    'modifiedby' => $item['modifiedby'],
                    'created_at' => $item['created_at'],
                    'updated_at' => $item['updated_at'],
                    'tgldariheaderpenerimaanheader' => $item['tgldariheaderpenerimaanheader'],
                    'tglsampaiheaderpenerimaanheader' => $item['tglsampaiheaderpenerimaanheader'],
                    'penerimaanbank_id' => $item['penerimaanbank_id'],
                    'nobukti_pelunasan' => $item['nobukti_pelunasan'],
                    'url_pelunasan' => $item['url_pelunasan'],

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
                'a.penerimaantrucking_id',
                'a.penerimaan_nobukti',
                'a.keteranganheader',
                'a.bank_id',
                'a.supir_id',
                'a.karyawan_id',
                'a.tglbukacetak',
                'a.statuscetak',
                'a.userbukacetak',
                'a.jumlahcetak',
                'a.coa',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
                'a.tgldariheaderpenerimaanheader',
                'a.tglsampaiheaderpenerimaanheader',
                'a.penerimaanbank_id',
                'a.nobukti_pelunasan',
                'a.url_pelunasan',
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

        $query = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
            ->select(
                'penerimaantruckingheader.id',
                'penerimaantruckingheader.nobukti',
                'penerimaantruckingheader.tglbukti',
                'penerimaantruckingheader.keterangan as keteranganheader',
                'penerimaantrucking.kodepenerimaan',
                'penerimaantruckingheader.penerimaantrucking_id',
                'penerimaantrucking.kodepenerimaan as penerimaantrucking',
                'penerimaantruckingheader.bank_id',
                'penerimaantruckingheader.supir_id as supirheader_id',
                'penerimaantruckingheader.karyawan_id as karyawanheader_id',
                'penerimaantruckingheader.periodedari',
                'penerimaantruckingheader.periodesampai',
                'bank.namabank as bank',
                'supir.namasupir as supir',
                'karyawan.namakaryawan as karyawan',
                'penerimaantruckingheader.coa',
                'akunpusat.keterangancoa',
                'penerimaantruckingheader.penerimaan_nobukti',
                'penerimaantruckingheader.jenisorder_id as jenisorderan_id',
                'penerimaantruckingheader.periodedari',
                'penerimaantruckingheader.periodesampai',
                'jenisorder.keterangan as jenisorder'
            )
            ->leftJoin(DB::raw("penerimaantrucking with (readuncommitted)"), 'penerimaantruckingheader.penerimaantrucking_id', 'penerimaantrucking.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'penerimaantruckingheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'penerimaantruckingheader.supir_id', 'supir.id')
            ->leftJoin(DB::raw("karyawan with (readuncommitted)"), 'penerimaantruckingheader.karyawan_id', 'karyawan.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'penerimaantruckingheader.jenisorder_id', 'jenisorder.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'penerimaantruckingheader.coa', 'akunpusat.coa')
            ->where('penerimaantruckingheader.id', '=', $id);


        $data = $query->first();

        return $data;
    }

    public function penerimaantruckingdetail()
    {
        return $this->hasMany(PenerimaanTruckingDetail::class, 'penerimaantruckingheader_id');
    }

    public function selectColumns($query)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->integer('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->integer('penerimaantruckingid')->nullable();
            $table->string('penerimaantrucking_id', 50)->nullable();
            $table->string('penerimaan_nobukti', 50)->nullable();
            $table->longText('keteranganheader')->nullable();
            $table->string('bank_id', 50)->nullable();
            $table->string('supir_id', 200)->nullable();
            $table->string('karyawan_id', 200)->nullable();
            $table->dateTime('tglbukacetak')->nullable();
            $table->longText('statuscetak')->nullable();
            $table->longText('statuscetaktext')->nullable();
            $table->string('userbukacetak', 200)->nullable();
            $table->integer('jumlahcetak')->nullable();
            $table->string('coa', 200)->nullable();
            $table->string('modifiedby', 200)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->date('tgldariheaderpenerimaanheader')->nullable();
            $table->date('tglsampaiheaderpenerimaanheader')->nullable();
            $table->longText('nobukti_pelunasan')->nullable();
        });


        $tempPelunasan = '##tempPelunasan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempPelunasan, function ($table) {
            $table->string('nobukti')->nullable();
            $table->longText('nobukti_pelunasan')->nullable();
            $table->longText('url_pelunasan')->nullable();
        });
        $petik = '"';
        $url = config('app.url_fe') . 'pengeluarantruckingheader';
        // DPO
        $getDataLain = DB::table("pengeluarantruckingdetail")->from(DB::raw("pengeluarantruckingdetail as a with (readuncommitted)"))
            ->select(DB::raw("b.nobukti, STRING_AGG(cast(a.nobukti as nvarchar(max)), ', ') as nobukti_pelunasan, STRING_AGG(cast('<a href=$petik" . $url . "?tgldari='+(format(c.tglbukti,'yyyy-MM')+'-1')+'&tglsampai='+(format(c.tglbukti,'yyyy-MM')+'-31')+'&nobukti='+a.nobukti+'$petik class=$petik link-color $petik target=$petik _blank $petik>'+a.nobukti+'</a>' as nvarchar(max)), ',') as url_pelunasan"))
            ->join(DB::raw("penerimaantruckingheader as b with (readuncommitted)"), 'a.penerimaantruckingheader_nobukti', 'b.nobukti')
            ->join(DB::raw("pengeluarantruckingheader as c with (readuncommitted)"), 'c.nobukti', 'a.nobukti')
            ->groupBy("b.nobukti");
        if (request()->tgldariheader && request()->tglsampaiheader) {
            $getDataLain->whereBetween('b.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))])->where('b.penerimaantrucking_id', 3);
        }

        DB::table($tempPelunasan)->insertUsing(['nobukti', 'nobukti_pelunasan', 'url_pelunasan'], $getDataLain);

        // BBM
        $getDataLain = DB::table("pengeluarantruckingdetail")->from(DB::raw("pengeluarantruckingdetail as a with (readuncommitted)"))
            ->select(DB::raw("b.nobukti, STRING_AGG(cast(a.nobukti as nvarchar(max)), ', ') as nobukti_pelunasan, STRING_AGG(cast('<a href=$petik" . $url . "?tgldari='+(format(c.tglbukti,'yyyy-MM')+'-1')+'&tglsampai='+(format(c.tglbukti,'yyyy-MM')+'-31')+'&nobukti='+a.nobukti+'$petik class=$petik link-color $petik target=$petik _blank $petik>'+a.nobukti+'</a>' as nvarchar(max)), ',') as url_pelunasan"))
            ->join(DB::raw("penerimaantruckingheader as b with (readuncommitted)"), 'a.penerimaantruckingheader_nobukti', 'b.nobukti')
            ->join(DB::raw("pengeluarantruckingheader as c with (readuncommitted)"), 'c.nobukti', 'a.nobukti')
            ->groupBy("b.nobukti");
        if (request()->tgldariheader && request()->tglsampaiheader) {
            $getDataLain->whereBetween('b.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))])->where('b.penerimaantrucking_id', 1);
        }

        DB::table($tempPelunasan)->insertUsing(['nobukti', 'nobukti_pelunasan', 'url_pelunasan'], $getDataLain);
        $query = DB::table($this->table)->from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
            ->select(
                'penerimaantruckingheader.id',
                'penerimaantruckingheader.nobukti',
                'penerimaantruckingheader.tglbukti',
                'penerimaantruckingheader.penerimaantrucking_id as penerimaantruckingid',

                'penerimaantrucking.keterangan as penerimaantrucking_id',
                'penerimaantruckingheader.penerimaan_nobukti',
                'penerimaantruckingheader.keterangan as keteranganheader',

                'bank.namabank as bank_id',
                'supir.namasupir as supir_id',
                'karyawan.namakaryawan as karyawan_id',
                DB::raw('(case when (year(penerimaantruckingheader.tglbukacetak) <= 2000) then null else penerimaantruckingheader.tglbukacetak end ) as tglbukacetak'),
                'parameter.memo as statuscetak',
                'parameter.text as statuscetaktext',
                'penerimaantruckingheader.userbukacetak',
                'penerimaantruckingheader.jumlahcetak',
                'akunpusat.keterangancoa as coa',
                'penerimaantruckingheader.modifiedby',
                'penerimaantruckingheader.created_at',
                'penerimaantruckingheader.updated_at',
                db::raw("cast((format(penerimaanheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpenerimaanheader"),
                db::raw("cast(cast(format((cast((format(penerimaanheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpenerimaanheader"),
                DB::raw("cast(isnull(asal.nobukti_pelunasan, '') as nvarchar(max)) as nobukti_pelunasan"),
            )
            ->leftJoin(DB::raw("penerimaanheader with (readuncommitted)"), 'penerimaantruckingheader.penerimaan_nobukti', '=', 'penerimaanheader.nobukti')
            ->leftJoin(DB::raw("penerimaantrucking with (readuncommitted)"), 'penerimaantruckingheader.penerimaantrucking_id', 'penerimaantrucking.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'penerimaantruckingheader.coa', 'akunpusat.coa')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'penerimaantruckingheader.statuscetak', 'parameter.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'penerimaantruckingheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'penerimaantruckingheader.supir_id', 'supir.id')
            ->leftJoin(DB::raw("$tempPelunasan as asal with (readuncommitted)"), 'penerimaantruckingheader.nobukti', 'asal.nobukti')
            ->leftJoin(DB::raw("karyawan with (readuncommitted)"), 'penerimaantruckingheader.karyawan_id', 'karyawan.id');
        // ->join(db::raw($temprole . " d "), 'penerimaantrucking.aco_id', 'd.aco_id');
        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'penerimaantruckingid',
            'penerimaantrucking_id',
            'penerimaan_nobukti',
            'keteranganheader',
            'bank_id',
            'supir_id',
            'karyawan_id',
            'tglbukacetak',
            'statuscetak',
            'statuscetaktext',
            'userbukacetak',
            'jumlahcetak',
            'coa',
            'modifiedby',
            'created_at',
            'updated_at',
            'tgldariheaderpenerimaanheader',
            'tglsampaiheaderpenerimaanheader',
            'nobukti_pelunasan'
        ], $query);

        $query = DB::table($temp)->from(DB::raw($temp . " a "))
            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                'a.penerimaantruckingid',
                'a.penerimaantrucking_id',
                'a.penerimaan_nobukti',
                'a.keteranganheader',
                'a.bank_id',
                'a.supir_id',
                'a.karyawan_id',
                'a.tglbukacetak',
                'a.statuscetak',
                'a.statuscetaktext',
                'a.userbukacetak',
                'a.jumlahcetak',
                'a.coa',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
                'a.tgldariheaderpenerimaanheader',
                'a.tglsampaiheaderpenerimaanheader',
                'a.nobukti_pelunasan'

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
            $table->string('penerimaantruckingid', 50)->nullable();
            $table->string('penerimaantrucking_id', 50)->nullable();
            $table->string('penerimaan_nobukti', 50)->nullable();
            $table->longText('keteranganheader')->nullable();
            $table->string('bank_id', 50)->nullable();
            $table->string('supir_id', 200)->nullable();
            $table->string('karyawan_id', 200)->nullable();
            $table->dateTime('tglbukacetak')->nullable();
            $table->longText('statuscetak')->nullable();
            $table->longText('statuscetaktext')->nullable();
            $table->string('userbukacetak', 200)->nullable();
            $table->integer('jumlahcetak')->nullable();
            $table->string('coa', 200)->nullable();
            $table->string('modifiedby', 200)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->date('tgldariheaderpenerimaanheader')->nullable();
            $table->date('tglsampaiheaderpenerimaanheader')->nullable();
            $table->longText('nobukti_pelunasan')->nullable();
            $table->increments('position');
        });
        // if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
        //     request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
        //     request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
        // }

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        if (request()->tgldariheader) {
            $query->whereBetween('a.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        }
        if (request()->penerimaanheader_id) {
            $query->where('a.penerimaantruckingid', request()->penerimaanheader_id);
        }
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'penerimaantruckingid', 'penerimaantrucking_id', 'penerimaan_nobukti', 'keteranganheader', 'bank_id', 'supir_id', 'karyawan_id', 'tglbukacetak', 'statuscetak', 'statuscetaktext', 'userbukacetak',  'jumlahcetak', 'coa', 'modifiedby', 'created_at', 'updated_at', 'tgldariheaderpenerimaanheader', 'tglsampaiheaderpenerimaanheader', 'nobukti_pelunasan'], $models);


        return  $temp;
    }

    public function getDeposito($supir_id)
    {
        $tglbukti = date('Y-m-d', strtotime(request()->tglbukti));
        $tempPribadi = $this->createTempDeposito($supir_id, $tglbukti);

        $query = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By penerimaantruckingdetail.nobukti) as id,penerimaantruckingheader.tglbukti,penerimaantruckingdetail.nobukti,penerimaantruckingdetail.keterangan," . $tempPribadi . ".sisa"))
            ->leftJoin(DB::raw("$tempPribadi with (readuncommitted)"), 'penerimaantruckingdetail.nobukti', $tempPribadi . ".nobukti")
            ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingdetail.nobukti', "penerimaantruckingheader.nobukti")
            ->whereRaw("penerimaantruckingdetail.supir_id = $supir_id")
            ->whereRaw("penerimaantruckingdetail.nobukti = $tempPribadi.nobukti")
            ->where(function ($query) use ($tempPribadi) {
                $query->whereRaw("$tempPribadi.sisa != 0")
                    ->orWhereRaw("$tempPribadi.sisa is null");
            })
            ->where("penerimaantruckingheader.tglbukti", '<=', $tglbukti)
            ->orderBy('penerimaantruckingheader.tglbukti', 'asc')
            ->orderBy('penerimaantruckingdetail.nobukti', 'asc');

        return $query->get();
    }

    public function createTempDeposito($supir_id, $tglbukti)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        // $fetch = DB::table('penerimaantruckingdetail')
        //     ->from(
        //         DB::raw("penerimaantruckingdetail with (readuncommitted)")
        //     )
        //     ->select(DB::raw("penerimaantruckingdetail.nobukti, (SELECT (penerimaantruckingdetail.nominal - coalesce(SUM(pengeluarantruckingdetail.nominal),0)) FROM pengeluarantruckingdetail WHERE pengeluarantruckingdetail.penerimaantruckingheader_nobukti= penerimaantruckingdetail.nobukti) AS sisa"))
        //     // ->leftJoin(DB::raw("penerimaantruckingdetail with (readuncommitted)"), 'penerimaantruckingdetail.pengeluarantruckingheader_nobukti', 'pengeluarantruckingdetail.nobukti')
        //     ->whereRaw("penerimaantruckingdetail.supir_id = $supir_id")
        //     ->where("penerimaantruckingdetail.nobukti",  'LIKE', "%DPO%")
        //     ->groupBy('penerimaantruckingdetail.nobukti', 'penerimaantruckingdetail.nominal');

        $temppenerimaandeposito = '##temppenerimaandeposito' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppenerimaandeposito, function ($table) {
            $table->bigInteger('supir_id')->nullable();
            $table->string('nobukti')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $temppengeluarandeposito = '##temppengeluarandeposito' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppengeluarandeposito, function ($table) {
            $table->bigInteger('supir_id')->nullable();
            $table->string('nobukti')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });



        $querypenerimaandeposito = db::table("penerimaantruckingheader")->from(db::raw("penerimaantruckingheader a with (readuncommitted)"))
            ->select(
                'b.supir_id',
                'a.nobukti',
                db::raw("sum(b.nominal) as nominal")
            )
            ->join(db::raw("penerimaantruckingdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereraw("a.penerimaantrucking_id=3")
            ->where('b.supir_id', $supir_id)
            ->where('a.tglbukti', '<=', $tglbukti)
            ->groupby('b.supir_id')
            ->groupby('a.nobukti');


        $querypengeluarandeposito = db::table("pengeluarantruckingheader")->from(db::raw("pengeluarantruckingheader a with (readuncommitted)"))
            ->select(
                'b.supir_id',
                db::raw("b.penerimaantruckingheader_nobukti as nobukti"),
                db::raw("sum(b.nominal) as nominal")
            )
            ->join(db::raw("pengeluarantruckingdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereraw("a.pengeluarantrucking_id=2")
            ->where('b.supir_id', $supir_id)
            ->groupby('b.supir_id')
            ->groupby('b.penerimaantruckingheader_nobukti');

        DB::table($temppenerimaandeposito)->insertUsing([
            'supir_id',
            'nobukti',
            'nominal'
        ], $querypenerimaandeposito);

        DB::table($temppengeluarandeposito)->insertUsing([
            'supir_id',
            'nobukti',
            'nominal'
        ], $querypengeluarandeposito);

        // dump(db::table($temppenerimaandeposito)->get());
        // dd(db::table($temppengeluarandeposito)->get());

        $fetch = db::table($temppenerimaandeposito)->from(db::raw($temppenerimaandeposito . " a"))
            ->select(
                'a.nobukti',
                db::raw("(isnull(a.nominal,0)-isnull(b.nominal,0)) as sisa")

            )
            ->leftjoin(db::raw($temppengeluarandeposito . " b"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("(isnull(a.nominal,0)-isnull(b.nominal,0))<>0")
            ->orderBy('a.nobukti', 'asc');


        // dd($fetch->get());

        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->double('sisa', 15, 2)->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'sisa'], $fetch);


        return $temp;
    }

    public function createTempBbm($nobuktiebs, $tglbukti)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $temppenerimaanbbm = '##temppenerimaanbbm' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppenerimaanbbm, function ($table) {
            $table->string('nobuktiebs', 50)->nullable();
            $table->string('nobukti')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $temppengeluaranbbm = '##temppengeluaranbbm' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppengeluaranbbm, function ($table) {
            $table->string('nobuktiebs', 50)->nullable();
            $table->string('nobukti')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });



        $querypenerimaanbbm = db::table("penerimaantruckingheader")->from(db::raw("penerimaantruckingheader a with (readuncommitted)"))
            ->select(
                'c.nobukti as nobuktiebs',
                'a.nobukti',
                db::raw("sum(b.nominal) as nominal")
            )
            ->join(db::raw("penerimaantruckingdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(db::raw("gajisupirbbm d with (readuncommitted)"), 'a.nobukti', 'd.penerimaantrucking_nobukti')
            ->join(db::raw("prosesgajisupirdetail c with (readuncommitted)"), 'd.gajisupir_nobukti', 'c.gajisupir_nobukti')
            ->whereraw("a.penerimaantrucking_id=1")
            ->where('c.nobukti', $nobuktiebs)
            ->where('a.tglbukti', '<=', $tglbukti)
            ->groupby('c.nobukti')
            ->groupby('a.nobukti');


        $querypengeluaranbbm = db::table("pengeluarantruckingheader")->from(db::raw("pengeluarantruckingheader a with (readuncommitted)"))
            ->select(
                'c.nobukti as nobuktiebs',
                db::raw("b.penerimaantruckingheader_nobukti as nobukti"),
                db::raw("sum(b.nominal) as nominal")
            )
            ->join(db::raw("pengeluarantruckingdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(db::raw("penerimaantruckingheader e with (readuncommitted)"), 'b.penerimaantruckingheader_nobukti', 'e.nobukti')
            ->join(db::raw("gajisupirbbm d with (readuncommitted)"), 'e.nobukti', 'd.penerimaantrucking_nobukti')
            ->join(db::raw("prosesgajisupirdetail c with (readuncommitted)"), 'd.gajisupir_nobukti', 'c.gajisupir_nobukti')
            ->whereraw("a.pengeluarantrucking_id=5")
            ->where('c.nobukti', $nobuktiebs)
            ->groupby('c.nobukti')
            ->groupby('b.penerimaantruckingheader_nobukti');

        DB::table($temppenerimaanbbm)->insertUsing([
            'nobuktiebs',
            'nobukti',
            'nominal'
        ], $querypenerimaanbbm);

        DB::table($temppengeluaranbbm)->insertUsing([
            'nobuktiebs',
            'nobukti',
            'nominal'
        ], $querypengeluaranbbm);

        // dump(db::table($temppenerimaandeposito)->get());
        // dd(db::table($temppengeluarandeposito)->get());

        $fetch = db::table($temppenerimaanbbm)->from(db::raw($temppenerimaanbbm . " a"))
            ->select(
                'a.nobukti',
                db::raw("(isnull(a.nominal,0)-isnull(b.nominal,0)) as sisa")

            )
            ->leftjoin(db::raw($temppengeluaranbbm . " b"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("(isnull(a.nominal,0)-isnull(b.nominal,0))<>0")
            ->orderBy('a.nobukti', 'asc');


        // dd($fetch->get());

        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->double('sisa', 15, 2)->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'sisa'], $fetch);


        return $temp;
    }

    public function getDepositoKaryawan($karyawan_id)
    {
        $tempPribadi = $this->createTempDepositoKaryawan($karyawan_id);
        $tglbukti = date('Y-m-d', strtotime(request()->tglbukti));

        $query = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By penerimaantruckingdetail.nobukti) as id,penerimaantruckingheader.tglbukti,penerimaantruckingdetail.nobukti,penerimaantruckingdetail.keterangan," . $tempPribadi . ".sisa"))
            ->leftJoin(DB::raw("$tempPribadi with (readuncommitted)"), 'penerimaantruckingdetail.nobukti', $tempPribadi . ".nobukti")
            ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingdetail.nobukti', "penerimaantruckingheader.nobukti")
            ->whereRaw("penerimaantruckingdetail.karyawan_id = $karyawan_id")
            ->whereRaw("penerimaantruckingdetail.nobukti = $tempPribadi.nobukti")
            ->where(function ($query) use ($tempPribadi) {
                $query->whereRaw("$tempPribadi.sisa != 0")
                    ->orWhereRaw("$tempPribadi.sisa is null");
            })
            ->where("penerimaantruckingheader.tglbukti", '<=', $tglbukti)
            ->orderBy('penerimaantruckingheader.tglbukti', 'asc')
            ->orderBy('penerimaantruckingdetail.nobukti', 'asc');

        return $query->get();
    }

    public function createTempDepositoKaryawan($karyawan_id)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $temppenerimaandeposito = '##temppenerimaandepositokaryawan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppenerimaandeposito, function ($table) {
            $table->bigInteger('karyawan_id')->nullable();
            $table->string('nobukti')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $temppengeluarandeposito = '##temppengeluarandepositokaryawan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppengeluarandeposito, function ($table) {
            $table->bigInteger('karyawan_id')->nullable();
            $table->string('nobukti')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });



        $querypenerimaandeposito = db::table("penerimaantruckingheader")->from(db::raw("penerimaantruckingheader a with (readuncommitted)"))
            ->select(
                'b.karyawan_id',
                'a.nobukti',
                db::raw("sum(b.nominal) as nominal")
            )
            ->join(db::raw("penerimaantruckingdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereraw("a.penerimaantrucking_id=6")
            ->where('b.karyawan_id', $karyawan_id)
            ->groupby('b.karyawan_id')
            ->groupby('a.nobukti');


        $querypengeluarandeposito = db::table("pengeluarantruckingheader")->from(db::raw("pengeluarantruckingheader a with (readuncommitted)"))
            ->select(
                'b.karyawan_id',
                db::raw("b.penerimaantruckingheader_nobukti as nobukti"),
                db::raw("sum(b.nominal) as nominal")
            )
            ->join(db::raw("pengeluarantruckingdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereraw("a.pengeluarantrucking_id=16")
            ->where('b.karyawan_id', $karyawan_id)
            ->groupby('b.karyawan_id')
            ->groupby('b.penerimaantruckingheader_nobukti');

        DB::table($temppenerimaandeposito)->insertUsing([
            'karyawan_id',
            'nobukti',
            'nominal'
        ], $querypenerimaandeposito);

        DB::table($temppengeluarandeposito)->insertUsing([
            'karyawan_id',
            'nobukti',
            'nominal'
        ], $querypengeluarandeposito);

        // dump(db::table($temppenerimaandeposito)->get());
        // dd(db::table($temppengeluarandeposito)->get());

        $fetch = db::table($temppenerimaandeposito)->from(db::raw($temppenerimaandeposito . " a"))
            ->select(
                'a.nobukti',
                db::raw("(isnull(a.nominal,0)-isnull(b.nominal,0)) as sisa")

            )
            ->leftjoin(db::raw($temppengeluarandeposito . " b"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("(isnull(a.nominal,0)-isnull(b.nominal,0))<>0")
            ->orderBy('a.nobukti', 'asc');


        // dd($fetch->get());

        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'sisa'], $fetch);


        return $temp;
    }

    public function getPelunasanOld($tgldari, $tglsampai)
    {
        $tempPribadi = $this->createTempPelunasan($tgldari, $tglsampai);

        $query =  DB::table('penerimaantruckingheader')
            ->from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By penerimaantruckingheader.nobukti) as id, penerimaantruckingheader.tglbukti, penerimaantruckingheader.nobukti, $tempPribadi.keterangan, $tempPribadi.sisa"))
            ->leftJoin(DB::raw("$tempPribadi with (readuncommitted)"), 'penerimaantruckingheader.nobukti', "$tempPribadi.nobukti")
            ->whereBetween('penerimaantruckingheader.tglbukti', [date('Y-m-d', strtotime($tgldari)), date('Y-m-d', strtotime($tglsampai))])
            ->whereRaw("penerimaantruckingheader.nobukti = $tempPribadi.nobukti")
            ->where(function ($query) use ($tempPribadi) {
                $query->whereRaw("$tempPribadi.sisa != 0")
                    ->orWhereRaw("$tempPribadi.sisa is null");
            })
            ->orderBy('penerimaantruckingheader.tglbukti', 'asc')
            ->orderBy('penerimaantruckingheader.nobukti', 'asc');

        return $query->get();
    }

    public function getPelunasan($tgldari, $tglsampai)
    {
        $tempPribadi = $this->createTempPelunasan($tgldari, $tglsampai);

        $parameter = new Parameter();
        $cabang = $parameter->cekText('CABANG', 'CABANG') ?? '1900-01-01';

        if ($cabang == 'MEDAN') {
            // dd(db::table($tempPribadi)->get());
            $query =  DB::table($tempPribadi)
                ->from(DB::raw($tempPribadi . " a "))
                ->select(
                    DB::raw("row_number() Over(Order By a.nobukti) as id"),
                    'a.tglbukti',
                    'a.nobukti',
                    'a.keterangan',
                    'a.sisa'
                )
                ->orderBy('a.tglbukti', 'asc')
                ->orderBy('a.nobukti', 'asc');
        } else {
            $query =  DB::table('penerimaantruckingheader')
                ->from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                ->select(
                    DB::raw("row_number() Over(Order By penerimaantruckingheader.nobukti) as id, 
                penerimaantruckingheader.tglbukti, 
                penerimaantruckingheader.nobukti, 
                $tempPribadi.keterangan, 
                $tempPribadi.sisa")
                )
                ->leftJoin(DB::raw("$tempPribadi with (readuncommitted)"), 'penerimaantruckingheader.nobukti', "$tempPribadi.nobukti")
                ->whereBetween('penerimaantruckingheader.tglbukti', [date('Y-m-d', strtotime($tgldari)), date('Y-m-d', strtotime($tglsampai))])
                ->whereRaw("penerimaantruckingheader.nobukti = $tempPribadi.nobukti")
                ->where(function ($query) use ($tempPribadi) {
                    $query->whereRaw("$tempPribadi.sisa != 0")
                        ->orWhereRaw("$tempPribadi.sisa is null");
                })
                ->orderBy('penerimaantruckingheader.tglbukti', 'asc')
                ->orderBy('penerimaantruckingheader.nobukti', 'asc');
        }


        return $query->get();
    }

    public function createTempPelunasanOld($tgldari, $tglsampai)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = DB::table('penerimaantruckingdetail')
            ->from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
            ->leftJoin('penerimaantruckingheader', 'penerimaantruckingdetail.nobukti', '=', 'penerimaantruckingheader.nobukti')
            ->select(DB::raw("penerimaantruckingdetail.nobukti, MAX(penerimaantruckingdetail.keterangan) as keterangan, (SELECT (SUM(penerimaantruckingdetail.nominal) - COALESCE(SUM(pengeluarantruckingdetail.nominal), 0)) FROM pengeluarantruckingdetail WHERE pengeluarantruckingdetail.penerimaantruckingheader_nobukti = penerimaantruckingdetail.nobukti) AS sisa"))
            ->whereBetween('penerimaantruckingheader.tglbukti', [date('Y-m-d', strtotime($tgldari)), date('Y-m-d', strtotime($tglsampai))])
            ->where('penerimaantruckingheader.penerimaantrucking_id', '=', 1)
            ->groupBy('penerimaantruckingdetail.nobukti');
        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->longText('keterangan')->nullable();
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'keterangan', 'sisa'], $fetch);

        return $temp;
    }

    public function createTempPelunasan($tgldari, $tglsampai)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $parameter = new Parameter();
        $cabang = $parameter->cekText('CABANG', 'CABANG') ?? '1900-01-01';

        if ($cabang == 'MEDAN') {

            $temptgl = '##temptgl' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

            Schema::create($temptgl, function ($table) {
                $table->string('nobukti');
                $table->longText('keterangan')->nullable();
            });

            $fetch1 = DB::table('penerimaantruckingdetail')
                ->from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
                ->select(
                    DB::raw("prosesgajisupirdetail.nobukti, 
                'Pelunasan Hutang BBM Supir Periode '+format(min(suratpengantar.tglbukti),'dd-MM-yyyy')+' s/d ' + format(max(suratpengantar.tglbukti),'dd-MM-yyyy') as keterangan")
                )
                ->Join(db::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingdetail.nobukti', '=', 'penerimaantruckingheader.nobukti')
                ->Join(db::raw("gajisupirbbm with (readuncommitted)"), 'gajisupirbbm.penerimaantrucking_nobukti', '=', 'penerimaantruckingheader.nobukti')
                ->Join(db::raw("prosesgajisupirdetail with (readuncommitted)"), 'prosesgajisupirdetail.gajisupir_nobukti', '=', 'gajisupirbbm.gajisupir_nobukti')
                ->Join(db::raw("prosesgajisupirheader with (readuncommitted)"), 'prosesgajisupirheader.nobukti', '=', 'prosesgajisupirdetail.nobukti')
                ->Join(db::raw("gajisupirdetail with (readuncommitted)"), 'gajisupirdetail.nobukti', '=', 'prosesgajisupirdetail.gajisupir_nobukti')
                ->Join(db::raw("suratpengantar with (readuncommitted)"), 'suratpengantar.nobukti', '=', 'gajisupirdetail.suratpengantar_nobukti')
                ->leftJoin(db::raw("pengeluarantruckingdetail with (readuncommitted)"), 'penerimaantruckingdetail.nobukti', '=', 'pengeluarantruckingdetail.penerimaantruckingheader_nobukti ')
                ->whereBetween('penerimaantruckingheader.tglbukti', [date('Y-m-d', strtotime($tgldari)), date('Y-m-d', strtotime($tglsampai))])
                ->where('penerimaantruckingheader.penerimaantrucking_id', '=', 1)
                ->groupBy('prosesgajisupirdetail.nobukti');

            DB::table($temptgl)->insertUsing(['nobukti', 'keterangan'], $fetch1);

            $fetch = DB::table('penerimaantruckingdetail')
                ->from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
                ->select(
                    DB::raw("prosesgajisupirdetail.nobukti, 
                MAX(prosesgajisupirheader.tglbukti) as tglbukti, 
                    max(isnull(d.keterangan,'')) as keterangan, 
                    sum(isnull(penerimaantruckingdetail.nominal,0)-isnull(pengeluarantruckingdetail.nominal,0)) as sisa ")
                )
                ->Join(db::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingdetail.nobukti', '=', 'penerimaantruckingheader.nobukti')
                ->Join(db::raw("gajisupirbbm with (readuncommitted)"), 'gajisupirbbm.penerimaantrucking_nobukti', '=', 'penerimaantruckingheader.nobukti')
                ->Join(db::raw("prosesgajisupirdetail with (readuncommitted)"), 'prosesgajisupirdetail.gajisupir_nobukti', '=', 'gajisupirbbm.gajisupir_nobukti')
                ->Join(db::raw("prosesgajisupirheader with (readuncommitted)"), 'prosesgajisupirheader.nobukti', '=', 'prosesgajisupirdetail.nobukti')
                ->leftJoin(db::raw("pengeluarantruckingdetail with (readuncommitted)"), 'penerimaantruckingdetail.nobukti', '=', 'pengeluarantruckingdetail.penerimaantruckingheader_nobukti ')
                ->leftJoin(db::raw($temptgl . " d "), 'prosesgajisupirheader.nobukti', '=', 'd.nobukti ')
                ->whereBetween('penerimaantruckingheader.tglbukti', [date('Y-m-d', strtotime($tgldari)), date('Y-m-d', strtotime($tglsampai))])
                ->where('penerimaantruckingheader.penerimaantrucking_id', '=', 1)
                // ->whereraw("isnull(prosesgajisupirdetail.nobukti,'')='EBS 0012/VIiI/2024'")
                ->groupBy('prosesgajisupirdetail.nobukti');

            // dd($fetch->tosql());
            Schema::create($temp, function ($table) {
                $table->string('nobukti');
                $table->datetime('tglbukti');
                $table->longText('keterangan')->nullable();
                $table->bigInteger('sisa')->nullable();
            });


            DB::table($temp)->insertUsing(['nobukti', 'tglbukti', 'keterangan', 'sisa'], $fetch);

            DB::delete(DB::raw("delete " . $temp . " where sisa=0"));
        } else {
            $fetch = DB::table('penerimaantruckingdetail')
                ->from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
                ->leftJoin('penerimaantruckingheader', 'penerimaantruckingdetail.nobukti', '=', 'penerimaantruckingheader.nobukti')
                ->select(
                    DB::raw("penerimaantruckingdetail.nobukti, 
                MAX(penerimaantruckingheader.tglbukti) as tglbukti, 
                MAX(penerimaantruckingdetail.keterangan) as keterangan, 
                (SELECT (SUM(penerimaantruckingdetail.nominal) - COALESCE(SUM(pengeluarantruckingdetail.nominal), 0)) FROM pengeluarantruckingdetail WHERE pengeluarantruckingdetail.penerimaantruckingheader_nobukti = penerimaantruckingdetail.nobukti) AS sisa")
                )
                ->whereBetween('penerimaantruckingheader.tglbukti', [date('Y-m-d', strtotime($tgldari)), date('Y-m-d', strtotime($tglsampai))])
                ->where('penerimaantruckingheader.penerimaantrucking_id', '=', 1)
                ->groupBy('penerimaantruckingdetail.nobukti');

            Schema::create($temp, function ($table) {
                $table->string('nobukti');
                $table->datetime('tglbukti');
                $table->longText('keterangan')->nullable();
                $table->bigInteger('sisa')->nullable();
            });

            DB::table($temp)->insertUsing(['nobukti', 'tglbukti', 'keterangan', 'sisa'], $fetch);
        }



        return $temp;
    }

    public function getPinjaman($supir_id, $isCekPemutihan = false)
    {
        $tempPribadi = $this->createTempPinjPribadi($supir_id);
        $tglbukti = date('Y-m-d', strtotime(request()->tglbukti));
        $query = PengeluaranTruckingDetail::from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By pengeluarantruckingdetail.nobukti) as id,pengeluarantruckingheader.tglbukti,pengeluarantruckingdetail.nobukti,pengeluarantruckingdetail.keterangan,pengeluarantruckingdetail.supir_id as pinj_supirid, supir.namasupir as pinj_supir," . $tempPribadi . ".sisa,$tempPribadi.jlhpinjaman,$tempPribadi.totalbayar"))
            ->leftJoin(DB::raw("$tempPribadi with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', $tempPribadi . ".nobukti")
            ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', "pengeluarantruckingheader.nobukti")
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingdetail.supir_id', "supir.id");
        if ($supir_id != 0) {
            $query->whereRaw("pengeluarantruckingdetail.supir_id = $supir_id");
        }
        $query->whereRaw("pengeluarantruckingdetail.nobukti = $tempPribadi.nobukti")
            ->where(function ($query) use ($tempPribadi) {
                $query->whereRaw("$tempPribadi.sisa != 0")
                    ->orWhereRaw("$tempPribadi.sisa is null");
            })
            ->where("pengeluarantruckingheader.pengeluarantrucking_id",  1)
            ->where("pengeluarantruckingheader.tglbukti", '<=', $tglbukti)
            ->orderBy('pengeluarantruckingheader.tglbukti', 'asc')
            ->orderBy('pengeluarantruckingdetail.nobukti', 'asc');
        if ($isCekPemutihan) {
            return $query->first();
        } else {
            return $query->get();
        }
    }
    public function getPinjamanKaryawan($karyawan_id)
    {
        $tempPribadi = $this->createTempPinjPribadiKaryawan($karyawan_id);

        $tglbukti = date('Y-m-d', strtotime(request()->tglbukti));
        $query = PengeluaranTruckingDetail::from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By pengeluarantruckingdetail.nobukti) as id,pengeluarantruckingheader.tglbukti,pengeluarantruckingdetail.nobukti,pengeluarantruckingdetail.keterangan,pengeluarantruckingdetail.karyawan_id as pinj_karyawanid, karyawan.namakaryawan as pinj_karyawan," . $tempPribadi . ".sisa"))
            ->leftJoin(DB::raw("$tempPribadi with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', $tempPribadi . ".nobukti")
            ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', "pengeluarantruckingheader.nobukti")
            ->leftJoin(DB::raw("karyawan with (readuncommitted)"), 'pengeluarantruckingdetail.karyawan_id', "karyawan.id")
            ->whereRaw("pengeluarantruckingheader.pengeluarantrucking_id=8");
        if ($karyawan_id != 0) {
            $query->whereRaw("pengeluarantruckingdetail.karyawan_id = $karyawan_id");
        }
        $query->whereRaw("pengeluarantruckingdetail.nobukti = $tempPribadi.nobukti")
            ->where(function ($query) use ($tempPribadi) {
                $query->whereRaw("$tempPribadi.sisa != 0")
                    ->orWhereRaw("$tempPribadi.sisa is null");
            })
            ->where("pengeluarantruckingheader.tglbukti", '<=', $tglbukti)
            ->orderBy('pengeluarantruckingheader.tglbukti', 'asc')
            ->orderBy('pengeluarantruckingdetail.nobukti', 'asc');

        return $query->get();
    }

    public function getPengembalianTitipan(array $data)
    {
        $bbt = PengeluaranTrucking::from(DB::raw("pengeluarantrucking with (readuncommitted)"))->where('kodepengeluaran', 'BBT')->first();
        $periodedari = date('Y-m-d', strtotime($data['periodedari']));
        $periodesampai = date('Y-m-d', strtotime($data['periodesampai']));

        $pengeluaranTruckingDetail = PengeluaranTruckingDetail::from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
            ->select(
                'pengeluarantruckingheader.id',
                'pengeluarantruckingdetail.nobukti as nobukti_titipan',
                'pengeluarantruckingheader.tglbukti as tglbukti_titipan',
                DB::raw("SUM(pengeluarantruckingdetail.nominaltagih) as nominal_titipan"),
                DB::raw("max(jenisorder.keterangan) as jenisorder_id"),
                DB::raw("max(pengeluarantruckingdetail.keterangan) as keterangan_titipan"),
                // 'pengeluarantruckingdetail.suratpengantar_nobukti',
            )
            ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingheader.id', 'pengeluarantruckingdetail.pengeluarantruckingheader_id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'jenisorder.id', 'pengeluarantruckingheader.jenisorder_id');

        if ($data['id'] != null) {
            $pengeluaranTruckingDetail->leftJoin(DB::raw("penerimaantruckingdetail with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'penerimaantruckingdetail.pengeluarantruckingheader_nobukti')
                ->where('penerimaantruckingdetail.penerimaantruckingheader_id', $data['id']);
        } else {
            $pengeluaranTruckingDetail->whereRaw("pengeluarantruckingheader.nobukti not in (select pengeluarantruckingheader_nobukti from penerimaantruckingdetail)");
        }

        $pengeluaranTruckingDetail->where('pengeluarantruckingheader.pengeluarantrucking_id', $bbt->id)
            ->where('jenisorder.id', $data['jenisorderan_id'])
            ->whereBetween('pengeluarantruckingheader.tglbukti', [$periodedari, $periodesampai])
            ->groupBy('pengeluarantruckingheader.id', 'pengeluarantruckingdetail.nobukti', 'pengeluarantruckingheader.tglbukti');
        $data = $pengeluaranTruckingDetail->get();

        return $data;
    }
    public function getPengembalianTitipanReload(array $data)
    {
        $bbt = PengeluaranTrucking::from(DB::raw("pengeluarantrucking with (readuncommitted)"))->where('kodepengeluaran', 'BBT')->first();
        $periodedari = date('Y-m-d', strtotime($data['periodedari']));
        $periodesampai = date('Y-m-d', strtotime($data['periodesampai']));

        $fetch = PengeluaranTruckingDetail::from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
            ->select(
                'pengeluarantruckingheader.id',
                'pengeluarantruckingdetail.nobukti as nobukti_titipan',
                'pengeluarantruckingheader.tglbukti as tglbukti_titipan',
                DB::raw("SUM(pengeluarantruckingdetail.nominaltagih) as nominal_titipan"),
                DB::raw("max(jenisorder.keterangan) as jenisorder_id"),
                DB::raw("max(pengeluarantruckingdetail.keterangan) as keterangan_titipan"),
                // 'pengeluarantruckingdetail.suratpengantar_nobukti',
            )
            ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingheader.id', 'pengeluarantruckingdetail.pengeluarantruckingheader_id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'jenisorder.id', 'pengeluarantruckingheader.jenisorder_id')
            ->leftJoin(DB::raw("penerimaantruckingdetail with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'penerimaantruckingdetail.pengeluarantruckingheader_nobukti')
            ->where('penerimaantruckingdetail.penerimaantruckingheader_id', $data['id'])
            ->where('pengeluarantruckingheader.pengeluarantrucking_id', $bbt->id)
            ->where('jenisorder.id', $data['jenisorderan_id'])
            ->whereBetween('pengeluarantruckingheader.tglbukti', [$periodedari, $periodesampai])
            ->groupBy('pengeluarantruckingheader.id', 'pengeluarantruckingdetail.nobukti', 'pengeluarantruckingheader.tglbukti');

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti_titipan')->nullable();
            $table->date('tglbukti_titipan')->nullable();
            $table->float('nominal_titipan')->nullable();
            $table->string('jenisorder_id')->nullable();
            $table->string('keterangan_titipan')->nullable();
        });

        DB::table($temp)->insertUsing(['id', 'nobukti_titipan', 'tglbukti_titipan', 'nominal_titipan', 'jenisorder_id', 'keterangan_titipan'], $fetch);


        $fetch = PengeluaranTruckingDetail::from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
            ->select(
                'pengeluarantruckingheader.id',
                'pengeluarantruckingdetail.nobukti as nobukti_titipan',
                'pengeluarantruckingheader.tglbukti as tglbukti_titipan',
                DB::raw("SUM(pengeluarantruckingdetail.nominaltagih) as nominal_titipan"),
                DB::raw("max(jenisorder.keterangan) as jenisorder_id"),
                DB::raw("max(pengeluarantruckingdetail.keterangan) as keterangan_titipan"),
                // 'pengeluarantruckingdetail.suratpengantar_nobukti',
            )
            ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingheader.id', 'pengeluarantruckingdetail.pengeluarantruckingheader_id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'jenisorder.id', 'pengeluarantruckingheader.jenisorder_id')
            ->whereRaw("pengeluarantruckingheader.nobukti not in (select pengeluarantruckingheader_nobukti from penerimaantruckingdetail)")
            ->where('pengeluarantruckingheader.pengeluarantrucking_id', $bbt->id)
            ->where('jenisorder.id', $data['jenisorderan_id'])
            ->whereBetween('pengeluarantruckingheader.tglbukti', [$periodedari, $periodesampai])
            ->groupBy('pengeluarantruckingheader.id', 'pengeluarantruckingdetail.nobukti', 'pengeluarantruckingheader.tglbukti');

        DB::table($temp)->insertUsing(['id', 'nobukti_titipan', 'tglbukti_titipan', 'nominal_titipan', 'jenisorder_id', 'keterangan_titipan'], $fetch);

        $pengeluaranTruckingDetail = DB::table($temp)->from(DB::raw("$temp with (readuncommitted)"));

        $data = $pengeluaranTruckingDetail->get();

        return $data;
    }

    public function getPengembalianTitipanShow($id)
    {
        $penerimaanTruckingDetail = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
            ->select(
                'penerimaantruckingdetail.id',
                'penerimaantruckingdetail.pengeluarantruckingheader_nobukti as nobukti',
                'penerimaantruckingdetail.nominal',
                'jenisorder.keterangan as jenisorder_id',
                'penerimaantruckingdetail.keterangan',
                'pengeluarantruckingheader.tglbukti as tglbukti',
            )
            ->where('penerimaantruckingheader.id', $id)
            // dd($penerimaantruckingdetail->get());
            ->leftJoin(DB::raw("penerimaantruckingdetail with (readuncommitted)"), 'penerimaantruckingdetail.penerimaantruckingheader_id', 'penerimaantruckingheader.id')
            ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'penerimaantruckingdetail.pengeluarantruckingheader_nobukti')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'jenisorder.id', 'penerimaantruckingheader.jenisorder_id');
        return $penerimaanTruckingDetail->get();
    }

    public function createTempPinjPribadi($supir_id)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("pengeluarantruckingdetail.nobukti,  SUM(pengeluarantruckingdetail.nominal) AS jlhpinjaman,
            (SELECT isnull(SUM(penerimaantruckingdetail.nominal),0) FROM penerimaantruckingdetail
            WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS totalbayar ,
            (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"));
        // ->leftJoin(DB::raw("penerimaantruckingdetail with (readuncommitted)"), 'penerimaantruckingdetail.pengeluarantruckingheader_nobukti', 'pengeluarantruckingdetail.nobukti')
        if ($supir_id != 0) {
            $fetch->whereRaw("pengeluarantruckingdetail.supir_id = $supir_id");
        }
        $fetch->where("pengeluarantruckingdetail.nobukti",  'LIKE', "%PJT%")
            ->groupBy('pengeluarantruckingdetail.nobukti', 'pengeluarantruckingdetail.nominal');

        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->bigInteger('jlhpinjaman')->nullable();
            $table->bigInteger('totalbayar')->nullable();
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'jlhpinjaman', 'totalbayar', 'sisa'], $fetch);


        return $temp;
    }
    public function createTempPinjPribadiKaryawan($karyawan_id)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("pengeluarantruckingdetail.nobukti, (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"));
        // ->leftJoin(DB::raw("penerimaantruckingdetail with (readuncommitted)"), 'penerimaantruckingdetail.pengeluarantruckingheader_nobukti', 'pengeluarantruckingdetail.nobukti')
        if ($karyawan_id != 0) {
            $fetch->whereRaw("pengeluarantruckingdetail.karyawan_id = $karyawan_id");
        }
        $fetch->where("pengeluarantruckingdetail.nobukti",  'LIKE', "%PJK%")
            ->groupBy('pengeluarantruckingdetail.nobukti', 'pengeluarantruckingdetail.nominal');

        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'sisa'], $fetch);


        return $temp;
    }

    public function getPengembalianPinjaman($id, $supir_id)
    {
        // return $supir_id;
        $tempPribadi = $this->createTempPengembalianPinjaman($id, $supir_id);
        $tempAll = $this->createTempPinjaman($id, $supir_id);

        $temp = '##tempGet' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $pengembalian = DB::table($tempPribadi)->from(DB::raw("$tempPribadi with (readuncommitted)"))
            ->select(DB::raw("penerimaantrucking_id,nobukti,keterangan,jlhpinjaman,totalbayar,sisa,bayar,pinj_supirid,pinj_supir"));

        Schema::create($temp, function ($table) {
            $table->bigInteger('penerimaantrucking_id')->nullable();
            $table->string('nobukti');
            $table->longText('keterangan')->nullable();
            $table->bigInteger('jlhpinjaman')->nullable();
            $table->bigInteger('totalbayar')->nullable();
            $table->bigInteger('sisa')->nullable();
            $table->bigInteger('bayar')->nullable();
            $table->bigInteger('pinj_supirid')->nullable();
            $table->string('pinj_supir')->nullable();
        });
        DB::table($temp)->insertUsing(['penerimaantrucking_id', 'nobukti', 'keterangan', 'jlhpinjaman', 'totalbayar', 'sisa', 'bayar', 'pinj_supirid', 'pinj_supir'], $pengembalian);

        $pinjaman = DB::table($tempAll)->from(DB::raw("$tempAll with (readuncommitted)"))
            ->select(DB::raw("null as penerimaantrucking_id,nobukti,keterangan,jlhpinjaman,totalbayar,sisa, 0 as bayar,pinj_supirid,pinj_supir"))
            ->where('sisa', '!=', '0');

        DB::table($temp)->insertUsing(['penerimaantrucking_id', 'nobukti', 'keterangan', 'jlhpinjaman', 'totalbayar', 'sisa', 'bayar', 'pinj_supirid', 'pinj_supir'], $pinjaman);

        $data = DB::table($temp)->from(DB::raw("$temp with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $temp.nobukti) as id,penerimaantrucking_id,nobukti,keterangan,jlhpinjaman,totalbayar,sisa,bayar as nominal,pinj_supirid,pinj_supir"))
            ->get();

        return $data;
    }
    public function getPengembalianPinjamanKaryawan($id, $karyawan_id)
    {
        // return $karyawan_id;
        $tempPribadi = $this->createTempPengembalianPinjamanKaryawan($id, $karyawan_id);
        $tempAll = $this->createTempPinjamanKaryawan($id, $karyawan_id);

        $temp = '##tempGet' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $pengembalian = DB::table($tempPribadi)->from(DB::raw("$tempPribadi with (readuncommitted)"))
            ->select(DB::raw("penerimaantrucking_id,nobukti,keterangan,sisa,bayar,pinj_karyawanid,pinj_karyawan"));

        Schema::create($temp, function ($table) {
            $table->bigInteger('penerimaantrucking_id')->nullable();
            $table->string('nobukti');
            $table->longText('keterangan')->nullable();
            $table->bigInteger('sisa')->nullable();
            $table->bigInteger('bayar')->nullable();
            $table->bigInteger('pinj_karyawanid')->nullable();
            $table->string('pinj_karyawan')->nullable();
        });
        DB::table($temp)->insertUsing(['penerimaantrucking_id', 'nobukti', 'keterangan', 'sisa', 'bayar', 'pinj_karyawanid', 'pinj_karyawan'], $pengembalian);

        $pinjaman = DB::table($tempAll)->from(DB::raw("$tempAll with (readuncommitted)"))
            ->select(DB::raw("null as penerimaantrucking_id,nobukti,keterangan,sisa, 0 as bayar,pinj_karyawanid,pinj_karyawan"))
            ->where('sisa', '!=', '0');

        DB::table($temp)->insertUsing(['penerimaantrucking_id', 'nobukti', 'keterangan', 'sisa', 'bayar', 'pinj_karyawanid', 'pinj_karyawan'], $pinjaman);

        $data = DB::table($temp)->from(DB::raw("$temp with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $temp.nobukti) as id,penerimaantrucking_id,nobukti,keterangan,sisa,bayar as nominal, pinj_karyawanid,pinj_karyawan"))
            ->get();

        return $data;
    }

    public function createTempPinjaman($id, $supir_id)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetch = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("pengeluarantruckingdetail.nobukti,pengeluarantruckingdetail.keterangan,
            pengeluarantruckingdetail.nominal AS jlhpinjaman,
            (SELECT isnull(SUM(penerimaantruckingdetail.nominal),0) FROM penerimaantruckingdetail
             WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS totalbayar,
            (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) 
            FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa, pengeluarantruckingdetail.supir_id as pinj_supirid, supir.namasupir as pinj_supir"))
            ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'pengeluarantruckingdetail.nobukti')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingdetail.supir_id', "supir.id");
        if ($supir_id != 0) {
            $fetch->whereRaw("pengeluarantruckingdetail.supir_id = $supir_id");
        }
        $fetch->where("pengeluarantruckingheader.pengeluarantrucking_id",  1)
            ->whereRaw("pengeluarantruckingheader.nobukti not in (select pengeluarantruckingheader_nobukti from penerimaantruckingdetail where penerimaantruckingheader_id=$id)")
            ->orderBy('pengeluarantruckingheader.tglbukti', 'asc')
            ->orderBy('pengeluarantruckingdetail.nobukti', 'asc');
        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->longText('keterangan');
            $table->bigInteger('jlhpinjaman')->nullable();
            $table->bigInteger('totalbayar')->nullable();
            $table->bigInteger('sisa')->nullable();
            $table->bigInteger('pinj_supirid')->nullable();
            $table->string('pinj_supir')->nullable();
        });
        // return $fetch->get();
        $tes = DB::table($temp)->insertUsing(['nobukti', 'keterangan', 'jlhpinjaman', 'totalbayar', 'sisa', 'pinj_supirid', 'pinj_supir'], $fetch);
        return $temp;
    }
    public function createTempPinjamanKaryawan($id, $karyawan_id)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetch = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("pengeluarantruckingdetail.nobukti,pengeluarantruckingdetail.keterangan,
            (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) 
            FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa, pengeluarantruckingdetail.karyawan_id as pinj_karyawanid, karyawan.namakaryawan as pinj_karyawan"))
            ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'pengeluarantruckingdetail.nobukti')
            ->leftJoin(DB::raw("karyawan with (readuncommitted)"), 'pengeluarantruckingdetail.karyawan_id', "karyawan.id");

        if ($karyawan_id != 0) {
            $fetch->whereRaw("pengeluarantruckingdetail.karyawan_id = $karyawan_id");
        }
        $fetch->where("pengeluarantruckingheader.pengeluarantrucking_id", "8")
            ->whereRaw("pengeluarantruckingheader.nobukti not in (select pengeluarantruckingheader_nobukti from penerimaantruckingdetail where penerimaantruckingheader_id=$id)")
            ->orderBy('pengeluarantruckingheader.tglbukti', 'asc')
            ->orderBy('pengeluarantruckingdetail.nobukti', 'asc');
        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->longText('keterangan');
            $table->bigInteger('sisa')->nullable();
            $table->bigInteger('pinj_karyawanid')->nullable();
            $table->string('pinj_karyawan')->nullable();
        });
        // return $fetch->get();
        $tes = DB::table($temp)->insertUsing(['nobukti', 'keterangan', 'sisa', 'pinj_karyawanid', 'pinj_karyawan'], $fetch);
        return $temp;
    }

    public function createTempPengembalianPinjaman($id, $supir_id)
    {
        $temp = '##tempPengembalian' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("penerimaantruckingdetail.penerimaantruckingheader_id,pengeluarantruckingdetail.nobukti,pengeluarantruckingdetail.keterangan, penerimaantruckingdetail.nominal as bayar ,
                (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) 
                FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa,
                (SELECT isnull(SUM(penerimaantruckingdetail.nominal),0) FROM penerimaantruckingdetail
				WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti
				and penerimaantruckingdetail.[penerimaantruckingheader_id] != $id) AS totalbayar,
				 pengeluarantruckingdetail.nominal AS jlhpinjaman, pengeluarantruckingdetail.supir_id as pinj_supirid, supir.namasupir as pinj_supir"))
            ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'pengeluarantruckingdetail.nobukti')
            ->leftJoin(DB::raw("penerimaantruckingdetail with (readuncommitted)"), 'penerimaantruckingdetail.pengeluarantruckingheader_nobukti', 'pengeluarantruckingdetail.nobukti')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingdetail.supir_id', "supir.id");
        if ($supir_id != 0) {
            $fetch->whereRaw("pengeluarantruckingdetail.supir_id = $supir_id");
        }
        $fetch->where("pengeluarantruckingheader.pengeluarantrucking_id",  1)
            ->where("penerimaantruckingdetail.penerimaantruckingheader_id", $id)
            ->orderBy('pengeluarantruckingheader.tglbukti', 'asc')
            ->orderBy('pengeluarantruckingdetail.nobukti', 'asc');

        Schema::create($temp, function ($table) {
            $table->bigInteger('penerimaantrucking_id')->nullable();
            $table->string('nobukti');
            $table->longText('keterangan');
            $table->bigInteger('bayar')->nullable();
            $table->bigInteger('sisa')->nullable();
            $table->bigInteger('totalbayar')->nullable();
            $table->bigInteger('jlhpinjaman')->nullable();
            $table->bigInteger('pinj_supirid')->nullable();
            $table->string('pinj_supir')->nullable();
        });
        // return $fetch->get();
        $tes = DB::table($temp)->insertUsing(['penerimaantrucking_id', 'nobukti', 'keterangan', 'bayar', 'sisa', 'totalbayar', 'jlhpinjaman', 'pinj_supirid', 'pinj_supir'], $fetch);


        return $temp;
    }

    public function createTempPengembalianPinjamanKaryawan($id, $karyawan_id)
    {
        $temp = '##tempPengembalian' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("penerimaantruckingdetail.penerimaantruckingheader_id,pengeluarantruckingdetail.nobukti,pengeluarantruckingdetail.keterangan, penerimaantruckingdetail.nominal as bayar ,
                (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) 
                FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa,pengeluarantruckingdetail.karyawan_id as pinj_karyawanid, karyawan.namakaryawan as pinj_karyawan "))
            ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'pengeluarantruckingdetail.nobukti')
            ->leftJoin(DB::raw("penerimaantruckingdetail with (readuncommitted)"), 'penerimaantruckingdetail.pengeluarantruckingheader_nobukti', 'pengeluarantruckingdetail.nobukti')
            ->leftJoin(DB::raw("karyawan with (readuncommitted)"), 'pengeluarantruckingdetail.karyawan_id', "karyawan.id")
            ->where("pengeluarantruckingheader.pengeluarantrucking_id", "8");

        if ($karyawan_id != 0) {
            $fetch->whereRaw("pengeluarantruckingdetail.karyawan_id = $karyawan_id");
        }
        $fetch->where("penerimaantruckingdetail.penerimaantruckingheader_id", $id)
            ->orderBy('pengeluarantruckingheader.tglbukti', 'asc')
            ->orderBy('pengeluarantruckingdetail.nobukti', 'asc');
        Schema::create($temp, function ($table) {
            $table->bigInteger('penerimaantrucking_id')->nullable();
            $table->string('nobukti');
            $table->longText('keterangan');
            $table->bigInteger('bayar')->nullable();
            $table->bigInteger('sisa')->nullable();
            $table->bigInteger('pinj_karyawanid')->nullable();
            $table->string('pinj_karyawan')->nullable();
        });
        // return $fetch->get();
        $tes = DB::table($temp)->insertUsing(['penerimaantrucking_id', 'nobukti', 'keterangan', 'bayar', 'sisa', 'pinj_karyawanid', 'pinj_karyawan'], $fetch);


        return $temp;
    }

    public function getDeletePengembalianPinjaman($id, $supir_id)
    {
        // return $supir_id;
        $tempPribadi = $this->createTempPengembalianPinjaman($id, $supir_id);

        $data = DB::table($tempPribadi)->from(DB::raw("$tempPribadi with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $tempPribadi.nobukti) as id,penerimaantrucking_id,nobukti,keterangan,jlhpinjaman,totalbayar,sisa,bayar as nominal,pinj_supirid, pinj_supir"))
            ->get();

        return $data;
    }
    public function getDeletePengembalianPinjamanKaryawan($id, $karyawan_id)
    {
        // return $supir_id;
        $tempPribadi = $this->createTempPengembalianPinjamanKaryawan($id, $karyawan_id);

        $data = DB::table($tempPribadi)->from(DB::raw("$tempPribadi with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $tempPribadi.nobukti) as id,penerimaantrucking_id,nobukti,keterangan,sisa,bayar as nominal,pinj_karyawanid, pinj_karyawan"))
            ->get();

        return $data;
    }

    public function sort($query)
    {
        // if ($this->params['sortIndex'] == 'penerimaantrucking_id') {
        //     return $query->orderBy('penerimaantrucking.keterangan', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'bank_id') {
        //     return $query->orderBy('bank.namabank', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'coa') {
        //     return $query->orderBy('akunpusat.keterangancoa', $this->params['sortOrder']);
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
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglbukacetak') {
                                $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'url_pelunasan') {
                                $query = $query->where('a.nobukti_pelunasan', 'LIKE', "%$filters[data]%");
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
                                    $query = $query->orWhere('a.statuscetaktext', '=', "$filters[data]");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglbukacetak') {
                                    $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'url_pelunasan') {
                                    $query = $query->orWhere('a.nobukti_pelunasan', 'LIKE', "%$filters[data]%");
                                } else {
                                    // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
            $query->where('penerimaantruckingheader.statuscetak', '<>', request()->cetak)
                ->whereYear('penerimaantruckingheader.tglbukti', '=', request()->year)
                ->whereMonth('penerimaantruckingheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }


    public function processStore(array $data): PenerimaanTruckingHeader
    {
        $idpenerimaan = $data['penerimaantrucking_id'];
        $fetchFormat =  DB::table('penerimaantrucking')->where('id', $idpenerimaan)->first();

        $tanpaprosesnobukti = array_key_exists("tanpaprosesnobukti", $data) ? $data['tanpaprosesnobukti'] : 0;
        $from = array_key_exists("from", $data) ? $data['from'] : '';
        if ($fetchFormat->kodepenerimaan == 'PJP') {
            $data['coa'] = $fetchFormat->coapostingkredit;
        } else if ($fetchFormat->kodepenerimaan == 'PJPK') {
            $data['coa'] = $fetchFormat->coapostingkredit;
        } else if ($fetchFormat->kodepenerimaan == 'BBM') {
            $data['coa'] = $fetchFormat->coakredit;
        } else if ($fetchFormat->kodepenerimaan == 'DPO') {
            $data['coa'] = $fetchFormat->coapostingkredit;
        } else if ($fetchFormat->kodepenerimaan == 'DPOK') {
            $data['coa'] = $fetchFormat->coapostingkredit;
        } else if ($fetchFormat->kodepenerimaan == 'PBT') {
            $data['coa'] = $fetchFormat->coapostingkredit;
        } else if ($fetchFormat->kodepenerimaan == 'ATS') {
            $data['coa'] = $fetchFormat->coapostingkredit;
        }

        $statusformat = $fetchFormat->format;
        $fetchGrp = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('id', $statusformat)->first();
        $format = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', $fetchGrp->grp)->where('subgrp', $fetchGrp->subgrp)->first();

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        $coadebet = '';
        if ($tanpaprosesnobukti != 2) {
            // throw new \Exception($data['bank_id']);
            $bank = $data['bank_id'];
            $querySubgrpPenerimaan = Bank::from(DB::raw("bank with (readuncommitted)"))
                ->select('parameter.grp', 'parameter.subgrp', 'bank.formatpenerimaan', 'bank.coa', 'bank.tipe')
                ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')
                ->where('bank.id', $data['bank_id'])
                ->first();
            $coadebet = $querySubgrpPenerimaan->coa;
        }


        $nobuktipengeluarantrucking = $data['pengeluarantruckingheader_nobukti'][0] ?? '';
        if ($fetchFormat->kodepenerimaan == 'PJP' || $fetchFormat->kodepenerimaan == 'PJPK') {
            $queryposting = db::table('pengeluarantruckingheader')->from(db::raw("pengeluarantruckingheader a with (readuncommitted)"))
                ->select('statusposting', 'coa')->where('nobukti', $nobuktipengeluarantrucking)->first();
            if (isset($queryposting)) {
                $bukanposting = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                    ->select('id')
                    ->where('grp', 'STATUS POSTING')
                    ->where('subgrp', 'STATUS POSTING')
                    ->where('id', '84')
                    ->first()->id ?? 0;
                if ($bukanposting == $queryposting->statusposting) {
                    $coa =  $queryposting->coa ?? $data['coa'];
                } else {
                    $coa = $data['coa'];
                }
            } else {
                $coa = $data['coa'];
            }
        } else {
            $coa = $data['coa'];
        }

        $penerimaanTruckingHeader = new PenerimaanTruckingHeader();

        $penerimaanTruckingHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $penerimaanTruckingHeader->penerimaantrucking_id = $data['penerimaantrucking_id'] ?? $idpenerimaan;
        $penerimaanTruckingHeader->bank_id = $data['bank_id'];
        $penerimaanTruckingHeader->coa = $coa ?? '';
        $penerimaanTruckingHeader->keterangan = $data['keteranganheader'] ?? '';
        $penerimaanTruckingHeader->supir_id = $data['supirheader_id'] ?? '';
        $penerimaanTruckingHeader->karyawan_id = $data['karyawanheader_id'] ?? '';
        $penerimaanTruckingHeader->penerimaan_nobukti = $data['penerimaan_nobukti'] ?? '';
        $penerimaanTruckingHeader->pendapatansupir_bukti = $data['pendapatansupir_bukti'] ?? '';
        $penerimaanTruckingHeader->jenisorder_id = $data['jenisorderan_id'] ?? '';
        $penerimaanTruckingHeader->periodedari = array_key_exists("periodedari", $data) ? date('Y-m-d', strtotime($data['periodedari'])) : '';
        $penerimaanTruckingHeader->periodesampai = array_key_exists("periodesampai", $data) ? date('Y-m-d', strtotime($data['periodesampai'])) : '';
        $penerimaanTruckingHeader->statusformat = $data['statusformat'] ?? $format->id;
        $penerimaanTruckingHeader->statuscetak = $statusCetak->id;
        $penerimaanTruckingHeader->modifiedby = auth('api')->user()->name;
        $penerimaanTruckingHeader->info = html_entity_decode(request()->info);
        $penerimaanTruckingHeader->nobukti = (new RunningNumberService)->get($fetchGrp->grp, $fetchGrp->subgrp, $penerimaanTruckingHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$penerimaanTruckingHeader->save()) {
            throw new \Exception("Error storing Penerimaan Trucking header.");
        }

        $penerimaanTruckingHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerimaanTruckingHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY penerimaan Trucking Header '),
            'idtrans' => $penerimaanTruckingHeader->id,
            'nobuktitrans' => $penerimaanTruckingHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $penerimaanTruckingHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);


        $cabang = (new Parameter())->cekText('CABANG', 'CABANG') ?? 0;
        $penerimaanTruckingDetails = [];
        $totalNominal = 0;
        $firstBuktiPosting = '';
        $firstBuktiNonPosting = '';
        $nominalPostingNon = ['nonposting' => 0, 'posting' => 0];
        $coakreditPostingNon = ['nonposting' => '', 'posting' => ''];
        $keteranganPostingNon = ['nonposting' => '', 'posting' => ''];
        $nobuktiPosting = '';
        $nobuktiNonPosting = '';
        $namasupirdata = '';
        $hit = 0;
        for ($i = 0; $i < count($data['nominal']); $i++) {
            $hit = $hit + 1;
            $supir_id = $data['supir_id'][$i] ?? 0;
            $querysupir = db::table('supir')->from(db::raw("supir a with (readuncommitted)"))
                ->select('a.namasupir')
                ->where('a.id', $supir_id)
                ->first();
            if (isset($querysupir)) {
                if ($hit = 1) {
                    $namasupirdata = $namasupirdata . $querysupir->namasupir ?? '';
                } else {
                    $namasupirdata = $namasupirdata . ',' . $querysupir->namasupir ?? '';
                }
            }
        }


        for ($i = 0; $i < count($data['nominal']); $i++) {
            $penerimaanTruckingDetail = (new PenerimaanTruckingDetail())->processStore($penerimaanTruckingHeader, [
                'penerimaantruckingheader_id' => $penerimaanTruckingHeader->id,
                'nobukti' => $penerimaanTruckingHeader->nobukti,
                'supir_id' =>   $data['supir_id'][$i] ?? '',
                'karyawan_id' =>   $data['karyawan_id'][$i] ?? '',
                'pengeluarantruckingheader_nobukti' => $data['pengeluarantruckingheader_nobukti'][$i] ?? '',
                'keterangan' =>  mb_convert_encoding($data['keterangan'][$i], 'ISO-8859-1', 'UTF-8'),
                'nominal' =>  $data['nominal'][$i],
                'modifiedby' => $penerimaanTruckingHeader->modifiedby,
            ]);
            $nobuktipengeluarantrucking = $data['pengeluarantruckingheader_nobukti'][$i] ?? '';
            $keteranganpengeluarantrucking = $data['keterangan'][$i] ?? '';
            $penerimaanTruckingDetails[] = $penerimaanTruckingDetail->toArray();
            if ($fetchFormat->kodepenerimaan == 'PJP' || $fetchFormat->kodepenerimaan == 'PJPK') {
                $queryposting = db::table('pengeluarantruckingheader')->from(db::raw("pengeluarantruckingheader a with (readuncommitted)"))
                    ->select('statusposting', 'coa')->where('nobukti', $nobuktipengeluarantrucking)->first();
                if (isset($queryposting)) {
                    if ($from != 'pemutihan') {

                        $bukanposting = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                            ->select('id')
                            ->where('grp', 'STATUS POSTING')
                            ->where('subgrp', 'STATUS POSTING')
                            ->where('id', '84')
                            ->first()->id ?? 0;
                        if ($bukanposting == $queryposting->statusposting) {
                            if ($firstBuktiNonPosting == '') {
                                if ($cabang == 'MEDAN') {
                                    $nobuktiNonPosting = $nobuktiNonPosting . $nobuktipengeluarantrucking . " ($keteranganpengeluarantrucking)";
                                } else {
                                    $nobuktiNonPosting = $nobuktiNonPosting . $nobuktipengeluarantrucking;
                                }
                                $coakreditPostingNon['nonposting'] =  $queryposting->coa ?? $data['coa'];
                                $coadebet_detail[] = $coadebet;
                                $tgljatuhtempo[] = date('Y-m-d', strtotime($data['tglbukti']));
                                $firstBuktiNonPosting = $nobuktipengeluarantrucking;
                            } else {
                                if ($cabang == 'MEDAN') {
                                    $nobuktiNonPosting = $nobuktiNonPosting . ', ' . $nobuktipengeluarantrucking . " ($keteranganpengeluarantrucking)";
                                } else {
                                    $nobuktiNonPosting = $nobuktiNonPosting . ', ' . $nobuktipengeluarantrucking;
                                }
                            }
                            if ($tanpaprosesnobukti == 3) {
                                if ($fetchFormat->kodepenerimaan == 'PJP') {
                                    $keteranganPostingNon['nonposting'] =  '(NON POSTING) PENGEMBALIAN PINJAMAN DARI PENDAPATAN SUPIR ' . $data['pendapatansupir_bukti'] . ' ' . $data['tglbukti'] . ' ' . $nobuktiNonPosting;
                                }
                            } else {
                                $keteranganPostingNon['nonposting'] =  '(NON POSTING) PENGEMBALIAN PINJAMAN ' . $nobuktiNonPosting;
                            }
                            $nominalPostingNon['nonposting'] += $data['nominal'][$i];
                        } else {
                            if ($firstBuktiPosting == '') {
                                if ($cabang == 'MEDAN') {
                                    $nobuktiPosting = $nobuktiPosting . $nobuktipengeluarantrucking . " ($keteranganpengeluarantrucking)";
                                } else {
                                    $nobuktiPosting = $nobuktiPosting . $nobuktipengeluarantrucking;
                                }
                                $coakreditPostingNon['posting'] = $data['coa'];
                                $coadebet_detail[] = $coadebet;
                                $tgljatuhtempo[] = date('Y-m-d', strtotime($data['tglbukti']));
                                $firstBuktiPosting = $nobuktipengeluarantrucking;
                            } else {
                                if ($cabang == 'MEDAN') {
                                    $nobuktiPosting = $nobuktiPosting . ', ' . $nobuktipengeluarantrucking . " ($keteranganpengeluarantrucking)";
                                } else {
                                    $nobuktiPosting = $nobuktiPosting . ', ' . $nobuktipengeluarantrucking;
                                }
                            }
                            if ($tanpaprosesnobukti == 3) {
                                if ($fetchFormat->kodepenerimaan == 'PJP') {
                                    $keteranganPostingNon['posting'] =  '(POSTING) PENGEMBALIAN PINJAMAN DARI PENDAPATAN SUPIR' . $data['pendapatansupir_bukti'] . ' ' . $data['tglbukti'] . ' ' . $nobuktiPosting;
                                }
                            } else {
                                $keteranganPostingNon['posting'] =  '(POSTING) PENGEMBALIAN PINJAMAN ' . $nobuktiPosting;
                            }
                            $nominalPostingNon['posting'] += $data['nominal'][$i];
                        }
                    } else {

                        $tgljatuhtempo[] = date('Y-m-d', strtotime($data['tglbukti']));
                        $nominal_detail[] = $data['nominal'][$i];
                        $keterangan_detail[] = 'PENGEMBALIAN PINJAMAN DARI PEMUTIHAN SUPIR ' . $data['supirheader'] . ' ' . $nobuktipengeluarantrucking;
                        $coakredit_detail[] = $queryposting->coa;
                        $coadebet_detail[] = $coadebet;
                    }
                } else {
                    $coakredit_detail[] = $data['coa'];
                }
            } else {
                $coakredit_detail[] = $data['coa'];
            }

            if ($fetchFormat->kodepenerimaan != 'PJP' && $fetchFormat->kodepenerimaan != 'PJPK') {
                $coadebet_detail[] = $coadebet;
                $nominal_detail[] = $data['nominal'][$i];
                if ($fetchFormat->kodepenerimaan == 'DPO') {
                    $namasupir = DB::table("supir")->from(DB::raw("supir with (readuncommitted)"))->select('namasupir')->where('id', $data['supir_id'][$i])->first();
                    $keterangan_detail[] = "DEPOSITO $namasupir->namasupir " . $data['keterangan'][$i];
                } else {
                    $keterangan_detail[] = $data['keterangan'][$i];
                }
                $tgljatuhtempo[] = date('Y-m-d', strtotime($data['tglbukti']));
                $totalNominal = $totalNominal + $data['nominal'][$i];
            }
        }

        $diterimaDari = '';
        // convert nominal dan coa pjp
        if ($fetchFormat->kodepenerimaan == 'PJP' || $fetchFormat->kodepenerimaan == 'PJPK') {

            if ($from != 'pemutihan') {
                $nominalPostingNon = array_filter($nominalPostingNon, function ($value) {
                    return $value !== 0;
                });
                $coakreditPostingNon = array_filter($coakreditPostingNon, function ($value) {
                    return $value !== '';
                });
                $keteranganPostingNon = array_filter($keteranganPostingNon, function ($value) {
                    return $value !== '';
                });

                $nominalPostingNon = array_values($nominalPostingNon);
                $coakreditPostingNon = array_values($coakreditPostingNon);
                $keteranganPostingNon = array_values($keteranganPostingNon);
                $nominal_detail = $nominalPostingNon;
                $coakredit_detail = $coakreditPostingNon;
                $keterangan_detail = $keteranganPostingNon;
            }
            if ($fetchFormat->kodepenerimaan == 'PJP') {

                $getNamaSupir = DB::table("supir")->from(DB::raw("supir with (readuncommitted)"))->select('namasupir')->where('id', $data['supirheader_id'])->first();
                if ($getNamaSupir != '') {
                    $diterimaDari = $getNamaSupir->namasupir;
                } else {
                    $diterimaDari = 'SUPIR';
                }
            }
            if ($fetchFormat->kodepenerimaan == 'PJPK') {

                $getNamaKaryawan = DB::table("karyawan")->from(DB::raw("karyawan with (readuncommitted)"))->select('namakaryawan')->where('id', $data['karyawanheader_id'])->first();
                if ($getNamaKaryawan != '') {
                    $diterimaDari = $getNamaKaryawan->namakaryawan;
                } else {
                    $diterimaDari = 'KARYAWAN';
                }
            }
        }

        if ($fetchFormat->kodepenerimaan == 'PBT') {
            $coakredit_detail = [];
            $coadebet_detail = [];
            $nominal_detail = [];
            $tgljatuhtempo = [];
            $keterangan_detail = [];
            $coakredit_detail[] = $data['coa'];
            $coadebet_detail[] = $coadebet;
            $nominal_detail[] = $totalNominal;
            $tgljatuhtempo[] = date('Y-m-d', strtotime($data['tglbukti']));
            $keterangan_detail[] = "PENGEMBALIAN TITIPAN EMKL " . $penerimaanTruckingHeader->nobukti . ". " . $data['keteranganheader'];
            $diterimaDari = 'DIV. EMKL';
        }
        $penerimaanTruckingDetailLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerimaanTruckingHeaderLogTrail->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY penerimaan Trucking detail '),
            'idtrans' => $penerimaanTruckingHeaderLogTrail->id,
            'nobuktitrans' => $penerimaanTruckingHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $penerimaanTruckingDetails,
            'modifiedby' => auth('api')->user()->user
        ]);

        //if tanpaprosesnobukti NOT 2 STORE PENERIMAAN karena 
        // tanpaprosesnobukti = 2 dari gaji supir (tidak posting)
        if ($tanpaprosesnobukti != 2) {

            // tanpaprosesnobukti = 3 dari pendatapan supir jakarta
            if ($fetchFormat->kodepenerimaan == 'DPO') {

                $coakredit_detail = [];
                $coadebet_detail = [];
                $nominal_detail = [];
                $keterangan_detail = [];
                $tgljatuhtempo = [];
                $tgljatuhtempo[] = date('Y-m-d', strtotime($data['tglbukti']));
                $coakredit_detail[] = $data['coa'];
                $coadebet_detail[] = $coadebet;
                $nominal_detail[] = $totalNominal;

                if ($tanpaprosesnobukti == 3) {
                    $keterangan_detail[] = 'DEPOSITO DARI PENDAPATAN SUPIR ' . $data['pendapatansupir_bukti'] . ' ' . $data['tglbukti'];
                } else {
                    $keterangan_detail[] = 'DEPOSITO SUPIR';
                    // $keterangan_detail[] = 'DEPOSITO SUPIR A/N ' . $namasupirdata . ' ' . $data['keterangan'][0];
                }
            } else if ($fetchFormat->kodepenerimaan == 'BBM' || $fetchFormat->kodepenerimaan == 'DPOK') {
                $coakredit_detail = [];
                $coadebet_detail = [];
                $nominal_detail = [];
                $keterangan_detail = [];
                $tgljatuhtempo = [];
                $tgljatuhtempo[] = date('Y-m-d', strtotime($data['tglbukti']));
                $coakredit_detail[] = $data['coa'];
                $coadebet_detail[] = $coadebet;
                $nominal_detail[] = $totalNominal;
                if ($fetchFormat->kodepenerimaan == 'DPOK') {
                    $keterangan_detail[] = 'DEPOSITO KARYAWAN';
                } else {
                    $keterangan_detail[] = $data['keterangan'][0];
                }
            }
            /*STORE PENERIMAAN*/
            $penerimaanRequest = [
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'postingdari' => (array_key_exists("postingdari", $data)) ? $data['postingdari'] : "ENTRY PENERIMAAN TRUCKING",
                'statusapproval' => $statusApproval->id,
                'pelanggan_id' => 0,
                'agen_id' => 0,
                'diterimadari' => $diterimaDari,
                'tgllunas' => date('Y-m-d', strtotime($data['tglbukti'])),
                'statusformat' => $format->id,
                'bank_id' => $penerimaanTruckingHeader->bank_id,

                'nowarkat' => null,
                'tgljatuhtempo' => $tgljatuhtempo,
                'nominal_detail' => $nominal_detail,
                'coadebet' => $coadebet_detail,
                'coakredit' => $coakredit_detail,
                'keterangan_detail' => $keterangan_detail,
                'invoice_nobukti' => null,
                'bankpelanggan_id' => null,
                'pelunasanpiutang_nobukti' => null,
                'bulanbeban' => null,
            ];
            $penerimaanHeader = (new PenerimaanHeader())->processStore($penerimaanRequest);

            $penerimaanTruckingHeader->penerimaan_nobukti = $penerimaanHeader->nobukti;
            $penerimaanTruckingHeader->save();
        }

        return $penerimaanTruckingHeader;
    }

    public function processUpdate(PenerimaanTruckingHeader $penerimaanTruckingHeader, array $data): PenerimaanTruckingHeader
    {
        $isEBS = $data['ebs'] ?? false;
        $isKomisi = $data['komisi'] ?? false;


        if ($isEBS == false) {
            $idpenerimaan = $data['penerimaantrucking_id'];
            $fetchFormat =  DB::table('penerimaantrucking')->where('id', $idpenerimaan)->first();

            $tanpaprosesnobukti = array_key_exists("tanpaprosesnobukti", $data) ? $data['tanpaprosesnobukti'] : 0;
            $from = array_key_exists("from", $data) ? $data['from'] : '';
            if ($fetchFormat->kodepenerimaan == 'PJP') {
                $data['coa'] = $fetchFormat->coapostingkredit;
            } else if ($fetchFormat->kodepenerimaan == 'PJPK') {
                $data['coa'] = $fetchFormat->coapostingkredit;
            } else if ($fetchFormat->kodepenerimaan == 'BBM') {
                $data['coa'] = $fetchFormat->coakredit;
            } else if ($fetchFormat->kodepenerimaan == 'DPO') {
                $data['coa'] = $fetchFormat->coapostingkredit;
            } else if ($fetchFormat->kodepenerimaan == 'DPOK') {
                $data['coa'] = $fetchFormat->coapostingkredit;
            } else if ($fetchFormat->kodepenerimaan == 'PBT') {
                $data['coa'] = $fetchFormat->coapostingkredit;
            }





            $statusformat = $fetchFormat->format;
            $fetchGrp = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('id', $statusformat)->first();
            $format = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', $fetchGrp->grp)->where('subgrp', $fetchGrp->subgrp)->first();

            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
            $coadebet = '';

            $querycek = DB::table('penerimaantruckingheader')->from(
                DB::raw("penerimaantruckingheader a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.id', $penerimaanTruckingHeader->id)
                ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
                ->first();

            $nobuktiold = DB::table('penerimaantruckingheader')->from(
                DB::raw("penerimaantruckingheader a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.id', $penerimaanTruckingHeader->id)
                ->first();

            if (isset($querycek)) {
                $nobukti = $querycek->nobukti;
            } else {
                $nobukti = (new RunningNumberService)->get($fetchGrp->grp, $fetchGrp->subgrp, $penerimaanTruckingHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
            }



            if ($tanpaprosesnobukti != 2) {
                // throw new \Exception($data['bank_id']);
                $bank = $data['bank_id'];
                $querySubgrpPenerimaan = Bank::from(DB::raw("bank with (readuncommitted)"))
                    ->select('parameter.grp', 'parameter.subgrp', 'bank.formatpenerimaan', 'bank.coa', 'bank.tipe')
                    ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')
                    ->where('bank.id', $data['bank_id'])
                    ->first();
                $coadebet = $querySubgrpPenerimaan->coa;
            }
        }

        if ($isEBS == false) {
            if ($from == 'ric') {

                $querycek = DB::table('penerimaantruckingheader')->from(
                    DB::raw("penerimaantruckingheader a with (readuncommitted)")
                )
                    ->select(
                        'a.nobukti'
                    )
                    ->where('a.id', $penerimaanTruckingHeader->id)
                    ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
                    ->first();
                if (isset($querycek)) {
                    $nobukti = $querycek->nobukti;
                } else {
                    $nobukti = (new RunningNumberService)->get($fetchGrp->grp, $fetchGrp->subgrp, $penerimaanTruckingHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
                }

                $penerimaanTruckingHeader->nobukti = $nobukti;
                $penerimaanTruckingHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
            } else {
                $getTgl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'EDIT TANGGAL BUKTI')->where('subgrp', 'PENERIMAAN TRUCKING')->first();

                if (trim($getTgl->text) == 'YA') {
                    $querycek = DB::table('penerimaantruckingheader')->from(
                        DB::raw("penerimaantruckingheader a with (readuncommitted)")
                    )
                        ->select(
                            'a.nobukti'
                        )
                        ->where('a.id', $penerimaanTruckingHeader->id)
                        ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
                        ->first();
                    if (isset($querycek)) {
                        $nobukti = $querycek->nobukti;
                    } else {
                        $nobukti = (new RunningNumberService)->get($fetchGrp->grp, $fetchGrp->subgrp, $penerimaanTruckingHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
                    }

                    $penerimaanTruckingHeader->nobukti = $nobukti;
                    $penerimaanTruckingHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
                }
            }

            $nobuktipengeluarantrucking = $data['pengeluarantruckingheader_nobukti'][0] ?? '';
            if ($fetchFormat->kodepenerimaan == 'PJP' || $fetchFormat->kodepenerimaan == 'PJPK') {
                $queryposting = db::table('pengeluarantruckingheader')->from(db::raw("pengeluarantruckingheader a with (readuncommitted)"))
                    ->select('statusposting', 'coa')->where('nobukti', $nobuktipengeluarantrucking)->first();
                if (isset($queryposting)) {
                    $bukanposting = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                        ->select('id')
                        ->where('grp', 'STATUS POSTING')
                        ->where('subgrp', 'STATUS POSTING')
                        ->where('id', '84')
                        ->first()->id ?? 0;
                    if ($bukanposting == $queryposting->statusposting) {
                        $coa =  $queryposting->coa ?? $data['coa'];
                    } else {
                        $coa = $data['coa'];
                    }
                } else {
                    $coa = $data['coa'];
                }
            } else {
                $coa = $data['coa'];
            }

            $penerimaanTruckingHeader->bank_id = $data['bank_id'];
            $penerimaanTruckingHeader->coa = $coa ?? '';
            $penerimaanTruckingHeader->keterangan = $data['keteranganheader'] ?? '';
            $penerimaanTruckingHeader->supir_id = $data['supirheader_id'] ?? '';
            $penerimaanTruckingHeader->karyawan_id = $data['karyawanheader_id'] ?? '';
            $penerimaanTruckingHeader->periodedari = array_key_exists("periodedari", $data) ? date('Y-m-d', strtotime($data['periodedari'])) : '';
            $penerimaanTruckingHeader->periodesampai = array_key_exists("periodesampai", $data) ? date('Y-m-d', strtotime($data['periodesampai'])) : '';
            $penerimaanTruckingHeader->modifiedby = auth('api')->user()->name;
            $penerimaanTruckingHeader->info = html_entity_decode(request()->info);
            $penerimaanTruckingHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
            $penerimaanTruckingHeader->pendapatansupir_bukti = $data['pendapatansupir_bukti'] ?? '';
            $penerimaanTruckingHeader->jenisorder_id = $data['jenisorderan_id'] ?? '';
            $penerimaanTruckingHeader->periodedari = array_key_exists("periodedari", $data) ? date('Y-m-d', strtotime($data['periodedari'])) : '';
            $penerimaanTruckingHeader->periodesampai = array_key_exists("periodesampai", $data) ? date('Y-m-d', strtotime($data['periodesampai'])) : '';
        } else {
            $penerimaanTruckingHeader->bank_id = $data['bank_id'];
            $penerimaanTruckingHeader->penerimaan_nobukti = $data['penerimaan_nobukti'];
        }

        $penerimaanTruckingHeader->editing_by = '';
        $penerimaanTruckingHeader->editing_at = null;
        if (!$penerimaanTruckingHeader->save()) {
            throw new \Exception("Error storing Penerimaan Trucking header.");
        }



        if ($isEBS == false) {
            /*DELETE EXISTING PenerimaanTruckingDetail*/
            $penerimaanTruckingDetail = PenerimaanTruckingDetail::where('penerimaantruckingheader_id', $penerimaanTruckingHeader->id)->lockForUpdate()->delete();

            $cabang = (new Parameter())->cekText('CABANG', 'CABANG') ?? 0;
            $penerimaanTruckingDetails = [];
            $totalNominal = 0;
            $firstBuktiPosting = '';
            $firstBuktiNonPosting = '';
            $nominalPostingNon = ['nonposting' => 0, 'posting' => 0];
            $coakreditPostingNon = ['nonposting' => '', 'posting' => ''];
            $keteranganPostingNon = ['nonposting' => '', 'posting' => ''];
            $nobuktiPosting = '';
            $nobuktiNonPosting = '';

            $namasupirdata = '';
            $hit = 0;
            for ($i = 0; $i < count($data['nominal']); $i++) {
                $hit = $hit + 1;
                $supir_id = $data['supir_id'][$i] ?? 0;
                $querysupir = db::table('supir')->from(db::raw("supir a with (readuncommitted)"))
                    ->select('a.namasupir')
                    ->where('a.id', $supir_id)
                    ->first();
                if (isset($querysupir)) {
                    if ($hit == 1) {
                        $namasupirdata = $namasupirdata . $querysupir->namasupir ?? '';
                    } else {
                        $namasupirdata = $namasupirdata . ',' . $querysupir->namasupir ?? '';
                    }
                }
            }
            // dd($namasupir);


            for ($i = 0; $i < count($data['nominal']); $i++) {
                $penerimaanTruckingDetail = (new PenerimaanTruckingDetail())->processStore($penerimaanTruckingHeader, [
                    'penerimaantruckingheader_id' => $penerimaanTruckingHeader->id,
                    'nobukti' => $penerimaanTruckingHeader->nobukti,
                    'supir_id' =>   $data['supir_id'][$i] ?? '',
                    'karyawan_id' =>   $data['karyawan_id'][$i] ?? '',
                    'pengeluarantruckingheader_nobukti' => $data['pengeluarantruckingheader_nobukti'][$i] ?? '',
                    'keterangan' =>  $data['keterangan'][$i],
                    'nominal' =>  $data['nominal'][$i],
                    'modifiedby' => $penerimaanTruckingHeader->modifiedby,
                ]);
                $nobuktipengeluarantrucking = $data['pengeluarantruckingheader_nobukti'][$i] ?? '';
                $keteranganpengeluarantrucking = $data['keterangan'][$i] ?? '';
                $penerimaanTruckingDetails[] = $penerimaanTruckingDetail->toArray();
                if ($fetchFormat->kodepenerimaan == 'PJP' || $fetchFormat->kodepenerimaan == 'PJPK') {
                    $queryposting = db::table('pengeluarantruckingheader')->from(db::raw("pengeluarantruckingheader a with (readuncommitted)"))
                        ->select('statusposting', 'coa')->where('nobukti', $nobuktipengeluarantrucking)->first();
                    if (isset($queryposting)) {
                        if ($from != 'pemutihan') {
                            $bukanposting = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                                ->select('id')
                                ->where('grp', 'STATUS POSTING')
                                ->where('subgrp', 'STATUS POSTING')
                                ->where('id', '84')
                                ->first()->id ?? 0;
                            if ($bukanposting == $queryposting->statusposting) {
                                if ($firstBuktiNonPosting == '') {
                                    if ($cabang == 'MEDAN') {
                                        $nobuktiNonPosting = $nobuktiNonPosting . $nobuktipengeluarantrucking . " ($keteranganpengeluarantrucking)";
                                    } else {
                                        $nobuktiNonPosting = $nobuktiNonPosting . $nobuktipengeluarantrucking;
                                    }
                                    $coakreditPostingNon['nonposting'] =  $queryposting->coa ?? $data['coa'];
                                    $coadebet_detail[] = $coadebet;
                                    $tgljatuhtempo[] = date('Y-m-d', strtotime($data['tglbukti']));
                                    $firstBuktiNonPosting = $nobuktipengeluarantrucking;
                                } else {
                                    if ($cabang == 'MEDAN') {
                                        $nobuktiNonPosting = $nobuktiNonPosting . ', ' . $nobuktipengeluarantrucking . " ($keteranganpengeluarantrucking)";
                                    } else {
                                        $nobuktiNonPosting = $nobuktiNonPosting . ', ' . $nobuktipengeluarantrucking;
                                    }
                                }
                                if ($tanpaprosesnobukti == 3) {
                                    if ($fetchFormat->kodepenerimaan == 'PJP') {
                                        $keteranganPostingNon['nonposting'] =  '(NON POSTING) PENGEMBALIAN PINJAMAN DARI PENDAPATAN SUPIR ' . $data['pendapatansupir_bukti'] . ' ' . $data['tglbukti'] . ' ' . $nobuktiNonPosting;
                                    }
                                } else {
                                    $keteranganPostingNon['nonposting'] =  '(NON POSTING) PENGEMBALIAN PINJAMAN ' . $nobuktiNonPosting;
                                }
                                $nominalPostingNon['nonposting'] += $data['nominal'][$i];
                            } else {
                                if ($firstBuktiPosting == '') {
                                    if ($cabang == 'MEDAN') {
                                        $nobuktiPosting = $nobuktiPosting . $nobuktipengeluarantrucking . " ($keteranganpengeluarantrucking)";
                                    } else {
                                        $nobuktiPosting = $nobuktiPosting . $nobuktipengeluarantrucking;
                                    }
                                    $coakreditPostingNon['posting'] = $data['coa'];
                                    $coadebet_detail[] = $coadebet;
                                    $tgljatuhtempo[] = date('Y-m-d', strtotime($data['tglbukti']));
                                    $firstBuktiPosting = $nobuktipengeluarantrucking;
                                } else {
                                    if ($cabang == 'MEDAN') {
                                        $nobuktiPosting = $nobuktiPosting  . ', ' . $nobuktipengeluarantrucking . " ($keteranganpengeluarantrucking)";
                                    } else {
                                        $nobuktiPosting = $nobuktiPosting . ', ' . $nobuktipengeluarantrucking;
                                    }
                                }
                                if ($tanpaprosesnobukti == 3) {
                                    if ($fetchFormat->kodepenerimaan == 'PJP') {
                                        $keteranganPostingNon['posting'] =  '(POSTING) PENGEMBALIAN PINJAMAN DARI PENDAPATAN SUPIR ' . $data['pendapatansupir_bukti'] . ' ' . $data['tglbukti'] . ' ' . $nobuktiPosting;
                                    }
                                } else {
                                    $keteranganPostingNon['posting'] =  '(POSTING) PENGEMBALIAN PINJAMAN ' . $nobuktiPosting;
                                }
                                $nominalPostingNon['posting'] += $data['nominal'][$i];
                            }
                        } else {

                            $tgljatuhtempo[] = date('Y-m-d', strtotime($data['tglbukti']));
                            $nominal_detail[] = $data['nominal'][$i];
                            $keterangan_detail[] = 'PENGEMBALIAN PINJAMAN DARI PEMUTIHAN SUPIR ' . $data['supirheader'] . ' ' . $nobuktipengeluarantrucking;
                            $coakredit_detail[] = $queryposting->coa;
                            $coadebet_detail[] = $coadebet;
                        }
                    } else {
                        $coakredit_detail[] = $data['coa'];
                    }
                } else {
                    $coakredit_detail[] = $data['coa'];
                }
                if ($fetchFormat->kodepenerimaan != 'PJP' && $fetchFormat->kodepenerimaan != 'PJPK') {
                    $coadebet_detail[] = $coadebet;
                    $nominal_detail[] = $data['nominal'][$i];
                    if ($fetchFormat->kodepenerimaan == 'DPO') {
                        $namasupir = DB::table("supir")->from(DB::raw("supir with (readuncommitted)"))->select('namasupir')->where('id', $data['supir_id'][$i])->first();
                        $keterangan_detail[] = "DEPOSITO $namasupir->namasupir " . $data['keterangan'][$i];
                    } else {
                        $keterangan_detail[] = $data['keterangan'][$i];
                    }
                    $tgljatuhtempo[] = date('Y-m-d', strtotime($data['tglbukti']));
                    $totalNominal = $totalNominal + $data['nominal'][$i];
                }
            }

            // convert nominal dan coa pjp
            $diterimaDari = '';
            if ($fetchFormat->kodepenerimaan == 'PJP' || $fetchFormat->kodepenerimaan == 'PJPK') {
                if ($from != 'pemutihan') {
                    $nominalPostingNon = array_filter($nominalPostingNon, function ($value) {
                        return $value !== 0;
                    });
                    $coakreditPostingNon = array_filter($coakreditPostingNon, function ($value) {
                        return $value !== '';
                    });
                    $keteranganPostingNon = array_filter($keteranganPostingNon, function ($value) {
                        return $value !== '';
                    });
                    $nominalPostingNon = array_values($nominalPostingNon);
                    $coakreditPostingNon = array_values($coakreditPostingNon);
                    $keteranganPostingNon = array_values($keteranganPostingNon);
                    $nominal_detail = $nominalPostingNon;
                    $coakredit_detail = $coakreditPostingNon;
                    $keterangan_detail = $keteranganPostingNon;
                }

                if ($fetchFormat->kodepenerimaan == 'PJP') {
                    $getNamaSupir = DB::table("supir")->from(DB::raw("supir with (readuncommitted)"))->select('namasupir')->where('id', $data['supirheader_id'])->first();
                    if ($getNamaSupir != '') {
                        $diterimaDari = $getNamaSupir->namasupir;
                    } else {
                        $diterimaDari = 'SUPIR';
                    }
                }
                if ($fetchFormat->kodepenerimaan == 'PJPK') {

                    $getNamaKaryawan = DB::table("karyawan")->from(DB::raw("karyawan with (readuncommitted)"))->select('namakaryawan')->where('id', $data['karyawanheader_id'])->first();
                    if ($getNamaKaryawan != '') {
                        $diterimaDari = $getNamaKaryawan->namakaryawan;
                    } else {
                        $diterimaDari = 'KARYAWAN';
                    }
                }
            }

            if ($fetchFormat->kodepenerimaan == 'PBT') {
                $coakredit_detail = [];
                $coadebet_detail = [];
                $nominal_detail = [];
                $tgljatuhtempo = [];
                $keterangan_detail = [];
                $coakredit_detail[] = $data['coa'];
                $coadebet_detail[] = $coadebet;
                $nominal_detail[] = $totalNominal;
                $tgljatuhtempo[] = date('Y-m-d', strtotime($data['tglbukti']));
                $keterangan_detail[] = "PENGEMBALIAN TITIPAN EMKL " . $penerimaanTruckingHeader->nobukti . ". " . $data['keteranganheader'];
                $diterimaDari = 'DIV. EMKL';
            }
            //if tanpaprosesnobukti NOT 2 STORE PENERIMAAN
            if ($tanpaprosesnobukti != 2) {

                // tanpaprosesnobukti = 3 dari pendapatan supir

                if ($fetchFormat->kodepenerimaan == 'DPO') {
                    $coakredit_detail = [];
                    $coadebet_detail = [];
                    $nominal_detail = [];
                    $keterangan_detail = [];
                    $tgljatuhtempo = [];
                    $tgljatuhtempo[] = date('Y-m-d', strtotime($data['tglbukti']));
                    $coakredit_detail[] = $data['coa'];
                    $coadebet_detail[] = $coadebet;
                    $nominal_detail[] = $totalNominal;
                    if ($tanpaprosesnobukti == 3) {
                        $keterangan_detail[] = 'DEPOSITO DARI PENDAPATAN SUPIR ' . $data['pendapatansupir_bukti'] . ' ' . $data['tglbukti'];
                    } else {
                        $keterangan_detail[] = 'DEPOSITO SUPIR';
                        // $keterangan_detail[] = 'DEPOSITO SUPIR A/N ' . $namasupirdata . ' ' . $data['keterangan'][0];
                    }
                } else if ($fetchFormat->kodepenerimaan == 'BBM' || $fetchFormat->kodepenerimaan == 'DPOK') {
                    $coakredit_detail = [];
                    $coadebet_detail = [];
                    $nominal_detail = [];
                    $keterangan_detail = [];
                    $tgljatuhtempo = [];
                    $tgljatuhtempo[] = date('Y-m-d', strtotime($data['tglbukti']));
                    $coakredit_detail[] = $data['coa'];
                    $coadebet_detail[] = $coadebet;
                    $nominal_detail[] = $totalNominal;
                    if ($fetchFormat->kodepenerimaan == 'DPOK') {
                        $keterangan_detail[] = 'DEPOSITO KARYAWAN';
                    } else {
                        $keterangan_detail[] = $data['keterangan'][0];
                    }
                }

                /*UPDATE PENERIMAAN*/
                $penerimaanRequest = [
                    'tglbukti' => $penerimaanTruckingHeader->tglbukti,
                    'postingdari' => (array_key_exists("postingdari", $data)) ? $data['postingdari'] : "ENTRY PENERIMAAN TRUCKING",
                    'statusapproval' => $statusApproval->id,
                    'pelanggan_id' => 0,
                    'agen_id' => 0,
                    'diterimadari' => $diterimaDari,
                    'tgllunas' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'statusformat' => $format->id,
                    'bank_id' => $penerimaanTruckingHeader->bank_id,

                    'nowarkat' => null,
                    'tgljatuhtempo' => $tgljatuhtempo,
                    'nominal_detail' => $nominal_detail,
                    'coadebet' => $coadebet_detail,
                    'coakredit' => $coakredit_detail,
                    'keterangan_detail' => $keterangan_detail,
                    'invoice_nobukti' => null,
                    'bankpelanggan_id' => null,
                    'pelunasanpiutang_nobukti' => null,
                    'bulanbeban' => null,
                ];
                $penerimaanHeader = PenerimaanHeader::where('nobukti', $penerimaanTruckingHeader->penerimaan_nobukti)->first();
                $dataPenerimaan = (new PenerimaanHeader())->processUpdate($penerimaanHeader, $penerimaanRequest);
                $penerimaanTruckingHeader->penerimaan_nobukti = $dataPenerimaan->nobukti;
                $penerimaanTruckingHeader->save();
            }

            // dari pendapatan
            if ($isKomisi) {
                if ($penerimaanTruckingHeader->penerimaan_nobukti == '') {
                    if ($data['bank_id'] != 0 && $data['bank_id'] != '') {
                        if ($fetchFormat->kodepenerimaan == 'DPO') {
                            $coakredit_detail = [];
                            $coadebet_detail = [];
                            $nominal_detail = [];
                            $keterangan_detail = [];
                            $tgljatuhtempo = [];
                            $tgljatuhtempo[] = date('Y-m-d', strtotime($data['tglbukti']));
                            $coakredit_detail[] = $data['coa'];
                            $coadebet_detail[] = $coadebet;
                            $nominal_detail[] = $totalNominal;
                            $keterangan_detail[] = 'DEPOSITO DARI PENDAPATAN SUPIR ' . $data['pendapatansupir_bukti'] . ' ' . $data['tglbukti'];
                        }

                        /*STORE PENERIMAAN*/
                        $penerimaanRequest = [
                            'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                            'postingdari' => (array_key_exists("postingdari", $data)) ? $data['postingdari'] : "ENTRY PENERIMAAN TRUCKING",
                            'statusapproval' => $statusApproval->id,
                            'pelanggan_id' => 0,
                            'agen_id' => 0,
                            'diterimadari' => "",
                            'tgllunas' => date('Y-m-d', strtotime($data['tglbukti'])),
                            'statusformat' => $format->id,
                            'bank_id' => $penerimaanTruckingHeader->bank_id,

                            'nowarkat' => null,
                            'tgljatuhtempo' => $tgljatuhtempo,
                            'nominal_detail' => $nominal_detail,
                            'coadebet' => $coadebet_detail,
                            'coakredit' => $coakredit_detail,
                            'keterangan_detail' => $keterangan_detail,
                            'invoice_nobukti' => null,
                            'bankpelanggan_id' => null,
                            'pelunasanpiutang_nobukti' => null,
                            'bulanbeban' => null,
                        ];
                        $penerimaanHeader = (new PenerimaanHeader())->processStore($penerimaanRequest);

                        $penerimaanTruckingHeader->penerimaan_nobukti = $penerimaanHeader->nobukti;
                        $penerimaanTruckingHeader->save();
                    }
                } else {
                    if ($data['bank_id'] == 0 && $data['bank_id'] == '') {
                        $getPenerimaan = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))->where('nobukti', $penerimaanTruckingHeader->penerimaan_nobukti)->first();
                        if (isset($getPenerimaan)) {
                            (new PenerimaanHeader())->processDestroy($getPenerimaan->id, 'EDIT PENDAPATAN SUPIR');
                            $penerimaanTruckingHeader->penerimaan_nobukti = '';

                            $penerimaanTruckingHeader->save();
                        }
                    } else {
                        if ($data['bank_id'] != $data['prevBank']) {

                            $getPenerimaan = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))->where('nobukti', $penerimaanTruckingHeader->penerimaan_nobukti)->first();
                            if (isset($getPenerimaan)) {
                                (new PenerimaanHeader())->processDestroy($getPenerimaan->id, 'EDIT PENDAPATAN SUPIR');
                            }

                            if ($fetchFormat->kodepenerimaan == 'DPO') {
                                $coakredit_detail = [];
                                $coadebet_detail = [];
                                $nominal_detail = [];
                                $keterangan_detail = [];
                                $tgljatuhtempo = [];
                                $tgljatuhtempo[] = date('Y-m-d', strtotime($data['tglbukti']));
                                $coakredit_detail[] = $data['coa'];
                                $coadebet_detail[] = $coadebet;
                                $nominal_detail[] = $totalNominal;
                                $keterangan_detail[] = 'DEPOSITO DARI PENDAPATAN SUPIR ' . $data['pendapatansupir_bukti'] . ' ' . $data['tglbukti'];
                            }

                            /*STORE PENERIMAAN*/
                            $penerimaanRequest = [
                                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                                'postingdari' => (array_key_exists("postingdari", $data)) ? $data['postingdari'] : "ENTRY PENERIMAAN TRUCKING",
                                'statusapproval' => $statusApproval->id,
                                'pelanggan_id' => 0,
                                'agen_id' => 0,
                                'diterimadari' => "",
                                'tgllunas' => date('Y-m-d', strtotime($data['tglbukti'])),
                                'statusformat' => $format->id,
                                'bank_id' => $penerimaanTruckingHeader->bank_id,

                                'nowarkat' => null,
                                'tgljatuhtempo' => $tgljatuhtempo,
                                'nominal_detail' => $nominal_detail,
                                'coadebet' => $coadebet_detail,
                                'coakredit' => $coakredit_detail,
                                'keterangan_detail' => $keterangan_detail,
                                'invoice_nobukti' => null,
                                'bankpelanggan_id' => null,
                                'pelunasanpiutang_nobukti' => null,
                                'bulanbeban' => null,
                            ];
                            $penerimaanHeader = (new PenerimaanHeader())->processStore($penerimaanRequest);

                            $penerimaanTruckingHeader->penerimaan_nobukti = $penerimaanHeader->nobukti;
                            $penerimaanTruckingHeader->save();
                        } else {

                            // tanpaprosesnobukti = 3 dari pendapatan supir
                            if ($fetchFormat->kodepenerimaan == 'DPO') {
                                $coakredit_detail = [];
                                $coadebet_detail = [];
                                $nominal_detail = [];
                                $keterangan_detail = [];
                                $tgljatuhtempo = [];
                                $tgljatuhtempo[] = date('Y-m-d', strtotime($data['tglbukti']));
                                $coakredit_detail[] = $data['coa'];
                                $coadebet_detail[] = $coadebet;
                                $nominal_detail[] = $totalNominal;
                                $keterangan_detail[] = 'DEPOSITO DARI PENDAPATAN SUPIR ' . $data['pendapatansupir_bukti'] . ' ' . $data['tglbukti'];
                            }
                            /*UPDATE PENERIMAAN*/
                            $penerimaanRequest = [
                                'tglbukti' => $penerimaanTruckingHeader->tglbukti,
                                'postingdari' => (array_key_exists("postingdari", $data)) ? $data['postingdari'] : "ENTRY PENERIMAAN TRUCKING",
                                'statusapproval' => $statusApproval->id,
                                'pelanggan_id' => 0,
                                'agen_id' => 0,
                                'diterimadari' => "",
                                'tgllunas' => date('Y-m-d', strtotime($data['tglbukti'])),
                                'statusformat' => $format->id,
                                'bank_id' => $penerimaanTruckingHeader->bank_id,

                                'nowarkat' => null,
                                'tgljatuhtempo' => $tgljatuhtempo,
                                'nominal_detail' => $nominal_detail,
                                'coadebet' => $coadebet_detail,
                                'coakredit' => $coakredit_detail,
                                'keterangan_detail' => $keterangan_detail,
                                'invoice_nobukti' => null,
                                'bankpelanggan_id' => null,
                                'pelunasanpiutang_nobukti' => null,
                                'bulanbeban' => null,
                            ];
                            $penerimaanHeader = PenerimaanHeader::where('nobukti', $penerimaanTruckingHeader->penerimaan_nobukti)->first();
                            $dataPenerimaan = (new PenerimaanHeader())->processUpdate($penerimaanHeader, $penerimaanRequest);
                            $penerimaanTruckingHeader->penerimaan_nobukti = $dataPenerimaan->nobukti;
                            $penerimaanTruckingHeader->save();
                        }
                    }
                }
            }
            $penerimaanTruckingHeaderLogTrail = (new LogTrail())->processStore([
                'namatabel' => strtoupper($penerimaanTruckingHeader->getTable()),
                'postingdari' => $data['postingdari'] ?? strtoupper('EDIT penerimaan Trucking Header '),
                'idtrans' => $penerimaanTruckingHeader->id,
                'nobuktitrans' => $penerimaanTruckingHeader->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $penerimaanTruckingHeader->toArray(),
                'modifiedby' => auth('api')->user()->user
            ]);

            $penerimaanTruckingDetailLogTrail = (new LogTrail())->processStore([
                'namatabel' => strtoupper($penerimaanTruckingHeaderLogTrail->getTable()),
                'postingdari' => $data['postingdari'] ?? strtoupper('EDIT penerimaan Trucking detail '),
                'idtrans' => $penerimaanTruckingHeaderLogTrail->id,
                'nobuktitrans' => $penerimaanTruckingHeader->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $penerimaanTruckingDetails,
                'modifiedby' => auth('api')->user()->user
            ]);
        }

        return $penerimaanTruckingHeader;
    }


    public function processDestroy($id, $postingDari): PenerimaanTruckingHeader
    {
        $penerimaanTruckingDetails = PenerimaanTruckingDetail::lockForUpdate()->where('penerimaantruckingheader_id', $id)->get();

        $penerimaanTruckingHeader = new penerimaanTruckingHeader();
        $penerimaanTruckingHeader = $penerimaanTruckingHeader->lockAndDestroy($id);

        $penerimaanTruckingHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $penerimaanTruckingHeader->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $penerimaanTruckingHeader->id,
            'nobuktitrans' => $penerimaanTruckingHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $penerimaanTruckingHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'penerimaantruckingdetail',
            'postingdari' => $postingDari,
            'idtrans' => $penerimaanTruckingHeaderLogTrail['id'],
            'nobuktitrans' => $penerimaanTruckingHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $penerimaanTruckingDetails->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        if ($postingDari != 'EDIT GAJI SUPIR' && $postingDari != 'DELETE GAJI SUPIR') {

            $penerimaanHeader = PenerimaanHeader::where('nobukti', $penerimaanTruckingHeader->penerimaan_nobukti)->first();
            // throw new \Exception($penerimaanHeader->nobukti);
            if (isset($penerimaanHeader)) {
                (new PenerimaanHeader())->processDestroy($penerimaanHeader->id, $postingDari);
            }
            $penerimaanTruckingHeader->delete();
        }
        return $penerimaanTruckingHeader;
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
        $query = DB::table($this->table)->from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
            ->select(
                'penerimaantruckingheader.id',
                'penerimaantruckingheader.nobukti',
                'penerimaantruckingheader.tglbukti',
                'penerimaantrucking.keterangan as penerimaantrucking_id',
                'penerimaantruckingheader.penerimaan_nobukti',
                'penerimaantruckingheader.keterangan as keteranganheader',
                'penerimaantruckingheader.statusformat',
                'penerimaantruckingheader.periodedari',
                'penerimaantruckingheader.periodesampai',
                'jenisorder.keterangan as jenisorder_id',
                'bank.namabank as bank_id',
                'akunpusat.keterangancoa as coa',
                'statuscetak.memo as statuscetak',
                'statuscetak.id as  statuscetak_id',
                DB::raw("'Bukti Penerimaan Trucking' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'penerimaantruckingheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("penerimaantrucking with (readuncommitted)"), 'penerimaantruckingheader.penerimaantrucking_id', 'penerimaantrucking.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'penerimaantruckingheader.coa', 'akunpusat.coa')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'penerimaantruckingheader.jenisorder_id', 'jenisorder.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'penerimaantruckingheader.bank_id', 'bank.id');
        if (request()->tgldari) {
            $query->whereBetween('penerimaantruckingheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if (request()->penerimaanheader_id) {
            $query->where('penerimaantrucking_id', request()->penerimaanheader_id);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(penerimaantruckingheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(penerimaantruckingheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("penerimaantruckingheader.statuscetak", $statusCetak);
        }
        $data = $query->first();
        return $data;
    }

    public function printValidation($id)
    {
        $query = DB::table($this->table)->from($this->table)->where('penerimaantruckingheader.id', $id);
        $data = $query->first();
        $status = $data->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($status == $statusCetak->id) {
            return true;
        }

        return false;
    }

    public function isUangJalanProcessed($nobukti)
    {
        $prosesUangJalan = DB::table('prosesuangjalansupirdetail')->from(DB::raw("prosesuangjalansupirdetail as a with (readuncommitted)"))->select('a.penerimaantrucking_nobukti', 'a.nobukti')
            ->where('a.penerimaantrucking_nobukti', '=', $nobukti)
            ->first();

        if (isset($prosesUangJalan)) {
            //jika uang jalan ada maka true
            $data = [
                'kondisi' => true,
                'nobukti' => $prosesUangJalan->nobukti
            ];
            return $data;
        }

        $data = [
            'kondisi' => false,
            'nobukti' => ''
        ];
        return $data;
    }
    public function isUangOut($nobukti)
    {
        $prosesUangJalan = DB::table('pengeluarantruckingdetail')->from(DB::raw("prosesuangjalansupirdetail as a with (readuncommitted)"))->select('a.penerimaantrucking_nobukti')
            ->where('a.penerimaantrucking_nobukti', '=', $nobukti)
            ->first();

        if (isset($prosesUangJalan)) {
            //jika uang jalan ada maka true
            return true;
        }
        return false;
    }

    public function cekvalidasiaksi($nobukti)
    {

        $error = new Error();
        $keteranganerror = $error->cekKeteranganError('SAPP') ?? '';
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';


        $jurnal = DB::table('penerimaantruckingheader')
            ->from(
                DB::raw("penerimaantruckingheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.penerimaan_nobukti'
            )
            ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.penerimaan_nobukti', 'b.nobukti')
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($jurnal)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $jurnal->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Approval Jurnal ' . $jurnal->penerimaan_nobukti,
                'kodeerror' => 'SAPP'
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('TDT') ?? '';
        $prosesUangJalan = DB::table('prosesuangjalansupirdetail')
            ->from(
                DB::raw("prosesuangjalansupirdetail as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.penerimaantrucking_nobukti'
            )
            ->where('a.penerimaantrucking_nobukti', '=', $nobukti)
            ->first();
        if (isset($prosesUangJalan)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti Proses Uang Jalan Supir <b>' . $prosesUangJalan->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Proses Uang Jalan Supir ' . $prosesUangJalan->nobukti,
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
        $pengeluaranTrucking = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.penerimaantruckingheader_nobukti'
            )
            ->where('a.penerimaantruckingheader_nobukti', '=', $nobukti)
            ->first();
        if (isset($pengeluaranTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti Pengeluaran Trucking <b>' . $pengeluaranTrucking->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Pengeluaran Trucking ' . $pengeluaranTrucking->nobukti,
                'kodeerror' => 'SATL2'
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('TDT') ?? '';
        $jurnal = DB::table('pemutihansupirheader')
            ->from(
                DB::raw("pemutihansupirheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.penerimaantruckingposting_nobukti'
            )
            ->where('a.penerimaantruckingposting_nobukti', '=', $nobukti)
            ->first();
        if (isset($jurnal)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti PEMUTIHAN SUPIR <b>' . $jurnal->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'PEMUTIHAN SUPIR ' . $jurnal->nobukti,
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('TDT') ?? '';

        $jurnal = DB::table('pemutihansupirheader')
            ->from(
                DB::raw("pemutihansupirheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.penerimaantruckingnonposting_nobukti'
            )
            ->where('a.penerimaantruckingnonposting_nobukti', '=', $nobukti)
            ->first();
        if (isset($jurnal)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti PEMUTIHAN SUPIR <b>' . $jurnal->nobukti . '</b> <br> ' . $keterangantambahanerror,
                'keterangan' => 'PEMUTIHAN SUPIR ' . $jurnal->nobukti,
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('TDT') ?? '';

        $gajiSupirDeposito = DB::table('gajisupirdeposito')
            ->from(
                DB::raw("gajisupirdeposito as a with (readuncommitted)")
            )
            ->select(
                'a.gajisupir_nobukti',
                'a.penerimaantrucking_nobukti'
            )
            ->where('a.penerimaantrucking_nobukti', '=', $nobukti)
            ->first();
        if (isset($gajiSupirDeposito)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti Rincian Gaji Supir <b>' . $gajiSupirDeposito->gajisupir_nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Rincian Gaji Supir ' . $gajiSupirDeposito->gajisupir_nobukti,
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('TDT') ?? '';
        $gajiSupirBBM = DB::table('gajisupirbbm')
            ->from(
                DB::raw("gajisupirbbm as a with (readuncommitted)")
            )
            ->select(
                'a.gajisupir_nobukti',
                'a.penerimaantrucking_nobukti'
            )
            ->where('a.penerimaantrucking_nobukti', '=', $nobukti)
            ->first();
        if (isset($gajiSupirBBM)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti Rincian Gaji Supir <b>' . $gajiSupirBBM->gajisupir_nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Rincian Gaji Supir ' . $gajiSupirBBM->gajisupir_nobukti,
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('TDT') ?? '';

        $gajiSupirPelunasan = DB::table('gajisupirpelunasanpinjaman')
            ->from(
                DB::raw("gajisupirpelunasanpinjaman as a with (readuncommitted)")
            )
            ->select(
                'a.gajisupir_nobukti',
                'a.penerimaantrucking_nobukti'
            )
            ->where('a.penerimaantrucking_nobukti', '=', $nobukti)
            ->first();
        if (isset($gajiSupirPelunasan)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti Rincian Gaji Supir <b>' . $gajiSupirPelunasan->gajisupir_nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Rincian Gaji Supir ' . $gajiSupirPelunasan->gajisupir_nobukti,
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('TDT') ?? '';
        $pendapatan = DB::table('penerimaantruckingheader')
            ->from(
                DB::raw("penerimaantruckingheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'b.nobukti as pendapatan'
            )
            ->join(DB::raw("pendapatansupirheader b with (readuncommitted)"), 'a.pendapatansupir_bukti', 'b.nobukti')
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($pendapatan)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti Komisi Supir <b>' . $pendapatan->pendapatan . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'pendapatan supir ' . $pendapatan->pendapatan,
                'kodeerror' => 'TDT'
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
}
