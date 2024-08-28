<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Models\PengeluaranStokDetailFifo;
use App\Models\PenerimaanStokDetail;
use App\Models\PenerimaanStok;
use Illuminate\Database\Schema\Blueprint;


class KartuStok extends MyModel
{
    use HasFactory;

    // protected $table = 'pengeluaranstokdetailfifo';
    protected $table = 'kartustok';

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
        // dd('test');
        $this->setRequestParameters();

        $tgldari = date('Y-m-d', strtotime(request()->dari));
        $tglsampai = date('Y-m-d', strtotime(request()->sampai));

        $filtergudang = Parameter::where('grp', 'STOK PERSEDIAAN')->where('subgrp', 'STOK PERSEDIAAN')->where('text', 'GUDANG')->first();
        $filtertrado = Parameter::where('grp', 'STOK PERSEDIAAN')->where('subgrp', 'STOK PERSEDIAAN')->where('text', 'TRADO')->first();
        $filtergandengan = Parameter::where('grp', 'STOK PERSEDIAAN')->where('subgrp', 'STOK PERSEDIAAN')->where('text', 'GANDENGAN')->first();

        // dump(request()->filter);
        // dd($filter->id);

        // if (request()->filter == $filter->id) {
        // dd('test');
        // dd($filter->text);
        $datafilter = request()->filter ?? '';
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'KartuStokController';

        // dd('test');

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

