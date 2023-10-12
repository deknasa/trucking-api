<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class HutangPrediksiFifo extends MyModel
{
    use HasFactory;

    protected $table = 'hutangprediksififo';

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
        $statushutangprediksi = $data['statushutangprediksi'] ?? 0;
        $pelunasanhutangprediksi_id = $data['pelunasanhutangprediksi_id'] ?? 0;
        $pelunasanhutangprediksi_nobukti = $data['pelunasanhutangprediksi_nobukti'] ?? '';

        // 
        $ptgl = '2023/9/30';


        $Tempsaldohutangprediksi = '##Tempsaldohutangprediksi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Tempsaldohutangprediksi, function ($table) {
            $table->id();
            $table->string('nobukti', 100)->nullable();
            $table->string('nobukti_id', 100)->nullable();
            $table->dateTime('tgl')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });
        $querysaldohutangprediksi = db::table("saldohutangprediksi")->from(db::raw("saldohutangprediksi a with (readuncommitted)"))
            ->select(
                'a.nobukti',
                db::raw("sum(nominal) as nominal"),
                db::raw($ptgl . " as tgl ")
            )
            ->where('a.statushutangprediksi', $statushutangprediksi)
            ->groupBY('a.nobukti');

        DB::table($Tempsaldohutangprediksi)->insertUsing([
            'nobukti',
            'nominal',
            'tgl',
        ], $querysaldohutangprediksi);


        $querysaldohutangprediksi = db::table("jurnalumumdetail")->from(db::raw("jurnalumumdetail a with (readuncommitted)"))
            ->select(
                'a.nobukti',
                db::raw("sum(nominal) as nominal"),
                db::raw($ptgl . " as tgl ")
            )
            ->join(db::raw("jurnalumumheader b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->where('a.statushutangprediksi', $statushutangprediksi)
            ->whereRaw("a.coa='03.02.02.04' AND a.nominal<0")
            ->groupBY('a.nobukti');

        DB::table($Tempsaldohutangprediksi)->insertUsing([
            'nobukti',
            'nominal',
            'tgl',
        ], $querysaldohutangprediksi);

        $tempmasuk = '##tempmasuk' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempmasuk, function ($table) {
            $table->id();
            $table->string('nobukti', 100)->nullable();
            $table->string('nobukti_id', 100)->nullable();
            $table->dateTime('tgl')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $tempkeluar = '##tempkeluar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempkeluar, function ($table) {
            $table->id();
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->string('nobuktimasuk', 100)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->integer('idheader')->nullable();
            $table->integer('iddetail')->nullable();
        });

        $querymasuk = db::table($Tempsaldohutangprediksi)->from(db::raw($Tempsaldohutangprediksi . " a"))
            ->select(
                'a.nobukti as FNtrans',
                'a.nominal as FNominalIN',
                'tgl as FTGl'
            )
            ->orderBY('a.id', 'asc');

        DB::table($tempmasuk)->insertUsing([
            'nobukti',
            'nominal',
            'tgl',
        ], $querymasuk);

        DB::update(DB::raw("UPDATE " . $tempmasuk . " SET nobukti_id=trim(nobukti)+'_'+replicate('0',5-len(trim(str(id))))+trim(str(id))"));

        $querykeluar = db::table("jurnalumumdetail")->from(db::raw("jurnalumumdetail a"))
            ->select(
                'a.nobukti as FNtrans',
                db::raw("sum(a.nominal) as FNominalOut"),
                'b.tglbukti',
                'b.id as idheader',
                'a.id as iddetail'
            )
            ->join(db::raw("jurnalumumheader b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->where('a.statushutangprediksi', $statushutangprediksi)
            ->whereRaw("a.coa='03.02.02.04' AND a.nominal>0")
            ->groupby('b.tglbukti')
            ->groupby('b.id')
            ->groupby('a.id')
            ->groupby('a.nobukti')
            ->orderBY('b.tglbukti', 'asc')
            ->orderBY('b.id', 'asc')
            ->orderBY('a.id', 'asc');

        DB::table($tempkeluar)->insertUsing([
            'nobukti',
            'nominal',
            'tglbukti',
            'idheader',
            'iddetail',
        ], $querykeluar);

        $Temppengeluaranstokdetailfifo = '##Temppengeluaranstokdetailfifo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppengeluaranstokdetailfifo, function ($table) {
            $table->id();
            $table->string('nobukti', 100)->nullable();
            $table->string('nobukti_id', 100)->nullable();
            $table->integer('urut')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->string('penerimaanhutangprediksi_nobukti', 100)->nullable();
            $table->double('penerimaanhutangprediksi_nominal', 15, 2)->nullable();
        });


        $Tempfifo = '##Tempfifo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Tempfifo, function ($table) {
            $table->string('penerimaanstok_nobukti', 100)->nullable();
            $table->string('nobukti_id', 100)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->bigInteger('id')->nullable();
        });

        $queryloop = db::table($tempkeluar)->from(db::raw($tempkeluar . " a"))
            ->select(
                'a.id',
                'a.nobukti',
                'a.nominal'
            )
            ->orderby('a.id', 'asc')
            ->get();

        $datadetail = json_decode($queryloop, true);
        foreach ($datadetail as $item) {

            $xid = $item['id'];
            $xnobukti = $item['nobukti'];
            $xnominal = $item['nominal'];

            $kondisi = true;
            while ($kondisi == true) {
                DB::delete(DB::raw("delete " . $Tempfifo));

                $querytempfifo = db::table($Temppengeluaranstokdetailfifo)->from(db::raw($Temppengeluaranstokdetailfifo . " a"))
                    ->select(
                        db::raw("max(a.penerimaanhutangprediksi_nobukti) as penerimaanhutangprediksi_nobukti"),
                        db::raw("sum(a.nominal) as nominal"),
                        db::raw("max(b.id) as id"),
                        'a.nobukti_id'
                    )
                    ->join(db::raw($tempmasuk . " b "), 'a.nobukti_id', 'b.nobukti_id')
                    ->groupBY('a.nobukti');

                DB::table($Tempfifo)->insertUsing([
                    'penerimaanstok_nobukti',
                    'nominal',
                    'id',
                    'nobukti_id',
                ], $querytempfifo);

                $querysisa = db::table($tempmasuk)->from(db::raw($tempmasuk . " a"))
                    ->select(
                        db::raw("(a.nominal-isnull(B.nominal,0)) as anominalsisa"),
                        'a.nobukti as anobukti',
                        'a.nominal as anominal',
                        db::raw("trim(a.nobukti)+'_'+replicate('0',5-len(trim(str(a.id))))+trim(str(a.id)) as anobukti_id")
                    )
                    ->leftjoin(db::raw($Tempfifo . " b"), 'a.nobukti_id', 'b.nobukti_id')
                    ->whereRaw("(a.nominal-isnull(b.nominal,0))>0")
                    ->orderBY('a.id', 'asc')
                    ->first();

                $anominalsisa = $querysisa->anominalsisa ?? 0;
                $anobukti = $querysisa->nobukti ?? '';
                $anominal = $querysisa->nominal ?? 0;
                $anobukti_id = $querysisa->anobukti_id ?? '';


                if (isset($querysisa)) {
                    if ($xnominal <= $anominalsisa) {

                        DB::table($Temppengeluaranstokdetailfifo)->insert([
                            'nobukti' => $xnobukti,
                            'urut' => 1,
                            'nominal' => $xnominal,
                            'penerimaanhutangprediksi_nobukti' => $anobukti,
                            'penerimaanhutangprediksi_nominal' => $anominal,
                            'nobukti_id' => $anobukti_id,
                        ]);

                        $kondisi = true;
                    } else {
                        $xnominal = $xnominal - $anominalsisa;

                        DB::table($Temppengeluaranstokdetailfifo)->insert([
                            'nobukti' => $xnobukti,
                            'urut' => 1,
                            'nominal' => $anominalsisa,
                            'penerimaanhutangprediksi_nobukti' => $anobukti,
                            'penerimaanhutangprediksi_nominal' => $anominal,
                            'nobukti_id' => $anobukti_id,
                        ]);
                    }
                } else {
                    $kondisi = false;
                }
            }
        }
return
        // return $hutangprediksiFifo;
    }
}
