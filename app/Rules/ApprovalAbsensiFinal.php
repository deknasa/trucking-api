<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Error;
use App\Models\Parameter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApprovalAbsensiFinal implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $keterror;
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
        $allowed = true;
        $absenid = request()->absenId ?? 0;
        $idabsensi = request()->Id ?? 0;
        $tglbukti = request()->tglbukti ?? '';
        if ($tglbukti!='') {
            $tglabsensi = date('Y-m-d',strtotime(request()->tglbukti)) ?? '';
        } else {
            $tglabsensi = date('Y-m-d',strtotime(request()->tglabsensi)) ?? '';

        }

        $defaultidnonapproval = db::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
        ->select(
            'a.id'
        )
        ->where('a.grp', 'STATUS APPROVAL')
        ->where('a.subgrp', 'STATUS APPROVAL')
        ->where('a.text', 'NON APPROVAL')
        ->first()->id ?? '';

        $defaultidapproval = db::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
        ->select(
            'a.id'
        )
        ->where('a.grp', 'STATUS APPROVAL')
        ->where('a.subgrp', 'STATUS APPROVAL')
        ->where('a.text', 'APPROVAL')
        ->first()->id ?? '';     

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        if ($absenid !=0) {
            $querytgl=db::table("absensisupirheader")->from(db::raw("absensisupirheader a with (readuncommitted)"))
            ->select(
                'a.tglbukti'
            )
            ->where('a.id',$absenid)
            ->first();
            if (isset($querytgl)) {
                $tglabsensi = $querytgl->tglbukti ?? '1900-01-01';
            } else {
                $allowed = true;  
                goto selesai;
            }
        }

        if ($idabsensi!=0) {
            goto idabsensi;
        }

      
        $query=db::table("absensisupirheader")->from(db::raw("absensisupirheader a with (readuncommitted)"))
        ->select(
            'a.tglbukti',
            'a.nobukti',
            db::raw("isnull(a.statusapprovalfinalabsensi,".$defaultidnonapproval.") as statusapprovalfinalabsensi"),

        )
        ->where('a.tglbukti',$tglabsensi)
        ->first();

        
        if (isset($query)) {
            if ($query->statusapprovalfinalabsensi!= $defaultidnonapproval) {
                $allowed = false;
                $keteranganerror = $error->cekKeteranganError('SAP') ?? '';
                $this->keterror = 'No Bukti <b>' . $query->nobukti . ' </b>' . $keteranganerror . ' FINAL ABSENSI ' . $keterangantambahanerror;
    
            }
        } else {
            $allowed = true;  
        }

        goto selesai;

        idabsensi:
        $dataId = request()->Id;
        
        $tempabsensi = '##tempabsensi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempabsensi, function ($table) {
            $table->date('tglbukti')->nullable();
        });

        // dd($dataId);
        $a = 0;
        $tglabsensi1 = '';
        foreach ($dataId as $dataid) {
            $query=db::table("absensisupirheader")->from(db::raw("absensisupirheader a with (readuncommitted)"))
                ->select(
                    'a.tglbukti',
                    db::raw("isnull(a.statusapprovalfinalabsensi,".$defaultidnonapproval.") as statusapprovalfinalabsensi"),

                )
                ->join(db::raw("bukaabsensi b with (readuncommitted)"),'a.tglbukti','b.tglabsensi')
                ->where('b.id',$dataid)
                ->first();

                // dump($query);
                if (isset($query)) {
                    if ($query->statusapprovalfinalabsensi!= $defaultidnonapproval) {
                        
                        $querytemp=db::table($tempabsensi)->from(db::raw($tempabsensi . " a "))
                        ->select(
                            'a.tglbukti'
                        )
                        ->where('a.tglbukti',$query->tglbukti)
                        ->first();
                        if (!isset($querytemp)) {
                            DB::table($tempabsensi)->insert(
                                [
                                    'tglbukti' => $query->tglbukti,
                                ]
                            );                        
                            if ($a == 0) {
                                $tglabsensi1 = $tglabsensi1 . date('d-m-Y',strtotime($query->tglbukti));
                            } else {
                                $tglabsensi1 = $tglabsensi1 . ', ' . date('d-m-Y',strtotime($query->tglbukti));
                            }   
                            $a = $a + 1;
                        }
            
                    }
                } else {
                    $allowed = true;  
                }

        }

        // dd(db::table($tempabsensi)->get());
        // dd($tglabsensi1);
        if ($a >= 1) {
            $allowed = false;
            $keteranganerror = $error->cekKeteranganError('SAP') ?? '';
            $this->keterror = 'Tgl Absensi <b>' . $tglabsensi1 . '</b><br>' . $keteranganerror . ' FINAL ABSENSI <br> ' . $keterangantambahanerror;

        }

        selesai:

        return $allowed;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->keterror;
    }
}
