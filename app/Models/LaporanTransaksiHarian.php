<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanTransaksiHarian extends MyModel
{
    use HasFactory;

    protected $table = 'laporantransaksiharian';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];



    public function getReport($dari, $sampai)
    {

        $dari = date('Y-m-d', strtotime(request()->dari)) ?? '1900/1/1';
        $sampai = date('Y-m-d', strtotime(request()->sampai)) ?? '1900/1/1';

        $statusapproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();

        $statusapproval_id = $statusapproval->id;

        $jurnalUmumHeader = '##jurnalumumheader' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($jurnalUmumHeader, function ($table) {
            $table->BigInteger('id');
            $table->string('nobukti', 50)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('postingdari', 50)->nullable();
            $table->integer('statusapproval')->nullable();
            $table->string('userapproval', 50)->nullable();
            $table->dateTime('tglapproval')->nullable();
            $table->BigInteger('statusformat')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();
        });

        $queryJurnalUmumHeader = DB::table("jurnalumumheader")->from(
            DB::raw("jurnalumumheader as a with (readuncommitted)")
        )

            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                'a.keterangan',
                'a.postingdari',
                'a.statusapproval',
                'a.tglapproval',
                'a.statusformat',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at'

            )
            ->where('a.tglbukti', '>=', $dari)
            ->where('a.tglbukti', '<=', $sampai)
            ->where('a.statusapproval', '=', $statusapproval_id);

        DB::table($jurnalUmumHeader)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'keterangan',
            'postingdari',
            'statusapproval',
            'tglapproval',
            'statusformat',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $queryJurnalUmumHeader);

        $jurnalUmumDetail = '##jurnalumumdetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($jurnalUmumDetail, function ($table) {
            $table->BigInteger('id');
            $table->BigInteger('jurnalumum_id');
            $table->string('nobukti', 50)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->string('coa', 50)->nullable();
            $table->double('nominal')->nullable();
            $table->longText('keterangan')->nullable();
            $table->integer('baris')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();
        });

        $queryJurnalUmumDetail = DB::table("jurnalumumdetail")->from(
            DB::raw("jurnalumumdetail as a with (readuncommitted)")
        )

            ->select(
                'a.id',
                'a.jurnalumum_id',
                'a.nobukti',
                'a.tglbukti',
                'a.coa',
                'a.nominal',
                'a.keterangan',
                'a.baris',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at'

            )
            ->join(DB::raw($jurnalUmumHeader . " as b"), 'a.nobukti', 'b.nobukti');

        DB::table($jurnalUmumDetail)->insertUsing([
            'id',
            'jurnalumum_id',
            'nobukti',
            'tglbukti',
            'coa',
            'nominal',
            'keterangan',
            'baris',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $queryJurnalUmumDetail);

        $result = DB::table($jurnalUmumHeader)->from(
            DB::raw($jurnalUmumHeader . " as a")
        )
            ->select(
                'a.nobukti',
                'a.tglbukti as tanggal',
                'c.keterangancoa as akunpusat',
                'b.keterangan',
                DB::raw("CASE SIGN(B.Nominal) WHEN 1 THEN B.Nominal ELSE 0 END AS debet"),
                DB::raw("CASE SIGN(B.Nominal) WHEN 1 THEN 0 ELSE B.Nominal END AS kredit"),

            )
            ->join(DB::raw($jurnalUmumDetail . " as b with (readuncommitted)"), 'a.NObukti', 'b.nobukti')
            ->join(DB::raw("akunpusat as c with (readuncommitted)"), 'b.coa', 'c.coa')->get();

        return $result;



    }
}
