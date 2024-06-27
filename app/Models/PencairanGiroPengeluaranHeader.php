<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PencairanGiroPengeluaranHeader extends MyModel
{
    use HasFactory;

    protected $table = 'PencairanGiroPengeluaranHeader';
    protected $anotherTable = 'pengeluaranheader';
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


        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'pencarianGiroController';

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
                $table->string('pengeluaran_nobukti', 300)->nullable();
                $table->date('tglbukti_giro')->nullable();
                $table->longText('dibayarke')->nullable();
                $table->longText('urlpengeluaran')->nullable();
                $table->longText('bank_id')->nullable();
                $table->longText('transferkeac')->nullable();
                $table->longText('alatbayar_id')->nullable();
                $table->longText('nobukti')->nullable();
                $table->date('tglbukti')->nullable();
                $table->longText('statusapproval')->nullable();
                $table->date('tgljatuhtempo')->nullable();
                $table->double('nominal')->nullable();
                $table->longText('modifiedby')->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
            });

            $status = request()->status;
            if ($status == 591) {
                $query =  $this->getPengeluaran();
            } else {
                $query =  $this->getPenerimaan();
            }
            DB::table($temtabel)->insertUsing([
                'pengeluaran_nobukti',
                'tglbukti_giro',
                'dibayarke',
                'urlpengeluaran',
                'bank_id',
                'transferkeac',
                'alatbayar_id',
                'nobukti',
                'tglbukti',
                'statusapproval',
                'tgljatuhtempo',
                'nominal',
                'modifiedby',
                'created_at',
                'updated_at'

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

            // dd($querydata);
            $temtabel = $querydata->namatabel;
        }

        $query = DB::table($temtabel)->from(DB::raw($temtabel . " a "))
            ->select(
                DB::raw("row_number() Over(Order By a.pengeluaran_nobukti) as id"),
                'a.pengeluaran_nobukti',
                'a.tglbukti_giro',
                'a.dibayarke',
                'a.urlpengeluaran',
                'a.bank_id',
                'a.transferkeac',
                'a.alatbayar_id',
                'a.nobukti',
                'a.tglbukti',
                'a.statusapproval',
                'a.tgljatuhtempo',
                'a.nominal',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at'
            )
            ->orderBy('a.tglbukti_giro');
        $this->sort($query);

        $this->filter($query);

        $this->paginate($query);


        $this->totalRows = $query->count();

        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $data = $query->get();
        // dd($data);
        return $data;
    }


    public function getPengeluaran()
    {

        $periode = request()->periode ?? date('m-Y');
        $month = substr($periode, 0, 2);
        $year = substr($periode, 3);

        $alatBayar = AlatBayar::from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'GIRO')->first();
        $alatBayarCheck = AlatBayar::from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'CHECK')->first()->id ?? 0;

        $petik = '"';
        $url = config('app.url_fe') . 'pengeluaranheader';

        $templist = '##templist' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($templist, function ($table) {
            $table->string('pengeluaran_nobukti', 300)->nullable();
            $table->date('tglbukti_giro')->nullable();
            $table->integer('id')->nullable();
            $table->longText('dibayarke')->nullable();
            $table->longText('urlpengeluaran')->nullable();
            $table->longText('bank_id')->nullable();
            $table->longText('transferkeac')->nullable();
            $table->longText('alatbayar_id')->nullable();
            $table->longText('nobukti')->nullable();
            $table->date('tglbukti')->nullable();
            $table->longText('statusapproval')->nullable();
            $table->date('tgljatuhtempo')->nullable();
            $table->double('nominal')->nullable();
            $table->longText('modifiedby')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        // ALAT BAYAR GIRO
        $query1 = DB::table("pengeluaranheader")->from(DB::raw("pengeluaranheader with (readuncommitted)"))
            ->select(
                DB::raw("
                    pengeluaranheader.nobukti as pengeluaran_nobukti,
                    pengeluaranheader.tglbukti as tglbukti_giro,
                    pengeluaranheader.id, 
                    pengeluaranheader.dibayarke, 
                    '<a href=$petik" . $url . "?tgldari='+(format(pengeluaranheader.tglbukti,'yyyy-MM')+'-1')+'&tglsampai='+(format(pengeluaranheader.tglbukti,'yyyy-MM')+'-31')+'&nobukti='+pengeluaranheader.nobukti+'&bank_id='+CAST(pengeluaranheader.bank_id AS varchar)+'$petik 
                    class=$petik link-color $petik target=$petik _blank $petik>'+pengeluaranheader.nobukti+'</a>' as urlpengeluaran,
                    bank.namabank as bank_id, 
                    pengeluaranheader.transferkeac, 
                    alatbayar.namaalatbayar as alatbayar_id,
                    pgp.nobukti,
                    pgp.tglbukti, 
                    parameter.memo as statusapproval,
                    pengeluarandetail.tgljatuhtempo,
                    (SELECT (SUM(pengeluarandetail.nominal)) FROM pengeluarandetail 
                        WHERE pengeluarandetail.nobukti= pengeluaranheader.nobukti and pengeluaranheader.alatbayar_id=$alatBayar->id) as nominal,
                    (case when isnull(pgp.nobukti,'')='' then pengeluaranheader.modifiedby else pgp.modifiedby end) as modifiedby, 
                    (case when isnull(pgp.nobukti,'')='' then pengeluaranheader.created_at else pgp.created_at end) as created_at, 
                    (case when isnull(pgp.nobukti,'')='' then pengeluaranheader.updated_at else pgp.updated_at end) as updated_at
                ")
            )
            ->distinct('pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("pengeluarandetail with (readuncommitted)"), 'pengeluarandetail.nobukti', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("pencairangiropengeluaranheader as pgp with (readuncommitted)"), 'pgp.pengeluaran_nobukti', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'pgp.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluaranheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pengeluaranheader.alatbayar_id', 'alatbayar.id')
            ->whereRaw("MONTH(pengeluaranheader.tglbukti) = $month")
            ->whereRaw("YEAR(pengeluaranheader.tglbukti) = $year")
            ->where('pengeluaranheader.alatbayar_id', $alatBayar->id);

        DB::table($templist)->insertUsing([
            'pengeluaran_nobukti',
            'tglbukti_giro',
            'id',
            'dibayarke',
            'urlpengeluaran',
            'bank_id',
            'transferkeac',
            'alatbayar_id',
            'nobukti',
            'tglbukti',
            'statusapproval',
            'tgljatuhtempo',
            'nominal',
            'modifiedby',
            'created_at',
            'updated_at',

        ], $query1);

        // alat bayar check
        $query1 = DB::table("pengeluaranheader")->from(DB::raw("pengeluaranheader with (readuncommitted)"))
            ->select(
                DB::raw("
                    pengeluaranheader.nobukti as pengeluaran_nobukti,
                    pengeluaranheader.tglbukti as tglbukti_giro,
                    pengeluaranheader.id, 
                    pengeluaranheader.dibayarke, 
                    '<a href=$petik" . $url . "?tgldari='+(format(pengeluaranheader.tglbukti,'yyyy-MM')+'-1')+'&tglsampai='+(format(pengeluaranheader.tglbukti,'yyyy-MM')+'-31')+'&nobukti='+pengeluaranheader.nobukti+'&bank_id='+CAST(pengeluaranheader.bank_id AS varchar)+'$petik 
                    class=$petik link-color $petik target=$petik _blank $petik>'+pengeluaranheader.nobukti+'</a>' as urlpengeluaran,
                    bank.namabank as bank_id, 
                    pengeluaranheader.transferkeac, 
                    alatbayar.namaalatbayar as alatbayar_id,
                    pgp.nobukti,
                    pgp.tglbukti, 
                    parameter.memo as statusapproval,
                    pengeluarandetail.tgljatuhtempo,
                    (SELECT (SUM(pengeluarandetail.nominal)) FROM pengeluarandetail 
                        WHERE pengeluarandetail.nobukti= pengeluaranheader.nobukti and pengeluaranheader.alatbayar_id=$alatBayarCheck) as nominal,
                    (case when isnull(pgp.nobukti,'')='' then pengeluaranheader.modifiedby else pgp.modifiedby end) as modifiedby, 
                    (case when isnull(pgp.nobukti,'')='' then pengeluaranheader.created_at else pgp.created_at end) as created_at, 
                    (case when isnull(pgp.nobukti,'')='' then pengeluaranheader.updated_at else pgp.updated_at end) as updated_at
                ")
            )
            ->distinct('pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("pengeluarandetail with (readuncommitted)"), 'pengeluarandetail.nobukti', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("pencairangiropengeluaranheader as pgp with (readuncommitted)"), 'pgp.pengeluaran_nobukti', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'pgp.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluaranheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pengeluaranheader.alatbayar_id', 'alatbayar.id')
            ->whereRaw("MONTH(pengeluaranheader.tglbukti) = $month")
            ->whereRaw("YEAR(pengeluaranheader.tglbukti) = $year")
            ->where('pengeluaranheader.alatbayar_id', $alatBayarCheck);

        DB::table($templist)->insertUsing([
            'pengeluaran_nobukti',
            'tglbukti_giro',
            'id',
            'dibayarke',
            'urlpengeluaran',
            'bank_id',
            'transferkeac',
            'alatbayar_id',
            'nobukti',
            'tglbukti',
            'statusapproval',
            'tgljatuhtempo',
            'nominal',
            'modifiedby',
            'created_at',
            'updated_at',

        ], $query1);

        $query2 = DB::table("saldopengeluaranheader")->from(DB::raw("saldopengeluaranheader as pengeluaranheader with (readuncommitted) "))
            ->select(
                DB::raw("
                    pengeluaranheader.nobukti as pengeluaran_nobukti,
                    pengeluaranheader.tglbukti as tglbukti_giro,
                    pengeluaranheader.id, 
                    pengeluaranheader.dibayarke, 
                    '<a href=$petik" . $url . "?tgldari='+(format(pengeluaranheader.tglbukti,'yyyy-MM')+'-1')+'&tglsampai='+(format(pengeluaranheader.tglbukti,'yyyy-MM')+'-31')+'$petik 
                    class=$petik link-color $petik target=$petik _blank $petik>'+pengeluaranheader.nobukti+'</a>' as urlpengeluaran,                    
                    bank.namabank as bank_id, 
                    pengeluaranheader.transferkeac,     
                    alatbayar.namaalatbayar as alatbayar_id,   
                    pgp.nobukti, 
                    pgp.tglbukti, 
                    parameter.memo as statusapproval,
                    pengeluarandetail.tgljatuhtempo,
                    (SELECT (SUM(pengeluarandetail.nominal)) FROM saldopengeluarandetail as pengeluarandetail
                        WHERE pengeluarandetail.nobukti= pengeluaranheader.nobukti and pengeluaranheader.alatbayar_id=$alatBayar->id) as nominal,      
                    (case when isnull(pgp.nobukti,'')='' then pengeluaranheader.modifiedby else pgp.modifiedby end) as modifiedby, 
                    (case when isnull(pgp.nobukti,'')='' then pengeluaranheader.created_at else pgp.created_at end) as created_at, 
                    (case when isnull(pgp.nobukti,'')='' then pengeluaranheader.updated_at else pgp.updated_at end) as updated_at
                ")
            )
            ->distinct('pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("saldopengeluarandetail as pengeluarandetail with (readuncommitted)"), 'pengeluarandetail.nobukti', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("pencairangiropengeluaranheader as pgp with (readuncommitted)"), 'pgp.pengeluaran_nobukti', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'pgp.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluaranheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pengeluaranheader.alatbayar_id', 'alatbayar.id')
            ->whereRaw("MONTH(pengeluaranheader.tglbukti) = $month")
            ->whereRaw("YEAR(pengeluaranheader.tglbukti) = $year")
            ->where('pengeluaranheader.alatbayar_id', $alatBayar->id);


        DB::table($templist)->insertUsing([
            'pengeluaran_nobukti',
            'tglbukti_giro',
            'id',
            'dibayarke',
            'urlpengeluaran',
            'bank_id',
            'transferkeac',
            'alatbayar_id',
            'nobukti',
            'tglbukti',
            'statusapproval',
            'tgljatuhtempo',
            'nominal',
            'modifiedby',
            'created_at',
            'updated_at',

        ], $query2);


        $url = config('app.url_fe') . 'pindahbuku';
        $query3 = DB::table("pindahbuku")->from(DB::raw("pindahbuku with (readuncommitted)"))
            ->select(
                DB::raw("
                    pindahbuku.nobukti as pengeluaran_nobukti,
                    pindahbuku.tglbukti as tglbukti_giro,
                    pindahbuku.id, 
                    '<a href=$petik" . $url . "?tgldari='+(format(pindahbuku.tglbukti,'yyyy-MM')+'-1')+'&tglsampai='+(format(pindahbuku.tglbukti,'yyyy-MM')+'-31')+'&nobukti='+pindahbuku.nobukti+'$petik 
                    class=$petik link-color $petik target=$petik _blank $petik>'+pindahbuku.nobukti+'</a>' as urlpengeluaran,
                    bank.namabank as bank_id, 
                    alatbayar.namaalatbayar as alatbayar_id, 
                    pgp.nobukti, 
                    pgp.tglbukti, 
				    parameter.memo as statusapproval,
                    CONVERT(date, GETDATE()) as tgljatuhtempo, 
                    pindahbuku.nominal,                
                    (case when isnull(pgp.nobukti,'')='' then pindahbuku.modifiedby else pgp.modifiedby end) as modifiedby, 
                    (case when isnull(pgp.nobukti,'')='' then pindahbuku.created_at else pgp.created_at end) as created_at, 
                    (case when isnull(pgp.nobukti,'')='' then pindahbuku.updated_at else pgp.updated_at end) as updated_at
                ")
            )
            ->leftJoin(DB::raw("pencairangiropengeluaranheader as pgp with (readuncommitted)"), 'pgp.pengeluaran_nobukti', 'pindahbuku.nobukti')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'pgp.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pindahbuku.bankdari_id', 'bank.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pindahbuku.alatbayar_id', 'alatbayar.id')
            ->whereRaw("MONTH(pindahbuku.tglbukti) = $month")
            ->whereRaw("YEAR(pindahbuku.tglbukti) = $year")
            ->where('pindahbuku.alatbayar_id', $alatBayar->id);
        DB::table($templist)->insertUsing([
            'pengeluaran_nobukti',
            'tglbukti_giro',
            'id',
            'urlpengeluaran',
            'bank_id',
            'alatbayar_id',
            'nobukti',
            'tglbukti',
            'statusapproval',
            'tgljatuhtempo',
            'nominal',
            'modifiedby',
            'created_at',
            'updated_at',

        ], $query3);

        $query = DB::table("$templist")
            ->select(
                'pengeluaran_nobukti',
                'tglbukti_giro',
                'dibayarke',
                'urlpengeluaran',
                'bank_id',
                'transferkeac',
                'alatbayar_id',
                'nobukti',
                'tglbukti',
                'statusapproval',
                'tgljatuhtempo',
                'nominal',
                'modifiedby',
                'created_at',
                'updated_at',
            );

        return $query;
    }
    public function getPenerimaan()
    {
        $tempDetail = '##tempDetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempDetail, function ($table) {
            $table->string('nobukti', 300)->nullable();
            $table->date('tgljatuhtempo')->nullable();
            $table->integer('bank_id')->nullable();
            $table->double('nominal')->nullable();
        });
        $getDetail = DB::table("penerimaangirodetail")->from(DB::raw("penerimaangirodetail with (readuncommitted)"))
            ->select('nobukti', 'tgljatuhtempo', 'bank_id', DB::raw("sum(nominal) as nominal"))
            ->groupBy('nobukti', 'tgljatuhtempo', 'bank_id');
        DB::table($tempDetail)->insertUsing([
            'nobukti',
            'tgljatuhtempo',
            'bank_id',
            'nominal',
        ], $getDetail);

        $templist = '##templist' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($templist, function ($table) {
            $table->string('pengeluaran_nobukti', 300)->nullable();
            $table->date('tglbukti_giro')->nullable();
            $table->integer('id')->nullable();
            $table->longText('dibayarke')->nullable();
            $table->longText('urlpengeluaran')->nullable();
            $table->longText('bank_id')->nullable();
            $table->longText('transferkeac')->nullable();
            $table->longText('alatbayar_id')->nullable();
            $table->longText('nobukti')->nullable();
            $table->date('tglbukti')->nullable();
            $table->longText('statusapproval')->nullable();
            $table->date('tgljatuhtempo')->nullable();
            $table->double('nominal')->nullable();
            $table->longText('modifiedby')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
        $petik = '"';
        $url = config('app.url_fe') . 'penerimaangiroheader';

        $query1 = DB::table("penerimaangiroheader")->from(DB::raw("penerimaangiroheader with (readuncommitted)"))
            ->select(
                DB::raw("
                        penerimaangiroheader.nobukti as pengeluaran_nobukti,
                        penerimaangiroheader.tglbukti as tglbukti_giro,
                        penerimaangiroheader.id, 
                        '<a href=$petik" . $url . "?tgldari='+(format(penerimaangiroheader.tglbukti,'yyyy-MM')+'-1')+'&tglsampai='+(format(penerimaangiroheader.tglbukti,'yyyy-MM')+'-31')+'&nobukti='+penerimaangiroheader.nobukti+'$petik 
                         class=$petik link-color $petik target=$petik _blank $petik>'+penerimaangiroheader.nobukti+'</a>' as urlpengeluaran,
                        bank.namabank as bank_id, 
                        'GIRO' as alatbayar_id,
                        pgp.nobukti,
                        pgp.tglbukti, 
                        penerimaangirodetail.tgljatuhtempo,
                        penerimaangirodetail.nominal,
                        (case when isnull(pgp.nobukti,'')='' then penerimaangiroheader.modifiedby else pgp.modifiedby end) as modifiedby, 
                        (case when isnull(pgp.nobukti,'')='' then penerimaangiroheader.created_at else pgp.created_at end) as created_at, 
                        (case when isnull(pgp.nobukti,'')='' then penerimaangiroheader.updated_at else pgp.updated_at end) as updated_at
                     ")
            )
            ->leftJoin(DB::raw("penerimaanheader as pgp with (readuncommitted)"), 'pgp.penerimaangiro_nobukti', 'penerimaangiroheader.nobukti')
            ->leftJoin(DB::raw("$tempDetail as penerimaangirodetail with (readuncommitted)"), 'penerimaangirodetail.nobukti', 'penerimaangiroheader.nobukti')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'penerimaangirodetail.bank_id', 'bank.id');
        DB::table($templist)->insertUsing([
            'pengeluaran_nobukti',
            'tglbukti_giro',
            'id',
            'urlpengeluaran',
            'bank_id',
            'alatbayar_id',
            'nobukti',
            'tglbukti',
            'tgljatuhtempo',
            'nominal',
            'modifiedby',
            'created_at',
            'updated_at',

        ], $query1);

        $query = DB::table("$templist")
            ->select(
                'pengeluaran_nobukti',
                'tglbukti_giro',
                'id',
                'urlpengeluaran',
                'bank_id',
                DB::raw("'' as transferkeac"),
                'alatbayar_id',
                'nobukti',
                'tglbukti',
                DB::raw("'' as statusapproval"),
                'tgljatuhtempo',
                'nominal',
                'modifiedby',
                'created_at',
                'updated_at',
            );

        return $query;
    }
    public function selectColumns()
    {
        $periode = request()->periode ?? date('m-Y');
        $month = substr($periode, 0, 2);
        $year = substr($periode, 3);

        $alatBayar = AlatBayar::from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'GIRO')->first();
        $alatBayarCheck = AlatBayar::from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'CHECK')->first();
        $query1 = DB::table($this->anotherTable)->from(DB::raw("pengeluaranheader with (readuncommitted)"))
            ->select(
                DB::raw("pengeluaranheader.nobukti as pengeluaran_nobukti,pengeluaranheader.id, 
                pengeluaranheader.dibayarke, bank.namabank as bank_id, pengeluaranheader.transferkeac, 
                pengeluaranheader.modifiedby, pengeluaranheader.created_at,pengeluaranheader.updated_at, alatbayar.namaalatbayar as alatbayar_id, pgp.nobukti, pgp.tglbukti, parameter.memo as statusapproval, (SELECT (SUM(pengeluarandetail.nominal)) FROM pengeluarandetail 
        WHERE pengeluarandetail.nobukti= pengeluaranheader.nobukti and pengeluaranheader.alatbayar_id=$alatBayar->id) as nominal")
            )
            ->distinct('pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("pengeluarandetail with (readuncommitted)"), 'pengeluarandetail.nobukti', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("pencairangiropengeluaranheader as pgp with (readuncommitted)"), 'pgp.pengeluaran_nobukti', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'pgp.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluaranheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pengeluaranheader.alatbayar_id', 'alatbayar.id')
            ->whereRaw("MONTH(pengeluaranheader.tglbukti) = $month")
            ->whereRaw("YEAR(pengeluaranheader.tglbukti) = $year")
            ->where('pengeluaranheader.alatbayar_id', $alatBayar->id);

        $query2 = DB::table($this->anotherTable)->from(DB::raw("saldopengeluaranheader as pengeluaranheader with (readuncommitted) "))
            ->select(
                DB::raw("pengeluaranheader.nobukti as pengeluaran_nobukti,pengeluaranheader.id, pengeluaranheader.dibayarke, bank.namabank as bank_id, pengeluaranheader.transferkeac, pengeluaranheader.modifiedby, pengeluaranheader.created_at,pengeluaranheader.updated_at, alatbayar.namaalatbayar as alatbayar_id, pgp.nobukti, pgp.tglbukti, parameter.memo as statusapproval, (SELECT (SUM(pengeluarandetail.nominal)) FROM saldopengeluarandetail as pengeluarandetail
        WHERE pengeluarandetail.nobukti= pengeluaranheader.nobukti and pengeluaranheader.alatbayar_id=$alatBayar->id) as nominal")
            )
            ->distinct('pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("saldopengeluarandetail as pengeluarandetail with (readuncommitted)"), 'pengeluarandetail.nobukti', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("pencairangiropengeluaranheader as pgp with (readuncommitted)"), 'pgp.pengeluaran_nobukti', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'pgp.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluaranheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pengeluaranheader.alatbayar_id', 'alatbayar.id')
            ->whereRaw("MONTH(pengeluaranheader.tglbukti) = $month")
            ->whereRaw("YEAR(pengeluaranheader.tglbukti) = $year")
            ->where('pengeluaranheader.alatbayar_id', $alatBayar->id);



        $query3 = DB::table(DB::raw("({$query1->toSql()} UNION ALL {$query2->toSql()}) as V"))
            ->mergeBindings($query1)
            ->mergeBindings($query2);



        $templist = '##templist' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($templist, function ($table) {
            $table->string('pengeluaran_nobukti', 300)->nullable();
            $table->integer('id')->nullable();
            $table->longText('dibayarke')->nullable();
            $table->longText('bank_id')->nullable();
            $table->longText('transferkeac')->nullable();
            $table->longText('alatbayar_id')->nullable();
            $table->longText('nobukti')->nullable();
            $table->date('tglbukti')->nullable();
            $table->longText('statusapproval')->nullable();
            $table->double('nominal')->nullable();
            $table->longText('modifiedby')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });



        DB::table($templist)->insertUsing([
            'pengeluaran_nobukti',
            'id',
            'dibayarke',
            'bank_id',
            'transferkeac',
            'modifiedby',
            'created_at',
            'updated_at',
            'alatbayar_id',
            'nobukti',
            'tglbukti',
            'statusapproval',
            'nominal',

        ], $query3);

        $query = DB::table($templist)->from(DB::raw($templist . " AS pengeluaranheader "))
            ->select([
                'pengeluaranheader.pengeluaran_nobukti',
                'pengeluaranheader.id',
                'pengeluaranheader.dibayarke',
                'pengeluaranheader.bank_id',
                'pengeluaranheader.transferkeac',
                'pengeluaranheader.alatbayar_id',
                'pengeluaranheader.nobukti',
                'pengeluaranheader.tglbukti',
                'pengeluaranheader.statusapproval',
                'pengeluaranheader.nominal',
                'pengeluaranheader.modifiedby',
                'pengeluaranheader.created_at',
                'pengeluaranheader.updated_at',
            ]);

        return $query;
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->string('pengeluaran_nobukti', 300)->nullable();
            $table->integer('id')->nullable();
            $table->longText('dibayarke')->nullable();
            $table->longText('bank_id')->nullable();
            $table->longText('transferkeac')->nullable();
            $table->longText('alatbayar_id')->nullable();
            $table->longText('nobukti')->nullable();
            $table->date('tglbukti')->nullable();
            $table->longText('statusapproval')->nullable();
            $table->double('nominal')->nullable();
            $table->longText('modifiedby')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = $this->selectColumns();
        $this->sort($query, 'pengeluaranheader');
        $models = $this->filter($query, 'pengeluaranheader');
        DB::table($temp)->insertUsing([
            'pengeluaran_nobukti', 'id', 'dibayarke', 'bank_id', 'transferkeac', 'alatbayar_id', 'nobukti', 'tglbukti', 'statusapproval', 'nominal', 'modifiedby', 'created_at', 'updated_at'
        ], $models);


        return  $temp;
    }

    // 'a.pengeluaran_nobukti',
    // 'a.id',
    // 'a.dibayarke',
    // 'a.bank_id',
    // 'a.transferkeac',
    // 'a.alatbayar_id',
    // 'a.nobukti',
    // 'a.tglbukti',
    // 'a.statusapproval',
    // 'a.nominal',
    // 'a.modifiedby',
    // 'a.created_at',
    // 'a.updated_at',
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

                        if ($filters['field'] == 'nominal') {
                            $query = $query->whereRaw("format(a.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglbukti') {
                            $query->whereRaw("format(a.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglbukti_giro') {
                            $query->whereRaw("format(a.tglbukti_giro, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'urlpengeluaran') {
                            $query = $query->where('a.pengeluaran_nobukti', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->whereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":

                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {

                            if ($filters['field'] == 'nominal') {
                                $query = $query->orWhereRaw("format(a.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti') {
                                $query->orWhereRaw("format(a.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti_giro') {
                                $query->orWhereRaw("format(a.tglbukti_giro, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'urlpengeluaran') {
                                $query = $query->orWhere('a.pengeluaran_nobukti', 'LIKE', "%$filters[data]%");
                            } else {
                                // $query = $query->orWhere($this->anotherTable . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function processStore(array $data)
    {

        $group = 'PENCAIRAN GIRO BUKTI';
        $subGroup = 'PENCAIRAN GIRO BUKTI';

        $format = DB::table('parameter')
            ->where('grp', $group)
            ->where('subgrp', $subGroup)
            ->first();

        if ($data['status'] == 591) {

            $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

            for ($i = 0; $i < count($data['nobukti']); $i++) {

                $cekAsal = substr($data['nobukti'][$i], 0, 3);
                if ($cekAsal == 'PBT') {
                    $pindahBuku = PindahBuku::from(DB::raw("pindahbuku with (readuncommitted)"))->where('nobukti', $data['nobukti'][$i])->first();
                    if ($pindahBuku != '') {
                        $cekPencairan = PencairanGiroPengeluaranHeader::from(DB::raw("pencairangiropengeluaranheader with (readuncommitted)"))->where('pengeluaran_nobukti', $pindahBuku->nobukti)->first();
                        if ($cekPencairan != null) {
                            $getJurnalHeader = JurnalUmumHeader::where('nobukti', $cekPencairan->nobukti)->first();
                            $pencairanGiro = $this->processDestroy($cekPencairan->id);
                            if (isset($getJurnalHeader)) {
                                (new JurnalUmumHeader())->processDestroy($getJurnalHeader->id, 'PENCAIRAN GIRO PENGELUARAN DETAIL');
                            }
                        } else {

                            $pencairanGiro = new PencairanGiroPengeluaranHeader();
                            $pencairanGiro->nobukti = (new RunningNumberService)->get($group, $subGroup, $pencairanGiro->getTable(), date('Y-m-d', strtotime($pindahBuku->tgljatuhtempo)));
                            $pencairanGiro->tglbukti = date('Y-m-d', strtotime($pindahBuku->tgljatuhtempo));
                            $pencairanGiro->pengeluaran_nobukti = $pindahBuku->nobukti;
                            $pencairanGiro->statusapproval = $statusApproval->id;
                            $pencairanGiro->userapproval = '';
                            $pencairanGiro->tglapproval = '';
                            $pencairanGiro->modifiedby = auth('api')->user()->name;
                            $pencairanGiro->info = html_entity_decode(request()->info);
                            $pencairanGiro->statusformat = $format->id;

                            if (!$pencairanGiro->save()) {
                                throw new \Exception("Error storing pencairan giro pengeluaran header.");
                            }
                            $pencairanGiroLogTrail = (new LogTrail())->processStore([
                                'namatabel' => strtoupper($pencairanGiro->getTable()),
                                'postingdari' => 'ENTRY PENCAIRAN GIRO PENGELUARAN HEADER',
                                'idtrans' => $pencairanGiro->id,
                                'nobuktitrans' => $pencairanGiro->nobukti,
                                'aksi' => 'ENTRY',
                                'datajson' => $pencairanGiro->toArray(),
                                'modifiedby' => auth('api')->user()->user
                            ]);



                            $getCoaBank = DB::table('bank')->from(DB::raw("bank with (readuncommitted)"))->where('id', $pindahBuku->bankdari_id)->first();
                            $pencairanGiroDetails = [];
                            $coadebet_detail = [];
                            $coakredit_detail = [];
                            $keterangan_detail = [];
                            $nominal_detail = [];
                            $alatBayar = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))
                                ->where('grp', 'ALAT BAYAR GIRO')
                                ->where('grp', 'ALAT BAYAR GIRO')
                                ->first()->text;

                            $pencairanGiroDetail = (new PencairanGiroPengeluaranDetail())->processStore($pencairanGiro, [
                                'alatbayar_id' => $alatBayar,
                                'nowarkat' => $pindahBuku->nowarkat,
                                'tgljatuhtempo' => $pindahBuku->tgljatuhtempo,
                                'nominal' => $pindahBuku->nominal,
                                'coadebet' => $pindahBuku->coakredit,
                                'coakredit' => $getCoaBank->coa,
                                'keterangan' => $pindahBuku->keterangan,
                                'bulanbeban' => '',
                            ]);

                            $coadebet_detail[] = $pindahBuku->coakredit;
                            $coakredit_detail[] = $getCoaBank->coa;
                            $keterangan_detail[] = $pindahBuku->keterangan;
                            $nominal_detail[] = $pindahBuku->nominal;

                            $pencairanGiroDetails[] = $pencairanGiroDetail->toArray();

                            (new LogTrail())->processStore([
                                'namatabel' => 'PENCAIRANGIROPENGELUARANDETAIL',
                                'postingdari' => 'ENTRY PENCAIRAN GIRO PENGELUARAN DETAIL',
                                'idtrans' => $pencairanGiroLogTrail['id'],
                                'nobuktitrans' => $pencairanGiro->nobukti,
                                'aksi' => 'ENTRY',
                                'datajson' => $pencairanGiroDetails,
                                'modifiedby' => auth('api')->user()->name
                            ]);

                            $jurnalRequest = [
                                'tanpaprosesnobukti' => 1,
                                'nobukti' => $pencairanGiro->nobukti,
                                'tglbukti' => date('Y-m-d', strtotime($pindahBuku->tgljatuhtempo)),
                                'postingdari' => "ENTRY PENCAIRAN GIRO PENGELUARAN",
                                'statusformat' => "0",
                                'coakredit_detail' => $coakredit_detail,
                                'coadebet_detail' => $coadebet_detail,
                                'nominal_detail' => $nominal_detail,
                                'keterangan_detail' => $keterangan_detail
                            ];
                            (new JurnalUmumHeader())->processStore($jurnalRequest);
                        }
                    }
                } else {

                    $pengeluaran = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))
                        ->select('nobukti', 'alatbayar_id', 'bank_id')->where('nobukti', $data['nobukti'][$i])->first();
                    if ($pengeluaran == null) {
                        $saldoPengeluaran = SaldoPengeluaranHeader::from(DB::raw("saldopengeluaranheader with (readuncommitted)"))
                            ->select('nobukti', 'alatbayar_id')->where('nobukti', $data['nobukti'][$i])->first();

                        $cekPencairanSaldo = PencairanGiroPengeluaranHeader::from(DB::raw("pencairangiropengeluaranheader with (readuncommitted)"))->where('pengeluaran_nobukti', $saldoPengeluaran->nobukti)->first();

                        if ($cekPencairanSaldo != null) {
                            $getJurnalHeader = JurnalUmumHeader::where('nobukti', $cekPencairanSaldo->nobukti)->first();
                            $pencairanGiro = $this->processDestroy($cekPencairanSaldo->id);
                            if (isset($getJurnalHeader)) {
                                (new JurnalUmumHeader())->processDestroy($getJurnalHeader->id, 'PENCAIRAN GIRO PENGELUARAN DETAIL');
                            }
                        } else {

                            $saldoPengeluaranDetail = saldopengeluarandetail::from(DB::raw("saldopengeluarandetail with (readuncommitted)"))->where('nobukti', $data['nobukti'][$i])->get();

                            $pencairanGiro = new PencairanGiroPengeluaranHeader();
                            $pencairanGiro->nobukti = (new RunningNumberService)->get($group, $subGroup, $pencairanGiro->getTable(), date('Y-m-d', strtotime($saldoPengeluaranDetail[0]['tgljatuhtempo'])));
                            $pencairanGiro->tglbukti = date('Y-m-d', strtotime($saldoPengeluaranDetail[0]['tgljatuhtempo']));
                            $pencairanGiro->pengeluaran_nobukti = $saldoPengeluaran->nobukti;
                            $pencairanGiro->statusapproval = $statusApproval->id;
                            $pencairanGiro->userapproval = '';
                            $pencairanGiro->tglapproval = '';
                            $pencairanGiro->modifiedby = auth('api')->user()->name;
                            $pencairanGiro->info = html_entity_decode(request()->info);
                            $pencairanGiro->statusformat = $format->id;

                            if (!$pencairanGiro->save()) {
                                throw new \Exception("Error storing pencairan giro pengeluaran header.");
                            }
                            $pencairanGiroLogTrail = (new LogTrail())->processStore([
                                'namatabel' => strtoupper($pencairanGiro->getTable()),
                                'postingdari' => 'ENTRY PENCAIRAN GIRO PENGELUARAN HEADER',
                                'idtrans' => $pencairanGiro->id,
                                'nobuktitrans' => $pencairanGiro->nobukti,
                                'aksi' => 'ENTRY',
                                'datajson' => $pencairanGiro->toArray(),
                                'modifiedby' => auth('api')->user()->user
                            ]);



                            // STORE DETAIL
                            $getCoaBank = DB::table('bank')->from(DB::raw("bank with (readuncommitted)"))->where('id', $pengeluaran->bank_id)->first();
                            $saldoPengeluaranDetail = saldopengeluarandetail::from(DB::raw("saldopengeluarandetail with (readuncommitted)"))->where('nobukti', $data['nobukti'][$i])->get();

                            $pencairanGiroDetails = [];
                            $coadebet_detail = [];
                            $coakredit_detail = [];
                            $keterangan_detail = [];
                            $nominal_detail = [];

                            $tglLunas = '';
                            foreach ($saldoPengeluaranDetail as $index => $value) {

                                $pencairanGiroDetail = (new PencairanGiroPengeluaranDetail())->processStore($pencairanGiro, [
                                    'alatbayar_id' => $saldoPengeluaran->alatbayar_id,
                                    'nowarkat' => $value->nowarkat,
                                    'tgljatuhtempo' => $value->tgljatuhtempo,
                                    'nominal' => $value->nominal,
                                    'coadebet' => $value->coakredit,
                                    'coakredit' => $getCoaBank->coa,
                                    'keterangan' => $value->keterangan,
                                    'bulanbeban' => $value->bulanbeban,
                                ]);

                                $coadebet_detail[$index] = $value->coakredit;
                                $coakredit_detail[$index] = $getCoaBank->coa;
                                $keterangan_detail[$index] = $value->keterangan;
                                $nominal_detail[$index] = $value->nominal;

                                $pencairanGiroDetails[] = $pencairanGiroDetail->toArray();

                                $tglLunas = date('Y-m-d', strtotime($value->tgljatuhtempo));
                            }


                            (new LogTrail())->processStore([
                                'namatabel' => 'PENCAIRANGIROPENGELUARANDETAIL',
                                'postingdari' => 'ENTRY PENCAIRAN GIRO PENGELUARAN DETAIL',
                                'idtrans' => $pencairanGiroLogTrail['id'],
                                'nobuktitrans' => $pencairanGiro->nobukti,
                                'aksi' => 'ENTRY',
                                'datajson' => $pencairanGiroDetails,
                                'modifiedby' => auth('api')->user()->name
                            ]);

                            $jurnalRequest = [
                                'tanpaprosesnobukti' => 1,
                                'nobukti' => $pencairanGiro->nobukti,
                                'tglbukti' => $tglLunas,
                                'postingdari' => "ENTRY PENCAIRAN GIRO PENGELUARAN",
                                'statusformat' => "0",
                                'coakredit_detail' => $coakredit_detail,
                                'coadebet_detail' => $coadebet_detail,
                                'nominal_detail' => $nominal_detail,
                                'keterangan_detail' => $keterangan_detail
                            ];
                            (new JurnalUmumHeader())->processStore($jurnalRequest);
                        }
                    } else {

                        $cekPencairan = PencairanGiroPengeluaranHeader::from(DB::raw("pencairangiropengeluaranheader with (readuncommitted)"))->where('pengeluaran_nobukti', $pengeluaran->nobukti)->first();
                        if ($cekPencairan != null) {
                            $getJurnalHeader = JurnalUmumHeader::where('nobukti', $cekPencairan->nobukti)->first();
                            $pencairanGiro = $this->processDestroy($cekPencairan->id);
                            if (isset($getJurnalHeader)) {
                                (new JurnalUmumHeader())->processDestroy($getJurnalHeader->id, 'PENCAIRAN GIRO PENGELUARAN DETAIL');
                            }
                        } else {

                            $pengeluaranDetail = PengeluaranDetail::from(DB::raw("pengeluarandetail with (readuncommitted)"))->where('nobukti', $data['nobukti'][$i])->get();

                            $pencairanGiro = new PencairanGiroPengeluaranHeader();
                            $pencairanGiro->nobukti = (new RunningNumberService)->get($group, $subGroup, $pencairanGiro->getTable(), date('Y-m-d', strtotime($pengeluaranDetail[0]['tgljatuhtempo'])));
                            $pencairanGiro->tglbukti = date('Y-m-d', strtotime($pengeluaranDetail[0]['tgljatuhtempo']));
                            $pencairanGiro->pengeluaran_nobukti = $pengeluaran->nobukti;
                            $pencairanGiro->statusapproval = $statusApproval->id;
                            $pencairanGiro->userapproval = '';
                            $pencairanGiro->tglapproval = '';
                            $pencairanGiro->modifiedby = auth('api')->user()->name;
                            $pencairanGiro->info = html_entity_decode(request()->info);
                            $pencairanGiro->statusformat = $format->id;

                            if (!$pencairanGiro->save()) {
                                throw new \Exception("Error storing pencairan giro pengeluaran header.");
                            }
                            $pencairanGiroLogTrail = (new LogTrail())->processStore([
                                'namatabel' => strtoupper($pencairanGiro->getTable()),
                                'postingdari' => 'ENTRY PENCAIRAN GIRO PENGELUARAN HEADER',
                                'idtrans' => $pencairanGiro->id,
                                'nobuktitrans' => $pencairanGiro->nobukti,
                                'aksi' => 'ENTRY',
                                'datajson' => $pencairanGiro->toArray(),
                                'modifiedby' => auth('api')->user()->user
                            ]);



                            $getCoaBank = DB::table('bank')->from(DB::raw("bank with (readuncommitted)"))->where('id', $pengeluaran->bank_id)->first();

                            // STORE DETAIL


                            $pencairanGiroDetails = [];
                            $coadebet_detail = [];
                            $coakredit_detail = [];
                            $keterangan_detail = [];
                            $nominal_detail = [];
                            $tglLunas = '';

                            foreach ($pengeluaranDetail as $index => $value) {

                                $pencairanGiroDetail = (new PencairanGiroPengeluaranDetail())->processStore($pencairanGiro, [
                                    'alatbayar_id' => $pengeluaran->alatbayar_id,
                                    'nowarkat' => $value->nowarkat,
                                    'tgljatuhtempo' => $value->tgljatuhtempo,
                                    'nominal' => $value->nominal,
                                    'coadebet' => $value->coakredit,
                                    'coakredit' => $getCoaBank->coa,
                                    'keterangan' => $value->keterangan,
                                    'bulanbeban' => $value->bulanbeban,
                                ]);

                                $coadebet_detail[$index] = $value->coakredit;
                                $coakredit_detail[$index] = $getCoaBank->coa;
                                $keterangan_detail[$index] = $value->keterangan;
                                $nominal_detail[$index] = $value->nominal;

                                $pencairanGiroDetails[] = $pencairanGiroDetail->toArray();
                                $tglLunas = date('Y-m-d', strtotime($value->tgljatuhtempo));
                            }


                            (new LogTrail())->processStore([
                                'namatabel' => 'PENCAIRANGIROPENGELUARANDETAIL',
                                'postingdari' => 'ENTRY PENCAIRAN GIRO PENGELUARAN DETAIL',
                                'idtrans' => $pencairanGiroLogTrail['id'],
                                'nobuktitrans' => $pencairanGiro->nobukti,
                                'aksi' => 'ENTRY',
                                'datajson' => $pencairanGiroDetails,
                                'modifiedby' => auth('api')->user()->name
                            ]);

                            $jurnalRequest = [
                                'tanpaprosesnobukti' => 1,
                                'nobukti' => $pencairanGiro->nobukti,
                                'tglbukti' => $tglLunas,
                                'postingdari' => "ENTRY PENCAIRAN GIRO PENGELUARAN",
                                'statusformat' => "0",
                                'coakredit_detail' => $coakredit_detail,
                                'coadebet_detail' => $coadebet_detail,
                                'nominal_detail' => $nominal_detail,
                                'keterangan_detail' => $keterangan_detail
                            ];
                            (new JurnalUmumHeader())->processStore($jurnalRequest);
                        }
                    }
                }
            }
        } else {
            $coa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'JURNAL PIUTANG GIRO')->where('subgrp', 'JURNAL PIUTANG GIRO')->first()->text;
            for ($i = 0; $i < count($data['nobukti']); $i++) {
                $cekPencairan = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))->where('penerimaangiro_nobukti', $data['nobukti'][$i])->first();
                if ($cekPencairan != '') {
                    (new PenerimaanHeader())->processDestroy($cekPencairan->id, 'PENCAIRAN GIRO');
                } else {
                    $dataHeader = DB::table("penerimaangiroheader")->from(DB::raw("penerimaangiroheader with (readuncommitted)"))
                        ->where('nobukti', $data['nobukti'][$i])->first();

                    $dataDetail = DB::table("penerimaangirodetail")->from(DB::raw("penerimaangirodetail with (readuncommitted)"))->where('nobukti', $data['nobukti'][$i])->get();

                    $alatBayar = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))
                        ->where('grp', 'ALAT BAYAR GIRO')
                        ->where('grp', 'ALAT BAYAR GIRO')
                        ->first()->text;

                    $noWarkat = [];
                    $tglJatuhTempo = [];
                    $nominalDetail = [];
                    $coaKredit = [];
                    $keteranganDetail = [];
                    $bank_id = '';
                    $tglLunas = '';
                    foreach ($dataDetail as $index => $value) {
                        $noWarkat[] = $value->nowarkat;
                        $tglJatuhTempo[] = $value->tgljatuhtempo;
                        $nominalDetail[] = $value->nominal;
                        $coaKredit[] = $coa;
                        $keteranganDetail[] = $value->keterangan;
                        $bank_id = $value->bank_id;
                        $tglLunas = $value->tgljatuhtempo;
                    }
                    $penerimaanRequest = [
                        'tglbukti' => $tglLunas,
                        'pelanggan_id' => 0,
                        'agen_id' => $dataHeader->agen_id,
                        'alatbayar_id' => $alatBayar,
                        'postingdari' => 'ENTRY PENCAIRAN GIRO',
                        'diterimadari' => 'PENCAIRAN GIRO',
                        'penerimaangiro_nobukti' => $dataHeader->nobukti,
                        'tgllunas' => $tglLunas,
                        'bank_id' => $bank_id,
                        'nowarkat' => $noWarkat,
                        'tgljatuhtempo' => $tglJatuhTempo,
                        'nominal_detail' => $nominalDetail,
                        'coakredit' => $coaKredit,
                        'keterangan_detail' => $keteranganDetail,

                    ];
                    $penerimaanHeader = (new PenerimaanHeader())->processStore($penerimaanRequest);
                }
            }
        }
        return $data;
    }

    public function processDestroy($id, $postingDari = ''): PencairanGiroPengeluaranHeader
    {
        $getDetail = PencairanGiroPengeluaranDetail::lockForUpdate()->where('pencairangiropengeluaran_id', $id)->get();

        $pencairanGiro = new PencairanGiroPengeluaranHeader();
        $pencairanGiro = $pencairanGiro->lockAndDestroy($id);

        $pencairanGiroLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($pencairanGiro->getTable()),
            'postingdari' => 'DELETE PENCAIRAN GIRO PENGELUARAN HEADER',
            'idtrans' => $pencairanGiro->id,
            'nobuktitrans' => $pencairanGiro->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $pencairanGiro->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        (new LogTrail())->processStore([
            'namatabel' => 'PENCAIRANGIROPENGELUARANDETAIL',
            'postingdari' => 'DELETE PENCAIRAN GIRO PENGELUARAN DETAIL',
            'idtrans' => $pencairanGiroLogTrail['id'],
            'nobuktitrans' => $pencairanGiro->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $getDetail->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $pencairanGiro;
    }

    public function cekValidasi($nobukti)
    {

        $error = new Error();
        $keteranganerror = $error->cekKeteranganError('SAPP') ?? '';
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $jurnalpusat = DB::table('jurnalumumpusatheader')
            ->from(
                DB::raw("jurnalumumpusatheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($jurnalpusat)) {
            $data = [
                'kondisi' => true,
                'keterangan' => '<br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror,
                'kodeerror' => 'SAPP'
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

    public function processUpdateTglJatuhtempo(array $data)
    {
        if ($data['status'] == 592) {

            for ($i = 0; $i < count($data['nobukti']); $i++) {
                $getPelunasan = DB::table("pelunasanpiutangheader")->from(DB::raw("pelunasanpiutangheader with (readuncommitted)"))
                    ->where('penerimaangiro_nobukti', $data['nobukti'][$i])
                    ->first();

                $tglJatuhTempo = '';
                $tglBukti = '';
                if ($getPelunasan != '') {
                    $tglJatuhTempo = $getPelunasan->tglcair;
                    $tglBukti = $getPelunasan->tglbukti;
                    DB::table("pelunasanpiutangheader")
                        ->where('penerimaangiro_nobukti', $data['nobukti'][$i])->update([
                            'tglcair' => date('Y-m-d', strtotime($data['tgljatuhtempo'])),
                        ]);
                    DB::table("penerimaangirodetail")
                        ->where('nobukti', $data['nobukti'][$i])->update([
                            'tgljatuhtempo' => date('Y-m-d', strtotime($data['tgljatuhtempo'])),
                        ]);
                } else {
                    $getTglJatuhTempo = DB::table("penerimaangirodetail")->from(DB::raw("penerimaangirodetail with (readuncommitted)"))
                        ->where('nobukti', $data['nobukti'][$i])
                        ->first();

                    $getTglBukti = DB::table("penerimaangiroheader")->from(DB::raw("penerimaangiroheader with (readuncommitted)"))
                        ->where('nobukti', $data['nobukti'][$i])
                        ->first();
                    $tglJatuhTempo = $getTglJatuhTempo->tgljatuhtempo;
                    $tglBukti = $getTglBukti->tglbukti;

                    DB::table("penerimaangirodetail")
                        ->where('nobukti', $data['nobukti'][$i])->update([
                            'tgljatuhtempo' => date('Y-m-d', strtotime($data['tgljatuhtempo'])),
                        ]);
                }

                $dataHistory = [
                    'nobukti' => $data['nobukti'][$i],
                    'tglbukti' => $tglBukti,
                    'tgljatuhtempo' => $data['tgljatuhtempo'],
                    'tgljatuhtempolama' => $tglJatuhTempo,
                ];
                (new HistoryTglJatuhTempoGiro())->processStore($dataHistory);
            }
        } else {
            for ($i = 0; $i < count($data['nobukti']); $i++) {
                $cekAsal = substr($data['nobukti'][$i], 0, 3);
                if ($cekAsal == 'PBT') {
                    $getTglBukti = DB::table("pindahbuku")->from(DB::raw("pindahbuku with (readuncommitted)"))
                        ->where('nobukti', $data['nobukti'][$i])
                        ->first();
                    $tglJatuhTempo = $getTglBukti->tgljatuhtempo;
                    $tglBukti = $getTglBukti->tglbukti;

                    DB::table("pindahbuku")
                        ->where('nobukti', $data['nobukti'][$i])->update([
                            'tgljatuhtempo' => date('Y-m-d', strtotime($data['tgljatuhtempo'])),
                        ]);
                } else {

                    $getTglJatuhTempo = DB::table("pengeluarandetail")->from(DB::raw("pengeluarandetail with (readuncommitted)"))
                        ->where('nobukti', $data['nobukti'][$i])
                        ->first();

                    $getTglBukti = DB::table("pengeluaranheader")->from(DB::raw("pengeluaranheader with (readuncommitted)"))
                        ->where('nobukti', $data['nobukti'][$i])
                        ->first();
                    $tglJatuhTempo = $getTglJatuhTempo->tgljatuhtempo;
                    $tglBukti = $getTglBukti->tglbukti;

                    DB::table("pengeluarandetail")
                        ->where('nobukti', $data['nobukti'][$i])->update([
                            'tgljatuhtempo' => date('Y-m-d', strtotime($data['tgljatuhtempo'])),
                        ]);
                }
                $dataHistory = [
                    'nobukti' => $data['nobukti'][$i],
                    'tglbukti' => $tglBukti,
                    'tgljatuhtempo' => $data['tgljatuhtempo'],
                    'tgljatuhtempolama' => $tglJatuhTempo,
                ];
            }
            (new HistoryTglJatuhTempoGiro())->processStore($dataHistory);
        }
        return $data;
    }
}
