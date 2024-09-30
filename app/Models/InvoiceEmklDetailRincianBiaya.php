<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\RunningNumberService;
use Illuminate\Database\Schema\Blueprint;

class InvoiceEmklDetailRincianBiaya extends MyModel
{
    use HasFactory;

    protected $table = 'invoiceemkldetailrincianbiaya';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function processStore( array $data): InvoiceEmklDetailRincianBiaya
    {

        $paramcoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE BONGKARAN UTAMA')
            ->where('subgrp', 'DEBET')
            ->where('text', 'DEBET')
            ->first();
        $memocoa = json_decode($paramcoa->memo, true);
        $coadebet = $memocoa['JURNAL'];

        $param = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE BONGKARAN UTAMA')
            ->where('subgrp', 'KREDIT')
            ->where('text', 'KREDIT')
            ->first();
        $memo = json_decode($param->memo, true);
        $coakredit = $memo['JURNAL'];

        $keteranganbiaya = $data['keteranganbiaya'] ?? '';

        $querydetailjob = db::table("a")->from(db::raw("openjson ( '" . $keteranganbiaya . "')  "))
            ->select(
                db::raw("[value] ")
            )
            ->orderby(db::raw("[key]"), 'asc');

        $datadetail = json_decode($querydetailjob->get(), true);
        // dd($datadetail);
        foreach ($datadetail as $item) {
            $keteranganjobdetail = $item['value'];
            $biayaemkl = db::table("a")->from(db::raw("openjson ( '" . $keteranganjobdetail . "')  "))
                ->select(
                    db::raw("[value] ")
                )
                ->whereraw("[key]='biaya_emkl'")
                ->first()->value ?? '';
            $nominal = db::table("a")->from(db::raw("openjson ( '" . $keteranganjobdetail . "')  "))
                ->select(
                    db::raw("[value] ")
                )
                ->whereraw("[key]='nominal_biaya'")
                ->first()->value ?? '';

            $keterangan = db::table("a")->from(db::raw("openjson ( '" . $keteranganjobdetail . "')  "))
                ->select(
                    db::raw("[value] ")
                )
                ->whereraw("[key]='keterangan_biaya'")
                ->first()->value ?? '';

            $idbiayaemkl = db::table("biayaemkl")->from(db::raw("biayaemkl a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.kodebiayaemkl', $biayaemkl)
                ->first()->id ?? 0;
            $invoiceEMklDetailRincianBiaya = new InvoiceEmklDetailRincianBiaya();
            $invoiceEMklDetailRincianBiaya->invoiceemkl_id = $data['invoiceemkl_id'];
            $invoiceEMklDetailRincianBiaya->invoiceemkldetail_id = $data['invoiceemkldetail_id'];
            $invoiceEMklDetailRincianBiaya->nobukti = $data['nobukti'];
            $invoiceEMklDetailRincianBiaya->jobemkl_nobukti = $data['jobemkl_nobukti'] ?? '';
            $invoiceEMklDetailRincianBiaya->biayaemkl_id = $idbiayaemkl ?? 0;
            $invoiceEMklDetailRincianBiaya->nominal = $nominal ?? 0;
            $invoiceEMklDetailRincianBiaya->keterangan = $keterangan ?? '';
            $invoiceEMklDetailRincianBiaya->modifiedby = $data['modifiedby'];

            $invoiceEMklDetailRincianBiaya->save();

            $coadebet_detail[] = $coadebet;
            $coakredit_detail[] = $coakredit;
            $nominal_detail[] =  $nominal ?? 0;;
            $keterangan_detail[] =   'Nominal Prediksi ' . $biayaemkl . ' ' . $data['nobukti'];
        }
        $jurnalRequest = [
            'tanpaprosesnobukti' => 1,
            'nobukti' => $data['nobukti'],
            'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
            'postingdari' => 'ENTRY Invoice Bongkaran',
            'statusformat' => "0",
            'coakredit_detail' => $coakredit_detail,
            'coadebet_detail' => $coadebet_detail,
            'nominal_detail' => $nominal_detail,
            'keterangan_detail' => $keterangan_detail
        ];

        $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti',  $data['nobukti'])->first();
        if ($getJurnal != '') {

            $newJurnal = new JurnalUmumHeader();
            $newJurnal = $newJurnal->find($getJurnal->id);
            (new JurnalUmumHeader())->processUpdate($newJurnal, $jurnalRequest);
        } else {
            (new JurnalUmumHeader())->processStore($jurnalRequest);
        }
        if (!$invoiceEMklDetailRincianBiaya->save()) {
            throw new \Exception("Error storing Nilai Prediksi.");
        }

        return $invoiceEMklDetailRincianBiaya;
    }    
}
