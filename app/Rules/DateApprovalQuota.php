<?php

namespace App\Rules;

use App\Models\HariLibur;
use Illuminate\Contracts\Validation\Rule;
use App\Models\SuratPengantarApprovalInputTrip;
use App\Models\SuratPengantar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DateApprovalQuota implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */

    public function passes($attribute, $value)
    {
        $date = date('Y-m-d', strtotime($value));
        $today = date('Y-m-d', strtotime("today"));
        $getDay = date('l', strtotime(request()->tglbukti . '+1 days'));
        $getTomorrow = date('Y-m-d', strtotime(request()->tglbukti . '+1 days'));
        $getHariLibur = HariLibur::where('tgl', $getTomorrow)->where('statusaktif', 1)->first();
        $user_id = auth('api')->user()->id;
        $allowed = false;
        $getBatasInput = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JAMBATASINPUTTRIP')->where('subgrp', 'JAMBATASINPUTTRIP')->first();
        $getBatasHari = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'BATASHARIINPUTTRIP')->where('subgrp', 'BATASHARIINPUTTRIP')->first()->text;
        $getFormat = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'INPUT TRIP')->where('subgrp', 'FORMAT BATAS INPUT')->first();
        $getapproval = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('subgrp', 'STATUS APPROVAL')->first();
        $bukaAbsensi = SuratPengantarApprovalInputTrip::where('tglbukti', '=', $date)
            ->orderBy('id', 'desc')
            ->first();
        if ($date == $today) {
            $allowed = true;
        }
        // if ($getFormat->text == 'FORMAT 2') {
        if (date('Y-m-d', strtotime(request()->tglbukti . "+$getBatasHari days")) . ' ' . $getBatasInput->text > date('Y-m-d H:i:s')) {
            $allowed = true;
        }
        // }
        $batasHari = $getBatasHari;
        $tanggal = date('Y-m-d', strtotime($date));

        $kondisi = true;
        if ($getBatasHari != 0) {

            while ($kondisi) {
                $cekHarilibur = DB::table("harilibur")->from(DB::raw("harilibur with (readuncommitted)"))
                    ->where('tgl', $tanggal)
                    ->first();

                $todayIsSunday = date('l', strtotime($tanggal));
                $tomorrowIsSunday = date('l', strtotime($tanggal . "+1 days"));
                if ($cekHarilibur == '') {
                    $kondisi = false;
                    $allowed = true;
                    if (strtolower($todayIsSunday) == 'sunday') {
                        $kondisi = true;
                        $batasHari += 1;
                    }
                    if (strtolower($tomorrowIsSunday) == 'sunday') {
                        $kondisi = true;
                        $batasHari += 1;
                    }
                } else {
                    $batasHari += 1;
                }
                $tanggal = date('Y-m-d', strtotime($date . "+$batasHari days"));
            }
        }
        if (date('Y-m-d H:i:s') > $tanggal . ' ' . $getBatasInput->text) {
            $allowed = false;
        }
        // if (strtolower($getDay) == 'sunday') {
        //     $allowed = true;
        //     $getTomorrowAfterSunday = date('Y-m-d', strtotime(request()->tglbukti . '+2 days'));
        //     $getHariLibur = HariLibur::where('tgl', $getTomorrowAfterSunday)->where('statusaktif',1)->first();
        // }

        // if ($getHariLibur != null) {
        //     $allowed = true;
        //     if (date('Y-m-d', strtotime($getHariLibur->tgl . "+$getBatasHari days")) . ' ' . $getBatasInput->text < date('Y-m-d H:i:s')) {
        //         $allowed = false;
        //     }
        // } else {
        //     $allowed = false;
        // }
        if ($bukaAbsensi) {

            // GET APPROVAL INPUTTRIP
            $tempApp = '##tempApp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempApp, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->date('tglbukti')->nullable();
                $table->unsignedBigInteger('jumlahtrip')->nullable();
                $table->unsignedBigInteger('statusapproval')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->datetime('tglbatas')->nullable();
            });

            $querybukaabsen = DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip with (readuncommitted)"))
                ->select('id', 'tglbukti', 'jumlahtrip', 'statusapproval', 'user_id', 'tglbatas')
                ->where('tglbukti', $date);
            DB::table($tempApp)->insertUsing([
                'id',
                'tglbukti',
                'jumlahtrip',
                'statusapproval',
                'user_id',
                'tglbatas',
            ],  $querybukaabsen);

            // GET MANDOR DETAIL
            $tempMandor = '##tempMandor' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempMandor, function ($table) {
                $table->id();
                $table->unsignedBigInteger('mandor_id')->nullable();
            });

            $querymandor = DB::table("mandordetail")->from(DB::raw("mandordetail with (readuncommitted)"))
                ->select('mandor_id')->where('user_id', $user_id);
            DB::table($tempMandor)->insertUsing([
                'mandor_id',
            ],  $querymandor);


            // BUAT TEMPORARY SP GROUP BY TEMPO ID
            $tempSP = '##tempSP' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempSP, function ($table) {
                $table->id();
                $table->unsignedBigInteger('approvalbukatanggal_id')->nullable();
                $table->unsignedBigInteger('jumlahtrip')->nullable();
            });

            $querySP = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
                ->select('approvalbukatanggal_id', DB::raw("count(nobukti) as jumlahtrip"))
                ->where('tglbukti', $date)
                ->whereRaw("isnull(approvalbukatanggal_id,0) != 0")
                ->groupBy('approvalbukatanggal_id');

            DB::table($tempSP)->insertUsing([
                'approvalbukatanggal_id',
                'jumlahtrip'
            ],  $querySP);


            // GET APPROVAL BERDASARKAN MANDOR
            $getAll = DB::table("mandordetail")->from(DB::raw("mandordetail as a"))
                ->select('a.mandor_id', 'c.id', 'c.user_id', 'c.statusapproval', 'c.tglbatas', 'c.jumlahtrip','e.namamandor')
                ->leftJoin(DB::raw("$tempMandor as b with (readuncommitted)"), 'a.mandor_id', 'b.mandor_id')
                ->leftJoin(DB::raw("$tempApp as c with (readuncommitted)"), 'a.user_id', 'c.user_id')
                ->leftJoin(DB::raw("$tempSP as d with (readuncommitted)"), 'c.id', 'd.approvalbukatanggal_id')
                ->leftjoin(db::raw("mandor e "),'a.mandor_id','e.id')
                ->whereRaw('COALESCE(b.mandor_id, 0) <> 0')
                ->whereRaw('COALESCE(c.user_id, 0) <> 0')
                ->whereRaw('isnull(c.user_id,0)='.$user_id)
                ->whereRaw('isnull(d.jumlahtrip,0) < c.jumlahtrip')
                ->first();
            //     dump($user_id );
            // dd($getAll);
            if ($getAll == '') {
                return false;
            }

            $now = date('Y-m-d');
            $nonApproval = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where("grp", 'STATUS APPROVAL')->where("text", "NON APPROVAL")->first();
            if ($getAll->statusapproval == $nonApproval->id) {
                return false;
            }

            $suratPengantar = SuratPengantar::where('tglbukti', '=', $date)->whereRaw("approvalbukatanggal_id = $getAll->id")->count();
            // $cekStatus =  DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip as a with (readuncommitted)"))
            //     ->where('a.tglbukti', $date)
            //     ->where('user_id', $user_id)
            //     ->orderBy('a.id', 'desc')
            //     ->first();

            $now = date('Y-m-d H:i:s');
            if ($now > $getAll->tglbatas) {
                return false;
            }

            if ($getAll->jumlahtrip < ($suratPengantar + 1)) {
                return false;
            }
            $allowed = true;
        }
        return $allowed;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Tanggal Sudah Tidak Berlaku';
    }
}
