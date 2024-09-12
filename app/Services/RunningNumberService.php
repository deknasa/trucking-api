<?php

namespace App\Services;

use App\Helpers\App;
use Illuminate\Support\Facades\DB;

class RunningNumberService
{
    public function get(string $group, string $subGroup, string $table, string $tgl, int  $tujuan = 0, int  $cabang = 0, int  $jenisbiaya = 0, int  $marketing = 0): string
    {
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
            $lastRow = DB::table($table)
                ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                ->where(DB::raw('statusformat'), '=', $statusformat)
                ->lockForUpdate()->count();


            // perubahan
            $a = 0;
            $b = $lastRow;
            $c = 0;
            while ($a <= $lastRow) {
                $nobukti = (new App)->getFormat($text, $a, $bulan, $tgl, $tujuan, $cabang, $jenisbiaya, $marketing);

                $queryCheck = DB::table($table)->where('nobukti', $nobukti)
                    ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                    ->where(DB::raw('statusformat'), '=', $statusformat)->first();

                if (!isset($queryCheck)) {
                    if ($a > 1) {
                        $c = $a - 1;
                        $nobukticek = (new App)->getFormat($text, $c, $bulan, $tgl, $tujuan, $cabang, $jenisbiaya, $marketing);

                        $queryCheckprev = DB::table($table)->where('nobukti', $nobukticek)
                            ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                            ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                            ->whereRaw("tglbukti <= '$tgl'")
                            ->where(DB::raw('statusformat'), '=', $statusformat)
                            ->orderby('tglbukti', 'desc')
                            ->orderby('nobukti', 'desc')
                            ->first();
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
            // 

        }

        if ($type == 'RESET TAHUN') {
            $lastRow = DB::table($table)
                ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                ->where(DB::raw('statusformat'), '=', $statusformat)
                ->lockForUpdate()->count();
            $a = 0;
            $b = $lastRow;
            $c = 0;
            while ($a <= $lastRow) {
                $nobukti = (new App)->getFormat($text, $a, $bulan, $tgl, $tujuan, $cabang, $jenisbiaya, $marketing);

                $queryCheck = DB::table($table)->where('nobukti', $nobukti)
                    ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                    ->where(DB::raw('statusformat'), '=', $statusformat)->first();

                if (!isset($queryCheck)) {
                    if ($a > 1) {
                        $c = $a - 1;
                        $nobukticek = (new App)->getFormat($text, $c, $bulan, $tgl, $tujuan, $cabang, $jenisbiaya, $marketing);

                        $queryCheckprev = DB::table($table)->where('nobukti', $nobukticek)
                            ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                            ->whereRaw("tglbukti <= '$tgl'")
                            ->where(DB::raw('statusformat'), '=', $statusformat)
                            ->orderby('tglbukti', 'desc')
                            ->orderby('nobukti', 'desc')
                            ->first();
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
            $lastRow = DB::table($table)
                ->where(DB::raw('statusformat'), '=', $statusformat)
                ->lockForUpdate()->count();
        }
        // $sqlcek=db::table($table)->from($table . " a with (readuncommitted)")
        // ->select ('a.nobukti')
        // ->where('a.nobukti')

        // dd($tgl);

        $runningNumber = (new App)->runningNumber($text, $lastRow, $bulan, $tgl, $table, $tujuan, $cabang, $jenisbiaya, $marketing);
        // dd($runningNumber);
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