            $temprekapall = '##temprekapall' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temprekapall, function ($table) {
                $table->id();
                $table->integer('stok_id')->nullable();
                $table->integer('gudang_id')->nullable();
                $table->integer('trado_id')->nullable();
                $table->integer('gandengan_id')->nullable();
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


            Schema::create($temtabel, function (Blueprint $table) {
                $table->id();
                $table->integer('stok_id')->nullable();
                $table->integer('gudang_id')->nullable();
                $table->integer('trado_id')->nullable();
                $table->integer('gandengan_id')->nullable();
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
                $table->decimal('nilaisaldo', 15, 3)->nullable();
                $table->string('modifiedby', 100)->nullable();
                $table->integer('urutfifo')->nullable();
                $table->integer('iddata')->nullable();
                $table->datetime('tglinput')->nullable();

                $table->index('kodebarang', 'temtabel_kodebarang_index');
                $table->index('namabarang', 'temtabel_namabarang_index');
                $table->index('nobukti', 'temtabel_nobukti_index');
                $table->index('kategori_id', 'temtabel_kategori_id_index');
            });

            if ($datafilter == 0 || $datafilter == '') {
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
                ], $this->getlaporan($tgldari, $tglsampai, request()->stokdari_id, request()->stoksampai_id, $datafilter, 0, 0, 0));
            } else {
                $filtergudang->text;
                if (request()->filter == $filtergudang->id) {
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

                    ], $this->getlaporan($tgldari, $tglsampai, request()->stokdari_id, request()->stoksampai_id, request()->datafilter, 0, 0, $filtergudang->text));
                } else if (request()->filter == $filtertrado->id) {
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

                    ], $this->getlaporan($tgldari, $tglsampai, request()->stokdari_id, request()->stoksampai_id, 0, request()->datafilter, 0, $filtertrado->text));
                } else if (request()->filter == $filtergandengan->id) {
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

                    ], $this->getlaporan($tgldari, $tglsampai, request()->stokdari_id, request()->stoksampai_id, 0, 0, request()->datafilter, $filtergandengan->text));
                } else {

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

                    ], $this->getlaporan($tgldari, $tglsampai, request()->stokdari_id, request()->stoksampai_id, 0, 0, 0, $filtergudang->text));
                }
            }

            // dd(db::table($temprekapall)->whereraw("stok_id=29")->get());
            // dd(request()->statustampil);
            $statustampilan = request()->statustampil ?? 0;
            // $statustampilan=531;
            // dd(db::table($temprekapall)->get());

            $queryytampilan = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select(
                    'a.id'
                )
                ->where('a.grp', 'STATUS TAMPILAN KARTU STOK')
                ->where('a.subgrp', 'STATUS TAMPILAN KARTU STOK')
                ->where('a.text', 'PERGERAKAN')
                ->where('a.id', $statustampilan)
                ->first();

            // dd($queryytampilan);

            $tempstokjumlah = '##tempstokjumlah' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempstokjumlah, function ($table) {
                $table->integer('stok_id')->nullable();
                $table->integer('gudang_id')->nullable();
                $table->integer('trado_id')->nullable();
                $table->integer('gandengan_id')->nullable();
                $table->integer('jumlah')->nullable();
            });

            $tempstoktransaksi = '##tempstoktransaksi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempstoktransaksi, function ($table) {
                $table->id();
                $table->string('kodebarang', 1000)->nullable();
            });


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
            $kelompok_id = request()->kelompok_id ?? '';
            if ($kelompok_id != '') {

                DB::delete(DB::raw("delete " . $temprekapall . " from " . $temprekapall . " as a  inner join stok b on a.stok_id=b.id
                WHERE isnull(b.kelompok_id,0) not in(" . $kelompok_id . ")"));
            }

            $tempstok = '##tempstok' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempstok, function ($table) {
                $table->id();
                $table->unsignedBigInteger('stok_id')->nullable();
                $table->unsignedBigInteger('gudang_id')->nullable();
                $table->unsignedBigInteger('trado_id')->nullable();
                $table->unsignedBigInteger('gandengan_id')->nullable();
                $table->string('lokasi', 500)->nullable();
            });

            $querystok = db::table($temprekapall)->from(db::raw($temprekapall . " a "))
                ->select(
                    'a.stok_id',
                    'a.gudang_id',
                    'a.trado_id',
                    'a.gandengan_id',
                    db::raw("max(a.lokasi) as lokasi"),
                )
                ->groupby('a.stok_id')
                ->groupby('a.gudang_id')
                ->groupby('a.trado_id')
                ->groupby('a.gandengan_id');

            DB::table($tempstok)->insertUsing([
                'stok_id',
                'gudang_id',
                'trado_id',
                'gandengan_id',
                'lokasi',
            ], $querystok);

            DB::delete(DB::raw("delete " . $tempstok . " from " . $tempstok . " as a inner join " . $temprekapall . " b on isnull(a.stok_id,0)=isnull(b.stok_id,0) and isnull(a.gudang_id,0)=isnull(b.gudang_id,0)
            and isnull(a.trado_id,0)=isnull(b.trado_id,0) and isnull(a.gandengan_id,0)=isnull(b.gandengan_id,0) and isnull(b.nobukti,'')='SALDO AWAL'
            "));

            $querysaldoawal = db::table($tempstok)->from(db::raw($tempstok . " a "))
                ->select(
                    'a.stok_id',
                    'a.gudang_id',
                    'a.trado_id',
                    'a.gandengan_id',
                    db::raw("a.lokasi as lokasi"),
                    db::raw("isnull(b.namastok,'') as kodebarang"),
                    db::raw("isnull(b.namastok,'') as namabarang"),
                    db::raw("'" . $tgldari . "' as tglbukti"),
                    db::raw("'SALDO AWAL' as nobukti"),
                    db::raw("'' as kategori_id"),
                    db::raw("0 as qtymasuk"),
                    db::raw("0 as nilaimasuk"),
                    db::raw("0 as qtykeluar"),
                    db::raw("0 as nilaikeluar"),
                    db::raw("0 as qtysaldo"),
                    db::raw("0 as nilaisaldo"),
                    db::raw("0 as modifiedby"),
                    db::raw("0 as urutfifo"),
                    db::raw("0 as iddata"),
                    db::raw("'1900/1/1' as tglinput"),

                )
                ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id');

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

            ], $querysaldoawal);



            if (isset($queryytampilan)) {

                $queryjumlah = db::table($temprekapall)->from(db::raw($temprekapall . " a"))
                    ->select(
                        'a.stok_id',
                        'a.gudang_id',
                        'a.trado_id',
                        'a.gandengan_id',
                        db::raw("count(a.stok_id) as jumlah"),
                    )
                    ->groupby('a.stok_id')
                    ->groupby('a.gudang_id')
                    ->groupby('a.trado_id')
                    ->groupby('a.gandengan_id');



                DB::table($tempstokjumlah)->insertUsing([
                    'stok_id',
                    'gudang_id',
                    'trado_id',
                    'gandengan_id',
                    'jumlah',
                ],  $queryjumlah);

                // dd(db::table($temprekapall)->whereraw("stok_id=29")->get());
                // dump(db::table($tempstokjumlah)->whereraw("stok_id=29")->get());

                DB::delete(DB::raw("delete " . $tempstokjumlah . " from " . $tempstokjumlah . " as a  
                WHERE isnull(a.jumlah,0)>1"));


                // dd(db::table($temprekapall)->where('stok_id',3492)->get());

                DB::delete(DB::raw("delete " . $temprekapall . " from " . $temprekapall . " as a inner join " . $tempstokjumlah . " b on isnull(a.stok_id,0)=isnull(b.stok_id,0)
                and isnull(a.trado_id,0)=isnull(b.trado_id,0) and isnull(a.gudang_id,0)=isnull(b.gudang_id,0) and isnull(a.gandengan_id,0)=isnull(b.gandengan_id,0)"));

                // dd(db::table($temprekapall)->get());
            }
            // dd('test1');








            $querylist = db::table($temprekapall)->from(db::raw($temprekapall . " a"))
                ->select(
                    'a.stok_id',
                    'a.gudang_id',
                    'a.trado_id',
                    'a.gandengan_id',
                    'a.lokasi',
                    'a.kodebarang',
                    'a.namabarang',
                    'a.tglbukti',
                    'a.nobukti',
                    'a.kategori_id',
                    'a.qtymasuk',
                    'a.nilaimasuk',
                    'a.qtykeluar',
                    'a.nilaikeluar',
                    // DB::raw("sum ((
                    //     (case when a.nobukti='SALDO AWAL' then a.qtysaldo else 0 end)+a.qtymasuk
                    //     )-a.qtykeluar) over (PARTITION BY isnull(a.stok_id,0),isnull(a.gudang_id,0),isnull(A.trado_id,0),isnull(A.gandengan_id,0) order by a.stok_id,isnull(a.gudang_id,0),isnull(A.trado_id,0),isnull(A.gandengan_id,0),isnull(a.tglbukti,0),a.urutfifo,a.nobukti,a.iddata ASC) as qtysaldo"),
                    // DB::raw("cast(sum ((isnull(a.nilaisaldo,0)+a.nilaimasuk)-a.nilaikeluar) over (PARTITION BY a.stok_id,isnull(a.gudang_id,0),isnull(A.trado_id,0),isnull(A.gandengan_id,0) order by isnull(a.stok_id,0),isnull(a.gudang_id,0),isnull(A.trado_id,0),isnull(A.gandengan_id,0),a.tglbukti,a.urutfifo,a.nobukti,a.iddata ASC) as money) as nilaisaldo"),

                    'a.qtysaldo',
                    'a.nilaisaldo',
                    'a.modifiedby',
                    'a.urutfifo',
                    'a.iddata',
                    'a.tglinput',
                )
                ->orderBy('a.stok_id', 'asc')
                ->orderBy('a.gudang_id', 'asc')
                ->orderBy('a.trado_id', 'asc')
                ->orderBy('a.gandengan_id', 'asc')
                ->orderBy('a.tglbukti', 'asc')
                ->orderBy('a.tglinput', 'asc')
                ->orderBy('a.urutfifo', 'asc')
                ->orderBy('a.nobukti', 'asc')
                ->orderBy('a.iddata', 'asc');
            // ->orderBy(db::raw("(case when UPPER(isnull(a.nobukti,''))='SALDO AWAL' then '' else isnull(a.nobukti,'') end)"), 'asc');

            // dd(db::table($temprekapall)->where('stok_id',94)->get());



            DB::table($temtabel)->insertUsing([
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
            ], $querylist);

            // dd(db::table($temtabel)->get());


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


        // dd(db::table($temtabel)->get());
        $query = DB::table(DB::raw($temtabel))->from(
            DB::raw(DB::raw($temtabel) . " a with (readuncommitted)")
        )
            ->select(
                db::raw("(case when isnull(a.gudang_id,0)<>0 then 'GUDANG'
                when isnull(a.trado_id,0)<>0 then 'TRADO'
                when isnull(a.gandengan_id,0)<>0 then 'GANDENGAN'
                else 'GUDANG' END) AS namalokasi
                "),
                'a.lokasi',
                'a.kodebarang',
                'a.namabarang',
                'a.tglbukti',
                'a.nobukti',
                'a.kategori_id',
                'a.qtymasuk',
                'a.nilaimasuk',
                'a.qtykeluar',
                // db::raw("round(a.nilaikeluar ,2) as nilaikeluar"),
                'a.nilaikeluar',
                'a.qtysaldo',
                // db::raw("round(a.nilaisaldo ,2) as nilaisaldo"),
                'a.nilaisaldo',
                'a.modifiedby',
                'a.stok_id',
                'a.gudang_id',
                'a.trado_id',
                'a.gandengan_id',
                'a.iddata',
                'a.urutfifo',

                db::raw("isnull(C.satuan,'') as satuan"),
            )
            ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id')
            ->leftjoin(db::raw("satuan c with (readuncommitted)"), 'b.satuan_id', 'c.id');



        // if ($datafilter == 0) {
        //     $query = $this->getall($tgldari, $tglsampai, request()->stokdari_id, request()->stoksampai_id, $datafilter, 0, 0, 0);
        // } else {
        //     if (request()->filter == $filtergudang->id) {
        //         $query = $this->getlaporan($tgldari, $tglsampai, request()->stokdari_id, request()->stoksampai_id, request()->datafilter, 0, 0, $filtergudang->text);
        //     } else if (request()->filter == $filtertrado->id) {
        //         $query = $this->getlaporan($tgldari, $tglsampai, request()->stokdari_id, request()->stoksampai_id, 0, request()->datafilter, 0, $filtertrado->text);
        //     } else if (request()->filter == $filtergandengan->id) {
        //         $query = $this->getlaporan($tgldari, $tglsampai, request()->stokdari_id, request()->stoksampai_id, 0, 0, request()->datafilter, $filtergandengan->text);
        //     } else {
        //         $query = $this->getlaporan($tgldari, $tglsampai, request()->stokdari_id, request()->stoksampai_id, request()->datafilter, 0, 0, $filtergudang->text);
        //     }
        // }


        $this->totalRows = $query->count();

        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);

        // dd($query->toSql());
        $this->filter($query);
        // dd($query->get());
        $this->paginate($query);

        $data = $query->get();

        // dd($data);
        // } else {
        //     $data = [];
        // }

        return $data;
    }

    public function default()
    {
        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('stokdari_id')->nullable();
            $table->string('stokdari', 255)->nullable();
            $table->unsignedBigInteger('stoksampai_id')->nullable();
            $table->string('stoksampai', 255)->nullable();
            $table->unsignedBigInteger('gudang_id')->nullable();
            $table->string('gudang', 255)->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->string('trado', 255)->nullable();
            $table->unsignedBigInteger('gandengan_id')->nullable();
            $table->string('gandengan', 255)->nullable();
            $table->unsignedBigInteger('filter')->nullable();
            $table->unsignedBigInteger('statustampil')->nullable();
        });

        // $tempStokDari = '##tempStokDari' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        // Schema::create($tempStokDari, function ($table) {
        //     $table->unsignedBigInteger('stokdari_id')->nullable();
        //     $table->string('stokdari', 255)->nullable();
        // });
        $stokDari = Stok::from(
            DB::raw('stok with (readuncommitted)')
        )
            ->select(
                'id as stokdari_id',
                'namastok as stokdari',

            )
            ->orderBy('id', 'asc')
            ->limit(1)
            ->first();

        $stokdari_id = $stokDari->stokdari_id ?? 0;
        $stokdari = $stokDari->stokdari ?? 0;

        // DB::table($tempStokDari)->insert(
        //     ["stokdari_id" => $stokDari->stokdari_id, "stokdari" => $stokDari->stokdari]
        // );


        // $tempStokSampai = '##tempStokSampai' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        // Schema::create($tempStokSampai, function ($table) {
        //     $table->unsignedBigInteger('stoksampai_id')->nullable();
        //     $table->string('stoksampai', 255)->nullable();
        // });
        $stokSampai = Stok::from(
            DB::raw('stok with (readuncommitted)')
        )
            ->select(
                'id as stoksampai_id',
                'namastok as stoksampai',

            )
            ->orderBy('id', 'asc')
            ->limit(1)
            ->first();

        $stoksampai_id = $stokSampai->stoksampai_id ?? 0;
        $stoksampai = $stokSampai->stoksampai ?? 0;
        // DB::table($tempStokSampai)->insert(
        //     ["stoksampai_id" => $stokSampai->stoksampai_id, "stoksampai" => $stokSampai->stoksampai]
        // );

        // $tempGudang = '##tempGudang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        // Schema::create($tempGudang, function ($table) {
        //     $table->unsignedBigInteger('gudang_id')->nullable();
        //     $table->string('gudang', 255)->nullable();
        // });
        $gudang = Gudang::from(
            DB::raw('gudang with (readuncommitted)')
        )
            ->select(
                'id as gudang_id',
                'gudang as gudang',

            )
            ->orderBy('id', 'asc')
            ->limit(1)
            ->first();

        $gudang_id = $gudang->gudang_id ?? 0;
        $namagudang = $gudang->gudang ?? 0;
        // DB::table($tempGudang)->insert(
        //     ["gudang_id" => $gudang->gudang_id, "gudang" => $gudang->gudang]
        // );

        // $tempTrado = '##tempTrado' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        // Schema::create($tempTrado, function ($table) {
        //     $table->unsignedBigInteger('trado_id')->nullable();
        //     $table->string('trado', 255)->nullable();
        // });
        $trado = Trado::from(
            DB::raw('trado with (readuncommitted)')
        )
            ->select(
                'id as trado_id',
                'kodetrado as trado',

            )
            ->orderBy('id', 'asc')
            ->limit(1)
            ->first();

        // DB::table($tempTrado)->insert(
        //     ["trado_id" => $trado->trado_id, "trado" => $trado->trado]
        // );

        $trado_id = $trado->trado_id ?? 0;
        $namatrado = $trado->trado ?? 0;
        // $tempGandengan = '##tempGandengan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        // Schema::create($tempGandengan, function ($table) {
        //     $table->unsignedBigInteger('gandengan_id')->nullable();
        //     $table->string('gandengan', 255)->nullable();
        // });
        $gandengan = Gandengan::from(
            DB::raw('gandengan with (readuncommitted)')
        )
            ->select(
                'id as gandengan_id',
                'keterangan as gandengan',

            )
            ->orderBy('id', 'asc')
            ->limit(1)
            ->first();

        $gandengan_id = $gandengan->gandengan_id ?? 0;
        $namagandengan = $gandengan->gandengan ?? 0;
        // DB::table($tempGandengan)->insert(
        //     ["gandengan_id" => $gandengan->gandengan_id, "gandengan" => $gandengan->gandengan]
        // );

        // $tempFilter = '##tempFilter' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        // Schema::create($tempFilter, function ($table) {
        //     $table->unsignedBigInteger('filter')->nullable();
        // });
        $filter = Parameter::from(
            DB::raw('parameter with (readuncommitted)')
        )
            ->where('grp', 'STOK PERSEDIAAN')
            ->where('text', 'GUDANG')
            ->first();

        $idstokpersediaan = $filter->id ?? 0;
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS TAMPILAN KARTU STOK')
            ->where('subgrp', '=', 'STATUS TAMPILAN KARTU STOK')
            ->where('default', '=', 'YA')
            ->first();

        $idstatusstok = $status->id ?? 0;
        // DB::table($tempFilter)->insert(
        //     ["filter" => $filter->id]
        // );
        DB::table($tempdefault)->insert(
            ["stokdari_id" => $stokdari_id, "stokdari" => $stokdari, "stoksampai_id" => $stoksampai_id, "stoksampai" => $stoksampai, "gudang_id" => $gudang_id, "gudang" => $namagudang, "trado_id" => $trado_id, "trado" => $namatrado, "gandengan_id" => $gandengan_id, "gandengan" => $namagandengan, "filter" => $idstokpersediaan, "statustampil" => $idstatusstok]
        );

        // $data = [
        //     'stokdari' => DB::table($tempStokDari)->from(DB::raw($tempStokDari))->first(),
        //     'stoksampai' => DB::table($tempStokSampai)->from(DB::raw($tempStokSampai))->first(),
        //     'gudang' => DB::table($tempGudang)->from(DB::raw($tempGudang))->first(),
        //     'filter' => DB::table($tempFilter)->from(DB::raw($tempFilter))->first(),
        //     'trado' => DB::table($tempTrado)->from(DB::raw($tempTrado))->first(),
        //     'gandengan' => DB::table($tempGandengan)->from(DB::raw($tempGandengan))->first(),
        // ];
        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'stokdari_id',
                'stokdari',
                'stoksampai_id',
                'stoksampai',
                'gudang_id',
                'gudang',
                'gudang_id',
                'trado_id',
                'trado',
                'gandengan_id',
                'gandengan',
                'filter',
                'statustampil',
            );

        $data = $query->first();
        return $data;
    }

    public function getall($tgldari, $tglsampai, $stokdari, $stoksampai, $gudang_id, $trado_id, $gandengan_id, $filter)
    {

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


        $querygudang = DB::table('gudang')->from(
            DB::raw("gudang a with (readuncommitted)")
        )->select(
            'id',
        )->orderBy('a.id', 'asc')
            ->get();

        $datadetail = json_decode($querygudang, true);
        foreach ($datadetail as $item) {

            $filter = $filtergudang->text;
            $gandengan_id = 0;
            $trado_id = 0;
            $gudang_id = $item['id'];

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
            ], $this->getlaporan($tgldari, $tglsampai, $stokdari, $stoksampai, $gudang_id, $trado_id, $gandengan_id, $filter));
        }

        $querytrado = DB::table('trado')->from(
            DB::raw("trado a with (readuncommitted)")
        )->select(
            'id',
        )->orderBy('a.id', 'asc')
            ->get();

        $datadetail = json_decode($querytrado, true);
        foreach ($datadetail as $item) {

            $filter = $filtertrado->text;
            $gandengan_id = 0;
            $trado_id = $item['id'];
            $gudang_id = 0;

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
            ], $this->getlaporan($tgldari, $tglsampai, $stokdari, $stoksampai, $gudang_id, $trado_id, $gandengan_id, $filter));
        }

        $querygandengan = DB::table('gandengan')->from(
            DB::raw("gandengan a with (readuncommitted)")
        )->select(
            'id',
        )->orderBy('a.id', 'asc')
            ->get();

        $datadetail = json_decode($querygandengan, true);
        foreach ($datadetail as $item) {

            $filter = $filtergandengan->text;
            $gandengan_id = $item['id'];
            $trado_id = 0;
            $gudang_id = 0;

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
            ], $this->getlaporan($tgldari, $tglsampai, $stokdari, $stoksampai, $gudang_id, $trado_id, $gandengan_id, $filter));
        }



        $datalist = DB::table($temprekapall)->from(
            DB::raw($temprekapall . " as a")
        )
            ->select(
                'a.lokasi',
                'a.kodebarang',
                'a.namabarang',
                'a.tglbukti',
                'a.nobukti',
                'a.kategori_id',
                'a.qtymasuk',
                'a.nilaimasuk',
                'a.qtykeluar',
                'a.nilaikeluar',
                'a.qtysaldo',
                'a.nilaisaldo',
                'a.modifiedby',
            )
            ->orderBy('a.id', 'asc');
        // dd($datalist->get());
        // dd($datalist->get());
        return $datalist;
    }


    public function getlaporan($tgldari, $tglsampai, $stokdari, $stoksampai, $gudang_id, $trado_id, $gandengan_id, $filter, $stokgantung)
    {

        // dd($gandengan_id);

        $tglsaldo = date('Y-m-d', strtotime('-1 days', strtotime($tgldari)));
        $tgl = date('Y-m-d', strtotime($tgldari));



        $gudang_id = $gudang_id ?? 0;
        $trado_id = $trado_id ?? 0;
        $gandengan_id = $gandengan_id ?? 0;
        // dump($gudang_id);
        // dump($trado_id);
        // dump($gandengan_id);
        // dd( $filter);


        $temprekap = '##temprekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temprekap, function ($table) {
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
            $table->dateTime('tglinput')->nullable();
        });

        if ($stokdari == 0 || $stoksampai == 0) {
            $querystokdari = DB::table("stok")->from(
                DB::raw("stok a with (readuncommitted)")
            )
                ->select(
                    'a.id'
                )->orderBy('a.id', 'asc')
                ->first();

            $querystoksampai = DB::table("stok")->from(
                DB::raw("stok a with (readuncommitted)")
            )
                ->select(
                    'a.id'
                )->orderBy('a.id', 'desc')
                ->first();

            $stokdari = $querystokdari->id;
            $stoksampai = $querystoksampai->id;
        }

        if ($filter == '' || $filter == '0') {
            $queryrekap = db::table('stok')->from(
                DB::raw("stok as a1 with (readuncommitted)")
            )
                ->select(
                    'a1.id as stok_id',
                    db::raw("isnull(a.gudang_id,0) as gudang_id"),
                    db::raw("isnull(a.trado_id,0) as trado_id"),
                    db::raw("isnull(a.gandengan_id,0) as gandengan_id"),
                    db::raw("max(a.lokasi) as lokasi"),
                    db::raw("max(a1.namastok) as kodebarang"),
                    db::raw("max(a1.namastok) as namabarang"),
                    db::raw("'" . $tgl . "' as tglbukti"),
                    db::raw("'SALDO AWAL' as nobukti"),
                    db::raw("max(a1.kategori_id) as kategori_id"),
                    db::raw("0 as qtymasuk"),
                    db::raw("0 as nilaimasuk"),
                    db::raw("0 as qtykeluar"),
                    db::raw("0 as nilaikeluar"),
                    DB::raw("sum(isnull(a.qtymasuk,0)-isnull(a.qtykeluar,0) ) as qtysaldo"),
                    // DB::raw("sum(
                    //     (case when isnull(a1.statuspembulatanlebih2decimal,0)=1 then
                    //     isnull(a.nilaimasuk,0)-isnull(a.nilaikeluar,0)
                    //     else
                    //     round(isnull(a.nilaimasuk,0),2)-round(isnull(a.nilaikeluar,0),2) 
                    //     end)
                    //     ) as nilaisaldo"),
                    // DB::raw("sum(
                    //     round(round(cast(isnull(a.nilaimasuk,0) as money),3),2)-round(round(cast(isnull(a.nilaikeluar,0) as money),3),2)
                    //     ) as nilaisaldo"),
                    DB::raw("sum(
                            (isnull(a.nilaimasuk,0) -isnull(a.nilaikeluar,0))
                            ) as nilaisaldo"),

                    db::raw("'ADMIN' as modifiedby"),
                    db::raw("0 as urutfifo"),
                    db::raw("'1900/1/1' as tglinput"),

                )
                ->leftjoin(db::raw("kartustok a with (readuncommitted)"), 'a1.id', 'a.stok_id')
                ->whereRaw("a.tglbukti<='" . $tglsaldo . "'")
                ->whereRaw("(a1.id>=" . $stokdari . " and a1.id<=" . $stoksampai . ")")
                ->whereRaw("(isnull(a.gudang_id,0)=" . $gudang_id . " or " . $gudang_id . "=0)")
                ->whereRaw("(isnull(a.gandengan_id,0)=" . $gandengan_id . " or " . $gandengan_id . "=0)")
                ->whereRaw("(isnull(a.trado_id,0)=" . $trado_id . " or " . $trado_id . "=0)")
                ->groupBy('a1.id')
                // ->groupBy('a.gudang_id')
                // ->groupBy('a.trado_id')
                // ->groupBy('a.gandengan_id');                
                ->groupBy(db::raw("isnull(a.gudang_id,0)"))
                ->groupBy(db::raw("isnull(a.trado_id,0)"))
                ->groupBy(db::raw("isnull(a.gandengan_id,0)"));

            // dd('test');
            // dd
        } else if ($filter == 'GUDANG') {
            if ($gudang_id == 0) {
                $queryrekap = db::table('stok')->from(
                    DB::raw("stok as a1 with (readuncommitted)")
                )
                    ->select(
                        'a1.id as stok_id',
                        db::raw("isnull(a.gudang_id,0) as gudang_id"),
                        db::raw("isnull(a.trado_id,0) as trado_id"),
                        db::raw("isnull(a.gandengan_id,0) as gandengan_id"),
                        db::raw("max(a.lokasi) as lokasi"),
                        db::raw("max(a1.namastok) as kodebarang"),
                        db::raw("max(a1.namastok) as namabarang"),
                        db::raw("'" . $tgl . "' as tglbukti"),
                        db::raw("'SALDO AWAL' as nobukti"),
                        db::raw("max(a1.kategori_id) as kategori_id"),
                        db::raw("0 as qtymasuk"),
                        db::raw("0 as nilaimasuk"),
                        db::raw("0 as qtykeluar"),
                        db::raw("0 as nilaikeluar"),
                        DB::raw("sum(isnull(a.qtymasuk,0)-isnull(a.qtykeluar,0) ) as qtysaldo"),
                        // DB::raw("sum(
                        //     (case when isnull(a1.statuspembulatanlebih2decimal,0)=1 then
                        //     isnull(a.nilaimasuk,0)-isnull(a.nilaikeluar,0)
                        //     else
                        //     round(isnull(a.nilaimasuk,0),2)-round(isnull(a.nilaikeluar,0),2) 
                        //     end)
                        //         ) as nilaisaldo"),
                        // DB::raw("sum(
                        //     round(round(cast(isnull(a.nilaimasuk,0) as money),3),2)-round(round(cast(isnull(a.nilaikeluar,0) as money),3),2)
                        //     ) as nilaisaldo"),
                        DB::raw("sum(
                        (isnull(a.nilaimasuk,0) -isnull(a.nilaikeluar,0))
                        ) as nilaisaldo"),


                        db::raw("'ADMIN' as modifiedby"),
                        db::raw("0 as urutfifo"),
                        db::raw("'1900/1/1' as tglinput"),

                    )
                    ->leftjoin(db::raw("kartustok a with (readuncommitted)"), 'a1.id', 'a.stok_id')
                    ->whereRaw("a.tglbukti<='" . $tglsaldo . "'")
                    ->whereRaw("(a1.id>=" . $stokdari . " and a1.id<=" . $stoksampai . ")")
                    ->whereRaw("(isnull(a.gudang_id,0)<>0)")
                    ->groupBy('a1.id')
                    ->groupBy(db::raw("isnull(a.gudang_id,0)"))
                    ->groupBy(db::raw("isnull(a.trado_id,0)"))
                    ->groupBy(db::raw("isnull(a.gandengan_id,0)"));
            } else {
                $queryrekap = db::table('stok')->from(
                    DB::raw("stok as a1 with (readuncommitted)")
                )
                    ->select(
                        'a1.id as stok_id',
                        db::raw("isnull(a.gudang_id,0) as gudang_id"),
                        db::raw("isnull(a.trado_id,0) as trado_id"),
                        db::raw("isnull(a.gandengan_id,0) as gandengan_id"),
                        db::raw("max(a.lokasi) as lokasi"),
                        db::raw("max(a1.namastok) as kodebarang"),
                        db::raw("max(a1.namastok) as namabarang"),
                        db::raw("'" . $tgl . "' as tglbukti"),
                        db::raw("'SALDO AWAL' as nobukti"),
                        db::raw("max(a1.kategori_id) as kategori_id"),
                        db::raw("0 as qtymasuk"),
                        db::raw("0 as nilaimasuk"),
                        db::raw("0 as qtykeluar"),
                        db::raw("0 as nilaikeluar"),
                        DB::raw("sum(isnull(a.qtymasuk,0)-isnull(a.qtykeluar,0) ) as qtysaldo"),
                        // DB::raw("sum(
                        //     (case when isnull(a1.statuspembulatanlebih2decimal,0)=1 then
                        //     isnull(a.nilaimasuk,0)-isnull(a.nilaikeluar,0)
                        //     else
                        //     round(isnull(a.nilaimasuk,0),2)-round(isnull(a.nilaikeluar,0),2) 
                        //     end)
                        //         ) as nilaisaldo"),
                        // DB::raw("sum(
                        //     round(round(cast(isnull(a.nilaimasuk,0) as money),3),2)-round(round(cast(isnull(a.nilaikeluar,0) as money),3),2)
                        //     ) as nilaisaldo"),
                        DB::raw("sum(
                        (isnull(a.nilaimasuk,0) -isnull(a.nilaikeluar,0))
                        ) as nilaisaldo"),



                        db::raw("'ADMIN' as modifiedby"),
                        db::raw("0 as urutfifo"),
                        db::raw("'1900/1/1' as tglinput"),

                    )
                    ->leftjoin(db::raw("kartustok a with (readuncommitted)"), 'a1.id', 'a.stok_id')
                    ->whereRaw("a.tglbukti<='" . $tglsaldo . "'")
                    ->whereRaw("(a1.id>=" . $stokdari . " and a1.id<=" . $stoksampai . ")")
                    ->whereRaw("(a.gudang_id=" . $gudang_id . ")")
                    ->groupBy('a1.id')
                    ->groupBy(db::raw("isnull(a.gudang_id,0)"))
                    ->groupBy(db::raw("isnull(a.trado_id,0)"))
                    ->groupBy(db::raw("isnull(a.gandengan_id,0)"));
            }
        } else if ($filter == 'TRADO') {

            if ($trado_id == 0) {
                $queryrekap = db::table('stok')->from(
                    DB::raw("stok as a1 with (readuncommitted)")
                )
                    ->select(
                        'a1.id as stok_id',
                        db::raw("isnull(a.gudang_id,0) as gudang_id"),
                        db::raw("isnull(a.trado_id,0) as trado_id"),
                        db::raw("isnull(a.gandengan_id,0) as gandengan_id"),
                        db::raw("max(a.lokasi) as lokasi"),
                        db::raw("max(a1.namastok) as kodebarang"),
                        db::raw("max(a1.namastok) as namabarang"),
                        db::raw("'" . $tgl . "' as tglbukti"),
                        db::raw("'SALDO AWAL' as nobukti"),
                        db::raw("max(a1.kategori_id) as kategori_id"),
                        db::raw("0 as qtymasuk"),
                        db::raw("0 as nilaimasuk"),
                        db::raw("0 as qtykeluar"),
                        db::raw("0 as nilaikeluar"),
                        DB::raw("sum(isnull(a.qtymasuk,0)-isnull(a.qtykeluar,0) ) as qtysaldo"),
                        // DB::raw("sum(
                        //     (case when isnull(a1.statuspembulatanlebih2decimal,0)=1 then
                        //     isnull(a.nilaimasuk,0)-isnull(a.nilaikeluar,0)
                        //     else
                        //     round(isnull(a.nilaimasuk,0),2)-round(isnull(a.nilaikeluar,0),2) 
                        //     end)
                        //     ) as nilaisaldo"),
                        // DB::raw("sum(
                        //     round(round(cast(isnull(a.nilaimasuk,0) as money),3),2)-round(round(cast(isnull(a.nilaikeluar,0) as money),3),2)
                        //     ) as nilaisaldo"),
                        DB::raw("sum(
                        (isnull(a.nilaimasuk,0) -isnull(a.nilaikeluar,0))
                        ) as nilaisaldo"),



                        db::raw("'ADMIN' as modifiedby"),
                        db::raw("0 as urutfifo"),
                        db::raw("'1900/1/1' as tglinput"),

                    )
                    ->leftjoin(db::raw("kartustok a with (readuncommitted)"), 'a1.id', 'a.stok_id')
                    ->whereRaw("a.tglbukti<='" . $tglsaldo . "'")
                    ->whereRaw("(a1.id>=" . $stokdari . " and a1.id<=" . $stoksampai . ")")
                    ->whereRaw("(isnull(a.trado_id,0)<>0)")
                    ->groupBy('a1.id')
                    ->groupBy(db::raw("isnull(a.gudang_id,0)"))
                    ->groupBy(db::raw("isnull(a.trado_id,0)"))
                    ->groupBy(db::raw("isnull(a.gandengan_id,0)"));
            } else {
                $queryrekap = db::table('stok')->from(
                    DB::raw("stok as a1 with (readuncommitted)")
                )
                    ->select(
                        'a1.id as stok_id',
                        db::raw("isnull(a.gudang_id,0) as gudang_id"),
                        db::raw("isnull(a.trado_id,0) as trado_id"),
                        db::raw("isnull(a.gandengan_id,0) as gandengan_id"),
                        db::raw("max(a.lokasi) as lokasi"),
                        db::raw("max(a1.namastok) as kodebarang"),
                        db::raw("max(a1.namastok) as namabarang"),
                        db::raw("'" . $tgl . "' as tglbukti"),
                        db::raw("'SALDO AWAL' as nobukti"),
                        db::raw("max(a1.kategori_id) as kategori_id"),
                        db::raw("0 as qtymasuk"),
                        db::raw("0 as nilaimasuk"),
                        db::raw("0 as qtykeluar"),
                        db::raw("0 as nilaikeluar"),
                        DB::raw("sum(isnull(a.qtymasuk,0)-isnull(a.qtykeluar,0) ) as qtysaldo"),
                        // DB::raw("sum(
                        //     (case when isnull(a1.statuspembulatanlebih2decimal,0)=1 then
                        //     isnull(a.nilaimasuk,0)-isnull(a.nilaikeluar,0)
                        //     else
                        //     round(isnull(a.nilaimasuk,0),2)-round(isnull(a.nilaikeluar,0),2) 
                        //     end)
                        //     ) as nilaisaldo"),
                        // DB::raw("sum(
                        //     round(round(cast(isnull(a.nilaimasuk,0) as money),3),2)-round(round(cast(isnull(a.nilaikeluar,0) as money),3),2)
                        //     ) as nilaisaldo"),
                        DB::raw("sum(
                        (isnull(a.nilaimasuk,0) -isnull(a.nilaikeluar,0))
                        ) as nilaisaldo"),



                        db::raw("'ADMIN' as modifiedby"),
                        db::raw("0 as urutfifo"),
                        db::raw("'1900/1/1' as tglinput"),

                    )
                    ->leftjoin(db::raw("kartustok a with (readuncommitted)"), 'a1.id', 'a.stok_id')
                    ->whereRaw("a.tglbukti<='" . $tglsaldo . "'")
                    ->whereRaw("(a1.id>=" . $stokdari . " and a1.id<=" . $stoksampai . ")")
                    ->whereRaw("(a.trado_id=" . $trado_id . ")")
                    ->groupBy('a1.id')
                    ->groupBy(db::raw("isnull(a.gudang_id,0)"))
                    ->groupBy(db::raw("isnull(a.trado_id,0)"))
                    ->groupBy(db::raw("isnull(a.gandengan_id,0)"));
            }
        } else if ($filter == 'GANDENGAN') {
            if ($gandengan_id == 0) {
                $queryrekap = db::table('stok')->from(
                    DB::raw("stok as a1 with (readuncommitted)")
                )
                    ->select(
                        'a1.id as stok_id',
                        db::raw("isnull(a.gudang_id,0) as gudang_id"),
                        db::raw("isnull(a.trado_id,0) as trado_id"),
                        db::raw("isnull(a.gandengan_id,0) as gandengan_id"),
                        db::raw("max(a.lokasi) as lokasi"),
                        db::raw("max(a1.namastok) as kodebarang"),
                        db::raw("max(a1.namastok) as namabarang"),
                        db::raw("'" . $tgl . "' as tglbukti"),
                        db::raw("'SALDO AWAL' as nobukti"),
                        db::raw("max(a1.kategori_id) as kategori_id"),
                        db::raw("0 as qtymasuk"),
                        db::raw("0 as nilaimasuk"),
                        db::raw("0 as qtykeluar"),
                        db::raw("0 as nilaikeluar"),
                        DB::raw("sum(isnull(a.qtymasuk,0)-isnull(a.qtykeluar,0) ) as qtysaldo"),
                        // DB::raw("sum(
                        //     (case when isnull(a1.statuspembulatanlebih2decimal,0)=1 then
                        //     isnull(a.nilaimasuk,0)-isnull(a.nilaikeluar,0)
                        //     else
                        //     round(isnull(a.nilaimasuk,0),2)-round(isnull(a.nilaikeluar,0),2) 
                        //     end)
                        //     ) as nilaisaldo"),
                        // DB::raw("sum(
                        //     round(round(cast(isnull(a.nilaimasuk,0) as money),3),2)-round(round(cast(isnull(a.nilaikeluar,0) as money),3),2)
                        //     ) as nilaisaldo"),
                        DB::raw("sum(
                        (isnull(a.nilaimasuk,0) -isnull(a.nilaikeluar,0))
                        ) as nilaisaldo"),



                        db::raw("'ADMIN' as modifiedby"),
                        db::raw("0 as urutfifo"),
                        db::raw("'1900/1/1' as tglinput"),

                    )
                    ->leftjoin(db::raw("kartustok a with (readuncommitted)"), 'a1.id', 'a.stok_id')
                    ->whereRaw("a.tglbukti<='" . $tglsaldo . "'")
                    ->whereRaw("(a1.id>=" . $stokdari . " and a1.id<=" . $stoksampai . ")")
                    ->whereRaw("(isnull(a.gandengan_id,0)<>0)")
                    ->groupBy('a1.id')
                    ->groupBy(db::raw("isnull(a.gudang_id,0)"))
                    ->groupBy(db::raw("isnull(a.trado_id,0)"))
                    ->groupBy(db::raw("isnull(a.gandengan_id,0)"));
            } else {
                $queryrekap = db::table('stok')->from(
                    DB::raw("stok as a1 with (readuncommitted)")
                )
                    ->select(
                        'a1.id as stok_id',
                        db::raw("isnull(a.gudang_id,0) as gudang_id"),
                        db::raw("isnull(a.trado_id,0) as trado_id"),
                        db::raw("isnull(a.gandengan_id,0) as gandengan_id"),
                        db::raw("max(a.lokasi) as lokasi"),
                        db::raw("max(a1.namastok) as kodebarang"),
                        db::raw("max(a1.namastok) as namabarang"),
                        db::raw("'" . $tgl . "' as tglbukti"),
                        db::raw("'SALDO AWAL' as nobukti"),
                        db::raw("max(a1.kategori_id) as kategori_id"),
                        db::raw("0 as qtymasuk"),
                        db::raw("0 as nilaimasuk"),
                        db::raw("0 as qtykeluar"),
                        db::raw("0 as nilaikeluar"),
                        DB::raw("sum(isnull(a.qtymasuk,0)-isnull(a.qtykeluar,0) ) as qtysaldo"),
                        // DB::raw("sum(
                        //     (case when isnull(a1.statuspembulatanlebih2decimal,0)=1 then
                        //     isnull(a.nilaimasuk,0)-isnull(a.nilaikeluar,0)
                        //     else
                        //     round(isnull(a.nilaimasuk,0),2)-round(isnull(a.nilaikeluar,0),2) 
                        //     end)
                        //     ) as nilaisaldo"),
                        // DB::raw("sum(
                        //     round(round(cast(isnull(a.nilaimasuk,0) as money),3),2)-round(round(cast(isnull(a.nilaikeluar,0) as money),3),2)
                        //     ) as nilaisaldo"),
                        DB::raw("sum(
                        (isnull(a.nilaimasuk,0) -isnull(a.nilaikeluar,0))
                        ) as nilaisaldo"),



                        db::raw("'ADMIN' as modifiedby"),
                        db::raw("0 as urutfifo"),
                        db::raw("'1900/1/1' as tglinput"),

                    )
                    ->leftjoin(db::raw("kartustok a with (readuncommitted)"), 'a1.id', 'a.stok_id')
                    ->whereRaw("a.tglbukti<='" . $tglsaldo . "'")
                    ->whereRaw("(a1.id>=" . $stokdari . " and a1.id<=" . $stoksampai . ")")
                    ->whereRaw("(a.gandengan_id=" . $gandengan_id . ")")
                    ->groupBy('a1.id')
                    ->groupBy(db::raw("isnull(a.gudang_id,0)"))
                    ->groupBy(db::raw("isnull(a.trado_id,0)"))
                    ->groupBy(db::raw("isnull(a.gandengan_id,0)"));
            }
        } else {
            $queryrekap = db::table('stok')->from(
                DB::raw("stok as a1 with (readuncommitted)")
            )
                ->select(
                    'a1.id as stok_id',
                    db::raw("isnull(a.gudang_id,0) as gudang_id"),
                    db::raw("isnull(a.trado_id,0) as trado_id"),
                    db::raw("isnull(a.gandengan_id,0) as gandengan_id"),
                    db::raw("max(a.lokasi) as lokasi"),
                    db::raw("max(a1.namastok) as kodebarang"),
                    db::raw("max(a1.namastok) as namabarang"),
                    db::raw("'" . $tgl . "' as tglbukti"),
                    db::raw("'SALDO AWAL' as nobukti"),
                    db::raw("max(a1.kategori_id) as kategori_id"),
                    db::raw("0 as qtymasuk"),
                    db::raw("0 as nilaimasuk"),
                    db::raw("0 as qtykeluar"),
                    db::raw("0 as nilaikeluar"),
                    DB::raw("sum(isnull(a.qtymasuk,0)-isnull(a.qtykeluar,0) ) as qtysaldo"),
                    // DB::raw("sum(
                    //     (case when isnull(a1.statuspembulatanlebih2decimal,0)=1 then
                    //     isnull(a.nilaimasuk,0)-isnull(a.nilaikeluar,0)
                    //     else
                    //     round(isnull(a.nilaimasuk,0),2)-round(isnull(a.nilaikeluar,0),2) 
                    //     end)
                    // ) as nilaisaldo"),
                    // DB::raw("sum(
                    //     round(round(cast(isnull(a.nilaimasuk,0) as money),3),2)-round(round(cast(isnull(a.nilaikeluar,0) as money),3),2)
                    //     ) as nilaisaldo"),
                    DB::raw("sum(
                        (isnull(a.nilaimasuk,0) -isnull(a.nilaikeluar,0))
                        ) as nilaisaldo"),



                    db::raw("'ADMIN' as modifiedby"),
                    db::raw("0 as urutfifo"),
                    db::raw("'1900/1/1' as tglinput"),

                )
                ->leftjoin(db::raw("kartustok a with (readuncommitted)"), 'a1.id', 'a.stok_id')
                ->whereRaw("a.tglbukti<='" . $tglsaldo . "'")
                ->whereRaw("(a1.id>=" . $stokdari . " and a1.id<=" . $stoksampai . ")")
                ->whereRaw("(a.gudang_id=" . $gudang_id . " or " . $gudang_id . "=0)")
                ->whereRaw("(a.gandengan_id=" . $gandengan_id . " or " . $gandengan_id . "=0)")
                ->whereRaw("(a.trado_id=" . $trado_id . " or " . $trado_id . "=0)")
                ->groupBy('a1.id')
                ->groupBy(db::raw("isnull(a.gudang_id,0)"))
                ->groupBy(db::raw("isnull(a.trado_id,0)"))
                ->groupBy(db::raw("isnull(a.gandengan_id,0)"));
        }




        // dd('test');
        DB::table($temprekap)->insertUsing([
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
            'tglinput',
        ], $queryrekap);
        // dd('test');

        // dd(db::table($temprekap)->get());

        $temprekapinput = '##temprekapinput' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temprekapinput, function ($table) {
            $table->id();
            $table->string('nobukti', 100)->nullable();
            $table->integer('stok_id')->nullable();
            $table->double('qtymasuk', 15, 2)->nullable();
            $table->double('qtykeluar', 15, 2)->nullable();
            $table->dateTime('tglinput')->nullable();
            $table->dateTime('tglinputheader')->nullable();
        });

        if ($filter == '' || $filter == '0') {

            $queryinput = db::table('kartustok')->from(
                DB::raw("kartustok as a with (readuncommitted)")
            )
                ->select(
                    db::raw("a.nobukti as nobukti"),
                    db::raw("a.stok_id as stok_id"),
                    db::raw("'1900/1/1' as tglinput"),
                    db::raw("sum(A.qtymasuk) as qtymasuk"),
                    db::raw("sum(A.qtykeluar) as qtykeluar"),
                )
                ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id')
                ->whereRaw("(a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("(a.stok_id>=" . $stokdari . " and a.stok_id<=" . $stoksampai . ")")
                ->whereRaw("(a.gudang_id=" . $gudang_id . " or " . $gudang_id . "=0)")
                ->whereRaw("(a.gandengan_id=" . $gandengan_id . " or " . $gandengan_id . "=0)")
                ->whereRaw("(a.trado_id=" . $trado_id . " or " . $trado_id . "=0)")
                ->Groupby('a.nobukti')
                ->Groupby('a.stok_id');

            DB::table($temprekapinput)->insertUsing([
                'nobukti',
                'stok_id',
                'tglinput',
                'qtymasuk',
                'qtykeluar',

            ], $queryinput);

            DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinput=b.created_at from " . $temprekapinput . " a inner join penerimaanstokdetail b on a.nobukti=b.nobukti and a.stok_id=b.stok_id"));
            DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinput=b.created_at from " . $temprekapinput . " a inner join pengeluaranstokdetail b on a.nobukti=b.nobukti and a.stok_id=b.stok_id"));
            DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinputheader=b.created_at from " . $temprekapinput . " a inner join penerimaanstokheader b on a.nobukti=b.nobukti "));
            DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinputheader=b.created_at from " . $temprekapinput . " a inner join pengeluaranstokheader b on a.nobukti=b.nobukti "));

            $queryrekap = db::table('kartustok')->from(
                DB::raw("kartustok as a with (readuncommitted)")
            )
                ->select(
                    'a.stok_id',
                    db::raw("isnull(a.gudang_id,0) as gudang_id"),
                    db::raw("isnull(a.trado_id,0) as trado_id"),
                    db::raw("isnull(a.gandengan_id,0) as gandengan_id"),
                    db::raw("(a.lokasi) as lokasi"),
                    db::raw("(a.kodebarang) as kodebarang"),
                    db::raw("(a.namabarang) as namabarang"),
                    db::raw("a.tglbukti as tglbukti"),
                    db::raw("a.nobukti as nobukti"),
                    db::raw("(a.kategori_id) as kategori_id"),
                    db::raw("isnull(a.qtymasuk,0) as qtymasuk"),
                    // db::raw("
                    // (case when isnull(b.statuspembulatanlebih2decimal,0)=1 then
                    // isnull(a.nilaimasuk,0)
                    // else
                    // round(isnull(a.nilaimasuk,0),2)
                    // end)
                    //  as nilaimasuk"),
                    // db::raw("
                    // round(round(cast(isnull(a.nilaimasuk,0) as money),3),2)
                    //  as nilaimasuk"),
                    db::raw("
                     isnull(a.nilaimasuk,0)
                      as nilaimasuk"),
                    db::raw("isnull(a.qtykeluar,0) as qtykeluar"),
                    // db::raw("
                    // (case when isnull(b.statuspembulatanlebih2decimal,0)=1 then
                    // isnull(a.nilaikeluar,0)
                    // else
                    // round(isnull(a.nilaikeluar,0),2)
                    // end)
                    // as nilaikeluar"),
                    // db::raw("
                    // round(round(cast(isnull(a.nilaikeluar,0) as money),3),2)
                    // as nilaikeluar"),
                    db::raw("
                    isnull(a.nilaikeluar,0) 
                    as nilaikeluar"),
                    DB::raw("0 as qtysaldo"),
                    DB::raw("0 as nilaisaldo"),
                    db::raw("a.modifiedby"),
                    db::raw("a.urutfifo as urutfifo"),
                    db::raw("c.tglinput as tglinput"),
                )
                ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id')
                ->leftjoin(DB::raw($temprekapinput . " as c"), function ($join) {
                    $join->on('a.nobukti', '=', 'c.nobukti');
                    $join->on('b.id', '=', 'c.stok_id');
                })

                // ->leftjoin(db::raw($temprekapinput . " c "), 'a.nobukti', 'c.nobukti')

                ->whereRaw("(a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("(a.stok_id>=" . $stokdari . " and a.stok_id<=" . $stoksampai . ")")
                ->whereRaw("(a.gudang_id=" . $gudang_id . " or " . $gudang_id . "=0)")
                ->whereRaw("(a.gandengan_id=" . $gandengan_id . " or " . $gandengan_id . "=0)")
                ->whereRaw("(a.trado_id=" . $trado_id . " or " . $trado_id . "=0)")
                ->orderby('a.tglbukti', 'asc')
                ->orderby('c.tglinput', 'asc')
                ->orderby('a.urutfifo', 'asc')
                ->orderby('a.nobukti', 'asc')
                ->orderby('a.id', 'asc');
        } else if ($filter == 'GUDANG') {
            if ($gudang_id == 0) {

                $queryinput = db::table('kartustok')->from(
                    DB::raw("kartustok as a with (readuncommitted)")
                )
                    ->select(
                        db::raw("a.nobukti as nobukti"),
                        db::raw("a.stok_id as stok_id"),
                        db::raw("'1900/1/1' as tglinput"),
                        db::raw("sum(A.qtymasuk) as qtymasuk"),
                        db::raw("sum(A.qtykeluar) as qtykeluar"),
                    )
                    ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id')
                    ->whereRaw("(a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                    ->whereRaw("(a.stok_id>=" . $stokdari . " and a.stok_id<=" . $stoksampai . ")")
                    ->whereRaw("(isnull(a.gudang_id,0)<>0)")
                    ->Groupby('a.nobukti')
                    ->Groupby('a.stok_id');

                DB::table($temprekapinput)->insertUsing([
                    'nobukti',
                    'stok_id',
                    'tglinput',
                    'qtymasuk',
                    'qtykeluar',

                ], $queryinput);

                DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinput=b.created_at from " . $temprekapinput . " a inner join penerimaanstokdetail b on a.nobukti=b.nobukti  and a.stok_id=b.stok_id"));
                DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinput=b.created_at from " . $temprekapinput . " a inner join pengeluaranstokdetail b on a.nobukti=b.nobukti  and a.stok_id=b.stok_id"));
                DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinputheader=b.created_at from " . $temprekapinput . " a inner join penerimaanstokheader b on a.nobukti=b.nobukti "));
                DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinputheader=b.created_at from " . $temprekapinput . " a inner join pengeluaranstokheader b on a.nobukti=b.nobukti "));


                $queryrekap = db::table('kartustok')->from(
                    DB::raw("kartustok as a with (readuncommitted)")
                )
                    ->select(
                        'a.stok_id',
                        db::raw("isnull(a.gudang_id,0) as gudang_id"),
                        db::raw("isnull(a.trado_id,0) as trado_id"),
                        db::raw("isnull(a.gandengan_id,0) as gandengan_id"),
                        db::raw("(a.lokasi) as lokasi"),
                        db::raw("(a.kodebarang) as kodebarang"),
                        db::raw("(a.namabarang) as namabarang"),
                        db::raw("a.tglbukti as tglbukti"),
                        db::raw("a.nobukti as nobukti"),
                        db::raw("(a.kategori_id) as kategori_id"),
                        db::raw("isnull(a.qtymasuk,0) as qtymasuk"),
                        // db::raw("
                        // (case when isnull(b.statuspembulatanlebih2decimal,0)=1 then
                        // isnull(a.nilaimasuk,0)
                        // else
                        // round(isnull(a.nilaimasuk,0),2)
                        // end)
                        //  as nilaimasuk"),
                        // db::raw("
                        // round(round(cast(isnull(a.nilaimasuk,0) as money),3),2)
                        //  as nilaimasuk"),
                        db::raw("
                     isnull(a.nilaimasuk,0)
                      as nilaimasuk"),
                        db::raw("isnull(a.qtykeluar,0) as qtykeluar"),
                        // db::raw("
                        // (case when isnull(b.statuspembulatanlebih2decimal,0)=1 then
                        // isnull(a.nilaikeluar,0)
                        // else
                        // round(isnull(a.nilaikeluar,0),2)
                        // end)
                        // as nilaikeluar"),
                        // db::raw("
                        // round(round(cast(isnull(a.nilaikeluar,0) as money),3),2)
                        // as nilaikeluar"),
                        db::raw("
                    isnull(a.nilaikeluar,0) 
                    as nilaikeluar"),
                        DB::raw("0 as qtysaldo"),
                        DB::raw("0 as nilaisaldo"),
                        db::raw("a.modifiedby"),
                        db::raw("a.urutfifo as urutfifo"),
                        db::raw("c.tglinput as tglinput"),

                    )
                    ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id')
                    ->leftjoin(DB::raw($temprekapinput . " as c"), function ($join) {
                        $join->on('a.nobukti', '=', 'c.nobukti');
                        $join->on('b.id', '=', 'c.stok_id');
                    })
                    // ->leftjoin(db::raw($temprekapinput . " c "), 'a.nobukti', 'c.nobukti')
                    ->whereRaw("(a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                    ->whereRaw("(a.stok_id>=" . $stokdari . " and a.stok_id<=" . $stoksampai . ")")
                    ->whereRaw("(isnull(a.gudang_id,0)<>0)")
                    ->orderby('a.tglbukti', 'asc')
                    ->orderby('c.tglinput', 'asc')
                    ->orderby('a.urutfifo', 'asc')
                    ->orderby('a.nobukti', 'asc')
                    ->orderby('a.id', 'asc');
            } else {


                $queryinput = db::table('kartustok')->from(
                    DB::raw("kartustok as a with (readuncommitted)")
                )
                    ->select(
                        db::raw("a.nobukti as nobukti"),
                        db::raw("a.stok_id as stok_id"),
                        db::raw("'1900/1/1' as tglinput"),
                        db::raw("sum(A.qtymasuk) as qtymasuk"),
                        db::raw("sum(A.qtykeluar) as qtykeluar"),
                    )
                    ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id')
                    ->whereRaw("(a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                    ->whereRaw("(a.stok_id>=" . $stokdari . " and a.stok_id<=" . $stoksampai . ")")
                    ->whereRaw("(a.gudang_id=" . $gudang_id . ")")
                    ->Groupby('a.nobukti')
                    ->Groupby('a.stok_id');

                DB::table($temprekapinput)->insertUsing([
                    'nobukti',
                    'stok_id',
                    'tglinput',
                    'qtymasuk',
                    'qtykeluar',

                ], $queryinput);
                DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinput=b.created_at from " . $temprekapinput . " a inner join penerimaanstokdetail b on a.nobukti=b.nobukti  and a.stok_id=b.stok_id"));
                DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinput=b.created_at from " . $temprekapinput . " a inner join pengeluaranstokdetail b on a.nobukti=b.nobukti  and a.stok_id=b.stok_id"));
                DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinputheader=b.created_at from " . $temprekapinput . " a inner join penerimaanstokheader b on a.nobukti=b.nobukti "));
                DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinputheader=b.created_at from " . $temprekapinput . " a inner join pengeluaranstokheader b on a.nobukti=b.nobukti "));

                // disini

                // dd(db::table($temprekapinput)->get());
                $queryrekap = db::table('kartustok')->from(
                    DB::raw("kartustok as a with (readuncommitted)")
                )
                    ->select(
                        'a.stok_id',
                        db::raw("isnull(a.gudang_id,0) as gudang_id"),
                        db::raw("isnull(a.trado_id,0) as trado_id"),
                        db::raw("isnull(a.gandengan_id,0) as gandengan_id"),
                        db::raw("(a.lokasi) as lokasi"),
                        db::raw("(a.kodebarang) as kodebarang"),
                        db::raw("(a.namabarang) as namabarang"),
                        db::raw("a.tglbukti as tglbukti"),
                        db::raw("a.nobukti as nobukti"),
                        db::raw("(a.kategori_id) as kategori_id"),
                        db::raw("isnull(a.qtymasuk,0) as qtymasuk"),
                        // db::raw("
                        // (case when isnull(b.statuspembulatanlebih2decimal,0)=1 then
                        // isnull(a.nilaimasuk,0)
                        // else
                        // round(isnull(a.nilaimasuk,0),2)
                        // end)
                        //  as nilaimasuk"),
                        // db::raw("
                        // round(round(cast(isnull(a.nilaimasuk,0) as money),3),2)
                        //  as nilaimasuk"),
                        db::raw("
                     isnull(a.nilaimasuk,0)
                      as nilaimasuk"),
                        db::raw("isnull(a.qtykeluar,0) as qtykeluar"),
                        // db::raw("
                        // (case when isnull(b.statuspembulatanlebih2decimal,0)=1 then
                        // isnull(a.nilaikeluar,0)
                        // else
                        // round(isnull(a.nilaikeluar,0),2)
                        // end)
                        // as nilaikeluar"),
                        // db::raw("
                        // round(round(cast(isnull(a.nilaikeluar,0) as money),3),2)
                        // as nilaikeluar"),
                        db::raw("
                    isnull(a.nilaikeluar,0) 
                    as nilaikeluar"),

                        DB::raw("0 as qtysaldo"),
                        DB::raw("0 as nilaisaldo"),
                        db::raw("a.modifiedby"),
                        db::raw("a.urutfifo as urutfifo"),
                        db::raw("c.tglinput as tglinput"),
                    )
                    ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id')
                    // ->leftjoin(db::raw($temprekapinput . " c "), 'a.nobukti', 'c.nobukti')
                    ->leftjoin(DB::raw($temprekapinput . " as c"), function ($join) {
                        $join->on('a.nobukti', '=', 'c.nobukti');
                        $join->on('b.id', '=', 'c.stok_id');
                    })

                    ->whereRaw("(a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                    ->whereRaw("(a.stok_id>=" . $stokdari . " and a.stok_id<=" . $stoksampai . ")")
                    ->whereRaw("(a.gudang_id=" . $gudang_id . ")")
                    ->orderby('a.tglbukti', 'asc')
                    ->orderby('c.tglinput', 'asc')
                    ->orderby('a.urutfifo', 'asc')
                    ->orderby('a.nobukti', 'asc')
                    ->orderby('a.id', 'asc');
            }
        } else if ($filter == 'TRADO') {
            if ($trado_id == 0) {

                $queryinput = db::table('kartustok')->from(
                    DB::raw("kartustok as a with (readuncommitted)")
                )
                    ->select(
                        db::raw("a.nobukti as nobukti"),
                        db::raw("a.stok_id as stok_id"),
                        db::raw("'1900/1/1' as tglinput"),
                        db::raw("sum(A.qtymasuk) as qtymasuk"),
                        db::raw("sum(A.qtykeluar) as qtykeluar"),
                    )
                    ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id')
                    ->whereRaw("(a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                    ->whereRaw("(a.stok_id>=" . $stokdari . " and a.stok_id<=" . $stoksampai . ")")
                    ->whereRaw("(isnull(a.trado_id,0)<>0)")
                    ->Groupby('a.nobukti')
                    ->Groupby('a.stok_id');

                DB::table($temprekapinput)->insertUsing([
                    'nobukti',
                    'stok_id',
                    'tglinput',
                    'qtymasuk',
                    'qtykeluar',

                ], $queryinput);

                DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinput=b.created_at from " . $temprekapinput . " a inner join penerimaanstokdetail b on a.nobukti=b.nobukti  and a.stok_id=b.stok_id"));
                DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinput=b.created_at from " . $temprekapinput . " a inner join pengeluaranstokdetail b on a.nobukti=b.nobukti  and a.stok_id=b.stok_id"));
                DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinputheader=b.created_at from " . $temprekapinput . " a inner join penerimaanstokheader b on a.nobukti=b.nobukti "));
                DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinputheader=b.created_at from " . $temprekapinput . " a inner join pengeluaranstokheader b on a.nobukti=b.nobukti "));

                $queryrekap = db::table('kartustok')->from(
                    DB::raw("kartustok as a with (readuncommitted)")
                )
                    ->select(
                        'a.stok_id',
                        db::raw("isnull(a.gudang_id,0) as gudang_id"),
                        db::raw("isnull(a.trado_id,0) as trado_id"),
                        db::raw("isnull(a.gandengan_id,0) as gandengan_id"),
                        db::raw("(a.lokasi) as lokasi"),
                        db::raw("(a.kodebarang) as kodebarang"),
                        db::raw("(a.namabarang) as namabarang"),
                        db::raw("a.tglbukti as tglbukti"),
                        db::raw("a.nobukti as nobukti"),
                        db::raw("(a.kategori_id) as kategori_id"),
                        db::raw("isnull(a.qtymasuk,0) as qtymasuk"),
                        // db::raw("
                        // (case when isnull(b.statuspembulatanlebih2decimal,0)=1 then
                        // isnull(a.nilaimasuk,0)
                        // else
                        // round(isnull(a.nilaimasuk,0),2)
                        // end)
                        //  as nilaimasuk"),
                        // db::raw("
                        // round(round(cast(isnull(a.nilaimasuk,0) as money),3),2)
                        //  as nilaimasuk"),
                        db::raw("
                     isnull(a.nilaimasuk,0)
                      as nilaimasuk"),
                        db::raw("isnull(a.qtykeluar,0) as qtykeluar"),
                        // db::raw("
                        // (case when isnull(b.statuspembulatanlebih2decimal,0)=1 then
                        // isnull(a.nilaikeluar,0)
                        // else
                        // round(isnull(a.nilaikeluar,0),2)
                        // end)
                        // as nilaikeluar"),
                        // db::raw("
                        // round(round(cast(isnull(a.nilaikeluar,0) as money),3),2)
                        // as nilaikeluar"),
                        db::raw("
                    isnull(a.nilaikeluar,0) 
                    as nilaikeluar"),

                        DB::raw("0 as qtysaldo"),
                        DB::raw("0 as nilaisaldo"),
                        db::raw("a.modifiedby"),
                        db::raw("a.urutfifo as urutfifo"),
                        db::raw("c.tglinput as tglinput"),
                    )
                    ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id')
                    // ->leftjoin(db::raw($temprekapinput . " c "), 'a.nobukti', 'c.nobukti')
                    ->leftjoin(DB::raw($temprekapinput . " as c"), function ($join) {
                        $join->on('a.nobukti', '=', 'c.nobukti');
                        $join->on('b.id', '=', 'c.stok_id');
                    })

                    ->whereRaw("(a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                    ->whereRaw("(a.stok_id>=" . $stokdari . " and a.stok_id<=" . $stoksampai . ")")
                    ->whereRaw("(isnull(a.trado_id,0)<>0)")
                    ->orderby('a.tglbukti', 'asc')
                    ->orderby('c.tglinput', 'asc')
                    ->orderby('a.urutfifo', 'asc')
                    ->orderby('a.nobukti', 'asc')
                    ->orderby('a.id', 'asc');
            } else {

                $queryinput = db::table('kartustok')->from(
                    DB::raw("kartustok as a with (readuncommitted)")
                )
                    ->select(
                        db::raw("a.nobukti as nobukti"),
                        db::raw("a.stok_id as stok_id"),
                        db::raw("'1900/1/1' as tglinput"),
                        db::raw("sum(A.qtymasuk) as qtymasuk"),
                        db::raw("sum(A.qtykeluar) as qtykeluar"),
                    )
                    ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id')
                    ->whereRaw("(a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                    ->whereRaw("(a.stok_id>=" . $stokdari . " and a.stok_id<=" . $stoksampai . ")")
                    ->whereRaw("(a.trado_id=" . $trado_id . ")")
                    ->Groupby('a.nobukti')
                    ->Groupby('a.stok_id');

                DB::table($temprekapinput)->insertUsing([
                    'nobukti',
                    'stok_id',
                    'tglinput',
                    'qtymasuk',
                    'qtykeluar',

                ], $queryinput);

                DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinput=b.created_at from " . $temprekapinput . " a inner join penerimaanstokdetail b on a.nobukti=b.nobukti  and a.stok_id=b.stok_id"));
                DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinput=b.created_at from " . $temprekapinput . " a inner join pengeluaranstokdetail b on a.nobukti=b.nobukti  and a.stok_id=b.stok_id"));
                DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinputheader=b.created_at from " . $temprekapinput . " a inner join penerimaanstokheader b on a.nobukti=b.nobukti "));
                DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinputheader=b.created_at from " . $temprekapinput . " a inner join pengeluaranstokheader b on a.nobukti=b.nobukti "));


                $queryrekap = db::table('kartustok')->from(
                    DB::raw("kartustok as a with (readuncommitted)")
                )
                    ->select(
                        'a.stok_id',
                        db::raw("isnull(a.gudang_id,0) as gudang_id"),
                        db::raw("isnull(a.trado_id,0) as trado_id"),
                        db::raw("isnull(a.gandengan_id,0) as gandengan_id"),
                        db::raw("(a.lokasi) as lokasi"),
                        db::raw("(a.kodebarang) as kodebarang"),
                        db::raw("(a.namabarang) as namabarang"),
                        db::raw("a.tglbukti as tglbukti"),
                        db::raw("a.nobukti as nobukti"),
                        db::raw("(a.kategori_id) as kategori_id"),
                        db::raw("isnull(a.qtymasuk,0) as qtymasuk"),
                        // db::raw("
                        // (case when isnull(b.statuspembulatanlebih2decimal,0)=1 then
                        // isnull(a.nilaimasuk,0)
                        // else
                        // round(isnull(a.nilaimasuk,0),2)
                        // end)
                        //  as nilaimasuk"),
                        // db::raw("
                        // round(round(cast(isnull(a.nilaimasuk,0) as money),3),2)
                        //  as nilaimasuk"),
                        db::raw("
                     isnull(a.nilaimasuk,0)
                      as nilaimasuk"),
                        db::raw("isnull(a.qtykeluar,0) as qtykeluar"),
                        // db::raw("
                        // (case when isnull(b.statuspembulatanlebih2decimal,0)=1 then
                        // isnull(a.nilaikeluar,0)
                        // else
                        // round(isnull(a.nilaikeluar,0),2)
                        // end)
                        // as nilaikeluar"),
                        // db::raw("
                        // round(round(cast(isnull(a.nilaikeluar,0) as money),3),2)
                        // as nilaikeluar"),
                        db::raw("
                    isnull(a.nilaikeluar,0) 
                    as nilaikeluar"),

                        DB::raw("0 as qtysaldo"),
                        DB::raw("0 as nilaisaldo"),
                        db::raw("a.modifiedby"),
                        db::raw("a.urutfifo as urutfifo"),
                        db::raw("c.tglinput as tglinput"),
                    )
                    ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id')
                    // ->leftjoin(db::raw($temprekapinput . " c "), 'a.nobukti', 'c.nobukti')
                    ->leftjoin(DB::raw($temprekapinput . " as c"), function ($join) {
                        $join->on('a.nobukti', '=', 'c.nobukti');
                        $join->on('b.id', '=', 'c.stok_id');
                    })


                    ->whereRaw("(a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                    ->whereRaw("(a.stok_id>=" . $stokdari . " and a.stok_id<=" . $stoksampai . ")")
                    ->whereRaw("(a.trado_id=" . $trado_id . ")")
                    ->orderby('a.tglbukti', 'asc')
                    ->orderby('c.tglinput', 'asc')
                    ->orderby('a.urutfifo', 'asc')
                    ->orderby('a.nobukti', 'asc')
                    ->orderby('a.id', 'asc');
            }
        } else if ($filter == 'GANDENGAN') {
            if ($gandengan_id == 0) {

                $queryinput = db::table('kartustok')->from(
                    DB::raw("kartustok as a with (readuncommitted)")
                )
                    ->select(
                        db::raw("a.nobukti as nobukti"),
                        db::raw("a.stok_id as nobukti"),
                        db::raw("'1900/1/1' as tglinput"),
                        db::raw("sum(A.qtymasuk) as qtymasuk"),
                        db::raw("sum(A.qtykeluar) as qtykeluar"),
                    )
                    ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id')
                    ->whereRaw("(a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                    ->whereRaw("(a.stok_id>=" . $stokdari . " and a.stok_id<=" . $stoksampai . ")")
                    ->whereRaw("(isnull(a.gandengan_id,0)<>0)")
                    ->Groupby('a.nobukti')
                    ->Groupby('a.stok_id');

                DB::table($temprekapinput)->insertUsing([
                    'nobukti',
                    'stok_id',
                    'tglinput',
                    'qtymasuk',
                    'qtykeluar',

                ], $queryinput);

                DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinput=b.created_at from " . $temprekapinput . " a inner join penerimaanstokdetail b on a.nobukti=b.nobukti and a.stok_id=b.stok_id"));
                DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinput=b.created_at from " . $temprekapinput . " a inner join pengeluaranstokdetail b on a.nobukti=b.nobukti and a.stok_id=b.stok_id"));
                DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinputheader=b.created_at from " . $temprekapinput . " a inner join penerimaanstokheader b on a.nobukti=b.nobukti "));
                DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinputheader=b.created_at from " . $temprekapinput . " a inner join pengeluaranstokheader b on a.nobukti=b.nobukti "));


                $queryrekap = db::table('kartustok')->from(
                    DB::raw("kartustok as a with (readuncommitted)")
                )
                    ->select(
                        'a.stok_id',
                        db::raw("isnull(a.gudang_id,0) as gudang_id"),
                        db::raw("isnull(a.trado_id,0) as trado_id"),
                        db::raw("isnull(a.gandengan_id,0) as gandengan_id"),
                        db::raw("(a.lokasi) as lokasi"),
                        db::raw("(a.kodebarang) as kodebarang"),
                        db::raw("(a.namabarang) as namabarang"),
                        db::raw("a.tglbukti as tglbukti"),
                        db::raw("a.nobukti as nobukti"),
                        db::raw("(a.kategori_id) as kategori_id"),
                        db::raw("isnull(a.qtymasuk,0) as qtymasuk"),
                        // db::raw("
                        // (case when isnull(b.statuspembulatanlebih2decimal,0)=1 then
                        // isnull(a.nilaimasuk,0)
                        // else
                        // round(isnull(a.nilaimasuk,0),2)
                        // end)
                        //  as nilaimasuk"),
                        // db::raw("
                        // round(round(cast(isnull(a.nilaimasuk,0) as money),3),2)
                        //  as nilaimasuk"),
                        db::raw("
                     isnull(a.nilaimasuk,0)
                      as nilaimasuk"),
                        db::raw("isnull(a.qtykeluar,0) as qtykeluar"),
                        // db::raw("
                        // (case when isnull(b.statuspembulatanlebih2decimal,0)=1 then
                        // isnull(a.nilaikeluar,0)
                        // else
                        // round(isnull(a.nilaikeluar,0),2)
                        // end)
                        // as nilaikeluar"),
                        // db::raw("
                        // round(round(cast(isnull(a.nilaikeluar,0) as money),3),2)
                        // as nilaikeluar"),
                        db::raw("
                    isnull(a.nilaikeluar,0) 
                    as nilaikeluar"),

                        DB::raw("0 as qtysaldo"),
                        DB::raw("0 as nilaisaldo"),
                        db::raw("a.modifiedby"),
                        db::raw("a.urutfifo as urutfifo"),
                        db::raw("c.tglinput as tglinput"),
                    )
                    ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id')
                    // ->leftjoin(db::raw($temprekapinput . " c "), 'a.nobukti', 'c.nobukti')
                    ->leftjoin(DB::raw($temprekapinput . " as c"), function ($join) {
                        $join->on('a.nobukti', '=', 'c.nobukti');
                        $join->on('b.id', '=', 'c.stok_id');
                    })

                    ->whereRaw("(a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                    ->whereRaw("(a.stok_id>=" . $stokdari . " and a.stok_id<=" . $stoksampai . ")")
                    ->whereRaw("(isnull(a.gandengan_id,0)<>0)")
                    ->orderby('a.tglbukti', 'asc')
                    ->orderby('c.tglinput', 'asc')
                    ->orderby('a.urutfifo', 'asc')
                    ->orderby('a.nobukti', 'asc')
                    ->orderby('a.id', 'asc');
            } else {

                $queryinput = db::table('kartustok')->from(
                    DB::raw("kartustok as a with (readuncommitted)")
                )
                    ->select(
                        db::raw("a.nobukti as nobukti"),
                        db::raw("a.stok_id as stok_id"),
                        db::raw("'1900/1/1' as tglinput"),
                        db::raw("sum(A.qtymasuk) as qtymasuk"),
                        db::raw("sum(A.qtykeluar) as qtykeluar"),
                    )
                    ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id')
                    ->whereRaw("(a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                    ->whereRaw("(a.stok_id>=" . $stokdari . " and a.stok_id<=" . $stoksampai . ")")
                    ->whereRaw("(a.gandengan_id=" . $gandengan_id . ")")
                    ->Groupby('a.nobukti')
                    ->Groupby('a.stok_id');

                DB::table($temprekapinput)->insertUsing([
                    'nobukti',
                    'stok_id',
                    'tglinput',
                    'qtymasuk',
                    'qtykeluar',

                ], $queryinput);

                DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinput=b.created_at from " . $temprekapinput . " a inner join penerimaanstokdetail b on a.nobukti=b.nobukti  and a.stok_id=b.stok_id"));
                DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinput=b.created_at from " . $temprekapinput . " a inner join pengeluaranstokdetail b on a.nobukti=b.nobukti  and a.stok_id=b.stok_id"));
                DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinputheader=b.created_at from " . $temprekapinput . " a inner join penerimaanstokheader b on a.nobukti=b.nobukti "));
                DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinputheader=b.created_at from " . $temprekapinput . " a inner join pengeluaranstokheader b on a.nobukti=b.nobukti "));

                $queryrekap = db::table('kartustok')->from(
                    DB::raw("kartustok as a with (readuncommitted)")
                )
                    ->select(
                        'a.stok_id',
                        db::raw("isnull(a.gudang_id,0) as gudang_id"),
                        db::raw("isnull(a.trado_id,0) as trado_id"),
                        db::raw("isnull(a.gandengan_id,0) as gandengan_id"),
                        db::raw("(a.lokasi) as lokasi"),
                        db::raw("(a.kodebarang) as kodebarang"),
                        db::raw("(a.namabarang) as namabarang"),
                        db::raw("a.tglbukti as tglbukti"),
                        db::raw("a.nobukti as nobukti"),
                        db::raw("(a.kategori_id) as kategori_id"),
                        db::raw("isnull(a.qtymasuk,0) as qtymasuk"),
                        // db::raw("
                        // (case when isnull(b.statuspembulatanlebih2decimal,0)=1 then
                        // isnull(a.nilaimasuk,0)
                        // else
                        // round(isnull(a.nilaimasuk,0),2)
                        // end)
                        //  as nilaimasuk"),
                        // db::raw("
                        // round(round(cast(isnull(a.nilaimasuk,0) as money),3),2)
                        //  as nilaimasuk"),
                        db::raw("
                     isnull(a.nilaimasuk,0)
                      as nilaimasuk"),
                        db::raw("isnull(a.qtykeluar,0) as qtykeluar"),
                        // db::raw("
                        // (case when isnull(b.statuspembulatanlebih2decimal,0)=1 then
                        // isnull(a.nilaikeluar,0)
                        // else
                        // round(isnull(a.nilaikeluar,0),2)
                        // end)
                        // as nilaikeluar"),
                        // db::raw("
                        // round(round(cast(isnull(a.nilaikeluar,0) as money),3),2)
                        // as nilaikeluar"),
                        db::raw("
                    isnull(a.nilaikeluar,0) 
                    as nilaikeluar"),

                        DB::raw("0 as qtysaldo"),
                        DB::raw("0 as nilaisaldo"),
                        db::raw("a.modifiedby"),
                        db::raw("a.urutfifo as urutfifo"),
                        db::raw("c.tglinput as tglinput"),
                    )
                    ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id')
                    // ->leftjoin(db::raw($temprekapinput . " c "), 'a.nobukti', 'c.nobukti')
                    ->leftjoin(DB::raw($temprekapinput . " as c"), function ($join) {
                        $join->on('a.nobukti', '=', 'c.nobukti');
                        $join->on('b.id', '=', 'c.stok_id');
                    })
                    ->whereRaw("(a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                    ->whereRaw("(a.stok_id>=" . $stokdari . " and a.stok_id<=" . $stoksampai . ")")
                    ->whereRaw("(a.gandengan_id=" . $gandengan_id . ")")
                    ->orderby('a.tglbukti', 'asc')
                    ->orderby('c.tglinput', 'asc')
                    ->orderby('a.urutfifo', 'asc')
                    ->orderby('a.nobukti', 'asc')
                    ->orderby('a.id', 'asc');
            }
        } else {

            $queryinput = db::table('kartustok')->from(
                DB::raw("kartustok as a with (readuncommitted)")
            )
                ->select(
                    db::raw("a.nobukti as nobukti"),
                    db::raw("a.stok_id as nobukti"),
                    db::raw("'1900/1/1' as tglinput"),
                    db::raw("sum(A.qtymasuk) as qtymasuk"),
                    db::raw("sum(A.qtykeluar) as qtykeluar"),
                )
                ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id')
                ->whereRaw("(a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("(a.stok_id>=" . $stokdari . " and a.stok_id<=" . $stoksampai . ")")
                ->whereRaw("(a.gudang_id=" . $gudang_id . " or " . $gudang_id . "=0)")
                ->whereRaw("(a.gandengan_id=" . $gandengan_id . " or " . $gandengan_id . "=0)")
                ->whereRaw("(a.trado_id=" . $trado_id . " or " . $trado_id . "=0)")
                ->Groupby('a.nobukti')
                ->Groupby('a.stok_id');

            DB::table($temprekapinput)->insertUsing([
                'nobukti',
                'stok_id',
                'tglinput',
                'qtymasuk',
                'qtykeluar',

            ], $queryinput);



            DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinput=b.created_at from " . $temprekapinput . " a inner join penerimaanstokdetail b on a.nobukti=b.nobukti  and a.stok_id=b.stok_id"));
            DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinput=b.created_at from " . $temprekapinput . " a inner join pengeluaranstokdetail b on a.nobukti=b.nobukti  and a.stok_id=b.stok_id"));
            DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinputheader=b.created_at from " . $temprekapinput . " a inner join penerimaanstokheader b on a.nobukti=b.nobukti "));
            DB::update(DB::raw("UPDATE " . $temprekapinput . " SET tglinputheader=b.created_at from " . $temprekapinput . " a inner join pengeluaranstokheader b on a.nobukti=b.nobukti "));



            $queryrekap = db::table('kartustok')->from(
                DB::raw("kartustok as a with (readuncommitted)")
            )
                ->select(
                    'a.stok_id',
                    db::raw("isnull(a.gudang_id,0) as gudang_id"),
                    db::raw("isnull(a.trado_id,0) as trado_id"),
                    db::raw("isnull(a.gandengan_id,0) as gandengan_id"),
                    db::raw("(a.lokasi) as lokasi"),
                    db::raw("(a.kodebarang) as kodebarang"),
                    db::raw("(a.namabarang) as namabarang"),
                    db::raw("a.tglbukti as tglbukti"),
                    db::raw("a.nobukti as nobukti"),
                    db::raw("(a.kategori_id) as kategori_id"),
                    db::raw("isnull(a.qtymasuk,0) as qtymasuk"),
                    // db::raw("
                    // (case when isnull(b.statuspembulatanlebih2decimal,0)=1 then
                    // isnull(a.nilaimasuk,0)
                    // else
                    // round(isnull(a.nilaimasuk,0),2)
                    // end)
                    //  as nilaimasuk"),
                    // db::raw("
                    // round(round(cast(isnull(a.nilaimasuk,0) as money),3),2)
                    //  as nilaimasuk"),
                    db::raw("
                    isnull(a.nilaimasuk,0)
                     as nilaimasuk"),
                    db::raw("isnull(a.qtykeluar,0) as qtykeluar"),
                    // db::raw("
                    // (case when isnull(b.statuspembulatanlebih2decimal,0)=1 then
                    // isnull(a.nilaikeluar,0)
                    // else
                    // round(isnull(a.nilaikeluar,0),2)
                    // end)
                    // as nilaikeluar"),
                    // db::raw("
                    // round(round(cast(isnull(a.nilaikeluar,0) as money),3),2)
                    // as nilaikeluar"),
                    db::raw("
                   isnull(a.nilaikeluar,0) 
                   as nilaikeluar"),

                    DB::raw("0 as qtysaldo"),
                    DB::raw("0 as nilaisaldo"),
                    db::raw("a.modifiedby"),
                    db::raw("a.urutfifo as urutfifo"),
                    db::raw("c.tglinput as tglinput"),
                )
                ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id')
                // ->leftjoin(db::raw($temprekapinput . " c "), 'a.nobukti', 'c.nobukti')
                ->leftjoin(DB::raw($temprekapinput . " as c"), function ($join) {
                    $join->on('a.nobukti', '=', 'c.nobukti');
                    $join->on('b.id', '=', 'c.stok_id');
                })

                ->whereRaw("(a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("(a.stok_id>=" . $stokdari . " and a.stok_id<=" . $stoksampai . ")")
                ->whereRaw("(a.gudang_id=" . $gudang_id . " or " . $gudang_id . "=0)")
                ->whereRaw("(a.gandengan_id=" . $gandengan_id . " or " . $gandengan_id . "=0)")
                ->whereRaw("(a.trado_id=" . $trado_id . " or " . $trado_id . "=0)")
                ->orderby('a.tglbukti', 'asc')
                ->orderby('c.tglinput', 'asc')
                ->orderby('a.urutfifo', 'asc')
                ->orderby('a.nobukti', 'asc')
                ->orderby('a.id', 'asc');
        }

        // dd('test');
        // dd(db::table($temprekapinput)->get());

        // dd($queryrekap->get());





        DB::table($temprekap)->insertUsing([
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
            'tglinput',
        ], $queryrekap);

        // dd(db::table($temprekap)->get());



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
            $table->dateTime('tglinput')->nullable();
        });


        $queryrekapall =  db::table($temprekap)->from(
            DB::raw($temprekap . " as a ")
        )
            ->select(
                'a.stok_id',
                db::raw("isnull(a.gudang_id,0) as gudang_id"),
                db::raw("isnull(a.trado_id,0) as trado_id"),
                db::raw("isnull(a.gandengan_id,0) as gandengan_id"),
                db::raw("(a.lokasi) as lokasi"),
                db::raw("(a.kodebarang) as kodebarang"),
                db::raw("(a.namabarang) as namabarang"),
                db::raw("a.tglbukti as tglbukti"),
                db::raw("a.nobukti as nobukti"),
                db::raw("(a.kategori_id) as kategori_id"),
                db::raw("isnull(a.qtymasuk,0) as qtymasuk"),
                db::raw("round(isnull(a.nilaimasuk,0),2) as nilaimasuk"),
                db::raw("isnull(a.qtykeluar,0) as qtykeluar"),
                db::raw("round(isnull(a.nilaikeluar,0),2) as nilaikeluar"),
                DB::raw("sum ((isnull(a.qtysaldo,0)+a.qtymasuk)-a.qtykeluar) over (PARTITION BY isnull(a.stok_id,0),isnull(a.gudang_id,0),isnull(A.trado_id,0),isnull(A.gandengan_id,0) order by a.stok_id,isnull(a.gudang_id,0),isnull(A.trado_id,0),isnull(A.gandengan_id,0),isnull(a.tglbukti,0),a.tglinput,a.urutfifo,a.nobukti,a.id ASC) as qtysaldo"),
                DB::raw("casT(
                    sum(((isnull(a.nilaisaldo,0)+a.nilaimasuk)-a.nilaikeluar))  
                    over (PARTITION BY a.stok_id,isnull(a.gudang_id,0),isnull(A.trado_id,0),isnull(A.gandengan_id,0) order by isnull(a.stok_id,0),isnull(a.gudang_id,0),isnull(A.trado_id,0),isnull(A.gandengan_id,0),a.tglbukti,a.tglinput,a.urutfifo,a.nobukti,a.id ASC)  as money)as nilaisaldo"),
                // DB::raw("
                //         cast(left(format(cast(
                //         sum(((isnull(a.nilaisaldo,0)+a.nilaimasuk)-a.nilaikeluar))  
                //         over (PARTITION BY a.stok_id,isnull(a.gudang_id,0),isnull(A.trado_id,0),isnull(A.gandengan_id,0) order by isnull(a.stok_id,0),isnull(a.gudang_id,0),isnull(A.trado_id,0),isnull(A.gandengan_id,0),a.tglbukti,a.urutfifo,a.nobukti,a.id ASC) as float),'#0.000'),charindex('.',format(cast(
                //         sum(((isnull(a.nilaisaldo,0)+a.nilaimasuk)-a.nilaikeluar))  
                //         over (PARTITION BY a.stok_id,isnull(a.gudang_id,0),isnull(A.trado_id,0),isnull(A.gandengan_id,0) order by isnull(a.stok_id,0),isnull(a.gudang_id,0),isnull(A.trado_id,0),isnull(A.gandengan_id,0),a.tglbukti,a.urutfifo,a.nobukti,a.id ASC) as float),'#0.000'))-1)+
                //         substring(format(cast(
                //         sum(((isnull(a.nilaisaldo,0)+a.nilaimasuk)-a.nilaikeluar))  
                //         over (PARTITION BY a.stok_id,isnull(a.gudang_id,0),isnull(A.trado_id,0),isnull(A.gandengan_id,0) order by isnull(a.stok_id,0),isnull(a.gudang_id,0),isnull(A.trado_id,0),isnull(A.gandengan_id,0),a.tglbukti,a.urutfifo,a.nobukti,a.id ASC) as float),'#0.000'),charindex('.',format(cast(
                //         sum(((isnull(a.nilaisaldo,0)+a.nilaimasuk)-a.nilaikeluar))  
                //         over (PARTITION BY a.stok_id,isnull(a.gudang_id,0),isnull(A.trado_id,0),isnull(A.gandengan_id,0) order by isnull(a.stok_id,0),isnull(a.gudang_id,0),isnull(A.trado_id,0),isnull(A.gandengan_id,0),a.tglbukti,a.urutfifo,a.nobukti,a.id ASC) as float),'#0.000')),3)  
                //         as float)                        
                //         as nilaisaldo"),

                db::raw("a.modifiedby"),
                db::raw("a.urutfifo as urutfifo"),
                db::raw("a.tglinput as tglinput"),
            )
            // ->where('kodebarang','3021/04831105 SWL')
            //    ->whereraw("isnull(a.gudang_id,0)=0")
            // ->orderby('a.gudang_id', 'asc')
            // ->orderby('a.trado_id', 'asc')
            // ->orderby('a.gandengan_id', 'asc')
            ->orderby('a.tglbukti', 'asc')
            ->orderby('a.tglinput', 'asc')
            ->orderby('a.urutfifo', 'asc')
            ->orderby('a.nobukti', 'asc')
            ->orderby('a.id', 'asc');


        // dd( db::table($temprekap)->get() );

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
            'tglinput',
        ], $queryrekapall);


        // dd( db::table($temprekap)->where('kodebarang','3021/04831105 SWL')->get());
        // dd( db::table($temprekapall)->get());

        if ($stokgantung == true) {
            $tempgantungstok = '##tempgantungstok' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempgantungstok, function ($table) {
                $table->integer('stok_id')->nullable();
                $table->double('qty', 15, 2)->nullable();
                $table->double('nominal', 15, 2)->nullable();
            });

            // dd($gudang_id);
            $querygantungstok = db::table("kartustok")->from(db::raw("kartustok a "))
                ->select(
                    'a.stok_id',
                    'a.qtykeluar as qty',
                    'a.nilaikeluar as nominal',

                )
                ->whereRaw("left(a.nobukti,3)='GST'")
                ->whereRaw("(a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("(a.stok_id>=" . $stokdari . " and a.stok_id<=" . $stoksampai . ")")
                ->whereRaw("(a.gudang_id=" . $gudang_id . " or " . $gudang_id . "=0)")
                ->whereRaw("(a.gandengan_id=" . $gandengan_id . " or " . $gandengan_id . "=0)")
                ->whereRaw("(a.trado_id=" . $trado_id . " or " . $trado_id . "=0)");


            DB::table($tempgantungstok)->insertUsing([
                'stok_id',
                'qty',
                'nominal',
            ],  $querygantungstok);

            $querygantungstok = db::table("kartustok")->from(db::raw("kartustok a "))
                ->select(
                    'a.stok_id',
                    db::raw("a.qtymasuk*-1 as qty"),
                    db::raw("a.nilaimasuk*-1 as nominal"),

                )
                ->whereRaw("left(a.nobukti,3)='PST'")
                ->whereRaw("(a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("(a.stok_id>=" . $stokdari . " and a.stok_id<=" . $stoksampai . ")")
                ->whereRaw("(a.gudang_id=" . $gudang_id . " or " . $gudang_id . "=0)")
                ->whereRaw("(a.gandengan_id=" . $gandengan_id . " or " . $gandengan_id . "=0)")
                ->whereRaw("(a.trado_id=" . $trado_id . " or " . $trado_id . "=0)");

            DB::table($tempgantungstok)->insertUsing([
                'stok_id',
                'qty',
                'nominal',
            ],  $querygantungstok);


            $tempgantungstokrekap = '##tempgantungstokrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempgantungstokrekap, function ($table) {
                $table->integer('stok_id')->nullable();
                $table->double('qty', 15, 2)->nullable();
                $table->double('nominal', 15, 2)->nullable();
            });

            $queryrekapstokgantung = db::table($tempgantungstok)->from(db::raw($tempgantungstok . " a "))
                ->select(
                    'a.stok_id',
                    db::raw("sum(a.qty) as qty"),
                    db::raw("sum(a.nominal) as nominal"),
                )
                ->groupby('a.stok_id');
            DB::table($tempgantungstokrekap)->insertUsing([
                'stok_id',
                'qty',
                'nominal',
            ],  $queryrekapstokgantung);

            $tempgantungstokrekaplist = '##tempgantungstokrekaplist' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempgantungstokrekaplist, function ($table) {
                $table->integer('stok_id')->nullable();
                $table->double('qty', 15, 2)->nullable();
                $table->double('nominal', 15, 2)->nullable();
            });

            $queryrekapstokgantunglist = db::table($tempgantungstok)->from(db::raw($tempgantungstok . " a "))
                ->select(
                    'a.stok_id',
                    'a.qty',
                    'a.nominal',
                )
                ->whereraw("a.nominal>0");
            DB::table($tempgantungstokrekaplist)->insertUsing([
                'stok_id',
                'qty',
                'nominal',
            ],  $queryrekapstokgantunglist);

            // dd($queryrekapall->get());

            $queryrekapall = db::table($tempgantungstokrekaplist)->from(db::raw($tempgantungstokrekaplist . " a"))
                ->select(
                    'a.stok_id',
                    db::raw($gudang_id . " as gudang_id"),
                    db::raw($trado_id . " as trado_id"),
                    db::raw($gandengan_id . " as gandengan_id"),
                    db::raw("'SPAREPART GANTUNG' as lokasi"),
                    'b.namastok as kodebarang',
                    'b.namastok as namabarang',
                    db::raw("'" . $tglsampai . "' as tglbukti"),
                    db::raw("'SPAREPART GANTUNG' as nobukti"),
                    db::raw("b.kelompok_id as kategori_id"),
                    'a.qty as qtymasuk',
                    'a.nominal as nilaimasuk',
                    db::raw("0 as qtykeluar"),
                    db::raw("0 as nilaikeluar"),
                    'a.qty as qtysaldo',
                    'a.nominal as nilaisaldo',
                    db::raw("'' as modifiedby"),
                    db::raw("0 as urutfifo"),
                    db::raw("getdate() as tglinput"),
                )
                ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id');

            // dd($queryrekapall->get());

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
                'tglinput',
            ], $queryrekapall);
        }

        // dd(db::table($temprekapall)->whereraw("stok_id=4546")->get());

        $datalist = DB::table($temprekapall)->from(
            DB::raw($temprekapall . " as a")
        )
            ->select(
                db::raw("isnull(a.stok_id,0) as stok_id"),
                db::raw("isnull(a.gudang_id,0) as gudang_id"),
                db::raw("isnull(a.trado_id,0) as trado_id"),
                db::raw("isnull(a.gandengan_id,0) as gandengan_id"),
                'a.lokasi',
                'a.kodebarang',
                'a.namabarang',
                'a.tglbukti',
                'a.nobukti',
                db::raw("isnull(c.kodekelompok,'') as kategori_id"),
                'a.qtymasuk',
                'a.nilaimasuk',
                'a.qtykeluar',
                'a.nilaikeluar',
                'a.qtysaldo',
                db::raw("round(a.nilaisaldo,2) as nilaisaldo"),
                // 'a.nilaisaldo',
                'a.modifiedby',
                'a.urutfifo',
                'a.id as iddata',
                'a.tglinput',

                // 'a.created_at',
            )
            ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id')
            ->leftjoin(db::raw("kelompok c with (readuncommitted)"), 'b.kelompok_id', 'c.id')
            // ->whereraw("a.stok_id in(4546)")

            ->orderBy('a.id', 'asc');

        //   dd($datalist->get());
        // $queryrekap = db::table('kartustok')->from(
        //     DB::raw("kartustok as a with (readuncommitted)")
        // )
        //     ->select(
        //         db::raw("sum(round(isnull(a.nilaimasuk,0),2)) as nilaimasuk"),
        //         db::raw("sum(round(isnull(a.nilaikeluar,0),2)) as nilaikeluar"),
        //         db::raw("sum(round(isnull(a.nilaimasuk,0),2)-round(isnull(a.nilaikeluar,0),2)) as nilaisaldo"),

        //     )
        //     ->whereRaw("(a.stok_id=4316)")
        //     ->whereRaw("(a.tglbukti<='2023/10/23')");


        // dd($datalist->get());
        return $datalist;
    }

    public function getlaporanold($tgldari, $tglsampai, $stokdari, $stoksampai, $gudang_id, $trado_id, $gandengan_id, $filter)
    {

        // dump($tgldari);
        // dd($tglsampai);

        if ($stokdari == 0 || $stoksampai == 0) {
            $querystokdari = DB::table("stok")->from(
                DB::raw("stok a with (readuncommitted)")
            )
                ->select(
                    'a.id'
                )->orderBy('a.id', 'asc')
                ->first();

            $querystoksampai = DB::table("stok")->from(
                DB::raw("stok a with (readuncommitted)")
            )
                ->select(
                    'a.id'
                )->orderBy('a.id', 'desc')
                ->first();

            $stokdari = $querystokdari->id;
            $stoksampai = $querystoksampai->id;
        }

        $gudang_id = $gudang_id ?? 0;
        $trado_id = $trado_id ?? 0;
        $gandengan_id = $gandengan_id ?? 0;

        $lokasigudang = DB::table('gudang')->from(DB::raw("gudang with (readuncommitted)"))->select('gudang as lokasi')->where('id', $gudang_id)->first();
        $lokasitrado = DB::table('trado')->from(DB::raw("trado with (readuncommitted)"))->select('kodetrado as lokasi')->where('id', $trado_id)->first();
        $lokasigandengan = DB::table('gandengan')->from(DB::raw("gandengan with (readuncommitted)"))->select('kodegandengan as lokasi')->where('id', $gandengan_id)->first();
        if (isset($lokasigudang)) {
            $lokasi = $lokasigudang->lokasi;
        }
        if (isset($lokasitrado)) {
            $lokasi = $lokasitrado->lokasi;
        }
        if (isset($lokasigandengan)) {
            $lokasi = $lokasigandengan->lokasi;
        }
        // dd($lokasi);


        $filtergudang = Parameter::where('grp', 'STOK PERSEDIAAN')->where('subgrp', 'STOK PERSEDIAAN')->where('text', 'GUDANG')->first();
        $filtertrado = Parameter::where('grp', 'STOK PERSEDIAAN')->where('subgrp', 'STOK PERSEDIAAN')->where('text', 'TRADO')->first();
        $filtergandengan = Parameter::where('grp', 'STOK PERSEDIAAN')->where('subgrp', 'STOK PERSEDIAAN')->where('text', 'GANDENGAN')->first();


        $templaporan = '##templaporan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        Schema::create($templaporan, function ($table) {
            $table->id();
            $table->unsignedBigInteger('kodebarang')->nullable();
            $table->string('namabarang', 1000)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->unsignedBigInteger('kategori_id')->nullable();
            $table->double('qtymasuk', 15, 2)->nullable();
            $table->double('nilaimasuk', 15, 2)->nullable();
            $table->double('qtykeluar', 15, 2)->nullable();
            $table->double('nilaikeluar', 15, 2)->nullable();
            $table->double('qtysaldo', 15, 2)->nullable();
            $table->double('nilaisaldo', 15, 2)->nullable();
            $table->string('modifiedby', 100)->nullable();
            $table->integer('urutfifo')->length(11)->nullable();
            $table->dateTime('created_at')->nullable();



            $table->index('kodebarang', 'templaporan_kodebarang_index');
            $table->index('kategori_id', 'templaporan_kategori_id_index');
            $table->index('namabarang', 'templaporan_namabarang_index');
            $table->index('nobukti', 'templaporan_nobukti_index');
        });



        $temprekap = '##temprekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekap, function ($table) {
            $table->id();
            $table->integer('statusmasuk')->length(11)->nullable();;
            $table->unsignedBigInteger('kodebarang')->nullable();
            $table->string('namabarang', 1000)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->unsignedBigInteger('kategori_id')->nullable();
            $table->double('qtymasuk', 15, 2)->nullable();
            $table->double('nilaimasuk', 15, 2)->nullable();
            $table->double('qtykeluar', 15, 2)->nullable();
            $table->double('nilaikeluar', 15, 2)->nullable();
            $table->double('qtysaldo', 15, 2)->nullable();
            $table->double('nilaisaldo', 15, 2)->nullable();
            $table->string('modifiedby', 100)->nullable();
            $table->integer('urutfifo')->length(11)->nullable();
            $table->dateTime('created_at')->nullable();


            $table->index('statusmasuk', 'temprekap_statusmasuk_index');
            $table->index('kodebarang', 'temprekap_kodebarang_index');
            $table->index('kategori_id', 'temprekap_kategori_id_index');
            $table->index('namabarang', 'temprekap_namabarang_index');
            $table->index('nobukti', 'temprekap_nobukti_index');
        });


        $tempsaldoawalmasuk = '##tempsaldoawalmasuk' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldoawalmasuk, function ($table) {
            $table->id();
            $table->unsignedBigInteger('kodebarang')->nullable();
            $table->double('qtymasuk', 15, 2)->nullable();
            $table->double('nilaimasuk', 15, 2)->nullable();

            $table->index('kodebarang', 'tempsaldoawalmasuk_kodebarang_index');
        });

        $tempsaldoawalkeluar = '##tempsaldoawalkeluar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldoawalkeluar, function ($table) {
            $table->id();
            $table->unsignedBigInteger('kodebarang')->nullable();
            $table->double('qtykeluar', 15, 2)->nullable();
            $table->double('nilaikeluar', 15, 2)->nullable();

            $table->index('kodebarang', 'tempsaldoawalkeluar_kodebarang_index');
        });

        $tempsaldoawal = '##tempsaldoawal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldoawal, function ($table) {
            $table->id();
            $table->unsignedBigInteger('kodebarang')->nullable();
            $table->double('qtysaldo', 15, 2)->nullable();
            $table->double('nilaisaldo', 15, 2)->nullable();

            $table->index('kodebarang', 'tempsaldoawal_kodebarang_index');
        });

        $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
        $gudangsementara = Parameter::where('grp', 'GUDANG SEMENTARA')->where('subgrp', 'GUDANG SEMENTARA')->first();
        $gudangpihak3 = Parameter::where('grp', 'GUDANG PIHAK3')->where('subgrp', 'GUDANG PIHAK3')->first();
        $workshop = Parameter::where('grp', 'WORK SHOP')->where('subgrp', 'WORK SHOP')->first();

        // and $gudang_id = $gudangkantor->text)

        $spb = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();


        $korplus = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', 'KOR STOK')->where('subgrp', 'KOR STOK')->first();


        $saldoawal = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', 'SALDO AWAL STOK')->where('subgrp', 'SALDO AWAL STOK')->first();

        $pg = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', 'PG STOK')->where('subgrp', 'PG STOK')->first();

        $pgdo = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', 'DO STOK')->where('subgrp', 'DO STOK')->first();

        $spbs = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', 'REUSE STOK')->where('subgrp', 'REUSE STOK')->first();

        $pst = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', 'PST STOK')->where('subgrp', 'PST STOK')->first();

        $pspk = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', 'PSPK STOK')->where('subgrp', 'PSPK STOK')->first();


        $spk = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();

        $retur = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', 'RETUR STOK')->where('subgrp', 'RETUR STOK')->first();

        $korminus = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', 'KOR MINUS STOK')->where('subgrp', 'KOR MINUS STOK')->first();

        $gst = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', 'GST STOK')->where('subgrp', 'GST STOK')->first();


        // dump($filter);
        // dump($filtergudang->text);
        // dump($gudang_id);
        // dd($gudangkantor->text);
        if ($filter == $filtergudang->text) {
            //=========================================saldo awal masuk=========================================
            if ($gudang_id == $gudangkantor->text) {
                $penerimaanstok_id = $spb->text . ',' . $saldoawal->text . ',' . $korplus->text . ',' .  $spbs->text . ',' .  $pst->text . ',' .  $pspk->text . ',' .  $pg->text;
                $pengeluaranstok_id = $spk->text . ',' . $korminus->text . ',' . $retur->text . ',' . $gst->text;
            } else if ($gudang_id == $gudangsementara->text) {
                $penerimaanstok_id = $pg->text . ',' . $pgdo->text . ',' . $spbs->text;
                $pengeluaranstok_id = $korminus->text;
            } else if ($gudang_id == $gudangpihak3->text) {
                $penerimaanstok_id = $pg->text . ',' . $pgdo->text . ',' . $spbs->text;
                $pengeluaranstok_id = $korminus->text;
            } else if ($gudang_id == $workshop->text) {
                $penerimaanstok_id = $pg->text . ',' . $pgdo->text . ',' . $spbs->text;
                $pengeluaranstok_id = $korminus->text;
            } else {
                $penerimaanstok_id = $spb->text . ',' . $saldoawal->text . ',' . $korplus->text . ',' .  $pst->text . ',' .  $pspk->text;
                $pengeluaranstok_id = $spk->text . ',' . $korminus->text . ',' . $retur->text . ',' . $gst->text;
            }
        } else if ($filter == $filtertrado->text) {
            $penerimaanstok_id =  $pg->text . ',' . $pgdo->text . ',' . $spbs->text . ',' . $saldoawal->text . ',' . $korplus->text;
            $pengeluaranstok_id = $korminus->text;
        } else if ($filter == $filtergandengan->text) {
            $penerimaanstok_id =  $pg->text . ',' . $pgdo->text . ',' . $spbs->text . ',' . $saldoawal->text . ',' . $korplus->text;
            $pengeluaranstok_id = $korminus->text;
        } else {
            if ($gudang_id == $gudangkantor->text) {
                $penerimaanstok_id = $spb->text . ',' . $saldoawal->text . ',' . $korplus->text . ',' .  $pst->text . ',' .  $pspk->text;;
                $pengeluaranstok_id = $spk->text . ',' . $korminus->text . ',' . $retur->text . ',' . $gst->text;
            } else if ($gudang_id == $gudangsementara->text) {
                $penerimaanstok_id = $pg->text . ',' . $pgdo->text . ',' . $spbs->text;
                $pengeluaranstok_id = $korminus->text;
            } else if ($gudang_id == $gudangpihak3->text) {
                $penerimaanstok_id = $pg->text . ',' . $pgdo->text . ',' . $spbs->text;
                $pengeluaranstok_id = $korminus->text;
            } else if ($gudang_id == $workshop->text) {
                $penerimaanstok_id = $pg->text . ',' . $pgdo->text . ',' . $spbs->text;
                $pengeluaranstok_id = $korminus->text;
            }
        }


        if ($gudang_id != 0) {

            $querysaldomasuk = PenerimaanstokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    'c.id as kodebarang',
                    DB::raw("sum(b.qty) as qtymasuk"),
                    DB::raw("sum(b.qty*b.harga) as nilaimasuk"),
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti <'" . $tgldari . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id . ")")
                ->whereRaw("(a.gudang_id=" . $gudang_id)
                ->OrwhereRaw("a.gudangke_id=" . $gudang_id . ")")
                ->groupBy('c.id');
        } else if ($trado_id != 0) {
            $querysaldomasuk = PenerimaanstokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    'c.id as kodebarang',
                    DB::raw("sum(b.qty) as qtymasuk"),
                    DB::raw("sum(b.qty*b.harga) as nilaimasuk"),
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti <'" . $tgldari . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id . ")")
                ->whereRaw("(a.trado_id=" . $trado_id)
                ->OrwhereRaw("a.tradoke_id=" . $trado_id . ")")
                ->groupBy('c.id');
        } else if ($gandengan_id != 0) {
            $querysaldomasuk = PenerimaanstokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    'c.id as kodebarang',
                    DB::raw("sum(b.qty) as qtymasuk"),
                    DB::raw("sum(b.qty*b.harga) as nilaimasuk"),
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti <'" . $tgldari . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id . ")")
                ->whereRaw("(a.gandengan_id=" . $gandengan_id)
                ->OrwhereRaw("a.gandenganke_id=" . $gandengan_id . ")")
                ->groupBy('c.id');
        } else {

            $querysaldomasuk = PenerimaanstokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    'c.id as kodebarang',
                    DB::raw("sum(b.qty) as qtymasuk"),
                    DB::raw("sum(b.qty*b.harga) as nilaimasuk"),
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti <'" . $tgldari . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id . ")")
                ->whereRaw("(a.gudang_id=" . $gudang_id)
                ->OrwhereRaw("a.gudangke_id=" . $gudang_id . ")")
                ->groupBy('c.id');
        }



        // dd($stokdari);
        //  dd($querysaldomasuk->get());




        DB::table($tempsaldoawalmasuk)->insertUsing([
            'kodebarang',
            'qtymasuk',
            'nilaimasuk',
        ], $querysaldomasuk);



        $statusreuse = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', 'STATUS REUSE')->where('subgrp', 'STATUS REUSE')->where('text', 'REUSE')->first();



        $pengeluaranstok_id2 = $spk->text . ',' . $gst->text;
        if ($trado_id != 0) {
            $querysaldomasuk = PengeluaranstokHeader::from(
                DB::raw("pengeluaranstokheader as a with (readuncommitted)")
            )
                ->select(
                    'c.id as kodebarang',
                    DB::raw("sum(b.penerimaanstok_qty) as qtymasuk"),
                    // DB::raw("0 as qtymasuk"),
                    DB::raw("0 as nilaimasuk"),
                    // DB::raw("sum(b.penerimaanstok_qty*b.penerimaanstok_harga) as nilaimasuk"),
                )
                ->join(DB::raw("pengeluaranstokdetailfifo as b with (readuncommitted)"), 'a.id', 'b.pengeluaranstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti <'" . $tgldari . "')")
                ->whereRaw("a.pengeluaranstok_id in(" . $pengeluaranstok_id2 . ")")
                ->whereRaw("a.trado_id in(" . $trado_id . ")")
                ->whereRaw("c.statusreuse in(" . $statusreuse->id . ")")
                ->groupBy('c.id');

            DB::table($tempsaldoawalmasuk)->insertUsing([
                'kodebarang',
                'qtymasuk',
                'nilaimasuk',
            ], $querysaldomasuk);
        } else   if ($gandengan_id != 0) {
            $querysaldomasuk = PengeluaranstokHeader::from(
                DB::raw("pengeluaranstokheader as a with (readuncommitted)")
            )
                ->select(
                    'c.id as kodebarang',
                    DB::raw("sum(b.penerimaanstok_qty) as qtymasuk"),
                    // DB::raw("0 as qtymasuk"),
                    DB::raw("0 as nilaimasuk"),
                    // DB::raw("sum(b.penerimaanstok_qty) as qtymasuk"),
                    // DB::raw("sum(b.penerimaanstok_qty*b.penerimaanstok_harga) as nilaimasuk"),
                )
                ->join(DB::raw("pengeluaranstokdetailfifo as b with (readuncommitted)"), 'a.id', 'b.pengeluaranstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')

                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti <'" . $tgldari . "')")
                ->whereRaw("a.pengeluaranstok_id in(" . $pengeluaranstok_id2 . ")")
                ->whereRaw("a.trado_id in(" . $trado_id . ")")
                ->whereRaw("c.statusreuse in(" . $statusreuse->id . ")")
                ->groupBy('c.id');

            DB::table($tempsaldoawalmasuk)->insertUsing([
                'kodebarang',
                'qtymasuk',
                'nilaimasuk',
            ], $querysaldomasuk);
        }








        //=========================================query rekap data masuk=========================================
        if ($gudang_id != 0) {
            $queryrekap = PenerimaanStokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    DB::raw("1 as statusmasuk"),
                    'c.id as kodebarang',
                    'c.namastok as namabarang',
                    'a.tglbukti as tglbukti',
                    'a.nobukti as nobukti',
                    'c.kategori_id',
                    'b.qty as qtymasuk',
                    DB::raw("(b.qty*b.harga) as nilaimasuk"),
                    DB::raw("0 as qtykeluar"),
                    DB::raw("0 as nilaikeluar"),
                    DB::raw("0 as qtysaldo"),
                    DB::raw("0 as nilaisaldo"),
                    'a.modifiedby',
                    DB::raw("isnull(d.urutfifo,0) as urutfifo"),
                    'a.created_at',
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->leftjoin(DB::raw("penerimaanstok as d with (readuncommitted)"), 'a.penerimaanstok_id', 'd.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id  . ")")
                ->whereRaw("(a.gudang_id=" . $gudang_id)
                ->OrwhereRaw("a.gudangke_id=" . $gudang_id . ")")
                ->orderBy('a.id', 'Asc')
                ->orderBy('a.tglbukti', 'Asc')
                ->orderBy('a.nobukti', 'Asc')
                ->orderBy('b.id', 'Asc');
        } else if ($gandengan_id != 0) {
            $queryrekap = PenerimaanStokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    DB::raw("1 as statusmasuk"),
                    'c.id as kodebarang',
                    'c.namastok as namabarang',
                    'a.tglbukti as tglbukti',
                    'a.nobukti as nobukti',
                    'c.kategori_id',
                    'b.qty as qtymasuk',
                    DB::raw("(b.qty*b.harga) as nilaimasuk"),
                    DB::raw("0 as qtykeluar"),
                    DB::raw("0 as nilaikeluar"),
                    DB::raw("0 as qtysaldo"),
                    DB::raw("0 as nilaisaldo"),
                    'a.modifiedby',
                    DB::raw("isnull(d.urutfifo,0) as urutfifo"),
                    'a.created_at',
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->leftjoin(DB::raw("penerimaanstok as d with (readuncommitted)"), 'a.penerimaanstok_id', 'd.id')

                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id  . ")")
                ->whereRaw("(a.gandengan_id=" . $gandengan_id)
                ->OrwhereRaw("a.gandenganke_id=" . $gandengan_id . ")")
                ->orderBy('a.id', 'Asc')
                ->orderBy('a.tglbukti', 'Asc')
                ->orderBy('a.nobukti', 'Asc')
                ->orderBy('b.id', 'Asc');
        } else if ($trado_id != 0) {
            $queryrekap = PenerimaanStokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    DB::raw("1 as statusmasuk"),
                    'c.id as kodebarang',
                    'c.namastok as namabarang',
                    'a.tglbukti as tglbukti',
                    'a.nobukti as nobukti',
                    'c.kategori_id',
                    'b.qty as qtymasuk',
                    DB::raw("(b.qty*b.harga) as nilaimasuk"),
                    DB::raw("0 as qtykeluar"),
                    DB::raw("0 as nilaikeluar"),
                    DB::raw("0 as qtysaldo"),
                    DB::raw("0 as nilaisaldo"),
                    'a.modifiedby',
                    DB::raw("isnull(d.urutfifo,0) as urutfifo"),
                    'a.created_at',
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->leftjoin(DB::raw("penerimaanstok as d with (readuncommitted)"), 'a.penerimaanstok_id', 'd.id')

                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id  . ")")
                ->whereRaw("(a.trado_id=" . $trado_id)
                ->OrwhereRaw("a.tradoke_id=" . $trado_id . ")")
                ->orderBy('a.id', 'Asc')
                ->orderBy('a.tglbukti', 'Asc')
                ->orderBy('a.nobukti', 'Asc')
                ->orderBy('b.id', 'Asc');
        } else {
            $queryrekap = PenerimaanStokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    DB::raw("1 as statusmasuk"),
                    'c.id as kodebarang',
                    'c.namastok as namabarang',
                    'a.tglbukti as tglbukti',
                    'a.nobukti as nobukti',
                    'c.kategori_id',
                    'b.qty as qtymasuk',
                    DB::raw("(b.qty*b.harga) as nilaimasuk"),
                    DB::raw("0 as qtykeluar"),
                    DB::raw("0 as nilaikeluar"),
                    DB::raw("0 as qtysaldo"),
                    DB::raw("0 as nilaisaldo"),
                    'a.modifiedby',
                    DB::raw("isnull(d.urutfifo,0) as urutfifo"),
                    'a.created_at',
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->leftjoin(DB::raw("penerimaanstok as d with (readuncommitted)"), 'a.penerimaanstok_id', 'd.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id  . ")")
                ->whereRaw("(a.gudang_id=" . $gudang_id)
                ->OrwhereRaw("a.gudangke_id=" . $gudang_id . ")")
                ->orderBy('a.id', 'Asc')
                ->orderBy('a.tglbukti', 'Asc')
                ->orderBy('a.nobukti', 'Asc')
                ->orderBy('b.id', 'Asc');
        }


        DB::table($temprekap)->insertUsing([
            'statusmasuk',
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
            'created_at',
        ], $queryrekap);


        if ($trado_id != 0) {
            $queryrekap = PengeluaranStokHeader::from(
                DB::raw("pengeluaranstokheader as a with (readuncommitted)")
            )
                ->select(
                    DB::raw("2 as statusmasuk"),
                    'c.id as kodebarang',
                    'c.namastok as namabarang',
                    'a.tglbukti as tglbukti',
                    'a.nobukti as nobukti',
                    'c.kategori_id',
                    DB::raw("b.penerimaanstok_qty as qtymasuk"),
                    // DB::raw("(b.penerimaanstok_qty*b.penerimaanstok_harga) as nilaimasuk"),
                    DB::raw("0 as nilaimasuk"),
                    DB::raw("0 as qtykeluar"),
                    DB::raw("0 as nilaikeluar"),
                    DB::raw("0 as qtysaldo"),
                    DB::raw("0 as nilaisaldo"),
                    'a.modifiedby',
                    DB::raw("isnull(d.urutfifo,0) as urutfifo"),
                    'a.created_at',

                )
                ->join(DB::raw("pengeluaranstokdetailfifo as b with (readuncommitted)"), 'a.id', 'b.pengeluaranstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->leftjoin(DB::raw("pengeluaranstok as d with (readuncommitted)"), 'a.pengeluaranstok_id', 'd.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("a.pengeluaranstok_id in(" . $pengeluaranstok_id2 . ")")
                ->whereRaw("c.statusreuse in(" . $statusreuse->id . ")")
                ->whereRaw("a.trado_id in(" . $trado_id . ")")
                ->orderBy('a.id', 'Asc')
                ->orderBy('a.tglbukti', 'Asc')
                ->orderBy('a.nobukti', 'Asc')
                ->orderBy('b.id', 'Asc');


            DB::table($temprekap)->insertUsing([
                'statusmasuk',
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
                'created_at',
            ], $queryrekap);
        } else  if ($gandengan_id != 0) {
            $queryrekap = PengeluaranStokHeader::from(
                DB::raw("pengeluaranstokheader as a with (readuncommitted)")
            )
                ->select(
                    DB::raw("2 as statusmasuk"),
                    'c.id as kodebarang',
                    'c.namastok as namabarang',
                    'a.tglbukti as tglbukti',
                    'a.nobukti as nobukti',
                    'c.kategori_id',
                    DB::raw("b.penerimaanstok_qty as qtymasuk"),
                    // DB::raw("(b.penerimaanstok_qty*b.penerimaanstok_harga) as nilaimasuk"),
                    DB::raw("0 as nilaimasuk"),
                    DB::raw("0 as qtykeluar"),
                    DB::raw("0 as nilaikeluar"),
                    DB::raw("0 as qtysaldo"),
                    DB::raw("0 as nilaisaldo"),
                    'a.modifiedby',
                    DB::raw("isnull(d.urutfifo,0) as urutfifo"),
                    'a.created_at',

                )
                ->join(DB::raw("pengeluaranstokdetailfifo as b with (readuncommitted)"), 'a.id', 'b.pengeluaranstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->leftjoin(DB::raw("pengeluaranstok as d with (readuncommitted)"), 'a.pengeluaranstok_id', 'd.id')

                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("a.pengeluaranstok_id in(" . $pengeluaranstok_id2 . ")")
                ->whereRaw("c.statusreuse in(" . $statusreuse->id . ")")
                ->whereRaw("a.gandengan_id in(" . $gandengan_id . ")")
                ->orderBy('a.id', 'Asc')
                ->orderBy('a.tglbukti', 'Asc')
                ->orderBy('a.nobukti', 'Asc')
                ->orderBy('b.id', 'Asc');


            DB::table($temprekap)->insertUsing([
                'statusmasuk',
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
                'created_at',
            ], $queryrekap);
        }




        //=========================================saldo awal keluar=========================================

        $spk = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();

        $querysaldokeluar = PengeluaranstokHeader::from(
            DB::raw("pengeluaranstokheader as a with (readuncommitted)")
        )
            ->select(
                'c.id as kodebarang',
                DB::raw("sum(b.penerimaanstok_qty) as qtykeluar"),
                DB::raw("sum(b.penerimaanstok_qty*b.penerimaanstok_harga) as nilaikeluar"),
            )
            ->join(DB::raw("pengeluaranstokdetailfifo as b with (readuncommitted)"), 'a.id', 'b.pengeluaranstokheader_id')
            ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
            ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti <'" . $tgldari . "')")
            ->whereRaw("a.pengeluaranstok_id in(" . $pengeluaranstok_id . ")")
            ->groupBy('c.id');

        DB::table($tempsaldoawalkeluar)->insertUsing([
            'kodebarang',
            'qtykeluar',
            'nilaikeluar',
        ], $querysaldokeluar);

        if ($gudang_id != 0) {
            $querysaldokeluar = PenerimaanstokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    'c.id as kodebarang',
                    DB::raw("sum(b.qty) as qtykeluar"),
                    DB::raw("sum(b.qty*b.harga) as nilaikeluar"),
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti <'" . $tgldari . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id . ")")
                ->where('a.gudangdari_id', $gudang_id)
                ->groupBy('c.id');
        } else if ($trado_id != 0) {
            $querysaldokeluar = PenerimaanstokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    'c.id as kodebarang',
                    DB::raw("sum(b.qty) as qtykeluar"),
                    DB::raw("sum(b.qty*b.harga) as nilaikeluar"),
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti <'" . $tgldari . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id . ")")
                ->where('a.tradodari_id', $trado_id)
                ->groupBy('c.id');
        } else if ($gandengan_id != 0) {
            $querysaldokeluar = PenerimaanstokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    'c.id as kodebarang',
                    DB::raw("sum(b.qty) as qtykeluar"),
                    DB::raw("sum(b.qty*b.harga) as nilaikeluar"),
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti <'" . $tgldari . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id . ")")
                ->where('a.gandengandari_id', $gandengan_id)
                ->groupBy('c.id');
        } else {
            $querysaldokeluar = PenerimaanstokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    'c.id as kodebarang',
                    DB::raw("sum(b.qty) as qtykeluar"),
                    DB::raw("sum(b.qty*b.harga) as nilaikeluar"),
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti <'" . $tgldari . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id . ")")
                ->where('a.gudangdari_id', $gudang_id)
                ->groupBy('c.id');
        }

        // dd($querysaldokeluar->get());

        DB::table($tempsaldoawalkeluar)->insertUsing([
            'kodebarang',
            'qtykeluar',
            'nilaikeluar',
        ], $querysaldokeluar);


        //=========================================saldo awal=========================================
        //masuk - keluar
        $querysaldo = Stok::from(
            DB::raw("stok as a with (readuncommitted)")
        )
            ->select(
                'a.id as kodebarang',
                DB::raw("(isnull(b.qtymasuk,0)-isnull(c.qtykeluar,0)) as qtysaldo"),
                DB::raw("(isnull(b.nilaimasuk,0)-isnull(c.nilaikeluar,0)) as nilaisaldo"),
            )
            ->leftjoin(DB::raw($tempsaldoawalmasuk . " as b"), 'a.id', 'b.kodebarang')
            ->leftjoin(DB::raw($tempsaldoawalkeluar . " as c"), 'a.id', 'c.kodebarang')
            ->whereRaw("(a.id>=" . $stokdari . " and a.id<=" . $stoksampai . " ) ");


        DB::table($tempsaldoawal)->insertUsing([
            'kodebarang',
            'qtysaldo',
            'nilaisaldo',
        ], $querysaldo);

        $queryrekap = DB::table($tempsaldoawal)->from(
            DB::raw($tempsaldoawal . " as A")
        )
            ->select(
                DB::raw("0 as statusmasuk"),
                'c.id as kodebarang',
                DB::raw("c.namastok as namabarang"),
                DB::raw("'" . $tgldari . "' as tglbukti"),
                DB::raw("'Saldo Awal' as nobukti"),
                'c.kategori_id',
                DB::raw("a.qtysaldo as qtymasuk"),
                DB::raw("a.nilaisaldo as nilaimasuk"),
                DB::raw("0 as qtykeluar"),
                DB::raw("0 as nilaikeluar"),
                DB::raw("0 as qtysaldo"),
                DB::raw("0 as nilaisaldo"),
                DB::raw("'' as modifiedby"),
                DB::raw("0 as urutfifo"),
                DB::raw("'1900/1/1' as created_at"),


            )
            ->join(DB::raw("stok as c with (readuncommitted)"), 'a.kodebarang', 'c.id');
        //saldo awal



        DB::table($temprekap)->insertUsing([
            'statusmasuk',
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
            'created_at',
        ], $queryrekap);

        // dd('test');

        $queryrekap = PengeluaranStokHeader::from(
            DB::raw("pengeluaranstokheader as a with (readuncommitted)")
        )
            ->select(
                DB::raw("2 as statusmasuk"),
                'c.id as kodebarang',
                'c.namastok as namabarang',
                'a.tglbukti as tglbukti',
                'a.nobukti as nobukti',
                'c.kategori_id',
                DB::raw("0 as qtymasuk"),
                DB::raw("0 as nilaimasuk"),
                DB::raw("b.penerimaanstok_qty as qtykeluar"),
                DB::raw("(b.penerimaanstok_qty*b.penerimaanstok_harga) as nilaikeluar"),
                DB::raw("0 as qtysaldo"),
                DB::raw("0 as nilaisaldo"),
                'a.modifiedby',
                DB::raw("isnull(d.urutfifo,0) as urutfifo"),
                'a.created_at',
            )
            ->join(DB::raw("pengeluaranstokdetailfifo as b with (readuncommitted)"), 'a.id', 'b.pengeluaranstokheader_id')
            ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
            ->leftjoin(DB::raw("pengeluaranstok as d with (readuncommitted)"), 'a.pengeluaranstok_id', 'd.id')

            ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
            ->whereRaw("a.pengeluaranstok_id in(" . $pengeluaranstok_id . ")")
            ->orderBy('a.id', 'Asc')
            ->orderBy('a.tglbukti', 'Asc')
            ->orderBy('a.nobukti', 'Asc')
            ->orderBy('b.id', 'Asc');


        DB::table($temprekap)->insertUsing([
            'statusmasuk',
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
            'created_at',
        ], $queryrekap);

        if ($gudang_id != 0) {
            $queryrekap = PenerimaanStokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    DB::raw("2 as statusmasuk"),
                    'c.id as kodebarang',
                    'c.namastok as namabarang',
                    'a.tglbukti as tglbukti',
                    'a.nobukti as nobukti',
                    'c.kategori_id',
                    DB::raw("0 as qtymasuk"),
                    DB::raw("0 as nilaimasuk"),
                    'b.qty as qtykeluar',
                    DB::raw("(b.qty*b.harga) as nilaikeluar"),
                    DB::raw("0 as qtysaldo"),
                    DB::raw("0 as nilaisaldo"),
                    'a.modifiedby',
                    DB::raw("isnull(d.urutfifo,0) as urutfifo"),
                    'a.created_at',
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->leftjoin(DB::raw("penerimaanstok as d with (readuncommitted)"), 'a.penerimaanstok_id', 'd.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id  . ")")
                ->where('a.gudangdari_id', $gudang_id)
                ->orderBy('a.id', 'Asc')
                ->orderBy('a.tglbukti', 'Asc')
                ->orderBy('a.nobukti', 'Asc')
                ->orderBy('b.id', 'Asc');
        } else if ($gandengan_id != 0) {
            $queryrekap = PenerimaanStokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    DB::raw("2 as statusmasuk"),
                    'c.id as kodebarang',
                    'c.namastok as namabarang',
                    'a.tglbukti as tglbukti',
                    'a.nobukti as nobukti',
                    'c.kategori_id',
                    DB::raw("0 as qtymasuk"),
                    DB::raw("0 as nilaimasuk"),
                    'b.qty as qtykeluar',
                    DB::raw("(b.qty*b.harga) as nilaikeluar"),
                    DB::raw("0 as qtysaldo"),
                    DB::raw("0 as nilaisaldo"),
                    'a.modifiedby',
                    DB::raw("isnull(d.urutfifo,0) as urutfifo"),
                    'a.created_at',
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->leftjoin(DB::raw("penerimaanstok as d with (readuncommitted)"), 'a.penerimaanstok_id', 'd.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id  . ")")
                ->where('a.gandengandari_id', $gandengan_id)
                ->orderBy('a.id', 'Asc')
                ->orderBy('a.tglbukti', 'Asc')
                ->orderBy('a.nobukti', 'Asc')
                ->orderBy('b.id', 'Asc');
        } else if ($trado_id != 0) {
            $queryrekap = PenerimaanStokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    DB::raw("2 as statusmasuk"),
                    'c.id as kodebarang',
                    'c.namastok as namabarang',
                    'a.tglbukti as tglbukti',
                    'a.nobukti as nobukti',
                    'c.kategori_id',
                    DB::raw("0 as qtymasuk"),
                    DB::raw("0 as nilaimasuk"),
                    'b.qty as qtykeluar',
                    DB::raw("(b.qty*b.harga) as nilaikeluar"),
                    DB::raw("0 as qtysaldo"),
                    DB::raw("0 as nilaisaldo"),
                    'a.modifiedby',
                    DB::raw("isnull(d.urutfifo,0) as urutfifo"),
                    'a.created_at',
                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->leftjoin(DB::raw("penerimaanstok as d with (readuncommitted)"), 'a.penerimaanstok_id', 'd.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id  . ")")
                ->where('a.tradodari_id', $trado_id)
                ->orderBy('a.id', 'Asc')
                ->orderBy('a.tglbukti', 'Asc')
                ->orderBy('a.nobukti', 'Asc')
                ->orderBy('b.id', 'Asc');
        } else {
            $queryrekap = PenerimaanStokHeader::from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
                ->select(
                    DB::raw("2 as statusmasuk"),
                    'c.id as kodebarang',
                    'c.namastok as namabarang',
                    'a.tglbukti as tglbukti',
                    'a.nobukti as nobukti',
                    'c.kategori_id',
                    DB::raw("0 as qtymasuk"),
                    DB::raw("0 as nilaimasuk"),
                    'b.qty as qtykeluar',
                    DB::raw("(b.qty*b.harga) as nilaikeluar"),
                    DB::raw("0 as qtysaldo"),
                    DB::raw("0 as nilaisaldo"),
                    'a.modifiedby',
                    DB::raw("isnull(d.urutfifo,0) as urutfifo"),
                    'a.created_at',

                )
                ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.id', 'b.penerimaanstokheader_id')
                ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->leftjoin(DB::raw("penerimaanstok as d with (readuncommitted)"), 'a.penerimaanstok_id', 'd.id')
                ->whereRaw("(b.stok_id>=" . $stokdari . " and B.stok_id<=" . $stoksampai . " )  and (a.tglBukti >='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereRaw("a.penerimaanstok_id in(" . $penerimaanstok_id  . ")")
                ->orderBy('a.id', 'Asc')
                ->where('a.gudangdari_id', $gudang_id)
                ->orderBy('a.tglbukti', 'Asc')
                ->orderBy('a.nobukti', 'Asc')
                ->orderBy('b.id', 'Asc');
        }

        DB::table($temprekap)->insertUsing([
            'statusmasuk',
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
            'created_at',
        ], $queryrekap);
        //akhir if gudang sebelumnya 
        // }

        $querylaporan = DB::table($temprekap)->from(
            DB::raw($temprekap . " as A")
        )
            ->select(
                'A.kodebarang',
                'A.namabarang',
                'A.tglbukti',
                'A.nobukti',
                'A.kategori_id',
                'A.qtymasuk',
                'A.nilaimasuk',
                'A.qtykeluar',
                'A.nilaikeluar',
                'A.qtysaldo',
                'A.nilaisaldo',
                'A.modifiedby',
                'A.urutfifo',
                'A.created_at',

            )
            ->orderBy('A.statusmasuk', 'Asc')
            ->orderBy('A.id', 'Asc');

        DB::table($templaporan)->insertUsing([
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
            'created_at',
        ], $querylaporan);

        $temprekapall = '##temprkpall' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
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
            $table->integer('urutfifo')->nullable();
            $table->dateTime('created_at')->nullable();

            $table->index('kodebarang', 'temprekapall_kodebarang_index');
            $table->index('namabarang', 'temprekapall_namabarang_index');
            $table->index('nobukti', 'temprekapall_nobukti_index');
            $table->index('kategori_id', 'temprekapall_kategori_id_index');
        });



        $datalist = DB::table($templaporan)->from(
            DB::raw($templaporan . " as a")
        )
            ->select(
                DB::raw("'" . $lokasi . "' as lokasi"),
                'a.kodebarang',
                'a.namabarang',
                'a.tglbukti',
                'a.nobukti',
                'B.kodekategori as kategori_id',
                'a.qtymasuk',
                'a.nilaimasuk',
                'a.qtykeluar',
                'a.nilaikeluar',
                DB::raw("sum ((isnull(a.qtysaldo,0)+a.qtymasuk)-a.qtykeluar) over (order by a.created_at,a.urutfifo,a.tglbukti,a.id ASC) as qtysaldo"),
                DB::raw("sum ((isnull(a.nilaisaldo,0)+a.nilaimasuk)-a.nilaikeluar) over (order by a.created_at,a.urutfifo,a.tglbukti,a.id ASC) as nilaisaldo"),
                'a.modifiedby',
                'a.urutfifo',
                'a.created_at',
            )
            ->leftjoin('kategori as B', 'a.kategori_id', 'B.id')
            ->orderBy('a.created_at', 'asc')
            ->orderBy('a.urutfifo', 'asc')
            ->orderBy('a.tglbukti', 'asc')
            ->orderBy('a.id', 'asc');
        //  dd($datalist->get());
        // dd($datalist->get());

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
            'urutfifo',
            'created_at',
        ],  $datalist);




        $datalist = DB::table($temprekapall)->from(
            DB::raw($temprekapall . " as a")
        )
            ->select(
                'a.lokasi',
                'a.kodebarang',
                'a.namabarang',
                'a.tglbukti',
                'a.nobukti',
                'a.kategori_id',
                'a.qtymasuk',
                'a.nilaimasuk',
                'a.qtykeluar',
                'a.nilaikeluar',
                'a.qtysaldo',
                'a.nilaisaldo',
                'a.modifiedby',
                // 'a.created_at',
            )
            ->orderBy('a.id', 'asc');

        //  dd($datalist->get());
        return $datalist;
    }


    public function getReport($stokdari_id, $stoksampai_id, $dari, $sampai, $filter, $datafilter)
    {
        // data coba coba
        $query = DB::table('pengeluaranstokdetailfifo')->select(
            'pengeluaranstokdetailfifo.id',
            'stok.namastok as namabarang',
            'stok.namaterpusat as kodebarang',
            'kategori.keterangan as kategori_id',
            'pengeluaranstokdetailfifo.qty as qtykeluar',
            'pengeluaranstokdetailfifo.penerimaanstok_qty as qtymasuk',
            'pengeluaranstokdetailfifo.modifiedby'
        )
            ->leftJoin('stok', 'pengeluaranstokdetailfifo.stok_id', 'stok.id')
            ->leftJoin('kategori', 'stok.kategori_id', 'kategori.id');

        $parameter = Parameter::where('id', $filter)->first();
        if ($parameter->text == 'GUDANG') {
            $gudang_id = $datafilter;
            $query->where('pengeluaranstokdetailfifo.gudang_id', $gudang_id);
        }
        if ($parameter->text == 'TRADO') {
            $trado_id = $datafilter;
            $query->where('pengeluaranstokdetailfifo.trado_id', $trado_id);
        }
        if ($parameter->text == 'GANDENGAN') {
            $gandengan_id = $datafilter;
            $query->where('pengeluaranstokdetailfifo.gandengan_id', $gandengan_id);
        }
        $data = $query->get();
        return $data;
    }

    public function filter($query, $relationFields = [])
    {

        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        // if ($filters['field'] == 'namabarang') {
                        //     $query = $query->where('a.namabarang', 'LIKE', "%$filters[data]%");
                        // } else if ($filters['field'] == 'kodebarang') {
                        //     $query = $query->where('a.kodebarang', 'LIKE', "%$filters[data]%");
                        // } else if ($filters['field'] == 'kategori_id') {
                        //     $query = $query->where('a.kategori_id', 'LIKE', "%$filters[data]%");
                        // } else if ($filters['field'] == 'qtymasuk' || $filters['field'] == 'nilaimasuk' || $filters['field'] == 'qtykeluar' || $filters['field'] == 'nilaikeluar') {
                        //     $query = $query->whereRaw("format(a." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                        // } else if ($filters['field'] == 'qtysaldo') {
                        //     $query = $query->whereRaw("format((isnull(a.qtysaldo,0)+a.qtymasuk)-a.qtykeluar, '#,#0.00') LIKE '%$filters[data]%'");
                        // } else if ($filters['field'] == 'nilaisaldo') {
                        //     $query = $query->whereRaw("format((isnull(a.nilaisaldo,0)+a.nilaimasuk)-a.nilaikeluar, '#,#0.00') LIKE '%$filters[data]%'");
                        // } else if ($filters['field'] == 'tglbukti') {
                        //     $query = $query->whereRaw("format(a.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        // } else {
                        // $query = $query->where('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        $query = $query->whereRaw('a' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        // }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {

                            // if ($filters['field'] == 'namabarang') {
                            //     $query = $query->orWhere('a.namabarang', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'kodebarang') {
                            //     $query = $query->orWhere('a.kodebarang', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'kategori') {
                            //     $query = $query->orWhere('a.kategori_id', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'qtymasuk' || $filters['field'] == 'nilaimasuk' || $filters['field'] == 'qtykeluar' || $filters['field'] == 'nilaikeluar') {
                            //     $query = $query->orWhereRaw("format(a." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            // } else if ($filters['field'] == 'qtysaldo') {
                            //     $query = $query->orWhereRaw("format((isnull(a.qtysaldo,0)+a.qtymasuk)-a.qtykeluar, '#,#0.00') LIKE '%$filters[data]%'");
                            // } else if ($filters['field'] == 'nilaisaldo') {
                            //     $query = $query->orWhereRaw("format((isnull(a.nilaisaldo,0)+a.nilaimasuk)-a.nilaikeluar, '#,#0.00') LIKE '%$filters[data]%'");
                            // } else if ($filters['field'] == 'tglbukti') {
                            //     $query = $query->orWhereRaw("format(a.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            // } else {
                            // $query->orWhere('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->OrwhereRaw('a' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            // }
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

    public function processStore(array $data): kartustok
    {

        $stokid = $data['stok_id'] ?? 0;
        $querystok = db::table("stok")->from(db::raw("stok as a with (readuncommitted)"))
            ->select(
                'a.namastok',
                'a.kategori_id'
            )->where('a.id', $stokid)
            ->first();

        $gudang_id = $data['gudang_id'] ?? 0;
        $trado_id = $data['trado_id'] ?? 0;
        $gandengan_id = $data['gandengan_id'] ?? 0;

        if ($gudang_id != 0) {
            $lokasi = db::table('gudang')->from(db::raw("gudang as a with (readuncommitted)"))->select('a.gudang')->where('a.id', $gudang_id)->first()->gudang ?? '';
        }
        if ($gandengan_id != 0) {
            $lokasi = db::table('gandengan')->from(db::raw("gandengan as a with (readuncommitted)"))->select('a.kodegandengan')->where('a.id', $gandengan_id)->first()->kodegandengan ?? '';
        }
        if ($trado_id != 0) {
            $lokasi = db::table('trado')->from(db::raw("trado as a with (readuncommitted)"))->select('a.kodetrado')->where('a.id', $trado_id)->first()->kodetrado ?? '';
        }

        $kartustok = new KartuStok();
        $kartustok->gudang_id = $data['gudang_id'] ?? 0;
        $kartustok->trado_id = $data['trado_id'] ?? 0;
        $kartustok->gandengan_id = $data['gandengan_id'] ?? 0;
        $kartustok->stok_id = $data['stok_id'] ?? 0;
        $kartustok->lokasi = $lokasi ?? '';
        $kartustok->kodebarang =  $querystok->namastok ?? '';
        $kartustok->namabarang = $querystok->namastok ?? '';
        $kartustok->tglbukti = $data['tglbukti'];
        $kartustok->nobukti = $data['nobukti'] ?? '';
        $kartustok->kategori_id = $querystok->kategori_id ?? 0;
        $kartustok->qtymasuk = $data['qtymasuk'] ?? '';
        $kartustok->nilaimasuk = $data['nilaimasuk'] ?? '';
        $kartustok->qtykeluar = $data['qtykeluar'] ?? '';
        $kartustok->nilaikeluar = $data['nilaikeluar'] ?? '';
        $kartustok->urutfifo = $data['urutfifo'] ?? '';
        $kartustok->modifiedby = auth('api')->user()->name;
        $kartustok->info = html_entity_decode(request()->info);

        if (!$kartustok->save()) {
            throw new \Exception("Error storing kartu stok detail.");
        }

        return $kartustok;
    }

    public function processDestroy($nobukti): kartustok
    {
        $query = db::table("kartustok")->from(db::raw("kartustok as a with(readuncommitted)"))->select('a.id')
            ->where('nobukti', $nobukti)->orderBy('a.id', 'asc')->get();

        $datadetail = json_decode($query, true);
        foreach ($datadetail as $item) {
            $kartuStok = new KartuStok();
            $kartuStok = $kartuStok->lockAndDestroy($item['id']);

            $kartuStokLogTrail = (new LogTrail())->processStore([
                'namatabel' => $kartuStok->getTable(),
                'postingdari' => '',
                'idtrans' => $kartuStok->id,
                'nobuktitrans' => $kartuStok->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $kartuStok->toArray(),
                'modifiedby' => auth('api')->user()->name
            ]);
        }


        return $kartuStok;
    }


    //     // saldo awal gudang
    //     $queryrekap = db::table('stok')->from(
    //         DB::raw("stok as a1 with (readuncommitted)")
    //     )
    //         ->select(
    //             'a1.id as stok_id',
    //             'b.id as gudang_id',
    //             db::raw("0 as trado_id"),
    //             db::raw("0 as gandengan_id"),
    //             db::raw("(b.gudang) as lokasi"),
    //             db::raw("(a1.namastok) as kodebarang"),
    //             db::raw("(a1.namastok) as namabarang"),
    //             db::raw("'" . $tgl . "' as tglbukti"),
    //             db::raw("'SALDO AWAL' as nobukti"),
    //             db::raw("(a1.kategori_id) as kategori_id"),
    //             db::raw("0 as qtymasuk"),
    //             db::raw("0 as nilaimasuk"),
    //             db::raw("0 as qtykeluar"),
    //             db::raw("0 as nilaikeluar"),
    //             DB::raw("0 as qtysaldo"),
    //             DB::raw("0 as nilaisaldo"),
    //             db::raw("'ADMIN' as modifiedby"),
    //             db::raw("0 as urutfifo"),
    //         )
    //         // ->leftjoin(DB::raw($temprekap." as a"), function ($join)  {
    //         ->crossjoin('gudang as b')
    //         ->leftjoin(DB::raw($temprekap . " as a with(readuncommitted)"), function ($join) {
    //             $join->on('a1.id', '=', 'a.stok_id');
    //             $join->on('b.id', '=', 'a.gudang_id');
    //         })

    //         ->whereRaw("(a1.id>=" . $stokdari . " and a1.id<=" . $stoksampai . ")")
    //         ->whereRaw("isnull(a.id,0)=0");


    //     DB::table($temprekap)->insertUsing([
    //         'stok_id',
    //         'gudang_id',
    //         'trado_id',
    //         'gandengan_id',
    //         'lokasi',
    //         'kodebarang',
    //         'namabarang',
    //         'tglbukti',
    //         'nobukti',
    //         'kategori_id',
    //         'qtymasuk',
    //         'nilaimasuk',
    //         'qtykeluar',
    //         'nilaikeluar',
    //         'qtysaldo',
    //         'nilaisaldo',
    //         'modifiedby',
    //         'urutfifo',
    //     ], $queryrekap);

    //     // end

    //        // saldo awal trado
    //        $queryrekap = db::table('stok')->from(
    //         DB::raw("stok as a1 with (readuncommitted)")
    //     )
    //         ->select(
    //             'a1.id as stok_id',
    //             db::raw("0 as gudang_id"),
    //             db::raw("b.id as trado_id"),
    //             db::raw("0 as gandengan_id"),
    //             db::raw("(b.kodetrado) as lokasi"),
    //             db::raw("(a1.namastok) as kodebarang"),
    //             db::raw("(a1.namastok) as namabarang"),
    //             db::raw("'" . $tgl . "' as tglbukti"),
    //             db::raw("'SALDO AWAL' as nobukti"),
    //             db::raw("(a1.kategori_id) as kategori_id"),
    //             db::raw("0 as qtymasuk"),
    //             db::raw("0 as nilaimasuk"),
    //             db::raw("0 as qtykeluar"),
    //             db::raw("0 as nilaikeluar"),
    //             DB::raw("0 as qtysaldo"),
    //             DB::raw("0 as nilaisaldo"),
    //             db::raw("'ADMIN' as modifiedby"),
    //             db::raw("0 as urutfifo"),
    //         )
    //         // ->leftjoin(DB::raw($temprekap." as a"), function ($join)  {
    //         ->crossjoin('trado as b')
    //         ->leftjoin(DB::raw($temprekap . " as a with(readuncommitted)"), function ($join) {
    //             $join->on('a1.id', '=', 'a.stok_id');
    //             $join->on('b.id', '=', 'a.trado_id');
    //         })

    //         ->whereRaw("(a1.id>=" . $stokdari . " and a1.id<=" . $stoksampai . ")")
    //         ->whereRaw("isnull(a.id,0)=0");


    //     DB::table($temprekap)->insertUsing([
    //         'stok_id',
    //         'gudang_id',
    //         'trado_id',
    //         'gandengan_id',
    //         'lokasi',
    //         'kodebarang',
    //         'namabarang',
    //         'tglbukti',
    //         'nobukti',
    //         'kategori_id',
    //         'qtymasuk',
    //         'nilaimasuk',
    //         'qtykeluar',
    //         'nilaikeluar',
    //         'qtysaldo',
    //         'nilaisaldo',
    //         'modifiedby',
    //         'urutfifo',
    //     ], $queryrekap);

    //     // end

    //  // saldo awal gandengan
    //  $queryrekap = db::table('stok')->from(
    //     DB::raw("stok as a1 with (readuncommitted)")
    // )
    //     ->select(
    //         'a1.id as stok_id',
    //         db::raw("0 as gudang_id"),
    //         db::raw("0 as trado_id"),
    //         db::raw("b.id as gandengan_id"),
    //         db::raw("(b.kodegandengan) as lokasi"),
    //         db::raw("(a1.namastok) as kodebarang"),
    //         db::raw("(a1.namastok) as namabarang"),
    //         db::raw("'" . $tgl . "' as tglbukti"),
    //         db::raw("'SALDO AWAL' as nobukti"),
    //         db::raw("(a1.kategori_id) as kategori_id"),
    //         db::raw("0 as qtymasuk"),
    //         db::raw("0 as nilaimasuk"),
    //         db::raw("0 as qtykeluar"),
    //         db::raw("0 as nilaikeluar"),
    //         DB::raw("0 as qtysaldo"),
    //         DB::raw("0 as nilaisaldo"),
    //         db::raw("'ADMIN' as modifiedby"),
    //         db::raw("0 as urutfifo"),
    //     )
    //     // ->leftjoin(DB::raw($temprekap." as a"), function ($join)  {
    //     ->crossjoin('gandengan as b')
    //     ->leftjoin(DB::raw($temprekap . " as a with(readuncommitted)"), function ($join) {
    //         $join->on('a1.id', '=', 'a.stok_id');
    //         $join->on('b.id', '=', 'a.gandengan_id');
    //     })

    //     ->whereRaw("(a1.id>=" . $stokdari . " and a1.id<=" . $stoksampai . ")")
    //     ->whereRaw("isnull(a.id,0)=0");


    // DB::table($temprekap)->insertUsing([
    //     'stok_id',
    //     'gudang_id',
    //     'trado_id',
    //     'gandengan_id',
    //     'lokasi',
    //     'kodebarang',
    //     'namabarang',
    //     'tglbukti',
    //     'nobukti',
    //     'kategori_id',
    //     'qtymasuk',
    //     'nilaimasuk',
    //     'qtykeluar',
    //     'nilaikeluar',
    //     'qtysaldo',
    //     'nilaisaldo',
    //     'modifiedby',
    //     'urutfifo',
    // ], $queryrekap);

    // end    
}
