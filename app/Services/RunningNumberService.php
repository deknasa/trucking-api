<?php

namespace App\Services;

use App\Helpers\App;
use Illuminate\Support\Facades\DB;

class RunningNumberService
{
    public function get(string $group, string $subGroup, string $table, string $tgl, int  $tujuan = 0, int  $cabang = 0, int  $jenisbiaya = 0, int  $marketing = 0, string $fieldnobukti = 'nobukti', string $fieldstatusformat = 'statusformat'): string
    {
        // dd($fieldnobukti);
        // dd($tujuan);
        $parameter = DB::table('parameter')
            ->select(
                DB::raw(
                    "parameter.id,
                    parameter.text,
                    isnull(type.text,'') as type"
                )

            )
            ->leftJoin('parameter as type', 'parameter.type', 'type.id')
            ->where('parameter.grp', $group)
            ->where('parameter.subgrp', $subGroup)
            ->first();


        if (!isset($parameter->text)) {
            return response([
                'status' => false,
                'message' => 'Parameter tidak ditemukan'
            ]);
        }
        $bulan = date('n', strtotime($tgl));
        $tahun = date('Y', strtotime($tgl));

        $statusformat = $parameter->id;
        $text = $parameter->text;
        $type = $parameter->type;

        if ($type == 'RESET BULAN') {
            $urut = 0;
            // dd($statusformat);
            $format = (new App)->getFormat($text, $urut, $bulan, $tgl, $tujuan, $cabang, $jenisbiaya, $marketing, $statusformat);
            $nobukti = $format[0]['nobukti'];
            $formatangka = $format[0]['formatangka'];
            $find = db::select("select charindex('" . $formatangka . "','" .  $nobukti . "') as findangka ")[0]->findangka;
            // dd($find);
            $tgluji2 = date('Y-m-d', strtotime($tgl . ' -1 day'));
            if ($tujuan != 0  &&  $marketing != 0) {
                // dd("substring(nobukti,".$find.",len('".$formatangka."'))");
                $lastRow = DB::table($table)
                    ->select(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "')) as urut"))
                    ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                    ->where(DB::raw('tujuan_id'), '=', $tujuan)
                    ->where(DB::raw('marketing_id'), '=', $marketing)
                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                    ->whereRaw("tglbukti = '$tgluji2'")                    
                    ->orderby(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "'))"), 'desc')
                    ->lockForUpdate()->first()->urut ?? 0;
                //  dd($lastRow->tosql());
                // ->lockForUpdate()->count();

            } else if ($tujuan != 0 &&  $jenisbiaya != 0 &&  $marketing != 0) {
                $lastRow = DB::table($table)
                    ->select(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "')) as urut"))
                    ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                    ->where(DB::raw('tujuan_id'), '=', $tujuan)
                    ->where(DB::raw('marketing_id'), '=', $marketing)
                    ->where(DB::raw('statusjenisbiaya'), '=', $jenisbiaya)
                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                    ->whereRaw("tglbukti = '$tgluji2'")                    
                    ->orderby(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "'))"), 'desc')
                    ->lockForUpdate()->first()->urut ?? 0;
                // ->lockForUpdate()->count();
            } else if ($tujuan != 0 &&  $cabang != 0) {

                $lastRow = DB::table($table)
                    ->select(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "')) as urut"))
                    ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                    ->where(DB::raw('cabang_id'), '=', $cabang)
                    ->where(DB::raw('tujuan_id'), '=', $tujuan)
                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                    ->whereRaw("tglbukti = '$tgluji2'")                    
                    ->orderby(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "'))"), 'desc')
                    ->lockForUpdate()->first()->urut ?? 0;
                // ->lockForUpdate()->count();
            } else if ($tujuan != 0 &&  $jenisbiaya != 0) {
                $lastRow = DB::table($table)
                    ->select(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "')) as urut"))
                    ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                    ->where(DB::raw('tujuan_id'), '=', $tujuan)
                    ->where(DB::raw('statusjenisbiaya'), '=', $jenisbiaya)
                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                    ->whereRaw("tglbukti = '$tgluji2'")                    
                    ->orderby(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "'))"), 'desc')
                    ->lockForUpdate()->first()->urut ?? 0;
                // ->lockForUpdate()->count();
            } else if ($tujuan != 0) {

                $lastRow = DB::table($table)
                    ->select(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "')) as urut"))
                    ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                    ->where(DB::raw('tujuan_id'), '=', $tujuan)
                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                    ->whereRaw("tglbukti = '$tgluji2'")                    
                    ->orderby(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "'))"), 'desc')
                    ->lockForUpdate()->first()->urut ?? 0;

                // ->lockForUpdate()->count();
            } else {
                // dd($fieldnobukti);
                $lastRow = DB::table($table)
                    ->select(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "')) as urut"))
                    ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                    ->whereRaw("tglbukti = '$tgluji2'")                    
                    ->orderby(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "'))"), 'desc')
                    ->lockForUpdate()->first()->urut ?? 0;

                // ->lockForUpdate()->count();
                // dd($lastRow->tosql());
            }
            // dd($lastRow);



            // perubahan
            $a = 0;
            $b = $lastRow;
            $c = 0;
            while ($a <= $lastRow) {
                $nobukti = (new App)->getFormat($text, $a, $bulan, $tgl, $tujuan, $cabang, $jenisbiaya, $marketing);

                if ($tujuan != 0  &&  $marketing != 0) {
                    $queryCheck = DB::table($table)->where('nobukti', $nobukti)
                        ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                        ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                        ->where(DB::raw('tujuan_id'), '=', $tujuan)
                        ->where(DB::raw('marketing_id'), '=', $marketing)
                        ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                        ->first();
                } else if ($tujuan != 0 &&  $jenisbiaya != 0 &&  $marketing != 0) {
                    $queryCheck = DB::table($table)->where('nobukti', $nobukti)
                        ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                        ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                        ->where(DB::raw('tujuan_id'), '=', $tujuan)
                        ->where(DB::raw('marketing_id'), '=', $marketing)
                        ->where(DB::raw('statusjenisbiaya'), '=', $jenisbiaya)
                        ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                        ->first();
                } else if ($tujuan != 0 &&  $cabang != 0) {
                    $queryCheck = DB::table($table)->where('nobukti', $nobukti)
                        ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                        ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                        ->where(DB::raw('cabang_id'), '=', $cabang)
                        ->where(DB::raw('tujuan_id'), '=', $tujuan)
                        ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                        ->first();
                } else if ($tujuan != 0 &&  $jenisbiaya != 0) {
                    $queryCheck = DB::table($table)->where('nobukti', $nobukti)
                        ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                        ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                        ->where(DB::raw('tujuan_id'), '=', $tujuan)
                        ->where(DB::raw('statusjenisbiaya'), '=', $jenisbiaya)
                        ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                        ->first();
                } else if ($tujuan != 0) {
                    $queryCheck = DB::table($table)->where('nobukti', $nobukti)
                        ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                        ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                        ->where(DB::raw('tujuan_id'), '=', $tujuan)
                        ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                        ->first();
                } else {
                    $queryCheck = DB::table($table)->where("$fieldnobukti", $nobukti)
                        ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                        ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                        ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                        ->first();
                }


                if (!isset($queryCheck)) {
                    if ($a > 1) {
                        $c = $a - 1;
                        $nobukticek = (new App)->getFormat($text, $c, $bulan, $tgl, $tujuan, $cabang, $jenisbiaya, $marketing);
                        $tgltransaksi = $tgl;
                        if ($tujuan != 0  &&  $marketing != 0) {

                            $ulang = true;
                            $tgluji = $tgltransaksi;
                            while ($ulang == true) {

                                $queryData = DB::table($table)
                                    ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                                    ->whereRaw("tglbukti = '$tgluji'")
                                    ->where(DB::raw('tujuan_id'), '=', $tujuan)
                                    ->where(DB::raw('marketing_id'), '=', $marketing)
                                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                                    ->orderby('tglbukti', 'desc')
                                    ->orderby('nobukti', 'desc')
                                    ->first();

                                if (isset($queryData)) {
                                    $tgltransaksi = $tgluji;
                                    $ulang = false;
                                } else {
                                    $tgluji = date('Y-m-d', strtotime($tgluji . ' -1 day'));
                                }
                            }
                            $queryCheckprev = DB::table($table)->where('nobukti', $nobukticek)
                                ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                                ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                                ->whereRaw("tglbukti = '$tgltransaksi'")
                                ->where(DB::raw('tujuan_id'), '=', $tujuan)
                                ->where(DB::raw('marketing_id'), '=', $marketing)
                                ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                                ->orderby('tglbukti', 'desc')
                                ->orderby('nobukti', 'desc')
                                ->first();
                        } else if ($tujuan != 0 &&  $jenisbiaya != 0 &&  $marketing != 0) {
                            $ulang = true;
                            $tgluji = $tgltransaksi;
                            while ($ulang == true) {

                                $queryData = DB::table($table)
                                    ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                                    ->whereRaw("tglbukti = '$tgluji'")
                                    ->where(DB::raw('tujuan_id'), '=', $tujuan)
                                    ->where(DB::raw('marketing_id'), '=', $marketing)
                                    ->where(DB::raw('statusjenisbiaya'), '=', $jenisbiaya)
                                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                                    ->orderby('tglbukti', 'desc')
                                    ->orderby('nobukti', 'desc')
                                    ->first();

                                if (isset($queryData)) {
                                    $tgltransaksi = $tgluji;
                                    $ulang = false;
                                } else {
                                    $tgluji = date('Y-m-d', strtotime($tgluji . ' -1 day'));
                                }
                            }

                            $queryCheckprev = DB::table($table)->where('nobukti', $nobukticek)
                                ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                                ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                                ->whereRaw("tglbukti = '$tgltransaksi'")
                                ->where(DB::raw('tujuan_id'), '=', $tujuan)
                                ->where(DB::raw('marketing_id'), '=', $marketing)
                                ->where(DB::raw('statusjenisbiaya'), '=', $jenisbiaya)
                                ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                                ->orderby('tglbukti', 'desc')
                                ->orderby('nobukti', 'desc')
                                ->first();
                        } else if ($tujuan != 0 &&  $cabang != 0) {

                            $ulang = true;
                            $tgluji = $tgltransaksi;
                            while ($ulang == true) {

                                $queryData = DB::table($table)
                                    ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                                    ->whereRaw("tglbukti = '$tgluji'")
                                    ->where(DB::raw('tujuan_id'), '=', $tujuan)
                                    ->where(DB::raw('cabang_id'), '=', $cabang)
                                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                                    ->orderby('tglbukti', 'desc')
                                    ->orderby('nobukti', 'desc')
                                    ->first();

                                if (isset($queryData)) {
                                    $tgltransaksi = $tgluji;
                                    $ulang = false;
                                } else {
                                    $tgluji = date('Y-m-d', strtotime($tgluji . ' -1 day'));
                                }
                            }

                            $queryCheckprev = DB::table($table)->where('nobukti', $nobukticek)
                                ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                                ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                                ->whereRaw("tglbukti = '$tgltransaksi'")
                                ->where(DB::raw('tujuan_id'), '=', $tujuan)
                                ->where(DB::raw('cabang_id'), '=', $cabang)
                                ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                                ->orderby('tglbukti', 'desc')
                                ->orderby('nobukti', 'desc')
                                ->first();
                        } else if ($tujuan != 0 &&  $jenisbiaya != 0) {

                            $ulang = true;
                            $tgluji = $tgltransaksi;
                            while ($ulang == true) {

                                $queryData = DB::table($table)
                                    ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                                    ->whereRaw("tglbukti = '$tgluji'")
                                    ->where(DB::raw('tujuan_id'), '=', $tujuan)
                                    ->where(DB::raw('statusjenisbiaya'), '=', $jenisbiaya)
                                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                                    ->orderby('tglbukti', 'desc')
                                    ->orderby('nobukti', 'desc')
                                    ->first();

                                if (isset($queryData)) {
                                    $tgltransaksi = $tgluji;
                                    $ulang = false;
                                } else {
                                    $tgluji = date('Y-m-d', strtotime($tgluji . ' -1 day'));
                                }
                            }

                            $queryCheckprev = DB::table($table)->where('nobukti', $nobukticek)
                                ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                                ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                                ->whereRaw("tglbukti = '$tgltransaksi'")
                                ->where(DB::raw('tujuan_id'), '=', $tujuan)
                                ->where(DB::raw('statusjenisbiaya'), '=', $jenisbiaya)
                                ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                                ->orderby('tglbukti', 'desc')
                                ->orderby('nobukti', 'desc')
                                ->first();
                        } else  if ($tujuan != 0) {

                            $ulang = true;
                            $tgluji = $tgltransaksi;
                            while ($ulang == true) {

                                $queryData = DB::table($table)
                                    ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                                    ->whereRaw("tglbukti = '$tgluji'")
                                    ->where(DB::raw('tujuan_id'), '=', $tujuan)
                                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                                    ->orderby('tglbukti', 'desc')
                                    ->orderby('nobukti', 'desc')
                                    ->first();

                                if (isset($queryData)) {
                                    $tgltransaksi = $tgluji;
                                    $ulang = false;
                                } else {
                                    $tgluji = date('Y-m-d', strtotime($tgluji . ' -1 day'));
                                }
                            }

                            $queryCheckprev = DB::table($table)->where('nobukti', $nobukticek)
                                ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                                ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                                ->whereRaw("tglbukti = '$tgltransaksi'")
                                ->where(DB::raw('tujuan_id'), '=', $tujuan)
                                ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                                ->orderby('tglbukti', 'desc')
                                ->orderby('nobukti', 'desc')
                                ->first();
                        } else {
                            $ulang = true;
                            $tgluji = $tgltransaksi;

                            while ($ulang == true) {

                                $queryData = DB::table($table)
                                    ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                                    ->whereRaw("tglbukti = '$tgluji'")
                                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                                    ->orderby('tglbukti', 'desc')
                                    ->orderby('nobukti', 'desc')
                                    ->first();

                                if (isset($queryData)) {
                                    $tgltransaksi = $tgluji;
                                    // dd($tgltransaksi);
                                    $ulang = false;
                                } else {
                                    $tgluji = date('Y-m-d', strtotime($tgluji . ' -1 day'));
                                }
                            }

                            // dd($tgltransaksi);
                            $queryCheckprev = DB::table($table)->where("$fieldnobukti", $nobukticek)
                                ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                                ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                                ->whereRaw("tglbukti = '$tgltransaksi'")
                                ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                                ->orderby('tglbukti', 'desc')
                                ->orderby("$fieldnobukti", 'desc')
                                ->first();
                        }

                        if (isset($queryCheckprev)) {
                            $lastRow = $a;
                            $a = $b;
                        } else {
                            $a = $b;
                        }
                    }
                }
                $a++;
            }
            // dd($lastRow);
            // 

        }

        if ($type == 'RESET TAHUN') {
            // $lastRow = DB::table($table)
            //     ->where(DB::raw('year(tglbukti)'), '=', $tahun)
            //     ->where(DB::raw('statusformat'), '=', $statusformat)
            //     ->lockForUpdate()->count();
            $urut = 0;
            $format = (new App)->getFormat($text, $urut, $bulan, $tgl, $tujuan, $cabang, $jenisbiaya, $marketing, $statusformat);
            $nobukti = $format[0]['nobukti'];
            $formatangka = $format[0]['formatangka'];
            $find = db::select("select charindex('" . $formatangka . "','" .  $nobukti . "') as findangka ")[0]->findangka;
            $tgluji2 = date('Y-m-d', strtotime($tgl . ' -1 day'));            
            if ($tujuan != 0  &&  $marketing != 0) {

                $lastRow = DB::table($table)
                    ->select(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "')) as urut"))
                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                    ->where(DB::raw('tujuan_id'), '=', $tujuan)
                    ->where(DB::raw('marketing_id'), '=', $marketing)
                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                    ->whereRaw("tglbukti = '$tgluji2'")                          
                    ->orderby(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "'))"), 'desc')
                    ->lockForUpdate()->first()->urut ?? 0;
                // ->lockForUpdate()->count();
            } else if ($tujuan != 0 &&  $jenisbiaya != 0 &&  $marketing != 0) {
                $lastRow = DB::table($table)
                    ->select(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "')) as urut"))
                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                    ->where(DB::raw('tujuan_id'), '=', $tujuan)
                    ->where(DB::raw('marketing_id'), '=', $marketing)
                    ->where(DB::raw('statusjenisbiaya'), '=', $jenisbiaya)
                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                    ->whereRaw("tglbukti = '$tgluji2'")                          
                    ->orderby(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "'))"), 'desc')
                    ->lockForUpdate()->first()->urut ?? 0;
                // ->lockForUpdate()->count();
            } else if ($tujuan != 0 &&  $cabang != 0) {

                $lastRow = DB::table($table)
                    ->select(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "')) as urut"))
                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                    ->where(DB::raw('cabang_id'), '=', $cabang)
                    ->where(DB::raw('tujuan_id'), '=', $tujuan)
                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                    ->whereRaw("tglbukti = '$tgluji2'")                          
                    ->orderby(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "'))"), 'desc')
                    ->lockForUpdate()->first()->urut ?? 0;
                // ->lockForUpdate()->count();

            } else if ($tujuan != 0 &&  $jenisbiaya != 0) {
                $lastRow = DB::table($table)
                    ->select(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "')) as urut"))
                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                    ->where(DB::raw('tujuan_id'), '=', $tujuan)
                    ->where(DB::raw('statusjenisbiaya'), '=', $jenisbiaya)
                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                    ->whereRaw("tglbukti = '$tgluji2'")                          
                    ->orderby(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "'))"), 'desc')
                    ->lockForUpdate()->first()->urut ?? 0;
                // ->lockForUpdate()->count();
            } else if ($tujuan != 0) {

                $lastRow = DB::table($table)
                    ->select(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "')) as urut"))
                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                    ->where(DB::raw('tujuan_id'), '=', $tujuan)
                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                    ->whereRaw("tglbukti = '$tgluji2'")                          
                    ->orderby(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "'))"), 'desc')
                    ->lockForUpdate()->first()->urut ?? 0;
                // ->lockForUpdate()->count();
            } else {
                $lastRow = DB::table($table)
                    ->select(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "')) as urut"))
                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                    ->whereRaw("tglbukti = '$tgluji2'")                          
                    ->orderby(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "'))"), 'desc')
                    ->lockForUpdate()->first()->urut ?? 0;
                // ->lockForUpdate()->count();
            }


            if ($tujuan != 0 &&  $jenisbiaya != 0 &&  $marketing != 0) {
                $queryCheck = DB::table($table)->where('nobukti', $nobukti)
                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                    ->where(DB::raw('tujuan_id'), '=', $tujuan)
                    ->where(DB::raw('marketing_id'), '=', $marketing)
                    ->where(DB::raw('statusjenisbiaya'), '=', $jenisbiaya)
                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                    ->first();
            } else if ($tujuan != 0 &&  $marketing != 0) {
                $queryCheck = DB::table($table)->where('nobukti', $nobukti)
                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                    ->where(DB::raw('tujuan_id'), '=', $tujuan)
                    ->where(DB::raw('marketing_id'), '=', $marketing)
                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                    ->first();
            } else if ($tujuan != 0 &&  $cabang != 0) {
                $queryCheck = DB::table($table)->where('nobukti', $nobukti)
                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                    ->where(DB::raw('tujuan_id'), '=', $tujuan)
                    ->where(DB::raw('cabang_id'), '=', $cabang)
                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                    ->first();
            } else if ($tujuan != 0 &&  $jenisbiaya != 0) {
                $queryCheck = DB::table($table)->where('nobukti', $nobukti)
                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                    ->where(DB::raw('tujuan_id'), '=', $tujuan)
                    ->where(DB::raw('statusjenisbiaya'), '=', $jenisbiaya)
                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                    ->first();
            } else if ($tujuan != 0) {
                $queryCheck = DB::table($table)->where('nobukti', $nobukti)
                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                    ->where(DB::raw('tujuan_id'), '=', $tujuan)
                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                    ->first();
            } else {
                $queryCheck = DB::table($table)->where('nobukti', $nobukti)
                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                    ->first();
            }

            $a = 0;
            $b = $lastRow;
            $c = 0;
            while ($a <= $lastRow) {
                $nobukti = (new App)->getFormat($text, $a, $bulan, $tgl, $tujuan, $cabang, $jenisbiaya, $marketing);
                if ($tujuan != 0 &&  $jenisbiaya != 0 &&  $marketing != 0) {
                    $queryCheck = DB::table($table)->where('nobukti', $nobukti)
                        ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                        ->where(DB::raw('tujuan_id'), '=', $tujuan)
                        ->where(DB::raw('marketing_id'), '=', $marketing)
                        ->where(DB::raw('statusjenisbiaya'), '=', $jenisbiaya)
                        ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                        ->first();
                } else if ($tujuan != 0 &&  $marketing != 0) {
                    $queryCheck = DB::table($table)->where('nobukti', $nobukti)
                        ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                        ->where(DB::raw('tujuan_id'), '=', $tujuan)
                        ->where(DB::raw('marketing_id'), '=', $marketing)
                        ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                        ->first();
                } else if ($tujuan != 0 &&  $cabang != 0) {
                    $queryCheck = DB::table($table)->where('nobukti', $nobukti)
                        ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                        ->where(DB::raw('tujuan_id'), '=', $tujuan)
                        ->where(DB::raw('cabang_id'), '=', $cabang)
                        ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                        ->first();
                } else if ($tujuan != 0 &&  $jenisbiaya != 0) {
                    $queryCheck = DB::table($table)->where('nobukti', $nobukti)
                        ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                        ->where(DB::raw('tujuan_id'), '=', $tujuan)
                        ->where(DB::raw('statusjenisbiaya'), '=', $jenisbiaya)
                        ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                        ->first();
                } else if ($tujuan != 0) {
                    $queryCheck = DB::table($table)->where('nobukti', $nobukti)
                        ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                        ->where(DB::raw('tujuan_id'), '=', $tujuan)
                        ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                        ->first();
                } else {
                    $queryCheck = DB::table($table)->where('nobukti', $nobukti)
                        ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                        ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                        ->first();
                }

                if (!isset($queryCheck)) {
                    if ($a > 1) {
                        $c = $a - 1;
                        $nobukticek = (new App)->getFormat($text, $c, $bulan, $tgl, $tujuan, $cabang, $jenisbiaya, $marketing);
                        $tgltransaksi = $tgl;
                        if ($tujuan != 0 &&    $marketing != 0) {

                            $ulang = true;
                            $tgluji = $tgltransaksi;
                            while ($ulang == true) {

                                $queryData = DB::table($table)
                                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                                    ->whereRaw("tglbukti= '$tgluji'")
                                    ->where(DB::raw('tujuan_id'), '=', $tujuan)
                                    ->where(DB::raw('marketing_id'), '=', $marketing)
                                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                                    ->orderby('tglbukti', 'desc')
                                    ->orderby('nobukti', 'desc')
                                    ->first();

                                if (isset($queryData)) {
                                    $tgltransaksi = $tgluji;
                                    $ulang = false;
                                } else {
                                    $tgluji = date('Y-m-d', strtotime($tgluji . ' -1 day'));
                                }
                            }


                            $queryCheckprev = DB::table($table)->where('nobukti', $nobukticek)
                                ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                                ->whereRaw("tglbukti= '$tgltransaksi'")
                                ->where(DB::raw('tujuan_id'), '=', $tujuan)
                                ->where(DB::raw('marketing_id'), '=', $marketing)
                                ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                                ->orderby('tglbukti', 'desc')
                                ->orderby('nobukti', 'desc')
                                ->first();
                        } else if ($tujuan != 0 &&  $jenisbiaya != 0 &&  $marketing != 0) {
                            $ulang = true;
                            $tgluji = $tgltransaksi;
                            while ($ulang == true) {

                                $queryData = DB::table($table)
                                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                                    ->whereRaw("tglbukti = '$tgluji'")
                                    ->where(DB::raw('tujuan_id'), '=', $tujuan)
                                    ->where(DB::raw('marketing_id'), '=', $marketing)
                                    ->where(DB::raw('statusjenisbiaya'), '=', $jenisbiaya)
                                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                                    ->orderby('tglbukti', 'desc')
                                    ->orderby('nobukti', 'desc')
                                    ->first();

                                if (isset($queryData)) {
                                    $tgltransaksi = $tgluji;
                                    $ulang = false;
                                } else {
                                    $tgluji = date('Y-m-d', strtotime($tgluji . ' -1 day'));
                                }
                            }

                            $queryCheckprev = DB::table($table)->where('nobukti', $nobukticek)
                                ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                                ->whereRaw("tglbukti = '$tgltransaksi'")
                                ->where(DB::raw('tujuan_id'), '=', $tujuan)
                                ->where(DB::raw('marketing_id'), '=', $marketing)
                                ->where(DB::raw('statusjenisbiaya'), '=', $jenisbiaya)
                                ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                                ->orderby('tglbukti', 'desc')
                                ->orderby('nobukti', 'desc')
                                ->first();
                        } else if ($tujuan != 0 &&  $cabang != 0) {
                            $ulang = true;
                            $tgluji = $tgltransaksi;
                            while ($ulang == true) {

                                $queryData = DB::table($table)
                                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                                    ->whereRaw("tglbukti = '$tgluji'")
                                    ->where(DB::raw('tujuan_id'), '=', $tujuan)
                                    ->where(DB::raw('cabang_id'), '=', $cabang)
                                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                                    ->orderby('tglbukti', 'desc')
                                    ->orderby('nobukti', 'desc')
                                    ->first();

                                if (isset($queryData)) {
                                    $tgltransaksi = $tgluji;
                                    $ulang = false;
                                } else {
                                    $tgluji = date('Y-m-d', strtotime($tgluji . ' -1 day'));
                                }
                            }
                            $queryCheckprev = DB::table($table)->where('nobukti', $nobukticek)
                                ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                                ->whereRaw("tglbukti = '$tgltransaksi'")
                                ->where(DB::raw('tujuan_id'), '=', $tujuan)
                                ->where(DB::raw('cabang_id'), '=', $cabang)
                                ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                                ->orderby('tglbukti', 'desc')
                                ->orderby('nobukti', 'desc')
                                ->first();
                        } else if ($tujuan != 0 &&  $jenisbiaya != 0) {
                            $ulang = true;
                            $tgluji = $tgltransaksi;
                            while ($ulang == true) {

                                $queryData = DB::table($table)
                                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                                    ->whereRaw("tglbukti = '$tgluji'")
                                    ->where(DB::raw('tujuan_id'), '=', $tujuan)
                                    ->where(DB::raw('statusjenisbiaya'), '=', $jenisbiaya)
                                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                                    ->orderby('tglbukti', 'desc')
                                    ->orderby('nobukti', 'desc')
                                    ->first();

                                if (isset($queryData)) {
                                    $tgltransaksi = $tgluji;
                                    $ulang = false;
                                } else {
                                    $tgluji = date('Y-m-d', strtotime($tgluji . ' -1 day'));
                                }
                            }

                            $queryCheckprev = DB::table($table)->where('nobukti', $nobukticek)
                                ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                                ->whereRaw("tglbukti = '$tgltransaksi'")
                                ->where(DB::raw('tujuan_id'), '=', $tujuan)
                                ->where(DB::raw('statusjenisbiaya'), '=', $jenisbiaya)
                                ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                                ->orderby('tglbukti', 'desc')
                                ->orderby('nobukti', 'desc')
                                ->first();
                        } else if ($tujuan != 0) {
                            $ulang = true;
                            $tgluji = $tgltransaksi;
                            while ($ulang == true) {

                                $queryData = DB::table($table)
                                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                                    ->whereRaw("tglbukti = '$tgluji'")
                                    ->where(DB::raw('tujuan_id'), '=', $tujuan)
                                    ->where(DB::raw('statusformat'), '=', $statusformat)
                                    ->orderby('tglbukti', 'desc')
                                    ->orderby('nobukti', 'desc')
                                    ->first();

                                if (isset($queryData)) {
                                    $tgltransaksi = $tgluji;
                                    $ulang = false;
                                } else {
                                    $tgluji = date('Y-m-d', strtotime($tgluji . ' -1 day'));
                                }
                            }

                            $queryCheckprev = DB::table($table)->where('nobukti', $nobukticek)
                                ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                                ->whereRaw("tglbukti = '$tgltransaksi'")
                                ->where(DB::raw('tujuan_id'), '=', $tujuan)
                                ->where(DB::raw('statusformat'), '=', $statusformat)
                                ->orderby('tglbukti', 'desc')
                                ->orderby('nobukti', 'desc')
                                ->first();
                        } else {
                            $ulang = true;
                            $tgluji = $tgltransaksi;
                            while ($ulang == true) {

                                $queryData = DB::table($table)
                                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                                    ->whereRaw("tglbukti = '$tgluji'")
                                    ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                                    ->orderby('tglbukti', 'desc')
                                    ->orderby('nobukti', 'desc')
                                    ->first();

                                if (isset($queryData)) {
                                    $tgltransaksi = $tgluji;
                                    $ulang = false;
                                } else {
                                    $tgluji = date('Y-m-d', strtotime($tgluji . ' -1 day'));
                                }
                            }

                            $queryCheckprev = DB::table($table)->where('nobukti', $nobukticek)
                                ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                                ->whereRaw("tglbukti = '$tgltransaksi'")
                                ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                                ->orderby('tglbukti', 'desc')
                                ->orderby('nobukti', 'desc')
                                ->first();
                        }

                        if (isset($queryCheckprev)) {
                            $lastRow = $a;
                            $a = $b;
                        } else {
                            $a = $b;
                        }
                    }
                }
                $a++;
            }
        }
        if ($type == '') {
            $urut = 0;
            $format = (new App)->getFormat($text, $urut, $bulan, $tgl, $tujuan, $cabang, $jenisbiaya, $marketing, $statusformat);
            $nobukti = $format[0]['nobukti'];
            $formatangka = $format[0]['formatangka'];
            $find = db::select("select charindex('" . $formatangka . "','" .  $nobukti . "') as findangka ")[0]->findangka;

            $lastRow = DB::table($table)
                ->select(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "')) as urut"))
                ->where(DB::raw($fieldstatusformat), '=', $statusformat)
                ->orderby(db::raw("substring($fieldnobukti," . $find . ",len('" . $formatangka . "'))"), 'desc')
                ->lockForUpdate()->first()->urut ?? 0;
            // ->lockForUpdate()->count();
        }
        // $sqlcek=db::table($table)->from($table . " a with (readuncommitted)")
        // ->select ('a.nobukti')
        // ->where('a.nobukti')

        // dd($tgl);

        $runningNumber = (new App)->runningNumber($text, $lastRow, $bulan, $tgl, $table, $tujuan, $cabang, $jenisbiaya, $marketing, $fieldnobukti);
        // $nilai = 0;
        // $nomor = $lastRow;
        // while ($nilai < 1) {
        //     $cekbukti = DB::table($table)
        //         ->where(DB::raw('nobukti'), '=', $runningNumber,$tgl)
        //         ->first();
        //     if (!isset($cekbukti)) {
        //         $nilai++;
        //         break;
        //     }
        //     $nomor++;
        //     $runningNumber = (new App)->runningNumber($text, $nomor, $bulan,$tgl,$table);
        // }

        return $runningNumber;
    }
}
