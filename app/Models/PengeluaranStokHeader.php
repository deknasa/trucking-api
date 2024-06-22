<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\RunningNumberService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;

class PengeluaranStokHeader extends MyModel
{
    use HasFactory;

    protected $table = 'PengeluaranStokHeader';

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
        $aksi = request()->aksi ?? '';

        $user_id = auth('api')->user()->id ?? 0;
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'PglrStokHeaderController';

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
                $table->date('tglbukti')->nullable();
                $table->integer('pengeluaranstok_id')->nullable();
                $table->integer('trado_id')->nullable();
                $table->integer('gandengan_id')->nullable();
                $table->integer('gudang_id')->nullable();
                $table->integer('supir_id')->nullable();
                $table->integer('supplier_id')->nullable();
                $table->string('pengeluaranstok_nobukti', 50)->nullable();
                $table->string('penerimaanstok_nobukti', 50)->nullable();
                $table->string('pengeluarantrucking_nobukti', 50)->nullable();
                $table->string('penerimaan_nobukti', 50)->nullable();
                $table->string('hutangbayar_nobukti', 50)->nullable();
                $table->string('servicein_nobukti', 50)->nullable();
                $table->integer('kerusakan_id')->nullable();
                $table->longText('statuscetak')->nullable();
                $table->integer('statuscetak_id')->nullable();
                $table->longText('statuskirimberkas')->nullable();
                $table->integer('statuskirimberkas_id')->nullable();
                $table->integer('statusformat')->nullable();
                $table->integer('statuspotongretur')->nullable();
                $table->integer('bank_id')->nullable();
                $table->date('tglkasmasuk')->nullable();
                $table->string('modifiedby', 200)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->integer('jumlahcetak')->nullable();
                $table->string('kerusakan', 50)->nullable();
                $table->string('bank', 50)->nullable();
                $table->string('pengeluaranstok', 50)->nullable();
                $table->string('trado', 50)->nullable();
                $table->string('gudang', 50)->nullable();
                $table->string('gandengan', 50)->nullable();
                $table->string('supir', 200)->nullable();
                $table->string('supplier', 200)->nullable();
                $table->longText('statusedit')->nullable();
                $table->integer('statusedit_id')->nullable();
                $table->integer('statuseditketerangan_id')->nullable();
                $table->string('judul', 200)->nullable();
                $table->string('tglcetak', 200)->nullable();
                $table->string('usercetak', 100)->nullable();
                
