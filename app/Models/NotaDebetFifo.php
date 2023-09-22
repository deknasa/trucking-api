<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class NotaDebetFifo extends Model
{
    use HasFactory;
    protected $table = 'notadebetfifo';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function processStore(array $data): NotaDebetFifo
    {

        $nominal = $data['nominal'] ?? 0;
        $agen_id = $data['agen_id'] ?? 0;
        $pelunasanpiutang_id = $data['pelunasanpiutang_id'] ?? 0;
        $pelunasanpiutang_nobukti = $data['pelunasanpiutang_nobukti'] ?? '';


        $tempnotadebetfifo = '##tempnotadebetfifo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempnotadebetfifo, function ($table) {
            $table->string('notadebet_nobukti', 100)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->bigInteger('id')->nullable();
        });

        $querynotadebetfifo = db::table('notadebefifo')->from(db::raw("notadebefifo a with (readuncommitted)"))
            ->select(
                'a.notadebet_nobukti as nobukti',
                db::raw("sum(a.nominal) as nominal"),
                db::raw("max(b.id) as id"),
            )
            ->join(db::raw("notadebet b with (readuncommitted)"), 'a.notadebet_bukti', 'b.nobukti')
            ->where('b.agen_id', '=',   $agen_id)
            ->groupBY('a.notadebet_bukti');

        DB::table($tempnotadebetfifo)->insertUsing([
            'nobukti',
            'nominal',
            'id',
        ], $querynotadebetfifo);




        $kondisi = true;
        while ($kondisi == true) {
            $querysisa = db::table('notadebetrincian')->from(db::raw("notadebetrincian a with (readuncommitted)"))
                ->select(
                    db::raw("(a.nominal-isnull(B.nominal,0)) as nominalsisa"),
                    'a.nobukti',
                    'a.nominal',
                )
                ->join(db::raw($tempnotadebetfifo . " b "), 'a.nobukti', 'b.notadebet_bukti')
                ->where('agen_id', $agen_id)
                ->whereRaw("(a.nominal-isnull(B.nominal,0))<0")
                ->orderBy('a.id', 'asc')
                ->first();

            if (isset($querysisa)) {
                $nominalsisa = $querysisa->nominalsisa ?? 0;
                if ($nominal <= $nominalsisa) {
                    $notadebetFifo = new notadebetFifo();
                    $notadebetFifo->pelunasanpiutang_id = $pelunasanpiutang_id;
                    $notadebetFifo->pelunasanpiutang_nobukti = $pelunasanpiutang_nobukti;
                    $notadebetFifo->agen_id = $agen_id;
                    $notadebetFifo->nominal = $nominal ?? 0;
                    $notadebetFifo->notadebet_nobukti = $querysisa->nobukti ?? '';
                    $notadebetFifo->notadebet_nominal = $querysisa->nominal ?? 0;
                    $notadebetFifo->modifiedby = $data['modifiedby'] ?? '';
                    $kondisi = false;
                    if (!$notadebetFifo->save()) {
                        throw new \Exception("Error Simpan Nota Debet Detail fifo.");
                    }
                } else {
                    $nominal = $nominal - $nominalsisa;
                    $notadebetFifo = new notadebetFifo();
                    $notadebetFifo->pelunasanpiutang_id = $pelunasanpiutang_id;
                    $notadebetFifo->pelunasanpiutang_nobukti = $pelunasanpiutang_nobukti;
                    $notadebetFifo->agen_id = $agen_id;
                    $notadebetFifo->nominal = $nominalsisa ?? 0;
                    $notadebetFifo->notadebet_nobukti = $querysisa->nobukti ?? '';
                    $notadebetFifo->notadebet_nominal = $querysisa->nominal ?? 0;
                    $notadebetFifo->modifiedby = $data['modifiedby'] ?? '';
                    if (!$notadebetFifo->save()) {
                        throw new \Exception("Error Simpan Nota Debet Detail fifo.");
                    }
                }
            }
        }

        // 

        // $tempmasuk = '##tempmasuk' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($tempmasuk, function ($table) {
        //     $table->string('nobukti', 100)->nullable();
        //     $table->dateTime('tglbukti')->nullable();
        //     $table->string('agen_id', 500)->nullable();
        //     $table->double('nominal', 15, 2)->nullable();
        //     $table->bigInteger('urut')->nullable();
        // });

        // $tempalur = '##tempalur' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($tempalur, function ($table) {
        //     $table->string('nobuktikeluar', 100)->nullable();
        //     $table->double('nominalout', 15, 2)->nullable();
        //     $table->double('nominaloutberjalan', 15, 2)->nullable();
        //     $table->string('nobuktimasuk', 100)->nullable();
        //     $table->double('nominalinberjalan', 15, 2)->nullable();
        //     $table->double('selisih', 15, 2)->nullable();
        //     $table->bigInteger('urut')->nullable();
        // });


        // $querytempmasuk = db::table('notadebetrincian')->from(db::raw("notadebetrincian a with (readuncommitted)"))
        //     ->select(
        //         'b.nobukti as nobukti',
        //         'b.tglbukti as tglbukti',
        //         'b.agen_id',
        //         db::raw("(a.nominal-isnull(a.nominalkeluar,0)) as nominal"),
        //         db::raw("row_number() Over(Order By b.tglbukti ,a.id ) as urut")
        //     )
        //     ->join(db::raw("notadebetheader as b with (readuncommitted)"), 'b.id', 'a.notadebet_id')
        //     ->where('b.agen_id', '=',  $data['agen_id'])
        //     ->whereRaw("isnull(a.nominalkeluar,0)<a.nominal")
        //     ->orderBy('B.tglbukti', 'Asc')
        //     ->orderBy('a.id', 'Asc');


        // DB::table($tempmasuk)->insertUsing([
        //     'nobukti',
        //     'tglbukti',
        //     'agen_id',
        //     'nominal',
        //     'urut',
        // ], $querytempmasuk);



        // $querymsk = DB::table($tempmasuk)
        //     ->select(
        //         DB::raw("sum(nominal) as nominal")
        //     )
        //     ->first();

        // $nominalin = $querymsk->nominal ?? 0;


        // if ($data['nominal'] > $nominalin) {
        //     // throw new \Exception("QTY " .app(ErrorController::class)->geterror('SMIN')->keterangan);
        //     throw ValidationException::withMessages(['nominal' => "Nominal " . app(ErrorController::class)->geterror('SMIN')->keterangan]);
        // }


        // $tempkeluar = '##tempkeluar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($tempkeluar, function ($table) {
        //     $table->string('nobukti', 100)->nullable();
        //     $table->dateTime('tglbukti')->nullable();
        //     $table->bigInteger('agen_id')->nullable();
        //     $table->double('nominal', 15, 2)->nullable();
        //     $table->double('urut', 15, 2)->nullable();
        //     $table->bigInteger('id')->nullable();
        // });



        // $querytempkeluar = db::table('pelunasanpiutangheader')->from(db::raw("pelunasanpiutangheader a with (readuncommitted)"))
        //     ->select(
        //         'a.nobukti',
        //         'a.tglbukti',
        //         'a.agen_id',
        //         'a.nominallunas as nominal',
        //         DB::raw(" row_number() Over(Order By a.tglbukti ,a.id)  as urut"),
        //         'a.id'
        //     )

        //     ->where('a.nobukti', '=',  $data['pelunasanpiutang_nobukti'])
        //     ->orderBy('a.tglbukti', 'Asc')
        //     ->orderBy('a.id', 'Asc');

        // DB::table($tempkeluar)->insertUsing([
        //     'nobukti',
        //     'tglbukti',
        //     'agen_id',
        //     'nominal',
        //     'urut',
        //     'id'
        // ], $querytempkeluar);


        // $tempkeluarrekap = '##Tempkeluarrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($tempkeluarrekap, function ($table) {
        //     $table->string('nobukti', 100)->nullable();
        //     $table->dateTime('tglbukti')->nullable();
        //     $table->bigInteger('agen_id')->nullable();
        //     $table->double('nominal', 15, 2)->nullable();
        //     $table->double('urut', 15, 2)->nullable();
        //     $table->double('nominal2', 15, 2)->nullable();
        //     $table->string('nobuktimasuk', 100)->nullable();
        //     $table->bigInteger('id')->nullable();
        // });

        // $tempmasukrekap = '##Tempmasukrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($tempmasukrekap, function ($table) {
        //     $table->string('nobukti', 100)->nullable();
        //     $table->dateTime('tglbukti')->nullable();
        //     $table->string('agen_id', 100)->nullable();
        //     $table->double('nominal', 15, 2)->nullable();
        //     $table->double('urut', 15, 2)->nullable();
        //     $table->double('nominal2', 15, 2)->nullable();
        // });


        // $querytempkeluarrekap = DB::table($tempkeluar)->from(
        //     DB::raw($tempkeluar . " as i")
        // )
        //     ->select(
        //         'i.nobukti',
        //         'i.tglbukti',
        //         'i.agen_id',
        //         'i.nominal',
        //         'i.urut',
        //         DB::raw(
        //             "isnull(sum(i.nominal) over (
        //     partition by i.agen_id
        //     order by i.tglbukti, i.nobukti
        //     rows between unbounded preceding and 0 preceding
        //  ),0) as nominal2"
        //         ),
        //         'i.id'
        //     );

        // DB::table($tempkeluarrekap)->insertUsing([
        //     'nobukti',
        //     'tglbukti',
        //     'agen_id',
        //     'nominal',
        //     'urut',
        //     'nominal2',
        //     'id'
        // ], $querytempkeluarrekap);


        // $querytempmasukrekap = DB::table($tempmasuk)->from(
        //     DB::raw($tempmasuk . " as i")
        // )
        //     ->select(
        //         'i.nobukti',
        //         'i.tglbukti',
        //         'i.agen_id',
        //         'i.nominal',
        //         'i.urut',
        //         DB::raw(
        //             "isnull(sum(i.nominal) over (
        //             partition by i.agen_id
        //             order by i.tglbukti, i.nobukti
        //             rows between unbounded preceding and 0 preceding
        //          ),0) as nominal2"
        //         )
        //     );
        // // dd($querytempmasukrekap ->get());

        // DB::table($tempmasukrekap)->insertUsing([
        //     'nobukti',
        //     'tglbukti',
        //     'agen_id',
        //     'nominal',
        //     'urut',
        //     'nominal2'
        // ], $querytempmasukrekap);


        // $tempkeluarupdate = '##tempkeluarupdate' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($tempkeluarupdate, function ($table) {
        //     $table->string('nobuktimasuk', 100)->nullable();
        //     $table->double('nominal', 15, 2)->nullable();
        // });


        // $queryloopkeluarrekap = DB::table($tempkeluarrekap)->select(
        //     'nobukti',
        //     'tglbukti',
        //     'agen_id',
        //     'nominal',
        //     'urut',
        //     'nominal2'
        // )->get();


        // dd(db::table($tempmasukrekap)->get());
        // dd($queryloopkeluarrekap);
        // $aqty = 1;

        // $curut = 0;
        // $datadetail = json_decode($queryloopkeluarrekap, true);
        // foreach ($datadetail as $item) {
        //     // dump('-');
        //     // dump($aqty);
        //     // dump($item['fqty2']);
        //     // dump('AA');
        //     $datamasuk = DB::table($tempmasukrekap)->select(
        //         'nobukti',
        //         'nominal2',
        //         'urut',
        //     )
        //         ->whereRaw($item['nominal2'] . "<=nominal2")
        //         ->orderBy('urut', 'asc')
        //         ->first();

        //     if (isset($datamasuk)) {
        //         $selnominal = $datamasuk->nominal2 - $item['nominal2'];
        //         $curut += 1;
        //         DB::table($tempalur)->insert([
        //             'nobuktikeluar' => $item['nobukti'],
        //             'nominalout' => $item['nominal'],
        //             'nominaloutberjalan' => $item['nominal2'],
        //             'nobuktimasuk' => $datamasuk->nobukti,
        //             'nominalinberjalan' => $datamasuk->nominal2,
        //             'selisih' => $selnominal,
        //             'urut' => $curut,
        //         ]);
        //     }
        // }

        // dd(db::table($tempalur)->get());

        // $tempalurrekap = '##Tempalurrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($tempalurrekap, function ($table) {
        //     $table->string('nobukti', 100)->nullable();
        //     $table->string('nobuktimasuk', 100)->nullable();
        //     $table->double('jumlah', 15, 2)->nullable();
        //     $table->double('urut', 15, 2)->nullable();
        // });


        // $querytempalurrekap = DB::table($tempalur)->from(
        //     DB::raw($tempalur . " as i")
        // )
        //     ->select(
        //         'i.nobuktikeluar',
        //         'i.nobuktimasuk',
        //         DB::raw("sum(i.nominaloutberjalan) as jumlah"),
        //         DB::raw("max(i.urut) as urut")
        //     )
        //     ->groupBy('i.nobuktikeluar', 'i.nobuktimasuk');


        // DB::table($tempalurrekap)->insertUsing([
        //     'nobukti',
        //     'nobuktimasuk',
        //     'jumlah',
        //     'urut'
        // ], $querytempalurrekap);



        // $temphasil = '##Temphasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($temphasil, function ($table) {
        //     $table->string('nobukti', 100)->nullable();
        //     $table->dateTime('tglbukti')->nullable();
        //     $table->bigInteger('agen_id')->nullable();
        //     $table->double('nominal', 15, 2)->nullable();
        //     $table->double('urut', 15, 2)->nullable();
        //     $table->double('nominal2', 15, 2)->nullable();
        //     $table->longText('notadebet_nobukti')->nullable();
        //     $table->double('notadebet_nominal', 15, 2)->nullable();
        //     $table->double('notadebet_terpakai', 15, 2)->nullable();
        //     $table->bigInteger('id')->nullable();
        // });

        // $temphasil2 = '##Temphasil2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($temphasil2, function ($table) {
        //     $table->string('nobukti', 100)->nullable();
        //     $table->dateTime('tglbukti')->nullable();
        //     $table->bigInteger('agen_id')->nullable();
        //     $table->float('nominal', 15, 2)->nullable();
        //     $table->bigInteger('urut')->nullable();
        //     $table->float('nominal2', 15, 2)->nullable();
        //     $table->longText('notadebet_nobukti')->nullable();
        //     $table->float('notadebet_nominal', 15, 2)->nullable();
        // });


        // $querytemphasil2 = DB::table($tempkeluarrekap)->from(
        //     DB::raw($tempkeluarrekap . " as A")
        // )
        //     ->select(
        //         'A.nobukti',
        //         'A.tglbukti',
        //         DB::raw($data['agen_id'] . " as agen_id"),
        //         'A.nominal',
        //         DB::raw("row_number() Over(Order By A.Urut,B.Urut) As Urut"),
        //         'A.nominal2',
        //         'B.nobuktimasuk',
        //         'B.jumlah as notadebet_nominal',
        //     )
        //     ->leftjoin(DB::raw($tempalurrekap . " as B"), 'A.nobukti', 'b.nobukti')
        //     ->leftjoin(DB::raw($tempmasuk . " as C"), 'B.nobuktimasuk', 'c.nobukti')

        //     ->orderBy('A.urut', 'Asc');

        // // dd($querytemphasil2->get());

        // DB::table($temphasil2)->insertUsing([
        //     'nobukti',
        //     'tglbukti',
        //     'agen_id',
        //     'nominal',
        //     'urut',
        //     'nominal2',
        //     'notadebet_nobukti',
        //     'notadebet_nominal',
        // ], $querytemphasil2);


        // $querytemphasil = DB::table($temphasil2)->from(
        //     DB::raw($temphasil2 . " as A")
        // )
        //     ->select(
        //         'A.nobukti',
        //         'A.tglbukti',
        //         DB::raw($data['agen_id'] . " as agen_id"),
        //         'A.nominal',
        //         'A.urut',
        //         'A.nominal2',
        //         'A.notadebet_nobukti',
        //         DB::raw("isnull(b.nominal,0) as nominal"),
        //         DB::raw("isnull(sum(A.notadebet_nominal) over (
        //                 partition by A.agen_id,A.nobukti
        //                 order by a.urut
        //                 rows between unbounded preceding and 0 preceding
        //              ),0) as saldonominal"),
        //         DB::raw("isnull(c.id,0) as id"),
        //     )
        //     ->leftjoin(DB::raw($tempmasuk . " as B"), 'A.notadebet_nobukti', 'B.nobukti')
        //     ->leftjoin(DB::raw($tempkeluar . " as C"), 'A.nobukti', 'C.nobukti')
        //     ->orderBy('A.urut', 'Asc');

        // // dd($querytemphasil->get());

        // DB::table($temphasil)->insertUsing([
        //     'nobukti',
        //     'tglbukti',
        //     'agen_id',
        //     'nominal',
        //     'urut',
        //     'nominal2',
        //     'notadebet_nobukti',
        //     'notadebet_nominal',
        //     'notadebet_terpakai',
        //     'id',
        // ], $querytemphasil);


        // // $test = DB::table($temphasil)->orderBy('urut', 'Asc')->get();
        // // dd(DB::table($temphasil)->get());

        // $datalist = DB::table($temphasil2);

        // dd($datalist->get());


        // $datadetail = json_decode($datalist->get(), true);


        // foreach ($datadetail as $item) {

        //     $notadebetFifo = new notadebetFifo();
        //     $notadebetFifo->pelunasanpiutang_id = $data['pelunasanpiutang_id'] ?? 0;
        //     $notadebetFifo->pelunasanpiutang_nobukti = $data['pelunasanpiutang_nobukti'] ?? '';
        //     $notadebetFifo->agen_id = $data['agen_id'] ?? 0;
        //     $notadebetFifo->urut = $item['urut'] ?? 0;
        //     $notadebetFifo->nominal = $item['nominal'] ?? 0;
        //     $notadebetFifo->notadebet_nobukti = $item['notadebet_nobukti'] ?? '';
        //     $notadebetFifo->notadebet_nominal = $item['notadebet_nominal'] ?? 0;
        //     $notadebetFifo->modifiedby = $data['modifiedby'] ?? '';
        //     // dd($item['notadebet_nominal']);
        //     if (!$notadebetFifo->save()) {
        //         throw new \Exception("Error Simpan Nota Debet Detail fifo.");
        //     }



        //     $notadebetrincian  = NotaDebetRincian::lockForUpdate()->where("agen_id", $item['agen_id'])
        //         ->where("nobukti", $item['notadebet_nobukti'])
        //         ->firstorFail();
        //     $notadebetrincian->nominalkeluar += $item['notadebet_nominal'] ?? 0;
        //     $notadebetrincian->save();
        // }



        // $pengeluaranstokdetail->save();
        // if (!$notadebetrincian->save()) {
        //     throw new \Exception("Error storing pengeluaran Stok Detail  update fifo. ");
        // }

        return $notadebetFifo;
    }
}
