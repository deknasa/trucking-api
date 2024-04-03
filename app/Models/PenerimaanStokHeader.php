<?php

namespace App\Models;

use App\Services\RunningNumberService;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class PenerimaanStokHeader extends MyModel
{
    use HasFactory;

    protected $table = 'penerimaanstokheader';

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
        $from = request()->from ?? '';
        $cabang = request()->cabang ?? '';
        // dd(request());

        $user_id = auth('api')->user()->id ?? 0;
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'PnrmStokHeaderController';
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
                $table->string('nobukti', 1000)->nullable();
                $table->dateTime('tglbukti')->nullable();
                $table->string('penerimaanstok', 50)->nullable();
                $table->string('penerimaanstok_nobukti', 50)->nullable();
                $table->date('tgldariheadernobuktipenerimaanstok')->nullable();
                $table->date('tglsampaiheadernobuktipenerimaanstok')->nullable();
                $table->string('pengeluaranstok_nobukti', 50)->nullable();
                $table->string('gudang', 50)->nullable();
                $table->string('trado', 50)->nullable();
                $table->string('gandengan', 50)->nullable();
                $table->string('tradodari', 50)->nullable();
                $table->string('tradoke', 50)->nullable();
                $table->string('gandengandari', 50)->nullable();
                $table->string('gandenganke', 50)->nullable();
                $table->string('supplier', 200)->nullable();
                $table->string('nobon', 50)->nullable();
                $table->string('hutang_nobukti', 50)->nullable();
                $table->string('gudangdari', 50)->nullable();
                $table->string('gudangke', 50)->nullable();
                $table->integer('statusformat')->nullable();
                $table->string('coa', 200)->nullable();
                $table->longText('keterangan')->nullable();
                $table->integer('kelompok_id')->nullable();
                $table->string('modifiedby', 200)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->integer('gudang_id')->nullable();
                $table->integer('gudangdari_id')->nullable();
                $table->integer('gudangke_id')->nullable();
                $table->integer('penerimaanstok_id')->nullable();
                $table->integer('trado_id')->nullable();
                $table->integer('tradoke_id')->nullable();
                $table->integer('tradodari_id')->nullable();
                $table->integer('gandenganke_id')->nullable();
                $table->integer('gandengandari_id')->nullable();
                $table->integer('gandengan_id')->nullable();
                $table->integer('supplier_id')->nullable();
                $table->integer('jumlahcetak')->nullable();
                $table->longText('statuscetak')->nullable();
                $table->longText('statusedit')->nullable();
                $table->date('parrenttglbukti')->nullable();
                $table->integer('statuscetak_id')->nullable();
                $table->integer('statusedit_id')->nullable();
                $table->integer('statuseditketerangan_id')->nullable();
                $table->date('tgldariheaderhutangheader')->nullable();
                $table->date('tglsampaiheaderhutangheader')->nullable();
                $table->date('tgldariheaderpengeluaranstok')->nullable();
                $table->date('tglsampaiheaderpengeluaranstok')->nullable();
                $table->longText('judul')->nullable();
            });

            // $temprole = '##temprole' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            // Schema::create($temprole, function ($table) {
            //     $table->bigInteger('aco_id')->nullable();
            // });

            // $queryaco = db::table("useracl")->from(db::raw("useracl a with (readuncommitted)"))
            //     ->select('a.aco_id')
            //     ->join(db::raw("penerimaanstok b with (readuncommitted)"), 'a.aco_id', 'b.aco_id')
            //     ->where('a.user_id', $user_id);

            // DB::table($temprole)->insertUsing(['aco_id'], $queryaco);


            // $queryrole = db::table("acl")->from(db::raw("acl a with (readuncommitted)"))
            //     ->select('a.aco_id')
            //     ->join(db::raw("userrole b with (readuncommitted)"), 'a.role_id', 'b.role_id')
            //     ->join(db::raw("penerimaanstok c with (readuncommitted)"), 'a.aco_id', 'c.aco_id')
            //     ->leftjoin(db::raw($temprole . " d "), 'a.aco_id', 'd.aco_id')
            //     ->where('b.user_id', $user_id)
            //     ->whereRaw("isnull(d.aco_id,0)=0");

            // DB::table($temprole)->insertUsing(['aco_id'], $queryrole);


            $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();
            $po = Parameter::where('grp', 'PO STOK')->where('subgrp', 'PO STOK')->first();
            $spbs = Parameter::where('grp', 'REUSE STOK')->where('subgrp', 'REUSE STOK')->first();
            $spbp = DB::table('penerimaanstok')->where('kodepenerimaan', 'SPBP')->first();
            $do = Parameter::where('grp', 'DO STOK')->where('subgrp', 'DO STOK')->first();
            $pg = Parameter::where('grp', 'PG STOK')->where('subgrp', 'PG STOK')->first();
            $rtb = Parameter::where('grp', 'RETUR STOK')->where('subgrp', 'RETUR STOK')->first();
            $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();


            $temtabelPg = '##temppg' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;
            Schema::create($temtabelPg, function (Blueprint $table) {
                $table->integer('id')->nullable();
                $table->string('nobukti', 1000)->nullable();
                $table->integer('stok_id')->nullable();
                $table->integer('kelompok_id')->nullable();
            });
            $queryPg = DB::table($this->table)->select(
                DB::raw("'' as id"),
                DB::raw("'' as nobukti"),
                DB::raw("'' as stok_id"),
                DB::raw("'' as kelompok_id"),
            )->take(1);

            if (request()->pengeluaranstok_id == $spk->text) {
                $queryPg = DB::table($this->table)->leftJoin(DB::raw('(SELECT *, ROW_NUMBER() OVER (PARTITION BY penerimaanstokheader_id ORDER BY id) AS row_num FROM penerimaanstokdetail) AS penerimaanstokdetail'), function ($join) {
                    $join->on('penerimaanstokheader.id', '=', 'penerimaanstokdetail.penerimaanstokheader_id')
                        ->where('penerimaanstokdetail.row_num', '=', 1);
                })
                    ->leftJoin('stok', 'penerimaanstokdetail.stok_id', '=', 'stok.id')
                    ->leftJoin('kelompok', 'stok.kelompok_id', '=', 'kelompok.id')
                    ->where('penerimaanstokheader.penerimaanstok_id', '=', $pg->text)
                    ->select('penerimaanstokheader.id', 'penerimaanstokheader.nobukti', 'penerimaanstokdetail.stok_id', 'stok.kelompok_id');
            }
            DB::table($temtabelPg)->insertUsing([
                'id',
                'nobukti',
                'stok_id',
                'kelompok_id',
            ], $queryPg);
            $queryTemtabelPg = DB::table($temtabelPg);

            $query = DB::table($this->table);
            $query = $this->selectColumns($query)

                ->leftJoin('gudang as gudangs', 'penerimaanstokheader.gudang_id', 'gudangs.id')
                ->leftJoin('gudang as dari', 'penerimaanstokheader.gudangdari_id', 'dari.id')
                ->leftJoin('gudang as ke', 'penerimaanstokheader.gudangke_id', 'ke.id')
                ->leftJoin('parameter as statuscetak', 'penerimaanstokheader.statuscetak', 'statuscetak.id')
                ->leftJoin('parameter as statusedit', 'penerimaanstokheader.statusapprovaledit', 'statusedit.id')
                ->leftJoin('parameter as statuseditketerangan', 'penerimaanstokheader.statusapprovaleditketerangan', 'statuseditketerangan.id')
                ->leftJoin('penerimaanstok', 'penerimaanstokheader.penerimaanstok_id', 'penerimaanstok.id')
                ->leftJoin('akunpusat', 'penerimaanstokheader.coa', 'akunpusat.coa')
                ->leftJoin('trado', 'penerimaanstokheader.trado_id', 'trado.id')
                ->leftJoin('trado as tradodari ', 'penerimaanstokheader.tradodari_id', 'tradodari.id')
                ->leftJoin('trado as tradoke ', 'penerimaanstokheader.tradoke_id', 'tradoke.id')
                ->leftJoin('gandengan as gandengandari ', 'penerimaanstokheader.gandengandari_id', 'gandengandari.id')
                ->leftJoin('gandengan as gandenganke ', 'penerimaanstokheader.gandenganke_id', 'gandenganke.id')
                ->leftJoin('gandengan as gandengan ', 'penerimaanstokheader.gandengan_id', 'gandengan.id')
                ->leftJoin('hutangheader', 'penerimaanstokheader.hutang_nobukti', 'hutangheader.nobukti')
                ->leftJoin('pengeluaranstokheader as pengeluaranstok', 'penerimaanstokheader.pengeluaranstok_nobukti', 'pengeluaranstok.nobukti')
                ->leftJoin('penerimaanstokheader as nobuktipenerimaanstok', 'nobuktipenerimaanstok.nobukti', 'penerimaanstokheader.penerimaanstok_nobukti')
                ->leftJoin('penerimaanstokheader as nobuktispb', 'penerimaanstokheader.nobukti', 'nobuktispb.penerimaanstok_nobukti')
                ->leftJoin(db::raw($temtabelPg . " d1"), "penerimaanstokheader.id", "d1.id")
                ->leftJoin('supplier', 'penerimaanstokheader.supplier_id', 'supplier.id');
            // ->join(db::raw($temprole . " d "), 'penerimaanstok.aco_id', 'd.aco_id');

            if (request()->penerimaanstok_id == $spb->text) {


                // $query->leftJoin('penerimaanstokheader as po', 'penerimaanstokheader.penerimaanstok_nobukti', '=', 'po.nobukti')
                $query->where('penerimaanstokheader.penerimaanstok_id', '=', $po->text)
                    ->whereNotIn('penerimaanstokheader.nobukti', function ($query) {
                        $query->select(DB::raw('DISTINCT penerimaanstokheader.penerimaanstok_nobukti'))
                            ->from('penerimaanstokheader')
                            ->whereNotNull('penerimaanstokheader.penerimaanstok_nobukti')
                            ->where('penerimaanstokheader.penerimaanstok_nobukti', '!=', '');
                    });
                // return $query->get();
            }

            if (request()->penerimaanstok_id == $spbs->text) {
                $query->leftJoin('penerimaanstokdetail as detail', 'penerimaanstokheader.id', '=', 'detail.penerimaanstokheader_id');
                $query->where('penerimaanstokheader.penerimaanstok_id', '=', $do->text)
                    ->where('detail.stok_id', '=', request()->stok_id)
                    ->whereNotIn('penerimaanstokheader.nobukti', function ($query) {
                        $query->select(DB::raw('DISTINCT penerimaanstokheader.penerimaanstok_nobukti'))
                            ->from('penerimaanstokheader')
                            ->whereNotNull('penerimaanstokheader.penerimaanstok_nobukti')
                            ->where('penerimaanstokheader.penerimaanstok_nobukti', '!=', '');
                    });
                // return $query->get();
            }
            if (request()->penerimaanstok_id == $spbp->id) {
                $query->where('penerimaanstokheader.penerimaanstok_id', '=', $spb->text)
                    ->whereNotIn('penerimaanstokheader.nobukti', function ($query) {
                        $query->select(DB::raw('DISTINCT penerimaanstokheader.penerimaanstok_nobukti'))
                            ->from('penerimaanstokheader')
                            ->whereNotNull('penerimaanstokheader.penerimaanstok_nobukti')
                            ->where('penerimaanstokheader.penerimaanstok_nobukti', '!=', '');
                    });
            }

            if (request()->penerimaanstok_id == $pg->text) {
                $query->where('penerimaanstokheader.penerimaanstok_id', '=', $pg->text)
                    ->whereNotIn('penerimaanstokheader.nobukti', function ($query) {
                        $query->select(DB::raw('DISTINCT penerimaanstokheader.penerimaanstok_nobukti'))
                            ->from('penerimaanstokheader')
                            ->whereNotNull('penerimaanstokheader.penerimaanstok_nobukti')
                            ->where('penerimaanstokheader.penerimaanstok_nobukti', '!=', '');
                    });
            }
            if (request()->supplier_id) {
                // $query->leftJoin('penerimaanstokheader as pobeli','penerimaanstokheader.penerimaanstok_nobukti','pobeli.nobukti');
                $query->where('penerimaanstokheader.supplier_id', '=', request()->supplier_id);
                // $query->whereRaw("isnull(pobeli.nobukti,'')=''");
                // dd($query->get());
            }
            if (request()->pengeluaranstok_id == $rtb->text) {
                //jika retur cari penerimaan hanya
                $query->where('penerimaanstokheader.penerimaanstok_id', '=', $spb->text);
            }
            if (request()->pengeluaranstok_id == $spk->text) {
                //jika spk, dan stok_id adalah barang reuse harus ada pg dari tujuan ke gudang sementara untuk diperbaiki
                $query->where('penerimaanstokheader.penerimaanstok_id', '=', $pg->text);
            }
            if (request()->tgldari) {
                $query->whereBetween('penerimaanstokheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
            }

            if ($from == 'klaim') {

                $pengeluarantrucking_id = request()->pengeluarantrucking_id ?? 0;
                $tempTrucking = '##tempTrucking' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempTrucking, function ($table) {
                    $table->unsignedBigInteger('jumlah')->nullable();
                    $table->string('penerimaanstok_nobukti')->nullable();
                });
                if ($cabang == 'TNL') {

                    $queryklaimtrucking = DB::connection('sqlsrvtas')->table("pengeluarantruckingdetail")
                        // ->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
                        ->select(DB::raw("count(pengeluarantruckingdetail.stoktnl_id) as jumlah, pengeluarantruckingdetail.penerimaanstoktnl_nobukti as penerimaanstok_nobukti"))
                        ->join(DB::raw("trucking.dbo.pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'pengeluarantruckingdetail.nobukti')
                        ->whereRaw("pengeluarantruckingdetail.penerimaanstoktnl_nobukti != ''")
                        ->where("pengeluarantruckingheader.pengeluarantrucking_id", 7)
                        ->whereBetween('pengeluarantruckingheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
                        ->where('pengeluarantruckingheader.id', '<>', $pengeluarantrucking_id)
                        ->groupBy('pengeluarantruckingdetail.penerimaanstoktnl_nobukti')->get();

                    foreach ($queryklaimtrucking as $item) {
                        DB::table($tempTrucking)->insert([
                            'jumlah' => $item->jumlah,
                            'penerimaanstok_nobukti' => $item->penerimaanstok_nobukti,

                        ]);
                    }
                } else {
                    $queryklaimtrucking = DB::table("pengeluarantruckingdetail")->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
                        ->select(DB::raw("count(pengeluarantruckingdetail.stok_id) as jumlah, pengeluarantruckingdetail.penerimaanstok_nobukti"))
                        ->join(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'pengeluarantruckingdetail.nobukti')
                        ->where("pengeluarantruckingdetail.penerimaanstok_nobukti", '!=', "''")
                        ->where("pengeluarantruckingheader.pengeluarantrucking_id", 7)
                        ->whereBetween('pengeluarantruckingheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
                        ->where('pengeluarantruckingheader.id', '<>', $pengeluarantrucking_id)
                        ->groupBy('pengeluarantruckingdetail.penerimaanstok_nobukti');

                    DB::table($tempTrucking)->insertUsing([
                        'jumlah',
                        'penerimaanstok_nobukti',
                    ],  $queryklaimtrucking);
                }

                $tempSpk = '##tempSpk' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempSpk, function ($table) {
                    $table->unsignedBigInteger('jumlah')->nullable();
                    $table->string('nobukti')->nullable();
                });


                $tutupbuku = DB::table("parameter")->where('grp', 'TUTUP BUKU')->first()->text ?? '1900/01/01';
                $queryklaimtrucking = DB::table("penerimaanstokheader")->from(DB::raw("penerimaanstokheader with (readuncommitted)"))
                    ->select(DB::raw("count(penerimaanstokdetail.stok_id) as jumlah,penerimaanstokheader.nobukti"))
                    ->join(DB::raw("penerimaanstokdetail with (readuncommitted)"), 'penerimaanstokheader.nobukti', 'penerimaanstokdetail.nobukti')
                    ->where("penerimaanstokheader.penerimaanstok_id", 5)
                    ->whereBetween('penerimaanstokheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
                    ->where('penerimaanstokheader.tglbukti', '>', date('Y-m-d', strtotime($tutupbuku)))
                    ->groupBy('penerimaanstokheader.nobukti');

                DB::table($tempSpk)->insertUsing([
                    'jumlah',
                    'nobukti',
                ],  $queryklaimtrucking);

                $tempfinalklaim = '##tempfinalklaim' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempfinalklaim, function ($table) {
                    $table->unsignedBigInteger('jumlah')->nullable();
                    $table->string('nobukti')->nullable();
                });

                $queryklaimtrucking = DB::table("$tempSpk")->from(DB::raw("$tempSpk as tempspk with (readuncommitted)"))
                    ->select(DB::raw("tempspk.jumlah,tempspk.nobukti"))
                    ->leftJoin(DB::raw("$tempTrucking as temptrucking with (readuncommitted)"), 'tempspk.nobukti', 'temptrucking.penerimaanstok_nobukti')
                    ->whereRaw("isnull(temptrucking.jumlah,0) != isnull(tempspk.jumlah,0)");
                DB::table($tempfinalklaim)->insertUsing([
                    'jumlah',
                    'nobukti',
                ],  $queryklaimtrucking);
                // dd(DB::table($tempfinalklaim)->get());

                $query->join(DB::raw("$tempfinalklaim as finalklaim with (readuncommitted)"), 'penerimaanstokheader.nobukti', 'finalklaim.nobukti');
            }


            if (request()->penerimaanheader_id) {
                $query->where('penerimaanstokheader.penerimaanstok_id', request()->penerimaanheader_id);
            }
            if ($periode != '') {
                $periode = explode("-", $periode);
                $query->whereRaw("MONTH(penerimaanstokheader.tglbukti) ='" . $periode[0] . "'")
                    ->whereRaw("year(penerimaanstokheader.tglbukti) ='" . $periode[1] . "'");
            }
            if ($statusCetak != '') {
                $query->where("penerimaanstokheader.statuscetak", $statusCetak);
            }


            $datadetail = json_decode($query->get(), true);
            foreach ($datadetail as $item) {

                DB::table($temtabel)->insert([
                    'id' => $item['id'],
                    'nobukti' => $item['nobukti'],
                    'tglbukti' => $item['tglbukti'],
                    'penerimaanstok' => $item['penerimaanstok'],
                    'penerimaanstok_nobukti' => $item['penerimaanstok_nobukti'],
                    'tgldariheadernobuktipenerimaanstok' => $item['tgldariheadernobuktipenerimaanstok'],
                    'tglsampaiheadernobuktipenerimaanstok' => $item['tglsampaiheadernobuktipenerimaanstok'],
                    'pengeluaranstok_nobukti' => $item['pengeluaranstok_nobukti'],
                    'gudang' => $item['gudang'],
                    'trado' => $item['trado'],
                    'gandengan' => $item['gandengan'],
                    'tradodari' => $item['tradodari'],
                    'tradoke' => $item['tradoke'],
                    'gandengandari' => $item['gandengandari'],
                    'gandenganke' => $item['gandenganke'],
                    'supplier' => $item['supplier'],
                    'nobon' => $item['nobon'],
                    'hutang_nobukti' => $item['hutang_nobukti'],
                    'gudangdari' => $item['gudangdari'],
                    'gudangke' => $item['gudangke'],
                    'statusformat' => $item['statusformat'],
                    'coa' => $item['coa'],
                    'keterangan' => $item['keterangan'],
                    'kelompok_id' => $item['kelompok_id'],
                    'modifiedby' => $item['modifiedby'],
                    'created_at' => $item['created_at'],
                    'updated_at' => $item['updated_at'],
                    'gudang_id' => $item['gudang_id'],
                    'gudangdari_id' => $item['gudangdari_id'],
                    'gudangke_id' => $item['gudangke_id'],
                    'penerimaanstok_id' => $item['penerimaanstok_id'],
                    'trado_id' => $item['trado_id'],
                    'tradoke_id' => $item['tradoke_id'],
                    'tradodari_id' => $item['tradodari_id'],
                    'gandenganke_id' => $item['gandenganke_id'],
                    'gandengandari_id' => $item['gandengandari_id'],
                    'gandengan_id' => $item['gandengan_id'],
                    'supplier_id' => $item['supplier_id'],
                    'jumlahcetak' => $item['jumlahcetak'],
                    'statuscetak' => $item['statuscetak'],
                    'statusedit' => $item['statusedit'],
                    'parrenttglbukti' => $item['parrenttglbukti'],
                    'statuscetak_id' => $item['statuscetak_id'],
                    'statusedit_id' => $item['statusedit_id'],
                    'statuseditketerangan_id' => $item['statuseditketerangan_id'],
                    'tgldariheaderhutangheader' => $item['tgldariheaderhutangheader'],
                    'tglsampaiheaderhutangheader' => $item['tglsampaiheaderhutangheader'],
                    'tgldariheaderpengeluaranstok' => $item['tgldariheaderpengeluaranstok'],
                    'tglsampaiheaderpengeluaranstok' => $item['tglsampaiheaderpengeluaranstok'],
                    'judul' => $item['judul'],
                ]);
            }

            // DB::table($temtabel)->insertUsing([
            //     'id',
            //     'nobukti',
            //     'tglbukti',
            //     'penerimaanstok',
            //     'penerimaanstok_nobukti',
            //     'tgldariheadernobuktipenerimaanstok',
            //     'tglsampaiheadernobuktipenerimaanstok',
            //     'pengeluaranstok_nobukti',
            //     'gudang',
            //     'trado',
            //     'gandengan',
            //     'tradodari',
            //     'tradoke',
            //     'gandengandari',
            //     'gandenganke',
            //     'supplier',
            //     'nobon',
            //     'hutang_nobukti',
            //     'gudangdari',
            //     'gudangke',
            //     'statusformat',
            //     'coa',
            //     'keterangan',
            //     'modifiedby',
            //     'created_at',
            //     'updated_at',
            //     'gudang_id',
            //     'gudangdari_id',
            //     'gudangke_id',
            //     'penerimaanstok_id',
            //     'trado_id',
            //     'tradoke_id',
            //     'tradodari_id',
            //     'gandenganke_id',
            //     'gandengandari_id',
            //     'gandengan_id',
            //     'supplier_id',
            //     'jumlahcetak',
            //     'statuscetak',
            //     'statusedit',
            //     'parrenttglbukti',
            //     'statuscetak_id',
            //     'statusedit_id',
            //     'tgldariheaderhutangheader',
            //     'tglsampaiheaderhutangheader',
            //     'tgldariheaderpengeluaranstok',
            //     'tglsampaiheaderpengeluaranstok',
            //     'judul',
            // ], $query);
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
                'a.penerimaanstok',
                'a.penerimaanstok_nobukti',
                'a.tgldariheadernobuktipenerimaanstok',
                'a.tglsampaiheadernobuktipenerimaanstok',
                'a.pengeluaranstok_nobukti',
                'a.gudang',
                'a.trado',
                'a.gandengan',
                'a.tradodari',
                'a.tradoke',
                'a.gandengandari',
                'a.gandenganke',
                'a.supplier',
                'a.nobon',
                'a.hutang_nobukti',
                'a.gudangdari',
                'a.gudangke',
                'a.statusformat',
                'a.coa',
                'a.keterangan',
                'a.kelompok_id',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
                'a.gudang_id',
                'a.gudangdari_id',
                'a.gudangke_id',
                'a.penerimaanstok_id',
                'a.trado_id',
                'a.tradoke_id',
                'a.tradodari_id',
                'a.gandenganke_id',
                'a.gandengandari_id',
                'a.gandengan_id',
                'a.supplier_id',
                'a.jumlahcetak',
                'a.statuscetak',
                'a.statusedit',
                'a.parrenttglbukti',
                'a.statuscetak_id',
                'a.statusedit_id',
                'a.statuseditketerangan_id',
                'a.tgldariheaderhutangheader',
                'a.tglsampaiheaderhutangheader',
                'a.tgldariheaderpengeluaranstok',
                'a.tglsampaiheaderpengeluaranstok',
                'a.judul',
            );
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }


    public function getTNLForKlaim($dari, $sampai)
    {
        $server = config('app.url_tnl');
        $getToken = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])
            ->post($server . 'token', [
                'user' => 'ADMIN',
                'password' => getenv('PASSWORD_TNL'),
                'ipclient' => '',
                'ipserver' => '',
                'latitude' => '',
                'longitude' => '',
                'browser' => '',
                'os' => '',
            ]);
        $access_token = json_decode($getToken, TRUE)['access_token'];

        $getTrado = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json',
        ])

            ->get($server . "penerimaanstokheader?limit=0&tgldari=" . $dari . "&tglsampai=" . $sampai);

        $data = $getTrado->json()['data'];

        $class = 'PenerimaanStokHeaderController';
        $user = auth('api')->user()->name;
        $temtabel = 'temppg' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

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
            $table->string('nobukti', 30)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('penerimaanstok', 50)->nullable();
            $table->string('penerimaanstok_nobukti', 50)->nullable();
            $table->date('tgldariheadernobuktipenerimaanstok')->nullable();
            $table->date('tglsampaiheadernobuktipenerimaanstok')->nullable();
            $table->string('pengeluaranstok_nobukti', 50)->nullable();
            $table->string('gudang', 50)->nullable();
            $table->string('trado', 50)->nullable();
            $table->string('gandengan', 50)->nullable();
            $table->string('tradodari', 50)->nullable();
            $table->string('tradoke', 50)->nullable();
            $table->string('gandengandari', 50)->nullable();
            $table->string('gandenganke', 50)->nullable();
            $table->string('supplier', 255)->nullable();
            $table->string('nobon', 50)->nullable();
            $table->string('hutang_nobukti', 50)->nullable();
            $table->string('gudangdari', 50)->nullable();
            $table->string('gudangke', 50)->nullable();
            $table->string('coa', 50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->integer('kelompok_id')->length(11)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('gudang_id')->length(11)->nullable();
            $table->integer('gudangdari_id')->length(11)->nullable();
            $table->integer('gudangke_id')->length(11)->nullable();
            $table->integer('penerimaanstok_id')->length(11)->nullable();
            $table->integer('trado_id')->length(11)->nullable();
            $table->integer('tradoke_id')->length(11)->nullable();
            $table->integer('tradodari_id')->length(11)->nullable();
            $table->integer('gandenganke_id')->length(11)->nullable();
            $table->integer('gandengandari_id')->length(11)->nullable();
            $table->integer('gandengan_id')->length(11)->nullable();
            $table->integer('supplier_id')->length(11)->nullable();
        });

        foreach ($data as $row) {

            unset($row['statusformat']);
            unset($row['jumlahcetak']);
            unset($row['statuscetak']);
            unset($row['statusedit']);
            unset($row['parrenttglbukti']);
            unset($row['statuscetak_id']);
            unset($row['statusedit_id']);
            unset($row['statuseditketerangan_id']);
            unset($row['tgldariheaderhutangheader']);
            unset($row['tglsampaiheaderhutangheader']);
            unset($row['tgldariheaderpengeluaranstok']);
            unset($row['tglsampaiheaderpengeluaranstok']);
            unset($row['judul']);
            DB::table($temtabel)->insert($row);
        }

        return $temtabel;
    }

    public function selectColumns($query)
    {
        $po = Parameter::where('grp', 'PO STOK')->where('subgrp', 'PO STOK')->first();
        $penerimaanstok_nobukti = $this->table . ".penerimaanstok_nobukti";
        $tgldaripenerimaanstok_nobukti = db::raw("cast((format(nobuktipenerimaanstok.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheadernobuktipenerimaanstok");
        $tglsampaipenerimaanstok_nobukti = db::raw("cast(cast(format((cast((format(nobuktipenerimaanstok.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheadernobuktipenerimaanstok");
        if (request()->penerimaanheader_id == $po->text) {

            $penerimaanstok_nobukti = "nobuktispb.nobukti as penerimaanstok_nobukti";
            $tgldaripenerimaanstok_nobukti = db::raw("cast((format(nobuktispb.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheadernobuktipenerimaanstok");
            $tglsampaipenerimaanstok_nobukti = db::raw("cast(cast(format((cast((format(nobuktispb.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheadernobuktipenerimaanstok");
        }

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        return $query->select(
            "$this->table.id",
            "$this->table.nobukti",
            "$this->table.tglbukti",
            "penerimaanstok.kodepenerimaan as penerimaanstok",
            $penerimaanstok_nobukti,
            $tgldaripenerimaanstok_nobukti,
            $tglsampaipenerimaanstok_nobukti,
            "$this->table.pengeluaranstok_nobukti",
            "gudangs.gudang as gudang",
            "trado.kodetrado as trado",
            "gandengan.kodegandengan as gandengan",
            "tradodari.kodetrado as tradodari",
            "tradoke.kodetrado as tradoke",
            "gandengandari.kodegandengan as gandengandari",
            "gandenganke.kodegandengan as gandenganke",
            "supplier.namasupplier as supplier",
            "$this->table.nobon",
            "$this->table.hutang_nobukti",
            "dari.gudang as gudangdari",
            "ke.gudang as gudangke",
            "$this->table.statusformat",
            "akunpusat.keterangancoa as coa",
            "$this->table.keterangan",
            "$this->table.modifiedby",
            "$this->table.created_at",
            "$this->table.updated_at",
            "penerimaanstokheader.gudang_id",
            "penerimaanstokheader.gudangdari_id",
            "penerimaanstokheader.gudangke_id",
            "penerimaanstokheader.penerimaanstok_id",
            "penerimaanstokheader.trado_id",
            "penerimaanstokheader.tradoke_id",
            "penerimaanstokheader.tradodari_id",
            "penerimaanstokheader.gandenganke_id",
            "penerimaanstokheader.gandengandari_id",
            "penerimaanstokheader.gandengan_id",
            "penerimaanstokheader.supplier_id",
            "penerimaanstokheader.jumlahcetak",
            "statuscetak.memo as  statuscetak",
            "statusedit.memo as  statusedit",
            "nobuktipenerimaanstok.tglbukti as parrenttglbukti",
            "statuscetak.id as  statuscetak_id",
            "statusedit.id as  statusedit_id",
            "statuseditketerangan.id as  statuseditketerangan_id",
            db::raw("cast((format(hutangheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderhutangheader"),
            db::raw("cast(cast(format((cast((format(hutangheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderhutangheader"),
            db::raw("cast((format(pengeluaranstok.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpengeluaranstok"),
            db::raw("cast(cast(format((cast((format(pengeluaranstok.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpengeluaranstok"),
            db::raw("d1.kelompok_id as kelompok_id"),
            DB::raw("'" . $getJudul->text . "' as judul")
        );
    }

    public function selectColumnPostion()
    {

        $temptable = '##tempget' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temptable, function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->longText('statuscetak')->nullable();
            $table->integer('statuscetak_id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->integer('penerimaanstok_id')->nullable();
            $table->string('penerimaanstok', 50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('penerimaanstok_nobukti', 50)->nullable();
            $table->string('pengeluaranstok_nobukti', 50)->nullable();
            $table->string('gudang', 50)->nullable();
            $table->string('trado', 50)->nullable();
            $table->string('supplier', 200)->nullable();
            $table->string('nobon', 50)->nullable();
            $table->string('hutang_nobukti', 50)->nullable();
            $table->string('gudangdari', 50)->nullable();
            $table->string('gudangke', 50)->nullable();
            $table->string('coa', 200)->nullable();
            $table->string('modifiedby', 200)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $po = Parameter::where('grp', 'PO STOK')->where('subgrp', 'PO STOK')->first();
        $penerimaanstok_nobukti = $this->table . ".penerimaanstok_nobukti";
        if (request()->penerimaanheader_id == $po->text) {
            $penerimaanstok_nobukti = "nobuktispb.nobukti as penerimaanstok_nobukti";
        }

        $query = DB::table('penerimaanstokheader');
        $query->select(
            "penerimaanstokheader.id",
            "statuscetak.text as  statuscetak",
            "statuscetak.id as  statuscetak_id",
            "penerimaanstokheader.nobukti",
            "penerimaanstokheader.tglbukti",
            "penerimaanstokheader.penerimaanstok_id",
            "penerimaanstok.kodepenerimaan as penerimaanstok",
            "penerimaanstokheader.keterangan",
            $penerimaanstok_nobukti,
            "penerimaanstokheader.pengeluaranstok_nobukti",
            "gudangs.gudang as gudang",
            "trado.kodetrado as trado",
            "supplier.namasupplier as supplier",
            "penerimaanstokheader.nobon",
            "penerimaanstokheader.hutang_nobukti",
            "dari.gudang as gudangdari",
            "ke.gudang as gudangke",
            "akunpusat.keterangancoa as coa",
            "penerimaanstokheader.modifiedby",
            "penerimaanstokheader.created_at",
            "penerimaanstokheader.updated_at",
        )
            ->leftJoin('gudang as gudangs', 'penerimaanstokheader.gudang_id', 'gudangs.id')
            ->leftJoin('gudang as dari', 'penerimaanstokheader.gudangdari_id', 'dari.id')
            ->leftJoin('gudang as ke', 'penerimaanstokheader.gudangke_id', 'ke.id')
            ->leftJoin('parameter as statuscetak', 'penerimaanstokheader.statuscetak', 'statuscetak.id')
            ->leftJoin('penerimaanstok', 'penerimaanstokheader.penerimaanstok_id', 'penerimaanstok.id')
            ->leftJoin('akunpusat', 'penerimaanstokheader.coa', 'akunpusat.coa')
            ->leftJoin('trado', 'penerimaanstokheader.trado_id', 'trado.id')
            ->leftJoin('penerimaanstokheader as nobuktipenerimaanstok', 'nobuktipenerimaanstok.nobukti', 'penerimaanstokheader.penerimaanstok_nobukti')
            ->leftJoin('penerimaanstokheader as nobuktispb', 'penerimaanstokheader.nobukti', 'nobuktispb.penerimaanstok_nobukti')
            ->leftJoin('supplier', 'penerimaanstokheader.supplier_id', 'supplier.id');

        DB::table($temptable)->insertUsing([
            'id',
            'statuscetak',
            'statuscetak_id',
            'nobukti',
            'tglbukti',
            'penerimaanstok_id',
            'penerimaanstok',
            'keterangan',
            'penerimaanstok_nobukti',
            'pengeluaranstok_nobukti',
            'gudang',
            'trado',
            'supplier',
            'nobon',
            'hutang_nobukti',
            'gudangdari',
            'gudangke',
            'coa',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $query);
        $query = DB::table($temptable)->from(DB::raw($temptable . " a "))
            ->select(
                'a.id',
                'a.statuscetak',
                'a.statuscetak_id',
                'a.nobukti',
                'a.tglbukti',
                'a.penerimaanstok',
                'a.keterangan',
                'a.penerimaanstok_nobukti',
                'a.pengeluaranstok_nobukti',
                'a.gudang',
                'a.trado',
                'a.supplier',
                'a.nobon',
                'a.hutang_nobukti',
                'a.gudangdari',
                'a.gudangke',
                'a.coa',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',

            );
        return $query;
    }
    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        Schema::create($temp, function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->longText('statuscetak')->nullable();
            $table->integer('statuscetak_id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->string('penerimaanstok', 50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('penerimaanstok_nobukti', 50)->nullable();
            $table->string('pengeluaranstok_nobukti', 50)->nullable();
            $table->string('gudang', 50)->nullable();
            $table->string('trado', 50)->nullable();
            $table->string('supplier', 200)->nullable();
            $table->string('nobon', 50)->nullable();
            $table->string('hutang_nobukti', 50)->nullable();
            $table->string('gudangdari', 50)->nullable();
            $table->string('gudangke', 50)->nullable();
            $table->string('coa', 200)->nullable();
            $table->string('modifiedby', 200)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $query = $this->selectColumnPostion();
        $query = $this->sort($query);
        $models = $this->filter($query);
        if (request()->tgldariheader) {
            $models->whereBetween('a.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        }
        if (request()->penerimaanheader_id) {
            $models->where('a.penerimaanstok_id', request()->penerimaanheader_id);
        }
        DB::table($temp)->insertUsing([
            'a.id',
            'a.statuscetak',
            'a.statuscetak_id',
            'a.nobukti',
            'a.tglbukti',
            'a.penerimaanstok',
            'a.keterangan',
            'a.penerimaanstok_nobukti',
            'a.pengeluaranstok_nobukti',
            'a.gudang',
            'a.trado',
            'a.supplier',
            'a.nobon',
            'a.hutang_nobukti',
            'a.gudangdari',
            'a.gudangke',
            'a.coa',
            'a.modifiedby',
            'a.created_at',
            'a.updated_at',
        ], $models);

        return  $temp;
    }

    public function sort($query)
    {
        return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statuscetak') {
                            if ($filters['data']) {
                                $query = $query->where('a.statuscetak_id', '=', "$filters[data]");
                            }
                            // } else if ($filters['field'] == 'penerimaanstok') {
                            //     $query = $query->where('penerimaanstok.kodepenerimaan', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'gudang') {
                            //     $query = $query->where('gudangs.gudang', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'trado') {
                            //     $query = $query->where('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'gandengan') {
                            //     $query = $query->where('gandengan.kodegandengan', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'supplier') {
                            //     $query = $query->where('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'gudangdari') {
                            //     $query = $query->where('dari.gudang', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'gudangke') {
                            //     $query = $query->where('ke.gudang', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'coa') {
                            //     $query = $query->where('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'tglbukti') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            $query = $query->where('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            // if ($filters['field'] == 'statuscetak') {
                            //     $query = $query->orWhere('statuscetak.text', '=', "$filters[data]");
                            // } else if ($filters['field'] == 'penerimaanstok') {
                            //     $query = $query->orWhere('penerimaanstok.kodepenerimaan', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'gudang') {
                            //     $query = $query->orWhere('gudangs.gudang', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'trado') {
                            //     $query = $query->orWhere('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'gandengan') {
                            //     $query = $query->orwhere('gandengan.kodegandengan', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'supplier') {
                            //     $query = $query->orWhere('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'gudangdari') {
                            //     $query = $query->orWhere('dari.gudang', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'gudangke') {
                            //     $query = $query->orWhere('ke.gudang', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'coa') {
                            //     $query = $query->orWhere('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                            // } else 
                            if ($filters['field'] == 'tglbukti') {
                                $query = $query->orWhereRaw("format( a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });
                    //function query
                    // foreach ($this->params['filters']['rules'] as $index => $filters) {
                    //     if ($filters['field'] == 'penerimaanstok_id_not_null') {
                    //         $query = $query->where($this->table . '.penerimaanstok_id', '=', "$filters[data]")->whereRaw(" $this->table.nobukti NOT IN 
                    //             (SELECT DISTINCT $this->table.penerimaanstok_nobukti
                    //             FROM penerimaanstokheader
                    //             WHERE $this->table.penerimaanstok_nobukti IS NOT NULL)
                    //             ");
                    //     }
                    // }

                    break;
                default:

                    break;
            }

            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }
        if (request()->cetak && request()->periode) {
            $query->where('a.statuscetak', '<>', request()->cetak)
                ->whereYear('a.tglbukti', '=', request()->year)
                ->whereMonth('a.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function find($id)
    {
        $this->setRequestParameters();
        $temtabelPg = '##temppg' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;
        Schema::create($temtabelPg, function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->integer('stok_id')->nullable();
            $table->integer('kelompok_id')->nullable();
        });
        $queryPg = DB::table($this->table)->select(
            DB::raw("'' as id"),
            DB::raw("'' as nobukti"),
            DB::raw("'' as stok_id"),
            DB::raw("'' as kelompok_id"),
        )->take(1);

        DB::table($temtabelPg)->insertUsing([
            'id',
            'nobukti',
            'stok_id',
            'kelompok_id',
        ], $queryPg);
        $queryTemtabelPg = DB::table($temtabelPg);

        $query = DB::table($this->table);
        $query = $this->selectColumns($query)
            ->leftJoin('gudang as gudangs', 'penerimaanstokheader.gudang_id', 'gudangs.id')
            ->leftJoin('gudang as dari', 'penerimaanstokheader.gudangdari_id', 'dari.id')
            ->leftJoin('gudang as ke', 'penerimaanstokheader.gudangke_id', 'ke.id')
            ->leftJoin('parameter as statuscetak', 'penerimaanstokheader.statuscetak', 'statuscetak.id')
            ->leftJoin('parameter as statusedit', 'penerimaanstokheader.statusapprovaledit', 'statusedit.id')
            ->leftJoin('parameter as statuseditketerangan', 'penerimaanstokheader.statusapprovaleditketerangan', 'statuseditketerangan.id')
            ->leftJoin('trado as tradodari ', 'penerimaanstokheader.tradodari_id', 'tradodari.id')
            ->leftJoin('trado as tradoke ', 'penerimaanstokheader.tradoke_id', 'tradoke.id')
            ->leftJoin('akunpusat', 'penerimaanstokheader.coa', 'akunpusat.coa')
            ->leftJoin('gandengan as gandengandari ', 'penerimaanstokheader.gandengandari_id', 'gandengandari.id')
            ->leftJoin('gandengan as gandenganke ', 'penerimaanstokheader.gandenganke_id', 'gandenganke.id')
            ->leftJoin('gandengan as gandengan ', 'penerimaanstokheader.gandengan_id', 'gandengan.id')
            ->leftJoin('penerimaanstok', 'penerimaanstokheader.penerimaanstok_id', 'penerimaanstok.id')
            ->leftJoin('trado', 'penerimaanstokheader.trado_id', 'trado.id')
            ->leftJoin('hutangheader', 'penerimaanstokheader.hutang_nobukti', 'hutangheader.nobukti')
            ->leftJoin('pengeluaranstokheader as pengeluaranstok', 'penerimaanstokheader.pengeluaranstok_nobukti', 'pengeluaranstok.nobukti')
            ->leftJoin('penerimaanstokheader as nobuktipenerimaanstok', 'nobuktipenerimaanstok.nobukti', 'penerimaanstokheader.penerimaanstok_nobukti')
            ->leftJoin('penerimaanstokheader as nobuktispb', 'penerimaanstokheader.nobukti', 'nobuktispb.penerimaanstok_nobukti')
            ->leftJoin(db::raw($temtabelPg . " d1"), "penerimaanstokheader.id", "d1.id")

            ->leftJoin('supplier', 'penerimaanstokheader.supplier_id', 'supplier.id');
        $data = $query->where("$this->table.id", $id)->first();


        return $data;
    }

    public function getDetailPengeluaran($id)
    {
        $penerimaan = PenerimaanStokHeader::findOrFail($id);
        $penerimaanstokdetail = DB::table('penerimaanstokdetail')
            ->select(
                "penerimaanstokdetail.penerimaanstokheader_id",
                "penerimaanstokdetail.nobukti",
                "stok.namastok as stok",
                "pengeluaranstokdetail.qty as maximum",
                "penerimaanstokdetail.stok_id",
                "penerimaanstokdetail.qty",
                "penerimaanstokdetail.harga",
                "penerimaanstokdetail.persentasediscount",
                "penerimaanstokdetail.nominaldiscount",
                "penerimaanstokdetail.total",
                "penerimaanstokdetail.keterangan",
                "penerimaanstokdetail.vulkanisirke",
                "penerimaanstokdetail.modifiedby",
            )
            ->leftJoin('penerimaanstokheader', 'penerimaanstokdetail.penerimaanstokheader_id', 'penerimaanstokheader.id')
            ->leftJoin('pengeluaranstokdetail', 'penerimaanstokheader.pengeluaranstok_nobukti', 'pengeluaranstokdetail.nobukti')
            ->leftJoin("stok", "penerimaanstokdetail.stok_id", "stok.id")
            ->where('penerimaanstokdetail.penerimaanstokheader_id', $penerimaan->id)
            ->whereRaw('penerimaanstokdetail.stok_id = pengeluaranstokdetail.stok_id')
            ->get();

        $data = $penerimaanstokdetail;
        return $data;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function processStore(array $data): PenerimaanStokHeader
    {
        $idpenerimaan = $data['penerimaanstok_id'];
        $fetchFormat =  PenerimaanStok::where('id', $idpenerimaan)->first();
        $statusformat = $fetchFormat->format;
        $fetchGrp = Parameter::where('id', $statusformat)->first();
        $group = $fetchGrp->grp;
        $subGroup = $fetchGrp->subgrp;
        $statusformat = $fetchFormat->format;
        $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        $jamBatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', 'JAMBATASAPPROVAL')->where('subgrp', 'JAMBATASAPPROVAL')->first();
        $tglbatasedit = date('Y-m-d H:i:s', strtotime(date('Y-m-d') . ' ' . $jamBatas->text));
        $gudangkantor =  Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first()->text;
        $gudangsementara =  Parameter::where('grp', 'GUDANG SEMENTARA')->where('subgrp', 'GUDANG SEMENTARA')->first()->text;


        $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();
        $pg = Parameter::where('grp', 'PG STOK')->where('subgrp', 'PG STOK')->first();
        $do = Parameter::where('grp', 'DO STOK')->where('subgrp', 'DO STOK')->first();
        $spbs = Parameter::where('grp', 'REUSE STOK')->where('subgrp', 'REUSE STOK')->first();
        $pst = Parameter::where('grp', 'PST STOK')->where('subgrp', 'PST STOK')->first();
        $pspk = Parameter::where('grp', 'PSPK STOK')->where('subgrp', 'PSPK STOK')->first();
        $korv = DB::table('penerimaanstok')->where('kodepenerimaan', 'KORV')->first();
        $spbp = DB::table('penerimaanstok')->where('kodepenerimaan', 'SPBP')->first();
        $pst = Parameter::where('grp', 'PENGEMBALIAN SPAREPART STOK')->where('subgrp', 'PENGEMBALIAN SPAREPART STOK')->first();
        
        

        $datahitungstok = PenerimaanStok::select('statushitungstok as statushitungstok_id')->where('format', '=', $statusformat)->first();
        $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();
        $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

        if ($data['penerimaanstok_id'] == $spb->text) {
            $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
            $data['gudang_id'] = $gudangkantor->text;
        }
        if ($data['penerimaanstok_id'] == $pst->text) {
            $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
            $data['gudang_id'] = $gudangkantor->text;
        }
        if ($data['penerimaanstok_id'] == $pspk->text) {
            $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
            $data['gudang_id'] = $gudangkantor->text;
        }
        if ($data['penerimaanstok_id'] == $korv->id) {
            $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
            $data['gudang_id'] = $gudangkantor->text;
        }
        $gudangdari_id = $data['gudangdari_id'];
        $gudangke_id = $data['gudangke_id'];
        $tradodari_id = $data['tradodari_id'];
        $tradoke_id = $data['tradoke_id'];
        $gandengandari_id = $data['gandengandari_id'];
        $gandenganke_id = $data['gandenganke_id'];
        $gdgkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();

        if ($data['penerimaanstok_id'] !== $pg->text) {
            $statuspindahgudang = Parameter::where('grp', 'STATUS PINDAH GUDANG')->where('text', 'BUKAN PINDAH GUDANG')->first();
            if ($data['penerimaanstok_id'] === $do->text) {
                $statuspindahgudang = Parameter::where('grp', 'STATUS PINDAH GUDANG')->where('text', 'GUDANG KE GUDANG')->first();
                $gudangdari_id = Gudang::where('gudang', 'GUDANG SEMENTARA')->first()->id;
                $gudangke_id = Gudang::where('gudang', 'GUDANG PIHAK III')->first()->id;
            }
            if ($data['penerimaanstok_id'] === $spbs->text) {
                $statuspindahgudang = Parameter::where('grp', 'STATUS PINDAH GUDANG')->where('text', 'GUDANG KE GUDANG')->first();
                $gudangdari_id = Gudang::where('gudang', 'GUDANG PIHAK III')->first()->id;
                $gudangke_id = Gudang::where('gudang', 'GUDANG KANTOR')->first()->id;
            }
            // if ($data['penerimaanstok_id'] === $pst->text) {
            //     $pengeluaranStokHeader = PengeluaranStokHeader::where('nobukti',$data['pengeluaranstok_nobukti'])->first();
            //     $dari = PenerimaanStokDetail::persediaan($pengeluaranStokHeader->gudang_id,$pengeluaranStokHeader->trado_id,$pengeluaranStokHeader->gandengan_id);
            //     $statuspindahgudang = Parameter::where('grp', 'STATUS PINDAH GUDANG')->where("text", $dari['nama']." ke GUDANG")->first();
            //     dd($pengeluaranStokHeader);
            // }

        } else {
            $dari = PenerimaanStokDetail::persediaan($data['gudangdari_id'], $data['tradodari_id'], $data['gandengandari_id']);
            $ke = PenerimaanStokDetail::persediaan($data['gudangke_id'], $data['tradoke_id'], $data['gandenganke_id']);
            $statuspindahgudang = Parameter::where('grp', 'STATUS PINDAH GUDANG')->where("text", $dari['nama'] . " ke " . $ke['nama'])->first();
        }

        $penerimaanStokHeader = new PenerimaanStokHeader();
        $penerimaanStokHeader->tglbukti                 = date('Y-m-d', strtotime($data['tglbukti']));
        $penerimaanStokHeader->penerimaanstok_nobukti   = ($data['penerimaanstok_nobukti'] == null) ? "" : $data['penerimaanstok_nobukti'];
        $penerimaanStokHeader->pengeluaranstok_nobukti  = ($data['pengeluaranstok_nobukti'] == null) ? "" : $data['pengeluaranstok_nobukti'];
        $penerimaanStokHeader->nobon                    = ($data['nobon'] == null) ? "" : $data['nobon'];
        $penerimaanStokHeader->keterangan               = $data['keterangan'];
        $penerimaanStokHeader->coa                      = ($data['coa'] == null) ? "" : $data['coa'];
        $penerimaanStokHeader->statusformat             = $statusformat;
        $penerimaanStokHeader->penerimaanstok_id        = $data['penerimaanstok_id'];
        $penerimaanStokHeader->gudang_id                = $data['gudang_id'];
        $penerimaanStokHeader->trado_id                 = $data['trado_id'];
        $penerimaanStokHeader->gandengan_id             = $data['gandengan_id'];
        $penerimaanStokHeader->supplier_id              = $data['supplier_id'];
        $penerimaanStokHeader->gudangdari_id            = $gudangdari_id;
        $penerimaanStokHeader->gudangke_id              = $gudangke_id;
        $penerimaanStokHeader->tradodari_id             = $tradodari_id;
        $penerimaanStokHeader->tradoke_id               = $tradoke_id;
        $penerimaanStokHeader->gandengandari_id         = $gandengandari_id;
        $penerimaanStokHeader->gandenganke_id           = $gandenganke_id;
        $penerimaanStokHeader->statuspindahgudang       = ($statuspindahgudang == null) ? "" : $statuspindahgudang->id;
        $penerimaanStokHeader->modifiedby               = auth('api')->user()->name;
        $penerimaanStokHeader->info = html_entity_decode(request()->info);
        $penerimaanStokHeader->statuscetak              = $statusCetak->id;
        $penerimaanStokHeader->tglbatasedit             = $tglbatasedit;
        $data['sortname']                               = $data['sortname'] ?? 'id';
        $data['sortorder']                              = $data['sortorder'] ?? 'asc';

        $penerimaanStokHeader->nobukti                  = (new RunningNumberService)->get($group, $subGroup, $penerimaanStokHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$penerimaanStokHeader->save()) {
            throw new \Exception("Error storing penerimaan Stok Header.");
        }

        $masukgudang_id = 0;
        $masuktrado_id = 0;
        $masukgandengan_id = 0;
        $keluargudang_id = 0;
        $keluartrado_id = 0;
        $keluargandengan_id = 0;

        $penerimaanstok_id = $data['penerimaanstok_id'] ?? 0;

        $urutfifo = db::table("penerimaanstok")->from(db::raw("penerimaanstok as a with (readuncommitted)"))
            ->select('a.urutfifo')->where('a.id', $penerimaanstok_id)->first()->urutfifo ?? 0;


        // dd($gudangke_id);
        $masukgudang_id = $data['gudang_id'] ?? 0;
        $masuktrado_id = $data['trado_id'] ?? 0;
        $masukgandengan_id = $data['gandengan_id'] ?? 0;
        if ($gudangke_id != 0) {
            $masukgudang_id = $gudangke_id ?? 0;
        }
        if ($gudangdari_id != 0) {
            $keluargudang_id = $gudangdari_id ?? 0;
        }


        if ($tradoke_id != 0) {
            $masuktrado_id = $tradoke_id ?? 0;
        }

        if ($tradodari_id != 0) {
            $keluartrado_id = $tradodari_id ?? 0;
        }


        if ($gandenganke_id != 0) {
            $masukgandengan_id = $gandenganke_id ?? 0;
        }

        if ($gandengandari_id != 0) {
            $keluargandengan_id = $gandengandari_id ?? 0;
        }

        // if ($penerimaanstok_id == 1) {
        //     $masukgudang_id = $data['gudangke_id'] ?? 0;
        // }
        // if ($penerimaanstok_id == 6) {
        //     $masukgudang_id = $data['gudangke_id'] ?? 0;
        // }


        /*STORE DETAIL*/
        $penerimaanStokDetails = [];
        $totalharga = 0;
        $detaildata = [];
        $tgljatuhtempo = [];
        $keterangan_detail = [];

        $coadebet_detail = [];
        $coakredit_detail = [];
        $nominal_detail = [];

        for ($i = 0; $i < count($data['detail_harga']); $i++) {
            $ksqty = $data['detail_qty'][$i] ?? 0;
            $ksnilai = $data['totalItem'][$i] ?? 0;
            $penerimaanStokDetail = (new PenerimaanStokDetail())->processStore($penerimaanStokHeader, [
                "penerimaanstokheader_id" => $penerimaanStokHeader->id,
                "nobukti" => $penerimaanStokHeader->nobukti,
                "stok_id" => $data['detail_stok_id'][$i],
                "qty" => $data['detail_qty'][$i],
                // "qtyterpakai" => $data['detail_qtyterpakai'][$i],
                "harga" => $data['detail_harga'][$i],
                "totalItem" => $data['totalItem'][$i],
                "totalsebelum" => $data['totalsebelum'][$i] ?? 0,
                "persentasediscount" => $data['detail_persentasediscount'][$i],
                "vulkanisirke" => $data['detail_vulkanisirke'][$i],
                "detail_keterangan" => $data['detail_keterangan'][$i],
                "detail_penerimaanstoknobukti" => $data['detail_penerimaanstoknobukti'][$i],
            ]);
            //update total vulkanisir
            $reuse = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select('a.id')
                ->where('grp', 'STATUS REUSE')
                ->where('subgrp', 'STATUS REUSE')
                ->where('text', 'REUSE')
                ->first()->id ?? 0;

            $stokreuse = db::table("stok")->from(db::raw("stok a with (readuncommitted)"))
                ->select(
                    'a.id',
                    db::raw("isnull(a.vulkanisirawal,0) as vulawal"),
                )
                ->where('a.id', $data['detail_stok_id'][$i])
                ->where('a.statusreuse', $reuse)
                ->first();

            if (isset($stokreuse)) {

                $queryvulkan = db::table("stok")->from(db::raw("stok a with (readuncommitted)"))
                    ->select(
                        db::raw("sum(isnull(b.vulkanisirke,0)) as vulkanplus"),
                        db::raw("sum(isnull(c.vulkanisirke,0)) as vulkanminus")
                    )
                    ->leftjoin(db::raw("penerimaanstokdetail b with (readuncommitted)"), 'a.id', 'b.stok_id')
                    ->leftjoin(db::raw("pengeluaranstokdetail c with (readuncommitted)"), 'a.id', 'c.stok_id')
                    ->where('a.id', $data['detail_stok_id'][$i])
                    ->groupby('a.id')
                    ->first();

                $totalplus = $queryvulkan->vulkanplus ?? 0;
                $totalminus = $queryvulkan->vulkanminus ?? 0;
                $vulawal = $stokreuse->vulawal ?? 0;
                $total = ($totalplus + $vulawal) - $totalminus;

                if (isset($queryvulkan)) {
                    $totalvulkan = $total ?? 0;
                } else {
                    $totalvulkan = 0;
                }
                $datastok  = Stok::lockForUpdate()->where("id", $data['detail_stok_id'][$i])
                    ->firstorFail();
                if ($data['penerimaanstok_id'] == $korv->id) {
                    $datastok->totalvulkanisir = $totalvulkan;
                    $datastok->statusban = ($data['detail_statusban']) ? $data['detail_statusban'][$i] : null;
                    $datastok->save();
                }
            }
            // end update vulkanisir
            // dd($masukgudang_id.' '. $masuktrado_id.' '. $masukgandengan_id , $keluargudang_id .' '. $keluartrado_id .' '. $keluargandengan_id);
            if ($penerimaanstok_id != 2 && $penerimaanstok_id != 10  && $penerimaanstok_id != 11) {
                if ($masukgudang_id != 0 || $masuktrado_id != 0  || $masukgandengan_id != 0) {
                    // dd('test');
                    if ($masukgudang_id == $gdgkantor->text) {
                        $kartuStok = (new KartuStok())->processStore([
                            "gudang_id" => $masukgudang_id,
                            "trado_id" => $masuktrado_id,
                            "gandengan_id" => $masukgandengan_id,
                            "stok_id" => $data['detail_stok_id'][$i],
                            "nobukti" => $penerimaanStokHeader->nobukti,
                            "tglbukti" => date('Y-m-d', strtotime($data['tglbukti'])),
                            "qtymasuk" => $ksqty ?? 0,
                            "nilaimasuk" => $ksnilai ?? 0,
                            "qtykeluar" => 0,
                            "nilaikeluar" => 0,
                            "urutfifo" => $urutfifo,
                        ]);
                    } else {
                        $kartuStok = (new KartuStok())->processStore([
                            "gudang_id" => $masukgudang_id,
                            "trado_id" => $masuktrado_id,
                            "gandengan_id" => $masukgandengan_id,
                            "stok_id" => $data['detail_stok_id'][$i],
                            "nobukti" => $penerimaanStokHeader->nobukti,
                            "tglbukti" => date('Y-m-d', strtotime($data['tglbukti'])),
                            "qtymasuk" => $ksqty ?? 0,
                            "nilaimasuk" => 0,
                            "qtykeluar" => 0,
                            "nilaikeluar" => 0,
                            "urutfifo" => $urutfifo,
                        ]);
                    }
                }

                if ($keluargudang_id != 0 || $keluartrado_id != 0  || $keluargandengan_id != 0) {
                    if ($penerimaanstok_id == 6) {
                        if ($keluargudang_id == $gdgkantor->text) {
                            $kartuStok = (new KartuStok())->processStore([
                                "gudang_id" => $keluargudang_id,
                                "trado_id" => $keluartrado_id,
                                "gandengan_id" => $keluargandengan_id,
                                "stok_id" => $data['detail_stok_id'][$i],
                                "nobukti" => $penerimaanStokHeader->nobukti,
                                "tglbukti" => date('Y-m-d', strtotime($data['tglbukti'])),
                                "qtymasuk" => 0,
                                "nilaimasuk" => 0,
                                "qtykeluar" => $ksqty ?? 0,
                                "nilaikeluar" => $ksnilai ?? 0,
                                "urutfifo" => $urutfifo,
                            ]);
                        } else {
                            $kartuStok = (new KartuStok())->processStore([
                                "gudang_id" => $keluargudang_id,
                                "trado_id" => $keluartrado_id,
                                "gandengan_id" => $keluargandengan_id,
                                "stok_id" => $data['detail_stok_id'][$i],
                                "nobukti" => $penerimaanStokHeader->nobukti,
                                "tglbukti" => date('Y-m-d', strtotime($data['tglbukti'])),
                                "qtymasuk" => 0,
                                "nilaimasuk" => 0,
                                "qtykeluar" => $ksqty ?? 0,
                                "nilaikeluar" => 0,
                                "urutfifo" => $urutfifo,
                            ]);
                        }
                    } else {
                        if ($keluargudang_id == $gdgkantor->text) {
                            if ($gudangke_id != $gudangsementara) {
                                $kartuStok = (new KartuStok())->processStore([
                                    "gudang_id" => $keluargudang_id,
                                    "trado_id" => $keluartrado_id,
                                    "gandengan_id" => $keluargandengan_id,
                                    "stok_id" => $data['detail_stok_id'][$i],
                                    "nobukti" => $penerimaanStokHeader->nobukti,
                                    "tglbukti" => date('Y-m-d', strtotime($data['tglbukti'])),
                                    "qtymasuk" => 0,
                                    "nilaimasuk" => 0,
                                    "qtykeluar" => $ksqty ?? 0,
                                    "nilaikeluar" => $ksnilai ?? 0,
                                    "urutfifo" => $urutfifo,
                                ]);
                            }
                        } else {
                            $kartuStok = (new KartuStok())->processStore([
                                "gudang_id" => $keluargudang_id,
                                "trado_id" => $keluartrado_id,
                                "gandengan_id" => $keluargandengan_id,
                                "stok_id" => $data['detail_stok_id'][$i],
                                "nobukti" => $penerimaanStokHeader->nobukti,
                                "tglbukti" => date('Y-m-d', strtotime($data['tglbukti'])),
                                "qtymasuk" => 0,
                                "nilaimasuk" => 0,
                                "qtykeluar" => $ksqty ?? 0,
                                "nilaikeluar" => 0,
                                "urutfifo" => $urutfifo,
                            ]);
                        }
                    }
                }
            }

            // dd('test');


            if ($data['penerimaanstok_id'] == $spbp->id) {
                $penambahanNilai = (new PenerimaanStokPenambahanNilai())->processStore($penerimaanStokHeader, [
                    "penerimaanstokheader_id" => $penerimaanStokHeader->id,
                    "nobukti" => $penerimaanStokHeader->nobukti,
                    "stok_id" => $data['detail_stok_id'][$i],
                    "qty" => $data['detail_qty'][$i],
                    "harga" => $data['detail_harga'][$i],
                    "penerimaanstok_nobukti" => $penerimaanStokHeader->penerimaanstok_nobukti,
                ]);
            }

            if (($gudangdari_id == $gudangkantor) && ($gudangke_id == $gudangsementara)) {

                $datadetailfifo = [
                    "penerimaanstokheader_id" => $penerimaanStokHeader->id,
                    "penerimaanstok_id" => $data['penerimaanstok_id'],
                    "nobukti" => $penerimaanStokHeader->nobukti,
                    "stok_id" => $data['detail_stok_id'][$i],
                    "gudang_id" => $gudangdari_id,
                    "tglbukti" => $data['tglbukti'],
                    "qty" => $data['detail_qty'][$i],
                    "modifiedby" => auth('api')->user()->name,
                    "keterangan" => $data['keterangan'] ?? '',
                    "detail_keterangan" => $data['detail_keterangan'][$i] ?? '',
                    "statusformat" => $statusformat,
                ];

                (new PenerimaanStokDetailFifo())->processStore($penerimaanStokHeader, $datadetailfifo);

                $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'DEBET')->first();
                $memo = json_decode($getCoaDebet->memo, true);
                $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'KREDIT')->first();
                $memokredit = json_decode($getCoaKredit->memo, true);

                $coadebet_detail[] = $memo['JURNAL'];
                $coakredit_detail[] = $memokredit['JURNAL'];
                // $nominal_detail[] = $penerimaanStokDetail->total;
                $keterangan_detail[] = $data['detail_keterangan'][$i];
                $penerimaanStokDetail = penerimaanStokDetail::where('id', $penerimaanStokDetail->id)->first();

                $nominal_detail[] = $penerimaanStokDetail->total;



                $jurnalRequest = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $penerimaanStokHeader->nobukti,
                    'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'postingdari' => "ENTRY PENERIMAAN STOK ($fetchFormat->kodepenerimaan)",
                    'statusapproval' => $statusApproval->id,
                    'userapproval' => "",
                    'tglapproval' => "",
                    'modifiedby' => auth('api')->user()->name,
                    'statusformat' => "0",
                    'coakredit_detail' => $coakredit_detail,
                    'coadebet_detail' => $coadebet_detail,
                    'nominal_detail' => $nominal_detail,
                    'keterangan_detail' => $keterangan_detail
                ];

                $jurnalUmumHeader = (new JurnalUmumHeader())->processStore($jurnalRequest);
            }



            if ($data['penerimaanstok_id'] == $spb->text || $data['penerimaanstok_id'] == $spbs->text) {
                // $totalsat = ($data['detail_qty'][$i] * $data['detail_harga'][$i]);
                $totalsat = $data['totalItem'][$i];
                $totalharga += $totalsat;
                $detaildata[] = $totalsat;
                $tgljatuhtempo[] =  $data['tglbukti'];
                $keterangan_detail[] =  $data['detail_keterangan'][$i];
            }
            $penerimaanStokDetails[] = $penerimaanStokDetail->toArray();
        }



        /*STORE HUTANG IF SPB*/
        if ($data['penerimaanstok_id'] == $spb->text || $data['penerimaanstok_id'] == $spbs->text) {


            $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL HUTANG PEMBELIAN STOK')->where('subgrp', 'DEBET')->first();
            $memoDebet = json_decode($getCoaDebet->memo, true);

            $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL HUTANG PEMBELIAN STOK')->where('subgrp', 'KREDIT')->first();
            $memoKredit = json_decode($getCoaKredit->memo, true);

            $hutangRequest = [
                'proseslain' => 'PEMBELIAN STOK',
                'postingdari' => "PENERIMAAN STOK ($fetchFormat->kodepenerimaan)",
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'coa' => $memoDebet['JURNAL'],
                'supplier_id' => ($data['supplier_id'] == null) ? "" : $data['supplier_id'],
                'modifiedby' => auth('api')->user()->name,
                'total' => $totalharga,
                'coadebet' => $memoDebet['JURNAL'],
                'coakredit' => $memoKredit['JURNAL'],
                'tgljatuhtempo' => $tgljatuhtempo,
                'total_detail' => $detaildata,
                'keterangan_detail' => $keterangan_detail,
            ];

            $hutangHeader = (new HutangHeader())->processStore($hutangRequest);
            $penerimaanStokHeader->hutang_nobukti = $hutangHeader->nobukti;
            $penerimaanStokHeader->save();
        } else if (($data['penerimaanstok_id'] == $pst->text) || ($data['penerimaanstok_id'] == $pspk->text)) {

            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

            $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'DEBET')->first();
            $memokredit = json_decode($getCoaKredit->memo, true);
            $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'KREDIT')->first();
            $memo = json_decode($getCoaDebet->memo, true);
            $isPostJurnal = false;
            for ($i = 0; $i < count($data['detail_harga']); $i++) {
                // $totalsat = ($data['detail_qty'][$i] * $data['detail_harga'][$i]);
                $totalsat = $data['totalItem'][$i];
                $coakredit_detail[] = $memokredit['JURNAL'];
                $coadebet_detail[] = $memo['JURNAL'];
                $nominal_detail[] = ceil($totalsat);
                $keterangan_detail[] = $data['detail_keterangan'][$i];

                if ($totalsat) {
                    $isPostJurnal = true;
                }
            }

            /*STORE JURNAL*/
            $jurnalRequest = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $penerimaanStokHeader->nobukti,
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'postingdari' => "ENTRY PENERIMAAN STOK ($fetchFormat->kodepenerimaan)",
                'statusapproval' => $statusApproval->id,
                'userapproval' => "",
                'tglapproval' => "",
                'modifiedby' => auth('api')->user()->name,
                'statusformat' => "0",
                'coakredit_detail' => $coakredit_detail,
                'coadebet_detail' => $coadebet_detail,
                'nominal_detail' => $nominal_detail,
                'keterangan_detail' => $keterangan_detail
            ];
            if ($isPostJurnal) {
                $jurnalUmumHeader = (new JurnalUmumHeader())->processStore($jurnalRequest);
            }
        }

        $penerimaanStokHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerimaanStokHeader->getTable()),
            'postingdari' => "ENTRY PENERIMAAN STOK ($fetchFormat->kodepenerimaan)",
            'idtrans' => $penerimaanStokHeader->id,
            'nobuktitrans' => $penerimaanStokHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $penerimaanStokHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerimaanStokDetail->getTable()),
            'postingdari' => "PENERIMAAN STOK ($fetchFormat->kodepenerimaan",
            'idtrans' =>  $penerimaanStokHeaderLogTrail->id,
            'nobuktitrans' => $penerimaanStokHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $penerimaanStokDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        //  if ($data['penerimaanstok_id'] == $pst->id) {
        //     $datapst = [
        //         "tglbukti" => $request->tglbukti,
        //         "pengeluaranstok" => $request->pengeluaranstok,
        //         "pengeluaranstok_id" => $request->pengeluaranstok_id,
        //         "penerimaanstok_nobukti" => $request->penerimaanstok_nobukti,
        //         "pengeluaranstok_nobukti" => $request->pengeluaranstok_nobukti,
        //         "pengeluarantrucking_nobukti" => $request->pengeluarantrucking_nobukti,
        //         "supplier" => $request->supplier,
        //         "supplier_id" => $request->supplier_id,
        //         "kerusakan" => $request->kerusakan,
        //         "kerusakan_id" => $request->kerusakan_id,
        //         "supir" => $request->supir,
        //         "supir_id" => $request->supir_id,
        //         "servicein_nobukti" => $request->servicein_nobukti,
        //         "trado" => $request->trado,
        //         "trado_id" => $request->trado_id,
        //         "gudang" => $request->gudang,
        //         "gudang_id" => $request->gudang_id,
        //         "gandengan" => $request->gandengan,
        //         "gandengan_id" => $request->gandengan_id,
        //         "statuspotongretur" => $request->statuspotongretur,
        //         "bank" => $request->bank,
        //         "bank_id" => $request->bank_id,
        //         "tglkasmasuk" => $request->tglkasmasuk,
        //         "penerimaan_nobukti" => $request->penerimaan_nobukti,

        //         "detail_stok" => $request->detail_stok,
        //         "detail_stok_id" => $request->detail_stok_id,
        //         "jlhhari" => $request->jlhhari,
        //         "detail_statusoli" => $request->detail_statusoli,
        //         "detail_vulkanisirke" => $request->detail_vulkanisirke,
        //         "detail_keterangan" => $request->detail_keterangan,
        //         "detail_statusban" => ($request->statusban) ? $request->statusban : $request->detail_statusban,
        //         "detail_qty" => $request->detail_qty ?? $request->qty_afkir,
        //         "detail_harga" => $request->detail_harga,
        //         "detail_persentasediscount" => $request->detail_persentasediscount,
        //         "totalItem" => $request->totalItem,
        //     ];
        //     $pengeluaranStokHeader = (new PengeluaranStokHeader())->processStore($datapst);
        //  }

        return $penerimaanStokHeader;
    }

    public function processUpdate(PenerimaanStokHeader $penerimaanStokHeader, array $data): PenerimaanStokHeader
    {
        /*STORE HEADER*/

        $idpenerimaan = $data['penerimaanstok_id'];
        $fetchFormat =  PenerimaanStok::where('id', $idpenerimaan)->first();
        $statusformat = $fetchFormat->format;

        $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        $gdgkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();

        $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();
        $pg = Parameter::where('grp', 'PG STOK')->where('subgrp', 'PG STOK')->first();
        $do = Parameter::where('grp', 'DO STOK')->where('subgrp', 'DO STOK')->first();
        $spbs = Parameter::where('grp', 'REUSE STOK')->where('subgrp', 'REUSE STOK')->first();
        $kor = Parameter::where('grp', 'KOR STOK')->where('subgrp', 'KOR STOK')->first();
        $pst = Parameter::where('grp', 'PST STOK')->where('subgrp', 'PST STOK')->first();
        $pspk = Parameter::where('grp', 'PSPK STOK')->where('subgrp', 'PSPK STOK')->first();
        $korv = DB::table('penerimaanstok')->where('kodepenerimaan', 'KORV')->first();
        $spbp = DB::table('penerimaanstok')->where('kodepenerimaan', 'SPBP')->first();
        $gudangkantor =  Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first()->text;
        $gudangsementara =  Parameter::where('grp', 'GUDANG SEMENTARA')->where('subgrp', 'GUDANG SEMENTARA')->first()->text;


        $datahitungstok = PenerimaanStok::select('statushitungstok as statushitungstok_id')->where('format', '=', $statusformat)->first();
        $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();
        $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();


        if ($data['penerimaanstok_id'] == $spb->text) {
            $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
            $data['gudang_id'] = $gudangkantor->text;
        }
        if ($data['penerimaanstok_id'] == $pst->text) {
            $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
            $data['gudang_id'] = $gudangkantor->text;
        }
        if ($data['penerimaanstok_id'] == $pspk->text) {
            $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
            $data['gudang_id'] = $gudangkantor->text;
        }

        $gudangdari_id = $data['gudangdari_id'];
        $gudangke_id = $data['gudangke_id'];
        $tradodari_id = $data['tradodari_id'];
        $tradoke_id = $data['tradoke_id'];
        $gandengandari_id = $data['gandengandari_id'];
        $gandenganke_id = $data['gandenganke_id'];




        if ($data['penerimaanstok_id'] !== $pg->text) {
            $statuspindahgudang = Parameter::where('grp', 'STATUS PINDAH GUDANG')->where('text', 'BUKAN PINDAH GUDANG')->first();
            if ($data['penerimaanstok_id'] === $do->text) {
                $statuspindahgudang = Parameter::where('grp', 'STATUS PINDAH GUDANG')->where('text', 'GUDANG KE GUDANG')->first();
                $gudangdari_id = Gudang::where('gudang', 'GUDANG SEMENTARA')->first()->id;
                $gudangke_id = Gudang::where('gudang', 'GUDANG PIHAK III')->first()->id;
            }
            if ($data['penerimaanstok_id'] === $spbs->text) {
                $statuspindahgudang = Parameter::where('grp', 'STATUS PINDAH GUDANG')->where('text', 'GUDANG KE GUDANG')->first();
                $gudangdari_id = Gudang::where('gudang', 'GUDANG PIHAK III')->first()->id;
                $gudangke_id = Gudang::where('gudang', 'GUDANG KANTOR')->first()->id;
            }
        } else {

            $dari = PenerimaanStokDetail::persediaan($data['gudangdari_id'], $data['tradodari_id'], $data['gandengandari_id']);

            $ke = PenerimaanStokDetail::persediaan($data['gudangke_id'], $data['tradoke_id'], $data['gandenganke_id']);
            $statuspindahgudang = Parameter::where('grp', 'STATUS PINDAH GUDANG')->where("text", $dari['nama'] . " ke " . $ke['nama'])->first();
        }

        $penerimaanStokHeader->penerimaanstok_nobukti   = ($data['penerimaanstok_nobukti'] == null) ? "" : $data['penerimaanstok_nobukti'];
        $penerimaanStokHeader->pengeluaranstok_nobukti  = ($data['pengeluaranstok_nobukti'] == null) ? "" : $data['pengeluaranstok_nobukti'];
        $penerimaanStokHeader->nobon                    = ($data['nobon'] == null) ? "" : $data['nobon'];
        $penerimaanStokHeader->keterangan               = ($data['keterangan'] == null) ? "" : $data['keterangan'];
        $penerimaanStokHeader->coa                      = ($data['coa'] == null) ? "" : $data['coa'];
        $penerimaanStokHeader->statusformat             = $statusformat;
        $penerimaanStokHeader->penerimaanstok_id        = ($data['penerimaanstok_id'] == null) ? "" : $data['penerimaanstok_id'];
        $penerimaanStokHeader->gudang_id                = $data['gudang_id'];
        $penerimaanStokHeader->trado_id                 = $data['trado_id'];
        $penerimaanStokHeader->supplier_id              = $data['supplier_id'];
        $penerimaanStokHeader->gandengan_id             = $data['gandengan_id'];
        $penerimaanStokHeader->gudangdari_id            = $gudangdari_id;
        $penerimaanStokHeader->gudangke_id              = $gudangke_id;
        $penerimaanStokHeader->tradodari_id             = $tradodari_id;
        $penerimaanStokHeader->tradoke_id               = $tradoke_id;
        $penerimaanStokHeader->gandengandari_id         = $gandengandari_id;
        $penerimaanStokHeader->gandenganke_id           = $gandenganke_id;
        $penerimaanStokHeader->statuspindahgudang       = ($statuspindahgudang == null) ? "" : $statuspindahgudang->id;
        $penerimaanStokHeader->modifiedby               = auth('api')->user()->name;
        $penerimaanStokHeader->info = html_entity_decode(request()->info);
        $penerimaanStokHeader->statuscetak              = $statusCetak->id;
        $data['sortname']                               = $data['sortname'] ?? 'id';
        $data['sortorder']                              = $data['sortorder'] ?? 'asc';

        if (!$penerimaanStokHeader->save()) {
            throw new \Exception("Error updating Penerimaan Stok header.");
        }

        // dd('test');
        $penerimaanStokHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerimaanStokHeader->getTable()),
            'postingdari' => "EDIT PENERIMAAN STOK ($fetchFormat->kodepenerimaan)",
            'idtrans' => $penerimaanStokHeader->id,
            'nobuktitrans' => $penerimaanStokHeader->nobukti,
            'aksi' => strtoupper('edit'),
            'datajson' => $penerimaanStokHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        /*RETURN STOK PENERIMAAN*/
        if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
            $datadetail = PenerimaanStokDetail::select('stok_id', 'qty')->where('penerimaanstokheader_id', '=', $penerimaanStokHeader->id)->get();
            (new PenerimaanStokDetail())->returnStokPenerimaan($penerimaanStokHeader->id);
        }
        // dd('test');
        if (($data['penerimaanstok_id'] == $korv->id) || ($data['penerimaanstok_id'] === $spbs->text)) {
            (new PenerimaanStokDetail())->returnVulkanisir($penerimaanStokHeader->id);
        }

        if (($data['penerimaanstok_id'] === $spb->text)) {
            for ($i = 0; $i < count($data['detail_harga']); $i++) {
                $detail_stok_id = $data['detail_stok_id'][$i];
                $detail_qty = $data['detail_qty'][$i];
                if (array_key_exists('detail_stok_id_old', $data)) {
                    if (array_key_exists($i, $data['detail_stok_id'])) {
                        if ($data['detail_stok_id'][$i] != $data['detail_stok_id_old'][$i]) {
                            $detail_stok_id = $data['detail_stok_id_old'][$i];
                            $detail_qty = 0;
                        }
                    }
                }

                // detail_stok_id_old
                (new PenerimaanStokDetail())->validasiSPBMinus(
                    $penerimaanStokHeader->id,
                    $detail_stok_id,
                    $detail_qty,
                );
            }
        }


        /*DELETE EXISTING DETAIL*/
        $penerimaanStokDetail = PenerimaanStokDetail::where('penerimaanstokheader_id', $penerimaanStokHeader->id)->lockForUpdate()->delete();
        $kartuStok = kartuStok::where('nobukti', $penerimaanStokHeader->nobukti)->lockForUpdate()->delete();
        $PenambahanNilai = PenerimaanStokPenambahanNilai::where('penerimaanstokheader_id', $penerimaanStokHeader->id)->lockForUpdate()->delete();
        $penerimaanStokDetailFifo = PenerimaanStokDetailFifo::where('penerimaanstokheader_id', $penerimaanStokHeader->id)->lockForUpdate()->delete();

        /*STORE DETAIL*/
        $penerimaanStokDetails = [];
        $totalharga = 0;
        $detaildata = [];
        $tgljatuhtempo = [];
        $keterangan_detail = [];


        $masukgudang_id = 0;
        $masuktrado_id = 0;
        $masukgandengan_id = 0;
        $keluargudang_id = 0;
        $keluartrado_id = 0;
        $keluargandengan_id = 0;


        $penerimaanstok_id = $data['penerimaanstok_id'] ?? 0;

        $urutfifo = db::table("penerimaanstok")->from(db::raw("penerimaanstok as a with (readuncommitted)"))
            ->select('a.urutfifo')->where('a.id', $penerimaanstok_id)->first()->urutfifo ?? 0;



        $masukgudang_id = $data['gudang_id'] ?? 0;
        $masuktrado_id = $data['trado_id'] ?? 0;
        $masukgandengan_id = $data['gandengan_id'] ?? 0;

        if ($gudangke_id != 0) {
            $masukgudang_id = $gudangke_id ?? 0;
        }
        if ($gudangdari_id != 0) {
            $keluargudang_id = $gudangdari_id ?? 0;
        }


        if ($tradoke_id != 0) {
            $masuktrado_id = $tradoke_id ?? 0;
        }

        if ($tradodari_id != 0) {
            $keluartrado_id = $tradodari_id ?? 0;
        }


        if ($gandenganke_id != 0) {
            $masukgandengan_id = $gandenganke_id ?? 0;
        }

        if ($gandengandari_id != 0) {
            $keluargandengan_id = $gandengandari_id ?? 0;
        }

        $coadebet_detail = [];
        $coakredit_detail = [];
        $nominal_detail = [];
        $keterangan_detail = [];

        for ($i = 0; $i < count($data['detail_harga']); $i++) {

            $ksqty = $data['detail_qty'][$i] ?? 0;
            $ksnilai = $data['totalItem'][$i] ?? 0;

            $penerimaanStokDetail = (new PenerimaanStokDetail())->processStore($penerimaanStokHeader, [
                "penerimaanstokheader_id" => $penerimaanStokHeader->id,
                "nobukti" => $penerimaanStokHeader->nobukti,
                "stok_id" => $data['detail_stok_id'][$i],
                "qty" => $data['detail_qty'][$i],
                "harga" => $data['detail_harga'][$i],
                "totalItem" => $data['totalItem'][$i],
                "totalsebelum" => $data['totalsebelum'][$i] ?? 0,
                "persentasediscount" => $data['detail_persentasediscount'][$i],
                "vulkanisirke" => $data['detail_vulkanisirke'][$i],
                "detail_keterangan" => $data['detail_keterangan'][$i],
                "detail_penerimaanstoknobukti" => $data['detail_penerimaanstoknobukti'][$i],
            ]);

            //update total vulkanisir
            $reuse = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select('a.id')
                ->where('grp', 'STATUS REUSE')
                ->where('subgrp', 'STATUS REUSE')
                ->where('text', 'REUSE')
                ->first()->id ?? 0;

            $stokreuse = db::table("stok")->from(db::raw("stok a with (readuncommitted)"))
                ->select(
                    'a.id',
                    db::raw("isnull(a.vulkanisirawal,0) as vulawal"),
                )
                ->where('a.id', $data['detail_stok_id'][$i])
                ->where('a.statusreuse', $reuse)
                ->first();

            if (isset($stokreuse)) {

                $queryvulkan = db::table("stok")->from(db::raw("stok a with (readuncommitted)"))
                    ->select(
                        db::raw("sum(isnull(b.vulkanisirke,0)) as vulkanplus"),
                        db::raw("sum(isnull(c.vulkanisirke,0)) as vulkanminus")
                    )
                    ->leftjoin(db::raw("penerimaanstokdetail b with (readuncommitted)"), 'a.id', 'b.stok_id')
                    ->leftjoin(db::raw("pengeluaranstokdetail c with (readuncommitted)"), 'a.id', 'c.stok_id')
                    ->where('a.id', $data['detail_stok_id'][$i])
                    ->groupby('a.id')
                    ->first();

                $totalplus = $queryvulkan->vulkanplus ?? 0;
                $totalminus = $queryvulkan->vulkanminus ?? 0;
                $vulawal = $stokreuse->vulawal ?? 0;
                $total = ($totalplus + $vulawal) - $totalminus;

                if (isset($queryvulkan)) {
                    $totalvulkan = $total ?? 0;
                } else {
                    $totalvulkan = 0;
                }
                $datastok  = Stok::lockForUpdate()->where("id", $data['detail_stok_id'][$i])
                    ->firstorFail();
                if ($data['penerimaanstok_id'] == $korv->id) {
                    $datastok->totalvulkanisir = $totalvulkan;
                    $datastok->statusban = ($data['detail_statusban']) ? $data['detail_statusban'][$i] : null;
                    $datastok->save();
                }
            }
            // end update vulkanisir


            $keterangan_detail[] = $data['detail_keterangan'][$i] ?? 'PENERIMAAN STOK HEADER';


            if ($penerimaanstok_id != 2 && $penerimaanstok_id != 10  && $penerimaanstok_id != 11) {
                if ($masukgudang_id != 0 || $masuktrado_id != 0  || $masukgandengan_id != 0) {
                    // dd($data['detail_qty'][$i]);
                    if ($masukgudang_id == $gdgkantor->text) {
                        $kartuStok = (new KartuStok())->processStore([
                            "gudang_id" => $masukgudang_id,
                            "trado_id" => $masuktrado_id,
                            "gandengan_id" => $masukgandengan_id,
                            "stok_id" => $data['detail_stok_id'][$i],
                            "nobukti" => $penerimaanStokHeader->nobukti,
                            "tglbukti" => date('Y-m-d', strtotime($data['tglbukti'])),
                            "qtymasuk" => $ksqty ?? 0,
                            "nilaimasuk" => $ksnilai ?? 0,
                            "qtykeluar" => 0,
                            "nilaikeluar" => 0,
                            "urutfifo" => $urutfifo,
                        ]);
                    } else {
                        $kartuStok = (new KartuStok())->processStore([
                            "gudang_id" => $masukgudang_id,
                            "trado_id" => $masuktrado_id,
                            "gandengan_id" => $masukgandengan_id,
                            "stok_id" => $data['detail_stok_id'][$i],
                            "nobukti" => $penerimaanStokHeader->nobukti,
                            "tglbukti" => date('Y-m-d', strtotime($data['tglbukti'])),
                            "qtymasuk" => $ksqty ?? 0,
                            "nilaimasuk" => 0,
                            "qtykeluar" => 0,
                            "nilaikeluar" => 0,
                            "urutfifo" => $urutfifo,
                        ]);
                    }
                }

                if ($keluargudang_id != 0 || $keluartrado_id != 0  || $keluargandengan_id != 0) {
                    if ($penerimaanstok_id == 6) {
                        if ($keluargudang_id == $gdgkantor->text) {
                            $kartuStok = (new KartuStok())->processStore([
                                "gudang_id" => $keluargudang_id,
                                "trado_id" => $keluartrado_id,
                                "gandengan_id" => $keluargandengan_id,
                                "stok_id" => $data['detail_stok_id'][$i],
                                "nobukti" => $penerimaanStokHeader->nobukti,
                                "tglbukti" => date('Y-m-d', strtotime($data['tglbukti'])),
                                "qtymasuk" => 0,
                                "nilaimasuk" => 0,
                                "qtykeluar" => $ksqty ?? 0,
                                "nilaikeluar" =>  $ksnilai ?? 0,
                                "urutfifo" => $urutfifo,
                            ]);
                        } else {
                            $kartuStok = (new KartuStok())->processStore([
                                "gudang_id" => $keluargudang_id,
                                "trado_id" => $keluartrado_id,
                                "gandengan_id" => $keluargandengan_id,
                                "stok_id" => $data['detail_stok_id'][$i],
                                "nobukti" => $penerimaanStokHeader->nobukti,
                                "tglbukti" => date('Y-m-d', strtotime($data['tglbukti'])),
                                "qtymasuk" => 0,
                                "nilaimasuk" => 0,
                                "qtykeluar" => $ksqty ?? 0,
                                "nilaikeluar" => 0,
                                "urutfifo" => $urutfifo,
                            ]);
                        }
                    } else {
                        if ($keluargudang_id == $gdgkantor->text) {
                            // $kartuStok = (new KartuStok())->processStore([
                            //     "gudang_id" => $keluargudang_id,
                            //     "trado_id" => $keluartrado_id,
                            //     "gandengan_id" => $keluargandengan_id,
                            //     "stok_id" => $data['detail_stok_id'][$i],
                            //     "nobukti" => $penerimaanStokHeader->nobukti,
                            //     "tglbukti" => date('Y-m-d', strtotime($data['tglbukti'])),
                            //     "qtymasuk" => 0,
                            //     "nilaimasuk" => 0,
                            //     "qtykeluar" => $ksqty ?? 0,
                            //     "nilaikeluar" => $ksnilai ?? 0,
                            //     "urutfifo" => $urutfifo,
                            // ]);
                        } else {
                            $kartuStok = (new KartuStok())->processStore([
                                "gudang_id" => $keluargudang_id,
                                "trado_id" => $keluartrado_id,
                                "gandengan_id" => $keluargandengan_id,
                                "stok_id" => $data['detail_stok_id'][$i],
                                "nobukti" => $penerimaanStokHeader->nobukti,
                                "tglbukti" => date('Y-m-d', strtotime($data['tglbukti'])),
                                "qtymasuk" => 0,
                                "nilaimasuk" => 0,
                                "qtykeluar" => $ksqty ?? 0,
                                "nilaikeluar" => 0,
                                "urutfifo" => $urutfifo,
                            ]);
                        }
                    }
                }
            }



            if ($data['penerimaanstok_id'] == $spbp->id) {
                $penambahanNilai = (new PenerimaanStokPenambahanNilai())->processStore($penerimaanStokHeader, [
                    "penerimaanstokheader_id" => $penerimaanStokHeader->id,
                    "nobukti" => $penerimaanStokHeader->nobukti,
                    "stok_id" => $data['detail_stok_id'][$i],
                    "qty" => $data['detail_qty'][$i],
                    "harga" => $data['detail_harga'][$i],
                    "penerimaanstok_nobukti" => $penerimaanStokHeader->penerimaanstok_nobukti,
                ]);
            }

            if (($gudangdari_id == $gudangkantor) && ($gudangke_id == $gudangsementara)) {

                $datadetailfifo = [
                    "penerimaanstokheader_id" => $penerimaanStokHeader->id,
                    "penerimaanstok_id" => $data['penerimaanstok_id'],
                    "nobukti" => $penerimaanStokHeader->nobukti,
                    "stok_id" => $data['detail_stok_id'][$i],
                    "gudang_id" => $gudangdari_id,
                    "tglbukti" => $data['tglbukti'],
                    "qty" => $data['detail_qty'][$i],
                    "modifiedby" => auth('api')->user()->name,
                    "keterangan" => $data['keterangan'] ?? '',
                    "detail_keterangan" => $data['detail_keterangan'][$i] ?? '',
                    "statusformat" => $statusformat,
                ];

                (new PenerimaanStokDetailFifo())->processStore($penerimaanStokHeader, $datadetailfifo);
                $isPostJurnal = false;
                $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'DEBET')->first();
                $memo = json_decode($getCoaDebet->memo, true);
                $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'KREDIT')->first();
                $memokredit = json_decode($getCoaKredit->memo, true);

                $coadebet_detail[] = $memo['JURNAL'];
                $coakredit_detail[] = $memokredit['JURNAL'];
                // $nominal_detail[] = $penerimaanStokDetail->total;
                $penerimaanStokDetail = penerimaanStokDetail::where('id', $penerimaanStokDetail->id)->first();

                $nominal_detail[] = $penerimaanStokDetail->total;
                if ($data['penerimaanstok_id'] == $spb->text || $data['penerimaanstok_id'] == $spbs->text) {
                    // $totalsat = ($data['detail_qty'][$i] * $data['detail_harga'][$i]);
                    $totalsat = $data['totalItem'][$i];
                } else {
                    $totalsat = 0;
                }
                if ($totalsat != 0) {
                    $isPostJurnal = true;
                }



                $jurnalRequest = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $penerimaanStokHeader->nobukti,
                    'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'postingdari' => "ENTRY PENERIMAAN STOK ($fetchFormat->kodepenerimaan)",
                    'statusapproval' => $statusApproval->id,
                    'userapproval' => "",
                    'tglapproval' => "",
                    'modifiedby' => auth('api')->user()->name,
                    'statusformat' => "0",
                    'coakredit_detail' => $coakredit_detail,
                    'coadebet_detail' => $coadebet_detail,
                    'nominal_detail' => $nominal_detail,
                    'keterangan_detail' => $keterangan_detail
                ];


                $jurnalUmumHeader = JurnalUmumHeader::where('nobukti', $penerimaanStokHeader->nobukti)->lockForUpdate()->first();

                if ($jurnalUmumHeader != null) {

                    $jurnalUmumHeader = (new JurnalUmumHeader())->processUpdate($jurnalUmumHeader, $jurnalRequest);
                } else {
                    if ($isPostJurnal) {
                        $jurnalUmumHeader = (new JurnalUmumHeader())->processStore($jurnalRequest);
                    }
                }
            }


            if ($data['penerimaanstok_id'] == $spb->text || $data['penerimaanstok_id'] == $spbs->text) {
                // $totalsat = ($data['detail_qty'][$i] * $data['detail_harga'][$i]);
                $totalsat = $data['totalItem'][$i];
                $totalharga += $totalsat;
                $detaildata[] = $totalsat;
                $tgljatuhtempo[] =  $data['tglbukti'];
                $keterangan_detail[] =  $data['detail_keterangan'][$i];
            }
            $penerimaanStokDetails[] = $penerimaanStokDetail->toArray();
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerimaanStokDetail->getTable()),
            'postingdari' => "UPDATE PENERIMAAN STOK ($fetchFormat->kodepenerimaan)",
            'idtrans' =>  $penerimaanStokHeaderLogTrail->id,
            'nobuktitrans' => $penerimaanStokHeader->nobukti,
            'aksi' => strtoupper('edit'),
            'datajson' => $penerimaanStokDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        /*UPDATE HUTANG IF SPB*/
        if ($data['penerimaanstok_id'] == $spb->text || $data['penerimaanstok_id'] == $spbs->text) {

            $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL HUTANG PEMBELIAN STOK')->where('subgrp', 'DEBET')->first();
            $memoDebet = json_decode($getCoaDebet->memo, true);

            $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL HUTANG PEMBELIAN STOK')->where('subgrp', 'KREDIT')->first();
            $memoKredit = json_decode($getCoaKredit->memo, true);


            $hutangRequest = [
                'proseslain' => 'PEMBELIAN STOK',
                'postingdari' => "PENERIMAAN STOK ($fetchFormat->kodepenerimaan)",
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'coa' => $memoDebet['JURNAL'],
                'supplier_id' => ($data['supplier_id'] == null) ? "" : $data['supplier_id'],
                'modifiedby' => auth('api')->user()->name,
                'total' => $totalharga,
                'coadebet' => $memoDebet['JURNAL'],
                'coakredit' => $memoKredit['JURNAL'],
                'tgljatuhtempo' => $tgljatuhtempo,
                'total_detail' => $detaildata,
                'keterangan_detail' => $keterangan_detail,
            ];
            $hutangHeader = HutangHeader::where('nobukti', $penerimaanStokHeader->hutang_nobukti)->first();
            (new HutangHeader())->processUpdate($hutangHeader, $hutangRequest);
        } else if (($data['penerimaanstok_id'] == $pst->text) || $data['penerimaanstok_id'] == $pspk->text) {
            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

            $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'DEBET')->first();
            $memokredit = json_decode($getCoaKredit->memo, true);
            $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'KREDIT')->first();
            $memo = json_decode($getCoaDebet->memo, true);

            for ($i = 0; $i < count($data['detail_harga']); $i++) {
                // $totalsat = ($data['detail_qty'][$i] * $data['detail_harga'][$i]);
                $totalsat = $data['totalItem'][$i];
                $coakredit_detail[] = $memokredit['JURNAL'];
                $coadebet_detail[] = $memo['JURNAL'];
                $nominal_detail[] = ceil($totalsat);
                $keterangan_detail[] = $data['detail_keterangan'][$i];
            }

            /*STORE JURNAL*/
            $jurnalRequest = [
                'tanpaprosesnobukti' => 1,
                'postingdari' => "EDIT PENERIMAAN STOK ($fetchFormat->kodepenerimaan)",
                'coakredit_detail' => $coakredit_detail,
                'coadebet_detail' => $coadebet_detail,
                'nominal_detail' => $nominal_detail,
                'keterangan_detail' => $keterangan_detail
            ];
            $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $penerimaanStokHeader->nobukti)->first();
            $newJurnal = new JurnalUmumHeader();
            $newJurnal = $newJurnal->find($getJurnal->id);
            $jurnalumumHeader = (new JurnalUmumHeader())->processUpdate($newJurnal, $jurnalRequest);
        }
        if ($data['penerimaanstok_id'] == $spb->text || $data['penerimaanstok_id'] == $kor->text) {
            $fifo = PengeluaranStokDetailFifo::where('penerimaanstokheader_nobukti', $penerimaanStokHeader->nobukti)->first();
            if ($fifo) {
                $pengeluaranStokHeader = PengeluaranStokHeader::where('nobukti', $fifo->nobukti)->first();
                $this->resetPengeluaranFifo($pengeluaranStokHeader);
            }
        }

        return $penerimaanStokHeader;
    }

    public function processDestroy($id): PenerimaanStokHeader
    {

        $penerimaanStokHeader = PenerimaanStokHeader::findOrFail($id);
        $dataHeader =  $penerimaanStokHeader->toArray();
        $datahitungstok = PenerimaanStok::select('statushitungstok as statushitungstok_id', 'kodepenerimaan')->where('format', '=', $penerimaanStokHeader->statusformat)->first();
        $penerimaanStokDetail = PenerimaanStokDetail::where('penerimaanstokheader_id', '=', $penerimaanStokHeader->id)->get();
        $dataDetail = $penerimaanStokDetail->toArray();
        $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();
        /*RETURN STOK PENERIMAAN*/
        if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
            $datadetail = PenerimaanStokDetail::select('stok_id', 'qty')->where('penerimaanstokheader_id', '=', $penerimaanStokHeader->id)->get();
            (new PenerimaanStokDetail())->returnStokPenerimaan($penerimaanStokHeader->id);
        }
        $korv = DB::table('penerimaanstok')->where('kodepenerimaan', 'KORV')->first();
        $spbs = Parameter::where('grp', 'REUSE STOK')->where('subgrp', 'REUSE STOK')->first();
        $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();
        $kor = Parameter::where('grp', 'KOR STOK')->where('subgrp', 'KOR STOK')->first();

        if (($penerimaanStokHeader->penerimaanstok_id == $korv->id) || ($penerimaanStokHeader->penerimaanstok_id == $spbs->text)) {
            (new PenerimaanStokDetail())->returnVulkanisir($penerimaanStokHeader->id);
        }

        if (($penerimaanStokHeader->penerimaanstok_id === $spb->text)) {
            foreach ($penerimaanStokDetail as $stokDetail) {
                (new PenerimaanStokDetail())->validasiSPBMinus(
                    $penerimaanStokHeader->id,
                    $stokDetail->stok_id,
                    0
                );
            }
        }



        $jurnalUmumHeader = JurnalUmumHeader::where('nobukti', $penerimaanStokHeader->nobukti)->lockForUpdate()->first();
        if ($jurnalUmumHeader) {
            (new JurnalUmumHeader())->processDestroy($jurnalUmumHeader->id);
        }
        /*DELETE EXISTING DETAIL*/
        $penerimaanStokDetail = PenerimaanStokDetail::where('penerimaanstokheader_id', $penerimaanStokHeader->id)->lockForUpdate()->delete();
        $PenambahanNilai = PenerimaanStokPenambahanNilai::where('penerimaanstokheader_id', $penerimaanStokHeader->id)->lockForUpdate()->delete();

        if (isset($penerimaanStokHeader->hutang_nobukti) && ($penerimaanStokHeader->hutang_nobukti !== "")) {
            // dd(isset($penerimaanStokHeader->hutang_nobukti) && ($penerimaanStokHeader->hutang_nobukti !== ""));
            $hutangHeader = HutangHeader::where('nobukti', $penerimaanStokHeader->hutang_nobukti)->first();
            (new HutangHeader())->processDestroy($hutangHeader->id);
        }
        /*DELETE EXISTING JURNAL*/
        $JurnalUmumHeader = JurnalUmumHeader::where('nobukti', $penerimaanStokHeader->nobukti)->lockForUpdate()->first();
        if ($JurnalUmumHeader) {
            (new JurnalUmumHeader())->processDestroy($JurnalUmumHeader->id, strtoupper('DELETE penerimaan Stok Header'));
        }

        //delete kartu stok
        // (new KartuStok())->processDestroy($penerimaanStokHeader->nobukti);
        $kartuStok = kartuStok::where('nobukti', $penerimaanStokHeader->nobukti)->lockForUpdate()->delete();
        $penerimaanStokDetailFifo = PenerimaanStokDetailFifo::where('penerimaanstokheader_id', $penerimaanStokHeader->id)->lockForUpdate()->delete();


        $penerimaanStokHeader = $penerimaanStokHeader->lockAndDestroy($id);

        $penerimaanStokHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $penerimaanStokHeader->getTable(),
            'postingdari' => "DELETE PENERIMAAN STOK ($datahitungstok->kodepenerimaan)",
            'idtrans' => $penerimaanStokHeader->id,
            'nobuktitrans' => $penerimaanStokHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $dataHeader,
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => "penerimaanstokdetail",
            'postingdari' =>  "DELETE PENERIMAAN STOK ($datahitungstok->kodepenerimaan)",
            'idtrans' => $penerimaanStokHeaderLogTrail['id'],
            'nobuktitrans' => $penerimaanStokHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $dataDetail,
            'modifiedby' => auth('api')->user()->name
        ]);

        return $penerimaanStokHeader;
    }

    public function resetPengeluaranFifo(PengeluaranStokHeader $pengeluaranStokHeader)
    {
        $fetchFormat =  PengeluaranStok::where('id', $pengeluaranStokHeader->pengeluaranstok_id)->first();
        $gudangkantor =  Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first()->text;
        $coaKasMasuk = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('memo')->where('grp', 'JURNAL RETUR STOK')->where('subgrp', 'KREDIT')->first();
        $memo = json_decode($coaKasMasuk->memo, true);
        $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'KREDIT')->first();
        $memokredit = json_decode($getCoaKredit->memo, true);
        $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

        $spk = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select('a.text')
            ->where('a.grp', 'SPK STOK')
            ->where('a.subgrp', 'SPK STOK')
            ->first()->text ?? 0;

        $queryspklainheader = db::table("pengeluaranstokheader")->from(db::raw("pengeluaranstokheader a with (readuncommitted)"))
            ->select(
                'a.id',
                'a.nobukti',
                'a.pengeluaranstok_id',
                'a.gudang_id',
                'a.trado_id',
                'a.gandengan_id',
                'a.tglbukti',
                'a.modifiedby',
                'a.keterangan',
                'a.statusformat',
            )
            ->whereRaw("a.id >=" . $pengeluaranStokHeader->id)
            ->where('a.pengeluaranstok_id', $spk)
            ->orderBy('a.id', 'asc')
            ->get();

        $dataheaderspk = json_decode($queryspklainheader, true);
        foreach ($dataheaderspk as $itemspkheader) {
            $coadebet_detailreset = [];
            $coakredit_detailreset = [];
            $nominal_detailreset = [];
            $keterangan_detailreset = [];
            $pengeluaranStokDetailsreset = [];
            $pengeluaranStokDetailFiforeset = PengeluaranStokDetailFifo::where('pengeluaranstokheader_id', $itemspkheader['id'])->lockForUpdate()->delete();
            $kartuStokreset = KartuStok::where('nobukti', $itemspkheader['nobukti'])->lockForUpdate()->delete();

            $queryspklaindetail = db::table("pengeluaranstokdetail")->from(db::raw("pengeluaranstokdetail a with (readuncommitted)"))
                ->select(
                    'a.qty',
                    'a.stok_id',
                    'a.keterangan',
                    'a.harga',
                    'a.total',
                    'a.id',
                )
                ->where("a.nobukti", $itemspkheader['nobukti'])
                ->orderBy('a.id', 'asc')
                ->get();
            $datadetailspk = json_decode($queryspklaindetail, true);
            foreach ($datadetailspk as $itemspkdetail) {

                $datadetailfiforeset = [
                    "pengeluaranstokheader_id" => $itemspkheader['id'],
                    "pengeluaranstok_id" => $itemspkheader['pengeluaranstok_id'],
                    "nobukti" => $itemspkheader['nobukti'],
                    "stok_id" => $itemspkdetail['stok_id'],
                    "gudang_id" => $gudangkantor,
                    "tglbukti" => $itemspkheader['tglbukti'],
                    "qty" => $itemspkdetail['qty'],
                    "modifiedby" => $itemspkheader['modifiedby'],
                    "keterangan" => $itemspkheader['keterangan'] ?? '',
                    "detail_keterangan" => $itemspkdetail['keterangan'] ?? '',
                    "detail_harga" => $itemspkdetail['harga'] ?? '' ?? '',
                    "statusformat" => $itemspkheader['statusformat'] ?? '',
                ];

                (new PengeluaranStokDetailFifo())->processStore($pengeluaranStokHeader, $datadetailfiforeset);
                $pengeluaranStokDetailreset = PengeluaranStokDetail::where('id', $itemspkdetail['id'])
                    ->where('nobukti', $itemspkheader['nobukti'])
                    ->first();

                $nominal_detailreset[] = $pengeluaranStokDetailreset->total;
                $coadebet_detailreset[] = $memo['JURNAL'];
                $coakredit_detailreset[] = $memokredit['JURNAL'];
                $keterangan_detailreset[] = $itemspkdetail['keterangan'] ?? 'PENGELUARAN STOK RETUR';
                $pengeluaranStokDetailsreset[] = $pengeluaranStokDetailreset->toArray();




                if ($itemspkheader['pengeluaranstok_id'] == 1 ||  $itemspkheader['pengeluaranstok_id'] == 5) {

                    $reuse = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                        ->select('a.id')
                        ->where('grp', 'STATUS REUSE')
                        ->where('subgrp', 'STATUS REUSE')
                        ->where('text', 'REUSE')
                        ->first()->id ?? 0;
                    $stokid = $itemspkdetail['stok_id'] ?? 0;

                    $stokreuse = db::table("stok")->from(db::raw("stok a with (readuncommitted)"))
                        ->select('a.id')
                        ->where('a.id', $stokid)
                        ->where('a.statusreuse', $reuse)
                        ->first();

                    if (isset($stokreuse)) {
                        $ksqty = $itemspkdetail['qty'] ?? 0;
                        $ksnobukti = $itemspkheader['nobukti'] ?? '';
                        $ksgudang_id = $itemspkheader['gudang_id'] ?? '';
                        $kstrado_id = $itemspkheader['trado_id'] ?? '';
                        $ksgandengan_id = $itemspkheader['gandengan_id'] ?? '';

                        $urutfifo = db::table("pengeluaranstok")->from(db::raw("pengeluaranstok as a with (readuncommitted)"))
                            ->select('a.urutfifo')->where('a.id', $itemspkheader['pengeluaranstok_id'])->first()->urutfifo ?? 0;

                        $kartuStok = (new KartuStok())->processStore([
                            "gudang_id" =>  $ksgudang_id,
                            "trado_id" =>  $kstrado_id,
                            "gandengan_id" => $ksgandengan_id,
                            "stok_id" => $itemspkdetail['stok_id'] ?? 0,
                            "nobukti" => $ksnobukti ?? '',
                            "tglbukti" => date('Y-m-d', strtotime($itemspkheader['tglbukti'])),
                            "qtymasuk" => $ksqty ?? 0,
                            "nilaimasuk" =>  0,
                            "qtykeluar" =>  0,
                            "nilaikeluar" => 0,
                            "urutfifo" => $urutfifo,
                        ]);
                    }
                }
            }

            $jurnalRequestreset = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $itemspkheader['nobukti'],
                'tglbukti' => $itemspkheader['tglbukti'],
                'postingdari' => "ENTRY PENGELUARAN STOK ($fetchFormat->kodepengeluaran)",
                'statusapproval' => $statusApproval->id,
                'userapproval' => "",
                'tglapproval' => "",
                'modifiedby' => $itemspkheader['modifiedby'],
                'statusformat' => "0",
                'coakredit_detail' => $coakredit_detailreset,
                'coadebet_detail' => $coadebet_detailreset,
                'nominal_detail' => $nominal_detailreset,
                'keterangan_detail' => $keterangan_detailreset,
            ];


            $jurnalUmumHeaderreset = JurnalUmumHeader::where('nobukti', $itemspkheader['nobukti'])->lockForUpdate()->first();
            if ($jurnalUmumHeaderreset != null) {
                $jurnalUmumHeaderreset = (new JurnalUmumHeader())->processUpdate($jurnalUmumHeaderreset, $jurnalRequestreset);
            } else {
                $jurnalUmumHeaderreset = (new JurnalUmumHeader())->processStore($jurnalRequestreset);
            }

            $pengeluaranStokHeaderLogTrailReset = (new LogTrail())->processStore([
                'namatabel' => strtoupper($pengeluaranStokHeader->getTable()),
                'postingdari' => "ENTRY PENGELUARAN STOK ($fetchFormat->kodepengeluaran)",
                'idtrans' => $itemspkheader['id'],
                'nobuktitrans' => $itemspkheader['nobukti'],
                'aksi' => 'ENTRY',
                'datajson' => $pengeluaranStokHeader->toArray(),
                'modifiedby' => auth('api')->user()->user
            ]);
            //store logtrail detail
            (new LogTrail())->processStore([
                'namatabel' => strtoupper('PengeluaranStokDetail'),
                'postingdari' => "ENTRY PENGELUARAN STOK ($fetchFormat->kodepengeluaran)",
                'idtrans' =>  $pengeluaranStokHeaderLogTrailReset->id,
                'nobuktitrans' => $itemspkheader['nobukti'],
                'aksi' => 'ENTRY',
                'datajson' => $pengeluaranStokDetailsreset,
                'modifiedby' => auth('api')->user()->user,
            ]);
        }
        return true;
    }

    public function isPOUsed($id)
    {
        $query = DB::table($this->table)->from($this->table)
            ->select('penerimaanstokheader.id', db::raw("nobuktispb.nobukti  as nobukti"),)
            ->where('penerimaanstokheader.id', $id)
            ->leftJoin('penerimaanstokheader as nobuktispb', 'penerimaanstokheader.nobukti', 'nobuktispb.penerimaanstok_nobukti');
        $data = $query->first();
        // dd($data->nobukti);
        if ($data->nobukti) {
            # code...
            return [
                true,
                $data->nobukti
            ];
        }
        return false;
    }
    public function isOutUsed($id)
    {
        $query = DB::table($this->table)->from($this->table)
            ->where('penerimaanstokheader.id', $id)
            ->join('pengeluaranstokdetailfifo', 'penerimaanstokheader.nobukti', 'pengeluaranstokdetailfifo.penerimaanstokheader_nobukti');
        $data = $query->first();

        if ($data) {
            # code...
            return [
                true,
                $data->nobukti
            ];
        }
        return false;
    }
    public function isPGUsed($id)
    {
        $query = DB::table($this->table)->from($this->table)
            ->where('penerimaanstokheader.id', $id)
            ->join('pengeluaranstokheader', 'penerimaanstokheader.nobukti', 'pengeluaranstokheader.penerimaanstok_nobukti');

        $data = $query->first();
        if ($data) {
            # code...
            return [
                true,
                $data->nobukti
            ];
        }
        return false;
    }
    public function isEhtUsed($id)
    {
        $query = DB::table($this->table)->from($this->table)
            ->where('penerimaanstokheader.id', $id)
            ->leftJoin('hutangheader', 'penerimaanstokheader.hutang_nobukti', 'hutangheader.nobukti');
        $data = $query->get();
        $statusApproval = DB::table('parameter')->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();

        foreach ($data as $penerimaanstok) {
            if ($statusApproval->id == $penerimaanstok->statusapproval) {
                $test[] = $penerimaanstok->statusapproval;
                return [
                    true,
                    $penerimaanstok->hutang_nobukti
                ];
            }
        }


        return false;
    }
    public function isApproved($id)
    {
        $query = DB::table($this->table)->from($this->table)->where('penerimaanstokheader.id', $id);
        $data = $query->first();
        $status = $data->statusapproval;
        $statusApproval = DB::table('parameter')->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();

        if ($status == $statusApproval->id) {
            return true;
        }

        return false;
    }
    public function isEHTApprovedJurnal($id)
    {
        $query = DB::table($this->table)->from($this->table)->where('penerimaanstokheader.id', $id);
        $data = $query->first();
        $approvalJurnal = DB::table('penerimaanstokheader')
            ->from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.hutang_nobukti',
            )
            ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.hutang_nobukti', 'b.nobukti')
            ->where('a.nobukti', '=', $data->nobukti)
            ->first();

        if (isset($approvalJurnal)) {
            return [
                true,
                $approvalJurnal->hutang_nobukti
            ];
        }

        return false;
    }

    public function printValidation($id)
    {
        $query = DB::table($this->table)->from($this->table)->where('penerimaanstokheader.id', $id);
        $data = $query->first();
        $status = $data->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($status == $statusCetak->id) {
            return true;
        }

        return false;
    }
    public function isBukaTanggalValidation($date, $penerimaanstok_id)
    {
        $date = date('Y-m-d', strtotime($date));
        $bukaPenerimaanStok = BukaPenerimaanStok::where('tglbukti', '=', $date)->where('penerimaanstok_id', '=', $penerimaanstok_id)->first();
        $tglbatas = $bukaPenerimaanStok->tglbatas ?? 0;
        $limit = strtotime($tglbatas);
        $now = strtotime('now');
        // dd( date('Y-m-d H:i:s',$now), date('Y-m-d H:i:s',$limit));
        if ($now < $limit) return true;
        return false;
    }
    public function todayValidation($tglbukti)
    {
        $tglbuktistr = strtotime($tglbukti);
        $jam = 23;
        $menit = 59;
        $limit = strtotime($tglbukti . ' +' . $jam . ' hours +' . $menit . ' minutes');
        $now = strtotime('now');
        if ($now < $limit) return true;
        return false;
    }
    public function isEditAble($id)
    {
        $tidakBolehEdit = DB::table('penerimaanstokheader')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

        $query = DB::table('penerimaanstokheader')->from(DB::raw("penerimaanstokheader with (readuncommitted)"))
            ->select('statusapprovaledit as statusedit', 'tglbatasedit')
            ->where('id', $id)
            ->first();

        if ($query->statusedit != $tidakBolehEdit->id) {
            $limit = strtotime($query->tglbatasedit);
            $now = strtotime('now');
            if ($now < $limit) return true;
        }
        return false;
    }

    public function isKeteranganEditAble($id)
    {
        $tidakBolehEdit = DB::table('penerimaanstokheader')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

        $query = DB::table('penerimaanstokheader')->from(DB::raw("penerimaanstokheader with (readuncommitted)"))
            ->select('statusapprovaleditketerangan as statusedit', 'tglbataseditketerangan')
            ->where('id', $id)
            ->first();

        if ($query->statusedit != $tidakBolehEdit->id) {
            $limit = strtotime($query->tglbataseditketerangan);
            $now = strtotime('now');
            if ($now < $limit) return true;
        }
        return false;
    }

    public function updateApproval()
    {
        DB::beginTransaction();
        try {
            $tutupbuku = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'TUTUP BUKU')->where('subgrp', '=', 'TUTUP BUKU')->first();
            $approval = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where("grp", 'STATUS APPROVAL')->where("text", "APPROVAL")->first();
            $nonApproval = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where("grp", 'STATUS APPROVAL')->where("text", "NON APPROVAL")->first();

            $query = DB::table('penerimaanstokheader')->where('tglbataseditketerangan', '<', date('Y-m-d H:i:s'))->where('tglbukti', '>', $tutupbuku->text)->where('statusapprovaleditketerangan', $approval->id);
            $query->update(['statusapprovaleditketerangan' => $nonApproval->id]);

            $query = DB::table('penerimaanstokheader')->where('tglbatasedit', '<', date('Y-m-d H:i:s'))->where('tglbukti', '>', $tutupbuku->text)->where('statusapprovaledit', $approval->id);
            $query->update(['statusapprovaledit' => $nonApproval->id]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }




    // public function checkTempat($stokId,$persediaan,$persediaanId)
    // {
    //     $result = StokPersediaan::lockForUpdate()->where("stok_id", $stokId)->where("$persediaan", $persediaanId)->first();
    //     return (!$result) ? false :$result;
    // }
}
