<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TutupBuku extends Model
{
    use HasFactory;

    public function processStore(array $data)
    {
        $tgltutupbuku = date('Y-m-d', strtotime($data['tgltutupbuku']));
        $this->saldoawalbank($tgltutupbuku);
        $this->saldoawalbukubesar($tgltutupbuku);
        $parameter = Parameter::where('grp', 'TUTUP BUKU')->where('subgrp', 'TUTUP BUKU')->first();

        $parameter->text = $tgltutupbuku;
        $parameter->modifiedby = auth('api')->user()->name;
        $parameter->info = html_entity_decode(request()->info);
        if (!$parameter->save()) {
            throw new \Exception("Error update tutup buku.");
        }
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($parameter->getTable()),
            'postingdari' => 'TUTUP BUKU',
            'idtrans' => $parameter->id,
            'nobuktitrans' => $parameter->id,
            'aksi' => 'EDIT',
            'datajson' => $parameter->toArray(),
            'modifiedby' => $parameter->modifiedby
        ]);

        return $parameter;
    }
    public function saldoawalbank($tgl1)
    {
        $bulan = date('m', strtotime($tgl1));
        $tahun = date('Y', strtotime($tgl1));
        $tgl = '01-' . $bulan . '-' . $tahun;
        $tgldari = date('Y-m-d', strtotime($tgl));
        $tgl2 = date('t-m-Y', strtotime($tgl));
        $tglsampai = date('Y-m-d', strtotime($tgl2));

        $temprekap = '##temprekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekap, function ($table) {
            $table->string('bulan', 1000)->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->double('nominaldebet', 15, 2)->nullable();
            $table->double('nominalkredit', 15, 2)->nullable();
        });

        $queryrekap = db::table('penerimaanheader')->from(db::raw("
        penerimaanheader a with(readuncommitted)
        "))
            ->select(
                db::raw("format(a.tglbukti,'MM-yyyy') as bulan"),
                'a.bank_id',
                db::raw("sum(b.nominal) as nominaldebet"),
                db::raw("0 as nominalkredit"),

            )
            ->join(db::raw("penerimaandetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("(a.tglbukti>='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
            ->groupBy(db::raw("format(a.tglbukti,'MM-yyyy')"))
            ->groupBy('a.bank_id');

        DB::table($temprekap)->insertUsing([
            'bulan',
            'bank_id',
            'nominaldebet',
            'nominalkredit',
        ],  $queryrekap);

        $queryrekap = db::table('pengeluaranheader')->from(db::raw("
        pengeluaranheader a with(readuncommitted)
        "))
            ->select(
                db::raw("format(a.tglbukti,'MM-yyyy') as bulan"),
                'a.bank_id',
                db::raw("0 as nominaldebet"),
                db::raw("sum(b.nominal) as nominalkredit"),

            )
            ->join(db::raw("pengeluarandetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("(a.tglbukti>='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
            ->groupBy(db::raw("format(a.tglbukti,'MM-yyyy')"))
            ->groupBy('a.bank_id');

        DB::table($temprekap)->insertUsing([
            'bulan',
            'bank_id',
            'nominaldebet',
            'nominalkredit',
        ],  $queryrekap);

        $temprekapall = '##temprekapall' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekapall, function ($table) {
            $table->string('bulan', 1000)->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->double('nominaldebet', 15, 2)->nullable();
            $table->double('nominalkredit', 15, 2)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $queryrekapall = db::table($temprekap)->from(db::raw($temprekap . " a "))
            ->select(
                'a.bulan',
                'a.bank_id',
                db::raw("sum(a.nominaldebet) as nominaldebet"),
                db::raw("sum(a.nominalkredit) as nominalkredit"),
                db::raw("getdate() as created_at"),
                db::raw("getdate() as updated_at"),

            )
            ->groupBy('a.bulan')
            ->groupBy('a.bank_id');

        DB::table($temprekapall)->insertUsing([
            'bulan',
            'bank_id',
            'nominaldebet',
            'nominalkredit',
            'created_at',
            'updated_at'
        ],  $queryrekapall);

        DB::delete(DB::raw("delete saldoawalbank from  saldoawalbank as a inner join " . $temprekapall . " b on a.bulan=b.bulan and a.bank_id=B.bank_id"));

        DB::table('saldoawalbank')->insertUsing([
            'bulan',
            'bank_id',
            'nominaldebet',
            'nominalkredit',
            'created_at',
            'updated_at'
        ],  $queryrekapall);
    }

    public function saldoawalbukubesar($tgl1)
    {
        $bulan = date('m', strtotime($tgl1));
        $tahun = date('Y', strtotime($tgl1));
        $tgl = '01-' . $bulan . '-' . $tahun;
        $tgldari = date('Y-m-d', strtotime($tgl));
        $tgl2 = date('t-m-Y', strtotime($tgl));
        $tglsampai = date('Y-m-d', strtotime($tgl2));

        $temprekap = '##temprekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekap, function ($table) {
            $table->string('bulan', 1000)->nullable();
            $table->string('coa', 1000)->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $queryrekap = db::table('jurnalumumpusatheader')->from(db::raw("
        jurnalumumpusatheader a with(readuncommitted)
        "))
            ->select(
                db::raw("format(a.tglbukti,'MM-yyyy') as bulan"),
                'b.coa',
                db::raw("sum(b.nominal) as nominal"),

            )
            ->join(db::raw("jurnalumumpusatdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("(a.tglbukti>='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
            ->groupBy(db::raw("format(a.tglbukti,'MM-yyyy')"))
            ->groupBy('b.coa');

        DB::table($temprekap)->insertUsing([
            'bulan',
            'coa',
            'nominal',
        ],  $queryrekap);



        $temprekapall = '##temprekapall' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekapall, function ($table) {
            $table->string('bulan', 1000)->nullable();
            $table->string('coa', 1000)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->string('modifiedby', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $queryrekapall = db::table($temprekap)->from(db::raw($temprekap . " a "))
            ->select(
                'a.bulan',
                'a.coa',
                db::raw("sum(a.nominal) as nominal"),
                db::raw("'" . auth('api')->user()->name . "'  as modifiedby"),
                db::raw("getdate() as created_at"),
                db::raw("getdate() as updated_at"),

            )
            ->groupBy('a.bulan')
            ->groupBy('a.coa');

        DB::table($temprekapall)->insertUsing([
            'bulan',
            'coa',
            'nominal',
            'modifiedby',
            'created_at',
            'updated_at'
        ],  $queryrekapall);

        DB::delete(DB::raw("delete saldoawalbukubesar from  saldoawalbukubesar as a inner join " . $temprekapall . " b on a.bulan=b.bulan and a.coa=B.coa"));

        DB::table('saldoawalbukubesar')->insertUsing([
            'bulan',
            'coa',
            'nominal',
            'modifiedby',
            'created_at',
            'updated_at'
        ],  $queryrekapall);
    }
}