                $table->date('tgldariheaderpenerimaanstok')->nullable();
                $table->date('tglsampaiheaderpenerimaanstok')->nullable();
                $table->date('tgldariheaderpenerimaanheader')->nullable();
                $table->date('tglsampaiheaderpenerimaanheader')->nullable();
                $table->date('tgldariheaderhutangbayarheader')->nullable();
                $table->date('tglsampaiheaderhutangbayarheader')->nullable();
                $table->date('tgldariheaderpengeluaran')->nullable();
                $table->date('tglsampaiheaderpengeluaran')->nullable();
                $table->date('tgldariheaderserviceinheader')->nullable();
                $table->date('tglsampaiheaderserviceinheader')->nullable();
                $table->date('tgldariheaderpengeluarantruckingheader')->nullable();
                $table->date('tglsampaiheaderpengeluarantruckingheader')->nullable();
                $table->integer('penerimaanbank_id')->nullable();
                $table->double('nominal')->nullable();
            });




            // $temprole = '##temprole' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            // Schema::create($temprole, function ($table) {
            //     $table->bigInteger('aco_id')->nullable();
            // });

            // $queryaco = db::table("useracl")->from(db::raw("useracl a with (readuncommitted)"))
            //     ->select('a.aco_id')
            //     ->join(db::raw("pengeluaranstok b with (readuncommitted)"), 'a.aco_id', 'b.aco_id')
            //     ->where('a.user_id', $user_id);

            // DB::table($temprole)->insertUsing(['aco_id'], $queryaco);


            // $queryrole = db::table("acl")->from(db::raw("acl a with (readuncommitted)"))
            //     ->select('a.aco_id')
            //     ->join(db::raw("userrole b with (readuncommitted)"), 'a.role_id', 'b.role_id')
            //     ->join(db::raw("pengeluaranstok c with (readuncommitted)"), 'a.aco_id', 'c.aco_id')
            //     ->leftjoin(db::raw($temprole . " d "), 'a.aco_id', 'd.aco_id')
            //     ->where('b.user_id', $user_id)
            //     ->whereRaw("isnull(d.aco_id,0)=0");

            // DB::table($temprole)->insertUsing(['aco_id'], $queryrole);


            $spk = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
            $pst = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PST STOK')->where('subgrp', 'PST STOK')->first();
            $gst = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'GST STOK')->where('subgrp', 'GST STOK')->first();
            $pspk = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PSPK STOK')->where('subgrp', 'PSPK STOK')->first();


            $tempbukti = '##tempbukti' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempbukti, function ($table) {
                $table->string('nobukti', 1000)->nullable();
                $table->string('penerimaanstok_nobukti', 1000)->nullable();
                $table->string('penerimaan_nobukti', 1000)->nullable();
                $table->string('hutangbayar_nobukti', 1000)->nullable();
                $table->string('pengeluaranstok_nobukti', 1000)->nullable();
                $table->string('servicein_nobukti', 1000)->nullable();
                $table->string('pengeluarantrucking_nobukti', 1000)->nullable();
            });

            // temporery penerimaanstokheader
            $temppenerimaanstokheader = '##temppenerimaanstokheader' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temppenerimaanstokheader, function ($table) {
                $table->string('nobukti', 50)->nullable();
                $table->date('tglbukti')->nullable();
                // $table->integer('bank_id')->nullable();

                $table->index('nobukti', 'temppenerimaanstokheader_nobukti_index');
            });

            // temporery penerimaanheader
            $temppenerimaanheader = '##temppenerimaanheader' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temppenerimaanheader, function ($table) {
                $table->string('nobukti', 50)->nullable();
                $table->date('tglbukti')->nullable();
                $table->integer('bank_id')->nullable();

                $table->index('nobukti', 'temppenerimaanheader_nobukti_index');
            });

            // temporary hutangbayar

            $temppelunasanhutangheader = '##temppelunasanhutangheader' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temppelunasanhutangheader, function ($table) {
                $table->string('nobukti', 50)->nullable();
                $table->date('tglbukti')->nullable();

                $table->index('nobukti', 'temppelunasanhutangheader_nobukti_index');
            });

            // temporary Pengeluaranstokheader

            $temppengeluaranstokheader = '##temppengeluaranstokheader' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temppengeluaranstokheader, function ($table) {
                $table->string('nobukti', 50)->nullable();
                $table->date('tglbukti', 50)->nullable();

                $table->index('nobukti', 'temppengeluaranstokheader_nobukti_index');
            });


            // temporary ServiceInHeader

            $tempserviceinheader = '##tempserviceinheader' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempserviceinheader, function ($table) {
                $table->string('nobukti', 50)->nullable();
                $table->date('tglbukti')->nullable();

                $table->index('nobukti', 'tempserviceinheader_nobukti_index');
            });

            // temporary Pengeluarantruckingheader

            $temppengeluarantruckingheader = '##temppengeluarantruckingheader' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temppengeluarantruckingheader, function ($table) {
                $table->string('nobukti', 50)->nullable();
                $table->date('tglbukti')->nullable();

                $table->index('nobukti', 'temppengeluarantruckingheader_nobukti_index');
            });
            if (request()->tgldari) {
                $querybukti = db::table("pengeluaranstokheader")->from(db::raw("pengeluaranstokheader a with (readuncommitted)"))
                    ->select(
                        'a.nobukti',
                        'a.penerimaanstok_nobukti',
                        'a.penerimaan_nobukti',
                        'a.hutangbayar_nobukti',
                        'a.pengeluaranstok_nobukti',
                        'a.servicein_nobukti',
                        'a.pengeluarantrucking_nobukti',

                    )
                    ->whereBetween('a.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);

                DB::table($tempbukti)->insertUsing([
                    'nobukti',
                    'penerimaanstok_nobukti',
                    'penerimaan_nobukti',
                    'hutangbayar_nobukti',
                    'pengeluaranstok_nobukti',
                    'servicein_nobukti',
                    'pengeluarantrucking_nobukti',
                ],  $querybukti);



                $querypenerimaanstokheader = db::table("penerimaanstokheader")->from(db::raw("penerimaanstokheader a with (readuncommitted)"))
                    ->select(
                        'a.nobukti',
                        'a.tglbukti',
                        // 'a.bank_id',
                    )
                    ->join(db::raw($tempbukti . " b "), 'a.nobukti', 'b.penerimaanstok_nobukti');

                DB::table($temppenerimaanstokheader)->insertUsing([
                    'nobukti',
                    'tglbukti',
                    // 'bank_id',
                ],  $querypenerimaanstokheader);

                // temporary penerimaanheader
                $querypenerimaanheader = db::table("penerimaanheader")->from(db::raw("penerimaanheader a with (readuncommitted)"))
                    ->select(
                        'a.nobukti',
                        'a.tglbukti',
                        'a.bank_id',
                    )
                    ->join(db::raw($tempbukti . " b "), 'a.nobukti', 'b.penerimaanstok_nobukti');

                DB::table($temppenerimaanheader)->insertUsing([
                    'nobukti',
                    'tglbukti',
                    'bank_id',
                ],  $querypenerimaanheader);

                // dd(db::table($temppenerimaanheader)->get());
                // hutang bayar

                $querypelunasanhutangheader = db::table("pelunasanhutangheader")->from(db::raw("pelunasanhutangheader a with (readuncommitted)"))
                    ->select(
                        'a.nobukti',
                        'a.tglbukti',
                    )
                    ->join(db::raw($tempbukti . " b "), 'a.nobukti', 'b.penerimaanstok_nobukti');

                DB::table($temppelunasanhutangheader)->insertUsing([
                    'nobukti',
                    'tglbukti',
                ],  $querypelunasanhutangheader);

                // pengeluaran stok header

                $querypengeluaranstokheader = db::table("pengeluaranstokheader")->from(db::raw("pengeluaranstokheader a with (readuncommitted)"))
                    ->select(
                        'a.nobukti',
                        'a.tglbukti',
                    )
                    ->join(db::raw($tempbukti . " b "), 'a.nobukti', 'b.penerimaanstok_nobukti');

                DB::table($temppengeluaranstokheader)->insertUsing([
                    'nobukti',
                    'tglbukti',
                ],  $querypengeluaranstokheader);

                // service in header

                $queryserviceinheader = db::table("serviceinheader")->from(db::raw("serviceinheader a with (readuncommitted)"))
                    ->select(
                        'a.nobukti',
                        'a.tglbukti',
                    )
                    ->join(db::raw($tempbukti . " b "), 'a.nobukti', 'b.penerimaanstok_nobukti');

                DB::table($tempserviceinheader)->insertUsing([
                    'nobukti',
                    'tglbukti',
                ],  $queryserviceinheader);

                // pengeluaran trucking header

                $querypengeluarantruckingheader = db::table("pengeluarantruckingheader")->from(db::raw("pengeluarantruckingheader a with (readuncommitted)"))
                    ->select(
                        'a.nobukti',
                        'a.tglbukti',
                    )
                    ->join(db::raw($tempbukti . " b "), 'a.nobukti', 'b.penerimaanstok_nobukti');

                DB::table($temppengeluarantruckingheader)->insertUsing([
                    'nobukti',
                    'tglbukti',
                ],  $querypengeluarantruckingheader);
            }

            $tempNominal = '##tempNominal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempNominal, function ($table) {
                $table->string('nobukti')->nullable();
                $table->double('nominal', 15, 2)->nullable();
            });
            $getNominal = DB::table("pengeluaranstokdetail")->from(DB::raw("pengeluaranstokdetail with (readuncommitted)"))
                ->select(DB::raw("pengeluaranstokheader.nobukti,SUM(pengeluaranstokdetail.total) AS nominal"))
                ->join(DB::raw("pengeluaranstokheader with (readuncommitted)"), 'pengeluaranstokheader.id', 'pengeluaranstokdetail.pengeluaranstokheader_id')
                ->groupBy("pengeluaranstokheader.nobukti");
            if (request()->tgldari && request()->tglsampai) {
                $getNominal->whereBetween('pengeluaranstokheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
                if (request()->pengeluaranheader_id) {
                    $getNominal->where('pengeluaranstokheader.pengeluaranstok_id', request()->pengeluaranheader_id);
                }
            }

            DB::table($tempNominal)->insertUsing(['nobukti', 'nominal'], $getNominal);


            // dd('test1');
            $query = DB::table($this->table);
            $query = $this->selectColumns($query)
                ->leftJoin('gudang', 'pengeluaranstokheader.gudang_id', 'gudang.id')
                ->leftJoin('gandengan', 'pengeluaranstokheader.gandengan_id', 'gandengan.id')
                ->leftJoin('pengeluaranstok', 'pengeluaranstokheader.pengeluaranstok_id', 'pengeluaranstok.id')
                ->leftJoin('trado', 'pengeluaranstokheader.trado_id', 'trado.id')
                ->leftJoin('supplier', 'pengeluaranstokheader.supplier_id', 'supplier.id')
                ->leftJoin('kerusakan', 'pengeluaranstokheader.kerusakan_id', 'kerusakan.id')
                ->leftJoin('bank', 'pengeluaranstokheader.bank_id', 'bank.id')
                ->leftJoin('parameter as statusedit', 'pengeluaranstokheader.statusapprovaledit', 'statusedit.id')
                ->leftJoin('parameter as statuseditketerangan', 'pengeluaranstokheader.statusapprovaleditketerangan', 'statuseditketerangan.id')
                ->leftJoin('parameter as statuscetak', 'pengeluaranstokheader.statuscetak', 'statuscetak.id')
                ->leftJoin('parameter as statuskirimberkas', 'pengeluaranstokheader.statuskirimberkas', 'statuskirimberkas.id')
                ->leftJoin(db::raw($temppenerimaanstokheader . " as penerimaan"), 'pengeluaranstokheader.penerimaanstok_nobukti', 'penerimaan.nobukti')
                ->leftJoin(db::raw($temppenerimaanstokheader . " as penerimaanheader"), 'pengeluaranstokheader.penerimaan_nobukti', 'penerimaanheader.nobukti')
                ->leftJoin(db::raw($temppelunasanhutangheader . " as pelunasanhutangheader"), 'pengeluaranstokheader.hutangbayar_nobukti', 'pelunasanhutangheader.nobukti')
                ->leftJoin(db::raw($temppengeluaranstokheader . " as pengeluaran"), 'pengeluaranstokheader.pengeluaranstok_nobukti', 'pengeluaran.nobukti')
                ->leftJoin(db::raw($tempserviceinheader . " as serviceinheader"), 'pengeluaranstokheader.servicein_nobukti', 'serviceinheader.nobukti')
                ->leftJoin(db::raw($temppengeluarantruckingheader . " as pengeluarantruckingheader"), 'pengeluaranstokheader.pengeluarantrucking_nobukti', 'pengeluarantruckingheader.nobukti')
                ->leftJoin(DB::raw("$tempNominal as nominal with (readuncommitted)"), 'pengeluaranstokheader.nobukti', 'nominal.nobukti')

                ->leftJoin('supir', 'pengeluaranstokheader.supir_id', 'supir.id');
            // ->join(db::raw($temprole . " d "), 'pengeluaranstok.aco_id', 'd.aco_id');

            if (request()->tgldari) {
                // $query->whereBetween('pengeluaranstokheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
                $query->join(db::raw($tempbukti . " as bukti"), 'pengeluaranstokheader.nobukti', 'bukti.nobukti');
            }


            if (request()->penerimaanstok_id  == $pst->text) {
                $query->where('pengeluaranstokheader.pengeluaranstok_id', '=', $gst->text)
                    ->whereNotIn('pengeluaranstokheader.nobukti', function ($query) {
                        $query->select(DB::raw('DISTINCT penerimaanstokheader.pengeluaranstok_nobukti'))
                            ->from('penerimaanstokheader')
                            ->whereNotNull('penerimaanstokheader.pengeluaranstok_nobukti')
                            ->where('penerimaanstokheader.pengeluaranstok_nobukti', '!=', '');
                    });
            }


            if (request()->penerimaanstok_id  == $pspk->text) {
                $query->where('pengeluaranstokheader.pengeluaranstok_id', '=', $spk->text)
                    ->whereNotIn('pengeluaranstokheader.nobukti', function ($query) {
                        $query->select(DB::raw('DISTINCT penerimaanstokheader.pengeluaranstok_nobukti'))
                            ->from('penerimaanstokheader')
                            ->whereNotNull('penerimaanstokheader.pengeluaranstok_nobukti')
                            ->where('penerimaanstokheader.pengeluaranstok_nobukti', '!=', '');
                    });
            }

            if ($from == 'klaim') {

                $pengeluarantrucking_id = request()->pengeluarantrucking_id ?? 0;
                $tempTrucking = '##tempTrucking' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempTrucking, function ($table) {
                    $table->unsignedBigInteger('jumlah')->nullable();
                    $table->string('pengeluaranstok_nobukti')->nullable();
                });
                if ($cabang == 'TNL') {

                    $queryklaimtrucking = DB::connection('sqlsrvtas')->table("pengeluarantruckingdetail")
                        // ->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
                        ->select(DB::raw("count(pengeluarantruckingdetail.stoktnl_id) as jumlah, pengeluarantruckingdetail.pengeluaranstoktnl_nobukti as pengeluaranstok_nobukti"))
                        ->join(DB::raw("trucking.dbo.pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'pengeluarantruckingdetail.nobukti')
                        ->whereRaw("pengeluarantruckingdetail.pengeluaranstoktnl_nobukti != ''")
                        ->where("pengeluarantruckingheader.pengeluarantrucking_id", 7)
                        ->whereBetween('pengeluarantruckingheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
                        ->where('pengeluarantruckingheader.id', '<>', $pengeluarantrucking_id)
                        ->groupBy('pengeluarantruckingdetail.pengeluaranstoktnl_nobukti')->get();

                    foreach ($queryklaimtrucking as $item) {
                        DB::table($tempTrucking)->insert([
                            'jumlah' => $item->jumlah,
                            'pengeluaranstok_nobukti' => $item->pengeluaranstok_nobukti,

                        ]);
                    }
                } else {

                    $queryklaimtrucking = DB::table("pengeluarantruckingdetail")->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
                        ->select(DB::raw("count(pengeluarantruckingdetail.stok_id) as jumlah, pengeluarantruckingdetail.pengeluaranstok_nobukti"))
                        ->join(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingheader.nobukti', 'pengeluarantruckingdetail.nobukti')
                        ->where("pengeluarantruckingdetail.pengeluaranstok_nobukti", '!=', "''")
                        ->where("pengeluarantruckingheader.pengeluarantrucking_id", 7)
                        ->whereBetween('pengeluarantruckingheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
                        ->where('pengeluarantruckingheader.id', '<>', $pengeluarantrucking_id)
                        ->groupBy('pengeluarantruckingdetail.pengeluaranstok_nobukti');

                    DB::table($tempTrucking)->insertUsing([
                        'jumlah',
                        'pengeluaranstok_nobukti',
                    ],  $queryklaimtrucking);
                }

                $tempSpk = '##tempSpk' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempSpk, function ($table) {
                    $table->unsignedBigInteger('jumlah')->nullable();
                    $table->string('nobukti')->nullable();
                });

                $tutupbuku = DB::table("parameter")->where('grp', 'TUTUP BUKU')->first()->text ?? '1900/01/01';
                $queryklaimtrucking = DB::table("pengeluaranstokheader")->from(DB::raw("pengeluaranstokheader with (readuncommitted)"))
                    ->select(DB::raw("count(pengeluaranstokdetail.stok_id) as jumlah,pengeluaranstokheader.nobukti"))
                    ->join(DB::raw("pengeluaranstokdetail with (readuncommitted)"), 'pengeluaranstokheader.nobukti', 'pengeluaranstokdetail.nobukti')
                    ->where("pengeluaranstokheader.pengeluaranstok_id", 1)
                    ->whereBetween('pengeluaranstokheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
                    ->where('pengeluaranstokheader.tglbukti', '>', date('Y-m-d', strtotime($tutupbuku)))
                    ->groupBy('pengeluaranstokheader.nobukti');

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
                    ->leftJoin(DB::raw("$tempTrucking as temptrucking with (readuncommitted)"), 'tempspk.nobukti', 'temptrucking.pengeluaranstok_nobukti')
                    ->whereRaw("isnull(temptrucking.jumlah,0) != isnull(tempspk.jumlah,0)");
                DB::table($tempfinalklaim)->insertUsing([
                    'jumlah',
                    'nobukti',
                ],  $queryklaimtrucking);
                // dd(DB::table($tempfinalklaim)->get());

                $query->join(DB::raw("$tempfinalklaim as finalklaim with (readuncommitted)"), 'pengeluaranstokheader.nobukti', 'finalklaim.nobukti');
            }

            if (request()->pengeluaranheader_id) {
                $query->where('pengeluaranstokheader.pengeluaranstok_id', request()->pengeluaranheader_id);
            }

            if ($periode != '') {
                $periode = explode("-", $periode);
                $query->whereRaw("MONTH(pengeluaranstokheader.tglbukti) ='" . $periode[0] . "'")
                    ->whereRaw("year(pengeluaranstokheader.tglbukti) ='" . $periode[1] . "'");
            }

            if ($statusCetak != '') {
                $query->where("pengeluaranstokheader.statuscetak", $statusCetak);
            }
            // dd($query->tosql());

            $datadetail = json_decode($query->get(), true);
            foreach ($datadetail as $item) {

                DB::table($temtabel)->insert([
                    'id' => $item['id'],
                    'nobukti' => $item['nobukti'],
                    'tglbukti' => $item['tglbukti'],
                    'pengeluaranstok_id' => $item['pengeluaranstok_id'],
                    'trado_id' => $item['trado_id'],
                    'gandengan_id' => $item['gandengan_id'],
                    'gudang_id' => $item['gudang_id'],
                    'supir_id' => $item['supir_id'],
                    'supplier_id' => $item['supplier_id'],
                    'pengeluaranstok_nobukti' => $item['pengeluaranstok_nobukti'],
                    'penerimaanstok_nobukti' => $item['penerimaanstok_nobukti'],
                    'pengeluarantrucking_nobukti' => $item['pengeluarantrucking_nobukti'],
                    'penerimaan_nobukti' => $item['penerimaan_nobukti'],
                    'hutangbayar_nobukti' => $item['hutangbayar_nobukti'],
                    'servicein_nobukti' => $item['servicein_nobukti'],
                    'kerusakan_id' => $item['kerusakan_id'],
                    'statuscetak' => $item['statuscetak'],
                    'statuscetak_id' => $item['statuscetak_id'],
                    'statuskirimberkas' => $item['statuskirimberkas'],
                    'statuskirimberkas_id' => $item['statuskirimberkas_id'],
                    'statusformat' => $item['statusformat'],
                    'statuspotongretur' => $item['statuspotongretur'],
                    'bank_id' => $item['bank_id'],
                    'tglkasmasuk' => $item['tglkasmasuk'],
                    'modifiedby' => $item['modifiedby'],
                    'created_at' => $item['created_at'],
                    'updated_at' => $item['updated_at'],
                    'jumlahcetak' => $item['jumlahcetak'],
                    'kerusakan' => $item['kerusakan'],
                    'bank' => $item['bank'],
                    'pengeluaranstok' => $item['pengeluaranstok'],
                    'trado' => $item['trado'],
                    'gudang' => $item['gudang'],
                    'gandengan' => $item['gandengan'],
                    'supir' => $item['supir'],
                    'supplier' => $item['supplier'],
                    'statusedit' => $item['statusedit'],
                    'statusedit_id' => $item['statusedit_id'],
                    'statuseditketerangan_id' => $item['statuseditketerangan_id'],
                    'judul' => $item['judul'],
                    'tglcetak' => $item['tglcetak'],
                    'usercetak' => $item['usercetak'],
                    'tgldariheaderpenerimaanstok' => $item['tgldariheaderpenerimaanstok'],
                    'tglsampaiheaderpenerimaanstok' => $item['tglsampaiheaderpenerimaanstok'],
                    'tgldariheaderpenerimaanheader' => $item['tgldariheaderpenerimaanheader'],
                    'tglsampaiheaderpenerimaanheader' => $item['tglsampaiheaderpenerimaanheader'],
                    'tgldariheaderhutangbayarheader' => $item['tgldariheaderhutangbayarheader'],
                    'tglsampaiheaderhutangbayarheader' => $item['tglsampaiheaderhutangbayarheader'],
                    'tgldariheaderpengeluaran' => $item['tgldariheaderpengeluaran'],
                    'tglsampaiheaderpengeluaran' => $item['tglsampaiheaderpengeluaran'],
                    'tgldariheaderserviceinheader' => $item['tgldariheaderserviceinheader'],
                    'tglsampaiheaderserviceinheader' => $item['tglsampaiheaderserviceinheader'],
                    'tgldariheaderpengeluarantruckingheader' => $item['tgldariheaderpengeluarantruckingheader'],
                    'tglsampaiheaderpengeluarantruckingheader' => $item['tglsampaiheaderpengeluarantruckingheader'],
                    'penerimaanbank_id' => $item['penerimaanbank_id'],
                    'nominal' => $item['nominal'],
                ]);
            }

            // DB::table($temtabel)->insertUsing([
            //     'id',
            //     'nobukti',
            //     'tglbukti',
            //     'pengeluaranstok_id',
            //     'trado_id',
            //     'gandengan_id',
            //     'gudang_id',
            //     'supir_id',
            //     'supplier_id',
            //     'pengeluaranstok_nobukti',
            //     'penerimaanstok_nobukti',
            //     'pengeluarantrucking_nobukti',
            //     'penerimaan_nobukti',
            //     'hutangbayar_nobukti',
            //     'servicein_nobukti',
            //     'kerusakan_id',
            //     'statuscetak',
            //     'statusformat',
            //     'statuspotongretur',
            //     'bank_id',
            //     'tglkasmasuk',
            //     'modifiedby',
            //     'created_at',
            //     'updated_at',
            //     'jumlahcetak',
            //     'kerusakan',
            //     'bank',
            //     'pengeluaranstok',
            //     'trado',
            //     'gudang',
            //     'gandengan',
            //     'supir',
            //     'supplier',
            //     'statusedit',
            //     'statusedit_id',
            //     'judul',
            //     'tglcetak',
            //     'usercetak',
            //     'tgldariheaderpenerimaanstok',
            //     'tglsampaiheaderpenerimaanstok',
            //     'tgldariheaderpenerimaanheader',
            //     'tglsampaiheaderpenerimaanheader',
            //     'tgldariheaderhutangbayarheader',
            //     'tglsampaiheaderhutangbayarheader',
            //     'tgldariheaderpengeluaran',
            //     'tglsampaiheaderpengeluaran',
            //     'tgldariheaderserviceinheader',
            //     'tglsampaiheaderserviceinheader',
            //     'tgldariheaderpengeluarantruckingheader',
            //     'tglsampaiheaderpengeluarantruckingheader',
            // ], $query);

            // dd('test2b');
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
                'a.pengeluaranstok_id',
                'a.trado_id',
                'a.gandengan_id',
                'a.gudang_id',
                'a.supir_id',
                'a.supplier_id',
                'a.pengeluaranstok_nobukti',
                'a.penerimaanstok_nobukti',
                'a.pengeluarantrucking_nobukti',
                'a.penerimaan_nobukti',
                'a.hutangbayar_nobukti',
                'a.servicein_nobukti',
                'a.kerusakan_id',
                'a.statuscetak',
                'a.statuscetak_id',
                'a.statuskirimberkas',
                'a.statuskirimberkas_id',
                'a.statusformat',
                'a.statuspotongretur',
                'a.bank_id',
                'a.tglkasmasuk',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
                'a.jumlahcetak',
                'a.kerusakan',
                'a.bank',
                'a.pengeluaranstok',
                'a.trado',
                'a.gudang',
                'a.gandengan',
                'a.supir',
                'a.supplier',
                'a.statusedit',
                'a.statusedit_id',
                'a.statuseditketerangan_id',
                'a.judul',
                'a.tglcetak',
                'a.usercetak',
                'a.tgldariheaderpenerimaanstok',
                'a.tglsampaiheaderpenerimaanstok',
                'a.tgldariheaderpenerimaanheader',
                'a.tglsampaiheaderpenerimaanheader',
                'a.tgldariheaderhutangbayarheader',
                'a.tglsampaiheaderhutangbayarheader',
                'a.tgldariheaderpengeluaran',
                'a.tglsampaiheaderpengeluaran',
                'a.tgldariheaderserviceinheader',
                'a.tglsampaiheaderserviceinheader',
                'a.tgldariheaderpengeluarantruckingheader',
                'a.tglsampaiheaderpengeluarantruckingheader',
                'a.penerimaanbank_id',
                'a.nominal',

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

            ->get($server . "pengeluaranstokheader?limit=0&tgldari=" . $dari . "&tglsampai=" . $sampai);
        $data = $getTrado->json()['data'];

        $class = 'PengeluaranStokHeaderController';
        $user = auth('api')->user()->name;

        $temtabel = 'tempspk' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;
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
            $table->integer('pengeluaranstok_id')->length(11)->nullable();
            $table->integer('trado_id')->length(11)->nullable();
            $table->integer('gandengan_id')->length(11)->nullable();
            $table->integer('gudang_id')->length(11)->nullable();
            $table->integer('supir_id')->length(11)->nullable();
            $table->integer('supplier_id')->length(11)->nullable();
            $table->string('pengeluaranstok_nobukti', 50)->nullable();
            $table->string('penerimaanstok_nobukti', 50)->nullable();
            $table->string('pengeluarantrucking_nobukti', 50)->nullable();
            $table->string('penerimaan_nobukti', 50)->nullable();
            $table->string('hutangbayar_nobukti', 50)->nullable();
            $table->string('servicein_nobukti', 50)->nullable();
            $table->integer('kerusakan_id')->length(11)->nullable();
            $table->string('statuscetak', 1500)->nullable();
            $table->integer('statuscetak_id')->length(11)->nullable();
            $table->string('statuskirimberkas', 1500)->nullable();
            $table->integer('statuskirimberkas_id')->length(11)->nullable();
            $table->integer('statusformat')->length(11)->nullable();
            $table->integer('statuspotongretur')->length(11)->nullable();
            $table->integer('bank_id')->length(11)->nullable();
            $table->date('tglkasmasuk')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('jumlahcetak')->length(11)->nullable();
            $table->string('kerusakan', 255)->nullable();
            $table->string('bank', 70)->nullable();
            $table->string('pengeluaranstok', 255)->nullable();
            $table->string('trado', 50)->nullable();
            $table->string('gudang', 50)->nullable();
            $table->string('gandengan', 50)->nullable();
            $table->string('supir', 100)->nullable();
            $table->string('supplier', 1500)->nullable();
            $table->string('statusedit', 1500)->nullable();
            $table->integer('statusedit_id')->length(11)->nullable();
            $table->integer('statuseditketerangan_id')->length(11)->nullable();
            $table->integer('penerimaanbank_id')->length(11)->nullable();
            $table->double('nominal', 15, 2)->nullable();    
        });

        foreach ($data as $row) {

            unset($row['judul']);
            unset($row['tglcetak']);
            unset($row['usercetak']);
            unset($row['tgldariheaderpenerimaanstok']);
            unset($row['tglsampaiheaderpenerimaanstok']);
            unset($row['tgldariheaderpenerimaanheader']);
            unset($row['tglsampaiheaderpenerimaanheader']);
            unset($row['tgldariheaderhutangbayarheader']);
            unset($row['tglsampaiheaderhutangbayarheader']);
            unset($row['tgldariheaderpengeluaran']);
            unset($row['tglsampaiheaderpengeluaran']);
            unset($row['tgldariheaderserviceinheader']);
            unset($row['tglsampaiheaderserviceinheader']);
            unset($row['tgldariheaderpengeluarantruckingheader']);
            unset($row['tglsampaiheaderpengeluarantruckingheader']);
            DB::table($temtabel)->insert($row);
        }

        return $temtabel;
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
                        } else if ($filters['field'] == '') {
                        } else if ($filters['field'] == 'statuskirimberkas') {
                                if ($filters['data']) {
                                    $query = $query->where('a.statuskirimberkas_id', '=', "$filters[data]");
                                }
                        } else if ($filters['field'] == 'tglbukti') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {

                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            // if ($filters['field'] == 'statuscetak') {
                            //     $query = $query->orWhere('statuscetak.text', '=', "$filters[data]");
                            // } else if ($filters['field'] == 'pengeluaranstok') {
                            //     $query = $query->orWhere('pengeluaranstok.kodepengeluaran', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'gudang') {
                            //     $query = $query->orWhere('gudang.gudang', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'gandengan') {
                            //     $query = $query->orWhere('gandengan.kodegandengan', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'trado') {
                            //     $query = $query->orWhere('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'supplier') {
                            //     $query = $query->orWhere('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'kerusakan') {
                            //     $query = $query->orWhere('kerusakan.keterangan', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'supir') {
                            //     $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'bank') {
                            //     $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                            // } else 
                            // if ($filters['field'] == 'statuscetak') {
                            //     if ($filters['data']) {
                            //         $query = $query->Orwhere('a.statuscetak_id', '=', "$filters[data]");
                            //     }
                            // } else 
                            if ($filters['field'] == '') {
                            // } else if ($filters['field'] == 'statuskirimberkas') {
                            //         if ($filters['data']) {
                            //             $query = $query->Orwhere('a.statuskirimberkas_id', '=', "$filters[data]");
                            //         }
    
                            } else if ($filters['field'] == 'tglbukti') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->orWhereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function selectColumnPostion()
    {

        $temptable = '##tempget' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temptable, function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->double('nominal')->nullable();
            $table->longText('statuscetak')->nullable();
            $table->integer('statuscetak_id')->nullable();
            $table->longText('statuskirimberkas')->nullable();
            $table->integer('statuskirimberkas_id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->string('gudang', 50)->nullable();
            $table->string('trado', 50)->nullable();
            $table->string('gandengan', 200)->nullable();
            $table->string('supplier', 200)->nullable();
            $table->string('supir', 200)->nullable();
            $table->integer('pengeluaranstok_id')->nullable();
            $table->string('pengeluaranstok', 50)->nullable();
            $table->string('pengeluarantrucking_nobukti', 50)->nullable();
            $table->string('servicein_nobukti', 50)->nullable();
            $table->string('penerimaanstok_nobukti', 50)->nullable();
            $table->string('pengeluaranstok_nobukti', 50)->nullable();
            $table->string('penerimaan_nobukti', 50)->nullable();
            $table->string('hutangbayar_nobukti', 50)->nullable();
            $table->string('kerusakan', 100)->nullable();
            $table->longText('statuspotongretur')->nullable();
            $table->string('bank', 50)->nullable();
            $table->string('modifiedby', 200)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $tempNominal = '##tempNominal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempNominal, function ($table) {
            $table->string('nobukti')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });
        $getNominal = DB::table("pengeluaranstokdetail")->from(DB::raw("pengeluaranstokdetail with (readuncommitted)"))
            ->select(DB::raw("pengeluaranstokheader.nobukti,SUM(pengeluaranstokdetail.total) AS nominal"))
            ->join(DB::raw("pengeluaranstokheader with (readuncommitted)"), 'pengeluaranstokheader.id', 'pengeluaranstokdetail.pengeluaranstokheader_id')
            ->groupBy("pengeluaranstokheader.nobukti");
        DB::table($tempNominal)->insertUsing(['nobukti', 'nominal'], $getNominal);

        $query = DB::table('pengeluaranstokheader');
        $query->select(
            "pengeluaranstokheader.id",
            "nominal.nominal",
            'statuscetak.text as statuscetak',
            "statuscetak.id as  statuscetak_id",
            'statuskirimberkas.text as statuskirimberkas',
            "statuskirimberkas.id as  statuskirimberkas_id",
            "pengeluaranstokheader.nobukti",
            "pengeluaranstokheader.tglbukti",
            "gudang.gudang as gudang",
            "trado.kodetrado as trado",
            "gandengan.kodegandengan as gandengan",
            "supplier.namasupplier as supplier",
            "supir.namasupir as supir",
            "pengeluaranstokheader.pengeluaranstok_id",
            "pengeluaranstok.kodepengeluaran as pengeluaranstok",
            "pengeluaranstokheader.pengeluarantrucking_nobukti",
            "pengeluaranstokheader.servicein_nobukti",
            "pengeluaranstokheader.penerimaanstok_nobukti",
            "pengeluaranstokheader.pengeluaranstok_nobukti",
            "pengeluaranstokheader.penerimaan_nobukti",
            "pengeluaranstokheader.hutangbayar_nobukti",
            "kerusakan.keterangan as kerusakan",
            "statuspotongretur.text as statuspotongretur",
            "bank.namabank as bank",
            "pengeluaranstokheader.modifiedby",
            "pengeluaranstokheader.created_at",
            "pengeluaranstokheader.updated_at"
        )
            ->leftJoin('gudang', 'pengeluaranstokheader.gudang_id', 'gudang.id')
            ->leftJoin('gandengan', 'pengeluaranstokheader.gandengan_id', 'gandengan.id')
            ->leftJoin('pengeluaranstok', 'pengeluaranstokheader.pengeluaranstok_id', 'pengeluaranstok.id')
            ->leftJoin('trado', 'pengeluaranstokheader.trado_id', 'trado.id')
            ->leftJoin('supplier', 'pengeluaranstokheader.supplier_id', 'supplier.id')
            ->leftJoin('supir', 'pengeluaranstokheader.supir_id', 'supir.id')
            ->leftJoin('kerusakan', 'pengeluaranstokheader.kerusakan_id', 'kerusakan.id')
            ->leftJoin('bank', 'pengeluaranstokheader.bank_id', 'bank.id')
            ->leftJoin('parameter as statuspotongretur', 'pengeluaranstokheader.statuspotongretur', 'statuspotongretur.id')
            ->leftJoin('parameter as statuscetak', 'pengeluaranstokheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("$tempNominal as nominal with (readuncommitted)"), 'pengeluaranstokheader.nobukti', 'nominal.nobukti')
            ->leftJoin('parameter as statuskirimberkas', 'pengeluaranstokheader.statuskirimberkas', 'statuskirimberkas.id');

        DB::table($temptable)->insertUsing([
            'id',
            'nominal',
            'statuscetak',
            'statuscetak_id',
            'statuskirimberkas',
            'statuskirimberkas_id',
            'nobukti',
            'tglbukti',
            'gudang',
            'trado',
            'gandengan',
            'supplier',
            'supir',
            'pengeluaranstok_id',
            'pengeluaranstok',
            'pengeluarantrucking_nobukti',
            'servicein_nobukti',
            'penerimaanstok_nobukti',
            'pengeluaranstok_nobukti',
            'penerimaan_nobukti',
            'hutangbayar_nobukti',
            'kerusakan',
            'statuspotongretur',
            'bank',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $query);
        $query = DB::table($temptable)->from(DB::raw($temptable . " a "))
            ->select(
                'a.id',
                'a.nominal',
                'a.statuscetak',
                'a.statuscetak_id',
                'a.statuskirimberkas',
                'a.statuskirimberkas_id',
                'a.nobukti',
                'a.tglbukti',
                'a.gudang',
                'a.trado',
                'a.gandengan',
                'a.supplier',
                'a.supir',
                'a.pengeluaranstok_id',
                'a.pengeluaranstok',
                'a.pengeluarantrucking_nobukti',
                'a.servicein_nobukti',
                'a.penerimaanstok_nobukti',
                'a.pengeluaranstok_nobukti',
                'a.penerimaan_nobukti',
                'a.hutangbayar_nobukti',
                'a.kerusakan',
                'a.statuspotongretur',
                'a.bank',
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

        Schema::create($temp, function ($table) {
            $table->integer('id')->nullable();
            $table->double('nominal')->nullable();
            $table->longText('statuscetak')->nullable();
            $table->integer('statuscetak_id')->nullable();
            $table->longText('statuskirimberkas')->nullable();
            $table->integer('statuskirimberkas_id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->string('gudang', 50)->nullable();
            $table->string('trado', 50)->nullable();
            $table->string('gandengan', 200)->nullable();
            $table->string('supplier', 200)->nullable();
            $table->string('supir', 200)->nullable();
            $table->integer('pengeluaranstok_id')->nullable();
            $table->string('pengeluaranstok', 50)->nullable();
            $table->string('pengeluarantrucking_nobukti', 50)->nullable();
            $table->string('servicein_nobukti', 50)->nullable();
            $table->string('penerimaanstok_nobukti', 50)->nullable();
            $table->string('pengeluaranstok_nobukti', 50)->nullable();
            $table->string('penerimaan_nobukti', 50)->nullable();
            $table->string('hutangbayar_nobukti', 50)->nullable();
            $table->string('kerusakan', 100)->nullable();
            $table->longText('statuspotongretur')->nullable();
            $table->string('bank', 50)->nullable();
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
        if (request()->pengeluaranheader_id) {
            $models->where('a.pengeluaranstok_id', request()->pengeluaranheader_id);
        }
        DB::table($temp)->insertUsing([
            'id',
            'nominal',
            'statuscetak',
            'statuscetak_id',
            'statuskirimberkas',
            'statuskirimberkas_id',
            'nobukti',
            'tglbukti',
            'gudang',
            'trado',
            'gandengan',
            'supplier',
            'supir',
            'pengeluaranstok_id',
            'pengeluaranstok',
            'pengeluarantrucking_nobukti',
            'servicein_nobukti',
            'penerimaanstok_nobukti',
            'pengeluaranstok_nobukti',
            'penerimaan_nobukti',
            'hutangbayar_nobukti',
            'kerusakan',
            'statuspotongretur',
            'bank',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $models);

        return  $temp;
    }

    public function selectColumns($query)
    {
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        return $query->select(
            "$this->table.id",
            "$this->table.nobukti",
            "$this->table.tglbukti",
            "$this->table.pengeluaranstok_id",
            "$this->table.trado_id",
            "$this->table.gandengan_id",
            "$this->table.gudang_id",
            "$this->table.supir_id",
            "$this->table.supplier_id",
            "$this->table.pengeluaranstok_nobukti",
            "$this->table.penerimaanstok_nobukti",
            "$this->table.pengeluarantrucking_nobukti",
            "$this->table.penerimaan_nobukti",
            "$this->table.hutangbayar_nobukti",
            "$this->table.servicein_nobukti",
            "$this->table.kerusakan_id",
            'statuscetak.memo as statuscetak',
            'statuscetak.id as statuscetak_id',
            'statuskirimberkas.memo as statuskirimberkas',
            'statuskirimberkas.id as statuskirimberkas_id',
            "$this->table.statusformat",
            "$this->table.statuspotongretur",
            "$this->table.bank_id as bank_id",
            "$this->table.tglkasmasuk",
            "$this->table.modifiedby",
            "$this->table.created_at",
            "$this->table.updated_at",
            "$this->table.jumlahcetak",
            "kerusakan.keterangan as kerusakan",
            "bank.namabank as bank",
            "pengeluaranstok.kodepengeluaran as pengeluaranstok",
            // db::raw("(case when $this->table.pengeluaranstok_id=1 then 
            //     (case when isnull(trado.kodetrado,'')<>'' then trado.kodetrado
            //     when isnull(gudang.gudang,'')<>'' then gudang.gudang
            //     when isnull(gandengan.keterangan,'')<>'' then gandengan.keterangan
            //     else '' end) else trado.kodetrado end) as trado
            // "),
            "trado.kodetrado as trado",
            "gudang.gudang as gudang",
            "gandengan.kodegandengan as gandengan",
            "supir.namasupir as supir",
            "supplier.namasupplier as supplier",
            "statusedit.memo as  statusedit",
            "statusedit.id as  statusedit_id",
            "statuseditketerangan.id as  statuseditketerangan_id",
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
            db::raw("cast((format(penerimaan.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpenerimaanstok"),
            db::raw("cast(cast(format((cast((format(penerimaan.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpenerimaanstok"),
            db::raw("cast((format(penerimaanheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpenerimaanheader"),
            db::raw("cast(cast(format((cast((format(penerimaanheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpenerimaanheader"),
            db::raw("cast((format(pelunasanhutangheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderhutangbayarheader"),
            db::raw("cast(cast(format((cast((format(pelunasanhutangheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderhutangbayarheader"),
            db::raw("cast((format(pengeluaran.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpengeluaran"),
            db::raw("cast(cast(format((cast((format(pengeluaran.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpengeluaran"),
            db::raw("cast((format(serviceinheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderserviceinheader"),
            db::raw("cast(cast(format((cast((format(serviceinheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderserviceinheader"),
            db::raw("cast((format(pengeluarantruckingheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpengeluarantruckingheader"),
            db::raw("cast(cast(format((cast((format(pengeluarantruckingheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpengeluarantruckingheader"),
            "$this->table.bank_id as penerimaanbank_id",
            'nominal.nominal',

        );
    }

    public function find($id)
    {
        $tempNominal = '##tempNominal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempNominal, function ($table) {
            $table->string('nobukti')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });
        $getNominal = DB::table("pengeluaranstokdetail")->from(DB::raw("pengeluaranstokdetail with (readuncommitted)"))
            ->select(DB::raw("pengeluaranstokheader.nobukti,SUM(pengeluaranstokdetail.total) AS nominal"))
            ->join(DB::raw("pengeluaranstokheader with (readuncommitted)"), 'pengeluaranstokheader.id', 'pengeluaranstokdetail.pengeluaranstokheader_id')
            ->groupBy("pengeluaranstokheader.nobukti");
        if (request()->tgldari && request()->tglsampai) {
            $getNominal->whereBetween('pengeluaranstokheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
            if (request()->pengeluaranheader_id) {
                $getNominal->where('pengeluaranstokheader.pengeluaranstok_id', $id);
            }
        }

        DB::table($tempNominal)->insertUsing(['nobukti', 'nominal'], $getNominal);


        $this->setRequestParameters();

        $query = DB::table($this->table);
        $query = $this->selectColumns($query)
            ->leftJoin('gudang', 'pengeluaranstokheader.gudang_id', 'gudang.id')
            ->leftJoin('gandengan', 'pengeluaranstokheader.gandengan_id', 'gandengan.id')
            ->leftJoin('pengeluaranstok', 'pengeluaranstokheader.pengeluaranstok_id', 'pengeluaranstok.id')
            ->leftJoin('trado', 'pengeluaranstokheader.trado_id', 'trado.id')
            ->leftJoin('supplier', 'pengeluaranstokheader.supplier_id', 'supplier.id')
            ->leftJoin('kerusakan', 'pengeluaranstokheader.kerusakan_id', 'kerusakan.id')
            ->leftJoin('bank', 'pengeluaranstokheader.bank_id', 'bank.id')
            ->leftJoin('parameter as statusedit', 'pengeluaranstokheader.statusapprovaledit', 'statusedit.id')
            ->leftJoin('parameter as statuseditketerangan', 'pengeluaranstokheader.statusapprovaleditketerangan', 'statuseditketerangan.id')
            ->leftJoin('parameter as statuscetak', 'pengeluaranstokheader.statuscetak', 'statuscetak.id')
            ->leftJoin('parameter as statuskirimberkas', 'pengeluaranstokheader.statuskirimberkas', 'statuskirimberkas.id')
            ->leftJoin('penerimaanstokheader as penerimaan', 'pengeluaranstokheader.penerimaanstok_nobukti', 'penerimaan.nobukti')
            ->leftJoin('penerimaanheader', 'pengeluaranstokheader.penerimaan_nobukti', 'penerimaanheader.nobukti')
            ->leftJoin('pelunasanhutangheader', 'pengeluaranstokheader.hutangbayar_nobukti', 'pelunasanhutangheader.nobukti')
            ->leftJoin('pengeluaranstokheader as pengeluaran', 'pengeluaranstokheader.pengeluaranstok_nobukti', 'pengeluaran.nobukti')
            ->leftJoin('serviceinheader', 'pengeluaranstokheader.servicein_nobukti', 'serviceinheader.nobukti')
            ->leftJoin('pengeluarantruckingheader', 'pengeluaranstokheader.pengeluarantrucking_nobukti', 'pengeluarantruckingheader.nobukti')
            ->leftJoin(DB::raw("$tempNominal as nominal with (readuncommitted)"), 'pengeluaranstokheader.nobukti', 'nominal.nobukti')
            ->leftJoin('supir', 'pengeluaranstokheader.supir_id', 'supir.id');

        $data = $query->where("$this->table.id", $id)->first();
        return $data;
    }
    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function isInUsed($id)
    {
        $query = DB::table($this->table)->from($this->table)
            ->where('pengeluaranstokheader.id', $id)
            ->leftJoin('penerimaanstokheader', 'pengeluaranstokheader.nobukti', 'penerimaanstokheader.pengeluaranstok_nobukti');
        $data = $query->first();
        if (isset($data)) {
            if ($data->id) {
                return [
                    true,
                    $data->nobukti
                ];
            }
        }
        return false;
    }
    public function isNobuktiApprovedJurnal($id)
    {

        $query = DB::table($this->table)->from($this->table)->where('pengeluaranstokheader.id', $id);
        $data = $query->first();
        if (isset($data)) {
            $approvalJurnal = DB::table('pengeluaranstokheader')
                ->from(
                    DB::raw("pengeluaranstokheader as a with (readuncommitted)")
                )
                ->select(
                    'a.nobukti'
                )
                ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
                ->where('a.nobukti', '=', $data->nobukti)
                ->first();

            if (isset($approvalJurnal)) {
                return [
                    true,
                    $data->nobukti
                ];
            }
        }
        return false;
    }
    public function isKMTApprovedJurnal($id)
    {
        $query = DB::table($this->table)->from($this->table)->where('pengeluaranstokheader.id', $id);
        $data = $query->first();
        if (isset($data)) {
            $approvalJurnal = DB::table('pengeluaranstokheader')
                ->from(
                    DB::raw("pengeluaranstokheader as a with (readuncommitted)")
                )
                ->select(
                    'a.nobukti'
                )
                ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.penerimaan_nobukti', 'b.nobukti')
                ->where('a.nobukti', '=', $data->nobukti)
                ->first();

            if (isset($approvalJurnal)) {
                return [
                    true,
                    $data->nobukti
                ];
            }
        }
        return false;
    }

    public function isPPHApprovedJurnal($id)
    {
        $query = DB::table($this->table)->from($this->table)->where('pengeluaranstokheader.id', $id);
        $data = $query->first();
        if (isset($data)) {
            $approvalJurnal = DB::table('pengeluaranstokheader')
                ->from(
                    DB::raw("pengeluaranstokheader as a with (readuncommitted)")
                )
                ->select(
                    'a.nobukti'
                )
                ->join(DB::raw("pelunasanhutangheader b with (readuncommitted)"), 'a.hutangbayar_nobukti', 'b.nobukti')
                ->join(DB::raw("jurnalumumpusatheader c with (readuncommitted)"), 'b.pengeluaran_nobukti', 'c.nobukti')
                ->where('a.nobukti', '=', $data->nobukti)
                ->first();

            if (isset($approvalJurnal)) {
                return [
                    true,
                    $data->nobukti
                ];
            }
        }
        return false;
    }

    public function printValidation($id)
    {
        $query = DB::table($this->table)->from($this->table)->where('pengeluaranstokheader.id', $id);
        $data = $query->first();
        if (isset($data)) {
            $status = $data->statuscetak;
            $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
            if ($status == $statusCetak->id) {
                return true;
            }
        }
        return false;
    }

    public function isBukaTanggalValidation($date, $pengeluaranstok_id)
    {
        if (auth('api')->user()->isUserPusat()) {
            $kor = Parameter::where('grp', 'KOR MINUS STOK')->where('subgrp', 'KOR MINUS STOK')->first();
            $korv = DB::table('pengeluaranstok')->where('kodepengeluaran', 'KORV')->first();
            if ($kor->text == $pengeluaranstok_id || $korv->id == $pengeluaranstok_id) {
               return true;
            }
        }
        $date = date('Y-m-d', strtotime($date));
        $bukaPengeluaranStok = BukaPengeluaranStok::where('tglbukti', '=', $date)->where('pengeluaranstok_id', '=', $pengeluaranstok_id)->first();
        $tglbatas = $bukaPengeluaranStok->tglbatas ?? 0;
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
        $tidakBolehEdit = DB::table('pengeluaranstokheader')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

        $query = DB::table('pengeluaranstokheader')->from(DB::raw("pengeluaranstokheader with (readuncommitted)"))
            ->select(
                db::raw("isnull(statusapprovaledit,4) as statusedit "),
                'tglbatasedit'
            )
            ->where('id', $id)
            ->first();
        if (isset($query)) {
            if ($query->statusedit != $tidakBolehEdit->id) {
                $limit = strtotime($query->tglbatasedit);
                $now = strtotime('now');
                if ($now < $limit) return true;
            }
        }
        return false;
    }

    public function isKeteranganEditAble($id)
    {
        if (auth('api')->user()->isUserPusat()) {//jika pusat gak wajib
            return true;
        }
        $tidakBolehEdit = DB::table('pengeluaranstokheader')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

        $query = DB::table('pengeluaranstokheader')->from(DB::raw("pengeluaranstokheader with (readuncommitted)"))
            ->select(
                db::raw("isnull(statusapprovaleditketerangan,4) as statusedit "),
                'tglbataseditketerangan'
            )
            ->where('id', $id)
            ->first();
        if (isset($query)) {
            if ($query->statusedit != $tidakBolehEdit->id) {
                $limit = strtotime($query->tglbataseditketerangan);
                $now = strtotime('now');
                if ($now < $limit) return true;
            }
        }
        return false;
    }



    public function processStore(array $data): PengeluaranStokHeader
    {
        $idpengeluaran = $data['pengeluaranstok_id'];
        $fetchFormat =  PengeluaranStok::where('id', $idpengeluaran)->first();

        $statusformat = $fetchFormat->format;
        $fetchGrp = Parameter::where('id', $statusformat)->first();
        $group = $fetchGrp->grp;
        $subGroup = $fetchGrp->subgrp;
        $statusformat = $fetchFormat->format;
        $jamBatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', 'JAMBATASAPPROVAL')->where('subgrp', 'JAMBATASAPPROVAL')->first();
        $tglbatasedit = date('Y-m-d H:i:s', strtotime(date('Y-m-d') . ' ' . $jamBatas->text));

        $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

        $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
        $kor = Parameter::where('grp', 'KOR MINUS STOK')->where('subgrp', 'KOR MINUS STOK')->first();
        $rtr = Parameter::where('grp', 'RETUR STOK')->where('subgrp', 'RETUR STOK')->first();
        $pja = Parameter::where('grp', 'PENJUALAN STOK AFKIR')->where('subgrp', 'PENJUALAN STOK AFKIR')->first();
        $gst = Parameter::where('grp', 'GST STOK')->where('subgrp', 'GST STOK')->first();
        $korv = DB::table('pengeluaranstok')->where('kodepengeluaran', 'KORV')->first();
        $afkir = DB::table('pengeluaranstok')->where('kodepengeluaran', 'AFKIR')->first();
        $statusKirimBerkas = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSKIRIMBERKAS')->where('text', 'BELUM KIRIM BERKAS')->first();


        if ($korv->id == $data['pengeluaranstok_id']) {
            $data['gudang_id'] =  Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first()->text;
        }
        if ($pja->text == $data['pengeluaranstok_id']) {
            $data['gudang_id'] =  Parameter::where('grp', 'GUDANG SEMENTARA')->where('subgrp', 'GUDANG SEMENTARA')->first()->text;
        }
        if ($afkir->id == $data['pengeluaranstok_id']) {
            $data['gudang_id'] =  Parameter::where('grp', 'GUDANG SEMENTARA')->where('subgrp', 'GUDANG SEMENTARA')->first()->text;
        }
        $bank_id = $data['bank_id'] ?? 0;
        $gudang_id = $data['gudang_id'];
        $trado_id = $data['trado_id'];
        $gandengan_id = $data['gandengan_id'];
        $penerimaanstok_nobukti = $data['penerimaanstok_nobukti'];
        $servicein_nobukti = $data['servicein_nobukti'];
        /* Store header */
        $pengeluaranStokHeader = new PengeluaranStokHeader();
        $pengeluaranStokHeader->tglbukti          = date('Y-m-d', strtotime($data['tglbukti']));
        $pengeluaranStokHeader->pengeluaranstok_id = ($data['pengeluaranstok_id'] == null) ? "" : $data['pengeluaranstok_id'];
        $pengeluaranStokHeader->trado_id          = $trado_id;
        $pengeluaranStokHeader->gandengan_id          = $gandengan_id;
        $pengeluaranStokHeader->gudang_id         = $gudang_id;
        $pengeluaranStokHeader->supir_id         = ($data['supir_id'] == null) ? "" : $data['supir_id'];
        $pengeluaranStokHeader->supplier_id         = ($data['supplier_id'] == null) ? "" : $data['supplier_id'];
        $pengeluaranStokHeader->pengeluaranstok_nobukti = ($data['pengeluaranstok_nobukti'] == null) ? "" : $data['pengeluaranstok_nobukti'];
        $pengeluaranStokHeader->penerimaanstokproses_nobukti  = ($data['penerimaanstokproses_nobukti'] == null) ? "" : $data['penerimaanstokproses_nobukti'];
        $pengeluaranStokHeader->penerimaanstok_nobukti  = $penerimaanstok_nobukti;
        $pengeluaranStokHeader->pengeluarantrucking_nobukti  = $data['pengeluarantrucking_nobukti'];
        $pengeluaranStokHeader->servicein_nobukti    = $servicein_nobukti;
        $pengeluaranStokHeader->kerusakan_id         = ($data['kerusakan_id'] == null) ? "" : $data['kerusakan_id'];
        $pengeluaranStokHeader->statusformat      = ($statusformat == null) ? "" : $statusformat;
        $pengeluaranStokHeader->statuspotongretur      = ($data['statuspotongretur'] == null) ? "" : $data['statuspotongretur'];
        $pengeluaranStokHeader->bank_id      = ($bank_id == null) ? "" : $bank_id;
        $pengeluaranStokHeader->tglkasmasuk      = date('Y-m-d', strtotime($data['tglkasmasuk']));
        $pengeluaranStokHeader->modifiedby        = auth('api')->user()->name;
        $pengeluaranStokHeader->info = html_entity_decode(request()->info);
        $pengeluaranStokHeader->statuscetak        = $statusCetak->id ?? 0;
        $pengeluaranStokHeader->tglbatasedit        = $tglbatasedit;
        $pengeluaranStokHeader->statuskirimberkas = $statusKirimBerkas->id;
        $pengeluaranStokHeader->userkirimberkas = '';
        $pengeluaranStokHeader->tglkirimberkas = '';
        $pengeluaranStokHeader->nobukti                  = (new RunningNumberService)->get($group, $subGroup, $pengeluaranStokHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$pengeluaranStokHeader->save()) {
            throw new \Exception("Error storing pengeluaran Stok Header.");
        }

        $pengeluaranstok_id = $data['pengeluaranstok_id'] ?? 0;

        /*STORE DETAIL*/
        $potongHutang = Parameter::where('grp', 'STATUS POTONG RETUR')->where('text', 'POTONG HUTANG')->first();
        $pengeluaranStokDetails = [];
        if (!$data['detail_stok_id']) {
            throw new \Exception("Error storing pengeluaran Stok Detail.");
        }
        if ($idpengeluaran == $kor->text) {
            $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL KOREKSI STOK MINUS')->where('subgrp', 'DEBET')->first();
            $memo = json_decode($getCoaDebet->memo, true);
            $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'KREDIT')->first();
            $memokredit = json_decode($getCoaKredit->memo, true);
        } else if ($idpengeluaran == $rtr->text && ($pengeluaranStokHeader->statuspotongretur == $potongHutang->id)) {
            $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL RETUR POTONG HUTANG')->where('subgrp', 'DEBET')->first();
            $memo = json_decode($getCoaDebet->memo, true);
            $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL RETUR POTONG HUTANG')->where('subgrp', 'KREDIT')->first();
            $memokredit = json_decode($getCoaKredit->memo, true);
        } else if ($idpengeluaran == $pja->text) {
            $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'DEBET')->first();
            $memo = json_decode($getCoaDebet->memo, true);
            $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PENJUALAN STOK AFKIR')->where('subgrp', 'KREDIT')->first();
            $memokredit = json_decode($getCoaKredit->memo, true);
        } else {
            $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'DEBET')->first();
            $memo = json_decode($getCoaDebet->memo, true);
            $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'KREDIT')->first();
            $memokredit = json_decode($getCoaKredit->memo, true);
        }

        $summaryDetail = 0;
        $coadebet_detail = [];
        $coakredit_detail = [];
        $nominal_detail = [];
        $keterangan_detail = [];

        $urutfifo = db::table("pengeluaranstok")->from(db::raw("pengeluaranstok as a with (readuncommitted)"))
            ->select('a.urutfifo')->where('a.id', $pengeluaranstok_id)->first()->urutfifo ?? 0;



        for ($i = 0; $i < count($data['detail_stok_id']); $i++) {
            $zqty = ($data['detail_qty']) ? $data['detail_qty'][$i] : null;
            if ($zqty <> 0 || ($afkir->id == $data['pengeluaranstok_id']) || ($korv->id == $data['pengeluaranstok_id'])) {



                $pengeluarantrucking_nobukti = $data['pengeluarantrucking_nobukti'] ?? '';
                if ($afkir->id == $data['pengeluaranstok_id']) {
                    // $kartustok = KartuStok::getlaporan(date('Y-m-d',strtotime(request()->tgldariheader)), date('Y-m-d',strtotime(request()->tglsampaiheader)),$data['detail_stok_id'][$i], $data['detail_stok_id'][$i], $data['gudang_id'], 0,0, 'GUDANG');
                    // $ks = KartuStok::select('stok_id', DB::raw('SUM(qtymasuk) - SUM(qtykeluar) AS qty'))
                    // ->where('stok_id',$data['detail_stok_id'][$i])
                    // ->groupBy('stok_id')
                    // ->first();
                    $statusafkir = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS KONDISI BAN')->where('subgrp', 'STATUS KONDISI BAN')->where('text', 'AFKIR')->first();
                    $statusNonAktif = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'NON AKTIF')->first();
                    $kelompokAKI = DB::table('kelompok')->from(DB::raw("kelompok with (readuncommitted)"))->where('kodekelompok', 'AKI')->first();
                    $kelompokBAN = DB::table('kelompok')->from(DB::raw("kelompok with (readuncommitted)"))->where('kodekelompok', 'BAN')->first();
                    $stok = (new Stok())->find($data['detail_stok_id'][$i]);
                    if (($kelompokAKI->id == $stok->kelompok_id) || ($kelompokBAN->id == $stok->kelompok_id)) {
                        $stok->statusaktif = $statusNonAktif->id;
                    }
                    $stok->statusban = $statusafkir->id;
                    $stok->save();
                    $data['detail_qty'][$i] = 0;
                }
                if ($afkir->id == $data['pengeluaranstok_id']) {
                    $vulkanisirke = 0;
                } else {
                    $vulkanisirke = ($data['detail_vulkanisirke']) ? $data['detail_vulkanisirke'][$i] : null ?? 0;
                }
            
                // $data3=[
                //     "pengeluaranstokheader_id" => $pengeluaranStokHeader->id,
                //     "nobukti" => $pengeluaranStokHeader->nobukti,
                //     "stok_id" => $data['detail_stok_id'][$i],
                //     "jlhhari" => $data['jlhhari'],
                //     "qty" => ($data['detail_qty']) ? $data['detail_qty'][$i] : null,
                //     "harga" => ($data['detail_harga']) ? $data['detail_harga'][$i] : null,
                //     "persentasediscount" => ($data['detail_persentasediscount']) ? $data['detail_persentasediscount'][$i] : null,
                //     'statusoli' => ($fetchFormat->kodepengeluaran == 'SPK') ? $data['detail_statusoli'][$i] : "",
                //     "vulkanisirke" => $vulkanisirke,
                //     "statusban" => ($data['detail_statusban']) ? $data['detail_statusban'][$i] : null,
                //     "detail_keterangan" => ($data['detail_keterangan']) ? $data['detail_keterangan'][$i] : null,
                //     "detail_statusban" => ($data['detail_statusban']) ? $data['detail_statusban'][$i] : null,
                //     "trado_id" => ($trado_id == null) ? 0 : $trado_id,
                //     "gandengan_id" => ($gandengan_id == null) ? 0 : $gandengan_id,
                //     "gudang_id" => ($gudang_id == null) ? 0 : $gudang_id,

                // ];
                // dd($data3);
                if (!isset($data['detail_statusban'][$i])) {
                    $data['detail_statusban'][$i]=null;
                }
                $pengeluaranStokDetail = (new PengeluaranStokDetail())->processStore($pengeluaranStokHeader, [
                    "pengeluaranstokheader_id" => $pengeluaranStokHeader->id,
                    "nobukti" => $pengeluaranStokHeader->nobukti,
                    "stok_id" => $data['detail_stok_id'][$i],
                    "jlhhari" => ($data['jlhhari']) ? $data['jlhhari'] : null,
                    "qty" => ($data['detail_qty']) ? $data['detail_qty'][$i] : null,
                    "harga" => ($data['detail_harga']) ? $data['detail_harga'][$i] : null,
                    "persentasediscount" => ($data['detail_persentasediscount']) ? $data['detail_persentasediscount'][$i] : null,
                    'statusoli' => ($fetchFormat->kodepengeluaran == 'SPK') ? $data['detail_statusoli'][$i] : "",
                    "vulkanisirke" => $vulkanisirke,
                    "statusban" => ($data['detail_statusban']) ? $data['detail_statusban'][$i] : null,
                    "detail_keterangan" => ($data['detail_keterangan']) ? $data['detail_keterangan'][$i] : null,
                    "detail_statusban" => ($data['detail_statusban']) ? $data['detail_statusban'][$i] : null,
                    "trado_id" => ($trado_id == null) ? 0 : $trado_id,
                    "gandengan_id" => ($gandengan_id == null) ? 0 : $gandengan_id,
                    "gudang_id" => ($gudang_id == null) ? 0 : $gudang_id,

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
                    $datastok  = Stok::lockForUpdate()->where("id", $data['detail_stok_id'][$i])->firstorFail();
                    if ($korv->id == $data['pengeluaranstok_id']) {
                        $datastok->totalvulkanisir = $totalvulkan;
                        $datastok->statusban = ($data['detail_statusban']) ? $data['detail_statusban'][$i] : null;
                        $datastok->save();
                    }
                }
                // end update vulkanisir


                $ksgudang_id = $gudang_id ?? 0;
                $kstrado_id = $trado_id ?? 0;
                $ksgandengan_id = $gandengan_id ?? 0;


                $datadetailfifo = [
                    "pengeluaranstokheader_id" => $pengeluaranStokHeader->id,
                    "pengeluaranstok_id" => $data['pengeluaranstok_id'],
                    "nobukti" => $pengeluaranStokHeader->nobukti,
                    "stok_id" => $data['detail_stok_id'][$i],
                    "gudang_id" => $gudang_id,
                    "tglbukti" => $data['tglbukti'],
                    "qty" => $data['detail_qty'][$i],
                    "total" => $data['detail_qty'][$i],
                    "modifiedby" => auth('api')->user()->name,
                    "keterangan" => $data['keterangan'] ?? '',
                    "detail_keterangan" => $data['detail_keterangan'][$i] ?? '',
                    "detail_harga" => $data['detail_harga'][$i] ?? '',
                    "statusformat" => $statusformat,
                ];


                if (($ksgudang_id == 0 && ($pengeluaranstok_id != 1 && $pengeluaranstok_id != 5)) || ($pja->text == $data['pengeluaranstok_id'])) {

                    $ksqty = $data['detail_qty'][$i] ?? 0;
                    $ksharga = $data['detail_harga'][$i] ?? 0;
                    $kstotal = $ksqty * $ksharga;
                    $ksnobukti = $pengeluaranStokHeader->nobukti ?? '';

                    if ($pja->text == $data['pengeluaranstok_id']) {
                        $kartuStok = (new KartuStok())->processStore([
                            "gudang_id" =>  $ksgudang_id,
                            "trado_id" =>  $kstrado_id,
                            "gandengan_id" => $ksgandengan_id,
                            "stok_id" => $data['detail_stok_id'][$i] ?? 0,
                            "nobukti" => $ksnobukti ?? '',
                            "tglbukti" => date('Y-m-d', strtotime($data['tglbukti'])),
                            "qtymasuk" => 0,
                            "nilaimasuk" =>  0,
                            "qtykeluar" =>  $ksqty ?? 0,
                            "nilaikeluar" => 0,
                            "urutfifo" => $urutfifo,
                        ]);
                    } else {
                        $kartuStok = (new KartuStok())->processStore([
                            "gudang_id" =>  $ksgudang_id,
                            "trado_id" =>  $kstrado_id,
                            "gandengan_id" => $ksgandengan_id,
                            "stok_id" => $data['detail_stok_id'][$i] ?? 0,
                            "nobukti" => $ksnobukti ?? '',
                            "tglbukti" => date('Y-m-d', strtotime($data['tglbukti'])),
                            "qtymasuk" => 0,
                            "nilaimasuk" =>  0,
                            "qtykeluar" =>  $ksqty ?? 0,
                            "nilaikeluar" => $kstotal,
                            "urutfifo" => $urutfifo,
                        ]);
                    }
                }
                if ($pengeluaranstok_id == 1 ||  $pengeluaranstok_id == 5) {

                    $reuse = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                        ->select('a.id')
                        ->where('grp', 'STATUS REUSE')
                        ->where('subgrp', 'STATUS REUSE')
                        ->where('text', 'REUSE')
                        ->first()->id ?? 0;
                    $stokid = $data['detail_stok_id'][$i] ?? 0;
                    $stokreuse = db::table("stok")->from(db::raw("stok a with (readuncommitted)"))
                        ->select(
                            'a.id'
                        )
                        ->where('a.id', $stokid)
                        ->where('a.statusreuse', $reuse)
                        ->first();

                    if (isset($stokreuse)) {

                        $ksqty = $data['detail_qty'][$i] ?? 0;
                        $ksharga = $data['detail_harga'][$i] ?? 0;
                        $kstotal = $ksqty * $ksharga;
                        $ksnobukti = $pengeluaranStokHeader->nobukti ?? '';

                        $kartuStok = (new KartuStok())->processStore([
                            "gudang_id" =>  $ksgudang_id,
                            "trado_id" =>  $kstrado_id,
                            "gandengan_id" => $ksgandengan_id,
                            "stok_id" => $data['detail_stok_id'][$i] ?? 0,
                            "nobukti" => $ksnobukti ?? '',
                            "tglbukti" => date('Y-m-d', strtotime($data['tglbukti'])),
                            "qtymasuk" => $ksqty ?? 0,
                            "nilaimasuk" =>  0,
                            "qtykeluar" =>  0,
                            "nilaikeluar" => 0,
                            "urutfifo" => $urutfifo,
                        ]);
                    }
                }


                if (($kor->text != $data['pengeluaranstok_id'])) {
                    $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
                    $datadetailfifo['gudang_id'] = $gudangkantor->text;
                }
                //hanya pja dan koreksi yang tidak dari gudang yang tidak menggunakan fifo
                if ((($kor->text == $data['pengeluaranstok_id']) && $gudang_id) || (($kor->text != $data['pengeluaranstok_id']) && ($pja->text != $data['pengeluaranstok_id']) && ($korv->id != $data['pengeluaranstok_id']) && ($afkir->id != $data['pengeluaranstok_id']))) {
                    (new PengeluaranStokDetailFifo())->processStore($pengeluaranStokHeader, $datadetailfifo);
                }

                $pengeluaranStokDetail = PengeluaranStokDetail::find($pengeluaranStokDetail->id);
                $pengeluaranStokDetails[] = $pengeluaranStokDetail->toArray();
                $coadebet_detail[] = $memo['JURNAL'];
                $coakredit_detail[] = $memokredit['JURNAL'];
                // $nominal_detail[] = $pengeluaranStokDetail->total;
                // $summaryDetail += $pengeluaranStokDetail->total;
                $keterangan_detail[] = $data['detail_keterangan'][$i] ?? 'PENGELUARAN STOK RETUR';

                $pengeluaranStokDetail = PengeluaranStokDetail::where('id', $pengeluaranStokDetail->id)->first();

                $nominal_detail[] = $pengeluaranStokDetail->total;
                $tgljatuhtempo[] = date('Y-m-d', strtotime($data['tglbukti']));
                $summaryDetail += $pengeluaranStokDetail->total;
            }
        }


        /*STORE JURNAL*/
        $jurnalRequest = [
            'tanpaprosesnobukti' => 1,
            'nobukti' => $pengeluaranStokHeader->nobukti,
            'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
            'postingdari' => "ENTRY PENGELUARAN STOK ($fetchFormat->kodepengeluaran)",
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

        if ($rtr->text == $data['pengeluaranstok_id']) {
            $potongKas = Parameter::where('grp', 'STATUS POTONG RETUR')->where('text', 'POSTING KE KAS/BANK')->first();
            $potongHutang = Parameter::where('grp', 'STATUS POTONG RETUR')->where('text', 'POTONG HUTANG')->first();

            if ($pengeluaranStokHeader->statuspotongretur == $potongKas->id) {
                //jika potongkas                
                /*STORE PENERIMAANHEADER*/
                $coaKasMasuk = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('memo')->where('grp', 'JURNAL RETUR STOK')->where('subgrp', 'KREDIT')->first();
                $memo = json_decode($coaKasMasuk->memo, true);
                $bank = Bank::select('coa', 'formatpenerimaan', 'tipe')->where('id', $pengeluaranStokHeader->bank_id)->first();
                $parameter = Parameter::where('id', $bank->formatpenerimaan)->first();
                if ($bank->tipe == 'KAS') {
                    $statusKas = Parameter::where('grp', 'STATUS KAS')->where('text', 'KAS')->first();
                }
                if ($bank->tipe == 'BANK') {
                    $statusKas = Parameter::where('grp', 'STATUS KAS')->where('text', 'BUKAN STATUS KAS')->first();
                }
                $bankid = $pengeluaranStokHeader->bank_id;
                $querysubgrppenerimaan = DB::table('bank')->from(DB::raw("bank with (readuncommitted)"))->select(
                    'parameter.grp',
                    'parameter.subgrp',
                    'bank.formatpenerimaan',
                    'bank.coa'
                )
                    ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')
                    ->whereRaw("bank.id = $bankid")
                    ->first();

                $pengeluaranStokHeader->coa = $querysubgrppenerimaan->coa;
                $group = $parameter->grp;
                $subgroup = $parameter->subgrp;
                $format = DB::table('parameter')->where('grp', $group)->where('subgrp', $subgroup)->first();

                $pengeluaranStokHeader->coa = $querysubgrppenerimaan->coa;

                $penerimaanRequest = [
                    'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'postingdari' => "ENTRY PENGELUARAN STOK ($fetchFormat->kodepengeluaran)",
                    'statusapproval' => $statusApproval->id,
                    'pelanggan_id' => 0,
                    'agen_id' => 0,
                    'diterimadari' => "RETUR STOK",
                    'tgllunas' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'statusformat' => $format->id,
                    'bank_id' => $pengeluaranStokHeader->bank_id,

                    'nowarkat' => null,
                    'tgljatuhtempo' => date('Y-m-d', strtotime($data['tglbukti'])),
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
                $pengeluaranStokHeader->coa = $querysubgrppenerimaan->coa;
                $pengeluaranStokHeader->penerimaan_nobukti = $penerimaanHeader->nobukti;
                $pengeluaranStokHeader->save();

                $jurnalselisihfifodebet = db::table("parameter")->from(db::raw("parameter as a with (readuncommitted)"))->select('memo')->where('grp', 'JURNAL SELISIH FIFO')->where('subgrp', 'DEBET')->first();
                $jurnalselisihfifokredit = db::table("parameter")->from(db::raw("parameter as a with (readuncommitted)"))->select('memo')->where('grp', 'JURNAL SELISIH FIFO')->where('subgrp', 'KREDIT')->first();

                $jurnalnominaldetail = db::table("pengeluaranstokdetail")->from(db::raw("pengeluaranstokdetail as a with (readuncommitted)"))
                    ->select(
                        db::raw("sum(isnull(selisihhargafifo,0)) as selisihhargafifo")
                    )
                    ->where('nobukti', $pengeluaranStokHeader->nobukti)->first()->selisihhargafifo ?? 0;

                $jurnalnominaldetailreal = db::table("pengeluaranstokdetail")->from(db::raw("pengeluaranstokdetail as a with (readuncommitted)"))
                    ->select(
                        db::raw("sum(isnull(selisihhargafifo,0)+isnull(total,0)) as total")
                    )
                    ->where('nobukti', $pengeluaranStokHeader->nobukti)->first()->total ?? 0;

                if ($jurnalnominaldetail != 0) {

                    $jurnalmemodebet = json_decode($jurnalselisihfifodebet->memo, true);
                    $jurnalmemokredit = json_decode($jurnalselisihfifokredit->memo, true);
                    $jurnalcoadebet_detail[] = $jurnalmemodebet['JURNAL'];
                    $jurnalcoakredit_detail[] = $jurnalmemokredit['JURNAL'];
                    $jurnalnominal_detail[] = $jurnalnominaldetailreal;
                    $jurnalketerangan_detail[] = $keterangan_detail[0];

                    $jurnalRequest = [
                        'tanpaprosesnobukti' => 1,
                        'nobukti' => $pengeluaranStokHeader->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                        'postingdari' => "ENTRY PENGELUARAN STOK ($fetchFormat->kodepengeluaran)",
                        'statusapproval' => $statusApproval->id,
                        'userapproval' => "",
                        'tglapproval' => "",
                        'modifiedby' => auth('api')->user()->name,
                        'statusformat' => "0",
                        'coakredit_detail' => $jurnalcoakredit_detail,
                        'coadebet_detail' => $jurnalcoadebet_detail,
                        'nominal_detail' => $jurnalnominal_detail,
                        'keterangan_detail' => $jurnalketerangan_detail
                    ];
                }

                if ($jurnalnominaldetailreal != 0) {

                    $jurnalmemodebet = json_decode($jurnalselisihfifodebet->memo, true);
                    $jurnalmemokredit = json_decode($jurnalselisihfifokredit->memo, true);
                    $jurnalcoadebet_detail[] = $jurnalmemokredit['JURNAL'];
                    $jurnalcoakredit_detail[] = $jurnalmemodebet['JURNAL'];
                    $jurnalnominal_detail[] = $jurnalnominaldetail;
                    $jurnalketerangan_detail[] = $keterangan_detail[0];

                    $jurnalRequest = [
                        'tanpaprosesnobukti' => 1,
                        'nobukti' => $pengeluaranStokHeader->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                        'postingdari' => "ENTRY PENGELUARAN STOK ($fetchFormat->kodepengeluaran)",
                        'statusapproval' => $statusApproval->id,
                        'userapproval' => "",
                        'tglapproval' => "",
                        'modifiedby' => auth('api')->user()->name,
                        'statusformat' => "0",
                        'coakredit_detail' => $jurnalcoakredit_detail,
                        'coadebet_detail' => $jurnalcoadebet_detail,
                        'nominal_detail' => $jurnalnominal_detail,
                        'keterangan_detail' => $jurnalketerangan_detail
                    ];

                    $jurnalUmumHeader = (new JurnalUmumHeader())->processStore($jurnalRequest);
                }
            } else if ($pengeluaranStokHeader->statuspotongretur == $potongHutang->id) {
                //jika potonghutang
                /*STORE HUTANGBAYAR*/

                $statusbayarhutang = db::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
                    ->select('id')->where('grp', 'PELUNASANHUTANG')->where('subgrp', 'PELUNASANHUTANG')->where('text', 'POTONG HUTANG (RETUR)')
                    ->first()->id ?? 0;
                $bank = db::table('bank')->from(db::raw("bank a with (readuncommitted)"))
                    ->select('id')->where('tipe', 'KAS')
                    ->first()->id ?? 0;
                $penerimaanstok = Penerimaanstokheader::where('nobukti', $data['penerimaanstok_nobukti'])->first();
                $hutang = HutangHeader::where('nobukti', $penerimaanstok->hutang_nobukti)->first();
                $bank = ($bank_id == null) ? $bank : $bank_id;
                // dd($bank);
                $hutangBayarRequest = [
                    'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),

                    'bank_id' => $bank,
                    'nowarkat' => "",
                    'supplier_id' => $data['supplier_id'],
                    'hutang_nobukti' => [$hutang->nobukti],
                    'statusapproval' => $statusApproval->id ?? 0,
                    'statusbayarhutang' => $statusbayarhutang,
                    'alatbayar_id' => 0,
                    'tglcair' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'bayar' => [$summaryDetail],
                    'hutang_id' => [$hutang->id],
                    'potongan' => [0],
                    // potongan => $summaryDetail,
                    'keterangan' => [$keterangan_detail[0]],
                ];
                // return response([$hutangHeader],422);

                $hutangBayarHeader = (new PelunasanHutangHeader())->processStore($hutangBayarRequest);
                $pengeluaranStokHeader->hutangbayar_nobukti = $hutangBayarHeader->nobukti;
                $pengeluaranStokHeader->save();

                $jurnalcoadebet_detail = [];
                $jurnalcoakredit_detail = [];
                $jurnalnominal_detail = [];
                $jurnalketerangan_detail = [];

                for ($i = 0; $i <= 1; $i++) {
                    if ($i == 0) {
                        $jurnalcoadebet_detail = $coadebet_detail;
                        $jurnalcoakredit_detail = $coakredit_detail;
                        $jurnalketerangan_detail[] = $keterangan_detail[0];
                        $jurnalnominal_detail = $nominal_detail;
                    } else {
                        $jurnalselisihfifodebet = db::table("parameter")->from(db::raw("parameter as a with (readuncommitted)"))->select('memo')->where('grp', 'JURNAL SELISIH FIFO')->where('subgrp', 'DEBET')->first();
                        $jurnalselisihfifokredit = db::table("parameter")->from(db::raw("parameter as a with (readuncommitted)"))->select('memo')->where('grp', 'JURNAL SELISIH FIFO')->where('subgrp', 'KREDIT')->first();
                        $jurnalnominaldetail = db::table("pengeluaranstokdetail")->from(db::raw("pengeluaranstokdetail as a with (readuncommitted)"))
                            ->select(
                                db::raw("sum(isnull(selisihhargafifo,0)) as selisihhargafifo")
                            )
                            ->where('nobukti', $pengeluaranStokHeader->nobukti)->first()->selisihhargafifo ?? 0;

                        $jurnalnominaldetailreal = db::table("pengeluaranstokdetail")->from(db::raw("pengeluaranstokdetail as a with (readuncommitted)"))
                            ->select(
                                db::raw("sum(isnull(selisihhargafifo,0)+isnull(total,0)) as total")
                            )
                            ->where('nobukti', $pengeluaranStokHeader->nobukti)->first()->total ?? 0;

                        if ($jurnalnominaldetail != 0) {
                            $jurnalmemodebet = json_decode($jurnalselisihfifodebet->memo, true);
                            $jurnalmemokredit = json_decode($jurnalselisihfifokredit->memo, true);
                            $jurnalcoadebet_detail[] = $jurnalmemodebet['JURNAL'];
                            $jurnalcoakredit_detail[] = $jurnalmemokredit['JURNAL'];
                            $jurnalnominal_detail[] = $jurnalnominaldetailreal;
                            $jurnalketerangan_detail[] = $keterangan_detail[0];
                        }
                        if ($jurnalnominaldetail != 0) {
                            $jurnalmemodebet = json_decode($jurnalselisihfifodebet->memo, true);
                            $jurnalmemokredit = json_decode($jurnalselisihfifokredit->memo, true);
                            $jurnalcoadebet_detail[] = $jurnalmemokredit['JURNAL'];
                            $jurnalcoakredit_detail[] = $jurnalmemodebet['JURNAL'];
                            $jurnalnominal_detail[] = $jurnalnominaldetail;
                            $jurnalketerangan_detail[] = $keterangan_detail[0];
                        }
                    }
                }

                $jurnalRequest = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $pengeluaranStokHeader->nobukti,
                    'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'postingdari' => "ENTRY PENGELUARAN STOK ($fetchFormat->kodepengeluaran)",
                    'statusapproval' => $statusApproval->id,
                    'userapproval' => "",
                    'tglapproval' => "",
                    'modifiedby' => auth('api')->user()->name,
                    'statusformat' => "0",
                    'coakredit_detail' => $jurnalcoakredit_detail,
                    'coadebet_detail' => $jurnalcoadebet_detail,
                    'nominal_detail' => $jurnalnominal_detail,
                    'keterangan_detail' => $jurnalketerangan_detail
                ];
                // dd( $jurnalRequest);



                $jurnalUmumHeader = (new JurnalUmumHeader())->processStore($jurnalRequest);
            }
        } else if ($pja->text == $data['pengeluaranstok_id'] && ($pengeluaranStokHeader->bank_id != null)) {
            //jika potongkas

            /*STORE PENERIMAANHEADER*/
            $coaKasMasuk = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('memo')->where('grp', 'JURNAL PENJUALAN STOK AFKIR')->where('subgrp', 'KREDIT')->first();
            $memo = json_decode($coaKasMasuk->memo, true);
            $bank = Bank::select('coa', 'formatpenerimaan', 'tipe')->where('id', $pengeluaranStokHeader->bank_id)->first();
            $parameter = Parameter::where('id', $bank->formatpenerimaan)->first();
            if ($bank->tipe == 'KAS') {
                $statusKas = Parameter::where('grp', 'STATUS KAS')->where('text', 'KAS')->first();
            }
            if ($bank->tipe == 'BANK') {
                $statusKas = Parameter::where('grp', 'STATUS KAS')->where('text', 'BUKAN STATUS KAS')->first();
            }
            $bankid = $pengeluaranStokHeader->bank_id;
            $querysubgrppenerimaan = DB::table('bank')->from(DB::raw("bank with (readuncommitted)"))->select(
                'parameter.grp',
                'parameter.subgrp',
                'bank.formatpenerimaan',
                'bank.coa'
            )
                ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')
                ->whereRaw("bank.id = $bankid")
                ->first();

            $pengeluaranStokHeader->coa = $querysubgrppenerimaan->coa;
            $group = $parameter->grp;
            $subgroup = $parameter->subgrp;
            $format = DB::table('parameter')->where('grp', $group)->where('subgrp', $subgroup)->first();

            $pengeluaranStokHeader->coa = $querysubgrppenerimaan->coa;

            $penerimaanRequest = [
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'postingdari' => "ENTRY PENGELUARAN STOK ($fetchFormat->kodepengeluaran)",
                'statusapproval' => $statusApproval->id,
                'pelanggan_id' => 0,
                'agen_id' => 0,
                'diterimadari' => "PENJUALAN STOK AFKIR",
                'tgllunas' => date('Y-m-d', strtotime($data['tglbukti'])),
                'statusformat' => $format->id,
                'bank_id' => $pengeluaranStokHeader->bank_id,

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
            $pengeluaranStokHeader->coa = $querysubgrppenerimaan->coa;
            $pengeluaranStokHeader->penerimaan_nobukti = $penerimaanHeader->nobukti;
            $pengeluaranStokHeader->save();
        } else if ($korv->id == $data['pengeluaranstok_id']) {
        } else {
            $jurnalUmumHeader = (new JurnalUmumHeader())->processStore($jurnalRequest);
        }

        $pengeluaranStokHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengeluaranStokHeader->getTable()),
            'postingdari' => "ENTRY PENGELUARAN STOK ($fetchFormat->kodepengeluaran)",
            'idtrans' => $pengeluaranStokHeader->id,
            'nobuktitrans' => $pengeluaranStokHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pengeluaranStokHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        //store logtrail detail
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengeluaranStokDetail->getTable()),
            'postingdari' => "ENTRY PENGELUARAN STOK ($fetchFormat->kodepengeluaran)",
            'idtrans' =>  $pengeluaranStokHeaderLogTrail->id,
            'nobuktitrans' => $pengeluaranStokHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pengeluaranStokDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);


        return $pengeluaranStokHeader;
    }

    public function processUpdate(PengeluaranStokHeader $pengeluaranStokHeader, array $data): PengeluaranStokHeader
    {
        $idpengeluaran = $pengeluaranStokHeader->pengeluaranstok_id;
        $fetchFormat =  PengeluaranStok::where('id', $idpengeluaran)->first();

        $statusformat = $fetchFormat->format;
        $fetchGrp = Parameter::where('id', $statusformat)->first();
        $group = $fetchGrp->grp;
        $subGroup = $fetchGrp->subgrp;
        $statusformat = $fetchFormat->format;
        $datahitungstok = $fetchFormat;
        $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();


        $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

        $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
        $kor = Parameter::where('grp', 'KOR MINUS STOK')->where('subgrp', 'KOR MINUS STOK')->first();
        $rtr = Parameter::where('grp', 'RETUR STOK')->where('subgrp', 'RETUR STOK')->first();
        $pja = Parameter::where('grp', 'PENJUALAN STOK AFKIR')->where('subgrp', 'PENJUALAN STOK AFKIR')->first();
        $korv = DB::table('pengeluaranstok')->where('kodepengeluaran', 'KORV')->first();
        $afkir = DB::table('pengeluaranstok')->where('kodepengeluaran', 'AFKIR')->first();

        if (array_key_exists("statuspotongretur", $data)) {
            $statuspotongretur = $data['statuspotongretur'];
        } else {
            $statuspotongretur = null;
        }
        if (array_key_exists("gudang_id", $data)) {
            $gudang_id = $data['gudang_id'];
        } else {
            $gudang_id = null;
        }
        if (array_key_exists("trado_id", $data)) {
            $trado_id = $data['trado_id'];
        } else {
            $trado_id = null;
        }
        if (array_key_exists("gandengan_id", $data)) {
            $gandengan_id = $data['gandengan_id'];
        } else {
            $gandengan_id = null;
        }
        if (array_key_exists("penerimaanstok_nobukti", $data)) {
            $penerimaanstok_nobukti = $data['penerimaanstok_nobukti'];
        } else {
            $penerimaanstok_nobukti = null;
        }
        if (array_key_exists("servicein_nobukti", $data)) {
            $servicein_nobukti = $data['servicein_nobukti'];
        } else {
            $servicein_nobukti = null;
        }
        $statuspotongretur = $pengeluaranStokHeader->statuspotongretur;

        /* Store header */
        $pengeluaranStokHeader->tglbukti          = date('Y-m-d', strtotime($data['tglbukti']));
        $pengeluaranStokHeader->trado_id          = $trado_id;
        $pengeluaranStokHeader->gandengan_id          = $gandengan_id;
        $pengeluaranStokHeader->gudang_id         = $gudang_id;
        $pengeluaranStokHeader->supir_id         = ($data['supir_id'] == null) ? "" : $data['supir_id'];
        $pengeluaranStokHeader->supplier_id         = ($data['supplier_id'] == null) ? "" : $data['supplier_id'];
        $pengeluaranStokHeader->pengeluaranstok_nobukti = ($data['pengeluaranstok_nobukti'] == null) ? "" : $data['pengeluaranstok_nobukti'];
        $pengeluaranStokHeader->penerimaanstok_nobukti  = $penerimaanstok_nobukti;
        $pengeluaranStokHeader->pengeluarantrucking_nobukti  = $data['pengeluarantrucking_nobukti'];
        $pengeluaranStokHeader->servicein_nobukti    = $servicein_nobukti;
        $pengeluaranStokHeader->kerusakan_id         = ($data['kerusakan_id'] == null) ? "" : $data['kerusakan_id'];
        $pengeluaranStokHeader->statusformat      = ($statusformat == null) ? "" : $statusformat;
        $pengeluaranStokHeader->statuspotongretur      = $statuspotongretur;
        $pengeluaranStokHeader->bank_id      = ($data['bank_id'] == null) ? "" : $data['bank_id'];
        $pengeluaranStokHeader->tglkasmasuk      = date('Y-m-d', strtotime($data['tglkasmasuk']));
        $pengeluaranStokHeader->modifiedby        = auth('api')->user()->name;
        $pengeluaranStokHeader->info = html_entity_decode(request()->info);
        $pengeluaranStokHeader->statuscetak        = $statusCetak->id ?? 0;

        if (!$pengeluaranStokHeader->save()) {
            throw new \Exception("Error storing pengeluaran Stok Header.");
        }



        /*RETURN STOK PENERIMAAN*/
        if ($datahitungstok->statushitungstok == $statushitungstok->id) {
            $datadetail = PengeluaranStokDetail::select('stok_id', 'qty')->where('pengeluaranstokheader_id', '=', $pengeluaranStokHeader->id)->get();
            (new PengeluaranStokDetail())->resetQtyPenerimaan($pengeluaranStokHeader->id);
        }

        if ($pengeluaranStokHeader->pengeluaranstok_id == $korv->id) {
            (new PengeluaranStokDetail())->returnVulkanisir($pengeluaranStokHeader->id);
        }
        $pengeluaranstok_id = $data['pengeluaranstok_id'] ?? 0;


        $urutfifo = db::table("pengeluaranstok")->from(db::raw("pengeluaranstok as a with (readuncommitted)"))
            ->select('a.urutfifo')->where('a.id', $pengeluaranstok_id)->first()->urutfifo ?? 0;

        // dd('asdas');
        /*DELETE EXISTING DETAIL*/
        $pengeluaranStokDetail = PengeluaranStokDetail::where('pengeluaranstokheader_id', $pengeluaranStokHeader->id)->lockForUpdate()->delete();
        $pengeluaranStokDetailFifo = PengeluaranStokDetailFifo::where('pengeluaranstokheader_id', $pengeluaranStokHeader->id)->lockForUpdate()->delete();
        $kartuStok = KartuStok::where('nobukti', $pengeluaranStokHeader->nobukti)->lockForUpdate()->delete();

        $potongKas = Parameter::where('grp', 'STATUS POTONG RETUR')->where('text', 'POSTING KE KAS/BANK')->first();
        $potongHutang = Parameter::where('grp', 'STATUS POTONG RETUR')->where('text', 'POTONG HUTANG')->first();
        /*STORE DETAIL*/
        $pengeluaranStokDetails = [];
        if (!$data['detail_stok_id']) {
            throw new \Exception("Error storing pengeluaran Stok Detail.");
        }
        if ($idpengeluaran == $kor->text) {
            $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL KOREKSI STOK MINUS')->where('subgrp', 'DEBET')->first();
            $memo = json_decode($getCoaDebet->memo, true);
            $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'KREDIT')->first();
            $memokredit = json_decode($getCoaKredit->memo, true);
        } else if ($idpengeluaran == $rtr->text && ($pengeluaranStokHeader->statuspotongretur == $potongHutang->id)) {
            $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL RETUR POTONG HUTANG')->where('subgrp', 'DEBET')->first();
            $memo = json_decode($getCoaDebet->memo, true);
            $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL RETUR POTONG HUTANG')->where('subgrp', 'KREDIT')->first();
            $memokredit = json_decode($getCoaKredit->memo, true);
        } else if ($idpengeluaran == $pja->text) {
            $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'DEBET')->first();
            $memo = json_decode($getCoaDebet->memo, true);
            $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PENJUALAN STOK AFKIR')->where('subgrp', 'KREDIT')->first();
            $memokredit = json_decode($getCoaKredit->memo, true);
        } else {
            $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'DEBET')->first();
            $memo = json_decode($getCoaDebet->memo, true);
            $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'KREDIT')->first();
            $memokredit = json_decode($getCoaKredit->memo, true);
        }

        $summaryDetail = 0;
        $coadebet_detail = [];
        $coakredit_detail = [];
        $nominal_detail = [];
        $keterangan_detail = [];

        $pengeluaranstok_id = $data['pengeluaranstok_id'] ?? 0;
        $urutfifo = db::table("pengeluaranstok")->from(db::raw("pengeluaranstok as a with (readuncommitted)"))
            ->select('a.urutfifo')->where('a.id', $pengeluaranstok_id)->first()->urutfifo ?? 0;


        for ($i = 0; $i < count($data['detail_stok_id']); $i++) {
            // $total = $data['detail_harga'][$i] * $data['detail_qty'][$i];

            $pengeluarantrucking_nobukti = $data['pengeluarantrucking_nobukti'] ?? '';
            if ($afkir->id == $data['pengeluaranstok_id']) {
                // $kartustok = KartuStok::getlaporan(date('Y-m-d',strtotime(request()->tgldariheader)), date('Y-m-d',strtotime(request()->tglsampaiheader)),$data['detail_stok_id'][$i], $data['detail_stok_id'][$i], $data['gudang_id'], 0,0, 'GUDANG');
                // $ks = KartuStok::select('stok_id', DB::raw('SUM(qtymasuk) - SUM(qtykeluar) AS qty'))
                // ->where('stok_id',$data['detail_stok_id'][$i])
                // ->groupBy('stok_id')
                // ->first();
                $statusafkir = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS KONDISI BAN')->where('subgrp', 'STATUS KONDISI BAN')->where('text', 'AFKIR')->first();
                $statusNonAktif = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'NON AKTIF')->first();
                $kelompokAKI = DB::table('kelompok')->from(DB::raw("kelompok with (readuncommitted)"))->where('kodekelompok', 'AKI')->first();
                $kelompokBAN = DB::table('kelompok')->from(DB::raw("kelompok with (readuncommitted)"))->where('kodekelompok', 'BAN')->first();
                $stok = (new Stok())->find($data['detail_stok_id'][$i]);
                $stok->statusban = $statusafkir->id;
                if (($kelompokAKI->id == $stok->kelompok_id) || ($kelompokBAN->id == $stok->kelompok_id)) {
                    $stok->statusaktif = $statusNonAktif->id;
                }
                $stok->save();
                $data['detail_qty'][$i] = 0;
            }

            if ($afkir->id ==  $pengeluaranstok_id) {
                $vulkanisirke = 0;
            } else {
                $vulkanisirke = ($data['detail_vulkanisirke']) ? $data['detail_vulkanisirke'][$i] : null ?? 0;
            }

            if ($spk->text ==  $pengeluaranstok_id) {
                $data['detail_statusban'] = null;
            }

            $pengeluaranStokDetail = (new PengeluaranStokDetail())->processStore($pengeluaranStokHeader, [
                "pengeluaranstokheader_id" => $pengeluaranStokHeader->id,
                "nobukti" => $pengeluaranStokHeader->nobukti,
                "stok_id" => $data['detail_stok_id'][$i],
                "jlhhari" => ($data['jlhhari']) ? $data['jlhhari'] : null,
                "qty" => ($data['detail_qty']) ? $data['detail_qty'][$i] : null,
                "harga" => ($data['detail_harga']) ? $data['detail_harga'][$i] : null,
                "persentasediscount" => ($data['detail_persentasediscount']) ? $data['detail_persentasediscount'][$i] : null,
                'statusoli' => ($fetchFormat->kodepengeluaran == 'SPK') ? $data['detail_statusoli'][$i] : "",
                "vulkanisirke" => $vulkanisirke,
                "statusban" => ($data['detail_statusban']) ? $data['detail_statusban'][$i] : null,
                "detail_keterangan" => ($data['detail_keterangan']) ? $data['detail_keterangan'][$i] : null,
                "detail_statusban" => ($data['detail_statusban']) ? $data['detail_statusban'][$i] : null,
                "trado_id" => ($trado_id == null) ? "" : $trado_id,
                "gandengan_id" => ($gandengan_id == null) ? "" : $gandengan_id,
                "gudang_id" => ($gudang_id == null) ? "" : $gudang_id,

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
                if ($pengeluaranStokHeader->pengeluaranstok_id == $korv->id) {
                    $datastok->totalvulkanisir = $totalvulkan;
                    $datastok->statusban = ($data['detail_statusban']) ? $data['detail_statusban'][$i] : null;
                    $datastok->save();
                }
            }
            // end update vulkanisir

            $pengeluaranStokDetails[] = $pengeluaranStokDetail->toArray();
            $coadebet_detail[] = $memo['JURNAL'];
            $coakredit_detail[] = $memokredit['JURNAL'];
            $tgljatuhtempo[] = date('Y-m-d', strtotime($data['tglbukti']));
            // $nominal_detail[] = $pengeluaranStokDetail->total;
            // $summaryDetail += $pengeluaranStokDetail->total;
            $keterangan_detail[] = $data['detail_keterangan'][$i] ?? 'PENGELUARAN STOK RETUR';

            $ksgudang_id = $gudang_id ?? 0;
            $kstrado_id = $trado_id ?? 0;
            $ksgandengan_id = $gandengan_id ?? 0;



            $datadetailfifo = [
                "pengeluaranstokheader_id" => $pengeluaranStokHeader->id,
                "pengeluaranstok_id" => $fetchFormat->id,
                "nobukti" => $pengeluaranStokHeader->nobukti,
                "stok_id" => $data['detail_stok_id'][$i],
                "gudang_id" => $data['gudang_id'],
                "tglbukti" => $data['tglbukti'],
                "qty" => $data['detail_qty'][$i],
                "modifiedby" => auth('api')->user()->name,
                "keterangan" => $data['keterangan'] ?? '',
                "detail_keterangan" => $data['detail_keterangan'][$i] ?? '',
                "detail_harga" => $data['detail_harga'][$i] ?? '',
                "statusformat" => $statusformat,
            ];



            if ($ksgudang_id == 0 && ($pengeluaranstok_id != 1) || ($pja->text == $data['pengeluaranstok_id'])) {

                $ksqty = $data['detail_qty'][$i] ?? 0;
                $ksharga = $data['detail_harga'][$i] ?? 0;
                $kstotal = $ksqty * $ksharga;
                $ksnobukti = $pengeluaranStokHeader->nobukti ?? '';

                if ($pja->text == $data['pengeluaranstok_id']) {
                    $kartuStok = (new KartuStok())->processStore([
                        "gudang_id" =>  $ksgudang_id,
                        "trado_id" =>  $kstrado_id,
                        "gandengan_id" => $ksgandengan_id,
                        "stok_id" => $data['detail_stok_id'][$i] ?? 0,
                        "nobukti" => $ksnobukti ?? '',
                        "tglbukti" => date('Y-m-d', strtotime($data['tglbukti'])),
                        "qtymasuk" => 0,
                        "nilaimasuk" =>  0,
                        "qtykeluar" =>  $ksqty ?? 0,
                        "nilaikeluar" => 0,
                        "urutfifo" => $urutfifo,
                    ]);
                } else {
                    $kartuStok = (new KartuStok())->processStore([
                        "gudang_id" =>  $ksgudang_id,
                        "trado_id" =>  $kstrado_id,
                        "gandengan_id" => $ksgandengan_id,
                        "stok_id" => $data['detail_stok_id'][$i] ?? 0,
                        "nobukti" => $ksnobukti ?? '',
                        "tglbukti" => date('Y-m-d', strtotime($data['tglbukti'])),
                        "qtymasuk" => 0,
                        "nilaimasuk" =>  0,
                        "qtykeluar" =>  $ksqty ?? 0,
                        "nilaikeluar" => $kstotal,
                        "urutfifo" => $urutfifo,
                    ]);
                }
            }

            if ($pengeluaranstok_id == 1 ||  $pengeluaranstok_id == 5) {

                $reuse = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                    ->select('a.id')
                    ->where('grp', 'STATUS REUSE')
                    ->where('subgrp', 'STATUS REUSE')
                    ->where('text', 'REUSE')
                    ->first()->id ?? 0;
                $stokid = $data['detail_stok_id'][$i] ?? 0;
                $stokreuse = db::table("stok")->from(db::raw("stok a with (readuncommitted)"))
                    ->select(
                        'a.id'
                    )
                    ->where('a.id', $stokid)
                    ->where('a.statusreuse', $reuse)
                    ->first();

                if (isset($stokreuse)) {

                    $ksqty = $data['detail_qty'][$i] ?? 0;
                    $ksharga = $data['detail_harga'][$i] ?? 0;
                    $kstotal = $ksqty * $ksharga;
                    $ksnobukti = $pengeluaranStokHeader->nobukti ?? '';

                    $kartuStok = (new KartuStok())->processStore([
                        "gudang_id" =>  $ksgudang_id,
                        "trado_id" =>  $kstrado_id,
                        "gandengan_id" => $ksgandengan_id,
                        "stok_id" => $data['detail_stok_id'][$i] ?? 0,
                        "nobukti" => $ksnobukti ?? '',
                        "tglbukti" => date('Y-m-d', strtotime($data['tglbukti'])),
                        "qtymasuk" => $ksqty ?? 0,
                        "nilaimasuk" =>  0,
                        "qtykeluar" =>  0,
                        "nilaikeluar" => 0,
                        "urutfifo" => $urutfifo,
                    ]);
                }
            }


            if (($kor->text != $fetchFormat->id)) {
                $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
                $datadetailfifo['gudang_id'] = $gudangkantor->text;
            }

            //hanya pja dan koreksi yang tidak dari gudang yang tidak menggunakan fifo
            if ((($kor->text == $fetchFormat->id) && $data['gudang_id']) || ($kor->text != $fetchFormat->id && $pja->text != $fetchFormat->id && ($korv->id != $fetchFormat->id) && ($afkir->id != $fetchFormat->id))) {
                (new PengeluaranStokDetailFifo())->processStore($pengeluaranStokHeader, $datadetailfifo);
            }


            $pengeluaranStokDetail = PengeluaranStokDetail::where('id', $pengeluaranStokDetail->id)->first();

            $nominal_detail[] = $pengeluaranStokDetail->total;
            $summaryDetail += $pengeluaranStokDetail->total;
        }


        // dd(PengeluaranStokDetail::where('nobukti',$pengeluaranStokHeader->nobukti)->get());
        /*STORE JURNAL*/
        $jurnalRequest = [
            'tanpaprosesnobukti' => 1,
            'nobukti' => $pengeluaranStokHeader->nobukti,
            'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
            'postingdari' => "ENTRY PENGELUARAN STOK ($fetchFormat->kodepengeluaran)",
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


        if ($rtr->text == $fetchFormat->id) {
            $potongKas = Parameter::where('grp', 'STATUS POTONG RETUR')->where('text', 'POSTING KE KAS/BANK')->first();
            $potongHutang = Parameter::where('grp', 'STATUS POTONG RETUR')->where('text', 'POTONG HUTANG')->first();

            if ($pengeluaranStokHeader->statuspotongretur == $potongKas->id) {
                //jika potongkas                
                /*STORE PENERIMAANHEADER*/
                $coaKasMasuk = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('memo')->where('grp', 'JURNAL RETUR STOK')->where('subgrp', 'KREDIT')->first();
                $memo = json_decode($coaKasMasuk->memo, true);
                $bank = Bank::select('coa', 'formatpenerimaan', 'tipe')->where('id', $pengeluaranStokHeader->bank_id)->first();
                $parameter = Parameter::where('id', $bank->formatpenerimaan)->first();
                if ($bank->tipe == 'KAS') {
                    $statusKas = Parameter::where('grp', 'STATUS KAS')->where('text', 'KAS')->first();
                }
                if ($bank->tipe == 'BANK') {
                    $statusKas = Parameter::where('grp', 'STATUS KAS')->where('text', 'BUKAN STATUS KAS')->first();
                }
                $bankid = $pengeluaranStokHeader->bank_id;
                $querysubgrppenerimaan = DB::table('bank')->from(DB::raw("bank with (readuncommitted)"))->select(
                    'parameter.grp',
                    'parameter.subgrp',
                    'bank.formatpenerimaan',
                    'bank.coa'
                )
                    ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')
                    ->whereRaw("bank.id = $bankid")
                    ->first();

                $pengeluaranStokHeader->coa = $querysubgrppenerimaan->coa;
                $group = $parameter->grp;
                $subgroup = $parameter->subgrp;
                $format = DB::table('parameter')->where('grp', $group)->where('subgrp', $subgroup)->first();

                $pengeluaranStokHeader->coa = $querysubgrppenerimaan->coa;

                $penerimaanRequest = [
                    'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'postingdari' => "ENTRY PENGELUARAN STOK ($fetchFormat->kodepengeluaran)",
                    'statusapproval' => $statusApproval->id,
                    'pelanggan_id' => 0,
                    'agen_id' => 0,
                    'diterimadari' => "RETUR STOK",
                    'tgllunas' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'statusformat' => $format->id,
                    'bank_id' => $pengeluaranStokHeader->bank_id,

                    'nowarkat' => null,
                    'tgljatuhtempo' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'nominal_detail' => $nominal_detail,
                    'coadebet' => $coadebet_detail,
                    'coakredit' => $coakredit_detail,
                    'keterangan_detail' => $keterangan_detail,
                    'invoice_nobukti' => null,
                    'bankpelanggan_id' => null,
                    'pelunasanpiutang_nobukti' => null,
                    'bulanbeban' => null,
                ];
                $penerimaan = PenerimaanHeader::where('nobukti', $pengeluaranStokHeader->penerimaan_nobukti)->lockForUpdate()->first();
                $penerimaanHeader = (new PenerimaanHeader())->processUpdate($penerimaan, $penerimaanRequest);
                $pengeluaranStokHeader->coa = $querysubgrppenerimaan->coa;
                $pengeluaranStokHeader->save();

                $jurnalselisihfifodebet = db::table("parameter")->from(db::raw("parameter as a with (readuncommitted)"))->select('memo')->where('grp', 'JURNAL SELISIH FIFO')->where('subgrp', 'DEBET')->first();
                $jurnalselisihfifokredit = db::table("parameter")->from(db::raw("parameter as a with (readuncommitted)"))->select('memo')->where('grp', 'JURNAL SELISIH FIFO')->where('subgrp', 'KREDIT')->first();
                $jurnalnominaldetail = db::table("pengeluaranstokdetail")->from(db::raw("pengeluaranstokdetail as a with (readuncommitted)"))
                    ->select(
                        db::raw("sum(isnull(selisihhargafifo,0)) as selisihhargafifo")
                    )
                    ->where('nobukti', $pengeluaranStokHeader->nobukti)->first()->selisihhargafifo ?? 0;

                $jurnalnominaldetailreal = db::table("pengeluaranstokdetail")->from(db::raw("pengeluaranstokdetail as a with (readuncommitted)"))
                    ->select(
                        db::raw("sum(isnull(selisihhargafifo,0)+isnull(total,0)) as total")
                    )
                    ->where('nobukti', $pengeluaranStokHeader->nobukti)->first()->total ?? 0;

                if ($jurnalnominaldetail != 0) {

                    $jurnalmemodebet = json_decode($jurnalselisihfifodebet->memo, true);
                    $jurnalmemokredit = json_decode($jurnalselisihfifokredit->memo, true);
                    $jurnalcoadebet_detail[] = $jurnalmemodebet['JURNAL'];
                    $jurnalcoakredit_detail[] = $jurnalmemokredit['JURNAL'];
                    $jurnalnominal_detail[] = $jurnalnominaldetailreal;
                    $jurnalketerangan_detail[] = $keterangan_detail[0];

                    $jurnalRequest = [
                        'tanpaprosesnobukti' => 1,
                        'nobukti' => $pengeluaranStokHeader->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                        'postingdari' => "ENTRY PENGELUARAN STOK ($fetchFormat->kodepengeluaran)",
                        'statusapproval' => $statusApproval->id,
                        'userapproval' => "",
                        'tglapproval' => "",
                        'modifiedby' => auth('api')->user()->name,
                        'statusformat' => "0",
                        'coakredit_detail' => $jurnalcoakredit_detail,
                        'coadebet_detail' => $jurnalcoadebet_detail,
                        'nominal_detail' => $jurnalnominal_detail,
                        'keterangan_detail' => $jurnalketerangan_detail
                    ];
                    $jurnalUmumHeader = JurnalUmumHeader::where('nobukti', $pengeluaranStokHeader->nobukti)->lockForUpdate()->first();
                    if ($jurnalUmumHeader != null) {
                        $jurnalUmumHeader = (new JurnalUmumHeader())->processUpdate($jurnalUmumHeader, $jurnalRequest);
                    } else {
                        $jurnalUmumHeader = (new JurnalUmumHeader())->processStore($jurnalRequest);
                    }
                }

                if ($jurnalnominaldetailreal != 0) {

                    $jurnalmemodebet = json_decode($jurnalselisihfifodebet->memo, true);
                    $jurnalmemokredit = json_decode($jurnalselisihfifokredit->memo, true);
                    $jurnalcoadebet_detail[] = $jurnalmemokredit['JURNAL'];
                    $jurnalcoakredit_detail[] = $jurnalmemodebet['JURNAL'];
                    $jurnalnominal_detail[] = $jurnalnominaldetail;
                    $jurnalketerangan_detail[] = $keterangan_detail[0];

                    $jurnalRequest = [
                        'tanpaprosesnobukti' => 1,
                        'nobukti' => $pengeluaranStokHeader->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                        'postingdari' => "ENTRY PENGELUARAN STOK ($fetchFormat->kodepengeluaran)",
                        'statusapproval' => $statusApproval->id,
                        'userapproval' => "",
                        'tglapproval' => "",
                        'modifiedby' => auth('api')->user()->name,
                        'statusformat' => "0",
                        'coakredit_detail' => $jurnalcoakredit_detail,
                        'coadebet_detail' => $jurnalcoadebet_detail,
                        'nominal_detail' => $jurnalnominal_detail,
                        'keterangan_detail' => $jurnalketerangan_detail
                    ];
                    $jurnalUmumHeader = JurnalUmumHeader::where('nobukti', $pengeluaranStokHeader->nobukti)->lockForUpdate()->first();
                    if ($jurnalUmumHeader != null) {
                        $jurnalUmumHeader = (new JurnalUmumHeader())->processUpdate($jurnalUmumHeader, $jurnalRequest);
                    } else {
                        $jurnalUmumHeader = (new JurnalUmumHeader())->processStore($jurnalRequest);
                    }
                }
            } else if ($pengeluaranStokHeader->statuspotongretur == $potongHutang->id) {
                //jika potonghutang
                /*STORE HUTANGBAYARHEADER*/
                /*STORE HUTANGBAYAR*/

                $statusbayarhutang = db::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
                    ->select('id')->where('grp', 'PELUNASANHUTANG')->where('subgrp', 'PELUNASANHUTANG')->where('text', 'POTONG HUTANG (RETUR)')
                    ->first()->id ?? 0;

                $penerimaanstok = Penerimaanstokheader::where('nobukti', $data['penerimaanstok_nobukti'])->first();
                $hutang = HutangHeader::where('nobukti', $penerimaanstok->hutang_nobukti)->first();

                $hutangBayarRequest = [
                    'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),

                    'bank_id' => $data['bank_id'],
                    'nowarkat' => "",
                    'supplier_id' => $data['supplier_id'],
                    'hutang_nobukti' => [$hutang->nobukti],
                    'statusapproval' => $statusApproval->id ?? 0,
                    'statusbayarhutang' => $statusbayarhutang,
                    'alatbayar_id' => 0,
                    'tglcair' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'bayar' => [$summaryDetail],
                    'hutang_id' => [$hutang->id],
                    'potongan' => [0],
                    // potongan => $summaryDetail,
                    'keterangan' => [$keterangan_detail[0]],
                ];
                // return response([$hutangHeader],422);
                $hutangbayar = PelunasanHutangHeader::where('nobukti', $pengeluaranStokHeader->hutangbayar_nobukti)->lockForUpdate()->first();
                $hutangBayarHeader = (new PelunasanHutangHeader())->processUpdate($hutangbayar, $hutangBayarRequest);

                for ($i = 0; $i <= 1; $i++) {
                    if ($i == 0) {
                        $jurnalcoadebet_detail = $coadebet_detail;
                        $jurnalcoakredit_detail = $coakredit_detail;
                        $jurnalnominal_detail = $nominal_detail;
                        $jurnalketerangan_detail[] = $keterangan_detail[0];
                    } else {
                        $jurnalselisihfifodebet = db::table("parameter")->from(db::raw("parameter as a with (readuncommitted)"))->select('memo')->where('grp', 'JURNAL SELISIH FIFO')->where('subgrp', 'DEBET')->first();
                        $jurnalselisihfifokredit = db::table("parameter")->from(db::raw("parameter as a with (readuncommitted)"))->select('memo')->where('grp', 'JURNAL SELISIH FIFO')->where('subgrp', 'KREDIT')->first();
                        $jurnalnominaldetail = db::table("pengeluaranstokdetail")->from(db::raw("pengeluaranstokdetail as a with (readuncommitted)"))
                            ->select(
                                db::raw("sum(isnull(selisihhargafifo,0)) as selisihhargafifo")
                            )
                            ->where('nobukti', $pengeluaranStokHeader->nobukti)->first()->selisihhargafifo ?? 0;

                        $jurnalnominaldetailreal = db::table("pengeluaranstokdetail")->from(db::raw("pengeluaranstokdetail as a with (readuncommitted)"))
                            ->select(
                                db::raw("sum(isnull(selisihhargafifo,0)+isnull(total,0)) as total")
                            )
                            ->where('nobukti', $pengeluaranStokHeader->nobukti)->first()->total ?? 0;

                        if ($jurnalnominaldetail != 0) {

                            $jurnalmemodebet = json_decode($jurnalselisihfifodebet->memo, true);
                            $jurnalmemokredit = json_decode($jurnalselisihfifokredit->memo, true);
                            $jurnalcoadebet_detail[] = $jurnalmemodebet['JURNAL'];
                            $jurnalcoakredit_detail[] = $jurnalmemokredit['JURNAL'];
                            $jurnalnominal_detail[] = $jurnalnominaldetailreal;
                            $jurnalketerangan_detail[] = $keterangan_detail[0];
                        }
                        if ($jurnalnominaldetailreal != 0) {

                            $jurnalmemodebet = json_decode($jurnalselisihfifodebet->memo, true);
                            $jurnalmemokredit = json_decode($jurnalselisihfifokredit->memo, true);
                            $jurnalcoadebet_detail[] = $jurnalmemokredit['JURNAL'];
                            $jurnalcoakredit_detail[] = $jurnalmemodebet['JURNAL'];
                            $jurnalnominal_detail[] = $jurnalnominaldetail;
                            $jurnalketerangan_detail[] = $keterangan_detail[0];
                        }
                    }
                }

                $jurnalRequest = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $pengeluaranStokHeader->nobukti,
                    'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'postingdari' => "ENTRY PENGELUARAN STOK ($fetchFormat->kodepengeluaran)",
                    'statusapproval' => $statusApproval->id,
                    'userapproval' => "",
                    'tglapproval' => "",
                    'modifiedby' => auth('api')->user()->name,
                    'statusformat' => "0",
                    'coakredit_detail' => $jurnalcoakredit_detail,
                    'coadebet_detail' => $jurnalcoadebet_detail,
                    'nominal_detail' => $jurnalnominal_detail,
                    'keterangan_detail' => $jurnalketerangan_detail
                ];
                $jurnalUmumHeader = JurnalUmumHeader::where('nobukti', $pengeluaranStokHeader->nobukti)->lockForUpdate()->first();
                if ($jurnalUmumHeader != null) {

                    $jurnalUmumHeader = (new JurnalUmumHeader())->processUpdate($jurnalUmumHeader, $jurnalRequest);
                } else {
                    $jurnalUmumHeader = (new JurnalUmumHeader())->processStore($jurnalRequest);
                }
            }
        } else if ($pja->text == $data['pengeluaranstok_id'] && ($pengeluaranStokHeader->bank_id)) {
            //jika potongkas                
            /*STORE PENERIMAANHEADER*/
            $coaKasMasuk = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('memo')->where('grp', 'JURNAL RETUR STOK')->where('subgrp', 'KREDIT')->first();
            $memo = json_decode($coaKasMasuk->memo, true);
            $bank = Bank::select('coa', 'formatpenerimaan', 'tipe')->where('id', $pengeluaranStokHeader->bank_id)->first();
            $parameter = Parameter::where('id', $bank->formatpenerimaan)->first();
            if ($bank->tipe == 'KAS') {
                $statusKas = Parameter::where('grp', 'STATUS KAS')->where('text', 'KAS')->first();
            }
            if ($bank->tipe == 'BANK') {
                $statusKas = Parameter::where('grp', 'STATUS KAS')->where('text', 'BUKAN STATUS KAS')->first();
            }
            $bankid = $pengeluaranStokHeader->bank_id;
            $querysubgrppenerimaan = DB::table('bank')->from(DB::raw("bank with (readuncommitted)"))->select(
                'parameter.grp',
                'parameter.subgrp',
                'bank.formatpenerimaan',
                'bank.coa'
            )
                ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')
                ->whereRaw("bank.id = $bankid")
                ->first();

            $pengeluaranStokHeader->coa = $querysubgrppenerimaan->coa;
            $group = $parameter->grp;
            $subgroup = $parameter->subgrp;
            $format = DB::table('parameter')->where('grp', $group)->where('subgrp', $subgroup)->first();

            $pengeluaranStokHeader->coa = $querysubgrppenerimaan->coa;

            $penerimaanRequest = [
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'postingdari' => "ENTRY PENGELUARAN STOK ($fetchFormat->kodepengeluaran)",
                'statusapproval' => $statusApproval->id,
                'pelanggan_id' => 0,
                'agen_id' => 0,
                'diterimadari' => "PENJUALAN STOK AFKIR",
                'tgllunas' => date('Y-m-d', strtotime($data['tglbukti'])),
                'statusformat' => $format->id,
                'bank_id' => $pengeluaranStokHeader->bank_id,

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
            $penerimaan = PenerimaanHeader::where('nobukti', $pengeluaranStokHeader->penerimaan_nobukti)->lockForUpdate()->first();

            if (!$penerimaan) {
                $penerimaanHeader = (new PenerimaanHeader())->processStore($penerimaanRequest);
                $pengeluaranStokHeader->penerimaan_nobukti = $penerimaanHeader->nobukti;
            } else {
                $penerimaanHeader = (new PenerimaanHeader())->processUpdate($penerimaan, $penerimaanRequest);
            }
            $pengeluaranStokHeader->coa = $querysubgrppenerimaan->coa;
            $pengeluaranStokHeader->save();
        } else if ($korv->id == $data['pengeluaranstok_id']) {
        } else {
            // dd($jurnalRequest);
            $jurnalUmumHeader = JurnalUmumHeader::where('nobukti', $pengeluaranStokHeader->nobukti)->lockForUpdate()->first();
            if ($jurnalUmumHeader != null) {
                $jurnalUmumHeader = (new JurnalUmumHeader())->processUpdate($jurnalUmumHeader, $jurnalRequest);
            }
        }

        $pengeluaranStokHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengeluaranStokHeader->getTable()),
            'postingdari' => "ENTRY PENGELUARAN STOK ($fetchFormat->kodepengeluaran)",
            'idtrans' => $pengeluaranStokHeader->id,
            'nobuktitrans' => $pengeluaranStokHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pengeluaranStokHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        //store logtrail detail
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengeluaranStokDetail->getTable()),
            'postingdari' => "ENTRY PENGELUARAN STOK ($fetchFormat->kodepengeluaran)",
            'idtrans' =>  $pengeluaranStokHeaderLogTrail->id,
            'nobuktitrans' => $pengeluaranStokHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pengeluaranStokDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        if ($spk->text == $fetchFormat->id) {
            $spk = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select(
                    'a.text'
                )
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
                ->whereRaw("a.id>" . $pengeluaranStokHeader->id)
                ->where('a.pengeluaranstok_id', $spk)
                ->orderBy('a.id', 'asc')
                ->get();

            // dd($queryspklainheader);
            $dataheaderspk = json_decode($queryspklainheader, true);
            foreach ($dataheaderspk as $itemspkheader) {
                // dd($itemspkheader['nobukti']);
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
                        "gudang_id" => $gudangkantor->text,
                        "tglbukti" => $itemspkheader['tglbukti'],
                        "qty" => $itemspkdetail['qty'],
                        "modifiedby" => $itemspkheader['modifiedby'],
                        "keterangan" => $itemspkheader['keterangan'] ?? '',
                        "detail_keterangan" => $itemspkdetail['keterangan'] ?? '',
                        "detail_harga" => $itemspkdetail['harga'] ?? '' ?? '',
                        "statusformat" => $itemspkheader['statusformat'] ?? '',
                    ];
                    // dd($datadetailfiforeset);
                    (new PengeluaranStokDetailFifo())->processStore($pengeluaranStokHeader, $datadetailfiforeset);
                    $pengeluaranStokDetailreset = PengeluaranStokDetail::where('id', $itemspkdetail['id'])
                        ->where('nobukti', $itemspkheader['nobukti'])
                        ->first();

                    $nominal_detailreset[] = $pengeluaranStokDetailreset->total;
                    $coadebet_detailreset[] = $memo['JURNAL'];
                    $coakredit_detailreset[] = $memokredit['JURNAL'];
                    $keterangan_detailreset[] = $itemspkdetail['keterangan'] ?? 'PENGELUARAN STOK RETUR';
                    $pengeluaranStokDetailsreset[] = $pengeluaranStokDetailreset->toArray();


                    // 

                    if ($itemspkheader['pengeluaranstok_id'] == 1 ||  $itemspkheader['pengeluaranstok_id'] == 5) {

                        $reuse = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                            ->select('a.id')
                            ->where('grp', 'STATUS REUSE')
                            ->where('subgrp', 'STATUS REUSE')
                            ->where('text', 'REUSE')
                            ->first()->id ?? 0;
                        $stokid = $itemspkdetail['stok_id'] ?? 0;
                        $stokreuse = db::table("stok")->from(db::raw("stok a with (readuncommitted)"))
                            ->select(
                                'a.id'
                            )
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

                    // 


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
                    'namatabel' => strtoupper($pengeluaranStokDetail->getTable()),
                    'postingdari' => "ENTRY PENGELUARAN STOK ($fetchFormat->kodepengeluaran)",
                    'idtrans' =>  $pengeluaranStokHeaderLogTrailReset->id,
                    'nobuktitrans' => $itemspkheader['nobukti'],
                    'aksi' => 'ENTRY',
                    'datajson' => $pengeluaranStokDetailsreset,
                    'modifiedby' => auth('api')->user()->user,
                ]);
            }
        }

        return $pengeluaranStokHeader;
    }

    public function processDestroy($id): PengeluaranStokHeader
    {
        $pengeluaranStokHeader = PengeluaranStokHeader::findOrFail($id);
        $dataHeader =  $pengeluaranStokHeader->toArray();
        $pengeluaranStokDetail = PengeluaranStokDetail::where('pengeluaranstokheader_id', '=', $pengeluaranStokHeader->id)->get();
        $pengeluaranStokDetail1 = PengeluaranStokDetail::where('pengeluaranstokheader_id', '=', $pengeluaranStokHeader->id);
        $dataDetail = $pengeluaranStokDetail->toArray();
        $statuspotongretur = $pengeluaranStokHeader->statuspotongretur;
        $fetchFormat =  PengeluaranStok::where('id', $pengeluaranStokHeader->pengeluaranstok_id)->first();
        $datahitungstok = $fetchFormat;
        $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();

        $korv = DB::table('pengeluaranstok')->where('kodepengeluaran', 'KORV')->first();
        $pja = DB::table('pengeluaranstok')->where('kodepengeluaran', 'PJA')->first();

        /*RETURN STOK PENERIMAAN*/
        if ($datahitungstok->statushitungstok == $statushitungstok->id) {
            $datadetail = PengeluaranStokDetail::select('stok_id', 'qty')->where('pengeluaranstokheader_id', '=', $pengeluaranStokHeader->id)->get();
            (new PengeluaranStokDetail())->resetQtyPenerimaan($pengeluaranStokHeader->id);
        }
        if ($pengeluaranStokHeader->pengeluaranstok_id == $korv->id) {
            (new PengeluaranStokDetail())->returnVulkanisir($pengeluaranStokHeader->id);
        }

        /*DELETE EXISTING DETAIL*/
        PengeluaranStokDetail::where('pengeluaranstokheader_id', $pengeluaranStokHeader->id)->lockForUpdate()->delete();
        $pengeluaranStokDetailFifo = PengeluaranStokDetailFifo::where('pengeluaranstokheader_id', $pengeluaranStokHeader->id)->lockForUpdate()->delete();
        $kartuStok = KartuStok::where('nobukti', $pengeluaranStokHeader->nobukti)->lockForUpdate()->delete();

        $potongKas = Parameter::where('grp', 'STATUS POTONG RETUR')->where('text', 'POSTING KE KAS/BANK')->first();
        $potongHutang = Parameter::where('grp', 'STATUS POTONG RETUR')->where('text', 'POTONG HUTANG')->first();


        if ($statuspotongretur == $potongKas->id) {
            $penerimaan = PenerimaanHeader::where('nobukti', $pengeluaranStokHeader->penerimaan_nobukti)->lockForUpdate()->first();
            (new PenerimaanHeader())->processDestroy($penerimaan->id);
            $jurnalUmumHeader = JurnalUmumHeader::where('nobukti', $pengeluaranStokHeader->nobukti)->lockForUpdate()->first();
            if ($jurnalUmumHeader) {
                (new JurnalUmumHeader())->processDestroy($jurnalUmumHeader->id);
            }
        } else if ($statuspotongretur == $potongHutang->id) {
            $hutangbayar = PelunasanHutangHeader::where('nobukti', $pengeluaranStokHeader->hutangbayar_nobukti)->lockForUpdate()->first();
            (new PelunasanHutangHeader())->processDestroy($hutangbayar->id, 'DELETE PENGELUARAN STOK RETUR');
            $jurnalUmumHeader = JurnalUmumHeader::where('nobukti', $pengeluaranStokHeader->nobukti)->lockForUpdate()->first();
            if ($jurnalUmumHeader) {
                (new JurnalUmumHeader())->processDestroy($jurnalUmumHeader->id);
            }
        } else if ($pengeluaranStokHeader->pengeluaranstok_id == $pja->id) {
            $penerimaan = PenerimaanHeader::where('nobukti', $pengeluaranStokHeader->penerimaan_nobukti)->lockForUpdate()->first();
            (new PenerimaanHeader())->processDestroy($penerimaan->id);
            $jurnalUmumHeader = JurnalUmumHeader::where('nobukti', $pengeluaranStokHeader->nobukti)->lockForUpdate()->first();
            if ($jurnalUmumHeader) {
                (new JurnalUmumHeader())->processDestroy($jurnalUmumHeader->id);
            }
        } else {
            /*DELETE EXISTING JURNALUMUMHEADER*/
            $jurnalUmumHeader = JurnalUmumHeader::where('nobukti', $pengeluaranStokHeader->nobukti)->lockForUpdate()->first();
            if ($jurnalUmumHeader) {
                (new JurnalUmumHeader())->processDestroy($jurnalUmumHeader->id);
            }
        }

        $pengeluaranStokHeader = $pengeluaranStokHeader->lockAndDestroy($id);
        $hutangLogTrail = (new LogTrail())->processStore([
            'namatabel' => $this->table,
            'postingdari' => "DELETE PENGELUARAN STOK ($fetchFormat->kodepengeluaran)",
            'idtrans' => $pengeluaranStokHeader->id,
            'nobuktitrans' => $pengeluaranStokHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $dataHeader,
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => (new LogTrail())->table,
            'postingdari' => "DELETE PENGELUARAN STOK ($fetchFormat->kodepengeluaran)",
            'idtrans' => $hutangLogTrail['id'],
            'nobuktitrans' => $pengeluaranStokHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $dataDetail,
            'modifiedby' => auth('api')->user()->name
        ]);

        $spk = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
        $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
        $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'DEBET')->first();
        $memo = json_decode($getCoaDebet->memo, true);
        $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'KREDIT')->first();
        $memokredit = json_decode($getCoaKredit->memo, true);
        $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();


        if ($spk->text == $fetchFormat->id) {
            $spk = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select(
                    'a.text'
                )
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
                ->whereRaw("a.id>" . $pengeluaranStokHeader->id)
                ->where('a.pengeluaranstok_id', $spk)
                ->orderBy('a.id', 'asc')
                ->get();

            // dd($queryspklainheader);
            $dataheaderspk = json_decode($queryspklainheader, true);
            foreach ($dataheaderspk as $itemspkheader) {
                // dd($itemspkheader['nobukti']);
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
                        "gudang_id" => $gudangkantor->text,
                        "tglbukti" => $itemspkheader['tglbukti'],
                        "qty" => $itemspkdetail['qty'],
                        "modifiedby" => $itemspkheader['modifiedby'],
                        "keterangan" => $itemspkheader['keterangan'] ?? '',
                        "detail_keterangan" => $itemspkdetail['keterangan'] ?? '',
                        "detail_harga" => $itemspkdetail['harga'] ?? '' ?? '',
                        "statusformat" => $itemspkheader['statusformat'] ?? '',
                    ];
                    // dd($datadetailfiforeset);
                    (new PengeluaranStokDetailFifo())->processStore($pengeluaranStokHeader, $datadetailfiforeset);
                    $pengeluaranStokDetailreset = PengeluaranStokDetail::where('id', $itemspkdetail['id'])
                        ->where('nobukti', $itemspkheader['nobukti'])
                        ->first();

                    $nominal_detailreset[] = $pengeluaranStokDetailreset->total;
                    $coadebet_detailreset[] = $memo['JURNAL'];
                    $coakredit_detailreset[] = $memokredit['JURNAL'];
                    $keterangan_detailreset[] = $itemspkdetail['keterangan'] ?? 'PENGELUARAN STOK RETUR';
                    $pengeluaranStokDetailsreset[] = $pengeluaranStokDetailreset->toArray();


                    // 

                    if ($itemspkheader['pengeluaranstok_id'] == 1 ||  $itemspkheader['pengeluaranstok_id'] == 5) {

                        $reuse = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                            ->select('a.id')
                            ->where('grp', 'STATUS REUSE')
                            ->where('subgrp', 'STATUS REUSE')
                            ->where('text', 'REUSE')
                            ->first()->id ?? 0;
                        $stokid = $itemspkdetail['stok_id'] ?? 0;
                        $stokreuse = db::table("stok")->from(db::raw("stok a with (readuncommitted)"))
                            ->select(
                                'a.id'
                            )
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

                    // 


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
                // dd($pengeluaranStokDetail1->getTable());
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
                    'namatabel' => 'PengeluaranStokDetail',
                    'postingdari' => "ENTRY PENGELUARAN STOK ($fetchFormat->kodepengeluaran)",
                    'idtrans' =>  $pengeluaranStokHeaderLogTrailReset->id,
                    'nobuktitrans' => $itemspkheader['nobukti'],
                    'aksi' => 'ENTRY',
                    'datajson' => $pengeluaranStokDetailsreset,
                    'modifiedby' => auth('api')->user()->user,
                ]);
            }
        }

        return $pengeluaranStokHeader;
    }

    public function updateApproval()
    {
        DB::beginTransaction();
        try {
            $tutupbuku = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'TUTUP BUKU')->where('subgrp', '=', 'TUTUP BUKU')->first();
            $approval = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where("grp", 'STATUS APPROVAL')->where("text", "APPROVAL")->first();
            $nonApproval = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where("grp", 'STATUS APPROVAL')->where("text", "NON APPROVAL")->first();

            $query = DB::table('pengeluaranstokheader')->where('tglbataseditketerangan', '<', date('Y-m-d H:i:s'))->where('tglbukti', '>', $tutupbuku->text)->where('statusapprovaleditketerangan', $approval->id);
            $query->update(['statusapprovaleditketerangan' => $nonApproval->id]);

            $query = DB::table('pengeluaranstokheader')->where('tglbatasedit', '<', date('Y-m-d H:i:s'))->where('tglbukti', '>', $tutupbuku->text)->where('statusapprovaledit', $approval->id);
            $query->update(['statusapprovaledit' => $nonApproval->id]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
