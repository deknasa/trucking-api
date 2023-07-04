<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExportLaporanMingguanSupir extends Model
{
    use HasFactory;

    public function getExport($dari, $sampai, $tradodari, $tradosampai)
    {
        $full = StatusContainer::where('kodestatuscontainer', '=', 'FULL')->first();
        $fullId = $full->id;

        $empty = StatusContainer::where('kodestatuscontainer', '=', 'EMPTY')->first();
        $emptyId = $empty->id;

        $fullEmpty = StatusContainer::where('kodestatuscontainer', '=', 'FULL EMPTY')->first();
        $fullEmptyId = $fullEmpty->id;

        $dari = date("Y-m-d", strtotime($dari));
        $sampai = date("Y-m-d", strtotime($sampai));

        $tempData = '##tempData' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempData, function ($table) {
            $table->BigIncrements('id');
            $table->string('nobukti', 50)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->string('nopol', 1000)->nullable();
            $table->string('namasupir', 1000)->nullable();
            $table->string('rute', 1000)->nullable();
            $table->string('qty', 1000)->nullable();
            $table->string('lokasimuat', 1000)->nullable();
            $table->string('nocontseal', 1000)->nullable();
            $table->string('emkl', 1000)->nullable();
            $table->string('spfull', 500)->nullable();
            $table->string('spempty', 500)->nullable();
            $table->string('spfullempty', 500)->nullable();
            $table->string('jobtrucking', 500)->nullable();
            $table->double('gajisupir', 15, 2)->nullable();
            $table->string('nobuktiebs', 100)->nullable();
            $table->string('nobuktiric', 50)->nullable();
        });

        $queryTempdata = DB::table("gajisupirheader")->from(
            DB::raw("gajisupirheader as a with (readuncommitted)")
        )

            ->select(
                'c.nobukti',
                'c.tglbukti',
                'd.kodetrado as nopol',
                'e.namasupir',
                DB::raw("ltrim(rtrim(isnull(f.kodekota ,'')))+'-'+ltrim(rtrim(isnull(g.kodekota,''))) as rute"),
                DB::raw("isnull(h.kodecontainer,'') as qty"),
                DB::raw("isnull(i.namapelanggan,'') as lokasimuat"),
                DB::raw("ltrim(rtrim(isnull(c.nocont,'')))+' / '+ltrim(rtrim(isnull(c.noseal,''))) as nocontseal"),
                DB::raw("isnull(j.namaagen,'') as emkl"),
                DB::raw("(case when c.statuscontainer_id =$fullId then c.nosp else '' end) as spfull"),
                DB::raw("(case when c.statuscontainer_id =$emptyId then c.nosp else '' end) as spempty"),
                DB::raw("(case when c.statuscontainer_id =$fullEmptyId then c.nosp else '' end) as spfullempty"),
                DB::raw("isnull(C.jobtrucking,'') as jobtrucking,isnull(c.gajisupir,0) as gajisupir"),
                DB::raw("isnull(k.nobukti,'') as nobuktiebs"),
                DB::raw("isnull(A.nobukti,'') as nobuktiric"),
            )
            ->join(DB::raw("gajisupirdetail as b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw("suratpengantar as c with (readuncommitted) "), 'b.suratpengantar_nobukti', 'c.nobukti')
            ->leftjoin(DB::raw("trado as d with (readuncommitted) "), 'c.trado_id', 'd.id')
            ->leftjoin(DB::raw("supir as e with (readuncommitted) "), 'c.supir_id', 'e.id')
            ->leftjoin(DB::raw("kota as f with (readuncommitted) "), 'c.dari_id', 'f.id')
            ->leftjoin(DB::raw("kota as g with (readuncommitted) "), 'c.sampai_id', 'g.id')
            ->leftjoin(DB::raw("container as h with (readuncommitted) "), 'c.container_id', 'h.id')
            ->leftjoin(DB::raw("pelanggan as i with (readuncommitted) "), 'c.pelanggan_id', 'i.id')
            ->leftjoin(DB::raw("agen as j with (readuncommitted) "), 'c.agen_id', 'j.id')
            ->leftjoin(DB::raw("prosesgajisupirdetail as k with (readuncommitted) "), 'a.nobukti', 'k.gajisupir_nobukti')
            ->whereRaw("(c.tglbukti >= '$dari' and c.tglbukti <= '$sampai')")
            ->whereraw("(c.trado_id>=$tradodari")
            ->whereraw("c.trado_id<=$tradosampai)")
            ->orderBy("d.kodetrado", "asc")
            ->orderBy("e.namasupir", "asc")
            ->orderBy("c.tglbukti", "asc")
            ->orderBy("c.nobukti", "asc");

        DB::table($tempData)->insertUsing([
            'nobukti',
            'tglbukti',
            'nopol',
            'namasupir',
            'rute',
            'qty',
            'lokasimuat',
            'nocontseal',
            'emkl',
            'spfull',
            'spempty',
            'spfullempty',
            'jobtrucking',
            'gajisupir',
            'nobuktiebs',
            'nobuktiric',
        ], $queryTempdata);

        $tempDataOrderan = '##tempDataOrderan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempDataOrderan, function ($table) {
            $table->string('nobukti', 50)->nullable();
        });

        $queryJobTrucking = DB::table($tempData)->from(
            DB::raw($tempData . " as a with (readuncommitted)")
        )

            ->select(
                'jobtrucking'
            )
            ->groupBy("jobtrucking");

        DB::table($tempDataOrderan)->insertUsing([
            'nobukti',
        ], $queryJobTrucking);

        $tempDataInvoice = '##tempDataInvoice' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempDataInvoice, function ($table) {
            $table->string('invoice', 50)->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->longText('nobuktitrip')->nullable();
        });

        $queryDataOrderan = DB::table("invoicedetail")->from(
            DB::raw("invoicedetail as a with (readuncommitted)")
        )

            ->select(
                'a.nobukti',
                'a.orderantrucking_nobukti',
                'a.suratpengantar_nobukti'
            )
            ->join(DB::raw($tempDataOrderan . " as b "), 'a.orderantrucking_nobukti', 'b.nobukti');

        DB::table($tempDataInvoice)->insertUsing([
            'invoice',
            'nobukti',
            'nobuktitrip'
        ], $queryDataOrderan);


        $tempDataInvoiceDetail = '##tempDataInvoiceDetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempDataInvoiceDetail, function ($table) {
            $table->string('invoice', 50)->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->longText('nobuktitrip')->nullable();
            $table->datetime('tgl')->nullable();
        });

        $xinvoice = '';
        $xnobukti = '';
        $xnobuktitrip = '';

        $tempList = '##tempList' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempList, function ($table) {
            $table->string('notrip', 50)->nullable();
        });


        $rekapCursor1a = DB::table($tempDataInvoice)->select('invoice', 'nobukti', 'nobuktitrip')->get();

        $result = [];

        foreach ($rekapCursor1a as $row) {
            $xinvoice = $row->invoice;
            $xnobukti = $row->nobukti;
            $xnobuktitrip = $row->nobuktitrip;

            $nobuktitripArr = explode(',', $xnobuktitrip);
            $trimmedNobuktitripArr = array_map('trim', $nobuktitripArr);

            foreach ($trimmedNobuktitripArr as $trimmedNobuktitrip) {
                $invoiceDetails = [
                    'invoice' => $xinvoice,
                    'nobukti' => $xnobukti,
                    'nobuktitrip' => $trimmedNobuktitrip,
                ];

                $result[] = $invoiceDetails;
            }
        }
        DB::table($tempDataInvoiceDetail)->insert($result);

        DB::table($tempDataInvoiceDetail . ' as A')
            ->join('suratpengantar as B', 'A.nobuktitrip', '=', 'B.nobukti')
            ->update(['A.tgl' => DB::raw('B.tglbukti')]);

        $tempListTrip = '##tempListTrip' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempListTrip, function ($table) {
            $table->bigIncrements('id');
            $table->string('nobukti', 50)->nullable();
            $table->longText('nobuktitrip', 50)->nullable();
            $table->datetime('tgl')->nullable();
        });

        $queryListTrip = DB::table($tempDataInvoiceDetail)->from(
            DB::raw($tempDataInvoiceDetail . " as a")
        )
            ->select(
                'nobukti',
                'tgl',
                'nobuktitrip'
            )
            ->orderBy('nobukti', 'asc')
            ->orderBy('tgl', 'asc')
            ->orderBy('nobuktitrip', 'asc');

        DB::table($tempListTrip)->insertUsing([
            'nobukti',
            'tgl',
            'nobuktitrip',

        ], $queryListTrip);

        $tempRekapListTrip = '##tempListTrip' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempRekapListTrip, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->integer('id')->nullable();
        });

        $queryRekapListTrip = DB::table($tempListTrip)->from(
            DB::raw($tempListTrip . " as a")
        )
            ->select(
                'nobukti',
                DB::raw("min(id) as id")
            )
            ->groupBy('nobukti');

        DB::table($tempRekapListTrip)->insertUsing([
            'nobukti',
            'id',
        ], $queryRekapListTrip);

        $tempOrderanTrucking = '##tempOrderanTrucking' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempOrderanTrucking, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->integer('id')->nullable();
            $table->string('notrip', 50)->nullable();
        });

        $queryOrderanTrucking = DB::table($tempRekapListTrip)->from(
            DB::raw($tempRekapListTrip . " as a")
        )
            ->select(
                'nobukti',
                'id'
            );

        DB::table($tempOrderanTrucking)->insertUsing([
            'nobukti',
            'id',
        ], $queryOrderanTrucking);

        DB::table($tempOrderanTrucking . ' as a')
            ->join($tempListTrip . ' as B', 'a.id', '=', 'B.id')
            ->update(['A.notrip' => DB::raw('B.nobuktitrip')]);

        $tempInvoice = '##tempInvoice' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempInvoice, function ($table) {
            $table->string('jobtrucking', 100)->nullable();
            $table->double('omset', 15, 2)->nullable();
            $table->integer('id')->nullable();
            $table->string('invoice', 50)->nullable();
            $table->string('notrip', 50)->nullable();
        });

        $queryTempInvoice = DB::table('invoicedetail')->from(
            DB::raw("invoicedetail as a with (readuncommitted)")
        )
            ->select(
                'a.orderantrucking_nobukti',
                'a.nominal',
                'b.id',
                'a.nobukti',
                'b.notrip'
            )
            ->join(DB::raw($tempOrderanTrucking . " as b "), 'a.orderantrucking_nobukti', 'b.nobukti');

        DB::table($tempInvoice)->insertUsing([
            'jobtrucking',
            'omset',
            'id',
            'invoice',
            'notrip',
        ], $queryTempInvoice);

        $data =  DB::table($tempData)->from(
            DB::raw($tempData." as a")
        )
            ->select(
                'a.nobukti',
                'a.tglbukti',
                'a.nopol',
                'a.namasupir',
                'a.rute',
                'a.qty',
                'a.lokasimuat',
                'a.nocontseal',
                'a.emkl',
                'a.spfull',
                'a.spempty',
                'a.spfullempty',
                'a.jobtrucking',
                DB::raw("isnull(B.omset,0) as omset"),
                DB::raw("isnull(c.invoice,'') as invoice"),
                DB::raw("isnull(A.gajisupir,0) as gajisupir"),
                DB::raw("isnull(A.nobuktiebs,'') as nobuktiebs"),
            )
            ->leftjoin(DB::raw($tempInvoice . " as b "), 'a.nobukti', 'b.notrip')
            ->leftjoin(DB::raw($tempInvoice . " as c "), 'a.jobtrucking', 'c.jobtrucking')->get();

       return $data;
    }
}
